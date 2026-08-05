<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Merit_system extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        if (is_null(get_user())) {
            redirect("welcome");
        }
        $this->load->model("Merit");
    }

    function index()
    {
        if (!is_page_permitted('merit_system')) {
            redirect_if_not_permitted();
        }

        $current_user = get_user();
        $data['pageTitle'] = "Monthly Merit System";
        $data['active_menu'] = "merit_system";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);
        $data["filters_form_action"] = "merit_system";
        render_all_filters($data);
        $data["selected_branch_id"] = 0;
        $data["selected_dep_id"] = 0;
        $data["selected_month"] = 0;
        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];
        $interval_minutes = get_interval_minutes($cid);
        //$where_branch_1 = '';
        $where_branch_2 = '';
        //$where_branch_3 = '';
        if ($permissions_level == "Outlet") {
            //$where_branch_1 = " AND branch_id = $bid ";
            $where_branch_2 = " AND id = $bid ";
            //$where_branch_3 = " AND permissions_level = 'Personal' ";
            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("merit_system?branch=$bid&" . getDateRangeFilterURLString($current_user['start_day']));
                return;
            }
        }
        $where_filter = "";
        if (!empty($this->input->get("branch"))) {
            $data["selected_branch_id"] = $this->input->get("branch");
            $where_filter = $where_filter . " branch_id = " . $this->input->get("branch") . " AND ";
        }

        if (!empty($this->input->get("emp"))) {
            $data["selected_emp_id"] = $this->input->get("emp");
            $where_filter = $where_filter . " e.id = " . $this->input->get("emp") . " AND ";
        }

        if (!empty($this->input->get("dep"))) {
            $data["selected_dep_id"] = $this->input->get("dep");
            $where_filter = $where_filter . " e.department_id = " . $this->input->get("dep") . " AND ";
        }

        if (!empty($this->input->get("emp_group"))) {
            $where_filter = $where_filter . " egr.group_id = " . $this->input->get("emp_group") . " AND ";
        }

        $year = $data["selected_year"];
        $where_filter = $where_filter . " e.company_id = " . $cid;
        $total_records = $this->db->select('count(distinct e.id) as total_records')->from('employees e')->join('roles r', 'e.role_id = r.id', 'left')->join('departments d', 'd.id = e.department_id', 'left')->join('branches b', 'b.id = e.branch_id', 'left')->join('positions p', 'p.id = e.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = e.id', 'left')->where("$where_filter AND r.exclude_from_system = 'no' AND e.deleted_at is NULL AND employee_status = 'active'")->get()->row()->total_records;

        $limit = 10;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;

        $employees = $this->db->select('e.id,e.first_name,special_id,e.is_daily_waged, d.name as department, p.title as position,e.branch_id,is_ot,is_early_ot,inc_late_in,inc_late_break, inc_early_out, inc_short_hours,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,void_lateness_time_if_less_than,deduct_from_ot,deduct_from_ot_single,deduction_date')->from('employees e')->join('roles r', 'e.role_id = r.id', 'left')->join('departments d', 'd.id = e.department_id', 'left')->join('branches b', 'b.id = e.branch_id', 'left')->join('positions p', 'p.id = e.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = e.id', 'left')->where("$where_filter AND r.exclude_from_system = 'no' AND e.deleted_at is NULL AND employee_status = 'active'")->group_by('e.id,e.first_name,special_id,e.is_daily_waged, d.name, p.title,e.branch_id,is_ot,is_early_ot,inc_late_in,inc_late_break, inc_early_out, inc_short_hours,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,void_lateness_time_if_less_than,deduct_from_ot,deduct_from_ot_single,deduction_date')->order_by('e.special_id', 'ASC')->limit($limit, $skip)->get()->result();

        $employees_ids = ['0'];
        foreach ($employees as &$employee) {
            $employees_ids[] = $employee->id;
        }

        $first_day = $data['start_date'];
        $last_day = $data['end_date'];

        $company_working_hours = get_company_working_hours($cid);
        $company_ot_settings = get_company_ot_settings($cid);
        $company_early_ot_settings = get_company_early_ot_settings($cid);
        $branch_rest_days = $this->db->select('id,rest_days,off_days')->from('branches')->where('company_id', $cid)->get()->result();
        $public_holidays_all = get_public_holidays_with_name();

        $public_holidays = $public_holidays_all[0];
        $public_holidays_names = $public_holidays_all[1];

        $public_holidays_all = get_public_holidays_all();

        $merit_deduction_points = [];

        $result_list = get_result_list($employees_ids, $first_day, $last_day);
        $result_list_overnight = get_result_list_overnight($employees_ids, $first_day, $last_day);

        $clockings_news = $this->db->select('id,employee_id,type,shift_id,date_format(datetime,"%H:%i") as clock_time,date_format(datetime, "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false)->from('clockings_news')->where('date(datetime) >=', $first_day)->where('date(datetime) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();
        $clockings_news_overnight = $this->db->select('id,employee_id,type,shift_id, date_format(datetime,"%H:%i") as clock_time,date_format(date_sub(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false)->from('clockings_news')->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) >=', $first_day)->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();

        if ($permissions_level == "Outlet") {
            $shifts = $this->db->select('id')->from('shifts')->where('branch_id', $bid)->where('is_leave', 'no')->get()->result();
            $merit_deduction_points = $this->Merit->get_deduction_points($cid, $bid);
        } else {
            $shifts = $this->db->select('id')->from('shifts')->where('company_id', $cid)->where('is_leave', 'no')->get()->result();
            $merit_deduction_points = $this->Merit->get_deduction_points($cid);
        }

        $shift_ids = array(0);
        foreach ($shifts as $s) {
            $shift_ids[] = $s->id;
        }

        $approved_ot_list = get_approved_ot_list($shift_ids, $first_day, $last_day);

        $output_employees = [];
        $days = array();

        $data["period_of_dates"] = new DatePeriod(
            new DateTime($first_day),
            new DateInterval('P1D'),
            (new DateTime($last_day))->add(new DateInterval('P1D'))
        );

        foreach ($data['period_of_dates'] as $date) {
            $d["date"] = $date->format('j');
            $d["day"] = $date->format('D');
            $d["holiday"] = false;
            $holiday_index = array_search($date->format('Y-m-d'), $public_holidays);
            if ($holiday_index > -1) {
                $d["holiday"] = true;
                $d["holiday_name"] = $public_holidays_names[$holiday_index];
            }
            $days[] = $d;
        }

        $default_offenses = default_offenses();

        $filler_array = [];
        foreach ($employees as $emp) {
            $calculated_data = calculate_summary_data($emp->id, $first_day, $last_day, "merit_system", $emp, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days, $cid, $filler_array, $filler_array, $filler_array, $clockings_news, $clockings_news_overnight);
            $temp = calculate_merit($emp, $calculated_data, $permissions_level, $merit_deduction_points, $first_day, $last_day, $default_offenses);
            $output_employees[] = $temp;
        }

        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET);
        $data["days"] = $days;
        $data["employees"] = $output_employees;
        $data["filters"] = $this->load->view('filters', $data, true);
        $data["first_day_f"] = to_html_date($first_day);
        $data["last_day_f"] = to_html_date($last_day);
        $query_string = http_build_query($_GET);
        $data["pagination_url"] = $currentURL . '?' . $query_string;
        $data["merit_system_export_url"] = base_url() . "merit_system/merit_system_pdf?$query_string";

        $this->load->view('merit_system/index', $data);
        $this->load->view('footer');
    }

    public function merit_system_pdf()
    {
        $current_user = get_user();
        $data['current_user'] = $current_user;
        $data["selected_branch_id"] = 0;
        $data["selected_dep_id"] = 0;
        $data["selected_month"] = 0;
        $data["selected_group_id"] = 0;
        render_all_filters($data);
        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];
        $interval_minutes = get_interval_minutes($cid);
        $data['branch_name'] = 'All';
        //$where_branch_1 = '';
        $where_branch_2 = '';
        //$where_branch_3 = '';
        if ($permissions_level == "Outlet") {
            //$where_branch_1 = " AND branch_id = $bid ";
            $where_branch_2 = " AND id = $bid ";
            //$where_branch_3 = " AND permissions_level = 'Personal' ";
            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("merit_system?branch=$bid&" . getDateRangeFilterURLString($current_user['start_day']));
                return;
            }
        }
        $where_filter = "";
        if (!empty($this->input->get("branch"))) {
            $data["selected_branch_id"] = $this->input->get("branch");
            $where_filter = $where_filter . " branch_id = " . $this->input->get("branch") . " AND ";
            $data['branch_name'] = $this->db->select('name')->from('branches')->where('id', $data['selected_branch_id'])->get()->row()->name;
        }

        if (!empty($this->input->get("emp"))) {
            $data["selected_emp_id"] = $this->input->get("emp");
            $where_filter = $where_filter . " e.id = " . $this->input->get("emp") . " AND ";
        }

        if (!empty($this->input->get("dep"))) {
            $data["selected_dep_id"] = $this->input->get("dep");
            $where_filter = $where_filter . " e.department_id = " . $this->input->get("dep") . " AND ";
        }

        if (!empty($this->input->get("emp_group"))) {
            $where_filter = $where_filter . " egr.group_id = " . $this->input->get("emp_group") . " AND ";
        }

        $year = $data["selected_year"];
        $where_filter = $where_filter . " e.company_id = " . $cid;
        $total_records = $this->db->select('count(distinct e.id) as total_records')->from('employees e')->join('roles r', 'e.role_id = r.id', 'left')->join('departments d', 'd.id = e.department_id', 'left')->join('branches b', 'b.id = e.branch_id', 'left')->join('positions p', 'p.id = e.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = e.id', 'left')->where("$where_filter AND r.exclude_from_system = 'no' AND e.deleted_at is NULL AND employee_status = 'active'")->get()->row()->total_records;

        $limit = INF;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;

        $employees = $this->db->select('e.id,e.first_name,special_id,e.is_daily_waged, d.name as department, p.title as position,e.branch_id,is_ot,is_early_ot,inc_late_in,inc_late_break, inc_early_out, inc_short_hours,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,void_lateness_time_if_less_than,deduct_from_ot,deduct_from_ot_single,deduction_date')->from('employees e')->join('roles r', 'e.role_id = r.id', 'left')->join('departments d', 'd.id = e.department_id', 'left')->join('branches b', 'b.id = e.branch_id', 'left')->join('positions p', 'p.id = e.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = e.id', 'left')->where("$where_filter AND r.exclude_from_system = 'no' AND e.deleted_at is NULL AND employee_status = 'active'")->group_by('e.id,e.first_name,special_id,e.is_daily_waged, d.name, p.title,e.branch_id,is_ot,is_early_ot,inc_late_in,inc_late_break, inc_early_out, inc_short_hours,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,void_lateness_time_if_less_than,deduct_from_ot,deduct_from_ot_single,deduction_date')->order_by('e.special_id', 'ASC')->limit($limit, $skip)->get()->result();

        $employees_ids = ['0'];
        foreach ($employees as &$employee) {
            $employees_ids[] = $employee->id;
        }

        $first_day = $data['start_date'];
        $last_day = $data['end_date'];

        $company_working_hours = get_company_working_hours($cid);
        $company_ot_settings = get_company_ot_settings($cid);
        $company_early_ot_settings = get_company_early_ot_settings($cid);
        $branch_rest_days = $this->db->select('id,rest_days,off_days')->from('branches')->where('company_id', $cid)->get()->result();
        $public_holidays_all = get_public_holidays_with_name();

        $public_holidays = $public_holidays_all[0];
        $public_holidays_names = $public_holidays_all[1];

        $public_holidays_all = get_public_holidays_all();

        $merit_deduction_points = [];

        $result_list = get_result_list($employees_ids, $first_day, $last_day);
        $result_list_overnight = get_result_list_overnight($employees_ids, $first_day, $last_day);

        $clockings_news = $this->db->select('id,employee_id,type,shift_id,date_format(datetime,"%H:%i") as clock_time,date_format(datetime, "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false)->from('clockings_news')->where('date(datetime) >=', $first_day)->where('date(datetime) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();
        $clockings_news_overnight = $this->db->select('id,employee_id,type,shift_id, date_format(datetime,"%H:%i") as clock_time,date_format(date_sub(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false)->from('clockings_news')->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) >=', $first_day)->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();

        if ($permissions_level == "Outlet") {
            $shifts = $this->db->select('id')->from('shifts')->where('branch_id', $bid)->where('is_leave', 'no')->get()->result();
            $merit_deduction_points = $this->Merit->get_deduction_points($cid, $bid);
        } else {
            $shifts = $this->db->select('id')->from('shifts')->where('company_id', $cid)->where('is_leave', 'no')->get()->result();
            $merit_deduction_points = $this->Merit->get_deduction_points($cid);
        }

        $shift_ids = array(0);
        foreach ($shifts as $s) {
            $shift_ids[] = $s->id;
        }

        $approved_ot_list = get_approved_ot_list($shift_ids, $first_day, $last_day);

        $output_employees = [];
        $days = array();

        $data["period_of_dates"] = new DatePeriod(
            new DateTime($first_day),
            new DateInterval('P1D'),
            (new DateTime($last_day))->add(new DateInterval('P1D'))
        );

        foreach ($data['period_of_dates'] as $date) {
            $d["date"] = $date->format('j');
            $d["day"] = $date->format('D');
            $d["holiday"] = false;
            $holiday_index = array_search($date->format('Y-m-d'), $public_holidays);
            if ($holiday_index > -1) {
                $d["holiday"] = true;
                $d["holiday_name"] = $public_holidays_names[$holiday_index];
            }
            $days[] = $d;
        }

        $default_offenses = default_offenses();

        $filler_array = [];
        foreach ($employees as &$emp) {
            $calculated_data = calculate_summary_data($emp->id, $first_day, $last_day, "merit_system", $emp, $result_list, $result_list_overnight, $company_working_hours, $public_holidays_all, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days, $cid, $filler_array, $filler_array, $filler_array, $clockings_news, $clockings_news_overnight);
            $temp = calculate_merit($emp, $calculated_data, $permissions_level, $merit_deduction_points, $first_day, $last_day, $default_offenses);
            $output_employees[] = $temp;
        }

        // $data["total_pages"] = $total_pages;
        // $data["page"] = $page;
        // unset($_GET['page']);
        // $currentURL = current_url();
        // $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET);
        $data["days"] = $days;
        $data["employees"] = $output_employees;
        // $data["filters"] = $this->load->view('filters', $data, true);
        $data["first_day_f"] = to_html_date($first_day);
        $data["last_day_f"] = to_html_date($last_day);


        $this->load->view('merit_system/merit_system_pdf', $data);
        $html = $this->output->get_output();
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper("A4", "landscape");
        $this->dompdf->render();
        $this->dompdf->stream($data["selected_month"] . "-" . $data["selected_year"] .
            " - Merit System - " . time(), array("Attachment" => 0));
        insert_log("Simple", ["action" => "Exported,Merit Sheet"]);
    }

    public function change_status()
    {
        $json = file_get_contents("php://input");
        $input = json_decode($json);

        $data = [
            "employee_id" => $input->employee_id,
            "date" => $input->date,
            "is_offense" => $input->is_offense == 1 ? "Y" : "N",
        ];

        $this->db->replace("offense_days", $data);
        return send_json_response(["success" => true]);
    }

    public function update_offense()
    {
        $json = file_get_contents("php://input");
        $input = json_decode($json);

        if (isset($input[4]) && $input[4]->value === "-") {
            $sign = "-";
        } else {
            $sign = "+";
        }

        $data = [
            "offense" => $input[0]->value,
            "points" => $input[1]->value,
            "employee_id" => $input[2]->value,
            "date" => $input[3]->value,
            "type" => $sign,
        ];

        $this->db->replace("manual_offenses", $data);
        return send_json_response(["success" => true, "data" => $data]);
    }

    public function remove_offense()
    {
        $json = file_get_contents("php://input");
        $input = json_decode($json);

        $employee_id = $input[2]->value;
        $date = $input[3]->value;

        $this->db->where("employee_id", $employee_id);
        $this->db->where("date", $date);
        $this->db->delete("manual_offenses");

        return send_json_response(["success" => true]);
    }


    public function yearly_report()
    {
        if (!is_page_permitted('yearly_report')) {
            redirect_if_not_permitted();
        }
        $current_user = get_user();
        $data['pageTitle'] = "Yearly Merit System";
        $data['active_menu'] = "merit_system/yearly_report";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);
        $data["filters_form_action"] = "merit_system/yearly_report";
        render_all_filters($data);
        $data["selected_branch_id"] = 0;
        $data["selected_dep_id"] = 0;
        $data["selected_month"] = 0;
        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];
        //$where_branch_1 = '';
        $where_branch_2 = '';
        //$where_branch_3 = '';
        if ($permissions_level == "Outlet") {
            //$where_branch_1 = " AND branch_id = $bid ";
            $where_branch_2 = " AND id = $bid ";
            //$where_branch_3 = " AND permissions_level = 'Personal' ";
            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("merit_system/yearly_report?branch=$bid&" . getDateRangeFilterURLString($current_user['start_day']));
                return;
            }
        }
        $where_filter = "";
        if (!empty($this->input->get("branch"))) {
            $data["selected_branch_id"] = $this->input->get("branch");
            $where_filter = $where_filter . " branch_id = " . $this->input->get("branch") . " AND ";
        }

        if (!empty($this->input->get("emp"))) {
            $data["selected_emp_id"] = $this->input->get("emp");
            $where_filter = $where_filter . " e.id = " . $this->input->get("emp") . " AND ";
        }

        if (!empty($this->input->get("dep"))) {
            $data["selected_dep_id"] = $this->input->get("dep");
            $where_filter = $where_filter . " e.department_id = " . $this->input->get("dep") . " AND ";
        }

        if (!empty($this->input->get("emp_group"))) {
            $where_filter = $where_filter . " egr.group_id = " . $this->input->get("emp_group") . " AND ";
        }
        // echo $year;die;
        // $year = $data["selected_year"];
        $where_filter = $where_filter . " e.company_id = " . $cid;
        // print_r($year);die;
        $total_records = $this->db->query("SELECT count(distinct e.id) as total_records FROM employees e INNER JOIN roles r on e.role_id = r.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = e.id where $where_filter AND r.exclude_from_system = 'no' AND r.deleted_at is NULL AND employee_status = 'active'")->row()->total_records;
        $limit = 20;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;

        $employees = $this->db->query("SELECT e.id, special_id,first_name FROM employees e INNER JOIN roles r ON e.role_id = r.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = e.id where $where_filter AND e.deleted_at is NULL AND employee_status = 'active' AND r.exclude_from_system = 'no' GROUP BY e.id, special_id,first_name ORDER BY special_id LIMIT $skip,$limit")->result();

        // $max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $year = $data['formatted_date']['start_date']->format('Y');

        $company_average_merit_points = get_average_merit_points($year, $cid);
        $company_merit_points = get_merit_points($year, $cid);

        $employees_ids = ['0'];
        foreach ($employees as &$employee) {
            $employees_ids[] = $employee->id;
            $points = search_average_merit_points($company_average_merit_points, $employee->id);
            $employee->average_merit_points = number_format($points, 2);
            $employee->grade = merit_system_grading($points);
            $employee->merit_points = search_merit_points($company_merit_points, $employee->id);
        }

        $first_day = $data['start_date'];
        $last_day = $data['end_date'];

        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET);
        $data["employees"] = $employees;
        $data["filters"] = $this->load->view('filters', $data, true);
        $data["first_day_f"] = to_html_date($first_day);
        $data["last_day_f"] = to_html_date($last_day);
        $query_string = http_build_query($_GET);
        $data["pagination_url"] = $currentURL . '?' . $query_string;
        $data["merit_system_export_url"] = base_url() . "merit_system/yearly_merit_system_pdf?$query_string";

        $this->load->view('merit_system/yearly_report', $data);
        $this->load->view('footer');
    }

    public function yearly_merit_system_pdf()
    {
        // echo 'a';die;
        $current_user = get_user();
        $data["selected_branch_id"] = 0;
        $data["selected_dep_id"] = 0;
        $data["selected_month"] = 0;
        $data["selected_group_id"] = 0;
        render_all_filters($data);
        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];
        //$where_branch_1 = '';
        $where_branch_2 = '';
        //$where_branch_3 = '';
        if ($permissions_level == "Outlet") {
            //$where_branch_1 = " AND branch_id = $bid ";
            $where_branch_2 = " AND id = $bid ";
            //$where_branch_3 = " AND permissions_level = 'Personal' ";
            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("merit_system/yearly_report?branch=$bid&" . getDateRangeFilterURLString($current_user['start_day']));
                return;
            }
        }
        $where_filter = "";
        if (!empty($this->input->get("branch"))) {
            $data["selected_branch_id"] = $this->input->get("branch");
            $where_filter = $where_filter . " branch_id = " . $this->input->get("branch") . " AND ";
        }

        if (!empty($this->input->get("emp"))) {
            $data["selected_emp_id"] = $this->input->get("emp");
            $where_filter = $where_filter . " e.id = " . $this->input->get("emp") . " AND ";
        }

        if (!empty($this->input->get("dep"))) {
            $data["selected_dep_id"] = $this->input->get("dep");
            $where_filter = $where_filter . " e.department_id = " . $this->input->get("dep") . " AND ";
        }

        if (!empty($this->input->get("emp_group"))) {
            $where_filter = $where_filter . " egr.group_id = " . $this->input->get("emp_group") . " AND ";
        }
        // echo $year;die;
        // $year = $data["selected_year"];
        $where_filter = $where_filter . " e.company_id = " . $cid;


        $employees = $this->db->query("SELECT e.id, special_id,first_name FROM employees e INNER JOIN roles r ON e.role_id = r.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = e.id where $where_filter AND e.deleted_at is NULL AND employee_status = 'active' AND r.exclude_from_system = 'no' GROUP BY e.id, special_id,first_name ORDER BY special_id")->result();
        $year = $data['formatted_date']['start_date']->format('Y');

        $company_average_merit_points = get_average_merit_points($year, $cid);
        $company_merit_points = get_merit_points($year, $cid);

        $employees_ids = ['0'];
        foreach ($employees as &$employee) {
            $employees_ids[] = $employee->id;
            $points = search_average_merit_points($company_average_merit_points, $employee->id);
            $employee->average_merit_points = number_format($points, 2);
            $employee->grade = merit_system_grading($points);
            $employee->merit_points = search_merit_points($company_merit_points, $employee->id);
        }

        unset($_GET['page']);
        $data["employees"] = $employees;
        $data["current_user"] = $current_user;

        $this->load->view('merit_system/yearly_report_pdf', $data);
        $html = $this->output->get_output();
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper("A4", "landscape");
        $this->dompdf->render();
        $this->dompdf->stream($data["selected_month"] . "-" . $data["selected_year"] .
            " - Merit System - " . time(), array("Attachment" => 0));
        insert_log("Simple", ["action" => "Exported,Yearly Merit Sheet"]);
    }

    public function approve_all_offenses()
    {
        $request = $this->input->post();
        $employee_id = $request['id'];
        $first_day = $request['start'];
        $last_day = $request['end'];

        $period = new DatePeriod(
            new DateTime($first_day),
            new DateInterval('P1D'),
            (new DateTime($last_day))->add(new DateInterval('P1D'))
        );

        foreach ($period as $date) {
            $date = $date->format('Y-m-d');
            $data = array(
                'employee_id' => $employee_id,
                'date' => $date,
                'is_offense' => 'Y'
            );
            $this->db->replace('offense_days', $data);
        }
        return send_json_response(["success" => true, "msg" => "Offenses approved! Please reload the page."]);
    }
}
