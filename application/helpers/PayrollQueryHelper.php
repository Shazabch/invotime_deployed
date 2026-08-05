<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PayrollQueryHelper — Independent payroll data retrieval layer
 *
 * Self-contained helper with complete function implementations copied from general_helper.
 * No longer depends on general_helper functions (except calculate_summary_data which is too complex).
 *
 * Benefits:
 * - Zero dependency on general_helper for 6 out of 7 key functions
 * - Proper type casting for PHP 8+ compatibility
 * - All implementations in one place for easier maintenance
 * - Can be unit tested independently
 *
 * Functions implemented:
 * - get_approved_ot_list() - Fetch approved OT records (FULLY INDEPENDENT)
 * - get_result_list() - Get regular clockings (FULLY INDEPENDENT)
 * - get_result_list_overnight() - Get overnight clockings (FULLY INDEPENDENT)
 * - get_company_ot_settings() - Fetch OT settings by company (FULLY INDEPENDENT)
 * - get_company_early_ot_settings() - Fetch early OT settings (FULLY INDEPENDENT)
 * - get_company_working_hours() - Fetch company working hours (FULLY INDEPENDENT)
 * - calculate_summary_data() - Wrapper only (too complex to duplicate)
 */
class PayrollQueryHelper
{
    /**
     * Get approved OT list - fetch OT approval records from auto_approve_days table
     *
     * @param  array  $shift_ids  Array of shift IDs
     * @param  string $first_day  Start date (Y-m-d)
     * @param  string $last_day   End date (Y-m-d)
     * @return array  Approved OT data with shift_id, approve_date, is_approved
     */
    public static function get_approved_ot_list($shift_ids, $first_day, $last_day)
    {
        $ci = &get_instance();

        // Ensure shift_ids is array of integers
        $shift_ids = array_map(function($id) {
            return (int) $id;
        }, (array) $shift_ids);
        $shift_ids[] = 0;

        $result = $ci->db->select('shift_id, approve_date, is_approved')
                         ->from('auto_approve_days')
                         ->where_in('shift_id', $shift_ids)
                         ->where('approve_date >=', $first_day)
                         ->where('approve_date <=', $last_day)
                         ->get()
                         ->result();

        return (array) $result;
    }

