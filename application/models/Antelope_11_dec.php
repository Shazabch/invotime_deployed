<?php
class Antelope extends CI_Model
{
  public function __construct()
  {
    parent::__construct();
  }
  //Antelope functions start ----------
  public function admin_accounts($xcrud)
  {
    $xcrud->table('employees');
    $xcrud->unset_remove(true, 'role_id', '=', 1);
    $xcrud->where('role_id =', 1);
    $xcrud->where('company_id =', get_user()["company_id"]);


    $xcrud->change_type('password', 'password', 'md5', 32);
    $xcrud->change_type('avatar', 'image', '', array('width' => 200, 'height' => 200, 'ratio' => 1.0, 'manual_crop' => true)); // auto-crop
    //$xcrud->set_attr('permissions',array('class'=>'permissions_list'));
    //$xcrud->change_type('permissions','multiselect','',get_menus_for_user_management());
    $xcrud->fields('role_id,company_id,email_verified,api_token,updated_at,deleted_at,permissions,created_at', true);




    return '<h3>Page under construction</h3>';

    //return $xcrud->render();
  }

  public function my_profile($xcrud)
  {
    $xcrud->table('employees');
    $xcrud->where('id =', get_user()["id"]);
    $xcrud->unset_remove();
    $xcrud->unset_add();
    $xcrud->unset_print();
    $xcrud->unset_csv();
    $xcrud->unset_search();
    $xcrud->unset_pagination();
    $xcrud->unset_limitlist();
    $xcrud->unset_sortable();
    $xcrud->unset_list();
    $xcrud->columns('role_id,permissions', true);
    $xcrud->fields('role_id,permissions', true);
    $xcrud->change_type('password', 'password', 'md5', 16);
    $xcrud->change_type('avatar', 'image', '', array('width' => 200, 'height' => 200, 'ratio' => 1.0, 'manual_crop' => true)); // auto-crop
    return '<h3>Page under construction</h3>';

    return $xcrud->render('edit', get_user()["id"]);
  }
  //Antelope functions end ------------


  //****************************************************************************************************************


  //Your functions start here
  // public function employees($xcrud){
  //   $xcrud->table('employees');
  //   $xcrud->where('role_id <>', 1);
  //   $xcrud->where('company_id =', get_user()["company_id"]);
  //   $xcrud->pass_var('company_id', get_user()["company_id"]);
  //   //$xcrud->join('department_id','departments','id','departments',true);
  //   $xcrud->relation('department_id','departments','id','name',array('company_id' => get_user()["company_id"]))->label('department_id','Department');
  //   $xcrud->relation('position_id','positions','id','title',array('company_id' => get_user()["company_id"]))->label('position_id','Position');

  //   $xcrud->fields('role_id,company_id,email_verified,api_token,updated_at,deleted_at,permissions,created_at', true);
  //   $xcrud->columns('photo,first_name,last_name,email,department_id,position_id');
  //   $xcrud->change_type('photo','image','',array('width'=>200, 'height'=>200, 'crop'=>true));
  //   $xcrud->change_type('password', 'password', 'md5', 32);


  //   return $xcrud->render();
  // }

  public function leaves($xcrud)
  {
    $xcrud->table('shifts');
    $cid = get_user()["company_id"];
    //$xcrud->pass_var('company_id', get_user()["company_id"]);
    if ($cid == 196) {
      $xcrud->fields('company_id,name,color,code,is_paid,half_day,void_late_in,void_early_out, is_approved, weekday_deduction, weekend_deduction, public_holiday_deduction');
      $xcrud->columns('company_id,name,color,code,is_paid, half_day,void_late_in,void_early_out, is_approved, weekday_deduction, weekend_deduction, public_holiday_deduction');
    } else {
      $xcrud->fields('company_id,name,color,code,is_paid,half_day,void_late_in,void_early_out, is_approved');
      $xcrud->columns('company_id,name,color,code,is_paid, half_day,void_late_in,void_early_out, is_approved');
    }

    $xcrud->relation('company_id', 'companies', 'id', 'name', array('id' => $cid));
    $xcrud->label('company_id', 'Company');
    $xcrud->unset_print();
    $xcrud->unset_csv();

    $today = date('Y-m-d');

    // echo "SELECT a.date, GROUP_CONCAT(b.first_name ORDER BY b.id) emp FROM shift_days a INNER JOIN employees b ON FIND_IN_SET(b.id, a.employees) WHERE a.date = '$today' AND shift_id={id} LIMIT 1";
    // die();

    // $xcrud->subselect('Employees Today',"SELECT GROUP_CONCAT(b.first_name, '(' ,b.special_id, ')'  ORDER BY b.first_name SEPARATOR ', ') emp FROM shift_days a INNER JOIN employees b ON FIND_IN_SET(b.id, a.employees) WHERE a.date = '$today' AND shift_id={id} LIMIT 1");

    $cid = get_user()["company_id"];

    //if($cid != 1){

    $xcrud->where('company_id = ', $cid);

    //}

    $xcrud->where('is_leave = ', 'yes');

    $xcrud->pass_var('is_leave', 'yes');


    // $shift_days = $xcrud->nested_table('shift_days','id','shift_days','shift_id');
    // $shift_days->columns('date,employees,created_at');
    // $shift_days->fields('date,employees');
    // $shift_days->no_editor('employees');
    // $shift_days->duplicate_button();
    // //$shift_days->before_insert('check_shift_overlap');

    // //var_dump(get_company_employees());
    // //die();
    // $shift_days->change_type('employees','multiselect','',get_company_employees());
    // $shift_days->order_by('date','DESC');

    //naveed

    $xcrud->after_insert('after_leave_insertion');
    $xcrud->after_update('after_leave_updation');
    $xcrud->before_remove('before_leave_deletion');

    return $xcrud->render();
  }

