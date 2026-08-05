<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Locked_data extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Optional: Ensure user is logged in
        if (is_null(get_user())) {
            $this->output->set_status_header(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }

    public function index()
    {
        $current_user = get_user();
        $this->load->model('Month_lock_model', 'month_lock');
        
        $locks = $this->month_lock->list_locks($current_user['company_id'], 50);

        $data = [
            'status' => 'success',
            'data' => []
        ];

        foreach ($locks as $lock) {
            $data['data'][] = [
                'id' => $lock->id,
                'period' => date('F Y', strtotime($lock->start_date)),
                'start_date' => $lock->start_date,
                'end_date' => $lock->end_date,
                'status' => $lock->status,
                'employees_locked' => $lock->total_employees,
                'locked_by' => $lock->locked_by ? "User #{$lock->locked_by}" : 'System'
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
