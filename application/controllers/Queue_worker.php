<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Queue_worker — CLI-only controller that processes async payroll report jobs.
 *
 * Usage:
 *   php index.php queue_worker process          -- process jobs continuously (daemon mode)
 *   php index.php queue_worker process_once      -- process one job and exit
 *   php index.php queue_worker status <job_id>   -- check a job's status (debug helper)
 *   php index.php queue_worker stats             -- show queue statistics
 *   php index.php queue_worker reset_stuck       -- re-queue stuck processing jobs
 *   php index.php queue_worker purge [days]      -- purge old completed/failed jobs
 *
 * Recommended: run with supervisor for production daemon mode, or cron for periodic polling.
 *
 *   supervisor example:
 *     [program:invotime-queue]
 *     command=php /path/to/invotime/index.php queue_worker process
 *     autostart=true
 *     autorestart=true
 *     stdout_logfile=/var/log/invotime-queue.log
 *
 *   cron example (every minute):
 *     * * * * * cd /path/to/invotime && php index.php queue_worker process_once >> /var/log/invotime-queue.log 2>&1
 */
class Queue_worker extends CI_Controller
{
    /**
     * @var Payroll_api  Loaded instance of the report controller
     */
    private $payroll;
    private $month_lock_snapshot_service;
    private $lateness_report_handler;
    private $short_month_lock_report_handler;
    private $accounts_report_handler;
    private $over_time_summary_report_handler;
    private $weekly_ot_report_handler;
    private $full_summary_report_handler;
    private $async_report_registry;
    private $sql_report_handler;
    private $daily_time_card_report_handler;
    private $work_hours_summary_report_handler;
    private $cjc01_payroll_report_handler;
    private $mm01_report_handler;
    private $tsf01_csv_report_handler;
    private $mcb01_clocking_report_handler;
    private $gni01_payroll_process_report_handler;
    private $bmi_summary_report_handler;
    private $bmi_summary_short_report_handler;

    public function __construct()
    {
        parent::__construct();

        // CLI access only — block HTTP requests
        if (!$this->input->is_cli_request()) {
            show_error('This controller is CLI-only.', 403);
            exit;
        }

        $this->load->model('Queue_model', 'queue');
        $this->load->model('Month_lock_model', 'month_lock');
        require_once APPPATH . 'libraries/QueueWorker/Services/Month_lock_snapshot_service.php';
        require_once APPPATH . 'libraries/QueueWorker/Handlers/Lateness_report_handler.php';
        require_once APPPATH . 'libraries/QueueWorker/Handlers/Short_month_lock_report_handler.php';
        require_once APPPATH . 'libraries/QueueWorker/Handlers/Accounts_report_handler.php';
        require_once APPPATH . 'libraries/QueueWorker/Handlers/Over_time_summary_report_handler.php';
        require_once APPPATH . 'libraries/QueueWorker/Handlers/Weekly_ot_report_handler.php';
        require_once APPPATH . 'libraries/QueueWorker/Handlers/Full_summary_report_handler.php';
        require_once APPPATH . 'libraries/QueueWorker/Handlers/Sql_report_handler.php';
        require_once APPPATH . 'libraries/QueueWorker/Handlers/Daily_time_card_report_handler.php';
        require_once APPPATH . 'libraries/QueueWorker/Handlers/Work_hours_summary_report_handler.php';
        require_once APPPATH . 'libraries/QueueWorker/Handlers/Cjc01_payroll_report_handler.php';
        require_once APPPATH . 'libraries/QueueWorker/Handlers/Mm01_report_handler.php';
        require_once APPPATH . 'libraries/QueueWorker/Handlers/Tsf01_csv_report_handler.php';
        require_once APPPATH . 'libraries/QueueWorker/Handlers/Mcb01_clocking_report_handler.php';
        require_once APPPATH . 'libraries/QueueWorker/Handlers/Gni01_payroll_process_report_handler.php';
        require_once APPPATH . 'libraries/QueueWorker/Handlers/Bmi_summary_report_handler.php';
        require_once APPPATH . 'libraries/QueueWorker/Handlers/Bmi_summary_short_report_handler.php';
        require_once APPPATH . 'libraries/QueueWorker/Support/Async_report_registry.php';
        $this->month_lock_snapshot_service = new Month_lock_snapshot_service(array(
            'db' => $this->db,
            'month_lock' => $this->month_lock
        ));
        $this->lateness_report_handler = new Lateness_report_handler(array(
            'load' => $this->load
        ));
        $this->short_month_lock_report_handler = new Short_month_lock_report_handler();
        $this->accounts_report_handler = new Accounts_report_handler(array(
            'load' => $this->load
        ));
        $this->over_time_summary_report_handler = new Over_time_summary_report_handler(array(
            'load' => $this->load
        ));
        $this->weekly_ot_report_handler = new Weekly_ot_report_handler(array(
            'load' => $this->load
        ));
        $this->full_summary_report_handler = new Full_summary_report_handler(array(
            'load' => $this->load,
            'session' => $this->session
        ));
        $this->sql_report_handler = new Sql_report_handler();
        $this->daily_time_card_report_handler = new Daily_time_card_report_handler();
        $this->work_hours_summary_report_handler = new Work_hours_summary_report_handler();
        $this->cjc01_payroll_report_handler = new Cjc01_payroll_report_handler();
        $this->mm01_report_handler = new Mm01_report_handler();
        $this->tsf01_csv_report_handler = new Tsf01_csv_report_handler();
        $this->mcb01_clocking_report_handler = new Mcb01_clocking_report_handler();
        $this->gni01_payroll_process_report_handler = new Gni01_payroll_process_report_handler();
        $this->bmi_summary_report_handler = new Bmi_summary_report_handler();
        $this->bmi_summary_short_report_handler = new Bmi_summary_short_report_handler();
        $this->async_report_registry = new Async_report_registry();
        // Don't auto-load Payroll_api - only load when needed for specific job types
    }

    /**
     * Load the Payroll_api controller so we can re-use its calculation
     * and report-building methods without duplicating 2700+ lines of code.
     */
    private function _load_payroll_api()
    {
        require_once APPPATH . 'controllers/Payroll_api.php';

        // In CLI worker context, constructing Payroll_api directly can fail
        // due web-only library autoload side-effects (e.g. Session loader).
        // We only need its calculation/report methods, so instantiate without constructor.
        $reflection = new ReflectionClass('Payroll_api');
        $this->payroll = $reflection->newInstanceWithoutConstructor();

        // Bind core services that Payroll_api methods use.
        $this->payroll->db = $this->db;
        $this->payroll->load = $this->load;
        $this->payroll->input = $this->input;
        $this->payroll->output = $this->output;

        require_once APPPATH . 'helpers/payroll_bulk_helper.php';
    }

    // ==================================================================
    //  PUBLIC COMMANDS
    // ==================================================================

    /**
     * Daemon mode — loop forever, polling for pending jobs.
     * Use CTRL+C or kill the process to stop.
     *
     * @param  int $sleep  Seconds to sleep when queue is empty (default 5)
     */
    public function process($sleep = 5)
    {
        $sleep = max(1, (int) $sleep);

        $this->_log('Queue worker started (daemon mode, sleep=' . $sleep . 's)');

        while (true) {
            $processed = $this->_process_one();

            if (!$processed) {
                // No jobs — wait before polling again
                sleep($sleep);
            }
            // If we did process a job, immediately check for more (no sleep)
        }
    }

    /**
     * Process exactly one pending job and exit.
     * Ideal for cron-based execution.
     */
    public function process_once()
    {
        $processed = $this->_process_one();

        if ($processed) {
            $this->_log('Job processed successfully.');
        } else {
            $this->_log('No pending jobs.');
        }
    }

    /**
     * Debug helper — check the status of a specific job.
     *
     * @param  string $job_id  UUID of the job
     */
    public function status($job_id = null)
    {
        if (!$job_id) {
            $this->_log('Usage: php index.php queue_worker status <job_id>');
            return;
        }

        $job = $this->queue->get_by_job_id($job_id);
        if (!$job) {
            $this->_log('Job not found: ' . $job_id);
            return;
        }

        $this->_log('--- Job Details ---');
        $this->_log('  ID:         ' . $job->id);
        $this->_log('  Job ID:     ' . $job->job_id);
        $this->_log('  Type:       ' . $job->type);
        $this->_log('  Status:     ' . $job->status);
        $this->_log('  Attempts:   ' . $job->attempts . '/' . $job->max_attempts);
        $this->_log('  Created:    ' . $job->created_at);
        $this->_log('  Started:    ' . ($job->started_at ?: '-'));
        $this->_log('  Completed:  ' . ($job->completed_at ?: '-'));

        if ($job->error) {
            $this->_log('  Error:      ' . $job->error);
        }
        if ($job->status === 'completed' && $job->result) {
            $result = json_decode($job->result, true);
            $this->_log('  Data count: ' . (isset($result['count']) ? $result['count'] : 'N/A'));
        }
    }

    /**
     * Show queue statistics.
     */
    public function stats()
    {
        $stats = $this->queue->get_stats();
        $this->_log('--- Queue Stats ---');
        foreach ($stats as $status => $count) {
            $this->_log('  ' . str_pad($status, 12) . ': ' . $count);
        }
        $this->_log('  ' . str_pad('TOTAL', 12) . ': ' . array_sum($stats));
    }

    /**
     * Reset stuck jobs (processing for more than N minutes).
     *
     * @param  int $minutes  Threshold in minutes (default 30)
     */
    public function reset_stuck($minutes = 30)
    {
        $minutes = max(1, (int) $minutes);
        $reset = $this->queue->reset_stuck_jobs($minutes);
        $this->_log('Reset ' . $reset . ' stuck job(s) older than ' . $minutes . ' minutes.');
    }

    /**
     * Purge completed/failed jobs older than N days.
     *
     * @param  int $days  (default 7)
     */
    public function purge($days = 7)
    {
        $days = max(1, (int) $days);
        $deleted = $this->queue->purge_old_jobs($days);
        $this->_log('Purged ' . $deleted . ' old job(s) older than ' . $days . ' day(s).');
    }

    /**
     * One-time maintenance command.
     * Recompute month_lock_details.shift_hours from shift definitions so legacy locks
     * match the same break-adjusted shift-hours logic used in summary calculations.
     *
     * Usage:
     *   php index.php queue_worker backfill_month_lock_shift_hours
     *   php index.php queue_worker backfill_month_lock_shift_hours <lock_id>
     *   php index.php queue_worker backfill_month_lock_shift_hours 0 <company_id>
     *   php index.php queue_worker backfill_month_lock_shift_hours <lock_id> <company_id>
     */
    public function backfill_month_lock_shift_hours($lock_id = null, $company_id = null)
    {
        $lock_id = ($lock_id === null || $lock_id === '') ? null : (int)$lock_id;
        $company_id = ($company_id === null || $company_id === '') ? null : (int)$company_id;

        $this->_log('Starting backfill for month_lock_details.shift_hours...');

        $this->db->from('month_lock_details d');
        $this->db->join('month_locks ml', 'ml.id = d.lock_id', 'inner');
        $this->db->where('ml.status', 'completed');
        if ($lock_id !== null && $lock_id > 0) {
            $this->db->where('d.lock_id', $lock_id);
        }
        if ($company_id !== null && $company_id > 0) {
            $this->db->where('ml.company_id', $company_id);
        }
        $candidate_rows = (int)$this->db->count_all_results();

        if ($candidate_rows <= 0) {
            $this->_log('No eligible rows found for backfill.');
            return;
        }

        $shift_hours_expr = $this->_get_shift_hours_backfill_expression();
        $where_sql = "ml.status = 'completed'";
        if ($lock_id !== null && $lock_id > 0) {
            $where_sql .= ' AND d.lock_id = ' . (int)$lock_id;
        }
        if ($company_id !== null && $company_id > 0) {
            $where_sql .= ' AND ml.company_id = ' . (int)$company_id;
        }

        $sql = "
            UPDATE month_lock_details d
            INNER JOIN month_locks ml ON ml.id = d.lock_id
            SET
                d.shift_hours = COALESCE(({$shift_hours_expr}), d.shift_hours),
                d.updated_at = NOW()
            WHERE {$where_sql}
        ";

        $started_at = microtime(true);
        $this->db->query($sql);
        $affected = (int)$this->db->affected_rows();
        $elapsed = round(microtime(true) - $started_at, 2);

        $this->_log('Backfill complete. Candidate rows: ' . $candidate_rows . ', affected rows: ' . $affected . ', elapsed: ' . $elapsed . 's');
    }

    // ==================================================================
    //  PRIVATE — Core processing logic
    // ==================================================================

    /**
     * Attempt to claim and process one job.
     *
     * @return bool  True if a job was processed, false if queue was empty.
     */
    private function _process_one()
    {
        $job = $this->queue->claim_next();

        if (!$job) {
            return false;
        }

        $this->_log('Processing job ' . $job->job_id . ' (type: ' . $job->type . ', attempt: ' . $job->attempts . ')');

        $start_time = microtime(true);
        $payload = null;

        try {
            $payload = json_decode($job->payload, true);
            if (!$payload || !is_array($payload)) {
                throw new Exception('Invalid job payload (not valid JSON)');
            }

            // Build the full response (same structure as sync Payroll_api)
            $response = $this->_execute_report($job->type, $payload, $job);

            // Store result
            $this->queue->mark_completed($job->id, $response);

            $elapsed = round(microtime(true) - $start_time, 2);
            $count_display = isset($response['count']) ? $response['count'] : (isset($response['summary']['employee_count']) ? $response['summary']['employee_count'] : 'N/A');
            $this->_log('  Completed in ' . $elapsed . 's (rows: ' . $count_display . ')');
        } catch (Throwable $e) {
            $this->queue->mark_failed($job->id, $e->getMessage());

            if ($job->type === 'month_lock_generate' && is_array($payload) && !empty($payload['lock_id'])) {
                $this->month_lock->mark_failed((int)$payload['lock_id'], $e->getMessage());
            }

            $elapsed = round(microtime(true) - $start_time, 2);
            $this->_log('  ✗ FAILED after ' . $elapsed . 's');
            $this->_log('  Error: ' . $e->getMessage());
            $this->_log('  File: ' . $e->getFile() . ' (Line ' . $e->getLine() . ')');

            // Log first few lines of stack trace for debugging
            $trace = explode("\n", $e->getTraceAsString());
            if (isset($trace[0])) {
                $this->_log('  Trace: ' . substr($trace[0], 0, 100) . '...');
            }
        }

        return true;
    }

