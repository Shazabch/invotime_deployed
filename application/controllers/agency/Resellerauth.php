<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Resellerauth extends CI_Controller
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

    /** JSON helper */
    private function json($data, $status_code = 200)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header($status_code)
            ->set_output(json_encode($data));
    }

    // =========================================================================
    // 1. LOGIN
    // =========================================================================
    public function login()
    {
        $input = json_decode(file_get_contents('php://input'));

        if (empty($input->email) || empty($input->password)) {
            return $this->json(['success' => false, 'message' => 'Email & password required'], 400);
        }

        $reseller = $this->db->where('email', $input->email)
            ->where('password', md5($input->password))
            ->get('resellers')
            ->row();
        if (!$reseller) {
            return $this->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        // Generate API token
        $token = bin2hex(random_bytes(32));

        // Save token in DB
        $this->db->where('id', $reseller->id)->update('resellers', ['api_token' => $token]);

        $this->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'reseller' => [
                'id' => $reseller->id,
                'name' => $reseller->name,
                'email' => $reseller->email,
                'admin_name' => $reseller->admin_name,
                'admin_id' => $reseller->admin_id
            ]
        ]);
    }

    // =========================================================================
    // 2. LOGOUT
    // =========================================================================
    public function logout()
    {
        $token = $this->input->get_request_header('Authorization');
        if (!$token) return $this->json(['success' => false, 'message' => 'Token required'], 400);

        $reseller = $this->db->where('api_token', $token)->get('resellers')->row();
        if ($reseller) {
            $this->db->where('id', $reseller->id)->update('resellers', ['api_token' => null]);
        }

        $this->json(['success' => true, 'message' => 'Logged out']);
    }

    // =========================================================================
    // 3. GET CURRENT RESELLER (AUTH CHECK)
    // =========================================================================
    public function me()
    {
        $token = $this->input->get_request_header('Authorization');
        if (!$token) return $this->json(['success' => false, 'message' => 'Token required'], 400);

        $reseller = $this->db->where('api_token', $token)->get('resellers')->row();
        if (!$reseller) return $this->json(['success' => false, 'message' => 'Invalid token'], 401);

        $this->json([
            'success' => true,
            'reseller' => [
                'id' => $reseller->id,
                'name' => $reseller->name,
                'email' => $reseller->email,
                'admin_name' => $reseller->admin_name,
                'status' => $reseller->status
            ]
        ]);
    }

    /**
     * Change password for a reseller (matches update_profile structure)
     */
    public function change_password($id = 0)
    {
        if (!$id) {
            return $this->json(['success' => false, 'message' => 'Reseller ID required'], 400);
        }

        // Check if reseller exists
        $reseller = $this->db->where('id', $id)->get('resellers')->row();
        if (!$reseller) {
            return $this->json(['success' => false, 'message' => 'Reseller not found'], 404);
        }

        $input = json_decode(file_get_contents('php://input'));

        // Required fields
        if (empty($input->current_password)) {
            return $this->json(['success' => false, 'message' => 'Current password is required'], 400);
        }
        if (empty($input->new_password)) {
            return $this->json(['success' => false, 'message' => 'New password is required'], 400);
        }

        // Verify current password
        if ($reseller->password !== md5($input->current_password)) {
            return $this->json(['success' => false, 'message' => 'Current password is incorrect'], 400);
        }

        // Optional: prevent same password
        if ($input->current_password === $input->new_password) {
            return $this->json(['success' => false, 'message' => 'New password must be different'], 400);
        }

        // Hash and update only the password
        $update_data = [
            'password' => md5($input->new_password)
        ];

        $this->db->where('id', $id)->update('resellers', $update_data);

        return $this->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    }
}
