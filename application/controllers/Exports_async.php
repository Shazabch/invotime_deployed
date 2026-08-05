<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Exports_async extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (is_null(get_user())) {
            redirect('welcome');
        }
    }

    /**
     * Vue-based async export page (CI view).
     */
    public function index()
    {
        if (!is_page_permitted('exports')) {
            redirect_if_not_permitted();
        }

        $data['pageTitle'] = 'Export Summary (Async)';
        $data['active_menu'] = 'exports_async';
        $data['menus'] = get_menus();

        $current_user = get_user();
        $data['company_id'] = $current_user['company_id'];

        $dates = getStartEndDatesWithOneMonthGap($current_user['start_day']);
        $data['from_f'] = $dates[0]->format('d/m/Y');
        $data['to_f'] = $dates[1]->format('d/m/Y');

        $data['ot_from_f'] = date('21/m/Y', strtotime($dates[0]->format('Y-m-d') . ' -1 month'));
        $data['ot_to_f'] = date('20/m/Y', strtotime($dates[0]->format('Y-m-d')));

        $this->load->view('header', $data);
        $this->load->view('sidebar', $data);
        $this->load->view('export_summary_async_vue', $data);
        $this->load->view('footer', $data);
    }
}
