<?php

class Ot_days extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        if (is_null(get_user())) {
            redirect("welcome");
        }
    }

    function index()
    {
        if (!is_page_permitted('ot_days')) {
            redirect_if_not_permitted();
        }

        $data['current_user'] = $current_user = get_user();
        $data['pageTitle'] = "OT Sheet";
        $data['active_menu'] = "ot_days";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);
        $data["filters_form_action"] = "ot_days";
        render_all_filters($data);
        $data["selected_branch_id"] = 0;
        $data["selected_dep_id"] = 0;
        $data["selected_month"] = 0;
        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
		$data["positions"] = $this->db->query("SELECT id,title as name FROM positions WHERE company_id = $cid ORDER BY name")->result();
		$data["sections"] = $this->db->query("SELECT id,title as name FROM sections WHERE company_id = $cid ORDER BY name")->result();
        $permissions_level = $current_user["permissions_level"];
        //$where_branch_1 = '';
        $where_branch_2 = '';
        //$where_branch_3 = '';
        if ($permissions_level == "Outlet") {
            //$where_branch_1 = " AND branch_id = $bid ";
            $where_branch_2 = " AND id = $bid ";
            //$where_branch_3 = " AND permissions_level = 'Personal' ";
            if (empty($this->input->get("branch")) || $this->input->get("branch") != $bid) {
                redirect("ot_days?branch=$bid&" . getDateRangeFilterURLString($current_user['start_day']));
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

        if (!empty($this->input->get("dep"))) {
            $data["selected_dep_id"] = $this->input->get("dep");

            $where_filter = $where_filter . " department_id = " . $this->input->get("dep") . " AND " ;
        }

        if (!empty($this->input->get("sec"))) {
            $data["selected_sec_id"] = $this->input->get("sec");

            $where_filter = $where_filter . " section_id = " . $this->input->get("sec") . " AND " ;
        }

        if (!empty($this->input->get("daterange_filter"))) {
            $daterange = $this->input->get("daterange_filter");
            $formatted_dates = daterange_to_dates($daterange);
            $start_date = $formatted_dates['start_date'];
            $end_date = $formatted_dates['end_date'];
            $data["start_date_f"] = urlencode($start_date->format('d/m/Y'));
            $data["end_date_f"] = urlencode($end_date->format('d/m/Y'));
            $data['start_date_1'] = $start_date->format('m/d/Y');
            $data['end_date_1'] = $end_date->format('m/d/Y');
        } else {
            redirect("ot_days?" . getDateRangeFilterURLString($current_user['start_day']));
            return;
        }

		if(!empty($this->input->get("pos"))){

			$data["selected_pos_id"] = $this->input->get("pos");

			$where_filter = $where_filter . " position_id = " . $this->input->get("pos") . " AND " ;

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

        $result = $this->db->query("SELECT e.id, special_id,first_name,is_ot,is_early_ot, ot_type, ot_round, early_ot_round, use_half_hours_for_saturdays, round_first_hour_only, round_by_exact_hour, different_first_hour_rounding, worked_hours_ot_rd, worked_hours_ot_ph, deduct_hour_ot_rd, deduct_hour_ot_ph, worked_hours_ot_off, deduct_hour_ot_off, ignore_breaks_after_endtime, branch_id, inc_late_in, inc_late_break, inc_early_out, inc_short_hours, void_lateness_time_if_less_than, deduct_from_ot, deduct_from_ot_single, deduction_date, ot_group FROM employees e LEFT JOIN branches b on b.id = e.branch_id  INNER JOIN roles r ON e.role_id = r.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = e.id where $where_filter AND e.deleted_at is NULL AND employee_status = 'active' AND r.exclude_from_system = 'no'
			GROUP BY e.id, special_id,first_name,is_ot,is_early_ot, ot_type, ot_round, early_ot_round,round_first_hour_only, round_by_exact_hour, different_first_hour_rounding, worked_hours_ot_rd, worked_hours_ot_ph, deduct_hour_ot_rd, deduct_hour_ot_ph, worked_hours_ot_off, deduct_hour_ot_off, ignore_breaks_after_endtime, branch_id, inc_late_in, inc_late_break, inc_early_out, inc_short_hours, void_lateness_time_if_less_than, deduct_from_ot, deduct_from_ot_single, deduction_date, ot_group ORDER BY special_id LIMIT $skip,$limit")->result();

        $employees_ids = array();
        foreach ($result as $r) {
            $employees_ids[] = $r->id;
        }

        $first_day = $start_date->format('Y-m-d');
        $last_day = $end_date->format('Y-m-d');

        $company_working_hours_array = get_company_working_hours($cid);
        // $company_working_hours_decimal = toDecimal($company_working_hours);

        $company_ot_settings = get_company_ot_settings($cid);
        $company_early_ot_settings = get_company_early_ot_settings($cid);

        $result_list = get_result_list($employees_ids, $first_day, $last_day);
        $result_list_overnight = get_result_list_overnight($employees_ids, $first_day, $last_day);


        $is_ot_list = $this->db->select('employee_id, is_ot, ot_date as date')->from('ot_days')->where_in('employee_id', $employees_ids)->where('ot_date >=', $first_day)->where('ot_date <=', $last_day)->get()->result();
        $is_late_list = $this->db->select('employee_id, is_late, late_date as date')->from('late_days')->where_in('employee_id', $employees_ids)->where('late_date<=', $last_day)->where('late_date>=', $first_day)->get()->result();
        $is_late_break_list = $this->db->select('employee_id, is_late_break, late_break_date as date')->from('late_break_days')->where_in('employee_id', $employees_ids)->where('late_break_date<=', $last_day)->where('late_break_date>=', $first_day)->get()->result();
        $is_early_out_list = $this->db->select('employee_id, is_early_out, early_out_date as date')->from('early_out_days')->where_in('employee_id', $employees_ids)->where('early_out_date<=', $last_day)->where('early_out_date>=', $first_day)->get()->result();
        $manual_late_list = $this->db->select('employee_id, late_hours, date')->from('manual_late')->where_in('employee_id', $employees_ids)->where('date<=', $last_day)->where('date>=', $first_day)->get()->result();
        $manual_early_out_list = $this->db->select('employee_id, early_out, date')->from('manual_early_out')->where_in('employee_id', $employees_ids)->where('date<=', $last_day)->where('date>=', $first_day)->get()->result();
        $manual_late_break_list = $this->db->select('employee_id, late_hours_break, date')->from('manual_late_break')->where_in('employee_id', $employees_ids)->where('date<=', $last_day)->where('date>=', $first_day)->get()->result();
        $manual_ot_list = $this->db->select('employee_id, overtime, type, date')->from('manual_ot')->where_in('employee_id', $employees_ids)->where('date<=', $last_day)->where('date>=', $first_day)->get()->result();

        $days = array();
        if ($permissions_level == "Outlet") {
            $holidays_with_names = get_public_holidays_with_name($bid);
            $public_holidays = $holidays_with_names[0];
            $holiday_names = $holidays_with_names[1];

            $shifts = $this->db->select('id')->from('shifts')->where('branch_id', $bid)->where('is_leave', 'no')->get()->result();
        } else {
            $holidays_with_names = get_public_holidays_with_name();
            $public_holidays = $holidays_with_names[0];
            $holiday_names = $holidays_with_names[1];

            $shifts = $this->db->select('id')->from('shifts')->where('company_id', $cid)->where('is_leave', 'no')->get()->result();
        }

        $shift_ids = array();
        foreach ($shifts as $s) {
            $shift_ids[] = $s->id;
        }

        $approved_ot_list = get_approved_ot_list($shift_ids, $first_day, $last_day);

        $periodOfDates = new DatePeriod(
            new DateTime($first_day),
            new DateInterval('P1D'),
            (new DateTime($last_day))->add(new DateInterval('P1D'))
        );

        foreach ($periodOfDates as $date) {
            $d["date"] = $date->format('d');
            $month = $date->format('m');

            $d["day"] = $date->format('D');
            $d["holiday"] = false;
            $holiday_index = array_search($date->format('Y-m-d'), $public_holidays);

            if ($holiday_index > -1) {
                $d["holiday"] = true;
                $d["holiday_name"] = $holiday_names[$holiday_index];
            }

            $days[] = $d;
        }

        $employees = array();

        foreach ($result as $emp) {
            $company_working_hours = get_employee_working_hours($company_working_hours_array, $emp->id);
            $company_half_hours = $company_working_hours->half_hours;
            $company_half_hours_decimal = toDecimal($company_half_hours);
            $company_working_hours = $company_working_hours->working_hours;
            $company_working_hours_decimal = toDecimal($company_working_hours);

            $ot_settings = search_from_list_by_branch_id($company_ot_settings, $emp->branch_id);
            $early_ot_settings = search_from_list_by_branch_id($company_early_ot_settings, $emp->branch_id);
            $ot_type_data = $this->db->select("ot_weekly_hours, ot_type")->from("branches")->where("company_id", $cid)
            ->where("id", $emp->branch_id)->get()->row();
            $apply_overtime = $emp->is_ot == 1 ? true : false;
            $apply_early_overtime = $emp->is_early_ot == 1 ? true : false;
            $inc_late_in = $emp->inc_late_in == 1 ? true : false;
            $inc_late_break = $emp->inc_late_break == 1 ? true : false;
            $inc_early_out = $emp->inc_early_out == 1 ? true : false;

            $temp["id"] = $emp->id;

            $temp["special_id"] = $emp->special_id;

            $temp["first_name"] = $emp->first_name;

            $auto_approve_ot_list = $this->db->select('auto_approve_ot, date')->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->where('FIND_IN_SET(' . $emp->id . ',employees)>', 0)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();

            $shift_list = get_shift_list($emp->id, $first_day, $last_day);

            $replaced_ph_list = get_replaced_ph_list($emp->id, $first_day, $last_day);

            $days_wise = array();

            foreach ($periodOfDates as $periodDate) {
                $year = $periodDate->format('Y');
                $month = $periodDate->format('m');
                $j = $periodDate->format('d');
                $temp1['day'] = $year . "-" . $month . "-" . $j;
                $date_f = $j . "-" . $month . "-" . $year;

                $day_name = date('l', strtotime($temp1['day']));

                $is_replaced_ph = search_from_list($replaced_ph_list, $temp1['day']) ? true : false;
                $is_rest_day = false;
                $is_ph_day = false;
                $is_off_day = false;

                $rest_and_off_days = $this->db->select('rest_days,off_days')->from('branches')->where('id', $emp->branch_id)->get()->row();
                $rest_days = explode(",", $rest_and_off_days->rest_days);
                $off_days = explode(",", $rest_and_off_days->off_days);

                $temp1['id'] = $emp->id;

                $work_hours = "";
                $break_hours = "";
                $break_late_hours = "";
                $late_hours = "";
                $early_out = "";

                $is_ot = false;
                $is_manual = false;
                $is_late = true;
                $is_late_break = true;
                $is_early_out = true;
                $half_day = false;

                $overnight = false;
                $shift_check = $this->search_from_list($shift_list, $temp1['day']);
                $next_shift_check = $this->search_from_list($shift_list, date('Y-m-d', strtotime($temp1['day'] . ' +1 day')));
                $shift_hours = "";
                $is_shift = "false";
                if ($shift_check) {
                    $is_shift = "true";
                    $shift_hours = $shift_check->shift_hours;
                    if ($shift_check->half_day == "Yes") {
                        $half_day = true;
                    }
                }
                if ($shift_check && $shift_check->overnight == "Yes") {
                    $result = $this->search_clocking($result_list_overnight, $temp1['day'], $emp->id);
                    $overnight = true;
                    // if ($next_shift_check && $next_shift_check->overnight == "No") {
                        $result = remove_next_day_clockings($result, $shift_check, $next_shift_check);
                    // }
                } else {
                    $result = $this->search_clocking($result_list, $temp1['day'], $emp->id);
                    if (!$shift_check) {
                        $result = remove_duplicate_clockings($result, $temp1['day'], $shift_list, $result_list_overnight);
                    }
                }
                $result = get_clockings_from_previous_day($result, $result_list_overnight, $temp1['day'], $emp->id, $shift_list);

                $is_late_result = $this->search_from_list_by_id($is_late_list, $temp1['day'], $emp->id);
                if ($is_late_result) {
                    $is_late = $is_late_result->is_late == "Y" ? true : false;
                }
                $is_late_break_result = $this->search_from_list_by_id($is_late_break_list, $temp1['day'], $emp->id);
                if ($is_late_break_result) {
                    $is_late_break = $is_late_break_result->is_late_break == "Y" ? true : false;
                }
                $is_early_out_result = $this->search_from_list_by_id($is_early_out_list, $temp1['day'], $emp->id);
                if ($is_early_out_result) {
                    $is_early_out = $is_early_out_result->is_early_out == "Y" ? true : false;
                }

                $manual_ot = $this->search_from_list_by_id($manual_ot_list, $temp1['day'], $emp->id);

                $formatted_data = array();
                $last_out = "";
                foreach ($result as $key => $value) {
                    if ($value->name == "") {
                        $value->name = "N/A";
                    }

                    $formatted_data[] = $value;
                    // $value->total_time = total_time($value->clock_in_1, $value->clock_out_1);
                    $value->total_time = calculate_total_hours($value->clock_in_1, $value->clock_out_1, $value->start_time, $value->early_ot_start, $value->early_ot_end, $value->search_date);
                    if (array_key_exists($key + 1, $result)) {
                        $x = new stdClass();
                        $x->overtime_starts = $value->overtime_starts;
                        $x->clock_in = $value->clock_out;
                        $x->early_ot_start = $value->early_ot_start;
                        $x->early_ot_end = $value->early_ot_end;
                        $x->clock_in_1 = $value->clock_out_1;
                        $x->clock_out = $result[$key + 1]->clock_in;
                        $x->clock_out_1 = $result[$key + 1]->clock_in_1;
                        $x->total_time = total_time($result[$key + 1]->clock_in_1, $value->clock_out_1);
                        $x->name = "Break";
                        $formatted_data[] = $x;
                    } else {
                        $last_out = $value->clock_out_1;
                    }
                }
                if ($result) {
                    $v = $result[0];

                    if (in_array($temp1['day'], $public_holidays) || $is_replaced_ph) {
                        $is_ph_day = true;
                    } elseif (in_array($day_name, $off_days)) {
                        $is_off_day = true;
                    } elseif (in_array($day_name, $rest_days) || $v->name == "N/A") {
                        $is_rest_day = true;
                    }
                }

                $break_and_late_hours = calculate_break_and_late_hours($formatted_data, $v);
                $work_hours = $break_and_late_hours->work_hours;
                $break_hours = $break_and_late_hours->break_hours;
                $breaks_array = $break_and_late_hours->breaks_array;
                $shift_break_hours = $break_and_late_hours->shift_break_hours;
                $shift_breaks_array = $break_and_late_hours->shift_breaks_array;
                $after_ot_starts_break_hours = $break_and_late_hours->after_ot_starts_break_hours;

                foreach ($formatted_data as $key => $value) {
                    if ($key == 0) {
                        $manual_late = $this->search_from_list_by_id($manual_late_list, $temp1['day'], $emp->id);
                        if ($manual_late) {
                            $late_hours = $manual_late->late_hours;
                        } elseif (isset($v) && $v->is_leave != "" && $v->is_leave != "yes" && $v->void_late_in == "No") {
                            if ($v->grace_time != "") {
                                if ($overnight) {
                                    $grace_time = $temp1['day'] . " " . $v->grace_time . ":00";
                                    $grace_time_stamp = strtotime($grace_time);
                                    $mid_day = $temp1['day'] . " 12:00:00";
                                    $mid_day_stamp = strtotime($mid_day);
                                    if (in_array($shift_check->same_day_overnight, ['default', 'next'])) {
                                        if ($mid_day_stamp > $grace_time_stamp) {
                                            $grace_time_stamp += 24 * 3600;
                                        }
                                    }
                                    $clock_in_stamp = strtotime($v->clock_in_o);

                                    if ($clock_in_stamp > $grace_time_stamp) {
                                        $late_stamp = $clock_in_stamp - $grace_time_stamp;
                                        date_default_timezone_set('UTC');
                                        $late_hours = date('H:i', $late_stamp);
                                        date_default_timezone_set("Asia/Kuala_Lumpur");
                                    }
                                } elseif (intval(str_replace(":", "", $v->clock_in)) > intval(str_replace(":", "", $v->grace_time))) {
                                    $late_hours = sub_time($v->clock_in, $v->grace_time);
                                }
                            }
                        }
                    }
                } // inner loop end
                if (!$half_day) {
                    $manual_early_out = $this->search_from_list_by_id($manual_early_out_list, $temp1['day'], $emp->id);
                    if ($manual_early_out) {
                        $early_out = $manual_early_out->early_out;
                    } elseif ($last_out != "" && $shift_check && $shift_check->void_early_out == "No") {
                        $early_out = calculate_early_out($last_out, $shift_check->end_time, $temp1['day'], $overnight);
                    }
                }

                $break_not_taken = "00:00";
                $extra_break_not_taken = "00:00";
                if (isset($v)) {
                    $break_not_taken = calculate_break_not_taken($break_hours, $breaks_array, $v);
                }
                if ($work_hours != "" && $work_hours != "00:00") {
                    $work_hours = sub_time($work_hours, $break_not_taken);
                }
                if (isset($v)) {
                    $extra_break_not_taken = calculate_extra_break_not_taken($breaks_array, $v, $work_hours);
                }
                if ($work_hours != "" && $work_hours != "00:00") {
                    $work_hours = sub_time($work_hours, $extra_break_not_taken);
                }

                if (isset($v) && !$half_day) {
                    $manual_late_break = $this->search_from_list_by_id($manual_late_break_list, $temp1['day'], $emp->id);
                    if ($manual_late_break) {
                        $break_late_hours = $manual_late_break->late_hours_break;
                    } else {
                        $break_late_hours = calculate_break_late($break_hours, $breaks_array, $v, $work_hours, $is_shift);
                    }
                }

                $work_hours = add_deducted_time_in_work_hours($work_hours, $late_hours, $break_late_hours, $early_out, $inc_late_in, $inc_late_break, $inc_early_out, $is_late, $is_late_break, $is_early_out, $ot_type_data->ot_type);
                $round_of_ot = 1;
                if ($shift_check) {
                    $round_of_ot = $shift_check->round_off_ot;
                }
                $overtime_m = "00:00";
                if ($manual_ot) {
                    $overtime_m = $manual_ot->overtime;
                    $overtime_type = $manual_ot->type;
                    if ($overtime_type == "-") {
                        $overtime_m = "-" . $overtime_m;
                    }
                    $is_manual = true;
                }

                if (($is_rest_day && $emp->worked_hours_ot_rd) || ($is_ph_day && $emp->worked_hours_ot_ph) || ($is_off_day && $emp->worked_hours_ot_off)) {
                    $overtime = $work_hours;
                    $overtime = round_off_ot($overtime, $ot_settings, $emp->round_first_hour_only);
                } else {
                    $final_company_working_hours = $company_working_hours;
                    $final_company_working_hours_decimal = $company_working_hours_decimal;
                    if ($emp->ot_type == 'eight_hours' && $day_name == 'Saturday' && $emp->use_half_hours_for_saturdays) {
                        $final_company_working_hours = $company_half_hours;
                        $final_company_working_hours_decimal = $company_half_hours_decimal;
                    }
                    $overtime = calculate_final_overtime($result, $formatted_data, $date_f, $overnight, $apply_overtime, $apply_early_overtime, $work_hours, $final_company_working_hours, $emp->ot_type, $emp->ot_round, $emp->round_first_hour_only, $emp->round_by_exact_hour, $emp->different_first_hour_rounding, $ot_settings, $shift_hours, $round_of_ot, $final_company_working_hours_decimal, $emp->early_ot_round, $early_ot_settings);
                    if ($emp->ignore_breaks_after_endtime == 1) {
                        $overtime = add_time($overtime, "-" . $after_ot_starts_break_hours);
                        if ($overtime == "00:00") {
                            $overtime = "";
                        }
                    }
                }

                $is_ot = $this->search_from_list_by_id($is_ot_list, $temp1['day'], $emp->id);

                if ($is_ot) {
                    $check = $is_ot->is_ot == "Y" ? true : false;
                } else {
                    $check = get_is_ot_status($approved_ot_list, $shift_check, $temp1['day'], $emp->id);
                }

                $is_ot = $check ? true : false;

                if (($is_rest_day && $emp->deduct_hour_ot_rd) || ($is_ph_day && $emp->deduct_hour_ot_ph) || ($is_off_day && $emp->deduct_hour_ot_off)) {
                    $overtime = deduct_hour_from_ot_rd($overtime);
                }

                if ($cid == "102") {
                    if ($emp->ot_group == "hours" && toDecimal($overtime) > 3) {
                        $overtime = add_time($overtime, "-00:30");
                    }
                }

                $overtime = ot_deduction_from_shift_settings($overtime, $shift_check);

                $temp1['overtime'] = (empty($overtime) || $overtime == "00:00") ? "-" : $overtime;

                $temp1['overtime_m'] = (empty($overtime_m) || $overtime_m == "00:00") ? "" : $overtime_m;

                $auto_approve_ot = $this->search_from_list($auto_approve_ot_list, $temp1['day']);
                if ($auto_approve_ot) {
                    $auto_approve_ot = $auto_approve_ot->auto_approve_ot;
                } else {
                    $auto_approve_ot = "No";
                }

                $temp1['is_ot'] = $is_ot;

                $temp1['is_manual'] = $is_manual;

                $days_wise[] = $temp1;
            }

            $temp["ot_data"] = $days_wise;

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

        $data["filters"] = $this->load->view('filters', $data, true);
        $this->load->view('ot_days', $data);

        $this->load->view('footer');
    }



    function change_status()
    {

        $request = $this->input->post();

        $employee_id = $request['id'];

        $ot_date = $request['day'];

        $is_ot = ($request['is_ot'] == 1) ? 'Y' : 'N';



        $data = array('employee_id' => $employee_id,

            'ot_date' => $ot_date,

            'is_ot' => $is_ot);

        $this->db->replace('ot_days', $data);
    }

    function approve_all_ot()
    {

        $request = $this->input->post();
        $employee_id = $request['id'];
        $start = $request['start'];
        $end = $request['end'];
        $first_day = date('Y-m-d', strtotime($start));
        $last_day = date('Y-m-d', strtotime($end));
        $period = new DatePeriod(
            new DateTime($first_day),
            new DateInterval('P1D'),
            (new DateTime($last_day))->add(new DateInterval('P1D'))
        );
        foreach ($period as $date) {
            $ot_date = $date->format('Y-m-d');
            $data = array('employee_id' => $employee_id,
                'ot_date' => $ot_date,
                'is_ot' => 'Y');
            $this->db->replace('ot_days', $data);
        }
    }

    function reject_all_ot()
    {
        $request = $this->input->post();
        $employee_id = $request['id'];
        $start = $request['start'];
        $end = $request['end'];
        $first_day = date('Y-m-d', strtotime($start));
        $last_day = date('Y-m-d', strtotime($end));
        $period = new DatePeriod(
            new DateTime($first_day),
            new DateInterval('P1D'),
            (new DateTime($last_day))->add(new DateInterval('P1D'))
        );
        foreach ($period as $date) {
            $ot_date = $date->format('Y-m-d');
            $data = array('employee_id' => $employee_id,
                'ot_date' => $ot_date,
                'is_ot' => 'N');
            $this->db->replace('ot_days', $data);
        }
    }



    function count_overtime($date, $result, $overnight, $apply_overtime, $apply_early_overtime, $company_working_hours, $ot_type, $ot_round, $round_first_hour_only, $ot_settings, $shift_hours, $shift, $company_working_hours_decimal)
    {

        $date_obj = DateTime::createFromFormat('Y-m-d', $date);

        $date_f = $date_obj->format('d-m-Y');


        $overtime = "";
        $early_overtime = "";

        $formatted_data = array();

        foreach ($result as $key => $value) {
            $formatted_data[] = $value;

            // $value->total_time = total_time($value->clock_in_1, $value->clock_out_1);
            $value->total_time = calculate_total_hours($value->clock_in_1, $value->clock_out_1, $value->start_time, $value->early_ot_start, $value->early_ot_end, $value->search_date);

            if (array_key_exists($key + 1, $result)) {
                $x = new stdClass();

                $x->overtime_starts = $value->overtime_starts;

                $x->clock_in = $value->clock_out;

                $x->early_ot_start = $value->early_ot_start;

                $x->early_ot_end = $value->early_ot_end;

                $x->clock_in_1 = $value->clock_out_1;

                $x->clock_out = $result[$key + 1]->clock_in;

                $x->clock_out_1 = $result[$key + 1]->clock_in_1;

                $x->total_time = total_time($result[$key + 1]->clock_in_1, $value->clock_out_1);

                $x->name = "Break";

                $formatted_data[] = $x;
            }
        }
        if ($result) {
            $v = $result[0];
        }

        $break_and_late_hours = calculate_break_and_late_hours($formatted_data, $v);
        $work_hours = $break_and_late_hours->work_hours;
        $break_hours = $break_and_late_hours->break_hours;
        $breaks_array = $break_and_late_hours->breaks_array;

        $break_not_taken = "00:00";
        $extra_break_not_taken = "00:00";
        if (isset($v)) {
            $break_not_taken = calculate_break_not_taken($break_hours, $breaks_array, $v);
        }
        if ($work_hours != "" && $work_hours != "00:00") {
            $work_hours = sub_time($work_hours, $break_not_taken);
        }
        if (isset($v)) {
            $extra_break_not_taken = calculate_extra_break_not_taken($breaks_array, $v, $work_hours);
        }
        if ($work_hours != "" && $work_hours != "00:00") {
            $work_hours = sub_time($work_hours, $extra_break_not_taken);
        }
        $round_of_ot = 1;
        if ($shift) {
            $round_of_ot = $shift->round_off_ot;
        }

        $overtime = (empty($overtime)) ? "-" : $overtime;

        return $overtime;
    }



    public function add_time($time1, $time2)
    {

        if ($time2 == null) {
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





    function search_from_list($list, $date)
    {
        foreach ($list as $l) {
            if ($l->date == $date) {
                return $l;
            }
        }
        return array();
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
