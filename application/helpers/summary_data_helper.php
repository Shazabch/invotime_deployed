<?php

/**
 * Genaric function to generate summaries
 *
 * @param string $emp_id Employee ID
 * @param string $first_day Mysql format start date
 * @param string $last_day Mysql format end date
 * @param string $summary_type
 * @param StdClass|boolean $employee Employee object
 * @param array|boolean $result_list Clocking data
 * @param array|boolean $result_list_overnight Clocking data
 * @param array|boolean $company_working_hours Company working hours
 * @param array|boolean $public_holidays Company public holidays
 * @param array|boolean $ot_settings Company overtime settings
 * @param array|boolean $approved_ot_list Approved overtime list
 * @param array|boolean $rest_days Branch rest days
 * @param null|int|string $cid Company ID
 * @param array $worked_rest_days_array
 * @param array $worked_holidays_array
 * @param array $unpaid_leaves_absent_days
 * @return array
 * @param array | boolean $result_list_preshift Clocking data for pre-shift
 */
function calculate_summary_data($emp_id, $first_day, $last_day, $summary_type = "summary", $employee = false, $result_list = false, $result_list_overnight = false, $company_working_hours = false, $public_holidays = false, $ot_settings = false, $early_ot_settings = false, $approved_ot_list = false, $rest_and_off_days = false, $cid = null, &$worked_rest_days_array = [], &$worked_off_days_array = [], &$worked_holidays_array = [], &$unpaid_leaves_absent_days = [], $clockings_news = null, $clockings_news_overnight = null, &$paid_leaves_array = [], &$daily_ot_array = [], &$daily_late_array = [], $result_list_preshift = false, $clockings_news_preshift = null)
{
    $companies_allowed_for_monthly_ot = companies_allowed_for_monthly_ot();
    $tsf_custom_summary = false;
    $custom_in_outs = false;

    $ci = &get_instance();
    $current_user = get_user();
    if (is_null($cid))
        $cid = $current_user["company_id"];

    if (is_null($cid) && $employee !== false)
        $cid = $employee->company_id;

    $data = [];
    $data['tsf_custom_summary'] = $tsf_custom_summary;
    $data['custom_in_outs'] = $custom_in_outs;
    // company ID (Goodnite International Sdn Bhd) for which custom In's Out's added in summary
    if ($cid == 223 || $cid == 259)
        $custom_in_outs = true;

    // TSF01 (146) company for which custom summary added in summary
    if ($cid == 146)
        $tsf_custom_summary = true;

    if ($employee === false)
        $employee = $ci->db->select('e.id as emp_id,first_name,special_id,d.name as department,p.title as position,is_ot,is_early_ot,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,round_by_exact_hour,different_first_hour_rounding,worked_hours_ot_rd,worked_hours_ot_ph,deduct_hour_ot_rd,deduct_hour_ot_ph,worked_hours_ot_off,deduct_hour_ot_off,ignore_breaks_after_endtime,inc_late_in,inc_late_break,inc_early_out,inc_short_hours,void_lateness_time_if_less_than,branch_id,deduct_from_ot,deduct_from_ot_single,deduction_date,min_worked_hours_meal,ta_rate,ma_rate,ca_rate,spa_rate,aca_rate,aa_rate,nsa_rate,fl_rate,cw_rate,mo_rate,shift1_rate,shift2_rate,shift3_rate,food_rate,b.name as branch,ot_group, att_all_code, att_all_desc, att_all_amount, is_att_all')->from('employees e')->join('departments d', 'd.id = e.department_id', 'left')->join('positions p', 'p.id = e.position_id', 'left')->join('branches b', 'b.id = e.branch_id', 'left')->where('e.id', $emp_id)->get()->row();

    if ($company_working_hours === false) {
        $company_working_hours = get_company_working_hours($cid);
    }

    $company_working_hours = get_employee_working_hours($company_working_hours, $emp_id);
    $company_half_hours = $company_working_hours->half_hours;
    $company_half_hours_decimal = toDecimal($company_half_hours);
    $company_working_hours = $company_working_hours->working_hours;
    $company_working_hours_decimal = toDecimal($company_working_hours);

    if ($rest_and_off_days === false) {
        $rest_and_off_days = $ci->db->select('rest_days,off_days')->from('branches')->where('id', $employee->branch_id)->get()->row();
        $rest_days = explode(",", $rest_and_off_days->rest_days);
        $off_days = explode(",", $rest_and_off_days->off_days);
    } else {
        $rest_days = explode(",", search_from_rest_days($rest_and_off_days, $employee->branch_id));
        $off_days = explode(",", search_from_off_days($rest_and_off_days, $employee->branch_id));
    }
    $public_holidays = get_public_holidays_mine($emp_id, $employee->branch_id, $first_day, $last_day);

    if ($summary_type === "summary")
        $public_holidays_names = get_public_holidays_with_name($employee->branch_id, $first_day, $last_day, $emp_id);
    // var_dump($public_holidays, $public_holidays_names);die;
    $apply_overtime = $employee->is_ot == 1 ? true : false;
    $apply_early_overtime = $employee->is_early_ot == 1 ? true : false;

    $inc_late_in = $employee->inc_late_in == 1 ? true : false;
    $inc_late_break = $employee->inc_late_break  == 1 ? true : false;
    $inc_early_out = $employee->inc_early_out == 1 ? true : false;
    $inc_short_hours = $employee->inc_short_hours == 1 ? true : false;
    $void_minutes = $employee->void_lateness_time_if_less_than;

    if ($employee->deduct_from_ot_single != "not_sure") {
        $deduct_from_ot = $employee->deduct_from_ot_single == "yes" ? true : false;
    } else {
        $deduct_from_ot = $employee->deduct_from_ot == 1 ? true : false;
    }

    $deduction_date = $employee->deduction_date;

    $employee->deduct_from_ot = $deduct_from_ot;

    $period = new DatePeriod(
        new DateTime($first_day),
        new DateInterval('P1D'),
        (new DateTime($last_day))->add(new DateInterval('P1D'))
    );

    $total = "00:00";
    $work = "00:00";
    $shift_hours_total = "00:00";
    $break = "00:00";
    $late = "00:00";
    $total_late = "00:00";
    $late_count = 0;
    $break_late = "00:00";
    $total_days = 0;
    $total_meal_days = 0;
    $total_trip_a = 0;
    $total_trip_b = 0;
    $paid_leaves = 0;
    $unpaid_leaves = 0;
    $full_unpaid_leaves = 0;
    $allowance_leaves = 0;
    $total_holidays = 0;
    $worked_holidays = 0;
    $worked_rest_days = 0;
    $worked_off_days = 0;
    $worked_days = 0;
    $lsk_non_worked_days = 0;
    $ln01_waived_days = 0;
    $working_days = 0;
    $absent_days = 0;
    $total_short = "00:00";
    $total_early = "00:00";
    $month_overtime = "00:00";
    $month_overtime_ph_x2 = "00:00";
    $month_overtime_ph_x3 = "00:00";
    $month_overtime_ph = "00:00";
    $month_overtime_rd = "00:00";
    $month_overtime_off = "00:00";
    $total_shift_hours = "00:00";
    // did today 29-07-2021
    $total_early_count = 0;
    $total_half_day_paid = 0;
    $total_half_day_unpaid = 0;
    $total_full_day_paid = 0;
    $total_medical_leaves = 0;
    $total_break_late = 0;
    $total_missing_in_out = 0;
    $total_absent_unpaid = 0;
    $total_early_late = 0;
    $total_off_days = 0;
    $total_late_only_count = 0;
    $total_rest_days_used = 0;

    $total_bmi_ot = 0;
    $total_bmi_ot_sunday = 0;
    $total_bmi_ph_1 = 0;
    $total_bmi_ph_2 = 0;
    $total_bmi_ta = 0;
    $total_bmi_ma = 0;
    $total_bmi_ca = 0;
    $total_bmi_spa = 0;
    $total_bmi_aca = 0;
    $total_bmi_fl = 0;
    $total_bmi_cw = 0;
    $total_bmi_mo = 0;
    $total_bmi_shift1 = 0;
    $total_bmi_shift2 = 0;
    $total_bmi_shift3 = 0;

    $bmi_attendance_allowance = true;
    $bmi_late_more_than_10 = 0;

    $gbr_attendance_allowance = true;
    $gbr_night_shifts = 0;
    $monthly_nsa_count = 0;
    $monthly_dsa_count = 0;

    $food_allowance_days = 0;
    $ln01_attendance_allowance_days = 0;

    $days_settings = $ci->db->select('from_hour,to_hour,days')->from('days_settings')->where('company_id', $cid)->get()->result();

    if ($result_list === false)
        $result_list = get_result_list(array($emp_id), $first_day, $last_day);
    if ($result_list_overnight === false)
        $result_list_overnight = get_result_list_overnight(array($emp_id), $first_day, $last_day);
    if ($result_list_preshift === false)  // Add this
        $result_list_preshift = get_result_list_preshift(array($emp_id), $first_day, $last_day);


    if ($approved_ot_list === false) {
        $shifts = $ci->db->select('id')->from('shifts')->where('branch_id', $employee->branch_id)->where('is_leave', 'no')->get()->result();
        $shift_ids = array(0);
        foreach ($shifts as $s) {
            $shift_ids[] = $s->id;
        }
        $approved_ot_list = get_approved_ot_list($shift_ids, $first_day, $last_day);
    }

    $is_ot_list = get_is_ot_list($emp_id, $first_day, $last_day);

    $is_offense_list = [];
    if ($summary_type === "merit_system")
        $is_offense_list = get_is_offense_list($emp_id, $first_day, $last_day);

    $is_late_list = get_is_late_list($emp_id, $first_day, $last_day);

    $is_late_break_list = get_is_late_break_list($emp_id, $first_day, $last_day);

    $is_early_out_list = get_is_early_out_list($emp_id, $first_day, $last_day);

    $manual_late_list = get_manual_late_list($emp_id, $first_day, $last_day);

    $manual_late_break_list = get_manual_late_break_list($emp_id, $first_day, $last_day);

    $shift_list = get_shift_list($emp_id, $first_day, $last_day);

    $remark_list = get_remark_list($emp_id, $first_day, $last_day);

    $staff_remark_list = get_staff_remark_list($emp_id, $first_day, $last_day);

    $manual_ot_list = get_manual_ot_list($emp_id, $first_day, $last_day);

    $manual_early_out_list = get_manual_early_out_list($emp_id, $first_day, $last_day);

    $manual_short_hours_list = get_manual_short_hours_list($emp_id, $first_day, $last_day);

    $trip_a_list = get_trip_a_list($emp_id, $first_day, $last_day);

    $trip_b_list = get_trip_b_list($emp_id, $first_day, $last_day);

    if ($cid == 66) {
        $manual_ta_list = get_manual_ta_list($emp_id, $first_day, $last_day);
        $manual_ma_list = get_manual_ma_list($emp_id, $first_day, $last_day);
        $manual_ca_list = get_manual_ca_list($emp_id, $first_day, $last_day);
        $manual_spa_list = get_manual_spa_list($emp_id, $first_day, $last_day);
        $manual_aca_list = get_manual_aca_list($emp_id, $first_day, $last_day);
        $manual_fl_list = get_manual_fl_list($emp_id, $first_day, $last_day);
        $manual_cw_list = get_manual_cw_list($emp_id, $first_day, $last_day);
        $manual_mo_list = get_manual_mo_list($emp_id, $first_day, $last_day);
        $manual_shift1_list = get_manual_shift1_list($emp_id, $first_day, $last_day);
        $manual_shift2_list = get_manual_shift2_list($emp_id, $first_day, $last_day);
        $manual_shift3_list = get_manual_shift3_list($emp_id, $first_day, $last_day);
    }


    if ($ot_settings === false) {
        $ot_settings = get_ot_settings($employee->branch_id);
    } else {
        $ot_settings = search_from_list_by_branch_id($ot_settings, $employee->branch_id);
    }

    if ($early_ot_settings === false) {
        $early_ot_settings = get_early_ot_settings($employee->branch_id);
    } else {
        $early_ot_settings = search_from_list_by_branch_id($early_ot_settings, $employee->branch_id);
    }

    $ot_type_data = $ci->db->select("ot_weekly_hours, ot_type")->from("branches")->where("company_id", $cid)
        ->where("id", $employee->branch_id)->get()->row();

    $replacement_leaves_list = get_replacement_leaves_list($emp_id, $first_day, $last_day);

    $replaced_ph_list = get_replaced_ph_list($emp_id, $first_day, $last_day);
    $employee_off_days_list = get_off_days_list($emp_id, $first_day, $last_day);

    $jl01_paid_leaves = $paid_leaves_array;

    $last_ids = [];
    foreach ($period as $date) {
        $obj = new stdClass();
        $obj->date = $date->format('Y-m-d');
        $obj->day_name = $date->format('l');
        $date_f = $date->format('d-m-Y');
        $date_string = $date->format('d/m D');
        $obj->date_string = $date_string;
        $obj->shift_hours = "";
        $obj->full_shift_hours = "";
        $obj->is_extra_ot = false;
        if ($tsf_custom_summary) {
            $address_distance = $ci->db->select('address, scan_distance')->from('clockings_news')
                ->where('employee_id', $emp_id)
                ->where('datetime >=', $obj->date . ' 00:00:00')
                ->where('datetime <=', $obj->date . ' 23:59:59')
                ->limit(1)
                ->get()
                ->row();
            $obj->location = isset($address_distance->address) ? $address_distance->address : "";
            $obj->distance = isset($address_distance->scan_distance) ? $address_distance->scan_distance : "";
        }
        $replacement = is_replacement($replacement_leaves_list, $obj->date);

        $is_ot = false;
        $is_late = true;
        $is_late_break = true;
        $is_early_out = true;
        $overnight = false;
        $is_shift = false;
        // Initialize preshift variable
        $preshift = false;

        $shift_check = search_from_list($shift_list, $obj->date);
        $next_shift_check = search_from_list($shift_list, add_days_to_date($date, 1)->format("Y-m-d"));
        // Get previous day shift for preshift logic
        $prev_shift_check = search_from_list($shift_list, add_days_to_date($date, -1)->format("Y-m-d"));
        $obj->shift_check = $shift_check;
        $obj->shift_name = "";
        $obj->acting_code = "";
        $obj->cut_off_time = "";
        $acting_codes = [];
        $half_day = false;
        if ($shift_check) {
            $is_shift = true;
            if ($shift_check->half_day == "Yes") {
                $half_day = true;
            }
            $obj->shift_hours = $shift_check->shift_hours;
            $obj->full_shift_hours = $shift_check->shift_hours;
            $total_shift_hours = add_time($total_shift_hours, $obj->shift_hours);
            $obj->shift_name = $shift_check->name;
            $obj->acting_code = str_replace(",", "|", $shift_check->acting_code);
            $obj->cut_off_time = $shift_check->cut_off_time;
            $obj->is_preshift = $shift_check->is_preshift;
            $obj->pre_shift_buffer = $shift_check->pre_shift_buffer ?? ($shift_check->is_preshift == "Yes" ? 60 : null); // Set default buffer to 60 if it's a preshift and pre_shift_buffer is not set
            $acting_codes = explode(",", $shift_check->acting_code);

            if ($shift_check->code == "N") {
                $gbr_night_shifts++;
            }
        }

        // echo "here";die;
        if ($shift_check && $shift_check->overnight == "Yes") {
            $result = search_clocking_by_id($result_list_overnight, $obj->date, $emp_id);
            $overnight = true;

            // if ($next_shift_check && $next_shift_check->overnight == "No") {
            $result = remove_next_day_clockings($result, $shift_check, $next_shift_check);
            // }
        } elseif ($shift_check && $shift_check->is_preshift == "Yes") {  // Add preshift check

            $result = search_clocking_by_id($result_list_preshift, $obj->date, $emp_id);
            $preshift = true;
            // You may need similar logic for preshift if required
            $result = remove_previous_day_clockings($result, $shift_check, $prev_shift_check);
        } else {
            $result = search_clocking_by_id($result_list, $obj->date, $emp_id);
            // if (!$shift_check || ($shift_check && $shift_check->overnight == "No")) {
            $result = remove_duplicate_clockings($result, $obj->date, $shift_list, $result_list_overnight);
            // }
        }
        $result = get_clockings_from_previous_day($result, $result_list_overnight, $obj->date, $emp_id, $shift_list);
        $result = get_clockings_from_next_day_for_preshift($result, $result_list_preshift, $obj->date, $emp_id, $shift_list); // New function for preshift


        $obj->overnight = $overnight ? "true" : "false";
        $obj->preshift = $preshift ? "true" : "false";  // Add preshift flag
        $obj->is_shift = $is_shift ? "true" : "false";
        $obj->is_leave = $shift_check && $shift_check->is_leave == "yes";
        $obj->is_paid_leave = $shift_check && $shift_check->is_leave == "yes" && $shift_check->is_paid == "yes";
        $obj->is_unpaid_leave = $shift_check && $shift_check->is_leave == "yes" && $shift_check->is_paid == "no";

        $is_replaced_ph = search_from_list($replaced_ph_list, $obj->date) ? true : false;
        $is_employee_off_day = search_from_list($employee_off_days_list, $obj->date) ? true : false;
        $obj->is_replaced_ph = $is_replaced_ph;
        $obj->is_employee_off_day = $is_employee_off_day;
        $obj->merit_is_half_day_paid = false;
        $obj->merit_is_full_day_paid = false;
        $obj->merit_is_half_day_unpaid = false;
        $obj->merit_is_medical_leave = false;
        $obj->merit_is_break_late = false;
        $obj->merit_is_missing_in_out = false;
        $obj->merit_is_absent_unpaid = false;
        $obj->merit_is_offense = $current_user['is_merit_approved'] === '1' ? true : false;
        $obj->merit_is_early_out = false;
        $obj->merit_is_late = false;

        // if ($summary_type === "merit_system") {
        if ($overnight) {
            $clockings_news_result = search_clocking_by_id($clockings_news_overnight, $obj->date, $emp_id);
        } elseif ($preshift) {  // Add preshift condition
            $clockings_news_result = search_clocking_by_id($clockings_news_preshift, $obj->date, $emp_id);
        } else {
            $clockings_news_result = search_clocking_by_id($clockings_news, $obj->date, $emp_id);
        }

        $clockings_news_result = remove_last_ids($clockings_news_result, $last_ids);
        $last_ids = [];

        if (!empty($clockings_news_result)) {
            if ($clockings_news_result[0]->type === "out" || $clockings_news_result[0]->add_by_admin == 1) {
                $obj->merit_is_missing_in_out = true;
            }
            if (end($clockings_news_result)->type === "in" || end($clockings_news_result)->add_by_admin == 1) {
                $obj->merit_is_missing_in_out = true;
            }
        }
        $missing_in_out_counter = 0;
        foreach ($clockings_news_result as $key => $value) {
            $last_ids[] = $value->id;
            if (($missing_in_out_counter % 2 === 0 && $value->type === "out") || $value->add_by_admin == 1) {
                $obj->merit_is_missing_in_out = true;
                $missing_in_out_counter++;
            } else if (($missing_in_out_counter % 2 === 1 && $value->type === "in") || $value->add_by_admin == 1) {
                $obj->merit_is_missing_in_out = true;
                $missing_in_out_counter++;
            }
            $missing_in_out_counter++;
        }

        if ($obj->merit_is_missing_in_out === true) $total_missing_in_out++;
        // }

        if ($shift_check && $shift_check->is_rest_day) {
            if (in_array($obj->date, $public_holidays)) {
                $total_rest_days_used += $shift_check->public_holiday_deduction;
            } else if (in_array($obj->day_name, ["Saturday", "Sunday"])) {
                $total_rest_days_used += $shift_check->weekend_deduction;
            } else {
                $total_rest_days_used += $shift_check->weekday_deduction;
            }
        }

        if (!$shift_check && empty($result) && $obj->date <= date('Y-m-d')) {
            $total_off_days++;
        }

        /** days calcuation */
        if (!in_array($obj->date, $public_holidays) && !in_array($obj->day_name, $rest_days) && !$obj->is_replaced_ph && !in_array($obj->day_name, $off_days) && !($shift_check && $shift_check->is_rest_day)) {
            $check = false;
            if ($shift_check) {
                $add_day = 1;
                if ($shift_check->half_day == "Yes") {
                    $add_day = 0.5;
                }
                if ($shift_check->is_leave == "yes" && $shift_check->is_paid == "yes") {
                    $paid_leaves += $add_day;
                    if (in_array($shift_check->code, get_attendance_allowance_leave_codes())) {
                        $allowance_leaves += $add_day;
                    }
                    $paid_leaves_array[$date->format("d/m/Y")][] = [
                        "employee_special_id" => $employee->special_id,
                        "paid_leave" => $add_day,
                        "branch_id" => $employee->branch_id,
                        'leave_type' => $shift_check->code
                    ];
                    $check = true;
                    if (stripos($shift_check->name, 'medical leave') !== false) {
                        $obj->merit_is_medical_leave = true;
                        $total_medical_leaves++;

                        // set bmi attendance allowance to false if more than 1 medical leave
                        if ($total_medical_leaves > 1) {
                            $bmi_attendance_allowance = false;
                        }

                        // set gbr attendance allowance to false if any medical leave
                        $gbr_attendance_allowance = false;

                        $lsk_non_worked_days++;
                    }
                    if ($add_day === 0.5) {
                        $obj->merit_is_half_day_paid = true;
                        $total_half_day_paid++;
                    } else if (stripos($shift_check->name, 'medical leave') === false) {
                        $obj->merit_is_full_day_paid = true;
                        $total_full_day_paid++;
                    }
                } else if ($shift_check->is_leave == "yes" && $shift_check->is_paid == "no") {
                    $unpaid_leaves += $add_day;
                    if (in_array($shift_check->code, get_attendance_allowance_leave_codes())) {
                        $allowance_leaves += $add_day;
                    }
                    if ($add_day === 0.5) {
                        $obj->merit_is_half_day_unpaid = true;
                        $total_half_day_unpaid++;
                    } else {
                        $obj->merit_is_absent_unpaid = true;
                        $total_absent_unpaid++;
                        $full_unpaid_leaves++;
                    }
                    $check = true;
                    $unpaid_leaves_absent_days[$date->format("d/m/Y")][] = [
                        "employee_special_id" => $employee->special_id,
                        "unpaid_leave" => $add_day,
                        "branch_id" => $employee->branch_id,
                        "type" => "unpaid_leave"
                    ];

                    // set bmi attendance allowance to false if there is any unpaid leave
                    $bmi_attendance_allowance = false;

                    $lsk_non_worked_days++;
                }
                $working_days++;
            }
            if (!$check && empty($result) && $shift_check) {
                if ($obj->date <= date('Y-m-d')) {
                    if ($replacement) {
                        if ($replacement->to !== $obj->date) {
                            if ($shift_check->start_time != NULL && $shift_check->end_time != NULL) {
                                $absent_days++;
                                $obj->merit_is_absent_unpaid = true;
                                $total_absent_unpaid++;
                                $unpaid_leaves_absent_days[$date->format("d/m/Y")][] = [
                                    "employee_special_id" => $employee->special_id,
                                    "unpaid_leave" => $add_day,
                                    "branch_id" => $employee->branch_id,
                                    "type" => "absent"
                                ];
                            }
                        }
                    } else {
                        if ($shift_check->start_time != NULL && $shift_check->end_time != NULL) {
                            $absent_days++;
                            $obj->merit_is_absent_unpaid = true;
                            $total_absent_unpaid++;
                            $unpaid_leaves_absent_days[$date->format("d/m/Y")][] = [
                                "employee_special_id" => $employee->special_id,
                                "unpaid_leave" => $add_day,
                                "branch_id" => $employee->branch_id,
                                "type" => "absent"
                            ];
                        }
                    }

                    // set bmi attendance allowance to false if there is any absent day
                    $bmi_attendance_allowance = false;

                    // set gbr attendance allowance to false if there is any absent day
                    $gbr_attendance_allowance = false;

                    $lsk_non_worked_days++;
                }
            }
        }

        if ($cid == 196 && $shift_check && $shift_check->is_leave == "yes") {
            if (in_array($obj->date, $public_holidays)) {
                $deduction = $shift_check->public_holiday_deduction;
            } else if (in_array($obj->day_name, ["Saturday", "Sunday"])) {
                $deduction = $shift_check->weekend_deduction;
            } else {
                $deduction = $shift_check->weekday_deduction;
            }
            $jl01_paid_leaves[$date->format("d/m/Y")][] = [
                "employee_special_id" => $employee->special_id,
                "paid_leave" => $deduction,
                "branch_id" => $employee->branch_id,
                'leave_type' => $shift_check->code
            ];
        }

        $total_hours = "";
        $work_hours = "";
        $break_hours = "";
        $late_hours = "";
        $break_late_hours = "";
        $early_out = "";
        $short_hours = "";
        $tripA = 0;
        $tripB = 0;
        $total_clockings = count($result);
        $formatted_data = array();
        $is_ot_result = search_from_list($is_ot_list, $obj->date);
        if ($is_ot_result) {
            $is_ot = $is_ot_result->is_ot == "Y" ? true : false;
        } else {
            $is_ot = get_is_ot_status($approved_ot_list, $shift_check, $obj->date, $emp_id, $total_clockings, $cid);
        }

        $is_late_result = search_from_list($is_late_list, $obj->date);
        if ($is_late_result) {
            $is_late = $is_late_result->is_late == "Y" ? true : false;
        }

        $is_late_break_result = search_from_list($is_late_break_list, $obj->date);
        if ($is_late_break_result) {
            $is_late_break = $is_late_break_result->is_late_break == "Y" ? true : false;
        }

        $is_early_out_result = search_from_list($is_early_out_list, $obj->date);
        if ($is_early_out_result) {
            $is_early_out = $is_early_out_result->is_early_out == "Y" ? true : false;
        }

        $is_offense_result = search_from_list($is_offense_list, $obj->date);
        if ($is_offense_result) {
            $obj->merit_is_offense = $is_offense_result->is_offense == "Y" ? true : false;
        }

        // array for showing in, out, in, out
        $in_outs = [];
        $in_outs_ids = [];

        $last_out = "";
        foreach ($result as $key => $value) {
            // pushing clock_in and clock_out in in_outs
            $in_outs_ids[] = $value->clock_in_id;
            $in_outs_ids[] = $value->clock_out_id;
            $in_outs[] = $value->clock_in;
            $in_outs[] = $value->clock_out;

            if ($key == 0 && $value->shift_remark != null && $value->shift_remark != "") {
                $value->remark = $value->shift_remark;
            }
            // $value->total_time = total_time($value->clock_in_1, $value->clock_out_1);
            $value->total_time = calculate_total_hours($value->clock_in_1, $value->clock_out_1, $value->start_time, $value->early_ot_start, $value->early_ot_end, $value->search_date);
            // var_dump($value->total_time);
            if ($value->name == "") {
                $value->name = "N/A";
            }
            if ($value->code == "") {
                $value->code = "N/A";
            }
            $value->is_break = false;

            $formatted_data[] = $value;
            if (array_key_exists($key + 1, $result)) {
                $x = new stdClass();
                $x->day_f = $value->day_f;
                $x->overtime_starts = $value->overtime_starts;
                $x->early_ot_start = $value->early_ot_start;
                $x->early_ot_end = $value->early_ot_end;
                $x->grace_time = $value->grace_time;
                $x->clock_in = $value->clock_out;
                $x->clock_in_1 = $value->clock_out_1;
                $x->clock_out = $result[$key + 1]->clock_in;
                $x->clock_out_1 = $result[$key + 1]->clock_in_1;
                $x->name = "Break";
                $x->code = "Break";
                $x->is_break = true;
                $x->reason = "";
                $x->remark = "";
                $x->staff_remark = "";
                $x->is_ot = $is_ot;
                $x->total_time = total_time($result[$key + 1]->clock_in_1, $value->clock_out_1);
                $formatted_data[] = $x;
            } else {
                $last_out = $value->clock_out_1;
            }
        } // inner loop end
        if (!$half_day) {
            $manual_early_out = search_from_list($manual_early_out_list, $obj->date);
            if ($manual_early_out) {
                $early_out = $manual_early_out->early_out;
                $early_out = round_off_early_out($early_out, get_early_out_settings($employee->branch_id), false);
            } else if ($last_out != "" && $shift_check && $shift_check->void_early_out == "No") {
                $early_out = calculate_early_out($last_out, $shift_check->end_time, $obj->date, $overnight);
            }
        }

        // if day is monday to satuday and last out is after 19:00
        if ($cid == 206 && $obj->day_name != "Sunday") {
            $last_out_time = $last_out ? explode(" ", $last_out)[1] : "";
            if ($last_out_time >= "19:00") {
                $food_allowance_days++;
            }
        }


        $obj->early_out = round_off_early_out($early_out, get_early_out_settings($employee->branch_id), false);
        $obj->clockings = $formatted_data;
        $obj->in_outs = $in_outs;
        $obj->in_outs_id = $in_outs_ids;
        if ($result) {
            $v = $result[0];
        }

        $break_and_late_hours = calculate_break_and_late_hours($obj->clockings, $v, $overnight);
        $work_hours = $break_and_late_hours->work_hours;
        $break_hours = $break_and_late_hours->break_hours;
        $breaks_array = $break_and_late_hours->breaks_array;
        $shift_break_hours = $break_and_late_hours->shift_break_hours;
        $shift_breaks_array = $break_and_late_hours->shift_breaks_array;
        $after_ot_starts_break_hours = $break_and_late_hours->after_ot_starts_break_hours;


        foreach ($obj->clockings as $key => $value) {
            if ($key != 0) {
                $value->day_f = '';
            }
            $total_hours = add_time($total_hours, $value->total_time);
            if ($key == 0) {
                $manual_late = search_from_list($manual_late_list, $obj->date);
                if ($manual_late) {
                    $late_hours = $manual_late->late_hours;
                    $late_hours = round_off_late_in($late_hours, get_late_in_settings($employee->branch_id), false);
                } else if (isset($v) && $v->is_leave != "" && $v->is_leave != "yes" && $v->void_late_in == "No") {
                    if ($v->grace_time != "") {
                        if ($overnight) {
                            $grace_time = $obj->date . " " . $v->grace_time . ":00";
                            $grace_time_stamp = strtotime($grace_time);
                            $mid_day = $obj->date . " 12:00:00";
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
                        } elseif ($preshift) {
                            // PRESHIFT LATE LOGIC
                            // The shift is assigned to $obj->date (e.g. May 19).
                            // Shift starts 00:00 on $obj->date, grace ends at grace_time on $obj->date.
                            // Employee clocks in on the PREVIOUS day (e.g. 23:50 May 18) — always
                            // before 00:00 May 19, so always before grace → never late.
                            // Or they clock in after midnight on $obj->date (e.g. 00:30 or 01:30).
                            // We compare raw clock_in_o directly against grace_time on $obj->date.
                            // No +24h adjustment needed — the timestamps handle cross-midnight naturally.

                            $grace_time = $obj->date . " " . $v->grace_time . ":00";
                            // e.g. "2026-05-19 01:00:00"
                            $grace_time_stamp = strtotime($grace_time);

                            $clock_in_stamp = strtotime($v->clock_in_o);
                            // e.g. strtotime("2026-05-18 23:50:46") < strtotime("2026-05-19 01:00:00") → NOT LATE

                            if ($clock_in_stamp > $grace_time_stamp) {
                                // Employee clocked in AFTER grace on the shift date (e.g. 01:30 on May 19)
                                $late_stamp = $clock_in_stamp - $grace_time_stamp;
                                date_default_timezone_set('UTC');
                                $late_hours = date('H:i', $late_stamp);
                                date_default_timezone_set("Asia/Kuala_Lumpur");
                            } else {
                                // Clocked in before or on grace → not late
                                $late_hours = "00:00";
                            }
                        } else if (intval(str_replace(":", "", $v->clock_in)) > intval(str_replace(":", "", $v->grace_time))) {
                            $late_hours = sub_time($v->clock_in, $v->grace_time);
                        }
                    }
                }
            }
        } // inner loop end

        // set bmi attendance allowance to false if 3 late ins more than 10 minutes
        if ($late_hours > "00:10") {
            $bmi_late_more_than_10++;
            if ($bmi_late_more_than_10 >= 3) {
                $bmi_attendance_allowance = false;
            }
        }

        // set gbr attendance allowance to false if late is more than 0 minutes
        if ($late_hours > "00:00") {
            $gbr_attendance_allowance = false;
        }

        if ($late_hours >= "01:00") {
            $ln01_waived_days++;
        }

        if (($early_out != "" && $early_out != "00:00" && $is_early_out) || ($late_hours != "" && $late_hours != "00:00" && $is_late)) {
            $total_early_late++;
            $obj->merit_is_early_late = true;
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
        if (!$half_day) {
            $manual_short_hours = search_from_list($manual_short_hours_list, $obj->date);
            if ($manual_short_hours) {
                $short_hours = $manual_short_hours->short_hours;
            } else {
                $short_hours = calculate_short_hours($company_working_hours, $work_hours);
            }
        }

        $trip_a = search_from_list($trip_a_list, $obj->date);
        $trip_b = search_from_list($trip_b_list, $obj->date);
        if ($trip_a) {
            $tripA = $trip_a->no_of_trips;
            $total_trip_a += $trip_a->no_of_trips;
        }
        if ($trip_b) {
            $tripB = $trip_b->no_of_trips;
            $total_trip_b += $trip_b->no_of_trips;
        }

        if (isset($v) && !$half_day) {
            $manual_late_break = search_from_list($manual_late_break_list, $obj->date);
            if ($manual_late_break) {
                $break_late_hours = $manual_late_break->late_hours_break;
                $break_late_hours = round_off_late_break($break_late_hours, get_late_break_settings($employee->branch_id), false);
            } else {
                if ($employee->ignore_breaks_after_endtime == 0) {
                    $break_late_hours = calculate_break_late($break_hours, $breaks_array, $v, $work_hours, $obj->is_shift);
                } else {
                    $break_late_hours = calculate_break_late($shift_break_hours, $shift_breaks_array, $v, $work_hours, $obj->is_shift);
                }
            }
        }

        if ($break_late_hours != "" && $break_late_hours != "00:00" && $is_late_break === true) {
            $obj->merit_is_break_late = true;
            $total_break_late++;
        }
        $obj->break_late_hours = round_off_late_break($break_late_hours, get_late_break_settings($employee->branch_id), false);;

        $work_hours = add_deducted_time_in_work_hours($work_hours, $late_hours, $break_late_hours, $early_out, $inc_late_in, $inc_late_break, $inc_early_out, $is_late, $is_late_break, $is_early_out, $ot_type_data->ot_type);
        if (
            $work_hours > 0 &&
            in_array($cid, companies_allowed_for_shift_allowance()) &&
            isset($shift_check->shift_code)
        ) {
            if ($shift_check->shift_code === "DSA") {
                $monthly_dsa_count++;
            } elseif ($shift_check->shift_code === "NSA") {
                $monthly_nsa_count++;
            }
        }

        $days = "";
        $is_rest_day = false;
        $is_off_day = false;
        $is_ph_day = false;

        if (in_array($obj->date, $public_holidays) || $obj->is_replaced_ph) {
            $is_ph_day = true;
            $total_holidays++;
        }

        $ph = get_public_holiday_by_date($obj->date, $employee->branch_id, $cid);
        if ($result) {
            $v = $result[0];
            if ($days_settings) {
                $days = calculate_days($work_hours, $days_settings);
            } else {
                $days = 1;
            }
            if ($v->is_leave == "yes" && $v->half_day == "Yes") {
                $days = 0.5;
            }
            if ($is_ph_day) {
                // if worked hours are OT then don't count as worked holiday
                if (!$employee->worked_hours_ot_ph && !$ph->replacement_ph) {
                    $worked_holidays += $days;
                    // echo $ph->rate;die;
                    if ($ph->rate == "x3" || $obj->is_replaced_ph) {
                        // echo 'x3';die;
                        $worked_holidays_array[$date->format("d/m/Y")][] = [
                            "employee_special_id" => $employee->special_id,
                            "worked_holiday" => $days,
                            "branch_id" => $employee->branch_id,
                            "holiday_rate" => 'x3'
                        ];
                    } else {
                        // echo 'x2';die;
                        $worked_holidays_array[$date->format("d/m/Y")][] = [
                            "employee_special_id" => $employee->special_id,
                            "worked_holiday" => $days,
                            "branch_id" => $employee->branch_id,
                            "holiday_rate" => 'x2'
                        ];
                    }
                }

                // For company 229: if employee worked on a public holiday, include
                // that day in LN01 attendance allowance days (skip half-day codes and leaves)
                if ($cid == 229 && !empty($result)) {
                    $ln01_halfdays = ['HPAM', 'HPPM'];
                    if (!($obj->shift_check && $obj->shift_check->code && in_array($obj->shift_check->code, $ln01_halfdays)) && !$obj->is_leave) {
                        $ln01_attendance_allowance_days += ($days && $days >= 1 && $days != "-" ? $days : 0);
                    }
                }

                if ($cid == 66 && $obj->day_name != "Sunday") {
                    $worked_days++; // public holiday count as worked day if not sunday for BMI
                }
            } else if (in_array($obj->day_name, $off_days)) {
                $is_off_day = true;
                // if worked hours are OT then don't count as worked rest day
                if (!$employee->worked_hours_ot_off) {
                    $worked_off_days += $days;
                    $worked_off_days_array[$date->format("d/m/Y")][] = [
                        "employee_special_id" => $employee->special_id,
                        "worked_off_day" => $days,
                        "branch_id" => $employee->branch_id,
                    ];
                }
            } else if (in_array($obj->day_name, $rest_days) || $v->name == "N/A" || !$obj->shift_check || ($shift_check && $shift_check->is_rest_day)) {
                $is_rest_day = true;
                // if worked hours are OT then don't count as worked rest day
                if (!$employee->worked_hours_ot_rd) {
                    $worked_rest_days += $days;
                    $worked_rest_days_array[$date->format("d/m/Y")][] = [
                        "employee_special_id" => $employee->special_id,
                        "worked_rest_day" => $days,
                        "branch_id" => $employee->branch_id,
                    ];
                }
            } else {
                if ($cid == 229) {
                    //// Half Day AM
                    //// Half Day PM
                    $ln01_halfdays = ['HPAM', 'HPPM'];
                    if ($obj->shift_check && $obj->shift_check->code && in_array($obj->shift_check->code, $ln01_halfdays)) {
                        /// Skip For halfdays
                    } elseif ($obj->is_leave) {
                        /// Skip leave
                    } else {
                        $ln01_attendance_allowance_days += ($days && $days >= 1 && $days != "-" ? $days : 0);
                    }
                }
                $worked_days += ($days && $days != "-" ? $days : 0);
            }
        } else {
            if (in_array($cid, $companies_allowed_for_monthly_ot)) {
                if ($obj->is_paid_leave) {
                    if ($shift_check->half_day == "Yes") {
                        $days = 0.5;
                        $work_hours = $company_half_hours;
                    } else {
                        $work_hours = $company_working_hours;
                        $days = 1;
                    }
                }
            }
        }

        $obj->is_rest_day = $is_rest_day;

        if ($cid == 87) {
            if ($obj->is_paid_leave) {
                if ($shift_check && $shift_check->half_day == 'Yes') {
                    $days = 0.5;
                    $work_hours = $company_half_hours;
                } else {
                    $days = 1;
                    $work_hours = $company_working_hours;
                }
            }
        }


        $obj->first_in = "";
        $obj->last_out = "";
        $obj->first_in_o = "";
        $obj->last_out_o = "";

        if ($result) {
            $obj->first_in = $result[0]->clock_in;
            $obj->last_out = end($result)->clock_out;
            $obj->first_in_o = $result[0]->clock_in_1;
            $obj->last_out_o = end($result)->clock_out_1;
        }


        $obj->total_hours = $total_hours;
        $obj->work_hours = $work_hours;
        $obj->work_hours_whole = floor(toDecimal($work_hours));


        $final_company_working_hours = $company_working_hours;
        $final_company_working_hours_decimal = $company_working_hours_decimal;
        if ($obj->day_name == 'Saturday' && $employee->use_half_hours_for_saturdays) {
            $final_company_working_hours = $company_half_hours;
            $final_company_working_hours_decimal = $company_half_hours_decimal;
        }
        if ($preshift && $shift_check && $shift_check->shift_hours != "") {
            $final_company_working_hours = $shift_check->shift_hours;
            $final_company_working_hours_decimal = toDecimal($shift_check->shift_hours);
        }
        if ($employee->ot_type == "eight_hours") {
            $decimal_work_hours = toDecimal($work_hours);
            // if company working hours is 8 hours and employee worked less than 8 hours then calculate early out
            if ($final_company_working_hours_decimal && $decimal_work_hours < $final_company_working_hours_decimal && $decimal_work_hours > 0) {
                $decimal_early_out = $final_company_working_hours_decimal - $decimal_work_hours;
                $eight_hours_early_out = decimal_to_time($decimal_early_out);
                if (!$half_day) {
                    $manual_early_out = search_from_list($manual_early_out_list, $obj->date);
                    if ($manual_early_out) {
                        $early_out = $manual_early_out->early_out;
                    } else if ($last_out != "" && $shift_check && $shift_check->void_early_out == "No") {
                        $obj->early_out = $early_out = $eight_hours_early_out;
                    }
                }
            }
        }
        $obj->break_hours = $break_hours;
        // $obj->late_hours = $late_hours;
        $obj->late_hours = round_off_late_in($late_hours, get_late_in_settings($employee->branch_id), false);
        $obj->short_hours = $short_hours;
        $obj->trip_a = $tripA;
        $obj->trip_b = $tripB;
        // don't show days if it's a rest day or public holiday and worked hours are OT
        $obj->days = (($is_rest_day && $employee->worked_hours_ot_rd) || ($is_ph_day && $employee->worked_hours_ot_ph) || ($is_off_day && $employee->worked_hours_ot_off)) ? "" : $days;
        $overtime = "";
        $early_overtime = "";
        $overtime_m = "";
        $overtime_type = "+";
        $is_manual_exist = false;
        $manual_ot = search_from_list($manual_ot_list, $obj->date);
        if ($manual_ot) {
            $overtime_m = $manual_ot->overtime;
            $overtime_type = $manual_ot->type;
            $is_manual_exist = true;
            if ($overtime_type == "-") {
                $overtime_m = "-" . $overtime_m;
            }
        }
        $round_of_ot = 1;
        if ($shift_check) {
            $round_of_ot = $shift_check->round_off_ot;
        }
        if (($is_rest_day && $employee->worked_hours_ot_rd) || ($is_ph_day && $employee->worked_hours_ot_ph) || ($is_off_day && $employee->worked_hours_ot_off)) {
            if ($apply_overtime) {
                $overtime = $work_hours;
            }
            $overtime = round_off_ot($overtime, $ot_settings, $employee->round_first_hour_only);
        } else {
            $overtime = calculate_final_overtime($result, $obj->clockings, $date_f, $overnight, $apply_overtime, $apply_early_overtime, $work_hours, $final_company_working_hours, $employee->ot_type, $employee->ot_round, $employee->round_first_hour_only, $employee->round_by_exact_hour, $employee->different_first_hour_rounding, $ot_settings, $obj->shift_hours, $round_of_ot, $final_company_working_hours_decimal, $employee->early_ot_round, $early_ot_settings);
            if ($employee->ignore_breaks_after_endtime == 1 && ($apply_overtime || $apply_early_overtime)) {
                $overtime = add_time($overtime, "-" . $after_ot_starts_break_hours);
                if ($overtime == "00:00") {
                    $overtime = "";
                }
            }
        }

        if (($is_rest_day && $employee->deduct_hour_ot_rd) || ($is_ph_day && $employee->deduct_hour_ot_ph) || ($is_off_day && $employee->deduct_hour_ot_off)) {
            $overtime = deduct_hour_from_ot_rd($overtime);
        }

        if ($cid == 66) {
            $obj->bmi_ot = "";
            $obj->bmi_ot_sunday = "";
            $obj->bmi_ph_1 = "";
            $obj->bmi_ph_2 = "";
            $obj->bmi_ta_final = $obj->bmi_ta = $obj->bmi_ta_manual = "";
            $obj->bmi_ma_final = $obj->bmi_ma = $obj->bmi_ma_manual = "";
            $obj->bmi_ca_final = $obj->bmi_ca = $obj->bmi_ca_manual = "";
            $obj->bmi_spa_final = $obj->bmi_spa = $obj->bmi_spa_manual = "";
            $obj->bmi_aca_final = $obj->bmi_aca = $obj->bmi_aca_manual = "";
            $obj->bmi_fl_final = $obj->bmi_fl = $obj->bmi_fl_manual = "";
            $obj->bmi_cw_final = $obj->bmi_cw = $obj->bmi_cw_manual = "";
            $obj->bmi_mo_final = $obj->bmi_mo = $obj->bmi_mo_manual = "";
            $obj->bmi_shift1_final = $obj->bmi_shift1 = $obj->bmi_shift1_manual = "";
            $obj->bmi_shift2_final = $obj->bmi_shift2 = $obj->bmi_shift2_manual = "";
            $obj->bmi_shift3_final = $obj->bmi_shift3 = $obj->bmi_shift3_manual = "";

            $bmi_total_time_original_format = time_bw_original_times($obj->last_out_o, $obj->first_in_o);
            $bmi_total_time = toDecimal(time_bw_original_times($obj->last_out_o, $obj->first_in_o));

            if (in_array($obj->date, $public_holidays)) {
                if ($bmi_total_time > 8) {
                    $obj->bmi_ph_1 = "8.00";
                    $obj->bmi_ph_2 = number_format($bmi_total_time - 8, 2);
                } else if ($bmi_total_time) {
                    $obj->bmi_ph_1 = number_format($bmi_total_time, 2);
                }
            } else if (in_array($obj->day_name, $rest_days)) {
                if ($bmi_total_time && $obj->last_out) {
                    $bmi_ot_sunday = round_off_ot($bmi_total_time_original_format, $ot_settings, $employee->round_first_hour_only);
                    $bmi_ot_sunday = toDecimal($bmi_ot_sunday);
                    if ($employee->deduct_hour_ot_rd) {
                        $bmi_ot_sunday = $bmi_ot_sunday - 1;
                        if ($bmi_ot_sunday < 0) $bmi_ot_sunday = 0;
                    }
                    $obj->bmi_ot_sunday = round($bmi_ot_sunday, 2);
                }
            } else if ($is_ot) {
                $manual_added_overtime = add_time_minus($overtime, $overtime_m);
                $obj->bmi_ot = $manual_added_overtime ? number_format(toDecimal($manual_added_overtime), 2) : "";
            }

            if ($bmi_total_time > 5 && $days == 1) {
                $obj->bmi_ta_final = $obj->bmi_ta = number_format($employee->ta_rate, 2);
            }
            if ($obj->bmi_ot >= 2.5 && $days == 1) {
                $obj->bmi_ma_final = $obj->bmi_ma = number_format($employee->ma_rate, 2);
            }
            if (in_array("CA", $acting_codes) && $days == 1) {
                $obj->bmi_ca_final = $obj->bmi_ca = number_format($employee->ca_rate, 2);
            }
            if (in_array("SPA", $acting_codes) && $days == 1) {
                $obj->bmi_spa_final = $obj->bmi_spa = number_format($employee->spa_rate, 2);
            }
            if (in_array("ACA", $acting_codes) && $days == 1) {
                $obj->bmi_aca_final = $obj->bmi_aca = number_format($employee->aca_rate, 2);
            }
            if (in_array("FL Inc", $acting_codes) && $days == 1) {
                $obj->bmi_fl_final = $obj->bmi_fl = number_format($employee->fl_rate, 2);
            }
            if (in_array("C/wash", $acting_codes) && $days == 1) {
                $obj->bmi_cw_final = $obj->bmi_cw = number_format($employee->cw_rate, 2);
            }
            if (in_array("M/ope", $acting_codes) && $days == 1) {
                $obj->bmi_mo_final = $obj->bmi_mo = number_format($employee->mo_rate, 2);
            }
            if (in_array("Shift1", $acting_codes) && $days == 1) {
                $obj->bmi_shift1_final = $obj->bmi_shift1 = number_format($employee->shift1_rate, 2);
            }
            if (in_array("Shift2", $acting_codes) && $days == 1) {
                $obj->bmi_shift2_final = $obj->bmi_shift2 = number_format($employee->shift2_rate, 2);
            }
            if (in_array("Shift3", $acting_codes) && $days == 1) {
                $obj->bmi_shift3_final = $obj->bmi_shift3 = number_format($employee->shift3_rate, 2);
            }

            $manual_ta = search_from_list($manual_ta_list, $obj->date);
            if ($manual_ta) {
                $obj->bmi_ta_final = $obj->bmi_ta_manual = number_format($manual_ta->value, 2);
            }

            $manual_ma = search_from_list($manual_ma_list, $obj->date);
            if ($manual_ma) {
                $obj->bmi_ma_final = $obj->bmi_ma_manual = number_format($manual_ma->value, 2);
            }

            $manual_ca = search_from_list($manual_ca_list, $obj->date);
            if ($manual_ca) {
                $obj->bmi_ca_final = $obj->bmi_ca_manual = number_format($manual_ca->value, 2);
            }

            $manual_spa = search_from_list($manual_spa_list, $obj->date);
            if ($manual_spa) {
                $obj->bmi_spa_final = $obj->bmi_spa_manual = number_format($manual_spa->value, 2);
            }

            $manual_aca = search_from_list($manual_aca_list, $obj->date);
            if ($manual_aca) {
                $obj->bmi_aca_final = $obj->bmi_aca_manual = number_format($manual_aca->value, 2);
            }

            $manual_fl = search_from_list($manual_fl_list, $obj->date);
            if ($manual_fl) {
                $obj->bmi_fl_final = $obj->bmi_fl_manual = number_format($manual_fl->value, 2);
            }

            $manual_cw = search_from_list($manual_cw_list, $obj->date);
            if ($manual_cw) {
                $obj->bmi_cw_final = $obj->bmi_cw_manual = number_format($manual_cw->value, 2);
            }

            $manual_mo = search_from_list($manual_mo_list, $obj->date);
            if ($manual_mo) {
                $obj->bmi_mo_final = $obj->bmi_mo_manual = number_format($manual_mo->value, 2);
            }

            $manual_shift1 = search_from_list($manual_shift1_list, $obj->date);
            if ($manual_shift1) {
                $obj->bmi_shift1_final = $obj->bmi_shift1_manual = number_format($manual_shift1->value, 2);
            }

            $manual_shift2 = search_from_list($manual_shift2_list, $obj->date);
            if ($manual_shift2) {
                $obj->bmi_shift2_final = $obj->bmi_shift2_manual = number_format($manual_shift2->value, 2);
            }

            $manual_shift3 = search_from_list($manual_shift3_list, $obj->date);
            if ($manual_shift3) {
                $obj->bmi_shift3_final = $obj->bmi_shift3_manual = number_format($manual_shift3->value, 2);
            }

            $total_bmi_ot += ($obj->bmi_ot ? $obj->bmi_ot : 0);
            $total_bmi_ot_sunday += ($obj->bmi_ot_sunday ? $obj->bmi_ot_sunday : 0);
            $total_bmi_ph_1 += ($obj->bmi_ph_1 ? $obj->bmi_ph_1 : 0);
            $total_bmi_ph_2 += ($obj->bmi_ph_2 ? $obj->bmi_ph_2 : 0);
            $total_bmi_ta += ($obj->bmi_ta_final ? $obj->bmi_ta_final : 0);
            $total_bmi_ma += ($obj->bmi_ma_final ? $obj->bmi_ma_final : 0);
            $total_bmi_ca += ($obj->bmi_ca_final ? $obj->bmi_ca_final : 0);
            $total_bmi_spa += ($obj->bmi_spa_final ? $obj->bmi_spa_final : 0);
            $total_bmi_aca += ($obj->bmi_aca_final ? $obj->bmi_aca_final : 0);
            $total_bmi_fl += ($obj->bmi_fl_final ? $obj->bmi_fl_final : 0);
            $total_bmi_cw += ($obj->bmi_cw_final ? $obj->bmi_cw_final : 0);
            $total_bmi_mo += ($obj->bmi_mo_final ? $obj->bmi_mo_final : 0);
            $total_bmi_shift1 += ($obj->bmi_shift1_final ? $obj->bmi_shift1_final : 0);
            $total_bmi_shift2 += ($obj->bmi_shift2_final ? $obj->bmi_shift2_final : 0);
            $total_bmi_shift3 += ($obj->bmi_shift3_final ? $obj->bmi_shift3_final : 0);
        }

        if ($cid == "102") {
            if ($employee->ot_group == "hours" && toDecimal($overtime) > 3) {
                $overtime = add_time($overtime, "-00:30");
            }
        }

        $overtime = ot_deduction_from_shift_settings($overtime, $shift_check);


        $obj->is_manual_exist = $is_manual_exist;
        $obj->overtime = $overtime;
        $obj->overtime_m = $overtime_m;
        $obj->overtime_type = $overtime_type;
        $obj->is_ot = $is_ot;
        $obj->is_late = $is_late;
        $obj->is_late_break = $is_late_break;
        $obj->is_early_out = $is_early_out;
        $obj->overtime_ph_x2 = "";
        $obj->overtime_ph_x3 = "";
        $obj->x2 = false;
        $obj->x3 = false;


        $daily_overtime = "";


        if (isEligibleForMealAllowance($cid, $obj, $public_holidays, $off_days, $overtime)) {
            $food_allowance_days++;
        }


        if ($is_ot) {
            $daily_overtime = $overtime;
        }

        if ($is_manual_exist) {
            $daily_overtime = add_time_minus($daily_overtime, $overtime_m);
        }

        if (toDecimal($daily_overtime) != 0) {
            $daily_ot_array[$date->format("d/m/Y")][] = [
                "employee_special_id" => $employee->special_id,
                "daily_overtime" => toDecimal($daily_overtime),
                "branch_id" => $employee->branch_id
            ];
        }

        $is_extra_ot = false;
        if ($obj->shift_check) {
            if (is_extra_ot_given($obj->work_hours, $obj->shift_check->extra_ot, $obj->shift_check->extra_ot_worked_hours_more_than, $obj->shift_check->extra_ot_hours)) {
                $is_extra_ot = true;
            }
        }

        $obj->is_extra_ot = $is_extra_ot;
        $dates[] = $obj;

        if (in_array($obj->date, $public_holidays) || $is_replaced_ph) {
            if ($ph->rate == "x3" || $is_replaced_ph) {
                $obj->x3 = true;
            } else {
                $obj->x2 = true;
            }
        }

        if ($obj->is_ot) {
            if (in_array($obj->date, $public_holidays) || $is_replaced_ph) {
                if ($obj->x3) {
                    $month_overtime_ph_x3 = add_time_minus($month_overtime_ph_x3, $obj->overtime);
                    $obj->overtime_ph_x3 = $obj->overtime;
                } else {
                    $month_overtime_ph_x2 = add_time_minus($month_overtime_ph_x2, $obj->overtime);
                    $obj->overtime_ph_x2 = $obj->overtime;
                }
                $month_overtime_ph = add_time_minus($month_overtime_ph, $obj->overtime);
            } else if (in_array($obj->day_name, $off_days) || $obj->is_employee_off_day) {
                $month_overtime_off = add_time_minus($month_overtime_off, $obj->overtime);
            } else if (in_array($obj->day_name, $rest_days) || !$obj->shift_check || $obj->shift_check->is_rest_day) {
                $month_overtime_rd = add_time_minus($month_overtime_rd, $obj->overtime);
            } else {
                $month_overtime = add_time_minus($month_overtime, $obj->overtime);
            }
        }
        // changed
        if ($obj->is_manual_exist) {
            if (in_array($obj->date, $public_holidays) || $is_replaced_ph) {
                if ($obj->x3) {
                    $month_overtime_ph_x3 = add_time_minus($month_overtime_ph_x3, $obj->overtime_m);
                    $obj->overtime_ph_x3 = add_time_minus($obj->overtime_ph_x3, $obj->overtime_m);
                } else {
                    $month_overtime_ph_x2 = add_time_minus($month_overtime_ph_x2, $obj->overtime_m);
                    $obj->overtime_ph_x2 = add_time_minus($obj->overtime_ph_x2, $obj->overtime_m);
                }
                $month_overtime_ph = add_time_minus($month_overtime_ph, $obj->overtime_m);
            } else if (in_array($obj->day_name, $off_days) || $obj->is_employee_off_day) {
                $month_overtime_off = add_time_minus($month_overtime_off, $obj->overtime_m);
            } else if (in_array($obj->day_name, $rest_days) || !$obj->shift_check || $obj->shift_check->is_rest_day) {
                $month_overtime_rd = add_time_minus($month_overtime_rd, $obj->overtime_m);
            } else {
                $month_overtime = add_time_minus($month_overtime, $obj->overtime_m);
            }
        }

        $obj->overtime_ph_x2 = $obj->overtime_ph_x2 == "00:00" ? "" : $obj->overtime_ph_x2;
        $obj->overtime_ph_x3 = $obj->overtime_ph_x3 == "00:00" ? "" : $obj->overtime_ph_x3;

        // shift name
        if (!$obj->clockings) {
            $shift_name = "";
            $shift_code = "";
            $cut_off_time = "";
            $shift = search_from_list($shift_list, $obj->date);
            if ($shift) {
                $shift_name = $shift->name;
                $shift_code = $shift->code;
                $cut_off_time = $shift->cut_off_time;
            }

            $remark = search_from_list($remark_list, $obj->date);

            $staff_remark = search_from_list($staff_remark_list, $obj->date);

            $no_data = new stdClass();
            $no_data->day_f = $date_string;
            $no_data->name = $shift_name;
            $no_data->code = $shift_code;
            $no_data->cut_off_time = $cut_off_time;
            $no_data->clock_in = "";
            $no_data->clock_out = "";
            $no_data->reason = "";
            $no_data->remark = "";
            if ($remark) {
                $no_data->remark = $remark->remark;
            }
            $no_data->staff_remark = "";
            if ($staff_remark) {
                $no_data->staff_remark = $staff_remark->remark;
            }
            $no_data->total_time = "";
            $obj->clockings[0] = $no_data;
            $obj->shift_name = $shift_name;
            $obj->shift_code = $shift_code;
            $obj->cut_off_time = $cut_off_time;
        }

        // revert replacement OT
        if ($replacement) {
            if ($replacement->to === $obj->date) {
                $obj->clockings[0]->name = "RL";
                $obj->clockings[0]->code = "RL";
                $formatted_from_date = convert_date("Y-m-d", "d/m/Y", $replacement->from);
                $obj->clockings[0]->remark = "Replacement leave from {$formatted_from_date}";
            }
        }

        $total = add_time($total, $obj->total_hours);
        $work = add_time($work, $obj->work_hours);
        $break = add_time($break, $obj->break_hours);
        if ($obj->is_late) {
            $late = add_time($late, $obj->late_hours);
        }
        if ($obj->is_late_break) {
            $break_late = add_time($break_late, $obj->break_late_hours);
        }
        $total_short = add_time($total_short, $obj->short_hours);
        if ($obj->is_early_out) {
            $total_early = add_time($total_early, $obj->early_out);
            if ($obj->early_out != "") {
                $total_early_count++;
                $obj->merit_is_early_out = true;
            }
        }
        $total_days = add_days($total_days, $obj->days);
        $late_result = get_lateness_time($total_late, $obj->late_hours, $obj->break_late_hours, $obj->early_out, $obj->short_hours, $inc_late_in, $inc_late_break, $inc_early_out && $is_early_out, $inc_short_hours, $late_count);
        $total_late = $late_result[0];
        $late_count = $late_result[1];
        $today_late = $late_result[2];
        if ($inc_late_in && check_if_time_exist($obj->late_hours)) {
            $obj->merit_is_late = true;
            $total_late_only_count++;
        }

        if (toDecimal($today_late) != 0) {
            $total_late_day = round(beautiful_time_to_minutes($today_late) / ($company_working_hours_decimal * 60), 3);
            $daily_late_array[$date->format("d/m/Y")][] = [
                "employee_special_id" => $employee->special_id,
                "daily_late" => $total_late_day,
                "branch_id" => $employee->branch_id
            ];
        }
        if ($custom_in_outs) {
            $obj->clockings = [$obj->clockings[0]];
        }
        $obj->employee_shift_hours = "";
        if ($result && $last_out != "" && !$is_ph_day && !$is_rest_day) {
            $obj->employee_shift_hours = add_time($obj->full_shift_hours, "-" . $today_late);
        }
        $shift_hours_total = add_time($shift_hours_total, $obj->employee_shift_hours);

        if ($cid == 146) {
            $obj->meal_days = 0;
            if (toDecimal($obj->work_hours) >= toDecimal($employee->min_worked_hours_meal) && $employee->department == "Worker") {
                $obj->meal_days = 1;
            }
        }
        $total_meal_days += $obj->meal_days ?? 0;
    } // outer loop end

    if ($cid == 196) {
        $paid_leaves_array = $jl01_paid_leaves;
    }

    $lateness_result = get_lateness_time("00:00", $late, $break_late, $total_early, $total_short, $inc_late_in, $inc_late_break, $inc_early_out && $is_early_out, $inc_short_hours, 0);
    $lateness_time = void_late_minutes($lateness_result[0], $void_minutes);

    $monthly_working_hours = "00:00";

    if ($ot_type_data->ot_type === "weekly_hours") {
        $work_decimal = toDecimal($work);
        $ot_weekly_hours_decimal = $ot_type_data->ot_weekly_hours;

        if ($ot_weekly_hours_decimal > $work_decimal) {
            $month_overtime = "00:00";
            $month_overtime_deducted = "00:00";

            foreach ($dates as $d) {
                $is_manual_exist = false;
                $manual_ot = search_from_list($manual_ot_list, $d->date);
                $shift_check = search_from_list($shift_list, $d->date);
                if ($manual_ot) {
                    $overtime_m = $manual_ot->overtime;
                    $overtime_type = $manual_ot->type;
                    $is_manual_exist = true;
                    if ($overtime_type == "-") {
                        $overtime_m = "-" . $overtime_m;
                    }
                }
                // If not holiday / rest day
                if (!in_array($d->date, $public_holidays) && !in_array($d->day_name, $rest_days) && $shift_check && $is_replaced_ph && !$shift_check->is_rest_day && $is_replaced_ph) {
                    if ($is_manual_exist) {
                        $month_overtime = add_time_minus($month_overtime, $overtime_m);
                    }
                    if ($deduct_from_ot) {
                        $after_deduction = deduct_from_ot($month_overtime, $lateness_time, $deduction_date, $last_day);
                        $month_overtime_deducted = $after_deduction[0];
                    }
                    $d->overtime = "";
                }
            }
        }
    } else if ($ot_type_data->ot_type === "monthly_ot") {
        $days_in_month = (int)date('t', strtotime($obj->date));
        $no_of_working_days = $ci->db->select("id, days")->from("monthly_working_days")
            ->where('month', $date->format('m'))
            ->where('year', $date->format('Y'))
            ->where('company_id', $cid)
            ->where('branch_id', $employee->branch_id)->get()->row();

        $work_decimal = toDecimal($work);
        // $ot_daily_hours = (double)$ot_type_data->ot_daily_hours;
        $off_days_m = 0;
        if (is_null($no_of_working_days)) {
            $no_of_working_days = $days_in_month - 4;
            if ($total_off_days > 4) $off_days_m = $total_off_days - 4;
        } else {
            $no_of_working_days = (int)$no_of_working_days->days;
            $remaining_days = $days_in_month - $no_of_working_days;
            if ($total_off_days > $remaining_days) $off_days_m = $total_off_days - $remaining_days;
        }

        $monthly_working_hours_decimal = $no_of_working_days * $company_working_hours_decimal;
        $monthly_working_hours = decimal_to_time($monthly_working_hours_decimal);
        $off_days_time = multiply_time_by_scalar($company_working_hours, $off_days_m);
        $month_overtime = add_time($month_overtime, "-{$off_days_time}");
        $absent_days_time = multiply_time_by_scalar($company_working_hours, $absent_days);
        $month_overtime = add_time($month_overtime, "-{$absent_days_time}");
        $unpaid_leaves_time = multiply_time_by_scalar($company_working_hours, $full_unpaid_leaves);
        $month_overtime = add_time($month_overtime, "-{$unpaid_leaves_time}");
        // if($monthly_working_hours_decimal < $work_decimal) {
        //   $month_overtime = decimal_to_time($work_decimal - $monthly_working_hours_decimal);
        // }
        if ($monthly_working_hours_decimal > $work_decimal) {
            $month_overtime = "00:00";
            $month_overtime_deducted = "00:00";

            foreach ($dates as $d) {
                $is_manual_exist = false;
                $manual_ot = search_from_list($manual_ot_list, $d->date);
                $shift_check = search_from_list($shift_list, $d->date);
                if ($manual_ot) {
                    $overtime_m = $manual_ot->overtime;
                    $overtime_type = $manual_ot->type;
                    $is_manual_exist = true;
                    if ($overtime_type == "-") {
                        $overtime_m = "-" . $overtime_m;
                    }
                }

                // If not holiday / rest day
                if (!in_array($d->date, $public_holidays) && !in_array($d->day_name, $rest_days) && $shift_check && $is_replaced_ph && !$shift_check->is_rest_day) {
                    if ($is_manual_exist) {
                        $month_overtime = add_time_minus($month_overtime, $overtime_m);
                    }
                    if ($deduct_from_ot) {
                        $after_deduction = deduct_from_ot($month_overtime, $lateness_time, $deduction_date, $last_day);
                        $month_overtime_deducted = $after_deduction[0];
                    }
                    $d->overtime = "";
                }
            }
        }
    }

    $month_overtime_deducted = $month_overtime;
    $lateness_time_deducted = $lateness_time;


    if ($deduct_from_ot) {
        $after_deduction = deduct_from_ot($month_overtime, $lateness_time, $deduction_date, $last_day);
        $month_overtime_deducted = $after_deduction[0];
        $lateness_time_deducted = $after_deduction[1];
    }

    if (in_array($cid, companies_allowed_for_att_all())) {
        if ($employee->is_att_all == 1) {
            if ($absent_days > 0 || $allowance_leaves > 2) {
                $employee->att_all_amount = 0;
            } else if ($allowance_leaves == 1) {
                $employee->att_all_amount = 75;
            } else if ($allowance_leaves == 2) {
                $employee->att_all_amount = 50;
            }
        }
    }

    $yrdata = strtotime($first_day);
    $month_name = date('F', $yrdata);

    $data["current_user"] = $current_user;
    $data["employee"] = $employee;
    // Set $custom_in_outs true if company ID is matched
    if ($custom_in_outs)
        $data['custom_in_outs'] = true;

    if ($tsf_custom_summary)
        $data['tsf_custom_summary'] = true;

    $data['lateness_time'] = $lateness_time; // total late
    $data['lateness_time_deducted'] = $lateness_time_deducted;
    $data['late'] = $late; // late_in
    $data['late_count'] = $late_count;

    $data['total'] = $total;
    $data['work'] = $work;
    $data['shift_hours_total'] = remove_seconds($shift_hours_total);
    $data['break'] = $break;
    $data['break_late'] = $break_late;
    $data['total_days'] = $total_days;
    $data['total_meal_days'] = $total_meal_days;
    $data['total_short'] = $total_short;
    $data['total_early'] = $total_early;
    $data['total_early_count'] = $total_early_count;
    $data['total_trip_a'] = $total_trip_a;
    $data['total_trip_b'] = $total_trip_b;
    $data['total_late_only_count'] = $total_late_only_count;

    $data['month_overtime'] = $month_overtime;
    $data['month_overtime_deducted'] = $month_overtime_deducted;
    $data['month_overtime_ph'] = $month_overtime_ph;
    $data['month_overtime_ph_x2'] = $month_overtime_ph_x2;
    $data['month_overtime_ph_x3'] = $month_overtime_ph_x3;
    $data['month_overtime_rd'] = $month_overtime_rd;
    $data['month_overtime_off'] = $month_overtime_off;
    $data['monthly_working_hours'] = $monthly_working_hours;

    if ($summary_type === "short") {
        $data['month_overtime_deducted'] = ($month_overtime_deducted === "00:00" ? "" : $month_overtime_deducted);
        $data['month_overtime_ph'] = ($month_overtime_ph === "00:00" ? "" : $month_overtime_ph);
        $data['month_overtime_ph_x2'] = ($month_overtime_ph_x2 === "00:00" ? "" : $month_overtime_ph_x2);
        $data['month_overtime_ph_x3'] = ($month_overtime_ph_x3 === "00:00" ? "" : $month_overtime_ph_x3);
        $data['month_overtime_rd'] = ($month_overtime_rd === "00:00" ? "" : $month_overtime_rd);
        $data['month_overtime_off'] = ($month_overtime_off === "00:00" ? "" : $month_overtime_off);
    } else if ($summary_type === "accounts") {
        $data['month_overtime_deducted'] = toDecimal($month_overtime_deducted);
        $data['month_overtime_ph'] = toDecimal($month_overtime_ph);
        $data['month_overtime_rd'] = toDecimal($month_overtime_rd);
        $data['lateness_time_deducted'] = toDecimal($lateness_time_deducted);
        $data['total_early'] = "";
        $data['total_early_count'] = "";
    } else if ($summary_type === "autocount") {
        $data['month_overtime_deducted'] = $month_overtime_deducted;
        $data['month_overtime_ph'] = $month_overtime_ph;
        $data['month_overtime_rd'] = $month_overtime_rd;
        $data['lateness_time_deducted'] = $lateness_time_deducted;
        $data['total_early'] = $total_early;
    } else if ($summary_type === "sql") {
        $data['lateness_time_deducted'] = toDecimal($lateness_time_deducted);
        $data['month_overtime_deducted'] = toDecimal($month_overtime_deducted);
        $data['month_overtime_ph'] = toDecimal($month_overtime_ph);
        $data['month_overtime_ph_x2'] = toDecimal($month_overtime_ph_x2);
        $data['month_overtime_ph_x3'] = toDecimal($month_overtime_ph_x3);
        $data['month_overtime_rd'] = toDecimal($month_overtime_rd);
        $data['month_overtime_off'] = toDecimal($month_overtime_off);
        $lateness_time_deducted_decimal = toDecimal($lateness_time_deducted);
        $company_working_hours_decimal = toDecimal($company_working_hours);
        $data['late_days'] = calculate_late_days($lateness_time_deducted_decimal, $company_working_hours_decimal);
    }

    $data['working_days'] = $working_days;
    $data['worked_days'] = $worked_days;
    $data['worked_rest_days'] = $worked_rest_days;
    $data['worked_off_days'] = $worked_off_days;
    $data['worked_holidays'] = $worked_holidays;
    $data['total_holidays'] = $total_holidays;
    $data['absent_days'] = $absent_days;
    $data['paid_leaves'] = $paid_leaves;
    $data['unpaid_leaves'] = $unpaid_leaves;

    $data['worked_holidays_array'] = $worked_holidays_array;
    $data['worked_rest_days_array'] = $worked_rest_days_array;
    $data['worked_off_days_array'] = $worked_off_days_array;
    $data['unpaid_leaves_absent_days'] = $unpaid_leaves_absent_days;

    $data['dates'] = $dates;

    $data["rest_days"] = $rest_days;
    $data["off_days"] = $off_days;
    $data['public_holidays'] = $public_holidays;
    if ($summary_type === "summary") $data['public_holidays_names'] = $public_holidays_names;
    $data['month_name'] = $month_name;
    $data['total_shift_hours'] = $total_shift_hours;

    $data["total_half_day_paid"] = $total_half_day_paid;
    $data["total_full_day_paid"] = $total_full_day_paid;
    $data["total_half_day_unpaid"] = $total_half_day_unpaid;
    $data["total_medical_leaves"] = $total_medical_leaves;
    $data["total_break_late"] = $total_break_late;
    $data["total_missing_in_out"] = $total_missing_in_out;
    $data["total_absent_unpaid"] = $total_absent_unpaid;
    $data["total_early_late"] = $total_early_late;
    $data["total_rest_days_used"] = $total_rest_days_used;

    if ($cid == 66) {
        $data["total_bmi_ot"] = $total_bmi_ot;
        $data["total_bmi_ot_sunday"] = $total_bmi_ot_sunday;
        $data["total_bmi_ph_1"] = $total_bmi_ph_1;
        $data["total_bmi_ph_2"] = $total_bmi_ph_2;
        $data["total_bmi_ta"] = $total_bmi_ta;
        $data["total_bmi_ma"] = $total_bmi_ma;
        $data["total_bmi_ca"] = $total_bmi_ca;
        $data["total_bmi_spa"] = $total_bmi_spa;
        $data["total_bmi_aca"] = $total_bmi_aca;
        $data["total_bmi_fl"] = $total_bmi_fl;
        $data["total_bmi_cw"] = $total_bmi_cw;
        $data["total_bmi_mo"] = $total_bmi_mo;
        $data["total_bmi_shift1"] = $total_bmi_shift1;
        $data["total_bmi_shift2"] = $total_bmi_shift2;
        $data["total_bmi_shift3"] = $total_bmi_shift3;
        $data["bmi_attendance_allowance"] = $bmi_attendance_allowance;
    }

    if ($cid == 215) {
        $data["gbr_attendance_allowance"] = $gbr_attendance_allowance;
        $data["gbr_night_shifts"] = $gbr_night_shifts;
    }
    if (in_array($cid, companies_allowed_for_shift_allowance())) {
        $data["monthly_dsa_count"] = $monthly_dsa_count;
        $data["monthly_nsa_count"] = $monthly_nsa_count;
    }
    if ($cid == 152) {
        $data['lsk_non_worked_days'] = $lsk_non_worked_days;
    }

    if ($cid == 229) {
        $data['ln01_waived_days'] = $ln01_waived_days;
        $data['ln01_attendance_allowance_days'] = $ln01_attendance_allowance_days;
    }

    if ($cid == 206 || in_array($cid, companies_allowed_for_meal_allowance())) {
        $data["food_allowance_days"] = $food_allowance_days;
    }


    return $data;
}
/**
 * get list of actual off days for an employee
 *
 * @param int $id
 * @param string $first_day
 * @param string $last_day
 * @return array
 */
function get_off_days_list($id, $first_day, $last_day)
{
    $ci = &get_instance();
    return $ci->db->select('*')
        ->from('employee_off_days')
        ->where('employee_id', $id)
        ->where('date >=', $first_day)
        ->where('date <=', $last_day)
        ->get()
        ->result();
}
/**
 * check if a specific date is a replaced public holiday for an employee
 *
 * @param int $employee_id
 * @param string $date (YYYY-MM-DD)
 * @return bool
 */
function is_employee_off_day($employee_id, $date)
{
    $ci = &get_instance();
    return $ci->db->where('employee_id', $employee_id)
        ->where('date', $date)
        ->count_all_results('employee_off_days') > 0;
}
