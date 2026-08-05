<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Absents extends CI_Controller
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

        $absents = $this->db->select('d.id, d.first_name, d.department, d.date, d.is_leave')
            ->from('month_lock_details d')
            ->join('month_locks ml', 'ml.id = d.lock_id')
            ->where('ml.company_id', $company_id)
            // ->where('d.is_absent', 1)
            ->where('d.clock_in', '00:00:00')
            ->order_by('d.date', 'DESC')
            ->limit(50)
            ->get()
            ->result();

        $data = [
            'status' => 'success',
            'data' => []
        ];

        foreach ($absents as $ab) {
            $data['data'][] = [
                'id' => $ab->id,
                'employee_name' => $ab->first_name,
                'department' => $ab->department ?: 'N/A',
                'date' => date('Y-m-d', strtotime($ab->date)),
                'reason' => $ab->is_leave ? 'Leave' : 'Absent',
                'status' => 'locked'
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
