<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Exports_async_api — Async export generation API for Vue.js frontend
 *
 * This controller handles async export job creation and file delivery.
 * All reports are processed via the Queue_worker background process.
 *
 * The old Exports.php controller remains untouched for backward compatibility.
 *
 * API Endpoints:
 *   POST   /exports_async_api/short_report      — Queue short report generation
 *   GET    /exports_async_api/job_status/:id    — Poll job status
 *   GET    /exports_async_api/form_data          — Load outlets/departments/positions/sections/employees/groups
 *   GET    /exports_async_api/jobs              — List recent export jobs
 *   POST   /exports_async_api/retry/:job_id     — Retry failed export job
 *   GET    /exports_async_api/download/:filename — Serve generated file
 *   GET    /exports_async_api/queue_stats        — Queue health metrics
 *   POST   /exports_async_api/cancel/:job_id    — Cancel pending/processing job
 *   POST   /exports_async_api/delete/:job_id    — Delete job from queue
 *
 * Authentication: Bearer token required
 */
class Exports_async_api extends CI_Controller
{
    private $API_KEY = 'inv-T1m3-P@yr0ll-2026-s3cur3K3y!';
    private $async_report_registry;

    private function resolve_company_id($input = [])
    {
        $user = get_user();
        if (is_array($user) && !empty($user['company_id'])) {
            return (int)$user['company_id'];
        }

        if (is_array($input) && isset($input['company_id'])) {
            return (int)$input['company_id'];
        }

        $query_company = (int)$this->input->get('company_id');
        if ($query_company > 0) {
            return $query_company;
        }

        return 0;
    }

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('general_helper');
        $this->load->model('Queue_model', 'queue');
        $this->load->library('excel');
        require_once APPPATH . 'libraries/QueueWorker/Support/Async_report_registry.php';
        $this->async_report_registry = new Async_report_registry();
    }

    /**
     * Validate Bearer token authentication
     */
    private function authenticate()
    {
        $header = $this->input->get_request_header('Authorization', TRUE);
        if (!$header || strpos($header, 'Bearer ') !== 0) {
            $this->response_json(['status' => 'error', 'message' => 'Missing or invalid Authorization header'], 401);
            return false;
        }
        $token = substr($header, 7);
        if ($token !== $this->API_KEY) {
            $this->response_json(['status' => 'error', 'message' => 'Invalid API key'], 401);
            return false;
        }
        return true;
    }

    /**
     * POST /exports_async_api/short_report
     *
     * Queue a short report generation job.
     *
     * Request Body (JSON):
     * {
     *   "company_id": 286,
     *   "from_date": "01/01/2026",
     *   "to_date": "31/01/2026",
    *   "file_type": "xlsx",  // xlsx | xls | pdf
    *   "data_source": "realtime", // realtime | month_lock
     *   "branch": [],
     *   "department": [],
     *   "position": [],
     *   "section": null,
     *   "employee": [],
     *   "exclude_employee": [],
     *   "priority": 5  // optional, 1-10
     * }
     *
     * Response (202 Accepted):
     * {
     *   "status": "queued",
     *   "job_id": "uuid-v4",
     *   "type": "export_short_report",
     *   "message": "Report generation queued. Poll /exports_async_api/job_status/{job_id}"
     * }
     */
    public function short_report()
    {
        if (!$this->authenticate()) {
            return;
        }

        // Accept both JSON and form-data
        $raw = file_get_contents("php://input");
        $input = json_decode($raw, true);
        if (empty($input) || !is_array($input)) {
            $input = array_merge($_GET, $_POST);
        }

        $company_id = $this->resolve_company_id($input);
        if ($company_id <= 0) {
            $this->response_json(['error' => 'Missing or invalid company_id'], 400);
            return;
        }
        $input['company_id'] = $company_id;

        // Validate required fields
        if (!isset($input['from_date']) || !isset($input['to_date'])) {
            $this->response_json(['error' => 'Missing required fields: from_date, to_date'], 400);
            return;
        }

        if (!isset($input['file_type'])) {
            $input['file_type'] = 'xlsx'; // default
        }

        if (!in_array($input['file_type'], ['xlsx', 'xls', 'pdf'])) {
            $this->response_json(['error' => 'Invalid file_type. Must be xlsx, xls, or pdf'], 400);
            return;
        }

        if (!isset($input['data_source']) || $input['data_source'] === '') {
            $input['data_source'] = 'month_lock';
        }

        if (!in_array($input['data_source'], ['realtime', 'month_lock'], true)) {
            $this->response_json(['error' => 'Invalid data_source. Must be realtime or month_lock'], 400);
            return;
        }

        // Generate job_id
        $job_id = $this->uuid_v4();

        // Get priority (default 5)
        $priority = isset($input['priority']) ? (int)$input['priority'] : 5;
        $priority = max(1, min(10, $priority)); // clamp 1-10

        $requested_type = isset($input['type']) ? trim((string)$input['type']) : 'short';
        $job_type = $requested_type !== '' ? $requested_type : 'short';

        if (!$this->is_supported_async_report_type($job_type)) {
            $this->response_json([
                'error' => 'Unsupported async report type: ' . $job_type,
                'supported_types' => $this->get_supported_async_report_types()
            ], 400);
            return;
        }

        // Create job
        $this->queue->create_job($job_id, $job_type, $input, $priority, (int)$company_id);

        $this->response_json([
            'status' => 'queued',
            'job_id' => $job_id,
            'type' => $job_type,
            'file_type' => $input['file_type'],
            'message' => "Report generation has been queued. Poll GET /exports_async_api/job_status/{$job_id} for results."
        ], 202);
    }

    /**
     * GET /exports_async_api/form_data?company_id=286
     *
     * Load frontend dropdown data for export form.
     */
    public function form_data()
    {
        if (!$this->authenticate()) {
            return;
        }

        $company_id = $this->resolve_company_id();
        if ($company_id <= 0) {
            $this->response_json(['error' => 'Missing or invalid company_id'], 400);
            return;
        }

        $outlets = $this->db->query("SELECT id,name FROM branches WHERE company_id = ? ORDER BY name", [$company_id])->result();
        $departments = $this->db->query("SELECT id,name FROM departments WHERE company_id = ? ORDER BY name", [$company_id])->result();
        $positions = $this->db->query("SELECT id,title as name FROM positions WHERE company_id = ? ORDER BY name", [$company_id])->result();
        $sections = $this->db->query("SELECT id,title as name FROM sections WHERE company_id = ? ORDER BY name", [$company_id])->result();

        $employees = $this->db->query("SELECT employees.id, branch_id, position_id, section_id, department_id, special_id, first_name, last_name, resignation_date, termination_date, employee_status
            FROM employees
            INNER JOIN roles ON employees.role_id = roles.id
            WHERE employees.deleted_at IS NULL
              AND roles.exclude_from_system = 'no'
              AND employees.company_id = ?
              AND (
                employees.employee_status = 'active'
                OR (employees.employee_status = 'terminated' AND employees.termination_date IS NOT NULL AND employees.termination_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
                OR (employees.employee_status = 'resigned' AND employees.resignation_date IS NOT NULL AND employees.resignation_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
              )
            ORDER BY special_id", [$company_id])->result();

        $groups = $this->db->query("SELECT * FROM employee_groups WHERE company_id = ?", [$company_id])->result();

        $this->response_json([
            'status' => 'success',
            'data' => [
                'outlets' => $outlets,
                'departments' => $departments,
                'positions' => $positions,
                'sections' => $sections,
                'employees' => $employees,
                'groups' => $groups
            ]
        ], 200);
    }

    /**
     * GET /exports_async_api/job_status/{job_id}
     *
     * Poll the status of an export job.
     *
     * Response (pending/processing):
     * {
     *   "job_id": "uuid",
     *   "type": "export_short_report",
     *   "status": "processing",
     *   "attempts": 1,
     *   "created_at": "2026-01-15 10:30:00",
     *   "started_at": "2026-01-15 10:30:05",
     *   "message": "Report is being generated..."
     * }
     *
     * Response (completed):
     * {
     *   "job_id": "uuid",
     *   "type": "export_short_report",
     *   "status": "completed",
     *   "file_url": "http://localhost/Majestic/invotime/exports_async_api/download/short_report_123456.xlsx",
     *   "file_name": "(All) Short Summary - 01 Jan, 2026 to 31 Jan, 2026.xlsx",
     *   "file_size": "45 KB",
     *   "completed_at": "2026-01-15 10:30:17"
     * }
     *
     * Response (failed):
     * {
     *   "job_id": "uuid",
     *   "status": "failed",
     *   "error": "No employees found",
     *   "attempts": 3
     * }
     */
    public function job_status($job_id = null)
    {
        if (!$this->authenticate()) {
            return;
        }

        if (!$job_id) {
            $this->response_json(['error' => 'Missing job_id'], 400);
            return;
        }

        $job = $this->queue->get_by_job_id($job_id);

        if (!$job) {
            $this->response_json(['error' => 'Job not found'], 404);
            return;
        }

        $response = [
            'job_id' => $job->job_id,
            'type' => $job->type,
            'status' => $job->status,
            'attempts' => (int)$job->attempts,
            'created_at' => $job->created_at,
            'started_at' => $job->started_at,
            'completed_at' => $job->completed_at
        ];

        if ($job->status === 'pending' || $job->status === 'processing') {
            $response['message'] = $job->status === 'pending'
                ? 'Report is queued and waiting to be processed.'
                : 'Report is being generated. Please poll again shortly.';
        } else if ($job->status === 'completed') {
            $result = json_decode($job->result, true);

            if ($result && isset($result['file_path'])) {
                $file_path = $result['file_path'];
                $file_name = isset($result['file_name']) ? $result['file_name'] : basename($file_path);
                $file_url = isset($result['file_url']) ? $result['file_url'] : $this->build_public_file_url($file_path);

                if ($file_url !== null) {
                    $file_size = file_exists($file_path) ? filesize($file_path) : null;
                    $response['file_url'] = $file_url;
                    $response['file_name'] = $result['file_name'] ?? $file_name;
                    if ($file_size !== null) {
                        $response['file_size'] = $this->format_bytes($file_size);
                    }
                    $response['report_summary'] = $result['summary'] ?? null;
                } else {
                    $response['warning'] = 'File URL could not be determined for this report';
                }
            }
        } else if ($job->status === 'failed') {
            $response['error'] = $job->error;
        }

        $this->response_json($response, 200);
    }

    /**
     * GET /exports_async_api/job_progress/{job_id}
     *
     * Fetch real-time progress information during job processing.
     * Returns step-by-step progress data for frontend UI updates.
     *
     * Example response:
     * {
     *   "job_id": "abc123...",
     *   "status": "processing",
     *   "progress": {
     *     "step": 5,
     *     "total_steps": 6,
     *     "title": "Calculating summary data...",
     *     "processed": 50,
     *     "total": 254,
     *     "percentage": 19.7,
     *     "message": "Processed 50/254 employees",
        $requested_target = $this->input->get('url', true);
        if ($requested_target === null || $requested_target === '') {
            $requested_target = $filename;
     *   "estimated_remaining": "14.2s"
     * }
        if (!$requested_target) {
    public function job_progress($job_id = null)
    {
        if (!$this->authenticate()) {
            return;
        $requested_target = urldecode($requested_target);

        if (preg_match('#^https?://#i', $requested_target)) {
            redirect($requested_target);
            return;
        }

        if (strpos($requested_target, 'uploads/summary/') !== 0) {
            $requested_target = 'uploads/summary/' . ltrim($requested_target, '/');
        }

        redirect(base_url() . ltrim($requested_target, '/'));
                $now = time();
                $elapsed = $now - $start;
                $response['elapsed_seconds'] = $elapsed;
                $response['elapsed_time'] = $this->format_seconds($elapsed);

                // Estimate remaining time if we have step progress
                if (isset($progress['step'], $progress['total_steps'], $progress['processed'], $progress['total'])) {
                    $step = (int)$progress['step'];
                    $total_steps = (int)$progress['total_steps'];
                    $processed = (int)$progress['processed'];
                    $total = (int)$progress['total'];

                    if ($step > 0 && $total > 0 && $processed > 0) {
                        // Average time per employee so far
                        $avg_per_record = $elapsed / $processed;
                        $remaining_records = $total - $processed;
                        // Rough estimate: remaining records + buffer for final steps
                        $estimated_remaining = ($remaining_records * $avg_per_record) + (($total_steps - $step) * 2);
                        $response['estimated_remaining_seconds'] = (int)round($estimated_remaining);
                        $response['estimated_remaining_time'] = $this->format_seconds($estimated_remaining);
                        $response['completion_percentage'] = round(($processed / $total) * 100, 1);
                    }
                }
            }
        } else if ($job->status === 'pending') {
            $response['message'] = 'Job is queued and waiting to be processed.';
        } else if ($job->status === 'completed') {
            $response['message'] = 'Job completed successfully.';
            $response['completed_at'] = $job->completed_at;
        } else if ($job->status === 'failed') {
            $response['error'] = $job->error;
        }

        $this->response_json($response, 200);
    }

    /**
     * GET /exports_async_api/download/{filename}
     *
     * Serve a generated export file.
     * This endpoint does NOT require authentication for easier download links.
     * Files are served through their public URL under uploads/summary/.
     */
    public function download($filename = null)
    {
        $requested_target = $this->input->get('url', true);
        if ($requested_target === null || $requested_target === '') {
            $requested_target = $filename;
        }

        if (!$requested_target) {
            show_404();
            return;
        }

        $requested_target = urldecode($requested_target);

        if (preg_match('#^https?://#i', $requested_target)) {
            redirect($requested_target);
            return;
        }

        if (strpos($requested_target, 'uploads/summary/') !== 0) {
            $requested_target = 'uploads/summary/' . ltrim($requested_target, '/');
        }

        redirect(base_url() . ltrim($requested_target, '/'));
        exit;
    }

    /**
     * GET /exports_async_api/queue_stats
     *
     * Get export queue statistics (for monitoring dashboard).
     *
     * Response:
     * {
     *   "status": "success",
     *   "queue": {
     *     "pending": 3,
     *     "processing": 1,
     *     "completed": 127,
     *     "failed": 2
     *   }
     * }
     */
    public function queue_stats()
    {
        if (!$this->authenticate()) {
            return;
        }

        $stats = $this->queue->get_stats();

        $this->response_json([
            'status' => 'success',
            'queue' => $stats
        ], 200);
    }

    /**
     * GET /exports_async_api/jobs?limit=20
     *
     * List recent export jobs for frontend queue dashboard.
     */
    public function jobs()
    {
        if (!$this->authenticate()) {
            return;
        }

        $limit = (int)$this->input->get('limit');
        if ($limit <= 0) {
            $limit = 20;
        }

        $rows = $this->queue->get_recent_jobs($limit);
        $jobs = [];

        foreach ($rows as $row) {
            if (!$this->is_report_job_type($row->type)) {
                continue;
            }

            $payload = json_decode($row->payload, true);
            $result = json_decode($row->result, true);
            if (!is_array($payload)) {
                $payload = [];
            }
            if (!is_array($result)) {
                $result = [];
            }

            $max_attempts = isset($row->max_attempts) ? (int)$row->max_attempts : 3;
            $attempts = (int)$row->attempts;

            $item = [
                'job_id' => $row->job_id,
                'type' => $row->type,
                'status' => $row->status,
                'attempts' => $attempts,
                'max_attempts' => $max_attempts,
                'created_at' => $row->created_at,
                'started_at' => $row->started_at,
                'completed_at' => $row->completed_at,
                'error' => $row->error,
                'can_retry' => ($row->status === 'failed' && $attempts < $max_attempts),
                'request' => [
                    'company_id' => isset($payload['company_id']) ? $payload['company_id'] : null,
                    'from_date' => isset($payload['from_date']) ? $payload['from_date'] : null,
                    'to_date' => isset($payload['to_date']) ? $payload['to_date'] : null,
                    'file_type' => isset($payload['file_type']) ? $payload['file_type'] : null,
                    'data_source' => isset($payload['data_source']) ? $payload['data_source'] : 'month_lock'
                ]
            ];

            if ($row->status === 'completed' && isset($result['file_path'])) {
                $file_name = isset($result['file_name']) ? $result['file_name'] : basename($result['file_path']);
                $item['file_name'] = isset($result['file_name']) ? $result['file_name'] : $file_name;
                $item['file_url'] = isset($result['file_url']) ? $result['file_url'] : $this->build_public_file_url($result['file_path']);
            }

            $jobs[] = $item;
        }

        $this->response_json([
            'status' => 'success',
            'jobs' => $jobs
        ], 200);
    }

    /**
     * POST /exports_async_api/retry/{job_id}
     *
     * Requeue a failed export job.
     */
    public function retry($job_id = null)
    {
        if (!$this->authenticate()) {
            return;
        }

        if (!$job_id) {
            $this->response_json(['error' => 'Missing job_id'], 400);
            return;
        }

        $job = $this->queue->get_by_job_id($job_id);
        if (!$job) {
            $this->response_json(['error' => 'Job not found'], 404);
            return;
        }

        if (!$this->is_report_job_type($job->type)) {
            $this->response_json(['error' => 'Only report export jobs can be retried from this endpoint'], 400);
            return;
        }

        if ($job->status !== 'failed') {
            $this->response_json(['error' => 'Only failed jobs can be retried'], 400);
            return;
        }

        $max_attempts = isset($job->max_attempts) ? (int)$job->max_attempts : 3;
        if ((int)$job->attempts >= $max_attempts) {
            $this->response_json(['error' => 'Max retry attempts reached'], 400);
            return;
        }

        $this->queue->requeue($job->id);

        $this->response_json([
            'status' => 'queued',
            'job_id' => $job->job_id,
            'type' => $job->type,
            'message' => 'Job requeued successfully'
        ], 200);
    }

    /**
     * POST /exports_async_api/cancel/{job_id}
     *
     * Cancel a pending or processing job by marking it as failed.
     */
    public function cancel($job_id = null)
    {
        if (!$this->authenticate()) {
            return;
        }

        if (!$job_id) {
            $this->response_json(['error' => 'Missing job_id'], 400);
            return;
        }

        $job = $this->queue->get_by_job_id($job_id);
        if (!$job) {
            $this->response_json(['error' => 'Job not found'], 404);
            return;
        }

        if (!$this->is_report_job_type($job->type)) {
            $this->response_json(['error' => 'Only report export jobs can be cancelled from this endpoint'], 400);
            return;
        }

        if ($job->status !== 'pending' && $job->status !== 'processing') {
            $this->response_json(['error' => 'Only pending or processing jobs can be cancelled'], 400);
            return;
        }

        $this->queue->cancel_job($job->id);

        $this->response_json([
            'status' => 'cancelled',
            'job_id' => $job->job_id,
            'message' => 'Job cancelled successfully'
        ], 200);
    }

    /**
     * POST /exports_async_api/delete/{job_id}
     *
     * Delete a job entirely from the queue.
     */
    public function delete($job_id = null)
    {
        if (!$this->authenticate()) {
            return;
        }

        if (!$job_id) {
            $this->response_json(['error' => 'Missing job_id'], 400);
            return;
        }

        $job = $this->queue->get_by_job_id($job_id);
        if (!$job) {
            $this->response_json(['error' => 'Job not found'], 404);
            return;
        }

        if (!$this->is_report_job_type($job->type)) {
            $this->response_json(['error' => 'Only report export jobs can be deleted from this endpoint'], 400);
            return;
        }

        $this->queue->delete_job($job->id);

        $this->response_json([
            'status' => 'deleted',
            'job_id' => $job->job_id,
            'message' => 'Job deleted successfully'
        ], 200);
    }

    // ==================================================================
    //  HELPERS
    // ==================================================================

    /**
     * Send JSON response and exit
     */
    private function response_json($data, $status_code = 200)
    {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function is_report_job_type($type)
    {
        return (string)$type !== 'month_lock_generate';
    }

    private function get_supported_async_report_types()
    {
        return $this->async_report_registry->get_supported_async_report_types();
    }

    private function is_supported_async_report_type($type)
    {
        return $this->async_report_registry->is_supported_async_report_type($type);
    }

    /**
     * Generate UUID v4
     */
    private function uuid_v4()
    {
        $data = function_exists('random_bytes')
            ? random_bytes(16)
            : openssl_random_pseudo_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Format bytes to human-readable size
     */
    private function format_bytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private function build_public_file_url($file_path)
    {
        $file_path = str_replace('\\', '/', (string)$file_path);
        $base_path = rtrim(str_replace('\\', '/', FCPATH), '/') . '/';

        if (strpos($file_path, $base_path) !== 0) {
            return null;
        }

        $relative_path = ltrim(substr($file_path, strlen($base_path)), '/');

        return base_url() . $relative_path;
    }

    /**
     * Format seconds to human-readable time (HH:MM:SS or MM:SS)
     */
    private function format_seconds($seconds)
    {
        $seconds = max(0, (int)$seconds);

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }
        return sprintf('%d:%02d', $minutes, $secs);
    }
}