    /**
     * Execute the report generation for a given type and payload.
     * This mirrors the switch/case logic in Payroll_api::summary() exactly.
     *
     * @param  string $type     Report type (e.g. 'pending_overtime')
     * @param  array  $payload  Input parameters (company_id, from_date, to_date, filters...)
     * @param  object $job      Job record (for progress tracking)
     * @return array  The full response array with status, data, count, etc.
     * @throws Exception
     */
    private function _execute_report($type, $payload, $job = null)
    {
        if ($type === 'month_lock_generate') {
            return $this->_generate_month_lock($payload, $job);
        }

        if (!$this->payroll) {
            $this->_load_payroll_api();
        }

        // --- Recreate the same variables that summary() builds ---
        $cid = $payload['company_id'];

        $date1 = DateTime::createFromFormat('d/m/Y', $payload['from_date']);
        $date2 = DateTime::createFromFormat('d/m/Y', $payload['to_date']);
        if (!$date1 || !$date2) {
            $date1 = DateTime::createFromFormat('Y-m-d', $payload['from_date']);
            $date2 = DateTime::createFromFormat('Y-m-d', $payload['to_date']);
        }
        if (!$date1 || !$date2) {
            throw new Exception('Invalid date format in payload');
        }

        $first_day = $date1->format('Y-m-d');
        $last_day  = $date2->format('Y-m-d');

        $branch_id    = isset($payload['branch'])           ? $payload['branch']           : array();
        $department_id = isset($payload['department'])       ? $payload['department']       : array();
        $position_id  = isset($payload['position'])         ? $payload['position']         : array();
        $section_id   = isset($payload['section'])          ? $payload['section']          : null;
        $employee_id  = isset($payload['employee'])         ? $payload['employee']         : array();
        $exclude_employees = isset($payload['exclude_employee']) ? $payload['exclude_employee'] : array();

        // Branch name
        $branch_name = 'All';
        if ($branch_id) {
            $row = $this->db->select('group_concat(name) as name')
                ->from('branches')
                ->where_in('id', $branch_id)
                ->get()->row();
            if ($row) {
                $branch_name = $row->name;
            }
        }

        // Calc params (same structure as Payroll_api::summary)
        $calcParams = array(
            'input'             => $payload,
            'cid'               => $cid,
            'first_day'         => $first_day,
            'last_day'          => $last_day,
            'branch_id'         => $branch_id,
            'branch_name'       => $branch_name,
            'department_id'     => $department_id,
            'position_id'       => $position_id,
            'section_id'        => $section_id,
            'employee_id'       => $employee_id,
            'exclude_employees' => $exclude_employees
        );

        $first_day_formatted = $date1->format('d M, Y');
        $last_day_formatted  = $date2->format('d M, Y');

        // --- Company CID guards (same as Payroll_api::summary) ---
        $final_data = null;

        // Map export job types to handler methods so adding new export types stays localized.
        $export_handler_methods = $this->async_report_registry->get_export_handler_methods();

        if (isset($export_handler_methods[$type])) {
            $handler_method = $export_handler_methods[$type];
            return $this->{$handler_method}($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job);
        }

        throw new Exception('Invalid report type: ' . $type);
    }

