<?php

/**
 * PayrollBulkHelper
 *
 * Optimized helper class for bulk data fetching in Payroll API, cron jobs, etc.
 * Replaces N+1 per-employee DB queries with single bulk queries + in-memory filtering.
 *
 * Usage:
 *   $bulk = new PayrollBulkHelper();
 *   $bulk->prefetch($employee_ids, $first_day, $last_day, $cid, $branch_ids);
 *   // Then per employee:
 *   $is_ot_list = $bulk->get_is_ot_list($emp_id);
 *   $shift_list = $bulk->get_shift_list($emp_id);
 *   $public_holidays = $bulk->get_public_holidays_mine($emp_id, $branch_id);
 *   $ph = $bulk->get_public_holiday_by_date($date, $branch_id);
 *   // etc.
 *
 * All logic and conditions are preserved exactly from general_helper.php.
 */
class PayrollBulkHelper
{
    /** @var object CI instance */
    private $ci;

    /** @var bool Whether prefetch has been called */
    private $prefetched = false;

    // --- Bulk-fetched data stores ---
    private $all_is_ot = [];
    private $all_is_late = [];
    private $all_is_late_break = [];
    private $all_is_early_out = [];
    private $all_manual_late = [];
    private $all_manual_late_break = [];
    private $all_manual_early_out = [];
    private $all_manual_ot = [];
    private $all_manual_short_hours = [];
    private $all_remark = [];
    private $all_staff_remark = [];
    private $all_trip_a = [];
    private $all_trip_b = [];
    private $all_replacement_leaves = [];
    private $all_replaced_ph = [];
    private $all_shifts = [];
    private $all_holidays = [];
    private $all_holidays_full = [];
    private $emp_groups_map = [];
    private $all_late_in_settings = [];
    private $all_late_break_settings = [];
    private $all_early_out_settings = [];

    // cid==66 allowance tables
    private $all_manual_ta = [];
    private $all_manual_ma = [];
    private $all_manual_ca = [];
    private $all_manual_spa = [];
    private $all_manual_aca = [];
    private $all_manual_fl = [];
    private $all_manual_cw = [];
    private $all_manual_mo = [];
    private $all_manual_shift1 = [];
    private $all_manual_shift2 = [];
    private $all_manual_shift3 = [];

    public function __construct()
    {
        $this->ci = &get_instance();
    }

