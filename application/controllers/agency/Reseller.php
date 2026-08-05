<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reseller extends CI_Controller
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
            ->set_status_header($status_code)
            ->set_output(json_encode($data));
    }

    // ==================================================================
    // 4. RESELLERS - FULL CRUD + SEARCH
    // ==================================================================

    /**
     * List resellers + Search
     */
    public function index()
    {
        $search = $this->input->get('search');
        $admin_id = $this->input->get('admin_id');

        $this->db->select("
        r.*,
        COUNT(c.id) AS companies_count
    ");
        $this->db->from("resellers r");
        $this->db->join("companies c", "c.reseller_id = r.id", "left");

        // Apply search
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('r.name', $search);
            $this->db->or_like('r.email', $search);
            $this->db->or_like('r.admin_name', $search);
            $this->db->or_like('r.contact', $search);
            $this->db->group_end();
        }

        if (!empty($admin_id)) {
            $this->db->where('r.admin_id', $admin_id);
        }

        $this->db->group_by("r.id");
        $this->db->order_by("r.id", "DESC");

        $resellers = $this->db->get()->result();


        return $this->json([
            'success' => true,
            'resellers' => $resellers
        ]);
    }

    public function getCompanies($reseller_id)
    {

        $data["companies"] = $this->db->select('id, name')->from('companies')->where('reseller_id', $reseller_id)->get()->result();
        $data["success"] = true;
        echo json_encode($data);
    }
    public function myCompanies($reseller_id)
    {
        try {
            if (empty($reseller_id)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Reseller ID is required'
                ], 400);
            }

            // Get query parameters
            $package_id    = $this->input->get('package_id');
            $status        = $this->input->get('status');
            $search        = $this->input->get('search');
            $limit         = $this->input->get('limit') ? (int)$this->input->get('limit') : 50;
            $offset        = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;
            $with_package  = $this->input->get('with_package') !== 'false';
            $with_stats    = $this->input->get('with_stats') === 'true';
            $sort_by = $this->input->get('sort_by') ? $this->input->get('sort_by') : 'daysLeft';
            $sort_order = $this->input->get('sort_order') ? strtoupper($this->input->get('sort_order')) : 'ASC';

            // Validate sort_order
            if (!in_array($sort_order, ['ASC', 'DESC'])) {
                $sort_order = 'ASC';
            }

            // Base select
            $this->db->select('c.*');

            // Count active staff: employee_status='active', NOT deleted, role.exclude_from_system = 'no'
            $this->db->select("(
                SELECT COUNT(e.id)
                FROM employees e
                JOIN roles r ON r.id = e.role_id
                WHERE e.company_id = c.id
                AND e.employee_status = 'active'
                AND e.deleted_at IS NULL
                AND r.exclude_from_system = 'no'
            ) AS active_staff", false);

            // Count total staff: all non-deleted employees
            $this->db->select("(
                SELECT COUNT(e.id)
                FROM employees e
                WHERE e.company_id = c.id
                AND e.deleted_at IS NULL
            ) AS total_staff", false);

            // Compute expiry_date consistent with Company controller
            $this->db->select("CASE\n    WHEN c.contract_months IS NULL OR c.contract_months <= 0 OR TRIM(c.contract_months) = '' THEN NULL\n    WHEN COALESCE(NULLIF(TRIM(c.last_renewal_date), ''), NULLIF(TRIM(c.start_date), '')) IS NULL THEN NULL\n    WHEN COALESCE(NULLIF(TRIM(c.last_renewal_date), '0000-00-00'), NULLIF(TRIM(c.start_date), '0000-00-00')) IS NULL THEN NULL\n    ELSE LAST_DAY(\n        DATE_ADD(\n            COALESCE(\n                NULLIF(TRIM(c.last_renewal_date), '0000-00-00'),\n                NULLIF(TRIM(c.last_renewal_date), ''),\n                NULLIF(TRIM(c.start_date), '0000-00-00'),\n                NULLIF(TRIM(c.start_date), '')\n            ),\n            INTERVAL c.contract_months - 1 MONTH\n        )\n    )\nEND AS expiry_date", false);

            // Compute days_left safely (matches Company controller)
            $this->db->select("CASE\n    WHEN c.status = 'terminated' THEN NULL\n    WHEN c.contract_months IS NULL OR c.contract_months <= 0 OR TRIM(c.contract_months) = '' THEN NULL\n    WHEN COALESCE(NULLIF(TRIM(c.last_renewal_date), ''), NULLIF(TRIM(c.start_date), '')) IS NULL THEN NULL\n    WHEN COALESCE(NULLIF(TRIM(c.last_renewal_date), '0000-00-00'), NULLIF(TRIM(c.start_date), '0000-00-00')) IS NULL THEN NULL\n    ELSE DATEDIFF(\n        LAST_DAY(\n            DATE_ADD(\n                COALESCE(\n                    NULLIF(TRIM(c.last_renewal_date), '0000-00-00'),\n                    NULLIF(TRIM(c.last_renewal_date), ''),\n                    NULLIF(TRIM(c.start_date), '0000-00-00'),\n                    NULLIF(TRIM(c.start_date), '')\n                ),\n                INTERVAL c.contract_months - 1 MONTH\n            )\n        ),\n        CURDATE()\n    )\nEND AS days_left", false);

            $this->db->from('companies c');

            // Always join packages table (needed for max_active_staff calculation)
            $this->db->join('packages p', 'p.id = c.package', 'left');

            // Calculate max_active_staff (package limit + additional staff) after join
            $this->db->select("(p.max_active_staff + COALESCE(c.additional_staff, 0)) AS max_active_staff", false);

            // Add package fields if requested
            if ($with_package) {
                $this->db->select('p.id as package_id, p.name as package_name, p.max_outlets');
            }

            // Mandatory reseller filter
            $this->db->where('c.reseller_id', $reseller_id);

            // Optional filters
            if (!empty($package_id)) {
                $this->db->where('c.package', $package_id);
            }

            if (!empty($status)) {
                $this->db->where('c.status', $status);
            }

            // Search
            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('c.name', $search);
                $this->db->or_like('c.phone', $search);
                $this->db->or_like('c.address', $search);
                $this->db->or_like('c.email', $search);
                $this->db->group_end();
            }

            // Clone for total count
            $count_query = clone $this->db;
            $total = $count_query->count_all_results();

            // Apply ordering based on sort_by parameter
            if ($sort_by === 'daysLeft') {
                $this->db->order_by('days_left IS NULL', 'ASC', false); // non-null first, NULLs last
                $this->db->order_by('days_left', $sort_order, false);   // min→max or max→min
                $this->db->order_by('c.id', $sort_order);               // tie-break in same direction
            } else {
                // Default sorting if no valid sort_by specified
                $this->db->order_by('days_left IS NULL', 'ASC', false);
                $this->db->order_by('days_left', 'ASC', false);
                $this->db->order_by('c.id', 'ASC');
            }
            $this->db->limit($limit, $offset);

            $query = $this->db->get();

            if (!$query) {
                throw new Exception($this->db->error()['message']);
            }

            $companies = $query->result();

            // Stats
            if ($with_stats && !empty($companies)) {
                foreach ($companies as $company) {
                    $company->total_users   = $this->getCompanyUsersCount($company->id);
                    $company->active_users  = $this->getActiveUsersCount($company->id);
                    $company->storage_used  = $this->getCompanyStorageUsed($company->id);
                }
            }

            return $this->json([
                'success' => true,
                'data' => $companies,
                'pagination' => [
                    'total' => (int)$total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => ($offset + count($companies)) < $total
                ]
            ]);
        } catch (Exception $e) {
            log_message('error', 'MyCompanies API Error: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'message' => 'Failed to fetch reseller companies',
                'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    // ==================================================================
    // HELPER FUNCTIONS with error handling
    // ==================================================================

    private function getCompanyUsersCount($company_id)
    {
        try {
            $this->db->where('company_id', $company_id);
            return $this->db->count_all_results('users');
        } catch (Exception $e) {
            log_message('error', 'getCompanyUsersCount Error: ' . $e->getMessage());
            return 0;
        }
    }

    private function getActiveUsersCount($company_id)
    {
        try {
            $this->db->where('company_id', $company_id)
                ->where('status', 'active');
            return $this->db->count_all_results('users');
        } catch (Exception $e) {
            log_message('error', 'getActiveUsersCount Error: ' . $e->getMessage());
            return 0;
        }
    }

    private function getCompanyStorageUsed($company_id)
    {
        // Placeholder - implement based on your actual storage structure
        return 0;
    }
    /**
     * View single reseller
     */
    public function view($id)
    {
        $reseller = $this->db->where('id', $id)->get('resellers')->row();

        if (!$reseller) {
            return $this->json(['success' => false, 'message' => 'Reseller not found'], 404);
        }
        $reseller->companies_count = $this->db->select('id')->from('companies')->where('reseller_id', $reseller->id)->get()->num_rows();
        $reseller->companies = $this->db->select('id, name')->from('companies')->where('reseller_id', $reseller->id)->get()->result();

        return $this->json(['success' => true, 'reseller' => $reseller]);
    }


    /**
     * Create a reseller
     */
    public function create()
    {
        $input = json_decode(file_get_contents('php://input'));

        // Basic validation
        if (empty($input->email)) return $this->json(['success' => false, 'message' => 'Email required'], 400);
        if (empty($input->password)) return $this->json(['success' => false, 'message' => 'Password required'], 400);
        if (empty($input->name)) return $this->json(['success' => false, 'message' => 'Name required'], 400);

        // Email must be unique
        $exists = $this->db->where('email', $input->email)->get('resellers')->row();
        if ($exists) {
            return $this->json(['success' => false, 'message' => 'Email already exists'], 409);
        }

        // Hash password
        $input->password = md5($input->password);
        $this->db->insert('resellers', (array)$input);

        return $this->json(['success' => true, 'message' => 'Reseller created']);
    }


    /**
     * Update reseller
     */
    public function update($id = 0)
    {
        if (!$id) return $this->json(['success' => false, 'message' => 'Reseller ID required'], 400);

        $input = json_decode(file_get_contents('php://input'));

        if (!empty($input->password)) {
            $input->password = md5($input->password);
        } else {
            unset($input->password);
        }

        // Prevent email duplication
        if (!empty($input->email)) {
            $exists = $this->db->where('email', $input->email)
                ->where('id !=', $id)
                ->get('resellers')
                ->row();

            if ($exists) {
                return $this->json(['success' => false, 'message' => 'Email already taken'], 409);
            }
        }

        $this->db->where('id', $id)->update('resellers', (array)$input);

        return $this->json(['success' => true, 'message' => 'Reseller updated']);
    }


    /**
     * Delete reseller
     */
    public function delete($id = 0)
    {
        if (!$id) return $this->json(['success' => false, 'message' => 'Reseller ID required'], 400);
        $reseller = $this->db->where('id', $id)->get('resellers')->row();

        if (!$reseller) {
            return $this->json(['success' => false, 'message' => 'Reseller not found'], 404);
        }
        $this->db->where('id', $id)->delete('resellers');

        return $this->json(['success' => true, 'message' => 'Reseller deleted']);
    }
}
