<?php
function publish_action($xcrud)
{
    if ($xcrud->get('primary'))
    {
        $db = Xcrud_db::get_instance();
        $query = 'UPDATE base_fields SET `bool` = b\'1\' WHERE id = ' . (int)$xcrud->get('primary');
        $db->query($query);
    }
}
function unpublish_action($xcrud)
{
    if ($xcrud->get('primary'))
    {
        $db = Xcrud_db::get_instance();
        $query = 'UPDATE base_fields SET `bool` = b\'0\' WHERE id = ' . (int)$xcrud->get('primary');
        $db->query($query);
    }
}

function exception_example($postdata, $primary, $xcrud)
{
    // get random field from $postdata
    $postdata_prepared = array_keys($postdata->to_array());
    shuffle($postdata_prepared);
    $random_field = array_shift($postdata_prepared);
    // set error message
    $xcrud->set_exception($random_field, 'This is a test error', 'error');
}

function test_column_callback($value, $fieldname, $primary, $row, $xcrud)
{
    return $value . ' - nice!';
}

function after_upload_example($field, $file_name, $file_path, $params, $xcrud)
{
    $ext = trim(strtolower(strrchr($file_name, '.')), '.');
    if ($ext != 'pdf' && $field == 'uploads.simple_upload')
    {
        unlink($file_path);
        $xcrud->set_exception('simple_upload', 'This is not PDF', 'error');
    }
}

function movetop($xcrud)
{
    if ($xcrud->get('primary') !== false)
    {
        $primary = (int)$xcrud->get('primary');
        $db = Xcrud_db::get_instance();
        $query = 'SELECT `officeCode` FROM `offices` ORDER BY `ordering`,`officeCode`';
        $db->query($query);
        $result = $db->result();
        $count = count($result);

        $sort = array();
        foreach ($result as $key => $item)
        {
            if ($item['officeCode'] == $primary && $key != 0)
            {
                array_splice($result, $key - 1, 0, array($item));
                unset($result[$key + 1]);
                break;
            }
        }

        foreach ($result as $key => $item)
        {
            $query = 'UPDATE `offices` SET `ordering` = ' . $key . ' WHERE officeCode = ' . $item['officeCode'];
            $db->query($query);
        }
    }
}
function movebottom($xcrud)
{
    if ($xcrud->get('primary') !== false)
    {
        $primary = (int)$xcrud->get('primary');
        $db = Xcrud_db::get_instance();
        $query = 'SELECT `officeCode` FROM `offices` ORDER BY `ordering`,`officeCode`';
        $db->query($query);
        $result = $db->result();
        $count = count($result);

        $sort = array();
        foreach ($result as $key => $item)
        {
            if ($item['officeCode'] == $primary && $key != $count - 1)
            {
                unset($result[$key]);
                array_splice($result, $key + 1, 0, array($item));
                break;
            }
        }

        foreach ($result as $key => $item)
        {
            $query = 'UPDATE `offices` SET `ordering` = ' . $key . ' WHERE officeCode = ' . $item['officeCode'];
            $db->query($query);
        }
    }
}

function show_description($value, $fieldname, $primary_key, $row, $xcrud)
{
    $result = '';
    if ($value == '1')
    {
        $result = '<i class="fa fa-check" />' . 'OK';
    }
    elseif ($value == '2')
    {
        $result = '<i class="fa fa-circle-o" />' . 'Pending';
    }
    return $result;
}

function custom_field($value, $fieldname, $primary_key, $row, $xcrud)
{
    return '<input type="text" readonly class="xcrud-input" name="' . $xcrud->fieldname_encode($fieldname) . '" value="' . $value .
        '" />';
}
function unset_val($postdata)
{
    $postdata->del('Paid');
}

function format_phone($new_phone)
{
    $new_phone = preg_replace("/[^0-9]/", "", $new_phone);

    if (strlen($new_phone) == 7)
        return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $new_phone);
    elseif (strlen($new_phone) == 10)
        return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $new_phone);
    else
        return $new_phone;
}

function before_list_example($list, $xcrud)
{
    var_dump($list);
}

function after_update_test($pd, $pm, $xc)
{
    $xc->search = 0;
}

