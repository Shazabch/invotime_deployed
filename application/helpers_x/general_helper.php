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

function random_string(int $size): string
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

function render_clockings_query_for_employee_month($employee_id,$month){

  return "(SELECT `a`.`id` AS `id`,
      `a`.`employee_id` AS `employee_id`,
      `a`.`device_id` AS `device_id`,
      `a`.`shift_id` AS `shift_id`,
      `a`.`datetime` AS `clock_in`,
      `b`.`datetime` AS `clock_out`,
      NULL AS `auto_clock_out`,
      NULL AS `reason`,
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
      AND isnull(`b`.`deleted_at`)
      AND (`a`.`type` = 'in')
      AND ((`b`.`type` = 'out')
          OR isnull(`b`.`type`)))
    ORDER BY `a`.`datetime`)";

  //return "clockings";
}