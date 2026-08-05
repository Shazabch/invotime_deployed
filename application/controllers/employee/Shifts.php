<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shifts extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: false');
        header('Content-Type: application/json; charset=UTF-8');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $this->output->set_content_type('application/json');
    }



    public function dashboard($employee_id = null)
    {
        $employee_id = (int)($employee_id ?: $this->input->get('employee_id'));
        if ($employee_id <= 0) {
            $this->json(array(
                'success' => false,
                'message' => 'employee_id is required'
            ), 400);
            return;
        }

        $employee = $this->db
            ->select('employees.id, employees.company_id, employees.branch_id, employees.department_id, employees.position_id, employees.special_id, employees.first_name, branches.name as branch_name, departments.name as department_name, positions.title as position_name')
            ->from('employees')
            ->join('branches', 'branches.id = employees.branch_id', 'left')
            ->join('departments', 'departments.id = employees.department_id', 'left')
            ->join('positions', 'positions.id = employees.position_id', 'left')
            ->where('employees.id', $employee_id)
            ->where('employees.deleted_at IS NULL', null, false)
            ->get()
            ->row();

        if (!$employee) {
            $this->json(array(
                'success' => false,
                'message' => 'Employee not found'
            ), 404);
            return;
        }

        $range = $this->resolve_date_range();
        if ($range === null) {
            return;
        }
        $start_date = $range['start_date'];
        $end_date = $range['end_date'];

        $shift_day_fields = $this->db->list_fields('shift_days');
        $shift_fields = $this->db->list_fields('shifts');

        $select_parts = array(
            'sd.date',
            'sd.shift_id',
            'sd.employees',
            's.name as shift_name',
            's.code as shift_code',
            's.color as shift_color'
        );

        if (in_array('half_day', $shift_day_fields, true)) {
            $select_parts[] = 'sd.half_day as shift_day_half_day';
        }
        if (in_array('is_paid', $shift_day_fields, true)) {
            $select_parts[] = 'sd.is_paid as shift_day_is_paid';
        }
        if (in_array('created_at', $shift_day_fields, true)) {
            $select_parts[] = 'sd.created_at as shift_day_created_at';
        }
        if (in_array('updated_at', $shift_day_fields, true)) {
            $select_parts[] = 'sd.updated_at as shift_day_updated_at';
        }

        if (in_array('overnight', $shift_fields, true)) {
            $select_parts[] = 's.overnight as shift_overnight';
        }
        if (in_array('same_day_overnight', $shift_fields, true)) {
            $select_parts[] = 's.same_day_overnight as shift_same_day_overnight';
        }
        if (in_array('half_day', $shift_fields, true)) {
            $select_parts[] = 's.half_day as shift_half_day';
        }
        if (in_array('is_leave', $shift_fields, true)) {
            $select_parts[] = 's.is_leave as shift_is_leave';
        }
        if (in_array('is_paid', $shift_fields, true)) {
            $select_parts[] = 's.is_paid as shift_is_paid';
        }
        if (in_array('start_time', $shift_fields, true)) {
            $select_parts[] = 's.start_time';
        }
        if (in_array('end_time', $shift_fields, true)) {
            $select_parts[] = 's.end_time';
        }
        if (in_array('grace_time', $shift_fields, true)) {
            $select_parts[] = 's.grace_time';
        }
        if (in_array('break_duration', $shift_fields, true)) {
            $select_parts[] = 's.break_duration';
        }
        if (in_array('branch_id', $shift_fields, true)) {
            $select_parts[] = 's.branch_id as shift_branch_id';
        }

        $query = $this->db
            ->select(implode(', ', $select_parts), false)
            ->from('shift_days sd')
            ->join('shifts s', 's.id = sd.shift_id', 'inner')
            ->where('s.company_id', (int)$employee->company_id)
            ->where('sd.date >=', $start_date)
            ->where('sd.date <=', $end_date)
            ->where('FIND_IN_SET(' . (int)$employee_id . ', sd.employees) >', 0, false)
            ->order_by('sd.date', 'ASC')
            ->get();

        if ($query === false) {
            $db_error = $this->db->error();
            $this->json(array(
                'success' => false,
                'message' => 'Failed to load shift rows',
                'db_error' => isset($db_error['message']) ? $db_error['message'] : 'Unknown SQL error'
            ), 500);
            return;
        }

        $shift_rows = $query->result();

        $shift_map = array();
        foreach ($shift_rows as $row) {
            $shift_map[$row->date] = $row;
        }

        $days = array();
        $period = new DatePeriod(
            new DateTime($start_date),
            new DateInterval('P1D'),
            (new DateTime($end_date))->modify('+1 day')
        );

        foreach ($period as $date) {
            $date_key = $date->format('Y-m-d');
            $matched_shift = isset($shift_map[$date_key]) ? $this->format_shift_row($shift_map[$date_key]) : null;

            $days[] = array(
                'date' => $date_key,
                'day_name' => $date->format('D'),
                'is_assigned' => $matched_shift ? true : false,
                'shift' => $matched_shift
            );
        }

        $assigned_days = array_values(array_map(function ($row) {
            return $this->format_shift_row($row);
        }, $shift_rows));

        $this->json(array(
            'success' => true,
            'data' => array(
                'employee' => array(
                    'id' => (int)$employee->id,
                    'company_id' => (int)$employee->company_id,
                    'branch_id' => $employee->branch_id !== null ? (int)$employee->branch_id : null,
                    'department_id' => $employee->department_id !== null ? (int)$employee->department_id : null,
                    'position_id' => $employee->position_id !== null ? (int)$employee->position_id : null,
                    'special_id' => $employee->special_id,
                    'first_name' => $employee->first_name,
                    'branch_name' => $employee->branch_name,
                    'department_name' => $employee->department_name,
                    'position_name' => $employee->position_name
                ),
                'period' => array(
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'month' => (int)date('m', strtotime($start_date)),
                    'year' => (int)date('Y', strtotime($start_date))
                ),
                'summary' => array(
                    'total_days' => count($days),
                    'assigned_days' => count($assigned_days),
                    'unassigned_days' => count($days) - count($assigned_days)
                ),
                // 'assigned_days' => $assigned_days,
                'days' => $days
            )
        ));
    }

    private function resolve_date_range()
    {
        $start_date = trim((string)$this->input->get('start_date'));
        $end_date = trim((string)$this->input->get('end_date'));

        if ($start_date !== '' || $end_date !== '') {
            $start = $this->parse_date($start_date);
            $end = $this->parse_date($end_date);

            if (!$start || !$end) {
                $this->json(array(
                    'success' => false,
                    'message' => 'Invalid start_date or end_date. Use YYYY-MM-DD or DD/MM/YYYY.'
                ), 400);
                return null;
            }

            if ($start > $end) {
                $this->json(array(
                    'success' => false,
                    'message' => 'start_date cannot be after end_date'
                ), 400);
                return null;
            }

            return array(
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d')
            );
        }

        $month = (int)$this->input->get('month');
        $year = (int)$this->input->get('year');

        if ($month < 1 || $month > 12) {
            $month = (int)date('m');
        }

        if ($year < 2000) {
            $year = (int)date('Y');
        }

        return array(
            'start_date' => sprintf('%04d-%02d-01', $year, $month),
            'end_date' => date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $year, $month)))
        );
    }

    private function parse_date($value)
    {
        if (!$value) {
            return null;
        }

        $formats = array('Y-m-d', 'd/m/Y');
        foreach ($formats as $format) {
            $date = DateTime::createFromFormat($format, $value);
            if ($date && $date->format($format) === $value) {
                return $date;
            }
        }

        return null;
    }

    private function format_shift_row($row)
    {
        return array(
            'date' => $row->date,
            'shift_id' => isset($row->shift_id) ? (int)$row->shift_id : null,
            'shift_name' => isset($row->shift_name) ? $row->shift_name : null,
            'shift_code' => isset($row->shift_code) ? $row->shift_code : null,
            'shift_color' => isset($row->shift_color) ? $row->shift_color : null,
            'shift_overnight' => isset($row->shift_overnight) ? $row->shift_overnight : null,
            'shift_same_day_overnight' => isset($row->shift_same_day_overnight) ? $row->shift_same_day_overnight : null,
            'shift_half_day' => isset($row->shift_half_day) ? $row->shift_half_day : null,
            'shift_is_leave' => isset($row->shift_is_leave) ? $row->shift_is_leave : null,
            'shift_is_paid' => isset($row->shift_is_paid) ? $row->shift_is_paid : null,
            'start_time' => isset($row->start_time) ? $row->start_time : null,
            'end_time' => isset($row->end_time) ? $row->end_time : null,
            'grace_time' => isset($row->grace_time) ? $row->grace_time : null,
            'break_duration' => isset($row->break_duration) ? $row->break_duration : null,

            'shift_day_half_day' => isset($row->shift_day_half_day) ? $row->shift_day_half_day : null,
            'shift_day_is_paid' => isset($row->shift_day_is_paid) ? $row->shift_day_is_paid : null,
            'shift_day_created_at' => isset($row->shift_day_created_at) ? $row->shift_day_created_at : null,
            'shift_day_updated_at' => isset($row->shift_day_updated_at) ? $row->shift_day_updated_at : null
        );
    }

    private function json($data, $code = 200)
    {
        $this->output->set_status_header($code);
        $this->output->set_content_type('application/json');
        $this->output->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}