// function after_upload_test($field, &$filename, $file_path, $upload_config, $this)
// {
//     $filename = 'bla-bla-bla';
// }


function hash_that_shit($postdata, $xcrud){

    // $db = Xcrud_db::get_instance();
    // // $query = 'SELECT count(1) SET `bool` = b\'1\' WHERE id = ' . (int)$xcrud->get('primary');
    // $query = "SELECT count(1) FROM tickets";

    // $res = $db->query($query);

    // var_dump($res);

     $postdata->set('qr_code', md5( $postdata->get('id') . uniqid()));

     $qr_code = $postdata->get('qr_code');
     $ticket_id = $postdata->get('ticket_id');
     $event_id = $postdata->get('event_id');
     $employee_id = $postdata->get('employee_id');
     $paid_amount = $postdata->get('paid_amount');
     $visitor_name = $postdata->get('visitor_name');
     $visitor_phone = $postdata->get('visitor_phone');
     $visitor_company = $postdata->get('visitor_company');
     $visitor_email = $postdata->get('visitor_email');

     $db = Xcrud_db::get_instance();

     $query = "INSERT INTO ticket_transactions (event_id,ticket_id,employee_id,qr_code,paid_amount,visitor_name,visitor_phone,visitor_company,visitor_email) VALUES ($event_id,$ticket_id,$employee_id,'$qr_code',$paid_amount,'$visitor_name','$visitor_phone','$visitor_company','$visitor_email')";

     $db->query($query);

    //var_dump($postdata);

    //$xcrud->set_exception('ticket_id','This is not PDF','error');
}


function generate_qr_barcode($postdata, $xcrud){
    if(empty($postdata->get('qr_barcode'))){
        $postdata->set('qr_barcode', random_string(8));
    }
}

function makeClockingTime($postdata, $xcrud)
{
    $employee_id = $postdata->get('employee_id');
    $db = Xcrud_db::get_instance();
    $query = 'SELECT shift_id FROM shift_days where FIND_IN_SET(' . $employee_id . ', employees) > 0 and date = "' . $postdata->get('current_date') . '"';
    $db->query($query);
    $result = $db->row();

    if ($result) {
        $postdata->set('shift_id', $result["shift_id"]);
    } else {
        $postdata->set('shift_id', 0);
    }

    $time = $postdata->get('datetime');
    $current_date = $postdata->get("current_date");

    $log_data = [
        'action' => 'Added,Clocking',
        'target_id' => $employee_id,
        'to_time' => $time,
        'for_date' => $current_date,
        'clocking_type' => $postdata->get('type'),
    ];
    insert_log("Clockings", $log_data);

    $time = $postdata->get('current_date') . " " . $time;
    $postdata->set('datetime', $time);
    $postdata->del('current_date');
    $postdata->set('add_by_admin', 1);
}

function makeClockingTimeUpdate($postdata, $primary, $xcrud)
{
    $employee_id = $postdata->get('employee_id');
    $db = Xcrud_db::get_instance();
    $query = 'SELECT shift_id FROM shift_days where FIND_IN_SET(' . $employee_id . ', employees) > 0 and date = "' . $postdata->get('current_date') . '"';
    $db->query($query);
    $result = $db->row();
    if ($result) {
        $postdata->set('shift_id', $result["shift_id"]);
    } else {
        $postdata->set('shift_id', 0);
    }
    $time = $postdata->get('datetime');
    $current_date = $postdata->get('current_date');

    // get previous record
    $db->query("SELECT * FROM clockings_news WHERE id = '$primary'");
    $old_clocking = $db->row();
    $old_datetime = explode(" ", $old_clocking['datetime']);

    $old_time = $old_datetime[1];

    $log_data = [
        'action' => 'Edited,Clocking',
        'target_id' => $employee_id,
        'from_time' => $old_time,
        'to_time' => $time,
        'for_date' => $current_date,
        'clocking_type' => $postdata->get('type'),
    ];
    insert_log('Clockings', $log_data);
    $checking['old_clocking_type'] = $old_clocking['type'];
    $checking['$new_clocking_type'] = $postdata->get('type');
    $checking['$old_clocking_time'] = $old_time;
    $checking['$new_clocking_time'] = $time;
    if ($checking['old_clocking_type'] != $checking['$new_clocking_type'] 
        && $checking['$old_clocking_time'] == $checking['$new_clocking_time']) 
        {
        //Do nthng;
    }else{
        $postdata->set('update_by_admin', 1);
    }
    // echo $old_time.' --- '.$time;die;
    $time = $postdata->get('current_date') . " " . $time;
    $postdata->set('datetime', $time);
    $postdata->del('current_date');
}

