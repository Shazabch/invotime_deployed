<?php

/**
 * SQL Payroll API Controller
 * Optimized & Fixed for PHP < 7.4 Compatibility
 */

class Employee_api extends CI_Controller
{
    private $_sql_data_cache = [];
    private $API_KEY = 'inv-T1m3-P@yr0ll-2026-s3cur3K3y!';
    private $company_id = null;

    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Validate the Authorization: Bearer <key> header.
     * Returns true if valid, otherwise sends 401 and exits.
     * Also sets $this->company_id from the token.
     */
    private function authenticate()
    {
        $header = $this->input->get_request_header('Authorization', TRUE);
        if (!$header || strpos($header, 'Bearer ') !== 0) {
            $this->response_json(['status' => 'error', 'message' => 'Missing or invalid Authorization header'], 401);
            return false;
        }
        $token = substr($header, 7); // strip "Bearer "
        // match token from bearer_tokens table with status = 1 and expires_at > now()
        $this->db->select('id, token, company_id, status, expires_at');
        $query = $this->db->get_where('bearer_tokens', ['token' => $token, 'status' => 1]);

        if ($query->num_rows() === 0) {
            $this->response_json(['status' => 'error', 'message' => 'Invalid or inactive token'], 401);
            return false;
        }

        $token_data = $query->row();

        // Check expiry if set
        if (!empty($token_data->expires_at) && strtotime($token_data->expires_at) < time()) {
            $this->response_json(['status' => 'error', 'message' => 'Token has expired'], 401);
            return false;
        }

        // Store company_id for use in queries
        $this->company_id = (int)$token_data->company_id;

        return true;
    }
      private function response_json($data, $status_code = 200)
    {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    

   

    /**
     * API Endpoint: GET /v2/Employee_api/getEmployees
     *
     * Returns employee master data for the authenticated company.
     * company_id is derived from bearer token and not accepted from request input.
        *
        * Query Params:
        * - employee_name (optional): Filters by first/last/full name.
        * - limit (optional): Pagination limit (default 200, max 500).
        * - offset (optional): Pagination offset (default 0).
     */
    public function getEmployees()
    {
        if (!$this->authenticate()) {
            return;
        }

        if (empty($this->company_id)) {
            $this->response_json([
                'status' => 'error',
                'message' => 'Unable to resolve company from token'
            ], 401);
            return;
        }

        $limit = (int) $this->input->get('limit', true);
        $offset = (int) $this->input->get('offset', true);
        $employee_name = trim((string) $this->input->get('employee_name', true));
        $limit = $limit > 0 ? min($limit, 500) : 100;
        $offset = $offset > 0 ? $offset : 0;

        $this->db->select([
            'e.id AS employee_id',
'e.email',
            'e.employee_status',    
            'e.hired_on',
            'e.mobile',
            'e.special_id AS employee_code',
            "TRIM(CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, ''))) AS employee_name",
            'd.name AS department',
            'p.title AS position',
            'b.name AS outlet'
        ]);
        $this->db->from('employees e');
        $this->db->join('departments d', 'd.id = e.department_id', 'left');
        $this->db->join('positions p', 'p.id = e.position_id', 'left');
        $this->db->join('branches b', 'b.id = e.branch_id', 'left');
        $this->db->where('e.company_id', (int) $this->company_id);
        $this->db->where('e.deleted_at IS NULL');
        $this->db->where('e.employee_status', 'active');
        if ($employee_name !== '') {
            $this->db->group_start();
            $this->db->like('e.first_name', $employee_name);
            $this->db->or_like('e.last_name', $employee_name);
            $this->db->or_like("CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, ''))", $employee_name, 'both', false);
            $this->db->group_end();
        }
        $this->db->order_by('e.special_id', 'ASC');
        $this->db->limit($limit, $offset);

        $query = $this->db->get();

        if ($query === false) {
            $this->response_json([
                'status' => 'error',
                'message' => 'Database query failed: ' . $this->db->error()['message']
            ], 500);
            return;
        }

        $employees = $query->result_array();

        $this->response_json([
            'status' => 'success',
            'company_id' => (int) $this->company_id,
            'count' => count($employees),
            'filters' => [
                'employee_name' => $employee_name
            ],
            'limit' => $limit,
            'offset' => $offset,
            'data' => $employees
        ], 200);
    }

}