    /**
     * Bulk-fetch ALL per-employee data in ~25 queries instead of N*17+ queries.
     *
     * @param array  $employee_ids  Array of employee IDs
     * @param string $first_day     Start date (Y-m-d)
     * @param string $last_day      End date (Y-m-d)
     * @param int    $cid           Company ID
     * @param array  $branch_ids    Array of unique branch IDs from the employee set
     */
    public function prefetch($employee_ids, $first_day, $last_day, $cid, $branch_ids = [])
    {
        if (empty($employee_ids)) {
            $this->prefetched = true;
            return;
        }

        $db = $this->ci->db;

        // 1. OT days
        $this->all_is_ot = $db->select('employee_id, id, is_ot, ot_date as date')
            ->from('ot_days')
            ->where_in('employee_id', $employee_ids)
            ->where('ot_date >=', $first_day)->where('ot_date <=', $last_day)
            ->get()->result();

        // 2. Late days
        $this->all_is_late = $db->select('employee_id, id, is_late, late_date as date')
            ->from('late_days')
            ->where_in('employee_id', $employee_ids)
            ->where('late_date >=', $first_day)->where('late_date <=', $last_day)
            ->get()->result();

        // 3. Late break days
        $this->all_is_late_break = $db->select('employee_id, id, is_late_break, late_break_date as date')
            ->from('late_break_days')
            ->where_in('employee_id', $employee_ids)
            ->where('late_break_date >=', $first_day)->where('late_break_date <=', $last_day)
            ->get()->result();

        // 4. Early out days
        $this->all_is_early_out = $db->select('employee_id, id, is_early_out, early_out_date as date')
            ->from('early_out_days')
            ->where_in('employee_id', $employee_ids)
            ->where('early_out_date >=', $first_day)->where('early_out_date <=', $last_day)
            ->get()->result();

        // 5. Manual late
        $this->all_manual_late = $db->select('employee_id, late_hours, date')
            ->from('manual_late')
            ->where_in('employee_id', $employee_ids)
            ->where('date >=', $first_day)->where('date <=', $last_day)
            ->get()->result();

        // 6. Manual late break
        $this->all_manual_late_break = $db->select('employee_id, late_hours_break, date')
            ->from('manual_late_break')
            ->where_in('employee_id', $employee_ids)
            ->where('date >=', $first_day)->where('date <=', $last_day)
            ->get()->result();

        // 7. Manual early out
        $this->all_manual_early_out = $db->select('employee_id, early_out, date')
            ->from('manual_early_out')
            ->where_in('employee_id', $employee_ids)
            ->where('date >=', $first_day)->where('date <=', $last_day)
            ->get()->result();

        // 8. Manual OT
        $this->all_manual_ot = $db->select('employee_id, overtime, type, date')
            ->from('manual_ot')
            ->where_in('employee_id', $employee_ids)
            ->where('date >=', $first_day)->where('date <=', $last_day)
            ->get()->result();

        // 9. Manual short hours
        $this->all_manual_short_hours = $db->select('employee_id, short_hours, date')
            ->from('manual_short_hours')
            ->where_in('employee_id', $employee_ids)
            ->where('date >=', $first_day)->where('date <=', $last_day)
            ->get()->result();

        // 10. Remarks
        $this->all_remark = $db->select('employee_id, remark, remark_date as date')
            ->from('remarks')
            ->where_in('employee_id', $employee_ids)
            ->where('remark_date >=', $first_day)->where('remark_date <=', $last_day)
            ->get()->result();

        // 11. Staff remarks
        $this->all_staff_remark = $db->select('employee_id, remark, remark_date as date')
            ->from('staff_remarks')
            ->where_in('employee_id', $employee_ids)
            ->where('remark_date >=', $first_day)->where('remark_date <=', $last_day)
            ->get()->result();

        // 12. Trips A
        $this->all_trip_a = $db->select('employee_id, no_of_trips, date')
            ->from('trips')
            ->where_in('employee_id', $employee_ids)
            ->where('type', 'a')
            ->where('date >=', $first_day)->where('date <=', $last_day)
            ->get()->result();

        // 13. Trips B
        $this->all_trip_b = $db->select('employee_id, no_of_trips, date')
            ->from('trips')
            ->where_in('employee_id', $employee_ids)
            ->where('type', 'b')
            ->where('date >=', $first_day)->where('date <=', $last_day)
            ->get()->result();

        // 14. Replacement leaves (raw SQL preserved from original)
        $emp_ids_escaped = implode(',', array_map(array($db, 'escape'), $employee_ids));
        $fd = $db->escape($first_day);
        $ld = $db->escape($last_day);
        $this->all_replacement_leaves = $db->query(
            "SELECT * FROM `replacement_leave_dates`
             WHERE `employee_id` IN ({$emp_ids_escaped})
             AND ((`from` >= {$fd} AND `from` <= {$ld}) OR (`to` >= {$fd} AND `to` <= {$ld}))
             AND `deleted_at` IS NULL"
        )->result();

        // 15. Replaced PH days
        $this->all_replaced_ph = $db->select('*')
            ->from('replaced_ph_days')
            ->where_in('employee_id', $employee_ids)
            ->where('date >=', $first_day)->where('date <=', $last_day)
            ->get()->result();

        // 16. Shift list (bulk for entire company, not per-employee FIND_IN_SET)
        $shift_first_day = date('Y-m-d', strtotime('-1 day', strtotime($first_day)));
        $shift_last_day = date('Y-m-d', strtotime('+1 day', strtotime($last_day)));
        $this->all_shifts = $db->select(
            "sd.employees, s.id, round_off_ot, s.name, acting_code, code, shift_code, overnight, same_day_overnight, s.half_day, is_leave, is_paid, void_late_in, void_early_out, date, end_time, is_approved,
            timediff(end_time, grace_time) as full_time,
            timediff(timediff(end_time, grace_time), break_duration) as work_time,
            is_rest_day, weekday_deduction, weekend_deduction, public_holiday_deduction,
            TIME_FORMAT(CASE
                WHEN overnight = 'No' OR (overnight = 'Yes' AND same_day_overnight = 'same')
                THEN TIMEDIFF(TIMEDIFF(`end_time`, `start_time`), `break_duration`)
                ELSE TIMEDIFF(TIMEDIFF(CONCAT(DATE_ADD(`date`, interval 1 DAY), ' ', `end_time`), CONCAT(`date`, ' ', `start_time`)), break_duration)
            END, '%H:%i:%s') AS shift_hours,
            auto_approve_ot, break_duration,
            break_1, consider_break_1, break_2, consider_break_2, break_3, consider_break_3,
            break_4, consider_break_4, break_5, consider_break_5, break_6, consider_break_6,
            start_time, extra_ot,
            date_format(extra_ot_worked_hours_more_than, '%H:%i') extra_ot_worked_hours_more_than,
            date_format(extra_ot_hours, '%H:%i') extra_ot_hours,
            if_ot_more_than, deduct_from_ot, max_ot_hours,
            coalesce(s.cut_off_time, c.cut_off_time, '07:00:00') as cut_off_time", FALSE
        )
            ->from('shift_days sd')
            ->join('shifts s', 's.id = sd.shift_id')
            ->join('companies c', 'c.id = s.company_id')
            ->where('s.company_id', $cid)
            ->where('date >=', $shift_first_day)
            ->where('date <=', $shift_last_day)
            ->get()->result();

        // Apply the same post-processing as the original get_shift_list()
        $this->all_shifts = calculate_break_and_shift_hours($this->all_shifts);

        // 17. Public holidays (with include/exclude group columns + branch_id)
        $this->all_holidays = $db->select('holiday_date, include_groups, exclude_groups, branch_id')
            ->from('public_holidays')
            ->where('company_id', $cid)
            ->where('holiday_date >=', $first_day)->where('holiday_date <=', $last_day)
            ->get()->result();

        // 18. Public holidays full row (for get_public_holiday_by_date replacement)
        $this->all_holidays_full = $db->from('public_holidays')
            ->where('company_id', $cid)
            ->where('holiday_date >=', $first_day)->where('holiday_date <=', $last_day)
            ->get()->result();

        // 19. Employee group memberships (for public holiday include/exclude filtering)
        $all_emp_groups = $db->select('employee_id, group_id')
            ->from('employee_groups_relation')
            ->where_in('employee_id', $employee_ids)
            ->get()->result();
        $this->emp_groups_map = [];
        foreach ($all_emp_groups as $eg) {
            if (!isset($this->emp_groups_map[$eg->employee_id])) {
                $this->emp_groups_map[$eg->employee_id] = [];
            }
            $this->emp_groups_map[$eg->employee_id][] = $eg->group_id;
        }

        // 20-22. Branch-level round settings
        if (!empty($branch_ids)) {
            $this->all_late_in_settings = $db->select('branch_id, start, end, round_to')
                ->from('late_in_round_settings')
                ->where_in('branch_id', $branch_ids)
                ->get()->result();

            $this->all_late_break_settings = $db->select('branch_id, start, end, round_to')
                ->from('late_break_round_settings')
                ->where_in('branch_id', $branch_ids)
                ->get()->result();

            $this->all_early_out_settings = $db->select('branch_id, start, end, round_to')
                ->from('early_out_round_settings')
                ->where_in('branch_id', $branch_ids)
                ->get()->result();
        }

        // 23-33. cid==66 allowance tables
        if ($cid == 66) {
            $allowance_tables = [
                'manual_ta', 'manual_ma', 'manual_ca', 'manual_spa', 'manual_aca',
                'manual_fl', 'manual_cw', 'manual_mo',
                'manual_shift1', 'manual_shift2', 'manual_shift3'
            ];
            foreach ($allowance_tables as $tbl) {
                $data = $db->select('employee_id, value, date')
                    ->from($tbl)
                    ->where_in('employee_id', $employee_ids)
                    ->where('date >=', $first_day)->where('date <=', $last_day)
                    ->get()->result();
                $prop = 'all_' . $tbl;
                $this->$prop = $data;
            }
        }

        $this->prefetched = true;
    }