function makeClockingTime_overnight($postdata, $xcrud)
{
    $employee_id = $postdata->get('employee_id');
    $db = Xcrud_db::get_instance();
    $query = 'SELECT shift_id FROM shift_days where FIND_IN_SET(' . $employee_id . ', employees) > 0 and date = "' . $postdata->get('current_date') . '"';
    $db->query($query);
    $result = $db->row();

    if ($result) {
        $postdata->set('shift_id', $result["shift_id"]);
    } else {
        $postdata->set('shift_id', 0);
    }

    $current_date = $postdata->get('current_date');
    $time = $postdata->get('datetime');

    $log_data = [
        'action' => 'Added,Clocking',
        'target_id' => $employee_id,
        'to_time' => $time,
        'for_date' => $current_date,
        'clocking_type' => $postdata->get('type'),
    ];
    insert_log('Clockings', $log_data);

    $hour = explode(":", $time)[0];
    $minute = explode(":", $time)[1];
    if ($hour < 7 || ($hour == 7 && $minute == 0) ) {
        $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
    }

    $time = $current_date . " " . $time;
    $postdata->set('datetime', $time);
    $postdata->del('current_date');
    $postdata->set('add_by_admin', 1);
}

function makeClockingTimeUpdate_overnight($postdata, $primary, $xcrud){
    $employee_id = $postdata->get('employee_id');
    $db = Xcrud_db::get_instance();
    $query = 'SELECT shift_id FROM shift_days where FIND_IN_SET('.$employee_id.', employees) > 0 and date = "'.$postdata->get('current_date').'"';
    $db->query($query);
    $result = $db->row();
    if($result){
        $postdata->set('shift_id', $result["shift_id"]);
    }else{
        $postdata->set('shift_id', 0);
    }

    $current_date = $postdata->get('current_date');
    $time = $postdata->get('datetime');

    $db->query("SELECT * FROM clockings_news WHERE id = '$primary'");
    $old_clocking = $db->row();

    $old_datetime = explode(" ", $old_clocking["datetime"]);

    $old_time = $old_datetime[1];
    
    $log_data = [
        'action' => 'Edited,Clocking',
        'target_id' => $employee_id,
        'from_time' => $old_time,
        'to_time' => $time,
        'for_date' => $current_date,
        'clocking_type' => $postdata->get('type'),
    ];
    insert_log('Clockings', $log_data);

    $hour = explode(":", $time)[0];
    $minute = explode(":", $time)[1];
    if ($hour < 7 || ($hour == 7 && $minute == 0) ) {
        $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
    }
    if ($old_time != $time) {
        $postdata->set('update_by_admin', 1);
    }
    $time = $current_date." ".$time;
    $postdata->set('datetime', $time);
    $postdata->del('current_date');
}

function generate_qr_barcode_update($postdata, $primary, $xcrud){

    if(empty($postdata->get('qr_barcode'))){
        $postdata->set('qr_barcode', random_string(8));
    }
    
}

function check_shift_overlap($postdata, $xcrud){
    

    $xcrud->set_exception('employees','Your password is too simple.');

}

// function random_string(int $size): string
// {
//     $characters = array_merge(
//         range(0, 9),
//         range('A', 'Z')
//     );

//     $string = '';
//     $max = count($characters) - 1;
//     for ($i = 0; $i < $size; $i++) {
//         $string .= $characters[random_int(0, $max)];
//     }

//     return $string;
// }

function after_shift_insertion($postdata, $primary_key) {
    $ci = &get_instance();
    $branch = $ci->db->get_where('branches', ['id' => $postdata->get('branch_id')])->row();

    $data = [
        'action' => "Added,Shift",
        'target_id' => $primary_key,
        'target_name' => $postdata->get('name'),
        'to_outlet' => "All",
    ];

    if ($branch) {
        $data['to_outlet'] = $branch->name;
        $data['to_branch_id'] = $branch->id;
    }

    insert_log("Shifts", $data);
}

