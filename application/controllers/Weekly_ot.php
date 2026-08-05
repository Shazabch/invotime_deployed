<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Weekly_ot extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        if (is_null(get_user())) {
            redirect("welcome");
            //var_dump($this->session->userdata('antelope_user'));
        }
    }

    public function wview($id = 0, $dep = false)
    {
        if (!is_page_permitted('wview')) {
            redirect_if_not_permitted();
        }

        // Check if it is HOD
        $current_user = (object)get_user();
        $data["current_user"] = $current_user;
        $is_HOD = $current_user->limit_access_to_department == "yes" ? TRUE : FALSE;
        $is_emp_summary_editable = $current_user->is_emp_summary_editable === "yes" ? TRUE : FALSE;
        $data["is_HOD"] = $is_HOD;
        $data["is_emp_summary_editable"] = $is_emp_summary_editable;

        $cid = $current_user->company_id;

        $bid = $current_user->branch_id;
        $branch_where_filter = "";
        $permissions_level = $current_user->permissions_level;

        if ($permissions_level == "Outlet") {
            $branch_where_filter = " AND branch_id = $bid ";
        }

        $department_filter = '';
        $data["selected_department"] = '';

        if ($is_HOD) {
            $hod_department_id = $current_user->department_id;
            $accessible_department_ids_array = [];
            if ($current_user->departments_access != "")
                $accessible_department_ids_array = array_map("trim", explode(",", $current_user->departments_access));

			$accessible_department_ids_array[] = $hod_department_id;
			$accessible_department_ids = implode(",", $accessible_department_ids_array);
			$data["departments"] = $this->db->query("SELECT * FROM departments WHERE id in( " . $accessible_department_ids . ")")->result();
            $department_filter = "AND employees.department_id in (" . $accessible_department_ids . ")";
        } else {
            $data["departments"] = $this->db->query("SELECT * FROM departments WHERE company_id = $cid ORDER BY name")->result();
            $temp = new stdClass();
            $temp->id = "all";
            $temp->name = "All";
            array_unshift($data["departments"], $temp);

            if ($dep) {
                if ($dep != "all") {
                    $department_filter = "AND employees.department_id = " . $dep;
                }
                $data["selected_department"] = $dep;
            }
        }

        $data["employees_dropdown"] = $this->db->query("SELECT employees.id, special_id,first_name,last_name FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE employees.deleted_at IS NULL 
            AND (
                employee_status = 'active'
                OR(employee_status = 'terminated' AND termination_date IS NOT NULL AND termination_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
                OR(employee_status = 'resigned' AND resignation_date IS NOT NULL AND resignation_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
            )
            AND roles.exclude_from_system = 'no' $department_filter AND employees.company_id = $cid $branch_where_filter ORDER BY special_id")->result();
		// echo count($data["employees_dropdown"]);die;



        if ($dep && $id == 0) {
            $data['employee'] = $this->db->select('e.id as emp_id,first_name,special_id,d.name as department,p.title as position,is_ot,is_early_ot,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,inc_late_in,inc_late_break,inc_early_out,inc_short_hours,void_lateness_time_if_less_than,branch_id,deduct_from_ot,deduct_from_ot_single,deduction_date')->from('employees e')->join('departments d', 'd.id = e.department_id', 'left')->join('positions p', 'p.id = e.position_id', 'left')->join('branches b', 'b.id = e.branch_id', 'left')->where('e.id', $data["employees_dropdown"][0]->id)->get()->row();
            $id = $data["employees_dropdown"][0]->id;
        } else {
            $data['employee'] = $this->db->select('e.id as emp_id,first_name,special_id,d.name as department,p.title as position,is_ot,is_early_ot,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,inc_late_in,inc_late_break,inc_early_out,inc_short_hours,void_lateness_time_if_less_than,branch_id,deduct_from_ot,deduct_from_ot_single,deduction_date')->from('employees e')->join('departments d', 'd.id = e.department_id', 'left')->join('positions p', 'p.id = e.position_id', 'left')->join('branches b', 'b.id = e.branch_id', 'left')->where('e.id', $id)->get()->row();
        }

        if (!$data['employee']) {
            redirect('weekly_ot/wview/' . $data["employees_dropdown"][0]->id);
            //var_dump($data["employees_dropdown"][0]->id);
            die();
        }

        if (empty($_GET)) {
            $first_day = date('Y-m-01');
            $last_day  = date('Y-m-07');
        } else {
            $date1 = DateTime::createFromFormat('d/m/Y', $_GET['from']);
            $date2 = add_days_to_date($date1, 6);

            $first_day = $date1->format('Y-m-d');
            $last_day = $date2->format('Y-m-d');
        }

        $calculated_data = calculate_summary_data($data["employee"]->emp_id, $first_day, $last_day);
        $data = array_merge($data, $calculated_data);
        $data["current_user"] = (object)$current_user;

        $date = DateTime::createFromFormat('Y-m-d', $first_day);
        $data['from_f'] = $date->format('d/m/Y');
        $data['from_p'] = $first_day;
        $date = DateTime::createFromFormat('Y-m-d', $last_day);
        $data['to_f'] = $date->format('d/m/Y');
        $data['to_p'] = $last_day;
        $data['emp_id'] = $id;
        $data['pageTitle'] = "Weekly OT";
        $data['active_menu'] = "weekly_ot/wview/";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();

        $this->load->view('sidebar', $data);
        $this->load->view('weekly_ot/view', $data);
        $this->load->view('footer');
    }
}