    /**
     * Get result list - fetch regular clockings with shift and remarks data
     *
     * @param  array  $employees  Employee IDs
     * @param  string $first_day  Start date
     * @param  string $last_day   End date
     * @return array  Clockings with shift details
     */
    public static function get_result_list($employees, $first_day, $last_day)
    {
        if (!function_exists('get_clockings_table_name')) {
            throw new Exception('get_clockings_table_name() not found');
        }

        $clockings_table = get_clockings_table_name($first_day);
        $first_day_adjusted = date('Y-m-d', strtotime('-1 day', strtotime($first_day)));
        $ci = &get_instance();

        // Ensure employees is array of integers
        $employees = array_map(function($id) {
            return (int) $id;
        }, (array) $employees);

        $result = $ci->db->select('c.employee_id,c.id,date_format(clock_in,"%d/%m %a") as day_f,clock_in as clock_in_o, date_format(clock_in,"%H:%i") as clock_in, date_format(clock_in,"%d-%m-%Y %H:%i") as clock_in_1,date_format(clock_out,"%H:%i") as clock_out,date_format(clock_out,"%d-%m-%Y %H:%i") as clock_out_1,clock_in_id,clock_out_id,s.grace_time as grace_time_o, date_format(s.end_time,"%H:%i") as end_time, date_format(s.grace_time,"%H:%i") as grace_time, s.start_time as start_time_o, date_format(s.start_time, "%H:%i") as start_time, s.name,s.code,reason,c.remark,date_format(end_time,"%H:%i") as end_time,date_format(overtime_starts,"%H:%i") as overtime_starts,date_format(early_ot_start,"%H:%i") as early_ot_start,date_format(early_ot_end,"%H:%i") as early_ot_end,time_format(timediff(end_time,start_time),"%H:%i") as shift_hours, fixed_ot, fixed_overtime, auto_approve_ot, r.remark as shift_remark, sr.remark as staff_remark, is_leave,void_late_in,void_early_out, date_format(break_duration,"%H:%i") as break_duration, date_format(break_1,"%H:%i") as break_1, consider_break_1, date_format(break_2,"%H:%i") as break_2, consider_break_2, date_format(break_3,"%H:%i") as break_3, consider_break_3, date_format(break_4,"%H:%i") as break_4, consider_break_4, date_format(break_5,"%H:%i") as break_5, consider_break_5, date_format(break_6,"%H:%i") as break_6, consider_break_6, half_day,date_format(clock_in, "%Y-%m-%d") as search_date, s.extra_ot, date_format(s.extra_ot_worked_hours_more_than, "%H:%i") as extra_ot_worked_hours_more_than, date_format(s.extra_ot_hours, "%H:%i") as extra_ot_hours, date_format(extra_break_1,"%H:%i") as extra_break_1, date_format(extra_break_2,"%H:%i") as extra_break_2, date_format(extra_break_3,"%H:%i") as extra_break_3, date_format(extra_break_4,"%H:%i") as extra_break_4, date_format(extra_break_5,"%H:%i") as extra_break_5, date_format(extra_break_6,"%H:%i") as extra_break_6, extra_break, date_format(extra_break_worked_hours_more_than, "%H:%i") as extra_break_worked_hours_more_than', false)
                         ->from($clockings_table . ' c')
                         ->join('shifts s', 'c.shift_id = s.id', 'left')
                         ->join('remarks r', 'r.remark_date = date(clock_in) and r.employee_id = c.employee_id', 'left')
                         ->join('staff_remarks sr', 'sr.remark_date = DATE(clock_in) AND sr.employee_id = c.employee_id', 'left')
                         ->where('clock_in >', $first_day_adjusted . ' 00:00:00')
                         ->where('clock_in <', $last_day . ' 23:59:59')
                         ->where_in('c.employee_id', $employees)
                         ->order_by('clock_in_o')
                         ->get()
                         ->result();

        return (array) $result;
    }