function before_shift_updation($postdata, $primary_key)
{
    $ci = &get_instance();
    $shift = $ci->db->get_where('shifts', ['id' => $primary_key])->row();
    $branch = $ci->db->get_where('branches', ['id' => $shift->branch_id])->row();


    $data = [
        'action' => "Edited,Shift",
        'target_id' => $primary_key,
        'target_name' => $postdata->get('name'),
        'from_target_name' => $shift->name,
        'from_outlet' => "All",
        'from_branch_id' => null,
        "to_outlet" => "All",
        "to_branch_id" => null,
    ];

    if ($branch) {
        $data['from_outlet'] = $branch->name;
        $data['from_branch_id'] = $branch->id;
    }

    $new_branch = $ci->db->get_where('branches', ['id' => $postdata->get('branch_id')])->row();

    if ($new_branch) {
        $data['to_outlet'] = $new_branch->name;
        $data['to_branch_id'] = $new_branch->id;
    }

    insert_log("Shifts", $data);
}

function before_shift_deletion($primary_key) {
    $ci = &get_instance();
    $shift = $ci->db->get_where('shifts', ['id' => $primary_key])->row();
    $branch = $ci->db->get_where('branches', ['id' => $shift->branch_id])->row();

    $data = [
        "action" => "Deleted,Shift",
        "target_name" => $shift->name,
        "to_outlet" => "All",
    ];

    if ($branch) {
        $data["to_outlet"] = $branch->name;
        $data["to_branch_id"] = $branch->id;
    }

    insert_log("Shifts", $data);
}

function after_leave_insertion($postdata, $primary_key)
{
    $log_data = [
        'action' => 'Added,Leave',
        'target_name' => $postdata->get('name'),
        'target_id' => $primary_key,
        'is_paid' => ucfirst($postdata->get('is_paid')),
        'is_half' => ucfirst($postdata->get('half_day'))
    ];
    insert_log('Leaves', $log_data);
}

function after_leave_updation($postdata, $primary_key)
{
    $log_data = [
        'action' => 'Edited,Leave',
        'target_name' => $postdata->get('name'),
        'target_id' => $primary_key,
        'is_paid' => ucfirst($postdata->get('is_paid')),
        'is_half' => ucfirst($postdata->get('half_day')),
    ];
    insert_log('Leaves', $log_data);
}

function before_leave_deletion($primary_key)
{
    $CI = &get_instance();
    $shift = $CI->db->get_where('shifts', ['id' => $primary_key])->row();

    $log_data = [
        'action' => 'Deleted,Leave',
        'target_name' => $shift->name,
        'is_paid' => ucfirst($shift->is_paid),
        'is_half' => ucfirst($shift->half_day),
    ];
    insert_log('Leaves', $log_data);
}

function after_day_settings_insert($postdata, $primary_key)
{
    $log_data = [
        'action' => 'Added,Day Setting',
        'target_id' => $primary_key,
        'from_hour' => $postdata->get('from_hour'),
        'to_hour' => $postdata->get('to_hour'),
        'days' => $postdata->get('days'),
    ];
    insert_log('Day Settings', $log_data);
}

function after_day_settings_update($postdata, $primary_key)
{
    $log_data = [
        'action' => 'Edited,Day Setting',
        'target_id' => $primary_key,
        'from_hour' => $postdata->get('from_hour'),
        'to_hour' => $postdata->get('to_hour'),
        'days' => $postdata->get('days'),
    ];
    insert_log('Day Settings', $log_data);
}

function before_day_settings_deletion($primary_key)
{
    $ci = &get_instance();
    $setting = $ci->db->get_where('days_settings', ['id' => $primary_key])->row();

    $log_data = [
        'action' => 'Deleted,Day Setting',
        'from_hour' => $setting->from_hour,
        'to_hour' => $setting->to_hour,
        'days' => $setting->days,
    ];
    insert_log('Day Settings', $log_data);
}

