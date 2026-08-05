<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Attendance_sheet extends CI_Controller
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
        $details = $this->db->select('id, employee_id, first_name, date, is_present, is_leave, is_absent, is_rest_day, is_off_day, shift_name,clock_in')
            ->from('month_lock_details')
            ->where('lock_id', $lock_id)
            ->order_by('employee_id', 'ASC')
            ->order_by('date', 'ASC')
            ->get()
            ->result();

        // Fetch all active employees
        $active_employees_db = $this->db->select('e.id, e.first_name, e.last_name, d.name as department')
            ->from('employees e')
            ->join('departments d', 'd.id = e.department_id', 'left')
            ->where('e.company_id', $company_id)
            ->where('e.deleted_at IS NULL')
            ->order_by('e.first_name', 'ASC')
            ->get()
            ->result();

        $employees = [];
        $dates = [];

        // Generate period dates based on lock start and end
        $start = new DateTime($lock->start_date);
        $end = new DateTime($lock->end_date);
        $end->modify('+1 day'); // for DatePeriod
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);

        foreach ($period as $dt) {
            $dates[] = $dt->format('Y-m-d');
        }

        // Initialize with all active employees
        foreach ($active_employees_db as $e) {
            $emp_id = $e->id;
            $employees[$emp_id] = [
                'id' => $emp_id,
                'name' => trim($e->first_name . ' ' . $e->last_name),
                'department' => $e->department ?: 'N/A',
                'attendance' => []
            ];
            foreach ($dates as $dt) {
                $employees[$emp_id]['attendance'][$dt] = [
                    'status' => '-',
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
                    'department' => 'N/A',
                    'attendance' => []
                ];
                // Initialize attendance array
                foreach ($dates as $dt) {
                    $employees[$emp_id]['attendance'][$dt] = [
                        'status' => '-',
                        'class' => ''
                    ];
                }
            }

            // Determine status
            $status = '-';
            $class = '';

            if ((int) $d->is_present === 1 || (!empty($d->clock_in) && $d->clock_in != '00:00:00')) {
                $status = 'Present';
                $class = 'fa-calendar-check color-calendar-check';
            } elseif ((int) $d->is_leave === 1) {
                $status = 'Leave';
                $class = 'fa-calendar-plus color-calendar-plus';
            } elseif ((int) $d->is_absent === 1) {
                $status = 'Absent';
                $class = 'fa-calendar-times color-calendar-times';
            } elseif ((int) $d->is_rest_day === 1) {
                $status = 'Rest Day';
                $class = 'fa-calendar-o color-calendar-o';
            } elseif ((int) $d->is_off_day === 1) {
                $status = 'Off Day';
                $class = 'fa-calendar-o color-calendar-o';
            }

            $date_formatted = date('Y-m-d', strtotime($d->date));
            if (isset($employees[$emp_id]['attendance'][$date_formatted])) {
                $employees[$emp_id]['attendance'][$date_formatted] = [
                    'status' => $status,
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
