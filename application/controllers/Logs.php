<?php

class Logs extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        if (is_null(get_user())) {
            redirect("welcome");
           //var_dump($this->session->userdata('antelope_user'));
        }
    }

    public function index()
    {
        if (!is_page_permitted('logs')) {
            redirect_if_not_permitted();
        }

        $user = get_user();
        $permission_level = get_role()->permissions_level;

        $filter_date = $this->input->get("filter_date");
        $branch_id = ($this->input->get("branch_id")) ? $this->input->get("branch_id") : $user['branch_id'];
        $branch_id = ($permission_level === 'Outlet') ? $user['branch_id'] : $branch_id;

        $data['branch_id'] = $branch_id;
        
        if (is_null($filter_date) || $filter_date == '') {
            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d');
        } else {
            $formatted_dates = daterange_to_dates($filter_date);
            $start_date = $formatted_dates["start_date"]->format('Y-m-d');
            $end_date = $formatted_dates["end_date"]->format('Y-m-d');
        }
        
        $logs_where = "DATE(l.created_at) BETWEEN '$start_date' AND '$end_date'";
        $logs_where .= " AND l.branch_id = $branch_id";

        $data['pageTitle'] = "Logs";
        $data['active_menu'] = "logs";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $cid = $user['company_id'];


        $data['logs'] =
         $this->db->select("l.*, TIME_FORMAT(l.from_time, '%H:%i') as from_time, TIME_FORMAT(l.to_time, '%H:%i') as to_time, e.first_name e_first_name")->from("logs l")
         ->join('employees e', 'e.id = l.user_id', 'left')
         ->where("l.company_id", $cid)
         ->where($logs_where)
         ->order_by('l.created_at', 'DESC')->get()->result();
         

        if ($permission_level == "Company") {
            $data['branches'] =
            $this->db->select("b.*")->from("branches b")->where("b.company_id", $cid)->get()->result();
        } else {
            $data['branches'] =
            $this->db->select("b.*")->from("branches b")->where("b.company_id", $cid)->where("b.id", $user['branch_id'])->get()->result();
        }
        // if($user['company_id'] == 206) {
        //     ini_set('display_errors', '1');
        //     ini_set('display_startup_errors', '1');
        //     error_reporting(E_ALL);
        // }
        foreach ($data['logs'] as $key => $log) {
            if (strpos($log->action, "assigned shift") || strpos($log->type, "assigned shift")) {
                unset($data['logs'][$key]);
                continue;
            }
            $log->description = $this->generate_description($log);
        }
        // if($user['company_id'] == 206) {
        //     die(__METHOD__ . ':' . __LINE__ . ' hello');
        // }
        
        $data['filter_date'] = $filter_date;
        $this->load->view('sidebar', $data);
        $this->load->view('logs', $data);
        $this->load->view('footer');
    }

    private function generate_description($log)
    {
        $description = "";

        $action_string = explode(",", $log->action);
        $action1 = $action_string[0];
        $action2 = $action_string[1];

        if ($log->type === 'Late In' || $log->type === 'Late Break' || $log->type === 'Early Out' || $log->type === 'Manual OT' || $log->type === "Clockings") {
            $target_name = $this->db->select("first_name")->from("employees")->where("id", $log->target_id)->get()->row()->first_name;
           // get user anchor
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> for ";
            $description .= $this->generate_link("summary/view/$log->target_id", $target_name);

           // If no record exist
            if ($log->from_time === null) {
                $description .= " set to $log->to_time";
                // If record exist but action is not deleted
            } elseif ($action1 === 'Deleted' || $action1 === 'Removed') {
                $description .= " from $log->from_time";
            } else {
                $description .= " from $log->from_time to $log->to_time";
            }

            $description .= " for ";
            if ($log->type === "Clockings") {
                $description .= "clock $log->clocking_type ";
            }
           // query string to get exact date's summary
            $query_date = to_html_date($log->for_date);
            $from_to_query_array = ['from' => $query_date, 'to' => $query_date];
            $query_string = http_build_query($from_to_query_array);

            $description .= $this->generate_link("summary/view/$log->target_id?$query_string", $log->for_date);
        } elseif ($log->type === 'Trips Update') {
            $target_name = $this->db->select("first_name")->from("employees")->where("id", $log->target_id)->get()->row()->first_name;
           // get user anchor
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> for ";
            $description .= $this->generate_link("summary/view/$log->target_id", $target_name);

            $description .= " for trip $log->trip_type from $log->from_trips to $log->to_trips for ";
           // query string to get exact date's summary
            $query_date = to_html_date($log->for_date);
            $from_to_query_array = ['from' => $query_date, 'to' => $query_date];
            $query_string = http_build_query($from_to_query_array);

            $description .= $this->generate_link('summary/view/' . $log->target_id . "?$query_string", $log->for_date);
        } elseif ($log->type === 'AUTH') {
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " <b>$action1 $action2</b>";
        } elseif ($log->type === 'Roles') {
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> ";
            $description .= $this->generate_link('dashboard/table/roles', $log->target_name);
            $description .= " with permissions level $log->role_permissions_level";
        } elseif ($log->type === "Employees") {
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> ";

            if ($action1 === "Added") {
                $description .= $this->generate_link("profile/index/$log->target_id", $log->target_name);
                $description .= " in outlet $log->to_outlet";
            } elseif ($action1 === "Edited") {
                $description .= $this->generate_link("profile/index/$log->target_id", $log->target_name);
                if ($log->from_branch_id && $log->from_branch_id !== $log->to_branch_id) {
                    $description .= " moved from $log->from_outlet to $log->to_outlet";
                }
            } elseif ($action1 === 'Terminated' || $action1 === 'Resigned') {
                $description .= "$log->target_name from $log->from_outlet";
            } else {
                $description .= $this->generate_link("profile/index/$log->target_id", $log->target_name);
                $description .= " from $log->from_outlet";
            }
        } elseif ($log->type === "Simple") {
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b>";
        } elseif ($log->type === 'Clocking Remarks' || $log->type === 'Clocking Late Reason') {
            $target_name = $this->db->select("first_name")->from("employees")->where("id", $log->target_id)->get()->row()->first_name;
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> for ";
            $description .= $this->generate_link("summary/view/$log->target_id", $target_name);
            $description .= " for ";
           // query string to get exact date's summary
            $query_date = to_html_date($log->for_date == null ? '0000-00-00' : $log->for_date);
            $from_to_query_array = ['from' => $query_date, 'to' => $query_date];
            $query_string = http_build_query($from_to_query_array);

            $description .= $this->generate_link('summary/view/' . $log->target_id . "?$query_string", $log->for_date);
        } elseif ($log->type === "Shifts") {
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> ";
            $description .= $this->generate_link("dashboard/table/shifts", $log->target_name);
            if ($action1 === "Added") {
                $description .= " to $log->to_outlet ";
            } elseif ($action1 === "Edited") {
                if ($log->from_outlet !== $log->to_outlet) {
                    $description .= " moved from $log->from_outlet to $log->to_outlet ";
                }
            } elseif ($action1 === "Deleted") {
                $description .= " from $log->to_outlet ";
            }
        } elseif ($log->type === "OT Deduction") {
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> ";
            $description .= "to $log->is_ot_deducted for ";
            $description .= $this->generate_link("summary/view/$log->target_id", $log->target_name);
        } elseif ($log->type === "Summary Status") {
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> ";
            $description .= "$log->summary_status_type to $log->summary_status for ";
            $description .= $this->generate_link("summary/view/$log->target_id", $log->target_name);
            $description .= " for ";
           // query string to get exact date's summary
            $query_date = to_html_date($log->for_date);
            $from_to_query_array = ['from' => $query_date, 'to' => $query_date];
            $query_string = http_build_query($from_to_query_array);

            $description .= $this->generate_link("summary/view/$log->target_id?$query_string", $log->for_date);
        } elseif ($log->type === "Leaves") {
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> ";
            if ($action1 === 'Deleted') {
                $description .= "$log->target_name that was ";
            } else {
                $description .= $this->generate_link("dashboard/table/leaves", $log->target_name) . " that is ";
            }
            $description .= (($log->is_paid === 'Yes') ? 'paid' : 'not paid') . " and ";
            $description .= (($log->is_half === 'Yes') ? 'half' : 'full') . " day ";
        } elseif ($log->type === "Day Settings") {
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> ";
            if ($action1 === 'Deleted') {
                $description .= "that was ";
            } else {
                $description .= "that is ";
            }
            $description .= "from hour $log->from_hour to hour $log->to_hour and days = $log->days";
        } elseif ($log->type === 'Termination Reasons') {
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> ";
            $description .= $this->generate_link('dashboard/table/termination_reasons', $log->target_name);
        } elseif ($log->type === 'Devices') {
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> ";
            $description .= $this->generate_link('dashboard/table/devices', $log->target_name);
            $description .= " to location $log->location";
        } elseif ($log->type === 'Holidays') {
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> ";
            $description .= $this->generate_link('dashboard/table/public_holidays', $log->target_name);
            $description .= " for date $log->for_date";
        } elseif ($log->type === 'Departments') {
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> ";
            $description .= $this->generate_link('dashboard/table/departments', $log->target_name);
            $description .= " at location $log->location";
        } elseif ($log->type === 'Positions') {
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> ";
            $description .= $this->generate_link('dashboard/table/positions', $log->target_name);
        } elseif ($log->type === 'OT Round Settings') {
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> ";
            $description .= " of outlet ";
            $description .= $this->generate_link('ot_settings', $log->to_outlet);
        } elseif ($log->type === 'Lateness Deduction Settings') {
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> ";
            $description .= " of outlet ";
            $description .= $this->generate_link('overview/lateness_deduction', $log->to_outlet);
        } elseif ($log->type === 'SQL Payroll Settings') {
            $description .= $this->generate_link("profile/index/$log->user_id", $log->e_first_name);
            $description .= " $action1 <b>$action2</b> ";
            $description .= " of outlet ";
            $description .= $this->generate_link('exports/sql_payroll_settings', $log->to_outlet);
        } else {
            $description .= $this->generate_description_for_old_log($log);
        }

        $description .= " on $log->created_at";

        return $description;
    }

    private function generate_link($url, $text)
    {
        return '<a href="' . base_url($url) . '">' . $text . '</a>';
    }

    private function generate_description_for_old_log($log)
    {
        $description = "";

        return $description;
    }
}
