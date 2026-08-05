<?php

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class OverviewCharts extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->driver('cache', array(
            'adapter' => 'file',
            'backup'  => 'dummy'
        ));
        set_sql_mode();
        // var_dump(get_user());
        // die();
        if ($this->session->userdata("payroll_user")) {
            redirect("invocore_payroll");
        } elseif (is_null(get_user())) {
            redirect("welcome");
            //var_dump($this->session->userdata('antelope_user'));
        }

        //echo "test";
    }


    /**
     * ============================================================
     *  OVERVIEW CONTROLLER — OPTIMIZED DASHBOARD METHODS  (v2)
     *  ─────────────────────────────────────────────────────────
     *  CHANGES FROM v1:
     *   • __construct() now loads the cache driver safely
     *   • api_dashboard_today() uses the EXACT original SQL
     *     queries from your file (lines 2776-2846) — the v1
     *     rewrites caused "Call to a member function row() on bool"
     *   • All ->row() / ->result() calls are null-safe
     *   • Cache wrapped in try/catch so it never crashes the page
     *
     *  HOW TO APPLY
     *  ─────────────
     *  1. In your Overview __construct() ADD this one line after
     *     parent::__construct():
     *
     *       $this->load->driver('cache', array(
     *           'adapter' => 'file',
     *           'backup'  => 'dummy'
     *       ));
     *
     *  2. Replace your index(), overview_view_charts() and the
     *     four api_* methods with the ones below.
     *
     *  3. Make sure  application/cache/  folder exists and is
     *     writable (right-click → Properties on Windows, or
     *     chmod 777 application/cache on Linux).
     * ============================================================
     */

    // ──────────────────────────────────────────────────────────────
    //  Paste this ONE LINE inside your existing __construct()
    //  right after  parent::__construct();
    // ──────────────────────────────────────────────────────────────
    /*
    $this->load->driver('cache', array(
        'adapter' => 'file',
        'backup'  => 'dummy'   // falls back to no-cache silently
    ));
*/

    // ──────────────────────────────────────────────────────────────
    //  PRIVATE HELPERS  (add inside the Overview class)
    // ──────────────────────────────────────────────────────────────

    private function _resolve_dashboard_context()
    {
        $current_user = get_user();
        $cid          = $current_user['company_id'];
        $bid          = $current_user['branch_id'];
        $permissions  = $current_user['permissions_level'];
        $branch_id    = $this->input->get('branch_id');

        $where_branch_1 = '';
        $where_branch_2 = '';
        $where_branch_3 = '';
        $branch         = null;

        if ($permissions === 'Outlet') {
            $branch_id = $bid;
        }

        if ($branch_id) {
            $branch = $this->db->get_where('branches', ['id' => $branch_id])->row();
            if ($branch) {
                $where_branch_1 = " AND e.branch_id = $branch_id ";
                $where_branch_2 = " AND employees.branch_id = $branch_id ";
                $where_branch_3 = " AND branch_id = $branch_id ";
            }
        }

        return compact(
            'cid',
            'bid',
            'permissions',
            'branch_id',
            'branch',
            'where_branch_1',
            'where_branch_2',
            'where_branch_3'
        );
    }

    private function _json($data)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    private function _cache_key($prefix, $cid, $branch_id = 0)
    {
        return $prefix . '_' . (int)$cid . '_' . (int)$branch_id;
    }

    /** Safe cache get — never crashes if cache not ready */
    private function _cache_get($key)
    {
        try {
            return $this->cache->get($key);
        } catch (Exception $e) {
            return false;
        }
    }

    /** Safe cache save — never crashes if cache not ready */
    private function _cache_save($key, $data, $ttl = 600)
    {
        try {
            // $this->cache->save($key, $data, $ttl);
        } catch (Exception $e) {
            // silently ignore
        }
    }

    // ==============================================================
    //  index()  — Replace your existing index() with this
    // ==============================================================
    public function index()
    {
        $current_user = get_user();
        $cid          = $current_user['company_id'];
        $bid          = $current_user['branch_id'];
        $permissions  = $current_user['permissions_level'];
        $branch_id    = $this->input->get('branch_id');

        if ($permissions === 'Outlet') {
            if (empty($branch_id) || $branch_id != $bid) {
                redirect("overview?branch_id=$bid");
                return;
            }
        }

        // Only one lightweight query — branches dropdown
        if ($permissions === 'Outlet') {
            $branches = $this->db->get_where('branches', ['id' => $bid])->result();
        } else {
            $branches = $this->db->get_where('branches', ['company_id' => $cid])->result();
        }

        $data = [
            'pageTitle'   => 'Dashboard Overview',
            'active_menu' => 'overview',
            'branches'    => $branches,
            'branch_id'   => (int)$branch_id,
            'api_base'    => base_url('overview'),
        ];

        $this->load->view('header', $data);
        $data['menus'] = get_menus();
        $this->load->view('sidebar', $data);

        if (is_page_permitted('overview')) {
            $this->load->view('overview_view_charts', $data);
        } else {
            $menus = get_menus();
            if (empty($menus)) {
                $this->load->view('not_permitted');
            } else {
                $first = reset($menus);
                redirect(is_null($first['sub_menus'])
                    ? $first['url']
                    : reset($first['sub_menus'])['url']);
            }
            return;
        }

        $this->load->view('footer', $data);
    }

    // ==============================================================
    //  overview_view_charts()  — now just an alias of index()
    // ==============================================================
    public function overview_view_charts()
    {
        $this->index();
    }

    // ==============================================================
    //  API: License & company header
    //  GET  overview/api_dashboard_header?branch_id=X
    // ==============================================================
    public function api_dashboard_header()
    {
        $ctx       = $this->_resolve_dashboard_context();
        $cid       = $ctx['cid'];
        $branch_id = $ctx['branch_id'];
        $cache_key = $this->_cache_key('dash_header', $cid, $branch_id);

        if ($cached = $this->_cache_get($cache_key)) {
            return $this->_json($cached);
        }

        // Package / staff limits
        $this->db->select('companies.package, companies.additional_staff, packages.max_outlets, packages.max_active_staff');
        $this->db->join('packages', 'packages.id = companies.package', 'left');
        $this->db->where('companies.id', $cid);
        $company_details = $this->db->get('companies')->row();

        $max_active_staff = 0;
        $max_outlets      = 0;
        if ($company_details) {
            $max_active_staff = (int)$company_details->max_active_staff
                + (int)$company_details->additional_staff;
            $max_outlets = (int)$company_details->max_outlets;
        }

        // Active employee count
        $emp_row = $this->db->select('COUNT(employees.id) as cnt', false)
            ->from('employees')
            ->join('roles', 'roles.id = employees.role_id', 'left')
            ->where('employees.company_id', $cid)
            ->where('employees.employee_status', 'active')
            ->where('roles.exclude_from_system', 'no')
            ->where('employees.deleted_at is null')
            ->get()->row();
        $employees_count = $emp_row ? (int)$emp_row->cnt : 0;

        // Branch / outlet count
        $branch_row = $this->db->select('COUNT(id) as cnt', false)
            ->from('branches')
            ->where('company_id', $cid)
            ->where('deleted_at is null')
            ->get()->row();
        $outlet_count = $branch_row ? (int)$branch_row->cnt : 0;

        // License
        $license      = get_license_status_simple($cid);
        $license_html = '<span style="padding:5px;" class="badge bg-'
            . $license['class'] . '">' . $license['label'] . '</span>';

        // Announcements
        $announcements = $this->db->select('*')
            ->from('old_announcements')
            ->where('active', 1)
            ->order_by('id', 'desc')
            ->get()->result();

        $result = [
            'license_html'             => $license_html,
            'employees_of_company'     => $employees_count,
            'company_max_active_staff' => $max_active_staff,
            'company_outlets'          => $outlet_count,
            'company_max_outlets'      => $max_outlets,
            'announcements'            => $announcements,
        ];

        $this->_cache_save($cache_key, $result, 600);
        $this->_json($result);
    }

    // ==============================================================
    //  API: KPI stat boxes
    //  GET  overview/api_dashboard_stats?branch_id=X
    // ==============================================================
    public function api_dashboard_stats()
    {
        $ctx            = $this->_resolve_dashboard_context();
        $cid            = $ctx['cid'];
        $where_branch_1 = $ctx['where_branch_1'];
        $branch_id      = $ctx['branch_id'];
        $cache_key      = $this->_cache_key('dash_stats', $cid, $branch_id);

        if ($cached = $this->_cache_get($cache_key)) {
            return $this->_json($cached);
        }

        // Total active employees
        $row = $this->db->select('COALESCE(COUNT(e.id),0) as total', false)
            ->from('employees e')->join('roles r', 'e.role_id = r.id')
            ->where("e.company_id = $cid $where_branch_1")
            ->where('exclude_from_system', 'no')->where('e.deleted_at is null')
            ->where('employee_status', 'active')->get()->row();
        $t_employees = $row ? (int)$row->total : 0;

        // New employees (last 7 days)
        $row = $this->db->select('COALESCE(COUNT(e.id),0) as total', false)
            ->from('employees e')->join('roles r', 'e.role_id = r.id')
            ->where("e.company_id = $cid $where_branch_1")
            ->where('exclude_from_system', 'no')->where('e.deleted_at is null')
            ->where('employee_status', 'active')
            ->where('e.created_at >= DATE(NOW()) - INTERVAL 7 DAY')
            ->get()->row();
        $new_employees = $row ? (int)$row->total : 0;

        // Resigned
        $row = $this->db->select('COALESCE(COUNT(e.id),0) as total', false)
            ->from('employees e')->join('roles r', 'e.role_id = r.id')
            ->where("e.company_id = $cid $where_branch_1")
            ->where('exclude_from_system', 'no')->where('e.deleted_at is null')
            ->where('employee_status', 'resigned')->get()->row();
        $resignation_employees = $row ? (int)$row->total : 0;

        // Terminated
        $row = $this->db->select('COALESCE(COUNT(e.id),0) as total', false)
            ->from('employees e')->join('roles r', 'e.role_id = r.id')
            ->where("e.company_id = $cid $where_branch_1")
            ->where('exclude_from_system', 'no')->where('e.deleted_at is null')
            ->where('employee_status', 'terminated')->get()->row();
        $terminated_employees = $row ? (int)$row->total : 0;

        // Invalid clocking distance (today only)
        $row = $this->db->select('COALESCE(COUNT(c.id),0) as total', false)
            ->from('clockings_news c')
            ->join('devices e', 'e.device_id = c.device_id')
            ->join('branches b', 'b.id = e.branch_id')
            ->where("e.company_id = $cid $where_branch_1")
            ->where('c.scan_distance > b.invalid_clocking_distance')
            ->where('c.datetime >', date('Y-m-d 00:00:00'))
            ->get()->row();
        $invalid_clocking_distance = $row ? (int)$row->total : 0;

        // Turnover %
        $ex_employees = $resignation_employees + $terminated_employees;
        $turnover = ($ex_employees == 0 || $t_employees == 0)
            ? 0
            : round(($ex_employees / $t_employees) * 100, 2);

        $box_title = isset($ctx['branch']) && $ctx['branch']
            ? 'Employees in ' . $ctx['branch']->name
            : 'Employees';

        $result = [
            'box_count'                 => $t_employees,
            'box_title'                 => $box_title,
            'new_employees'             => $new_employees,
            'resignation_employees'     => $resignation_employees,
            'terminated_employees'      => $terminated_employees,
            'invalid_clocking_distance' => $invalid_clocking_distance,
            'turnover'                  => $turnover,
            'month'                     => date('m'),
            'year'                      => date('Y'),
        ];

        $this->_cache_save($cache_key, $result, 600);
        $this->_json($result);
    }

    // ==============================================================
    //  API: Today's attendance — EXACT original SQL from your file
    //  GET  overview/api_dashboard_today?branch_id=X
    // ==============================================================
    public function api_dashboard_today()
    {
        $ctx            = $this->_resolve_dashboard_context();
        $cid            = $ctx['cid'];
        $where_branch_2 = $ctx['where_branch_2'];
        $where_branch_3 = $ctx['where_branch_3'];
        $branch_id      = $ctx['branch_id'];
        $cache_key      = $this->_cache_key('dash_today', $cid, $branch_id);

        // Today data — only cache 3 minutes (near real-time)
        if ($cached = $this->_cache_get($cache_key)) {
            return $this->_json($cached);
        }

        $from_today = date('Y-m-d 00:00:00');

        // ── LATE today  (exact original query, lines 2776-2792) ──
        $late_row = $this->db->query("
        SELECT COUNT(id) AS cnt FROM
        (SELECT employees.first_name, employees.special_id,
                branches.name as branch_name, departments.name as department_name,
                shifts.grace_time, shifts.start_time, shifts.name as shift_name,
                clockings_news.*
         FROM clockings_news
         INNER JOIN shift_days  ON DATE(clockings_news.datetime) = shift_days.date
         INNER JOIN shifts      ON clockings_news.shift_id = shifts.id
         INNER JOIN employees   ON clockings_news.employee_id = employees.id
         INNER JOIN branches    ON employees.branch_id = branches.id
         INNER JOIN departments ON employees.department_id = departments.id
         WHERE clockings_news.type = 'in'
           AND employees.company_id = $cid
           $where_branch_2
           AND clockings_news.datetime > '$from_today'
         GROUP BY employees.id, DATE(clockings_news.datetime)
         HAVING DATE_FORMAT(clockings_news.datetime, '%H:%i')
              > DATE_FORMAT(shifts.grace_time, '%H:%i')
        ) AS tbl_temp
    ");
        $late_today_count = ($late_row && $late_row->row()) ? (int)$late_row->row()->cnt : 0;

        // ── EARLY today  (exact original query, lines 2798-2814) ──
        $early_row = $this->db->query("
        SELECT COUNT(id) AS cnt FROM
        (SELECT employees.first_name, employees.special_id,
                branches.name as branch_name, departments.name as department_name,
                shifts.grace_time, shifts.start_time, shifts.name as shift_name,
                clockings.*
         FROM clockings_news AS clockings
         INNER JOIN shift_days  ON DATE(clockings.datetime) = shift_days.date
         INNER JOIN shifts      ON clockings.shift_id = shifts.id
         INNER JOIN employees   ON clockings.employee_id = employees.id
         INNER JOIN branches    ON employees.branch_id = branches.id
         INNER JOIN departments ON employees.department_id = departments.id
         WHERE clockings.type = 'in'
           AND employees.company_id = $cid
           $where_branch_2
           AND clockings.datetime > '$from_today'
         GROUP BY employees.id, DATE(clockings.datetime)
         HAVING DATE_FORMAT(clockings.datetime, '%H:%i')
              < DATE_FORMAT(shifts.start_time, '%H:%i')
        ) AS tbl_temp
    ");
        $early_today_count = ($early_row && $early_row->row()) ? (int)$early_row->row()->cnt : 0;

        // ── ON-TIME today  (exact original query, lines 2819-2835) ──
        $ontime_row = $this->db->query("
        SELECT COUNT(id) AS cnt FROM
        (SELECT employees.first_name, employees.special_id,
                branches.name as branch_name, departments.name as department_name,
                shifts.grace_time, shifts.start_time, shifts.name as shift_name,
                clockings.*
         FROM clockings_news AS clockings
         INNER JOIN shift_days  ON DATE(clockings.datetime) = shift_days.date
         INNER JOIN shifts      ON clockings.shift_id = shifts.id
         INNER JOIN employees   ON clockings.employee_id = employees.id
         INNER JOIN branches    ON employees.branch_id = branches.id
         INNER JOIN departments ON employees.department_id = departments.id
         WHERE clockings.type = 'in'
           AND employees.company_id = $cid
           $where_branch_2
           AND clockings.datetime > '$from_today'
         GROUP BY employees.id, DATE(clockings.datetime)
         HAVING DATE_FORMAT(clockings.datetime, '%H:%i')
                >  DATE_FORMAT(shifts.start_time, '%H:%i')
            AND DATE_FORMAT(clockings.datetime, '%H:%i')
                <  DATE_FORMAT(shifts.grace_time, '%H:%i')
        ) AS tbl_temp
    ");
        $ontime_today_count = ($ontime_row && $ontime_row->row()) ? (int)$ontime_row->row()->cnt : 0;

        // ── ON LEAVE today  (exact original query, line 2844) ──
        $onleave_row = $this->db->query("
        SELECT SUM(
            LENGTH(shift_days.employees)
            - LENGTH(REPLACE(shift_days.employees, ',', '')) + 1
        ) AS cnt
        FROM shifts
        INNER JOIN shift_days ON shifts.id = shift_days.shift_id
        WHERE shifts.company_id = $cid
          $where_branch_3
          AND shifts.is_leave = 'yes'
          AND shift_days.date = CURRENT_DATE
    ");
        $onleave_today_count = ($onleave_row && $onleave_row->row()) ? (int)$onleave_row->row()->cnt : 0;

        // ── ABSENT today  (exact original query, line 2846) ──
        $absent_row = $this->db->query("
        SELECT (
            (SELECT SUM(
                        LENGTH(shift_days.employees)
                        - LENGTH(REPLACE(shift_days.employees, ',', '')) + 1
                    )
             FROM shifts
             INNER JOIN shift_days ON shifts.id = shift_days.shift_id
             WHERE company_id = $cid
               $where_branch_3
               AND shifts.is_leave = 'no'
               AND shift_days.date = CURRENT_DATE)
            -
            (SELECT COUNT(DISTINCT clockings_news.employee_id)
             FROM clockings_news
             INNER JOIN employees ON clockings_news.employee_id = employees.id
             WHERE employees.company_id = $cid
               $where_branch_3
               AND DATE(clockings_news.datetime) = CURRENT_DATE)
        ) AS cnt
    ");
        $absent_today_count = ($absent_row && $absent_row->row()) ? (int)$absent_row->row()->cnt : 0;

        $result = [
            'late_today_count'    => max(0, $late_today_count),
            'early_today_count'   => max(0, $early_today_count),
            'ontime_today_count'  => max(0, $ontime_today_count),
            'onleave_today_count' => max(0, $onleave_today_count),
            'absent_today_count'  => max(0, $absent_today_count),
        ];

        $this->_cache_save($cache_key, $result, 180);   // 3-min TTL
        $this->_json($result);
    }

    // ==============================================================
    //  API: Chart data (gender, departments, outlets)
    //  GET  overview/api_dashboard_charts?branch_id=X
    // ==============================================================
    public function api_dashboard_charts()
    {
        $ctx            = $this->_resolve_dashboard_context();
        $cid            = $ctx['cid'];
        $where_branch_1 = $ctx['where_branch_1'];
        $branch_id      = $ctx['branch_id'];
        $cache_key      = $this->_cache_key('dash_charts', $cid, $branch_id);

        if ($cached = $this->_cache_get($cache_key)) {
            return $this->_json($cached);
        }

        // Gender breakdown (exact original query, line 2754)
        $gender_breakdown = $this->db->select('sex, COUNT(*) as gender_count')
            ->from('employees e')
            ->join('roles r', 'e.role_id = r.id')
            ->where("e.company_id = $cid $where_branch_1")
            ->where('exclude_from_system', 'no')
            ->where('e.deleted_at is null')
            ->where('employee_status', 'active')
            ->group_by('sex')
            ->get()->result();

        // Departments breakdown (exact original query, line 2761)
        $departments_breakdown = $this->db->select('d.name, COUNT(*) as count')
            ->from('employees e')
            ->join('roles r', 'e.role_id = r.id')
            ->join('departments d', 'e.department_id = d.id')
            ->where("e.company_id = $cid $where_branch_1")
            ->where('employee_status', 'active')
            ->where('exclude_from_system', 'no')
            ->where('e.deleted_at is null')
            ->group_by('department_id')
            ->order_by('count', 'DESC')
            ->get()->result();

        // Outlets breakdown — only when no branch filter (line 2767)
        $outlets_breakdown = [];
        if (!$ctx['branch']) {
            $outlets_breakdown = $this->db->select('b.name, COUNT(*) as count')
                ->from('employees e')
                ->join('roles r', 'e.role_id = r.id')
                ->join('branches b', 'e.branch_id = b.id')
                ->where("e.company_id = $cid")
                ->where('employee_status', 'active')
                ->where('exclude_from_system', 'no')
                ->where('e.deleted_at is null')
                ->group_by('branch_id')
                ->order_by('count', 'DESC')
                ->get()->result();
        }

        $result = [
            'gender_breakdown'      => $gender_breakdown,
            'departments_breakdown' => $departments_breakdown,
            'outlets_breakdown'     => $outlets_breakdown,
        ];

        $this->_cache_save($cache_key, $result, 600);
        $this->_json($result);
    }
}
