<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends CI_Controller
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
        
        $locks = $this->db->select('id, start_date, end_date, locked_at')
            ->from('month_locks')
            ->where('company_id', (int)$current_user['company_id'])
            ->where('status', 'completed')
            ->order_by('id', 'DESC')
            ->limit(50)
            ->get()
            ->result();

        $data = [
            'status' => 'success',
            'data' => []
        ];

        foreach ($locks as $lock) {
            $data['data'][] = [
                'id' => $lock->id,
                'name' => 'Monthly Summary - ' . date('F Y', strtotime($lock->start_date)),
                'type' => 'Data',
                'generated_at' => $lock->locked_at ? date('Y-m-d H:i:s', strtotime($lock->locked_at)) : '-',
                'size' => '-'
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
