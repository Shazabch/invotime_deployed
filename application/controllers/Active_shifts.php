<?php
class Active_shifts extends CI_Controller
{


    function __construct()
    {
        parent::__construct();

        if (is_null(get_user())) {
            redirect("welcome");
        }
    }
    public function index()
    {
        if (!is_page_permitted('active_shifts')) {
            redirect_if_not_permitted();
        }

        $table_name = "active_shifts";
        $active_menu = $table_name;
        $page = $table_name;
        $current_user = get_user();
        $cid = $current_user["company_id"];
        $data['permissions_level'] = $current_user["permissions_level"];
        $data['company_id'] = $current_user["company_id"];
        $data['branches'] = $this->db->select('id,name')->from('branches')->where("company_id = $cid")->order_by("name", "asc")->get()->result();
        $data['pageTitle'] = ucwords(str_replace("_", " ", $table_name));

        $this->load->helper('xcrud');
        $xcrud = xcrud_get_instance($table_name . "_" . time());
        $xcrud->unset_title();

        $xcrud  = $this->active_shifts($xcrud);

        $data['table_content'] = $xcrud;

        $data['active_menu'] = "active_shifts_table" . $active_menu;
        $this->load->view('header', $data);



        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);

        if (is_page_permitted($page)) {
            $this->load->view('active_shifts_table', $data);
        } else {
            $this->load->view('not_permitted');
        }

