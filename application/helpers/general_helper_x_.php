<?php

function antelope_config(){
  $ci=& get_instance();
  return $ci->config->item("antelope_config");
}

function get_public_holidays(){
  $ci=& get_instance();
  $cid = get_user()["company_id"];
  $result = $ci->db->select('holiday_date')->from('public_holidays')->where('company_id', $cid)->get()->result();
  $dates = array_map (function($value){
    return $value->holiday_date;
  } , $result);
  return $dates;
}

function get_public_holidays_with_name(){
  $ci=& get_instance();
  $cid = get_user()["company_id"];
  $result = $ci->db->select('holiday_date,title')->from('public_holidays')->where('company_id', $cid)->get()->result();
  $data = array();
  $data[] = array_map (function($value){
    return $value->holiday_date;
  } , $result);
  $data[] = array_map (function($value){
    return $value->title;
  } , $result);
  return $data;
}

function get_user(){
  $ci=& get_instance();
  $user = $ci->session->userdata('antelope_user');

  //var_dump($_SESSION);


  // $query = $ci->db->get_where('employees', array('id' => $user["id"]));
  // $user = $query->row_array();

  $query = $ci->db->query("SELECT employees.*, departments.id as department_id, departments.name as department_name, companies.name as company_name, companies.logo as company_logo, branches.logo_big as logo_big, branches.logo_small as logo_small, branches.name as branch_name, branches.weather_widget from employees 
  LEFT JOIN companies ON employees.company_id = companies.id 
  LEFT JOIN branches ON employees.branch_id = branches.id 
  LEFT JOIN departments ON employees.department_id = departments.id 
  WHERE employees.id = ?",array($user["id"]));

  $user = $query->row_array();

 

  if(isset($user)){

    // if($user["disabled"] == 'Yes'){
    //   redirect("user_management/logout");
    // }


    $permissions = $ci->db->query("SELECT permissions,permissions_level,limit_access_to_department from roles WHERE id = ?",array($user["role_id"]))->row();

    $user["permissions"] = $permissions->permissions;
    $user["permissions_level"] = $permissions->permissions_level;
    $user["limit_access_to_department"] = $permissions->limit_access_to_department;

    $user["photo"] = base_url() . "uploads/" . $user["photo"];

  }


  return $user;
}


function get_menus(){

  $permissions = explode(',',get_user()["permissions"]);


  $ci=& get_instance();
  $all_menus = $ci->config->item("antelope_config")["antelope_sidebar_menus"];

  if (in_array('everything', $permissions)) {

      return $all_menus;
  }

  foreach ($all_menus as $menukey => &$menu) {

    $menu_url_array = explode('/', $menu["url"]);
    $menu_url = end($menu_url_array);

    if(isset($menu["sub_menus"])){
      foreach ($menu["sub_menus"] as $submenukey => &$submenu) {

        $submenu_url_array = explode('/', $submenu["url"]);
        $submenu_url = end($submenu_url_array);

        if (!in_array($submenu_url, $permissions)) {
            unset($menu["sub_menus"][$submenukey]);
        }
      }
      if(count($menu["sub_menus"]) == 0){
          unset($all_menus[$menukey]);
      }

    }
    else{
        if (!in_array($menu_url, $permissions)) {
            unset($all_menus[$menukey]);
        }
    }
  }


  return $all_menus;

}

function get_company_employees(){

  $permissions = explode(',',get_user()["permissions"]);


  $ci=& get_instance();
  //$all_menus = $ci->config->item("antelope_config")["antelope_sidebar_menus"];

  $menus_to_return = array();

  

  $query = $ci->db->query("SELECT id,first_name,company_id, special_id FROM employees where company_id = ? ORDER BY first_name",array(get_user()["company_id"]));


  if(get_user()["company_id"] == 1){
    $query = $ci->db->query("SELECT id,first_name,company_id, special_id FROM employees ORDER BY first_name");
  }

  $employees = $query->result_array();

  //var_dump($employees);


  foreach ($employees as $key => &$emp) {

      $menus_to_return[$emp["id"]] = $emp["first_name"] . " (" . $emp["special_id"] . ")";

  }



  return $menus_to_return;
}

function get_menus_for_user_management(){

  $permissions = explode(',',get_user()["permissions"]);


  $ci=& get_instance();
  $all_menus = $ci->config->item("antelope_config")["antelope_sidebar_menus"];

  $menus_to_return = array();

  $menus_to_return["everything"] = "Everything (Admin)";

$menus_to_return["my_profile"] = "My Profile";


  foreach ($all_menus as $menukey => &$menu) {

    $menu_url_array = explode('/', $menu["url"]);
    $menu_url = end($menu_url_array);


    if(isset($menu["sub_menus"])){
      foreach ($menu["sub_menus"] as $submenukey => &$submenu) {

        $submenu_url_array = explode('/', $submenu["url"]);
        $submenu_url = end($submenu_url_array);
        $menus_to_return[$menu["title"]][$submenu_url] = $submenu["title"];

      }
    }
    else{
      $menus_to_return[$menu_url] = $menu["title"];

    }
  }


  return $menus_to_return;

}


function is_page_permitted($page){

  $permissions = explode(',',get_user()["permissions"]);

  if (in_array('everything', $permissions)) {
      return true;
  }
  else{
    if (in_array($page, $permissions)) {
        return true;
    }
  }

  return false;
}

function beautify_date($date){

  return date("d M, D", strtotime($date));
}

function beautify_time($time){
  return date("H:i", strtotime($time));
}

function beautify_time_am_pm($time){
  return date("h:i A", strtotime($time));
}

function random_string(int $size)
{
    $characters = array_merge(
        range(0, 9),
        range('A', 'Z')
    );

    $string = '';
    $max = count($characters) - 1;
    for ($i = 0; $i < $size; $i++) {
        $string .= $characters[random_int(0, $max)];
    }

    return $string;
}

function shift_calendar($month,$year,$dateArray) {
     // Create array containing abbreviations of days of week.
     $daysOfWeek = array('S','M','T','W','T','F','S');
     // What is the first day of the month in question?
     $firstDayOfMonth = mktime(0,0,0,$month,1,$year);
     // How many days does this month contain?
     $numberDays = date('t',$firstDayOfMonth);
     // Retrieve some information about the first day of the
     // month in question.
     $dateComponents = getdate($firstDayOfMonth);
     // What is the name of the month in question?
     $monthName = $dateComponents['month'];
     // What is the index value (0-6) of the first day of the
     // month in question.
     $dayOfWeek = $dateComponents['wday'];
     // Create the table tag opener and day headers
     $calendar = "<table class='calendar'>";
     $calendar .= "<caption>$monthName $year</caption>";
     $calendar .= "<tr>";
     // Create the calendar headers
     foreach($daysOfWeek as $day) {
          $calendar .= "<th class='header'>$day</th>";
     } 
     // Create the rest of the calendar
     // Initiate the day counter, starting with the 1st.
     $currentDay = 1;
     $calendar .= "</tr><tr>";
     // The variable $dayOfWeek is used to
     // ensure that the calendar
     // display consists of exactly 7 columns.
     if ($dayOfWeek > 0) { 
          $calendar .= "<td colspan='$dayOfWeek'>&nbsp;</td>"; 
     }
     
     $month = str_pad($month, 2, "0", STR_PAD_LEFT);
  
     while ($currentDay <= $numberDays) {
          // Seventh column (Saturday) reached. Start a new row.
          if ($dayOfWeek == 7) {
               $dayOfWeek = 0;
               $calendar .= "</tr><tr>";
          }
          
          $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);
          
          $date = "$year-$month-$currentDayRel";
          $calendar .= "<td class='day' rel='$date'>$currentDay</td>";
          // Increment counters
 
          $currentDay++;
          $dayOfWeek++;
     }
     
     
     // Complete the row of the last week in month, if necessary
     if ($dayOfWeek != 7) { 
     
          $remainingDays = 7 - $dayOfWeek;
          $calendar .= "<td colspan='$remainingDays'>&nbsp;</td>"; 
     }
     
     $calendar .= "</tr>";
     $calendar .= "</table>";
     return $calendar;
}