  public function active_shifts($xcrud)
  {
    $xcrud->table('shifts');
    $current_user = get_user();
    $cid = $current_user["company_id"];
    $bid = $current_user["branch_id"];
    $xcrud->pass_var('company_id', $cid);

    // show acting code only for BMI
    if ($cid == 196) {
      $xcrud->fields('acting_code,half_day,updated_at,deleted_at,created_at,is_paid,is_leave,void_late_in,void_early_out, excursion_period, extra_break, extra_break_worked_hours_more_than, extra_break_1, extra_break_2, extra_break_3, extra_break_4, extra_break_5, extra_break_6', true);
      $xcrud->columns('acting_code,half_day,updated_at,deleted_at,created_at,is_paid,is_leave,void_late_in,void_early_out,auto_clockout_time,excursion_period,fixed_overtime,auto_approve_ot, extra_break, extra_break_worked_hours_more_than, extra_break_1, extra_break_2, extra_break_3, extra_break_4, extra_break_5, extra_break_6, active', true);
    } elseif ($cid == 66) {
      $xcrud->fields('half_day,updated_at,deleted_at,created_at,is_paid,is_leave,void_late_in,void_early_out, excursion_period, extra_break, extra_break_worked_hours_more_than, extra_break_1, extra_break_2, extra_break_3, extra_break_4, extra_break_5, extra_break_6, weekday_deduction, weekend_deduction, public_holiday_deduction', true);
      $xcrud->columns('half_day,updated_at,deleted_at,created_at,is_paid,is_leave,void_late_in,void_early_out,auto_clockout_time,excursion_period,fixed_overtime,auto_approve_ot, extra_break, extra_break_worked_hours_more_than, extra_break_1, extra_break_2, extra_break_3, extra_break_4, extra_break_5, extra_break_6, active, weekday_deduction, weekend_deduction, public_holiday_deduction', true);
      $xcrud->change_type('acting_code', 'multiselect', '', 'CA,SPA,ACA,FL Inc,C/wash,M/ope,Shift1,Shift2,Shift3');
    } elseif ($cid == 97) {
      $xcrud->fields('acting_code,half_day,updated_at,deleted_at,created_at,is_paid,is_leave,void_late_in,void_early_out, excursion_period, extra_break, extra_break_worked_hours_more_than, extra_break_1, extra_break_2, extra_break_3, extra_break_4, extra_break_5, extra_break_6, weekday_deduction, weekend_deduction, public_holiday_deduction', true);
      $xcrud->columns('acting_code,half_day,updated_at,deleted_at,is_paid,is_leave,void_late_in,void_early_out,auto_clockout_time,excursion_period,fixed_overtime,auto_approve_ot, extra_break, extra_break_worked_hours_more_than, extra_break_1, extra_break_2, extra_break_3, extra_break_4, extra_break_5, extra_break_6, active, weekday_deduction, weekend_deduction, public_holiday_deduction', true);
    } else {
      $xcrud->fields('acting_code,half_day,updated_at,deleted_at,created_at,is_paid,is_leave,void_late_in,void_early_out, excursion_period, extra_break, extra_break_worked_hours_more_than, extra_break_1, extra_break_2, extra_break_3, extra_break_4, extra_break_5, extra_break_6, weekday_deduction, weekend_deduction, public_holiday_deduction', true);
      $xcrud->columns('acting_code,half_day,updated_at,deleted_at,created_at,is_paid,is_leave,void_late_in,void_early_out,auto_clockout_time,excursion_period,fixed_overtime,auto_approve_ot, extra_break, extra_break_worked_hours_more_than, extra_break_1, extra_break_2, extra_break_3, extra_break_4, extra_break_5, extra_break_6, active, weekday_deduction, weekend_deduction, public_holiday_deduction', true);
    }


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
    $xcrud->label('is_rest_day', 'Consider as Rest Day');


    $today = date('Y-m-d');
    $xcrud->unset_print();
    $xcrud->unset_csv();

    $xcrud->after_insert('after_shift_insertion');
    $xcrud->before_update('before_shift_updation');
    $xcrud->before_remove('before_shift_deletion');

    $permissions_level = $current_user["permissions_level"];

    if ($permissions_level == "Outlet") {
      $xcrud->where('(branch_id = ' . $bid . ' or branch_id is null)');
    }

    // echo "SELECT a.date, GROUP_CONCAT(b.first_name ORDER BY b.id) emp FROM shift_days a INNER JOIN employees b ON FIND_IN_SET(b.id, a.employees) WHERE a.date = '$today' AND shift_id={id} LIMIT 1";
    // die();

    // $xcrud->subselect('Employees Today',"SELECT GROUP_CONCAT(b.first_name, '(' ,b.special_id, ')'  ORDER BY b.first_name SEPARATOR ', ') emp FROM shift_days a INNER JOIN employees b ON FIND_IN_SET(b.id, a.employees) WHERE a.date = '$today' AND shift_id={id} LIMIT 1");

    //if($cid != 1){

    $xcrud->where('company_id = ', $cid);

    //}

    $xcrud->where('is_leave = ', 'no');

    // $shift_days = $xcrud->nested_table('shift_days','id','shift_days','shift_id');
    // $shift_days->columns('date,employees,created_at');
    // $shift_days->fields('date,employees');
    // $shift_days->no_editor('employees');
    // $shift_days->duplicate_button();
    // //$shift_days->before_insert('check_shift_overlap');

    // //var_dump(get_company_employees());
    // //die();
    // $shift_days->change_type('employees','multiselect','',get_company_employees());
    // $shift_days->order_by('date','DESC');

    //naveed


    return $xcrud->render();
  }

  public function inactive_shifts($xcrud)
  {
    $xcrud->table('shifts');
    $current_user = get_user();
    $cid = $current_user["company_id"];
    $bid = $current_user["branch_id"];
    $xcrud->pass_var('company_id', $cid);

    // show acting code only for BMI
    if ($cid == 66) {
      $xcrud->fields('half_day,updated_at,deleted_at,created_at,is_paid,is_leave,void_late_in,void_early_out, excursion_period, extra_break, extra_break_worked_hours_more_than, extra_break_1, extra_break_2, extra_break_3, extra_break_4, extra_break_5, extra_break_6', true);
      $xcrud->columns('half_day,updated_at,deleted_at,created_at,is_paid,is_leave,void_late_in,void_early_out,auto_clockout_time,excursion_period,fixed_overtime,auto_approve_ot, extra_break, extra_break_worked_hours_more_than, extra_break_1, extra_break_2, extra_break_3, extra_break_4, extra_break_5, extra_break_6, active', true);
      $xcrud->change_type('acting_code', 'multiselect', '', 'CA,SPA,ACA,FL Inc,C/wash,M/ope,Shift1,Shift2,Shift3');
    } else {
      $xcrud->fields('acting_code,half_day,updated_at,deleted_at,created_at,is_paid,is_leave,void_late_in,void_early_out, excursion_period, extra_break, extra_break_worked_hours_more_than, extra_break_1, extra_break_2, extra_break_3, extra_break_4, extra_break_5, extra_break_6', true);
      $xcrud->columns('acting_code,half_day,updated_at,deleted_at,created_at,is_paid,is_leave,void_late_in,void_early_out,auto_clockout_time,excursion_period,fixed_overtime,auto_approve_ot, extra_break, extra_break_worked_hours_more_than, extra_break_1, extra_break_2, extra_break_3, extra_break_4, extra_break_5, extra_break_6, active', true);
    }


    $xcrud->change_type('round_off_ot', 'select', '1', array('1' => "Yes", "0" => "No"));
    $xcrud->where('active', 0);
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

    $today = date('Y-m-d');
    $xcrud->unset_print();
    $xcrud->unset_csv();

    $xcrud->after_insert('after_shift_insertion');
    $xcrud->before_update('before_shift_updation');
    $xcrud->before_remove('before_shift_deletion');

    $permissions_level = $current_user["permissions_level"];

    if ($permissions_level == "Outlet") {
      $xcrud->where('(branch_id = ' . $bid . ' or branch_id is null)');
    }

    $xcrud->where('company_id = ', $cid);

    $xcrud->where('is_leave = ', 'no');

    $xcrud->unset_add();

    return $xcrud->render();
  }

  public function termination_reasons($xcrud)
  {
    $xcrud->table('termination_reasons');
    $cid = get_user()["company_id"];
    $xcrud->where('company_id', $cid);
    $xcrud->pass_var('company_id', $cid);
    $xcrud->fields('reason');
    $xcrud->columns('reason');


    $xcrud->unset_print();
    $xcrud->unset_csv();


    $xcrud->after_insert('after_termination_reason_insertion');
    $xcrud->after_update('after_termination_reason_updation');
    $xcrud->before_remove('before_termination_reason_deletion');


    return $xcrud->render();
  }

  public function employee_groups($xcrud)
  {
    $cid = get_user()["company_id"];
    $this->db->select('id');
    $this->db->where('job_name', 'Employee');
    $this->db->where('company_id', $cid);
    $employee_role_id = $this->db->get('roles')->row();
    // print_r($role_ids);die;
    $xcrud->table('employee_groups');
    $xcrud->where('company_id', $cid);
    $xcrud->fk_relation('Employees', 'id', 'employee_groups_relation', 'group_id', 'employee_id', 'employees', 'id', array('special_id', 'first_name'), array('company_id' => $cid, 'role_id' => $employee_role_id->id), '', ' - ');
    $xcrud->fields('updated_at,created_at', true);
    $xcrud->columns('updated_at', true);
    $xcrud->label('company_id', 'Company');
    $xcrud->label('branch_id', 'Branch');

    $xcrud->validation_required('name,company_id,branch_id');

    $xcrud->relation('company_id', 'companies', 'id', 'name', array('id' => $cid));
    $xcrud->relation('branch_id', 'branches', 'id', 'name', array('company_id' => $cid));

    return $xcrud->render();
  }

