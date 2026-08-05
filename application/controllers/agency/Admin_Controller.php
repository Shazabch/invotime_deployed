<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin_Controller extends CI_Controller
{
    protected $admin; // logged-in admin

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

    /**
     * Call this at the start of any method you want protected
     */
    protected function require_admin()
    {
        $token = $this->input->get_request_header('Authorization');

        if (!$token) {
            return $this->send_unauthorized('You are not logged in. Please provide an API token.');
        }

        $admin = $this->db->where('api_token', $token)
            ->where('token_expiry >=', date('Y-m-d H:i:s'))
            ->get('admins')
            ->row();

        if (!$admin) {
            return $this->send_unauthorized('Invalid or expired token. Please login again.');
        }

        $this->admin = $admin;
    }

    /**
     * Send JSON unauthorized response
     */
    protected function send_unauthorized($message)
    {
        // Clear any previous output buffer
        if (ob_get_length()) {
            ob_end_clean();
        }

        // Force JSON output
        $response = [
            'success' => false,
            'message' => $message
        ];

        // Set headers
        header('Content-Type: application/json', true, 401);

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Stop further execution
        exit();
    }


    /**
     * Generate a new API token for an admin
     */
    protected function generate_token($admin_id)
    {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

        $this->db->where('id', $admin_id)
            ->update('admins', ['api_token' => $token, 'token_expiry' => $expiry]);

        return $token;
    }

    /**
     * Helper for sending JSON responses
     */
    protected function json($data, $status_code = 200)
    {
        $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
