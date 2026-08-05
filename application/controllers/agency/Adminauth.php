<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Adminauth extends CI_Controller
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

        $admin = $this->db->where('email', $input->email)
            ->where('password', md5($input->password))
            ->where('status', 'active')
            ->get('admins')
            ->row();

        if (!$admin) {
            return $this->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        // Generate token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours')); // token valid 24 hours

        $this->db->where('id', $admin->id)->update('admins', [
            'api_token' => $token,
            'token_expiry' => $expiry
        ]);

        $this->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role
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

        $admin = $this->db->where('api_token', $token)->get('admins')->row();
        if ($admin) {
            $this->db->where('id', $admin->id)->update('admins', ['api_token' => null, 'token_expiry' => null]);
        }

        $this->json(['success' => true, 'message' => 'Logged out']);
    }

    // =========================================================================
    // 3. GET CURRENT ADMIN
    // =========================================================================
    public function me()
    {
        $token = $this->input->get_request_header('Authorization');
        if (!$token) return $this->json(['success' => false, 'message' => 'Token required'], 400);

        $admin = $this->db->where('api_token', $token)
                          ->where('token_expiry >=', date('Y-m-d H:i:s'))
                          ->get('admins')->row();

        if (!$admin) return $this->json(['success' => false, 'message' => 'Invalid or expired token'], 401);

        $this->json([
            'success' => true,
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
                'status' => $admin->status
            ]
        ]);
    }
}