        $this->load->view('footer', $data);
    }

    public function active_shifts($xcrud, $branchId = '', $shiftCode = '')
    {
        $xcrud->table('shifts');
        $current_user = get_user();
        $cid = $current_user["company_id"];
        $bid = '';
        $permissions_level = $current_user["permissions_level"];
        if ($branchId && $permissions_level != "Outlet") {
            $bid = $branchId;
        } else if ($permissions_level == "Outlet") {
            $bid = $current_user["branch_id"];
        }
        $xcrud->pass_var('company_id', $cid);

        $hidden_fields = ['acting_code','shift_code', 'half_day', 'updated_at', 'deleted_at', 'created_at', 'is_paid', 'is_leave', 'void_late_in', 'void_early_out', 'excursion_period', 'extra_break', 'extra_break_worked_hours_more_than', 'extra_break_1', 'extra_break_2', 'extra_break_3', 'extra_break_4', 'extra_break_5', 'extra_break_6', 'weekday_deduction', 'weekend_deduction', 'public_holiday_deduction'];
        $hidden_columns = ['acting_code','shift_code', 'half_day', 'updated_at', 'deleted_at', 'created_at', 'is_paid', 'is_leave', 'void_late_in', 'void_early_out', 'auto_clockout_time', 'excursion_period', 'fixed_overtime', 'auto_approve_ot', 'extra_break', 'extra_break_worked_hours_more_than', 'extra_break_1', 'extra_break_2', 'extra_break_3', 'extra_break_4', 'extra_break_5', 'extra_break_6', 'weekday_deduction', 'weekend_deduction', 'public_holiday_deduction', 'active'];

        if ($cid == 66) {
            // show acting code for BMI
            $hidden_fields = array_diff($hidden_fields, ['acting_code']);
            $hidden_columns = array_diff($hidden_columns, ['acting_code']);
            $xcrud->change_type('acting_code', 'multiselect', '', 'CA,SPA,ACA,FL Inc,C/wash,M/ope,Shift1,Shift2,Shift3');
        } else if ($cid == 196) {
            // show weekday_deduction, weekend_deduction, public_holiday_deduction for JL01
            $hidden_fields = array_diff($hidden_fields, ['weekday_deduction', 'weekend_deduction', 'public_holiday_deduction']);
            $hidden_columns = array_diff($hidden_columns, ['weekday_deduction', 'weekend_deduction', 'public_holiday_deduction']);
        } else if ($cid == 97) {
            // show created_at
            $hidden_columns = array_diff($hidden_columns, ['created_at']);
        }
        // === START NEW LOGIC FOR COMPANY 286 ===
        else if (in_array($cid, companies_allowed_for_shift_allowance())) {
            // 1. Ensure the field/column is visible
            $hidden_fields = array_diff($hidden_fields, ['shift_code']);
            $hidden_columns = array_diff($hidden_columns, ['shift_code']);
            // 2. Configure the field as a Select/Dropdown
            $options = array(
                '' => 'None',
                'DSA'  => 'DSA (Day Shift Allowance)',
                'NSA'  => 'NSA (Night Shift Allowance)'
            );
            $xcrud->change_type('shift_code', 'select', '', $options);
            $xcrud->label('shift_code', 'Shift Code');
        }
        // === END NEW LOGIC FOR COMPANY 286 ===

        $hidden_fields = implode(',', $hidden_fields);
        $hidden_columns = implode(',', $hidden_columns);

        $xcrud->fields($hidden_fields, true);
        $xcrud->columns($hidden_columns, true);

        $xcrud->change_type('round_off_ot', 'select', '1', array('1' => "Yes", "0" => "No"));
        $xcrud->where('active', 1);
        $xcrud->change_type('active', 'select', '1', array('1' => "Yes", "0" => "No"));

        $xcrud->relation('company_id', 'companies', 'id', 'name', array('id' => $cid));
        $xcrud->label('company_id', 'Company');

        $xcrud->relation('branch_id', 'branches', 'id', 'name', array('company_id' => $cid));
        $xcrud->label('branch_id', 'Branch');
        $xcrud->label('extra_ot_worked_hours_more_than', 'If Worked Hours More Than');

        $xcrud->label('consider_break_1', 'Consider Break Hours');
        $xcrud->label('consider_break_2', 'Consider Break Hours');
        $xcrud->label('consider_break_3', 'Consider Break Hours');
        $xcrud->label('consider_break_4', 'Consider Break Hours');
        $xcrud->label('consider_break_5', 'Consider Break Hours');
        $xcrud->label('consider_break_6', 'Consider Break Hours');

        $xcrud->label('early_ot_start', 'Early Overtime Start');
        $xcrud->label('early_ot_end', 'Early Overtime End');
        $xcrud->label('fixed_ot', 'Fixed Overtime');
        $xcrud->label('extra_ot', 'Extra Overtime');
        $xcrud->label('extra_ot_hours', 'Extra Overtime Hours');
        $xcrud->label('auto_approve_ot', 'Auto Approve Overtime');
        $xcrud->label('round_off_ot', 'Round Off Overtime');
        $xcrud->label('same_day_overnight', '- Same/Next Day');
        $xcrud->label('is_preshift', 'Is Pre-shift');
        $xcrud->label('pre_shift_buffer', 'Pre-shift Buffer (mins)');


        $xcrud->unset_print();
        $xcrud->unset_csv();

        $xcrud->after_insert('after_shift_insertion');
        $xcrud->before_update('before_shift_updation');
        $xcrud->before_remove('before_shift_deletion');

        if ($bid) {
            if ($permissions_level == "Outlet") {
                $xcrud->where('(branch_id = ' . $bid . ' or branch_id is null)');
            } else {
                $xcrud->where('branch_id = ' . $bid);
            }
        }
        if (!empty($shiftCode)) {
            // Apply the filter only if $shiftCode is provided
            $xcrud->where('shift_code', $shiftCode);
        }
        $xcrud->where('company_id = ', $cid);
        $xcrud->where('is_leave = ', 'no');


        return $xcrud->render();
    }

    public function filter_xcrud()
    {
        $branchId = $this->input->post('branch_id');
        $shiftCode = $this->input->post('shiftCode');

        $table_name = "active_shifts";

        $this->load->helper('xcrud');
        // Ensure the instance name is unique, using $table_name
        $xcrud = xcrud_get_instance($table_name . "_" . time());
        $xcrud->unset_title();

        // Pass the shiftCode as the third argument
        $data['shifts_xcrud'] = $this->active_shifts($xcrud, $branchId, $shiftCode);

        $shifts_xcrud = $this->load->view('shifts_xcrud', $data, TRUE);
        echo $shifts_xcrud;
    }
}
