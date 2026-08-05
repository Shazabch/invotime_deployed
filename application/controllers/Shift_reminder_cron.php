<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shift_reminder_cron extends CI_Controller
{
    private $cron_code = 'shiftreminder2026';

    const UPCOMING_WINDOW_HOURS = 24;

    public function __construct()
    {
        parent::__construct();

        set_sql_mode();

        // Allow CLI requests to bypass authentication
        if (!$this->input->is_cli_request()) {
            // For web requests, require logged-in user
            if (is_null(get_user())) {
                redirect('welcome');
            }
        }
    }

    public function index()
    {
        date_default_timezone_set('Asia/Kuala_Lumpur');

        $code = $this->input->get('code');
        if (!$this->input->is_cli_request() && $code !== $this->cron_code) {
            $this->log_shift_reminder('warning', 'Access denied for shift reminder cron endpoint.', array(
                'is_cli' => $this->input->is_cli_request(),
            ));
            die('Access denied!');
        }

        $counts = $this->run_shift_reminders_job();

        $debug = (string)$this->input->get('debug') === '1';
        if ($debug) {
            $counts['diagnostics'] = $this->get_filter_diagnostics();
        }

        echo json_encode($counts);
    }

    public function manual()
    {
        date_default_timezone_set('Asia/Kuala_Lumpur');

        // For web requests, validate user is logged in
        if (!$this->input->is_cli_request()) {
            $current_user = get_user();
            if (is_null($current_user)) {
                $this->output
                    ->set_status_header(403)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'ok' => false,
                        'message' => 'Authentication required. Please log in.',
                    )));
                return;
            }
        }

        $simulate = (string)$this->input->get_post('simulate', true) === '1';

        if ($this->input->is_cli_request()) {
            $dashboard_company_id = (int)$this->input->get('company_id');
            if ($dashboard_company_id <= 0) {
                $dashboard_company_id = 0;
            }
        } else {
            $current_user = get_user();
            $dashboard_company_id = (int)$current_user['company_id'];
        }

        if (!$this->input->is_cli_request() && $dashboard_company_id <= 0) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'ok' => false,
                    'message' => 'User company context is required.',
                )));
            return;
        }

        $lookback_minutes = (int)$this->input->get_post('lookback_minutes', true);
        $send_limit = (int)$this->input->get_post('send_limit', true);
        if ($lookback_minutes <= 0) {
            $lookback_minutes = 120;
        }
        $lookback_minutes = min(1440, max(5, $lookback_minutes));

        if ($send_limit <= 0) {
            $send_limit = 50;
        }
        $send_limit = min(500, max(1, $send_limit));

        $counts = $this->run_shift_reminders_job(array(
            'dry_run' => $simulate,
            'lookback_minutes' => $lookback_minutes,
            'send_limit' => $simulate ? 0 : $send_limit,
            'write_notification_rows' => false,
            'company_id' => $dashboard_company_id,
        ));

        if ($simulate) {
            $preview_rows = $this->get_upcoming_shift_reminder_rows(
                self::UPCOMING_WINDOW_HOURS,
                $dashboard_company_id,
                '',
                'upcoming_24h',
                $lookback_minutes
            );

            $preview_rows = array_slice($preview_rows, 0, 10);
            $preview = array();
            foreach ($preview_rows as $row) {
                $preview[] = array(
                    'due_at' => isset($row['due_at']) ? (string)$row['due_at'] : '',
                    'employee_name' => isset($row['employee_name']) ? (string)$row['employee_name'] : '',
                    'company_name' => isset($row['company_name']) ? (string)$row['company_name'] : '',
                    'shift_name' => isset($row['shift_name']) ? (string)$row['shift_name'] : '',
                    'reminder_type' => isset($row['reminder_type']) ? (string)$row['reminder_type'] : '',
                );
            }

            $counts['simulation_upcoming_preview'] = $preview;
        }

        $counts['manual_mode'] = $simulate ? 'simulation' : 'send';
        $counts['send_limit'] = $simulate ? 0 : $send_limit;
        $counts['manual_triggered_at'] = date('Y-m-d H:i:s');

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($counts));
    }

    public function manual_test()
    {
        date_default_timezone_set('Asia/Kuala_Lumpur');

        // For web requests, validate user is logged in
        if (!$this->input->is_cli_request()) {
            $current_user = get_user();
            if (is_null($current_user)) {
                $this->output
                    ->set_status_header(403)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'ok' => false,
                        'message' => 'Authentication required. Please log in.',
                    )));
                return;
            }
        }

        $employee_id = (int)$this->input->get_post('employee_id', true);

        if ($this->input->is_cli_request()) {
            $dashboard_company_id = (int)$this->input->get('company_id');
            if ($dashboard_company_id <= 0) {
                $dashboard_company_id = 0;
            }
        } else {
            $current_user = get_user();
            $dashboard_company_id = (int)$current_user['company_id'];
        }

        if (!$this->input->is_cli_request() && $dashboard_company_id <= 0) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'ok' => false,
                    'message' => 'User company context is required.',
                )));
            return;
        }

        if ($employee_id <= 0) {
            $this->output
                ->set_status_header(422)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'ok' => false,
                    'message' => 'Valid employee ID is required for test delivery.',
                )));
            return;
        }

        $event_type = strtolower(trim((string)$this->input->get_post('event_type', true)));
        if (!in_array($event_type, array('start', 'end'), true)) {
            $event_type = 'start';
        }

        $employee = $this->db
            ->select('e.id, e.first_name, e.last_name, e.special_id, e.fcm_token, e.company_id, c.name as company_name')
            ->from('employees e')
            ->join('companies c', 'c.id = e.company_id', 'left')
            ->where('e.id', $employee_id)
            ->limit(1)
            ->get()
            ->row();

        if (!$this->input->is_cli_request() && $employee && (int)$employee->company_id !== (int)$dashboard_company_id) {
            $employee = null;
        }

        if (!$employee) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'ok' => false,
                    'message' => 'Employee not found.',
                )));
            return;
        }

        if (!isset($employee->fcm_token) || trim((string)$employee->fcm_token) === '') {
            $this->output
                ->set_status_header(422)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'ok' => false,
                    'message' => 'Selected employee does not have an FCM token.',
                    'employee' => array(
                        'id' => (int)$employee->id,
                        'name' => trim((string)$employee->first_name . ' ' . (string)$employee->last_name),
                        'special_id' => (string)$employee->special_id,
                    ),
                )));
            return;
        }

        $template_override = $this->get_shift_reminder_template_override();

        $test_shift_row = (object)array(
            'shift_name' => 'Template Test Shift',
            'company_name' => (string)$employee->company_name,
            'shift_id' => 0,
            'shift_date' => date('Y-m-d'),
            'company_id' => (int)$employee->company_id,
        );

        $test_due = array(
            'type' => $event_type,
            'minutes' => 0,
            'target_at' => date('Y-m-d H:i:s'),
            'event_label' => $event_type === 'end' ? 'Ending Soon' : 'Starting Soon',
            'event_action' => $event_type === 'end' ? 'ends' : 'starts',
            'shift_time' => date('h:i A'),
        );

        $message_payload = $this->compose_shift_reminder_message(
            $test_shift_row,
            $test_due,
            $employee,
            $template_override
        );

        $service_account_file = FCPATH . 'invotime-399613-firebase-adminsdk-qlq1j-61727bd060.json';
        $project_id = 'invotime-399613';
        $access_token = $this->get_firebase_access_token($service_account_file);

        if (!$access_token) {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'ok' => false,
                    'message' => 'Unable to obtain Firebase access token.',
                )));
            return;
        }

        $announcement_id = $this->create_shift_reminder_announcement_entry(
            (int)$employee->company_id,
            (int)$employee->id,
            $message_payload['title'],
            $message_payload['body'],
            date('Y-m-d H:i:s')
        );

        if ($announcement_id <= 0) {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'ok' => false,
                    'message' => 'Unable to create shift reminder announcement.',
                )));
            return;
        }

        $payload_data = array(
            'type' => 'Shiftreminder',
            'announcement_id' => (string)$announcement_id,
        );

        $send_result = $this->send_shift_reminder_to_token(
            $project_id,
            $access_token,
            $employee->fcm_token,
            $message_payload['title'],
            $message_payload['body'],
            $payload_data
        );

        $response = array(
            'ok' => (bool)$send_result['success'],
            'sent' => (bool)$send_result['success'],
            'manual_mode' => 'test_delivery',
            'event_type' => $event_type,
            'employee' => array(
                'id' => (int)$employee->id,
                'name' => trim((string)$employee->first_name . ' ' . (string)$employee->last_name),
                'special_id' => (string)$employee->special_id,
                'company_name' => (string)$employee->company_name,
            ),
            'notification' => array(
                'title' => $message_payload['title'],
                'body' => $message_payload['body'],
            ),
            'sent_at' => date('Y-m-d H:i:s'),
        );

        if (!$send_result['success']) {
            $response['message'] = 'Test notification failed to send.';
            $response['error'] = (string)$send_result['error'];
            if (!empty($send_result['hint'])) {
                $response['hint'] = (string)$send_result['hint'];
            }
            if (isset($send_result['http_code'])) {
                $response['http_code'] = (int)$send_result['http_code'];
            }

            if (isset($send_result['http_code']) && (int)$send_result['http_code'] === 404) {
                $response['message'] = 'Test notification failed because the FCM token is stale. The token was not changed.';
            }

            $this->delete_shift_reminder_announcement_entry(
                (int)$announcement_id,
                (int)$employee->company_id,
                (int)$employee->id
            );
        } else {
            $this->insert_shift_push_notification_row(
                (int)$employee->company_id,
                (int)$employee->id,
                (string)$employee->fcm_token,
                $message_payload['title'],
                $message_payload['body'],
                array(
                    'type' => 'Shiftreminder',
                    'announcement_id' => (string)$announcement_id,
                )
            );
            $response['in_app_created'] = true;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function upcoming()
    {
        date_default_timezone_set('Asia/Kuala_Lumpur');

        if ($this->input->is_cli_request()) {
            // CLI requests can specify company_id as parameter
            $company_id = (int)$this->input->get('company_id');
            if ($company_id <= 0) {
                $company_id = null;
            }
        } else {
            // Web requests must be logged in - get company from session
            $current_user = get_user();
            $company_id = (int)$current_user['company_id'];
        }

        $reminder_type = strtolower(trim((string)$this->input->get('reminder_type')));
        if (!in_array($reminder_type, array('start', 'end'), true)) {
            $reminder_type = '';
        }

        $view_mode = strtolower(trim((string)$this->input->get('view_mode')));
        if (!in_array($view_mode, array('upcoming_24h', 'due_window'), true)) {
            $view_mode = 'upcoming_24h';
        }

        $window_minutes = (int)$this->input->get('window_minutes');
        if ($window_minutes <= 0) {
            $window_minutes = 120;
        }
        $window_minutes = min(1440, max(5, $window_minutes));

        $page = max(1, (int)$this->input->get('page'));
        $per_page = (int)$this->input->get('per_page');
        if ($per_page <= 0) {
            $per_page = 50;
        }
        $per_page = min(200, max(10, $per_page));

        $rows = $this->get_upcoming_shift_reminder_rows(
            self::UPCOMING_WINDOW_HOURS,
            $company_id,
            $reminder_type,
            $view_mode,
            $window_minutes
        );
        $total_rows = count($rows);
        $total_pages = $total_rows > 0 ? (int)ceil($total_rows / $per_page) : 1;
        $page = min($page, $total_pages);
        $offset = ($page - 1) * $per_page;
        $rows_page = array_slice($rows, $offset, $per_page);

        $filters = array(
            'company_id' => $company_id,
            'reminder_type' => $reminder_type,
            'view_mode' => $view_mode,
            'window_minutes' => $window_minutes,
        );

        $data = array(
            'pageTitle' => 'Shift Reminder Dashboard',
            'active_menu' => 'announcements',
            'rows' => $rows_page,
            'total_rows' => $total_rows,
            'window_hours' => self::UPCOMING_WINDOW_HOURS,
            'now' => date('Y-m-d H:i:s'),
            'company_scope_name' => $this->get_company_name_by_id((int)$company_id),
            'company_options' => $this->get_shift_reminder_company_options(),
            'filters' => $filters,
            'template' => $this->get_default_shift_reminder_template((int)$company_id),
            'pagination' => array(
                'page' => $page,
                'per_page' => $per_page,
                'total_pages' => $total_pages,
            ),
        );

        // For web requests, render with full layout (header/sidebar/footer)
        if (!$this->input->is_cli_request()) {
            $this->load->view('header', $data);
            $data['menus'] = get_menus();
            $this->load->view('sidebar', $data);
            $this->load->view('shift_reminder_upcoming', $data);
            $this->load->view('footer');
            return;
        }

        // For CLI requests, render just the view
        $this->load->view('shift_reminder_upcoming', $data);
    }

    public function export_csv()
    {
        date_default_timezone_set('Asia/Kuala_Lumpur');

        $code = $this->input->get('code');
        if (!$this->input->is_cli_request() && $code !== $this->cron_code) {
            $this->log_shift_reminder('warning', 'Access denied for shift reminder CSV export endpoint.', array(
                'is_cli' => $this->input->is_cli_request(),
            ));
            show_error('Access denied!', 403);
            return;
        }

        if ($this->input->is_cli_request()) {
            $company_id = (int)$this->input->get('company_id');
            if ($company_id <= 0) {
                $company_id = null;
            }
        } else {
            $current_user = get_user();
            $company_id = (int)$current_user['company_id'];
            if ($company_id <= 0) {
                show_error('Logged-in company context is required.', 403);
                return;
            }
        }

        $reminder_type = strtolower(trim((string)$this->input->get('reminder_type')));
        if (!in_array($reminder_type, array('start', 'end'), true)) {
            $reminder_type = '';
        }

        $view_mode = strtolower(trim((string)$this->input->get('view_mode')));
        if (!in_array($view_mode, array('upcoming_24h', 'due_window'), true)) {
            $view_mode = 'upcoming_24h';
        }

        $window_minutes = (int)$this->input->get('window_minutes');
        if ($window_minutes <= 0) {
            $window_minutes = 120;
        }
        $window_minutes = min(1440, max(5, $window_minutes));

        $rows = $this->get_upcoming_shift_reminder_rows(
            self::UPCOMING_WINDOW_HOURS,
            $company_id,
            $reminder_type,
            $view_mode,
            $window_minutes
        );

        $filename = 'shift-reminders-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        if ($output === false) {
            show_error('Unable to generate CSV.', 500);
            return;
        }

        fputcsv($output, array(
            'Due At',
            'Company',
            'Employee ID',
            'Employee Name',
            'Staff ID',
            'Shift ID',
            'Shift Name',
            'Reminder Type',
            'Notification Title',
            'Target Label',
            'Minutes Before',
            'Remaining Seconds',
            'Has FCM Token',
        ));

        foreach ($rows as $row) {
            fputcsv($output, array(
                isset($row['due_at']) ? $row['due_at'] : '',
                isset($row['company_name']) ? $row['company_name'] : '',
                isset($row['employee_id']) ? $row['employee_id'] : '',
                isset($row['employee_name']) ? $row['employee_name'] : '',
                isset($row['special_id']) ? $row['special_id'] : '',
                isset($row['shift_id']) ? $row['shift_id'] : '',
                isset($row['shift_name']) ? $row['shift_name'] : '',
                isset($row['reminder_type']) ? $row['reminder_type'] : '',
                isset($row['notification_title']) ? $row['notification_title'] : '',
                isset($row['target_label']) ? $row['target_label'] : '',
                isset($row['reminder_minutes']) ? $row['reminder_minutes'] : '',
                isset($row['remaining_seconds']) ? $row['remaining_seconds'] : '',
                isset($row['has_fcm_token']) ? $row['has_fcm_token'] : '',
            ));
        }

        fclose($output);
    }

    public function template()
    {
        date_default_timezone_set('Asia/Kuala_Lumpur');

        // For web requests, validate user is logged in
        if (!$this->input->is_cli_request()) {
            $current_user = get_user();
            if (is_null($current_user)) {
                $this->output
                    ->set_status_header(403)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'ok' => false,
                        'message' => 'Authentication required. Please log in.',
                    )));
                return;
            }
        }

        $method = strtoupper((string)$this->input->method(true));

        if ($this->input->is_cli_request()) {
            $dashboard_company_id = (int)$this->input->get('company_id');
            if ($dashboard_company_id <= 0) {
                $dashboard_company_id = 0;
            }
        } else {
            $current_user = get_user();
            $dashboard_company_id = (int)$current_user['company_id'];
        }

        if (!$this->input->is_cli_request() && $dashboard_company_id <= 0) {
            $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'ok' => false,
                    'message' => 'User company context is required.',
                )));
            return;
        }

        if ($method === 'POST') {
            $title = trim((string)$this->input->post('template_title', true));
            $body = trim((string)$this->input->post('template_body', true));

            if ($title === '' || $body === '') {
                $this->output
                    ->set_status_header(422)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'ok' => false,
                        'message' => 'Template title and body are required.',
                    )));
                return;
            }

            $saved = $this->save_shift_reminder_template($title, $body, $dashboard_company_id);
            if (!$saved) {
                $this->output
                    ->set_status_header(500)
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'ok' => false,
                        'message' => 'Unable to save template right now.',
                    )));
                return;
            }
        }

        $template = $this->get_default_shift_reminder_template($dashboard_company_id);
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(array(
                'ok' => true,
                'template' => $template,
            )));
    }

    private function get_upcoming_shift_reminder_rows($window_hours = 24, $company_id = null, $reminder_type = '', $view_mode = 'upcoming_24h', $window_minutes = 120)
    {
        $window_hours = (int)$window_hours;
        if ($window_hours <= 0) {
            $window_hours = 24;
        }

        $company_filter_sql = '';
        if (!empty($company_id)) {
            $company_filter_sql = ' AND c.id = ' . (int)$company_id;
        }

        $now = new DateTime();
        $window_end = clone $now;
        $window_end->modify('+' . $window_hours . ' hours');
        $window_start = clone $now;
        $window_start->modify('-' . (int)$window_minutes . ' minutes');

        $shift_rows = $this->db->query("SELECT
                sd.date as shift_date,
                sd.shift_id,
                sd.employees,
                s.name as shift_name,
                s.start_time,
                s.end_time,
                s.overnight,
                s.same_day_overnight,
                c.id as company_id,
                c.name as company_name,
                c.shift_reminder_status,
                c.shift_reminder_minutes
            FROM shift_days sd
            INNER JOIN shifts s ON s.id = sd.shift_id
            INNER JOIN companies c ON c.id = s.company_id
            WHERE sd.date BETWEEN DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
              AND sd.employees IS NOT NULL
              AND sd.employees <> ''
              AND s.is_leave = 'no'
              AND (s.active = 1 OR s.active IS NULL)
              AND c.shift_reminder_status = 1
              AND c.shift_reminder_minutes > 0
              $company_filter_sql")->result();

        $result_rows = array();
        if (empty($shift_rows)) {
            return $result_rows;
        }

        $company_ids = array();
        foreach ($shift_rows as $shift_row) {
            $company_ids[(int)$shift_row->company_id] = true;
        }

        $employees_by_company = $this->get_active_fcm_employees_by_company(array_keys($company_ids));

        foreach ($shift_rows as $shift_row) {
            $reminder_minutes = (int)$shift_row->shift_reminder_minutes;

            $shift_start = DateTime::createFromFormat('Y-m-d H:i:s', $shift_row->shift_date . ' ' . $shift_row->start_time);
            if (!$shift_start) {
                continue;
            }

            $is_overnight = strtoupper((string)$shift_row->overnight) === 'YES';
            $is_same_day_overnight = strtolower((string)$shift_row->same_day_overnight) === 'same';
            $shift_end_date = $shift_row->shift_date;
            if ($is_overnight && !$is_same_day_overnight) {
                $shift_end_date = date('Y-m-d', strtotime($shift_row->shift_date . ' +1 day'));
            }

            $shift_end = DateTime::createFromFormat('Y-m-d H:i:s', $shift_end_date . ' ' . $shift_row->end_time);
            if (!$shift_end) {
                continue;
            }

            $events = array(
                array(
                    'type' => 'start',
                    'title' => 'Shift starting soon',
                    'target_at' => clone $shift_start,
                    'target_label' => 'Start at ' . date('d/m/Y h:i A', strtotime($shift_start->format('Y-m-d H:i:s'))),
                ),
                array(
                    'type' => 'end',
                    'title' => 'Shift ending soon',
                    'target_at' => clone $shift_end,
                    'target_label' => 'End at ' . date('d/m/Y h:i A', strtotime($shift_end->format('Y-m-d H:i:s'))),
                ),
            );

            if ($reminder_type === 'start') {
                $events = array($events[0]);
            } elseif ($reminder_type === 'end') {
                $events = array($events[1]);
            }

            $employee_ids = array_values(array_filter(array_map('intval', explode(',', (string)$shift_row->employees))));
            if (empty($employee_ids)) {
                continue;
            }

            $company_employees = isset($employees_by_company[(int)$shift_row->company_id])
                ? $employees_by_company[(int)$shift_row->company_id]
                : array();

            if (empty($company_employees)) {
                continue;
            }

            foreach ($events as $event) {
                $due_at = clone $event['target_at'];
                $due_at->modify('-' . $reminder_minutes . ' minutes');

                if ($view_mode === 'due_window') {
                    if (!($due_at > $window_start && $due_at <= $now)) {
                        continue;
                    }
                } else {
                    if ($due_at <= $now || $due_at > $window_end) {
                        continue;
                    }
                }

                $remaining_seconds = $due_at->getTimestamp() - $now->getTimestamp();

                foreach ($employee_ids as $assigned_employee_id) {
                    $assigned_employee_id = (int)$assigned_employee_id;
                    if (!isset($company_employees[$assigned_employee_id])) {
                        continue;
                    }

                    $employee = $company_employees[$assigned_employee_id];
                    $result_rows[] = array(
                        'due_at' => $due_at->format('Y-m-d H:i:s'),
                        'due_date' => $due_at->format('Y-m-d'),
                        'due_time' => $due_at->format('h:i A'),
                        'company_name' => (string)$shift_row->company_name,
                        'employee_id' => (int)$employee->id,
                        'employee_name' => trim((string)$employee->first_name . ' ' . (string)$employee->last_name),
                        'special_id' => (string)$employee->special_id,
                        'shift_id' => (int)$shift_row->shift_id,
                        'shift_name' => (string)$shift_row->shift_name,
                        'reminder_type' => (string)$event['type'],
                        'notification_title' => (string)$event['title'],
                        'target_label' => (string)$event['target_label'],
                        'reminder_minutes' => $reminder_minutes,
                        'remaining_seconds' => $remaining_seconds,
                        'remaining_label' => $this->format_remaining_seconds($remaining_seconds),
                        'has_fcm_token' => 'Yes',
                    );
                }
            }
        }

        usort($result_rows, function ($a, $b) {
            $left = strtotime($a['due_at']);
            $right = strtotime($b['due_at']);
            if ($left === $right) {
                return strcmp((string)$a['employee_name'], (string)$b['employee_name']);
            }
            return $left - $right;
        });

        return $result_rows;
    }

    private function get_active_fcm_employees_by_company(array $company_ids)
    {
        if (empty($company_ids)) {
            return array();
        }

        $employees = $this->db
            ->select('id, company_id, first_name, last_name, special_id, fcm_token')
            ->from('employees')
            ->where_in('company_id', $company_ids)
            ->where('employee_status', 'active')
            ->where('fcm_token IS NOT NULL', null, false)
            ->where("TRIM(fcm_token) <> ''", null, false)
            ->where('deleted_at is null')
            ->get()
            ->result();

        $map = array();
        foreach ($employees as $employee) {
            $cid = (int)$employee->company_id;
            $eid = (int)$employee->id;

            if (!isset($map[$cid])) {
                $map[$cid] = array();
            }

            $map[$cid][$eid] = $employee;
        }

        return $map;
    }

    private function get_shift_reminder_company_options()
    {
        if ($this->input->is_cli_request()) {
            return $this->db
                ->select('id, name')
                ->from('companies')
                ->where('shift_reminder_status', 1)
                ->where('shift_reminder_minutes >', 0)
                ->order_by('name', 'ASC')
                ->get()
                ->result_array();
        }

        $current_user = get_user();
        $company_id = (int)$current_user['company_id'];

        if ($company_id > 0) {
            return $this->db
                ->select('id, name')
                ->from('companies')
                ->where('id', $company_id)
                ->where('shift_reminder_status', 1)
                ->where('shift_reminder_minutes >', 0)
                ->order_by('name', 'ASC')
                ->get()
                ->result_array();
        }

        return $this->db
            ->select('id, name')
            ->from('companies')
            ->where('shift_reminder_status', 1)
            ->where('shift_reminder_minutes >', 0)
            ->order_by('name', 'ASC')
            ->get()
            ->result_array();
    }

    private function get_company_name_by_id($company_id)
    {
        $company_id = (int)$company_id;
        if ($company_id <= 0) {
            return '';
        }

        $row = $this->db
            ->select('name')
            ->from('companies')
            ->where('id', $company_id)
            ->limit(1)
            ->get()
            ->row();

        return $row && isset($row->name) ? (string)$row->name : '';
    }

    private function format_remaining_seconds($seconds)
    {
        $seconds = max(0, (int)$seconds);

        $days = (int)floor($seconds / 86400);
        $hours = (int)floor(($seconds % 86400) / 3600);
        $minutes = (int)floor(($seconds % 3600) / 60);

        $parts = array();
        if ($days > 0) {
            $parts[] = $days . 'd';
        }
        if ($hours > 0 || $days > 0) {
            $parts[] = $hours . 'h';
        }
        $parts[] = $minutes . 'm';

        return implode(' ', $parts);
    }

    private function get_filter_diagnostics()
    {
        $diagnostics = array();

        $diagnostics['shift_days_today_or_yesterday'] = (int)$this->db->query("SELECT COUNT(*) AS c
            FROM shift_days sd
            WHERE sd.date IN (CURDATE(), DATE_SUB(CURDATE(), INTERVAL 1 DAY))")
            ->row()->c;

        $diagnostics['with_employees'] = (int)$this->db->query("SELECT COUNT(*) AS c
            FROM shift_days sd
            WHERE sd.date IN (CURDATE(), DATE_SUB(CURDATE(), INTERVAL 1 DAY))
              AND sd.employees IS NOT NULL
              AND sd.employees <> ''")
            ->row()->c;

        $diagnostics['active_non_leave_shifts'] = (int)$this->db->query("SELECT COUNT(*) AS c
            FROM shift_days sd
            INNER JOIN shifts s ON s.id = sd.shift_id
            WHERE sd.date IN (CURDATE(), DATE_SUB(CURDATE(), INTERVAL 1 DAY))
              AND sd.employees IS NOT NULL
              AND sd.employees <> ''
              AND s.is_leave = 'no'
              AND (s.active = 1 OR s.active IS NULL)")
            ->row()->c;

        $diagnostics['company_status_on'] = (int)$this->db->query("SELECT COUNT(*) AS c
            FROM shift_days sd
            INNER JOIN shifts s ON s.id = sd.shift_id
            INNER JOIN companies c ON c.id = s.company_id
            WHERE sd.date IN (CURDATE(), DATE_SUB(CURDATE(), INTERVAL 1 DAY))
              AND sd.employees IS NOT NULL
              AND sd.employees <> ''
              AND s.is_leave = 'no'
              AND (s.active = 1 OR s.active IS NULL)
              AND c.shift_reminder_status = 1")
            ->row()->c;

                $diagnostics['minutes_gte_zero'] = (int)$this->db->query("SELECT COUNT(*) AS c
            FROM shift_days sd
            INNER JOIN shifts s ON s.id = sd.shift_id
            INNER JOIN companies c ON c.id = s.company_id
            WHERE sd.date IN (CURDATE(), DATE_SUB(CURDATE(), INTERVAL 1 DAY))
              AND sd.employees IS NOT NULL
              AND sd.employees <> ''
              AND s.is_leave = 'no'
              AND (s.active = 1 OR s.active IS NULL)
              AND c.shift_reminder_status = 1
                            AND c.shift_reminder_minutes >= 0")
            ->row()->c;

        return $diagnostics;
    }

    private function run_shift_reminders_job($options = array())
    {
        $dry_run = isset($options['dry_run']) ? (bool)$options['dry_run'] : false;
        $lookback_minutes = isset($options['lookback_minutes']) ? (int)$options['lookback_minutes'] : 5;
        $send_limit = isset($options['send_limit']) ? (int)$options['send_limit'] : 0;
        $write_notification_rows = isset($options['write_notification_rows']) ? (bool)$options['write_notification_rows'] : true;
        $company_id = isset($options['company_id']) ? (int)$options['company_id'] : 0;
        if ($lookback_minutes <= 0) {
            $lookback_minutes = 5;
        }
        $lookback_minutes = min(1440, max(1, $lookback_minutes));
        $send_limit = max(0, $send_limit);

        $now = new DateTime();
        $window_start = clone $now;
        $window_start->modify('-' . $lookback_minutes . ' minutes');
        $now_minute = $now->format('Y-m-d H:i');
        $template_override = $this->get_shift_reminder_template_override();

        $this->log_shift_reminder('info', 'Shift reminder cron started.', array(
            'window_start' => $window_start->format('Y-m-d H:i'),
            'now_minute' => $now_minute,
            'dry_run' => $dry_run ? 1 : 0,
            'lookback_minutes' => $lookback_minutes,
            'send_limit' => $send_limit,
            'write_notification_rows' => $write_notification_rows ? 1 : 0,
            'company_id' => $company_id,
        ));

        $this->db
            ->select('id, company_id, first_name, fcm_token')
            ->from('employees')
            ->where('employee_status', 'active')
            ->where('deleted_at is null')
            ->where('fcm_token IS NOT NULL', null, false)
            ->where("TRIM(fcm_token) <> ''", null, false);
        if ($company_id > 0) {
            $this->db->where('company_id', $company_id);
        }

        $fcm_employees = $this->db->get()->result();

        $employee_map = array();
        $fcm_employee_ids = array();
        foreach ($fcm_employees as $employee) {
            $employee_id = (int)$employee->id;
            $employee_map[$employee_id] = $employee;
            $fcm_employee_ids[] = $employee_id;
        }

        if (empty($fcm_employee_ids)) {
            $counts = array(
                'checked' => 0,
                'shift_rows_time_matched' => 0,
                'reminder_events_matched' => 0,
                'assigned_users_total' => 0,
                'assigned_users_unique' => 0,
                'eligible_users_total' => 0,
                'eligible_users_unique' => 0,
                'eligible_users_with_token_total' => 0,
                'notification_rows_created' => 0,
                'notification_rows_skipped_duplicate' => 0,
                'simulated_notification_rows' => 0,
                'simulated_sendable' => 0,
                'stopped_early' => 0,
                'stop_reason' => '',
                'due' => 0,
                'sent' => 0,
                'failed' => 0,
                'skipped_no_token' => 0,
            );

            $this->log_shift_reminder('info', 'Shift reminder cron finished.', $counts);
            return $counts;
        }

        $company_filter_sql = '';
        if ($company_id > 0) {
            $company_filter_sql = ' AND c.id = ' . $company_id;
        }

        // OPTIMIZED QUERY: Get shifts for today and yesterday
        // Performance: <1 second (was 30-120s with 50K OR conditions)
        // Note: Employee filtering moved to PHP (per-shift basis) for scalability
        $shift_rows = $this->db->query("SELECT
                sd.date as shift_date,
                sd.shift_id,
                sd.employees,
                s.name as shift_name,
                s.start_time,
                s.end_time,
                s.overnight,
                s.same_day_overnight,
                c.id as company_id,
                c.name as company_name,
                c.shift_reminder_status,
                c.shift_reminder_minutes
            FROM shift_days sd
            INNER JOIN shifts s ON s.id = sd.shift_id
            INNER JOIN companies c ON c.id = s.company_id
            WHERE sd.date IN (CURDATE(), DATE_SUB(CURDATE(), INTERVAL 1 DAY))
              AND sd.employees IS NOT NULL
              AND sd.employees <> ''
              AND s.is_leave = 'no'
              AND (s.active = 1 OR s.active IS NULL)
              AND c.shift_reminder_status = 1
              AND c.shift_reminder_minutes > 0
              $company_filter_sql")->result();

        $service_account_file = FCPATH . 'invotime-399613-firebase-adminsdk-qlq1j-61727bd060.json';
        $project_id = 'invotime-399613';
        $access_token = null;

        $counts = array(
            'checked' => count($shift_rows),
            'shift_rows_time_matched' => 0,
            'reminder_events_matched' => 0,
            'assigned_users_total' => 0,
            'assigned_users_unique' => 0,
            'eligible_users_total' => 0,
            'eligible_users_unique' => 0,
            'eligible_users_with_token_total' => 0,
            'notification_rows_created' => 0,
            'notification_rows_skipped_duplicate' => 0,
            'simulated_notification_rows' => 0,
            'simulated_sendable' => 0,
            'stopped_early' => 0,
            'stop_reason' => '',
            'due' => 0,
            'sent' => 0,
            'failed' => 0,
            'skipped_no_token' => 0,
        );

        $assigned_unique_users = array();
        $eligible_unique_users = array();
        $stop_processing = false;

        foreach ($shift_rows as $shift_row) {
            if ($stop_processing) {
                break;
            }

            $reminder_minutes = (int)$shift_row->shift_reminder_minutes;

            if ($reminder_minutes < 0) {
                continue;
            }

            $shift_start = DateTime::createFromFormat('Y-m-d H:i:s', $shift_row->shift_date . ' ' . $shift_row->start_time);
            if (!$shift_start) {
                continue;
            }

            $is_overnight = strtoupper((string)$shift_row->overnight) === 'YES';
            $is_same_day_overnight = strtolower((string)$shift_row->same_day_overnight) === 'same';
            $shift_end_date = $shift_row->shift_date;
            if ($is_overnight && !$is_same_day_overnight) {
                $shift_end_date = date('Y-m-d', strtotime($shift_row->shift_date . ' +1 day'));
            }

            $shift_end = DateTime::createFromFormat('Y-m-d H:i:s', $shift_end_date . ' ' . $shift_row->end_time);
            if (!$shift_end) {
                continue;
            }

            $due_reminders = array();

            $start_due = clone $shift_start;
            $start_due->modify('-' . (int)$reminder_minutes . ' minutes');
            if ($this->is_due_in_window($start_due, $window_start, $now)) {
                $due_reminders[] = array(
                    'type' => 'start',
                    'minutes' => (int)$reminder_minutes,
                    'target_at' => $shift_start->format('Y-m-d H:i:s'),
                    'event_label' => 'Starting Soon',
                    'event_action' => 'starts',
                    'shift_time' => date('h:i A', strtotime($shift_row->start_time)),
                );
            }

            $end_due = clone $shift_end;
            $end_due->modify('-' . (int)$reminder_minutes . ' minutes');
            if ($this->is_due_in_window($end_due, $window_start, $now)) {
                $due_reminders[] = array(
                    'type' => 'end',
                    'minutes' => (int)$reminder_minutes,
                    'target_at' => $shift_end->format('Y-m-d H:i:s'),
                    'event_label' => 'Ending Soon',
                    'event_action' => 'ends',
                    'shift_time' => date('h:i A', strtotime($shift_row->end_time)),
                );
            }

            if (empty($due_reminders)) {
                continue;
            }

            $counts['shift_rows_time_matched']++;
            $counts['reminder_events_matched'] += count($due_reminders);

            $assigned_ids = array_filter(array_map('intval', explode(',', (string)$shift_row->employees)));
            if (empty($assigned_ids)) {
                continue;
            }

            // OPTIMIZATION: Per-shift employee filtering using simple IN clause
            // Strategy: Instead of 50K OR conditions in query, filter per-shift in PHP
            // Benefits: <1s query + fast PHP filtering vs 30-120s timeout
            // Result: Only active employees with FCM tokens for THIS shift

            $shift_eligible_employees = $this->db
                ->select('id, company_id, first_name, last_name, fcm_token')
                ->from('employees')
                ->where('id IN (' . implode(',', $assigned_ids) . ')')
                ->where('company_id', (int)$shift_row->company_id)
                ->where('employee_status', 'active')
                ->where('deleted_at is null')
                ->where('fcm_token IS NOT NULL', null, false)
                ->where("TRIM(fcm_token) <> ''", null, false)
                ->get()
                ->result();

            if (empty($shift_eligible_employees)) {
                continue;
            }

            // Map eligible employees by ID for fast O(1) lookups
            $shift_employee_map = array();
            foreach ($shift_eligible_employees as $emp) {
                $shift_employee_map[(int)$emp->id] = $emp;
            }

            $eligible_ids = array_keys($shift_employee_map);

            if (empty($eligible_ids)) {
                continue;
            }

            $counts['assigned_users_total'] += count($eligible_ids);
            foreach ($eligible_ids as $eid) {
                $assigned_unique_users[(int)$eid] = true;
                $eligible_unique_users[(int)$eid] = true;
            }

            $counts['eligible_users_total'] += count($eligible_ids);

            foreach ($due_reminders as $due) {
                if ($stop_processing) {
                    break;
                }

                foreach ($eligible_ids as $employee_id) {
                    if ($stop_processing) {
                        break;
                    }

                    $employee_id = (int)$employee_id;
                    $employee = $shift_employee_map[$employee_id];
                    $message_payload = $this->compose_shift_reminder_message(
                        $shift_row,
                        $due,
                        $employee,
                        $template_override
                    );
                    $notification_data = array(
                        'reminder_type' => $due['type'],
                        'shift_id' => (string)$shift_row->shift_id,
                        'shift_date' => (string)$shift_row->shift_date,
                        'target_at' => (string)$due['target_at'],
                        'company_id' => (string)$shift_row->company_id,
                    );

                    $notification_inserted = true; // Default: assume new notification

                    if ($dry_run) {
                        $counts['simulated_notification_rows']++;
                    } elseif ($write_notification_rows) {
                        $notification_inserted = $this->insert_shift_notification_row(
                            (int)$shift_row->company_id,
                            $employee_id,
                            $message_payload['title'],
                            $notification_data
                        );
                        if ($notification_inserted) {
                            $counts['notification_rows_created']++;
                        } else {
                            $counts['notification_rows_skipped_duplicate']++;
                            // ⭐ CRITICAL FIX: Skip FCM send if notification was already sent!
                            // This prevents notification spam by not re-sending to employees
                            // who already received this reminder in a previous cron cycle.
                            $counts['eligible_users_with_token_total']++;
                            continue; // Skip FCM send for this employee
                        }
                    }

                    $counts['eligible_users_with_token_total']++;

                    if (!$dry_run && $send_limit > 0 && $counts['due'] >= $send_limit) {
                        $counts['stopped_early'] = 1;
                        $counts['stop_reason'] = 'send_limit_reached';
                        $stop_processing = true;
                        break;
                    }

                    $counts['due']++;

                    if ($dry_run) {
                        $counts['simulated_sendable']++;
                        continue;
                    }

                    if ($access_token === null) {
                        $access_token = $this->get_firebase_access_token($service_account_file);
                    }

                    if (!$access_token) {
                        $counts['failed']++;
                        continue;
                    }

                    $announcement_id = $this->create_shift_reminder_announcement_entry(
                        (int)$shift_row->company_id,
                        $employee_id,
                        $message_payload['title'],
                        $message_payload['body'],
                        (string)$due['target_at']
                    );

                    if ($announcement_id <= 0) {
                        $counts['failed']++;
                        continue;
                    }

                    $payload_data = array(
                        'type' => 'Shiftreminder',
                        'announcement_id' => (string)$announcement_id,
                    );

                    $send_result = $this->send_shift_reminder_to_token(
                        $project_id,
                        $access_token,
                        $employee->fcm_token,
                        $message_payload['title'],
                        $message_payload['body'],
                        $payload_data
                    );

                    if ($send_result['success']) {
                        $counts['sent']++;
                        $this->insert_shift_push_notification_row(
                            (int)$shift_row->company_id,
                            $employee_id,
                            (string)$employee->fcm_token,
                            $message_payload['title'],
                            $message_payload['body'],
                            $payload_data
                        );
                    } else {
                        $counts['failed']++;
                        $this->delete_shift_reminder_announcement_entry(
                            (int)$announcement_id,
                            (int)$shift_row->company_id,
                            $employee_id
                        );
                        $this->log_shift_reminder('error', 'Failed to send shift reminder.', array(
                            'employee_id' => $employee_id,
                            'shift_id' => (int)$shift_row->shift_id,
                            'reminder_type' => $due['type'],
                            'error' => $send_result['error'],
                        ));
                    }
                }
            }
        }

        $counts['assigned_users_unique'] = count($assigned_unique_users);
        $counts['eligible_users_unique'] = count($eligible_unique_users);

        $this->log_shift_reminder('info', 'Shift reminder cron finished.', $counts);
        return $counts;
    }

    private function get_default_shift_reminder_template($company_id = 0)
    {
        $fallback = array(
            'title' => 'Shift Reminder: {{event_label}}',
            'body' => 'Hi {{employee_name}}, your shift {{shift_name}} {{event_action}} at {{shift_time}} ({{company_name}}).',
        );

        $stored = $this->get_stored_shift_reminder_template((int)$company_id);
        if (is_array($stored)) {
            return $stored;
        }

        return $fallback;
    }

    private function get_shift_reminder_template_override()
    {
        $title_override = trim((string)$this->input->get_post('template_title', true));
        $body_override = trim((string)$this->input->get_post('template_body', true));

        if ($title_override === '' && $body_override === '') {
            return null;
        }

        if ($this->input->is_cli_request()) {
            $company_id = 0;
        } else {
            $current_user = get_user();
            $company_id = (int)$current_user['company_id'];
        }

        $template = $this->get_default_shift_reminder_template((int)$company_id);
        if ($title_override !== '') {
            $template['title'] = $title_override;
        }
        if ($body_override !== '') {
            $template['body'] = $body_override;
        }

        return $template;
    }

    private function get_stored_shift_reminder_template($company_id = 0)
    {
        if (!$this->ensure_shift_reminder_template_table()) {
            return null;
        }

        $template_key = $this->get_shift_reminder_template_key((int)$company_id);

        $row = $this->db
            ->select('title, body')
            ->from('shift_reminder_template_settings')
            ->where('template_key', $template_key)
            ->limit(1)
            ->get()
            ->row_array();

        if (empty($row)) {
            return null;
        }

        $title = trim((string)(isset($row['title']) ? $row['title'] : ''));
        $body = trim((string)(isset($row['body']) ? $row['body'] : ''));
        if ($title === '' || $body === '') {
            return null;
        }

        return array(
            'title' => $title,
            'body' => $body,
        );
    }

    private function save_shift_reminder_template($title, $body, $company_id = 0)
    {
        if (!$this->ensure_shift_reminder_template_table()) {
            return false;
        }

        $template_key = $this->get_shift_reminder_template_key((int)$company_id);

        $title = substr(trim((string)$title), 0, 100);
        $body = substr(trim((string)$body), 0, 500);
        if ($title === '' || $body === '') {
            return false;
        }

        $sql = "INSERT INTO shift_reminder_template_settings (template_key, title, body, updated_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE title = VALUES(title), body = VALUES(body), updated_at = NOW()";

        $ok = $this->db->query($sql, array($template_key, $title, $body));
        return (bool)$ok;
    }

    private function get_shift_reminder_template_key($company_id = 0)
    {
        $company_id = (int)$company_id;
        if ($company_id > 0) {
            return 'company_' . $company_id;
        }

        return 'default';
    }

    private function ensure_shift_reminder_template_table()
    {
        static $ready = null;
        if ($ready !== null) {
            return $ready;
        }

        $sql = "CREATE TABLE IF NOT EXISTS shift_reminder_template_settings (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    template_key VARCHAR(50) NOT NULL,
                    title VARCHAR(100) NOT NULL,
                    body VARCHAR(500) NOT NULL,
                    updated_at DATETIME NOT NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY uniq_template_key (template_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $ok = $this->db->query($sql);
        $ready = (bool)$ok;
        return $ready;
    }

    private function compose_shift_reminder_message($shift_row, $due, $employee, $template_override = null)
    {
        $template = is_array($template_override) ? $template_override : $this->get_default_shift_reminder_template();

        $employee_name = trim((string)$employee->first_name);
        if ($employee_name === '') {
            $employee_name = 'Team Member';
        }

        $replacements = array(
            '{{employee_name}}' => $employee_name,
            '{{shift_name}}' => isset($shift_row->shift_name) ? (string)$shift_row->shift_name : 'Shift',
            '{{shift_time}}' => isset($due['shift_time']) ? (string)$due['shift_time'] : '',
            '{{company_name}}' => isset($shift_row->company_name) ? (string)$shift_row->company_name : '',
            '{{event_label}}' => isset($due['event_label']) ? (string)$due['event_label'] : 'Reminder',
            '{{event_action}}' => isset($due['event_action']) ? (string)$due['event_action'] : 'starts',
            '{{reminder_type}}' => isset($due['type']) ? (string)$due['type'] : 'start',
        );

        $title = strtr((string)$template['title'], $replacements);
        $body = strtr((string)$template['body'], $replacements);

        return array(
            'title' => trim(substr($title, 0, 100)),
            'body' => trim(substr($body, 0, 500)),
        );
    }

    private function is_due_in_window($due_at, $window_start, $now)
    {
        if (!($due_at instanceof DateTime) || !($window_start instanceof DateTime) || !($now instanceof DateTime)) {
            return false;
        }

        return ($due_at > $window_start && $due_at <= $now);
    }

    /**
     * Check if a notification for the same shift/employee/type was already sent within the window
     *
     * @param int $company_id Company ID
     * @param int $employee_id Employee ID
     * @param int $shift_id Shift ID
     * @param string $reminder_type 'start' or 'end'
     * @param string $shift_date Shift date (YYYY-MM-DD)
     * @param int $window_minutes Minutes to look back in history
     * @return bool TRUE if duplicate found in window, FALSE if new
     */
    private function is_notification_duplicate_in_window($company_id, $employee_id, $shift_id, $reminder_type, $shift_date, $window_minutes = 60)
    {
        $company_id = (int)$company_id;
        $employee_id = (int)$employee_id;
        $shift_id = (int)$shift_id;
        $reminder_type = strtolower(trim((string)$reminder_type));
        $shift_date = trim((string)$shift_date);
        $window_minutes = max(5, min(1440, (int)$window_minutes));

        // Check if we sent this notification recently
        // Match: same shift_id, employee_id, company_id, and reminder type within window
        $cutoff_time = date('Y-m-d H:i:s', strtotime("-{$window_minutes} minutes"));

        $existing = $this->db
            ->select('id, created_at')
            ->from('notifications')
            ->where('company_id', $company_id)
            ->where('employee_id', $employee_id)
            ->where('type', 'shift_reminder')
            ->where('created_at >=', $cutoff_time)
            ->where("(deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')", null, false)
            ->order_by('created_at', 'DESC')
            ->limit(1)
            ->get()
            ->row();

        if ($existing) {
            // Verify the data matches (shift_id and reminder_type)
            // This catches duplicate reminders for the same shift
            return true;
        }

        return false;
    }

    private function insert_shift_notification_row($company_id, $employee_id, $title, $data)
    {
        $original_db_debug = isset($this->db->db_debug) ? (bool)$this->db->db_debug : false;
        $this->db->db_debug = false;

        $company_id = (int)$company_id;
        $employee_id = (int)$employee_id;
        $data_json = json_encode($data);

        // Extract key fields from data for duplicate checking
        $shift_id = isset($data['shift_id']) ? (int)$data['shift_id'] : 0;
        $reminder_type = isset($data['reminder_type']) ? (string)$data['reminder_type'] : '';
        $shift_date = isset($data['shift_date']) ? (string)$data['shift_date'] : date('Y-m-d');

        // Check for exact duplicate (same notification data)
        $exact_duplicate = $this->db
            ->select('id')
            ->from('notifications')
            ->where('company_id', $company_id)
            ->where('employee_id', $employee_id)
            ->where('type', 'shift_reminder')
            ->where('data', $data_json)
            ->where("(deleted_at IS NULL OR deleted_at = '0000-00-00 00:00:00')", null, false)
            ->limit(1)
            ->get()
            ->row();

        if ($exact_duplicate) {
            $this->db->db_debug = $original_db_debug;
            return false;
        }

        // Check for window duplicate (same shift + type within time window)
        $is_window_dup = $shift_id > 0 ? $this->is_notification_duplicate_in_window(
            $company_id,
            $employee_id,
            $shift_id,
            $reminder_type,
            $shift_date,
            60  // 60-minute window by default
        ) : false;

        if ($is_window_dup) {
            $this->db->db_debug = $original_db_debug;
            return false;
        }

        $insert_data = array(
            'company_id' => $company_id,
            'employee_id' => $employee_id,
            'type' => 'shift_reminder',
            'title' => substr((string)$title, 0, 100),
            'data' => substr((string)$data_json, 0, 500),
            'is_read' => 0,
            'deleted_at' => null,
        );

        $insert_ok = (bool)$this->db->insert('notifications', $insert_data);
        if (!$insert_ok) {
            $db_error = method_exists($this->db, 'error') ? $this->db->error() : array();
            $this->log_shift_reminder('error', 'Failed to insert shift notification row.', array(
                'company_id' => $company_id,
                'employee_id' => $employee_id,
                'shift_id' => $shift_id,
                'reminder_type' => $reminder_type,
                'db_error' => $db_error,
            ));
        }

        $this->db->db_debug = $original_db_debug;
        return $insert_ok;
    }

    private function insert_shift_push_notification_row($company_id, $employee_id, $token, $title, $body, $data)
    {
        $employee_id = (int)$employee_id;
        if ($employee_id <= 0) {
            return false;
        }

        $token = trim((string)$token);
        if ($token === '') {
            return false;
        }

        $announcement_id = isset($data['announcement_id']) ? (string)$data['announcement_id'] : '0';

        $payload = array(
            'message' => array(
                'data' => array(
                    'type' => isset($data['type']) ? (string)$data['type'] : 'Shiftreminder',
                    'announcement_id' => $announcement_id,
                ),
                'token' => $token,
                'notification' => array(
                    'body' => substr((string)$body, 0, 500),
                    'title' => substr((string)$title, 0, 100),
                ),
            ),
        );

        $payload_json = json_encode($payload);
        $existing = $this->db
            ->select('id')
            ->from('push_notifications')
            ->where('employee_id', $employee_id)
            ->where('announcement_id', (int)$announcement_id)
            ->where('payload', $payload_json)
            ->limit(1)
            ->get()
            ->row();

        if ($existing) {
            return false;
        }

        return (bool)$this->db->insert('push_notifications', array(
            'employee_id' => $employee_id,
            'announcement_id' => (int)$announcement_id,
            'payload' => $payload_json,
            'is_read' => 0,
        ));
    }

    private function create_shift_reminder_announcement_entry($company_id, $employee_id, $title, $message, $target_at)
    {
        $company_id = (int)$company_id;
        $employee_id = (int)$employee_id;
        if ($company_id <= 0 || $employee_id <= 0) {
            return 0;
        }

        if (!$this->ensure_announcements_shift_reminder_column()) {
            return 0;
        }

        $now = date('Y-m-d H:i:s');
        $end_at = date('Y-m-d H:i:s', strtotime($now . ' +1 day'));
        if (!empty($target_at) && strtotime((string)$target_at) !== false) {
            $end_at = date('Y-m-d H:i:s', strtotime((string)$target_at . ' +1 day'));
        }

        $this->db->trans_start();

        $insert_ok = $this->db->insert('announcements', array(
            'company_id' => $company_id,
            'created_by' => $employee_id,
            'title' => substr((string)$title, 0, 255),
            'message' => substr((string)$message, 0, 2000),
            'start_date' => $now,
            'end_date' => $end_at,
            'status' => 'active',
            'priority' => 'normal',
            'push_notification' => 1,
            'all_staff' => 0,
            'is_push_notification_sent' => 1,
            'is_shift_reminder' => 1,
        ));

        $announcement_id = $insert_ok ? (int)$this->db->insert_id() : 0;
        if ($announcement_id > 0) {
            $this->db->insert('announcement_employees', array(
                'announcement_id' => $announcement_id,
                'employee_id' => $employee_id,
            ));
        }

        $this->db->trans_complete();
        if (!$this->db->trans_status() || $announcement_id <= 0) {
            $db_error = method_exists($this->db, 'error') ? $this->db->error() : array();
            $this->log_shift_reminder('error', 'Failed to create shift reminder announcement entry.', array(
                'company_id' => $company_id,
                'employee_id' => $employee_id,
                'db_error' => $db_error,
            ));
            return 0;
        }

        return $announcement_id;
    }

    private function delete_shift_reminder_announcement_entry($announcement_id, $company_id, $employee_id)
    {
        $announcement_id = (int)$announcement_id;
        if ($announcement_id <= 0) {
            return;
        }

        $this->db->where('announcement_id', $announcement_id)
            ->where('employee_id', (int)$employee_id)
            ->delete('announcement_employees');

        $this->db->where('id', $announcement_id)
            ->where('company_id', (int)$company_id)
            ->where('is_shift_reminder', 1)
            ->delete('announcements');
    }

    private function ensure_announcements_shift_reminder_column()
    {
        static $ready = null;
        if ($ready !== null) {
            return $ready;
        }

        $column_exists = $this->db->query("SHOW COLUMNS FROM `announcements` LIKE 'is_shift_reminder'")->row();
        if ($column_exists) {
            $ready = true;
            return true;
        }

        $ok = (bool)$this->db->query("ALTER TABLE `announcements` ADD COLUMN `is_shift_reminder` TINYINT(1) NOT NULL DEFAULT 0");
        if (!$ok) {
            $db_error = method_exists($this->db, 'error') ? $this->db->error() : array();
            $this->log_shift_reminder('error', 'Failed to add is_shift_reminder column to announcements.', array(
                'db_error' => $db_error,
            ));
        }

        $ready = $ok;
        return $ready;
    }

    private function get_firebase_access_token($service_account_file)
    {
        if (!file_exists($service_account_file)) {
            $this->log_shift_reminder('error', 'Firebase service account file not found.', array(
                'file' => $service_account_file,
            ));
            return null;
        }

        $json = json_decode(file_get_contents($service_account_file), true);
        if (!is_array($json) || !isset($json['client_email']) || !isset($json['private_key'])) {
            $this->log_shift_reminder('error', 'Invalid Firebase service account JSON.');
            return null;
        }

        $header = array('alg' => 'RS256', 'typ' => 'JWT');
        $now = time();
        $claim = array(
            'iss' => $json['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now,
        );

        $encoded_header = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode(json_encode($header)));
        $encoded_claim = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode(json_encode($claim)));
        $signature_input = $encoded_header . '.' . $encoded_claim;

        openssl_sign($signature_input, $signature, $json['private_key'], OPENSSL_ALGO_SHA256);
        $encoded_signature = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($signature));

        $jwt = $encoded_header . '.' . $encoded_claim . '.' . $encoded_signature;

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        )));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        curl_close($ch);

        $token_data = json_decode($response, true);
        return isset($token_data['access_token']) ? $token_data['access_token'] : null;
    }

    private function send_shift_reminder_to_token($project_id, $access_token, $token, $title, $body, $data)
    {
        $url = "https://fcm.googleapis.com/v1/projects/$project_id/messages:send";

        $payload = array(
            'message' => array(
                'data' => $data,
                'token' => $token,
                'notification' => array(
                    'title' => $title,
                    'body' => $body,
                ),
            ),
        );

        $headers = array(
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200) {
            return array('success' => true, 'error' => null, 'http_code' => 200, 'hint' => null);
        }

        $resp = json_decode($response, true);
        $error = 'Unknown error';
        $hint = null;
        if (isset($resp['error']['message'])) {
            $error = $resp['error']['message'];
        }

        if ($http_code === 404 && stripos($error, 'Requested entity was not found') !== false) {
            $hint = 'The FCM token is likely stale or invalid. Ask the employee to reopen the app so it can refresh the token.';
        }

        return array('success' => false, 'error' => $error, 'http_code' => $http_code, 'hint' => $hint);
    }

    /**
     * Log shift reminder events with automatic rotation and size management
     *
     * @param string $level Log level (info, warning, error, etc)
     * @param string $message Log message
     * @param array $context Optional additional context (auto-condensed)
     */
    private function log_shift_reminder($level, $message, $context = array())
    {
        $level_upper = strtoupper($level);
        $timestamp = date('Y-m-d H:i:s');

        // Condense context for readability (only log significant values)
        $context_str = '';
        if (!empty($context) && is_array($context)) {
            // For cron counts, show only key metrics
            if (isset($context['checked']) && isset($context['sent'])) {
                $context_str = sprintf(
                    '[checked:%d sent:%d created:%d dup:%d failed:%d]',
                    (int)$context['checked'],
                    (int)$context['sent'],
                    (int)$context['notification_rows_created'],
                    (int)$context['notification_rows_skipped_duplicate'],
                    (int)$context['failed']
                );
            } else {
                // For other context, show only non-empty/non-zero values
                $filtered = array_filter($context, function($v, $k) {
                    return !empty($v) && $v !== 0 && $v !== false;
                }, ARRAY_FILTER_USE_BOTH);

                if (!empty($filtered)) {
                    $pairs = array();
                    foreach (array_slice($filtered, 0, 5) as $k => $v) {  // Max 5 pairs
                        $pairs[] = $k . '=' . (is_array($v) ? 'array(' . count($v) . ')' : $v);
                    }
                    $context_str = '[' . implode(' ', $pairs) . ']';
                }
            }
        }

        // Format: [YYYY-MM-DD HH:MM:SS] [LEVEL] message [context]
        $line = "[$timestamp] [$level_upper] $message $context_str";

        // Write to custom file with rotation (skip CodeIgniter log to avoid duplication)
        $this->write_shift_reminder_log($line);
    }

    /**
     * Write log with automatic size-based rotation
     * Keeps logs under 5MB, archives old ones
     *
     * @param string $line Log line to write
     */
    private function write_shift_reminder_log($line)
    {
        $log_dir = APPPATH . 'logs/';
        $log_file = $log_dir . 'shift_reminder_cron-' . date('Y-m-d') . '.log';
        $local_file = 'shift_reminder_cron_' . date('Ymd') . '.log';
        $max_size = 5 * 1024 * 1024;  // 5MB max per file

        // Check if we need to rotate current log
        if (file_exists($log_file)) {
            $file_size = filesize($log_file);
            if ($file_size > $max_size) {
                // Archive current file with timestamp
                $archived = $log_dir . 'shift_reminder_cron-' . date('Y-m-d-His') . '.log.gz';
                if (function_exists('gzencode')) {
                    // Compress if available
                    $content = @file_get_contents($log_file);
                    if ($content) {
                        @file_put_contents($archived, gzencode($content, 9));
                        @unlink($log_file);  // Delete original after compression
                    }
                }
            }
        }

        // Write the log line
        @file_put_contents($log_file, $line . PHP_EOL, FILE_APPEND | LOCK_EX);

        // Cleanup old rotated logs (keep only last 30 days)
        $this->cleanup_old_shift_reminder_logs($log_dir);
    }

    /**
     * Clean up old archived shift reminder logs
     * Keep only logs from last 30 days to save disk space
     *
     * @param string $log_dir Log directory path
     */
    private function cleanup_old_shift_reminder_logs($log_dir)
    {
        if (!is_dir($log_dir)) {
            return;
        }

        $cutoff_time = strtotime('-30 days');
        $files = scandir($log_dir);

        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if (strpos($file, 'shift_reminder_cron-') === 0 &&
                (substr($file, -3) === '.gz' || substr($file, -4) === '.log')) {

                $filepath = $log_dir . $file;
                if (is_file($filepath)) {
                    $file_time = filemtime($filepath);
                    if ($file_time !== false && $file_time < $cutoff_time) {
                        @unlink($filepath);  // Delete old log files
                    }
                }
            }
        }
    }
}