function render_shift_calendar_week($data,$date){

  
  
  $html = "";

  $html .= "<p><b>".sprintf('%02d',$date)."</b></p>";

  $html .= '<table style="font-size: 11px" class="table">
              <theah >
                  <tr>
                      <th style="font-size: 11px">Shift</th>
                      <th style="font-size: 11px">#</th>
                  </tr>
              </theah>
              <tbody>';

     foreach($data as $d){

         
            $html .= '<tr>
                <td>'.$d["name"].'</td>
                <td><b>'.$d["count"].'</b></td>
            </tr>';
              

      }

    $html .= '</tbody></table>';


  echo $html;
}

function render_clockings_query_for_employee_month($employee_id,$month,$year){

  return "(SELECT `a`.`id` AS `id`,
      `a`.`employee_id` AS `employee_id`,
      `a`.`device_id` AS `device_id`,
      `a`.`shift_id` AS `shift_id`,
      `a`.`datetime` AS `clock_in`,
      `b`.`datetime` AS `clock_out`,
      NULL AS `auto_clock_out`,
      a.reason AS `reason`,
      `a`.`remark` AS `remark`,
      `a`.`mode` AS `scan_type_in`,
      `b`.`mode` AS `scan_type_out`,
      NULL AS `weather`,
      `a`.`created_at` AS `created_at`,
      `b`.`created_at` AS `updated_at`,
      NULL AS `deleted_at`
    FROM (`clockings_news` `a`
    LEFT JOIN `clockings_news` `b` on(((`a`.`employee_id` = `b`.`employee_id`)
                                                          AND (`a`.`type` = 'in')
                                                          AND (`b`.`type` = 'out')
                                                          AND (`b`.`datetime` =
                                                                  (SELECT min(`c`.`datetime`)
                                                                  FROM `clockings_news` `c`
                                                                  WHERE ((`c`.`datetime` > `a`.`datetime`)
                                                                          AND (`c`.`employee_id` = `a`.`employee_id`)
                                                                          AND isnull(`c`.`deleted_at`)))))))
    WHERE (((`a`.`id` <> `b`.`id`)
      OR isnull(`b`.`id`))
      AND isnull(`a`.`deleted_at`)
      AND a.employee_id = $employee_id
      AND MONTH(a.datetime) = $month
      AND YEAR(a.datetime) = $year
      AND isnull(`b`.`deleted_at`)
      AND (`a`.`type` = 'in')
      AND ((`b`.`type` = 'out')
          OR isnull(`b`.`type`)))
    ORDER BY `a`.`datetime`)";

  //return "clockings";
}


