<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Month_lock extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (is_null(get_user())) {
            redirect('welcome');
        }
    }

    public function index()
    {
        $current_user = get_user();

        $data = array();
        $data['pageTitle'] = 'Clocking Dashboard';
        $data['active_menu'] = 'month_lock';
        $data['menusTop'] = $this->_get_month_lock_menus();
        $data["menus"] = get_menus();
        $data['company_id'] = (int) $current_user['company_id'];
        // $data['is_month_lock'] = true;

        $data['branches'] = $this->db
            ->select('id, name')
            ->from('branches')
            ->where('company_id', (int) $current_user['company_id'])
            ->order_by('name', 'ASC')
            ->get()
            ->result();

        $this->load->view('month_lock/header', $data);
        $this->load->view('sidebar', $data);
        $this->load->view('month_lock/topbar', $data);
        $this->load->view('month_lock/index', $data);
        $this->load->view('footer', $data);
    }

    public function details($lock_id = null)
    {
        $current_user = get_user();
        $cid = (int) $current_user['company_id'];
        $lock_id = (int) $lock_id;
        // $data['is_month_lock'] = true;

        if ($lock_id <= 0) {
            show_404();
            return;
        }

        $lock = $this->db
            ->select('ml.*, b.name as branch_name')
            ->from('month_locks ml')
            ->join('branches b', 'b.id = ml.branch_id', 'left')
            ->where('ml.id', $lock_id)
            ->where('ml.company_id', $cid)
            ->get()
            ->row();

        if (!$lock) {
            show_404();
            return;
        }

        $data = array();
        $data['pageTitle'] = 'Lock Details: ' . date('d M Y', strtotime($lock->start_date)) . ' - ' . date('d M Y', strtotime($lock->end_date));
        $data['active_menu'] = 'month_lock';
         $data['menusTop'] = $this->_get_month_lock_menus();
        $data["menus"] = get_menus();
        $data['company_id'] = $cid;
        // $data['is_month_lock'] = true;
        $data['lock'] = $lock;

        $this->load->view('month_lock/header', $data);
        $this->load->view('sidebar', $data);
        $this->load->view('month_lock/topbar', $data);
        $this->load->view('month_lock/details', $data);
        $this->load->view('footer', $data);
    }

    private function _get_month_lock_menus()
    {
        return array(

            array(
                "title" => "Clocking Dashboard",
                "url" => "month_lock",
                "icon" => "fa fa-dashboard",
                "status" => true,
                "sub_menus" => null
            ),
            array(
                "title" => "Locked Data",
                "url" => "month_lock/locked_data",
                "icon" => "fa fa-database",
                "status" => false,
                "sub_menus" => null
            ),
            array(
                "title" => "REPORTS & SHEETS",
                "is_title" => true,
                "status" => false
            ),
            array(
                "title" => "Attendance",
                "url" => null,
                "icon" => "fa fa-list-alt",
                "status" => true,
                "sub_menus" => array(
                    array(
                        "title" => "Main Sheet",
                        "url" => "month_lock/attendance_sheet",
                        "icon" => "fa fa-calendar-check-o",
                        "status" => true
                    ),
                    array(
                        "title" => "OT Sheet",
                        "url" => "month_lock/ot_days",
                        "icon" => "fa fa-clock-o",
                        "status" => false
                    ),
                    array(
                        "title" => "Late Sheet",
                        "url" => "month_lock/late_days",
                        "icon" => "fa fa-clock-o",
                        "status" => false
                    ),
                    array(
                        "title" => "Late Break Sheet",
                        "url" => "month_lock/late_break_days",
                        "icon" => "fa fa-clock-o",
                        "status" => false
                    ),
                    array(
                        "title" => "Early Out Sheet",
                        "url" => "month_lock/early_out_days",
                        "icon" => "fa fa-clock-o",
                        "status" => false
                    )
                )
            ),
            array(
                "title" => "Latest Activity",
                "url" => null,
                "icon" => "fa fa-clock-o",
                "status" => false,
                "sub_menus" => array(
                    array(
                        "title" => "Absents",
                        "url" => "month_lock/absents",
                        "icon" => "fa fa-user-times",
                        "status" => true
                    ),
                    array(
                        "title" => "Lates",
                        "url" => "month_lock/lates",
                        "icon" => "fa fa-clock-o",
                        "status" => true
                    )
                )
            ),
            array(
                "title" => "All Reports",
                "url" => "month_lock/reports",
                "icon" => "fa fa-bar-chart",
                "status" => true,
                "sub_menus" => null
            )
        );
    }

    private function _render_page($title, $active_menu, $view_name)
    {
        $current_user = get_user();
        $data = array();
        $data['pageTitle'] = $title;
        $data['active_menu'] = $active_menu;
         $data['menusTop'] = $this->_get_month_lock_menus();
        $data["menus"] = get_menus();
        $data['company_id'] = (int) $current_user['company_id'];
        // $data['is_month_lock'] = true;

        $this->load->view('month_lock/header', $data);
        $this->load->view('sidebar', $data);
        $this->load->view('month_lock/topbar', $data);
        $this->load->view('month_lock/' . $view_name, $data);
        $this->load->view('footer', $data);
    }

    public function locked_data()
    {
        $this->_render_page('Locked Data', 'month_lock/locked_data', 'locked_data');
    }

    public function absents()
    {
        $this->_render_page('Latest Absents', 'month_lock/absents', 'absents');
    }

    public function lates()
    {
        $this->_render_page('Lates', 'month_lock/lates', 'lates');
    }

    public function attendance_sheet()
    {
        $this->_render_page('Attendance', 'month_lock/attendance_sheet', 'attendance_sheet');
    }

    public function ot_days()
    {
        $this->_render_page('OT Sheet', 'month_lock/ot_days', 'ot_days');
    }

    public function late_days()
    {
        $this->_render_page('Late Sheet', 'month_lock/late_days', 'late_days');
    }

    public function late_break_days()
    {
        $this->_render_page('Late Break Sheet', 'month_lock/late_break_days', 'late_break_days');
    }

    public function early_out_days()
    {
        $this->_render_page('Early Out Sheet', 'month_lock/early_out_days', 'early_out_days');
    }

    public function reports()
    {
        $current_user = get_user();
        $data = array();
        $data['pageTitle'] = 'All Reports';
        $data['active_menu'] = 'month_lock/reports';
        $data['menusTop'] = $this->_get_month_lock_menus();
        $data["menus"] = get_menus();
        $data['company_id'] = (int) $current_user['company_id'];
        // $data['is_month_lock'] = true;

        $dates = getStartEndDatesWithOneMonthGap($current_user['start_day']);
        $data['from_f'] = $dates[0]->format('d/m/Y');
        $data['to_f'] = $dates[1]->format('d/m/Y');

        $data['ot_from_f'] = date('21/m/Y', strtotime($dates[0]->format('Y-m-d') . ' -1 month'));
        $data['ot_to_f'] = date('20/m/Y', strtotime($dates[0]->format('Y-m-d')));

        $this->load->view('month_lock/header', $data);
        $this->load->view('sidebar', $data);
        $this->load->view('month_lock/topbar', $data);
        $this->load->view('export_summary_async_vue', $data);
        $this->load->view('footer', $data);
    }
}
