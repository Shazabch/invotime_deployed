<?php

class Late_days extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        // if(is_null(get_user())){
        //  redirect("welcome");
        //   //var_dump($this->session->userdata('antelope_user'));
        // }
    }
    function index()
    {
        if (!is_page_permitted('late_days')) {
            redirect_if_not_permitted();
        }

        $current_user = get_user();
        $data['pageTitle'] = "Late Sheet";
        $data['active_menu'] = "late_days";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);
        $data["filters_form_action"] = "late_days";
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
                redirect("late_days?branch=$bid&" . getDateRangeFilterURLString($current_user['start_day']));
                return;
            }
        }
        $where_filter = "";
        if (!empty($this->input->get("branch"))) {
            $data["selected_branch_id"] = $this->input->get("branch");

            $where_filter = $where_filter . " branch_id = " . $this->input->get("branch") . " AND " ;
        }

        if (!empty($this->input->get("emp"))) {
            $data["selected_emp_id"] = $this->input->get("emp");

            $where_filter = $where_filter . " e.id = " . $this->input->get("emp") . " AND " ;
        }

        if (!empty($this->input->get("sec"))) {
            $data["selected_sec_id"] = $this->input->get("sec");

            $where_filter = $where_filter . " section_id = " . $this->input->get("sec") . " AND " ;
        }



        if (!empty($this->input->get("dep"))) {
            $data["selected_dep_id"] = $this->input->get("dep");

            $where_filter = $where_filter . " department_id = " . $this->input->get("dep") . " AND " ;
        }

        if (!empty($this->input->get("emp_group"))) {
            $where_filter = $where_filter . " egr.group_id = " . $this->input->get("emp_group") . " AND ";
        }

        $year = $data["selected_year"]; //date('Y');

        $where_filter = $where_filter . " e.company_id = " . $cid;



        $total_records = $this->db->query("SELECT count(DISTINCT e.id) as total_records FROM employees e INNER JOIN roles r on e.role_id = r.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = e.id where $where_filter AND r.exclude_from_system = 'no' AND r.deleted_at is NULL AND employee_status = 'active'")->row()->total_records;



        $per_page_options = [10, 25, 50, 75, 100];
        $limit = (int) $this->input->get("per_page");
        if (!in_array($limit, $per_page_options, true)) {
            $limit = 10;
        }

        $total_pages = ceil($total_records / $limit);

        $page = 1;

        if (!empty($this->input->get("page"))) {
            $page = (int) $this->input->get("page");
        }

        if ($page < 1) {
            $page = 1;
        }

        if ($total_pages > 0 && $page > $total_pages) {
            $page = $total_pages;
        }

        $skip = ($page - 1) * $limit;

        $result = $this->db->query("SELECT e.id, special_id,first_name,is_ot,is_early_ot, ot_type, ot_round, early_ot_round, branch_id FROM employees e LEFT JOIN branches b on b.id = e.branch_id  INNER JOIN roles r ON e.role_id = r.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = e.id where $where_filter AND e.deleted_at is NULL AND employee_status = 'active' AND r.exclude_from_system = 'no'
			GROUP BY e.id, special_id,first_name,is_ot,is_early_ot, ot_type, ot_round, early_ot_round, branch_id ORDER BY special_id LIMIT $skip,$limit")->result();

        $employees_ids = array();
        foreach ($result as $r) {
            $employees_ids[] = $r->id;
        }

        $first_day = $data["start_date"];
        $last_day = $data["end_date"];

        $result_list = get_result_list($employees_ids, $first_day, $last_day);
        $result_list_overnight = get_result_list_overnight($employees_ids, $first_day, $last_day);

        $check_list = $this->db->select('employee_id, is_late, late_date as date')->from('late_days')->where_in('employee_id', $employees_ids)->where('late_date >=', $first_day)->where('late_date <=', $last_day)->get()->result();

        $days = array();
        if ($permissions_level == "Outlet") {
            $holidays_with_names = get_public_holidays_with_name($bid);
            $public_holidays = $holidays_with_names[0];
            $holiday_names = $holidays_with_names[1];
        } else {
            $holidays_with_names = get_public_holidays_with_name();
            $public_holidays = $holidays_with_names[0];
            $holiday_names = $holidays_with_names[1];
        }


        $data['period_of_dates'] = new DatePeriod(
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
                $d["holiday_name"] = $holiday_names[$holiday_index];
            }

            $days[] = $d;
        }

        $manual_late_list_all = get_manual_late_list_all($employees_ids, $first_day, $last_day);

        $late_round_off_settings = [];

        $employees = array();

        foreach ($result as $emp) {
            $temp["id"] = $emp->id;

            $temp["special_id"] = $emp->special_id;

            $temp["first_name"] = $emp->first_name;

            $manual_late_list = get_manual_late_list_by_id($manual_late_list_all, $emp->id);

            $shift_list = $this->db->select('overnight,date,half_day,same_day_overnight')->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->where('FIND_IN_SET(' . $emp->id . ',employees)>', 0)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();

            $days_wise = array();

            foreach ($data['period_of_dates'] as $periodDate) {
                $temp1['day'] = $periodDate->format('Y-m-d');

                $temp1['id'] = $emp->id;

                $check = true;
                $check_is_late = $this->search_from_list_by_id($check_list, $temp1['day'], $emp->id);
                if ($check_is_late) {
                    $check = $check_is_late->is_late == "Y" ? true : false;
                }

                $temp1['is_late'] = $check ? true : false;

                $overnight = false;
                $half_day = false;
                $shift_check = $this->search_from_list($shift_list, $temp1['day']);
                if ($shift_check && $shift_check->half_day == "Yes") {
                    $half_day = true;
                }
                if ($shift_check && $shift_check->overnight == "Yes") {
                    $result = $this->search_clocking($result_list_overnight, $temp1['day'], $emp->id);
                    $overnight = true;
                } else {
                    $result = $this->search_clocking($result_list, $temp1['day'], $emp->id);
                    if (!$shift_check) {
                        $result = remove_duplicate_clockings($result, $temp1['day'], $shift_list, $result_list_overnight);
                    }
                }

                $late_hours = count_late($temp1['day'], $result, $overnight, $half_day, $manual_late_list, $shift_check ? $shift_check->same_day_overnight : 'default');

                if (isset($late_round_off_settings[$emp->branch_id])) {
                    $late_round_off = $late_round_off_settings[$emp->branch_id];
                } else {
                    $late_round_off = $this->db->select('start, end, round_to')->from('late_in_round_settings')->where('branch_id', $emp->branch_id)->get()->result();
                    $late_round_off_settings[$emp->branch_id] = $late_round_off;
                }

                $late_hours = round_off_late_in($late_hours, $late_round_off, false);

                $late_hours = ($late_hours == "" || $late_hours == "00:00") ? "-" : $late_hours;

                $temp1['late_time'] = $late_hours;

                $days_wise[] = $temp1;
            }

            $temp["late_data"] = $days_wise;

            $employees[] = $temp;
        }

        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        $data["per_page"] = $limit;
        $data["per_page_options"] = $per_page_options;

        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET);

        $data["days"] = $days;

        $data["employees"] = $employees;

        $data["branches"] = $this->db->query("SELECT * FROM branches WHERE company_id = $cid  $where_branch_2 ORDER BY name")->result();

        $data["departments"] = $this->db->query("SELECT id,name FROM departments WHERE company_id = $cid ORDER BY name")->result();

        $data["positions"] = $this->db->query("SELECT id,title as name FROM positions WHERE company_id = $cid ORDER BY name")->result();

		$data["sections"] = $this->db->query("SELECT id,title as name FROM sections WHERE company_id = $cid ORDER BY name")->result();


        $data["filters"] = $this->load->view('filters', $data, true);
        $this->load->view('late_days', $data);

        $this->load->view('footer');
    }



    function change_status()
    {

        $request = $this->input->post();

        $employee_id = $request['id'];

        $late_date = $request['day'];

        $is_late = ($request['is_late'] == 1) ? 'Y' : 'N';



        $data = array('employee_id' => $employee_id,

            'late_date' => $late_date,

            'is_late' => $is_late);

        $this->db->replace('late_days', $data);
    }

    function approve_all_late()
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
            $late_date = $date->format('Y-m-d');
            $data = array('employee_id' => $employee_id,
                'late_date' => $late_date,
                'is_late' => 'Y');
            $this->db->replace('late_days', $data);
        }
    }



    function search_from_list_by_id($list, $date, $id)
    {
        foreach ($list as $l) {
            if ($l->date == $date && $l->employee_id == $id) {
                return $l;
            }
        }
        return array();
    }

    function search_from_list($list, $date)
    {
        foreach ($list as $l) {
            if ($l->date == $date) {
                return $l;
            }
        }
        return array();
    }

    function search_clocking($list, $date, $id)
    {
        $result = array();
        foreach ($list as $l) {
            if ($l->search_date == $date && $l->employee_id == $id) {
                $result[] = $l;
            }
        }
        return $result;
    }
}
