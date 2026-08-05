<?php

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class Overview extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

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

    function save_overtimes()
    {
        $month = date('m');
        $date = date('d');
        $year = date('Y');
        $employees = $this->db->select('e.id,e.company_id,e.branch_id,e.department_id')->from('employees e')->join('roles r', 'e.role_id = r.id')->where('e.deleted_at is null')->where('exclude_from_system', 'no')->get()->result();


        foreach ($employees as $emp) {
            for ($j = 1; $j <= $date; $j++) {
                $day = $year . "-" . $month . "-" . sprintf("%02d", $j);
                $overtime = $this->count_overtime($emp->id, $day);
                $overtime = $this->getHours($overtime);
                $data = array(
                    "employee_id" => $emp->id,
                    "company_id" => $emp->company_id,
                    "branch_id" => $emp->branch_id,
                    "department_id" => $emp->branch_id,
                    "date" => $day,
                    "overtime" => $overtime
                );
                $this->db->replace('overtimes', $data);
            }
        }
    }

    public function late_frequency_report()
    {
        if (!is_page_permitted('late_frequency_report')) {
            redirect_if_not_permitted();
        }

        $current_user = get_user();
        $data['pageTitle'] = "Late Frequency";
        $data['active_menu'] = "overview/late_frequency_report";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);
        $data["filters_form_action"] = "overview/late_frequency_report";
        render_all_filters($data);
        $branch = null;
        $branches = null;

        $branch_id = $this->input->get('branch');
        $status = $this->input->get('status');

        $data["first_day"] = $data['start_date'];
        $data["last_day"] = $data['end_date'];
        $cid = $current_user["company_id"];

        if ($branch_id) {
            $branch = $this->db->get_where('branches', array('id' => $branch_id))->row();
        }
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];

        $where_branch_2 = '';

        if (isset($branch)) {
            $where_branch_2 = " AND employees.branch_id = $branch_id ";
        }

        if ($permissions_level == "Outlet") {
            $branches = $this->db->get_where('branches', array('id' => $bid))->result();

            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("overview/late_frequency_report?branch=$bid");
                return;
            }
        }

        $where_filter = $data["where_filter"];
        $where_date = $data["where_date"];
        $where_clock_date = $data["where_clock_date"];

        $cid = $current_user["company_id"];

        $where_filter = str_replace("branch_id", "employees.branch_id", $where_filter);
        $where_filter = str_replace("department_id", "employees.department_id", $where_filter);

        $data["late_this_month"] = $this->db->query("SELECT count(1) as cnt,xxx.* FROM (SELECT employees.first_name,
        employees.special_id, branches.name as branch_name,departments.name as department_name,
        shifts.grace_time, shifts.start_time, shifts.name as shift_name, clockings_news.*
        FROM clockings_news
        INNER JOIN shift_days ON DATE(clockings_news.datetime) = shift_days.date
        INNER JOIN shifts ON clockings_news.shift_id = shifts.id
        INNER JOIN employees ON clockings_news.employee_id = employees.id
        INNER JOIN branches ON employees.branch_id = branches.id
        INNER JOIN departments ON employees.department_id = departments.id
        LEFT JOIN employee_groups_relation egr ON egr.employee_id = employees.id

        WHERE $where_filter
          AND shift_days.shift_id = clockings_news.shift_id
          AND clockings_news.datetime between '$data[start_date] 00:00:00'
          AND '$data[end_date] 23:59:59'
          GROUP BY employees.id, DATE(datetime)
        HAVING DATE_FORMAT(clockings_news.datetime, '%H:%i') > DATE_FORMAT(shifts.grace_time, '%H:%i')) as xxx
        GROUP BY employee_id HAVING cnt > 0 ORDER BY cnt DESC")->result();

        $data["filters"] = $this->load->view('filters', $data, true);

        $this->load->view('late_frequency_report', $data);
        $this->load->view('footer', $data);
    }

    public function late_frequency_comparison_report()
    {
        if (!is_page_permitted('late_frequency_comparison_report')) {
            redirect_if_not_permitted();
        }

        $current_user = get_user();
        $data['pageTitle'] = "Late Frequency Comparison";
        $data['active_menu'] = "overview/late_frequency_comparison_report";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);

        $data["filters_form_action"] = "overview/late_frequency_comparison_report";
        render_all_filters($data);
        $branch = null;
        $branches = null;



        $branch_id = $this->input->get('branch');
        $status = $this->input->get('status');
        $month = $this->input->get('month');
        $data['year'] = $year = $data['formatted_date']['start_date']->format('Y');

        $status = $this->input->get('status');


        $cid = $current_user["company_id"];

        if ($branch_id) {
            $branch = $this->db->get_where('branches', array('id' => $branch_id))->row();
        }



        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];

        $where_branch_2 = '';


        if (isset($branch)) {
            $where_branch_2 = " AND employees.branch_id = $branch_id ";
        }

        if ($permissions_level == "Outlet") {
            $branches = $this->db->get_where('branches', array('id' => $bid))->result();

            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("overview/late_frequency_comparison_report?branch=$bid");
                return;
            }
        }

        $where_filter = $data["where_filter"];
        $where_date = $data["where_date"];
        $where_clock_date = $data["where_clock_date"];

        $cid = $current_user["company_id"];

        $where_filter = str_replace("branch_id", "employees.branch_id", $where_filter);
        $where_filter = str_replace("department_id", "employees.department_id", $where_filter);


        //echo "The number is: ". sprintf("%02d", $x) ."<br>";

        //22 times late for august 2020

        for ($x = 1; $x <= intval(date('m')); $x++) {
            $month_loop = sprintf("%02d", $x);

            $data["late_data"][$month_loop] = $this->db->query("SELECT count(1) as cnt FROM (SELECT shifts.grace_time, shifts.start_time, shifts.name as shift_name, clockings_news.*
            FROM clockings_news
            INNER JOIN shift_days ON DATE(clockings_news.datetime) = shift_days.date
            INNER JOIN shifts ON clockings_news.shift_id = shifts.id
            INNER JOIN employees ON clockings_news.employee_id = employees.id
            INNER JOIN branches ON employees.branch_id = branches.id
            INNER JOIN departments ON employees.department_id = departments.id
            LEFT JOIN employee_groups_relation egr ON egr.employee_id = employees.id

            WHERE $where_filter
              AND shift_days.shift_id = clockings_news.shift_id
              AND MONTH(clockings_news.datetime) = $month_loop
              AND YEAR(clockings_news.datetime) = $year
              GROUP BY employees.id, DATE(datetime)
            HAVING DATE_FORMAT(clockings_news.datetime, '%H:%i') > DATE_FORMAT(shifts.grace_time, '%H:%i')) as xxx")->row()->cnt;
        }

        // var_dump($data["late_data"]);
        // die();







        $data["filters"] = $this->load->view('filters', $data, true);


        $this->load->view('late_frequency_comparison_report', $data);
        $this->load->view('footer', $data);
    }

    public function time_logs()
    {
        if (!is_page_permitted('time_logs')) {
            redirect_if_not_permitted();
        }

        $data['pageTitle'] = "Time Logs";
        $data['active_menu'] = "overview/time_logs";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);
        $data["filters_form_action"] = "overview/time_logs";
        render_all_filters($data);

        $branch_id = $this->input->get('branch');
        $department_id = $this->input->get('dep');
        $section_id = $this->input->get('sec');
        $position_id = $this->input->get('pos');
        $employee_id = $this->input->get('emp');
        $month = $this->input->get('month');
        $year = $this->input->get('year');
        $emp_group = $this->input->get('emp_group');

        $current_user = get_user();
        $data['current_user'] = $current_user;

        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];

        if ($permissions_level == "Outlet") {
            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("overview/time_logs?branch=$bid");
                return;
            }
        }

        $cid = $current_user["company_id"];
        $data["positions"] = $this->db->query("SELECT id,title as name FROM positions WHERE company_id = $cid ORDER BY name")->result();
        $data["sections"] = $this->db->query("SELECT id,title as name FROM sections WHERE company_id = $cid ORDER BY name")->result();
        $interval_minutes = get_interval_minutes($cid);

        $data["filters"] = $this->load->view('filters', $data, true);

        $this->db->select('COUNT(DISTINCT employees.id) total_records')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = employees.id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($section_id != "") {
            $this->db->where('employees.section_id', $section_id);
        }
        if ($position_id != "") {
            $this->db->where('employees.position_id', $position_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        if ($emp_group != "") {
            $this->db->where('egr.group_id', $emp_group);
        }
        $total_records = $this->db->where('roles.exclude_from_system', 'no')->order_by('employees.special_id', 'asc')->get()->row()->total_records;
        $limit = 30;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;

        $this->db->select('employees.id,employees.first_name as name,d.name as department,special_id,branch_id')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = employees.id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($section_id != "") {
            $this->db->where('employees.section_id', $section_id);
        }
        if ($position_id != "") {
            $this->db->where('employees.position_id', $position_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        if ($emp_group != "") {
            $this->db->where('egr.group_id', $emp_group);
        }
        $this->db->where('roles.exclude_from_system', 'no');
        $this->db->group_by('employees.id,employees.first_name,d.name,special_id');
        $employees = $this->db->order_by('special_id', 'asc')->limit($limit, $skip)->get()->result();

        $employees_ids = array('0');
        foreach ($employees as $emp) {
            $employees_ids[] = $emp->id;
        }

        // $max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $first_day = $data['start_date'];
        $last_day = $data['end_date'];

        $currentMonthDateObj = DateTime::createFromFormat('Y-m-d', $first_day);
        $data['month_f'] = $currentMonthDateObj->format('F');
        $data['year_f'] = $currentMonthDateObj->format('Y');

        $data['numberOfDays'] = $data['formatted_date']['start_date']->diff($data['formatted_date']['end_date'])->days + 1;

        $data["first_day"] = $data['formatted_date']['start_date']->format('d/m/Y');
        $data["last_day"] = $data['formatted_date']['end_date']->format('d/m/Y');

        $prev_day = date('Y-m-d', strtotime($first_day . ' -1 day'));

        $result_list = $this->db->select('id,employee_id,type,shift_id,date_format(datetime,"%H:%i") as clock_time, datetime as clock_time_o, date_format(datetime, "%Y-%m-%d") as search_date, clocking_remark, add_by_admin, update_by_admin', false)->from('clockings_news')->where('date(datetime) >=', $prev_day)->where('date(datetime) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();

        $result_list_overnight = $this->db->select('id,employee_id,type,shift_id, date_format(datetime,"%H:%i") as clock_time, datetime as clock_time_o, date_format(date_sub(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, clocking_remark, add_by_admin, update_by_admin', false)->from('clockings_news')->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) >=', $prev_day)->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();
        $result_list_preshift = $this->db->select('id,employee_id,type,shift_id, date_format(datetime,"%H:%i") as clock_time, datetime as clock_time_o, date_format(date_add(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, clocking_remark, add_by_admin, update_by_admin', false)->from('clockings_news')->where('date(date_add(datetime, interval ' . $interval_minutes . ' minute)) >=', $prev_day)->where('date(date_add(datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();
        $final_data = array();
        foreach ($employees as $emp) {
            $public_holidays = get_public_holidays_mine($emp->id, $emp->branch_id, $first_day, $last_day);
            $public_holidays_names = get_public_holidays_with_name($emp->branch_id, $first_day, $last_day)[1];

            $obj = new stdClass();
            $obj->employee = $emp;

            $shift_list = $this->db->select('code,date,overnight,is_preshift,color,end_time, coalesce(s.cut_off_time, c.cut_off_time, "07:00:00") as cut_off_time')->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->join('companies c', 'c.id = s.company_id')->where('FIND_IN_SET(' . $emp->id . ',employees)>', 0)->where('date >=', $prev_day)->where('date <=', $last_day)->get()->result();

            $period = new DatePeriod(
                new DateTime($prev_day),
                new DateInterval('P1D'),
                (new DateTime($last_day))->add(new DateInterval('P1D'))
            );
            $dates = array();
            $days = "<tr>";
            $last_ids = array();
            $skip_day = true;
            foreach ($period as $p) {
                $date = $p->format('Y-m-d');
                $day = $p->format('d');
                $day_name = $p->format('D');
                $clockings = array();
                $shift_code = "-";
                $holiday_class = "";
                $holiday_name = "";
                if (in_array($date, $public_holidays)) {
                    $holiday_class = "holiday";
                    $holiday_name = $public_holidays_names[array_search($date, $public_holidays)];
                }
                $shift = $this->search_from_list($shift_list, $date);
                $next_shift_check = $this->search_from_list($shift_list, date('Y-m-d', strtotime($date . ' +1 day')));
                $prev_shift_check = $this->search_from_list($shift_list, date('Y-m-d', strtotime($date . ' -1 day')));
                if ($shift && $shift->overnight == "Yes") {
                    $result = $this->search_clocking($result_list_overnight, $date, $emp->id, true);
                    // if ($next_shift_check && $next_shift_check->overnight == "No") {
                    $result = remove_next_day_clockings_timelog($result, $shift, $next_shift_check);
                    // }
                } elseif ($shift && isset($shift->is_preshift) && $shift->is_preshift == "Yes") {
                    $result = $this->search_clocking($result_list_preshift, $date, $emp->id, true);
                    $result = remove_previous_day_clockings_timelog($result, $shift, $prev_shift_check);
                } else {
                    $result = $this->search_clocking($result_list, $date, $emp->id);
                }
                $result = get_clockings_from_previous_day_timelog($result, $result_list_overnight, $date, $emp->id, $shift_list);
                $result = get_clockings_from_next_day_for_preshift_timelog($result, $result_list_preshift, $date, $emp->id, $shift_list);
                $result = remove_last_ids($result, $last_ids);
                $last_ids = array();
                if ($shift) {
                    $shift_code = "<span style='color: " . $shift->color . ";'>(" . $shift->code . ")</span>";
                }
                if (!$skip_day) {
                    $days .= "<td class='text-center'><strong><span class='$holiday_class' data-toggle='tooltip' data-html='true' data-placement='top' data-original-title='$holiday_name'>" . $day_name . "<br>" . $day . "</span><br>" . $shift_code . "</strong></td>";
                } else {
                    $skip_day = false;
                    foreach ($result as $r) {
                        $last_ids[] = $r->id;
                    }
                    continue;
                }
                $i = 0;
                // echo '<pre>';
                // print_r($result);
                // echo '</pre>';die;
                foreach ($result as $key => $r) {
                    // print_r($r);die;
                    $last_ids[] = $r->id;
                    if ($i % 2 == 0 && $r->type == "out") {
                        $clockings[] = "<span class='text-danger' data-toggle='tooltip' title='Missing IN'>MI</span>";
                        $i++;
                    } elseif ($i % 2 == 1 && $r->type == "in") {
                        $clockings[] = "<span class='text-danger' data-toggle='tooltip' title='Missing OUT'>MO</span>";
                        $i++;
                    }
                    if ($r->shift_id == 0 || $r->shift_id == null) {
                        if (!empty($r->clocking_remark)) {
                            $clockings[] = "<span style='color: #0000ff;' data-html='true' data-toggle='tooltip' title='No Shift <br> " . $r->clocking_remark . "'>" . $r->clock_time . "</span>";
                        } else {
                            $clockings[] = "<span class='text-danger' data-html='true' data-toggle='tooltip' title='No Shift'>" . $r->clock_time . "</span>";
                        }
                    } else {
                        if (!empty($r->clocking_remark)) {
                            if ($r->add_by_admin == 1 || $r->update_by_admin == 1) {
                                $clockings[] = "<span style='color: #008000;font-weight:700;' data-toggle='tooltip' title='" . $r->clocking_remark . "'>" . $r->clock_time . "</span>";
                            } else {
                                $clockings[] = "<span style='color: #008000;' data-toggle='tooltip' title='" . $r->clocking_remark . "'>" . $r->clock_time . "</span>";
                            }
                        } else {
                            if ($r->add_by_admin == 1 || $r->update_by_admin == 1) {
                                $clockings[] = "<span style='font-weight:700'>" . $r->clock_time . "</span>";
                            } else {
                                $clockings[] = "<span>" . $r->clock_time . "</span>";
                            }
                        }
                    }
                    $i++;
                    if ($key == count($result) - 1 && $r->type == "in") {
                        $clockings[] = "<span class='text-danger' data-toggle='tooltip' title='Missing OUT'>MO</span>";
                    }
                }
                $dates[] = implode("<br>", $clockings);
            }
            $days .= "</tr>";

            $obj->days = $days;
            $obj->dates = $dates;

            $final_data[] = $obj;
        }
        $data["final_data"] = $final_data;
        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        $data["skip"] = $skip;
        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET);
        $this->load->view('time_logs', $data);
        $this->load->view('footer', $data);
    }

    public function time_logs_pdf()
    {
        $branch_id = $this->input->get('branch');
        $department_id = $this->input->get('dep');
        $section_id = $this->input->get('sec');
        $employee_id = $this->input->get('emp');
        $month = $this->input->get('month');
        $year = $this->input->get('year');
        $emp_group = $this->input->get('emp_group');

        $current_user = get_user();
        $data['current_user'] = $current_user;

        $data["filters_form_action"] = "overview/time_logs";
        render_all_filters($data);

        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];

        if ($permissions_level == "Outlet") {
            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("overview/time_logs_pdf?branch=$bid");
                return;
            }
        }

        $data['pageTitle'] = "Time Logs PDF";
        $data['active_menu'] = "overview/time_logs_pdf";
        $data['branch_name'] = '';

        $cid = $current_user["company_id"];

        $interval_minutes = get_interval_minutes($cid);

        $this->db->select('COUNT(DISTINCT employees.id) total_records')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = employees.id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($section_id != "") {
            $this->db->where('employees.section_id', $section_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        if ($emp_group != "") {
            $this->db->where('egr.group_id', $emp_group);
        }
        $total_records = $this->db->where('roles.exclude_from_system', 'no')->order_by('special_id', 'asc')->get()->row()->total_records;
        $limit = 30;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;

        $this->db->select('employees.id,employees.first_name as name,d.name as department,special_id,branch_id')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = employees.id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($section_id != "") {
            $this->db->where('employees.section_id', $section_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        if ($emp_group != "") {
            $this->db->where('egr.group_id', $emp_group);
        }
        $this->db->where('roles.exclude_from_system', 'no');
        $this->db->group_by('employees.id,employees.first_name,d.name,special_id');
        $employees = $this->db->order_by('special_id', 'asc')->limit($limit, $skip)->get()->result();

        $employees_ids = array();
        foreach ($employees as $emp) {
            $employees_ids[] = $emp->id;
        }

        if ($branch_id != "") {
            $branch_row = $this->db->select('name')->from('branches')->where('id', $branch_id)->get()->row();
            if ($branch_row) {
                $data['branch_name'] = $branch_row->name;
            }
        }

        $first_day = $data['start_date'];
        $last_day = $data['end_date'];

        $currentMonthDateObj = DateTime::createFromFormat('Y-m-d', $first_day);
        $data['month_f'] = $currentMonthDateObj->format('F');
        $data['year_f'] = $currentMonthDateObj->format('Y');

        $data['numberOfDays'] = $data['formatted_date']['start_date']->diff($data['formatted_date']['end_date'])->days + 1;

        $data["first_day"] = $data['formatted_date']['start_date']->format('d/m/Y');
        $data["last_day"] = $data['formatted_date']['end_date']->format('d/m/Y');

        $prev_day = date('Y-m-d', strtotime($first_day . ' -1 day'));

        $result_list = $this->db->select('id,employee_id,type,shift_id,date_format(datetime,"%H:%i") as clock_time, datetime as clock_time_o, date_format(datetime, "%Y-%m-%d") as search_date', false)->from('clockings_news')->where('date(datetime) >=', $prev_day)->where('date(datetime) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();

        $result_list_overnight = $this->db->select('id,employee_id,type,shift_id, date_format(datetime,"%H:%i") as clock_time, datetime as clock_time_o, date_format(date_sub(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date', false)->from('clockings_news')->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) >=', $prev_day)->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();
        $result_list_preshift = $this->db->select('id,employee_id,type,shift_id, date_format(datetime,"%H:%i") as clock_time, datetime as clock_time_o, date_format(date_add(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, clocking_remark, add_by_admin, update_by_admin', false)->from('clockings_news')->where('date(date_add(datetime, interval ' . $interval_minutes . ' minute)) >=', $prev_day)->where('date(date_add(datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();
        $final_data = array();
        foreach ($employees as $emp) {
            $public_holidays = get_public_holidays_mine($emp->id, $emp->branch_id, $first_day, $last_day);
            $public_holidays_names = get_public_holidays_with_name($emp->branch_id, $first_day, $last_day)[1];

            $obj = new stdClass();
            $obj->employee = $emp;

            $shift_list = $this->db->select('code,date,overnight,is_preshift,color,end_time, coalesce(s.cut_off_time, c.cut_off_time, "07:00:00") as cut_off_time')->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->join('companies c', 'c.id = s.company_id')->where('FIND_IN_SET(' . $emp->id . ',employees)>', 0)->where('date >=', $prev_day)->where('date <=', $last_day)->get()->result();

            $period = new DatePeriod(
                new DateTime($prev_day),
                new DateInterval('P1D'),
                (new DateTime($last_day))->add(new DateInterval('P1D'))
            );
            $dates = array();
            $days = "<tr>";
            $last_ids = array();
            $skip_day = true;
            foreach ($period as $p) {
                $date = $p->format('Y-m-d');
                $day = $p->format('d');
                $day_name = $p->format('D');
                $clockings = array();
                $holiday_class = "";
                $holiday_name = "";
                if (in_array($date, $public_holidays)) {
                    $holiday_class = "holiday";
                    $holiday_name = $public_holidays_names[array_search($date, $public_holidays)];
                }
                $shift_code = "-";
                $shift = $this->search_from_list($shift_list, $date);
                $next_shift_check = $this->search_from_list($shift_list, date('Y-m-d', strtotime($date . ' +1 day')));
                $prev_shift_check = $this->search_from_list($shift_list, date('Y-m-d', strtotime($date . ' -1 day')));
                if ($shift && $shift->overnight == "Yes") {
                    $result = $this->search_clocking($result_list_overnight, $date, $emp->id, true);
                    // if ($next_shift_check && $next_shift_check->overnight == "No") {
                    $result = remove_next_day_clockings_timelog($result, $shift, $next_shift_check);
                    // }
                } elseif ($shift && isset($shift->is_preshift) && $shift->is_preshift == "Yes") {
                    $result = $this->search_clocking($result_list_preshift, $date, $emp->id, true);
                    $result = remove_previous_day_clockings_timelog($result, $shift, $prev_shift_check);
                } else {
                    $result = $this->search_clocking($result_list, $date, $emp->id);
                }
                $result = get_clockings_from_previous_day_timelog($result, $result_list_overnight, $date, $emp->id, $shift_list);
                $result = get_clockings_from_next_day_for_preshift_timelog($result, $result_list_preshift, $date, $emp->id, $shift_list);
                $result = remove_last_ids($result, $last_ids);
                $last_ids = array();
                if ($shift) {
                    $shift_code = "<span style='color: " . $shift->color . ";'>" . $shift->code . "</span>";
                }
                if (!$skip_day) {
                    $days .= "<td class='text-center'><strong><span class='$holiday_class' data-toggle='tooltip' data-html='true' data-placement='top' data-original-title='$holiday_name'>" . $day_name . "<br>" . $day . "</span><br><span class='shift'>" . $shift_code . "</span></strong></td>";
                } else {
                    $skip_day = false;
                    foreach ($result as $r) {
                        $last_ids[] = $r->id;
                    }
                    continue;
                }
                $i = 0;
                foreach ($result as $key => $r) {
                    $last_ids[] = $r->id;
                    if ($i % 2 == 0 && $r->type == "out") {
                        $clockings[] = "<span class='text-danger' title='Missing IN'>MI</span>";
                        $i++;
                    } elseif ($i % 2 == 1 && $r->type == "in") {
                        $clockings[] = "<span class='text-danger' title='Missing OUT'>MO</span>";
                        $i++;
                    }
                    if ($r->shift_id == 0 || $r->shift_id == null) {
                        $clockings[] = "<span class='text-danger' title='No Shift'>" . $r->clock_time . "</span>";
                    } else {
                        $clockings[] = $r->clock_time;
                    }
                    $i++;
                    if ($key == count($result) - 1 && $r->type == "in") {
                        $clockings[] = "<span class='text-danger' title='Missing OUT'>MO</span>";
                    }
                }
                $dates[] = implode("<br>", $clockings);
            }
            $days .= "</tr>";

            $obj->days = $days;
            $obj->dates = $dates;

            $final_data[] = $obj;
        }
        $data["final_data"] = $final_data;
        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        $data["skip"] = $skip;
        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET);
        $this->load->view('time_logs_pdf', $data);

        $html = $this->output->get_output();
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper("A4", "landscape");
        $this->dompdf->render();
        $export_date = date('Y-m-d H:i:s');
        $this->dompdf->stream("time-logs-$export_date");
        insert_log("Simple", ["action" => "Exported,time logs"]);
    }

    public function time_logs_excel()
    {
        $branch_id = $this->input->get('branch');
        $department_id = $this->input->get('dep');
        $section_id = $this->input->get('sec');
        $employee_id = $this->input->get('emp');
        $month = $this->input->get('month');
        $year = $this->input->get('year');
        $emp_group = $this->input->get('emp_group');

        $current_user = get_user();

        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];
        $data = [];
        render_all_filters($data);

        if ($permissions_level == "Outlet") {
            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("overview/time_logs?branch=$bid");
                return;
            }
        }

        $cid = $current_user["company_id"];

        $interval_minutes = get_interval_minutes($cid);

        $this->db->select('COUNT(DISTINCT employees.id) total_records')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = employees.id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($section_id != "") {
            $this->db->where('employees.section_id', $section_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        if ($emp_group != "") {
            $this->db->where('egr.group_id', $emp_group);
        }
        $total_records = $this->db->where('roles.exclude_from_system', 'no')->order_by('employees.special_id', 'asc')->get()->row()->total_records;
        $limit = 30;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;

        $this->db->select('employees.id,employees.first_name as name,d.name as department,special_id')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = employees.id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($section_id != "") {
            $this->db->where('employees.section_id', $section_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        if ($emp_group != "") {
            $this->db->where('egr.group_id', $emp_group);
        }
        $this->db->where('roles.exclude_from_system', 'no');
        $this->db->group_by('employees.id,employees.first_name,d.name,special_id');
        $employees = $this->db->order_by('special_id', 'asc')->limit($limit, $skip)->get()->result();

        $employees_ids = array('0');
        foreach ($employees as $emp) {
            $employees_ids[] = $emp->id;
        }

        $max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $first_day = $data['start_date'];
        $last_day = $data['end_date'];

        $data['numberOfDays'] = $data['formatted_date']['start_date']->diff($data['formatted_date']['end_date'])->days + 1;

        $data["first_day"] = $data['formatted_date']['start_date']->format('d/m/Y');
        $data["last_day"] = $data['formatted_date']['end_date']->format('d/m/Y');

        $prev_day = date('Y-m-d', strtotime($first_day . ' -1 day'));

        $result_list = $this->db->select('id,employee_id,type,shift_id,date_format(datetime,"%H:%i") as clock_time, datetime as clock_time_o, date_format(datetime, "%Y-%m-%d") as search_date, clocking_remark', false)->from('clockings_news')->where('date(datetime) >=', $prev_day)->where('date(datetime) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();

        $result_list_overnight = $this->db->select('id,employee_id,type,shift_id, date_format(datetime,"%H:%i") as clock_time, datetime as clock_time_o, date_format(date_sub(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, clocking_remark', false)->from('clockings_news')->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) >=', $prev_day)->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();
        $result_list_preshift = $this->db->select('id,employee_id,type,shift_id, date_format(datetime,"%H:%i") as clock_time, datetime as clock_time_o, date_format(date_add(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, clocking_remark, add_by_admin, update_by_admin', false)->from('clockings_news')->where('date(date_add(datetime, interval ' . $interval_minutes . ' minute)) >=', $prev_day)->where('date(date_add(datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();
        $final_data = array();
        foreach ($employees as $emp) {
            $obj = new stdClass();
            $obj->employee = $emp;
            $obj->days = [];

            $shift_list = $this->db->select('code,date,overnight,is_preshift,color,end_time, coalesce(s.cut_off_time, c.cut_off_time, "07:00:00") as cut_off_time')->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->join('companies c', 'c.id = s.company_id')->where('FIND_IN_SET(' . $emp->id . ',employees)>', 0)->where('date >=', $prev_day)->where('date <=', $last_day)->get()->result();

            $period = new DatePeriod(
                new DateTime($prev_day),
                new DateInterval('P1D'),
                (new DateTime($last_day))->add(new DateInterval('P1D'))
            );
            $dates = array();
            $last_ids = array();
            $skip_day = true;
            foreach ($period as $index => $p) {
                $date = $p->format('Y-m-d');
                $day = $p->format('d');
                $day_name = $p->format('D');

                $clockings = array();
                $shift_code = "-";
                $shift = $this->search_from_list($shift_list, $date);
                $next_shift_check = $this->search_from_list($shift_list, date('Y-m-d', strtotime($date . ' +1 day')));
                $prev_shift_check = $this->search_from_list($shift_list, date('Y-m-d', strtotime($date . ' -1 day')));
                if ($shift && $shift->overnight == "Yes") {
                    $result = $this->search_clocking($result_list_overnight, $date, $emp->id, true);
                    // if ($next_shift_check && $next_shift_check->overnight == "No") {
                    $result = remove_next_day_clockings_timelog($result, $shift, $next_shift_check);
                    // }
                } elseif ($shift && isset($shift->is_preshift) && $shift->is_preshift == "Yes") {
                    $result = $this->search_clocking($result_list_preshift, $date, $emp->id, true);
                    $result = remove_previous_day_clockings_timelog($result, $shift, $prev_shift_check);
                } else {
                    $result = $this->search_clocking($result_list, $date, $emp->id);
                }
                $result = get_clockings_from_previous_day_timelog($result, $result_list_overnight, $date, $emp->id, $shift_list);
                $result = get_clockings_from_next_day_for_preshift_timelog($result, $result_list_preshift, $date, $emp->id, $shift_list);
                $result = remove_last_ids($result, $last_ids);
                $last_ids = array();
                if ($shift) {
                    $shift_code = "(" . $shift->code . ")";
                }

                if ($skip_day) {
                    $skip_day = false;
                    foreach ($result as $r) {
                        $last_ids[] = $r->id;
                    }
                    continue;
                }

                $obj->days[$index]["day_name"] = $day_name;
                $obj->days[$index]["day"] = $day;

                $obj->days[$index]["shift_code"] = $shift_code;

                $i = 0;
                foreach ($result as $key => $r) {
                    $last_ids[] = $r->id;
                    if ($i % 2 == 0 && $r->type == "out") {
                        $clocking_obj = new stdClass();
                        $clocking_obj->clock_time = "MI";
                        $clocking_obj->clocking_remark = null;
                        $clockings[] = $clocking_obj;
                        $i++;
                    } elseif ($i % 2 == 1 && $r->type == "in") {
                        $clocking_obj = new stdClass();
                        $clocking_obj->clock_time = "MO";
                        $clocking_obj->clocking_remark = null;
                        $clockings[] = $clocking_obj;
                        $i++;
                    }
                    if ($r->shift_id == 0 || $r->shift_id == null) {
                        $clockings[] = $r;
                    } else {
                        $clockings[] = $r;
                    }
                    $i++;
                    if ($key == count($result) - 1 && $r->type == "in") {
                        $clocking_obj = new stdClass();
                        $clocking_obj->clock_time = "MO";
                        $clocking_obj->clocking_remark = null;
                        $clockings[] = $clocking_obj;
                    }
                }
                $dates[] = $clockings;
            }

            $obj->dates = $dates;
            $final_data[] = $obj;
        }
        $data["final_data"] = $final_data;
        $this->load->library("excel");
        $object = new PHPExcel();
        $object->setActiveSheetIndex(0);
        // print_r($final_data);die;
        $row = 1;
        foreach ($final_data as $f) {
            $count1 = count($f->dates[0]);
            $count2 = count($f->dates[1]);
            $count = $count1 + $count2;
            // print_r($f);die;
            // echo $count;die;
            // $object->getActiveSheet()->fromArray(array('ID: '.$f->employee->special_id, 'Name: '.$f->employee->name, 'Department: '.$f->employee->department), null, 'A' . $row);
            $object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, 'ID: ' . $f->employee->special_id);
            $object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, 'Name: ' . $f->employee->name);
            $object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, 'Department: ' . $f->employee->department);
            $object->getActiveSheet()->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true);
            $object->getActiveSheet()->getStyleByColumnAndRow(1, $row)->getFont()->setBold(true);
            $object->getActiveSheet()->getStyleByColumnAndRow(2, $row)->getFont()->setBold(true);

            $row++;
            $column = 0;
            foreach ($f->days as $day) {
                $object->getActiveSheet()->setCellValueByColumnAndRow($column++, $row, $day["day_name"] . "\n" . $day["day"] . "\n" . $day["shift_code"]);
                $object->getActiveSheet()->setCellValueByColumnAndRow($column++, $row, "Clocking Remarks");
            }
            $row++;
            $column = 0;
            $maxRowUsed = $row;
            // $count = 0;
            foreach ($f->dates as $d) {
                $current_row = $row;
                // if(count($d) === 0) $column += 2;
                foreach ($d as $clocking) {
                    $object->getActiveSheet()->setCellValueByColumnAndRow($column, $current_row, $clocking->clock_time);
                    $object->getActiveSheet()->setCellValueByColumnAndRow($column + 1, $current_row, $clocking->clocking_remark);
                    $current_row++;
                    // $count++;
                }

                // update the max row used
                if ($current_row > $maxRowUsed) {
                    $maxRowUsed = $current_row;
                }
                $column += 2;
            }
            $row = $maxRowUsed + 3;
        }
        for ($i = 'A'; $i < 'ZZ'; $i++) {
            if (
                $i == 'A' || $i == 'C' || $i == 'E' || $i == 'G' ||
                $i == 'I' || $i == 'K' || $i == 'M' || $i == 'O' ||
                $i == 'Q' || $i == 'S' || $i == 'U' || $i == 'W' ||
                $i == 'Y' || $i == 'AA' || $i == 'AC' || $i == 'AE' ||
                $i == 'AG' || $i == 'AI' || $i == 'AK' || $i == 'AM' ||
                $i == 'AO' || $i == 'AQ' || $i == 'AS' || $i == 'AU' ||
                $i == 'AW' || $i == 'AY' || $i == 'BA' || $i == 'BC' ||
                $i == 'BE' || $i == 'BG' || $i == 'BI' || $i == 'BK' ||
                $i == 'BM' || $i == 'BO' || $i == 'BQ' || $i == 'BS' ||
                $i == 'BU' || $i == 'BW' || $i == 'BY'
            ) {
                $object->getActiveSheet()->getColumnDimension($i)->setWidth(11);
            } else {
                $object->getActiveSheet()->getColumnDimension($i)->setWidth(20);
            }
            // $object->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $export_date = date('Y-m-d H:i:s');
        $filename = 'time-logs-' . $export_date . '.xls'; //save our workbook as this file name

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.ms-excel'); //mime type

        header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name

        header('Cache-Control: max-age=0'); //no cache

        $object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
        $object_writer->save("php://output");
        exit;
    }

    public function edited_time_logs_excel()
    {
        $branch_id = $this->input->get('branch');
        $department_id = $this->input->get('dep');
        $employee_id = $this->input->get('emp');
        $month = $this->input->get('month');
        $year = $this->input->get('year');
        $emp_group = $this->input->get('emp_group');

        $current_user = get_user();

        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];

        if ($permissions_level == "Outlet") {
            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("overview/edited_time_logs?branch=$bid");
                return;
            }
        }

        $cid = $current_user["company_id"];

        $interval_minutes = get_interval_minutes($cid);

        $this->db->select('COUNT(DISTINCT employees.id) total_records')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = employees.id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        if ($emp_group != "") {
            $this->db->where('egr.group_id', $emp_group);
        }
        $total_records = $this->db->where('roles.exclude_from_system', 'no')->order_by('employees.special_id', 'asc')->get()->row()->total_records;
        $limit = 30;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;

        $this->db->select('employees.id,employees.first_name as name,d.name as department,special_id')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = employees.id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        if ($emp_group != "") {
            $this->db->where('egr.group_id', $emp_group);
        }
        $this->db->where('roles.exclude_from_system', 'no');
        $this->db->group_by('employees.id,employees.first_name,d.name,special_id');
        $employees = $this->db->order_by('special_id', 'asc')->limit($limit, $skip)->get()->result();

        $employees_ids = array('0');
        foreach ($employees as $emp) {
            $employees_ids[] = $emp->id;
        }

        $max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $first_day = sprintf("%04d-%02d-%02d", $year, $month, 1);
        $last_day = sprintf("%04d-%02d-%02d", $year, $month, $max_date);

        $data["first_day"] = sprintf("%02d/%02d/%02d", 1, $month, $year);
        $data["last_day"] = sprintf("%02d/%02d/%02d", $max_date, $month, $year);

        $prev_day = date('Y-m-d', strtotime($first_day . ' -1 day'));

        $result_list = $this->db->select('cn.id,cn.employee_id,cn.type,shift_id,date_format(cn.datetime,"%H:%i") as clock_time,date_format(cn.datetime, "%Y-%m-%d") as search_date, clocking_remark, add_by_admin, update_by_admin, ec.type ec_type, date_format(ec.datetime,"%H:%i") as ec_clock_time,date_format(ec.datetime, "%Y-%m-%d") as ec_search_date', false)->from('clockings_news cn')->join('edited_clockings ec', 'ec.clocking_id = cn.id and ec.deleted_at is null', 'left')->where('date(cn.datetime) >=', $first_day)->where('date(cn.datetime) <=', $last_day)->where_in('cn.employee_id', $employees_ids)->where('cn.deleted_at is null')->order_by('cn.datetime')->get()->result();

        $result_list_overnight = $this->db->select('cn.id,cn.employee_id,cn.type,shift_id, date_format(cn.datetime,"%H:%i") as clock_time,date_format(date_sub(cn.datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, clocking_remark, add_by_admin, update_by_admin,  ec.type ec_type, date_format(ec.datetime,"%H:%i") as ec_clock_time,date_format(ec.datetime, "%Y-%m-%d") as ec_search_date', false)->from('clockings_news cn')->join('edited_clockings ec', 'ec.clocking_id = cn.id and ec.deleted_at is null', 'left')->where('date(date_sub(cn.datetime, interval ' . $interval_minutes . ' minute)) >=', $first_day)->where('date(date_sub(cn.datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('cn.employee_id', $employees_ids)->where('cn.deleted_at is null')->order_by('cn.datetime')->get()->result();

        $final_data = array();
        foreach ($employees as $emp) {
            $obj = new stdClass();
            $obj->employee = $emp;
            $obj->days = [];

            $shift_list = $this->db->select('code,date,overnight,color,end_time, coalesce(s.cut_off_time, c.cut_off_time, "07:00:00") as cut_off_time')->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->join('companies c', 'c.id = s.company_id')->where('FIND_IN_SET(' . $emp->id . ',employees)>', 0)->where('date >=', $prev_day)->where('date <=', $last_day)->get()->result();

            $period = new DatePeriod(
                new DateTime($prev_day),
                new DateInterval('P1D'),
                (new DateTime($last_day))->add(new DateInterval('P1D'))
            );
            $dates = array();
            $last_ids = array();
            $skip_day = true;
            foreach ($period as $index => $p) {
                $date = $p->format('Y-m-d');
                $day = $p->format('d');
                $day_name = $p->format('D');

                $edited_clockings = [];
                $shift_code = "-";
                $shift = $this->search_from_list($shift_list, $date);
                $next_shift_check = $this->search_from_list($shift_list, date('Y-m-d', strtotime($date . ' +1 day')));
                if ($shift && $shift->overnight == "Yes") {
                    $result = $this->search_clocking($result_list_overnight, $date, $emp->id, true);
                    $result = remove_next_day_clockings_timelog($result, $shift, $next_shift_check);
                } else {
                    $result = $this->search_clocking($result_list, $date, $emp->id);
                }
                $result = get_clockings_from_previous_day_timelog($result, $result_list_overnight, $date, $emp->id, $shift_list);
                $result = remove_last_ids($result, $last_ids);
                $last_ids = array();
                if ($shift) {
                    $shift_code = "(" . $shift->code . ")";
                }

                if ($skip_day) {
                    $skip_day = false;
                    foreach ($result as $r) {
                        $last_ids[] = $r->id;
                    }
                    continue;
                }

                $obj->days[$index]["day_name"] = $day_name;
                $obj->days[$index]["day"] = $day;

                $obj->days[$index]["shift_code"] = $shift_code;

                $i = 0;
                foreach ($result as $key => $r) {
                    $last_ids[] = $r->id;
                    if ($i % 2 == 0 && $r->type == "out") {
                        $clocking_obj = new stdClass();
                        $clocking_obj->clock_time = "MI";
                        $clocking_obj->clocking_remark = null;
                        $edited_clockings[] = $clocking_obj;
                        $i++;
                    } elseif ($i % 2 == 1 && $r->type == "in") {
                        $clocking_obj = new stdClass();
                        $clocking_obj->clock_time = "MO";
                        $clocking_obj->clocking_remark = null;
                        $edited_clockings[] = $clocking_obj;
                        $i++;
                    }
                    if ($r->shift_id == 0 || $r->shift_id == null) {
                        $edited_clockings[] = "";
                    } else {
                        if ($r->add_by_admin == 1 || $r->update_by_admin == 1) {
                            $clocking_obj = new stdClass();
                            $clocking_obj->clock_time = $r->ec_clock_time;
                            $clocking_obj->clocking_remark = null;
                            $edited_clockings[] = $clocking_obj;
                        } else {
                            $edited_clockings[] = "";
                        }
                    }
                    $i++;
                    if ($key == count($result) - 1 && $r->type == "in") {
                        $clocking_obj = new stdClass();
                        $clocking_obj->clock_time = "MO";
                        $clocking_obj->clocking_remark = null;
                        $edited_clockings[] = $clocking_obj;
                    }
                }
                $dates[] = $edited_clockings;
            }

            $obj->dates = $dates;
            $final_data[] = $obj;
        }
        $data["final_data"] = $final_data;
        $this->load->library("excel");
        $object = new PHPExcel();
        $object->setActiveSheetIndex(0);
        // print_r($final_data);die;
        $row = 1;
        foreach ($final_data as $f) {
            $count1 = count($f->dates[0]);
            $count2 = count($f->dates[1]);
            $count = $count1 + $count2;
            // print_r($f);die;
            // echo $count;die;
            // $object->getActiveSheet()->fromArray(array('ID: '.$f->employee->special_id, 'Name: '.$f->employee->name, 'Department: '.$f->employee->department), null, 'A' . $row);
            $object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, 'ID: ' . $f->employee->special_id);
            $object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, 'Name: ' . $f->employee->name);
            $object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, 'Department: ' . $f->employee->department);
            $object->getActiveSheet()->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true);
            $object->getActiveSheet()->getStyleByColumnAndRow(1, $row)->getFont()->setBold(true);
            $object->getActiveSheet()->getStyleByColumnAndRow(2, $row)->getFont()->setBold(true);

            $row++;
            $column = 0;
            foreach ($f->days as $day) {
                $object->getActiveSheet()->setCellValueByColumnAndRow($column++, $row, $day["day_name"] . "\n" . $day["day"] . "\n" . $day["shift_code"]);
                $object->getActiveSheet()->setCellValueByColumnAndRow($column++, $row, "Clocking Remarks");
            }
            $row++;
            $column = 0;
            // $count = 0;
            foreach ($f->dates as $index => $d) {
                $current_row = $row;
                // if(count($d) === 0) $column += 2;
                foreach ($d as $clocking) {
                    $object->getActiveSheet()->setCellValueByColumnAndRow($column, $current_row, $clocking->clock_time);
                    $object->getActiveSheet()->setCellValueByColumnAndRow($column + 1, $current_row, $clocking->clocking_remark);
                    $current_row++;
                    // $count++;
                }
                $column += 2;
            }
            $count += 1;
            $row += $count;
        }
        for ($i = 'A'; $i < 'ZZ'; $i++) {
            if (
                $i == 'A' || $i == 'C' || $i == 'E' || $i == 'G' ||
                $i == 'I' || $i == 'K' || $i == 'M' || $i == 'O' ||
                $i == 'Q' || $i == 'S' || $i == 'U' || $i == 'W' ||
                $i == 'Y' || $i == 'AA' || $i == 'AC' || $i == 'AE' ||
                $i == 'AG' || $i == 'AI' || $i == 'AK' || $i == 'AM' ||
                $i == 'AO' || $i == 'AQ' || $i == 'AS' || $i == 'AU' ||
                $i == 'AW' || $i == 'AY' || $i == 'BA' || $i == 'BC' ||
                $i == 'BE' || $i == 'BG' || $i == 'BI' || $i == 'BK' ||
                $i == 'BM' || $i == 'BO' || $i == 'BQ' || $i == 'BS' ||
                $i == 'BU' || $i == 'BW' || $i == 'BY'
            ) {
                $object->getActiveSheet()->getColumnDimension($i)->setWidth(11);
            } else {
                $object->getActiveSheet()->getColumnDimension($i)->setWidth(20);
            }
            // $object->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
        }

        $export_date = date('Y-m-d H:i:s');
        $filename = 'time-logs-' . $export_date . '.xls'; //save our workbook as this file name

        header('Content-Type: application/vnd.ms-excel'); //mime type

        header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name

        header('Cache-Control: max-age=0'); //no cache

        $object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
        $object_writer->save("php://output");
    }

    public function time_logs_daily()
    {
        if (!is_page_permitted('time_logs_daily')) {
            redirect_if_not_permitted();
        }

        $current_user = get_user();
        $branch_id = $this->input->get('branch');
        $department_id = $this->input->get('dep');
        $employee_id = $this->input->get('emp');
        $date = $this->input->get("date");

        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];

        if ($permissions_level == "Outlet") {
            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("overview/time_logs_daily?branch=$bid");
                return;
            }
        }
        $data['pageTitle'] = "Daily Time Logs";
        $data['active_menu'] = "overview/time_logs_daily";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);

        $data["filters_form_action"] = "overview/time_logs_daily";
        render_daily_time_logs_filter($data);
        $data["date_f"] = $date;
        $cid = $current_user["company_id"];

        $interval_minutes = get_interval_minutes($cid);

        $data["filters"] = $this->load->view('filters_timelog_daily', $data, true);

        $this->db->select('employees.id,employees.first_name as name,d.name as department,special_id')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        $employees = $this->db->where('roles.exclude_from_system', 'no')->order_by('employees.special_id', 'asc')->get()->result();
        $total_records = count($employees);
        $limit = 30;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;

        $this->db->select('employees.id,employees.first_name as name,d.name as department,special_id')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        $employees = $this->db->where('roles.exclude_from_system', 'no')->order_by('special_id', 'asc')->limit($limit, $skip)->get()->result();
        $employees_ids = array('0');
        foreach ($employees as $emp) {
            $employees_ids[] = $emp->id;
        }

        $db_date = DateTime::createFromFormat("d/m/Y", $date);
        $db_date_string = $db_date->format("Y-m-d");
        $month = $db_date->format("m");
        $year = $db_date->format("Y");
        $day = $db_date->format("D");

        $data["month"] = $month;
        $data["year"] = $year;
        $data["day"] = $day;

        // $max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // $first_day = sprintf("%04d-%02d-%02d", $year, $month, 1);
        // $last_day = sprintf("%04d-%02d-%02d", $year, $month, $max_date);

        // $data["first_day"] = sprintf("%02d/%02d/%02d", 1, $month, $year);
        // $data["last_day"] = sprintf("%02d/%02d/%02d", $max_date, $month, $year);

        // echo "<pre>";
        // var_dump($db_date_string, $employees_ids);
        // echo "</pre>";
        // exit;

        $result_list = $this->db->select('id,employee_id,type,shift_id,date_format(datetime,"%H:%i") as clock_time, datetime as clock_time_o, date_format(datetime, "%Y-%m-%d") as search_date', false)->from('clockings_news')->where('date(datetime) >=', $db_date_string)->where('date(datetime) <=', $db_date_string)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();

        $result_list_overnight = $this->db->select('id,employee_id,type,shift_id, date_format(datetime,"%H:%i") as clock_time, datetime as clock_time_o, date_format(date_sub(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date', false)->from('clockings_news')->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) >=', $db_date_string)->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) <=', $db_date_string)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();

        $final_data = array();
        $max_col_span = 0;
        foreach ($employees as $emp) {
            $obj = new stdClass();
            $obj->employee = $emp;

            $shift_list = $this->db->select('code,date,overnight,color,end_time, coalesce(s.cut_off_time, c.cut_off_time, "07:00:00") as cut_off_time')->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->join('companies c', 'c.id = s.company_id')->where('FIND_IN_SET(' . $emp->id . ',employees)>', 0)->where('date >=', $db_date_string)->where('date <=', $db_date_string)->get()->result();

            $last_ids = array();

            $clockings = array();
            $shift_code = "-";
            $shift = $this->search_from_list($shift_list, $db_date_string);
            $next_shift_check = $this->search_from_list($shift_list, date('Y-m-d', strtotime($db_date_string . ' +1 day')));
            if ($shift && $shift->overnight == "Yes") {
                $result = $this->search_clocking($result_list_overnight, $db_date_string, $emp->id, true);
                $result = remove_next_day_clockings_timelog($result, $shift, $next_shift_check);
            } else {
                $result = $this->search_clocking($result_list, $db_date_string, $emp->id);
            }
            $result = get_clockings_from_previous_day_timelog($result, $result_list_overnight, $date, $emp->id, $shift_list);
            $result = remove_last_ids($result, $last_ids);
            $last_ids = array();
            if ($shift) {
                $shift_code = "(" . $shift->code . ")";
            }

            $obj->shift_code = $shift_code;

            $i = 0;
            foreach ($result as $key => $r) {
                $last_ids[] = $r->id;
                if ($i % 2 == 0 && $r->type == "out") {
                    $clockings[] = "<span class='text-danger m-r-5' title='Missing IN'>MI</span>";
                    $i++;
                } elseif ($i % 2 == 1 && $r->type == "in") {
                    $clockings[] = "<span class='text-danger m-r-5' title='Missing OUT'>MO</span>";
                    $i++;
                }
                if ($r->shift_id == 0 || $r->shift_id == null) {
                    $clockings[] = "<span class='text-danger m-r-5' title='No Shift'>" . $r->clock_time . "</span>";
                } else {
                    $clockings[] = "<span class='m-r-5'>" . $r->clock_time . "</span>";
                }
                $i++;
                if ($key == count($result) - 1 && $r->type == "in") {
                    $clockings[] = "<span class='text-danger m-r-5' title='Missing OUT'>MO</span>";
                }
            }

            // $obj->days = $days;
            $obj->clockings = $clockings;

            $clockings_count = count($clockings);
            if ($clockings_count > $max_col_span) {
                $max_col_span = $clockings_count;
            }

            $final_data[] = $obj;
        }

        $data["final_data"] = $final_data;
        $data["max_col_span"] = $max_col_span;
        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        $data["skip"] = $skip;
        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET);
        $this->load->view('time_logs_daily', $data);
        $this->load->view('footer', $data);
    }

    public function time_logs_daily_pdf()
    {
        $current_user = get_user();
        $branch_id = $this->input->get('branch');
        $department_id = $this->input->get('dep');
        $employee_id = $this->input->get('emp');
        $date = $this->input->get("date");

        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];

        if ($permissions_level == "Outlet") {
            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("overview/time_logs_daily_pdf?branch=$bid");
                return;
            }
        }
        $data['pageTitle'] = "Daily Time Logs PDF";
        $data['active_menu'] = "overview/time_logs_daily_pdf";

        $data["filters_form_action"] = "overview/time_logs_daily_pdf";

        $data["date_f"] = $date;
        $cid = $current_user["company_id"];

        $interval_minutes = get_interval_minutes($cid);

        $this->db->select('employees.id,employees.first_name as name,d.name as department,special_id')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        $employees = $this->db->where('roles.exclude_from_system', 'no')->order_by('employees.special_id', 'asc')->get()->result();
        $total_records = count($employees);
        $limit = 30;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;

        $this->db->select('employees.id,employees.first_name as name,d.name as department,special_id')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        $employees = $this->db->where('roles.exclude_from_system', 'no')->order_by('special_id', 'asc')->limit($limit, $skip)->get()->result();
        $employees_ids = array('0');
        foreach ($employees as $emp) {
            $employees_ids[] = $emp->id;
        }

        $db_date = DateTime::createFromFormat("d/m/Y", $date);
        $db_date_string = $db_date->format("Y-m-d");
        $month = $db_date->format("m");
        $year = $db_date->format("Y");
        $day = $db_date->format("D");

        $data["month"] = $month;
        $data["year"] = $year;
        $data["day"] = $day;
        // $max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // $first_day = sprintf("%04d-%02d-%02d", $year, $month, 1);
        // $last_day = sprintf("%04d-%02d-%02d", $year, $month, $max_date);

        // $data["first_day"] = sprintf("%02d/%02d/%02d", 1, $month, $year);
        // $data["last_day"] = sprintf("%02d/%02d/%02d", $max_date, $month, $year);

        // echo "<pre>";
        // var_dump($db_date_string, $employees_ids);
        // echo "</pre>";
        // exit;

        $result_list = $this->db->select('id,employee_id,type,shift_id,date_format(datetime,"%H:%i") as clock_time, datetime as clock_time_o, date_format(datetime, "%Y-%m-%d") as search_date', false)->from('clockings_news')->where('date(datetime) >=', $db_date_string)->where('date(datetime) <=', $db_date_string)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();

        $result_list_overnight = $this->db->select('id,employee_id,type,shift_id, date_format(datetime,"%H:%i") as clock_time, datetime as clock_time_o, date_format(date_sub(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date', false)->from('clockings_news')->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) >=', $db_date_string)->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) <=', $db_date_string)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();

        $final_data = array();
        $max_col_span = 0;
        foreach ($employees as $emp) {
            $obj = new stdClass();
            $obj->employee = $emp;

            $shift_list = $this->db->select('code,date,overnight,color,end_time, coalesce(s.cut_off_time, c.cut_off_time, "07:00:00") as cut_off_time')->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->join('companies c', 'c.id = s.company_id')->where('FIND_IN_SET(' . $emp->id . ',employees)>', 0)->where('date >=', $db_date_string)->where('date <=', $db_date_string)->get()->result();

            $last_ids = array();

            $clockings = array();
            $shift_code = "-";
            $shift = $this->search_from_list($shift_list, $db_date_string);
            $next_shift_check = $this->search_from_list($shift_list, date('Y-m-d', strtotime($db_date_string . ' +1 day')));
            if ($shift && $shift->overnight == "Yes") {
                $result = $this->search_clocking($result_list_overnight, $db_date_string, $emp->id, true);
                $result = remove_next_day_clockings_timelog($result, $shift, $next_shift_check);
            } else {
                $result = $this->search_clocking($result_list, $db_date_string, $emp->id);
            }
            $result = get_clockings_from_previous_day_timelog($result, $result_list_overnight, $date, $emp->id, $shift_list);
            $result = remove_last_ids($result, $last_ids);
            $last_ids = array();
            if ($shift) {
                $shift_code = "(" . $shift->code . ")";
            }

            $obj->shift_code = $shift_code;

            $i = 0;
            foreach ($result as $key => $r) {
                $last_ids[] = $r->id;
                if ($i % 2 == 0 && $r->type == "out") {
                    $clockings[] = "<span class='text-danger m-r-5' title='Missing IN'>MI</span>";
                    $i++;
                } elseif ($i % 2 == 1 && $r->type == "in") {
                    $clockings[] = "<span class='text-danger m-r-5' title='Missing OUT'>MO</span>";
                    $i++;
                }
                if ($r->shift_id == 0 || $r->shift_id == null) {
                    $clockings[] = "<span class='text-danger m-r-5' title='No Shift'>" . $r->clock_time . "</span>";
                } else {
                    $clockings[] = "<span class='m-r-5'>" . $r->clock_time . "</span>";
                }
                $i++;
                if ($key == count($result) - 1 && $r->type == "in") {
                    $clockings[] = "<span class='text-danger m-r-5' title='Missing OUT'>MO</span>";
                }
            }

            // $obj->days = $days;
            $obj->clockings = $clockings;

            $clockings_count = count($clockings);
            if ($clockings_count > $max_col_span) {
                $max_col_span = $clockings_count;
            }

            $final_data[] = $obj;
        }

        $data["final_data"] = $final_data;
        $data["max_col_span"] = $max_col_span;
        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        $data["skip"] = $skip;
        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET);
        $this->load->view('time_logs_pdf_daily', $data);

        $html = $this->output->get_output();
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper('A4', 'portrait');
        $this->dompdf->render();
        $export_date = $db_date->format('Y-m-d');
        $this->dompdf->stream("daily-time-logs-$export_date-$page");
        insert_log("Simple", ["action" => "Exported,time logs daily"]);
    }

    function search_clocking($list, $date, $id, $next_day_out = false)
    {
        $result = array();
        foreach ($list as $l) {
            if ($l->search_date == $date && $l->employee_id == $id) {
                $result[] = $l;
            }
        }
        // if last clocking is in and next day out is true then add missing out
        // search result of next day and check if first clocking is out then add that to result
        if ($next_day_out && count($result) > 0 && $result[count($result) - 1]->type == "in") {
            $next_date = date('Y-m-d', strtotime($date . ' +1 day'));
            $next_result = array();
            foreach ($list as $l) {
                if ($l->search_date == $next_date && $l->employee_id == $id) {
                    $next_result[] = $l;
                }
            }
            if (count($next_result) > 0 && $next_result[0]->type == "out") {
                $result[] = $next_result[0];
            }
        }

        return $result;
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

    public function edited_time_logs()
    {
        if (!is_page_permitted('edited_time_logs')) {
            redirect_if_not_permitted();
        }

        $data['pageTitle'] = "Edited Time Logs";
        $data['active_menu'] = "overview/edited_time_logs";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);
        $data["filters_form_action"] = "overview/edited_time_logs";
        render_all_filters($data);

        $branch_id = $this->input->get('branch');
        $department_id = $this->input->get('dep');
        $employee_id = $this->input->get('emp');
        $month = $this->input->get('month');
        $year = $this->input->get('year');
        $emp_group = $this->input->get('emp_group');

        $current_user = get_user();

        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];

        if ($permissions_level == "Outlet") {
            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("overview/edited_time_logs?branch=$bid");
                return;
            }
        }

        $cid = $current_user["company_id"];

        $interval_minutes = get_interval_minutes($cid);

        $data["filters"] = $this->load->view('filters', $data, true);

        $this->db->select('COUNT(DISTINCT employees.id) total_records')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = employees.id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        if ($emp_group != "") {
            $this->db->where('egr.group_id', $emp_group);
        }
        $total_records = $this->db->where('roles.exclude_from_system', 'no')->order_by('employees.special_id', 'asc')->get()->row()->total_records;
        $limit = 30;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;

        $this->db->select('employees.id,employees.first_name as name,d.name as department,special_id,branch_id')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = employees.id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        if ($emp_group != "") {
            $this->db->where('egr.group_id', $emp_group);
        }
        $this->db->where('roles.exclude_from_system', 'no');
        $this->db->group_by('employees.id,employees.first_name,d.name,special_id');
        $employees = $this->db->order_by('special_id', 'asc')->limit($limit, $skip)->get()->result();

        $employees_ids = array('0');
        foreach ($employees as $emp) {
            $employees_ids[] = $emp->id;
        }

        $first_day = $data['start_date'];
        $last_day = $data['end_date'];

        $data["first_day"] = $data['formatted_date']['start_date']->format('d/m/Y');
        $data["last_day"] = $data['formatted_date']['end_date']->format('d/m/Y');

        $data['numberOfDays'] = $data['formatted_date']['end_date']->diff($data['formatted_date']['start_date'])->days + 1;

        $result_list = $this->db->select('cn.id,cn.employee_id,cn.type,shift_id,date_format(cn.datetime,"%H:%i") as clock_time,date_format(cn.datetime, "%Y-%m-%d") as search_date, clocking_remark, add_by_admin, update_by_admin, ec.type ec_type, date_format(ec.datetime,"%H:%i") as ec_clock_time,date_format(ec.datetime, "%Y-%m-%d") as ec_search_date', false)->from('clockings_news cn')->join('edited_clockings ec', 'ec.clocking_id = cn.id and ec.deleted_at is null', 'left')->where('date(cn.datetime) >=', $first_day)->where('date(cn.datetime) <=', $last_day)->where_in('cn.employee_id', $employees_ids)->where('cn.deleted_at is null')->order_by('cn.datetime')->get()->result();

        $result_list_overnight = $this->db->select('cn.id,cn.employee_id,cn.type,shift_id, date_format(cn.datetime,"%H:%i") as clock_time,date_format(date_sub(cn.datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, clocking_remark, add_by_admin, update_by_admin,  ec.type ec_type, date_format(ec.datetime,"%H:%i") as ec_clock_time,date_format(ec.datetime, "%Y-%m-%d") as ec_search_date', false)->from('clockings_news cn')->join('edited_clockings ec', 'ec.clocking_id = cn.id and ec.deleted_at is null', 'left')->where('date(date_sub(cn.datetime, interval ' . $interval_minutes . ' minute)) >=', $first_day)->where('date(date_sub(cn.datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('cn.employee_id', $employees_ids)->where('cn.deleted_at is null')->order_by('cn.datetime')->get()->result();

        $final_data = array();
        foreach ($employees as $emp) {
            $public_holidays = get_public_holidays_mine($emp->id, $emp->branch_id, $first_day, $last_day);
            $public_holidays_names = get_public_holidays_with_name($emp->branch_id, $first_day, $last_day)[1];

            $obj = new stdClass();
            $obj->employee = $emp;

            $shift_list = $this->db->select('code,date,overnight,color,end_time, coalesce(s.cut_off_time, c.cut_off_time, "07:00:00") as cut_off_time')->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->join('companies c', 'c.id = s.company_id')->where('FIND_IN_SET(' . $emp->id . ',employees)>', 0)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();

            $period = new DatePeriod(
                new DateTime($first_day),
                new DateInterval('P1D'),
                (new DateTime($last_day))->add(new DateInterval('P1D'))
            );
            $dates = array();
            $days = "<tr>";
            $last_ids = array();
            foreach ($period as $p) {
                $date = $p->format('Y-m-d');
                $day = $p->format('d');
                $day_name = $p->format('D');
                $clockings = array();
                $edited_clockings = array();
                $holiday_class = "";
                $holiday_name = "";
                if (in_array($date, $public_holidays)) {
                    $holiday_class = "holiday";
                    $holiday_name = $public_holidays_names[array_search($date, $public_holidays)];
                }
                $shift_code = "-";
                $shift = $this->search_from_list($shift_list, $date);
                $next_shift_check = $this->search_from_list($shift_list, date('Y-m-d', strtotime($date . ' +1 day')));
                if ($shift && $shift->overnight == "Yes") {
                    $result = $this->search_clocking($result_list_overnight, $date, $emp->id, true);
                    // if ($next_shift_check && $next_shift_check->overnight == "No") {
                    $result = remove_next_day_clockings_timelog($result, $shift, $next_shift_check);
                    // }
                } else {
                    $result = $this->search_clocking($result_list, $date, $emp->id);
                }
                $result = get_clockings_from_previous_day_timelog($result, $result_list_overnight, $date, $emp->id, $shift_list);

                $result = remove_last_ids($result, $last_ids);
                $last_ids = array();
                if ($shift) {
                    $shift_code = "<span style='color: " . $shift->color . ";'>(" . $shift->code . ")</span>";
                }
                $days .= "<td colspan='2' class='text-center'><strong><span class='$holiday_class' data-toggle='tooltip' data-html='true' data-placement='top' data-original-title='$holiday_name'>" . $day_name . "<br>" . $day . "</span><br>" . $shift_code . "</strong></td>";
                $i = 0;
                // echo '<pre>';
                // print_r($result);
                // echo '</pre>';die;
                foreach ($result as $key => $r) {
                    // print_r($r);die;
                    $last_ids[] = $r->id;
                    if ($i % 2 == 0 && $r->type == "out") {
                        $clockings[] = "<span></span>";
                        $edited_clockings[] = "<span class='text-danger' data-toggle='tooltip' title='Missing IN'>MI</span>";
                        $i++;
                    } elseif ($i % 2 == 1 && $r->type == "in") {
                        $clockings[] = "<span></span>";
                        $edited_clockings[] = "<span class='text-danger' data-toggle='tooltip' title='Missing OUT'>MO</span>";
                        $i++;
                    }
                    if ($r->shift_id == 0 || $r->shift_id == null) {
                        if (!empty($r->clocking_remark)) {
                            $clockings[] = "<span style='color: #0000ff;' data-html='true' data-toggle='tooltip' title='No Shift <br> " . $r->clocking_remark . "'>" . $r->clock_time . "</span>";
                            $edited_clockings[] = "<span></span>";
                        } else {
                            $clockings[] = "<span class='text-danger' data-html='true' data-toggle='tooltip' title='No Shift'>" . $r->clock_time . "</span>";
                            $edited_clockings[] = "<span></span>";
                        }
                    } else {
                        if (!empty($r->clocking_remark)) {
                            if ($r->add_by_admin == 1 || $r->update_by_admin == 1) {
                                $clockings[] = "<span style='color: #008000;font-weight:700;' data-toggle='tooltip' title='" . $r->clocking_remark . "'>" . $r->clock_time . "</span>";
                                $edited_clockings[] = "<span></span>";
                            } else {
                                $clockings[] = "<span style='color: #008000;' data-toggle='tooltip' title='" . $r->clocking_remark . "'>" . $r->clock_time . "</span>";
                                $edited_clockings[] = "<span></span>";
                            }
                        } else {
                            if ($r->add_by_admin == 1 || $r->update_by_admin == 1) {
                                $clockings[] = "<span style='font-weight:700'>" . $r->clock_time . "</span>";
                                $edited_clockings[] = "<span>$r->ec_clock_time</span>";
                            } else {
                                $clockings[] = "<span>" . $r->clock_time . "</span>";
                                $edited_clockings[] = "<span></span>";
                            }
                        }
                    }
                    $i++;
                    if ($key == count($result) - 1 && $r->type == "in") {
                        $clockings[] = "<span class='text-danger' data-toggle='tooltip' title='Missing OUT'>MO</span>";
                        $edited_clockings[] = "<span></span>";
                    }
                }
                $dates[] = [
                    'clockings' => implode("<br>", $clockings),
                    'edited_clockings' => implode("<br>", $edited_clockings),
                ];
            }
            $days .= "</tr>";

            $obj->days = $days;
            $obj->dates = $dates;

            $final_data[] = $obj;
        }

        // print_r($final_data);die;
        $data["final_data"] = $final_data;
        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        $data["skip"] = $skip;
        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET);
        $this->load->view('edited_time_logs', $data);
        $this->load->view('footer', $data);
    }

    public function edited_time_logs_pdf()
    {
        $branch_id = $this->input->get('branch');
        $department_id = $this->input->get('dep');
        $employee_id = $this->input->get('emp');
        $month = $this->input->get('month');
        $year = $this->input->get('year');
        $emp_group = $this->input->get('emp_group');

        $current_user = get_user();
        $data['current_user'] = $current_user;

        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];

        if ($permissions_level == "Outlet") {
            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("overview/edited_time_logs?branch=$bid");
                return;
            }
        }
        $data['pageTitle'] = "Edited Time Logs";
        $data['active_menu'] = "overview/edited_time_logs";
        $data['branch_name'] = '';

        $data["filters_form_action"] = "overview/edited_time_logs";
        render_all_filters($data);

        $cid = $current_user["company_id"];

        $interval_minutes = get_interval_minutes($cid);

        $this->db->select('COUNT(DISTINCT employees.id) total_records')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = employees.id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        if ($emp_group != "") {
            $this->db->where('egr.group_id', $emp_group);
        }
        $total_records = $this->db->where('roles.exclude_from_system', 'no')->order_by('employees.special_id', 'asc')->get()->row()->total_records;
        $limit = 30;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;

        $this->db->select('employees.id,employees.first_name as name,d.name as department,special_id,branch_id')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = employees.id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        if ($emp_group != "") {
            $this->db->where('egr.group_id', $emp_group);
        }
        $this->db->where('roles.exclude_from_system', 'no');
        $this->db->group_by('employees.id,employees.first_name,d.name,special_id');
        $employees = $this->db->order_by('special_id', 'asc')->limit($limit, $skip)->get()->result();

        $employees_ids = array('0');
        foreach ($employees as $emp) {
            $employees_ids[] = $emp->id;
        }

        if ($branch_id != "") {
            $branch_row = $this->db->select('name')->from('branches')->where('id', $branch_id)->get()->row();
            if ($branch_row) {
                $data['branch_name'] = $branch_row->name;
            }
        }


        $first_day = $data['start_date'];
        $last_day = $data['end_date'];

        $currentMonthDateObj = DateTime::createFromFormat('Y-m-d', $first_day);
        $data['month_f'] = $currentMonthDateObj->format('F');
        $data['year_f'] = $currentMonthDateObj->format('Y');

        $data["first_day"] = $data['formatted_date']['start_date']->format('d/m/Y');
        $data["last_day"] = $data['formatted_date']['end_date']->format('d/m/Y');

        $data['numberOfDays'] = $data['formatted_date']['end_date']->diff($data['formatted_date']['start_date'])->days + 1;

        $result_list = $this->db->select('cn.id,cn.employee_id,cn.type,shift_id,date_format(cn.datetime,"%H:%i") as clock_time,date_format(cn.datetime, "%Y-%m-%d") as search_date, clocking_remark, add_by_admin, update_by_admin, ec.type ec_type, date_format(ec.datetime,"%H:%i") as ec_clock_time,date_format(ec.datetime, "%Y-%m-%d") as ec_search_date', false)->from('clockings_news cn')->join('edited_clockings ec', 'ec.clocking_id = cn.id and ec.deleted_at is null', 'left')->where('date(cn.datetime) >=', $first_day)->where('date(cn.datetime) <=', $last_day)->where_in('cn.employee_id', $employees_ids)->where('cn.deleted_at is null')->order_by('cn.datetime')->get()->result();

        $result_list_overnight = $this->db->select('cn.id,cn.employee_id,cn.type,shift_id, date_format(cn.datetime,"%H:%i") as clock_time,date_format(date_sub(cn.datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, clocking_remark, add_by_admin, update_by_admin,  ec.type ec_type, date_format(ec.datetime,"%H:%i") as ec_clock_time,date_format(ec.datetime, "%Y-%m-%d") as ec_search_date', false)->from('clockings_news cn')->join('edited_clockings ec', 'ec.clocking_id = cn.id and ec.deleted_at is null', 'left')->where('date(date_sub(cn.datetime, interval ' . $interval_minutes . ' minute)) >=', $first_day)->where('date(date_sub(cn.datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('cn.employee_id', $employees_ids)->where('cn.deleted_at is null')->order_by('cn.datetime')->get()->result();

        $final_data = array();
        foreach ($employees as $emp) {
            $public_holidays = get_public_holidays_mine($emp->id, $emp->branch_id, $first_day, $last_day);
            $public_holidays_names = get_public_holidays_with_name($emp->branch_id, $first_day, $last_day)[1];

            $obj = new stdClass();
            $obj->employee = $emp;

            $shift_list = $this->db->select('code,date,overnight,color,end_time, coalesce(s.cut_off_time, c.cut_off_time, "07:00:00") as cut_off_time')->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->join('companies c', 'c.id = s.company_id')->where('FIND_IN_SET(' . $emp->id . ',employees)>', 0)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();

            $period = new DatePeriod(
                new DateTime($first_day),
                new DateInterval('P1D'),
                (new DateTime($last_day))->add(new DateInterval('P1D'))
            );
            $dates = array();
            $days = "<tr>";
            $last_ids = array();
            foreach ($period as $p) {
                $date = $p->format('Y-m-d');
                $day = $p->format('d');
                $day_name = $p->format('D');
                $edited_clockings = array();
                $holiday_class = "";
                $holiday_name = "";
                if (in_array($date, $public_holidays)) {
                    $holiday_class = "holiday";
                    $holiday_name = $public_holidays_names[array_search($date, $public_holidays)];
                }
                $shift_code = "-";
                $shift = $this->search_from_list($shift_list, $date);
                $next_shift_check = $this->search_from_list($shift_list, date('Y-m-d', strtotime($date . ' +1 day')));
                if ($shift && $shift->overnight == "Yes") {
                    $result = $this->search_clocking($result_list_overnight, $date, $emp->id, true);
                    // if ($next_shift_check && $next_shift_check->overnight == "No") {
                    $result = remove_next_day_clockings_timelog($result, $shift, $next_shift_check);
                    // }
                } else {
                    $result = $this->search_clocking($result_list, $date, $emp->id);
                }
                $result = get_clockings_from_previous_day_timelog($result, $result_list_overnight, $date, $emp->id, $shift_list);

                $result = remove_last_ids($result, $last_ids);
                $last_ids = array();
                if ($shift) {
                    $shift_code = "<span style='color: " . $shift->color . ";'>(" . $shift->code . ")</span>";
                }
                $days .= "<td class='text-center'><strong><span class='$holiday_class' data-toggle='tooltip' data-html='true' data-placement='top' data-original-title='$holiday_name'>" . $day_name . "<br>" . $day . "</span><br>" . $shift_code . "</strong></td>";
                $i = 0;
                // echo '<pre>';
                // print_r($result);
                // echo '</pre>';die;
                foreach ($result as $key => $r) {
                    // print_r($r);die;
                    $last_ids[] = $r->id;
                    if ($i % 2 == 0 && $r->type == "out") {
                        $edited_clockings[] = "<span class='text-danger' data-toggle='tooltip' title='Missing IN'>MI</span>";
                        $i++;
                    } elseif ($i % 2 == 1 && $r->type == "in") {
                        $edited_clockings[] = "<span class='text-danger' data-toggle='tooltip' title='Missing OUT'>MO</span>";
                        $i++;
                    }
                    if ($r->shift_id == 0 || $r->shift_id == null) {
                        if (!empty($r->clocking_remark)) {
                            $edited_clockings[] = "<span></span>";
                        } else {
                            $edited_clockings[] = "<span></span>";
                        }
                    } else {
                        if (!empty($r->clocking_remark)) {
                            if ($r->add_by_admin == 1 || $r->update_by_admin == 1) {
                                $edited_clockings[] = "<span></span>";
                            } else {
                                $edited_clockings[] = "<span></span>";
                            }
                        } else {
                            if ($r->add_by_admin == 1 || $r->update_by_admin == 1) {
                                $edited_clockings[] = "<span>$r->ec_clock_time</span>";
                            } else {
                                $edited_clockings[] = "<span></span>";
                            }
                        }
                    }
                    $i++;
                    if ($key == count($result) - 1 && $r->type == "in") {
                        $edited_clockings[] = "<span class='text-danger m-r-5' title='Missing OUT'>MO</span>";
                    }
                }
                $dates[] = implode("<br>", $edited_clockings);
            }
            $days .= "</tr>";

            $obj->days = $days;
            $obj->dates = $dates;

            $final_data[] = $obj;
        }

        // print_r($final_data);die;
        $data["final_data"] = $final_data;
        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        $data["skip"] = $skip;
        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET);
        $this->load->view('edited_time_logs_pdf', $data);

        $html = $this->output->get_output();
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper("A4", "landscape");
        $this->dompdf->render();
        $export_date = date('Y-m-d H:i:s');
        $this->dompdf->stream("edited-time-logs-$export_date");
        insert_log("Simple", ["action" => "Exported,edited time logs"]);
    }

    public function temperature_logs()
    {
        if (!is_page_permitted('temperature_logs')) {
            redirect_if_not_permitted();
        }

        $current_user = get_user();
        $data['pageTitle'] = "Temperature Logs";
        $data['active_menu'] = "overview/temperature_logs";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);

        $data["filters_form_action"] = "overview/temperature_logs";
        render_all_filters($data);

        $branch_id = $this->input->get('branch');
        $department_id = $this->input->get('dep');
        $employee_id = $this->input->get('emp');
        $month = $this->input->get('month');
        $year = $this->input->get('year');
        $emp_group = $this->input->get('emp_group');

        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];

        if ($permissions_level == "Outlet") {
            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("overview/temperature_logs?branch=$bid");
                return;
            }
        }

        $cid = $current_user["company_id"];

        $interval_minutes = get_interval_minutes($cid);

        $data["filters"] = $this->load->view('filters', $data, true);
        // To calculate the colspan of table columns
        $data['numberOfDays'] = $data['formatted_date']['end_date']->diff($data['formatted_date']['start_date'])->days + 1;

        $this->db->select('COUNT(DISTINCT employees.id) total_records')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->where('employees.company_id', $cid)->join('employee_groups_relation egr', 'egr.employee_id = employees.id', 'left')->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        if ($emp_group != "") {
            $this->db->where('egr.group_id', $emp_group);
        }
        $total_records = $this->db->where('roles.exclude_from_system', 'no')->order_by('employees.special_id', 'asc')->get()->row()->total_records;
        $limit = 30;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;

        $this->db->select('employees.id,employees.first_name as name,d.name as department,special_id')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = employees.id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        if ($emp_group != "") {
            $this->db->where('egr.group_id', $emp_group);
        }
        $this->db->where('roles.exclude_from_system', 'no');
        $this->db->group_by('employees.id,employees.first_name,d.name,special_id');
        $employees = $this->db->order_by('special_id', 'asc')->limit($limit, $skip)->get()->result();

        $employees_ids = array();
        foreach ($employees as $emp) {
            $employees_ids[] = $emp->id;
        }

        $first_day = $data['start_date'];
        $last_day = $data['end_date'];

        $data["first_day"] = $data['formatted_date']['start_date']->format('d/m/Y');
        $data["last_day"] = $data['formatted_date']['end_date']->format('d/m/Y');

        $result_list = $this->db->select('id,employee_id,type,temprature,shift_id,date_format(datetime,"%H:%i") as clock_time, datetime as clock_time_o, date_format(datetime, "%Y-%m-%d") as search_date', false)->from('clockings_news')->where('date(datetime) >=', $first_day)->where('date(datetime) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();

        $result_list_overnight = $this->db->select('id,employee_id,type,temprature,shift_id, date_format(datetime,"%H:%i") as clock_time, datetime as clock_time_o, date_format(date_sub(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date', false)->from('clockings_news')->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) >=', $first_day)->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();

        $final_data = array();
        foreach ($employees as $emp) {
            $obj = new stdClass();
            $obj->employee = $emp;

            $shift_list = $this->db->select('code,date,overnight,color,end_time, coalesce(s.cut_off_time, c.cut_off_time, "07:00:00") as cut_off_time')->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->join('companies c', 'c.id = s.company_id')->where('FIND_IN_SET(' . $emp->id . ',employees)>', 0)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();

            $period = new DatePeriod(
                new DateTime($first_day),
                new DateInterval('P1D'),
                (new DateTime($last_day))->add(new DateInterval('P1D'))
            );
            $dates = array();
            $days = "<tr>";
            $last_ids = array();
            foreach ($period as $p) {
                $date = $p->format('Y-m-d');
                $day = $p->format('d');
                $clockings = array();
                $shift_code = "-";
                $shift = $this->search_from_list($shift_list, $date);
                $next_shift_check = $this->search_from_list($shift_list, date('Y-m-d', strtotime($date . ' +1 day')));
                if ($shift && $shift->overnight == "Yes") {
                    $result = $this->search_clocking($result_list_overnight, $date, $emp->id, true);
                    // if ($next_shift_check && $next_shift_check->overnight == "No") {
                    $result = remove_next_day_clockings_timelog($result, $shift, $next_shift_check);
                    // }
                } else {
                    $result = $this->search_clocking($result_list, $date, $emp->id);
                }
                $result = get_clockings_from_previous_day_timelog($result, $result_list_overnight, $date, $emp->id, $shift_list);

                $result = remove_last_ids($result, $last_ids);
                $last_ids = array();
                if ($shift) {
                    $shift_code = "<span style='color: " . $shift->color . ";'>(" . $shift->code . ")</span>";
                }
                $days .= "<td class='text-center'><strong>" . $day . "<br>" . $shift_code . "</strong></td>";

                foreach ($result as $key => $r) {
                    $last_ids[] = $r->id;
                    if (is_null($r->temprature)) {
                        $clockings[] = "N/A";
                    } else {
                        if ($r->temprature >= "37.3") {
                            $clockings[] = "<span class='text-danger'>" . $r->temprature . "</span>";
                        } else {
                            $clockings[] = $r->temprature;
                        }
                    }
                }
                $dates[] = implode("<br>", $clockings);
            }
            $days .= "</tr>";

            $obj->days = $days;
            $obj->dates = $dates;

            $final_data[] = $obj;
        }
        $data["final_data"] = $final_data;
        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        $data["skip"] = $skip;
        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET);
        $this->load->view('temperature_logs', $data);
        $this->load->view('footer', $data);
    }

    public function temperature_logs_pdf()
    {
        $current_user = get_user();
        $branch_id = $this->input->get('branch');
        $department_id = $this->input->get('dep');
        $employee_id = $this->input->get('emp');
        $month = $this->input->get('month');
        $year = $this->input->get('year');
        $emp_group = $this->input->get('emp_group');


        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];
        $data = [];
        render_all_filters($data);

        if ($permissions_level == "Outlet") {
            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("overview/temperature_logs_pdf?branch=$bid");
                return;
            }
        }

        $data['pageTitle'] = "Temperature Logs PDF";
        $data['active_menu'] = "overview/temperature_logs_pdf";

        $data["filters_form_action"] = "overview/temperature_logs_pdf";

        $cid = $current_user["company_id"];

        $interval_minutes = get_interval_minutes($cid);

        $this->db->select('COUNT(DISTINCT employees.id) total_records')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = employees.id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        if ($emp_group != "") {
            $this->db->where('egr.group_id', $emp_group);
        }
        $total_records = $this->db->where('roles.exclude_from_system', 'no')->order_by('special_id', 'asc')->get()->row()->total_records;
        $limit = 30;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;

        $this->db->select('employees.id,employees.first_name as name,d.name as department,special_id')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->join('employee_groups_relation egr', 'egr.employee_id = employees.id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ');
        if ($branch_id != "") {
            $this->db->where('employees.branch_id', $branch_id);
        }
        if ($department_id != "") {
            $this->db->where('employees.department_id', $department_id);
        }
        if ($employee_id != "") {
            $this->db->where('employees.id', $employee_id);
        }
        if ($emp_group != "") {
            $this->db->where('egr.group_id', $emp_group);
        }
        $this->db->where('roles.exclude_from_system', 'no');
        $this->db->group_by('employees.id,employees.first_name,d.name,special_id');
        $employees = $this->db->order_by('special_id', 'asc')->limit($limit, $skip)->get()->result();

        $employees_ids = array();
        foreach ($employees as $emp) {
            $employees_ids[] = $emp->id;
        }


        $first_day = $data['start_date'];
        $last_day = $data['end_date'];

        $data["first_day"] = $data['formatted_date']['start_date']->format('d/m/Y');
        $data["last_day"] = $data['formatted_date']['end_date']->format('d/m/Y');

        $data['numberOfDays'] = $data['formatted_date']['start_date']->diff($data['formatted_date']['end_date'])->days + 1;

        $result_list = $this->db->select('id,employee_id,type, temprature, shift_id,date_format(datetime,"%H:%i") as clock_time, datetime as clock_time_o, date_format(datetime, "%Y-%m-%d") as search_date', false)->from('clockings_news')->where('date(datetime) >=', $first_day)->where('date(datetime) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();

        $result_list_overnight = $this->db->select('id,employee_id,type, temprature, shift_id, date_format(datetime,"%H:%i") as clock_time, datetime as clock_time_o, date_format(date_sub(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date', false)->from('clockings_news')->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) >=', $first_day)->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();

        $final_data = array();
        foreach ($employees as $emp) {
            $obj = new stdClass();
            $obj->employee = $emp;

            $shift_list = $this->db->select('code,date,overnight,color,end_time, coalesce(s.cut_off_time, c.cut_off_time, "07:00:00") as cut_off_time')->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->join('companies c', 'c.id = s.company_id')->where('FIND_IN_SET(' . $emp->id . ',employees)>', 0)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();

            $period = new DatePeriod(
                new DateTime($first_day),
                new DateInterval('P1D'),
                (new DateTime($last_day))->add(new DateInterval('P1D'))
            );
            $dates = array();
            $days = "<tr>";
            $last_ids = array();
            foreach ($period as $p) {
                $date = $p->format('Y-m-d');
                $day = $p->format('d');
                $clockings = array();
                $shift_code = "-";
                $shift = $this->search_from_list($shift_list, $date);
                $next_shift_check = $this->search_from_list($shift_list, date('Y-m-d', strtotime($date . ' +1 day')));
                if ($shift && $shift->overnight == "Yes") {
                    $result = $this->search_clocking($result_list_overnight, $date, $emp->id, true);
                    // if ($next_shift_check && $next_shift_check->overnight == "No") {
                    $result = remove_next_day_clockings_timelog($result, $shift, $next_shift_check);
                    // }
                } else {
                    $result = $this->search_clocking($result_list, $date, $emp->id);
                }
                $result = get_clockings_from_previous_day_timelog($result, $result_list_overnight, $date, $emp->id, $shift_list);

                $result = remove_last_ids($result, $last_ids);
                $last_ids = array();
                if ($shift) {
                    $shift_code = "<span style='color: " . $shift->color . ";'>(" . $shift->code . ")</span>";
                }
                $days .= "<td class='text-center'><strong>" . $day . "<br><span class='shift'>" . $shift_code . "</span></strong></td>";

                foreach ($result as $key => $r) {
                    $last_ids[] = $r->id;
                    if (is_null($r->temprature)) {
                        $clockings[] = "N/A";
                    } else {
                        if ($r->temprature >= "37.3") {
                            $clockings[] = "<span class='text-danger'>" . $r->temprature . "</span>";
                        } else {
                            $clockings[] = $r->temprature;
                        }
                    }
                }
                $dates[] = implode("<br>", $clockings);
            }
            $days .= "</tr>";

            $obj->days = $days;
            $obj->dates = $dates;

            $final_data[] = $obj;
        }
        $data["final_data"] = $final_data;
        $data["max_date"] = $max_date;
        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        $data["skip"] = $skip;
        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET);
        $this->load->view('temperature_logs_pdf', $data);

        $html = $this->output->get_output();
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper("A4", "landscape");
        $this->dompdf->render();
        $export_date = date('Y-m-d H:i:s');
        $this->dompdf->stream("temperature-logs-$export_date");
        insert_log("Simple", ["action" => "Exported,temperature logs"]);
    }

    public function att_report()
    {
        if (!is_page_permitted('att_report')) {
            redirect_if_not_permitted();
        }

        $branch = null;
        $branches = null;


        $branch_id = $this->input->get('branch');
        $status = $this->input->get('status');
        $date = $this->input->get('date');

        $data["date"] = $date = $date ? $date : date("d/m/Y");

        $status = $this->input->get('status');


        $cid = get_user()["company_id"];

        if ($branch_id) {
            $branch = $this->db->get_where('branches', array('id' => $branch_id))->row();
        }



        $bid = get_user()["branch_id"];
        $permissions_level = get_user()["permissions_level"];

        $where_branch_2 = '';


        if (isset($branch)) {
            $where_branch_2 = " AND employees.branch_id = $branch_id ";
        }

        // if($permissions_level == "Outlet"){

        //     $branches = $this->db->get_where('branches', array('id' => $bid))->result();

        //     if(empty($this->input->get("branch_id")) || $this->input->get("branch_id") != $bid){
        //         redirect("overview/att_report?branch=$bid");
        //         return;
        //     }

        // }

        $data['pageTitle'] = "Attendance Report";
        $data['active_menu'] = "overview/att_report";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);

        $data["filters_form_action"] = "overview/att_report";
        // echo "here";die;
        render_att_report_filters($data);

        $where_filter = $data["where_filter"];
        $where_date = $data["where_date"];
        $where_clock_date = $data["where_clock_date"];

        // $public_holidays = get_public_holidays();

        $cid = get_user()["company_id"];

        $date = DateTime::createFromFormat('d/m/Y', $date);

        $date = $date->format('Y-m-d');

        $this->db->select('employees.id,employees.first_name,special_id,employees.is_daily_waged, d.name as department, p.title as position,employees.branch_id,is_ot,is_early_ot,inc_late_in,inc_late_break, inc_early_out, inc_short_hours,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,void_lateness_time_if_less_than,deduct_from_ot,deduct_from_ot_single,deduction_date,min_worked_hours_meal,ta_rate,ma_rate,ca_rate,spa_rate,aca_rate,aa_rate,nsa_rate,fl_rate,cw_rate,mo_rate,shift1_rate,shift2_rate,shift3_rate,food_rate,basic_wage,b.name as branch')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('branches b', 'b.id = employees.branch_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null')->where('roles.exclude_from_system', 'no')
            ->where('employees.employee_status', 'active');
        if ($branch_id) {
            $this->db->where_in('employees.branch_id', $branch_id);
        }
        $this->db->order_by('special_id', 'asc');

        $employees = $this->db->get()->result();

        $employees_ids = array();
        foreach ($employees as $emp) {
            $employees_ids[] = $emp->id;
        }

        $result_list = get_result_list($employees_ids, $date, $date);
        $result_list_overnight = get_result_list_overnight($employees_ids, $date, $date);
        $all_data = array();
        foreach ($employees as $emp) {
            $emp_data = calculate_summary_data($emp->id, $date, $date, "summary", $emp, $result_list, $result_list_overnight);
            // echo '';
            ob_flush();
            flush();
            $all_data[] = $emp_data;
            $emp_data = array();
            $dates = array();
        }

        $formatted_data = array();
        foreach ($all_data as $row) {
            $temp = new stdClass();
            $temp->first_name = $row["employee"]->first_name;
            $temp->special_id = $row["employee"]->special_id;
            $temp->branch = $row["employee"]->branch;
            $temp->department = $row["employee"]->department;
            $date_data = $row["dates"][0];
            $temp->shift_name = $date_data->shift_name;
            $temp->early_out = $date_data->early_out;
            $temp->late_in = $date_data->late_hours;
            $temp->late_break = $date_data->break_late_hours;
            $temp->first_in = $date_data->first_in_o;
            $temp->last_out = $date_data->last_out_o;
            $formatted_data[] = $temp;
        }

        $result = array();
        if ($status == "late") {
            foreach ($formatted_data as $row_data) {
                if ($row_data->late_in != "" && $row_data->late_in != "00:00") {
                    $result[] = $row_data;
                }
            }
        } elseif ($status == "late_break") {
            foreach ($formatted_data as $row_data) {
                if ($row_data->late_break != "" && $row_data->late_break != "00:00") {
                    $result[] = $row_data;
                }
            }
        } elseif ($status == "early_out") {
            foreach ($formatted_data as $row_data) {
                if ($row_data->early_out != "" && $row_data->early_out != "00:00") {
                    $result[] = $row_data;
                }
            }
        }

        $data["result"] = $result;


        $data["filters"] = $this->load->view('filters_att', $data, true);

        $this->load->view('att_report', $data);
        $this->load->view('footer', $data);
    }

    public function IndexOLD()
    {
        $branch = null;
        $branches = null;


        $branch_id = $this->input->get('branch_id');
        $cid = get_user()["company_id"];

        if ($branch_id) {
            $branch = $this->db->get_where('branches', array('id' => $branch_id))->row();
        }



        $bid = get_user()["branch_id"];
        $permissions_level = get_user()["permissions_level"];

        $where_branch_1 = '';
        $where_branch_2 = '';
        $where_branch_3 = '';
        $is_branch = false;


        if (isset($branch)) {
            $where_branch_1 = " AND e.branch_id = $branch_id ";
            $where_branch_2 = " AND employees.branch_id = $branch_id ";
            $where_branch_3 = " AND branch_id = $branch_id ";
            $is_branch = true;
        }


        //if($cid != 1){
        $branches = $this->db->get_where('branches', array('company_id' => $cid))->result();
        // }
        // else{
        //     $branches = $this->db->get('branches')->result();
        // }

        if ($permissions_level == "Outlet") {
            $branches = $this->db->get_where('branches', array('id' => $bid))->result();

            //$where_branch_1 = " AND e.branch_id = $bid ";
            //$where_branch_2 = " AND id = $bid ";
            //$where_branch_3 = " AND permissions_level = 'Personal' ";

            if (empty($this->input->get("branch_id")) || $this->input->get("branch_id") != $bid) {
                redirect("overview?branch_id=$bid");
                return;
            }
        }

        $data['pageTitle'] = "Dashboard Overview";
        $data['active_menu'] = "overview";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);

        //Boxes and graphs start here------------------------------

        $boxes = array();




        $join_roles = array();
        $join_roles['table'] = 'roles';
        $join_roles['on'] = 'employees.role_id = roles.id';

        $t_employees = $this->db->select('COALESCE(count(e.id), 0) as total', false)->from('employees e')->join('roles r', 'e.role_id = r.id')->where("e.company_id = $cid $where_branch_1 ")->where('exclude_from_system', 'no')->where('e.deleted_at is null')->where('employee_status', 'active')->get()->row()->total;



        $data["new_employees"] = $this->db->select('COALESCE(count(e.id), 0) as total', false)->from('employees e')->join('roles r', 'e.role_id = r.id')->where("e.company_id = $cid $where_branch_1 ")->where('exclude_from_system', 'no')->where('e.deleted_at is null')->where('employee_status', 'active')->where('e.created_at >= DATE(NOW()) - INTERVAL 7 DAY')->get()->row()->total;


        // $data["invalid_clocking_distance"] = $this->db->select('COALESCE(count(e.id), 0) as total',false)->from('clockings_news e')->where("e.company_id = $cid $where_branch_1 ")->where("e.scan_distance > 30")->where('e.deleted_at IS null')->get()->row()->total;
        $data["invalid_clocking_distance"] = $this->db->select('COALESCE(count(c.id), 0) as total', false)->from('clockings_news c')->join('devices e', 'e.device_id = c.device_id')->join('branches b', 'b.id = e.branch_id')->where("e.company_id = $cid $where_branch_1 ")->where("c.scan_distance > b.invalid_clocking_distance")->where("c.datetime > ", date('Y-m-d 00:00:00'))->get()->row()->total;

        $data["resignation_employees"] = $this->db->select('COALESCE(count(e.id), 0) as total', false)->from('employees e')->join('roles r', 'e.role_id = r.id')->where("e.company_id = $cid $where_branch_1 ")->where('exclude_from_system', 'no')->where('e.deleted_at is null')->where('employee_status', 'resigned')->get()->row()->total;




        //die($this->db->last_query());

        $data["terminated_employees"] = $this->db->select('COALESCE(count(e.id), 0) as total', false)->from('employees e')->join('roles r', 'e.role_id = r.id')->where("e.company_id = $cid $where_branch_1 ")->where('exclude_from_system', 'no')->where('e.deleted_at is null')->where('employee_status', 'terminated')->get()->row()->total;



        $ex_employees = $data["resignation_employees"] + $data["terminated_employees"];

        if ($ex_employees == 0 || $t_employees == 0) {
            $data["turnover"] = 0;
        } else {
            $data["turnover"] = round(($ex_employees / $t_employees) * 100, 2);
        }




        ///////////////////////////////




        //===================

        if (isset($branch)) {
            $boxes[] = stats_box("Employees in <b>" . $branch->name . "</b>", "employees", "COUNT(1)", "3", array('employees.company_id' => $cid, 'employees.branch_id' => $branch->id, 'roles.exclude_from_system' => 'no', 'employees.employee_status' => 'active', 'employees.deleted_at is null' => null), $join_roles);
        } else {
            $boxes[] = stats_box("Employees", "employees", "COUNT(1)", "3", array('employees.company_id' => $cid, 'roles.exclude_from_system' => 'no', 'employees.employee_status' => 'active', 'employees.deleted_at is null' => null), $join_roles);
        }
        $data["boxes"] = $boxes;



        $data["branch"] = $branch;
        $data["branches"] = $branches;




        $this->db->select('companies.package, companies.additional_staff, packages.max_outlets, packages.max_active_staff');
        $this->db->join('packages', 'packages.id = companies.package', 'left');
        $this->db->where('companies.id', $cid);
        $company_details = $this->db->get('companies')->row();
        $data['company_max_active_staff'] = $company_details->max_active_staff + $company_details->additional_staff;
        $data['company_max_outlets'] = $company_details->max_outlets;
        $this->db->join('roles', 'roles.id = employees.role_id', 'left');
        $this->db->where('employees.company_id', $cid);
        $this->db->where('employees.employee_status', 'active');
        $this->db->where('roles.exclude_from_system', 'no');
        $this->db->where('employees.deleted_at is null');
        $employees_of_company = $this->db->get('employees')->result();
        $data['employees_of_company'] = count($employees_of_company);
        $license_days_remaining = '';
        $license = get_license_status_simple($cid);
        // Create badge HTML
        $license_days_remaining = '<span style="padding:5px;" class="badge bg-' . $license['class'] . '">' . $license['label'] . '</span>';
        $data['license_days_remaining'] = $license_days_remaining;
        $this->db->where('company_id', $cid);
        $this->db->where('deleted_at is null');
        $company_branches = $this->db->get('branches')->result();
        $data['company_outlets'] = count($company_branches);
        $data['outlets_of_company'] = count($employees_of_company);
        $data['announcements'] = $this->db->select('*')->from('old_announcements')->where('active', 1)->order_by('id', 'desc')->get()->result();
        if (is_page_permitted('overview')) {
            $this->load->view('overview_view', $data);
        } else {
            //$this->load->view('not_permitted');
            //redirect("welcome");

            //print_r(get_menus());

            if (count(get_menus()) == 0) {
                $this->load->view('not_permitted');
            } else {
                foreach (get_menus() as $menu) {
                    //var_dump(is_null($menu["sub_menus"]));

                    if (is_null($menu["sub_menus"])) {
                        redirect($menu['url']);
                        return;
                    } else {
                        redirect(reset($menu["sub_menus"])['url']);
                        return;
                    }

                    return;
                }
            }
        }

        $this->load->view('footer', $data);
    }
    // ==============================================================
    //  index()  — Replace your existing index() with this
    // ==============================================================
    public function index()
    {
        $current_user = get_user();
        $cid = $current_user['company_id'];
        $bid = $current_user['branch_id'];
        $permissions = $current_user['permissions_level'];
        $branch_id = $this->input->get('branch_id');

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
            'pageTitle' => 'Dashboard Overview',
            'active_menu' => 'overview',
            'branches' => $branches,
            'branch_id' => (int) $branch_id,
            'api_base' => base_url('overview'),
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
    function getHours($time)
    {
        $time = explode(":", $time);
        return round($time[0] + ($time[1] / 60), 2);
    }

    function count_overtime($id, $date)
    {

        $date_obj = DateTime::createFromFormat('Y-m-d', $date);

        $date_f = $date_obj->format('d-m-Y');

        $result = $this->db->select('c.id,date_format(clock_in, "%d %b %Y, %a") as day_f, date_format(clock_in,"%H:%i") as clock_in, date_format(clock_in,"%d-%m-%Y %H:%i") as clock_in_1,date_format(clock_out,"%H:%i") as clock_out,date_format(clock_out,"%d-%m-%Y %H:%i") as clock_out_1,date_format(overtime_starts,"%H:%i") as overtime_starts,is_ot, fixed_ot, fixed_overtime', false)->from('clockings c')->join('shifts s', 'c.shift_id = s.id', 'left')->join('ot_days od', 'od.employee_id = c.employee_id and od.ot_date = date(clock_in)', 'left')->where('date(clock_in)', $date)->where('c.employee_id', $id)->get()->result();




        $overtime = "";

        $formatted_data = array();

        $obj = new stdClass();



        if ($result) {
            $v = $result[0];
            // if is_ot is "N"
            if ($v->is_ot == null or $v->is_ot == "N") {
                return "00:00";
            }
            if ($v->fixed_ot == 'Y') {
                $formatted_ot = $v->fixed_overtime;
                if ($formatted_ot == "00:00:00") {
                    $formatted_ot = "";
                } else {
                    $formatted_ot = explode(":", $formatted_ot);
                    unset($formatted_ot[2]);
                    $formatted_ot = implode(":", $formatted_ot);
                }
                $overtime = $formatted_ot;
            } else {
                foreach ($result as $key => $value) {
                    $formatted_data[] = $value;

                    if (array_key_exists($key + 1, $result)) {
                        $x = new stdClass();

                        $x->overtime_starts = $value->overtime_starts;

                        $x->clock_in = $value->clock_out;

                        $x->clock_in_1 = $value->clock_out_1;

                        $x->clock_out = $result[$key + 1]->clock_in;

                        $x->clock_out_1 = $result[$key + 1]->clock_in_1;

                        $x->name = "Break";

                        $formatted_data[] = $x;
                    }
                }

                foreach ($formatted_data as $clock) {
                    $overtime = $this->overtime2($overtime, $clock->clock_in_1, $clock->clock_out_1, $clock->overtime_starts, $date_f);
                }
            }
        }

        $overtime = (empty($overtime)) ? "00:00" : $overtime;

        return $overtime;
    }

    public function overtime2($overtime, $clock_in_1, $clock_out_1, $overtime_starts, $date)
    {

        if (empty($clock_in_1) || empty($clock_out_1) || $overtime_starts == "") {
            return "";
        }

        $overtime_starts = $date . " " . $overtime_starts;

        $overtime_starts = DateTime::createFromFormat('d-m-Y H:i', $overtime_starts);

        $clock_in = DateTime::createFromFormat('d-m-Y H:i', $clock_in_1);

        $clock_out = DateTime::createFromFormat('d-m-Y H:i', $clock_out_1);



        if ($clock_in > $overtime_starts) {
            $interval = $this->total_time($clock_in_1, $clock_out_1);

            $overtime = $this->add_time($overtime, $interval);
        } elseif ($clock_out > $overtime_starts) {
            $interval = $this->total_time(date_format($overtime_starts, "d-m-Y H:i"), $clock_out_1);

            $overtime = $this->add_time($overtime, $interval);
        }



        return $overtime;
    }

    public function total_time($a, $b)
    {

        if ($a == null || $b == null) {
            return "00:00";
        }

        $time1 = DateTime::createFromFormat('d-m-Y H:i', $a);

        $time2 = DateTime::createFromFormat('d-m-Y H:i', $b);

        $interval = date_diff($time1, $time2);

        $days = $interval->format('%a');

        $format = $interval->format('%H:%i');

        $format = explode(":", $format);

        $format[0] = $format[0] + ($days * 24);

        $format[0] = sprintf("%02d", $format[0]);

        $format[1] = sprintf("%02d", $format[1]);

        $format = implode(":", $format);

        return $format;
    }

    public function add_time($time1, $time2)
    {

        if ($time2 == null || $time2 == "" || $time2 == "00:00") {
            return $time1;
        }

        if (empty($time1)) {
            $time1 = "00:00";
        }

        $time1 = explode(":", $time1);

        $time2 = explode(":", $time2);

        $hours = $time1[0] + $time2[0];

        $minutes = $time1[1] + $time2[1];

        if ($minutes >= 60) {
            $minutes -= 60;

            $hours = $hours + 1;
        }

        $hours = sprintf("%02d", $hours);

        $minutes = sprintf("%02d", $minutes);

        return $hours . ":" . $minutes;
    }


    public function add_manual_clocking_new()
    {

        $data['pageTitle'] = "Add Clocking";
        $data['active_menu'] = "overview/add_manual_clocking_new";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);
        //naveed


        $this->load->view('add_manual_clocking_new', $data);

        $this->load->view('footer', $data);
    }

    public function manual_clocking_new()
    {
        if (!is_page_permitted('manual_clocking_new')) {
            redirect_if_not_permitted();
        }

        $data['pageTitle'] = "View / Edit Clocking";
        $data['active_menu'] = "overview/manual_clocking_new";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);

        $data["selected_branch_id"] = 0;
        $data["selected_emp_id"] = 0;
        $data["selected_month"] = 0;
        $data["selected_year"] = 0;
        $data["selected_distance"] = "all";

        // Default date range = current month (used for redirects and pre-filling the picker)
        $default_start_date = date('d/m/Y', strtotime('first day of this month'));
        $default_end_date   = date('d/m/Y', strtotime('last day of this month'));
        $default_daterange  = $default_start_date . ' - ' . $default_end_date;

        $current_user = get_user();

        $cid = $current_user["company_id"];

        $where_filter = "";
        $branch_where_filter = "";
        $where_clock_date = "";
        $where_date = "";

        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];
        $limit_access_to_department = $current_user["limit_access_to_department"];
        $department_id = $current_user["department_id"];
        $data["is_emp_summary_editable"] = $current_user["is_emp_summary_editable"] === "yes" ? true : false;

        $where_branch_2 = '';
        $where_department = '';

        if ($permissions_level == "Outlet") {
            $where_branch_2 = " AND id = $bid ";

            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("overview/manual_clocking_new?branch=$bid&daterange_filter=" . urlencode($default_daterange));
                return;
            }
        }

        if ($limit_access_to_department == "yes") {
            $allowed_departments = get_allowed_departments($current_user);
            $where_department = " AND employees.department_id in ($allowed_departments) ";
        }

        if (!empty($this->input->get("branch"))) {
            $data["selected_branch_id"] = $this->input->get("branch");
            $where_filter = $where_filter . " employees.branch_id = " . $this->input->get("branch") . " AND ";
            $branch_where_filter = $branch_where_filter . " AND employees.branch_id = " . $this->input->get("branch");
        }

        if (!empty($this->input->get("emp"))) {
            $data["selected_emp_id"] = $this->input->get("emp");
            $where_filter = $where_filter . " employee_id = " . $this->input->get("emp") . " AND ";
        }

        if (!empty($this->input->get("pos"))) {
            $data["selected_pos_id"] = $this->input->get("pos");
            $where_filter = $where_filter . " position_id = " . $this->input->get("pos") . " AND ";
        }
        if (!empty($this->input->get("mode"))) {
            $data["selected_mode"] = $this->input->get("mode");
            $where_filter = $where_filter . " clockings_news.mode = '" . $this->input->get("mode") . "' AND ";
        }

        if (!empty($this->input->get("dev"))) {
            $data["selected_dev_id"] = $this->input->get("dev");
            $where_filter = $where_filter . " clockings_news.device_id = " . $this->input->get("dev") . " AND ";
        }

        if (!empty($this->input->get("scan_distance"))) {
            $data["selected_distance"] = $this->input->get("scan_distance");

            if ($data["selected_distance"] == "invalid") {
                $where_filter = $where_filter . " scan_distance > branches.invalid_clocking_distance AND ";
            }
        }

        if (!empty($this->input->get("daterange_filter"))) {
            $daterange_raw = $this->input->get("daterange_filter");
            $daterange_parts = array_map('trim', explode(' - ', $daterange_raw));

            $start_date_obj = DateTime::createFromFormat('d/m/Y', $daterange_parts[0]);
            $end_date_obj   = DateTime::createFromFormat('d/m/Y', isset($daterange_parts[1]) ? $daterange_parts[1] : $daterange_parts[0]);

            // Fallback to current month if the date range could not be parsed
            if (!$start_date_obj || !$end_date_obj) {
                $start_date_obj = new DateTime('first day of this month');
                $end_date_obj   = new DateTime('last day of this month');
            }

            // Make sure start is not after end
            if ($start_date_obj > $end_date_obj) {
                $tmp = $start_date_obj;
                $start_date_obj = $end_date_obj;
                $end_date_obj = $tmp;
            }

            // Enforce single calendar month only (mirrors the frontend restriction,
            // and protects against someone editing the URL/query params directly)
            if ($start_date_obj->format('Y-m') !== $end_date_obj->format('Y-m')) {
                $end_date_obj = (clone $start_date_obj)->modify('last day of this month');
            }

            $start_date_sql = $start_date_obj->format('Y-m-d');
            $end_date_sql   = $end_date_obj->format('Y-m-d');

            $data["start_date"] = $start_date_sql;
            $data["end_date"] = $end_date_sql;
            $data["selected_daterange"] = $start_date_obj->format('d/m/Y') . ' - ' . $end_date_obj->format('d/m/Y');

            // Kept for backward compatibility with other links on this page (employee_report, summary/view)
            // that still filter by a single month/year.
            $data["selected_month"] = $start_date_obj->format('m');
            $data["selected_year"] = $start_date_obj->format('Y');

            $where_date = " AND datetime BETWEEN '" . $start_date_sql . " 00:00:00' AND '" . $end_date_sql . " 23:59:59'";
        } else {
            redirect("overview/manual_clocking_new?daterange_filter=" . urlencode($default_daterange));
            return;
        }

        $where_filter = $where_filter . " employees.company_id = " . $cid;
        $where_filter = trim($where_filter);
        $where_filter = trim($where_filter, "AND");

        $total_records = $this->db->query("SELECT COUNT(1) as total_records FROM clockings_news INNER JOIN employees ON clockings_news.employee_id = employees.id INNER JOIN roles ON employees.role_id = roles.id INNER JOIN branches ON employees.branch_id = branches.id WHERE roles.exclude_from_system = 'no' AND clockings_news.deleted_at IS NULL AND $where_filter $where_date")->row()->total_records;

        $limit = 100;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;

        $result = $this->db->query("SELECT clockings_news.*, shifts.name as shift_name, devices.mac_address, devices.location, employees.first_name, employees.last_name, employees.special_id,employees.branch_id,branches.name as branch_name, branches1.name as branch_name_clocking FROM clockings_news INNER JOIN employees ON clockings_news.employee_id = employees.id INNER JOIN roles ON employees.role_id = roles.id INNER JOIN branches ON employees.branch_id = branches.id LEFT JOIN devices ON clockings_news.device_id = devices.device_id LEFT JOIN shifts ON clockings_news.shift_id = shifts.id LEFT JOIN branches branches1 on branches1.id = devices.branch_id  WHERE roles.exclude_from_system = 'no' AND clockings_news.deleted_at IS NULL AND $where_filter $where_date $where_department ORDER BY clockings_news.datetime DESC LIMIT $skip,$limit")->result_array();

        $s3 = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION', ''),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID', ''),
                'secret' => env('AWS_SECRET_ACCESS_KEY', ''),
            ],
        ]);

        $bucket = env('AWS_BUCKET', '');

        foreach ($result as &$row) {
            if (!empty($row['selfie'])) {
                try {
                    $url = $row['selfie'];

                    if (strpos($url, 'amazonaws.com') !== false) {
                        $parsed = parse_url($url);
                        $key = ltrim($parsed['path'], '/');
                    } else {
                        $key = $url;
                    }

                    $cmd = $s3->getCommand('GetObject', [
                        'Bucket' => $bucket,
                        'Key' => $key,
                    ]);

                    $request = $s3->createPresignedRequest($cmd, '+5 minutes');
                    $row['selfie_url'] = (string) $request->getUri();
                } catch (AwsException $e) {
                    $row['selfie_url'] = null;
                }
            } else {
                $row['selfie_url'] = null;
            }
        }

        $data["clockings"] = $result;

        $data["employees_dropdown"] = $this->db->query("SELECT employees.id, special_id,first_name,last_name FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND roles.exclude_from_system = 'no' AND employees.company_id = $cid $branch_where_filter ORDER BY special_id")->result();

        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        $data["skip"] = $skip;
        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET);

        $data["positions"] = $this->db->query("SELECT id,title as name FROM positions WHERE company_id = $cid ORDER BY name")->result();
        $data["sections"] = $this->db->query("SELECT id,title as name FROM sections WHERE company_id = $cid ORDER BY name")->result();
        $data["devices"] = $this->db->query("SELECT device_id, mac_address FROM devices WHERE company_id = $cid AND mac_address IS NOT NULL ORDER BY mac_address")->result();
        $data["branches"] = $this->db->query("SELECT * FROM branches WHERE company_id = $cid  $where_branch_2 ORDER BY name")->result();
        $data["departments"] = $this->db->query("SELECT * FROM departments WHERE company_id = $cid ORDER BY name")->result();
        $data["shifts"] = $this->db->query("SELECT * FROM shifts WHERE company_id = $cid ORDER BY is_leave DESC,name ASC")->result();
        $this->load->view('manual_clocking_new', $data);
        $this->load->view('footer', $data);
    }

    public function refresh_address_ajax()
    {
        $id = $this->input->post('id');
        $latlon = $this->input->post('latlon');

        // Parse the lat/lon string
        $latlon_array = array_map('trim', explode(",", (string) $latlon));
        $lat = isset($latlon_array[0]) ? $latlon_array[0] : null;
        $lon = isset($latlon_array[1]) ? $latlon_array[1] : null;

        if (!$lat || !$lon || $lat == 0) {
            echo json_encode(['success' => false, 'message' => 'No valid coordinates available.']);
            return;
        }

        // Call your existing private function
        $address = $this->getAddress($lat, $lon);

        if ($address) {
            // Save to Database so it stays fixed
            $this->db->where('id', $id);
            $this->db->update('clockings_news', ['address' => $address]); // Ensure 'clockings' matches your table name

            echo json_encode(['success' => true, 'address' => $address]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Service error: Could not fetch address at this time.']);
        }
    }
    private function getAddress($lat, $lon)
    {
        // Nominatim requires a User-Agent
        $url = "https://nominatim.openstreetmap.org/reverse.php?lat={$lat}&lon={$lon}&zoom=18&format=jsonv2";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'invotime/1.0'); // Nominatim requires a User-Agent
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept-Language: en"
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200 || !$response) {
            return null;
        }

        $data = json_decode($response, true);
        if (isset($data['display_name'])) {
            return $data['display_name'];
        }

        return null;
    }


    public function branch_report()
    {
        $data['pageTitle'] = "Branch Report";
        $data['active_menu'] = "overview/branch_report";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);

        $cid = get_user()["company_id"];
        $data["selected_dep_id"] = 0;
        $data["selected_month"] = 0;
        $where_filter = "";


        $bid = get_user()["branch_id"];
        $permissions_level = get_user()["permissions_level"];

        //$where_branch_1 = '';
        $where_branch_2 = '';
        //$where_branch_3 = '';


        if ($permissions_level == "Outlet") {
            //$where_branch_1 = " AND branch_id = $bid ";
            $where_branch_2 = " AND id = $bid ";
            //$where_branch_3 = " AND permissions_level = 'Personal' ";

            // if(empty($this->input->get("branch")) || $this->input->get("branch") != $bid){
            //     redirect("overview/shifts_calendar?branch=$bid&month=".date('m'));
            //     return;
            // }
        }


        if (!empty($this->input->get("dep"))) {
            $data["selected_dep_id"] = $this->input->get("dep");
            $where_filter = $where_filter . " department_id = " . $this->input->get("dep") . " AND ";
        }

        if (!empty($this->input->get("month"))) {
            $data["selected_month"] = $this->input->get("month");
            $where_clock_date = " AND MONTH(clock_in) = " . $this->input->get("month");
            $where_date = " AND MONTH(date) = " . $this->input->get("month");
        } else {
            redirect("overview/branch_report?month=" . date('m'));
            return;
        }

        $dateComponents = getdate();
        //$month = $dateComponents['mon'];
        $year = $dateComponents['year'];

        $data["branches"] = $this->db->query("SELECT * FROM branches WHERE company_id = $cid $where_branch_2 ORDER BY name")->result();

        //naveed

        // echo "SELECT GROUP_CONCAT(employees) as all_employees FROM shift_days WHERE YEAR(date) = 2019 AND MONTH(date) = ".$data["selected_month"]." AND $where_filter employees <> ''";
        //     die();

        foreach ($data["branches"] as $branch) {
            $all_employees = $this->db->query("SELECT GROUP_CONCAT(employees) as all_employees FROM shift_days WHERE YEAR(date) = 2019 AND MONTH(date) = " . $data["selected_month"] . " AND employees <> '' ")->row()->all_employees;

            $all_employees = trim($all_employees, ',');
            //die($all_employees);

            // var_dump($all_employees);
            // die();

            $total_employees = $this->db->query("SELECT COUNT(id) as total_employees FROM employees WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND branch_id = " . $branch->id . " AND deleted_at is NULL")->row()->total_employees;


            $branch->total_employees = $total_employees;


            $branch_id = $branch->id;

            if (!empty($all_employees)) {
                $all_employees_array = explode(",", $all_employees);

                $emps_to_remove = $this->db->query("SELECT GROUP_CONCAT(id) as employees_to_remove FROM employees WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND id NOT IN($all_employees) AND $where_filter branch_id = $branch_id")->row()->employees_to_remove;

                $emps_to_remove_array = explode(",", $emps_to_remove);

                //var_dump($emps_to_remove_array);
                // var_dump($all_employees_array);

                $all_employees_array = array_diff($all_employees_array, $emps_to_remove_array);

                $branch->shifts = count($all_employees_array);
                $branch->absent = 0;
                $branch->on_leave = 0;
                //$branch->total_employees = count($all_employees_array) + count($emps_to_remove_array);
            } else {
                $branch->shifts = 0;
                $branch->absent = 0;
                $branch->on_leave = 0;
                //$branch->total_employees = 0;
            }
        }

        //var_dump($data["branches"]);

        //die();


        $data["departments"] = $this->db->query("SELECT * FROM departments WHERE company_id = $cid ORDER BY name")->result();


        $this->load->view('branch_report', $data);
        $this->load->view('footer', $data);
    }


    public function attendance_report()
    {
        $data['pageTitle'] = "Attendance Overview";
        $data['active_menu'] = "overview/branch_report";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);

        $cid = get_user()["company_id"];

        $data["selected_branch_id"] = 0;
        $data["selected_dep_id"] = 0;
        $data["selected_month"] = 0;


        $where_filter = "";
        $where_clock_date = "";
        $where_date = "";


        if (!empty($this->input->get("branch"))) {
            $data["selected_branch_id"] = $this->input->get("branch");
            $where_filter = $where_filter . " branch_id = " . $this->input->get("branch") . " AND ";
        }
        if (!empty($this->input->get("dep"))) {
            $data["selected_dep_id"] = $this->input->get("dep");
            $where_filter = $where_filter . " department_id = " . $this->input->get("dep") . " AND ";
        }

        if (!empty($this->input->get("month"))) {
            $data["selected_month"] = $this->input->get("month");
            $where_clock_date = " AND MONTH(clock_in) = " . $this->input->get("month");
            $where_date = " AND MONTH(date) = " . $this->input->get("month");
        } else {
            redirect("overview/attendance_report?month=" . date('m'));
            return;
        }

        $where_filter = $where_filter . " employees.deleted_at IS NULL AND ";

        $where_filter = $where_filter . " company_id = " . $cid;

        $where_filter = trim($where_filter);
        $where_filter = trim($where_filter, "AND");


        if (!empty($where_filter)) {
            $where_filter = " WHERE " . $where_filter;
        }



        $employees = $this->db->query("SELECT id,special_id,first_name,last_name FROM employees $where_filter ORDER BY first_name")->result();


        foreach ($employees as $emp) {
            $emp_id = $emp->id;

            $hours_row = $this->db->query("SELECT IFNULL(SUM(TIME_FORMAT(TIMEDIFF(a.`clock_out`, a.`clock_in` ),'%H')), 0) AS hours FROM `clockings` a WHERE a.employee_id=$emp_id AND clock_out IS NOT NULL " . $where_clock_date)->row();


            $leaves_row = $this->db->query("SELECT  count(1) as leaves FROM shift_days WHERE shift_days.date < CURDATE() AND NOT EXISTS(SELECT null FROM clockings WHERE DATE(clockings.clock_in) = shift_days.date AND clockings.employee_id = $emp_id $where_clock_date) AND FIND_IN_SET($emp_id,shift_days.employees) $where_date")->row();

            //die($this->db->last_query());


            $early_row = $this->db->query("SELECT clockings.id,clockings.employee_id,MIN(clock_in) as clock_in,clock_out,grace_time,start_time,end_time FROM clockings
INNER JOIN shift_days ON DATE(clockings.clock_in) = shift_days.date
INNER JOIN shifts ON clockings.shift_id = shifts.id
WHERE FIND_IN_SET($emp_id,shift_days.employees) AND clockings.employee_id = $emp_id AND shift_days.shift_id=clockings.shift_id
GROUP BY DATE(clock_in) HAVING DATE_FORMAT(clock_in,'%H:%i') < DATE_FORMAT(start_time,'%H:%i') $where_clock_date");

            // echo $this->db->last_query();
            // die();

            $late_row = $this->db->query("SELECT clockings.id,clockings.employee_id,MIN(clock_in) as clock_in,clock_out,grace_time,start_time,end_time FROM clockings
INNER JOIN shift_days ON DATE(clockings.clock_in) = shift_days.date
INNER JOIN shifts ON clockings.shift_id = shifts.id
WHERE FIND_IN_SET($emp_id,shift_days.employees) AND clockings.employee_id = $emp_id AND shift_days.shift_id=clockings.shift_id GROUP BY DATE(clock_in)
HAVING DATE_FORMAT(clock_in,'%H:%i') > DATE_FORMAT(grace_time,'%H:%i') $where_clock_date");






            // echo "SELECT * FROM clockings
            // INNER JOIN shift_days ON clockings.clock_date = shift_days.date
            // INNER JOIN shifts ON clockings.shift_id = shifts.id
            // WHERE FIND_IN_SET($emp_id,shift_days.employees) AND clockings.employee_id = $emp_id AND shift_days.shift_id=clockings.shift_id
            // GROUP BY clock_date HAVING clockings.clock_in > shifts.grace_time $where_clock_date";

            //  die();


            $emp->hours = $hours_row->hours;
            $emp->leaves = $leaves_row->leaves;
            $emp->early = $early_row->num_rows();
            $emp->late = $late_row->num_rows();
        }

        $data["employees"] = $employees;



        $data["branches"] = $this->db->query("SELECT * FROM branches WHERE company_id = $cid ORDER BY name")->result();
        $data["departments"] = $this->db->query("SELECT * FROM departments WHERE company_id = $cid ORDER BY name")->result();




        $this->load->view('attendance_report', $data);
        $this->load->view('footer', $data);
    }

    public function employee_report($emp_id = 0)
    {
        if (!is_page_permitted('employee_report')) {
            redirect_if_not_permitted();
        }

        $cid = get_user()["company_id"];
        $bid = get_user()["branch_id"];
        $permissions_level = get_user()["permissions_level"];

        $where_branch_2 = '';
        if ($permissions_level == "Outlet") {
            $where_branch_2 = " AND branch_id = $bid ";
        }

        $data["employees_dropdown"] = $this->db->query("SELECT employees.id, special_id, first_name, last_name FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE employees.deleted_at IS NULL AND roles.exclude_from_system = 'no' AND employees.company_id = $cid $where_branch_2
        AND (employees.employee_status = 'active'
            OR (employees.employee_status = 'terminated' AND employees.termination_date IS NOT NULL AND employees.termination_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
            OR(employees.employee_status = 'resigned' AND employees.resignation_date IS NOT NULL AND employees.resignation_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
        )
        ORDER BY special_id")->result();


        $emp = $this->db->query("SELECT * FROM employees WHERE employees.deleted_at IS NULL AND employees.company_id = $cid $where_branch_2 AND
        (employee_status = 'active'
            OR (employee_status = 'terminated' AND termination_date IS NOT NULL AND termination_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
            OR(employee_status = 'resigned' AND resignation_date IS NOT NULL AND resignation_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
        )
        AND id = $emp_id")->row();

        if (!$emp) {
            redirect('overview/employee_report/' . $data["employees_dropdown"][0]->id);
            die();
        }


        $data['pageTitle'] = $emp->special_id . " - " . $emp->first_name;
        $data['active_menu'] = "overview/employee_report";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);

        $data["selected_month"] = 0;
        $data["selected_year"] = 0;
        $where_clock_date = "";
        $where_date = "";


        $limit_access_to_department = get_user()["limit_access_to_department"];
        $department_id = get_user()["department_id"];

        $selected_emp_id = $emp_id;

        if (!empty($this->input->get("emp"))) {
            redirect("overview/employee_report/" . $this->input->get("emp") . "?month=" . $this->input->get("month") . "&year=" . $this->input->get("year"));
            return;
        }

        if (!empty($this->input->get("month")) && !empty($this->input->get("year"))) {
            $data["selected_month"] = $this->input->get("month");
            $data["selected_year"] = $this->input->get("year");
            $where_clock_date = " AND MONTH(clock_in) = " . $this->input->get("month") . " AND YEAR(clock_in) = " . $this->input->get("year");
            $where_date = " AND MONTH(date) = " . $this->input->get("month") . " AND YEAR(date) = " . $this->input->get("year");;
        } else {
            redirect("overview/employee_report/" . $emp_id . "?month=" . date('m') . "&year=" . date('Y'));
            return;
        }

        $render_clockings_query_for_employee_month = render_clockings_query_for_employee_month($emp_id, $this->input->get("month"), $this->input->get("year"));

        // A preshift clock-in only belongs to the PREVIOUS calendar day's shift
        // when it happened late at night (>= noon, e.g. 23:50). A preshift
        // clock-in just after midnight (e.g. 01:19) already belongs to its own
        // calendar date and needs no shift. This mirrors the same noon cutoff
        // used by preshift_normalized_minutes() for the late/early label.
        $preshift_date_match = "(CASE WHEN shifts.is_preshift = 'Yes' AND TIME(clockings.clock_in) >= '12:00:00' THEN DATE(clockings.clock_in) = DATE_SUB(shift_days.date, INTERVAL 1 DAY) ELSE DATE(clockings.clock_in) = shift_days.date END)";
        $shift_days = $this->db->query("SELECT (SELECT MIN(id) FROM $render_clockings_query_for_employee_month as clockings where $preshift_date_match AND clockings.employee_id=$emp_id) as id,

        (SELECT reason FROM $render_clockings_query_for_employee_month as clockings where $preshift_date_match AND clockings.employee_id=$emp_id LIMIT 1) as reason,

        r.remark as remark,

        (SELECT GROUP_CONCAT(DISTINCT s2.name SEPARATOR ', ') FROM $render_clockings_query_for_employee_month as clockings INNER JOIN shifts s2 ON clockings.shift_id = s2.id where $preshift_date_match AND clockings.employee_id=$emp_id LIMIT 1) as shifts,

        shift_days.shift_id,shifts.name, date, employees,

    (SELECT MIN(clock_in) FROM $render_clockings_query_for_employee_month as clockings where $preshift_date_match AND clockings.employee_id=$emp_id) as clock_in,

    (SELECT MAX(clock_out) FROM $render_clockings_query_for_employee_month as clockings where $preshift_date_match AND clockings.employee_id=$emp_id) as clock_out,

    (SELECT auto_clock_out FROM $render_clockings_query_for_employee_month as clockings where $preshift_date_match AND clockings.employee_id=$emp_id ORDER BY id DESC LIMIT 1) as auto_clock_out,

   (SELECT IFNULL(SUM(TIME_FORMAT(TIMEDIFF(a.`clock_out`, a.`clock_in` ),'%H')), 0) AS hours FROM $render_clockings_query_for_employee_month as  a WHERE a.employee_id=$emp_id AND clock_out IS NOT NULL AND (CASE WHEN shifts.is_preshift = 'Yes' AND TIME(a.clock_in) >= '12:00:00' THEN DATE(a.clock_in) = DATE_SUB(shift_days.date, INTERVAL 1 DAY) ELSE DATE(a.clock_in) = shift_days.date END)) as hours,

    (SELECT IFNULL(SUM(TIME_FORMAT(TIMEDIFF(a.`clock_out`, a.`clock_in` ),'%i')), 0) AS hours FROM $render_clockings_query_for_employee_month as  a WHERE a.employee_id=$emp_id AND clock_out IS NOT NULL AND (CASE WHEN shifts.is_preshift = 'Yes' AND TIME(a.clock_in) >= '12:00:00' THEN DATE(a.clock_in) = DATE_SUB(shift_days.date, INTERVAL 1 DAY) ELSE DATE(a.clock_in) = shift_days.date END)) as minutes,
    TIME_FORMAT(TIMEDIFF((SELECT MAX(clock_out) FROM $render_clockings_query_for_employee_month as clockings where $preshift_date_match AND clockings.employee_id=$emp_id), (SELECT Min(clock_in) FROM clockings where (CASE WHEN shifts.is_preshift = 'Yes' AND TIME(clockings.clock_in) >= '12:00:00' THEN DATE(clockings.clock_in) = DATE_SUB(shift_days.date, INTERVAL 1 DAY) ELSE DATE(clockings.clock_in) = shift_days.date END) AND clockings.employee_id=$emp_id)),'%H:%i') as total_time,

    (SELECT grace_time FROM shifts where id = shift_days.shift_id) as shift_grace_time,

    (SELECT end_time FROM shifts where id = shift_days.shift_id) as shift_end_time,

    (SELECT start_time FROM shifts where id = shift_days.shift_id) as shift_start_time,

    (SELECT is_leave FROM shifts where id = shift_days.shift_id) as shift_is_leave,
    (SELECT is_paid FROM shifts where id = shift_days.shift_id) as shift_is_paid,
    (SELECT color FROM shifts where id = shift_days.shift_id) as shift_color,
    (SELECT is_preshift FROM shifts where id = shift_days.shift_id) as shift_is_preshift

    FROM shift_days INNER JOIN shifts ON shift_days.shift_id = shifts.id LEFT JOIN remarks r ON r.remark_date = shift_days.date AND r.employee_id = $emp_id WHERE FIND_IN_SET($emp_id,shift_days.employees) $where_date ORDER BY shift_days.date")->result();

        $data["shift_days"] = $shift_days;

        $data["emp"] = $emp;
        $data["selected_emp_id"] = $selected_emp_id;

        $this->load->view('employee_report', $data);
        $this->load->view('footer', $data);
    }

    public function save_reason()
    {
        $id = $this->input->get('id');
        $reason = $this->input->get('reason');

        $data = array(
            'reason' => $reason
        );

        $this->db->where('id', $id);
        echo $this->db->update('clockings_news', $data);

        $this->db->set("reason", $reason)->where("clock_in_id", $id)->or_where("clock_out_id", $id)->update("new_clockings");

        $clocking = $this->db->select("DATE(datetime) as date, employee_id")->from('clockings_news')
            ->where("id", $id)->get()->row();


        $date = $clocking->date;

        $emp = $this->db->select('id, first_name')->from('employees')->where('id', $clocking->employee_id)->get()->row();

        insert_log("Clocking Late Reason", [
            "action" => "Edited,Clocking Late Reason",
            "target_id" => $emp->id,
            "target_name" => $emp->first_name,
            "for_date" => $date,
        ]);
    }

    public function save_remark()
    {
        $id = $this->input->get('id');
        $remark = $this->input->get('remark');
        $date = $this->input->get('date');

        $data = array(
            'employee_id' => $id,
            'remark' => $remark,
            'remark_date' => $date
        );

        echo $this->db->replace('remarks', $data);

        $this->db->set("remark", $remark)->where("employee_id", $id)->where("date(clock_in)", $date)->update("new_clockings");

        $emp_name = $this->db->select('first_name')->from('employees')->where('id', $id)->get()->row()->first_name;
        insert_log("Clocking Remarks", [
            "action" => "Edited,Clocking Remarks",
            "target_id" => $id,
            "target_name" => $emp_name,
            "for_date" => $date,
        ]);
    }

    public function save_staff_remark()
    {
        $employee_id = $this->input->get('id');
        $remark = $this->input->get('remark');
        $date = $this->input->get('date');

        $data = array(
            'employee_id' => $employee_id,
            'remark' => $remark,
            'remark_date' => $date,
        );

        // Insert or update staff remark
        // echo $this->db->replace('staff_remarks', $data);

        // Check if remark already exists for employee + date
        $exists = $this->db->get_where('staff_remarks', [
            'employee_id' => $employee_id,
            'remark_date' => $date
        ])->row();

        if ($exists) {
            if ($remark == "") {
                // Delete existing remark if new remark is empty
                echo $this->db->where('employee_id', $employee_id)
                    ->where('remark_date', $date)
                    ->delete('staff_remarks');
            } else {
                // Update existing remark
                echo $this->db->where('employee_id', $employee_id)
                    ->where('remark_date', $date)
                    ->update('staff_remarks', ['remark' => $remark]);
            }
        } else {
            // Insert new remark
            echo $this->db->insert('staff_remarks', $data);
        }

        // (Optional) If you also want to reflect staff_remark in new_clockings table:
        $this->db->set("staff_remark", $remark)
            ->where("employee_id", $employee_id)
            ->where("date(clock_in)", $date)
            ->update("new_clockings");

        // Log action
        $emp_name = $this->db->select('first_name')
            ->from('employees')
            ->where('id', $employee_id)
            ->get()
            ->row()
            ->first_name;

        insert_log("Staff Remarks", [
            "action" => "Edited, Staff Remarks",
            "target_id" => $employee_id,
            "target_name" => $emp_name,
            "for_date" => $date,
        ]);
    }

    public function delete_assignment()
    {

        // $temp_sql_x = $this->db->query("SELECT * FROM shift_days WHERE shift_id = 18 AND FIND_IN_SET(883,employees) AND date = '2019-03-03'");

        // var_dump($temp_sql_x->row());


        $dataa = explode(',', $this->input->post('data'));

        //var_dump($dataa);
        $response_records = array();


        foreach ($dataa as $d) {
            $d_exploded = explode('|', $d);

            $employee_id = $d_exploded[0];
            $date = $d_exploded[1];
            $shift_id = $d_exploded[2];



            $shift_day = $this->db->query("SELECT * FROM shift_days WHERE shift_id = $shift_id AND FIND_IN_SET($employee_id,employees) AND date = '$date'")->row();

            //var_dump($this->db->last_query());

            $employees = explode(",", $shift_day->employees);

            $employees = array_diff($employees, array($employee_id));

            $remove_data = array(
                'employees' => trim(implode(",", $employees), ",")
            );

            //var_dump(empty(trim(implode(",",$employees),",")));
            //die();


            //var_dump($remove_data);

            $this->db->where('id', $shift_day->id);

            if (!empty(trim(implode(",", $employees), ","))) {
                $this->db->update('shift_days', $remove_data);
            } else {
                $this->db->delete('shift_days');
            }

            //echo "done";

            $this->db->set("shift_id", 0)->where("employee_id", $employee_id)->where("date(datetime)", $date)->update("clockings_news");
            $this->db->set("shift_id", 0)->where("employee_id", $employee_id)->where("date(clock_in)", $date)->update("new_clockings");

            $data = array(
                'shift_id' => $shift_id,
                'date' => $date,
                'employee_id' => $employee_id
            );

            $response_records[] = $data;
        }

        echo json_encode($response_records);
    }


    public function save_clocking()
    {

        $current_user = get_user();
        $is_emp_summary_editable = $current_user["is_emp_summary_editable"] == "yes";

        if (!$is_emp_summary_editable) {
            echo json_encode(array("success" => false, "message" => "You are not allowed to edit employee summary"));
            return;
        }

        $clocking_id = $this->input->post('clocking_id');
        $clocking_type = $this->input->post('clocking_type');
        $clocking_datetime = $this->input->post('clocking_datetime');

        $response = array();

        $response["success"] = true;
        $response["clocking_id"] = $clocking_id;
        $response["clocking_type"] = $clocking_type;
        $response["clocking_datetime"] = $clocking_datetime;

        //naveed

        $data = array(
            'type' => $clocking_type,
            'datetime' => $clocking_datetime,
            'update_by_admin' => 1
        );

        $this->db->where('id', $clocking_id);
        $this->db->update('clockings_news', $data);

        $clocking = $this->db->select("datetime, employee_id")->from('clockings_news')->where("id", $clocking_id)->get()->row();

        update_new_clockings($clocking->employee_id, $clocking->datetime);

        echo json_encode($response);
    }

    public function delete_clocking()
    {

        $clocking_id = $this->input->post('clocking_id');

        $response = array();

        $response["success"] = true;
        $response["clocking_id"] = $clocking_id;


        $data = array(
            'deleted_at' => date("Y-m-d H:i:s"),
            'delete_by_admin' => 1
        );

        $this->db->where('id', $clocking_id);
        $this->db->update('clockings_news', $data);

        $clocking = $this->db->select("datetime, employee_id")->from('clockings_news')->where("id", $clocking_id)->get()->row();

        update_new_clockings($clocking->employee_id, $clocking->datetime);

        echo json_encode($response);
    }


    public function save_assignment()
    {

        // $temp_sql_x = $this->db->query("SELECT * FROM shift_days WHERE FIND_IN_SET(883,employees) AND shift_days.date = '2019-03-03'");

        // var_dump($temp_sql_x->row());
        // die();
        $company_id = get_user()["company_id"];
        $interval_minutes = get_interval_minutes($company_id);


        $dataa = explode(',', $this->input->post('data'));

        $response_records = array();

        //var_dump($dataa);



        foreach ($dataa as $d) {
            $d_exploded = explode('|', $d);
            $employee_id = $d_exploded[0];
            $date = $d_exploded[1];
            $shift_id = $d_exploded[2];
            $remark = "";
            if (isset($d_exploded[3])) {
                $remark = trim($d_exploded[3]);

                if ($remark) {
                    $remark_data = array(
                        'employee_id' => $employee_id,
                        'remark_date' => $date,
                        'remark' => $remark
                    );
                    $this->db->replace('remarks', $remark_data);

                    $this->db->set("remark", $remark)->where("employee_id", $employee_id)->where("date(clock_in)", $date)->update("new_clockings");
                }
            }

            $remark_data = $this->db->select('remark')->from('remarks')->where('employee_id', $employee_id)->where('remark_date', $date)->get()->row();
            if ($remark_data) {
                $remark = $remark_data->remark;
            }




            // var_dump($employee_id);
            // var_dump($date);
            // var_dump($shift_id);

            // $shift_id = $this->input->get('shift_id');
            // $date = $this->input->get('date');
            // $employee_id = $this->input->get('employee_id');

            $data = array(
                'shift_id' => $shift_id,
                'date' => $date,
                'employee_id' => $employee_id
            );

            $shift_day = $this->db->query("SELECT * FROM shift_days WHERE shift_id = $shift_id AND date = '$date'")->row();
            $shift = $this->db->query("SELECT id,name,color,code,overnight FROM shifts WHERE id = $shift_id")->row();

            //var_dump($data);

            if ($shift_day) {
                //var_dump($shift_day);
                $employees_new = explode(",", $shift_day->employees);

                $shift_day_prev = $this->db->query("SELECT * FROM shift_days WHERE date = '$date' AND FIND_IN_SET($employee_id,employees)")->row();

                $employees = array();

                if ($shift_day_prev) {
                    $employees = explode(",", $shift_day_prev->employees);
                }

                $employees = array_diff($employees, array($employee_id));
                $employees_new = array_diff($employees_new, array($employee_id));


                if ($shift_day_prev) {
                    $remove_data = array(
                        'employees' => trim(implode(",", $employees), ",")
                    );
                    $this->db->where('id', $shift_day_prev->id);
                    $this->db->update('shift_days', $remove_data);
                }


                array_push($employees_new, $employee_id);

                $update_data = array(
                    'employees' => trim(implode(",", $employees_new), ",")
                );



                $this->db->where('id', $shift_day->id);
                $this->db->update('shift_days', $update_data);
            } else {
                $shift_day_prev = $this->db->query("SELECT * FROM shift_days WHERE date = '$date' AND FIND_IN_SET($employee_id,employees)")->row();

                $employees = array();

                if ($shift_day_prev) {
                    $employees = explode(",", $shift_day_prev->employees);
                }

                $employees = array_diff($employees, array($employee_id));


                if ($shift_day_prev) {
                    $remove_data = array(
                        'employees' => trim(implode(",", $employees), ",")
                    );

                    $this->db->where('id', $shift_day_prev->id);
                    $this->db->update('shift_days', $remove_data);
                }

                $insert_data = array(
                    'shift_id' => $shift_id,
                    'date' => $date,
                    'employees' => $employee_id
                );

                $this->db->insert('shift_days', $insert_data);
            }

            //var_dump($shift_day);

            if ($shift->overnight == "Yes") {
                $update_shift_id_in_clockings = $this->db->query("UPDATE clockings_news SET shift_id = $shift_id WHERE date(date_sub(datetime, interval " . $interval_minutes . " minute)) = '$date' AND employee_id = $employee_id");
                $this->db->query("UPDATE new_clockings SET shift_id = $shift_id WHERE employee_id = $employee_id AND date(date_sub(clock_in, interval " . $interval_minutes . " minute)) = '$date'");
            } else {
                $update_shift_id_in_clockings = $this->db->query("UPDATE clockings_news SET shift_id = $shift_id WHERE DATE(datetime) = '$date' AND employee_id = $employee_id");
                $this->db->query("UPDATE new_clockings SET shift_id = $shift_id WHERE employee_id = $employee_id AND DATE(clock_in) = '$date'");
            }



            //var_dump($update_shift_id_in_clockings);


            //print_r($data);
            $data["name"] = $shift->name;
            $data["color"] = $shift->color;
            $data["code"] = $shift->code;
            $data["remark"] = $remark;
            $response_records[] = $data;
        }

        echo json_encode($response_records);

        //$this->db->where('id', $id);
        //echo $this->db->update('clockings', $data);
    }

    public function clocking_details_modal()
    {

        $date = $this->input->get('date');
        $emp_id = $this->input->get('emp_id');

        // var_dump($date);
        // var_dump($emp_id);

        //$clocking_data = array();

        $clocking_data = $this->db->query("SELECT clockings.*, shifts.name as shift_name FROM clockings LEFT JOIN shifts ON clockings.shift_id = shifts.id WHERE DATE(clockings.clock_in) = '$date' AND clockings.employee_id = $emp_id")->result();

        // echo $this->db->last_query();
        // die();

        //var_dump($clocking_data);
        //die();
        //header('Content-Type: application/json');

        //echo json_encode($clocking_data);
        $data["clockings"] = $clocking_data;
        $data["date"] = $date;
        $string = $this->load->view('clocking_details_modal', $data, true);

        echo $string;
    }

    public function attendance_sheet()
    {
        if (!is_page_permitted('attendance_sheet')) {
            redirect_if_not_permitted();
        }

        $data['pageTitle'] = "Attendance Sheet";
        $data['active_menu'] = "overview/attendance_sheet";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);

        $data["filters_form_action"] = "overview/attendance_sheet";
        render_all_filters($data);

        $where_filter = $data["where_filter"];
        $where_date = $data["where_date"];
        $where_clock_date = $data["where_clock_date"];
        $current_user = get_user();
        $cid = $current_user["company_id"];

        $days_settings = $this->db->select('from_hour,to_hour,days')->from('days_settings')->where('company_id', $cid)->get()->result();


        $total_records = $this->db->query("SELECT COUNT(DISTINCT employees.id) as total_records FROM employees INNER JOIN roles ON employees.role_id = roles.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = employees.id WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND roles.exclude_from_system = 'no' AND $where_filter")->row()->total_records;
        $limit = 20;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;


        $result = $this->db->query("SELECT employees.id,special_id, first_name FROM employees INNER JOIN roles ON employees.role_id = roles.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = employees.id WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND roles.exclude_from_system = 'no' AND  $where_filter
            GROUP BY employees.id,special_id, first_name ORDER BY special_id LIMIT $skip,$limit")->result_array();

        $employees_ids = array();
        foreach ($result as $r) {
            $employees_ids[] = $r["id"];
        }

        if (empty($employees_ids)) {
            $employees_ids = array(0);
        }

        $first_day = $data['start_date'];
        $last_day = $data['end_date'];

        $clockings_list = $this->db->select('id, employee_id, date(datetime) as search_date')->from('clockings_news')->where('clockings_news.deleted_at', null)->where_in('employee_id', $employees_ids)->where("datetime between '$first_day 00:00:00' AND '$last_day 23:59:59'")->get()->result();
        $result_list = get_result_list_basic($employees_ids, $first_day, $last_day);
        $result_list_overnight = get_result_list_overnight_basic($employees_ids, $first_day, $last_day);
        $result_list_preshift = get_result_list_preshift_basic($employees_ids, $first_day, $last_day);

        $prev_day = date('Y-m-d', strtotime('-1 day', strtotime($first_day)));
        $shift_data_array = $this->db->select('s.id, name, overnight, s.is_preshift, date, date as shift_date, employees, half_day, grace_time as shift_grace_time, start_time as shift_start_time, end_time as shift_end_time, is_leave as shift_is_leave, is_paid, is_paid as shift_is_paid')->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->where('date >=', $prev_day)->where('date <=', $last_day)->where('company_id', $cid)->get()->result();

        $dateComponents = getdate();
        //$month = $dateComponents['mon'];
        $year = $data["selected_year"];

        $comma_separated_emp_ids = implode(",", $employees_ids);
        $interval_minutes = get_interval_minutes($cid);

        $employees_min_clockings = $this->db->query("select employee_id, date(clockings.datetime) as clocking_date, MIN(clockings.datetime) as clock_in FROM clockings_news as clockings where date(clockings.datetime) between '$first_day' and '$last_day' and clockings.employee_id in ($comma_separated_emp_ids) AND type = 'in' AND clockings.deleted_at is null group by employee_id, date(clockings.datetime) ORDER BY date(clockings.datetime)")->result_array();

        // Preshift-adjusted version: shifts a clock-in made just before midnight forward onto
        // the next calendar day, so it lines up with the shift's own (next-day) date.
        $employees_min_clockings_preshift = $this->db->query("select employee_id, date(date_add(clockings.datetime, interval $interval_minutes minute)) as clocking_date, MIN(clockings.datetime) as clock_in FROM clockings_news as clockings where date(date_add(clockings.datetime, interval $interval_minutes minute)) between '$first_day' and '$last_day' and clockings.employee_id in ($comma_separated_emp_ids) AND type = 'in' AND clockings.deleted_at is null group by employee_id, date(date_add(clockings.datetime, interval $interval_minutes minute)) ORDER BY date(date_add(clockings.datetime, interval $interval_minutes minute))")->result_array();

        $manual_late_list = $this->db->select('employee_id, date, late_hours')->from('manual_late')->where('date >=', $first_day)->where('date <=', $last_day)->where_in('employee_id', $employees_ids)->get()->result();
        $data["period_of_dates"] = new DatePeriod(
            new DateTime($first_day),
            new DateInterval('P1D'),
            (new DateTime($last_day))->add(new DateInterval('P1D'))
        );

        //echo '<pre>';
        foreach ($result as &$row) {
            //$result2 = $this->db->query("SELECT shift_id,date as shift_date FROM shift_days WHERE FIND_IN_SET(".$row["id"].",shift_days.employees)")->result_array();

            $emp_id = $row["id"];

            // $shift_data_list = get_shift_list_basic($emp_id, $first_day, $last_day);
            $shift_data_list = make_shift_list_basic($shift_data_array, $emp_id);

            // $clockings_table = render_clockings_query_for_employee_month($emp_id,$this->input->get("month"),$this->input->get("year"));

            $result2_normal = get_current_employee_clockings($employees_min_clockings, $emp_id);
            $result2_preshift = get_current_employee_clockings($employees_min_clockings_preshift, $emp_id);
            // echo $this->db->last_query();die;

            $result2 = merge_result_with_shifts_preshift_aware($result2_normal, $result2_preshift, $shift_data_list);
            // echo $this->db->last_query();
            // die();

            //print_r($result2);




            // $max_date = cal_days_in_month(CAL_GREGORIAN, $data["selected_month"], $year);

            foreach ($data["period_of_dates"] as $value) {
                $dd = $value->format('Y-m-d');
                $row[$dd] = array("applicable" => "false", "presence" => "-", "status" => "-");
            }

            foreach ($result2 as &$row2) {
                $presence = "-";
                $status = "-";
                $tooltip = "";
                $icon_class = "far";

                //var_dump($row2);
                //die();

                $explode_employees = explode(',', $row2["employees"]);

                if ($row2["clock_in"] == null) {
                    //$presence = "times";
                    //$status = "absent";

                    if (in_array($emp_id, $explode_employees)) {
                        if ($row2["shift_is_leave"] == "no") {
                            $presence = "calendar-times";
                            $status = "absent";
                            $tooltip = "Absent<br/> Shift: " . $row2["name"];
                        }
                        if (empty($row2['shift_start_time']) || empty($row2['shift_end_time'])) {
                            $presence = "-";
                            $status = "-";
                            $tooltip = "";
                        }
                        if ($row2["shift_is_leave"] == "yes") {
                            if ($row2["shift_is_paid"] == "yes") {
                                $presence = "calendar-plus";
                                $status = "leave";
                                $tooltip = "Paid Leave<br/> Shift: " . $row2["name"];
                            } else {
                                $presence = "calendar-minus";
                                $status = "leave";
                                $tooltip = "Unpaid Leave<br/> Shift: " . $row2["name"];
                            }
                        }
                    }
                } else {
                    $presence = "calendar-check";
                    $is_preshift_shift = false;
                    if ($row2['shift_date']) {
                        $shift_data = search_from_list($shift_data_list, $row2['shift_date']);

                        if ($shift_data && $shift_data->overnight == "Yes") {
                            $current_clockings = search_clocking_by_id($result_list_overnight, $row2['shift_date'], $emp_id);
                        } elseif ($shift_data && isset($shift_data->is_preshift) && $shift_data->is_preshift == "Yes") {
                            $current_clockings = search_clocking_by_id($result_list_preshift, $row2['shift_date'], $emp_id);
                            $is_preshift_shift = true;
                        } else {
                            $current_clockings = search_clocking_by_id($result_list, $row2['shift_date'], $emp_id);
                            if (!$shift_data) {
                                $prev_day = date('Y-m-d', strtotime('-1 day', strtotime($row2['shift_date'])));
                                $prev_clockings = search_clocking_by_id($result_list_overnight, $prev_day, $emp_id);
                                $current_clockings = remove_duplicate_clockings($current_clockings, $row2['shift_date'], $shift_data_list, $prev_clockings);
                            }
                        }

                        $clockings_list = $this->removeClockings($clockings_list, $current_clockings);

                        $days = calculate_days_from_clockings($current_clockings, $days_settings);

                        if ($days == 0.5 && $shift_data->half_day != "Yes") {
                            $presence = "clock-o";
                        }
                        if ($shift_data) {
                            if ($shift_data->half_day == "Yes" && $shift_data->is_paid == "yes") {
                                $presence = "calendar-day";
                                $icon_class = "half-day-paid fa";
                            } elseif ($shift_data->half_day == "Yes" && $shift_data->is_paid == "no") {
                                $presence = "calendar-day";
                                $icon_class = "half-day-unpaid fa";
                            }
                        }
                    }



                    // $manual_late = $this->db->select('late_hours')->from('manual_late')->where('employee_id', $emp_id)->where('date', $row2['shift_date'])->get()->row();
                    $manual_late = search_late_from_list($manual_late_list, $row2['shift_date'], $emp_id);

                    if ($manual_late) {
                        $manual_clock_in = $this->add_time(beautify_time($row2['shift_grace_time']), $manual_late->late_hours);


                        if ($manual_clock_in < beautify_time($row2["shift_start_time"])) {
                            $status = "early";
                            $tooltip = "Early <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];;
                        }

                        $grace_time_for_late = !empty($row2["shift_grace_time"]) ? $row2["shift_grace_time"] : $row2["shift_start_time"];
                        if ($manual_clock_in > beautify_time($grace_time_for_late)) {
                            $status = "late";
                            $tooltip = "Late <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];
                        }

                        if (($manual_clock_in >= beautify_time($row2["shift_start_time"])) && ($manual_clock_in <= beautify_time($row2["shift_grace_time"]))) {
                            $status = "ontime";
                            $tooltip = "Ontime <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];
                        }
                    } elseif ($is_preshift_shift) {
                        // Preshift clock-ins can land the night before (e.g. 23:50) for a shift
                        // that starts just after midnight (e.g. 00:10). Plain "H:i" string
                        // comparison would wrongly read 23:50 as later-in-the-day than 00:10,
                        // so compare using minutes normalized around the midnight boundary.
                        $clock_in_minutes = preshift_normalized_minutes(beautify_time($row2["clock_in"]));
                        $shift_start_minutes = preshift_normalized_minutes(beautify_time($row2["shift_start_time"]));

                        $grace_time_for_late = !empty($row2["shift_grace_time"]) ? $row2["shift_grace_time"] : $row2["shift_start_time"];
                        $grace_minutes = preshift_normalized_minutes(beautify_time($grace_time_for_late));
                        $shift_grace_minutes = preshift_normalized_minutes(beautify_time($row2["shift_grace_time"]));

                        if ($clock_in_minutes < $shift_start_minutes) {
                            $status = "early";
                            $tooltip = "Early <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];
                        }

                        if ($clock_in_minutes > $grace_minutes) {
                            $status = "late";
                            $tooltip = "Late <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];
                        }

                        if (($clock_in_minutes >= $shift_start_minutes) && ($clock_in_minutes <= $shift_grace_minutes)) {
                            $status = "ontime";
                            $tooltip = "Ontime <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];
                        }
                    } else {
                        if (beautify_time($row2["clock_in"]) < beautify_time($row2["shift_start_time"])) {
                            $status = "early";
                            $tooltip = "Early <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];;
                        }

                        $grace_time_for_late = !empty($row2["shift_grace_time"]) ? $row2["shift_grace_time"] : $row2["shift_start_time"];
                        if (beautify_time($row2["clock_in"]) > beautify_time($grace_time_for_late)) {
                            $status = "late";
                            $tooltip = "Late <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];
                        }

                        if ((beautify_time($row2["clock_in"]) >= beautify_time($row2["shift_start_time"])) && (beautify_time($row2["clock_in"]) <= beautify_time($row2["shift_grace_time"]))) {
                            $status = "ontime";
                            $tooltip = "Ontime <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];
                        }
                    }
                }


                $s_d = strtotime($row2["shift_date"]);
                $t = strtotime(date("Y-m-d"));

                if ($s_d <= $t || $row2["clock_in"] != null) {
                    $row[$row2["shift_date"]] = array("applicable" => "true", "presence" => $presence, "status" => $status, "tooltip" => $tooltip, "icon_class" => $icon_class);
                } else {
                    $row[$row2["shift_date"]] = array("applicable" => "false", "presence" => "-", "status" => "", "tooltip" => "", "icon_class" => "");
                }
            }

            foreach ($row as $key => &$value) {
                if (is_array($value) && $value["applicable"] == "false") {
                    if ($this->check_clocking($clockings_list, $row["id"], $key)) {
                        $value["applicable"] = "true";
                        $value["presence"] = "calendar-o";
                        $value["status"] = "no_shift";
                        $value["tooltip"] = "No Shift";
                        $value["icon_class"] = "far";
                    }
                }
            }
            unset($value);
        }

        $data["employees"] = $result;
        $data["positions"] = $this->db->query("SELECT id,title as name FROM positions WHERE company_id = $cid ORDER BY name")->result();
        $data["sections"] = $this->db->query("SELECT id,title as name FROM sections WHERE company_id = $cid ORDER BY name")->result();
        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        $data["skip"] = $skip;
        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET);

        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];
        if ($permissions_level == "Outlet") {
            $holidays_with_names = get_public_holidays_with_name($bid);
            $data["public_holidays"] = $holidays_with_names[0];
            $data["public_holidays_names"] = $holidays_with_names[1];
        } else {
            $holidays_with_names = get_public_holidays_with_name();
            $data["public_holidays"] = $holidays_with_names[0];
            $data["public_holidays_names"] = $holidays_with_names[1];
        }

        $data["filters"] = $this->load->view('filters', $data, true);
        $currentURL = current_url();
        $query_string = http_build_query($_GET);
        $data["pagination_url"] = $currentURL . '?' . $query_string;
        $data["attendance_sheet_export_url"] = base_url() . "overview/attendance_sheet_pdf?$query_string";

        $this->load->view('attendance_sheet', $data);
        $this->load->view('footer', $data);
    }

    public function attendance_sheet_pdf()
    {
        $current_user = get_user();
        $data['current_user'] = $current_user;
        // $data['pageTitle'] = "Attendance Sheet";
        // $data['active_menu'] = "overview/attendance_sheet";
        // $this->load->view('header',$data);
        // $data["menus"] = get_menus();
        // $this->load->view('sidebar',$data);

        $data["filters_form_action"] = "overview/attendance_sheet";
        render_all_filters($data);
        $where_filter = $data["where_filter"];
        $where_date = $data["where_date"];
        $where_clock_date = $data["where_clock_date"];
        // echo $where_filter;die;
        $cid = get_user()["company_id"];

        $days_settings = $this->db->select('from_hour,to_hour,days')->from('days_settings')->where('company_id', $cid)->get()->result();


        $total_records = $this->db->query("SELECT COUNT(DISTINCT employees.id) as total_records FROM employees INNER JOIN roles ON employees.role_id = roles.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = employees.id WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND roles.exclude_from_system = 'no' AND $where_filter")->row()->total_records;
        $limit = 20;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;


        $result = $this->db->query("SELECT employees.id,special_id, first_name FROM employees INNER JOIN roles ON employees.role_id = roles.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = employees.id WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND roles.exclude_from_system = 'no' AND  $where_filter
            GROUP BY employees.id,special_id, first_name ORDER BY special_id")->result_array();

        $employees_ids = array();
        foreach ($result as $r) {
            $employees_ids[] = $r["id"];
        }

        $first_day = $data['start_date'];
        $last_day = $data['end_date'];

        $clockings_list = $this->db->select('id, employee_id, date(datetime) as search_date')->from('clockings_news')->where('clockings_news.deleted_at', null)->where_in('employee_id', $employees_ids)->where("datetime between '$first_day 00:00:00' AND '$last_day 23:59:59'")->get()->result();
        $result_list = get_result_list_basic($employees_ids, $first_day, $last_day);
        $result_list_overnight = get_result_list_overnight_basic($employees_ids, $first_day, $last_day);
        $result_list_preshift = get_result_list_preshift_basic($employees_ids, $first_day, $last_day);

        $prev_day = date('Y-m-d', strtotime('-1 day', strtotime($first_day)));
        $shift_data_array = $this->db->select('s.id, name, overnight, s.is_preshift, date, date as shift_date, employees, half_day, grace_time as shift_grace_time, start_time as shift_start_time, end_time as shift_end_time, is_leave as shift_is_leave, is_paid, is_paid as shift_is_paid')->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->where('date >=', $prev_day)->where('date <=', $last_day)->where('company_id', $cid)->get()->result();

        $dateComponents = getdate();
        //$month = $dateComponents['mon'];
        $year = $data["selected_year"];

        $comma_separated_emp_ids = implode(",", $employees_ids);
        $interval_minutes = get_interval_minutes($cid);

        $employees_min_clockings = $this->db->query("select employee_id, date(clockings.datetime) as clocking_date, MIN(clockings.datetime) as clock_in FROM clockings_news as clockings where date(clockings.datetime) between '$first_day' and '$last_day' and clockings.employee_id in ($comma_separated_emp_ids) AND type = 'in' AND clockings.deleted_at is null group by employee_id, date(clockings.datetime) ORDER BY date(clockings.datetime)")->result_array();

        // Preshift-adjusted version: shifts a clock-in made just before midnight forward onto
        // the next calendar day, so it lines up with the shift's own (next-day) date.
        $employees_min_clockings_preshift = $this->db->query("select employee_id, date(date_add(clockings.datetime, interval $interval_minutes minute)) as clocking_date, MIN(clockings.datetime) as clock_in FROM clockings_news as clockings where date(date_add(clockings.datetime, interval $interval_minutes minute)) between '$first_day' and '$last_day' and clockings.employee_id in ($comma_separated_emp_ids) AND type = 'in' AND clockings.deleted_at is null group by employee_id, date(date_add(clockings.datetime, interval $interval_minutes minute)) ORDER BY date(date_add(clockings.datetime, interval $interval_minutes minute))")->result_array();

        $manual_late_list = $this->db->select('employee_id, date, late_hours')->from('manual_late')->where('date >=', $first_day)->where('date <=', $last_day)->where_in('employee_id', $employees_ids)->get()->result();
        $data["period_of_dates"] = new DatePeriod(
            new DateTime($first_day),
            new DateInterval('P1D'),
            (new DateTime($last_day))->add(new DateInterval('P1D'))
        );

        //echo '<pre>';
        foreach ($result as &$row) {
            //$result2 = $this->db->query("SELECT shift_id,date as shift_date FROM shift_days WHERE FIND_IN_SET(".$row["id"].",shift_days.employees)")->result_array();

            $emp_id = $row["id"];

            // $shift_data_list = get_shift_list_basic($emp_id, $first_day, $last_day);
            $shift_data_list = make_shift_list_basic($shift_data_array, $emp_id);

            // $clockings_table = render_clockings_query_for_employee_month($emp_id,$this->input->get("month"),$this->input->get("year"));

            $result2_normal = get_current_employee_clockings($employees_min_clockings, $emp_id);
            $result2_preshift = get_current_employee_clockings($employees_min_clockings_preshift, $emp_id);
            // echo $this->db->last_query();die;

            $result2 = merge_result_with_shifts_preshift_aware($result2_normal, $result2_preshift, $shift_data_list);

            // echo $this->db->last_query();
            // die();

            //print_r($result2);




            // $max_date = cal_days_in_month(CAL_GREGORIAN, $data["selected_month"], $year);

            foreach ($data["period_of_dates"] as $value) {
                $dd = $value->format('Y-m-d');
                $row[$dd] = array("applicable" => "false", "presence" => "-", "status" => "-");
            }



            foreach ($result2 as &$row2) {
                $presence = "-";
                $status = "-";
                $tooltip = "";
                $icon_class = "far";

                //var_dump($row2);
                //die();

                $explode_employees = explode(',', $row2["employees"]);

                if ($row2["clock_in"] == null) {
                    //$presence = "times";
                    //$status = "absent";

                    if (in_array($emp_id, $explode_employees)) {
                        if ($row2["shift_is_leave"] == "no") {
                            $presence = "calendar-times";
                            $status = "absent";
                            $tooltip = "Absent<br/> Shift: " . $row2["name"];
                        }
                        if (empty($row2['shift_start_time']) || empty($row2['shift_end_time'])) {
                            $presence = "-";
                            $status = "-";
                            $tooltip = "";
                        }
                        if ($row2["shift_is_leave"] == "yes") {
                            if ($row2["shift_is_paid"] == "yes") {
                                $presence = "calendar-plus";
                                $status = "leave";
                                $tooltip = "Paid Leave<br/> Shift: " . $row2["name"];
                            } else {
                                $presence = "calendar-minus";
                                $status = "leave";
                                $tooltip = "Unpaid Leave<br/> Shift: " . $row2["name"];
                            }
                        }
                    }
                } else {
                    $presence = "calendar-check";
                    $is_preshift_shift = false;
                    if ($row2['shift_date']) {
                        $shift_data = search_from_list($shift_data_list, $row2['shift_date']);

                        if ($shift_data && $shift_data->overnight == "Yes") {
                            $current_clockings = search_clocking_by_id($result_list_overnight, $row2['shift_date'], $emp_id);
                        } elseif ($shift_data && isset($shift_data->is_preshift) && $shift_data->is_preshift == "Yes") {
                            $current_clockings = search_clocking_by_id($result_list_preshift, $row2['shift_date'], $emp_id);
                            $is_preshift_shift = true;
                        } else {
                            if (!$shift_data) {
                                $prev_day = date('Y-m-d', strtotime('-1 day', strtotime($row2['shift_date'])));
                                $prev_clockings = search_clocking_by_id($result_list_overnight, $prev_day, $emp_id);
                                $current_clockings = remove_duplicate_clockings($current_clockings, $row2['shift_date'], $shift_data_list, $prev_clockings);
                            }
                        }

                        $clockings_list = $this->removeClockings($clockings_list, $current_clockings);

                        $days = calculate_days_from_clockings($current_clockings, $days_settings);

                        if ($days == 0.5 && $shift_data->half_day != "Yes") {
                            $presence = "clock-o";
                        }

                        if ($shift_data->half_day == "Yes" && $shift_data->is_paid == "yes") {
                            $presence = "calendar-day";
                            $icon_class = "half-day-paid fa";
                        } elseif ($shift_data->half_day == "Yes" && $shift_data->is_paid == "no") {
                            $presence = "calendar-day";
                            $icon_class = "half-day-unpaid fa";
                        }
                    }



                    // $manual_late = $this->db->select('late_hours')->from('manual_late')->where('employee_id', $emp_id)->where('date', $row2['shift_date'])->get()->row();
                    $manual_late = search_late_from_list($manual_late_list, $row2['shift_date'], $emp_id);

                    if ($manual_late) {
                        $manual_clock_in = $this->add_time(beautify_time($row2['shift_grace_time']), $manual_late->late_hours);


                        if ($manual_clock_in < beautify_time($row2["shift_start_time"])) {
                            $status = "early";
                            $tooltip = "Early <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];;
                        }

                        $grace_time_for_late = !empty($row2["shift_grace_time"]) ? $row2["shift_grace_time"] : $row2["shift_start_time"];
                        if ($manual_clock_in > beautify_time($grace_time_for_late)) {
                            $status = "late";
                            $tooltip = "Late <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];
                        }

                        if (($manual_clock_in >= beautify_time($row2["shift_start_time"])) && ($manual_clock_in <= beautify_time($row2["shift_grace_time"]))) {
                            $status = "ontime";
                            $tooltip = "Ontime <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];
                        }
                    } elseif ($is_preshift_shift) {
                        // Preshift clock-ins can land the night before (e.g. 23:50) for a shift
                        // that starts just after midnight (e.g. 00:10). Plain "H:i" string
                        // comparison would wrongly read 23:50 as later-in-the-day than 00:10,
                        // so compare using minutes normalized around the midnight boundary.
                        $clock_in_minutes = preshift_normalized_minutes(beautify_time($row2["clock_in"]));
                        $shift_start_minutes = preshift_normalized_minutes(beautify_time($row2["shift_start_time"]));

                        $grace_time_for_late = !empty($row2["shift_grace_time"]) ? $row2["shift_grace_time"] : $row2["shift_start_time"];
                        $grace_minutes = preshift_normalized_minutes(beautify_time($grace_time_for_late));
                        $shift_grace_minutes = preshift_normalized_minutes(beautify_time($row2["shift_grace_time"]));

                        if ($clock_in_minutes < $shift_start_minutes) {
                            $status = "early";
                            $tooltip = "Early <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];
                        }

                        if ($clock_in_minutes > $grace_minutes) {
                            $status = "late";
                            $tooltip = "Late <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];
                        }

                        if (($clock_in_minutes >= $shift_start_minutes) && ($clock_in_minutes <= $shift_grace_minutes)) {
                            $status = "ontime";
                            $tooltip = "Ontime <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];
                        }
                    } else {
                        if (beautify_time($row2["clock_in"]) < beautify_time($row2["shift_start_time"])) {
                            $status = "early";
                            $tooltip = "Early <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];;
                        }

                        $grace_time_for_late = !empty($row2["shift_grace_time"]) ? $row2["shift_grace_time"] : $row2["shift_start_time"];
                        if (beautify_time($row2["clock_in"]) > beautify_time($grace_time_for_late)) {
                            $status = "late";
                            $tooltip = "Late <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];
                        }

                        if ((beautify_time($row2["clock_in"]) >= beautify_time($row2["shift_start_time"])) && (beautify_time($row2["clock_in"]) <= beautify_time($row2["shift_grace_time"]))) {
                            $status = "ontime";
                            $tooltip = "Ontime <br/>Clock in: " . beautify_time($row2["clock_in"]) . "<br/> Shift: " . $row2["name"];
                        }
                    }
                }


                $s_d = strtotime($row2["shift_date"]);
                $t = strtotime(date("Y-m-d"));

                if ($s_d <= $t || $row2["clock_in"] != null) {
                    $row[$row2["shift_date"]] = array("applicable" => "true", "presence" => $presence, "status" => $status, "tooltip" => $tooltip, "icon_class" => $icon_class);
                } else {
                    $row[$row2["shift_date"]] = array("applicable" => "false", "presence" => "-", "status" => "", "tooltip" => "", "icon_class" => "");
                }
            }



            foreach ($row as $key => &$value) {
                if (is_array($value) && $value["applicable"] == "false") {
                    if ($this->check_clocking($clockings_list, $row["id"], $key)) {
                        $value["applicable"] = "true";
                        $value["presence"] = "calendar-o";
                        $value["status"] = "no_shift";
                        $value["tooltip"] = "No Shift";
                        $value["icon_class"] = "far";
                    }
                }
            }
            unset($value);
        }


        // print_r($result);
        // print_r($result2);
        // die();


        $data["employees"] = $result;

        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        $data["skip"] = $skip;
        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET);

        $bid = get_user()["branch_id"];
        $permissions_level = get_user()["permissions_level"];
        if ($permissions_level == "Outlet") {
            $holidays_with_names = get_public_holidays_with_name($bid);
            $data["public_holidays"] = $holidays_with_names[0];
            $data["public_holidays_names"] = $holidays_with_names[1];
        } else {
            $holidays_with_names = get_public_holidays_with_name();
            $data["public_holidays"] = $holidays_with_names[0];
            $data["public_holidays_names"] = $holidays_with_names[1];
        }

        $this->load->view('attendance_sheet_pdf', $data);
        $html = $this->output->get_output();
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper("A4", "landscape");
        $this->dompdf->render();

        $this->dompdf->stream($data["selected_month"] . "-" . $data["selected_year"] .
            " - Merit System - " . time(), array("Attachment" => 0));
        insert_log("Simple", ["action" => "Exported,Merit Sheet"]);
    }

    public function shifts_assignment()
    {
        if (!is_page_permitted('shifts_assignment')) {
            redirect_if_not_permitted();
        }

        $current_user = get_user();
        $data['pageTitle'] = "Shift Assignment";
        $data['active_menu'] = "overview/shifts_assignment";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);

        $data["filters_form_action"] = "overview/shifts_assignment";
        render_all_filters($data);

        $first_day = $data["formatted_date"]["start_date"]->format("Y-m-d");
        $last_day = $data["formatted_date"]["end_date"]->format("Y-m-d");
        $where_filter = $data["where_filter"];
        $where_date = $data["where_date"];
        $where_clock_date = $data["where_clock_date"];



        $cid = $current_user["company_id"];

        $total_records = $this->db->query("SELECT COUNT(DISTINCT employees.id) as total_records FROM employees INNER JOIN roles ON employees.role_id = roles.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = employees.id WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND roles.exclude_from_system = 'no' AND $where_filter ")->row()->total_records;

        $limit = 20;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;


        $result = $this->db->query("SELECT employees.id, special_id,first_name FROM employees INNER JOIN roles ON employees.role_id = roles.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = employees.id WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND roles.exclude_from_system = 'no' AND $where_filter
            GROUP BY employees.id, special_id,first_name ORDER BY special_id LIMIT $skip,$limit")->result_array();

        $dateComponents = getdate();
        $year = $data["selected_year"]; //$dateComponents['year'];
        $data["period_of_dates"] = new DatePeriod(
            new DateTime($first_day),
            new DateInterval('P1D'),
            (new DateTime($last_day))->add(new DateInterval('P1D'))
        );

        foreach ($result as &$row) {
            $emp_id = $row["id"];

            $result2 = $this->db->query("SELECT remark, shift_days.*, shifts.color as color, shifts.code as code, shifts.name as shift, shifts.id = shift_id FROM shift_days INNER JOIN shifts ON shift_days.shift_id = shifts.id LEFT JOIN remarks on remarks.employee_id = $emp_id and remarks.remark_date = shift_days.date where FIND_IN_SET($emp_id,shift_days.employees) $where_date ")->result_array();

            $periodOfDates = new DatePeriod(
                new DateTime($first_day),
                new DateInterval('P1D'),
                (new DateTime($last_day))->add(new DateInterval('P1D'))
            );

            foreach ($periodOfDates as $date) {
                $dd = $date->format('Y-m-d');
                $row[$dd] = array("applicable" => "false", "assigned" => "-", "shift" => "", "shift_id" => "", "color" => "", "code" => "", "remark" => "");
            }

            foreach ($result2 as &$row2) {
                $assigned = "yes";
                $shift = $row2["shift"];
                $shift_id = $row2["shift_id"];
                $color = $row2["color"];
                $code = $row2["code"];
                $remark = $row2["remark"];

                $row[$row2["date"]] = array("applicable" => "true", "assigned" => $assigned, "shift" => $shift, "shift_id" => $shift_id, "color" => $color, "code" => $code, "remark" => $remark);
            }
        }

        $data["employees"] = $result;
        $data["positions"] = $this->db->query("SELECT id,title as name FROM positions WHERE company_id = $cid ORDER BY name")->result();
        $data["sections"] = $this->db->query("SELECT id,title as name FROM sections WHERE company_id = $cid ORDER BY name")->result();
        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        $data["skip"] = $skip;
        unset($_GET['page']);
        $currentURL = current_url();
        $query_string = http_build_query($_GET);
        $data["pagination_url"] = $currentURL . '?' . $query_string;
        $data["summary_export_url"] = base_url() . "overview/shifts_assignment_pdf?$query_string";
        $permissions_level = $current_user["permissions_level"];
        $bid = $current_user["branch_id"];
        $selected_branch = $data["selected_branch_id"];

        $shifts_query = $this->db->select("s.*, b.name as branch_name")->from("shifts s")
            ->join("branches b", "s.branch_id = b.id", "left")->where("s.company_id = '$cid'")->where('active', 1);

        if ($selected_branch != 0) {
            $shifts_query->where("(s.branch_id = '$selected_branch' or s.is_leave = 'yes' or s.branch_id is null)");
        } elseif ($permissions_level === "Outlet") {
            $shifts_query->where("(s.branch_id = '$bid' or s.is_leave = 'yes' or s.branch_id is null)");
        }

        $data['shifts'] = $shifts_query->order_by('s.is_leave DESC, s.name ASC')->get()->result();

        $bid = $current_user["branch_id"];
        if ($permissions_level == "Outlet") {
            $holidays_with_names = get_public_holidays_with_name($bid);
            $data["public_holidays"] = $holidays_with_names[0];
            $data["public_holidays_names"] = $holidays_with_names[1];
        } else {
            $holidays_with_names = get_public_holidays_with_name();
            $data["public_holidays"] = $holidays_with_names[0];
            $data["public_holidays_names"] = $holidays_with_names[1];
        }

        $data['permissions_level'] = $permissions_level;
        $data["filters"] = $this->load->view('filters', $data, true);


        $this->load->view('shifts_assignment', $data);
        $this->load->view('footer', $data);
    }

    public function shifts_assignment_pdf()
    {
        $current_user = get_user();
        $data['current_user'] = $current_user;
        $data['pageTitle'] = "Shift Assignment";
        $data['active_menu'] = "overview/shifts_assignment_pdf";

        $data["filters_form_action"] = "overview/shifts_assignment_pdf";
        render_all_filters($data);

        $first_day = $data["formatted_date"]["start_date"]->format("Y-m-d");
        $last_day = $data["formatted_date"]["end_date"]->format("Y-m-d");
        $date = DateTime::createFromFormat('Y-m-d', $first_day);
        $data['from_f'] = $date->format('d/m/Y');
        $date = DateTime::createFromFormat('Y-m-d', $last_day);
        $data['to_f'] = $date->format('d/m/Y');
        $where_filter = $data["where_filter"];
        $where_date = $data["where_date"];
        $where_clock_date = $data["where_clock_date"];

        $cid = $current_user["company_id"];

        $result = $this->db->query("SELECT employees.id, special_id,first_name FROM employees INNER JOIN roles ON employees.role_id = roles.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = employees.id WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND roles.exclude_from_system = 'no' AND $where_filter
            GROUP BY employees.id, special_id,first_name ORDER BY special_id")->result_array();

        $data['dateComponents'] = getdate();
        $year = $data["selected_year"]; //$dateComponents['year'];
        $data["period_of_dates"] = new DatePeriod(
            new DateTime($first_day),
            new DateInterval('P1D'),
            (new DateTime($last_day))->add(new DateInterval('P1D'))
        );

        foreach ($result as &$row) {
            $emp_id = $row["id"];
            $result2 = $this->db->query("SELECT remark, shift_days.*, shifts.color as color, shifts.code as code, shifts.name as shift, shifts.id = shift_id FROM shift_days INNER JOIN shifts ON shift_days.shift_id = shifts.id LEFT JOIN remarks on remarks.employee_id = $emp_id and remarks.remark_date = shift_days.date where FIND_IN_SET($emp_id,shift_days.employees) $where_date ")->result_array();

            foreach ($data['period_of_dates'] as $date) {
                $dd = $date->format('Y-m-d');
                $row[$dd] = array("applicable" => "false", "assigned" => "-", "shift" => "", "shift_id" => "", "color" => "", "code" => "", "remark" => "");
            }

            foreach ($result2 as &$row2) {
                $assigned = "yes";
                $shift = $row2["shift"];
                $shift_id = $row2["shift_id"];
                $color = $row2["color"];
                $code = $row2["code"];
                $remark = $row2["remark"];

                $row[$row2["date"]] = array("applicable" => "true", "assigned" => $assigned, "shift" => $shift, "shift_id" => $shift_id, "color" => $color, "code" => $code, "remark" => $remark);
            }
        }

        $data["employees"] = $result;
        $data["positions"] = $this->db->query("SELECT id,title as name FROM positions WHERE company_id = $cid ORDER BY name")->result();
        $data["sections"] = $this->db->query("SELECT id,title as name FROM sections WHERE company_id = $cid ORDER BY name")->result();

        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET);
        $permissions_level = $current_user["permissions_level"];
        $bid = $current_user["branch_id"];
        $selected_branch = $data["selected_branch_id"];

        $shifts_query = $this->db->select("s.*, b.name as branch_name")->from("shifts s")
            ->join("branches b", "s.branch_id = b.id", "left")->where("s.company_id = '$cid'");

        if ($selected_branch != 0) {
            $shifts_query->where("(s.branch_id = '$selected_branch' or s.is_leave = 'yes')");
        } elseif ($permissions_level === "Outlet") {
            $shifts_query->where("(s.branch_id = '$bid' or s.is_leave = 'yes')");
        }

        $data['shifts'] = $shifts_query->order_by('s.is_leave DESC, s.name ASC')->get()->result();

        $bid = $current_user["branch_id"];
        if ($permissions_level == "Outlet") {
            $holidays_with_names = get_public_holidays_with_name($bid);
            $data["public_holidays"] = $holidays_with_names[0];
            $data["public_holidays_names"] = $holidays_with_names[1];
        } else {
            $holidays_with_names = get_public_holidays_with_name();
            $data["public_holidays"] = $holidays_with_names[0];
            $data["public_holidays_names"] = $holidays_with_names[1];
        }

        $data['permissions_level'] = $permissions_level;

        $this->load->view('shifts_assignment_pdf', $data);
        $html = $this->output->get_output();
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper("A4", "landscape");
        $this->dompdf->render();
        $this->dompdf->stream($data["selected_month"] . "-" . $data["selected_year"] .
            " - Shift Assignment - " . time(), array("Attachment" => 0));
        insert_log("Simple", ["action" => "Exported,Shifts Assignment"]);
    }

    public function shifts_calendar()
    {

        $data['pageTitle'] = "Shifts Calendar";
        $data['active_menu'] = "overview/shifts_calendar";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);

        $current_user = get_user();

        $cid = $current_user["company_id"];

        $data["selected_branch_id"] = 0;
        $data["selected_dep_id"] = 0;
        $data["selected_month"] = 0;
        $data["selected_year"] = 0;

        $where_filter = "";
        $where_clock_date = "";
        $where_date = "";

        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];

        $limit_access_to_department = $current_user["limit_access_to_department"];
        $department_id = $current_user["department_id"];

        //$where_branch_1 = '';
        $where_branch_2 = '';
        $where_department = '';

        //$where_branch_3 = '';


        if ($permissions_level == "Outlet") {
            $where_branch_2 = " AND id = $bid ";

            if ($limit_access_to_department == "yes") {
                $allowed_departments = get_allowed_departments($current_user);
                $where_department = " AND id in ($allowed_departments) ";
                // $where_department = " AND id = $department_id ";
                if ($this->input->get("branch") != $bid || $this->input->get("dep") != $department_id) {
                    redirect("overview/shifts_calendar?dep=$department_id&branch=$bid&month=" . date('m') . "&year=" . date('Y'));
                    return;
                }
            } else {
                if ($this->input->get("branch") != $bid) {
                    redirect("overview/shifts_calendar?branch=$bid&month=" . date('m') . "&year=" . date('Y'));
                    return;
                }
            }
        } else {
            if ($limit_access_to_department == "yes") {
                $allowed_departments = get_allowed_departments($current_user);
                $where_department = " AND id in ($department_id) ";
                // $where_department = " AND id = $department_id ";
                if ($this->input->get("dep") != $department_id) {
                    redirect("overview/shifts_calendar?dep=$department_id&month=" . date('m') . "&year=" . date('Y'));
                    return;
                }
            }
        }


        if (!empty($this->input->get("branch"))) {
            $data["selected_branch_id"] = $this->input->get("branch");
            $where_filter = $where_filter . " branch_id = " . $this->input->get("branch") . " AND ";
        }
        if (!empty($this->input->get("dep"))) {
            $data["selected_dep_id"] = $this->input->get("dep");
            $where_filter = $where_filter . " department_id = " . $this->input->get("dep") . " AND ";
        }

        if (!empty($this->input->get("month")) && !empty($this->input->get("year"))) {
            $data["selected_month"] = $this->input->get("month");
            $data["selected_year"] = $this->input->get("year");
            $where_clock_date = " AND MONTH(clock_in) = " . $this->input->get("month") . " AND YEAR(clock_in) = " . $this->input->get("year");
            $where_date = " AND MONTH(date) = " . $this->input->get("month") . " AND YEAR(date) = " . $this->input->get("year");
        } else {
            redirect("overview/shifts_calendar?month=" . date('m') . "&year=" . date('Y'));
            return;
        }

        $where_filter = $where_filter . " company_id = " . $cid;

        $where_filter = trim($where_filter);
        $where_filter = trim($where_filter, "AND");


        if (!empty($where_filter)) {
            $where_filter = " WHERE " . $where_filter;
        }

        $dateComponents = getdate();
        //$month = $dateComponents['mon'];
        $year = $data["selected_year"]; //$dateComponents['year'];

        $max_date = cal_days_in_month(CAL_GREGORIAN, $data["selected_month"], $year);
        //die();


        for ($x = 1; $x <= $max_date; $x++) {
            $dd = $year . "-" . $data["selected_month"] . "-" . sprintf("%02d", $x);

            $result = $this->db->query("SELECT shifts.name, IFNULL((SELECT (LENGTH(shift_days.employees) - LENGTH(REPLACE(shift_days.employees, ',', ''))+ 1) FROM shift_days where shift_days.shift_id = shifts.id AND shift_days.date = '$dd'),0) as count FROM shifts where shifts.company_id = $cid")->result_array();

            $total_assigned_emp = array_sum(array_column($result, 'count'));


            $result2 = $this->db->query("SELECT DATE(clock_in) as clock_in,COUNT(employee_id) as cnt FROM (SELECT clockings.employee_id,MIN(clock_in) as clock_in FROM clockings
                INNER JOIN shifts ON clockings.shift_id = shifts.id WHERE shifts.company_id = $cid AND DATE(clock_in) = '$dd'
                GROUP BY DATE(clock_in),clockings.employee_id) as xx")->row_array();

            //var_dump($result2);

            $result[] = array("name" => "Absent", "count" => ($total_assigned_emp - $result2["cnt"]));



            $data["shifts_calendar_data"][$dd] = $result;
        }

        // echo $this->db->last_query();
        // die();

        // print_r($data["shifts_calendar_data"]);
        // die();






        $data["branches"] = $this->db->query("SELECT * FROM branches WHERE company_id = $cid $where_branch_2 ORDER BY name")->result();
        $data["departments"] = $this->db->query("SELECT * FROM departments WHERE company_id = $cid $where_department ORDER BY name")->result();

        $this->load->view('shifts_calendar', $data);
        $this->load->view('footer', $data);
    }

    function check_clocking($list, $emp_id, $date)
    {
        foreach ($list as $l) {
            if ($l->search_date == $date && $l->employee_id == $emp_id) {
                return true;
            }
        }
        return false;
    }

    function removeClockings($clockings_list, $current_clockings)
    {
        $ids = array();
        foreach ($current_clockings as $cc) {
            $ids[] = $cc->clock_in_id;
            $ids[] = $cc->clock_out_id;
        }
        foreach ($clockings_list as $key => $value) {
            if (in_array($value->id, $ids)) {
                unset($clockings_list[$key]);
            }
        }
        return $clockings_list;
    }

    function round_late_in()
    {
        if (!is_page_permitted('round_late_in')) {
            redirect_if_not_permitted();
        }
        $data['pageTitle'] = "Late In Round Settings";
        $data['active_menu'] = "overview/round_late_in";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);
        $this->load->view('round_late_in');
        $this->load->view('footer');
    }
    function getSettings()
    {
        $user = get_user();
        $permssion_level = $user['permissions_level'];

        // Default data for company admin
        $data['ot_type'] = "default";
        $data['ot_round'] = false;
        $data['round_first_hour_only'] = false;
        $data['round_by_exact_hour'] = false;
        $data['ot_weekly_hours'] = 0;
        $data['first_day_of_week'] = '';
        $data['bid'] = null;
        $data['ot_round_settings'] = [];

        if ($permssion_level === "Company") {
            $data['outlets'] = get_company_outlets();
            echo json_encode($data);
        } else {
            // Load outlet data
            $bid = $user['branch_id'];
            $data['bid'] = $bid;
            $data['outlets'] = get_company_outlets($bid);
            $ot_settings = $this->db->select('ot_type, ot_round, early_ot_round, round_first_hour_only, round_by_exact_hour, different_first_hour_rounding, ot_weekly_hours, first_day_of_week')->from('branches')->where('id', $bid)->get()->row();

            if ($ot_settings) {
                $data['ot_type'] = $ot_settings->ot_type;
                $data['ot_round'] = ($ot_settings->ot_round == 0) ? false : true;
                $data['round_first_hour_only'] = ($ot_settings->round_first_hour_only == 0) ? false : true;
                $data['round_by_exact_hour'] = ($ot_settings->round_by_exact_hour == 0) ? false : true;
                $data['ot_weekly_hours'] = (float) $ot_settings->ot_weekly_hours;
                $data['first_day_of_week'] = $ot_settings->first_day_of_week;
            }

            $late_in_round_settings = $this->db->select("start, end, round_to, branch_id")->from("late_in_round_settings")->where("branch_id", $bid)->get()->result();

            // Conversion to number is needed to use input:number on front end
            foreach ($late_in_round_settings as $key => $v) {
                $late_in_round_settings[$key]->start = (float) $v->start;
                $late_in_round_settings[$key]->end = (float) $v->end;
                $late_in_round_settings[$key]->round_to = (float) $v->round_to;
            }

            $data['late_in_round_settings'] = $late_in_round_settings;
            echo json_encode($data);
        }
    }
    function getOutletSettings()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $bid = $request->outletId;
        $data['bid'] = $request->outletId;
        $data['late_in_round_settings'] = [];

        $late_in_round_settings = $this->db->select("start, end, round_to, branch_id")->from("late_in_round_settings")->where("branch_id", $bid)->get()->result();

        // Conversion to number is needed to use input:number on front end
        foreach ($late_in_round_settings as $key => $v) {
            $late_in_round_settings[$key]->start = (float) $v->start;
            $late_in_round_settings[$key]->end = (float) $v->end;
            $late_in_round_settings[$key]->round_to = (float) $v->round_to;
        }

        $data['late_in_round_settings'] = $late_in_round_settings;
        echo json_encode($data);
    }
    public function updateLDRoundSettings()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if ($request->bid === null || $request->bid === '') {
            $data['success'] = false;
            $data['message'] = "Please select an outlet";
            echo json_encode($data);
            return;
        }

        // if(count($request->round_settings) === 0)
        // {
        //  $data['success'] = false;
        //  $data['message'] = "Please add at least one Late In Round setting";
        //  echo json_encode($data);
        //  return;
        // }

        if ($this->is_LD_range_overlapping($request->round_settings)) {
            $data['success'] = false;
            $data['message'] = "Late In Round settings are invalid or overlapping";
            echo json_encode($data);
            return;
        }
        // Validated now update records

        $this->db->delete('late_in_round_settings', ['branch_id' => $request->bid]);

        $this->db->insert_batch('late_in_round_settings', $request->round_settings);

        $branch = $this->db->select('name')->from('branches')->where('id', $request->bid)->get()->row();

        $log_data = [
            'action' => 'Edited,Late In Round Settings',
            'to_branch_id' => $request->bid,
            'to_outlet' => $branch->name,
        ];
        insert_log("Late In Round Settings", $log_data);

        $data['success'] = true;
        echo json_encode($data);
    }
    function round_late_break()
    {
        if (!is_page_permitted('round_late_break')) {
            redirect_if_not_permitted();
        }
        $data['pageTitle'] = "Late Break Round Settings";
        $data['active_menu'] = "overview/round_late_break";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);
        $this->load->view('round_late_break');
        $this->load->view('footer');
    }
    function getSettingsLateBreak()
    {
        $user = get_user();
        $permssion_level = $user['permissions_level'];

        // Default data for company admin
        $data['ot_type'] = "default";
        $data['ot_round'] = false;
        $data['round_first_hour_only'] = false;
        $data['round_by_exact_hour'] = false;
        $data['ot_weekly_hours'] = 0;
        $data['first_day_of_week'] = '';
        $data['bid'] = null;
        $data['ot_round_settings'] = [];

        if ($permssion_level === "Company") {
            $data['outlets'] = get_company_outlets();
            echo json_encode($data);
        } else {
            // Load outlet data
            $bid = $user['branch_id'];
            $data['bid'] = $bid;
            $data['outlets'] = get_company_outlets($bid);
            $ot_settings = $this->db->select('ot_type, ot_round, early_ot_round, round_first_hour_only, round_by_exact_hour, different_first_hour_rounding, ot_weekly_hours, first_day_of_week')->from('branches')->where('id', $bid)->get()->row();

            if ($ot_settings) {
                $data['ot_type'] = $ot_settings->ot_type;
                $data['ot_round'] = ($ot_settings->ot_round == 0) ? false : true;
                $data['round_first_hour_only'] = ($ot_settings->round_first_hour_only == 0) ? false : true;
                $data['round_by_exact_hour'] = ($ot_settings->round_by_exact_hour == 0) ? false : true;
                $data['ot_weekly_hours'] = (float) $ot_settings->ot_weekly_hours;
                $data['first_day_of_week'] = $ot_settings->first_day_of_week;
            }

            $late_in_round_settings = $this->db->select("start, end, round_to, branch_id")->from("late_break_round_settings")->where("branch_id", $bid)->get()->result();

            // Conversion to number is needed to use input:number on front end
            foreach ($late_in_round_settings as $key => $v) {
                $late_in_round_settings[$key]->start = (float) $v->start;
                $late_in_round_settings[$key]->end = (float) $v->end;
                $late_in_round_settings[$key]->round_to = (float) $v->round_to;
            }

            $data['late_in_round_settings'] = $late_in_round_settings;
            echo json_encode($data);
        }
    }
    function getOutletSettingsLateBreak()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $bid = $request->outletId;
        $data['bid'] = $request->outletId;
        $data['late_in_round_settings'] = [];

        $late_in_round_settings = $this->db->select("start, end, round_to, branch_id")->from("late_break_round_settings")->where("branch_id", $bid)->get()->result();

        // Conversion to number is needed to use input:number on front end
        foreach ($late_in_round_settings as $key => $v) {
            $late_in_round_settings[$key]->start = (float) $v->start;
            $late_in_round_settings[$key]->end = (float) $v->end;
            $late_in_round_settings[$key]->round_to = (float) $v->round_to;
        }

        $data['late_in_round_settings'] = $late_in_round_settings;
        echo json_encode($data);
    }
    public function updateLDRoundSettingsLateBreak()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if ($request->bid === NULL || $request->bid === '') {
            $data['success'] = false;
            $data['message'] = "Please select an outlet";
            echo json_encode($data);
            return;
        }

        // if(count($request->round_settings) === 0)
        // {
        // 	$data['success'] = false;
        // 	$data['message'] = "Please add at least one Late In Round setting";
        // 	echo json_encode($data);
        // 	return;
        // }

        if ($this->is_LD_range_overlapping($request->round_settings)) {
            $data['success'] = false;
            $data['message'] = "Late In Round settings are invalid or overlapping";
            echo json_encode($data);
            return;
        }
        // Validated now update records

        $this->db->delete('late_break_round_settings', ['branch_id' => $request->bid]);

        $this->db->insert_batch('late_break_round_settings', $request->round_settings);

        $branch = $this->db->select('name')->from('branches')->where('id', $request->bid)->get()->row();

        $log_data = [
            'action' => 'Edited,Late In Round Settings',
            'to_branch_id' => $request->bid,
            'to_outlet' => $branch->name,
        ];
        insert_log("Late In Round Settings", $log_data);

        $data['success'] = true;
        echo json_encode($data);
    }
    function round_early_out()
    {
        if (!is_page_permitted('round_early_out')) {
            redirect_if_not_permitted();
        }
        $data['pageTitle'] = "Late Break Round Settings";
        $data['active_menu'] = "overview/round_early_out";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);
        $this->load->view('round_early_out');
        $this->load->view('footer');
    }
    function getSettingsEarlyOut()
    {
        $user = get_user();
        $permssion_level = $user['permissions_level'];

        // Default data for company admin
        $data['ot_type'] = "default";
        $data['ot_round'] = false;
        $data['round_first_hour_only'] = false;
        $data['round_by_exact_hour'] = false;
        $data['ot_weekly_hours'] = 0;
        $data['first_day_of_week'] = '';
        $data['bid'] = null;
        $data['ot_round_settings'] = [];

        if ($permssion_level === "Company") {
            $data['outlets'] = get_company_outlets();
            echo json_encode($data);
        } else {
            // Load outlet data
            $bid = $user['branch_id'];
            $data['bid'] = $bid;
            $data['outlets'] = get_company_outlets($bid);
            $ot_settings = $this->db->select('ot_type, ot_round, early_ot_round, round_first_hour_only, round_by_exact_hour, different_first_hour_rounding, ot_weekly_hours, first_day_of_week')->from('branches')->where('id', $bid)->get()->row();

            if ($ot_settings) {
                $data['ot_type'] = $ot_settings->ot_type;
                $data['ot_round'] = ($ot_settings->ot_round == 0) ? false : true;
                $data['round_first_hour_only'] = ($ot_settings->round_first_hour_only == 0) ? false : true;
                $data['round_by_exact_hour'] = ($ot_settings->round_by_exact_hour == 0) ? false : true;
                $data['ot_weekly_hours'] = (float) $ot_settings->ot_weekly_hours;
                $data['first_day_of_week'] = $ot_settings->first_day_of_week;
            }

            $late_in_round_settings = $this->db->select("start, end, round_to, branch_id")->from("early_out_round_settings")->where("branch_id", $bid)->get()->result();

            // Conversion to number is needed to use input:number on front end
            foreach ($late_in_round_settings as $key => $v) {
                $late_in_round_settings[$key]->start = (float) $v->start;
                $late_in_round_settings[$key]->end = (float) $v->end;
                $late_in_round_settings[$key]->round_to = (float) $v->round_to;
            }

            $data['late_in_round_settings'] = $late_in_round_settings;
            echo json_encode($data);
        }
    }
    function getOutletSettingsEarlyOut()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $bid = $request->outletId;
        $data['bid'] = $request->outletId;
        $data['late_in_round_settings'] = [];

        $late_in_round_settings = $this->db->select("start, end, round_to, branch_id")->from("early_out_round_settings")->where("branch_id", $bid)->get()->result();

        // Conversion to number is needed to use input:number on front end
        foreach ($late_in_round_settings as $key => $v) {
            $late_in_round_settings[$key]->start = (float) $v->start;
            $late_in_round_settings[$key]->end = (float) $v->end;
            $late_in_round_settings[$key]->round_to = (float) $v->round_to;
        }

        $data['late_in_round_settings'] = $late_in_round_settings;
        echo json_encode($data);
    }
    public function updateLDRoundSettingsEarlyOut()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if ($request->bid === NULL || $request->bid === '') {
            $data['success'] = false;
            $data['message'] = "Please select an outlet";
            echo json_encode($data);
            return;
        }

        // if(count($request->round_settings) === 0)
        // {
        // 	$data['success'] = false;
        // 	$data['message'] = "Please add at least one Late In Round setting";
        // 	echo json_encode($data);
        // 	return;
        // }

        if ($this->is_LD_range_overlapping($request->round_settings)) {
            $data['success'] = false;
            $data['message'] = "Early Out Round settings are invalid or overlapping";
            echo json_encode($data);
            return;
        }
        // Validated now update records

        $this->db->delete('early_out_round_settings', ['branch_id' => $request->bid]);

        $this->db->insert_batch('early_out_round_settings', $request->round_settings);

        $branch = $this->db->select('name')->from('branches')->where('id', $request->bid)->get()->row();

        $log_data = [
            'action' => 'Edited,Late In Round Settings',
            'to_branch_id' => $request->bid,
            'to_outlet' => $branch->name,
        ];
        insert_log("Early Out Round Settings", $log_data);

        $data['success'] = true;
        echo json_encode($data);
    }
    private function is_LD_range_overlapping(array $collection)
    {
        $length = count($collection);

        for ($i = 0; $i < $length; $i++) {
            if ($collection[$i]->start < 0 || $collection[$i]->end < 0 || $collection[$i]->round_to < 0) {
                return true;
            }
            for ($j = 0; $j < $length; $j++) {
                if ($i != $j && $collection[$j]->start <= $collection[$i]->end && $collection[$j]->end >= $collection[$i]->start) {
                    return true;
                }
            }
        }
        return false;
    }

    function lateness_deduction()
    {
        if (!is_page_permitted('lateness_deduction')) {
            redirect_if_not_permitted();
        }
        $data['pageTitle'] = "Lateness Deduction";
        $data['active_menu'] = "overview/lateness_deduction";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);

        $this->load->view('lateness_deduction');
        $this->load->view('footer');
    }

    public function get_deduction_settings()
    {
        $user = get_user();
        $permssion_level = $user['permissions_level'];

        $data['inc_early_out'] = true;
        $data['inc_late_break'] = true;
        $data['inc_late_in'] = true;
        $data['inc_short_hours'] = false;
        $data['bid'] = '';
        $data['lateness_time'] = 0;
        $data['deduct_from_ot'] = false;
        $data['deduction_date'] = 30;

        if ($permssion_level === "Company") {
            $data['outlets'] = get_company_outlets();
            echo json_encode($data);
        } else {
            $bid = $user['branch_id'];
            $data['bid'] = $bid;
            $data['outlets'] = get_company_outlets($bid);
            $deduction = $this->db->select('inc_late_in, inc_late_break, inc_early_out, inc_short_hours, void_lateness_time_if_less_than, deduct_from_ot, deduction_date')->from('branches')->where('id', $bid)->get()->row();

            if ($deduction) {
                $data['inc_early_out'] = ($deduction->inc_early_out == 0) ? false : true;
                $data['inc_late_break'] = ($deduction->inc_late_break == 0) ? false : true;
                $data['inc_late_in'] = ($deduction->inc_late_in == 0) ? false : true;
                $data['inc_short_hours'] = ($deduction->inc_short_hours == 0) ? false : true;
                $data['lateness_time'] = (float) $deduction->void_lateness_time_if_less_than;
                $data['deduct_from_ot'] = ($deduction->deduct_from_ot == 0) ? false : true;
                $data['deduction_date'] = $deduction->deduction_date;
            }
            echo json_encode($data);
        }
    }

    public function get_outlet_deduction_settings()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $data['inc_early_out'] = true;
        $data['inc_late_break'] = true;
        $data['inc_late_in'] = true;
        $data['inc_short_hours'] = false;
        $data['bid'] = $request->outletId;
        $data['lateness_time'] = 0;
        $data['deduct_from_ot'] = false;
        $data['deduction_date'] = 30;
        $deduction = $this->db->get_where('branches', array('id' => $request->outletId))->row();

        if ($deduction) {
            $data['inc_early_out'] = ($deduction->inc_early_out == 0) ? false : true;
            $data['inc_late_break'] = ($deduction->inc_late_break == 0) ? false : true;
            $data['inc_late_in'] = ($deduction->inc_late_in == 0) ? false : true;
            $data['inc_short_hours'] = ($deduction->inc_short_hours == 0) ? false : true;
            $data['lateness_time'] = (float) $deduction->void_lateness_time_if_less_than;
            $data['deduct_from_ot'] = ($deduction->deduct_from_ot == 0) ? false : true;
            $data['deduction_date'] = $deduction->deduction_date;
        }

        echo json_encode($data);
    }

    public function update_lateness_deduction_settings()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        if ($request->bid === null || $request->bid === '') {
            $data['success'] = false;
            $data['message'] = "Please select an outlet";
            echo json_encode($data);
            return;
        }

        if (
            $request->inc_short_hours === true && ($request->inc_early_out === true || $request->inc_late_break === true ||
                $request->inc_late_in === true)
        ) {
            $data['success'] = false;
            $data['message'] = 'Cannot select short hours with other settings';
            echo json_encode($data);
            return;
        }

        $request->inc_early_out = ($request->inc_early_out === true) ? 1 : 0;
        $request->inc_late_break = ($request->inc_late_break === true) ? 1 : 0;
        $request->inc_late_in = ($request->inc_late_in === true) ? 1 : 0;
        $request->inc_short_hours = ($request->inc_short_hours === true) ? 1 : 0;
        $request->deduct_from_ot = ($request->deduct_from_ot === true) ? 1 : 0;

        $data_to_save = [
            'inc_early_out' => $request->inc_early_out,
            'inc_late_break' => $request->inc_late_break,
            'inc_short_hours' => $request->inc_short_hours,
            'inc_late_in' => $request->inc_late_in,
            'void_lateness_time_if_less_than' => $request->lateness_time,
            'deduct_from_ot' => $request->deduct_from_ot,
            'deduction_date' => $request->deduction_date
        ];

        $this->db->where('id', $request->bid);
        $result = $this->db->update('branches', $data_to_save);
        $branch = $this->db->select('id, name')->from('branches')->where('id', $request->bid)->get()->row();
        if ($result === true) {
            $data['success'] = true;
            insert_log('Lateness Deduction Settings', [
                'action' => 'Edited,Lateness Deduction Settings',
                'to_outlet' => $branch->name,
                'to_branch_id' => $branch->id
            ]);
        } else {
            $data['success'] = false;
        }

        echo json_encode($data);
    }

    public function update_public_holidays()
    {
        $companies = $this->db->select('id')->from('companies')->get()->result();

        foreach ($companies as $c) {
            $public_holidays = $this->db->select('title, holiday_date')->from('public_holidays')->where('company_id', $c->id)->where('branch_id', 0)->get()->result();
            $branches = $this->db->select('id')->from('branches')->where('company_id', $c->id)->get()->result();

            foreach ($public_holidays as $p) {
                foreach ($branches as $b) {
                    $data = array(
                        "company_id" => $c->id,
                        "branch_id" => $b->id,
                        "title" => $p->title,
                        "holiday_date" => $p->holiday_date
                    );

                    $this->db->insert("public_holidays", $data);
                }
            }
        }

        $this->db->where('branch_id', 0)->delete('public_holidays');
    }

    public function public_holidays()
    {
        if (!is_page_permitted('public_holidays')) {
            redirect_if_not_permitted();
        }

        $user = (object) get_user();
        $permissions_level = $user->permissions_level;
        $cid = $user->company_id;
        $bid = $user->branch_id;
        $month = date('m');

        $data['current_year'] = date('Y');
        $this->db->select('distinct(year(holiday_date)) year')->from('public_holidays')->where('company_id', $cid);
        if ($permissions_level === 'Outlet') {
            $this->db->where('branch_id', $bid);
        }

        $years = $this->db->order_by('year', 'asc')->get()->result();
        $data['years'] = array_map(function ($year) {
            return $year->year;
        }, $years);

        if (!in_array($data['current_year'], $data['years'])) {
            $data['years'][] = $data['current_year'];
            sort($data['years']);
        }

        if ($month == '12') {
            $next_year = (int) $data['current_year'] + 1;
            $is_next_year_found = array_search($next_year, $data['years']);
            if ($is_next_year_found === false) {
                $data['years'][] = $next_year;
            }
        }

        $data['pageTitle'] = "Public Holidays";
        $data['active_menu'] = "overview/public_holidays";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();

        $this->load->view('sidebar', $data);
        $this->load->view('public_holidays', $data);
        $this->load->view('footer', $data);
    }

    public function get_public_holidays_xcrud()
    {
        $year = $this->input->get('year');
        $previous_year = $year - 1;
        $current_user = get_user();
        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];

        $permissions_level = $current_user["permissions_level"];


        $this->load->helper('xcrud');
        $xcrud = xcrud_get_instance();

        $xcrud->table('public_holidays');
        $xcrud->pass_var('company_id', $cid);

        $xcrud->where('company_id =', $cid);

        $xcrud->where("year(holiday_date) = $year");

        if ($permissions_level == "Outlet") {
            $xcrud->where('branch_id = ', $bid);
            $xcrud->relation('branch_id', 'branches', 'id', 'name', array('company_id' => $cid, 'id' => $bid));
        } else {
            $xcrud->relation('branch_id', 'branches', 'id', 'name', array('company_id' => $cid));
        }

        $xcrud->relation(
            'include_groups',
            'employee_groups',
            'id',
            'name',
            array('company_id' => $cid),
            $order_by = false,
            $multi = true
        );
        $xcrud->relation(
            'exclude_groups',
            'employee_groups',
            'id',
            'name',
            array('company_id' => $cid),
            $order_by = false,
            $multi = true
        );

        $xcrud->label('branch_id', 'Branch');
        $xcrud->label('replacement_ph', 'Replacement PH');
        $xcrud->column_callback('replacement_ph', 'get_replacement_ph_status');

        $xcrud->fields('company_id,updated_at,deleted_at,created_at', true);
        $xcrud->columns('company_id,updated_at,deleted_at,created_at', true);
        $xcrud->order_by('holiday_date', 'asc');

        $previous_button = '<button id="copy-holidays" data-year="' . $previous_year . '" class="btn btn-primary">Copy from year ' . $previous_year . '</button>';

        $xcrud->after_insert('after_holiday_insertion');
        $xcrud->after_update('after_holiday_updation');
        $xcrud->before_remove('before_holiday_removal');

        $xcrud->unset_title();

        $data['public_holidays'] = $xcrud->render() . $previous_button;
        $public_holidays = $this->load->view('public_holidays_xcrud', $data, true);
        echo $public_holidays;
    }
}