  public function holiday_rates($xcrud)
  {
    $xcrud->table('companies');
    $cid = get_user()["company_id"];
    $xcrud->where('id', $cid);
    $xcrud->fields('normal_weekend,public_holiday_normal,public_holiday_weekend', false, 'Standard Hours');
    $xcrud->fields('normal_weekend_overtime,public_holiday_normal_overtime,public_holiday_weekend_overtime', false, 'Overtime Hours');
    // $xcrud->columns('normal_weekend,public_holiday_normal,public_holiday_weekend');
    $xcrud->label('public_holiday_normal', 'Public Holiday Weekday');
    $xcrud->label('normal_weekend_overtime', 'Normal Weekend');
    $xcrud->label('public_holiday_normal_overtime', 'Public Holiday Weekday');
    $xcrud->label('public_holiday_weekend_overtime', 'Public Holiday Weekend');
    $xcrud->unset_add();
    $xcrud->unset_remove();
    $xcrud->unset_print();
    // $xcrud->unset_edit();
    $xcrud->unset_csv();
    $xcrud->unset_view();
    $xcrud->unset_list();
    $xcrud->unset_pagination();
    $xcrud->unset_search();
    return $xcrud->render('edit', $cid);
  }

  public function departments($xcrud)
  {
    $xcrud->table('departments');
    $cid = get_user()["company_id"];
    //$xcrud->where('company_id =', get_user()["company_id"]);
    //$xcrud->pass_var('company_id', get_user()["company_id"]);
    $xcrud->fields('updated_at,deleted_at,created_at', true);
    $xcrud->columns('company_id,updated_at,deleted_at,created_at', true);

    $xcrud->relation('company_id', 'companies', 'id', 'name', array('id' => $cid));
    $xcrud->label('company_id', 'Company');



    //if($cid != 1){

    $xcrud->where('company_id = ', $cid);

    //}

    $permissions_level = get_user()["permissions_level"];

    // if($permissions_level != "Company"){
    // $xcrud->unset_remove();
    // $xcrud->unset_add();
    // $xcrud->unset_edit();

    // }

    $xcrud->after_insert('after_department_insertion');
    $xcrud->after_update('after_department_updation');
    $xcrud->before_remove('before_department_removal');

    return $xcrud->render();
  }

  public function add_clocking($xcrud)
  {
    $xcrud->table('clockings_news');

    $xcrud->join('employee_id', 'employees', 'id', 'employees', true);

    $xcrud->join('employees.branch_id', 'branches', 'id', 'branches', true);

    $cid = get_user()["company_id"];

    $xcrud->where('employees.company_id = ', $cid);

    $permissions_level = get_user()["permissions_level"];
    $limit_access_to_department = get_user()["limit_access_to_department"];
    $department_id = get_user()["department_id"];

    $device_where = array('company_id' => $cid);
    $employees_where = array('company_id' => $cid);

    if ($permissions_level == "Company") {
      // $xcrud->unset_remove();
      // $xcrud->unset_add();
      // $xcrud->unset_edit();

      //$xcrud->where('company_id =', get_user()["company_id"]);
    }

    if ($permissions_level == "Outlet") {
      // $xcrud->unset_remove();
      // $xcrud->unset_add();
      // $xcrud->unset_edit();

      //$xcrud->where('branch_id =', get_user()["branch_id"]);

      $device_where["branch_id"] = get_user()["branch_id"];
      $employees_where["branch_id"] = get_user()["branch_id"];
      $xcrud->where('employees.branch_id = ', get_user()["branch_id"]);
    }

    if ($limit_access_to_department == "yes") {
      $employees_where["department_id"] = $department_id;
      $xcrud->where('employees.department_id = ', $department_id);
    }


    $xcrud->label("device_id", "Device");
    $xcrud->label("shift_id", "Shifts");
    $xcrud->label("branches.name", "Outlet");
    $xcrud->label("employee_id", "Employee");

    $xcrud->unset_remove();
    $xcrud->unset_print();
    $xcrud->unset_edit();
    $xcrud->unset_csv();
    $xcrud->unset_view();


    //add conditions for company and outlet where clause to the below line
    $permissions_level = get_user()["permissions_level"];

    if ($permissions_level == "Outlet") {
      $xcrud->relation(
        'shift_id',
        'shifts',
        'id',
        array('name', 'code'),
        array('company_id' => $cid, 'branch_id' => get_user()["branch_id"]),
        'name asc',
        false,
        ' - '
      );
    } else {
      $xcrud->relation(
        'shift_id',
        'shifts',
        'id',
        array('name', 'code'),
        array('company_id' => $cid),
        'name asc',
        false,
        ' - '
      );
    }


    $xcrud->relation('device_id', 'devices', 'device_id', array('mac_address', 'location'), $device_where);

    $xcrud->relation(
      'employee_id',
      'employees',
      'id',
      'first_name',
      $employees_where,
      'first_name asc'

    );

    $xcrud->columns('employee_id,branches.name,shift_id,device_id,datetime');
    $xcrud->fields('employee_id,shift_id,device_id,datetime,mode,type');
    //$xcrud->order_by('datetime','desc');
    // $shift_days->fields('date,employees');


    //$xcrud->where('company_id = ', $cid);


    $xcrud->order_by('datetime', 'desc');

    $xcrud->before_insert('before_clocking_insertion');
    $xcrud->before_update('before_clocking_update');

    return $xcrud->render();
  }

  public function hod_department_access($xcrud)
  {
    $xcrud->table('employees');
    $xcrud->join('role_id', 'roles', 'id', 'roles', true);
    $xcrud->join('department_id', 'departments', 'id', 'departments', true);
    $xcrud->join('branch_id', 'branches', 'id', 'branches', true);
    $cid = get_user()["company_id"];
    $bid = get_user()["branch_id"];

    $permissions_level = get_user()["permissions_level"];

    //echo  $permissions_level; die();
    if ($permissions_level == "Outlet") {
      //$xcrud->unset_remove(true,'branch_id','!=',$bid);
      //$xcrud->unset_edit(true,'branch_id','!=',$bid);
      $xcrud->where('branch_id = ', $bid);
    }

    $xcrud->label('branches.name', 'Branch');
    $xcrud->label('departments.name', 'Department');
    $xcrud->unset_remove();
    $xcrud->unset_add();
    $xcrud->readonly('special_id,first_name,department_id,branches.name,departments.name');
    //$xcrud->pass_var('company_id', get_user()["company_id"]);
    $xcrud->fields('special_id,first_name,branches.name,departments.name,departments_access');
    $xcrud->columns('special_id,first_name,branches.name,departments.name,departments_access');

    $xcrud->relation('departments_access', 'departments', 'id', 'name', array('company_id' => $cid), 'name asc', true);


    //$xcrud->relation('company_id','companies','id','name',array('id' => $cid));

    // if($permissions_level == "Company"){
    //   $xcrud->relation('branch_id','branches','id','name','','','','','','company_id','company_id');

    // }else{
    //   $xcrud->relation('branch_id','branches','id','name',array('id' => $bid),'','','','','company_id','company_id');
    // }


    // $xcrud->label('company_id','Company');
    // $xcrud->label('branch_id','Outlet');


    //if($cid != 1){

    $xcrud->where('company_id = ', $cid);
    $xcrud->where("roles.limit_access_to_department = 'yes'");

    //}


    return $xcrud->render();
  }