    /**
     * Check if prefetch has been called.
     */
    public function is_prefetched()
    {
        return $this->prefetched;
    }

    // =========================================================================
    // FILTER-BY-EMPLOYEE METHODS
    // Each mirrors the original general_helper function's return format exactly.
    // =========================================================================

    /**
     * Generic filter: return rows from $list where employee_id == $id
     */
    private function filter_by_employee($list, $id)
    {
        $result = array();
        foreach ($list as $l) {
            if ($l->employee_id == $id) {
                $result[] = $l;
            }
        }
        return $result;
    }

    /** Same return format as get_is_ot_list() */
    public function get_is_ot_list($emp_id)
    {
        return $this->filter_by_employee($this->all_is_ot, $emp_id);
    }

    /** Same return format as get_is_late_list() */
    public function get_is_late_list($emp_id)
    {
        return $this->filter_by_employee($this->all_is_late, $emp_id);
    }

    /** Same return format as get_is_late_break_list() */
    public function get_is_late_break_list($emp_id)
    {
        return $this->filter_by_employee($this->all_is_late_break, $emp_id);
    }

    /** Same return format as get_is_early_out_list() */
    public function get_is_early_out_list($emp_id)
    {
        return $this->filter_by_employee($this->all_is_early_out, $emp_id);
    }