    /**
     * Get result list overnight - fetch overnight clockings with shift data
     *
     * @param  array  $employees    Employee IDs
     * @param  string $first_day    Start date
     * @param  string $last_day     End date
     * @param  int    $company_id   Company ID
     * @return array  Overnight clockings
     */
    public static function get_result_list_overnight($employees, $first_day, $last_day, $company_id = null)
    {
        if (!function_exists('get_clockings_table_name') || !function_exists('get_interval_minutes')) {
            throw new Exception('Required helper functions not found');
        }

        $clockings_table = get_clockings_table_name($first_day);
        $first_day_adjusted = date('Y-m-d', strtotime('-1 day', strtotime($first_day)));
        $ci = &get_instance();

        // Get company_id from user session if not provided
        if (!$company_id) {
            $user = function_exists('get_user') ? get_user() : null;
            $company_id = $user && isset($user['company_id']) ? $user['company_id'] : 1;
        }

        $company_id = (int) $company_id;
        $interval_minutes = get_interval_minutes($company_id);

        // Ensure employees is array of integers
        $employees = array_map(function($id) {
            return (int) $id;
        }, (array) $employees);

        $result = $ci->db->select('c.employee_id,c.id,date_format(date_sub(clock_in, interval ' . $interval_minutes . ' minute),"%d/%m %a") as day_f,clock_in as clock_in_o, date_format(clock_in,"%H:%i") as clock_in, date_format(clock_in,"%d-%m-%Y %H:%i") as clock_in_1,date_format(clock_out,"%H:%i") as clock_out,date_format(clock_out,"%d-%m-%Y %H:%i") as clock_out_1,clock_in_id,clock_out_id,s.grace_time as grace_time_o, date_format(s.end_time,"%H:%i") as end_time, date_format(s.grace_time,"%H:%i") as grace_time, s.start_time as start_time_o, date_format(s.start_time, "%H:%i") as start_time, s.name,s.code,reason,c.remark,date_format(end_time,"%H:%i") as end_time,date_format(overtime_starts,"%H:%i") as overtime_starts,date_format(early_ot_start,"%H:%i") as early_ot_start,date_format(early_ot_end,"%H:%i") as early_ot_end,time_format(timediff(end_time,start_time),"%H:%i") as shift_hours, fixed_ot, fixed_overtime, auto_approve_ot, r.remark as shift_remark, sr.remark as staff_remark, is_leave,void_late_in,void_early_out, date_format(break_duration,"%H:%i") as break_duration, date_format(break_1,"%H:%i") as break_1, consider_break_1, date_format(break_2,"%H:%i") as break_2, consider_break_2, date_format(break_3,"%H:%i") as break_3, consider_break_3, date_format(break_4,"%H:%i") as break_4, consider_break_4, date_format(break_5,"%H:%i") as break_5, consider_break_5, date_format(break_6,"%H:%i") as break_6, consider_break_6, half_day,date_format(date_sub(clock_in, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, s.extra_ot, date_format(s.extra_ot_worked_hours_more_than, "%H:%i") as extra_ot_worked_hours_more_than, date_format(s.extra_ot_hours, "%H:%i") as extra_ot_hours, date_format(extra_break_1,"%H:%i") as extra_break_1, date_format(extra_break_2,"%H:%i") as extra_break_2, date_format(extra_break_3,"%H:%i") as extra_break_3, date_format(extra_break_4,"%H:%i") as extra_break_4, date_format(extra_break_5,"%H:%i") as extra_break_5, date_format(extra_break_6,"%H:%i") as extra_break_6, extra_break, date_format(extra_break_worked_hours_more_than, "%H:%i") as extra_break_worked_hours_more_than', false)
                         ->from($clockings_table . ' c')
                         ->join('shifts s', 'c.shift_id = s.id', 'left')
                         ->join('remarks r', 'r.remark_date = date(date_sub(clock_in, interval ' . $interval_minutes . ' minute)) and r.employee_id = c.employee_id', 'left')
                         ->join('staff_remarks sr', 'sr.remark_date = date(date_sub(clock_in, interval ' . $interval_minutes . ' minute)) and sr.employee_id = c.employee_id', 'left')
                         ->where('date(date_sub(clock_in, interval ' . $interval_minutes . ' minute)) >=', $first_day_adjusted)
                         ->where('date(date_sub(clock_in, interval ' . $interval_minutes . ' minute)) <=', $last_day)
                         ->where_in('c.employee_id', $employees)
                         ->order_by('clock_in_o')
                         ->get()
                         ->result();

        return (array) $result;
    }

    /**
     * Get company OT settings - fetch OT rounding settings for all branches
     *
     * @param  int $company_id
     * @return array  OT settings by branch
     */
    public static function get_company_ot_settings($company_id)
    {
        $company_id = (int) $company_id;
        $ci = &get_instance();

        $result = $ci->db->select('start, end, round_to, first_hour, branch_id')
                         ->from('ot_round_settings o')
                         ->join('branches b', 'b.id = o.branch_id')
                         ->where('b.company_id', $company_id)
                         ->get()
                         ->result();

        return (array) $result;
    }

    /**
     * Get company early OT settings - fetch early OT rounding settings
     *
     * @param  int $company_id
     * @return array  Early OT settings by branch
     */
    public static function get_company_early_ot_settings($company_id)
    {
        $company_id = (int) $company_id;
        $ci = &get_instance();

        $result = $ci->db->select('start, end, round_to, branch_id')
                         ->from('early_ot_round_settings o')
                         ->join('branches b', 'b.id = o.branch_id')
                         ->where('b.company_id', $company_id)
                         ->get()
                         ->result();

        return (array) $result;
    }

