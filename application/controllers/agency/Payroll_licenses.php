<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'controllers/agency/Payroll_licenses.php';

class Payroll_licenses extends CI_Controller
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
        // $this->require_admin();
    }

    /**
     * List all payroll licenses with pagination
     * GET /payroll_licenses/index
     * Params: limit, offset, search, status
     */
    public function index()
    {
        $input = $this->get_input();

        $this->db->select('payroll_licenses.*, companies.name as company_name')
            ->from('payroll_licenses')
            ->join('companies', 'companies.id = payroll_licenses.company_id', 'left');

        // Search filter
        if (!empty($input['search'])) {
            $this->db->group_start();
            $this->db->like('token_name', $input['search']);
            $this->db->or_like('description', $input['search']);
            $this->db->or_like('token', $input['search']);
            $this->db->group_end();
        }

        // Status filter
        if (isset($input['status'])) {
            $this->db->where('status', $input['status']);
        }

        // Pagination
        $limit = !empty($input['limit']) ? (int)$input['limit'] : 50;
        $offset = !empty($input['offset']) ? (int)$input['offset'] : 0;
        $this->db->limit($limit, $offset);

        $this->db->order_by('created_at', 'DESC');

        $query = $this->db->get();

        if ($query === false) {
            $this->response_json([
                'status' => 'error',
                'message' => 'Database query failed'
            ], 500);
            return;
        }

        $data = $query->result_array();

        $this->response_json([
            'status' => 'success',
            'count' => count($data),
            'limit' => $limit,
            'offset' => $offset,
            'data' => $data
        ]);
    }

    /**
     * View a specific payroll license
     * GET /payroll_licenses/view/{id}
     */
    public function view($id = null)
    {
        if (empty($id)) {
            $this->response_json([
                'status' => 'error',
                'message' => 'License ID is required'
            ], 400);
            return;
        }

        $query = $this->db->select('payroll_licenses.*, companies.name as company_name')
            ->from('payroll_licenses')
            ->join('companies', 'companies.id = payroll_licenses.company_id', 'left')
            ->where('payroll_licenses.id', $id)
            ->get();

        if ($query === false) {
            $this->response_json([
                'status' => 'error',
                'message' => 'Database query failed'
            ], 500);
            return;
        }

        $token = $query->row_array();

        if (empty($token)) {
            $this->response_json([
                'status' => 'error',
                'message' => 'License not found'
            ], 404);
            return;
        }

        $this->response_json([
            'status' => 'success',
            'data' => $token
        ]);
    }

    /**
     * Generate a new payroll license
     * POST /payroll_licenses/generate
     * Body: token_name, description, company_id, expires_at (optional), status (default: 1)
     */
    public function generate()
    {
        $input = $this->get_input();

        // Validation
        if (empty($input['token_name'])) {
            $this->response_json([
                'status' => 'error',
                'message' => 'Token name is required'
            ], 400);
            return;
        }

        if (empty($input['company_id'])) {
            $this->response_json([
                'status' => 'error',
                'message' => 'Company ID is required'
            ], 400);
            return;
        }

        // Generate unique token
        $token = $this->generate_unique_token();

        $data = [
            'token' => $token,
            'token_name' => $input['token_name'],
            'description' => $input['description'] ?? '',
            'company_id' => (int)$input['company_id'],
            'status' => $input['status'] ?? 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (!empty($input['expires_at'])) {
            $data['expires_at'] = $input['expires_at'];
        }

        $inserted = $this->db->insert('payroll_licenses', $data);

        if (!$inserted) {
            $this->response_json([
                'status' => 'error',
                'message' => 'Failed to generate license'
            ], 500);
            return;
        }

        $insert_id = $this->db->insert_id();
        $data['id'] = $insert_id;

        $this->response_json([
            'status' => 'success',
            'message' => 'Payroll license generated successfully',
            'data' => $data
        ], 201);
    }

    /**
     * Delete a payroll license
     * DELETE /payroll_licenses/delete/{id}
     */
    public function delete($id = null)
    {
        if (empty($id)) {
            $this->response_json([
                'status' => 'error',
                'message' => 'License ID is required'
            ], 400);
            return;
        }

        // Check if license exists
        $query = $this->db->get_where('payroll_licenses', ['id' => $id]);
        if (empty($query) || $query->num_rows() === 0) {
            $this->response_json([
                'status' => 'error',
                'message' => 'License not found'
            ], 404);
            return;
        }

        $deleted = $this->db->delete('payroll_licenses', ['id' => $id]);

        if (!$deleted) {
            $this->response_json([
                'status' => 'error',
                'message' => 'Failed to delete license'
            ], 500);
            return;
        }

        $this->response_json([
            'status' => 'success',
            'message' => 'Payroll license deleted successfully'
        ]);
    }

    /**
     * Update payroll license expiry and status
     * PUT/POST /payroll_licenses/update/{id}
     * Body: expires_at, status, company_id
     */
    public function update($id = null)
    {
        if (empty($id)) {
            $this->response_json([
                'status' => 'error',
                'message' => 'License ID is required'
            ], 400);
            return;
        }

        $input = $this->get_input();

        // Check if license exists
        $query = $this->db->get_where('payroll_licenses', ['id' => $id]);
        if (empty($query) || $query->num_rows() === 0) {
            $this->response_json([
                'status' => 'error',
                'message' => 'License not found'
            ], 404);
            return;
        }

        // Build update data
        $update_data = ['updated_at' => date('Y-m-d H:i:s')];

        if (isset($input['status'])) {
            $update_data['status'] = (int)$input['status'];
        }

        if (isset($input['company_id'])) {
            $update_data['company_id'] = (int)$input['company_id'];
        }

        if (isset($input['expires_at'])) {
            $update_data['expires_at'] = $input['expires_at'] === '' || $input['expires_at'] === null ? null : $input['expires_at'];
        }

        // Ensure at least one field to update
        if (count($update_data) === 1) {
            $this->response_json([
                'status' => 'error',
                'message' => 'No fields to update. Provide status or expires_at'
            ], 400);
            return;
        }

        $this->db->where('id', $id);
        $updated = $this->db->update('payroll_licenses', $update_data);

        if (!$updated) {
            $this->response_json([
                'status' => 'error',
                'message' => 'Failed to update license'
            ], 500);
            return;
        }

        // Get updated license
        $updated_license = $this->db->get_where('payroll_licenses', ['id' => $id])->row_array();

        $this->response_json([
            'status' => 'success',
            'message' => 'Payroll license updated successfully',
            'data' => $updated_license
        ]);
    }

    /**
     * Helper: Generate a unique payroll license token
     */
    private function generate_unique_token()
    {
        do {
            $token = 'plt_' . bin2hex(random_bytes(32));
            $exists = $this->db->get_where('payroll_licenses', ['token' => $token])->num_rows() > 0;
        } while ($exists);

        return $token;
    }

    /**
     * Helper: Get input from JSON body or query params
     */
    private function get_input()
    {
        $raw = file_get_contents("php://input");
        $input = json_decode($raw, true);
        if (empty($input) || !is_array($input)) {
            $input = array_merge($_GET, $_POST);
        }
        return $input;
    }

    /**
     * Helper: Send JSON response
     */
    private function response_json($data, $status_code = 200)
    {
        http_response_code($status_code);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }


}
