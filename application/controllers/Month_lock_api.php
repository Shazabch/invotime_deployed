<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Month_lock_api extends CI_Controller
{
    private $API_KEY = 'inv-T1m3-P@yr0ll-2026-s3cur3K3y!';

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Queue_model', 'queue');
        $this->load->model('Month_lock_model', 'month_lock');
    }

    private function response_json($data, $status_code = 200)
    {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function authenticate_or_user()
    {
        $user = function_exists('get_user') ? get_user() : null;
        if (is_array($user) && !empty($user['company_id'])) {
            return array('ok' => true, 'user' => $user, 'auth_type' => 'session');
        }

        $header = $this->input->get_request_header('Authorization', true);
        if ($header && strpos($header, 'Bearer ') === 0) {
            $token = substr($header, 7);
            if ($token === $this->API_KEY) {
                return array('ok' => true, 'user' => null, 'auth_type' => 'api_key');
            }
        }

        return array('ok' => false);
    }

    private function resolve_company_id($input, $auth)
    {
        if ($auth['auth_type'] === 'session' && !empty($auth['user']['company_id'])) {
            return (int)$auth['user']['company_id'];
        }

        if (isset($input['company_id'])) {
            return (int)$input['company_id'];
        }

        $company_id = (int)$this->input->get('company_id');
        return $company_id > 0 ? $company_id : 0;
    }

    private function parse_input()
    {
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);

        if (empty($input) || !is_array($input)) {
            $input = array_merge($_GET, $_POST);
        }

        return is_array($input) ? $input : array();
    }

    private function uuid_v4()
    {
        $data = function_exists('random_bytes') ? random_bytes(16) : openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    private function parse_date_ymd($value)
    {
        if (!$value) {
            return null;
        }

        $d = DateTime::createFromFormat('Y-m-d', $value);
        if ($d && $d->format('Y-m-d') === $value) {
            return $d;
        }

        return null;
    }

    private function split_month_lock_ranges(DateTime $start_obj, DateTime $end_obj)
    {
        $ranges = array();
        $current = clone $start_obj;

        while ($current <= $end_obj) {
            $chunk_start = clone $current;
            $chunk_end = clone $current;
            $chunk_end->modify('last day of this month');

            if ($chunk_end > $end_obj) {
                $chunk_end = clone $end_obj;
            }

            $ranges[] = array(
                'lock_year' => (int)$chunk_start->format('Y'),
                'lock_month' => (int)$chunk_start->format('m'),
                'start_date' => $chunk_start->format('Y-m-d'),
                'end_date' => $chunk_end->format('Y-m-d')
            );

            $current = clone $chunk_start;
            $current->modify('first day of next month');
        }

        return $ranges;
    }

    public function create()
    {
        $auth = $this->authenticate_or_user();
        if (!$auth['ok']) {
            $this->response_json(array('status' => 'error', 'message' => 'Unauthorized'), 401);
        }

        $input = $this->parse_input();

        $company_id = $this->resolve_company_id($input, $auth);
        if ($company_id <= 0) {
            $this->response_json(array('status' => 'error', 'message' => 'Missing or invalid company_id'), 400);
        }

        $branch_id = isset($input['branch_id']) ? (int)$input['branch_id'] : 0;
        if ($branch_id <= 0) {
            $branch_id = null;
        }

        $custom_start = isset($input['start_date']) ? trim((string)$input['start_date']) : '';
        $custom_end = isset($input['end_date']) ? trim((string)$input['end_date']) : '';

        if ($custom_start !== '' || $custom_end !== '') {
            $start_obj = $this->parse_date_ymd($custom_start);
            $end_obj = $this->parse_date_ymd($custom_end);
            if (!$start_obj || !$end_obj) {
                $this->response_json(array('status' => 'error', 'message' => 'Invalid start_date/end_date format. Use YYYY-MM-DD'), 400);
            }
            if ($start_obj > $end_obj) {
                $this->response_json(array('status' => 'error', 'message' => 'start_date cannot be after end_date'), 400);
            }

            $start_date = $start_obj->format('Y-m-d');
            $end_date = $end_obj->format('Y-m-d');
            $lock_year = (int)$start_obj->format('Y');
            $lock_month = (int)$start_obj->format('m');
        } else {
            $lock_year = isset($input['lock_year']) ? (int)$input['lock_year'] : 0;
            $lock_month = isset($input['lock_month']) ? (int)$input['lock_month'] : 0;
            if ($lock_year < 2000 || $lock_month < 1 || $lock_month > 12) {
                $this->response_json(array('status' => 'error', 'message' => 'Invalid lock_year or lock_month'), 400);
            }

            $start_date = sprintf('%04d-%02d-01', $lock_year, $lock_month);
            $end_date = date('Y-m-t', strtotime($start_date));
        }

        $lock_ranges = $this->split_month_lock_ranges($start_obj, $end_obj);

        foreach ($lock_ranges as $range) {
            $existing = $this->month_lock->get_existing_lock($company_id, $branch_id, $range['lock_year'], $range['lock_month']);
            if ($existing) {
                $this->response_json(array(
                    'status' => 'exists',
                    'message' => 'Month lock already exists for one of the selected months',
                    'lock' => $existing,
                    'conflict_range' => $range
                ), 409);
            }

            $overlap = $this->month_lock->find_overlapping_lock($company_id, $branch_id, $range['start_date'], $range['end_date']);
            if ($overlap) {
                $this->response_json(array(
                    'status' => 'overlap',
                    'message' => 'Selected date range overlaps an existing lock',
                    'overlap_lock' => $overlap,
                    'conflict_range' => $range
                ), 409);
            }
        }

        $locked_by = null;
        if ($auth['auth_type'] === 'session' && !empty($auth['user']['id'])) {
            $locked_by = (int)$auth['user']['id'];
        }
        $priority = isset($input['priority']) ? (int)$input['priority'] : 3;
        $priority = max(1, min(10, $priority));

        $created_locks = array();
        $this->db->trans_begin();

        try {
            foreach ($lock_ranges as $range) {
                $lock_id = $this->month_lock->create_lock(array(
                    'company_id' => $company_id,
                    'branch_id' => $branch_id,
                    'lock_year' => $range['lock_year'],
                    'lock_month' => $range['lock_month'],
                    'start_date' => $range['start_date'],
                    'end_date' => $range['end_date'],
                    'locked_by' => $locked_by
                ));

                if (!$lock_id) {
                    throw new Exception('Failed to create month lock record');
                }

                $job_id = $this->uuid_v4();
                $payload = array(
                    'lock_id' => (int)$lock_id,
                    'company_id' => (int)$company_id,
                    'branch_id' => $branch_id,
                    'from_date' => $range['start_date'],
                    'to_date' => $range['end_date'],
                    'lock_year' => $range['lock_year'],
                    'lock_month' => $range['lock_month']
                );

                if (!$this->queue->create_job($job_id, 'month_lock_generate', $payload, $priority, (int)$company_id)) {
                    throw new Exception('Failed to queue month lock job');
                }

                $created_locks[] = array(
                    'lock_id' => (int)$lock_id,
                    'job_id' => $job_id,
                    'period' => array(
                        'start_date' => $range['start_date'],
                        'end_date' => $range['end_date']
                    )
                );
            }

            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Failed to persist month lock jobs');
            }

            $this->db->trans_commit();
        } catch (Exception $e) {
            $this->db->trans_rollback();
            $this->response_json(array('status' => 'error', 'message' => $e->getMessage()), 500);
        }

        $first_created = isset($created_locks[0]) ? $created_locks[0] : array();
        $message = count($created_locks) > 1
            ? 'Month locks queued successfully and split by month automatically'
            : 'Month lock job queued successfully';

        $this->response_json(array(
            'status' => 'queued',
            'message' => $message,
            'lock_id' => isset($first_created['lock_id']) ? $first_created['lock_id'] : null,
            'job_id' => isset($first_created['job_id']) ? $first_created['job_id'] : null,
            'locks' => $created_locks,
            'period' => array('start_date' => $start_date, 'end_date' => $end_date)
        ), 202);
    }

    public function status($lock_id = null)
    {
        $auth = $this->authenticate_or_user();
        if (!$auth['ok']) {
            $this->response_json(array('status' => 'error', 'message' => 'Unauthorized'), 401);
        }

        if (!$lock_id) {
            $this->response_json(array('status' => 'error', 'message' => 'Missing lock_id'), 400);
        }

        $lock = $this->month_lock->get_by_id((int)$lock_id);
        if (!$lock) {
            $this->response_json(array('status' => 'error', 'message' => 'Lock not found'), 404);
        }

        $this->response_json(array(
            'status' => 'success',
            'lock' => $lock
        ));
    }

    public function list_locks()
    {
        $auth = $this->authenticate_or_user();
        if (!$auth['ok']) {
            $this->response_json(array('status' => 'error', 'message' => 'Unauthorized'), 401);
        }

        $input = $this->parse_input();
        $company_id = $this->resolve_company_id($input, $auth);
        if ($company_id <= 0) {
            $this->response_json(array('status' => 'error', 'message' => 'Missing or invalid company_id'), 400);
        }

        $limit = isset($input['limit']) ? (int)$input['limit'] : 50;
        $locks = $this->month_lock->list_locks($company_id, $limit);

        $this->response_json(array(
            'status' => 'success',
            'count' => count($locks),
            'data' => $locks
        ));
    }

    public function retry($lock_id = null)
    {
        $auth = $this->authenticate_or_user();
        if (!$auth['ok']) {
            $this->response_json(array('status' => 'error', 'message' => 'Unauthorized'), 401);
        }

        if (!$lock_id) {
            $this->response_json(array('status' => 'error', 'message' => 'Missing lock_id'), 400);
        }

        $lock = $this->month_lock->get_by_id((int)$lock_id);
        if (!$lock) {
            $this->response_json(array('status' => 'error', 'message' => 'Lock not found'), 404);
        }

        if (!in_array($lock->status, array('failed', 'completed'), true)) {
            $this->response_json(array('status' => 'error', 'message' => 'Only failed or completed locks can be retried'), 409);
        }

        $this->month_lock->clear_lock_data($lock->id);
        $this->month_lock->reset_for_retry($lock->id);

        $job_id = $this->uuid_v4();
        $payload = array(
            'lock_id' => (int)$lock->id,
            'company_id' => (int)$lock->company_id,
            'branch_id' => $lock->branch_id !== null ? (int)$lock->branch_id : null,
            'from_date' => $lock->start_date,
            'to_date' => $lock->end_date,
            'lock_year' => (int)$lock->lock_year,
            'lock_month' => (int)$lock->lock_month
        );

        $this->queue->create_job($job_id, 'month_lock_generate', $payload, 2, (int)$lock->company_id);

        $this->response_json(array(
            'status' => 'queued',
            'message' => 'Month lock retry queued successfully',
            'lock_id' => (int)$lock->id,
            'job_id' => $job_id
        ), 202);
    }

    public function unlock($lock_id = null)
    {
        $auth = $this->authenticate_or_user();
        if (!$auth['ok']) {
            $this->response_json(array('status' => 'error', 'message' => 'Unauthorized'), 401);
        }

        if (!$lock_id) {
            $this->response_json(array('status' => 'error', 'message' => 'Missing lock_id'), 400);
        }

        $lock = $this->month_lock->get_by_id((int)$lock_id);
        if (!$lock) {
            $this->response_json(array('status' => 'error', 'message' => 'Lock not found'), 404);
        }

        if (!in_array($lock->status, array('completed', 'failed', 'pending'), true)) {
            $this->response_json(array('status' => 'error', 'message' => 'Lock cannot be unlocked while processing'), 409);
        }

        $input = $this->parse_input();
        $reason = isset($input['reason']) ? trim((string)$input['reason']) : null;
        $actor_id = null;
        if ($auth['auth_type'] === 'session' && !empty($auth['user']['id'])) {
            $actor_id = (int)$auth['user']['id'];
        }

        $ok = $this->month_lock->unlock_lock((int)$lock_id, $actor_id, $reason);
        if (!$ok) {
            $this->response_json(array('status' => 'error', 'message' => 'Failed to unlock lock'), 500);
        }

        $this->response_json(array(
            'status' => 'success',
            'message' => 'Month lock has been unlocked and snapshot data cleared',
            'lock_id' => (int)$lock_id
        ));
    }

    public function delete_lock_data($lock_id = null)
    {
        $auth = $this->authenticate_or_user();
        if (!$auth['ok']) {
            $this->response_json(array('status' => 'error', 'message' => 'Unauthorized'), 401);
        }

        if (!$lock_id) {
            $this->response_json(array('status' => 'error', 'message' => 'Missing lock_id'), 400);
        }

        $lock = $this->month_lock->get_by_id((int)$lock_id);
        if (!$lock) {
            $this->response_json(array('status' => 'error', 'message' => 'Lock not found'), 404);
        }

        $input = $this->parse_input();
        $reason = isset($input['reason']) ? trim((string)$input['reason']) : 'Manual delete';
        $actor_id = null;
        if ($auth['auth_type'] === 'session' && !empty($auth['user']['id'])) {
            $actor_id = (int)$auth['user']['id'];
        }

        $ok = $this->month_lock->delete_lock_data((int)$lock_id, $actor_id, $reason);
        if (!$ok) {
            $this->response_json(array('status' => 'error', 'message' => 'Failed to delete lock data'), 500);
        }

        $this->response_json(array(
            'status' => 'success',
            'message' => 'Month lock data has been deleted',
            'lock_id' => (int)$lock_id
        ));
    }

    public function dashboard()
    {
        $auth = $this->authenticate_or_user();
        if (!$auth['ok']) {
            $this->response_json(array('status' => 'error', 'message' => 'Unauthorized'), 401);
        }

        $input = $this->parse_input();
        $company_id = $this->resolve_company_id($input, $auth);
        if ($company_id <= 0) {
            $this->response_json(array('status' => 'error', 'message' => 'Missing or invalid company_id'), 400);
        }

        $limit = isset($input['limit']) ? (int)$input['limit'] : 20;
        $limit = max(1, min(100, $limit));

        $locks = $this->month_lock->list_locks($company_id, $limit);
        $jobs = array();

        foreach ($locks as $lock) {
            $lock_id = (int)$lock->id;
            $row = $this->db
                ->select('id, job_id, status, progress, error, created_at, started_at, completed_at')
                ->from('job_queue')
                ->where('type', 'month_lock_generate')
                ->like('payload', '"lock_id":' . $lock_id)
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get()
                ->row();

            if ($row) {
                $decoded_progress = json_decode($row->progress, true);
                $jobs[] = array(
                    'lock_id' => $lock_id,
                    'job_id' => $row->job_id,
                    'status' => $row->status,
                    'progress' => is_array($decoded_progress) ? $decoded_progress : null,
                    'error' => $row->error,
                    'created_at' => $row->created_at,
                    'started_at' => $row->started_at,
                    'completed_at' => $row->completed_at
                );
            }
        }

        $active_jobs = array_values(array_filter($jobs, function ($j) {
            return in_array($j['status'], array('pending', 'processing'), true);
        }));

        $this->response_json(array(
            'status' => 'success',
            'summary' => array(
                'total_locks' => count($locks),
                'active_jobs' => count($active_jobs)
            ),
            'locks' => $locks,
            'jobs' => $jobs,
            'active_jobs_list' => $active_jobs
        ));
    }

    public function details($lock_id = null)
    {
        $auth = $this->authenticate_or_user();
        if (!$auth['ok']) {
            $this->response_json(array('status' => 'error', 'message' => 'Unauthorized'), 401);
        }

        if (!$lock_id) {
            $this->response_json(array('status' => 'error', 'message' => 'Missing lock_id'), 400);
        }

        $lock = $this->month_lock->get_by_id((int)$lock_id);
        if (!$lock) {
            $this->response_json(array('status' => 'error', 'message' => 'Lock not found'), 404);
        }

        $input = $this->parse_input();
        $tab = isset($input['tab']) ? trim((string)$input['tab']) : 'summary';
        $limit = isset($input['limit']) ? (int)$input['limit'] : 50;
        $page = isset($input['page']) ? (int)$input['page'] : 1;
        $search = isset($input['search']) ? trim((string)$input['search']) : '';
        $page = max(1, $page);
        $offset = ($page - 1) * max(1, $limit);

        if ($tab === 'details') {
            $date = isset($input['date']) ? trim((string)$input['date']) : null;
            $employee_id = isset($input['employee_id']) ? (int)$input['employee_id'] : null;
            if ($employee_id !== null && $employee_id <= 0) {
                $employee_id = null;
            }

            $rows = $this->month_lock->get_detail_rows((int)$lock_id, $limit, $offset, $date, $employee_id, $search);
            $total = $this->month_lock->count_detail_rows((int)$lock_id, $date, $employee_id, $search);

            $this->response_json(array(
                'status' => 'success',
                'lock' => $lock,
                'tab' => 'details',
                'pagination' => array(
                    'page' => $page,
                    'limit' => (int)$limit,
                    'total' => $total
                ),
                'filters' => array(
                    'date' => $date,
                    'employee_id' => $employee_id,
                    'search' => $search
                ),
                'data' => $rows
            ));
        }

        $rows = $this->month_lock->get_summary_rows((int)$lock_id, $limit, $offset, $search);
        $total = $this->month_lock->count_summary_rows((int)$lock_id, $search);

        $this->response_json(array(
            'status' => 'success',
            'lock' => $lock,
            'tab' => 'summary',
            'pagination' => array(
                'page' => $page,
                'limit' => (int)$limit,
                'total' => $total
            ),
            'filters' => array(
                'search' => $search
            ),
            'data' => $rows
        ));
    }
}