function render_all_filters(&$data){

  $CI =& get_instance();


  $data["selected_branch_id"] = 0;
  $data["selected_dep_id"] = 0;
  $data["selected_month"] = 0;
  $data["selected_year"] = 0;
  $data["selected_emp_id"] = 0;

  $data["where_filter"] = "";
  $data["where_department_dropdown"] = "";
  $data["where_clock_date"] = "";
  $data["where_date"] = "";


        $cid = get_user()["company_id"];
        $bid = get_user()["branch_id"];
        
        $permissions_level = get_user()["permissions_level"];
        $limit_access_to_department = get_user()["limit_access_to_department"];
        $department_id = get_user()["department_id"];

        $dids =  $department_id . "," . get_user()["departments_access"];

        $dids = trim($dids, ",");

        $dids_array = explode(',',$dids);


        $data["where_branch_2"] = '';
        $data["where_department"] = '';
        $data["branch_where_filter"] = "";
       
        //echo $dids;die();
        
        if($permissions_level == "Outlet"){
            
            $data["where_branch_2"] = " AND id = $bid ";

            if($limit_access_to_department == "yes"){

              //echo "aa"; die();
               
                $data["where_department"] = " AND id IN ($dids) ";

                if($CI->input->get("branch") != $bid || !in_array($CI->input->get("dep"), $dids_array)){
                    redirect($data["filters_form_action"]."?dep=$department_id&branch=$bid&month=".date('m')."&year=".date('Y'));
                    return;
                }
            }
            else{
                if($CI->input->get("branch") != $bid){
                    redirect($data["filters_form_action"]."?branch=$bid&month=".date('m')."&year=".date('Y'));
                    return;
                }
            }            
        }
        else{

            if($limit_access_to_department == "yes"){
                $data["where_department"] = " AND id IN ($dids) ";
                if(!in_array($CI->input->get("dep"), $dids_array)){
                    redirect($data["filters_form_action"]."?dep=$department_id&month=".date('m')."&year=".date('Y'));
                    return;
                }
            }

        }

        
        if(!empty($CI->input->get("branch"))){
            $data["selected_branch_id"] = $CI->input->get("branch");
            $data["where_filter"] = $data["where_filter"] . " branch_id = " . $CI->input->get("branch") . " AND " ;
            $data["branch_where_filter"] = $data["branch_where_filter"] . " AND employees.branch_id = " . $CI->input->get("branch");

        }

        if(!empty($CI->input->get("dep"))){
            $data["selected_dep_id"] = $CI->input->get("dep");
            $data["where_filter"] = $data["where_filter"] . " department_id = " . $CI->input->get("dep") . " AND " ;

            $data["where_department_dropdown"] = " AND department_id = " .  $CI->input->get("dep");

        }

        if(!empty($CI->input->get("emp"))){
            $data["selected_emp_id"] = $CI->input->get("emp");
            $data["where_filter"] = $data["where_filter"] . " employees.id = " . $CI->input->get("emp") . " AND " ;
        }

        if(!empty($CI->input->get("month")) && !empty($CI->input->get("year"))){

            $data["selected_month"] = $CI->input->get("month");
            $data["selected_year"] = $CI->input->get("year");
            $data["where_clock_date"] = " AND MONTH(clock_in) = " . $CI->input->get("month") . " AND YEAR(clock_in) = " . $CI->input->get("year");
            $data["where_date"] = " AND MONTH(date) = " . $CI->input->get("month") . " AND YEAR(date) = " . $CI->input->get("year");
        }
        else{

            redirect($data["filters_form_action"]."?branch=".$CI->input->get("branch")."&month=".date('m')."&year=".date('Y'));
            return;
        }

        $data["where_filter"] = $data["where_filter"] . " employees.company_id = " . $cid;

        $data["where_filter"] = trim($data["where_filter"]);
        $data["where_filter"] = trim($data["where_filter"],"AND");

        $data["employees_dropdown"] = $CI->db->query("SELECT employees.id, special_id,first_name,last_name FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND roles.exclude_from_system = 'no' AND employees.company_id = $cid ". $data["branch_where_filter"] . "  ". $data["where_department_dropdown"] . " ORDER BY special_id")->result();

        $data["branches"] = $CI->db->query("SELECT * FROM branches WHERE company_id = $cid  ". $data["where_branch_2"] . " ORDER BY name")->result();

        $data["departments"] = $CI->db->query("SELECT * FROM departments WHERE company_id = $cid ". $data["where_department"] . " ORDER BY name")->result();


}

