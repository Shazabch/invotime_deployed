<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Outlet extends CI_Controller
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
            ->set_output(json_encode($data));
    }


    // =========================================================================
    // 1. LIST OUTLETS (INDEX)
    // =========================================================================
    public function index()
    {
        $company_id = $this->input->get('company');

        if ($company_id) {
            $this->db->where('company_id', $company_id);
        }

        $list = $this->db->order_by('id', 'DESC')->get('branches')->result();

        $this->json(['success' => true, 'outlets' => $list]);
    }


    // =========================================================================
    // 2. VIEW SINGLE OUTLET
    // =========================================================================
    public function view($id)
    {
        $row = $this->db->where('id', $id)->get('branches')->row();

        if (!$row) {
            return $this->json(['success' => false, 'message' => 'Outlet not found'], 404);
        }

        $this->json(['success' => true, 'outlet' => $row]);
    }


    // =========================================================================
    // 3. CREATE SINGLE OUTLET
    // =========================================================================
    public function create()
    {
        $input = json_decode(file_get_contents("php://input"));

        $required = ['name', 'address', 'phone', 'admin', 'email', 'password', 'company'];
        foreach ($required as $f)
            if (empty($input->$f)) return $this->json(['success' => false, 'message' => "$f required"], 400);

        return $this->createOutlet($input);
    }


    // =========================================================================
    // 4. CREATE MULTIPLE OUTLETS
    // =========================================================================
    public function create_multiple()
    {
        $input = json_decode(file_get_contents("php://input"));
        if (empty($input->company) || empty($input->outlets) || !is_array($input->outlets)) {
            return $this->json(['success' => false, 'message' => 'company & outlets array required'], 400);
        }

        $company_id = $input->company;
        $outletList = $input->outlets;

        // Check package outlet limit
        $pkg = $this->db->select("p.max_outlets")
            ->join("packages p", "p.id=c.package")
            ->where("c.id", $company_id)
            ->get("companies c")->row();

        if ($pkg && $pkg->max_outlets > 0) {
            $current = $this->db->where("company_id", $company_id)->get("branches")->num_rows();
            if ($current + count($outletList) > $pkg->max_outlets) {
                return $this->json(['success' => false, 'message' => 'Outlet limit exceeded'], 403);
            }
        }

        $created = [];
        foreach ($outletList as $o) {
            $obj = (object)$o;

            // validate each outlet
            $required = ['name', 'address', 'phone', 'admin', 'email', 'password'];
            foreach ($required as $f)
                if (empty($obj->$f))
                    return $this->json(['success' => false, 'message' => "$f required for each outlet"], 400);

            $obj->company = $company_id;
            $created[] = $this->createOutlet($obj, false); // false = do not output json
        }

        return $this->json(['success' => true, 'message' => 'All outlets created', 'data' => $created]);
    }


    // =========================================================================
    // PRIVATE FUNCTION USED BY BOTH create & create_multiple
    // =========================================================================
    private function createOutlet($input, $respond = true)
    {
        $company_id = $input->company;

        // check email exists
        if ($this->db->where('email', $input->email)
            ->where("deleted_at IS NULL", null, false)
            ->get("employees")->row()
        ) {
            return $respond ?
                $this->json(['success' => false, 'message' => 'Email already exists'], 409)
                : ['error' => 'Email exists'];
        }

        // Check package outlet limit for single create
        $pkg = $this->db->select("p.max_outlets")
            ->join("packages p", "p.id=c.package")
            ->where("c.id", $company_id)
            ->get("companies c")->row();

        if ($pkg && $pkg->max_outlets > 0) {
            $current = $this->db->where("company_id", $company_id)->get("branches")->num_rows();
            if ($current + 1 > $pkg->max_outlets) {
                return $respond ?
                    $this->json(['success' => false, 'message' => 'Outlet limit exceeded'], 403)
                    : ['error' => 'Outlet limit exceeded'];
            }
        }

        // Create outlet
        $this->db->insert('branches', [
            'company_id' => $company_id,
            'name'       => $input->name,
            'address'    => $input->address,
            'phone'      => $input->phone
        ]);
        $branch_id = $this->db->insert_id();

        // Ensure Outlet Admin role exists
        $role = $this->db->where('company_id', $company_id)
            ->where('permissions', 'everything')
            ->where('permissions_level', 'Outlet')
            ->get('roles')->row();

        if (!$role) {
            $this->db->insert('roles', [
                'company_id' => $company_id,
                'job_name' => 'Outlet Admin',
                'permissions' => 'everything',
                'permissions_level' => 'Outlet',
                'exclude_from_system' => 'yes'
            ]);
            $role_id = $this->db->insert_id();
        } else {
            $role_id = $role->id;
        }

        // Create employee for outlet
        $this->db->insert('employees', [
            'first_name' => $input->admin,
            'email'      => $input->email,
            'password'   => md5($input->password),
            'company_id' => $company_id,
            'branch_id'  => $branch_id,
            'role_id'    => $role_id
        ]);

        if ($respond)
            return $this->json(['success' => true, 'message' => 'Outlet created successfully'], 201);

        return ['branch_id' => $branch_id];
    }


    // =========================================================================
    // 5. UPDATE SINGLE OUTLET
    // =========================================================================
    public function update($id)
    {
        $input = json_decode(file_get_contents("php://input"));
        if (!$id) return $this->json(['success' => false, 'message' => 'Outlet ID required'], 400);

        $this->db->where('id', $id)->update('branches', (array)$input);
        return $this->json(['success' => true, 'message' => 'Outlet updated']);
    }


    // =========================================================================
    // 6. UPDATE MULTIPLE OUTLETS
    // =========================================================================
    public function update_multiple()
    {
        $input = json_decode(file_get_contents("php://input"));

        if (empty($input->outlets) || !is_array($input->outlets))
            return $this->json(['success' => false, 'message' => 'outlets array required'], 400);

        foreach ($input->outlets as $o) {
            if (empty($o['id'])) continue;
            $id = $o['id'];
            unset($o['id']);
            $this->db->where('id', $id)->update('branches', $o);
        }

        return $this->json(['success' => true, 'message' => 'All outlets updated']);
    }


    // =========================================================================
    // 7. DELETE OUTLET
    // =========================================================================
    public function delete($id)
    {
        if (!$id) return $this->json(['success' => false, 'message' => 'Outlet ID required'], 400);

        $this->db->where('id', $id)->delete('branches');
        return $this->json(['success' => true, 'message' => 'Outlet deleted']);
    }
}
