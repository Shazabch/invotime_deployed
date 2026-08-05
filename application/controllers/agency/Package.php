<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Package extends CI_Controller
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
    /**
     * JSON response helper
     */
    private function json($data, $status_code = 200)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_status_header($status_code)
            ->set_output(json_encode($data));
    }

    // ==================================================================
    // 2. PACKAGES - FULL CRUD
    // ==================================================================
    public function index()
    {
        $search = $this->input->get('search');

        $this->db->order_by('id', 'DESC');

        if (!empty($search)) {

            $this->db->group_start();
            $this->db->like('name', $search);
            $this->db->or_like('max_active_staff', $search);
            $this->db->or_like('max_active_staff', $search);

            $this->db->group_end();
        }

        $packages = $this->db->get('packages')->result();

        $this->json(['success' => true, 'packages' => $packages]);
    }
    public function view($id)
    {
        // Fetch record by ID
        $package = $this->db->where('id', $id)->get('packages')->row();

        // If no record found
        if (!$package) {
            return $this->json([
                'success' => false,
                'message' => 'Package not found'
            ]);
        }

        // Return response
        return $this->json([
            'success' => true,
            'package' => $package
        ]);
    }

    public function create()
    {
        $input = json_decode(file_get_contents('php://input'));
        if (empty($input->name)) return $this->json(['success' => false, 'message' => 'Package name required'], 400);

        $this->db->insert('packages', [
            'name'             => $input->name,
            'max_outlets'      => $input->max_outlets ?? 0,
            'max_active_staff' => $input->max_active_staff ?? 0
        ]);

        $this->json(['success' => true, 'message' => 'Package created successfully']);
    }

    public function update($id = 0)
    {
        if (!$id) return $this->json(['success' => false, 'message' => 'Package ID required'], 400);
        $input = json_decode(file_get_contents('php://input'));
        $this->db->where('id', $id)->update('packages', (array)$input);
        $this->json(['success' => true, 'message' => 'Package updated successfully']);
    }

    public function delete($id = 0)
    {
        if (!$id) return $this->json(['success' => false, 'message' => 'Package ID required'], 400);
        $this->db->where('id', $id)->delete('packages');
        $this->json(['success' => true, 'message' => 'Package deleted successfully']);
    }
}
