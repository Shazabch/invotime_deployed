<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fcm extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();

        // ===== CORS HEADERS =====
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Allow-Credentials: false");
        header("Content-Type: application/json; charset=UTF-8");

        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $this->output->set_content_type('application/json');
    }

    /** JSON Helper */
    private function json($data, $status_code = 200)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header($status_code)
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * POST /fcmcontroller/update_token
     *
     * Allows mobile app to update employee's FCM registration token
     *
     * Expected JSON body:
     * {
     *   "employee_id": 123,
     *   "fcm_token": "dTYdvJCG8sbXbwO-FUlqoh:APA91bHnq0qMfyin4gPz2uJRV64u3lwcNQXXox8EA9coE83U0NQWQ9csWQSipDetvWjgRhaTFPdPXjR0B4_w2xGFhkkVufjVhp0rEDbMv3xs-7FYBAtHcIU"
     * }
     */
    public function update_token()
    {
        if ($this->input->method() !== 'post') {
            $this->json(['success' => false, 'message' => 'Method Not Allowed'], 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($input)) {
            $this->json(['success' => false, 'message' => 'Invalid JSON payload'], 400);
            return;
        }

        $employee_id = isset($input['employee_id']) ? (int)$input['employee_id'] : null;
        $fcm_token   = isset($input['fcm_token']) ? trim($input['fcm_token']) : null;

        if (!$employee_id) {
            $this->json([
                'success' => false,
                'message' => 'Missing required fields: employee_id'
            ], 400);
            return;
        }
        if (!$fcm_token) {
            $this->json([
                'success' => false,
                'message' => 'Missing required fields: fcm_token'
            ], 400);
            return;
        }


        // Optional: Validate employee exists
        $employee_exists = $this->db->select('id')
            ->where('id', $employee_id)
            ->get('employees')
            ->num_rows() > 0;

        if (!$employee_exists) {
            $this->json(['success' => false, 'message' => 'Employee not found'], 404);
            return;
        }

        // Update FCM token
        $updated = $this->db->where('id', $employee_id)
            ->update('employees', [
                'fcm_token'  => $fcm_token,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        if ($updated) {
            log_message('info', "FCM Token updated for Employee ID: $employee_id");
            $employee = $this->db->select('fcm_token')
                ->where('id', $employee_id)
                ->get('employees')
                ->row();

            $this->json([
                'success'     => true,
                'message'     => 'FCM token updated successfully',
                'fcm_token' => $employee->fcm_token ?? null,
                'employee_id' => $employee_id,
                'updated_at'  => date('Y-m-d H:i:s')
            ], 200);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Failed to update FCM token'
            ], 500);
        }
    }
    /**
     * POST /fcmcontroller/check_token
     *
     * Returns only the current FCM token for the employee (or null)
     *
     * JSON body:
     * {
     *   "employee_id": 123
     * }
     *
     * Response examples:
     * "dTYdvJCG8sbXbwO-FUlqoh:APA91bHnq0qMfyin4gPz2uJRV64u3lwcNQXXox8EA9coE83U0NQWQ9csWQSipDetvWjgRhaTFPdPXjR0B4_w2xGFhkkVufjVhp0rEDbMv3xs-7FYBAtHcIU"
     *
     * or
     * null
     */
    public function check_token()
    {

        if ($this->input->method() !== 'post') {
            $this->json(['success' => false, 'message' => 'Method Not Allowed'], 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($input)) {
            $this->json(['success' => false, 'message' => 'Input Error'], 400);
            return;
        }

        $employee_id = isset($input['employee_id']) ? (int)$input['employee_id'] : null;

        if (!$employee_id) {

            $this->json(['success' => false, 'message' => 'Employee Not Found'], 400);
            return;
        }

        $employee = $this->db->select('fcm_token')
            ->where('id', $employee_id)
            ->get('employees')
            ->row();

        if (!$employee) {

            $this->json(['success' => false, 'message' => 'FCM Not Found'], 404);
            return;
        }

        $this->json(['success' => true, 'fcm_token' => $employee->fcm_token ?? null], 200);
    }
    /**
     * GET /fcmcontroller/test
     * Simple health check endpoint
     */
    public function test()
    {
        $this->json([
            'success' => true,
            'message' => 'FCM Controller is working!',
            'timestamp' => date('Y-m-d H:i:s')
        ], 200);
    }
}
