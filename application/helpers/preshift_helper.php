<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('get_result_list_preshift')) {
    function get_result_list_preshift($employees, $first_day, $last_day,$same_day = false)
    {
        $clockings_table = get_clockings_table_name($first_day);
        if($same_day){
            $first_day = date('Y-m-d', strtotime('-1 day', strtotime($first_day)));
        }else{
            $first_day = date('Y-m-d', strtotime('+1 day', strtotime($first_day)));
        }
        // $first_day = date('Y-m-d', strtotime('+1 day', strtotime($first_day)));
        $ci = &get_instance();

        $company_id = get_user()["company_id"];
        $preshift_buffer_minutes = get_preshift_buffer_minutes();

        $result = $ci->db->select('c.employee_id,c.id,date_format(date_add(clock_in, interval ' . $preshift_buffer_minutes . ' minute),"%d/%m %a") as day_f,clock_in as clock_in_o, date_format(clock_in,"%H:%i") as clock_in, date_format(clock_in,"%d-%m-%Y %H:%i") as clock_in_1,date_format(clock_out,"%H:%i") as clock_out,date_format(clock_out,"%d-%m-%Y %H:%i") as clock_out_1,clock_in_id,clock_out_id,s.grace_time as grace_time_o, date_format(s.end_time,"%H:%i") as end_time, date_format(s.grace_time,"%H:%i") as grace_time, s.start_time as start_time_o, date_format(s.start_time, "%H:%i") as start_time, s.name,s.code,reason,c.remark,date_format(end_time,"%H:%i") as end_time,date_format(overtime_starts,"%H:%i") as overtime_starts,date_format(early_ot_start,"%H:%i") as early_ot_start,date_format(early_ot_end,"%H:%i") as early_ot_end,time_format(timediff(end_time,start_time),"%H:%i") as shift_hours, fixed_ot, fixed_overtime, auto_approve_ot, r.remark as shift_remark, sr.remark as staff_remark, is_leave,void_late_in,void_early_out, date_format(break_duration,"%H:%i") as break_duration, date_format(break_1,"%H:%i") as break_1, consider_break_1, date_format(break_2,"%H:%i") as break_2, consider_break_2, date_format(break_3,"%H:%i") as break_3, consider_break_3, date_format(break_4,"%H:%i") as break_4, consider_break_4, date_format(break_5,"%H:%i") as break_5, consider_break_5, date_format(break_6,"%H:%i") as break_6, consider_break_6, half_day,date_format(date_add(clock_in, interval ' . $preshift_buffer_minutes . ' minute), "%Y-%m-%d") as search_date, s.extra_ot, date_format(s.extra_ot_worked_hours_more_than, "%H:%i") as extra_ot_worked_hours_more_than, date_format(s.extra_ot_hours, "%H:%i") as extra_ot_hours, date_format(extra_break_1,"%H:%i") as extra_break_1, date_format(extra_break_2,"%H:%i") as extra_break_2, date_format(extra_break_3,"%H:%i") as extra_break_3, date_format(extra_break_4,"%H:%i") as extra_break_4, date_format(extra_break_5,"%H:%i") as extra_break_5, date_format(extra_break_6,"%H:%i") as extra_break_6, extra_break, date_format(extra_break_worked_hours_more_than, "%H:%i") as extra_break_worked_hours_more_than', false)
            ->from($clockings_table . ' c')
            ->join('shifts s', 'c.shift_id = s.id', 'left')
            ->join('remarks r', 'r.remark_date = date(date_add(clock_in, interval ' . $preshift_buffer_minutes . ' minute)) and r.employee_id = c.employee_id', 'left')
            ->join('staff_remarks sr', 'sr.remark_date = date(date_add(clock_in, interval ' . $preshift_buffer_minutes . ' minute)) and sr.employee_id = c.employee_id', 'left')
            ->where('date(date_add(clock_in, interval ' . $preshift_buffer_minutes . ' minute)) >=', $first_day)
            ->where('date(date_add(clock_in, interval ' . $preshift_buffer_minutes . ' minute)) <=', $last_day)
            ->where_in('c.employee_id', $employees)
            ->order_by('clock_in_o')
            ->get()
            ->result();

        return $result;
    }
}

if (!function_exists('get_preshift_buffer_minutes')) {
    function get_preshift_buffer_minutes()
    {
        return 180;
    }
}


// Function to get clockings from next day for preshift (similar to get_clockings_from_previous_day)
function get_clockings_from_next_day_for_preshift($result, $result_list_preshift, $date, $emp_id, $shift_list = array())
{
    // $result              = current day's clocking result (being assembled for $date)
    // $result_list_preshift = all preshift clockings for the period
    // $date                = current date being processed (e.g. "2024-01-14" = Sunday)
    // $emp_id              = employee ID
    // $shift_list          = all shift assignments for this employee

    // Nothing to strip if current result is already empty
    if (empty($result)) {
        return $result;
    }

    // Look at the NEXT day
    $next_date = date('Y-m-d', strtotime('+1 day', strtotime($date)));

    // Check if next day has a preshift shift assigned
    $next_day_shift = search_from_list($shift_list, $next_date);

    // If next day has no shift, or its shift is NOT preshift, nothing to strip
    // NOTE: use is_preshift, NOT preshift — that's the correct DB column name
    if (!$next_day_shift || $next_day_shift->is_preshift != "Yes") {
        return $result;
    }

    // Get all preshift clockings for next day (these are physically from $date — today)
    // search_clocking_by_id matches on search_date = $next_date (after date_add)
    $next_day_preshift_clockings = search_clocking_by_id($result_list_preshift, $next_date, $emp_id);

    // If next day has no preshift clockings at all, nothing to strip
    if (empty($next_day_preshift_clockings)) {
        return $result;
    }

    // Collect the IDs of clockings already claimed by next day's preshift
    $preshift_ids = [];
    foreach ($next_day_preshift_clockings as $clocking) {
        $preshift_ids[] = $clocking->id;
        // NOTE: $clocking->id is the PRIMARY KEY of the clockings record.
        // Using ID ensures we match the exact record, not just the time.
    }

    // Strip from current day's result any clocking whose ID is already
    // claimed by next day's preshift. This prevents duplicates.
    $filtered_result = [];
    foreach ($result as $clocking) {
        if (!in_array($clocking->id, $preshift_ids)) {
            $filtered_result[] = $clocking;
        }
    }

    return $filtered_result;
}