function render_att_report_filters(&$data){

  $CI =& get_instance();


  $data["selected_branch_id"] = 0;
  $data["selected_dep_id"] = 0;
  $data["selected_month"] = 0;
  $data["selected_emp_id"] = 0;

  $data["where_filter"] = "";
  $data["where_department_dropdown"] = "";
  $data["where_clock_date"] = "";
  $data["where_date"] = "";


        $cid = get_user()["company_id"];
        $bid = get_user()["branch_id"];
        
        $permissions_level = get_user()["permissions_level"];
        $limit_access_to_department = get_user()["limit_access_to_department"];
        $department_id = get_user()["department_id"];

        $dids =  $department_id . "," . get_user()["departments_access"];

        $dids = trim($dids, ",");

        $dids_array = explode(',',$dids);


        $data["where_branch_2"] = '';
        $data["where_department"] = '';
        $data["branch_where_filter"] = "";
       
        //echo $dids;die();
        
        if($permissions_level == "Outlet"){
            
            $data["where_branch_2"] = " AND id = $bid ";

            //if($limit_access_to_department == "yes"){

              //echo "aa"; die();
               
                $data["where_department"] = " AND id IN ($dids) ";

                if($CI->input->get("branch") != $bid || !in_array($CI->input->get("dep"), $dids_array)){
                    redirect($data["filters_form_action"]."?branch=$bid&status=late");
                    return;
                }
            // }
            // else{
            //     if($CI->input->get("branch") != $bid){
            //         redirect($data["filters_form_action"]."?branch=$bid&month=".date('m'));
            //         return;
            //     }
            // }            
        }
        else{

            // if($limit_access_to_department == "yes"){
            //     $data["where_department"] = " AND id IN ($dids) ";
            //     if(!in_array($CI->input->get("dep"), $dids_array)){
            //         redirect($data["filters_form_action"]."?dep=$department_id&month=".date('m'));
            //         return;
            //     }
            // }

        }

        
        if(!empty($CI->input->get("branch"))){
            $data["selected_branch_id"] = $CI->input->get("branch");
            $data["where_filter"] = $data["where_filter"] . " branch_id = " . $CI->input->get("branch") . " AND " ;
            $data["branch_where_filter"] = $data["branch_where_filter"] . " AND employees.branch_id = " . $CI->input->get("branch");

        }

        // if(!empty($CI->input->get("dep"))){
        //     $data["selected_dep_id"] = $CI->input->get("dep");
        //     $data["where_filter"] = $data["where_filter"] . " department_id = " . $CI->input->get("dep") . " AND " ;

        //     $data["where_department_dropdown"] = " AND department_id = " .  $CI->input->get("dep");

        // }

        if(!empty($CI->input->get("emp"))){
            $data["selected_emp_id"] = $CI->input->get("emp");
            $data["where_filter"] = $data["where_filter"] . " employees.id = " . $CI->input->get("emp") . " AND " ;
        }

        if(!empty($CI->input->get("status"))){
            $data["selected_month"] = $CI->input->get("status");
        }
        else{
            redirect($data["filters_form_action"]."?status=late");
            return;
        }

        $data["where_filter"] = $data["where_filter"] . " employees.company_id = " . $cid;

        $data["where_filter"] = trim($data["where_filter"]);
        $data["where_filter"] = trim($data["where_filter"],"AND");

        $data["employees_dropdown"] = $CI->db->query("SELECT employees.id, special_id,first_name,last_name FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND roles.exclude_from_system = 'no' AND employees.company_id = $cid ". $data["branch_where_filter"] . "  ". $data["where_department_dropdown"] . " ORDER BY special_id")->result();

        $data["branches"] = $CI->db->query("SELECT * FROM branches WHERE company_id = $cid  ". $data["where_branch_2"] . " ORDER BY name")->result();



}

