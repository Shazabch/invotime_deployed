<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lates extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (is_null(get_user())) {
            $this->output->set_status_header(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }

    public function index()
    {
        $current_user = get_user();
        $company_id = (int) $current_user['company_id'];

        $lates = $this->db->select('d.id, d.first_name, d.department, d.date, d.shift_name, d.clock_in, d.late_minutes, d.late_time')
            ->from('month_lock_details d')
            ->join('month_locks ml', 'ml.id = d.lock_id')
            ->where('ml.company_id', $company_id)
            ->where('d.late_minutes >', 0)
            ->order_by('d.date', 'DESC')
            ->limit(50)
            ->get()
            ->result();

        $data = [
            'status' => 'success',
            'data' => []
        ];

        foreach ($lates as $lt) {
            $data['data'][] = [
                'id' => $lt->id,
                'employee_name' => $lt->first_name,
                'department' => $lt->department ?: 'N/A',
                'date' => date('Y-m-d', strtotime($lt->date)),
                'shift' => $lt->shift_name ?: 'N/A',
                'clock_in' => $lt->clock_in ?: '-',
                'late_by' => $lt->late_time ?: '-'
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