// Function to remove previous day clockings for preshift (mirror of remove_next_day_clockings)
function remove_previous_day_clockings($result, $shift_check, $prev_shift_check)
{
    // $result        = the preshift clockings for current day (Day X)
    // $shift_check   = current day's shift (which is is_preshift = Yes)
    // $prev_shift_check = previous day's shift (Day X-1)

    // If result is empty or previous day had no shift, nothing to do
    if (empty($result) || !$prev_shift_check) {
        return $result;
    }

    // The only real conflict case:
    // If previous day had an OVERNIGHT shift, it physically owns all clockings
    // on prev_date. Some of those clockings may have been pulled into our
    // preshift window by date_add() — they need to be removed from preshift.
    //
    // WHY: overnight shift "date" is Day X-1. All clockings physically on
    // Day X-1 belong to overnight. Preshift must not steal them.
    if ($prev_shift_check->overnight == "Yes") {
        $prev_date = $prev_shift_check->date; // the date string of Day X-1

        $new_result = [];
        foreach ($result as $clocking) {
            // clock_in_o is the RAW physical clock_in datetime (e.g. "2024-01-14 23:30:00")
            // Extract just the date part to check which calendar day it physically happened
            $physical_date = date('Y-m-d', strtotime($clocking->clock_in_o));

            // If the physical clock_in is on prev_date, overnight already owns it → skip
            if ($physical_date == $prev_date) {
                continue;
            }

            // Physical clock_in is on current date (early morning like 00:30 AM)
            // Preshift can keep it
            $new_result[] = $clocking;
        }

        return $new_result;
    }

    // Previous day was a normal day (not overnight) — no conflict possible
    // because the normal result_list for Day X-1 only searches by DATE(clock_in),
    // which means it can only claim clockings physically on Day X-1.
    // But our preshift also claims those exact same Day X-1 late-night clockings.
    // HOWEVER: get_clockings_from_next_day_for_preshift (called separately)
    // already handles this by stripping preshift-claimed clockings FROM Day X-1's result.
    // So here we just return result unchanged.
    return $result;
}
function get_result_list_preshift_basic($employees, $first_day, $last_day)
{
  $clockings_table = get_clockings_table_name($first_day);
  $company_id = get_user()["company_id"];
  $interval_minutes = get_interval_minutes($company_id);
  $first_day_shifted = date('Y-m-d', strtotime('-1 day', strtotime($first_day)));
  $ci = &get_instance();
  return $ci->db->select('employee_id,date_format(date_add(clock_in, interval ' . $interval_minutes . ' minute),"%d/%m %a") as day_f,clock_in as clock_in_o, date_format(clock_in,"%H:%i") as clock_in, date_format(clock_in,"%d-%m-%Y %H:%i") as clock_in_1,date_format(clock_out,"%H:%i") as clock_out,date_format(clock_out,"%d-%m-%Y %H:%i") as clock_out_1,date_format(date_add(clock_in, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, clock_in_id, clock_out_id', false)->from($clockings_table . ' c')->where('date(date_add(clock_in, interval ' . $interval_minutes . ' minute)) >=', $first_day_shifted)->where('date(date_add(clock_in, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $employees)->order_by('clock_in_o')->get()->result();
}

function merge_result_with_shifts_preshift_aware($result2_normal, $result2_preshift, $shift_data_list)
{
  // Same as merge_result_with_shifts, but for shift days flagged is_preshift == "Yes",
  // the clock_in is pulled from $result2_preshift (min clock-in grouped by the
  // preshift-adjusted date) instead of $result2_normal (grouped by literal date).
  // This is needed because a preshift employee's actual clock-in can fall on the
  // calendar day before the shift's own date.
  $result = [];
  foreach ($shift_data_list as $shift_data) {
    $result[] = (array) $shift_data;
    $result[count($result) - 1]["clock_in"] = null;

    $source = (isset($shift_data->is_preshift) && $shift_data->is_preshift == "Yes") ? $result2_preshift : $result2_normal;

    foreach ($source as $result2_data) {
      if ($result2_data["clocking_date"] == $shift_data->shift_date) {
        $result[count($result) - 1]["clock_in"] = $result2_data["clock_in"];
        break;
      }
    }
  }
  return $result;
}

function preshift_normalized_minutes($time)
{
  // Plain "H:i" comparisons break for preshift shifts because the clock-in
  // (e.g. 23:50 the night before) and the shift start (e.g. 00:10) straddle
  // midnight: as strings "23:50" > "00:10", which wrongly reads as "late".
  // This normalizes a time-of-day into minutes, treating anything from noon
  // onward as belonging to "the night before" (i.e. negative/earlier),
  // so a pre-midnight clock-in correctly sorts before an early-morning start.
  if ($time === "" || $time === null) {
    return null;
  }
  $parts = explode(":", $time);
  $minutes = ((int) $parts[0]) * 60 + (int) $parts[1];
  if ($minutes >= 12 * 60) {
    $minutes -= 24 * 60;
  }
  return $minutes;
}