    /**
     * Return a "not available" response (same as Payroll_api) for CID-restricted reports.
     */
    /**
     * Generate short summary report Excel/PDF file.
     * This is copied from Exports.php "short" report logic and optimized with PayrollBulkHelper.
     *
     * @return array Response with file_path, file_name, summary
     */
    /**
     * FAILURE RECOVERY SYSTEM EXPLANATION:
     * ====================================
     * When this job fails mid-processing (e.g., at employee 160/254), all progress is saved:
     * 1. Processed employee IDs are saved in job_queue.progress JSON as 'processed_employee_ids': [123, 456, 789...]
     * 2. Resume info like which employee failed and why is also saved
     * 3. On retry, the code loads this 'processed_employee_ids' array at line ~755
     * 4. As it loops through all employees, it SKIPS any that are already in this array (continue)
     * 5. So instead of reprocessing employees 1-160 again, it starts from employee 161
     * Result: If it takes 8 minutes to process 254 employees and fails at 160, the retry only processes 94
     *
     * The recovery tracking happens:
     * - Every 10 employees during step 5 (line ~770)
     * - When an error occurs (line ~805)
     * - On completion (line ~843, 970)
     *
     * Frontend shows recovery info in yellow alert box when progress.resume_count > 0
     */
    private function _generate_short_report($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job = null)
    {
        $data_source = $this->_resolve_short_report_source($payload);
        if ($data_source === 'month_lock') {
            return $this->_generate_short_report_from_month_lock($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job);
        }

        require_once APPPATH . 'helpers/payroll_bulk_helper.php';
        require_once APPPATH . 'helpers/PayrollQueryHelper.php';

        // Extract filter params
        $branch_id         = isset($payload['branch'])           ? $payload['branch']           : array();
        $department_id     = isset($payload['department'])       ? $payload['department']       : array();
        $position_id       = isset($payload['position'])         ? $payload['position']         : array();
        $section_id        = isset($payload['section'])          ? $payload['section']          : null;
        $employee_id       = isset($payload['employee'])         ? $payload['employee']         : array();
        $exclude_employees = isset($payload['exclude_employee']) ? $payload['exclude_employee'] : array();
        $file_type         = isset($payload['file_type'])        ? $payload['file_type']        : 'xlsx';

        // Fetch settings (same as Exports.php)
        $company_working_hours      = PayrollQueryHelper::get_company_working_hours($cid);
        $company_ot_settings        = PayrollQueryHelper::get_company_ot_settings($cid);
        $company_early_ot_settings  = PayrollQueryHelper::get_company_early_ot_settings($cid);

        // Employee group expansion logic (same as Exports.php)
        $employees_from_group = array();
        $excluded_employees_from_group = array();

        if ($employee_id) {
            $employee_group_arr = array();
            foreach ($employee_id as $key) {
                if (strpos($key, '-') !== false) {
                    $arr = explode("-", $key);
                    array_push($employee_group_arr, $arr[0]);
                } else {
                    $employees_from_group[] = $key;
                }
            }
            foreach ($employee_group_arr as $group_id) {
                $results = $this->db->where_in('group_id', array($group_id))->get('employee_groups_relation')->result();
                foreach ($results as $result) {
                    $employees_from_group[] = $result->employee_id;
                }
            }
            $employees_from_group = array_unique($employees_from_group);
        }

        if ($exclude_employees) {
            $employee_group_arr = array();
            foreach ($exclude_employees as $key) {
                if (strpos($key, '-') !== false) {
                    $arr = explode("-", $key);
                    array_push($employee_group_arr, $arr[0]);
                } else {
                    $excluded_employees_from_group[] = $key;
                }
            }
            foreach ($employee_group_arr as $group_id) {
                $results = $this->db->where_in('group_id', array($group_id))->get('employee_groups_relation')->result();
                foreach ($results as $result) {
                    $excluded_employees_from_group[] = $result->employee_id;
                }
            }
            $excluded_employees_from_group = array_unique($excluded_employees_from_group);
        }

        // Fetch employees (same query as Exports.php with COALESCE for potentially missing columns)
        $this->db->select('
            employees.id,
            employees.first_name,
            special_id,
            employees.is_daily_waged,
            d.name as department,
            s.title as section,
            p.title as position,
            employees.branch_id,
            b.name as branch,
            COALESCE(is_ot, 0) as is_ot,
            COALESCE(is_early_ot, 0) as is_early_ot,
            COALESCE(inc_late_in, 0) as inc_late_in,
            COALESCE(inc_late_break, 0) as inc_late_break,
            COALESCE(inc_early_out, 0) as inc_early_out,
            COALESCE(inc_short_hours, 0) as inc_short_hours,
            COALESCE(ot_type, "") as ot_type,
            COALESCE(ot_round, 0) as ot_round,
            COALESCE(early_ot_round, 0) as early_ot_round,
            COALESCE(use_half_hours_for_saturdays, 0) as use_half_hours_for_saturdays,
            COALESCE(round_first_hour_only, 0) as round_first_hour_only,
            COALESCE(round_by_exact_hour, 0) as round_by_exact_hour,
            COALESCE(different_first_hour_rounding, 0) as different_first_hour_rounding,
            COALESCE(worked_hours_ot_rd, 0) as worked_hours_ot_rd,
            COALESCE(worked_hours_ot_ph, 0) as worked_hours_ot_ph,
            COALESCE(deduct_hour_ot_rd, 0) as deduct_hour_ot_rd,
            COALESCE(deduct_hour_ot_ph, 0) as deduct_hour_ot_ph,
            COALESCE(worked_hours_ot_off, 0) as worked_hours_ot_off,
            COALESCE(deduct_hour_ot_off, 0) as deduct_hour_ot_off,
            COALESCE(ignore_breaks_after_endtime, 0) as ignore_breaks_after_endtime,
            COALESCE(void_lateness_time_if_less_than, 0) as void_lateness_time_if_less_than,
            COALESCE(deduct_from_ot, 0) as deduct_from_ot,
            COALESCE(deduct_from_ot_single, "not_sure") as deduct_from_ot_single,
            COALESCE(deduction_date, NOW()) as deduction_date,
            COALESCE(min_worked_hours_meal, 0) as min_worked_hours_meal,
            COALESCE(ta_rate, 0) as ta_rate,
            COALESCE(ma_rate, 0) as ma_rate,
            COALESCE(ca_rate, 0) as ca_rate,
            COALESCE(spa_rate, 0) as spa_rate,
            COALESCE(aca_rate, 0) as aca_rate,
            COALESCE(aa_rate, 0) as aa_rate,
            COALESCE(nsa_rate, 0) as nsa_rate,
            COALESCE(dsa_rate, 0) as dsa_rate,
            COALESCE(fl_rate, 0) as fl_rate,
            COALESCE(cw_rate, 0) as cw_rate,
            COALESCE(mo_rate, 0) as mo_rate,
            COALESCE(shift1_rate, 0) as shift1_rate,
            COALESCE(shift2_rate, 0) as shift2_rate,
            COALESCE(shift3_rate, 0) as shift3_rate,
            COALESCE(food_rate, 0) as food_rate,
            COALESCE(basic_wage, 0) as basic_wage,
            COALESCE(ot_group, "") as ot_group,
            COALESCE(special_incentive, 0) as special_incentive,
            COALESCE(att_all_code, "") as att_all_code,
            COALESCE(att_all_desc, "") as att_all_desc,
            COALESCE(att_all_amount, 0) as att_all_amount,
            COALESCE(is_att_all, 0) as is_att_all,
            COALESCE(mi_mo_rate, 0) as mi_mo_rate,
            COALESCE(lateness_deduction_99, 0) as lateness_deduction_99,
            COALESCE(lateness_deduction_100, 0) as lateness_deduction_100,
            COALESCE(rest_day_entitlement, 0) as rest_day_entitlement,
            COALESCE(is_shift_hours, 0) as is_shift_hours
        ', FALSE)
            ->from('employees')
            ->join('roles', 'employees.role_id = roles.id', 'left')
            ->join('departments d', 'd.id = employees.department_id', 'left')
            ->join('branches b', 'b.id = employees.branch_id', 'left')
            ->join('sections s', 'employees.section_id = s.id', 'left')
            ->join('positions p', 'p.id = employees.position_id', 'left')
            ->where('employees.company_id', $cid)
            ->where('employees.deleted_at is null')
            ->where('roles.exclude_from_system', 'no')
            ->where("(employees.employee_status = 'active'
            OR (employees.employee_status = 'terminated' AND employees.termination_date >= DATE_FORMAT('$first_day', '%Y-%m-01'))
            OR (employees.employee_status = 'resigned' AND employees.resignation_date >= DATE_FORMAT('$first_day', '%Y-%m-01'))
        )");

        if ($branch_id) $this->db->where_in('employees.branch_id', $branch_id);
        if ($department_id) $this->db->where_in('employees.department_id', $department_id);
        if ($position_id) $this->db->where_in('employees.position_id', $position_id);
        if ($section_id) $this->db->where_in('employees.section_id', $section_id);
        if ($employees_from_group) $this->db->where_in('employees.id', $employees_from_group);
        if ($excluded_employees_from_group) $this->db->where_not_in('employees.id', $excluded_employees_from_group);

        $this->db->group_by('employees.id');
        $this->db->order_by('special_id', 'asc');

        $employees = $this->db->get()->result();

        if (empty($employees)) {
            throw new Exception('No employees found for the selected criteria');
        }

        $employees_ids = array_column($employees, 'id');

        // Fetch shifts (with validation)
        $this->_log('  Fetching shifts for company ' . $cid . '...');
        $shifts = $this->db->select('id')->from('shifts')->where('company_id', $cid)->where('is_leave', 'no')->get()->result();

        if (!$shifts || count($shifts) == 0) {
            $this->_log('  [WARNING] No shifts found for company ' . $cid);
        } else {
            $this->_log('  [OK] Found ' . count($shifts) . ' shifts');
        }

        $shift_ids = array(0);
        foreach ($shifts as $s) {
            $shift_ids[] = $s->id;
        }

        $this->_log('  [STEP 1/6] Fetching OT approval list...');
        try {
            $approved_ot_list = PayrollQueryHelper::get_approved_ot_list($shift_ids, $first_day, $last_day);
            $this->_log('  [OK] Approved OT list: ' . count($approved_ot_list) . ' records');

            // Update progress in database
            if ($job) {
                $this->queue->update_progress($job->id, array(
                    'step' => 1,
                    'total_steps' => 6,
                    'title' => 'Fetching OT approval list',
                    'processed' => 0,
                    'total' => count($employees)
                ));
            }
        } catch (Exception $e) {
            throw new Exception('Failed to fetch approved OT list: ' . $e->getMessage());
        }

        $this->_log('  [STEP 2/6] Fetching branch rest/off days...');
        try {
            $branch_rest_days = $this->db->select('id,rest_days,off_days')->from('branches')->where('company_id', $cid)->get()->result();
            $this->_log('  [OK] Branch settings: ' . count($branch_rest_days) . ' branches');

            // Update progress
            if ($job) {
                $this->queue->update_progress($job->id, array(
                    'step' => 2,
                    'total_steps' => 6,
                    'title' => 'Fetching branch settings',
                    'processed' => 0,
                    'total' => count($employees)
                ));
            }
        } catch (Exception $e) {
            throw new Exception('Failed to fetch branch settings: ' . $e->getMessage());
        }

        // Bulk fetch result lists (same optimization as Payroll_api)
        $this->_log('  [STEP 3/6] Fetching result lists (' . count($employees_ids) . ' employees in ' . count(array_chunk($employees_ids, 100)) . ' chunks)...');
        $result_list = array();
        $result_list_overnight = array();
        $chunkedEmployeeIds = array_chunk($employees_ids, 100);

        try {
            foreach ($chunkedEmployeeIds as $chunk_num => $chunk) {
                $result_list = array_merge($result_list, PayrollQueryHelper::get_result_list($chunk, $first_day, $last_day));
                $result_list_overnight = array_merge($result_list_overnight, PayrollQueryHelper::get_result_list_overnight($chunk, $first_day, $last_day, $cid));
            }
            $this->_log('  [OK] Result lists fetched: ' . count($result_list) . ' regular, ' . count($result_list_overnight) . ' overnight');

            // Update progress
            if ($job) {
                $this->queue->update_progress($job->id, array(
                    'step' => 3,
                    'total_steps' => 6,
                    'title' => 'Fetching result lists',
                    'processed' => 0,
                    'total' => count($employees)
                ));
            }
        } catch (Exception $e) {
            throw new Exception('Failed to fetch result lists: ' . $e->getMessage());
        }

        // === OPTIMIZED: Use PayrollBulkHelper to eliminate N+1 queries ===
        $this->_log('  [STEP 4/6] Initializing bulk query helper...');
        try {
            $branch_ids = array_unique(array_map(function ($e) {
                return $e->branch_id;
            }, $employees));
            $bulk = new PayrollBulkHelper();
            $bulk->prefetch($employees_ids, $first_day, $last_day, $cid, $branch_ids);
            $this->_log('  [OK] Bulk helper initialized for ' . count($branch_ids) . ' branches');

            // Update progress
            if ($job) {
                $this->queue->update_progress($job->id, array(
                    'step' => 4,
                    'total_steps' => 6,
                    'title' => 'Initializing bulk helper',
                    'processed' => 0,
                    'total' => count($employees)
                ));
            }
        } catch (Exception $e) {
            throw new Exception('Failed to initialize bulk helper: ' . $e->getMessage());
        }

        $all_data = array();

        // Empty arrays for unused parameters (must be variables for pass-by-reference in PHP 8+)
        $worked_rest_days_array = array();
        $worked_off_days_array = array();
        $worked_holidays_array = array();
        $unpaid_leaves_absent_days = array();
        $clockings_news = array();
        $clockings_news_overnight = array();
        $paid_leaves_array = array();
        $daily_ot_array = array();
        $daily_late_array = array();
        $days_settings = array();
        $ot_type_data_map = array();

        // Calculate summary data for each employee
        $this->_log('  [STEP 5/6] Calculating summary data for ' . count($employees) . ' employees...');

        // Check if resuming from previous failure - load already-processed employee IDs
        $processed_employee_ids = array();
        if ($job && $job->progress) {
            $progress_data = json_decode($job->progress, true);
            $processed_employee_ids = isset($progress_data['processed_employee_ids']) ? (array)$progress_data['processed_employee_ids'] : array();
            if (!empty($processed_employee_ids)) {
                $this->_log('  [RESUME] Resuming from previous attempt' . count($processed_employee_ids) . ' employees already calculated');
            }
        }

        $emp_count = 0;
        $resume_count = count($processed_employee_ids);
        $all_data = array();

        foreach ($employees as $emp) {
            // Skip if already processed in a previous attempt
            if (in_array((int)$emp->id, $processed_employee_ids)) {
                $emp_count++;
                continue;
            }

            try {
                $data = PayrollQueryHelper::calculate_summary_data(
                    $emp->id,
                    $first_day,
                    $last_day,
                    "short",  // type
                    $emp,
                    $result_list,
                    $result_list_overnight,
                    $company_working_hours,
                    false,  // public_holidays
                    $company_ot_settings,
                    $company_early_ot_settings,
                    $approved_ot_list,
                    $branch_rest_days,
                    $cid,
                    $worked_rest_days_array,
                    $worked_off_days_array,
                    $worked_holidays_array,
                    $unpaid_leaves_absent_days,
                    $clockings_news,
                    $clockings_news_overnight,
                    $paid_leaves_array,
                    $daily_ot_array,
                    $daily_late_array,
                    $days_settings,
                    $ot_type_data_map,
                    $bulk    // PayrollBulkHelper instance
                );
                $all_data[] = $data;

                // Track this employee as processed
                $processed_employee_ids[] = (int)$emp->id;

                $emp_count++;
                if ($emp_count % 10 == 0) {
                    $this->_log('    Processed ' . $emp_count . '/' . count($employees) . ' employees... (Resumable from: ' . count($processed_employee_ids) . ')');

                    // Update progress in database every 10 employees with recovery info
                    if ($job) {
                        $this->queue->update_progress($job->id, array(
                            'step' => 5,
                            'total_steps' => 6,
                            'title' => 'Calculating summary data',
                            'processed' => $emp_count,
                            'total' => count($employees),
                            'message' => 'Processed ' . $emp_count . '/' . count($employees) . ' employees',
                            'processed_employee_ids' => $processed_employee_ids, // SAVE PROGRESS FOR RECOVERY
                            'resume_count' => $resume_count
                        ));
                    }
                }
            } catch (Exception $e) {
                $this->_log('  [ERROR] Failed for employee ' . $emp->special_id . ' (ID: ' . $emp->id . '): ' . $e->getMessage());

                // Save partial progress for retry to resume from here
                if ($job) {
                    $this->queue->update_progress($job->id, array(
                        'step' => 5,
                        'total_steps' => 6,
                        'title' => 'Calculating summary data',
                        'processed' => $emp_count,
                        'total' => count($employees),
                        'message' => 'FAILED at employee ' . $emp->special_id . ' after processing ' . $emp_count . ' employees',
                        'processed_employee_ids' => $processed_employee_ids, // SAVE FOR RECOVERY
                        'failed_employee' => array(
                            'id' => $emp->id,
                            'special_id' => $emp->special_id,
                            'error' => $e->getMessage()
                        ),
                        'resume_count' => $resume_count
                    ));
                }

                throw new Exception('Failed to calculate summary for employee ' . $emp->special_id . ': ' . $e->getMessage());
            }
        }
        $this->_log('  [OK] Summary calculation complete for ' . count($all_data) . ' employees');

        // Update progress for step 5 completion - save final recovery info
        if ($job) {
            $this->queue->update_progress($job->id, array(
                'step' => 5,
                'total_steps' => 6,
                'title' => 'Summary calculation complete',
                'processed' => count($employees),
                'total' => count($employees),
                'message' => 'Calculated for ' . count($employees) . ' employees',
                'processed_employee_ids' => $processed_employee_ids,  // FINAL LIST FOR RECOVERY
                'resume_count' => $resume_count
            ));
        }

        // === GENERATE FILE (Excel or PDF) ===
        $this->_log('  [STEP 6/6] Generating ' . strtoupper($file_type) . ' file...');
        $file_name = "($branch_name) Short Summary - $first_day to $last_day " . time();

        try {
            if ($file_type === 'pdf') {
                // PDF generation using dompdf
                $this->load->library('dompdf_lib');

                $view_data = array(
                    'all_data' => $all_data,
                    'branch_name' => $branch_name,
                    'from_f' => $first_day_formatted,
                    'to_f' => $last_day_formatted
                );

                $html = $this->load->view('short_summary_pdf', $view_data, true);

                $this->dompdf_lib->reset();
                $this->dompdf_lib->loadHtml($html);
                $this->dompdf_lib->setPaper("A4", "landscape");
                $this->dompdf_lib->render();

                $output = $this->dompdf_lib->output();
                $file_name .= '.pdf';
                $file_path = FCPATH . 'uploads/summary/' . $file_name;
                file_put_contents($file_path, $output);
                $this->_log('  [OK] PDF file created: ' . $file_name);
            } else {
                // Excel generation using PHPExcel
                $this->load->library('excel');

                $style = array(
                    'alignment' => array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    )
                );

                $object = new PHPExcel();
                $object->setActiveSheetIndex(0);
                $object->getDefaultStyle()->applyFromArray($style);

                // Header rows
                $name_columns = array("Branch", "From", "To", "Generated at", "Generated by");
                $table_columns = array("Name", "Employee ID", "Working Days", "Worked Days", "Absent Days", "Leave Days", "Unpaid Leave Days", "Worked Rest Days", "Worked Holidays", "OT", "OT (PHx2)", "OT (PHx3)", "OT (RD)", "OT (OFF)", "Lateness Count", "Lateness Time", "Trips A", "Trips B");

                $column = 0;
                foreach ($name_columns as $field) {
                    $object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
                    $object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);
                    $column++;
                }

                $object->getActiveSheet()->setCellValueByColumnAndRow(0, 2, $branch_name);
                $object->getActiveSheet()->setCellValueByColumnAndRow(1, 2, $first_day_formatted);
                $object->getActiveSheet()->setCellValueByColumnAndRow(2, 2, $last_day_formatted);
                $object->getActiveSheet()->setCellValueByColumnAndRow(3, 2, date("d/m/Y H:i:s"));
                $object->getActiveSheet()->setCellValueByColumnAndRow(4, 2, 'Queue Worker');

                $column = 0;
                foreach ($table_columns as $field) {
                    $object->getActiveSheet()->setCellValueByColumnAndRow($column, 4, $field);
                    $object->getActiveSheet()->getStyleByColumnAndRow($column, 4)->getFont()->setBold(true);
                    $column++;
                }

                // Data rows
                $row = 5;
                foreach ($all_data as $r) {
                    $object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $r["employee"]->first_name);
                    $object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $r["employee"]->special_id);
                    $object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r["working_days"]);
                    $object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $r["worked_days"]);
                    $object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $r["absent_days"]);
                    $object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $r["paid_leaves"]);
                    $object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $r["unpaid_leaves"]);
                    $object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $r["worked_rest_days"]);
                    $object->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $r["worked_holidays"]);
                    $object->getActiveSheet()->setCellValueByColumnAndRow(9, $row, $r["month_overtime_deducted"]);
                    $object->getActiveSheet()->setCellValueByColumnAndRow(10, $row, $r["month_overtime_ph_x2"]);
                    $object->getActiveSheet()->setCellValueByColumnAndRow(11, $row, $r["month_overtime_ph_x3"]);
                    $object->getActiveSheet()->setCellValueByColumnAndRow(12, $row, $r["month_overtime_rd"]);
                    $object->getActiveSheet()->setCellValueByColumnAndRow(13, $row, $r["month_overtime_off"]);
                    $object->getActiveSheet()->setCellValueByColumnAndRow(14, $row, $r["late_count"]);
                    $object->getActiveSheet()->setCellValueByColumnAndRow(15, $row, $r["lateness_time_deducted"]);
                    $object->getActiveSheet()->setCellValueByColumnAndRow(16, $row, $r["total_trip_a"]);
                    $object->getActiveSheet()->setCellValueByColumnAndRow(17, $row, $r["total_trip_b"]);
                    $row++;
                }

                // Auto-size columns
                foreach (range('A', 'R') as $columnID) {
                    $object->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
                }

                // Save file
                if ($file_type === 'xlsx') {
                    $file_name .= '.xlsx';
                    $object_writer = new PHPExcel_Writer_Excel2007($object);
                } else {
                    $file_name .= '.xls';
                    $object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
                }

                $file_path = FCPATH . 'uploads/summary/' . $file_name;
                $object_writer->save($file_path);
                $this->_log('  [OK] Excel file created: ' . $file_name);
            }
        } catch (Exception $e) {
            throw new Exception('Failed to generate file: ' . $e->getMessage());
        }

        // Update progress for step 6 completion
        if ($job) {
            $this->queue->update_progress($job->id, array(
                'step' => 6,
                'total_steps' => 6,
                'title' => 'File generation complete',
                'processed' => count($employees),
                'total' => count($employees),
                'message' => 'Generated ' . $file_type . ' file: ' . $file_name,
                'processed_employee_ids' => $processed_employee_ids,  // FINAL RECOVERY INFO
                'resume_count' => $resume_count
            ));
        }

        $this->_log('✓ JOB COMPLETED SUCCESSFULLY');

        // Return file info for job_status endpoint
        return array(
            'status' => 'success',
            'file_path' => $file_path,
            'file_name' => $file_name,
            'summary' => array(
                'branch' => $branch_name,
                'period' => array(
                    'from' => $first_day_formatted,
                    'to' => $last_day_formatted
                ),
                'employee_count' => count($employees),
                'file_type' => $file_type,
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => 'Queue Worker'
            )
        );
    }

    private function _generate_short_report_from_month_lock($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job = null)
    {
        $file_type = isset($payload['file_type']) ? $payload['file_type'] : 'xlsx';
        $branch_ids = $this->_normalize_short_report_ids(isset($payload['branch']) ? $payload['branch'] : array());
        $department_ids = $this->_normalize_short_report_ids(isset($payload['department']) ? $payload['department'] : array());
        $position_ids = $this->_normalize_short_report_ids(isset($payload['position']) ? $payload['position'] : array());
        $section_ids = $this->_normalize_short_report_ids(isset($payload['section']) ? $payload['section'] : array());
        $employee_ids = $this->_normalize_short_report_ids(isset($payload['employee']) ? $payload['employee'] : array());
        $excluded_employee_ids = $this->_normalize_short_report_ids(isset($payload['exclude_employee']) ? $payload['exclude_employee'] : array());

        if ($job) {
            $this->queue->update_progress($job->id, array(
                'step' => 1,
                'total_steps' => 3,
                'title' => 'Resolving month lock snapshot',
                'processed' => 0,
                'total' => 0,
                'message' => 'Looking for completed lock data'
            ));
        }

        $resolved_snapshot = $this->month_lock_snapshot_service->resolve_snapshots($cid, $first_day, $last_day, $branch_ids);
        $locks = $resolved_snapshot['locks'];
        $summary_rows = $this->month_lock_snapshot_service->merge_summary_rows($locks);
        if (empty($summary_rows)) {
            throw new Exception('No snapshot rows found for the selected month lock');
        }

        if ($branch_name === 'All' && !empty($resolved_snapshot['display_branch_name'])) {
            $branch_name = $resolved_snapshot['display_branch_name'];
        }

        if ($job) {
            $this->queue->update_progress($job->id, array(
                'step' => 2,
                'total_steps' => 3,
                'title' => 'Filtering month lock snapshot',
                'processed' => 0,
                'total' => count($summary_rows),
                'message' => 'Preparing pre-calculated rows'
            ));
        }

        $employee_ids_in_lock = array();
        foreach ($summary_rows as $row) {
            if (isset($row['employee_id'])) {
                $employee_ids_in_lock[] = (int)$row['employee_id'];
            }
        }
        $employee_ids_in_lock = array_values(array_unique(array_filter($employee_ids_in_lock)));

        $employee_meta_map = array();
        if (!empty($employee_ids_in_lock)) {
            $metadata_rows = $this->db->select('id, branch_id, department_id, position_id, section_id')
                ->from('employees')
                ->where_in('id', $employee_ids_in_lock)
                ->get()
                ->result_array();

            foreach ($metadata_rows as $meta_row) {
                $employee_meta_map[(int)$meta_row['id']] = $meta_row;
            }
        }

        $all_data = array();
        foreach ($summary_rows as $row) {
            $employee_id = isset($row['employee_id']) ? (int)$row['employee_id'] : 0;
            if ($employee_id <= 0) {
                continue;
            }

            $meta = isset($employee_meta_map[$employee_id]) ? $employee_meta_map[$employee_id] : null;
            if (!$meta) {
                continue;
            }

            if (!empty($branch_ids) && !in_array((int)$meta['branch_id'], $branch_ids, true)) {
                continue;
            }
            if (!empty($department_ids) && !in_array((int)$meta['department_id'], $department_ids, true)) {
                continue;
            }
            if (!empty($position_ids) && !in_array((int)$meta['position_id'], $position_ids, true)) {
                continue;
            }
            if (!empty($section_ids) && !in_array((int)$meta['section_id'], $section_ids, true)) {
                continue;
            }
            if (!empty($employee_ids) && !in_array($employee_id, $employee_ids, true)) {
                continue;
            }
            if (!empty($excluded_employee_ids) && in_array($employee_id, $excluded_employee_ids, true)) {
                continue;
            }

            $employee = (object) array(
                'id' => $employee_id,
                'first_name' => isset($row['first_name']) ? $row['first_name'] : null,
                'special_id' => isset($row['special_id']) ? $row['special_id'] : null
            );

            $all_data[] = array(
                'employee' => $employee,
                'working_days' => isset($row['working_days']) ? (int)$row['working_days'] : 0,
                'worked_days' => isset($row['worked_days']) ? (float)$row['worked_days'] : 0,
                'absent_days' => isset($row['absent_days']) ? (int)$row['absent_days'] : 0,
                'paid_leaves' => isset($row['paid_leaves']) ? (float)$row['paid_leaves'] : 0,
                'unpaid_leaves' => isset($row['unpaid_leaves']) ? (float)$row['unpaid_leaves'] : 0,
                'worked_rest_days' => isset($row['worked_rest_days']) ? (float)$row['worked_rest_days'] : 0,
                'worked_holidays' => isset($row['worked_holidays']) ? (float)$row['worked_holidays'] : 0,
                'month_overtime' => $this->_format_time_value_for_report(isset($row['month_overtime']) ? $row['month_overtime'] : null),
                'month_overtime_deducted' => $this->_format_time_value_for_report(isset($row['month_overtime_deducted']) ? $row['month_overtime_deducted'] : null),
                'month_overtime_ph_x2' => $this->_format_time_value_for_report(isset($row['month_overtime_ph_x2']) ? $row['month_overtime_ph_x2'] : null),
                'month_overtime_ph_x3' => $this->_format_time_value_for_report(isset($row['month_overtime_ph_x3']) ? $row['month_overtime_ph_x3'] : null),
                'month_overtime_rd' => $this->_format_time_value_for_report(isset($row['month_overtime_rd']) ? $row['month_overtime_rd'] : null),
                'month_overtime_off' => $this->_format_time_value_for_report(isset($row['month_overtime_off']) ? $row['month_overtime_off'] : null),
                'late_count' => isset($row['late_count']) ? (int)$row['late_count'] : 0,
                'lateness_time_deducted' => $this->_format_time_value_for_report(isset($row['lateness_time_deducted']) ? $row['lateness_time_deducted'] : null),
                'total_trip_a' => isset($row['total_trip_a']) ? (int)$row['total_trip_a'] : 0,
                'total_trip_b' => isset($row['total_trip_b']) ? (int)$row['total_trip_b'] : 0
            );
        }

        if (empty($all_data)) {
            throw new Exception('No employees found for the selected criteria');
        }

        if ($job) {
            $this->queue->update_progress($job->id, array(
                'step' => 3,
                'total_steps' => 3,
                'title' => 'Generating file from month lock',
                'processed' => count($all_data),
                'total' => count($all_data),
                'message' => 'Using snapshot data for export'
            ));
        }

        $this->_log('  [STEP 3/3] Generating ' . strtoupper($file_type) . ' file from month lock snapshot...');

        try {
            $result = $this->short_month_lock_report_handler->generate(
                $all_data,
                $branch_name,
                $first_day,
                $last_day,
                $first_day_formatted,
                $last_day_formatted,
                $file_type
            );
        } catch (Exception $e) {
            throw new Exception('Failed to generate file: ' . $e->getMessage());
        }

        $this->_log('✓ JOB COMPLETED SUCCESSFULLY');

        return $result;
    }

    private function _collect_summary_context($payload, $first_day, $last_day, $cid, $summary_type, $job = null)
    {
        $data_source = $this->_resolve_short_report_source($payload);
        if ($data_source === 'month_lock') {
            return $this->_collect_summary_context_from_month_lock($payload, $first_day, $last_day, $cid, $summary_type, $job);
        }

        require_once APPPATH . 'helpers/payroll_bulk_helper.php';
        require_once APPPATH . 'helpers/PayrollQueryHelper.php';

        $branch_id = isset($payload['branch']) ? $payload['branch'] : array();
        $department_id = isset($payload['department']) ? $payload['department'] : array();
        $position_id = isset($payload['position']) ? $payload['position'] : array();
        $section_id = isset($payload['section']) ? $payload['section'] : null;
        $employee_id = isset($payload['employee']) ? $payload['employee'] : array();
        $exclude_employees = isset($payload['exclude_employee']) ? $payload['exclude_employee'] : array();

        $employees_from_group = array();
        $excluded_employees_from_group = array();

        if ($employee_id) {
            $employee_group_arr = array();
            foreach ($employee_id as $key) {
                if (strpos($key, '-') !== false) {
                    $arr = explode('-', $key);
                    $employee_group_arr[] = $arr[0];
                } else {
                    $employees_from_group[] = $key;
                }
            }
            foreach ($employee_group_arr as $group_id) {
                $results = $this->db->where_in('group_id', array($group_id))->get('employee_groups_relation')->result();
                foreach ($results as $result) {
                    $employees_from_group[] = $result->employee_id;
                }
            }
            $employees_from_group = array_unique($employees_from_group);
        }

        if ($exclude_employees) {
            $employee_group_arr = array();
            foreach ($exclude_employees as $key) {
                if (strpos($key, '-') !== false) {
                    $arr = explode('-', $key);
                    $employee_group_arr[] = $arr[0];
                } else {
                    $excluded_employees_from_group[] = $key;
                }
            }
            foreach ($employee_group_arr as $group_id) {
                $results = $this->db->where_in('group_id', array($group_id))->get('employee_groups_relation')->result();
                foreach ($results as $result) {
                    $excluded_employees_from_group[] = $result->employee_id;
                }
            }
            $excluded_employees_from_group = array_unique($excluded_employees_from_group);
        }

        $branch_name = 'All';
        if ($branch_id) {
            $row = $this->db->select('group_concat(name) as name')
                ->from('branches')
                ->where_in('id', $branch_id)
                ->get()->row();
            if ($row) {
                $branch_name = $row->name;
            }
        }

        $this->db->select('
            employees.id,
            employees.first_name,
            special_id,
            employees.is_daily_waged,
            d.name as department,
            s.title as section,
            p.title as position,
            employees.branch_id,
            b.name as branch,
            COALESCE(is_ot, 0) as is_ot,
            COALESCE(is_early_ot, 0) as is_early_ot,
            COALESCE(inc_late_in, 0) as inc_late_in,
            COALESCE(inc_late_break, 0) as inc_late_break,
            COALESCE(inc_early_out, 0) as inc_early_out,
            COALESCE(inc_short_hours, 0) as inc_short_hours,
            COALESCE(ot_type, "") as ot_type,
            COALESCE(ot_round, 0) as ot_round,
            COALESCE(early_ot_round, 0) as early_ot_round,
            COALESCE(use_half_hours_for_saturdays, 0) as use_half_hours_for_saturdays,
            COALESCE(round_first_hour_only, 0) as round_first_hour_only,
            COALESCE(round_by_exact_hour, 0) as round_by_exact_hour,
            COALESCE(different_first_hour_rounding, 0) as different_first_hour_rounding,
            COALESCE(worked_hours_ot_rd, 0) as worked_hours_ot_rd,
            COALESCE(worked_hours_ot_ph, 0) as worked_hours_ot_ph,
            COALESCE(deduct_hour_ot_rd, 0) as deduct_hour_ot_rd,
            COALESCE(deduct_hour_ot_ph, 0) as deduct_hour_ot_ph,
            COALESCE(worked_hours_ot_off, 0) as worked_hours_ot_off,
            COALESCE(deduct_hour_ot_off, 0) as deduct_hour_ot_off,
            COALESCE(ignore_breaks_after_endtime, 0) as ignore_breaks_after_endtime,
            COALESCE(void_lateness_time_if_less_than, 0) as void_lateness_time_if_less_than,
            COALESCE(deduct_from_ot, 0) as deduct_from_ot,
            COALESCE(deduct_from_ot_single, "not_sure") as deduct_from_ot_single,
            COALESCE(deduction_date, NOW()) as deduction_date,
            COALESCE(min_worked_hours_meal, 0) as min_worked_hours_meal,
            COALESCE(ta_rate, 0) as ta_rate,
            COALESCE(ma_rate, 0) as ma_rate,
            COALESCE(ca_rate, 0) as ca_rate,
            COALESCE(spa_rate, 0) as spa_rate,
            COALESCE(aca_rate, 0) as aca_rate,
            COALESCE(aa_rate, 0) as aa_rate,
            COALESCE(nsa_rate, 0) as nsa_rate,
            COALESCE(dsa_rate, 0) as dsa_rate,
            COALESCE(fl_rate, 0) as fl_rate,
            COALESCE(cw_rate, 0) as cw_rate,
            COALESCE(mo_rate, 0) as mo_rate,
            COALESCE(shift1_rate, 0) as shift1_rate,
            COALESCE(shift2_rate, 0) as shift2_rate,
            COALESCE(shift3_rate, 0) as shift3_rate,
            COALESCE(food_rate, 0) as food_rate,
            COALESCE(basic_wage, 0) as basic_wage,
            COALESCE(ot_group, "") as ot_group,
            COALESCE(special_incentive, 0) as special_incentive,
            COALESCE(att_all_code, "") as att_all_code,
            COALESCE(att_all_desc, "") as att_all_desc,
            COALESCE(att_all_amount, 0) as att_all_amount,
            COALESCE(is_att_all, 0) as is_att_all,
            COALESCE(mi_mo_rate, 0) as mi_mo_rate,
            COALESCE(lateness_deduction_99, 0) as lateness_deduction_99,
            COALESCE(lateness_deduction_100, 0) as lateness_deduction_100,
            COALESCE(rest_day_entitlement, 0) as rest_day_entitlement,
            COALESCE(is_shift_hours, 0) as is_shift_hours
        ', FALSE)
            ->from('employees')
            ->join('roles', 'employees.role_id = roles.id', 'left')
            ->join('departments d', 'd.id = employees.department_id', 'left')
            ->join('branches b', 'b.id = employees.branch_id', 'left')
            ->join('sections s', 'employees.section_id = s.id', 'left')
            ->join('positions p', 'p.id = employees.position_id', 'left')
            ->where('employees.company_id', $cid)
            ->where('employees.deleted_at is null')
            ->where('roles.exclude_from_system', 'no')
            ->where("(employees.employee_status = 'active'
            OR (employees.employee_status = 'terminated' AND employees.termination_date >= DATE_FORMAT('$first_day', '%Y-%m-01'))
            OR (employees.employee_status = 'resigned' AND employees.resignation_date >= DATE_FORMAT('$first_day', '%Y-%m-01'))
        )");

        if ($branch_id) $this->db->where_in('employees.branch_id', $branch_id);
        if ($department_id) $this->db->where_in('employees.department_id', $department_id);
        if ($position_id) $this->db->where_in('employees.position_id', $position_id);
        if ($section_id) $this->db->where_in('employees.section_id', $section_id);
        if ($employees_from_group) $this->db->where_in('employees.id', $employees_from_group);
        if ($excluded_employees_from_group) $this->db->where_not_in('employees.id', $excluded_employees_from_group);

        $this->db->group_by('employees.id');
        $this->db->order_by('special_id', 'asc');
        $employees = $this->db->get()->result();
        if (empty($employees)) {
            throw new Exception('No employees found for the selected criteria');
        }

        $employees_ids = array_column($employees, 'id');
        $shifts = $this->db->select('id')->from('shifts')->where('company_id', $cid)->where('is_leave', 'no')->get()->result();
        $shift_ids = array(0);
        foreach ($shifts as $s) {
            $shift_ids[] = $s->id;
        }

        $approved_ot_list = PayrollQueryHelper::get_approved_ot_list($shift_ids, $first_day, $last_day);
        $branch_rest_days = $this->db->select('id,rest_days,off_days')->from('branches')->where('company_id', $cid)->get()->result();
        $company_working_hours = PayrollQueryHelper::get_company_working_hours($cid);
        $company_ot_settings = PayrollQueryHelper::get_company_ot_settings($cid);
        $company_early_ot_settings = PayrollQueryHelper::get_company_early_ot_settings($cid);

        $worked_rest_days_array = array();
        $worked_off_days_array = array();
        $worked_holidays_array = array();
        $unpaid_leaves_absent_days = array();
        $clockings_news = array();
        $clockings_news_overnight = array();
        $paid_leaves_array = array();
        $daily_ot_array = array();
        $daily_late_array = array();
        $days_settings = array();
        $ot_type_data_map = array();

        $result_list = array();
        $result_list_overnight = array();
        foreach (array_chunk($employees_ids, 100) as $chunk) {
            $result_list = array_merge($result_list, PayrollQueryHelper::get_result_list($chunk, $first_day, $last_day));
            $result_list_overnight = array_merge($result_list_overnight, PayrollQueryHelper::get_result_list_overnight($chunk, $first_day, $last_day, $cid));
        }

        $bulk = new PayrollBulkHelper();
        $bulk->prefetch($employees_ids, $first_day, $last_day, $cid, array_unique(array_map(function ($employee) {
            return $employee->branch_id;
        }, $employees)));

        $worked_rest_days_array = array();
        $worked_off_days_array = array();
        $worked_holidays_array = array();
        $unpaid_leaves_absent_days = array();
        $clockings_news = array();
        $clockings_news_overnight = array();
        $paid_leaves_array = array();
        $daily_ot_array = array();
        $daily_late_array = array();
        $days_settings = array();
        $ot_type_data_map = array();

        $all_data = array();
        foreach ($employees as $emp) {
            $all_data[] = PayrollQueryHelper::calculate_summary_data(
                $emp->id,
                $first_day,
                $last_day,
                $summary_type,
                $emp,
                $result_list,
                $result_list_overnight,
                $company_working_hours,
                false,
                $company_ot_settings,
                $company_early_ot_settings,
                $approved_ot_list,
                $branch_rest_days,
                $cid,
                $worked_rest_days_array,
                $worked_off_days_array,
                $worked_holidays_array,
                $unpaid_leaves_absent_days,
                $clockings_news,
                $clockings_news_overnight,
                $paid_leaves_array,
                $daily_ot_array,
                $daily_late_array,
                $days_settings,
                $ot_type_data_map,
                $bulk
            );
        }

        return array(
            'branch_name' => $branch_name,
            'employees' => $employees,
            'all_data' => $all_data,
            'employees_ids' => $employees_ids,
            'worked_rest_days_array' => $worked_rest_days_array,
            'worked_off_days_array' => $worked_off_days_array,
            'worked_holidays_array' => $worked_holidays_array,
            'unpaid_leaves_absent_days' => $unpaid_leaves_absent_days,
            'paid_leaves_array' => $paid_leaves_array,
            'daily_ot_array' => $daily_ot_array,
            'daily_late_array' => $daily_late_array,
            'data_source' => 'realtime'
        );
    }

    private function _collect_summary_context_from_month_lock($payload, $first_day, $last_day, $cid, $summary_type, $job = null)
    {
        $branch_ids = $this->_normalize_short_report_ids(isset($payload['branch']) ? $payload['branch'] : array());
        $department_ids = $this->_normalize_short_report_ids(isset($payload['department']) ? $payload['department'] : array());
        $position_ids = $this->_normalize_short_report_ids(isset($payload['position']) ? $payload['position'] : array());
        $section_ids = $this->_normalize_short_report_ids(isset($payload['section']) ? $payload['section'] : array());
        $employee_ids = $this->_normalize_short_report_ids(isset($payload['employee']) ? $payload['employee'] : array());
        $excluded_employee_ids = $this->_normalize_short_report_ids(isset($payload['exclude_employee']) ? $payload['exclude_employee'] : array());

        $resolved_snapshot = $this->month_lock_snapshot_service->resolve_snapshots($cid, $first_day, $last_day, $branch_ids);
        $locks = $resolved_snapshot['locks'];

        $summary_rows = $this->month_lock_snapshot_service->merge_summary_rows($locks);
        if (empty($summary_rows)) {
            throw new Exception('No snapshot rows found for the selected month lock');
        }

        $employee_ids_in_lock = array();
        foreach ($summary_rows as $row) {
            if (isset($row['employee_id'])) {
                $employee_ids_in_lock[] = (int)$row['employee_id'];
            }
        }
        $employee_ids_in_lock = array_values(array_unique(array_filter($employee_ids_in_lock)));

        $employee_meta_map = array();
        if (!empty($employee_ids_in_lock)) {
            $metadata_rows = $this->db->select('id, first_name, special_id, branch_id, department_id, position_id, section_id, branch_id')
                ->from('employees')
                ->where_in('id', $employee_ids_in_lock)
                ->get()
                ->result_array();

            foreach ($metadata_rows as $meta_row) {
                $employee_meta_map[(int)$meta_row['id']] = $meta_row;
            }
        }

        $detail_rows = $this->month_lock_snapshot_service->merge_detail_rows($locks, $employee_ids_in_lock, $first_day, $last_day);
        $details_by_employee = array();
        foreach ($detail_rows as $detail_row) {
            $employee_id = (int)$detail_row['employee_id'];
            if (!isset($details_by_employee[$employee_id])) {
                $details_by_employee[$employee_id] = array();
            }
            $details_by_employee[$employee_id][] = $detail_row;
        }

        $branch_name = 'All';
        if (count($branch_ids) === 1) {
            $branch_row = $this->db->select('name')->from('branches')->where('id', (int)$branch_ids[0])->get()->row();
            if ($branch_row && !empty($branch_row->name)) {
                $branch_name = $branch_row->name;
            }
        } elseif (!empty($resolved_snapshot['display_branch_name'])) {
            $branch_name = $resolved_snapshot['display_branch_name'];
        }

        $all_data = array();
        $employees = array();

        foreach ($summary_rows as $row) {
            $employee_id = isset($row['employee_id']) ? (int)$row['employee_id'] : 0;
            if ($employee_id <= 0) {
                continue;
            }

            $meta = isset($employee_meta_map[$employee_id]) ? $employee_meta_map[$employee_id] : null;
            if (!$meta) {
                continue;
            }

            if (!empty($branch_ids) && !in_array((int)$meta['branch_id'], $branch_ids, true)) {
                continue;
            }
            if (!empty($department_ids) && !in_array((int)$meta['department_id'], $department_ids, true)) {
                continue;
            }
            if (!empty($position_ids) && !in_array((int)$meta['position_id'], $position_ids, true)) {
                continue;
            }
            if (!empty($section_ids) && !in_array((int)$meta['section_id'], $section_ids, true)) {
                continue;
            }
            if (!empty($employee_ids) && !in_array($employee_id, $employee_ids, true)) {
                continue;
            }
            if (!empty($excluded_employee_ids) && in_array($employee_id, $excluded_employee_ids, true)) {
                continue;
            }

            $employee = (object) array(
                'id' => $employee_id,
                'first_name' => isset($row['first_name']) ? $row['first_name'] : null,
                'special_id' => isset($row['special_id']) ? $row['special_id'] : null,
                'department' => isset($row['department']) ? $row['department'] : null,
                'position' => isset($row['position']) ? $row['position'] : null,
                'branch' => isset($row['branch_name']) ? $row['branch_name'] : $branch_name,
                'group_names' => ''
            );

            $dates = array();
            $public_holidays = array();
            $public_holidays_names = array();
            $rest_days = array();
            $off_days = array();

            if (isset($details_by_employee[$employee_id])) {
                foreach ($details_by_employee[$employee_id] as $detail_row) {
                    $day = new stdClass();
                    $day->date = $detail_row['date'];
                    $day->date_string = date('d M Y', strtotime($detail_row['date']));
                    $day->day_name = $detail_row['day_name'];
                    $day->shift_name = $detail_row['shift_name'];
                    $day->shift_hours = $detail_row['shift_hours'];
                    $day->work_hours = $detail_row['work_hours'];
                    $day->total_hours = $detail_row['total_hours'];
                    $day->break_hours = $detail_row['break_hours'];
                    $day->late_hours = $detail_row['late_time'];
                    $day->break_late_hours = $detail_row['break_late_time'];
                    $day->early_out = $detail_row['early_out_time'];
                    $day->short_hours = $detail_row['short_hours'];
                    $day->is_late = !empty($detail_row['is_late']);
                    $day->is_late_break = !empty($detail_row['is_break_late']);
                    $day->is_early_out = !empty($detail_row['is_early_out']);
                    $day->is_shift = !empty($detail_row['shift_name']) ? 'true' : 'false';
                    $day->is_replaced_ph = false;
                    $day->is_rest_day = !empty($detail_row['is_rest_day']);
                    $day->is_ot = !empty($detail_row['is_ot_approved']);
                    $day->overtime = $this->_normalize_time_value($detail_row['overtime']);
                    $day->overtime_m = $this->_normalize_time_value($detail_row['overtime']);
                    $day->overtime_ph_x2 = isset($detail_row['overtime_ph_x2']) ? (float)$detail_row['overtime_ph_x2'] : 0;
                    $day->overtime_ph_x3 = isset($detail_row['overtime_ph_x3']) ? (float)$detail_row['overtime_ph_x3'] : 0;
                    $day->x2 = $day->overtime_ph_x2 > 0;
                    $day->x3 = $day->overtime_ph_x3 > 0;
                    $day->days = !empty($detail_row['is_present']) ? 1 : 0;
                    $day->trip_a = (int)$detail_row['trip_a'];
                    $day->trip_b = (int)$detail_row['trip_b'];
                    $day->month_overtime_deducted = (float)$detail_row['overtime_deducted'];
                    $day->overtime_m = $detail_row['overtime'];
                    $day->month_overtime_ph = (float)$detail_row['overtime_ph'];
                    $day->month_overtime_rd = (float)$detail_row['overtime_rd'];

                    $clockings = array();
                    if (!empty($detail_row['clockings_json'])) {
                        $decoded = json_decode($detail_row['clockings_json']);
                        if (is_array($decoded)) {
                            foreach ($decoded as $clock) {
                                $clockings[] = (object) $clock;
                            }
                        }
                    }

                    if (empty($clockings)) {
                        $clockings[] = (object) array(
                            'day_f' => date('d/m D', strtotime($detail_row['date'])),
                            'code' => $detail_row['shift_name'],
                            'clock_in' => $detail_row['clock_in'],
                            'clock_out' => $detail_row['clock_out'],
                            'reason' => '',
                            'remark' => $detail_row['remark'],
                            'staff_remark' => $detail_row['staff_remark'],
                            'clock_in_id' => null,
                            'clock_out_id' => null
                        );
                    }
                    $day->clockings = $clockings;

                    if (!empty($detail_row['is_public_holiday'])) {
                        $public_holidays[] = $detail_row['date'];
                        $public_holidays_names[] = $detail_row['public_holiday_name'];
                    }
                    if (!empty($detail_row['is_rest_day']) && !empty($detail_row['day_name'])) {
                        $rest_days[] = $detail_row['day_name'];
                    }
                    if (!empty($detail_row['is_off_day']) && !empty($detail_row['day_name'])) {
                        $off_days[] = $detail_row['day_name'];
                    }

                    $dates[] = $day;
                }
            }

            $all_data[] = array(
                'employee' => $employee,
                'dates' => $dates,
                'public_holidays' => array_values(array_unique($public_holidays)),
                'public_holidays_names' => $public_holidays_names,
                'rest_days' => array_values(array_unique($rest_days)),
                'off_days' => array_values(array_unique($off_days)),
                'working_days' => isset($row['working_days']) ? (int)$row['working_days'] : 0,
                'worked_days' => isset($row['worked_days']) ? (float)$row['worked_days'] : 0,
                'absent_days' => isset($row['absent_days']) ? (int)$row['absent_days'] : 0,
                'paid_leaves' => isset($row['paid_leaves']) ? (float)$row['paid_leaves'] : 0,
                'unpaid_leaves' => isset($row['unpaid_leaves']) ? (float)$row['unpaid_leaves'] : 0,
                'worked_rest_days' => isset($row['worked_rest_days']) ? (float)$row['worked_rest_days'] : 0,
                'worked_holidays' => isset($row['worked_holidays']) ? (float)$row['worked_holidays'] : 0,
                'month_overtime' => isset($row['month_overtime']) ? $row['month_overtime'] : null,
                'month_overtime_deducted' => isset($row['month_overtime_deducted']) ? (float)$row['month_overtime_deducted'] : 0,
                'month_overtime_ph' => isset($row['month_overtime_ph']) ? (float)$row['month_overtime_ph'] : 0,
                'month_overtime_ph_x2' => isset($row['month_overtime_ph_x2']) ? (float)$row['month_overtime_ph_x2'] : 0,
                'month_overtime_ph_x3' => isset($row['month_overtime_ph_x3']) ? (float)$row['month_overtime_ph_x3'] : 0,
                'month_overtime_rd' => isset($row['month_overtime_rd']) ? (float)$row['month_overtime_rd'] : 0,
                'month_overtime_off' => isset($row['month_overtime_off']) ? (float)$row['month_overtime_off'] : 0,
                'late_count' => isset($row['late_count']) ? (int)$row['late_count'] : 0,
                'lateness_time' => isset($row['lateness_time']) ? $row['lateness_time'] : null,
                'lateness_time_deducted' => isset($row['lateness_time_deducted']) ? (float)$row['lateness_time_deducted'] : 0,
                'total_early_count' => isset($row['early_out_count']) ? (int)$row['early_out_count'] : 0,
                'total_early' => isset($row['total_early']) ? $row['total_early'] : null,
                'food_allowance_days' => isset($row['food_allowance_days']) ? (int)$row['food_allowance_days'] : 0,
                'monthly_dsa_count' => isset($row['monthly_dsa_count']) ? (int)$row['monthly_dsa_count'] : 0,
                'monthly_nsa_count' => isset($row['monthly_nsa_count']) ? (int)$row['monthly_nsa_count'] : 0,
                'total_trip_a' => isset($row['total_trip_a']) ? (int)$row['total_trip_a'] : 0,
                'total_trip_b' => isset($row['total_trip_b']) ? (int)$row['total_trip_b'] : 0,
                'total' => isset($row['total_hours']) ? $row['total_hours'] : null,
                'work' => isset($row['work_hours']) ? $row['work_hours'] : null,
                'break' => null,
                'late' => isset($row['lateness_time']) ? $row['lateness_time'] : null,
                'break_late' => null,
                'total_days' => count($dates)
            );

            $employees[] = $employee;
        }

        if (empty($all_data)) {
            throw new Exception('No employees found for the selected criteria');
        }

        return array(
            'branch_name' => $branch_name,
            'employees' => $employees,
            'all_data' => $all_data,
            'employees_ids' => array(),
            'worked_rest_days_array' => array(),
            'worked_off_days_array' => array(),
            'worked_holidays_array' => array(),
            'unpaid_leaves_absent_days' => array(),
            'paid_leaves_array' => array(),
            'daily_ot_array' => array(),
            'daily_late_array' => array(),
            'data_source' => 'month_lock'
        );
    }
    private function _generate_accounts_report($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job = null)
    {
        $context = $this->_collect_summary_context($payload, $first_day, $last_day, $cid, 'accounts', $job);
        $all_data = $context['all_data'];

        return $this->accounts_report_handler->generate(
            $all_data,
            $branch_name,
            $first_day,
            $last_day,
            $first_day_formatted,
            $last_day_formatted
        );
    }

    private function _generate_over_time_summary_report($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job = null)
    {
        $context = $this->_collect_summary_context($payload, $first_day, $last_day, $cid, 'short', $job);
        $all_data = $context['all_data'];

        return $this->over_time_summary_report_handler->generate(
            $all_data,
            $branch_name,
            $first_day,
            $last_day,
            $first_day_formatted,
            $last_day_formatted
        );
    }

    private function _generate_lateness_report($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job = null)
    {
        $branch_ids = $this->_normalize_short_report_ids(isset($payload['branch']) ? $payload['branch'] : array());
        $resolved_snapshot = $this->month_lock_snapshot_service->resolve_snapshots($cid, $first_day, $last_day, $branch_ids);
        $locks = $resolved_snapshot['locks'];
        $all_data = $this->month_lock_snapshot_service->merge_summary_rows($locks);
        $month_rows = $this->month_lock_snapshot_service->collect_lateness_monthly_rows($locks, $first_day, $last_day);

        return $this->lateness_report_handler->generate(
            $all_data,
            $month_rows,
            $branch_name,
            $first_day,
            $last_day,
            $first_day_formatted,
            $last_day_formatted
        );
    }

    private function _generate_weekly_ot_report($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job = null)
    {
        $context = $this->_collect_summary_context($payload, $first_day, $last_day, $cid, 'summary', $job);
        $all_data = $context['all_data'];

        return $this->weekly_ot_report_handler->generate(
            $all_data,
            $branch_name,
            $first_day,
            $last_day,
            $first_day_formatted,
            $last_day_formatted
        );
    }

    private function _generate_full_summary_report($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job = null)
    {
        $context = $this->_collect_summary_context($payload, $first_day, $last_day, $cid, 'summary', $job);
        $all_data = $context['all_data'];

        return $this->full_summary_report_handler->generate(
            $all_data,
            $branch_name,
            $first_day,
            $last_day,
            $first_day_formatted,
            $last_day_formatted
        );
    }

    private function _generate_sql_report($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job = null)
    {
        $sql_payload = $payload;
        $sql_payload['data_source'] = 'realtime';

        $context = $this->_collect_summary_context($sql_payload, $first_day, $last_day, $cid, 'sql', $job);
        return $this->sql_report_handler->generate(
            $context,
            $cid,
            $branch_name,
            $first_day,
            $last_day,
            $first_day_formatted,
            $last_day_formatted
        );
    }

    private function _generate_mcb01_clocking_report($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job = null)
    {
        $context = $this->_collect_summary_context($payload, $first_day, $last_day, $cid, 'summary', $job);
        $all_data = $context['all_data'];
        $file_type = isset($payload['file_type']) ? (string)$payload['file_type'] : 'xlsx';

        return $this->mcb01_clocking_report_handler->generate(
            $all_data,
            $branch_name,
            $first_day,
            $last_day,
            $file_type
        );
    }

    private function _generate_tsf01_csv_report($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job = null)
    {
        $context = $this->_collect_summary_context($payload, $first_day, $last_day, $cid, 'sql', $job);
        $all_data = $context['all_data'];

        return $this->tsf01_csv_report_handler->generate(
            $all_data,
            $branch_name,
            $first_day,
            $last_day
        );
    }

    private function _generate_daily_time_card_report($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job = null)
    {
        $context = $this->_collect_summary_context($payload, $first_day, $last_day, $cid, 'summary', $job);
        $all_data = $context['all_data'];
        $file_type = isset($payload['file_type']) ? (string)$payload['file_type'] : 'xlsx';

        return $this->daily_time_card_report_handler->generate(
            $all_data,
            $branch_name,
            $first_day,
            $file_type
        );
    }

    private function _generate_work_hours_summary_report($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job = null)
    {
        $context = $this->_collect_summary_context($payload, $first_day, $last_day, $cid, 'summary', $job);
        $all_data = $context['all_data'];
        $file_type = isset($payload['file_type']) ? (string)$payload['file_type'] : 'xlsx';

        return $this->work_hours_summary_report_handler->generate(
            $all_data,
            $branch_name,
            $first_day,
            $last_day,
            $file_type
        );
    }

    private function _generate_gni01_payroll_process_report($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job = null)
    {
        $summary_context = $this->_collect_summary_context($payload, $first_day, $last_day, $cid, 'short', $job);
        $sql_context = $this->_collect_summary_context($payload, $first_day, $last_day, $cid, 'sql', $job);

        $ot_payload = $payload;
        if (!empty($payload['ot_from']) && !empty($payload['ot_to'])) {
            $ot_payload['from_date'] = $payload['ot_from'];
            $ot_payload['to_date'] = $payload['ot_to'];
        } else {
            $ot_payload['from_date'] = date('d/m/Y', strtotime($first_day . ' -1 month'));
            $ot_payload['to_date'] = date('d/m/Y', strtotime($last_day));
        }

        $ot_date1 = DateTime::createFromFormat('d/m/Y', $ot_payload['from_date']);
        $ot_date2 = DateTime::createFromFormat('d/m/Y', $ot_payload['to_date']);
        if (!$ot_date1 || !$ot_date2) {
            $ot_date1 = DateTime::createFromFormat('Y-m-d', $ot_payload['from_date']);
            $ot_date2 = DateTime::createFromFormat('Y-m-d', $ot_payload['to_date']);
        }
        if (!$ot_date1 || !$ot_date2) {
            throw new Exception('Invalid OT period for GNI01 report');
        }

        $ot_first_day = $ot_date1->format('Y-m-d');
        $ot_last_day = $ot_date2->format('Y-m-d');

        $short_ot_context = $this->_collect_summary_context($ot_payload, $ot_first_day, $ot_last_day, $cid, 'short', $job);
        $sql_ot_context = $this->_collect_summary_context($ot_payload, $ot_first_day, $ot_last_day, $cid, 'sql', $job);

        return $this->gni01_payroll_process_report_handler->generate(
            $cid,
            $branch_name,
            $first_day,
            $last_day,
            $first_day_formatted,
            $last_day_formatted,
            $summary_context['all_data'],
            $short_ot_context['all_data'],
            $sql_context['all_data'],
            $sql_ot_context['all_data']
        );
    }

    private function _generate_cjc01_payroll_report($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job = null)
    {
        $context = $this->_collect_summary_context($payload, $first_day, $last_day, $cid, 'summary', $job);
        $all_data = $context['all_data'];

        return $this->cjc01_payroll_report_handler->generate(
            $all_data,
            $branch_name,
            $first_day,
            $last_day
        );
    }

    private function _generate_bmi_summary_report($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job = null)
    {
        $context = $this->_collect_summary_context($payload, $first_day, $last_day, $cid, 'summary', $job);
        return $this->bmi_summary_report_handler->generate(
            $context['all_data'],
            $branch_name,
            $first_day,
            $last_day
        );
    }

    private function _generate_bmi_summary_short_report($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job = null)
    {
        $context = $this->_collect_summary_context($payload, $first_day, $last_day, $cid, 'summary', $job);
        return $this->bmi_summary_short_report_handler->generate(
            $context['all_data'],
            $branch_name,
            $first_day,
            $last_day
        );
    }

    private function _generate_mm01_report($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $job = null)
    {
        $context = $this->_collect_summary_context($payload, $first_day, $last_day, $cid, 'sql', $job);
        $all_data = $context['all_data'];
        $file_type = isset($payload['file_type']) ? (string)$payload['file_type'] : 'xlsx';

        return $this->mm01_report_handler->generate(
            $all_data,
            $branch_name,
            $first_day,
            $last_day,
            $first_day_formatted,
            $last_day_formatted,
            $file_type
        );
    }

    private function _generate_short_style_summary_report($payload, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $branch_name, $cid, $summary_type, $job = null)
    {
        $context = $this->_collect_summary_context($payload, $first_day, $last_day, $cid, $summary_type, $job);
        $all_data = $context['all_data'];
        $file_type = isset($payload['file_type']) ? (string)$payload['file_type'] : 'xlsx';

        return $this->short_month_lock_report_handler->generate(
            $all_data,
            $branch_name,
            $first_day,
            $last_day,
            $first_day_formatted,
            $last_day_formatted,
            $file_type
        );
    }

    private function _resolve_short_report_source($payload)
    {
        if (isset($payload['data_source']) && $payload['data_source'] !== '') {
            return strtolower(trim((string)$payload['data_source']));
        }

        if (isset($payload['source']) && $payload['source'] !== '') {
            return strtolower(trim((string)$payload['source']));
        }

        return 'month_lock';
    }

    private function _normalize_short_report_ids($value)
    {
        if (!is_array($value)) {
            $value = empty($value) ? array() : array($value);
        }

        $ids = array();
        foreach ($value as $item) {
            if ($item === null || $item === '') {
                continue;
            }
            $ids[] = (int)$item;
        }

        return array_values(array_unique(array_filter($ids)));
    }

    private function _generate_month_lock($payload, $job = null)
    {
        if (empty($payload['lock_id']) || empty($payload['company_id']) || empty($payload['from_date']) || empty($payload['to_date'])) {
            throw new Exception('Invalid month lock payload');
        }

        if (!$this->payroll) {
            $this->_load_payroll_api();
        }

        $lock_id = (int)$payload['lock_id'];
        $company_id = (int)$payload['company_id'];
        $branch_id = isset($payload['branch_id']) && (int)$payload['branch_id'] > 0 ? (int)$payload['branch_id'] : null;
        $first_day = $payload['from_date'];
        $last_day = $payload['to_date'];
        $this_month_end_date= date('Y-m-t', strtotime($first_day));

        $lock = $this->month_lock->get_by_id($lock_id);
        if (!$lock) {
            throw new Exception('Month lock not found: ' . $lock_id);
        }

        // Daily incremental runs send from_date == to_date == yesterday so that detail
        // rows for already-locked days are never touched/recalculated. The summary,
        // however, is always a full month-to-date aggregate — it must be calculated
        // over the whole month so far (lock.start_date -> to_date), not just the single
        // incremental day, otherwise it gets overwritten with one day's numbers every night.
        $summary_calc_first_day = (!empty($lock->start_date) && $lock->start_date <= $first_day)
            ? $lock->start_date
            : $first_day;

        $this->month_lock->mark_processing($lock_id);
        // $this->month_lock->clear_lock_data($lock_id);
        // Detail rows stay scoped to the narrow incremental window — old days are left alone.
        $this->month_lock->clear_lock_details_for_range($lock_id, $first_day, $last_day);
        // Summary is always a full-month aggregate, so it must be rebuilt in full.
        $this->month_lock->clear_lock_summary($lock_id);

        if ($job) {
            $this->queue->update_progress($job->id, array(
                'step' => 1,
                'total_steps' => 4,
                'title' => 'Preparing month lock',
                'processed' => 0,
                'total' => 0,
                'message' => 'Initializing lock job'
            ));
        }

        $date1 = DateTime::createFromFormat('Y-m-d', $first_day);
        $date2 = DateTime::createFromFormat('Y-m-d', $last_day);
        if (!$date1 || !$date2) {
            $this->month_lock->mark_failed($lock_id, 'Invalid lock date range');
            throw new Exception('Invalid lock date range');
        }

        $branch_filter = array();
        if ($branch_id !== null) {
            $branch_filter[] = $branch_id;
        }

        $calcParams = array(
            'input' => array(),
            'cid' => $company_id,
            // Calculation runs over the full month-to-date so the summary aggregate is
            // correct. Detail rows are filtered back down to [first_day, last_day] below.
            'first_day' => $summary_calc_first_day,
            'last_day' => $last_day,
            'branch_id' => $branch_filter,
            'branch_name' => $branch_id ? '' : 'All',
            'department_id' => array(),
            'position_id' => array(),
            'section_id' => null,
            'employee_id' => array(),
            'exclude_employees' => array()
        );

        try {
            $calculation_results = $this->payroll->dataCalculations($calcParams);
        } catch (Exception $e) {
            $this->month_lock->mark_failed($lock_id, $e->getMessage());
            throw $e;
        }

        $all_data = isset($calculation_results['all_data']) ? $calculation_results['all_data'] : array();
        if (empty($all_data)) {
            $this->month_lock->mark_failed($lock_id, 'No employees found for selected lock criteria');
            throw new Exception('No employees found for selected lock criteria');
        }

        $total_employees = count($all_data);
        $total_detail_rows = 0;
        $summary_batch = array();
        $detail_batch = array();

        if ($job) {
            $this->queue->update_progress($job->id, array(
                'step' => 2,
                'total_steps' => 4,
                'title' => 'Calculating and persisting snapshots',
                'processed' => 0,
                'total' => $total_employees,
                'message' => 'Processing employee snapshots'
            ));
        }

        foreach ($all_data as $index => $employee_data) {
            $summary_batch[] = $this->_build_month_lock_summary_row($lock_id, $company_id, $branch_id, $employee_data);

            $employee_details = $this->_build_month_lock_detail_rows($lock_id, $company_id, $branch_id, $employee_data, $first_day, $last_day);
            $total_detail_rows += count($employee_details);
            foreach ($employee_details as $row) {
                $detail_batch[] = $row;
            }

            if (count($summary_batch) >= 100) {
                if ($this->month_lock->insert_summary_batch($summary_batch) === false) {
                    throw new Exception('Failed to persist month lock summary batch');
                }
                $summary_batch = array();
            }

            if (count($detail_batch) >= 500) {
                if ($this->month_lock->insert_details_batch($detail_batch) === false) {
                    throw new Exception('Failed to persist month lock details batch');
                }
                $detail_batch = array();
            }

            if ($job && (($index + 1) % 20 === 0 || ($index + 1) === $total_employees)) {
                $this->queue->update_progress($job->id, array(
                    'step' => 2,
                    'total_steps' => 4,
                    'title' => 'Calculating and persisting snapshots',
                    'processed' => ($index + 1),
                    'total' => $total_employees,
                    'message' => 'Processed ' . ($index + 1) . '/' . $total_employees . ' employees'
                ));
            }
        }

        if (!empty($summary_batch)) {
            if ($this->month_lock->insert_summary_batch($summary_batch) === false) {
                throw new Exception('Failed to persist final month lock summary batch');
            }
        }
        if (!empty($detail_batch)) {
            if ($this->month_lock->insert_details_batch($detail_batch) === false) {
                throw new Exception('Failed to persist final month lock details batch');
            }
        }

        if ($job) {
            $this->queue->update_progress($job->id, array(
                'step' => 3,
                'total_steps' => 4,
                'title' => 'Finalizing month lock',
                'processed' => $total_employees,
                'total' => $total_employees,
                'message' => 'Updating lock metadata'
            ));
        }

        if( $last_day < $this_month_end_date) {
            $this->month_lock->mark_rolling_synced($lock_id, $total_employees, $total_detail_rows, $last_day);
        } else {
            $this->month_lock->mark_completed($lock_id, $total_employees, $total_detail_rows,$last_day);
        }
       ;

        if ($job) {
            $this->queue->update_progress($job->id, array(
                'step' => 4,
                'total_steps' => 4,
                'title' => 'Completed',
                'processed' => $total_employees,
                'total' => $total_employees,
                'message' => 'Month lock completed successfully'
            ));
        }

        return array(
            'status' => 'success',
            'lock_id' => $lock_id,
            'summary' => array(
                'employee_count' => $total_employees,
                'detail_records' => $total_detail_rows,
                'from_date' => $first_day,
                'to_date' => $last_day,
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => 'Queue Worker'
            )
        );
    }

    private function _build_month_lock_summary_row($lock_id, $company_id, $branch_id, $employee_data)
    {
        $employee = isset($employee_data['employee']) ? $employee_data['employee'] : null;

        return array(
            'lock_id' => (int)$lock_id,
            'employee_id' => $employee ? (int)$employee->id : 0,
            'company_id' => (int)$company_id,
            'branch_id' => $branch_id,
            'first_name' => $employee ? $employee->first_name : null,
            'special_id' => $employee ? $employee->special_id : null,
            'department' => $employee ? $employee->department : null,
            'position' => $employee ? $employee->position : null,
            'working_days' => isset($employee_data['working_days']) ? (int)$employee_data['working_days'] : 0,
            'worked_days' => isset($employee_data['worked_days']) ? (float)$employee_data['worked_days'] : 0,
            'absent_days' => isset($employee_data['absent_days']) ? (int)$employee_data['absent_days'] : 0,
            'worked_rest_days' => isset($employee_data['worked_rest_days']) ? (float)$employee_data['worked_rest_days'] : 0,
            'worked_off_days' => isset($employee_data['worked_off_days']) ? (float)$employee_data['worked_off_days'] : 0,
            'worked_holidays' => isset($employee_data['worked_holidays']) ? (float)$employee_data['worked_holidays'] : 0,
            'total_holidays' => isset($employee_data['total_holidays']) ? (int)$employee_data['total_holidays'] : 0,
            'paid_leaves' => isset($employee_data['paid_leaves']) ? (float)$employee_data['paid_leaves'] : 0,
            'unpaid_leaves' => isset($employee_data['unpaid_leaves']) ? (float)$employee_data['unpaid_leaves'] : 0,
            'medical_leaves' => isset($employee_data['total_medical_leaves']) ? (float)$employee_data['total_medical_leaves'] : 0,
            'half_day_paid' => isset($employee_data['total_half_day_paid']) ? (int)$employee_data['total_half_day_paid'] : 0,
            'full_day_paid' => isset($employee_data['total_full_day_paid']) ? (int)$employee_data['total_full_day_paid'] : 0,
            'half_day_unpaid' => isset($employee_data['total_half_day_unpaid']) ? (int)$employee_data['total_half_day_unpaid'] : 0,
            'total_hours' => isset($employee_data['total_hours']) ? $employee_data['total_hours'] : null,
            'work_hours' => isset($employee_data['work_hours']) ? $employee_data['work_hours'] : null,
            'shift_hours_total' => isset($employee_data['total_shift_hours']) ? $employee_data['total_shift_hours'] : null,
            'monthly_working_hours' => isset($employee_data['monthly_working_hours']) ? $employee_data['monthly_working_hours'] : null,
            'late_days' => isset($employee_data['late_days']) ? (int)$employee_data['late_days'] : 0,
            'late_count' => isset($employee_data['late_count']) ? (int)$employee_data['late_count'] : 0,
            'lateness_time' => isset($employee_data['lateness_time']) ? $employee_data['lateness_time'] : null,
            'lateness_time_deducted' => isset($employee_data['lateness_time_deducted']) ? (float)$employee_data['lateness_time_deducted'] : 0,
            'early_out_count' => isset($employee_data['total_early_count']) ? (int)$employee_data['total_early_count'] : 0,
            'total_early' => isset($employee_data['total_early']) ? $employee_data['total_early'] : null,
            'total_short' => isset($employee_data['total_short']) ? $employee_data['total_short'] : null,
            'break_late' => isset($employee_data['break_late']) ? $employee_data['break_late'] : null,
            'total_break_late' => isset($employee_data['total_break_late']) ? (int)$employee_data['total_break_late'] : 0,
            'month_overtime' => isset($employee_data['month_overtime']) ? $employee_data['month_overtime'] : null,
            'month_overtime_deducted' => isset($employee_data['month_overtime_deducted']) ? (float)$employee_data['month_overtime_deducted'] : 0,
            'month_overtime_ph' => isset($employee_data['month_overtime_ph']) ? (float)$employee_data['month_overtime_ph'] : 0,
            'month_overtime_ph_x2' => isset($employee_data['month_overtime_ph_x2']) ? (float)$employee_data['month_overtime_ph_x2'] : 0,
            'month_overtime_ph_x3' => isset($employee_data['month_overtime_ph_x3']) ? (float)$employee_data['month_overtime_ph_x3'] : 0,
            'month_overtime_rd' => isset($employee_data['month_overtime_rd']) ? (float)$employee_data['month_overtime_rd'] : 0,
            'month_overtime_off' => isset($employee_data['month_overtime_off']) ? (float)$employee_data['month_overtime_off'] : 0,
            'total_missing_in_out' => isset($employee_data['total_missing_in_out']) ? (int)$employee_data['total_missing_in_out'] : 0,
            'bmi_total_ot' => isset($employee_data['total_bmi_ot']) ? (float)$employee_data['total_bmi_ot'] : 0,
            'bmi_total_ot_sunday' => isset($employee_data['total_bmi_ot_sunday']) ? (float)$employee_data['total_bmi_ot_sunday'] : 0,
            'bmi_total_ph_1' => isset($employee_data['total_bmi_ph_1']) ? (float)$employee_data['total_bmi_ph_1'] : 0,
            'bmi_total_ph_2' => isset($employee_data['total_bmi_ph_2']) ? (float)$employee_data['total_bmi_ph_2'] : 0,
            'bmi_total_ta' => isset($employee_data['total_bmi_ta']) ? (float)$employee_data['total_bmi_ta'] : 0,
            'bmi_total_ma' => isset($employee_data['total_bmi_ma']) ? (float)$employee_data['total_bmi_ma'] : 0,
            'bmi_total_ca' => isset($employee_data['total_bmi_ca']) ? (float)$employee_data['total_bmi_ca'] : 0,
            'bmi_total_spa' => isset($employee_data['total_bmi_spa']) ? (float)$employee_data['total_bmi_spa'] : 0,
            'bmi_total_aca' => isset($employee_data['total_bmi_aca']) ? (float)$employee_data['total_bmi_aca'] : 0,
            'bmi_total_fl' => isset($employee_data['total_bmi_fl']) ? (float)$employee_data['total_bmi_fl'] : 0,
            'bmi_total_cw' => isset($employee_data['total_bmi_cw']) ? (float)$employee_data['total_bmi_cw'] : 0,
            'bmi_total_mo' => isset($employee_data['total_bmi_mo']) ? (float)$employee_data['total_bmi_mo'] : 0,
            'bmi_total_shift1' => isset($employee_data['total_bmi_shift1']) ? (float)$employee_data['total_bmi_shift1'] : 0,
            'bmi_total_shift2' => isset($employee_data['total_bmi_shift2']) ? (float)$employee_data['total_bmi_shift2'] : 0,
            'bmi_total_shift3' => isset($employee_data['total_bmi_shift3']) ? (float)$employee_data['total_bmi_shift3'] : 0,
            'bmi_attendance_allowance' => isset($employee_data['bmi_attendance_allowance']) ? (int)$employee_data['bmi_attendance_allowance'] : 1,
            'bmi_late_more_than_10' => isset($employee_data['late_count']) ? (int)$employee_data['late_count'] : 0,
            'gbr_attendance_allowance' => isset($employee_data['gbr_attendance_allowance']) ? (int)$employee_data['gbr_attendance_allowance'] : 1,
            'gbr_night_shifts' => isset($employee_data['gbr_night_shifts']) ? (int)$employee_data['gbr_night_shifts'] : 0,
            'monthly_dsa_count' => isset($employee_data['monthly_dsa_count']) ? (int)$employee_data['monthly_dsa_count'] : 0,
            'monthly_nsa_count' => isset($employee_data['monthly_nsa_count']) ? (int)$employee_data['monthly_nsa_count'] : 0,
            'total_meal_days' => isset($employee_data['total_meal_days']) ? (int)$employee_data['total_meal_days'] : 0,
            'total_trip_a' => isset($employee_data['total_trip_a']) ? (int)$employee_data['total_trip_a'] : 0,
            'total_trip_b' => isset($employee_data['total_trip_b']) ? (int)$employee_data['total_trip_b'] : 0,
            'food_allowance_days' => isset($employee_data['food_allowance_days']) ? (int)$employee_data['food_allowance_days'] : 0,
            'lsk_non_worked_days' => isset($employee_data['lsk_non_worked_days']) ? (int)$employee_data['lsk_non_worked_days'] : 0,
            'ln01_waived_days' => isset($employee_data['ln01_waived_days']) ? (int)$employee_data['ln01_waived_days'] : 0,
            'ln01_attendance_allowance_days' => isset($employee_data['ln01_attendance_allowance_days']) ? (int)$employee_data['ln01_attendance_allowance_days'] : 0,
            'total_absent_unpaid' => isset($employee_data['total_absent_unpaid']) ? (int)$employee_data['total_absent_unpaid'] : 0,
            'total_early_late' => isset($employee_data['total_early_late']) ? (int)$employee_data['total_early_late'] : 0,
            'total_rest_days_used' => isset($employee_data['total_rest_days_used']) ? (int)$employee_data['total_rest_days_used'] : 0,
            'total_late_only_count' => isset($employee_data['total_late_only_count']) ? (int)$employee_data['total_late_only_count'] : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
    }

    private function _build_month_lock_detail_rows($lock_id, $company_id, $branch_id, $employee_data, $window_first_day = null, $window_last_day = null)
    {
        $rows = array();
        $employee = isset($employee_data['employee']) ? $employee_data['employee'] : null;
        $dates = isset($employee_data['dates']) && is_array($employee_data['dates']) ? $employee_data['dates'] : array();
        $public_holidays = isset($employee_data['public_holidays']) ? (array)$employee_data['public_holidays'] : array();

        foreach ($dates as $d) {
            $date_value = isset($d->date) ? $d->date : null;
            if (!$date_value) {
                continue;
            }

            // The calculation may span the whole month-to-date (so the summary aggregate
            // is correct), but we only ever persist/overwrite detail rows that fall inside
            // the requested incremental window — older, already-locked days are left as-is.
            if ($window_first_day !== null && $window_last_day !== null) {
                if ($date_value < $window_first_day || $date_value > $window_last_day) {
                    continue;
                }
            }

            $clockings = isset($d->clockings) ? (array)$d->clockings : array();
            $first_clock = !empty($clockings) ? $clockings[0] : null;

            $late_time = $this->_pick_time_field($d, array('late_hours', 'late_time', 'late'));
            $early_out_time = $this->_pick_time_field($d, array('early_out', 'early_out_time'));
            if ($early_out_time === null && isset($d->short_hours)) {
                $early_out_time = $this->_normalize_time_value($d->short_hours);
            }
            $late_minutes = $this->_time_to_minutes($late_time);
            $early_out_minutes = $this->_time_to_minutes($early_out_time);

            $is_late = isset($d->is_late) ? (int)!!$d->is_late : 0;
            if ($late_time === null) {
                $is_late = 0;
            } elseif ($is_late === 0) {
                $is_late = 1;
            }

            $is_early_out = isset($d->is_early_out) ? (int)!!$d->is_early_out : 0;
            if ($early_out_time === null) {
                $is_early_out = 0;
            } elseif ($is_early_out === 0) {
                $is_early_out = 1;
            }

            $clock_in = null;
            $clock_out = null;
            if ($first_clock && isset($first_clock->clock_in)) {
                $clock_in = $first_clock->clock_in;
            }
            if ($first_clock && isset($first_clock->clock_out)) {
                $clock_out = $first_clock->clock_out;
            }

            $is_public_holiday = in_array($date_value, $public_holidays);

            $rows[] = array(
                'lock_id' => (int)$lock_id,
                'employee_id' => $employee ? (int)$employee->id : 0,
                'company_id' => (int)$company_id,
                'branch_id' => $branch_id,
                'date' => $date_value,
                'first_name' => $employee ? $employee->first_name : null,
                'special_id' => $employee ? $employee->special_id : null,
                'department' => $employee ? $employee->department : null,
                'position' => $employee ? $employee->position : null,
                'branch_name' => $employee ? $employee->branch : null,
                'day_name' => isset($d->day_name) ? $d->day_name : null,
                'is_rest_day' => isset($d->is_rest_day) ? (int)!!$d->is_rest_day : 0,
                'is_off_day' => isset($d->is_off_day) ? (int)!!$d->is_off_day : 0,
                'is_public_holiday' => $is_public_holiday ? 1 : 0,
                'is_leave' => isset($d->is_leave) ? (int)!!$d->is_leave : 0,
                'clock_in' => $clock_in,
                'clock_out' => $clock_out,
                'shift_name' => ($first_clock && isset($first_clock->name)) ? $first_clock->name : null,
                'shift_start' => ($first_clock && isset($first_clock->start_time)) ? $first_clock->start_time : null,
                'shift_end' => ($first_clock && isset($first_clock->end_time)) ? $first_clock->end_time : null,
                'shift_hours' => isset($d->shift_hours)
                    ? $d->shift_hours
                    : (($first_clock && isset($first_clock->shift_hours)) ? $first_clock->shift_hours : null),
                'is_overnight' => ($first_clock && isset($first_clock->overnight)) ? (int)($first_clock->overnight === 'Yes') : 0,
                'total_hours' => isset($d->total_hours) ? $d->total_hours : null,
                'work_hours' => isset($d->work_hours) ? $d->work_hours : null,
                'break_hours' => isset($d->break_hours) ? $d->break_hours : null,
                'is_present' => isset($d->is_worked) ? (int)!!$d->is_worked : 0,
                'is_absent' => isset($d->is_absent) ? (int)!!$d->is_absent : 0,
                'is_worked_rest_day' => isset($d->is_worked_rest_day) ? (int)!!$d->is_worked_rest_day : 0,
                'is_worked_off_day' => isset($d->is_worked_off_day) ? (int)!!$d->is_worked_off_day : 0,
                'is_worked_holiday' => isset($d->is_worked_holiday) ? (int)!!$d->is_worked_holiday : 0,
                'is_late' => $is_late,
                'late_time' => $late_time,
                'late_minutes' => $late_minutes,
                'late_time_deducted' => isset($d->late_hours_decimal) ? (float)$d->late_hours_decimal : 0,
                'void_late' => isset($d->void_late_in) ? (int)!!$d->void_late_in : 0,
                'is_early_out' => $is_early_out,
                'early_out_time' => $early_out_time,
                'early_out_minutes' => $early_out_minutes,
                'is_short_hours' => isset($d->is_short_hours) ? (int)!!$d->is_short_hours : 0,
                'short_hours' => isset($d->short_hours) ? $d->short_hours : null,
                'is_break_late' => isset($d->is_late_break) ? (int)!!$d->is_late_break : 0,
                'break_late_time' => isset($d->break_late_hours) ? $d->break_late_hours : null,
                'break_not_taken' => isset($d->break_not_taken) ? $d->break_not_taken : null,
                'is_ot_approved' => isset($d->is_ot_approved) ? (int)!!$d->is_ot_approved : 0,
                'overtime' => isset($d->overtime) ? $d->overtime : null,
                'overtime_deducted' => isset($d->overtime_deducted) ? (float)$d->overtime_deducted : 0,
                'overtime_ph_x2' => isset($d->overtime_ph_x2) ? (float)$d->overtime_ph_x2 : 0,
                'overtime_ph_x3' => isset($d->overtime_ph_x3) ? (float)$d->overtime_ph_x3 : 0,
                'overtime_rd' => isset($d->overtime_rd) ? (float)$d->overtime_rd : 0,
                'overtime_off' => isset($d->overtime_off) ? (float)$d->overtime_off : 0,
                'trip_a' => isset($d->trip_a) ? (int)!!$d->trip_a : 0,
                'trip_b' => isset($d->trip_b) ? (int)!!$d->trip_b : 0,
                'remark' => ($first_clock && isset($first_clock->remark)) ? $first_clock->remark : null,
                'staff_remark' => ($first_clock && isset($first_clock->staff_remark)) ? $first_clock->staff_remark : null,
                'clockings_json' => !empty($clockings) ? json_encode($clockings) : null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
        }

        return $rows;
    }

    private function _pick_time_field($day, $field_names)
    {
        foreach ($field_names as $field_name) {
            $raw_value = null;

            if (is_object($day) && isset($day->{$field_name})) {
                $raw_value = $day->{$field_name};
            } elseif (is_array($day) && array_key_exists($field_name, $day)) {
                $raw_value = $day[$field_name];
            }

            $normalized = $this->_normalize_time_value($raw_value);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        return null;
    }

    private function _normalize_time_value($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            $decimal = (float)$value;
            if ($decimal <= 0) {
                return null;
            }

            $hours = (int)floor($decimal);
            $minutes = (int)round(($decimal - $hours) * 60);
            if ($minutes === 60) {
                $hours += 1;
                $minutes = 0;
            }

            return sprintf('%02d:%02d', $hours, $minutes);
        }

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '' || $value === '00:00' || $value === '00:00:00') {
            return null;
        }

        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
            return substr($value, 0, 5);
        }

        return null;
    }

    private function _time_to_minutes($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            $decimal = (float)$value;
            if ($decimal <= 0) {
                return 0;
            }

            return (int)round($decimal * 60);
        }

        if (!is_string($value)) {
            return 0;
        }

        $value = trim($value);
        if ($value === '' || $value === '00:00' || $value === '00:00:00') {
            return 0;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $value, $matches)) {
            return ((int)$matches[1] * 60) + (int)$matches[2];
        }

        return 0;
    }

    private function _format_time_value_for_report($value)
    {
        if ($value === null || $value === '') {
            return '00:00';
        }

        if (is_numeric($value)) {
            $decimal = (float)$value;
            if ($decimal <= 0) {
                return '00:00';
            }

            $minutes = (int)round($decimal * 60);
            $hours = (int)floor($minutes / 60);
            $remaining_minutes = $minutes % 60;
            return sprintf('%02d:%02d', $hours, $remaining_minutes);
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '' || $value === '00:00:00') {
                return '00:00';
            }

            if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $value)) {
                return substr($value, 0, 5);
            }
        }

        return '00:00';
    }

    private function _get_shift_hours_backfill_expression()
    {
        $total_break_seconds_expr = "
            CASE
                WHEN s.break_duration IS NOT NULL AND s.break_duration <> '00:00:00'
                    THEN TIME_TO_SEC(s.break_duration)
                ELSE (
                    CASE WHEN s.consider_break_1 = 1 THEN TIME_TO_SEC(COALESCE(s.break_1, '00:00:00')) ELSE 0 END +
                    CASE WHEN s.consider_break_2 = 1 THEN TIME_TO_SEC(COALESCE(s.break_2, '00:00:00')) ELSE 0 END +
                    CASE WHEN s.consider_break_3 = 1 THEN TIME_TO_SEC(COALESCE(s.break_3, '00:00:00')) ELSE 0 END +
                    CASE WHEN s.consider_break_4 = 1 THEN TIME_TO_SEC(COALESCE(s.break_4, '00:00:00')) ELSE 0 END +
                    CASE WHEN s.consider_break_5 = 1 THEN TIME_TO_SEC(COALESCE(s.break_5, '00:00:00')) ELSE 0 END +
                    CASE WHEN s.consider_break_6 = 1 THEN TIME_TO_SEC(COALESCE(s.break_6, '00:00:00')) ELSE 0 END
                )
            END
        ";

        $raw_shift_seconds_expr = "
            CASE
                WHEN s.overnight = 'No' OR (s.overnight = 'Yes' AND s.same_day_overnight = 'same')
                    THEN TIME_TO_SEC(TIMEDIFF(s.end_time, s.start_time))
                ELSE
                    TIME_TO_SEC(TIMEDIFF(CONCAT(DATE_ADD(sd.date, INTERVAL 1 DAY), ' ', s.end_time), CONCAT(sd.date, ' ', s.start_time)))
            END
        ";

        return "
            SELECT TIME_FORMAT(
                SEC_TO_TIME(
                    GREATEST(({$raw_shift_seconds_expr}) - ({$total_break_seconds_expr}), 0)
                ),
                '%H:%i'
            )
            FROM shift_days sd
            INNER JOIN shifts s ON s.id = sd.shift_id
            WHERE sd.date = d.date
              AND s.company_id = ml.company_id
              AND FIND_IN_SET(CAST(d.employee_id AS CHAR), sd.employees) > 0
            ORDER BY sd.id DESC
            LIMIT 1
        ";
    }

    // ==================================================================
    //  HELPERS
    // ==================================================================

    /**
     * Log a message to stderr (visible in terminal / supervisor logs)
     * and also to CI's log file.
     */
    private function _log($message)
    {
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;
        fwrite(STDERR, $line . PHP_EOL);
        log_message('info', 'Queue_worker: ' . $message);
    }

    /**
     * This method is intended to be run once daily (e.g. via cron or supervisor)
     * to ensure that all companies with "auto lock" enabled have an active rolling
     * month lock for the current month. It will create new locks as needed and queue
     * month_lock_generate jobs to keep the data up-to-date.
     * php index.php queue_worker run_daily_sync
     */

    public function run_daily_sync()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        try {
            $this->_log('Starting daily sync for rolling month locks...');

            $yesterday   = date('Y-m-d', strtotime('-1 day'));
            $lock_year   = date('Y', strtotime($yesterday));
            $lock_month  = date('m', strtotime($yesterday));
            $month_start = date('Y-m-01', strtotime($yesterday)); // first day of the month

            $companies = $this->db
                ->select('id')
                ->from('companies')
                ->where('deleted_at IS NULL', null, false)
                ->where('is_auto_lock', 1)
                ->get()->result();

            $this->_log('Found ' . count($companies) . ' companies');

            foreach ($companies as $comp) {
                $cid = $comp->id;

                // Skip if a completed MANUAL lock already exists for this month
                $completed_lock = $this->db
                    ->where('company_id', $cid)
                    ->where('lock_year',  $lock_year)
                    ->where('lock_month', $lock_month)
                    ->where('status',     'completed')
                    ->where('is_auto_rolling', 0)
                    ->get('month_locks')->row();

                if ($completed_lock) {
                    $this->_log("  Company {$cid}: manual lock exists, skipping");
                    continue;
                }

                // Find existing rolling lock or create one
                $lock = $this->db
                    ->where('company_id', $cid)
                    ->where('lock_year',  $lock_year)
                    ->where('lock_month', $lock_month)
                    ->group_start()
                    ->where('status', 'rolling')
                    ->or_where('is_auto_rolling', 1)
                    ->group_end()
                    ->get('month_locks')->row();

                if (!$lock) {
                    $payload = array(
                        'company_id'     => $cid,
                        'branch_id'      => null,
                        'lock_year'      => $lock_year,
                        'lock_month'     => $lock_month,
                        'start_date'     => $month_start,
                        'end_date'       => date('Y-m-t', strtotime($yesterday)),
                        'status'         => 'rolling',
                        'is_auto_rolling' => 1,
                        'total_employees' => 0,
                        'total_records'   => 0,
                        'created_at'     => date('Y-m-d H:i:s'),
                        'updated_at'     => date('Y-m-d H:i:s')
                    );
                    $this->db->insert('month_locks', $payload);
                    $lock_id = $this->db->insert_id();
                } else {
                    $lock_id = $lock->id;
                }

                $job_payload = array(
                    'lock_id'    => $lock_id,
                    'company_id' => $cid,
                    // 'from_date'  => $month_start,   // ← 1st of month, e.g. 2025-05-01
                    'from_date'  => $yesterday,
                    'to_date'    => $yesterday       // ← yesterday,     e.g. 2025-05-13
                );

                $job_id = sprintf(
                    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0x0fff) | 0x4000,
                    mt_rand(0, 0x3fff) | 0x8000,
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff)
                );

                // Uses 'month_lock_generate' — the same job type as a manual lock.
                // _generate_month_lock() will clear old data and recalculate the
                // full period (month_start -> yesterday) from scratch each night.
                $this->queue->create_job($job_id, 'month_lock_generate', $job_payload, 3);
                $this->_log("  Queued month_lock_generate for Company {$cid}, Lock {$lock_id}, {$month_start} -> {$yesterday}");
            }

            $this->_log('Daily sync queuing complete.');
        } catch (\Throwable $e) {
            $this->_log('CRASH: ' . $e->getMessage());
        }
    }
}