    /**
     * Get company working hours - fetch default working hours setting
     *
     * @param  int $company_id
     * @return array  Working hours group settings
     */
    public static function get_company_working_hours($company_id)
    {
        if ($company_id === false || $company_id === null) {
            $user = function_exists('get_user') ? get_user() : null;
            $company_id = $user && isset($user['company_id']) ? $user['company_id'] : 1;
        }

        $company_id = (int) $company_id;
        $ci = &get_instance();

        $result = $ci->db->select('id, group_id, date_format(total_hours,"%H:%i") as working_hours, date_format(half_hours, "%H:%i") as half_hours')
                         ->from('company_working_hours')
                         ->where('company_id', $company_id)
                         ->get()
                         ->result();

        return (array) $result;
    }

    /**
     * Calculate summary data - **WRAPPER ONLY** for this complex function
     *
     * This function is too large and complex (1000+ lines) to duplicate without creating
     * massive code maintenance issues. It depends on 20+ internal helper functions.
     * Using a wrapper for type safety while keeping dependencies minimal.
     *
     * @param  int    $employee_id
     * @param  string $first_day
     * @param  string $last_day
     * @param  string $type
     * @param  object $employee
     * @param  array  $result_list
     * @param  array  $result_list_overnight
     * @param  array  $company_working_hours
     * @param  bool   $public_holidays
     * @param  array  $company_ot_settings
     * @param  array  $company_early_ot_settings
     * @param  array  $approved_ot_list
     * @param  array  $branch_rest_days
     * @param  int    $company_id
     * @param  array  &$worked_rest_days_array
     * @param  array  &$worked_off_days_array
     * @param  array  &$worked_holidays_array
     * @param  array  &$unpaid_leaves_absent_days
     * @param  array  &$clockings_news
     * @param  array  &$clockings_news_overnight
     * @param  array  &$paid_leaves_array
     * @param  array  &$daily_ot_array
     * @param  array  &$daily_late_array
     * @param  array  &$days_settings
     * @param  array  &$ot_type_data_map
     * @param  PayrollBulkHelper $bulk
     * @return array  Summary data
     */
    public static function calculate_summary_data(
        $employee_id,
        $first_day,
        $last_day,
        $type,
        $employee,
        $result_list,
        $result_list_overnight,
        $company_working_hours,
        $public_holidays,
        $company_ot_settings,
        $company_early_ot_settings,
        $approved_ot_list,
        $branch_rest_days,
        $company_id,
        &$worked_rest_days_array,
        &$worked_off_days_array,
        &$worked_holidays_array,
        &$unpaid_leaves_absent_days,
        &$clockings_news,
        &$clockings_news_overnight,
        &$paid_leaves_array,
        &$daily_ot_array,
        &$daily_late_array,
        &$days_settings,
        &$ot_type_data_map,
        $bulk = null
    ) {
        // Cast numeric parameters to proper types
        $employee_id = (int) $employee_id;
        $company_id = (int) $company_id;

        // Call original helper
        $result = calculate_summary_data(
            $employee_id,
            $first_day,
            $last_day,
            $type,
            $employee,
            $result_list,
            $result_list_overnight,
            $company_working_hours,
            $public_holidays,
            $company_ot_settings,
            $company_early_ot_settings,
            $approved_ot_list,
            $branch_rest_days,
            $company_id,
            $worked_rest_days_array,
            $worked_off_days_array,
            $worked_holidays_array,
            $unpaid_leaves_absent_days,
            $clockings_news,
            $clockings_news_overnight,
            $paid_leaves_array,
            $daily_ot_array,
            $daily_late_array,
            $days_settings,
            $ot_type_data_map,
            $bulk
        );

        return (array) $result;
    }
}
