<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Month_lock_model extends CI_Model
{
    private $locks_table = 'month_locks';
    private $details_table = 'month_lock_details';
    private $summary_table = 'month_lock_summary';

    public function __construct()
    {
        parent::__construct();
    }

    private function apply_branch_filter($branch_id)
    {
        if ($branch_id === null || $branch_id === '' || (int)$branch_id === 0) {
            $this->db->where($this->locks_table . '.branch_id IS NULL', null, false);
            return;
        }

        $this->db->where($this->locks_table . '.branch_id', (int)$branch_id);
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', (int)$id)->get($this->locks_table)->row();
    }

    public function get_existing_lock($company_id, $branch_id, $lock_year, $lock_month)
    {
        $this->db->from($this->locks_table)
            ->where('company_id', (int)$company_id)
            ->where('lock_year', (int)$lock_year)
            ->where('lock_month', (int)$lock_month);

        if ($branch_id === null || $branch_id === '' || (int)$branch_id === 0) {
            $this->db->where('branch_id IS NULL', null, false);
        } else {
            $this->db->where('branch_id', (int)$branch_id);
        }

        return $this->db->get()->row();
    }

    public function find_overlapping_lock($company_id, $branch_id, $start_date, $end_date, $exclude_lock_id = null)
    {
        $this->db->from($this->locks_table)
            ->where('company_id', (int)$company_id)
            ->where('start_date <=', $end_date)
            ->where('end_date >=', $start_date)
            ->where_in('status', array('pending', 'processing', 'completed'));

        if ($exclude_lock_id !== null) {
            $this->db->where('id !=', (int)$exclude_lock_id);
        }

        if ($branch_id === null || $branch_id === '' || (int)$branch_id === 0) {
            $this->db->where('branch_id IS NULL', null, false);
        } else {
            $this->db->where('branch_id', (int)$branch_id);
        }

        return $this->db->order_by('id', 'DESC')->get()->row();
    }

    public function create_lock($data)
    {
        $payload = array(
            'company_id' => (int)$data['company_id'],
            'branch_id' => isset($data['branch_id']) && (int)$data['branch_id'] > 0 ? (int)$data['branch_id'] : null,
            'lock_year' => (int)$data['lock_year'],
            'lock_month' => (int)$data['lock_month'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => 'pending',
            'total_employees' => 0,
            'total_records' => 0,
            'locked_by' => isset($data['locked_by']) ? (int)$data['locked_by'] : null,
            'error_message' => null,
            'locked_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        $this->db->insert($this->locks_table, $payload);
        return (int)$this->db->insert_id();
    }

    public function list_locks($company_id, $limit = 50)
    {
        $limit = max(1, min(200, (int)$limit));
        return $this->db
            ->select('ml.id, ml.company_id, ml.branch_id, b.name as branch_name, ml.lock_year, ml.lock_month, ml.start_date, ml.end_date, ml.status, ml.total_employees, ml.total_records, ml.locked_by, ml.locked_at, ml.error_message, ml.created_at, ml.updated_at')
            ->from($this->locks_table . ' ml')
            ->join('branches b', 'b.id = ml.branch_id', 'left')
            ->where('ml.company_id', (int)$company_id)
            ->order_by('ml.id', 'DESC')
            ->limit($limit)
            ->get()
            ->result();
    }

    public function mark_processing($lock_id)
    {
        return $this->db->where('id', (int)$lock_id)->update($this->locks_table, array(
            'status' => 'processing',
            'error_message' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ));
    }

    public function mark_completed($lock_id, $total_employees, $total_records, $endate)
    {
        return $this->db->where('id', (int)$lock_id)->update($this->locks_table, array(
            'status' => 'completed',
            'end_date' => $endate,
            'total_employees' => (int)$total_employees,
            'total_records' => (int)$total_records,
            'locked_at' => date('Y-m-d H:i:s'),
            'error_message' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ));
    }

    /**
     * Like mark_completed(), but for incremental nightly syncs that have NOT yet
     * reached the end of the month. Updates the running employee/record counts and
     * timestamp, but deliberately keeps status = 'rolling' instead of 'completed' —
     * so run_daily_sync()'s "completed manual lock" lookup (status='completed') never
     * matches a rolling lock that's only partway through the month, and the lock keeps
     * getting picked up and synced on subsequent nights until the real month-end run
     * calls mark_completed().
     */
    public function mark_rolling_synced($lock_id, $total_employees, $total_records, $endate)
    {
        return $this->db->where('id', (int)$lock_id)->update($this->locks_table, array(
            'status' => 'rolling',
            'is_auto_rolling' => 1,
            'end_date' => $endate,
            'total_employees' => (int)$total_employees,
            'total_records' => (int)$total_records,
            'error_message' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ));
    }

    public function mark_failed($lock_id, $error_message)
    {
        return $this->db->where('id', (int)$lock_id)->update($this->locks_table, array(
            'status' => 'failed',
            'error_message' => (string)$error_message,
            'updated_at' => date('Y-m-d H:i:s')
        ));
    }

    public function reset_for_retry($lock_id)
    {
        return $this->db->where('id', (int)$lock_id)->update($this->locks_table, array(
            'status' => 'pending',
            'error_message' => null,
            'total_employees' => 0,
            'total_records' => 0,
            'locked_at' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ));
    }

    public function unlock_lock($lock_id, $actor_id = null, $reason = null)
    {
        $lock = $this->get_by_id($lock_id);
        if (!$lock) {
            return false;
        }

        $this->clear_lock_data($lock_id);

        $notes = array();
        $notes[] = 'Unlocked at ' . date('Y-m-d H:i:s');
        if ($actor_id !== null) {
            $notes[] = 'by user #' . (int)$actor_id;
        }
        if ($reason) {
            $notes[] = 'reason: ' . trim($reason);
        }

        return $this->db->where('id', (int)$lock_id)->update($this->locks_table, array(
            'status' => 'failed',
            'error_message' => implode(' | ', $notes),
            'total_employees' => 0,
            'total_records' => 0,
            'locked_at' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ));
    }

    public function clear_lock_data($lock_id)
    {
        $lock_id = (int)$lock_id;
        $this->db->delete($this->details_table, array('lock_id' => $lock_id));
        $this->db->delete($this->summary_table, array('lock_id' => $lock_id));
    }

    public function delete_lock_data($lock_id, $actor_id = null, $reason = null)
    {
        $lock = $this->get_by_id($lock_id);
        if (!$lock) {
            return false;
        }

        $this->db->like('payload', '"lock_id":' . (int)$lock_id, 'after');
        $this->db->where('type', 'month_lock_generate')->delete('job_queue');

        $this->clear_lock_data($lock_id);

        return (bool)$this->db->where('id', (int)$lock_id)->delete($this->locks_table);
    }
    public function clear_lock_details_for_range($lock_id, $start_date, $end_date)
    {
        return $this->db
            ->where('lock_id', (int)$lock_id)
            ->where('date >=', $start_date)
            ->where('date <=', $end_date)
            ->delete($this->details_table);
    }

    public function clear_lock_summary($lock_id)
    {
        return $this->db->delete($this->summary_table, array('lock_id' => (int)$lock_id));
    }
    public function insert_summary_batch($rows)
    {
        if (empty($rows)) {
            return true;
        }
        return $this->db->insert_batch($this->summary_table, $rows);
    }

    public function insert_details_batch($rows)
    {
        if (empty($rows)) {
            return true;
        }
        return $this->db->insert_batch($this->details_table, $rows);
    }

    public function get_completed_lock_for_period($company_id, $branch_id, $start_date, $end_date)
    {
        $this->db->from($this->locks_table)
            ->where('company_id', (int)$company_id)
            ->where('start_date', $start_date)
            ->where('end_date', $end_date)
            ->where('status', 'completed');

        if ($branch_id === null || $branch_id === '' || (int)$branch_id === 0) {
            $this->db->where('branch_id IS NULL', null, false);
        } else {
            $this->db->where('branch_id', (int)$branch_id);
        }

        return $this->db->order_by('id', 'DESC')->get()->row();
    }

    public function get_completed_lock_for_date($company_id, $branch_id, $target_date)
    {
        $this->db->from($this->locks_table)
            ->where('company_id', (int)$company_id)
            ->where('status', 'completed')
            ->where('start_date <=', $target_date)
            ->where('end_date >=', $target_date);

        if ($branch_id === null || $branch_id === '' || (int)$branch_id === 0) {
            $this->db->where('branch_id IS NULL', null, false);
        } else {
            $this->db->group_start()
                ->where('branch_id', (int)$branch_id)
                ->or_where('branch_id IS NULL', null, false)
                ->group_end();
        }

        return $this->db->order_by('id', 'DESC')->get()->row();
    }

    public function find_completed_lock_for_range($company_id, $branch_id, $start_date, $end_date)
    {
        $this->db->from($this->locks_table)
            ->where('company_id', (int)$company_id)
            ->where('status', 'completed')
            ->where('start_date <=', $end_date)
            ->where('end_date >=', $start_date);

        if ($branch_id === null || $branch_id === '' || (int)$branch_id === 0) {
            $this->db->where('branch_id IS NULL', null, false);
        } else {
            $this->db->group_start()
                ->where('branch_id', (int)$branch_id)
                ->or_where('branch_id IS NULL', null, false)
                ->group_end();
        }

        return $this->db->order_by('id', 'DESC')->get()->row();
    }

    public function get_completed_locks_for_span($company_id, $start_date, $end_date)
    {
        return $this->db
            ->select('id, company_id, branch_id, start_date, end_date, status, locked_at, updated_at')
            ->from($this->locks_table)
            ->where('company_id', (int)$company_id)
            ->where_in('status', array('completed', 'rolling'))
            ->where('start_date <=', $end_date)
            ->where('end_date >=', $start_date)
            ->order_by('start_date', 'ASC')
            ->order_by('end_date', 'ASC')
            ->order_by('id', 'DESC')
            ->get()
            ->result();
    }

    public function is_supported_report_type($type)
    {
        $supported = array(
            'pending_worked_hours',
            'pending_shift_worked_hours',
            'pending_unpaid_leaves',
            'pending_daily_waged',
            'pending_early_lates',
            'pending_worked_rest_days',
            'pending_worked_off_days',
            'pending_worked_holidays',
            'pending_deductions'
        );

        return in_array($type, $supported, true);
    }

    public function get_report_rows_from_lock($lock_id, $type)
    {
        $rows = $this->db
            ->select('*')
            ->from($this->summary_table)
            ->where('lock_id', (int)$lock_id)
            ->order_by('special_id', 'ASC')
            ->get()
            ->result_array();

        if (empty($rows)) {
            return array();
        }

        $data = array();

        foreach ($rows as $r) {
            $base = array(
                'id' => (int)$r['employee_id'],
                'employee_id' => (int)$r['employee_id'],
                'first_name' => $r['first_name'],
                'special_id' => $r['special_id'],
                'department' => $r['department'],
                'position' => $r['position']
            );

            switch ($type) {
                case 'pending_worked_hours':
                    $base['work_hours'] = $r['work_hours'];
                    $base['monthly_working_hours'] = $r['monthly_working_hours'];
                    $base['worked_days'] = (int)$r['worked_days'];
                    break;

                case 'pending_shift_worked_hours':
                    $base['shift_hours_total'] = $r['shift_hours_total'];
                    $base['monthly_working_hours'] = $r['monthly_working_hours'];
                    break;

                case 'pending_unpaid_leaves':
                    $base['unpaid_leaves'] = (float)$r['unpaid_leaves'];
                    $base['absent_days'] = (int)$r['absent_days'];
                    break;

                case 'pending_daily_waged':
                    $base['working_days'] = (int)$r['working_days'];
                    $base['worked_days'] = (int)$r['worked_days'];
                    $base['absent_days'] = (int)$r['absent_days'];
                    $base['paid_leaves'] = (float)$r['paid_leaves'];
                    $base['unpaid_leaves'] = (float)$r['unpaid_leaves'];
                    break;

                case 'pending_early_lates':
                    $base['late_count'] = (int)$r['late_count'];
                    $base['lateness_time'] = $r['lateness_time'];
                    $base['lateness_time_deducted'] = (float)$r['lateness_time_deducted'];
                    $base['early_out_count'] = (int)$r['early_out_count'];
                    $base['total_early'] = $r['total_early'];
                    break;

                case 'pending_worked_rest_days':
                    $base['worked_rest_days'] = (float)$r['worked_rest_days'];
                    break;

                case 'pending_worked_off_days':
                    $base['worked_off_days'] = (float)$r['worked_off_days'];
                    break;

                case 'pending_worked_holidays':
                    $base['worked_holidays'] = (float)$r['worked_holidays'];
                    $base['total_holidays'] = (int)$r['total_holidays'];
                    break;

                case 'pending_deductions':
                    $base['lateness_time_deducted'] = (float)$r['lateness_time_deducted'];
                    $base['month_overtime_deducted'] = (float)$r['month_overtime_deducted'];
                    break;

                default:
                    break;
            }

            $data[] = $base;
        }

        return $data;
    }

    public function count_summary_rows($lock_id, $search = null)
    {
        $this->db
            ->from($this->summary_table)
            ->where('lock_id', (int)$lock_id);

        if (!empty($search)) {
            $this->db->group_start()
                ->like('first_name', $search)
                ->or_like('special_id', $search)
                ->or_like('department', $search)
                ->or_like('position', $search)
                ->group_end();
        }

        return (int)$this->db->count_all_results();
    }

    public function get_summary_rows($lock_id, $limit = 50, $offset = 0, $search = null)
    {
        $limit = max(1, min(500, (int)$limit));
        $offset = max(0, (int)$offset);

        $this->db
            ->select('*')
            ->from($this->summary_table)
            ->where('lock_id', (int)$lock_id);

        if (!empty($search)) {
            $this->db->group_start()
                ->like('first_name', $search)
                ->or_like('special_id', $search)
                ->or_like('department', $search)
                ->or_like('position', $search)
                ->group_end();
        }

        return $this->db
            ->order_by('special_id', 'ASC')
            ->limit($limit, $offset)
            ->get()
            ->result_array();
    }

    public function get_all_summary_rows($lock_id)
    {
        return $this->db
            ->select('*')
            ->from($this->summary_table)
            ->where('lock_id', (int)$lock_id)
            ->order_by('special_id', 'ASC')
            ->get()
            ->result_array();
    }

    public function get_all_detail_rows($lock_id, $employee_ids = array())
    {
        $this->db
            ->select('*')
            ->from($this->details_table)
            ->where('lock_id', (int)$lock_id);

        if (!empty($employee_ids)) {
            $this->db->where_in('employee_id', array_map('intval', (array)$employee_ids));
        }

        return $this->db
            ->order_by('date', 'ASC')
            ->order_by('special_id', 'ASC')
            ->get()
            ->result_array();
    }

    public function count_detail_rows($lock_id, $date = null, $employee_id = null, $search = null)
    {
        $this->db->from($this->details_table)
            ->where('lock_id', (int)$lock_id);

        if (!empty($date)) {
            $this->db->where('date', $date);
        }

        if (!empty($employee_id)) {
            $this->db->where('employee_id', (int)$employee_id);
        }

        if (!empty($search)) {
            $this->db->group_start()
                ->like('date', $search)
                ->or_like('first_name', $search)
                ->or_like('special_id', $search)
                ->or_like('department', $search)
                ->or_like('position', $search)
                ->or_like('shift_name', $search)
                ->or_like('clockings_json', $search)
                ->group_end();
        }

        return (int)$this->db->count_all_results();
    }

    public function get_detail_rows($lock_id, $limit = 100, $offset = 0, $date = null, $employee_id = null, $search = null)
    {
        $limit = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);

        $this->db
            ->select('*')
            ->from($this->details_table)
            ->where('lock_id', (int)$lock_id);

        if (!empty($date)) {
            $this->db->where('date', $date);
        }

        if (!empty($employee_id)) {
            $this->db->where('employee_id', (int)$employee_id);
        }

        if (!empty($search)) {
            $this->db->group_start()
                ->like('date', $search)
                ->or_like('first_name', $search)
                ->or_like('special_id', $search)
                ->or_like('department', $search)
                ->or_like('position', $search)
                ->or_like('shift_name', $search)
                ->or_like('clock_in', $search)
                ->or_like('clock_out', $search)
                ->or_like('clockings_json', $search)
                ->group_end();
        }

        return $this->db
            ->order_by('date', 'ASC')
            ->order_by('special_id', 'ASC')
            ->limit($limit, $offset)
            ->get()
            ->result_array();
    }
    public function has_lock_data_for_date($company_id, $branch_id, $target_date)
    {
        return $this->get_completed_lock_for_date($company_id, $branch_id, $target_date) !== null;
    }
}
