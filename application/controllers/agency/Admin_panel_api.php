<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Admin_panel_api extends CI_Controller
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

    private function json($data, $code = 200)
    {
        $this->output->set_status_header($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }





    // ==================================================================
    // 3. ANNOUNCEMENTS - FULL CRUD
    // ==================================================================
    public function announcements()
    {
        $announcements = $this->db
            ->select('id, title, announcement, active, created_at')
            ->order_by('created_at', 'DESC')
            ->get('old_announcements')
            ->result();

        $this->json(['success' => true, 'announcements' => $announcements]);
    }

    public function add_announcement()
    {
        $input = json_decode(file_get_contents('php://input'));
        if (empty($input->title) || empty($input->announcement)) {
            return $this->json(['success' => false, 'message' => 'Title and announcement required'], 400);
        }

        $this->db->insert('old_announcements', [
            'title'        => $input->title,
            'announcement' => $input->announcement,
            'active'       => $input->active ?? '1',
            'created_at'   => date('Y-m-d H:i:s')
        ]);

        $this->json(['success' => true, 'message' => 'Announcement added']);
    }

    public function update_announcement($id = 0)
    {
        if (!$id) return $this->json(['success' => false, 'message' => 'Announcement ID required'], 400);
        $input = json_decode(file_get_contents('php://input'));
        $this->db->where('id', $id)->update('old_announcements', (array)$input);
        $this->json(['success' => true, 'message' => 'Announcement updated']);
    }

    public function delete_announcement($id = 0)
    {
        if (!$id) return $this->json(['success' => false, 'message' => 'Announcement ID required'], 400);
        $this->db->where('id', $id)->delete('old_announcements');
        $this->json(['success' => true, 'message' => 'Announcement deleted']);
    }



    // ==================================================================
    // 6. SUBSCRIPTIONS & RESET PASSWORD (Already Working)
    // ==================================================================
    public function subscriptions()
    {
        $input = json_decode(file_get_contents('php://input'));
        if (empty($input->company_id)) return $this->json(['success' => false, 'message' => 'company_id required'], 400);

        $subscriptions = $this->db->where('company_id', $input->company_id)
            ->order_by('created_at', 'DESC')
            ->get('subscriptions')->result();

        $this->json(['success' => true, 'subscriptions' => $subscriptions]);
    }

    public function reset_password()
    {
        $input = json_decode(file_get_contents('php://input'));
        if (empty($input->email) || empty($input->password)) return $this->json(['success' => false, 'message' => 'email & password required'], 400);

        $user = $this->db->where('email', $input->email)->get('employees')->row();
        if ($user) {
            $this->db->set('password', md5($input->password))->where('id', $user->id)->update('employees');
            $this->json(['success' => true, 'message' => 'Password reset successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Email not found'], 404);
        }
    }


}