function gantt_chart_department_shift($is_branch = false, $branch_id = 0){


  $CI =& get_instance();

  $CI->benchmark->mark('code_start');


  $gantt_array = array();
  $departments = array();
  $gantt_array_final = array();

  $where_branch_3 = " ";

  $cid = get_user()["company_id"];


  if($is_branch){
    $where_branch_3 = " AND branch_id = $branch_id ";
  }

  // $gantt_array["department"]["shift1"]["count"] = 2;
  // $gantt_array["department"]["shift2"]["count"] = 5;
  // $gantt_array["department2"]["shift1"]["count"] = 8;


  $all_departments = $CI->db->query("SELECT DISTINCT(name) as name FROM departments WHERE company_id = $cid ORDER BY name")->result_array();
  foreach ($all_departments as $key => $dep) {   

    $new_dep = array();
    $new_dep["index"] = $key;
    $new_dep["dep"] = $dep["name"];

    $departments[] = $dep["name"];


    $arr_temp = array();
    $arr_temp["name"] = $dep["name"];
    $arr_temp["id"] = (str_replace(' ', '_', strtolower($dep["name"])));
    $arr_temp["count"] = 0;
    $arr_temp["collapsed"] = true;
    $arr_temp["code"] = "";//$shift_d["code"];
    $arr_temp["color"] = "black";//$shift_d["color"];
    $arr_temp["start"] = "";//strtotime(date("Y-m-d") . " " . $shift_d["start_time"]) * 1000; //mktime(8,30,0,11,17,2019);
    $arr_temp["end"] =  "";//strtotime(date("Y-m-d") . " " . $shift_d["end_time"]) * 1000; //mktime(10,30,0,11,17,2019);;
    $gantt_array[$dep["name"]] = $arr_temp;
  }

  $all_employees = $CI->db->query("SELECT employees.id as emp_id,departments.id as dep_id, departments.name as dep_name FROM employees INNER JOIN departments ON departments.id = employees.department_id WHERE employees.company_id = $cid $where_branch_3 ")->result_array();

  //header('Content-Type: application/json');

  //die(json_encode($gantt_array));
  
  $all_shifts_today = $CI->db->query("SELECT shifts.start_time, shifts.end_time, shifts.code, shifts.name, shifts.color, shift_days.date, shift_days.employees FROM shifts INNER JOIN shift_days ON shifts.id = shift_days.shift_id where company_id = $cid $where_branch_3 AND shifts.is_leave = 'no' AND shift_days.employees <> '' AND shift_days.date = CURRENT_DATE GROUP BY shift_id")->result_array();
 
  foreach ($all_employees as $emp) {
    foreach ($all_shifts_today as $shift_d) {

        
        $arr_temp = array();
        $arr_temp["name"] = $shift_d["name"];
        $arr_temp["id"] = (str_replace(' ', '_', strtolower($emp["dep_name"].$shift_d["name"])));
        $arr_temp["parent"] = (str_replace(' ', '_', strtolower($emp["dep_name"])));
        $arr_temp["count"] = 0;
        $arr_temp["collapsed"] = false;
        $arr_temp["code"] = $shift_d["code"];
        $arr_temp["color"] = $shift_d["color"];
        $arr_temp["start"] = strtotime(date("Y-m-d") . " " . $shift_d["start_time"]) * 1000; //mktime(8,30,0,11,17,2019);
        $arr_temp["end"] =  strtotime(date("Y-m-d") . " " . $shift_d["end_time"]) * 1000; //mktime(10,30,0,11,17,2019);;
        $gantt_array[$emp["dep_name"] . "_" . $shift_d["name"]] = $arr_temp;


       


    }
  }

  //die();

  // start: Date.UTC(2019, 11, 17, 08, 00),
  // end: Date.UTC(2019, 11, 17, 12, 00),
  // y: 0,
  // shift: 'abc',
  // count: 22
  // unique: dep_shift

   foreach ($all_employees as $emp) {
    foreach ($all_shifts_today as $shift_d) {
      $emps = explode(",",$shift_d["employees"]);

      //die();
      // if($emp["emp_id"] == "637" && $shift_d["employees"] == "637"){
      //   var_dump($emp);
      //   var_dump($shift_d);
      //   die("here");
      // }
      
      if (in_array($emp["emp_id"], $emps)){
        $gantt_array[$emp["dep_name"] . "_" . $shift_d["name"]]["count"]++;
        $gantt_array[$emp["dep_name"]]["count"]++;

          if(empty($gantt_array[$emp["dep_name"]]["start"])){
            $gantt_array[$emp["dep_name"]]["start"] = strtotime(date("Y-m-d") . " " . $shift_d["start_time"]) * 1000;
          }

          if(empty($gantt_array[$emp["dep_name"]]["end"])){
            $gantt_array[$emp["dep_name"]]["end"] = strtotime(date("Y-m-d") . " " . $shift_d["end_time"]) * 1000;
          }

          if( (strtotime(date("Y-m-d") . " " . $shift_d["start_time"]) * 1000) < $gantt_array[$emp["dep_name"]]["start"]){
            $gantt_array[$emp["dep_name"]]["start"] = strtotime(date("Y-m-d") . " " . $shift_d["start_time"]) * 1000;
          }

          
          if( (strtotime(date("Y-m-d") . " " . $shift_d["end_time"]) * 1000) > $gantt_array[$emp["dep_name"]]["end"]){
            $gantt_array[$emp["dep_name"]]["end"] = strtotime(date("Y-m-d") . " " . $shift_d["end_time"]) * 1000;
          }


      }  
    }
  }

  foreach ($gantt_array as $key => $ga) {

    //var_dump($key);
    //var_dump($ga);
    //die();
    //die("$key - $ga");
    if($ga["count"] > 0){
      $gantt_array_final[] = $ga;
    }
  }

  //var_dump($departments);
  //var_dump($gantt_array_final);
  //var_dump($all_shifts_today);

  $response = array();
  //$response["categories"] = $departments;
  $response["data"] = $gantt_array_final;

  return json_encode($response);

  // header('Content-Type: application/json');
  // echo json_encode($gantt_array);
  // die();

  //$CI->benchmark->mark('code_end');

  //echo $CI->benchmark->elapsed_time('code_start', 'code_end');
  //die();


}

function update_shifts($device_id){

  //Shift 18 on 31sth for emp 1030 - comment added by Naveed

  var_dump('update_shifts function calling for device ' . $device_id);
  $CI =& get_instance();

  $result1 = $CI->db->query("SELECT * FROM clockings_news WHERE device_id = $device_id AND shift_id = 0 ")->result();

  //var_dump($result1);

  foreach ($result1 as $row1)
  {
    $d = date( 'Y-m-d', strtotime($row1->datetime));
    $employee_id = $row1->employee_id; 
    //var_dump($row1);
    $shift_day = $CI->db->query("SELECT * FROM shift_days WHERE DATE(date) = '$d' AND FIND_IN_SET($employee_id,employees)")->row();

    var_dump("date " . $d);
    var_dump("employee id " . $employee_id);

    if($shift_day){

      $shift_id = $shift_day->shift_id;
      var_dump("shift_id " . $shift_id);
      $update_shift = $CI->db->query("UPDATE clockings_news SET shift_id = $shift_id WHERE DATE(datetime) = '$d' AND employee_id = $employee_id");

    }

   
    var_dump("------------------");

  }

  //echo "done";

}