function after_role_insertion($postdata, $primary_key) {
    $log_data = [
        'action' => 'Added,Role',
        'target_id' => $primary_key,
        'target_name' => $postdata->get('job_name'),
        'role_permissions_level' => $postdata->get('permissions_level'),
    ];
    insert_log('Roles', $log_data);
}

function after_role_updation($postdata, $primary_key)
{
    $log_data = ['action' => 'Edited,Role', 'target_id' => $primary_key, 'target_name' => $postdata->get('job_name'), 'role_permissions_level' => $postdata->get('permissions_level')];
    insert_log('Roles', $log_data);
}

function before_role_deletion($primary_key)
{
    $ci = &get_instance();
    $role = $ci->db->get_where('roles', ['id' => $primary_key])->row();
    $job_name = $role->job_name;
    $permissions_level = $role->permissions_level;

    $log_data = ['action' => 'Deleted,Role', 'target_name' => $job_name, 'role_permissions_level' => $permissions_level];
    insert_log('Roles', $log_data);
}

function after_device_insertion($postdata, $primary_key)
{
    $log_data = [
        'action' => 'Added,Device',
        'target_id' => $primary_key,
        'target_name' => $postdata->get('mac_address'),
        'location' => $postdata->get('location'),
    ];
    insert_log('Devices', $log_data);
}

function after_device_updation($postdata, $primary_key)
{
    $log_data = [
        'action' => 'Edited,Device',
        'target_id' => $primary_key,
        'target_name' => $postdata->get('mac_address'),
        'location' => $postdata->get('location'),
    ];
    insert_log('Devices', $log_data);
}

function before_device_deletion($primary_key)
{
    $ci = &get_instance();
    $device = $ci->db->get_where('devices', ['device_id' => $primary_key])->row();
    $log_data = [
        'action' => 'Deleted,Device',
        'target_id' => $primary_key,
        'target_name' => $device->mac_address,
        'location' => $device->location,
    ];
    insert_log('Devices', $log_data);
}

function after_termination_reason_insertion($postdata, $primary_key)
{
    $log_data = [
        'action' => 'Added,Termination Reason',
        'target_name' => $postdata->get('reason'),
        'target_id' => $primary_key,
    ];
    insert_log('Termination Reasons', $log_data);
}

function after_termination_reason_updation($postdata, $primary_key)
{
    $log_data = [
        'action' => 'Edited,Termination Reason',
        'target_name' => $postdata->get('reason'),
        'target_id' => $primary_key,
    ];
    insert_log('Termination Reasons', $log_data);
}

function before_termination_reason_deletion($primary_key)
{
    $ci = &get_instance();
    $reason = $ci->db->get_where('termination_reasons', ['id' => $primary_key])->row();
    $log_data = [
        'action' => 'Deleted,Termination Reason',
        'target_name' => $reason->reason,
        'target_id' => $primary_key,
    ];
    insert_log('Termination Reasons', $log_data);
}

function replace_remove_clocking($primary_key, $xcrud)
{
    $ci = &get_instance();
    $clocking = $ci->db->get_where("clockings_news", ["id" => $primary_key])->row();

    $old_datetime = explode(" ", $clocking->datetime);
    $date = $old_datetime[0];
    $time = $old_datetime[1];

    $result = $ci->db->where("id", $primary_key)->update("clockings_news", ["deleted_at" => date("Y-m-d H:i:s"), "delete_by_admin" => 1]);

    $log_data = [
        'action' => 'Removed,Clocking',
        'target_id' => $clocking->employee_id,
        'from_time' => $time,
        'for_date' => $date,
        'clocking_type' => $clocking->type,
    ];
    insert_log('Clockings', $log_data);
}

function after_holiday_insertion($postdata, $primary_key)
{
    $ci = &get_instance();
    $branch_name = $ci->db->select('name')->from('branches')->where('id', $postdata->get('branch_id'))->get()->row()->name;
    $log_data = [
        'action' => 'Added,Holiday',
        'target_id' => $primary_key,
        'target_name' => $postdata->get('title'),
        'to_branch_id' => $postdata->get('branch_id'),
        'to_outlet' => $branch_name,
        'for_date' => $postdata->get('holiday_date'),
    ];
    insert_log('Holidays', $log_data);
}

