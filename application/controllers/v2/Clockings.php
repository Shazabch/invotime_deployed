<?php

/**
 * SQL Payroll API Controller
 * Optimized & Fixed for PHP < 7.4 Compatibility
 */

class Clockings extends CI_Controller
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

    public function getClockings()
    {
        if (!$this->authenticate()) {
            return;
        }

        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);

        // Accept both GET and POST (JSON body / form-data / query string)
        $raw = file_get_contents("php://input");
        $input = json_decode($raw, true);
        if (empty($input) || !is_array($input)) {
            // Fallback: merge GET and POST form data
            $input = array_merge($_GET, $_POST);
        }

        //fetch clocking data(Name,Emp ID,Device,Location,Type,Datetime) from clockings_news
        $this->db->select('cn.employee_id, d.mac_address as device_id, cn.type, cn.datetime, d.location, e.first_name as employee_name');
        $this->db->from('clockings_news cn');
        $this->db->join('employees e', 'cn.employee_id = e.id', 'left');
        $this->db->join('devices d', 'cn.device_id = d.device_id', 'left');

        // Always filter by company_id from token
        if ($this->company_id) {
            $this->db->where('e.company_id', $this->company_id);
        }

        // Apply filters efficiently
        if (!empty($input['company_id'])) {
            is_array($input['company_id']) ? $this->db->where_in('e.company_id', $input['company_id']) : $this->db->where('e.company_id', $input['company_id']);
        }
        if (!empty($input['employee_id'])) {
            is_array($input['employee_id']) ? $this->db->where_in('cn.employee_id', $input['employee_id']) : $this->db->where('cn.employee_id', $input['employee_id']);
        }
        if (!empty($input['device_id'])) {
            is_array($input['device_id']) ? $this->db->where_in('cn.device_id', $input['device_id']) : $this->db->where('cn.device_id', $input['device_id']);
        }
        if (!empty($input['type'])) {
            is_array($input['type']) ? $this->db->where_in('cn.type', $input['type']) : $this->db->where('cn.type', $input['type']);
        }

        if (!empty($input['datetime_from'])) $this->db->where('cn.datetime >=', $input['datetime_from']);
        if (!empty($input['datetime_to'])) $this->db->where('cn.datetime <=', $input['datetime_to']);

        // Pagination
        $limit = !empty($input['limit']) ? (int)$input['limit'] : 20;
        $offset = !empty($input['offset']) ? (int)$input['offset'] : 0;
        $this->db->limit($limit, $offset);

        $this->db->order_by('cn.datetime', 'DESC');

        $query = $this->db->get();

        if ($query === false) {
            $this->response_json([
                'status' => 'error',
                'message' => 'Database query failed: ' . $this->db->error()['message']
            ], 500);
            return;
        }

        $clocking_data = $query->result_array();

        $response = [
            'status' => 'success',
            'count' => count($clocking_data),
            'limit' => $limit,
            'offset' => $offset,
            'data' => $clocking_data
        ];

        $this->response_json($response, 200);
    }

}