  public function devices($xcrud)
  {
    $xcrud->table('devices');

    $cid = get_user()["company_id"];
    $bid = get_user()["branch_id"];

    $permissions_level = get_user()["permissions_level"];

    if ($permissions_level == "Outlet") {
      $xcrud->unset_remove(true, 'branch_id', '!=', $bid);
      $xcrud->unset_edit(true, 'branch_id', '!=', $bid);
      $xcrud->where('branch_id = ', $bid);
    }


    //$xcrud->pass_var('company_id', get_user()["company_id"]);
    $xcrud->fields('updated_at,deleted_at,created_at', true);
    $xcrud->columns('updated_at,deleted_at,created_at', true);

    $xcrud->change_type('type', 'select', '1', array('Attendance Device' => "Attendance Device", 'QR Code' => "QR Code", "BLE" => "BLE"));
    $xcrud->relation('company_id', 'companies', 'id', 'name', array('id' => $cid));

    if ($permissions_level == "Company") {
      $xcrud->relation('branch_id', 'branches', 'id', 'name', '', '', '', '', '', 'company_id', 'company_id');
    } else {
      $xcrud->relation('branch_id', 'branches', 'id', 'name', array('id' => $bid), '', '', '', '', 'company_id', 'company_id');
    }

    $xcrud->button(base_url() . "get_qr/device/{device_id}", "QR Code", "fa fa-qrcode", "btn btn-success", array('target' => '_blank'));
    $xcrud->label('company_id', 'Company');
    $xcrud->label('branch_id', 'Outlet');
    $xcrud->label('uuid', 'UUID');

    $cid = get_user()["company_id"];

    //if($cid != 1){

    $xcrud->where('company_id = ', $cid);

    //}

    $xcrud->after_insert('after_device_insertion');
    $xcrud->after_update('after_device_updation');
    $xcrud->before_remove('before_device_deletion');

    return $xcrud->render();
  }

  public function positions($xcrud)
  {
    $xcrud->table('positions');
    //$xcrud->where('company_id =', get_user()["company_id"]);
    //$xcrud->pass_var('company_id', get_user()["company_id"]);
    $xcrud->fields('updated_at,deleted_at,created_at,department_id', true);
    $xcrud->columns('company_id,updated_at,deleted_at,created_at,department_id', true);



    $xcrud->label('company_id', 'Company');
    //$xcrud->label('department_id','Department');

    $cid = get_user()["company_id"];

    $xcrud->relation('company_id', 'companies', 'id', 'name', array('id' => $cid));
    //$xcrud->relation('department_id','departments','id','name',array('id' => $cid));
    //$xcrud->relation('department_id','departments','id','name','','','','','','company_id','company_id');
    //if($cid != 1){

    $xcrud->where('company_id = ', $cid);

    //}



    // $permissions_level = get_user()["permissions_level"];

    // if($permissions_level != "Company"){
    // $xcrud->unset_remove();
    // $xcrud->unset_add();
    // $xcrud->unset_edit();

    // }

    $xcrud->after_insert('after_position_insertion');
    $xcrud->after_update('after_position_updation');
    $xcrud->before_remove('before_position_removal');

    return $xcrud->render();
  }



  public function bakar($xcrud)
  {

    return $xcrud->render();
  }


  public function students($xcrud)
  {

    $xcrud->table('students');
    $xcrud->columns('name,father_name,photo');
    $xcrud->fields('name,father_name,photo');


    //$xcrud->change_type('photo','image','',array('width'=>300, 'height'=>300,'ratio'=>1.0, 'manual_crop'=>true)); // 
    //$xcrud->where('name = ', 'naveed');
    //$xcrud->where('id = ', 2);



    return $xcrud->render();
  }


  public function manage_subjects($xcrud)
  {
    $xcrud->table('subjects');
    $xcrud->columns('subject_name,subject_teacher,student_id');
    $xcrud->fields('subject_name,subject_teacher');
    $xcrud->where('id = ', 2);



    return $xcrud->render();
  }