function after_holiday_updation($postdata, $primary_key)
{
    $ci = &get_instance();
    $branch_name = $ci->db->select('name')->from('branches')->where('id', $postdata->get('branch_id'))->get()->row()->name;

    $log_data = [
        'action' => 'Edited,Holiday',
        'target_id' => $primary_key,
        'target_name' => $postdata->get('title'),
        'to_branch_id' => $postdata->get('branch_id'),
        'to_outlet' => $branch_name,
        'for_date' => $postdata->get('holiday_date'),
    ];
    insert_log('Holidays', $log_data);
}

function before_holiday_removal($primary_key)
{
    $ci = &get_instance();
    $public_holiday = $ci->db->select('ph.branch_id id, b.name, ph.holiday_date, ph.title')->from('public_holidays ph')->join('branches b', 'b.id = ph.branch_id')->where('ph.id', $primary_key)->get()->row();

    $log_data = [
        'action' => 'Deleted,Holiday',
        'target_id' => $primary_key,
        'target_name' => $public_holiday->title,
        'from_branch_id' => $public_holiday->id,
        'from_outlet' => $public_holiday->name,
        'for_date' => $public_holiday->holiday_date,
    ];
    insert_log('Holidays', $log_data);
}

function after_department_insertion($postdata, $primary_key)
{
    $log_data = [
        'action' => 'Added,Department',
        'target_id' => $primary_key,
        'target_name' => $postdata->get('name'),
        'location' => $postdata->get('location'),
    ];
    insert_log('Departments', $log_data);
}

function after_department_updation($postdata, $primary_key)
{
    $log_data = [
        'action' => 'Edited,Department',
        'target_id' => $primary_key,
        'target_name' => $postdata->get('name'),
        'location' => $postdata->get('location'),
    ];
    insert_log('Departments', $log_data);
}

function before_department_removal($primary_key)
{
    $ci = &get_instance();
    $department = $ci->db->select('name, location')->from('departments')->where('id', $primary_key)->get()->row();
    $log_data = [
        'action' => 'Deleted,Department',
        'target_id' => $primary_key,
        'target_name' => $department->name,
        'location' => $department->location,
    ];
    insert_log('Departments', $log_data);
}

function after_position_insertion($postdata, $primary_key)
{
    $log_data = [
        'action' => 'Added,Position',
        'target_id' => $primary_key,
        'target_name' => $postdata->get('title'),
    ];
    insert_log('Positions', $log_data);
}

function after_position_updation($postdata, $primary_key)
{
    $log_data = [
        'action' => 'Edited,Position',
        'target_id' => $primary_key,
        'target_name' => $postdata->get('title'),
    ];
    insert_log('Positions', $log_data);
}

function before_position_removal($primary_key)
{
    $ci = &get_instance();
    $position = $ci->db->select('title')->from('positions')->where('id', $primary_key)->get()->row();
    $log_data = [
        'action' => 'Deleted,Position',
        'target_id' => $primary_key,
        'target_name' => $position->title,
    ];
    insert_log('Positions', $log_data);
}

function after_work_hour_updation($postdata, $primary_key)
{
   $log_data = [
      'action' => 'Edited,Work Hours',
   ];
   insert_log('Simple', $log_data);
}

function after_company_details_updation()
{
   $log_data = [ 'action' => 'Edited,Company Details', ];
   insert_log('Simple', $log_data);
}

function after_outlet_updation()
{
   $log_data = [ 'action' => 'Edited,Outlet Details',];
   insert_log('Simple', $log_data);
}

function before_sql_payroll_settings_update($postdata, $primary_key)
{
    $ci = &get_instance();
    $branch = $ci->db->select('name')->from('branches')->where('id', $primary_key)->get()->row();

    $log_data = [
        'action' => 'Edited,SQL Payroll Settings',
        'target_id' => $primary_key,
        'to_branch_id' => $primary_key,
        'to_outlet' => $branch->name,
    ];
    insert_log('SQL Payroll Settings', $log_data);
}

function before_clocking_update($postdata, $primary_key)
{
    $postdata->set("update_by_admin", 1);
}

function before_clocking_insertion($postdata, $xcrud)
{
    $postdata->set("add_by_admin", 1);
}