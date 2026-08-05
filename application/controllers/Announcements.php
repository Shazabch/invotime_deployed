<?php
class Announcements extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        if (is_null(get_user())) {
            redirect("welcome");
        }
    }

    public function index()
    {
        // IMPORTANT: Add 'announcements' to your permissions system
        if (!is_page_permitted('announcements')) {
            redirect_if_not_permitted();
        }

        $data['pageTitle'] = "Announcements";
        $data['active_menu'] = "announcements";
        $current_user = get_user();
        $cid = $current_user["company_id"];

        // Get list of all announcements
        $data['announcements'] = $this->db
            ->select("a.*, e.first_name as created_by_name")
            ->select("(SELECT COUNT(1) FROM announcement_views av WHERE av.announcement_id = a.id) as view_count", false)
            ->select("CASE
                        WHEN a.status = 'draft' THEN 'Draft'
                        WHEN a.status = 'closed' THEN 'Closed'
                        WHEN a.status = 'active' AND NOW() > a.end_date THEN 'Closed'  /* Expired active announcement */
                        WHEN a.status = 'active' AND NOW() BETWEEN a.start_date AND a.end_date THEN 'Active'
                        WHEN a.status = 'active' AND NOW() < a.start_date THEN 'Scheduled' /* Active but waiting for start date */
                        ELSE 'Draft'
                      END as display_status", false)
            ->from('announcements a')
            ->join('employees e', 'e.id = a.created_by', 'left')
            ->where('a.company_id', $cid)
            ->where('a.is_shift_reminder', 0)
            ->where('a.deleted_at IS NULL')
            ->order_by('a.created_at', 'DESC')
            ->get()
            ->result();

        // Get data for filters in modals
        $data['branches'] = $this->db->select('id, name')->from('branches')->where('company_id', $cid)->order_by("name", "asc")->get()->result();
        $data['departments'] = $this->db->select('id, name')->from('departments')->where('company_id', $cid)->order_by("name", "asc")->get()->result();
        $data['positions'] = $this->db->select('id, title')->from('positions')->where('company_id', $cid)->order_by("title", "asc")->get()->result();
        $data['sections'] = $this->db->select('id, title')->from('sections')->where('company_id', $cid)->order_by("title", "asc")->get()->result();

        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);
        $this->load->view('announcements', $data);
        $this->load->view('footer');
    }

    public function getSingleAnnouncement()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id;
        $cid = get_user()["company_id"];

        $data['announcement'] = $this->db->get_where('announcements', array('id' => $id, 'company_id' => $cid))->row();

        if ($data['announcement']) {

            $data['outlets'] = array_column($this->db->select('branch_id')->get_where('announcement_outlets', array('announcement_id' => $id))->result_array(), 'branch_id');
            $data['departments'] = array_column($this->db->select('department_id')->get_where('announcement_departments', array('announcement_id' => $id))->result_array(), 'department_id');
            $data['positions'] = array_column($this->db->select('position_id')->get_where('announcement_positions', array('announcement_id' => $id))->result_array(), 'position_id');
            $data['sections'] = array_column($this->db->select('section_id')->get_where('announcement_sections', array('announcement_id' => $id))->result_array(), 'section_id');


            $emp_ids = array_column($this->db->select('employee_id')->get_where('announcement_employees', array('announcement_id' => $id))->result_array(), 'employee_id');
            if (!empty($emp_ids)) {
                $data['employees'] = $this->db->select('id, first_name, special_id')->from('employees')->where_in('id', $emp_ids)->get()->result();
            } else {
                $data['employees'] = [];
            }

            $data['success'] = true;
        } else {
            $data['success'] = false;
        }

        echo json_encode($data);
    }


    public function searchEmployees()
    {
        $cid = get_user()["company_id"];
        $term = $this->input->get('term');

        $results = $this->db
            ->select('id, CONCAT(first_name, " (", special_id, ")") as text', false)
            ->from('employees')
            ->where('company_id', $cid)
            ->where('employee_status', 'active')
            ->where('deleted_at IS NULL')
            ->group_start()
            ->like('first_name', $term)
            ->or_like('special_id', $term)
            ->group_end()
            ->limit(50)
            ->get()
            ->result();

        echo json_encode(array('results' => $results));
    }


    private function parseDatetime($date_string)
    {
        if (empty($date_string)) {
            return NULL;
        }
        $date_string = trim($date_string);

        // 1. Try to parse with the full DateTime format (DD/MM/YYYY hh:mm A)
        $full_format = 'd/m/Y h:i A';
        $dateTime = DateTime::createFromFormat($full_format, $date_string);

        // 2. If it failed, check if the string contains only the date (e.g., "13/11/2025")
        if (!$dateTime && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date_string)) {
            // Try to parse with date-only format (DD/MM/YYYY). Time defaults to 00:00:00.
            $date_only_format = 'd/m/Y';
            $dateTime = DateTime::createFromFormat($date_only_format, $date_string);
        }

        if ($dateTime) {
            return $dateTime->format('Y-m-d H:i:s');
        }

        return NULL;
    }

    public function save() // Default to active for the main 'Create' button
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $cid = get_user()["company_id"];
        $user_id = get_user()["id"];


        $this->db->trans_start();

        // 1. Insert into main announcements table
        $ann_data = array(
            'company_id' => $cid,
            'created_by' => $user_id,
            'title' => $request->title,
            'message' => $request->message,
            'start_date' => $this->parseDatetime($request->start_date),
            'end_date' => $this->parseDatetime($request->end_date),
            'priority' => $request->priority,
            'status' => $request->status,
            'push_notification' => $request->push_notification ? 1 : 0,
            'all_staff' => $request->all_staff ? 1 : 0
        );
        $this->db->insert('announcements', $ann_data);
        $ann_id = $this->db->insert_id();

        if (!$request->all_staff) {
            $this->insertJunctions('announcement_outlets', $ann_id, 'branch_id', $request->outlets);
            $this->insertJunctions('announcement_departments', $ann_id, 'department_id', $request->departments);
            $this->insertJunctions('announcement_positions', $ann_id, 'position_id', $request->positions);
            $this->insertJunctions('announcement_sections', $ann_id, 'section_id', $request->sections);
            $this->insertJunctions('announcement_employees', $ann_id, 'employee_id', $request->employees);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => false, 'msg' => 'Failed to create announcement.']);
        } else {
            if ($request->push_notification && $request->status === 'active') {
                $this->sendPushNotification($ann_id, $request->title, strip_tags(substr($request->message, 0, 200)) . '...');
            }
            echo json_encode(['success' => true, 'msg' => 'Announcement saved successfully!']);
        }
    }

    public function update()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $cid = get_user()["company_id"];
        $ann_id = $request->id;

        $this->db->trans_start();

        // 1. Update main announcements table
        $ann_data = array(
            'title' => $request->title,
            'message' => $request->message,
            'start_date' => $this->parseDatetime($request->start_date),
            'end_date' => $this->parseDatetime($request->end_date),
            'priority' => $request->priority,
            'status' => $request->status,
            'push_notification' => $request->push_notification ? 1 : 0,
            'all_staff' => $request->all_staff ? 1 : 0
        );
        $this->db->where('id', $ann_id)->where('company_id', $cid)->update('announcements', $ann_data);

        // 2. Clear all old junction data
        $this->db->where('announcement_id', $ann_id)->delete('announcement_outlets');
        $this->db->where('announcement_id', $ann_id)->delete('announcement_departments');
        $this->db->where('announcement_id', $ann_id)->delete('announcement_positions');
        $this->db->where('announcement_id', $ann_id)->delete('announcement_sections');
        $this->db->where('announcement_id', $ann_id)->delete('announcement_employees');

        // 3. Insert new junction tables (if not 'All Staff')
        if (!$request->all_staff) {
            $this->insertJunctions('announcement_outlets', $ann_id, 'branch_id', $request->outlets);
            $this->insertJunctions('announcement_departments', $ann_id, 'department_id', $request->departments);
            $this->insertJunctions('announcement_positions', $ann_id, 'position_id', $request->positions);
            $this->insertJunctions('announcement_sections', $ann_id, 'section_id', $request->sections);
            $this->insertJunctions('announcement_employees', $ann_id, 'employee_id', $request->employees);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(['success' => false, 'msg' => 'Failed to update announcement.']);
        } else {
            if ($request->push_notification && $request->status === 'active') {
                $this->sendPushNotification($ann_id, $request->title, strip_tags(substr($request->message, 0, 200)) . '...');
            }
            echo json_encode(['success' => true, 'msg' => 'Announcement updated successfully!']);
        }
    }

    public function delete()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id;
        $cid = get_user()["company_id"];

        $this->db->set('deleted_at', 'NOW()', false)
            ->where('id', $id)
            ->where('company_id', $cid)
            ->update('announcements');

        echo json_encode(['success' => true]);
    }

    private function insertJunctions($table, $ann_id, $fk_name, $id_array)
    {
        if (!empty($id_array) && is_array($id_array)) {
            $batch_data = [];
            foreach ($id_array as $id) {
                if (!empty($id)) { // Ensure ID is not null or empty
                    $batch_data[] = [
                        'announcement_id' => $ann_id,
                        $fk_name => $id
                    ];
                }
            }
            if (!empty($batch_data)) {
                $this->db->insert_batch($table, $batch_data);
            }
        }
    }
    private function sendPushNotification($announcement_id, $title, $body)
    {
        $cid = get_user()["company_id"];
        $log_file = APPPATH . 'logs/push_notifications.log';

        $log = function ($message) use ($log_file) {
            $timestamp = date('Y-m-d H:i:s');
            $entry = "[$timestamp] $message" . PHP_EOL;
            @file_put_contents($log_file, $entry, FILE_APPEND | LOCK_EX);
        };

        $log("=== PUSH NOTIFICATION ATTEMPT START ===");
        $log("Announcement ID: $announcement_id | Title: $title");

        // === CONFIGURATION ===
        $service_account_file = FCPATH . 'invotime-399613-firebase-adminsdk-qlq1j-61727bd060.json';
        $project_id = 'invotime-399613';

        if (!file_exists($service_account_file)) {
            $log("ERROR: Service account JSON not found.");
            $log("=== END (CONFIG ERROR) ===" . PHP_EOL);
            return;
        }

        // Fetch announcement
        $ann = $this->db->select('all_staff, push_notification')
            ->where('id', $announcement_id)
            ->where('company_id', $cid)
            ->where('deleted_at IS NULL')
            ->get('announcements')
            ->row();

        if (!$ann || !$ann->push_notification) {
            $log("SKIPPED: Announcement not found or push disabled.");
            $log("=== END (SKIPPED) ===" . PHP_EOL);
            return;
        }

        // Your original targeting logic (preserved exactly)
        $sql = "SELECT DISTINCT e.id, e.fcm_token FROM employees e";
        $base_where = [
            "e.company_id = " . (int)$cid,
            "e.employee_status = 'active'",
            "e.deleted_at IS NULL",
            "e.fcm_token IS NOT NULL",
            "e.fcm_token != ''"
        ];
        $target_conditions = [];

        $db_sub = $this->load->database('default', TRUE);

        if ($ann->all_staff != 1) {
            $outlets = array_column($db_sub->select('branch_id')->where('announcement_id', $announcement_id)->get('announcement_outlets')->result_array(), 'branch_id');
            if (!empty($outlets)) $target_conditions[] = "e.branch_id IN (" . implode(',', array_map('intval', $outlets)) . ")";

            $depts = array_column($db_sub->select('department_id')->where('announcement_id', $announcement_id)->get('announcement_departments')->result_array(), 'department_id');
            if (!empty($depts)) $target_conditions[] = "e.department_id IN (" . implode(',', array_map('intval', $depts)) . ")";

            $positions = array_column($db_sub->select('position_id')->where('announcement_id', $announcement_id)->get('announcement_positions')->result_array(), 'position_id');
            if (!empty($positions)) $target_conditions[] = "e.position_id IN (" . implode(',', array_map('intval', $positions)) . ")";

            $sections = array_column($db_sub->select('section_id')->where('announcement_id', $announcement_id)->get('announcement_sections')->result_array(), 'section_id');
            if (!empty($sections)) $target_conditions[] = "e.section_id IN (" . implode(',', array_map('intval', $sections)) . ")";

            $emps = array_column($db_sub->select('employee_id')->where('announcement_id', $announcement_id)->get('announcement_employees')->result_array(), 'employee_id');
            if (!empty($emps)) $target_conditions[] = "e.id IN (" . implode(',', array_map('intval', $emps)) . ")";

            if (empty($target_conditions)) {
                $log("SKIPPED: No targeting criteria.");
                $log("=== END (NO TARGETS) ===" . PHP_EOL);
                return;
            }
            $base_where[] = "(" . implode(" AND ", $target_conditions) . ")";
        }

        $sql .= " WHERE " . implode(" AND ", $base_where);
        $log("Final SQL Query: $sql");

        $query = $this->db->query($sql);
        if (!$query) {
            $log("ERROR: Query failed.");
            $log("=== END (QUERY FAILED) ===" . PHP_EOL);
            return;
        }

        $employees = $query->result_array();
        $log("Recipients with token: " . count($employees));

        if (count($employees) === 0) {
            $log("SKIPPED: No recipients.");
            $log("=== END (NO RECIPIENTS) ===" . PHP_EOL);
            return;
        }

        // Create array mapping tokens to employee IDs (handle multiple employees with same token)
        $token_to_employees = [];
        foreach ($employees as $emp) {
            if (!empty($emp['fcm_token'])) {
                if (!isset($token_to_employees[$emp['fcm_token']])) {
                    $token_to_employees[$emp['fcm_token']] = [];
                }
                $token_to_employees[$emp['fcm_token']][] = $emp['id'];
            }
        }

        $tokens = array_keys($token_to_employees);
        $log("Unique valid tokens: " . count($tokens));

        // === Get Access Token (same as test page) ===
        $json = json_decode(file_get_contents($service_account_file), true);
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $now = time();
        $claim = [
            'iss' => $json['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ];

        $encoded_header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($header)));
        $encoded_claim = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($claim)));
        $signature_input = "$encoded_header.$encoded_claim";

        openssl_sign($signature_input, $signature, $json['private_key'], OPENSSL_ALGO_SHA256);
        $encoded_signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        $jwt = "$encoded_header.$encoded_claim.$encoded_signature";

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $token_data = json_decode($response, true);
        $access_token = $token_data['access_token'] ?? null;

        if (!$access_token) {
            $log("ERROR: Failed to get access token.");
            $log("=== END (AUTH FAILED) ===" . PHP_EOL);
            return;
        }

        $log("Access token obtained successfully.");

        // === Send one message per token (same as test page – guaranteed to work) ===
        $url = "https://fcm.googleapis.com/v1/projects/$project_id/messages:send";
        $headers = ['Authorization: Bearer ' . $access_token, 'Content-Type: application/json'];

        $total_success = 0;
        $total_failure = 0;

        foreach ($tokens as $i => $token) {
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body
                    ],
                    'data' => [
                        'announcement_id' => (string)$announcement_id,
                        'type' => 'announcement'
                    ]
                ]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code === 200) {
                $total_success++;
                $log("Token " . ($i + 1) . ": Success (delivered)");

                // Save to push_notifications table for each employee with this token
                $employee_ids = $token_to_employees[$token];
                foreach ($employee_ids as $emp_id) {
                    $push_data = array(
                        'employee_id' => $emp_id,
                        'announcement_id' => $announcement_id,
                        'payload' => json_encode($payload),
                        'is_read' => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    $this->db->insert('push_notifications', $push_data);
                }
            } else {
                $total_failure++;
                $resp = json_decode($response, true);
                $error = $resp['error']['message'] ?? 'Unknown';
                $log("Token " . ($i + 1) . ": Failed - $error");
            }
        }
        // === Mark as sent only if at least one success ===
        if ($total_success > 0) {
            $this->markPushAsSent($announcement_id);
        }
        $log("=== PUSH NOTIFICATION SUMMARY ===");
        $log("Total Tokens: " . count($tokens));
        $log("Successful: $total_success");
        $log("Failed: $total_failure");
        $log("=== PUSH NOTIFICATION END ===" . PHP_EOL);
    }
    private function markPushAsSent($announcement_id)
    {
        $cid = get_user()["company_id"];
        $log_file = APPPATH . 'logs/push_notifications.log';

        $log = function ($message) use ($log_file) {
            $timestamp = date('Y-m-d H:i:s');
            $entry = "[$timestamp] $message" . PHP_EOL;
            @file_put_contents($log_file, $entry, FILE_APPEND | LOCK_EX);
        };

        $updated = $this->db->where('id', $announcement_id)
            ->where('company_id', $cid)
            ->set('is_push_notification_sent', 1)
            ->update('announcements');

        if ($updated) {
            $log("PUSH STATUS UPDATED: Announcement ID $announcement_id marked as sent.");
        } else {
            $log("ERROR: Failed to update push status for Announcement ID $announcement_id");
        }
    }
    // Returns the list of employees who have viewed a given announcement (for the "Viewed by" modal)
    public function getAnnouncementViewers()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id ?? null;
        $cid = get_user()["company_id"];

        if (empty($id)) {
            echo json_encode(['success' => false, 'msg' => 'Announcement ID is missing.']);
            return;
        }

        // Confirm the announcement belongs to this company before exposing any viewer data
        $ann = $this->db->select('id, title')
            ->where('id', $id)
            ->where('company_id', $cid)
            ->get('announcements')
            ->row();

        if (!$ann) {
            echo json_encode(['success' => false, 'msg' => 'Announcement not found.']);
            return;
        }

        $viewers = $this->db
            ->select('e.id, e.first_name, e.special_id, b.name as branch_name, av.read_at')
            ->from('announcement_views av')
            ->join('employees e', 'e.id = av.employee_id')
            ->join('branches b', 'b.id = e.branch_id', 'left')
            ->where('av.announcement_id', $id)
            ->order_by('av.read_at', 'DESC')
            ->get()
            ->result();

        echo json_encode(['success' => true, 'title' => $ann->title, 'viewers' => $viewers]);
    }
}