  //uvtivket code starts from here
  public function outlets($xcrud)
  {
    $xcrud->table('branches');
    $xcrud->buttons_position('left');

    $cid = get_user()["company_id"];
    $bid = get_user()["branch_id"];

    $permissions_level = get_user()["permissions_level"];

    if ($permissions_level == "Outlet") {

      $xcrud->unset_edit(true, 'id', '!=', $bid);
      $xcrud->where('id = ', $bid);
    }

    $xcrud->unset_remove();
    $xcrud->unset_add();

    //  if($cid != 1){

    $xcrud->where('company_id = ', $cid);
    //   $xcrud->unset_remove();

    // }
    // else{
    //   $xcrud->unset_remove(true,'company_id','=',$cid);
    // }

    $xcrud->label('company_id', 'Company');
    $xcrud->label('pic', 'PIC');
    $xcrud->label('pic_contact', 'PIC Contact');
    $xcrud->validation_required('name,address,phone,pic,pic_contact');

    if ($cid != 1) {


      $xcrud->unset_add();
      //$xcrud->unset_edit();

    }

    $xcrud->columns('company_id, name, address, phone, pic, pic_contact, clocking_type, rest_days, off_days, invalid_clocking_distance');
    $xcrud->fields('company_id, name, address, timezone, phone, pic, pic_contact, logo_big, logo_small, weather_widget, clocking_type, rest_days, off_days, invalid_clocking_distance');

    $xcrud->change_type('clocking_type', 'select', 'regular', array("regular" => "Regular", "alternate" => "Alternate"));

    // $xcrud->columns('created_at,updated_at,deleted_at,weather_widget,logo_big,logo_small,timezone,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,inc_late_in,inc_late_break,inc_early_out,inc_short_hours,void_lateness_time_if_less_than,deduct_from_ot,deduction_date,sql_ot1_name, sql_ot1_code, sql_ot1_description, sql_ot1_rate,sql_ot2_name, sql_ot2_code, sql_ot2_description, sql_ot2_rate,sql_ot3_name, sql_ot3_code, sql_ot3_description, sql_ot3_rate,sql_ul_name, sql_ul_code, sql_ul_description, sql_ul_rate,sql_dw_name, sql_dw_code, sql_dw_description, sql_dw_rate,sql_dd1_name, sql_dd1_code, sql_dd1_description, sql_dd1_rate,sql_dd2_name, sql_dd2_code, sql_dd2_description, sql_dd2_rate,sql_e_l_name, sql_e_l_code, sql_e_l_description, sql_e_l_rate, sql_wrd_name, sql_wrd_code, sql_wrd_description, sql_wrd_rate, sql_wph_name, sql_wph_code, sql_wph_description, sql_wph_rate, round_first_hour_only', true);
    // $xcrud->fields('created_at,updated_at,deleted_at,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,inc_late_in,inc_late_break,inc_early_out,inc_short_hours,void_lateness_time_if_less_than,deduct_from_ot,deduction_date,sql_ot1_name, sql_ot1_code, sql_ot1_description, sql_ot1_rate,sql_ot2_name, sql_ot2_code, sql_ot2_description, sql_ot2_rate,sql_ot3_name, sql_ot3_code, sql_ot3_description, sql_ot3_rate,sql_ul_name, sql_ul_code, sql_ul_description, sql_ul_rate,sql_dw_name, sql_dw_code, sql_dw_description, sql_dw_rate,sql_dd1_name, sql_dd1_code, sql_dd1_description, sql_dd1_rate,sql_dd2_name, sql_dd2_code, sql_dd2_description, sql_dd2_rate,sql_e_l_name, sql_e_l_code, sql_e_l_description, sql_e_l_rate, sql_wrd_name, sql_wrd_code, sql_wrd_description, sql_wrd_rate, sql_wph_name, sql_wph_code, sql_wph_description, sql_wph_rate, round_first_hour_only', true);
    $xcrud->change_type('rest_days', 'multiselect', '', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday');
    $xcrud->change_type('off_days', 'multiselect', '', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday');

    $xcrud->change_type('timezone', 'select', 'Asia/Kuala_Lumpur', implode(",", timezone_identifiers_list()));

    //$xcrud->change_type('logo','image','',array('width'=>200, 'height'=>200,'ratio'=>1.0, 'manual_crop'=>true)); // 

    $xcrud->change_type('logo_big', 'image', '', array('quality' => 95)); // 
    $xcrud->change_type('logo_small', 'image', '', array('quality' => 95)); // 
    $xcrud->no_editor('weather_widget'); // 

    $xcrud->relation('company_id', 'companies', 'id', 'name', array('id' => $cid));

    $xcrud->order_by('id', 'desc');

    $xcrud->after_update('after_outlet_updation');

    return $xcrud->render();
  }


  public function days_settings($xcrud)
  {
    $xcrud->table('days_settings');
    $xcrud->buttons_position('left');
    $cid = get_user()["company_id"];
    $xcrud->columns('from_hour,to_hour,days');
    $xcrud->fields('from_hour,to_hour,days');
    $xcrud->where('company_id = ', $cid);
    $xcrud->pass_var('company_id', $cid);

    $xcrud->after_insert('after_day_settings_insert');
    $xcrud->after_update('after_day_settings_update');
    $xcrud->before_remove('before_day_settings_deletion');
    return $xcrud->render();
  }

  public function work_hours_settings($xcrud)
  {
    $xcrud->table('company_working_hours');
    $xcrud->buttons_position('left');
    $cid = get_user()["company_id"];

    $xcrud->columns('group_id, total_hours, half_hours');
    $xcrud->fields('group_id, total_hours, half_hours');

    $xcrud->label('group_id', 'Employee Group');

    $xcrud->relation('group_id', 'employee_groups', 'id', 'name', array('company_id' => $cid), 'name asc', false);

    // $xcrud->unset_add();
    $xcrud->unset_remove(true, 'group_id', '=', null);
    $xcrud->unset_view();
    $xcrud->unset_print();
    $xcrud->unset_csv();
    $xcrud->unset_limitlist();
    $xcrud->unset_search();

    $xcrud->where('company_id = ', $cid);

    $xcrud->before_insert('before_work_hour_insertion');
    $xcrud->after_update('after_work_hour_updation');
    return $xcrud->render();
  }

  public function overtime($xcrud)
  {

    $xcrud->table('companies');
    $xcrud->buttons_position('left');



    $cid = get_user()["company_id"];

    $permissions_level = get_user()["permissions_level"];

    if ($permissions_level == "Outlet") {

      //$xcrud->unset_edit();
    }


    $xcrud->columns('pay_overtime,pay_after_hours,overtime_rate');
    $xcrud->fields('pay_overtime,pay_after_hours,overtime_rate');

    $xcrud->unset_list();
    $xcrud->unset_add();
    $xcrud->label('pay_overtime', 'Do you want to pay overtime?');
    $xcrud->label('pay_after_hours', 'Pay overtime after how many hours?');
    $xcrud->label('overtime_rate', 'What is the overtime rate?');
    $xcrud->field_tooltip('overtime_rate', 'For example 1.5 indicates normal hour rate * 1.5');



    return '<div class="col-md-6">' . $xcrud->render('edit', $cid) . '</div>';
  }


  public function companies($xcrud)
  {
    $current_user = get_user();

    $xcrud->table('companies');
    $xcrud->buttons_position('left');



    $cid = $current_user["company_id"];
    $is_merit_allowed = in_array($cid, companies_allowed_for_merit());

    $permissions_level = $current_user["permissions_level"];

    if ($permissions_level == "Outlet") {

      $xcrud->unset_edit();
    }

    //if($cid != 1){

    $xcrud->where('id = ', $cid);
    $xcrud->unset_remove();

    // }
    // else{
    //   $xcrud->unset_remove(true,'id','=',$cid);
    // }

    $xcrud->label('pic', 'PIC');
    $xcrud->label('pic_contact', 'PIC Contact');
    //$xcrud->label('industry_id','Industry');
    $xcrud->validation_required('name,address,phone,pic,pic_contact');

    //$xcrud->relation('industry_id','industries','id','name');

    //if($cid != 1){


    $xcrud->unset_add();
    //$xcrud->unset_edit();

    //}
    $columns = 'name,organization_id,address,phone,lhdn_no,pic,pic_contact,qr_code,self_clocking,bluetooth,logo';
    $fields = 'name,address,phone,lhdn_no,pic,pic_contact,qr_code,include_in_qr_code,exclude_from_qr_code,self_clocking,include_in_self_clocking,exclude_from_self_clocking,bluetooth,include_in_bluetooth,exclude_from_bluetooth,logo';

    if ($is_merit_allowed) {
      $merit_system_columns_fields = ',merit_system_sign,merit_system_position_text,is_merit_approved';
      $columns .= $merit_system_columns_fields;
      $fields .= $merit_system_columns_fields;
    }

    $xcrud->columns($columns);
    $xcrud->fields($fields);
    $xcrud->relation('exclude_from_qr_code', 'employee_groups', 'id', 'name', array('company_id' => $cid), $order_by = false, $multi = true);
    $xcrud->relation('include_in_qr_code', 'employee_groups', 'id', 'name', array('company_id' => $cid), $order_by = false, $multi = true);

    $xcrud->relation('exclude_from_self_clocking', 'employee_groups', 'id', 'name', array('company_id' => $cid), $order_by = false, $multi = true);
    $xcrud->relation('include_in_self_clocking', 'employee_groups', 'id', 'name', array('company_id' => $cid), $order_by = false, $multi = true);

    $xcrud->relation('exclude_from_bluetooth', 'employee_groups', 'id', 'name', array('company_id' => $cid), $order_by = false, $multi = true);
    $xcrud->relation('include_in_bluetooth', 'employee_groups', 'id', 'name', array('company_id' => $cid), $order_by = false, $multi = true);

    $xcrud->label('lhdn_no', 'LHDN No.');

    $xcrud->label('exclude_from_qr_code', '- Exclude From Qr Code');
    $xcrud->label('include_in_qr_code', '- Include In Qr Code');
    $xcrud->label('exclude_from_self_clocking', '- Exclude From Self Clocking');
    $xcrud->label('include_in_self_clocking', '- Include In Self Clocking');
    $xcrud->label('exclude_from_bluetooth', '- Exclude From Bluetooth');
    $xcrud->label('include_in_bluetooth', '- Include In Bluetooth');
    if ($is_merit_allowed) {
      $xcrud->label('merit_system_sign', 'Merit System Signature - 400x150');
      $xcrud->label('merit_system_position_text', 'Merit System Position');
      $xcrud->label('is_merit_approved', 'Auto Approve Merit');
      $xcrud->change_type('merit_system_sign', 'image', '', array('width' => 450, 'height' => 150, 'crop' => true));
      $xcrud->change_type('is_merit_approved', 'select', '1', array('1' => 'Yes', '0' => 'No'));
    }



    //$xcrud->relation('this_table_relation_id','other_table_name','other_table_id','other_table_display_field');
    $xcrud->change_type('qr_code', 'select', '1', array('1' => 'Yes', '0' => 'No')); // 
    $xcrud->change_type('self_clocking', 'select', '1', array('1' => 'Yes', '0' => 'No')); // 
    $xcrud->change_type('bluetooth', 'select', '1', array('1' => 'Yes', '0' => 'No')); // 

    $xcrud->change_type('logo', 'image', '', array('quality' => 95)); // 
    //$xcrud->change_type('logo_small','image','',array('quality'=>100)); // 

    $xcrud->order_by('id', 'desc');

    $xcrud->after_update('after_company_details_updation');

    return $xcrud->render();
  }

  public function packages($xcrud)
  {

    $xcrud = Xcrud::get_instance('MyInstance');
    $xcrud->table('packages');
    $xcrud->buttons_position('left');
    $xcrud->order_by('id', 'desc');
    $xcrud->unset_add();
    return $xcrud->render();
  }

  public function tickets($xcrud)
  {

    //var_dump(get_user()["permissions"]);

    $xcrud->table('tickets'); //this
    $xcrud->buttons_position('left');

    $xcrud->label('event_id', 'Event');
    $cid = get_user()["company_id"];


    if ($cid != 1) {

      $xcrud->where('company_id = ', $cid);
    }

    if ($cid != 1) {

      $xcrud->relation('company_id', 'companies', 'id', 'name', array('id' => $cid));
    } else {
      $xcrud->relation('company_id', 'companies', 'id', 'name');
    }

    $xcrud->relation('event_id', 'events', 'id', 'event_name_english', '', '', '', '', '', 'company_id', 'company_id');
    $xcrud->validation_required('ticket_type,ticket_price,ticket_limit,winner_ticket,event_id,company_id');


    $xcrud->subselect('Sold', 'SELECT COUNT(1) FROM ticket_transactions WHERE ticket_id = {id}');
    $xcrud->subselect('Scanned', 'SELECT COUNT(1) FROM ticket_scans WHERE ticket_id = {id}');




    $xcrud->columns('ticket_type,ticket_price,ticket_limit,Sold,Scanned,winner_ticket,company_id,event_id');
    $xcrud->fields('ticket_type,ticket_price,ticket_limit,winner_ticket,company_id,event_id');
    $xcrud->label('company_id', 'Company');
    //$xcrud->relation('event_id','events','id','event_name_english');

    //$xcrud->change_type('photo','image','',array('width'=>300, 'height'=>300,'ratio'=>1.0, 'manual_crop'=>true)); //

    $xcrud->order_by('id', 'desc');


    return $xcrud->render();
  }

  public function ticket_transactions($xcrud)
  {

    $xcrud->table('ticket_transactions');
    $xcrud->buttons_position('left');

    $xcrud->button(base_url() . 'print_ticket?qr={qr_code}', 'Print', 'fa fa-print', '', array('target' => '_blank'));

    $xcrud->label('event_id', 'Event');
    $xcrud->label('ticket_id', 'Ticket');
    $xcrud->label('qr_code', 'QR Code');
    $xcrud->label('external_ticket', 'External');
    $xcrud->buttons_position('left');

    $xcrud->join('employee_id', 'employees', 'id', 'employees', true);
    $xcrud->join('ticket_id', 'tickets', 'id', 'tickets', true);
    $xcrud->column_cut(8);


    $cid = get_user()["company_id"];

    if ($cid != 1) {

      $xcrud->where('tickets.company_id = ', $cid);
    }



    $xcrud->label('employees.first_name', 'Sold By');

    //$xcrud->pass_var('qr_code','{event_id}{ticket_id}{visitor_name}{visitor_email}','edit');

    //$xcrud->before_update('hash_that_shit');
    $xcrud->replace_insert('hash_that_shit');


    $xcrud->columns('event_id,ticket_id,qr_code,visitor_name,paid_amount,employees.first_name,created_at,external_ticket');
    $xcrud->fields('event_id,ticket_id,visitor_name,visitor_phone,visitor_company,visitor_email,paid_amount');
    $xcrud->pass_var('employee_id', get_user()["id"], 'create');



    $xcrud->validation_required('event_id,ticket_id,visitor_name,visitor_phone,paid_amount');

    $xcrud->relation('event_id', 'events', 'id', 'event_name_english');

    //$xcrud->relation('ticket_id','tickets','id','ticket_type');
    $xcrud->relation('ticket_id', 'tickets', 'id', array('ticket_type', 'ticket_price'), '', '', '', ' $', '', 'event_id', 'event_id');

    $xcrud->order_by('id', 'desc');
    $xcrud->readonly('qr_code');

    //$xcrud->change_type('photo','image','',array('width'=>300, 'height'=>300,'ratio'=>1.0, 'manual_crop'=>true)); //    
    return $xcrud->render();
  }

  public function fast_ticket_transactions($xcrud)
  {

    $xcrud->table('ticket_transactions');
    $xcrud->buttons_position('left');

    //$xcrud->columns('event_id,ticket_id,qr_code,paid_amount');
    //$xcrud->fields('event_id,ticket_id,paid_amount');
    $xcrud->label('event_id', 'Event');
    $xcrud->label('ticket_id', 'Ticket');
    $xcrud->label('qr_code', 'QR Code');
    $xcrud->label('external_ticket', 'External');
    $xcrud->label('employees.first_name', 'Sold By');
    $xcrud->column_cut(8);
    $xcrud->readonly('qr_code');

    $xcrud->button(base_url() . 'print_ticket?qr={qr_code}', 'Print', 'fa fa-print', '', array('target' => '_blank'));

    //$xcrud->pass_var('qr_code','{event_id}{ticket_id}{visitor_name}{visitor_email}','edit');

    //$xcrud->before_update('hash_that_shit');
    //$xcrud->before_insert('hash_that_shit');
    $xcrud->replace_insert('hash_that_shit');



    //$xcrud->columns('qr_code',true);
    //$xcrud->fields('qr_code',true);

    $xcrud->join('ticket_id', 'tickets', 'id', 'tickets', true);

    $cid = get_user()["company_id"];

    if ($cid != 1) {

      $xcrud->where('tickets.company_id = ', $cid);
    }

    $xcrud->join('employee_id', 'employees', 'id', 'employees', true);
    $xcrud->columns('event_id,ticket_id,qr_code,visitor_name,paid_amount,employees.first_name,created_at,external_ticket');
    $xcrud->fields('event_id,ticket_id,paid_amount');
    $xcrud->pass_var('employee_id', get_user()["id"], 'create');
    $xcrud->order_by('id', 'desc');

    $xcrud->validation_required('event_id,ticket_id,paid_amount');

    $xcrud->relation('event_id', 'events', 'id', 'event_name_english');

    //$xcrud->relation('ticket_id','tickets','id','ticket_type');
    $xcrud->relation('ticket_id', 'tickets', 'id', array('ticket_type', 'ticket_price'), '', '', '', ' $', '', 'event_id', 'event_id');

    $xcrud->order_by('id', 'desc');

    //$xcrud->change_type('photo','image','',array('width'=>300, 'height'=>300,'ratio'=>1.0, 'manual_crop'=>true)); //    
    return $xcrud->render();
  }


  public function employees($xcrud)
  {

    $xcrud->table('employees');
    $xcrud->buttons_position('left');

    $cid = get_user()["company_id"];

    //if($cid != 1){
    $xcrud->where('company_id = ', $cid);
    //}




    if ($cid != 1) {

      $xcrud->relation('company_id', 'companies', 'id', 'name', array('id' => $cid));
    } else {
      $xcrud->relation('company_id', 'companies', 'id', 'name');
    }

    $xcrud->relation('role_id', 'roles', 'id', 'job_name', '', '', '', '', '', 'company_id', 'company_id');

    $xcrud->relation('department_id', 'departments', 'id', 'name', '', '', '', '', '', 'company_id', 'company_id');
    $xcrud->relation('position_id', 'positions', 'id', 'title', '', '', '', '', '', 'department_id', 'department_id');
    $xcrud->relation('branch_id', 'branches', 'id', 'name', '', '', '', '', '', 'company_id', 'company_id');

    //$xcrud->relation('position','roles','id','job_name','','','','','','company_id','company_id');

    //$xcrud->subselect('Shift Today',"SELECT GROUP_CONCAT(b.first_name, '(' ,b.special_id, ')'  ORDER BY b.first_name SEPARATOR ', ') emp FROM shift_days a INNER JOIN employees b ON FIND_IN_SET(b.id, a.employees) WHERE a.date = '$today' AND shift_id={id} LIMIT 1");

    //$xcrud->subselect('Shift Today',"SELECT GROUP_CONCAT(b.first_name, '(' ,b.special_id, ')'  ORDER BY b.first_name SEPARATOR ', ') emp FROM shift_days a INNER JOIN employees b ON FIND_IN_SET(b.id, a.employees) WHERE a.date = '$today' AND shift_id={id} LIMIT 1");




    $xcrud->label('role_id', 'Role');
    $xcrud->label('department_id', 'Department');
    $xcrud->label('position_id', 'Position');
    $xcrud->label('branch_id', 'Branch');
    $xcrud->label('qr_barcode', 'Code');
    $xcrud->label('dob', 'DOB');
    $xcrud->label('pob', 'POB');
    $xcrud->label('epf_no', 'EPF No');
    $xcrud->label('address', 'Permanent Address');
    $xcrud->label('temp_address', 'Temporary Address');

    $xcrud->field_tooltip('qr_barcode', 'Leave it empty to generate automatically');


    //$xcrud->pass_var('qr_barcode', random_string(8), 'edit');
    //$xcrud->pass_var('qr_barcode', date('Y-m-d H:i:s'), 'edit');

    $xcrud->before_insert('generate_qr_barcode');
    $xcrud->before_update('generate_qr_barcode_update');

    //$xcrud->change_type('photo','image','',array('width'=>300, 'height'=>300,'ratio'=>1.0, 'manual_crop'=>true)); // 

    $xcrud->label('company_id', 'Company');
    $xcrud->columns('company_id,branch_id,first_name,photo,role_id,department_id,position_id,qr_barcode');

    $xcrud->fields('special_id,company_id,branch_id,role_id,department_id,position_id,first_name,last_name,email,password,address,temp_address,epf_no,dob,pob,sex,race,religion,nationality,mobile,house_phone,ic_passport,photo,hired_on,qr_barcode', false, 'Basic Information');

    $xcrud->fields('salary,is_ot,ot_rate_percentage,grade,incentive,ot_hourly_rate', false, 'Salary & OT');

    $xcrud->fields('emergency_relation,emergency_mobile,emergency_email,emergency_house_phone,emergency_office,emergency_address', false, 'Emergency Contact');

    $xcrud->fields('license_class,license_no,license_expiry', false, 'License Details');

    $xcrud->fields('bank_account_no,bank_name', false, 'Bank Details');




    //$xcrud->validation_required('company_id,email,first_name,last_name,address,phone,role_id,username,location');
    $xcrud->change_type('photo', 'image', '', array('width' => 200, 'height' => 200, 'ratio' => 1.0, 'manual_crop' => true));


    $xcrud->change_type('password', 'password', 'md5', 32);

    $xcrud->validation_required('special_id,branch_id,department_id,position_id,company_id,email,first_name,last_name,address,phone,photo,role_id,username,location,permissions');

    $xcrud->order_by('id', 'desc');

    //$xcrud->columns('job_no,updated_at,deleted_at,created_at',true);
    //$xcrud->fields('job_no,updated_at,deleted_at,created_at',true);


    return "<h2>Not uploaded yet</h2>"; //$xcrud->render();
  }


  public function roles($xcrud)
  {

    $xcrud->table('roles');
    $cid = get_user()["company_id"];
    $xcrud->buttons_position('left');

    //if($cid != 1){

    $xcrud->where('company_id = ', $cid);
    //}

    $xcrud->where('role_type', 'invotime');

    $xcrud->pass_var('role_type', 'invotime');



    //if($cid != 1){

    $xcrud->relation('company_id', 'companies', 'id', 'name', array('id' => $cid));

    // }
    // else{ 

    //   $xcrud->relation('company_id','companies','id','name');

    // }

    //$xcrud->relation('department_id','departments','id','name',array('company_id' => get_user()["company_id"]))->label('department_id','Department');

    $xcrud->change_type('permissions', 'multiselect', '', get_menus_for_user_management());

    $permissions_level = get_user()["permissions_level"];

    if ($permissions_level != "Company") {
      $xcrud->unset_remove();
      // $xcrud->unset_add();
      // $xcrud->unset_edit();

    }



    $xcrud->label('company_id', 'Company');
    $xcrud->label('job_name', 'Role Name');
    $xcrud->columns('senior_staff_access,check_payroll_access,approve_payroll_access,role_type,company_id,permissions,updated_at,deleted_at,created_at', true);
    $xcrud->fields('senior_staff_access,check_payroll_access,approve_payroll_access,role_type,updated_at,deleted_at,created_at', true);
    $xcrud->validation_required('company_id,job_name');

    $xcrud->order_by('id', 'desc');

    //$xcrud->change_type('photo','image','',array('width'=>300, 'height'=>300,'ratio'=>1.0, 'manual_crop'=>true));

    $xcrud->after_insert('after_role_insertion');
    $xcrud->after_update('after_role_updation');
    $xcrud->before_remove('before_role_deletion');

    return $xcrud->render();
  }

  public function roles_payroll($xcrud)
  {

    $xcrud->table('roles');
    $cid = $this->session->userdata("payroll_user")["company_id"];
    $xcrud->buttons_position('left');

    //if($cid != 1){

    $xcrud->where('company_id = ', $cid);
    //}

    $xcrud->where('role_type', 'payroll');

    $xcrud->pass_var('role_type', 'payroll');


    //if($cid != 1){

    $xcrud->relation('company_id', 'companies', 'id', 'name', array('id' => $cid));

    // }
    // else{ 

    //   $xcrud->relation('company_id','companies','id','name');

    // }

    //$xcrud->relation('department_id','departments','id','name',array('company_id' => get_user()["company_id"]))->label('department_id','Department');

    $xcrud->change_type('permissions', 'multiselect', '', get_menus_for_payroll());

    // $permissions_level = $this->session->userdata("payroll_user")["permissions_level"];

    $permissions_level = "Company";

    if ($permissions_level != "Company") {
      $xcrud->unset_remove();
      // $xcrud->unset_add();
      // $xcrud->unset_edit();

    }



    $xcrud->label('company_id', 'Company');
    $xcrud->label('job_name', 'Role Name');
    $xcrud->columns('limit_access_to_department,is_emp_summary_editable,role_type,company_id,permissions,updated_at,deleted_at,created_at', true);
    $xcrud->fields('limit_access_to_department,is_emp_summary_editable,role_type,updated_at,deleted_at,created_at', true);
    $xcrud->validation_required('company_id,job_name');

    $xcrud->order_by('id', 'desc');

    //$xcrud->change_type('photo','image','',array('width'=>300, 'height'=>300,'ratio'=>1.0, 'manual_crop'=>true));

    $xcrud->after_insert('after_role_insertion');
    $xcrud->after_update('after_role_updation');
    $xcrud->before_remove('before_role_deletion');

    return $xcrud->render();
  }

  public function ticket_scans($xcrud)
  {

    $xcrud->table('ticket_scans');
    $cid = get_user()["company_id"];
    $xcrud->buttons_position('left');

    if ($cid != 1) {

      $xcrud->where('events.company_id = ', $cid);
    }

    $xcrud->join('ticket_transaction_id', 'ticket_transactions', 'id', 'ticket_transactions', true);
    $xcrud->join('scanned_by', 'employees', 'id', 'employees', true);

    $xcrud->join('ticket_transactions.ticket_id', 'tickets', 'id', 'tickets', true);

    $xcrud->join('tickets.event_id', 'events', 'id', 'events', true);
    $xcrud->join('events.company_id', 'companies', 'id', 'companies', true);

    $xcrud->columns('employees.first_name,companies.name, events.event_name_english,tickets.ticket_type,ticket_transactions.visitor_name,time_in');

    $xcrud->label('employees.first_name', 'Scanned By');
    $xcrud->label('companies.name', 'Company');
    $xcrud->label('events.event_name_english', 'Event');
    $xcrud->label('events.event_name_english', 'Event');
    $xcrud->label('time_in', 'Scan Time');

    $xcrud->order_by('time_in', 'desc');



    $xcrud->unset_remove();
    $xcrud->unset_edit();
    $xcrud->unset_add();

    return $xcrud->render();
  }


  public function public_holidays($xcrud)
  {

    $cid = get_user()["company_id"];
    $bid = get_user()["branch_id"];

    $permissions_level = get_user()["permissions_level"];



    $xcrud->table('public_holidays');
    $xcrud->pass_var('company_id', $cid);

    $xcrud->where('company_id =', $cid);

    // $xcrud->where('year(holiday_date) = '.date("Y"));

    if ($permissions_level == "Outlet") {
      $xcrud->where('branch_id = ', $bid);
      $xcrud->relation('branch_id', 'branches', 'id', 'name', array('company_id' => $cid, 'id' => $bid));
    } else {
      $xcrud->relation('branch_id', 'branches', 'id', 'name', array('company_id' => $cid));
    }

    $xcrud->relation(
      'include_groups',
      'employee_groups',
      'id',
      'name',
      array('company_id' => $cid, 'branch_id' => $bid),
      $order_by = false,
      $multi = true
    );
    $xcrud->relation(
      'exclude_groups',
      'employee_groups',
      'id',
      'name',
      array('company_id' => $cid, 'branch_id' => $bid),
      $order_by = false,
      $multi = true
    );

    $xcrud->label('branch_id', 'Branch');

    $xcrud->fields('company_id,updated_at,deleted_at,created_at', true);
    $xcrud->columns('company_id,updated_at,deleted_at,created_at', true);
    $xcrud->order_by('holiday_date', 'asc');

    $previous_button = '<button id="copy-holidays" class="btn btn-primary">Import from previous year</button>';

    $xcrud->after_insert('after_holiday_insertion');
    $xcrud->after_update('after_holiday_updation');
    $xcrud->before_remove('before_holiday_removal');

    return $xcrud->render() . $previous_button;
  }

  public function sql_payroll_settings($xcrud)
  {

    $xcrud->table('companies');
    //$xcrud->pass_var('company_id', get_user()["company_id"]);

    $xcrud->where('id =', get_user()["company_id"]);
    // $xcrud->where('holiday_date >= ', date("Y") . "-01-01");
    // $xcrud->where('holiday_date <= ', date("Y") . "-12-31");
    //

    $xcrud->label(array('sql_ot1_name' => 'Name', 'sql_ot1_code' => 'Code', 'sql_ot1_description' => 'Description', 'sql_ot1_rate' => 'Rate'));
    $xcrud->label(array('sql_ot2_name' => 'Name', 'sql_ot2_code' => 'Code', 'sql_ot2_description' => 'Description', 'sql_ot2_rate' => 'Rate'));
    $xcrud->label(array('sql_ot3_name' => 'Name', 'sql_ot3_code' => 'Code', 'sql_ot3_description' => 'Description', 'sql_ot3_rate' => 'Rate'));
    $xcrud->label(array('sql_ul_name' => 'Name', 'sql_ul_code' => 'Code', 'sql_ul_description' => 'Description', 'sql_ul_rate' => 'Rate'));
    $xcrud->label(array('sql_dw_name' => 'Name', 'sql_dw_code' => 'Code', 'sql_dw_description' => 'Description', 'sql_dw_rate' => 'Rate'));
    $xcrud->label(array('sql_dd1_name' => 'Name', 'sql_dd1_code' => 'Code', 'sql_dd1_description' => 'Description', 'sql_dd1_rate' => 'Rate'));
    $xcrud->label(array('sql_dd2_name' => 'Name', 'sql_dd2_code' => 'Code', 'sql_dd2_description' => 'Description', 'sql_dd2_rate' => 'Rate'));
    $xcrud->label(array('sql_e_l_name' => 'Name', 'sql_e_l_code' => 'Code', 'sql_e_l_description' => 'Description', 'sql_e_l_rate' => 'Rate'));


    $xcrud->columns('sql_ot1_name, sql_ot1_code, sql_ot1_description', false);

    $xcrud->fields('sql_ot1_name, sql_ot1_code, sql_ot1_description, sql_ot1_rate', false, 'Overtime 1');
    $xcrud->fields('sql_ot2_name, sql_ot2_code, sql_ot2_description, sql_ot2_rate', false, 'Overtime 2');
    $xcrud->fields('sql_ot3_name, sql_ot3_code, sql_ot3_description, sql_ot3_rate', false, 'Overtime 3');
    $xcrud->fields('sql_ul_name, sql_ul_code, sql_ul_description, sql_ul_rate', false, 'Unpaid Leave');
    $xcrud->fields('sql_e_l_name, sql_e_l_code, sql_e_l_description, sql_e_l_rate', false, 'Early / Late');
    $xcrud->fields('sql_dw_name, sql_dw_code, sql_dw_description, sql_dw_rate', false, 'Daily Wage');
    $xcrud->fields('sql_dd1_name, sql_dd1_code, sql_dd1_description, sql_dd1_rate', false, 'Deduction 1');
    $xcrud->fields('sql_dd2_name, sql_dd2_code, sql_dd2_description, sql_dd2_rate', false, 'Deduction 2');

    $xcrud->unset_remove();
    $xcrud->unset_add();
    $xcrud->unset_print();
    $xcrud->unset_csv();
    $xcrud->unset_search();
    $xcrud->unset_pagination();
    $xcrud->unset_limitlist();
    $xcrud->unset_sortable();
    $xcrud->unset_list();

    return $xcrud->render('edit', get_user()["company_id"]);
  }

  public function merit_deduction_settings($xcrud)
  {
    $current_user = get_user();
    $company_id = $current_user["company_id"];

    $xcrud->table('merit_deduction_points');
    $xcrud->where('company_id =', $company_id);
    // relations
    $xcrud->relation('company_id', 'companies', 'id', 'name', array('id' => $company_id));
    $xcrud->relation('branch_id', 'branches', 'id', 'name', array('company_id' => $company_id));

    $xcrud->change_type('offense', 'select', '', 'Late In,Early Out,Half Day Paid, Full Day Paid,Half Day Unpaid,Medical Leave,Missing In / Out,Late Break,Absent / Unpaid Leave');

    $xcrud->label(array('company_id' => 'Company', 'branch_id' => 'Branch', 'special_deduction_points' => 'Unapproved Deduction Points'));

    $xcrud->columns('id, created_at, updated_at', true);

    $xcrud->fields('id, created_at, updated_at', true);

    $xcrud->unset_csv();
    $xcrud->unset_print();

    return $xcrud->render();
  }

  public function allowances_settings($xcrud)
  {
    $current_user = get_user();
    $company_id = $current_user["company_id"];

    $xcrud->table('allowances_settings');
    $xcrud->where('company_id =', $company_id);

    $xcrud->columns('name, code, description, rate');
    $xcrud->fields('name, code, description, rate');

    // make all required
    $xcrud->validation_required('name, code, description, rate');

    $xcrud->pass_var('company_id', $company_id);

    $xcrud->unset_csv();
    $xcrud->unset_print();

    return $xcrud->render();
  }
}