    /** Same return format as get_manual_late_list() */
    public function get_manual_late_list($emp_id)
    {
        return $this->filter_by_employee($this->all_manual_late, $emp_id);
    }

    /** Same return format as get_manual_late_break_list() */
    public function get_manual_late_break_list($emp_id)
    {
        return $this->filter_by_employee($this->all_manual_late_break, $emp_id);
    }

    /** Same return format as get_manual_early_out_list() */
    public function get_manual_early_out_list($emp_id)
    {
        return $this->filter_by_employee($this->all_manual_early_out, $emp_id);
    }

    /** Same return format as get_manual_ot_list() */
    public function get_manual_ot_list($emp_id)
    {
        return $this->filter_by_employee($this->all_manual_ot, $emp_id);
    }

    /** Same return format as get_manual_short_hours_list() */
    public function get_manual_short_hours_list($emp_id)
    {
        return $this->filter_by_employee($this->all_manual_short_hours, $emp_id);
    }

    /** Same return format as get_remark_list() */
    public function get_remark_list($emp_id)
    {
        return $this->filter_by_employee($this->all_remark, $emp_id);
    }

    /** Same return format as get_staff_remark_list() */
    public function get_staff_remark_list($emp_id)
    {
        return $this->filter_by_employee($this->all_staff_remark, $emp_id);
    }

    /** Same return format as get_trip_a_list() */
    public function get_trip_a_list($emp_id)
    {
        return $this->filter_by_employee($this->all_trip_a, $emp_id);
    }

    /** Same return format as get_trip_b_list() */
    public function get_trip_b_list($emp_id)
    {
        return $this->filter_by_employee($this->all_trip_b, $emp_id);
    }

    /** Same return format as get_replacement_leaves_list() */
    public function get_replacement_leaves_list($emp_id)
    {
        return $this->filter_by_employee($this->all_replacement_leaves, $emp_id);
    }

    /** Same return format as get_replaced_ph_list() */
    public function get_replaced_ph_list($emp_id)
    {
        return $this->filter_by_employee($this->all_replaced_ph, $emp_id);
    }

    /**
     * Same return format as get_shift_list().
     * Filters from bulk-fetched shifts using FIND_IN_SET logic in PHP.
     */
    public function get_shift_list($emp_id)
    {
        $result = array();
        $id_str = (string)$emp_id;
        foreach ($this->all_shifts as $l) {
            $emp_arr = explode(',', $l->employees);
            if (in_array($id_str, $emp_arr)) {
                $result[] = $l;
            }
        }
        return $result;
    }

