<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Late_days extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (is_null(get_user())) {
            $this->output->set_status_header(401)->set_content_type('application/json')->set_output(json_encode(['error' => 'Unauthorized']));
            exit;
        }
    }

    public function index()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        $current_user = get_user();
        $company_id = (int) $current_user['company_id'];
        $lock_id = (int) $this->input->get('lock_id');

        if ($lock_id <= 0) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status' => 'error',
                'message' => 'Invalid lock_id'
            ]));
            return;
        }

        // Verify lock belongs to company
        $lock = $this->db->get_where('month_locks', ['id' => $lock_id, 'company_id' => $company_id])->row();
        if (!$lock) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'status' => 'error',
                'message' => 'Lock not found'
            ]));
            return;
        }

        // Fetch details
        $details = $this->db->select('id, employee_id, first_name, date, is_late, late_time, void_late, shift_name')
            ->from('month_lock_details')
            ->where('lock_id', $lock_id)
            ->order_by('employee_id', 'ASC')
            ->order_by('date', 'ASC')
            ->get()
            ->result();

        // Fetch all active employees
        $active_employees_db = $this->db->select('e.id, e.first_name, e.special_id, d.name as department')
            ->from('employees e')
            ->join('departments d', 'd.id = e.department_id', 'left')
            ->where('e.company_id', $company_id)
            ->where('e.deleted_at IS NULL')
            ->order_by('e.first_name', 'ASC')
            ->get()
            ->result();

        $employees = [];
        $dates = [];

        $start = new DateTime($lock->start_date);
        $end = new DateTime($lock->end_date);
        $end->modify('+1 day');
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);

        foreach ($period as $dt) {
            $dates[] = $dt->format('Y-m-d');
        }

        foreach ($active_employees_db as $e) {
            $emp_id = $e->id;
            $employees[$emp_id] = [
                'id' => $emp_id,
                'name' => trim($e->first_name),
                'special_id' => $e->special_id,
                'department' => $e->department ?: 'N/A',
                'data' => []
            ];
            foreach ($dates as $dt) {
                $employees[$emp_id]['data'][$dt] = [
                    'value' => '-',
                    'class' => ''
                ];
            }
        }

        foreach ($details as $d) {
            $emp_id = $d->employee_id;
            if (!isset($employees[$emp_id])) {
                $employees[$emp_id] = [
                    'id' => $emp_id,
                    'name' => trim($d->first_name),
                    'special_id' => '',
                    'department' => 'N/A',
                    'data' => []
                ];
                foreach ($dates as $dt) {
                    $employees[$emp_id]['data'][$dt] = [
                        'value' => '-',
                        'class' => ''
                    ];
                }
            }

            $value = '-';
            $class = '';

            if ((int)$d->is_late === 1) {
                $value = $d->late_time ?: 'Late';
                $class = ((int)$d->void_late === 1) ? 'text-success' : 'text-danger';
            }

            $date_formatted = date('Y-m-d', strtotime($d->date));
            if (isset($employees[$emp_id]['data'][$date_formatted])) {
                $employees[$emp_id]['data'][$date_formatted] = [
                    'value' => $value,
                    'class' => $class,
                    'shift' => $d->shift_name
                ];
            }
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'lock' => [
                    'period' => date('F Y', strtotime($lock->start_date)),
                    'start_date' => $lock->start_date,
                    'end_date' => $lock->end_date
                ],
                'dates' => $dates,
                'employees' => array_values($employees)
            ]));
    }
}
