<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Month_lock_snapshot_service
{
    private $db;
    private $month_lock;

    public function __construct($params = array())
    {
        $CI = get_instance();

        $this->db = isset($params['db']) ? $params['db'] : $CI->db;

        if (isset($params['month_lock'])) {
            $this->month_lock = $params['month_lock'];
        } else {
            $CI->load->model('Month_lock_model', 'month_lock');
            $this->month_lock = $CI->month_lock;
        }
    }

    public function resolve_snapshots($cid, $first_day, $last_day, $branch_ids = array())
    {
        $candidate_locks = $this->month_lock->get_completed_locks_for_span($cid, $first_day, $last_day);
        if (empty($candidate_locks)) {
            throw new Exception('No completed month lock found in the selected date span. Please lock the requested period first.');
        }

        // Use only locks that are fully inside the requested span to avoid over-counting from partial overlap.
        $contained_locks = array();
        foreach ($candidate_locks as $lock) {
            if ($lock->start_date >= $first_day && $lock->end_date <= $last_day) {
                $contained_locks[] = $lock;
            }
        }

        if (empty($contained_locks)) {
            throw new Exception('Completed locks exist, but none are fully contained in the selected period. Please align From/To with available lock ranges.');
        }

        $selected_locks = $this->select_preferred_month_locks($contained_locks, $first_day, $last_day, $branch_ids);
        if (empty($selected_locks)) {
            throw new Exception('No matching completed month lock found for the selected branch/date filters.');
        }

        $display_branch_name = null;
        $selected_branch_ids = array();
        foreach ($selected_locks as $lock) {
            if (!empty($lock->branch_id)) {
                $selected_branch_ids[] = (int)$lock->branch_id;
            }
        }
        $selected_branch_ids = array_values(array_unique($selected_branch_ids));

        if (count($selected_branch_ids) === 1) {
            $branch_row = $this->db->select('name')->from('branches')->where('id', $selected_branch_ids[0])->get()->row();
            if ($branch_row && !empty($branch_row->name)) {
                $display_branch_name = $branch_row->name;
            }
        }

        return array(
            'locks' => $selected_locks,
            'display_branch_name' => $display_branch_name
        );
    }

    public function merge_summary_rows($locks)
    {
        $rows_by_employee = array();

        foreach ($locks as $lock) {
            $rows = $this->month_lock->get_all_summary_rows((int)$lock->id);
            foreach ($rows as $row) {
                $employee_id = isset($row['employee_id']) ? (int)$row['employee_id'] : 0;
                if ($employee_id <= 0) {
                    continue;
                }

                if (!isset($rows_by_employee[$employee_id])) {
                    $rows_by_employee[$employee_id] = $row;
                    continue;
                }

                $rows_by_employee[$employee_id] = $this->merge_month_lock_summary_row($rows_by_employee[$employee_id], $row);
            }
        }

        $merged_rows = array_values($rows_by_employee);
        usort($merged_rows, function ($a, $b) {
            $left = isset($a['special_id']) ? (string)$a['special_id'] : '';
            $right = isset($b['special_id']) ? (string)$b['special_id'] : '';
            return strcmp($left, $right);
        });

        return $merged_rows;
    }

    public function merge_detail_rows($locks, $employee_ids, $first_day, $last_day)
    {
        $rows_by_employee_date = array();

        foreach ($locks as $lock) {
            $rows = $this->month_lock->get_all_detail_rows((int)$lock->id, $employee_ids);
            foreach ($rows as $row) {
                if (empty($row['date']) || $row['date'] < $first_day || $row['date'] > $last_day) {
                    continue;
                }

                $employee_id = isset($row['employee_id']) ? (int)$row['employee_id'] : 0;
                if ($employee_id <= 0) {
                    continue;
                }

                $key = $employee_id . '|' . $row['date'];
                $row['__lock_id'] = (int)$lock->id;

                if (!isset($rows_by_employee_date[$key]) || (int)$row['__lock_id'] > (int)$rows_by_employee_date[$key]['__lock_id']) {
                    $rows_by_employee_date[$key] = $row;
                }
            }
        }

        $merged_rows = array_values($rows_by_employee_date);
        foreach ($merged_rows as &$merged_row) {
            unset($merged_row['__lock_id']);
        }
        unset($merged_row);

        usort($merged_rows, function ($a, $b) {
            if ($a['date'] === $b['date']) {
                $left = isset($a['special_id']) ? (string)$a['special_id'] : '';
                $right = isset($b['special_id']) ? (string)$b['special_id'] : '';
                return strcmp($left, $right);
            }
            return strcmp($a['date'], $b['date']);
        });

        return $merged_rows;
    }

    public function collect_lateness_monthly_rows($locks, $first_day, $last_day)
    {
        $lock_rows_cache = array();
        foreach ($locks as $lock) {
            $lock_rows_cache[(int)$lock->id] = $this->month_lock->get_all_summary_rows((int)$lock->id);
        }

        $month_rows = array();
        $start_date = new DateTime($first_day);
        $end_date = new DateTime($last_day);
        $current_date = clone $start_date;
        $current_date->modify('first day of this month');
        $end_month = clone $end_date;
        $end_month->modify('first day of next month');

        while ($current_date < $end_month) {
            $month_name = $current_date->format('F Y');
            $month_start_date = clone $current_date;
            $month_end_date = clone $current_date;
            $month_end_date->modify('last day of this month');

            if ($month_start_date < $start_date) {
                $month_start_date = clone $start_date;
            }
            if ($month_end_date > $end_date) {
                $month_end_date = clone $end_date;
            }

            if ($month_start_date <= $month_end_date) {
                $rows_by_employee = array();
                $month_start = $month_start_date->format('Y-m-d');
                $month_end = $month_end_date->format('Y-m-d');

                foreach ($locks as $lock) {
                    if ($lock->start_date > $month_end || $lock->end_date < $month_start) {
                        continue;
                    }

                    $rows = isset($lock_rows_cache[(int)$lock->id]) ? $lock_rows_cache[(int)$lock->id] : array();
                    foreach ($rows as $row) {
                        $employee_id = isset($row['employee_id']) ? (int)$row['employee_id'] : 0;
                        if ($employee_id <= 0) {
                            continue;
                        }

                        if (!isset($rows_by_employee[$employee_id])) {
                            $rows_by_employee[$employee_id] = $row;
                            continue;
                        }

                        $rows_by_employee[$employee_id] = $this->merge_month_lock_summary_row($rows_by_employee[$employee_id], $row);
                    }
                }

                $month_rows[] = array(
                    'month_name' => $month_name,
                    'from' => $month_start_date->format('d/m/Y'),
                    'to' => $month_end_date->format('d/m/Y'),
                    'rows_by_employee' => $rows_by_employee
                );
            }

            $current_date->modify('+1 month');
        }

        return $month_rows;
    }

    private function select_preferred_month_locks($locks, $first_day, $last_day, $branch_ids = array())
    {
        $branch_ids = array_values(array_unique(array_map('intval', (array)$branch_ids)));

        $company_locks = array();
        $branch_locks = array();
        foreach ($locks as $lock) {
            if (empty($lock->branch_id)) {
                $company_locks[] = $lock;
            } else {
                $branch_locks[] = $lock;
            }
        }

        $selected = array();

        if (count($branch_ids) === 1) {
            $target_branch_id = (int)$branch_ids[0];
            $target_branch_locks = array_values(array_filter($branch_locks, function ($lock) use ($target_branch_id) {
                return (int)$lock->branch_id === $target_branch_id;
            }));

            if (!empty($target_branch_locks)) {
                $selected = $target_branch_locks;
                $selected = $this->add_fallback_locks_for_uncovered_dates($selected, $company_locks, $first_day, $last_day);
            } else {
                $selected = $company_locks;
            }
        } else {
            $eligible_branch_locks = $branch_locks;
            if (!empty($branch_ids)) {
                $eligible_branch_locks = array_values(array_filter($branch_locks, function ($lock) use ($branch_ids) {
                    return in_array((int)$lock->branch_id, $branch_ids, true);
                }));
            }

            if (!empty($company_locks)) {
                $selected = $company_locks;
                $selected = $this->add_fallback_locks_for_uncovered_dates($selected, $eligible_branch_locks, $first_day, $last_day);
            } else {
                $selected = $eligible_branch_locks;
            }
        }

        $deduped = array();
        $seen = array();
        foreach ($selected as $lock) {
            $key = (int)$lock->id;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $deduped[] = $lock;
        }

        usort($deduped, function ($a, $b) {
            if ($a->start_date === $b->start_date) {
                if ($a->end_date === $b->end_date) {
                    return (int)$a->id <=> (int)$b->id;
                }
                return strcmp($a->end_date, $b->end_date);
            }
            return strcmp($a->start_date, $b->start_date);
        });

        return $deduped;
    }

    private function add_fallback_locks_for_uncovered_dates($selected_locks, $fallback_locks, $first_day, $last_day)
    {
        if (empty($fallback_locks)) {
            return $selected_locks;
        }

        $covered_dates = array();
        foreach ($selected_locks as $selected_lock) {
            $this->mark_lock_covered_dates($covered_dates, $selected_lock, $first_day, $last_day);
        }

        usort($fallback_locks, function ($a, $b) {
            if ($a->start_date === $b->start_date) {
                if ($a->end_date === $b->end_date) {
                    return (int)$b->id <=> (int)$a->id;
                }
                return strcmp($a->end_date, $b->end_date);
            }
            return strcmp($a->start_date, $b->start_date);
        });

        foreach ($fallback_locks as $fallback_lock) {
            if ($this->lock_has_uncovered_dates($covered_dates, $fallback_lock, $first_day, $last_day)) {
                $selected_locks[] = $fallback_lock;
                $this->mark_lock_covered_dates($covered_dates, $fallback_lock, $first_day, $last_day);
            }
        }

        return $selected_locks;
    }

    private function lock_has_uncovered_dates($covered_dates, $lock, $first_day, $last_day)
    {
        $from = max($first_day, $lock->start_date);
        $to = min($last_day, $lock->end_date);

        if ($from > $to) {
            return false;
        }

        $cursor = new DateTime($from);
        $end = new DateTime($to);

        while ($cursor <= $end) {
            $key = $cursor->format('Y-m-d');
            if (empty($covered_dates[$key])) {
                return true;
            }
            $cursor->modify('+1 day');
        }

        return false;
    }

    private function mark_lock_covered_dates(&$covered_dates, $lock, $first_day, $last_day)
    {
        $from = max($first_day, $lock->start_date);
        $to = min($last_day, $lock->end_date);

        if ($from > $to) {
            return;
        }

        $cursor = new DateTime($from);
        $end = new DateTime($to);

        while ($cursor <= $end) {
            $covered_dates[$cursor->format('Y-m-d')] = true;
            $cursor->modify('+1 day');
        }
    }

    private function merge_month_lock_summary_row($base, $incoming)
    {
        $skip_fields = array('id', 'lock_id', 'company_id', 'branch_id', 'employee_id', 'created_at', 'updated_at');
        $text_fields = array('first_name', 'special_id', 'department', 'position', 'branch_name');
        $time_fields = array('total_hours', 'work_hours', 'shift_hours_total', 'monthly_working_hours', 'lateness_time', 'total_early', 'total_short', 'break_late', 'month_overtime');

        foreach ($incoming as $field => $value) {
            if (in_array($field, $skip_fields, true)) {
                continue;
            }

            if (in_array($field, $text_fields, true)) {
                if ((!isset($base[$field]) || $base[$field] === '' || $base[$field] === null) && $value !== '' && $value !== null) {
                    $base[$field] = $value;
                }
                continue;
            }

            if (in_array($field, $time_fields, true)) {
                $base_minutes = isset($base[$field]) ? $this->time_to_minutes($base[$field]) : 0;
                $incoming_minutes = $this->time_to_minutes($value);
                $base[$field] = $this->minutes_to_time_value($base_minutes + $incoming_minutes);
                continue;
            }

            if (is_numeric($value)) {
                $current = isset($base[$field]) && is_numeric($base[$field]) ? (float)$base[$field] : 0;
                $base[$field] = $current + (float)$value;
                continue;
            }

            if ((!isset($base[$field]) || $base[$field] === '' || $base[$field] === null) && $value !== '' && $value !== null) {
                $base[$field] = $value;
            }
        }

        return $base;
    }

    private function time_to_minutes($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            $decimal = (float)$value;
            if ($decimal <= 0) {
                return 0;
            }

            return (int)round($decimal * 60);
        }

        if (!is_string($value)) {
            return 0;
        }

        $value = trim($value);
        if ($value === '' || $value === '00:00' || $value === '00:00:00') {
            return 0;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $value, $matches)) {
            return ((int)$matches[1] * 60) + (int)$matches[2];
        }

        return 0;
    }

    private function minutes_to_time_value($minutes)
    {
        $minutes = (int)$minutes;
        if ($minutes <= 0) {
            return null;
        }

        $hours = (int)floor($minutes / 60);
        $remaining_minutes = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $remaining_minutes);
    }
}