    /**
     * Same logic as get_public_holidays_mine() from general_helper.php.
     * Preserves include_groups/exclude_groups filtering exactly.
     *
     * @param int       $emp_id
     * @param int|false $branch_id
     * @return array    Array of holiday_date strings
     */
    public function get_public_holidays_mine($emp_id, $branch_id = false)
    {
        $emp_group_ids = isset($this->emp_groups_map[$emp_id]) ? $this->emp_groups_map[$emp_id] : array();

        $dates = array();
        foreach ($this->all_holidays as $value) {
            // Branch filter: same logic as original WHERE (branch_id = X or branch_id = 0)
            if ($branch_id && $value->branch_id != 0 && $value->branch_id != $branch_id) {
                continue;
            }

            $include_groups = array_filter(explode(',', $value->include_groups));
            $exclude_groups = array_filter(explode(',', $value->exclude_groups));

            if (empty($include_groups) && empty($exclude_groups)) {
                $dates[] = $value->holiday_date;
                continue;
            }

            // If include groups are defined → only include if employee belongs
            if (!empty($include_groups) && array_intersect($emp_group_ids, $include_groups)) {
                $dates[] = $value->holiday_date;
                continue;
            }

            // If exclude groups are defined → include if employee is NOT in them
            if (!empty($exclude_groups) && !array_intersect($emp_group_ids, $exclude_groups)) {
                $dates[] = $value->holiday_date;
                continue;
            }
        }
        return $dates;
    }

    /**
     * Same logic as get_public_holiday_by_date() from general_helper.php.
     * Returns the full row object or null.
     *
     * @param string $date       Y-m-d format
     * @param int    $branch_id
     * @return object|null
     */
    public function get_public_holiday_by_date($date, $branch_id)
    {
        foreach ($this->all_holidays_full as $h) {
            if ($h->holiday_date == $date && ($h->branch_id == $branch_id || $h->branch_id == 0)) {
                return $h;
            }
        }
        return null;
    }

    // =========================================================================
    // BRANCH-LEVEL SETTINGS (pre-fetched)
    // Same return format as get_late_in_settings(), etc.
    // =========================================================================

    /** Same return format as get_late_in_settings($bid) */
    public function get_late_in_settings($branch_id)
    {
        $result = array();
        foreach ($this->all_late_in_settings as $l) {
            if ($l->branch_id == $branch_id) $result[] = $l;
        }
        return $result;
    }

    /** Same return format as get_late_break_settings($bid) */
    public function get_late_break_settings($branch_id)
    {
        $result = array();
        foreach ($this->all_late_break_settings as $l) {
            if ($l->branch_id == $branch_id) $result[] = $l;
        }
        return $result;
    }

    /** Same return format as get_early_out_settings($bid) */
    public function get_early_out_settings($branch_id)
    {
        $result = array();
        foreach ($this->all_early_out_settings as $l) {
            if ($l->branch_id == $branch_id) $result[] = $l;
        }
        return $result;
    }

    // =========================================================================
    // CID==66 ALLOWANCE METHODS
    // Same return format as get_manual_ta_list(), get_manual_ma_list(), etc.
    // =========================================================================

    public function get_manual_ta_list($emp_id)
    {
        return $this->filter_by_employee($this->all_manual_ta, $emp_id);
    }

    public function get_manual_ma_list($emp_id)
    {
        return $this->filter_by_employee($this->all_manual_ma, $emp_id);
    }

    public function get_manual_ca_list($emp_id)
    {
        return $this->filter_by_employee($this->all_manual_ca, $emp_id);
    }

    public function get_manual_spa_list($emp_id)
    {
        return $this->filter_by_employee($this->all_manual_spa, $emp_id);
    }

    public function get_manual_aca_list($emp_id)
    {
        return $this->filter_by_employee($this->all_manual_aca, $emp_id);
    }

    public function get_manual_fl_list($emp_id)
    {
        return $this->filter_by_employee($this->all_manual_fl, $emp_id);
    }

    public function get_manual_cw_list($emp_id)
    {
        return $this->filter_by_employee($this->all_manual_cw, $emp_id);
    }

    public function get_manual_mo_list($emp_id)
    {
        return $this->filter_by_employee($this->all_manual_mo, $emp_id);
    }

    public function get_manual_shift1_list($emp_id)
    {
        return $this->filter_by_employee($this->all_manual_shift1, $emp_id);
    }

    public function get_manual_shift2_list($emp_id)
    {
        return $this->filter_by_employee($this->all_manual_shift2, $emp_id);
    }

    public function get_manual_shift3_list($emp_id)
    {
        return $this->filter_by_employee($this->all_manual_shift3, $emp_id);
    }
}
