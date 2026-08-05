<?php

function antelope_config()
{
  $ci = &get_instance();
  return $ci->config->item("antelope_config");
}

function get_public_holidays_all()
{
  $ci = &get_instance();
  $cid = get_user()["company_id"];
  $result = $ci->db->select('branch_id, holiday_date')->from('public_holidays')->where('company_id', $cid)->get()->result();
  return $result;
}

function get_public_holidays($branch_id = false)
{
  $ci = &get_instance();
  if ($ci->session->userdata("payroll_user")) {
    $cid = $ci->session->userdata("payroll_user")["company_id"];
  } else {
    $cid = get_user()["company_id"];
  }
  $ci->db->select('holiday_date')->from('public_holidays')->where('company_id', $cid);

  if ($branch_id) {
    $ci->db->where('(branch_id = ' . $ci->db->escape($branch_id) . ' or branch_id = 0)');
  }

  $result = $ci->db->get()->result();
  $dates = array_map(function ($value) {
    return $value->holiday_date;
  }, $result);
  return $dates;
}

function get_public_holidays_mine($emp_id, $branch_id = false, $first_day, $last_day)
{
  $ci = &get_instance();
  if ($ci->session->userdata("payroll_user")) {
    $cid = $ci->session->userdata("payroll_user")["company_id"];
  } else {
    $cid = get_user()["company_id"];
  }
  $ci->db->select('holiday_date,include_groups,exclude_groups')->from('public_holidays')->where('company_id', $cid)->where('holiday_date >=', $first_day)->where('holiday_date <=', $last_day);

  if ($branch_id) {
    $ci->db->where('(branch_id = ' . $ci->db->escape($branch_id) . ' or branch_id = 0)');
  }

  $result = $ci->db->get()->result();

  // print_r($result);die;
  $date = array();
  foreach ($result as $value) {
    // print_r($value);die;
    if (empty($value->include_groups) && empty($value->exclude_groups)) {
      $date[] = $value->holiday_date;
    } else {
      if (!empty($value->include_groups)) {
        $IG = $value->include_groups;
        $group_ids = explode(',', $IG);
        foreach ($group_ids as $group_id) {
          $ci->db->where('group_id', $group_id);
          $ci->db->where('employee_id', $emp_id);
          $resultss = $ci->db->get('employee_groups_relation')->result();
          if (!empty($resultss)) {
            foreach ($resultss as $results) {
              if ($results->employee_id == $emp_id) {
                $date[] = $value->holiday_date;
                break 2;
              } else {
              }
            }
          } else {
          }
        }
      } else {
        $IG = $value->exclude_groups;
        $group_ids = explode(',', $IG);
        foreach ($group_ids as $group_id) {
          $ci->db->where('group_id', $group_id);
          $ci->db->where('employee_id', $emp_id);
          $resultss = $ci->db->get('employee_groups_relation')->result();
          if (!empty($resultss)) {
            foreach ($resultss as $results) {
              if ($results->employee_id != $emp_id) {
                $date[] = $value->holiday_date;
                // break 2;
              } else {
              }
            }
          } else {
            $date[] = $value->holiday_date;
          }
        }
      }
    }
  }
  return $date;
}

function get_public_holidays_by_branch($public_holidays, $branch_id)
{
  $dates = array();
  foreach ($public_holidays as $p) {
    if ($p->branch_id == $branch_id) {
      $dates[] = $p->holiday_date;
    }
  }

  return $dates;
}

function get_public_holidays_with_name($branch_id = false, $first_day = false, $last_day = false)
{
  $ci = &get_instance();
  $cid = get_user()["company_id"];
  $ci->db->select('holiday_date,title')->from('public_holidays')->where('company_id', $cid);

  if ($first_day && $last_day) {
    $ci->db->where('holiday_date >=', $first_day)->where('holiday_date <=', $last_day);
  }
  if ($branch_id) {
    $ci->db->where('(branch_id = ' . $ci->db->escape($branch_id) . ' or branch_id = 0)');
  }

  $result = $ci->db->get()->result();
  $data = array();
  $data[] = array_map(function ($value) {
    return $value->holiday_date;
  }, $result);
  $data[] = array_map(function ($value) {
    return $value->title;
  }, $result);
  return $data;
}

function get_public_holidays_for_default_shift()
{
  $ci = &get_instance();
  $cid = get_user()["company_id"];
  return $ci->db->select('distinct(holiday_date),title')->from('public_holidays')->where('company_id', $cid)->get()->result();
}

function get_user()
{
  $ci = &get_instance();

  if ($ci->session->userdata("payroll_user")) {
    return $ci->session->userdata("payroll_user");
  }

  $user = $ci->session->userdata('antelope_user');

  //var_dump($_SESSION);


  // $query = $ci->db->get_where('employees', array('id' => $user["id"]));
  // $user = $query->row_array();

  $query = $ci->db->query("SELECT employees.*, departments.id as department_id, departments.name as department_name, companies.name as company_name, companies.logo as company_logo, branches.logo_big as logo_big, branches.logo_small as logo_small, branches.name as branch_name, branches.weather_widget, merit_system_sign, merit_system_position_text, is_merit_approved, companies.start_day from employees 
    LEFT JOIN companies ON employees.company_id = companies.id 
    LEFT JOIN branches ON employees.branch_id = branches.id 
    LEFT JOIN departments ON employees.department_id = departments.id 
    WHERE employees.id = ?", array($user["id"]));

  $user = $query->row_array();



  if (isset($user)) {

    // if($user["disabled"] == 'Yes'){
    //   redirect("user_management/logout");
    // }


    $permissions = $ci->db->query("SELECT permissions,permissions_level,limit_access_to_department, is_emp_summary_editable from roles WHERE id = ?", array($user["role_id"]))->row();

    $user["permissions"] = $permissions->permissions;
    $user["permissions_level"] = $permissions->permissions_level;
    $user["limit_access_to_department"] = $permissions->limit_access_to_department;
    $user["is_emp_summary_editable"] = $permissions->is_emp_summary_editable;

    $user["photo"] = base_url() . "uploads/" . $user["photo"];
  }


  return $user;
}


function get_menus()
{
  $user = get_user();
  $permissions = explode(',', $user["permissions"]);

  $companies_allowed_for_merit = companies_allowed_for_merit();
  $companies_allowed_for_monthly_ot = companies_allowed_for_monthly_ot();

  $ci = &get_instance();
  $all_menus = $ci->config->item("antelope_config")["antelope_sidebar_menus"];
  if (!in_array($user["company_id"], $companies_allowed_for_merit)) {
    $titles_array = array_column($all_menus, 'title');
    $index = array_search('Merit System', $titles_array);
    if ($index !== false) unset($all_menus[$index]);
  }

  foreach ($all_menus as $menukey => &$menu) {

    $menu_url_array = explode('/', $menu["url"]);
    $menu_url = end($menu_url_array);

    if (isset($menu["sub_menus"])) {
      if ($menu["title"] === "Settings") {
        if (!in_array($user["company_id"], $companies_allowed_for_merit)) {
          $titles_array = array_column($menu["sub_menus"], 'title');
          $index = array_search('Merit Deduction Settings', $titles_array);
          if ($index !== false) unset($menu["sub_menus"][$index]);
        }
      }
      if ($menu["title"] === "Attendance") {
        if (!in_array($user["company_id"], $companies_allowed_for_monthly_ot)) {
          $titles_array = array_column($menu["sub_menus"], 'title');
          $index = array_search('Monthly OT', $titles_array);
          if ($index !== false) unset($menu["sub_menus"][$index]);
        }
      }
      foreach ($menu["sub_menus"] as $submenukey => &$submenu) {

        $submenu_url_array = explode('/', $submenu["url"]);
        $submenu_url = end($submenu_url_array);

        if ((!in_array($submenu_url, $permissions) && !in_array('everything', $permissions)) || 
          (($submenu_url == "Bmi_report" || $submenu["url"] == "bmi_summary/bmi_view" || $submenu["url"] == "bmi_summary/allowances") && $user["company_id"] != 66) ||
          (($submenu["url"] == "dashboard/table/allowances_settings" || $submenu["url"] == "allowances/assignment") && !in_array($user["company_id"], companies_allowed_for_allowance_report()))
        ) {
          unset($menu["sub_menus"][$submenukey]);
        }
      }
      if (count($menu["sub_menus"]) == 0) {
        unset($all_menus[$menukey]);
      }
    } else {
      if (!in_array($menu_url, $permissions) && !in_array('everything', $permissions)) {
        unset($all_menus[$menukey]);
      }
    }
  }


  return $all_menus;
}

function get_menus_payroll()
{
  $ci = &get_instance();
  $permissions = explode(',', $ci->session->userdata("payroll_user")["payroll_permissions"]);



  $all_menus = $ci->config->item("payroll_config")["antelope_sidebar_menus"];

  if (in_array('everything', $permissions)) {

    return $all_menus;
  }

  foreach ($all_menus as $menukey => &$menu) {

    $menu_url_array = explode('/', $menu["url"]);
    $menu_url = end($menu_url_array);

    if (isset($menu["sub_menus"])) {
      foreach ($menu["sub_menus"] as $submenukey => &$submenu) {

        $submenu_url_array = explode('/', $submenu["url"]);
        $submenu_url = end($submenu_url_array);

        if (!in_array($submenu_url, $permissions)) {
          unset($menu["sub_menus"][$submenukey]);
        }
      }
      if (count($menu["sub_menus"]) == 0) {
        unset($all_menus[$menukey]);
      }
    } else {
      if (!in_array($menu_url, $permissions)) {
        unset($all_menus[$menukey]);
      }
    }
  }


  return $all_menus;
}

function get_company_employees()
{

  $permissions = explode(',', get_user()["permissions"]);


  $ci = &get_instance();
  //$all_menus = $ci->config->item("antelope_config")["antelope_sidebar_menus"];

  $menus_to_return = array();



  $query = $ci->db->query("SELECT id,first_name,company_id, special_id FROM employees where company_id = ? ORDER BY first_name", array(get_user()["company_id"]));


  if (get_user()["company_id"] == 1) {
    $query = $ci->db->query("SELECT id,first_name,company_id, special_id FROM employees ORDER BY first_name");
  }

  $employees = $query->result_array();

  //var_dump($employees);


  foreach ($employees as $key => &$emp) {

    $menus_to_return[$emp["id"]] = $emp["first_name"] . " (" . $emp["special_id"] . ")";
  }



  return $menus_to_return;
}

function get_menus_for_user_management()
{

  $permissions = explode(',', get_user()["permissions"]);


  $ci = &get_instance();
  $all_menus = $ci->config->item("antelope_config")["antelope_sidebar_menus"];

  $menus_to_return = array();

  $menus_to_return["everything"] = "Everything (Admin)";

  $menus_to_return["my_profile"] = "My Profile";


  foreach ($all_menus as $menukey => &$menu) {

    $menu_url_array = explode('/', $menu["url"]);
    $menu_url = end($menu_url_array);


    if (isset($menu["sub_menus"])) {
      foreach ($menu["sub_menus"] as $submenukey => &$submenu) {

        $submenu_url_array = explode('/', $submenu["url"]);
        $submenu_url = end($submenu_url_array);
        $menus_to_return[$menu["title"]][$submenu_url] = $submenu["title"];
      }
    } else {
      $menus_to_return[$menu_url] = $menu["title"];
    }
  }


  return $menus_to_return;
}

function get_menus_for_payroll()
{




  $ci = &get_instance();
  $permissions = explode(',', $ci->session->userdata("payroll_user")["permissions"]);
  $all_menus = $ci->config->item("payroll_config")["antelope_sidebar_menus"];

  $menus_to_return = array();

  $menus_to_return["everything"] = "Everything (Admin)";

  // $menus_to_return["my_profile"] = "My Profile";


  foreach ($all_menus as $menukey => &$menu) {

    $menu_url_array = explode('/', $menu["url"]);
    $menu_url = end($menu_url_array);


    if (isset($menu["sub_menus"])) {
      foreach ($menu["sub_menus"] as $submenukey => &$submenu) {

        $submenu_url_array = explode('/', $submenu["url"]);
        $submenu_url = end($submenu_url_array);
        $menus_to_return[$menu["title"]][$submenu_url] = $submenu["title"];
      }
    } else {
      $menus_to_return[$menu_url] = $menu["title"];
    }
  }


  return $menus_to_return;
}


function is_page_permitted($page)
{

  $permissions = explode(',', get_user()["permissions"]);

  if (in_array('everything', $permissions)) {
    return true;
  } else {
    if (in_array($page, $permissions)) {
      return true;
    }
  }

  return false;
}

function is_page_permitted_payroll($page)
{
  $ci = &get_instance();

  $permissions = explode(',', $ci->session->userdata("payroll_user")["payroll_permissions"]);

  if (in_array('everything', $permissions)) {
    return true;
  } else {
    if (in_array($page, $permissions)) {
      return true;
    }
  }

  return false;
}

function beautify_date($date)
{
  if ($date == "") {
    return "";
  }

  return date("d M, D", strtotime($date));
}

function beautify_time($time)
{
  if ($time == "") {
    return "";
  }
  return date("H:i", strtotime($time));
}

function beautiful_time_to_minutes($time)
{
  if ($time == '' || $time == null)
    return 0;
  $time_parts = explode(":", $time);
  return $time_parts[0] * 60 + $time_parts[1];
}

function beautify_time_am_pm($time)
{
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

function shift_calendar($month, $year, $dateArray)
{
  // Create array containing abbreviations of days of week.
  $daysOfWeek = array('S', 'M', 'T', 'W', 'T', 'F', 'S');
  // What is the first day of the month in question?
  $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
  // How many days does this month contain?
  $numberDays = date('t', $firstDayOfMonth);
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
  foreach ($daysOfWeek as $day) {
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

function render_shift_calendar_week($data, $date)
{



  $html = "";

  $html .= "<p><b>" . sprintf('%02d', $date) . "</b></p>";

  $html .= '<table style="font-size: 11px" class="table">
  <theah >
  <tr>
  <th style="font-size: 11px">Shift</th>
  <th style="font-size: 11px">#</th>
  </tr>
  </theah>
  <tbody>';

  foreach ($data as $d) {


    $html .= '<tr>
    <td>' . $d["name"] . '</td>
    <td><b>' . $d["count"] . '</b></td>
    </tr>';
  }

  $html .= '</tbody></table>';


  echo $html;
}

function render_clockings_query_for_employee_month($employee_id, $month, $year)
{

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


function render_all_filters(&$data)
{
  $current_user = get_user();
  $CI = &get_instance();


  $data["selected_branch_id"] = 0;
  $data["selected_dep_id"] = 0;
  $data["selected_pos_id"] = 0;
  $data["selected_sec_id"] = 0;
  $data["selected_month"] = 0;
  $data["selected_year"] = 0;
  $data["selected_emp_id"] = 0;
  $data["selected_group_id"] = 0;

  $data["where_filter"] = "";
  $data["where_department_dropdown"] = "";
  $data["where_clock_date"] = "";
  $data["where_date"] = "";


  $cid = $current_user["company_id"];
  $bid = $current_user["branch_id"];

  $permissions_level = $current_user["permissions_level"];
  $limit_access_to_department = $current_user["limit_access_to_department"];
  $department_id = $current_user["department_id"];

  $dids =  $department_id . "," . $current_user["departments_access"];

  $dids = trim($dids, ",");

  $dids_array = explode(',', $dids);


  $data["where_branch_2"] = '';
  $data["where_department"] = '';
  $data["branch_where_filter"] = "";

  //echo $dids;die();

  if ($permissions_level == "Outlet") {

    $data["where_branch_2"] = " AND id = $bid ";

    $query_string = "?daterange_filter=" . $CI->input->get('daterange_filter');
    if (!empty($CI->input->get('emp'))) {
      $data["selected_emp_id"] = $CI->input->get("emp");
      $query_string .= "&emp=" . $CI->input->get("emp");
    }

    if ($limit_access_to_department == "yes") {

      //echo "aa"; die();

      $data["where_department"] = " AND id IN ($dids) ";
      if ($CI->input->get("branch") != $bid || !in_array($CI->input->get("dep"), $dids_array)) {
        $query_string .= "&dep=$department_id&branch=$bid";
        redirect($data["filters_form_action"] . $query_string);
        return;
      }
    } else {
      if ($CI->input->get("branch") != $bid) {
        $query_string .= "&branch=$bid";
        redirect($data["filters_form_action"] . $query_string);
        return;
      }
    }
  } else {
    $query_string = '?' . getDateRangeFilterURLString($current_user['start_day']);

    if ($limit_access_to_department == "yes") {
      $data["where_department"] = " AND id IN ($dids) ";
      if (!in_array($CI->input->get("dep"), $dids_array)) {
        $query_string .= "&dep=$department_id";
        redirect($data["filters_form_action"] . $query_string);
        return;
      }
    }
  }


  if (!empty($CI->input->get("branch"))) {
    $data["selected_branch_id"] = $CI->input->get("branch");
    $data["where_filter"] = $data["where_filter"] . " branch_id = " . $CI->input->get("branch") . " AND ";
    $data["branch_where_filter"] = $data["branch_where_filter"] . " AND employees.branch_id = " . $CI->input->get("branch");
  }

  if (!empty($CI->input->get("dep"))) {
    $data["selected_dep_id"] = $CI->input->get("dep");
    $data["where_filter"] = $data["where_filter"] . " department_id = " . $CI->input->get("dep") . " AND ";

    $data["where_department_dropdown"] = " AND department_id = " .  $CI->input->get("dep");
  }

  if (!empty($CI->input->get("emp"))) {
    $data["selected_emp_id"] = $CI->input->get("emp");
    $data["where_filter"] = $data["where_filter"] . " employees.id = " . $CI->input->get("emp") . " AND ";
  }

  if (!empty($CI->input->get("pos"))) {
    $data["selected_pos_id"] = $CI->input->get("pos");
    $data["where_filter"] = $data["where_filter"] . " position_id = " . $CI->input->get("pos") . " AND ";
  }

  if (!empty($CI->input->get("sec"))) {
    $data["selected_sec_id"] = $CI->input->get("sec");
    $data["where_filter"] = $data["where_filter"] . " section_id = " . $CI->input->get("sec") . " AND ";
  }

  if (!empty($CI->input->get("daterange_filter"))) {
    $daterange = $CI->input->get("daterange_filter");
    $formatted_date = daterange_to_dates($daterange);
    $data["formatted_date"] = $formatted_date;
    $data["selected_month"] = $CI->input->get("month");
    $data["selected_year"] = $CI->input->get("year");
    $data["daterange_filter"] = $daterange;
    
    $month = $data["selected_month"];
    $year = $data["selected_year"];
    $start_date = $formatted_date['start_date'];
    $end_date = $formatted_date['end_date'];
    $data["start_date_f"] = urlencode($start_date->format('d/m/Y'));
    $data["end_date_f"] = urlencode($end_date->format('d/m/Y'));
    $data["start_date_1"] = $start_date->format('m/d/Y');
    $data["end_date_1"] = $end_date->format('m/d/Y');
    $data["start_date"] = $start_date->format('Y-m-d');
    $data["end_date"] = $end_date->format('Y-m-d');
    
    $data["where_clock_date"] = " AND MONTH(clock_in) = " . $CI->input->get("month") . " AND YEAR(clock_in) = " . $CI->input->get("year");
    $data["where_date"] = " AND date BETWEEN '{$start_date->format('Y-m-d')}' AND '{$end_date->format('Y-m-d')}'";
  } else {

    redirect($data["filters_form_action"] . "?branch=" . $CI->input->get("branch") .
      "&" . getDateRangeFilterURLString($current_user['start_day']));
    return;
  }

  if (!empty($CI->input->get("emp_group"))) {
    $data["selected_group_id"] = $CI->input->get("emp_group");
    $data['where_filter'] = $data['where_filter'] . " egr.group_id = " . $CI->input->get("emp_group") . " AND ";
  }

  $data["where_filter"] = $data["where_filter"] . " employees.company_id = " . $cid;

  $data["where_filter"] = trim($data["where_filter"]);
  $data["where_filter"] = trim($data["where_filter"], "AND");

  $data["employees_dropdown"] = $CI->db->query("SELECT employees.id, special_id,first_name,last_name FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE employees.deleted_at IS NULL 
    AND (employee_status = 'active'
      OR (employee_status = 'terminated' AND termination_date IS NOT NULL AND termination_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
      OR (employee_status = 'resigned' AND resignation_date IS NOT NULL AND resignation_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
    )
    AND roles.exclude_from_system = 'no' AND employees.company_id = $cid " . $data["branch_where_filter"] . "  " . $data["where_department_dropdown"] . " ORDER BY special_id")->result();
  // echo count($data["employees_dropdown"]);die;



  $data["branches"] = $CI->db->query("SELECT * FROM branches WHERE company_id = $cid  " . $data["where_branch_2"] . " ORDER BY name")->result();

  $data["departments"] = $CI->db->query("SELECT * FROM departments WHERE company_id = $cid " . $data["where_department"] . " ORDER BY name")->result();

  $data["employee_groups"] = $CI->db->query("SELECT * FROM employee_groups WHERE company_id = $cid " . $data["where_branch_2"] . " ORDER BY name")->result();
}

function render_daily_time_logs_filter(&$data)
{
  $current_user = get_user();

  $CI = &get_instance();


  $data["selected_branch_id"] = 0;
  $data["selected_dep_id"] = 0;
  $data["selected_emp_id"] = 0;
  $data["selected_date"] = 0;

  $data["where_filter"] = "";
  $data["where_department_dropdown"] = "";
  $data["where_clock_date"] = "";
  $data["where_date"] = "";

  $cid = $current_user["company_id"];
  $bid = $current_user["branch_id"];

  $permissions_level = $current_user["permissions_level"];
  $limit_access_to_department = $current_user["limit_access_to_department"];
  $department_id = $current_user["department_id"];

  $dids =  $department_id . "," . $current_user["departments_access"];

  $dids = trim($dids, ",");

  $dids_array = explode(',', $dids);


  $data["where_branch_2"] = '';
  $data["where_department"] = '';
  $data["branch_where_filter"] = "";

  //echo $dids;die();

  if ($permissions_level == "Outlet") {
    $data["where_branch_2"] = " AND id = $bid ";

    $query_string = "?date=" . urlencode($CI->input->get('date'));

    if (!empty($CI->input->get('emp'))) {
      $data["selected_emp_id"] = $CI->input->get("emp");
      $query_string .= "&emp=" . $CI->input->get("emp");
    }

    if ($limit_access_to_department == "yes") {
      //echo "aa"; die();

      $data["where_department"] = " AND id IN ($dids) ";
      if ($CI->input->get("branch") != $bid || !in_array($CI->input->get("dep"), $dids_array)) {
        $query_string .= "&dep=$department_id&branch=$bid";
        redirect($data["filters_form_action"] . $query_string);
        return;
      }
    } else {
      if ($CI->input->get("branch") != $bid) {
        $query_string .= "&branch=$bid";
        redirect($data["filters_form_action"] . $query_string);
        return;
      }
    }
  } else {
    $query_string = "?date=" . date('d/m/Y');

    if ($limit_access_to_department == "yes") {
      $data["where_department"] = " AND id IN ($dids) ";
      if (!in_array($CI->input->get("dep"), $dids_array)) {
        $query_string .= "&dep=$department_id";
        redirect($data["filters_form_action"] . $query_string);
        return;
      }
    }
  }

  if (!empty($CI->input->get("branch"))) {
    $data["selected_branch_id"] = $CI->input->get("branch");
    $data["where_filter"] = $data["where_filter"] . " branch_id = " . $CI->input->get("branch") . " AND ";
    $data["branch_where_filter"] = $data["branch_where_filter"] . " AND employees.branch_id = " . $CI->input->get("branch");
  }
  if (!empty($CI->input->get("dep"))) {
    $data["selected_dep_id"] = $CI->input->get("dep");
    $data["where_filter"] = $data["where_filter"] . " department_id = " . $CI->input->get("dep") . " AND ";

    $data["where_department_dropdown"] = " AND department_id = " .  $CI->input->get("dep");
  }

  if (!empty($CI->input->get("emp"))) {
    $data["selected_emp_id"] = $CI->input->get("emp");
    $data["where_filter"] = $data["where_filter"] . " employees.id = " . $CI->input->get("emp") . " AND ";
  }

  if (!empty($CI->input->get("date"))) {
    $date = DateTime::createFromFormat('d/m/Y', $CI->input->get("date"));
    $date = $date->format('Y-m-d');

    $data["selected_date"] = $CI->input->get("date");
    $data["where_clock_date"] = " AND DATE(clock_in) = $date";
    $data["where_date"] = " AND DATE(date) = $date";
  } else {
    redirect($data["filters_form_action"] . "?branch=" . $CI->input->get("branch") . "&date=" . urlencode(date("d/m/Y")));
    return;
  }

  $data["where_filter"] = $data["where_filter"] . " employees.company_id = " . $cid;

  $data["where_filter"] = trim($data["where_filter"]);
  $data["where_filter"] = trim($data["where_filter"], "AND");

  $data["employees_dropdown"] = $CI->db->query("SELECT employees.id, special_id,first_name,last_name FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE employees.deleted_at IS NULL 
    AND (employee_status = 'active'
      OR (employee_status = 'terminated' AND termination_date IS NOT NULL AND termination_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
      OR (employee_status = 'resigned' AND resignation_date IS NOT NULL AND resignation_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
    )
    AND roles.exclude_from_system = 'no' AND employees.company_id = $cid " . $data["branch_where_filter"] . "  " . $data["where_department_dropdown"] . " ORDER BY special_id")->result();
  // echo count($data["employees_dropdown"]);die;



  $data["branches"] = $CI->db->query("SELECT * FROM branches WHERE company_id = $cid  " . $data["where_branch_2"] . " ORDER BY name")->result();

  $data["departments"] = $CI->db->query("SELECT * FROM departments WHERE company_id = $cid " . $data["where_department"] . " ORDER BY name")->result();
}

function render_att_report_filters(&$data)
{

  $CI = &get_instance();


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

  $dids_array = explode(',', $dids);


  $data["where_branch_2"] = '';
  $data["where_department"] = '';
  $data["branch_where_filter"] = "";

  //echo $dids;die();

  if ($permissions_level == "Outlet") {



    $data["where_branch_2"] = " AND id = $bid ";

    //if($limit_access_to_department == "yes"){

    //echo "aa"; die();

    $data["where_department"] = " AND id IN ($dids) ";

    // if($CI->input->get("branch") != $bid || !in_array($CI->input->get("dep"), $dids_array)){
    if ($CI->input->get("branch") != $bid) {
      redirect($data["filters_form_action"] . "?branch=$bid&status=late");
      return;
    }
    // }
    // else{
    //     if($CI->input->get("branch") != $bid){
    //         redirect($data["filters_form_action"]."?branch=$bid&month=".date('m'));
    //         return;
    //     }
    // }            
  } else {

    // if($limit_access_to_department == "yes"){
    //     $data["where_department"] = " AND id IN ($dids) ";
    //     if(!in_array($CI->input->get("dep"), $dids_array)){
    //         redirect($data["filters_form_action"]."?dep=$department_id&month=".date('m'));
    //         return;
    //     }
    // }

  }


  if (!empty($CI->input->get("branch"))) {
    $data["selected_branch_id"] = $CI->input->get("branch");
    $data["where_filter"] = $data["where_filter"] . " branch_id = " . $CI->input->get("branch") . " AND ";
    $data["branch_where_filter"] = $data["branch_where_filter"] . " AND employees.branch_id = " . $CI->input->get("branch");
  }

  // if(!empty($CI->input->get("dep"))){
  //     $data["selected_dep_id"] = $CI->input->get("dep");
  //     $data["where_filter"] = $data["where_filter"] . " department_id = " . $CI->input->get("dep") . " AND " ;

  //     $data["where_department_dropdown"] = " AND department_id = " .  $CI->input->get("dep");

  // }

  if (!empty($CI->input->get("emp"))) {
    $data["selected_emp_id"] = $CI->input->get("emp");
    $data["where_filter"] = $data["where_filter"] . " employees.id = " . $CI->input->get("emp") . " AND ";
  }

  if (!empty($CI->input->get("status"))) {
    $data["selected_month"] = $CI->input->get("status");
  } else {
    redirect($data["filters_form_action"] . "?status=late");
    return;
  }

  $data["where_filter"] = $data["where_filter"] . " employees.company_id = " . $cid;

  $data["where_filter"] = trim($data["where_filter"]);
  $data["where_filter"] = trim($data["where_filter"], "AND");

  $data["employees_dropdown"] = $CI->db->query("SELECT employees.id, special_id,first_name,last_name FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE employees.deleted_at IS NULL 
    AND (employee_status = 'active'
      OR (employee_status = 'terminated' AND termination_date IS NOT NULL AND termination_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
      OR (employee_status = 'resigned' AND resignation_date IS NOT NULL AND resignation_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
    )
    AND roles.exclude_from_system = 'no' AND employees.company_id = $cid " . $data["branch_where_filter"] . "  " . $data["where_department_dropdown"] . " ORDER BY special_id")->result();
  // echo count($data["employees_dropdown"]);die;



  $data["branches"] = $CI->db->query("SELECT * FROM branches WHERE company_id = $cid  " . $data["where_branch_2"] . " ORDER BY name")->result();
}

function gantt_chart_department_shift($is_branch = false, $branch_id = 0)
{


  $CI = &get_instance();

  $CI->benchmark->mark('code_start');


  $gantt_array = array();
  $departments = array();
  $gantt_array_final = array();

  $where_branch_3 = " ";

  $cid = get_user()["company_id"];


  if ($is_branch) {
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
    $arr_temp["code"] = ""; //$shift_d["code"];
    $arr_temp["color"] = "black"; //$shift_d["color"];
    $arr_temp["start"] = ""; //strtotime(date("Y-m-d") . " " . $shift_d["start_time"]) * 1000; //mktime(8,30,0,11,17,2019);
    $arr_temp["end"] =  ""; //strtotime(date("Y-m-d") . " " . $shift_d["end_time"]) * 1000; //mktime(10,30,0,11,17,2019);;
    $gantt_array[$dep["name"]] = $arr_temp;
  }

  $all_employees = $CI->db->query("SELECT employees.id as emp_id,departments.id as dep_id, departments.name as dep_name FROM employees INNER JOIN departments ON departments.id = employees.department_id WHERE employees.company_id = $cid $where_branch_3 ")->result_array();

  //header('Content-Type: application/json');

  //die(json_encode($gantt_array));

  $all_shifts_today = $CI->db->query("SELECT shifts.id, shifts.start_time, shifts.end_time, shifts.code, shifts.name, shifts.color, shift_days.date, shift_days.employees FROM shifts INNER JOIN shift_days ON shifts.id = shift_days.shift_id where company_id = $cid $where_branch_3 AND shifts.is_leave = 'no' AND shift_days.employees <> '' AND shift_days.date = CURRENT_DATE GROUP BY shift_id")->result_array();

  foreach ($all_employees as $emp) {
    foreach ($all_shifts_today as $shift_d) {


      $arr_temp = array();
      $arr_temp["name"] = $shift_d["name"];
      $arr_temp["id"] = (str_replace(' ', '_', strtolower($emp["dep_name"] . $shift_d["name"]) . $shift_d["id"]));
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
      $emps = explode(",", $shift_d["employees"]);

      //die();
      // if($emp["emp_id"] == "637" && $shift_d["employees"] == "637"){
      //   var_dump($emp);
      //   var_dump($shift_d);
      //   die("here");
      // }

      if (in_array($emp["emp_id"], $emps)) {
        $gantt_array[$emp["dep_name"] . "_" . $shift_d["name"]]["count"]++;
        $gantt_array[$emp["dep_name"]]["count"]++;

        if (empty($gantt_array[$emp["dep_name"]]["start"])) {
          $gantt_array[$emp["dep_name"]]["start"] = strtotime(date("Y-m-d") . " " . $shift_d["start_time"]) * 1000;
        }

        if (empty($gantt_array[$emp["dep_name"]]["end"])) {
          $gantt_array[$emp["dep_name"]]["end"] = strtotime(date("Y-m-d") . " " . $shift_d["end_time"]) * 1000;
        }

        if ((strtotime(date("Y-m-d") . " " . $shift_d["start_time"]) * 1000) < $gantt_array[$emp["dep_name"]]["start"]) {
          $gantt_array[$emp["dep_name"]]["start"] = strtotime(date("Y-m-d") . " " . $shift_d["start_time"]) * 1000;
        }


        if ((strtotime(date("Y-m-d") . " " . $shift_d["end_time"]) * 1000) > $gantt_array[$emp["dep_name"]]["end"]) {
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
    if ($ga["count"] > 0) {
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

function update_shifts($device_id)
{

  //Shift 18 on 31sth for emp 1030 - comment added by Naveed

  var_dump('update_shifts function calling for device ' . $device_id);
  $CI = &get_instance();

  $result1 = $CI->db->query("SELECT * FROM clockings_news WHERE device_id = $device_id AND shift_id = 0 ")->result();

  //var_dump($result1);

  foreach ($result1 as $row1) {
    $d = date('Y-m-d', strtotime($row1->datetime));
    $employee_id = $row1->employee_id;
    //var_dump($row1);
    $shift_day = $CI->db->query("SELECT * FROM shift_days WHERE DATE(date) = '$d' AND FIND_IN_SET($employee_id,employees)")->row();

    var_dump("date " . $d);
    var_dump("employee id " . $employee_id);

    if ($shift_day) {

      $shift_id = $shift_day->shift_id;
      var_dump("shift_id " . $shift_id);
      $update_shift = $CI->db->query("UPDATE clockings_news SET shift_id = $shift_id WHERE DATE(datetime) = '$d' AND employee_id = $employee_id");
    }


    var_dump("------------------");
  }

  //echo "done";

}

function last_day_of_month($month)
{

  $a_date = "2020-$month-01";

  return date("t", strtotime($a_date));
}

function getDistance($latitude1, $longitude1, $latitude2, $longitude2)
{
  $earth_radius = 6371;

  $dLat = deg2rad($latitude2 - $latitude1);
  $dLon = deg2rad($longitude2 - $longitude1);

  $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon / 2) * sin($dLon / 2);
  $c = 2 * asin(sqrt($a));
  $d = $earth_radius * $c;

  return $d;
}

function insert_log($type, $data = [])
{
  $CI = &get_instance();

  if ($CI->session->userdata("payroll_user")) {
    $user = $CI->session->userdata("payroll_user");
  } else {
    $user = get_user();
  }


  $neccessary_data = [
    'user_id' => $user['id'],
    'type' => $type,
    'branch_id' => $user['branch_id'],
    'company_id' => $user['company_id'],
    'created_at' => date('Y-m-d H:i:s'),
  ];

  $data_to_insert = array_merge($neccessary_data, $data);
  return $CI->db->insert('logs', $data_to_insert);
}

function get_role()
{
  $CI = &get_instance();
  return $CI->db->get_where('roles', array('id' => get_user()['role_id']))->row();
}

/**
 * Function to convert daterange string to start and end date
 * 
 * @param string $daterange_dates Daterange string in DD/MM/YYYY - DD/MM/YYYY format
 *
 * @return array<DateTime, DateTime> Start and end date
 */
function daterange_to_dates($daterange_dates)
{
  $dates = explode(" - ", $daterange_dates);

  $data['start_date'] = DateTime::createFromFormat('d/m/Y', $dates[0]);
  $data['end_date'] = DateTime::createFromFormat('d/m/Y', $dates[1]);
  
  return $data;
}


function get_company_outlets($oid = FALSE)
{
  $ci = &get_instance();
  $cid = get_user()["company_id"];

  if ($oid === FALSE)
    return $ci->db->select("id, name")->from('branches')->where('company_id', $cid)->get()->result();
  else
    return $ci->db->select("id, name")->from("branches")->where("company_id", $cid)->where("id", $oid)->get()->result();
}

function add_days($total_days, $days)
{
  if ($days == "-" || $days == "") {
    return $total_days;
  } else {
    return $total_days + $days;
  }
}

function add_time($time1, $time2)
{
  if ($time2 == null || $time2 == "" || $time2 == "00:00" || $time2 == "-") {
    return remove_seconds($time1);
  }
  if (empty($time1)) {
    $time1 = "00:00";
  }

  $is_time1_minus = $time1[0] == "-";
  $is_time2_minus = $time2[0] == "-";

  $time1 = explode(":", $time1);
  $time2 = explode(":", $time2);

  $time1_in_minutes = abs($time1[0] * 60) + $time1[1];
  $time2_in_minutes = abs($time2[0] * 60) + $time2[1];

  $time1_in_minutes = $is_time1_minus == true ? -$time1_in_minutes : $time1_in_minutes;
  $time2_in_minutes = $is_time2_minus == true ? -$time2_in_minutes : $time2_in_minutes;

  $total_time_in_minutes = $time1_in_minutes + $time2_in_minutes;
  $abs_total_time_in_minutes = abs($total_time_in_minutes);

  $hours = floor($abs_total_time_in_minutes / 60);
  $minutes = ($abs_total_time_in_minutes % 60);

  $time = sprintf("%02d:%02d", $hours, $minutes);

  return $total_time_in_minutes < 0 === true ? "-{$time}" : $time;
}

function remove_seconds($time) {
  $time_parts = explode(":", $time);
  if (count($time_parts) == 3) {
    return $time_parts[0] . ":" . $time_parts[1];
  }
  return $time;
}

function add_time_minus($time1, $time2)
{
  if ($time2 == null || $time2 == "" || $time2 == "00:00") {
    return $time1;
  }
  if (empty($time1)) {
    $time1 = "00:00";
  }

  if (is_minus($time1) && is_minus($time2)) {
    $time1 = str_replace("-", "", $time1);
    $time2 = str_replace("-", "", $time2);
    $total = "-" . add_time($time1, $time2);
  } else if (!is_minus($time1) && !is_minus($time2)) {
    $total = add_time($time1, $time2);
  } else if (!is_minus($time1) && is_minus($time2)) {
    $time2 = str_replace("-", "", $time2);
    $t1 = (int) str_replace(":", "", $time1);
    $t2 = (int) str_replace(":", "", $time2);

    if ($t1 < $t2) {
      $total = "-" . sub_time($time2, $time1);
    } else {
      $total = sub_time($time1, $time2);
    }
  } else {
    $time1 = str_replace("-", "", $time1);
    $t1 = (int) str_replace(":", "", $time1);
    $t2 = (int) str_replace(":", "", $time2);

    if ($t2 < $t1) {
      $total = "-" . sub_time($time1, $time2);
    } else {
      $total = sub_time($time2, $time1);
    }
  }

  if ($total == "-00:00") $total = "00:00";

  return $total;
}

function is_minus($string)
{
  if (strpos($string, '-') !== false) {
    return true;
  }
  return false;
}

function total_time($a, $b)
{
  if ($a == null || $b == null) {
    return "";
  }
  $time1 = DateTime::createFromFormat('d-m-Y H:i', $a);
  $time2 = DateTime::createFromFormat('d-m-Y H:i', $b);
  $interval = date_diff($time1, $time2);
  $days = $interval->format('%a');
  $format = $interval->format('%H:%i');
  $format = explode(":", $format);
  $format[0] = $format[0] + ($days * 24);
  $format[0] = sprintf("%02d", $format[0]);
  $format[1] = sprintf("%02d", $format[1]);
  $format = implode(":", $format);
  return $format;
}

function sub_time($time1, $time2)
{
  if ($time2 == null) {
    return $time1;
  }
  if (empty($time1)) {
    $time1 = "00:00";
  }
  $time1 = explode(":", $time1);
  $time2 = explode(":", $time2);
  $hours = $time1[0] - $time2[0];
  $minutes = $time1[1] - $time2[1];
  if ($minutes <= 0) {
    $minutes += 60;
    //= abs($minutes);
    $hours = $hours - 1;
  }
  if ($minutes >= 60) {
    $minutes -= 60;
    $hours = $hours + 1;
  }

  $hours = sprintf("%02d", $hours);
  $minutes = sprintf("%02d", $minutes);
  // if($hours == "00" && $minutes == "00"){
  //  return "";
  // }
  return $hours . ":" . $minutes;
}

function calculate_overtime($overtime, $clock_in_1, $clock_out_1, $overtime_starts, $date, $overnight = false)
{
  if (empty($clock_in_1) || empty($clock_out_1) || $overtime_starts == "") {
    return "";
  }

  $date1 = date('Y-m-d', strtotime($date));

  if ($overnight) {
    date_default_timezone_set('UTC');
    $overtime_starts = $date1 . " " . $overtime_starts . ":00";
    $overtime_starts_stamp = strtotime($overtime_starts);
    $mid_day = $date1 . " 12:00:00";
    $mid_day_stamp = strtotime($mid_day);
    if ($mid_day_stamp > $overtime_starts_stamp) {
      $overtime_starts_stamp += 24 * 3600;
    }
    $overtime_starts = date('d-m-Y H:i', $overtime_starts_stamp);
    date_default_timezone_set("Asia/Kuala_Lumpur");
  } else {
    $overtime_starts = $date . " " . $overtime_starts;
  }




  $overtime_starts = DateTime::createFromFormat('d-m-Y H:i', $overtime_starts);
  $clock_in = DateTime::createFromFormat('d-m-Y H:i', $clock_in_1);
  $clock_out = DateTime::createFromFormat('d-m-Y H:i', $clock_out_1);

  if ($clock_in > $overtime_starts) {
    $interval = total_time($clock_in_1, $clock_out_1);
    $overtime = add_time($overtime, $interval);
  } else if ($clock_out > $overtime_starts) {
    $interval = total_time(date_format($overtime_starts, "d-m-Y H:i"), $clock_out_1);
    $overtime = add_time($overtime, $interval);
  }

  return $overtime;
}

function calculate_early_overtime($overtime, $clock_in_1, $clock_out_1, $early_ot_start, $early_ot_end, $date, $overnight = false)
{

  if (empty($clock_in_1) || empty($clock_out_1) || $early_ot_start == "" || $early_ot_start == "00:00" || $early_ot_end == "" || $early_ot_end == "00:00") {
    return "";
  }



  $early_ot_start = DateTime::createFromFormat('d-m-Y H:i', $date . " " . $early_ot_start);
  $early_ot_end = DateTime::createFromFormat('d-m-Y H:i', $date . " " . $early_ot_end);
  $clock_in = DateTime::createFromFormat('d-m-Y H:i', $clock_in_1);
  $clock_out = DateTime::createFromFormat('d-m-Y H:i', $clock_out_1);
  if ($clock_in >= $early_ot_end && $clock_out >= $early_ot_end) {
  } else if ($clock_in >= $early_ot_start) {
    if ($clock_out <= $early_ot_end) {
      $interval = total_time($clock_in_1, $clock_out_1);
      $overtime = add_time($overtime, $interval);
    } else {
      $interval = total_time($clock_in_1, date_format($early_ot_end, "d-m-Y H:i"));
      $overtime = add_time($overtime, $interval);
    }
  } else if ($clock_out > $early_ot_start) {
    if ($clock_out <= $early_ot_end) {
      $interval = total_time(date_format($early_ot_start, "d-m-Y H:i"), $clock_out_1);
      $overtime = add_time($overtime, $interval);
    } else {
      $interval = total_time(date_format($early_ot_start, "d-m-Y H:i"), date_format($early_ot_end, "d-m-Y H:i"));
      $overtime = add_time($overtime, $interval);
    }
  }

  return $overtime;
}

function calculate_weekly_ot($work_hours, $shift_hours)
{
  if ($work_hours == "" || $work_hours == "00:00") {
    return "";
  }
  $work_hours_decimal = toDecimal($work_hours);
  $shift_hours_decimal = toDecimal($shift_hours);

  if ($work_hours_decimal > $shift_hours_decimal) {
    return sub_time($work_hours, $shift_hours);
  }
  return "";
}

function calculate_days($hours, $days_settings)
{
  if ($hours == "") {
    $hours = "00:00";
  }
  $temp = explode(":", $hours);
  if (count($temp) > 1) {
    $hours_formatted = $temp[0] + ($temp[1] / 60);
  } else {
    return "";
  }

  $days = "-";
  foreach ($days_settings as $ds) {
    if ($ds->from_hour <= $hours_formatted && $ds->to_hour > $hours_formatted) {
      $days = $ds->days;
      break;
    }
  }
  return $days;
}

/**
 * The function calculates late break times
 *
 * @param string $break_hours
 * @param array $breaks_array
 * @param stdClass $v Reference to clockings object
 * 
 * @return string Returns the late break time
 */
function calculate_break_late($break_hours, $breaks_array, &$v, $work_hours, $is_shift)
{
  if ($is_shift === "false") return "";
  if ($break_hours == "" || $break_hours == "00:00") {
    return ""; // didn't take any breaks that's why no late
  }
  $total_breaks = count($breaks_array);
  // $normal_breaks = 0;
  $normal_breaks = count_normal_breaks($v, $breaks_array);
  $final_late = "00:00";
  if ($v->break_duration != "") {
    // if break duration is set then return total late no need to proceed with extra breaks
    $break_hours = date('d-m-Y') . " " . $break_hours;
    $break_duration = date('d-m-Y') . " " . $v->break_duration;
    if ($break_hours > $break_duration) {
      return total_time($break_duration, $break_hours);
    }
    return $final_late;
  } else {
    if ($v->break_1 != "" || $v->break_2 != "" || $v->break_3 != "" || $v->break_4 != "" || $v->break_5 != "" || $v->break_6 != "") {
      // check filled $break variables and check if any breaks_array element is more than that break
      for ($i = 0, $j = 0; $i < 6; $i++) {
        $current_break = $v->{"break_" . ($i + 1)};
        $consider_current_break = $v->{"consider_break_" . ($i + 1)};
        // $next_break = $v->{"break_" . ($i + 2)};

        if ($current_break != "") {
          if(!isset($breaks_array[$j])) $breaks_array[$j] = "00:00"; // UmarNote: if breaks_array is empty then set it to 00:00
          if (($i + 1) % 2 !== 0) {
            if ($consider_current_break == 0 && $breaks_array[$j] > "00:40") {
              continue;
            }
            $final_late = add_time($final_late, get_late_time($breaks_array[$j], $v->{"break_" . ($i + 1)}));
          } else {
            $final_late = add_time($final_late, get_late_time($breaks_array[$j], $v->{"break_" . ($i + 1)}));
          }
          $j++;
          // $normal_breaks++;
        } else break; // if a break variable is empty then skip the rest
      }

      $extra_breaks = count_extra_breaks($v);
      if ($v->extra_break === "Y" && $v->extra_break_worked_hours_more_than != "") {
        if ($work_hours > $v->extra_break_worked_hours_more_than) {
          $loop_counter = $normal_breaks + 6; // loop should run upto $normal_breaks + 6 count
          for ($i = $normal_breaks; $i < $loop_counter; $i++) { // skip normal breaks from breaks_array
            if ($v->{"extra_break_" . ($i + 1 - $normal_breaks)} != "") { // extra_break_1, 2, 3 that's why subtract $normal_breaks
              $final_late = add_time($final_late, get_late_time($breaks_array[$i], $v->{"extra_break_" . ($i + 1 - $normal_breaks)}));
              // $extra_breaks++;
            } else break; // same logic, if extra_break variable is empty, skip the rest
          }
        }
      }
      // if person takes more breaks than specified in the shift then add them to late break
      if ($total_breaks > $normal_breaks + $extra_breaks) {
        for ($i = $normal_breaks + $extra_breaks; $i < $total_breaks; $i++) {
          $final_late = add_time($final_late, $breaks_array[$i]);
        }
      }
    }
  }
  // $extra_breaks = 0;

  return $final_late;
}

function calculate_break_not_taken($break_hours, $breaks_array, &$v)
{
  $break_not_taken = "00:00";

  if ($v->break_duration != "") {

    if ($break_hours < $v->break_duration) {
      return sub_time($v->break_duration, $break_hours);
    } else {
      return "00:00";
    }
  } else {

    if ($v->break_1 != "" || $v->break_2 != "" || $v->break_3 != "" || $v->break_4 != "" || $v->break_5 != "" || $v->break_6 != "") {
      for ($i = 0; $i < 6; $i++) {
        $current_break = $v->{"break_" . ($i + 1)};
        $current_consider_break = $v->{"consider_break_" . ($i + 1)};
        if ($current_break != "" && $current_consider_break == 0) {
          if (!isset($breaks_array[$i])) {
            $breaks_array[$i] = "00:00";
          } else {
            if ($breaks_array[$i] > "00:40") {
              array_splice($breaks_array, $i, 0, "00:00");
            }
          }
        } else {
          if (!isset($breaks_array[$i]))
            $breaks_array[$i] = "00:00";
        }

        if ($current_consider_break == 1) {
          $break_not_taken = calculate_break_not_used($break_not_taken, $breaks_array[$i], $current_break);
        }
      }
      // if ($v->consider_break_1 == 1) {
      //   $break_not_taken = calculate_break_not_used($break_not_taken, $breaks_array[0], $v->break_1);
      // }
      // if ($v->consider_break_2 == 1) {
      //   if ($v->consider_break_1 == 0 && $breaks_array[0] > "00:40" || $breaks_array[0] == "00:00") {
      //     $break_not_taken = calculate_break_not_used($break_not_taken, $breaks_array[0], $v->break_2);
      //   } else {
      //     $break_not_taken = calculate_break_not_used($break_not_taken, $breaks_array[1], $v->break_2);
      //   }
      // }
      // if ($v->consider_break_3 == 1) {
      //   $break_not_taken = calculate_break_not_used($break_not_taken, $breaks_array[2], $v->break_3);
      // }
      // if ($v->consider_break_4 == 1) {
      //   if ($v->consider_break_3 == 0 && $breaks_array[2] > "00:40") {
      //     $break_not_taken = calculate_break_not_used($break_not_taken, $breaks_array[2], $v->break_4);
      //   } else {
      //     $break_not_taken = calculate_break_not_used($break_not_taken, $breaks_array[3], $v->break_4);
      //   }
      // }
      // if ($v->consider_break_5 == 1) {
      //   $break_not_taken = calculate_break_not_used($break_not_taken, $breaks_array[4], $v->break_5);
      // }
      // if ($v->consider_break_6 == 1) {
      //   if ($v->consider_break_5 == 0 && $breaks_array[4] > "00:40") {
      //     $break_not_taken = calculate_break_not_used($break_not_taken, $breaks_array[4], $v->break_6);
      //   } else {
      //     $break_not_taken = calculate_break_not_used($break_not_taken, $breaks_array[5], $v->break_6);
      //   }
      // }

      return $break_not_taken;
    } else {
      return "00:00";
    }
  }
}

function calculate_extra_break_not_taken($breaks_array, &$v, $work_hours)
{
  $break_not_taken = "00:00";
  $no_of_extra_breaks = count_extra_breaks($v);
  if ($no_of_extra_breaks === 0) return $break_not_taken;

  if ($v->extra_break === "Y" && $v->extra_break_worked_hours_more_than && $work_hours > $v->extra_break_worked_hours_more_than) {

    $no_of_normal_breaks = count_normal_breaks($v, $breaks_array);
    $total_breaks = $no_of_normal_breaks + $no_of_extra_breaks;

    for ($i = $no_of_normal_breaks; $i < $total_breaks; $i++) {
      if (!isset($breaks_array[$i])) {
        $breaks_array[$i] = "00:00";
      }

      $break_not_taken = calculate_break_not_used($break_not_taken, $breaks_array[$i], $v->{"extra_break_" . ($i + 1 - $no_of_normal_breaks)});
    }
  }


  return $break_not_taken;
}

/**
 * Function to check how many normal breaks are set
 *
 * @param stdClass $v Clocking object as a reference
 * @param array $breaks_array Array of breaks as a reference
 * 
 * @return int count of normal breaks
 */
function count_normal_breaks(&$v, &$breaks_array)
{
  $normal_breaks = 0;
  // $array_counter = null;
  // $is_skipped = false;
  // if ($v->break_1 != "" && $v->consider_break_1 == 0) {
  //   if (!isset($breaks_array[0]) || $breaks_array[0] == "00:00" || $breaks_array[0] > "00:40") {
  //     $is_skipped = true;
  //   } else {
  //     $normal_breaks++;
  //   }
  // }
  // if ($is_skipped) {
  // } else {
  // }
  for ($i = 0, $j = 0; $i < 6; $i++) {
    $current_break = $v->{"break_" . ($i + 1)};
    // $next_break = $v->{"break_" . ($i + 2)};
    $current_break_consider = $v->{"consider_break_" . ($i + 1)};
    // $next_break_consider = $v->{"consider_break_" . ($i + 2)};

    // if (($i + 1) % 2 !== 0) {
    // } else {
    // }

    if ($current_break != "") {
      if ($current_break_consider == 0) {
        if (!isset($breaks_array[$j]) || $breaks_array[$j] == "00:00" || $breaks_array[$j] > "00:40") {
        } else {
          $normal_breaks++;
          $j++;
        }
      } else {
        if (isset($breaks_array[$j])) {
          $j++;
          $normal_breaks++;
        }
      }
    } else break;
  }
  return $normal_breaks;
}

/**
 * Function to count extra breaks set in settings
 *
 * @param stdClass $v Clocking object as a reference
 * @return int
 */
function count_extra_breaks(&$v)
{
  $extra_breaks = 0;
  for ($i = 0; $i < 6; $i++) {
    if ($v->{"extra_break_" . ($i + 1)} != "") {
      $extra_breaks++;
    } else break;
  }
  return $extra_breaks;
}

function calculate_break_not_used($break_not_taken, $actual_break, $shift_break)
{
  if ($actual_break < $shift_break) {
    return add_time($break_not_taken, sub_time($shift_break, $actual_break));
  }
  return $break_not_taken;
}

function get_late_time($emp_break, $org_break)
{
  if ($org_break == "") $org_break = "00:00";
  $emp_break = date('d-m-Y') . " " . $emp_break;
  $org_break = date('d-m-Y') . " " . $org_break;

  if ($emp_break > $org_break) {
    return total_time($org_break, $emp_break);
  } else {
    return "";
  }
}

function search_from_list($list, $date)
{
  foreach ($list as $l) {
    if ($l->date == $date) {
      return $l;
    }
  }
  return array();
}

function search_clocking($list, $date)
{
  $result = array();
  foreach ($list as $l) {
    if ($l->search_date == $date) {
      $result[] = $l;
    }
  }
  return $result;
}

function calculate_early_out($last_out, $end_time, $date, $overnight)
{
  if ($end_time == "") return "";
  $end_time = $date . " " . $end_time;
  $end_time = DateTime::createFromFormat('Y-m-d H:i:s', $end_time);
  $end_time_in_format = toDecimal($end_time->format("H:i")); // if overnight shift ends on same day
  if ($overnight && $end_time_in_format < 12) {
    $end_time->modify('+1 day');
  }
  $last_out = DateTime::createFromFormat('d-m-Y H:i', $last_out);
  if ($last_out < $end_time) {
    $interval = date_diff($last_out, $end_time);
    $days = $interval->format('%a');
    $format = $interval->format('%H:%i');
    $format = explode(":", $format);
    $format[0] = $format[0] + ($days * 24);
    $format[0] = sprintf("%02d", $format[0]);
    $format[1] = sprintf("%02d", $format[1]);
    $format = implode(":", $format);
    return $format;
  } else {
    return "";
  }
}

function calculate_short_hours($company_working_hours, $work_hours)
{
  if ($work_hours == "" || $work_hours == "00:00") {
    return "";
  }
  if (!empty($company_working_hours) && $company_working_hours != "00:00") {
    $company_working_hours = DateTime::createFromFormat('H:i', $company_working_hours);
    $work_hours = DateTime::createFromFormat('H:i', $work_hours);
    if ($work_hours == false || $company_working_hours == false) {
      return "";
    }
    if ($work_hours < $company_working_hours) {
      $interval = date_diff($work_hours, $company_working_hours);
      $days = $interval->format('%a');
      $format = $interval->format('%H:%i');
      $format = explode(":", $format);
      $format[0] = $format[0] + ($days * 24);
      $format[0] = sprintf("%02d", $format[0]);
      $format[1] = sprintf("%02d", $format[1]);
      $format = implode(":", $format);
      return $format;
    } else {
      return "";
    }
  } else {
    return "";
  }
}

function search_clocking_by_id($list, $date, $id)
{
  $result = array();
  foreach ($list as $l) {
    if ($l->search_date == $date && $l->employee_id == $id) {
      $result[] = $l;
    }
  }
  return $result;
}

function search_from_list_by_branch_id($list, $branch_id)
{
  $result = array();
  foreach ($list as $l) {
    if ($l->branch_id == $branch_id) {
      $result[] = $l;
    }
  }
  return $result;
}

function search_from_rest_days($list, $id)
{
  foreach ($list as $l) {
    if ($l->id == $id) {
      return $l->rest_days;
    }
  }
  return '';
}

function search_from_off_days($list, $id)
{
  foreach ($list as $l) {
    if ($l->id == $id) {
      return $l->off_days;
    }
  }
  return '';
}


function toDecimal($time)
{
  $is_minus = false;
  if (is_minus($time)) $is_minus = true;
  $time = str_replace("-", "", $time);
  if ($time == "" || $time == "00:00") {
    return 0;
  } else {
    $time = explode(":", $time);
    if (count($time) >= 2) {
      $result = round($time[0] + ($time[1] / 60), 2);
      if ($is_minus) $result *= -1;
      return $result;
    } else {
      return 0;
    }
  }
}

function decimal_to_time($decimal)
{
  // hours to time string
  if ($decimal == 0) {
    return "00:00";
  }
  // convert to minutes first.
  $minutes = round($decimal * 60);

  $hours = floor($minutes / 60);
  $minutes = floor($minutes % 60);

  // return str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minutes, 2, "0", STR_PAD_RIGHT);
  return sprintf("%02d:%02d", $hours, $minutes);
}

function calculate_late_days($late_hours, $company_working_hours)
{
  // use 8.5 hours if GNI01 or GNIC01
  $cid = get_user()["company_id"];
  if ($cid == 223 || $cid == 259) {
    $company_working_hours = "8.5";
  }
  return round(($late_hours / $company_working_hours), 2);
}

function calculate_days_for_report($hours, $days_settings)
{
  $temp = explode(":", $hours);
  if (count($temp) > 1) {
    $hours_formatted = $temp[0] + ($temp[1] / 60);
  } else {
    return 0;
  }

  $days = 0;
  foreach ($days_settings as $ds) {
    if ($ds->from_hour <= $hours_formatted && $ds->to_hour > $hours_formatted) {
      $days = $ds->days;
      break;
    }
  }
  return $days;
}

function get_clockings_table_name($date)
{
  $table_name = "clockings";
  // if date is >= "2024-09-01" and also not older than 3 months, set table name to "new_clockings"
  if ($date >= "2024-09-01" && $date >= date('Y-m-d', strtotime('-3 months'))) {
    $table_name = "new_clockings";
  }
  return $table_name;
}

function get_result_list($employees, $first_day, $last_day)
{
  $clockings_table = get_clockings_table_name($first_day);
  $first_day = date('Y-m-d', strtotime('-1 day', strtotime($first_day)));
  $ci = &get_instance();

  $result = $ci->db->select('c.employee_id,c.id,date_format(clock_in,"%d/%m %a") as day_f,clock_in as clock_in_o, date_format(clock_in,"%H:%i") as clock_in, date_format(clock_in,"%d-%m-%Y %H:%i") as clock_in_1,date_format(clock_out,"%H:%i") as clock_out,date_format(clock_out,"%d-%m-%Y %H:%i") as clock_out_1,clock_in_id,clock_out_id,s.grace_time as grace_time_o, date_format(s.end_time,"%H:%i") as end_time, date_format(s.grace_time,"%H:%i") as grace_time, s.start_time as start_time_o, date_format(s.start_time, "%H:%i") as start_time, s.name,s.code,reason,c.remark,date_format(end_time,"%H:%i") as end_time,date_format(overtime_starts,"%H:%i") as overtime_starts,date_format(early_ot_start,"%H:%i") as early_ot_start,date_format(early_ot_end,"%H:%i") as early_ot_end,time_format(timediff(end_time,start_time),"%H:%i") as shift_hours, fixed_ot, fixed_overtime, auto_approve_ot, r.remark as shift_remark, is_leave,void_late_in,void_early_out, date_format(break_duration,"%H:%i") as break_duration, date_format(break_1,"%H:%i") as break_1, consider_break_1, date_format(break_2,"%H:%i") as break_2, consider_break_2, date_format(break_3,"%H:%i") as break_3, consider_break_3, date_format(break_4,"%H:%i") as break_4, consider_break_4, date_format(break_5,"%H:%i") as break_5, consider_break_5, date_format(break_6,"%H:%i") as break_6, consider_break_6, half_day,date_format(clock_in, "%Y-%m-%d") as search_date, s.extra_ot, date_format(s.extra_ot_worked_hours_more_than, "%H:%i") as extra_ot_worked_hours_more_than, date_format(s.extra_ot_hours, "%H:%i") as extra_ot_hours, date_format(extra_break_1,"%H:%i") as extra_break_1, date_format(extra_break_2,"%H:%i") as extra_break_2, date_format(extra_break_3,"%H:%i") as extra_break_3, date_format(extra_break_4,"%H:%i") as extra_break_4, date_format(extra_break_5,"%H:%i") as extra_break_5, date_format(extra_break_6,"%H:%i") as extra_break_6, extra_break, date_format(extra_break_worked_hours_more_than, "%H:%i") as extra_break_worked_hours_more_than', false)->from($clockings_table . ' c')->join('shifts s', 'c.shift_id = s.id', 'left')->join('remarks r', 'r.remark_date = date(clock_in) and r.employee_id = c.employee_id', 'left')->where('clock_in >', $first_day . ' 00:00:00')->where('clock_in <', $last_day . ' 23:59:59')->where_in('c.employee_id', $employees)->order_by('clock_in_o')->get()->result();

  return $result;
}

function get_result_list_overnight($employees, $first_day, $last_day)
{
  $clockings_table = get_clockings_table_name($first_day);
  $first_day = date('Y-m-d', strtotime('-1 day', strtotime($first_day)));
  $ci = &get_instance();

  $company_id = get_user()["company_id"];
  $interval_minutes = get_interval_minutes($company_id);

  $result = $ci->db->select('c.employee_id,c.id,date_format(date_sub(clock_in, interval ' . $interval_minutes . ' minute),"%d/%m %a") as day_f,clock_in as clock_in_o, date_format(clock_in,"%H:%i") as clock_in, date_format(clock_in,"%d-%m-%Y %H:%i") as clock_in_1,date_format(clock_out,"%H:%i") as clock_out,date_format(clock_out,"%d-%m-%Y %H:%i") as clock_out_1,clock_in_id,clock_out_id,s.grace_time as grace_time_o, date_format(s.end_time,"%H:%i") as end_time, date_format(s.grace_time,"%H:%i") as grace_time, s.start_time as start_time_o, date_format(s.start_time, "%H:%i") as start_time, s.name,s.code,reason,c.remark,date_format(end_time,"%H:%i") as end_time,date_format(overtime_starts,"%H:%i") as overtime_starts,date_format(early_ot_start,"%H:%i") as early_ot_start,date_format(early_ot_end,"%H:%i") as early_ot_end,time_format(timediff(end_time,start_time),"%H:%i") as shift_hours, fixed_ot, fixed_overtime, auto_approve_ot, r.remark as shift_remark, is_leave,void_late_in,void_early_out, date_format(break_duration,"%H:%i") as break_duration, date_format(break_1,"%H:%i") as break_1, consider_break_1, date_format(break_2,"%H:%i") as break_2, consider_break_2, date_format(break_3,"%H:%i") as break_3, consider_break_3, date_format(break_4,"%H:%i") as break_4, consider_break_4, date_format(break_5,"%H:%i") as break_5, consider_break_5, date_format(break_6,"%H:%i") as break_6, consider_break_6, half_day,date_format(date_sub(clock_in, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, s.extra_ot, date_format(s.extra_ot_worked_hours_more_than, "%H:%i") as extra_ot_worked_hours_more_than, date_format(s.extra_ot_hours, "%H:%i") as extra_ot_hours, date_format(extra_break_1,"%H:%i") as extra_break_1, date_format(extra_break_2,"%H:%i") as extra_break_2, date_format(extra_break_3,"%H:%i") as extra_break_3, date_format(extra_break_4,"%H:%i") as extra_break_4, date_format(extra_break_5,"%H:%i") as extra_break_5, date_format(extra_break_6,"%H:%i") as extra_break_6, extra_break, date_format(extra_break_worked_hours_more_than, "%H:%i") as extra_break_worked_hours_more_than', false)->from($clockings_table . ' c')->join('shifts s', 'c.shift_id = s.id', 'left')->join('remarks r', 'r.remark_date = date(date_sub(clock_in, interval ' . $interval_minutes . ' minute)) and r.employee_id = c.employee_id', 'left')->where('date(date_sub(clock_in, interval ' . $interval_minutes . ' minute)) >=', $first_day)->where('date(date_sub(clock_in, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('c.employee_id', $employees)->order_by('clock_in_o')->get()->result();

  return $result;
}

function get_result_list_basic($employees, $first_day, $last_day)
{
  $first_day = date('Y-m-d', strtotime('-1 day', strtotime($first_day)));
  $ci = &get_instance();
  return $ci->db->select('employee_id,clock_in as clock_in_o, date_format(clock_in,"%H:%i") as clock_in, date_format(clock_in,"%d-%m-%Y %H:%i") as clock_in_1,date_format(clock_out,"%H:%i") as clock_out,date_format(clock_out,"%d-%m-%Y %H:%i") as clock_out_1,date_format(clock_in, "%Y-%m-%d") as search_date, clock_in_id, clock_out_id', false)->from('clockings')->where('date(clock_in) >=', $first_day)->where('date(clock_in) <=', $last_day)->where_in('employee_id', $employees)->order_by('clock_in_o')->get()->result();
}

function get_result_list_overnight_basic($employees, $first_day, $last_day)
{
  $clockings_table = get_clockings_table_name($first_day);
  $company_id = get_user()["company_id"];
  $interval_minutes = get_interval_minutes($company_id);
  $first_day = date('Y-m-d', strtotime('-1 day', strtotime($first_day)));
  $ci = &get_instance();
  return $ci->db->select('employee_id,date_format(date_sub(clock_in, interval ' . $interval_minutes . ' minute),"%d/%m %a") as day_f,clock_in as clock_in_o, date_format(clock_in,"%H:%i") as clock_in, date_format(clock_in,"%d-%m-%Y %H:%i") as clock_in_1,date_format(clock_out,"%H:%i") as clock_out,date_format(clock_out,"%d-%m-%Y %H:%i") as clock_out_1,date_format(date_sub(clock_in, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, clock_in_id, clock_out_id', false)->from($clockings_table . ' c')->where('date(date_sub(clock_in, interval ' . $interval_minutes . ' minute)) >=', $first_day)->where('date(date_sub(clock_in, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $employees)->order_by('clock_in_o')->get()->result();
}

function get_is_ot_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('id, is_ot, ot_date as date')->from('ot_days')->where('ot_date >=', $first_day)->where('ot_date <=', $last_day)->where('employee_id', $id)->get()->result();
}

function get_is_late_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('id, is_late, late_date as date')->from('late_days')->where('late_date >=', $first_day)->where('late_date <=', $last_day)->where('employee_id', $id)->get()->result();
}

function get_is_late_break_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('id, is_late_break, late_break_date as date')->from('late_break_days')->where('late_break_date >=', $first_day)->where('late_break_date <=', $last_day)->where('employee_id', $id)->get()->result();
}

function get_is_early_out_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('id, is_early_out, early_out_date as date')->from('early_out_days')->where('early_out_date >=', $first_day)->where('early_out_date <=', $last_day)->where('employee_id', $id)->get()->result();
}

function get_manual_late_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('late_hours,date')->from('manual_late')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_late_break_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('late_hours_break,date')->from('manual_late_break')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_shift_list($id, $first_day, $last_day)
{
  $first_day = date('Y-m-d', strtotime('-1 day', strtotime($first_day)));
  $last_day = date('Y-m-d', strtotime('+1 day', strtotime($last_day)));
  $ci = &get_instance();
  $result = $ci->db->select(
    "s.id,round_off_ot,s.name,acting_code,code,overnight,same_day_overnight,s.half_day,is_leave,is_paid,void_late_in,void_early_out,date,end_time, is_approved,timediff(end_time,grace_time) as full_time, timediff(timediff(end_time,grace_time) ,break_duration) as work_time, is_rest_day,
    weekday_deduction, weekend_deduction, public_holiday_deduction,
    TIME_FORMAT(CASE
    WHEN overnight = 'No' OR (overnight = 'Yes' AND same_day_overnight = 'same')
    THEN TIMEDIFF(TIMEDIFF(`end_time`, `start_time`), `break_duration`)
    ELSE
    TIMEDIFF(TIMEDIFF(CONCAT(DATE_ADD(`date`, interval 1 DAY), ' ', `end_time`),
    CONCAT(`date`, ' ', `start_time`)), break_duration)
    END, '%H:%i:%s') AS shift_hours, auto_approve_ot, break_duration, break_1, consider_break_1, break_2, consider_break_2, break_3, consider_break_3, break_4, consider_break_4, break_5, consider_break_5, break_6, consider_break_6,
    start_time, extra_ot, date_format(extra_ot_worked_hours_more_than, '%H:%i') extra_ot_worked_hours_more_than, date_format(extra_ot_hours, '%H:%i') extra_ot_hours,
    if_ot_more_than, deduct_from_ot, max_ot_hours, coalesce(s.cut_off_time, c.cut_off_time, '07:00:00') as cut_off_time"
  )->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->join('companies c', 'c.id = s.company_id')->where('FIND_IN_SET(' . $id . ',employees)>', 0)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();


  $result = calculate_break_and_shift_hours($result);

  return $result;
}

function get_shift_list_basic($id, $first_day, $last_day)
{
  $first_day = date('Y-m-d', strtotime('-1 day', strtotime($first_day)));
  $ci = &get_instance();
  return $ci->db->select('overnight, date, half_day, is_paid')->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->where('FIND_IN_SET(' . $id . ',employees)>', 0)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_remark_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('remark, remark_date as date')->from('remarks')->where('remark_date >=', $first_day)->where('remark_date <=', $last_day)->where('employee_id', $id)->get()->result();
}

function get_manual_ot_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('overtime,type,date')->from('manual_ot')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_ta_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('value,date')->from('manual_ta')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_ma_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('value,date')->from('manual_ma')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_ca_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('value,date')->from('manual_ca')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_spa_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('value,date')->from('manual_spa')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_aca_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('value,date')->from('manual_aca')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_fl_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('value,date')->from('manual_fl')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_cw_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('value,date')->from('manual_cw')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_mo_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('value,date')->from('manual_mo')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_shift1_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('value,date')->from('manual_shift1')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_shift2_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('value,date')->from('manual_shift2')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_shift3_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('value,date')->from('manual_shift3')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_early_out_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('early_out,date')->from('manual_early_out')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_short_hours_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('short_hours,date')->from('manual_short_hours')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_trip_a_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('no_of_trips, date')->from('trips')->where('employee_id', $id)->where('type', "a")->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_trip_b_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('no_of_trips, date')->from('trips')->where('employee_id', $id)->where('type', "b")->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_trip_a_total($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('coalesce(sum(no_of_trips), 0) as total')->from('trips')->where('date >=', $first_day)->where('date <=', $last_day)->where('type', "a")->where('employee_id', $id)->get()->row()->total;
}

function get_trip_b_total($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('coalesce(sum(no_of_trips), 0) as total')->from('trips')->where('date >=', $first_day)->where('date <=', $last_day)->where('type', "b")->where('employee_id', $id)->get()->row()->total;
}

function get_lateness_time($late, $late_hours, $break_late_hours, $early_out, $short_hours, $inc_late_in, $inc_late_break, $inc_early_out, $inc_short_hours, $late_count)
{
  $current_day_late = "";
  $count_this_as_late = false;
  if ($inc_late_in) {
    $late = add_time($late, $late_hours);
    $count_this_as_late = check_if_time_exist($late_hours) || $count_this_as_late;
    $current_day_late = add_time($current_day_late, $late_hours);
  }
  if ($inc_late_break) {
    $late = add_time($late, $break_late_hours);
    $count_this_as_late = check_if_time_exist($break_late_hours) || $count_this_as_late;
    $current_day_late = add_time($current_day_late, $break_late_hours);
  }
  if ($inc_early_out) {
    $late = add_time($late, $early_out);
    $count_this_as_late = check_if_time_exist($early_out) || $count_this_as_late;
    $current_day_late = add_time($current_day_late, $early_out);
  }
  if ($inc_short_hours) {
    $late = add_time($late, $short_hours);
    $count_this_as_late = check_if_time_exist($short_hours) || $count_this_as_late;
    $current_day_late = add_time($current_day_late, $short_hours);
  }
  if ($count_this_as_late) {
    $late_count++;
  }

  return array($late, $late_count, $current_day_late);
}

function void_late_minutes($late, $void_minutes)
{
  if ($late == "" || $late == "00:00") {
    return $late;
  }
  $hour_minutes = explode(":", $late);
  $late_minutes = ($hour_minutes[0] * 60) + $hour_minutes[1];
  // echo $late_minutes. " - ". $void_minutes;
  // die();
  if ($late_minutes < $void_minutes) {
    return "00:00";
  }

  return $late;
}

function check_if_time_exist($time)
{
  if ($time != "" && $time != "00:00") {
    return true;
  }
  return false;
}

function set_sql_mode()
{
  $ci = &get_instance();
  $ci->db->query('SET SESSION sql_mode = REPLACE(REPLACE(REPLACE(@@sql_mode,"ONLY_FULL_GROUP_BY,",""),",ONLY_FULL_GROUP_BY",""),"ONLY_FULL_GROUP_BY","")');
}

function get_company_ot_settings($cid)
{
  $ci = &get_instance();
  return $ci->db->select('start, end, round_to, first_hour, branch_id')->from('ot_round_settings o')->join('branches b', 'b.id = o.branch_id')->where('b.company_id', $cid)->get()->result();
}

function get_ot_settings($bid)
{
  $ci = &get_instance();
  return $ci->db->select('start, end, round_to, first_hour')->from('ot_round_settings')->where('branch_id', $bid)->get()->result();
}

function get_company_early_ot_settings($cid)
{
  $ci = &get_instance();
  return $ci->db->select('start, end, round_to, branch_id')->from('early_ot_round_settings o')->join('branches b', 'b.id = o.branch_id')->where('b.company_id', $cid)->get()->result();
}

function get_early_ot_settings($bid)
{
  $ci = &get_instance();
  return $ci->db->select('start, end, round_to')->from('early_ot_round_settings')->where('branch_id', $bid)->get()->result();
}

function get_late_in_settings($bid)
{
  $ci = &get_instance();
  return $ci->db->select('start, end, round_to')->from('late_in_round_settings')->where('branch_id', $bid)->get()->result();
}
function get_late_break_settings($bid)
{
  $ci = &get_instance();
  return $ci->db->select('start, end, round_to')->from('late_break_round_settings')->where('branch_id', $bid)->get()->result();
}
function get_early_out_settings($bid)
{
  $ci = &get_instance();
  return $ci->db->select('start, end, round_to')->from('early_out_round_settings')->where('branch_id', $bid)->get()->result();
}

/**
 * Main function to calculate overtime
 *
 * @param array $result array of raw clockings from result list functions
 * @param array $clockings Clockings from the view
 * @param string $date_f formatted date in dd-mm-yyyy format
 * @param bool $overnight is overnight shift
 * @param bool $apply_overtime should apply overtime? This is employee's is ot setting
 * @param bool $apply_early_overtime should apply early overtime? This is employee's is early ot setting
 * @param string $work_hours hours employee has worked that day excluding breaks
 * @param string $company_working_hours company working hours
 * @param string $ot_type type of overtime set in OT settings
 * @param string $ot_round should round OT from OT settings? It's bool in string format
 * @param string $round_first_hour_only should round only first hour of OT? It's bool in string format
 * @param string $round_by_exact_hour should round by exact hour? It's bool in string format
 * @param array $ot_settings list of OT round settings
 * @param string $shift_hours shift hours in hh:mm format
 * @param string $round_off_ot round off OT? It's bool in string format
 * @param int|float $company_working_hours_decimal Company working hours in decimal format
 * @param string $early_ot_round should round early OT? It's bool in string format
 * @param array $early_ot_settings list of early OT round settings
 *
 * @return string 
 */
function calculate_final_overtime($result, $clockings, $date_f, $overnight, $apply_overtime, $apply_early_overtime, $work_hours, $company_working_hours, $ot_type, $ot_round, $round_first_hour_only, $round_by_exact_hour, $different_first_hour_rounding, $ot_settings, $shift_hours, $round_off_ot, $company_working_hours_decimal, $early_ot_round, $early_ot_settings)
{

  $ci = &get_instance();
  $company_id = $ci->session->userdata('antelope_user')['company_id'];

  $overtime = "";
  $early_overtime = "";
  if ($result) {
    $v = $result[0];

    if ($v->fixed_ot != 'Y') {

      if (($v->is_leave != "" && $v->is_leave != "yes") || $ot_type == "eight_hours"){
        if ($ot_type == "default") {
          if ($ot_round == 1 && $round_off_ot == 1 && $round_by_exact_hour == 1) {
            $clockings = round_by_exact_hour($clockings, $ot_settings);
          }
          foreach ($clockings as $clock) {
            if ($apply_overtime) {
              $overtime = calculate_overtime($overtime, $clock->clock_in_1, $clock->clock_out_1, $clock->overtime_starts, $date_f, $overnight);
            }
            if ($apply_early_overtime) {
              $early_overtime = calculate_early_overtime($early_overtime, $clock->clock_in_1, $clock->clock_out_1, $clock->early_ot_start, $clock->early_ot_end, $date_f, $overnight);
            }
          }
        } else if ($ot_type == "weekly_hours") {
          if ($shift_hours != "") {
            if ($apply_overtime) {
              $overtime = calculate_weekly_ot($work_hours, $shift_hours);
            }
          } else {
            $overtime = "";
          }
        } else if ($ot_type == "monthly_ot") {
          if ($apply_overtime) {
            $overtime = calculate_monthly_ot($work_hours, $company_working_hours_decimal);
          }
        } else {
          // Eight hours OT should also be only calculated when apply_overtime is true
          if ($apply_overtime) {
            $overtime = calculate_8hours_ot($work_hours, $company_working_hours);
          }
        }
        if ($ot_round == 1 && $round_off_ot == 1) {
          $normal_ot_settings = [];
          foreach ($ot_settings as $o) {
            if ($o->first_hour == 0) {
              $normal_ot_settings[] = $o;
            }
          }
          $first_hour_ot_settings = [];
          foreach ($ot_settings as $o) {
            if ($o->first_hour == 1) {
              $first_hour_ot_settings[] = $o;
            }
          }
          if ($overtime < "01:00" && $different_first_hour_rounding == 1) {
            $overtime = round_off_ot($overtime, $first_hour_ot_settings, $round_first_hour_only);
          } else {
            $overtime = round_off_ot($overtime, $normal_ot_settings, $round_first_hour_only);
          }
        } else if ($round_off_ot == 0 && $ot_round == 1 && $company_id == 39) {
          $default_setting = new stdClass();
          $default_setting->start = 1;
          $default_setting->end = 29;
          $default_setting->round_to = 0;
          $ot_settings = array($default_setting);
          $overtime = round_off_ot($overtime, $ot_settings, $round_first_hour_only);
        }
        if($early_ot_round){
          $early_overtime = round_off_ot($early_overtime, $early_ot_settings, 0);
        }
        $overtime = add_time($overtime, $early_overtime);
      }
    } else if ($apply_overtime) {
      if ($v->is_leave != "" && $v->is_leave != "yes") {
        $formatted_ot = $v->fixed_overtime;
        if ($formatted_ot == "00:00:00") {
          $formatted_ot = "";
        } else {
          $formatted_ot = explode(":", $formatted_ot);
          unset($formatted_ot[2]);
          $formatted_ot = implode(":", $formatted_ot);
        }
        $overtime = $formatted_ot;
      }
    }
    $overtime = calculate_extra_ot($overtime, $work_hours, $v->extra_ot, $v->extra_ot_worked_hours_more_than, $v->extra_ot_hours);
    return $overtime;
  } else {
    return "";
  }
}

function calculate_8hours_ot($work_hours, $company_working_hours)
{
  if ($work_hours == "" || $work_hours == "00:00") {
    return "";
  }
  if (!empty($company_working_hours) && $company_working_hours != "00:00") {
    $company_working_hours = DateTime::createFromFormat('H:i', $company_working_hours);
    $work_hours = DateTime::createFromFormat('H:i', $work_hours);
    if ($work_hours == false || $company_working_hours == false) {
      return "";
    }
    if ($work_hours > $company_working_hours) {
      $interval = date_diff($work_hours, $company_working_hours);
      $days = $interval->format('%a');
      $format = $interval->format('%H:%i');
      $format = explode(":", $format);
      $format[0] = $format[0] + ($days * 24);
      $format[0] = sprintf("%02d", $format[0]);
      $format[1] = sprintf("%02d", $format[1]);
      $format = implode(":", $format);
      return $format;
    } else {
      return "";
    }
  } else {
    return "";
  }
}

function round_off_ot($overtime, $ot_settings, $round_first_hour_only)
{
  if ($overtime == "" || $overtime == "00:00") {
    return "";
  }

  $overtime = explode(":", $overtime);
  if (count($overtime) == 2) {
    if ($round_first_hour_only == 0) {
      foreach ($ot_settings as $o) {
        if ($overtime[1] >= $o->start && $overtime[1] <= $o->end) {
          if ($o->round_to == 60) {
            $overtime[0]++;
            $overtime[1] = "00";
          } else {
            $overtime[1] = $o->round_to;
          }

          break;
        }
      }
    } else if ($overtime[0] == "00") {
      foreach ($ot_settings as $o) {
        if ($overtime[1] >= $o->start && $overtime[1] <= $o->end) {
          if ($o->round_to == 60) {
            $overtime[0]++;
            $overtime[1] = "00";
          } else {
            $overtime[1] = $o->round_to;
          }

          break;
        }
      }
    }

    $overtime[0] = sprintf('%02d', $overtime[0]);
    $overtime[1] = sprintf('%02d', $overtime[1]);
    $overtime = implode(":", $overtime);
    if ($overtime == "00:00") $overtime = "";
    return $overtime;
  } else {
    return "";
  }
}
function round_off_late_in($late, $late_in_settings, $round_first_hour_only)
{
  if ($late == "" || $late == "00:00") {
    return "";
  }

  $late_minutes = time_to_minutes($late);
  foreach ($late_in_settings as $o) {
    if ($late_minutes >= $o->start && $late_minutes <= $o->end) {
      $late_minutes = $o->round_to;
      break;
    }
  }

  $late = minutes_to_time($late_minutes);
  if($late == "00:00") $late = "";
  return $late;
}
function round_off_late_break($late, $late_break_settings, $round_first_hour_only)
{
  if ($late == "" || $late == "00:00") {
    return "";
  }

  $late_minutes = time_to_minutes($late);
  foreach ($late_break_settings as $o) {
    if ($late_minutes >= $o->start && $late_minutes <= $o->end) {
      $late_minutes = $o->round_to;
      break;
    }
  }

  $late = minutes_to_time($late_minutes);
  if($late == "00:00") $late = "";
  return $late;
}
function round_off_early_out($late, $early_out_settings, $round_first_hour_only)
{
  if ($late == "" || $late == "00:00") {
    return "";
  }

  $late_minutes = time_to_minutes($late);
  foreach ($early_out_settings as $o) {
    if ($late_minutes >= $o->start && $late_minutes <= $o->end) {
      $late_minutes = $o->round_to;
      break;
    }
  }

  $late = minutes_to_time($late_minutes);
  if($late == "00:00") $late = "";
  return $late;
}

function calculate_days_from_clockings($result, $days_settings)
{
  $formatted_data = array();
  $work_hours = "00:00";
  foreach ($result as $key => $value) {
    // $value->total_time = total_time($value->clock_in_1, $value->clock_out_1);
    if(!isset($value->start_time)) $value->start_time = "";
    if(!isset($value->early_ot_start)) $value->early_ot_start = "";
    if(!isset($value->early_ot_end)) $value->early_ot_end = "";
    $value->total_time = calculate_total_hours($value->clock_in_1, $value->clock_out_1, $value->start_time, $value->early_ot_start, $value->early_ot_end, $value->search_date);
    $formatted_data[] = $value;
    if (array_key_exists($key + 1, $result)) {
      $x = new stdClass();
      $x->clock_in = $value->clock_out;
      $x->clock_in_1 = $value->clock_out_1;
      $x->clock_out = $result[$key + 1]->clock_in;
      $x->clock_out_1 = $result[$key + 1]->clock_in_1;
      $x->total_time = total_time($result[$key + 1]->clock_in_1, $value->clock_out_1);
      $formatted_data[] = $x;
    }
  }

  foreach ($formatted_data as $key => $value) {
    if ($key % 2 == 0) {
      $work_hours = add_time($work_hours, $value->total_time);
    }
  }

  return calculate_days($work_hours, $days_settings);
}

function get_auto_count_data($employee, $month, $year, $cid)
{
  $max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year);

  $first_day = sprintf("%04d-%02d-%02d", $year, $month, 1);
  $last_day = sprintf("%04d-%02d-%02d", $year, $month, $max_date);

  $data = calculate_summary_data($employee->id, $first_day, $last_day, "autocount", false, false, false, false, false, false, false, false, false, $cid);

  $auto_count_data = new stdClass();
  $auto_count_data->late = toDecimal($data["lateness_time_deducted"]);
  $auto_count_data->late_count = $data["late_count"];
  $auto_count_data->unpaid_leaves = $data["unpaid_leaves"];
  $auto_count_data->absent_days = $data["absent_days"];
  $auto_count_data->overtime = toDecimal($data["month_overtime_deducted"]);
  $auto_count_data->overtime_rd = toDecimal($data["month_overtime_rd"]);
  $auto_count_data->overtime_ph = toDecimal($data["month_overtime_ph"]);
  $auto_count_data->worked_holiday = $data["worked_holidays"];
  $auto_count_data->worked_rest_day = $data["worked_rest_days"];
  $auto_count_data->worked_off_day = $data["worked_off_days"];

  $auto_count_data->per_day = round($employee->basic_wage / $max_date, 2);

  $per_hour_late = round($employee->basic_wage / $max_date, 2);
  $per_hour_late = round($per_hour_late / 8, 2);

  $auto_count_data->per_hour_late = $per_hour_late;

  $per_hour = round($employee->basic_wage / 26, 2);
  $per_hour = round($per_hour / 8, 2);

  $auto_count_data->per_hour = $per_hour;

  $auto_count_data->per_day_worked = round($employee->basic_wage / 26, 2);

  $auto_count_data->month_days = $max_date;

  return $auto_count_data;
}

function deduct_from_ot($overtime, $lateness_time, $deduction_date, $last_day)
{
  $date = explode("-", $last_day);
  $month = $date[1];
  $year = $date[0];
  $max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year);
  if ($max_date < $deduction_date) {
    $deduction_date = $max_date;
  }
  $deduction_date = sprintf("%04d-%02d-%02d", $year, $month, $deduction_date);
  $current_date = date("Y-m-d");
  if (!is_minus($overtime) && $current_date >= $deduction_date) {
    $total = add_time_minus($overtime, "-" . $lateness_time);
    if (is_minus($total)) {
      $overtime = "00:00";
      $lateness_time = str_replace("-", "", $total);
    } else {
      $lateness_time = "00:00";
      $overtime = $total;
    }
  }


  return array($overtime, $lateness_time);
}

function to_html_date($date)
{
  $newDate = DateTime::createFromFormat('Y-m-d', $date);
  return $newDate->format('d/m/Y');
}

function get_manual_late_list_all($employees, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('employee_id,late_hours,date')->from('manual_late')->where_in('employee_id', $employees)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_late_list_by_id($list, $id)
{
  $result = array();
  foreach ($list as $l) {
    if ($l->employee_id == $id) {
      $result[] = $l;
    }
  }
  return $result;
}

function get_manual_late_break_list_all($employees, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('employee_id,late_hours_break,date')->from('manual_late_break')->where_in('employee_id', $employees)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_late_break_list_by_id($list, $id)
{
  $result = array();
  foreach ($list as $l) {
    if ($l->employee_id == $id) {
      $result[] = $l;
    }
  }
  return $result;
}

function get_manual_early_out_list_all($employees, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('employee_id,early_out,date')->from('manual_early_out')->where_in('employee_id', $employees)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_early_out_list_by_id($list, $id)
{
  $result = array();
  foreach ($list as $l) {
    if ($l->employee_id == $id) {
      $result[] = $l;
    }
  }
  return $result;
}

function remove_last_ids($result, $last_ids)
{
  $final_result = array();
  foreach ($result as $value) {
    if (!in_array($value->id, $last_ids)) {
      $final_result[] = $value;
    }
  }
  return $final_result;
}

function remove_duplicate_clockings($result, $date, $shift_list, $result_list_overnight)
{
  $prev_date = date('Y-m-d', strtotime('-1 day', strtotime($date)));
  $prev_shift = search_from_list($shift_list, $prev_date);
  $final_result = $result;
  if ($prev_shift && $prev_shift->overnight == "Yes") {
    $final_result = array();
    $result2 = search_clocking($result_list_overnight, $prev_date);
    $current_shift = search_from_list($shift_list, $date);
    // if ($current_shift && $current_shift->overnight == "No") {
      $result2 = remove_next_day_clockings($result2, $prev_shift, $current_shift);
    // }
    $result2_ids = array();
    foreach ($result2 as $r2) {
      $result2_ids[] = $r2->id;
    }
    foreach ($result as $r) {
      if (!in_array($r->id, $result2_ids) && (!isset($r->unused) || $r->unused == false)) {
        $final_result[] = $r;
      }
    }
  }
  return $final_result;
}

function count_early_out($date, $result, $overnight, $half_day, $manual_early_out_list, $shift_check)
{
  $early_out = "00:00";
  $formatted_data = array();
  $obj = new stdClass();
  $last_out = "";
  foreach ($result as $key => $value) {
    if (array_key_exists($key + 1, $result)) {
    } else {
      $last_out = $value->clock_out_1;
    }
  }
  if (!$half_day) {
    $manual_early_out = search_from_list($manual_early_out_list, $date);
    if ($manual_early_out) {
      $early_out = $manual_early_out->early_out;
    } else if ($last_out != "" && $shift_check && $shift_check->void_early_out == "No") {
      $early_out = calculate_early_out($last_out, $shift_check->end_time, $date, $overnight);
    }
  }

  $early_out = ($early_out == "00:00" || $early_out == "") ? "-" : $early_out;

  return $early_out;
}

function count_late($date, $result, $overnight, $half_day, $manual_late_list, $same_day_overnight)
{
  $formatted_data = array();
  $obj = new stdClass();
  $late_hours = "00:00";
  foreach ($result as $key => $value) {

    $formatted_data[] = $value;
    if (array_key_exists($key + 1, $result)) {
      $x = new stdClass();
      $x->grace_time = $value->grace_time;
      $x->clock_in = $value->clock_out;
      $x->clock_in_1 = $value->clock_out_1;
      $formatted_data[] = $x;
    }
  }

  $obj->clockings = $formatted_data;
  if ($result) {
    $v = $result[0];
  }
  $breaks_array = array();
  foreach ($obj->clockings as $key => $value) {

    if ($key == 0) {
      $manual_late = search_from_list($manual_late_list, $date);
      if ($manual_late) {
        $late_hours = $manual_late->late_hours;
      } else if (isset($v) && $v->is_leave != "" && $v->is_leave != "yes" && $v->void_late_in == "No") {
        if ($v->grace_time != "") {
          if ($overnight) {
            $grace_time = $date . " " . $v->grace_time . ":00";
            $grace_time_stamp = strtotime($grace_time);
            $mid_day = $date . " 12:00:00";
            $mid_day_stamp = strtotime($mid_day);
            if (in_array($same_day_overnight, ['default', 'next'])) {
              if ($mid_day_stamp > $grace_time_stamp) {
                $grace_time_stamp += 24 * 3600;
              }
            }
            $clock_in_stamp = strtotime($v->clock_in_o);


            if ($clock_in_stamp > $grace_time_stamp) {
              $late_stamp = $clock_in_stamp - $grace_time_stamp;
              date_default_timezone_set('UTC');
              $late_hours = date('H:i', $late_stamp);
              date_default_timezone_set("Asia/Kuala_Lumpur");
            }
          } else if (intval(str_replace(":", "", $v->clock_in)) > intval(str_replace(":", "", $v->grace_time))) {
            $late_hours = sub_time($v->clock_in, $v->grace_time);
          }
        }
      }
    }
    break;
  }

  $late_hours = $late_hours == "00:00" ? "-" : $late_hours;

  return $late_hours;
}


function count_late_break($date, $result, $overnight, $half_day, $manual_late_break_list, $is_shift, $ignore_breaks_after_endtime)
{
  $formatted_data = array();
  $obj = new stdClass();
  $break_hours = "00:00";
  $work_hours = "00:00";
  $break_late_hours = "00:00";
  foreach ($result as $key => $value) {

    $value->total_time = total_time($value->clock_in_1, $value->clock_out_1);



    $formatted_data[] = $value;
    if (array_key_exists($key + 1, $result)) {
      $x = new stdClass();
      $x->clock_in = $value->clock_out;
      $x->clock_out = $result[$key + 1]->clock_in;
      $x->total_time = total_time($result[$key + 1]->clock_in_1, $value->clock_out_1);
      $formatted_data[] = $x;
    }
  }



  $obj->clockings = $formatted_data;
  if ($result) {
    $v = $result[0];
  }

  $break_and_late_hours = calculate_break_and_late_hours($obj->clockings, $v);
  $work_hours = $break_and_late_hours->work_hours;
  $break_hours = $break_and_late_hours->break_hours;
  $breaks_array = $break_and_late_hours->breaks_array;
  $shift_break_hours = $break_and_late_hours->shift_break_hours;
  $shift_breaks_array = $break_and_late_hours->shift_breaks_array;


  $break_not_taken = "00:00";
  $extra_break_not_taken = "00:00";
  if (isset($v)) {
    $break_not_taken = calculate_break_not_taken($break_hours, $breaks_array, $v);
  }
  if ($work_hours != "" && $work_hours != "00:00") {
    $work_hours = sub_time($work_hours, $break_not_taken);
  }
  if (isset($v)) {
    $extra_break_not_taken = calculate_extra_break_not_taken($breaks_array, $v, $work_hours);
  }
  if ($work_hours != "" && $work_hours != "00:00") {
    $work_hours = sub_time($work_hours, $extra_break_not_taken);
  }

  if (isset($v) && !$half_day) {
    $manual_late_break = search_from_list($manual_late_break_list, $date);
    if ($manual_late_break) {
      $break_late_hours = $manual_late_break->late_hours_break;
    } else {
      if ($ignore_breaks_after_endtime == 0) {
        $break_late_hours = calculate_break_late($break_hours, $breaks_array, $v, $work_hours, $is_shift);
      } else {
        $break_late_hours = calculate_break_late($shift_break_hours, $shift_breaks_array, $v, $work_hours, $is_shift);
      }
    }
  }

  $break_late_hours = ($break_late_hours == "00:00" || $break_late_hours == "") ? "-" : $break_late_hours;

  return $break_late_hours;
}

function get_approved_ot_list($shift_ids, $first_day, $last_day)
{
  $ci = &get_instance();

  $shift_ids[] = 0;

  $result = $ci->db->select('shift_id, approve_date, is_approved')->from('auto_approve_days')->where_in('shift_id', $shift_ids)->where('approve_date >=', $first_day)->where('approve_date <=', $last_day)->get()->result();

  return $result;
}

function get_is_ot_status($approved_ot_list, $shift_check, $date, $id, $clocking_count = 0, $cid = 0)
{
  $ci = &get_instance();

  if ($shift_check && $shift_check->is_leave == "no" && $date <= date("Y-m-d")) {
    $shift_id = $shift_check->id;
    $status = get_approved_ot_status_by_shift_and_date($approved_ot_list, $shift_id, $date);
    if ($status) {
      $is_ot = $status->is_approved == "Yes" ? true : false;
      if ($status->is_approved == "Yes") {
        $ot_data = array(
          'employee_id' => $id,
          'ot_date' => $date,
          'is_ot' => 'Y'
        );
        $ci->db->replace('ot_days', $ot_data);
      }
    } else {
      $auto_approve_ot = $shift_check->auto_approve_ot;
      $is_ot = $auto_approve_ot == "Yes" ? true : false;
      $approve_data = array(
        'shift_id' => $shift_id,
        'approve_date' => $date,
        'is_approved' => $auto_approve_ot
      );
      $ci->db->replace('auto_approve_days', $approve_data);
      if ($auto_approve_ot == "Yes") {
        $ot_data = array(
          'employee_id' => $id,
          'ot_date' => $date,
          'is_ot' => 'Y'
        );
        $ci->db->replace('ot_days', $ot_data);
      }
    }
    return $is_ot;
  } else if ((!isset($shift_check) || empty($shift_check)) && $date <= date("Y-m-d") && $clocking_count != 0) {
    if ($cid != 39) {
      $is_ot = true;
      $ot_data = [
        'employee_id' => $id,
        'ot_date' => $date,
        'is_ot' => 'Y',
      ];
      $ci->db->replace('ot_days', $ot_data);
      return $is_ot;
    }
    return false;
  } else {
    return false;
  }
}


function get_approved_ot_status_by_shift_and_date($approved_ot_list, $shift_id, $date)
{
  $result = array();
  foreach ($approved_ot_list as $a) {
    if ($a->shift_id == $shift_id && $a->approve_date == $date) {
      $result = $a;
    }
  }
  return $result;
}

/**
 * Gets branch id and returns sql record from the branches table
 *
 * @param int $branch_id
 * @return array
 */
function get_sql_data($branch_id)
{
  $CI = &get_instance();

  $sql_data = $CI->db->select('*')
    ->from('branches')->where('id', $branch_id)->get()->row_array();
  return $sql_data;
}

function add_days_to_date($date, $days)
{
  $date = strtotime($date->format("Y-m-d"));
  $date = strtotime("+$days day", $date);
  return DateTime::createFromFormat("Y-m-d", date("Y-m-d", $date));
}

function get_employee_bank_id($banks, $bank_name)
{
  $id = null;
  foreach ($banks as $bank) {
    if ($bank->name == trim($bank_name)) {
      $id = $bank->id;
      break;
    }
  }
  return $id;
}

function calculate_total_hours($clock_in, $clock_out, $shift_start, $early_ot_start, $early_ot_end, $date)
{
  $dmyDate = implode("-", array_reverse(explode("-", $date)));

  $is_count_early = empty($early_ot_start) || empty($early_ot_end);

  $clock_in_time = DateTime::createFromFormat('d-m-Y H:i', $clock_in);
  $shift_start_time = DateTime::createFromFormat('d-m-Y H:i', $dmyDate . " " . $shift_start);

  $is_in_after_start_time = $clock_in_time <= $shift_start_time;

  if ($is_in_after_start_time && $is_count_early) {
    return total_time($dmyDate . " " . $shift_start, $clock_out);
  } else {
    return total_time($clock_in, $clock_out);
  }
}

/**
 * Get an array of shifts, add shift_hours and total_break in it
 *
 * @param array $list
 * @return array
 */
function calculate_break_and_shift_hours($list)
{
  $shift_list = array_map(function ($shift) {
    if (!empty($shift->break_duration)) {
      $shift->total_break = beautify_time($shift->break_duration);
      $shift->shift_hours = beautify_time($shift->shift_hours);
      return $shift;
    }

    $total_break_time = "00:00";
    if ($shift->consider_break_1 == 1)
      $total_break_time = add_time($total_break_time, $shift->break_1);
    if ($shift->consider_break_2 == 1)
      $total_break_time = add_time($total_break_time, $shift->break_2);
    if ($shift->consider_break_3 == 1)
      $total_break_time = add_time($total_break_time, $shift->break_3);
    if ($shift->consider_break_4 == 1)
      $total_break_time = add_time($total_break_time, $shift->break_4);
    if ($shift->consider_break_5 == 1)
      $total_break_time = add_time($total_break_time, $shift->break_5);
    if ($shift->consider_break_6 == 1)
      $total_break_time = add_time($total_break_time, $shift->break_6);

    $shift->total_break = $total_break_time;
    $shift_hours = "00:00";
    if ($shift->overnight == "No") {
      $shift_hours = sub_time($shift->end_time, $shift->start_time);
    } else {
      $start_time = DateTime::createFromFormat("H:i:s", $shift->start_time);
      $end_time = DateTime::createFromFormat("H:i:s", $shift->end_time);
      if ($start_time === false && $end_time === false) {
        $shift_hours == "00:00";
      } else {
        $end_time->modify("+1 day");

        $start_time = strtotime($start_time->format("Y-m-d H:i:s"));
        $end_time = strtotime($end_time->format("Y-m-d H:i:s"));
        $shift_hours = $end_time - $start_time;
        date_default_timezone_set('UTC');
        $shift_hours = date("H:i", $shift_hours);
        date_default_timezone_set("Asia/Kuala_Lumpur");
      }
    }

    $shift_hours = sub_time($shift_hours, $total_break_time);
    $shift->shift_hours = $shift_hours;

    return $shift;
  }, $list);

  return $shift_list;
}

function create_ot_rich_text($d)
{
  if ((empty($d->overtime) || $d->overtime == "00:00") && (empty($d->overtime_m) || $d->overtime_m == "00:00")) return "";

  $color = new PHPExcel_Style_Color('FFd9534f');
  $ot_value = new PHPExcel_RichText();

  if ($d->is_ot) {
    $m_ot = $ot_value->createTextRun(add_time_minus($d->overtime, $d->overtime_m));
  } else {
    if (!empty($d->overtime)) {
      $ot_n = $ot_value->createTextRun($d->overtime);
      $ot_n->getFont()->setStrikethrough(true);
      if ($d->is_extra_ot) $ot_n->getFont()->setColor($color);
      if (!empty($d->overtime_m)) {
        $ot_value->createText(", ");
      }
    }
    $m_ot = $ot_value->createTextRun($d->overtime_m);
  }

  if (isset($m_ot) && !empty($d->overtime_m) || $d->is_extra_ot)
    $m_ot->getFont()->setColor($color);
  return $ot_value;
}

function to_mysql_date($date)
{
  $newDate = DateTime::createFromFormat('d/m/Y', $date);
  return $newDate->format('Y-m-d');
}

function get_replacement_leaves_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  $result = $ci->db->query("SELECT *
    FROM `replacement_leave_dates`
    WHERE `employee_id` = '$id'
    AND ((`from` >= '$first_day' AND `from` <= '$last_day') OR (`to` >= '$first_day' AND `to` <= '$last_day'))
    AND `deleted_at` is null")->result();

  return $result;
}

function search_replacement_leave($list, $date)
{
  $array_list = json_decode(json_encode($list), true);

  $to_dates = array_column($array_list, "from");

  $result = array_search($date, $to_dates);

  if ($result !== false) return (object)$array_list[$result];

  return null;
}

function search_replacement_leave_to($list, $date)
{
  $array_list = json_decode(json_encode($list), true);

  $to_dates = array_column($array_list, "to");

  $result = array_search($date, $to_dates);

  if ($result !== false) return (object)$array_list[$result];

  return null;
}


function are_dates_in_same_month($date1, $date2)
{
  $date1 = DateTime::createFromFormat("Y-m-d", $date1);
  $date2 = DateTime::createFromFormat("Y-m-d", $date2);

  return $date1->format('Y-m') === $date2->format('Y-m');
}

function send_json_response($data, $status = 200)
{
  $ci = &get_instance();
  return $ci->output
    ->set_content_type("application/json")
    ->set_status_header($status)
    ->set_output(json_encode($data));
}

/**
 * Genaric function to generate summaries
 *
 * @param string $emp_id Employee ID
 * @param string $first_day Mysql format start date
 * @param string $last_day Mysql format end date
 * @param string $summary_type
 * @param StdClass|boolean $employee Employee object
 * @param array|boolean $result_list Clocking data
 * @param array|boolean $result_list_overnight Clocking data
 * @param array|boolean $company_working_hours Company working hours
 * @param array|boolean $public_holidays Company public holidays
 * @param array|boolean $ot_settings Company overtime settings
 * @param array|boolean $approved_ot_list Approved overtime list
 * @param array|boolean $rest_days Branch rest days
 * @param null|int|string $cid Company ID
 * @param array $worked_rest_days_array
 * @param array $worked_holidays_array
 * @param array $unpaid_leaves_absent_days
 * @return array
 */
function calculate_summary_data($emp_id, $first_day, $last_day, $summary_type = "summary", $employee = false, $result_list = false, $result_list_overnight = false, $company_working_hours = false, $public_holidays = false, $ot_settings = false, $early_ot_settings = false, $approved_ot_list = false, $rest_and_off_days = false, $cid = null, &$worked_rest_days_array = [], &$worked_off_days_array = [], &$worked_holidays_array = [], &$unpaid_leaves_absent_days = [], $clockings_news = null, $clockings_news_overnight = null, &$paid_leaves_array = [], &$daily_ot_array = [], &$daily_late_array = [])
{
  $companies_allowed_for_monthly_ot = companies_allowed_for_monthly_ot();
  $ci = &get_instance();
  $current_user = get_user();
  if (is_null($cid))
    $cid = $current_user["company_id"];

  if (is_null($cid) && $employee !== false)
    $cid = $employee->company_id;

  $data = [];
  // company ID (Goodnite International Sdn Bhd) for which custom In's Out's added in summary
  if ($cid == 223 || $cid == 259)
    $custom_in_outs = true;
  
  // TSF01 (146) company for which custom summary added in summary
  if ($cid == 146)
    $tsf_custom_summary = true;

  if ($employee === false)
    $employee = $ci->db->select('e.id as emp_id,first_name,special_id,d.name as department,p.title as position,is_ot,is_early_ot,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,round_by_exact_hour,different_first_hour_rounding,worked_hours_ot_rd,worked_hours_ot_ph,deduct_hour_ot_rd,deduct_hour_ot_ph,worked_hours_ot_off,deduct_hour_ot_off,ignore_breaks_after_endtime,inc_late_in,inc_late_break,inc_early_out,inc_short_hours,void_lateness_time_if_less_than,branch_id,deduct_from_ot,deduct_from_ot_single,deduction_date,min_worked_hours_meal,ta_rate,ma_rate,ca_rate,spa_rate,aca_rate,aa_rate,nsa_rate,fl_rate,cw_rate,mo_rate,shift1_rate,shift2_rate,shift3_rate,food_rate,b.name as branch,ot_group, att_all_code, att_all_desc, att_all_amount, is_att_all')->from('employees e')->join('departments d', 'd.id = e.department_id', 'left')->join('positions p', 'p.id = e.position_id', 'left')->join('branches b', 'b.id = e.branch_id', 'left')->where('e.id', $emp_id)->get()->row();
  
  if ($company_working_hours === false) {
    $company_working_hours = get_company_working_hours($cid);
  }
  
  $company_working_hours = get_employee_working_hours($company_working_hours, $emp_id);
  $company_half_hours = $company_working_hours->half_hours;
  $company_half_hours_decimal = toDecimal($company_half_hours);
  $company_working_hours = $company_working_hours->working_hours;
  $company_working_hours_decimal = toDecimal($company_working_hours);
  
  if ($rest_and_off_days === false) {
    $rest_and_off_days = $ci->db->select('rest_days,off_days')->from('branches')->where('id', $employee->branch_id)->get()->row();
    $rest_days = explode(",", $rest_and_off_days->rest_days);
    $off_days = explode(",", $rest_and_off_days->off_days);
  } else {
    $rest_days = explode(",", search_from_rest_days($rest_and_off_days, $employee->branch_id));
    $off_days = explode(",", search_from_off_days($rest_and_off_days, $employee->branch_id));
  }
  $public_holidays = get_public_holidays_mine($emp_id, $employee->branch_id, $first_day, $last_day);

  if ($summary_type === "summary")
    $public_holidays_names = get_public_holidays_with_name($employee->branch_id, $first_day, $last_day)[1];

  $apply_overtime = $employee->is_ot == 1 ? true : false;
  $apply_early_overtime = $employee->is_early_ot == 1 ? true : false;

  $inc_late_in = $employee->inc_late_in == 1 ? true : false;
  $inc_late_break = $employee->inc_late_break  == 1 ? true : false;
  $inc_early_out = $employee->inc_early_out == 1 ? true : false;
  $inc_short_hours = $employee->inc_short_hours == 1 ? true : false;
  $void_minutes = $employee->void_lateness_time_if_less_than;

  if ($employee->deduct_from_ot_single != "not_sure") {
    $deduct_from_ot = $employee->deduct_from_ot_single == "yes" ? true : false;
  } else {
    $deduct_from_ot = $employee->deduct_from_ot == 1 ? true : false;
  }

  $deduction_date = $employee->deduction_date;

  $employee->deduct_from_ot = $deduct_from_ot;

  $period = new DatePeriod(
    new DateTime($first_day),
    new DateInterval('P1D'),
    (new DateTime($last_day))->add(new DateInterval('P1D'))
  );

  $total = "00:00";
  $work = "00:00";
  $shift_hours_total = "00:00";
  $break = "00:00";
  $late = "00:00";
  $total_late = "00:00";
  $late_count = 0;
  $break_late = "00:00";
  $total_days = 0;
  $total_trip_a = 0;
  $total_trip_b = 0;
  $paid_leaves = 0;
  $unpaid_leaves = 0;
  $full_unpaid_leaves = 0;
  $allowance_leaves = 0;
  $total_holidays = 0;
  $worked_holidays = 0;
  $worked_rest_days = 0;
  $worked_off_days = 0;
  $worked_days = 0;
  $lsk_non_worked_days = 0;
  $working_days = 0;
  $absent_days = 0;
  $total_short = "00:00";
  $total_early = "00:00";
  $month_overtime = "00:00";
  $month_overtime_ph_x2 = "00:00";
  $month_overtime_ph_x3 = "00:00";
  $month_overtime_ph = "00:00";
  $month_overtime_rd = "00:00";
  $month_overtime_off = "00:00";
  $total_shift_hours = "00:00";
  // did today 29-07-2021
  $total_early_count = 0;
  $total_half_day_paid = 0;
  $total_half_day_unpaid = 0;
  $total_full_day_paid = 0;
  $total_medical_leaves = 0;
  $total_break_late = 0;
  $total_missing_in_out = 0;
  $total_absent_unpaid = 0;
  $total_early_late = 0;
  $total_off_days = 0;
  $total_late_only_count = 0;
  $total_rest_days_used = 0;

  $total_bmi_ot = 0;
  $total_bmi_ot_sunday = 0;
  $total_bmi_ph_1 = 0;
  $total_bmi_ph_2 = 0;
  $total_bmi_ta = 0;
  $total_bmi_ma = 0;
  $total_bmi_ca = 0;
  $total_bmi_spa = 0;
  $total_bmi_aca = 0;
  $total_bmi_fl = 0;
  $total_bmi_cw = 0;
  $total_bmi_mo = 0;
  $total_bmi_shift1 = 0;
  $total_bmi_shift2 = 0;
  $total_bmi_shift3 = 0;

  $bmi_attendance_allowance = true;
  $bmi_late_more_than_10 = 0;

  $gbr_attendance_allowance = true;
  $gbr_night_shifts = 0;

  $food_allowance_days = 0;

  $days_settings = $ci->db->select('from_hour,to_hour,days')->from('days_settings')->where('company_id', $cid)->get()->result();

  if ($result_list === false)
    $result_list = get_result_list(array($emp_id), $first_day, $last_day);
  if ($result_list_overnight === false)
    $result_list_overnight = get_result_list_overnight(array($emp_id), $first_day, $last_day);

  if ($approved_ot_list === false) {
    $shifts = $ci->db->select('id')->from('shifts')->where('branch_id', $employee->branch_id)->where('is_leave', 'no')->get()->result();
    $shift_ids = array(0);
    foreach ($shifts as $s) {
      $shift_ids[] = $s->id;
    }
    $approved_ot_list = get_approved_ot_list($shift_ids, $first_day, $last_day);
  }

  $is_ot_list = get_is_ot_list($emp_id, $first_day, $last_day);

  $is_offense_list = [];
  if ($summary_type === "merit_system")
    $is_offense_list = get_is_offense_list($emp_id, $first_day, $last_day);

  $is_late_list = get_is_late_list($emp_id, $first_day, $last_day);

  $is_late_break_list = get_is_late_break_list($emp_id, $first_day, $last_day);

  $is_early_out_list = get_is_early_out_list($emp_id, $first_day, $last_day);

  $manual_late_list = get_manual_late_list($emp_id, $first_day, $last_day);

  $manual_late_break_list = get_manual_late_break_list($emp_id, $first_day, $last_day);

  $shift_list = get_shift_list($emp_id, $first_day, $last_day);

  $remark_list = get_remark_list($emp_id, $first_day, $last_day);

  $manual_ot_list = get_manual_ot_list($emp_id, $first_day, $last_day);

  $manual_early_out_list = get_manual_early_out_list($emp_id, $first_day, $last_day);

  $manual_short_hours_list = get_manual_short_hours_list($emp_id, $first_day, $last_day);

  $trip_a_list = get_trip_a_list($emp_id, $first_day, $last_day);

  $trip_b_list = get_trip_b_list($emp_id, $first_day, $last_day);

  if ($cid == 66) {
    $manual_ta_list = get_manual_ta_list($emp_id, $first_day, $last_day);
    $manual_ma_list = get_manual_ma_list($emp_id, $first_day, $last_day);
    $manual_ca_list = get_manual_ca_list($emp_id, $first_day, $last_day);
    $manual_spa_list = get_manual_spa_list($emp_id, $first_day, $last_day);
    $manual_aca_list = get_manual_aca_list($emp_id, $first_day, $last_day);
    $manual_fl_list = get_manual_fl_list($emp_id, $first_day, $last_day);
    $manual_cw_list = get_manual_cw_list($emp_id, $first_day, $last_day);
    $manual_mo_list = get_manual_mo_list($emp_id, $first_day, $last_day);
    $manual_shift1_list = get_manual_shift1_list($emp_id, $first_day, $last_day);
    $manual_shift2_list = get_manual_shift2_list($emp_id, $first_day, $last_day);
    $manual_shift3_list = get_manual_shift3_list($emp_id, $first_day, $last_day);
  }


  if ($ot_settings === false) {
    $ot_settings = get_ot_settings($employee->branch_id);
  } else {
    $ot_settings = search_from_list_by_branch_id($ot_settings, $employee->branch_id);
  }

  if ($early_ot_settings === false) {
    $early_ot_settings = get_early_ot_settings($employee->branch_id);
  } else {
    $early_ot_settings = search_from_list_by_branch_id($early_ot_settings, $employee->branch_id);
  }

  $ot_type_data = $ci->db->select("ot_weekly_hours, ot_type")->from("branches")->where("company_id", $cid)
    ->where("id", $employee->branch_id)->get()->row();

  $replacement_leaves_list = get_replacement_leaves_list($emp_id, $first_day, $last_day);

  $replaced_ph_list = get_replaced_ph_list($emp_id, $first_day, $last_day);
  
  $jl01_paid_leaves = $paid_leaves_array;

  $last_ids = [];
  foreach ($period as $date) {
    $obj = new stdClass();
    $obj->date = $date->format('Y-m-d');
    $obj->day_name = $date->format('l');
    $date_f = $date->format('d-m-Y');
    $date_string = $date->format('d/m D');
    $obj->date_string = $date_string;
    $obj->shift_hours = "";
    $obj->full_shift_hours = "";
    $obj->is_extra_ot = false;
    if ($tsf_custom_summary) {
      $address_distance = $ci->db->select('address, scan_distance')->from('clockings_news')
        ->where('employee_id', $emp_id)
        ->where('datetime >=', $obj->date . ' 00:00:00')
        ->where('datetime <=', $obj->date . ' 23:59:59')
        ->limit(1)
        ->get()
        ->row();
      $obj->location = isset($address_distance->address) ? $address_distance->address : "";
      $obj->distance = isset($address_distance->scan_distance) ? $address_distance->scan_distance : "";
    }
    $replacement = is_replacement($replacement_leaves_list, $obj->date);

    $is_ot = false;
    $is_late = true;
    $is_late_break = true;
    $is_early_out = true;
    $overnight = false;
    $is_shift = false;

    $shift_check = search_from_list($shift_list, $obj->date);
    $next_shift_check = search_from_list($shift_list, add_days_to_date($date, 1)->format("Y-m-d"));
    $obj->shift_check = $shift_check;
    $obj->shift_name = "";
    $obj->acting_code = "";
    $obj->cut_off_time = "";
    $acting_codes = [];
    $half_day = false;
    if ($shift_check) {
      $is_shift = true;
      if ($shift_check->half_day == "Yes") {
        $half_day = true;
      }
      $obj->shift_hours = $shift_check->shift_hours;
      $obj->full_shift_hours = $shift_check->shift_hours;
      $total_shift_hours = add_time($total_shift_hours, $obj->shift_hours);
      $obj->shift_name = $shift_check->name;
      $obj->acting_code = str_replace(",", "|", $shift_check->acting_code);
      $obj->cut_off_time = $shift_check->cut_off_time;
      $acting_codes = explode(",", $shift_check->acting_code);

      if ($shift_check->code == "N") {
        $gbr_night_shifts++;
      }
    }
    if ($shift_check && $shift_check->overnight == "Yes") {
      $result = search_clocking_by_id($result_list_overnight, $obj->date, $emp_id);
      $overnight = true;

      // if ($next_shift_check && $next_shift_check->overnight == "No") {
        $result = remove_next_day_clockings($result, $shift_check, $next_shift_check);
      // }
    } else {
      $result = search_clocking_by_id($result_list, $obj->date, $emp_id);
      // if (!$shift_check || ($shift_check && $shift_check->overnight == "No")) {
        $result = remove_duplicate_clockings($result, $obj->date, $shift_list, $result_list_overnight);
      // }
    }
    $result = get_clockings_from_previous_day($result, $result_list_overnight, $obj->date, $emp_id, $shift_list);
    $obj->overnight = $overnight ? "true" : "false";
    $obj->is_shift = $is_shift ? "true" : "false";
    $obj->is_paid_leave = $shift_check && $shift_check->is_leave == "yes" && $shift_check->is_paid == "yes";
    $obj->is_unpaid_leave = $shift_check && $shift_check->is_leave == "yes" && $shift_check->is_paid == "no";

    $is_replaced_ph = search_from_list($replaced_ph_list, $obj->date) ? true : false;
    $obj->is_replaced_ph = $is_replaced_ph;
    $obj->merit_is_half_day_paid = false;
    $obj->merit_is_full_day_paid = false;
    $obj->merit_is_half_day_unpaid = false;
    $obj->merit_is_medical_leave = false;
    $obj->merit_is_break_late = false;
    $obj->merit_is_missing_in_out = false;
    $obj->merit_is_absent_unpaid = false;
    $obj->merit_is_offense = $current_user['is_merit_approved'] === '1' ? true : false;    
    $obj->merit_is_early_out = false;
    $obj->merit_is_late = false;

    // if ($summary_type === "merit_system") {
      if ($overnight) {
        $clockings_news_result = search_clocking_by_id($clockings_news_overnight, $obj->date, $emp_id);
      } else {
        $clockings_news_result = search_clocking_by_id($clockings_news, $obj->date, $emp_id);
      }

      $clockings_news_result = remove_last_ids($clockings_news_result, $last_ids);
      $last_ids = [];

      if (!empty($clockings_news_result)) {
        if ($clockings_news_result[0]->type === "out" || $clockings_news_result[0]->add_by_admin == 1) {
          $obj->merit_is_missing_in_out = true;
        }
        if (end($clockings_news_result)->type === "in" || end($clockings_news_result)->add_by_admin == 1) {
          $obj->merit_is_missing_in_out = true;
        }
      }
      $missing_in_out_counter = 0;
      foreach ($clockings_news_result as $key => $value) {
        $last_ids[] = $value->id;
        if (($missing_in_out_counter % 2 === 0 && $value->type === "out") || $value->add_by_admin == 1) {
          $obj->merit_is_missing_in_out = true;
          $missing_in_out_counter++;
        } else if (($missing_in_out_counter % 2 === 1 && $value->type === "in") || $value->add_by_admin == 1) {
          $obj->merit_is_missing_in_out = true;
          $missing_in_out_counter++;
        }
        $missing_in_out_counter++;
      }

      if ($obj->merit_is_missing_in_out === true) $total_missing_in_out++;
    // }

    if ($shift_check && $shift_check->is_rest_day) {
      if (in_array($obj->date, $public_holidays)) {
        $total_rest_days_used += $shift_check->public_holiday_deduction;
      } else if (in_array($obj->day_name, ["Saturday", "Sunday"])) {
        $total_rest_days_used += $shift_check->weekend_deduction;
      } else {
        $total_rest_days_used += $shift_check->weekday_deduction;
      }
    }

    if (!$shift_check && empty($result) && $obj->date <= date('Y-m-d')) {
      $total_off_days++;
    }

    /** days calcuation */
    if (!in_array($obj->date, $public_holidays) && !in_array($obj->day_name, $rest_days) && !$obj->is_replaced_ph && !in_array($obj->day_name, $off_days) && !($shift_check && $shift_check->is_rest_day)) {
      $check = false;
      if ($shift_check) {
        $add_day = 1;
        if ($shift_check->half_day == "Yes") {
          $add_day = 0.5;
        }
        if ($shift_check->is_leave == "yes" && $shift_check->is_paid == "yes") {
          $paid_leaves += $add_day;
          if (in_array($shift_check->code, get_attendance_allowance_leave_codes())) {
            $allowance_leaves += $add_day;
          }
          $paid_leaves_array[$date->format("d/m/Y")][] = [
            "employee_special_id" => $employee->special_id,
            "paid_leave" => $add_day,
            "branch_id" => $employee->branch_id,
            'leave_type' => $shift_check->code
          ];
          $check = true;
          if (stripos($shift_check->name, 'medical leave') !== false) {
            $obj->merit_is_medical_leave = true;
            $total_medical_leaves++;

            // set bmi attendance allowance to false if more than 1 medical leave
            if ($total_medical_leaves > 1) {
              $bmi_attendance_allowance = false;
            }

            // set gbr attendance allowance to false if any medical leave
            $gbr_attendance_allowance = false;

            $lsk_non_worked_days++;
          }
          if ($add_day === 0.5) {
            $obj->merit_is_half_day_paid = true;
            $total_half_day_paid++;
          } else if (stripos($shift_check->name, 'medical leave') === false) {
            $obj->merit_is_full_day_paid = true;
            $total_full_day_paid++;
          }
        } else if ($shift_check->is_leave == "yes" && $shift_check->is_paid == "no") {
          $unpaid_leaves += $add_day;
          if (in_array($shift_check->code, get_attendance_allowance_leave_codes())) {
            $allowance_leaves += $add_day;
          }
          if ($add_day === 0.5) {
            $obj->merit_is_half_day_unpaid = true;
            $total_half_day_unpaid++;
          } else {
            $obj->merit_is_absent_unpaid = true;
            $total_absent_unpaid++;
            $full_unpaid_leaves++;
          }
          $check = true;
          $unpaid_leaves_absent_days[$date->format("d/m/Y")][] = [
            "employee_special_id" => $employee->special_id,
            "unpaid_leave" => $add_day,
            "branch_id" => $employee->branch_id,
            "type" => "unpaid_leave"
          ];

          // set bmi attendance allowance to false if there is any unpaid leave
          $bmi_attendance_allowance = false;

          $lsk_non_worked_days++;
        }
        $working_days++;
      }
      if (!$check && empty($result) && $shift_check) {
        if ($obj->date <= date('Y-m-d')) {
          if ($replacement) {
            if ($replacement->to !== $obj->date) {
              if ($shift_check->start_time != NULL && $shift_check->end_time != NULL) {
                $absent_days++;
                $obj->merit_is_absent_unpaid = true;
                $total_absent_unpaid++;
                $unpaid_leaves_absent_days[$date->format("d/m/Y")][] = [
                  "employee_special_id" => $employee->special_id,
                  "unpaid_leave" => $add_day,
                  "branch_id" => $employee->branch_id,
                  "type" => "absent"
                ];
              }
            }
          } else {
            if ($shift_check->start_time != NULL && $shift_check->end_time != NULL) {
              $absent_days++;
              $obj->merit_is_absent_unpaid = true;
              $total_absent_unpaid++;
              $unpaid_leaves_absent_days[$date->format("d/m/Y")][] = [
                "employee_special_id" => $employee->special_id,
                "unpaid_leave" => $add_day,
                "branch_id" => $employee->branch_id,
                "type" => "absent"
              ];
            }
          }

          // set bmi attendance allowance to false if there is any absent day
          $bmi_attendance_allowance = false;

          // set gbr attendance allowance to false if there is any absent day
          $gbr_attendance_allowance = false;
          
          $lsk_non_worked_days++;
        }
      }
    }
    
    if ($cid == 196 && $shift_check && $shift_check->is_leave == "yes") {
      if (in_array($obj->date, $public_holidays)) {
        $deduction = $shift_check->public_holiday_deduction;
      } else if (in_array($obj->day_name, ["Saturday", "Sunday"])) {
        $deduction = $shift_check->weekend_deduction;
      } else {
        $deduction = $shift_check->weekday_deduction;
      }
      $jl01_paid_leaves[$date->format("d/m/Y")][] = [
        "employee_special_id" => $employee->special_id,
        "paid_leave" => $deduction,
        "branch_id" => $employee->branch_id,
        'leave_type' => $shift_check->code
      ];
    }

    $total_hours = "";
    $work_hours = "";
    $break_hours = "";
    $late_hours = "";
    $break_late_hours = "";
    $early_out = "";
    $short_hours = "";
    $tripA = 0;
    $tripB = 0;
    $total_clockings = count($result);
    $formatted_data = array();
    $is_ot_result = search_from_list($is_ot_list, $obj->date);
    if ($is_ot_result) {
      $is_ot = $is_ot_result->is_ot == "Y" ? true : false;
    } else {
      $is_ot = get_is_ot_status($approved_ot_list, $shift_check, $obj->date, $emp_id, $total_clockings, $cid);
    }

    $is_late_result = search_from_list($is_late_list, $obj->date);
    if ($is_late_result) {
      $is_late = $is_late_result->is_late == "Y" ? true : false;
    }

    $is_late_break_result = search_from_list($is_late_break_list, $obj->date);
    if ($is_late_break_result) {
      $is_late_break = $is_late_break_result->is_late_break == "Y" ? true : false;
    }

    $is_early_out_result = search_from_list($is_early_out_list, $obj->date);
    if ($is_early_out_result) {
      $is_early_out = $is_early_out_result->is_early_out == "Y" ? true : false;
    }

    $is_offense_result = search_from_list($is_offense_list, $obj->date);
    if ($is_offense_result) {
      $obj->merit_is_offense = $is_offense_result->is_offense == "Y" ? true : false;
    }

    // array for showing in, out, in, out
    $in_outs = [];
    $in_outs_ids = [];

    $last_out = "";
    foreach ($result as $key => $value) {
      // pushing clock_in and clock_out in in_outs
      $in_outs_ids[] = $value->clock_in_id;
      $in_outs_ids[] = $value->clock_out_id;
      $in_outs[] = $value->clock_in;
      $in_outs[] = $value->clock_out;
      
      if ($key == 0 && $value->shift_remark != null && $value->shift_remark != "") {
        $value->remark = $value->shift_remark;
      }
      // $value->total_time = total_time($value->clock_in_1, $value->clock_out_1);
      $value->total_time = calculate_total_hours($value->clock_in_1, $value->clock_out_1, $value->start_time, $value->early_ot_start, $value->early_ot_end, $value->search_date);
      // var_dump($value->total_time);
      if ($value->name == "") {
        $value->name = "N/A";
      }
      if ($value->code == "") {
        $value->code = "N/A";
      }
      $value->is_break = false;

      $formatted_data[] = $value;
      if (array_key_exists($key + 1, $result)) {
        $x = new stdClass();
        $x->day_f = $value->day_f;
        $x->overtime_starts = $value->overtime_starts;
        $x->early_ot_start = $value->early_ot_start;
        $x->early_ot_end = $value->early_ot_end;
        $x->grace_time = $value->grace_time;
        $x->clock_in = $value->clock_out;
        $x->clock_in_1 = $value->clock_out_1;
        $x->clock_out = $result[$key + 1]->clock_in;
        $x->clock_out_1 = $result[$key + 1]->clock_in_1;
        $x->name = "Break";
        $x->code = "Break";
        $x->is_break = true;
        $x->reason = "";
        $x->remark = "";
        $x->is_ot = $is_ot;
        $x->total_time = total_time($result[$key + 1]->clock_in_1, $value->clock_out_1);
        $formatted_data[] = $x;
      } else {
        $last_out = $value->clock_out_1;
      }
    } // inner loop end
    if (!$half_day) {
      $manual_early_out = search_from_list($manual_early_out_list, $obj->date);
      if ($manual_early_out) {
        $early_out = $manual_early_out->early_out;
        $early_out = round_off_early_out($early_out, get_early_out_settings($employee->branch_id), false);

      } else if ($last_out != "" && $shift_check && $shift_check->void_early_out == "No") {
        $early_out = calculate_early_out($last_out, $shift_check->end_time, $obj->date, $overnight);
      }
    }

    // if day is monday to satuday and last out is after 19:00
    if ($cid == 206 && $obj->day_name != "Sunday") {
      $last_out_time = $last_out ? explode(" ", $last_out)[1] : "";
      if ($last_out_time >= "19:00") {
        $food_allowance_days++;
      }
    }

    $obj->early_out = round_off_early_out($early_out, get_early_out_settings($employee->branch_id), false);
    $obj->clockings = $formatted_data;
    $obj->in_outs = $in_outs;
    $obj->in_outs_id = $in_outs_ids;
    if ($result) {
      $v = $result[0];
    }

    $break_and_late_hours = calculate_break_and_late_hours($obj->clockings, $v, $overnight);
    $work_hours = $break_and_late_hours->work_hours;
    $break_hours = $break_and_late_hours->break_hours;
    $breaks_array = $break_and_late_hours->breaks_array;
    $shift_break_hours = $break_and_late_hours->shift_break_hours;
    $shift_breaks_array = $break_and_late_hours->shift_breaks_array;
    $after_ot_starts_break_hours = $break_and_late_hours->after_ot_starts_break_hours;


    foreach ($obj->clockings as $key => $value) {
      if ($key != 0) {
        $value->day_f = '';
      }
      $total_hours = add_time($total_hours, $value->total_time);
      if ($key == 0) {
        $manual_late = search_from_list($manual_late_list, $obj->date);
        if ($manual_late) {
          $late_hours = $manual_late->late_hours;
          $late_hours = round_off_late_in($late_hours, get_late_in_settings($employee->branch_id), false);
        } else if (isset($v) && $v->is_leave != "" && $v->is_leave != "yes" && $v->void_late_in == "No") {
          if ($v->grace_time != "") {
            if ($overnight) {
              $grace_time = $obj->date . " " . $v->grace_time . ":00";
              $grace_time_stamp = strtotime($grace_time);
              $mid_day = $obj->date . " 12:00:00";
              $mid_day_stamp = strtotime($mid_day);
              if (in_array($shift_check->same_day_overnight, ['default', 'next'])) {
                if ($mid_day_stamp > $grace_time_stamp) {
                  $grace_time_stamp += 24 * 3600;
                }
              }
              $clock_in_stamp = strtotime($v->clock_in_o);

              if ($clock_in_stamp > $grace_time_stamp) {
                $late_stamp = $clock_in_stamp - $grace_time_stamp;
                date_default_timezone_set('UTC');
                $late_hours = date('H:i', $late_stamp);
                date_default_timezone_set("Asia/Kuala_Lumpur");
              }
            } else if (intval(str_replace(":", "", $v->clock_in)) > intval(str_replace(":", "", $v->grace_time))) {
              $late_hours = sub_time($v->clock_in, $v->grace_time);
            }
          }
        }
      }
    } // inner loop end

    // set bmi attendance allowance to false if 3 late ins more than 10 minutes
    if ($late_hours > "00:10") {
      $bmi_late_more_than_10++;
      if ($bmi_late_more_than_10 >= 3) {
        $bmi_attendance_allowance = false;
      }
    }

    // set gbr attendance allowance to false if late is more than 0 minutes
    if ($late_hours > "00:00") {
      $gbr_attendance_allowance = false;
    }

    if (($early_out != "" && $early_out != "00:00" && $is_early_out) || ($late_hours != "" && $late_hours != "00:00" && $is_late)) {
      $total_early_late++;
      $obj->merit_is_early_late = true;
    }

    $break_not_taken = "00:00";
    $extra_break_not_taken = "00:00";
    if (isset($v)) {
      $break_not_taken = calculate_break_not_taken($break_hours, $breaks_array, $v);
    }
    if ($work_hours != "" && $work_hours != "00:00") {
      $work_hours = sub_time($work_hours, $break_not_taken);
    }
    if (isset($v)) {
      $extra_break_not_taken = calculate_extra_break_not_taken($breaks_array, $v, $work_hours);
    }
    if ($work_hours != "" && $work_hours != "00:00") {
      $work_hours = sub_time($work_hours, $extra_break_not_taken);
    }
    if (!$half_day) {
      $manual_short_hours = search_from_list($manual_short_hours_list, $obj->date);
      if ($manual_short_hours) {
        $short_hours = $manual_short_hours->short_hours;
      } else {
        $short_hours = calculate_short_hours($company_working_hours, $work_hours);
      }
    }

    $trip_a = search_from_list($trip_a_list, $obj->date);
    $trip_b = search_from_list($trip_b_list, $obj->date);
    if ($trip_a) {
      $tripA = $trip_a->no_of_trips;
      $total_trip_a += $trip_a->no_of_trips;
    }
    if ($trip_b) {
      $tripB = $trip_b->no_of_trips;
      $total_trip_b += $trip_b->no_of_trips;
    }

    if (isset($v) && !$half_day) {
      $manual_late_break = search_from_list($manual_late_break_list, $obj->date);
      if ($manual_late_break) {
        $break_late_hours = $manual_late_break->late_hours_break;
        $break_late_hours = round_off_late_break($break_late_hours, get_late_break_settings($employee->branch_id), false);
      } else {
        if ($employee->ignore_breaks_after_endtime == 0) {
          $break_late_hours = calculate_break_late($break_hours, $breaks_array, $v, $work_hours, $obj->is_shift);
        } else {
          $break_late_hours = calculate_break_late($shift_break_hours, $shift_breaks_array, $v, $work_hours, $obj->is_shift);
        }
      }
    }

    if ($break_late_hours != "" && $break_late_hours != "00:00" && $is_late_break === true) {
      $obj->merit_is_break_late = true;
      $total_break_late++;
    }
    $obj->break_late_hours = round_off_late_break($break_late_hours, get_late_break_settings($employee->branch_id), false);;

    $work_hours = add_deducted_time_in_work_hours($work_hours, $late_hours, $break_late_hours, $early_out, $inc_late_in, $inc_late_break, $inc_early_out, $is_late, $is_late_break, $is_early_out, $ot_type_data->ot_type);

    $days = "";
    $is_rest_day = false;
    $is_off_day = false;
    $is_ph_day = false;

    if (in_array($obj->date, $public_holidays) || $obj->is_replaced_ph) {
      $is_ph_day = true;
      $total_holidays++;
    }

    $ph = get_public_holiday_by_date($obj->date, $employee->branch_id, $cid);
    if ($result) {
      $v = $result[0];
      if ($days_settings) {
        $days = calculate_days($work_hours, $days_settings);
      } else {
        $days = 1;
      }
      if ($v->is_leave == "yes" && $v->half_day == "Yes") {
        $days = 0.5;
      }
      if ($is_ph_day) {
        // if worked hours are OT then don't count as worked holiday
        if(!$employee->worked_hours_ot_ph && !$ph->replacement_ph) {
          $worked_holidays += $days;
          // echo $ph->rate;die;
          if ($ph->rate == "x3" || $obj->is_replaced_ph) {
            // echo 'x3';die;
            $worked_holidays_array[$date->format("d/m/Y")][] = [
              "employee_special_id" => $employee->special_id,
              "worked_holiday" => $days,
              "branch_id" => $employee->branch_id,
              "holiday_rate" => 'x3'
            ];
          } else {
            // echo 'x2';die;
            $worked_holidays_array[$date->format("d/m/Y")][] = [
              "employee_special_id" => $employee->special_id,
              "worked_holiday" => $days,
              "branch_id" => $employee->branch_id,
              "holiday_rate" => 'x2'
            ];
          }
        }

        if ($cid == 66 && $obj->day_name != "Sunday") {
          $worked_days++; // public holiday count as worked day if not sunday for BMI
        }
      } else if (in_array($obj->day_name, $off_days)) {
        $is_off_day = true;
        // if worked hours are OT then don't count as worked rest day
        if(!$employee->worked_hours_ot_off){
          $worked_off_days += $days;
          $worked_off_days_array[$date->format("d/m/Y")][] = [
            "employee_special_id" => $employee->special_id,
            "worked_off_day" => $days,
            "branch_id" => $employee->branch_id,
          ];
        }
      } else if (in_array($obj->day_name, $rest_days) || $v->name == "N/A" || !$obj->shift_check || ($shift_check && $shift_check->is_rest_day)) {
        $is_rest_day = true;
        // if worked hours are OT then don't count as worked rest day
        if(!$employee->worked_hours_ot_rd){
          $worked_rest_days += $days;
          $worked_rest_days_array[$date->format("d/m/Y")][] = [
            "employee_special_id" => $employee->special_id,
            "worked_rest_day" => $days,
            "branch_id" => $employee->branch_id,
          ];
        }
      } else {
        $worked_days += ($days && $days != "-" ? $days : 0);
      }
    } else {
      if (in_array($cid, $companies_allowed_for_monthly_ot)) {
        if ($obj->is_paid_leave) {
          if ($shift_check->half_day == "Yes") {
            $days = 0.5;
            $work_hours = $company_half_hours;
          } else {
            $work_hours = $company_working_hours;
            $days = 1;
          }
        }
      }
    }

    $obj->is_rest_day = $is_rest_day;

    if ($cid == 87) {
      if ($obj->is_paid_leave) {
        if ($shift_check && $shift_check->half_day == 'Yes') {
          $days = 0.5;
          $work_hours = $company_half_hours;
        } else {
          $days = 1;
          $work_hours = $company_working_hours;
        }
      }
    }


    $obj->first_in = "";
    $obj->last_out = "";
    $obj->first_in_o = "";
    $obj->last_out_o = "";

    if ($result) {
      $obj->first_in = $result[0]->clock_in;
      $obj->last_out = end($result)->clock_out;
      $obj->first_in_o = $result[0]->clock_in_1;
      $obj->last_out_o = end($result)->clock_out_1;
    }


    $obj->total_hours = $total_hours;
    $obj->work_hours = $work_hours;
    $obj->work_hours_whole = floor(toDecimal($work_hours));

    
    $final_company_working_hours = $company_working_hours;
    $final_company_working_hours_decimal = $company_working_hours_decimal;
    if($obj->day_name == 'Saturday' && $employee->use_half_hours_for_saturdays){
      $final_company_working_hours = $company_half_hours;
      $final_company_working_hours_decimal = $company_half_hours_decimal;
    }

    if ($employee->ot_type == "eight_hours") {
      $decimal_work_hours = toDecimal($work_hours);
      // if company working hours is 8 hours and employee worked less than 8 hours then calculate early out
      if ($final_company_working_hours_decimal && $decimal_work_hours < $final_company_working_hours_decimal && $decimal_work_hours > 0) {
        $decimal_early_out = $final_company_working_hours_decimal - $decimal_work_hours;
        $eight_hours_early_out = decimal_to_time($decimal_early_out);
        if (!$half_day) {
          $manual_early_out = search_from_list($manual_early_out_list, $obj->date);
          if ($manual_early_out) {
            $early_out = $manual_early_out->early_out;
          } else if ($last_out != "" && $shift_check && $shift_check->void_early_out == "No") {
            $obj->early_out = $early_out = $eight_hours_early_out;
          }
        }
      }
    }
    $obj->break_hours = $break_hours;
    // $obj->late_hours = $late_hours;
    $obj->late_hours = round_off_late_in($late_hours, get_late_in_settings($employee->branch_id), false);
    $obj->short_hours = $short_hours;
    $obj->trip_a = $tripA;
    $obj->trip_b = $tripB;
    // don't show days if it's a rest day or public holiday and worked hours are OT
    $obj->days = (($is_rest_day && $employee->worked_hours_ot_rd) || ($is_ph_day && $employee->worked_hours_ot_ph) || ($is_off_day && $employee->worked_hours_ot_off)) ? "" : $days;
    $overtime = "";
    $early_overtime = "";
    $overtime_m = "";
    $overtime_type = "+";
    $is_manual_exist = false;
    $manual_ot = search_from_list($manual_ot_list, $obj->date);
    if ($manual_ot) {
      $overtime_m = $manual_ot->overtime;
      $overtime_type = $manual_ot->type;
      $is_manual_exist = true;
      if ($overtime_type == "-") {
        $overtime_m = "-" . $overtime_m;
      }
    }
    $round_of_ot = 1;
    if ($shift_check) {
      $round_of_ot = $shift_check->round_off_ot;
    }
    if (($is_rest_day && $employee->worked_hours_ot_rd) || ($is_ph_day && $employee->worked_hours_ot_ph) || ($is_off_day && $employee->worked_hours_ot_off)) {
      if ($apply_overtime) {
        $overtime = $work_hours;
      }
      $overtime = round_off_ot($overtime, $ot_settings, $employee->round_first_hour_only);
    } else {
      $overtime = calculate_final_overtime($result, $obj->clockings, $date_f, $overnight, $apply_overtime, $apply_early_overtime, $work_hours, $final_company_working_hours, $employee->ot_type, $employee->ot_round, $employee->round_first_hour_only, $employee->round_by_exact_hour, $employee->different_first_hour_rounding, $ot_settings, $obj->shift_hours, $round_of_ot, $final_company_working_hours_decimal, $employee->early_ot_round, $early_ot_settings);
      if ($employee->ignore_breaks_after_endtime == 1 && ($apply_overtime || $apply_early_overtime)) {
        $overtime = add_time($overtime, "-" . $after_ot_starts_break_hours);
        if ($overtime == "00:00") {
          $overtime = "";
        }
      }
    }

    if (($is_rest_day && $employee->deduct_hour_ot_rd) || ($is_ph_day && $employee->deduct_hour_ot_ph) || ($is_off_day && $employee->deduct_hour_ot_off)) {
      $overtime = deduct_hour_from_ot_rd($overtime);
    }

    if ($cid == 66) {
      $obj->bmi_ot = "";
      $obj->bmi_ot_sunday = "";
      $obj->bmi_ph_1 = "";
      $obj->bmi_ph_2 = "";
      $obj->bmi_ta_final = $obj->bmi_ta = $obj->bmi_ta_manual = "";
      $obj->bmi_ma_final = $obj->bmi_ma = $obj->bmi_ma_manual = "";
      $obj->bmi_ca_final = $obj->bmi_ca = $obj->bmi_ca_manual = "";
      $obj->bmi_spa_final = $obj->bmi_spa = $obj->bmi_spa_manual = "";
      $obj->bmi_aca_final = $obj->bmi_aca = $obj->bmi_aca_manual = "";
      $obj->bmi_fl_final = $obj->bmi_fl = $obj->bmi_fl_manual = "";
      $obj->bmi_cw_final = $obj->bmi_cw = $obj->bmi_cw_manual = "";
      $obj->bmi_mo_final = $obj->bmi_mo = $obj->bmi_mo_manual = "";
      $obj->bmi_shift1_final = $obj->bmi_shift1 = $obj->bmi_shift1_manual = "";
      $obj->bmi_shift2_final = $obj->bmi_shift2 = $obj->bmi_shift2_manual = "";
      $obj->bmi_shift3_final = $obj->bmi_shift3 = $obj->bmi_shift3_manual = "";

      $bmi_total_time_original_format = time_bw_original_times($obj->last_out_o, $obj->first_in_o);
      $bmi_total_time = toDecimal(time_bw_original_times($obj->last_out_o, $obj->first_in_o));

      if (in_array($obj->date, $public_holidays)) {
        if ($bmi_total_time > 8) {
          $obj->bmi_ph_1 = "8.00";
          $obj->bmi_ph_2 = number_format($bmi_total_time - 8, 2);
        } else if ($bmi_total_time) {
          $obj->bmi_ph_1 = number_format($bmi_total_time, 2);
        }
      } else if (in_array($obj->day_name, $rest_days)) {
        if ($bmi_total_time && $obj->last_out) {
          $bmi_ot_sunday = round_off_ot($bmi_total_time_original_format, $ot_settings, $employee->round_first_hour_only);
          $bmi_ot_sunday = toDecimal($bmi_ot_sunday);
          if ($employee->deduct_hour_ot_rd) {
            $bmi_ot_sunday = $bmi_ot_sunday - 1;
            if ($bmi_ot_sunday < 0) $bmi_ot_sunday = 0;
          }
          $obj->bmi_ot_sunday = round($bmi_ot_sunday, 2);
        }
      } else if ($is_ot) {
        $manual_added_overtime = add_time_minus($overtime, $overtime_m);
        $obj->bmi_ot = $manual_added_overtime ? number_format(toDecimal($manual_added_overtime), 2) : "";
      }

      if ($bmi_total_time > 5 && $days == 1) {
        $obj->bmi_ta_final = $obj->bmi_ta = number_format($employee->ta_rate, 2);
      }
      if ($obj->bmi_ot >= 2.5 && $days == 1) {
        $obj->bmi_ma_final = $obj->bmi_ma = number_format($employee->ma_rate, 2);
      }
      if (in_array("CA", $acting_codes) && $days == 1) {
        $obj->bmi_ca_final = $obj->bmi_ca = number_format($employee->ca_rate, 2);
      }
      if (in_array("SPA", $acting_codes) && $days == 1) {
        $obj->bmi_spa_final = $obj->bmi_spa = number_format($employee->spa_rate, 2);
      }
      if (in_array("ACA", $acting_codes) && $days == 1) {
        $obj->bmi_aca_final = $obj->bmi_aca = number_format($employee->aca_rate, 2);
      }
      if (in_array("FL Inc", $acting_codes) && $days == 1) {
        $obj->bmi_fl_final = $obj->bmi_fl = number_format($employee->fl_rate, 2);
      }
      if (in_array("C/wash", $acting_codes) && $days == 1) {
        $obj->bmi_cw_final = $obj->bmi_cw = number_format($employee->cw_rate, 2);
      }
      if (in_array("M/ope", $acting_codes) && $days == 1) {
        $obj->bmi_mo_final = $obj->bmi_mo = number_format($employee->mo_rate, 2);
      }
      if (in_array("Shift1", $acting_codes) && $days == 1) {
        $obj->bmi_shift1_final = $obj->bmi_shift1 = number_format($employee->shift1_rate, 2);
      }
      if (in_array("Shift2", $acting_codes) && $days == 1) {
        $obj->bmi_shift2_final = $obj->bmi_shift2 = number_format($employee->shift2_rate, 2);
      }
      if (in_array("Shift3", $acting_codes) && $days == 1) {
        $obj->bmi_shift3_final = $obj->bmi_shift3 = number_format($employee->shift3_rate, 2);
      }

      $manual_ta = search_from_list($manual_ta_list, $obj->date);
      if ($manual_ta) {
        $obj->bmi_ta_final = $obj->bmi_ta_manual = number_format($manual_ta->value, 2);
      }

      $manual_ma = search_from_list($manual_ma_list, $obj->date);
      if ($manual_ma) {
        $obj->bmi_ma_final = $obj->bmi_ma_manual = number_format($manual_ma->value, 2);
      }

      $manual_ca = search_from_list($manual_ca_list, $obj->date);
      if ($manual_ca) {
        $obj->bmi_ca_final = $obj->bmi_ca_manual = number_format($manual_ca->value, 2);
      }

      $manual_spa = search_from_list($manual_spa_list, $obj->date);
      if ($manual_spa) {
        $obj->bmi_spa_final = $obj->bmi_spa_manual = number_format($manual_spa->value, 2);
      }

      $manual_aca = search_from_list($manual_aca_list, $obj->date);
      if ($manual_aca) {
        $obj->bmi_aca_final = $obj->bmi_aca_manual = number_format($manual_aca->value, 2);
      }

      $manual_fl = search_from_list($manual_fl_list, $obj->date);
      if ($manual_fl) {
        $obj->bmi_fl_final = $obj->bmi_fl_manual = number_format($manual_fl->value, 2);
      }

      $manual_cw = search_from_list($manual_cw_list, $obj->date);
      if ($manual_cw) {
        $obj->bmi_cw_final = $obj->bmi_cw_manual = number_format($manual_cw->value, 2);
      }

      $manual_mo = search_from_list($manual_mo_list, $obj->date);
      if ($manual_mo) {
        $obj->bmi_mo_final = $obj->bmi_mo_manual = number_format($manual_mo->value, 2);
      }

      $manual_shift1 = search_from_list($manual_shift1_list, $obj->date);
      if ($manual_shift1) {
        $obj->bmi_shift1_final = $obj->bmi_shift1_manual = number_format($manual_shift1->value, 2);
      }

      $manual_shift2 = search_from_list($manual_shift2_list, $obj->date);
      if ($manual_shift2) {
        $obj->bmi_shift2_final = $obj->bmi_shift2_manual = number_format($manual_shift2->value, 2);
      }

      $manual_shift3 = search_from_list($manual_shift3_list, $obj->date);
      if ($manual_shift3) {
        $obj->bmi_shift3_final = $obj->bmi_shift3_manual = number_format($manual_shift3->value, 2);
      }

      $total_bmi_ot += ($obj->bmi_ot ? $obj->bmi_ot : 0);
      $total_bmi_ot_sunday += ($obj->bmi_ot_sunday ? $obj->bmi_ot_sunday : 0);
      $total_bmi_ph_1 += ($obj->bmi_ph_1 ? $obj->bmi_ph_1 : 0);
      $total_bmi_ph_2 += ($obj->bmi_ph_2 ? $obj->bmi_ph_2 : 0);
      $total_bmi_ta += ($obj->bmi_ta_final ? $obj->bmi_ta_final : 0);
      $total_bmi_ma += ($obj->bmi_ma_final ? $obj->bmi_ma_final : 0);
      $total_bmi_ca += ($obj->bmi_ca_final ? $obj->bmi_ca_final : 0);
      $total_bmi_spa += ($obj->bmi_spa_final ? $obj->bmi_spa_final : 0);
      $total_bmi_aca += ($obj->bmi_aca_final ? $obj->bmi_aca_final : 0);
      $total_bmi_fl += ($obj->bmi_fl_final ? $obj->bmi_fl_final : 0);
      $total_bmi_cw += ($obj->bmi_cw_final ? $obj->bmi_cw_final : 0);
      $total_bmi_mo += ($obj->bmi_mo_final ? $obj->bmi_mo_final : 0);
      $total_bmi_shift1 += ($obj->bmi_shift1_final ? $obj->bmi_shift1_final : 0);
      $total_bmi_shift2 += ($obj->bmi_shift2_final ? $obj->bmi_shift2_final : 0);
      $total_bmi_shift3 += ($obj->bmi_shift3_final ? $obj->bmi_shift3_final : 0);
    }

    if ($cid == "102") {
      if ($employee->ot_group == "hours" && toDecimal($overtime) > 3) {
        $overtime = add_time($overtime, "-00:30");
      }
    }

    $overtime = ot_deduction_from_shift_settings($overtime, $shift_check);


    $obj->is_manual_exist = $is_manual_exist;
    $obj->overtime = $overtime;
    $obj->overtime_m = $overtime_m;
    $obj->overtime_type = $overtime_type;
    $obj->is_ot = $is_ot;
    $obj->is_late = $is_late;
    $obj->is_late_break = $is_late_break;
    $obj->is_early_out = $is_early_out;
    $obj->overtime_ph_x2 = "";
    $obj->overtime_ph_x3 = "";
    $obj->x2 = false;
    $obj->x3 = false;

    $daily_overtime = "";

    if($is_ot) {
      $daily_overtime = $overtime;
    }

    if($is_manual_exist) {
      $daily_overtime = add_time_minus($daily_overtime, $overtime_m);
    }

    if(toDecimal($daily_overtime) != 0) {
      $daily_ot_array[$date->format("d/m/Y")][] = [
        "employee_special_id" => $employee->special_id,
        "daily_overtime" => toDecimal($daily_overtime),
        "branch_id" => $employee->branch_id
      ];
    }

    $is_extra_ot = false;
    if ($obj->shift_check) {
      if (is_extra_ot_given($obj->work_hours, $obj->shift_check->extra_ot, $obj->shift_check->extra_ot_worked_hours_more_than, $obj->shift_check->extra_ot_hours)) {
        $is_extra_ot = true;
      }
    }

    $obj->is_extra_ot = $is_extra_ot;
    $dates[] = $obj;
    
    if (in_array($obj->date, $public_holidays) || $is_replaced_ph) {
      if ($ph->rate == "x3" || $is_replaced_ph) {
        $obj->x3 = true;
      } else {
        $obj->x2 = true;
      }
    }

    if ($obj->is_ot) {
      if (in_array($obj->date, $public_holidays) || $is_replaced_ph) {
        if ($obj->x3) {
          $month_overtime_ph_x3 = add_time_minus($month_overtime_ph_x3, $obj->overtime);
          $obj->overtime_ph_x3 = $obj->overtime;
        } else {
          $month_overtime_ph_x2 = add_time_minus($month_overtime_ph_x2, $obj->overtime);
          $obj->overtime_ph_x2 = $obj->overtime;
        }
        $month_overtime_ph = add_time_minus($month_overtime_ph, $obj->overtime);
      } else if (in_array($obj->day_name, $off_days)) {
        $month_overtime_off = add_time_minus($month_overtime_off, $obj->overtime);
      } else if (in_array($obj->day_name, $rest_days) || !$obj->shift_check || $obj->shift_check->is_rest_day) {
        $month_overtime_rd = add_time_minus($month_overtime_rd, $obj->overtime);
      } else {
        $month_overtime = add_time_minus($month_overtime, $obj->overtime);
      }
    }
    // changed
    if ($obj->is_manual_exist) {
      if (in_array($obj->date, $public_holidays) || $is_replaced_ph) {
        if ($obj->x3) {
          $month_overtime_ph_x3 = add_time_minus($month_overtime_ph_x3, $obj->overtime_m);
          $obj->overtime_ph_x3 = add_time_minus($obj->overtime_ph_x3, $obj->overtime_m);
        } else {
          $month_overtime_ph_x2 = add_time_minus($month_overtime_ph_x2, $obj->overtime_m);
          $obj->overtime_ph_x2 = add_time_minus($obj->overtime_ph_x2, $obj->overtime_m);
        }
        $month_overtime_ph = add_time_minus($month_overtime_ph, $obj->overtime_m);
      } else if (in_array($obj->day_name, $off_days)) {
        $month_overtime_off = add_time_minus($month_overtime_off, $obj->overtime_m);
      } else if (in_array($obj->day_name, $rest_days) || !$obj->shift_check || $obj->shift_check->is_rest_day) {
        $month_overtime_rd = add_time_minus($month_overtime_rd, $obj->overtime_m);
      } else {
        $month_overtime = add_time_minus($month_overtime, $obj->overtime_m);
      }
    }

    $obj->overtime_ph_x2 = $obj->overtime_ph_x2 == "00:00" ? "" : $obj->overtime_ph_x2;
    $obj->overtime_ph_x3 = $obj->overtime_ph_x3 == "00:00" ? "" : $obj->overtime_ph_x3;

    // shift name
    if (!$obj->clockings) {
      $shift_name = "";
      $shift_code = "";
      $cut_off_time = "";
      $shift = search_from_list($shift_list, $obj->date);
      if ($shift) {
        $shift_name = $shift->name;
        $shift_code = $shift->code;
        $cut_off_time = $shift->cut_off_time;
      }

      $remark = search_from_list($remark_list, $obj->date);

      $no_data = new stdClass();
      $no_data->day_f = $date_string;
      $no_data->name = $shift_name;
      $no_data->code = $shift_code;
      $no_data->cut_off_time = $cut_off_time;
      $no_data->clock_in = "";
      $no_data->clock_out = "";
      $no_data->reason = "";
      $no_data->remark = "";
      if ($remark) {
        $no_data->remark = $remark->remark;
      }
      $no_data->total_time = "";
      $obj->clockings[0] = $no_data;
      $obj->shift_name = $shift_name;
      $obj->shift_code = $shift_code;
      $obj->cut_off_time = $cut_off_time;
    }

    // revert replacement OT
    if ($replacement) {
      if ($replacement->to === $obj->date) {
        $obj->clockings[0]->name = "RL";
        $obj->clockings[0]->code = "RL";
        $formatted_from_date = convert_date("Y-m-d", "d/m/Y", $replacement->from);
        $obj->clockings[0]->remark = "Replacement leave from {$formatted_from_date}";
      }
    }

    $total = add_time($total, $obj->total_hours);
    $work = add_time($work, $obj->work_hours);
    $break = add_time($break, $obj->break_hours);
    if ($obj->is_late) {
      $late = add_time($late, $obj->late_hours);
    }
    if ($obj->is_late_break) {
      $break_late = add_time($break_late, $obj->break_late_hours);
    }
    $total_short = add_time($total_short, $obj->short_hours);
    if ($obj->is_early_out) {
      $total_early = add_time($total_early, $obj->early_out);
      if ($obj->early_out != "") {
        $total_early_count++;
        $obj->merit_is_early_out = true;
      }
    }
    $total_days = add_days($total_days, $obj->days);
    $late_result = get_lateness_time($total_late, $obj->late_hours, $obj->break_late_hours, $obj->early_out, $obj->short_hours, $inc_late_in, $inc_late_break, $inc_early_out && $is_early_out, $inc_short_hours, $late_count);
    $total_late = $late_result[0];
    $late_count = $late_result[1];
    $today_late = $late_result[2];
    if ($inc_late_in && check_if_time_exist($obj->late_hours)) {
      $obj->merit_is_late = true;
      $total_late_only_count++;
    }
    
    if(toDecimal($today_late) != 0) {
      $total_late_day = round(beautiful_time_to_minutes($today_late) / ($company_working_hours_decimal * 60), 3);
      $daily_late_array[$date->format("d/m/Y")][] = [
        "employee_special_id" => $employee->special_id,
        "daily_late" => $total_late_day,
        "branch_id" => $employee->branch_id
      ];
    }
    if ($custom_in_outs) {
      $obj->clockings = [$obj->clockings[0]];
    }
    $obj->employee_shift_hours = "";
    if ($result && $last_out != "" && !$is_ph_day && !$is_rest_day) {
      $obj->employee_shift_hours = add_time($obj->full_shift_hours, "-" . $today_late);
    }
    $shift_hours_total = add_time($shift_hours_total, $obj->employee_shift_hours);

    if ($cid == 146) {
      $obj->meal_days = 0;
      if (toDecimal($obj->work_hours) >= toDecimal($employee->min_worked_hours_meal) && $employee->department == "Worker") {
        $obj->meal_days = 1;
      }
    }

  } // outer loop end

  if ($cid == 196) {
    $paid_leaves_array = $jl01_paid_leaves;
  }

  $lateness_result = get_lateness_time("00:00", $late, $break_late, $total_early, $total_short, $inc_late_in, $inc_late_break, $inc_early_out && $is_early_out, $inc_short_hours, 0);
  $lateness_time = void_late_minutes($lateness_result[0], $void_minutes);

  $monthly_working_hours = "00:00";

  if ($ot_type_data->ot_type === "weekly_hours") {
    $work_decimal = toDecimal($work);
    $ot_weekly_hours_decimal = $ot_type_data->ot_weekly_hours;

    if ($ot_weekly_hours_decimal > $work_decimal) {
      $month_overtime = "00:00";
      $month_overtime_deducted = "00:00";

      foreach ($dates as $d) {
        $is_manual_exist = false;
        $manual_ot = search_from_list($manual_ot_list, $d->date);
        $shift_check = search_from_list($shift_list, $d->date);
        if ($manual_ot) {
          $overtime_m = $manual_ot->overtime;
          $overtime_type = $manual_ot->type;
          $is_manual_exist = true;
          if ($overtime_type == "-") {
            $overtime_m = "-" . $overtime_m;
          }
        }
        // If not holiday / rest day
        if (!in_array($d->date, $public_holidays) && !in_array($d->day_name, $rest_days) && $shift_check && $is_replaced_ph && !$shift_check->is_rest_day) {
          if ($is_manual_exist) {
            $month_overtime = add_time_minus($month_overtime, $overtime_m);
          }
          if ($deduct_from_ot) {
            $after_deduction = deduct_from_ot($month_overtime, $lateness_time, $deduction_date, $last_day);
            $month_overtime_deducted = $after_deduction[0];
          }
          $d->overtime = "";
        }
      }
    }
  } else if ($ot_type_data->ot_type === "monthly_ot") {
    $days_in_month = (int)date('t', strtotime($obj->date));
    $no_of_working_days = $ci->db->select("id, days")->from("monthly_working_days")
      ->where('month', $date->format('m'))
      ->where('year', $date->format('Y'))
      ->where('company_id', $cid)
      ->where('branch_id', $employee->branch_id)->get()->row();

    $work_decimal = toDecimal($work);
    // $ot_daily_hours = (double)$ot_type_data->ot_daily_hours;
    $off_days_m = 0;
    if (is_null($no_of_working_days)) {
      $no_of_working_days = $days_in_month - 4;
      if ($total_off_days > 4) $off_days_m = $total_off_days - 4;
    } else {
      $no_of_working_days = (int)$no_of_working_days->days;
      $remaining_days = $days_in_month - $no_of_working_days;
      if ($total_off_days > $remaining_days) $off_days_m = $total_off_days - $remaining_days;
    }

    $monthly_working_hours_decimal = $no_of_working_days * $company_working_hours_decimal;
    $monthly_working_hours = decimal_to_time($monthly_working_hours_decimal);
    $off_days_time = multiply_time_by_scalar($company_working_hours, $off_days_m);
    $month_overtime = add_time($month_overtime, "-{$off_days_time}");
    $absent_days_time = multiply_time_by_scalar($company_working_hours, $absent_days);
    $month_overtime = add_time($month_overtime, "-{$absent_days_time}");
    $unpaid_leaves_time = multiply_time_by_scalar($company_working_hours, $full_unpaid_leaves);
    $month_overtime = add_time($month_overtime, "-{$unpaid_leaves_time}");
    // if($monthly_working_hours_decimal < $work_decimal) {
    //   $month_overtime = decimal_to_time($work_decimal - $monthly_working_hours_decimal);
    // }
    if ($monthly_working_hours_decimal > $work_decimal) {
      $month_overtime = "00:00";
      $month_overtime_deducted = "00:00";

      foreach ($dates as $d) {
        $is_manual_exist = false;
        $manual_ot = search_from_list($manual_ot_list, $d->date);
        $shift_check = search_from_list($shift_list, $d->date);
        if ($manual_ot) {
          $overtime_m = $manual_ot->overtime;
          $overtime_type = $manual_ot->type;
          $is_manual_exist = true;
          if ($overtime_type == "-") {
            $overtime_m = "-" . $overtime_m;
          }
        }

        // If not holiday / rest day
        if (!in_array($d->date, $public_holidays) && !in_array($d->day_name, $rest_days) && $shift_check && $is_replaced_ph && !$shift_check->is_rest_day) {
          if ($is_manual_exist) {
            $month_overtime = add_time_minus($month_overtime, $overtime_m);
          }
          if ($deduct_from_ot) {
            $after_deduction = deduct_from_ot($month_overtime, $lateness_time, $deduction_date, $last_day);
            $month_overtime_deducted = $after_deduction[0];
          }
          $d->overtime = "";
        }
      }
    }
  }

  $month_overtime_deducted = $month_overtime;
  $lateness_time_deducted = $lateness_time;


  if ($deduct_from_ot) {
    $after_deduction = deduct_from_ot($month_overtime, $lateness_time, $deduction_date, $last_day);
    $month_overtime_deducted = $after_deduction[0];
    $lateness_time_deducted = $after_deduction[1];
  }

  if (in_array($cid, companies_allowed_for_att_all())) {
    if ($employee->is_att_all == 1) {
      if ($absent_days > 0 || $allowance_leaves > 2) {
        $employee->att_all_amount = 0;
      } else if ($allowance_leaves == 1) {
        $employee->att_all_amount = 75;
      } else if ($allowance_leaves == 2) {
        $employee->att_all_amount = 50;
      }
    }
  }

  $yrdata = strtotime($first_day);
  $month_name = date('F', $yrdata);

  $data["current_user"] = $current_user;
  $data["employee"] = $employee;
  // Set $custom_in_outs true if company ID is matched
  if($custom_in_outs)
    $data['custom_in_outs'] = true;
  
  if($tsf_custom_summary)
    $data['tsf_custom_summary'] = true;

  $data['lateness_time'] = $lateness_time; // total late
  $data['lateness_time_deducted'] = $lateness_time_deducted;
  $data['late'] = $late; // late_in
  $data['late_count'] = $late_count;

  $data['total'] = $total;
  $data['work'] = $work;
  $data['shift_hours_total'] = remove_seconds($shift_hours_total);
  $data['break'] = $break;
  $data['break_late'] = $break_late;
  $data['total_days'] = $total_days;
  $data['total_short'] = $total_short;
  $data['total_early'] = $total_early;
  $data['total_early_count'] = $total_early_count;
  $data['total_trip_a'] = $total_trip_a;
  $data['total_trip_b'] = $total_trip_b;
  $data['total_late_only_count'] = $total_late_only_count;

  $data['month_overtime'] = $month_overtime;
  $data['month_overtime_deducted'] = $month_overtime_deducted;
  $data['month_overtime_ph'] = $month_overtime_ph;
  $data['month_overtime_ph_x2'] = $month_overtime_ph_x2;
  $data['month_overtime_ph_x3'] = $month_overtime_ph_x3;
  $data['month_overtime_rd'] = $month_overtime_rd;
  $data['month_overtime_off'] = $month_overtime_off;
  $data['monthly_working_hours'] = $monthly_working_hours;

  if ($summary_type === "short") {
    $data['month_overtime_deducted'] = ($month_overtime_deducted === "00:00" ? "" : $month_overtime_deducted);
    $data['month_overtime_ph'] = ($month_overtime_ph === "00:00" ? "" : $month_overtime_ph);
    $data['month_overtime_ph_x2'] = ($month_overtime_ph_x2 === "00:00" ? "" : $month_overtime_ph_x2);
    $data['month_overtime_ph_x3'] = ($month_overtime_ph_x3 === "00:00" ? "" : $month_overtime_ph_x3);
    $data['month_overtime_rd'] = ($month_overtime_rd === "00:00" ? "" : $month_overtime_rd);
    $data['month_overtime_off'] = ($month_overtime_off === "00:00" ? "" : $month_overtime_off);
  } else if ($summary_type === "accounts") {
    $data['month_overtime_deducted'] = toDecimal($month_overtime_deducted);
    $data['month_overtime_ph'] = toDecimal($month_overtime_ph);
    $data['month_overtime_rd'] = toDecimal($month_overtime_rd);
    $data['lateness_time_deducted'] = toDecimal($lateness_time_deducted);
    $data['total_early'] = "";
    $data['total_early_count'] = "";
  } else if ($summary_type === "autocount") {
    $data['month_overtime_deducted'] = $month_overtime_deducted;
    $data['month_overtime_ph'] = $month_overtime_ph;
    $data['month_overtime_rd'] = $month_overtime_rd;
    $data['lateness_time_deducted'] = $lateness_time_deducted;
    $data['total_early'] = $total_early;
  } else if ($summary_type === "sql") {
    $data['lateness_time_deducted'] = toDecimal($lateness_time_deducted);
    $data['month_overtime_deducted'] = toDecimal($month_overtime_deducted);
    $data['month_overtime_ph'] = toDecimal($month_overtime_ph);
    $data['month_overtime_ph_x2'] = toDecimal($month_overtime_ph_x2);
    $data['month_overtime_ph_x3'] = toDecimal($month_overtime_ph_x3);
    $data['month_overtime_rd'] = toDecimal($month_overtime_rd);
    $data['month_overtime_off'] = toDecimal($month_overtime_off);
    $lateness_time_deducted_decimal = toDecimal($lateness_time_deducted);
    $company_working_hours_decimal = toDecimal($company_working_hours);
    $data['late_days'] = calculate_late_days($lateness_time_deducted_decimal, $company_working_hours_decimal);
  }

  $data['working_days'] = $working_days;
  $data['worked_days'] = $worked_days;
  $data['worked_rest_days'] = $worked_rest_days;
  $data['worked_off_days'] = $worked_off_days;
  $data['worked_holidays'] = $worked_holidays;
  $data['total_holidays'] = $total_holidays;
  $data['absent_days'] = $absent_days;
  $data['paid_leaves'] = $paid_leaves;
  $data['unpaid_leaves'] = $unpaid_leaves;

  $data['worked_holidays_array'] = $worked_holidays_array;
  $data['worked_rest_days_array'] = $worked_rest_days_array;
  $data['worked_off_days_array'] = $worked_off_days_array;
  $data['unpaid_leaves_absent_days'] = $unpaid_leaves_absent_days;

  $data['dates'] = $dates;

  $data["rest_days"] = $rest_days;
  $data["off_days"] = $off_days;
  $data['public_holidays'] = $public_holidays;
  if ($summary_type === "summary") $data['public_holidays_names'] = $public_holidays_names;
  $data['month_name'] = $month_name;
  $data['total_shift_hours'] = $total_shift_hours;

  $data["total_half_day_paid"] = $total_half_day_paid;
  $data["total_full_day_paid"] = $total_full_day_paid;
  $data["total_half_day_unpaid"] = $total_half_day_unpaid;
  $data["total_medical_leaves"] = $total_medical_leaves;
  $data["total_break_late"] = $total_break_late;
  $data["total_missing_in_out"] = $total_missing_in_out;
  $data["total_absent_unpaid"] = $total_absent_unpaid;
  $data["total_early_late"] = $total_early_late;
  $data["total_rest_days_used"] = $total_rest_days_used;

  if ($cid == 66) {
    $data["total_bmi_ot"] = $total_bmi_ot;
    $data["total_bmi_ot_sunday"] = $total_bmi_ot_sunday;
    $data["total_bmi_ph_1"] = $total_bmi_ph_1;
    $data["total_bmi_ph_2"] = $total_bmi_ph_2;
    $data["total_bmi_ta"] = $total_bmi_ta;
    $data["total_bmi_ma"] = $total_bmi_ma;
    $data["total_bmi_ca"] = $total_bmi_ca;
    $data["total_bmi_spa"] = $total_bmi_spa;
    $data["total_bmi_aca"] = $total_bmi_aca;
    $data["total_bmi_fl"] = $total_bmi_fl;
    $data["total_bmi_cw"] = $total_bmi_cw;
    $data["total_bmi_mo"] = $total_bmi_mo;
    $data["total_bmi_shift1"] = $total_bmi_shift1;
    $data["total_bmi_shift2"] = $total_bmi_shift2;
    $data["total_bmi_shift3"] = $total_bmi_shift3;
    $data["bmi_attendance_allowance"] = $bmi_attendance_allowance;
  }

  if ($cid == 215) {
    $data["gbr_attendance_allowance"] = $gbr_attendance_allowance;
    $data["gbr_night_shifts"] = $gbr_night_shifts;
  }

  if ($cid == 152) {
    $data['lsk_non_worked_days'] = $lsk_non_worked_days;
  }

  if ($cid == 206) {
    $data["food_allowance_days"] = $food_allowance_days;
  }

  return $data;
}

function generate_full_summary_excel($data)
{
  $style = array(
    'alignment' => array(
      'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
      'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
    )
  );

  $object = new PHPExcel();

  $object->setActiveSheetIndex(0);
  $object->getDefaultStyle()->applyFromArray($style);

  $table_columns = array("Date", "Shift", "In", "Out", "Total Hours", "Work Hours", "Break Hours", "Late Hours", "Late (Break)", "Early Out", "OT", "OT(M)", "OT(PHx2)", "OT(PHx3)", "OT(RD)", "OT(OFF)", "Days", "Trip(A)", "Trip(B)", "Late Reason", "Remark");

  $name_columns = array("Name", "Special ID", "From", "To", "Position", "Department", "Generated at", "Generated by");

  $column = 0;

  foreach ($name_columns as $field) {
    $object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
    $object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

    $column++;
  }

  $object->getActiveSheet()->setCellValueByColumnAndRow(0, 2, $data["employee"]->first_name);
  $object->getActiveSheet()->setCellValueByColumnAndRow(1, 2, $data["employee"]->special_id);
  $object->getActiveSheet()->setCellValueByColumnAndRow(2, 2, $data["from_f"]);
  $object->getActiveSheet()->setCellValueByColumnAndRow(3, 2, $data["to_f"]);
  $object->getActiveSheet()->setCellValueByColumnAndRow(4, 2, $data["employee"]->position);
  $object->getActiveSheet()->setCellValueByColumnAndRow(5, 2, $data["employee"]->department);
  $object->getActiveSheet()->setCellValueByColumnAndRow(6, 2, date("d/m/Y H:i:s"));
  $object->getActiveSheet()->setCellValueByColumnAndRow(7, 2, get_user()["first_name"]);

  $column = 0;

  foreach ($table_columns as $field) {
    $object->getActiveSheet()->setCellValueByColumnAndRow($column, 4, $field);
    $object->getActiveSheet()->getStyleByColumnAndRow($column, 4)->getFont()->setBold(true);

    $column++;
  }
  $row = 5;
  foreach ($data['dates'] as $d) {
    foreach ($d->clockings as $key => $clock) {
      if ($key == 0) {
        // Check if the date is a public holiday
        $isPublicHoliday = in_array($d->date, $data["public_holidays"]);

        $object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $clock->day_f);
        if ($isPublicHoliday) {
        $object->getActiveSheet()->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true)->getColor()->setRGB('FF0000');
        }else {
          $object->getActiveSheet()->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true);
        }
        $count_clockings = count($d->clockings);
        if ($count_clockings > 1) {
          $start_merge = $row;
          $end_merge = $row + $count_clockings - 1;
          $object->getActiveSheet()->mergeCells('A' . $row . ':A' . $end_merge);
        }
      }
      if (isset($clock->code) && $clock->code == 'Break') {
        $object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $clock->code);
        $object->getActiveSheet()->getStyleByColumnAndRow(1, $row)->getFont()->getColor()->setRGB('FF0000');
        $object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $clock->clock_in);
        $object->getActiveSheet()->getStyleByColumnAndRow(2, $row)->getFont()->getColor()->setRGB('FF0000');
        $object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $clock->clock_out);
        $object->getActiveSheet()->getStyleByColumnAndRow(3, $row)->getFont()->getColor()->setRGB('FF0000');
      } else {
        $object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $clock->code);
        // $clock_in_check = admin_add_edit_check_clock_in(isset($d->clockings[0]->clock_in_id) ? $d->clockings[0]->clock_in_id : null);
        $clock_in_check = admin_add_edit_check_clock_in(isset($clock->clock_in_id) ? $clock->clock_in_id : null);
        if ($clock_in_check == 1) {
          $object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $clock->clock_in);
          $object->getActiveSheet()->getStyleByColumnAndRow(2, $row)->getFont()->setBold(true)->getColor()->setRGB('FF0000');
        } else {
          $object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $clock->clock_in);
        }
        // $clock_out_check = admin_add_edit_check_clock_out(isset($d->clockings[0]->clock_out_id) ? $d->clockings[0]->clock_out_id : null);
        $clock_out_check = admin_add_edit_check_clock_out(isset($clock->clock_out_id) ? $clock->clock_out_id : null);
        if ($clock_out_check == 1) {
          $object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $clock->clock_out);
          $object->getActiveSheet()->getStyleByColumnAndRow(3, $row)->getFont()->setBold(true)->getColor()->setRGB('FF0000');
        } else {
          $object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $clock->clock_out);
        }
      }
      // $object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $clock->total_time);
      if ($key == 0) {
        $object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $d->total_hours);
        $object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $d->work_hours);
        $object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $d->break_hours);
        $object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $d->late_hours);
        if (!$d->is_late) {
          $object->getActiveSheet()->getStyleByColumnAndRow(7, $row)->getFont()->setStrikethrough(true);
        }
        $object->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $d->break_late_hours);
        if (!$d->is_late_break) {
          $object->getActiveSheet()->getStyleByColumnAndRow(8, $row)->getFont()->setStrikethrough(true);
        }
        $object->getActiveSheet()->setCellValueByColumnAndRow(9, $row, $d->early_out);
        if (!$d->is_early_out) {
          $object->getActiveSheet()->getStyleByColumnAndRow(9, $row)->getFont()->setStrikethrough(true);
        }
        // $object->getActiveSheet()->setCellValueByColumnAndRow(10, $row, $d->short_hours);
        $ot_value = create_ot_rich_text($d);
        if (!in_array($d->date, $data["public_holidays"]) && !in_array($d->day_name, $data["rest_days"]) && $d->is_shift == 'true' && !$d->is_replaced_ph && !in_array($d->day_name, $data["off_days"]) && !$d->is_rest_day) {
          $object->getActiveSheet()->setCellValueByColumnAndRow(10, $row, $ot_value);
        }

        if ($d->x2 && (in_array($d->date, $data["public_holidays"]) || $d->is_replaced_ph)) {
          $object->getActiveSheet()->setCellValueByColumnAndRow(12, $row, $d->overtime_ph_x2);
        }

        if ($d->x3 && (in_array($d->date, $data["public_holidays"]) || $d->is_replaced_ph)) {
          $object->getActiveSheet()->setCellValueByColumnAndRow(13, $row, $d->overtime_ph_x3);
        }

        if (!in_array($d->date, $data["public_holidays"]) && (in_array($d->day_name, $data["rest_days"]) || $d->is_shift == 'false' || $d->is_rest_day) && !in_array($d->day_name, $data["off_days"])) {
          $object->getActiveSheet()->setCellValueByColumnAndRow(14, $row, $ot_value);
        }

        if (!in_array($d->date, $data["public_holidays"]) && in_array($d->day_name, $data["off_days"])) {
          $object->getActiveSheet()->setCellValueByColumnAndRow(15, $row, $ot_value);
        }

        $object->getActiveSheet()->setCellValueByColumnAndRow(16, $row, $d->days);
        $object->getActiveSheet()->setCellValueByColumnAndRow(17, $row, $d->trip_a);
        $object->getActiveSheet()->setCellValueByColumnAndRow(18, $row, $d->trip_b);
        $object->getActiveSheet()->setCellValueByColumnAndRow(19, $row, $clock->reason);
        if ($isPublicHoliday) {
          $public_holidays_names = $data['public_holidays_names'];
          // print_r($public_holidays_names[array_search($d->date,$data["public_holidays"])]);die;
          $object->getActiveSheet()->setCellValueByColumnAndRow(20, $row, $public_holidays_names[array_search($d->date,$data["public_holidays"])]);
        }else{
          $object->getActiveSheet()->setCellValueByColumnAndRow(20, $row, $clock->remark);
        }
        $count_clockings = count($d->clockings);
        if ($count_clockings > 1) {
          $start_merge = $row;
          $end_merge = $row + $count_clockings - 1;
          $object->getActiveSheet()->mergeCells('E' . $start_merge . ':E' . $end_merge);
          $object->getActiveSheet()->mergeCells('F' . $start_merge . ':F' . $end_merge);
          $object->getActiveSheet()->mergeCells('G' . $start_merge . ':G' . $end_merge);
          $object->getActiveSheet()->mergeCells('H' . $start_merge . ':H' . $end_merge);
          $object->getActiveSheet()->mergeCells('I' . $start_merge . ':I' . $end_merge);
          $object->getActiveSheet()->mergeCells('J' . $start_merge . ':J' . $end_merge);
          $object->getActiveSheet()->mergeCells('K' . $start_merge . ':K' . $end_merge);
          $object->getActiveSheet()->mergeCells('L' . $start_merge . ':L' . $end_merge);
          $object->getActiveSheet()->mergeCells('M' . $start_merge . ':M' . $end_merge);
          $object->getActiveSheet()->mergeCells('N' . $start_merge . ':N' . $end_merge);
          $object->getActiveSheet()->mergeCells('O' . $start_merge . ':O' . $end_merge);
          $object->getActiveSheet()->mergeCells('P' . $start_merge . ':P' . $end_merge);
          $object->getActiveSheet()->mergeCells('Q' . $start_merge . ':Q' . $end_merge);
          $object->getActiveSheet()->mergeCells('R' . $start_merge . ':R' . $end_merge);
          $object->getActiveSheet()->mergeCells('S' . $start_merge . ':S' . $end_merge);
          $object->getActiveSheet()->mergeCells('T' . $start_merge . ':T' . $end_merge);
          $object->getActiveSheet()->mergeCells('U' . $start_merge . ':U' . $end_merge);
        }
      }
      $row++;
    }
  }
  $object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, "Total");
  $object->getActiveSheet()->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true);
  $object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $data["total"]);
  $object->getActiveSheet()->getStyleByColumnAndRow(4, $row)->getFont()->setBold(true);
  $object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $data["work"]);
  $object->getActiveSheet()->getStyleByColumnAndRow(5, $row)->getFont()->setBold(true);
  $object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $data["break"]);
  $object->getActiveSheet()->getStyleByColumnAndRow(6, $row)->getFont()->setBold(true);
  $object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $data["late"]);
  $object->getActiveSheet()->getStyleByColumnAndRow(7, $row)->getFont()->setBold(true);
  $object->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $data["break_late"]);
  $object->getActiveSheet()->getStyleByColumnAndRow(8, $row)->getFont()->setBold(true);
  $object->getActiveSheet()->setCellValueByColumnAndRow(9, $row, $data["total_early"]);
  $object->getActiveSheet()->getStyleByColumnAndRow(9, $row)->getFont()->setBold(true);
  // $object->getActiveSheet()->setCellValueByColumnAndRow(10, $row, $data["total_short"]);
  // $object->getActiveSheet()->getStyleByColumnAndRow(10, $row)->getFont()->setBold(true);
  $object->getActiveSheet()->setCellValueByColumnAndRow(10, $row, $data["month_overtime"]);
  $object->getActiveSheet()->getStyleByColumnAndRow(10, $row)->getFont()->setBold(true);
  if ($data["lateness_time"] != $data["lateness_time_deducted"]) {
    $object->getActiveSheet()->getStyleByColumnAndRow(10, $row)->getFont()->setStrikethrough(true);
  }
  $object->getActiveSheet()->setCellValueByColumnAndRow(12, $row, $data["month_overtime_ph_x2"]);
  $object->getActiveSheet()->getStyleByColumnAndRow(12, $row)->getFont()->setBold(true);
  $object->getActiveSheet()->setCellValueByColumnAndRow(13, $row, $data["month_overtime_ph_x3"]);
  $object->getActiveSheet()->getStyleByColumnAndRow(13, $row)->getFont()->setBold(true);
  $object->getActiveSheet()->setCellValueByColumnAndRow(14, $row, $data["month_overtime_rd"]);
  $object->getActiveSheet()->getStyleByColumnAndRow(14, $row)->getFont()->setBold(true);
  $object->getActiveSheet()->setCellValueByColumnAndRow(15, $row, $data["month_overtime_off"]);
  $object->getActiveSheet()->getStyleByColumnAndRow(15, $row)->getFont()->setBold(true);
  $object->getActiveSheet()->setCellValueByColumnAndRow(16, $row, $data["total_days"]);
  $object->getActiveSheet()->getStyleByColumnAndRow(16, $row)->getFont()->setBold(true);
  $object->getActiveSheet()->setCellValueByColumnAndRow(17, $row, $data["total_trip_a"]);
  $object->getActiveSheet()->getStyleByColumnAndRow(17, $row)->getFont()->setBold(true);
  $object->getActiveSheet()->setCellValueByColumnAndRow(18, $row, $data["total_trip_b"]);
  $object->getActiveSheet()->getStyleByColumnAndRow(18, $row)->getFont()->setBold(true);
  $object->getActiveSheet()->mergeCells('A' . $row . ':D' . $row);
  $object->getActiveSheet()->mergeCells('K' . $row . ':L' . $row);
  $object->getActiveSheet()->mergeCells('T' . $row . ':U' . $row);

  $row += 2;
  $object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, "Lateness Time");
  $object->getActiveSheet()->getStyleByColumnAndRow(4, $row)->getFont()->setBold(true);
  $object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $data["lateness_time"]);
  $object->getActiveSheet()->getStyleByColumnAndRow(7, $row)->getFont()->setBold(true);
  if ($data["lateness_time"] != $data["lateness_time_deducted"]) {
    $object->getActiveSheet()->getStyleByColumnAndRow(7, $row)->getFont()->setStrikethrough(true);
  }

  $object->getActiveSheet()->mergeCells('E' . $row . ':G' . $row);
  $object->getActiveSheet()->mergeCells('H' . $row . ':J' . $row);

  if ($data["lateness_time"] != $data["lateness_time_deducted"]) {
    $row++;
    $object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, "After Deduction");
    $object->getActiveSheet()->getStyleByColumnAndRow(4, $row)->getFont()->setBold(true);
    $object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $data["lateness_time_deducted"]);
    $object->getActiveSheet()->getStyleByColumnAndRow(7, $row)->getFont()->setBold(true);
    $object->getActiveSheet()->setCellValueByColumnAndRow(10, $row, $data["month_overtime_deducted"]);
    $object->getActiveSheet()->getStyleByColumnAndRow(10, $row)->getFont()->setBold(true);
    $object->getActiveSheet()->mergeCells('E' . $row . ':G' . $row);
    $object->getActiveSheet()->mergeCells('H' . $row . ':J' . $row);
    $object->getActiveSheet()->mergeCells('K' . $row . ':L' . $row);
  }

  foreach (range('A', 'U') as $columnID) {
    $object->getActiveSheet()->getColumnDimension($columnID)
      ->setAutoSize(true);
  }

  return $object;
}

function is_replacement($list, $date)
{
  foreach ($list as $li) {
    if ($li->from == $date || $li->to == $date) {
      return $li;
    }
  }
  return null;
}

function get_empty_data(
  $id,
  $date,
  $employee,
  $obj,
  $ot_settings,
  $approved_ot_list,
  $is_ot_list,
  $manual_ot_list,
  $company_working_hours,
  $public_holidays,
  $rest_days,
  $result_list,
  $result_list_overnight,
  $early_ot_settings
) {
  $apply_overtime = $employee->is_ot == 1 ? true : false;
  $apply_early_overtime = $employee->is_early_ot == 1 ? true : false;

  $inc_late_in = $employee->inc_late_in == 1 ? true : false;
  $inc_late_break = $employee->inc_late_break  == 1 ? true : false;
  $inc_early_out = $employee->inc_early_out == 1 ? true : false;
  $inc_short_hours = $employee->inc_short_hours == 1 ? true : false;
  $void_minutes = $employee->void_lateness_time_if_less_than;

  $mysql_date = $date->format('Y-m-d');

  $shift_list = get_shift_list($id, $mysql_date, $mysql_date);
  $result_list = $result_list; // changed
  $result_list_overnight = $result_list_overnight; // changed
  $approved_ot_list = $approved_ot_list; // changed 
  $is_ot_list = $is_ot_list; // changed

  $is_late_list = array();
  $is_late_break_list = array();
  $is_early_out_list = array();
  $manual_late_list = array();
  $manual_late_break_list = array();
  //$shift_list = array(); // changed
  $remark_list = array();
  $manual_ot_list = $manual_ot_list;
  $manual_early_out_list = array();
  $manual_short_hours_list = array();
  $trip_a_list = array();
  $trip_b_list = array();
  $company_working_hours_decimal = toDecimal($company_working_hours);

  $date_f = $date->format('d-m-Y');
  $obj->shift_hours = "";
  $is_ot = false;
  $is_late = true;
  $is_late_break = true;
  $is_early_out = true;
  $overnight = false;
  $is_shift = false;
  $shift_check = search_from_list($shift_list, $obj->date);
  $obj->shift_check = $shift_check;
  $half_day = false;
  if ($shift_check) {
    $is_shift = true;
    if ($shift_check->half_day == "Yes") {
      $half_day = true;
    }
    $obj->shift_hours = $shift_check->shift_hours;
  }
  if ($shift_check && $shift_check->overnight == "Yes") {
    $result = search_clocking($result_list_overnight, $obj->date);
    $overnight = true;
  } else {
    $result = search_clocking($result_list, $obj->date);
    if (!$shift_check) {
      $result = remove_duplicate_clockings($result, $obj->date, $shift_list, $result_list_overnight);
    }
  }
  $obj->overnight = $overnight ? "true" : "false";
  $obj->is_shift = $is_shift ? "true" : "false";
  // changed
  $obj->paid_leave = 0;
  $obj->unpaid_leave = 0;
  $obj->working_day = 0;
  $obj->absent_day = 0;
  $obj->worked_day = 0;
  $obj->unpaid_leaves_absent_day = [];
  $obj->worked_holiday_array = [];
  $obj->worked_rest_day_array = [];
  $obj->worked_off_day_array = [];

  $total_hours = "";
  $work_hours = "";
  $break_hours = "";
  $late_hours = "";
  $break_late_hours = "";
  $early_out = "";
  $short_hours = "";
  $tripA = 0;
  $tripB = 0;
  $formatted_data = array();
  $is_ot_result = search_from_list($is_ot_list, $obj->date);
  if ($is_ot_result) {
    $is_ot = $is_ot_result->is_ot == "Y" ? true : false;
  } else {
    $is_ot = get_is_ot_status($approved_ot_list, $shift_check, $obj->date, $id);
  }

  $is_late_result = search_from_list($is_late_list, $obj->date);
  if ($is_late_result) {
    $is_late = $is_late_result->is_late == "Y" ? true : false;
  }

  $is_late_break_result = search_from_list($is_late_break_list, $obj->date);
  if ($is_late_break_result) {
    $is_late_break = $is_late_break_result->is_late_break == "Y" ? true : false;
  }

  $is_early_out_result = search_from_list($is_early_out_list, $obj->date);
  if ($is_early_out_result) {
    $is_early_out = $is_early_out_result->is_early_out == "Y" ? true : false;
  }

  $last_out = "";
  foreach ($result as $key => $value) {
    if ($key == 0) $value->day_f = $obj->date_string;
    if ($key == 0 && $value->shift_remark != null && $value->shift_remark != "") {
      $value->remark = $value->shift_remark;
    }
    // $value->total_time = total_time($value->clock_in_1, $value->clock_out_1);
    $value->total_time = calculate_total_hours($value->clock_in_1, $value->clock_out_1, $value->start_time, $value->early_ot_start, $value->early_ot_end, $value->search_date);
    // var_dump($value->total_time);
    if ($value->name == "") {
      $value->name = "N/A";
    }
    if ($value->code == "") {
      $value->code = "N/A";
    }
    $value->is_break = false;

    $formatted_data[] = $value;
    if (array_key_exists($key + 1, $result)) {
      $x = new stdClass();
      $x->day_f = $obj->date_string;
      $x->overtime_starts = $value->overtime_starts;
      $x->early_ot_start = $value->early_ot_start;
      $x->early_ot_end = $value->early_ot_end;
      $x->grace_time = $value->grace_time;
      $x->clock_in = $value->clock_out;
      $x->clock_in_1 = $value->clock_out_1;
      $x->clock_out = $result[$key + 1]->clock_in;
      $x->clock_out_1 = $result[$key + 1]->clock_in_1;
      $x->name = "Break";
      $x->code = "Break";
      $x->is_break = true;
      $x->reason = "";
      $x->remark = "";
      $x->is_ot = $is_ot;
      $x->total_time = total_time($result[$key + 1]->clock_in_1, $value->clock_out_1);
      $formatted_data[] = $x;
    } else {
      $last_out = $value->clock_out_1;
    }
  }
  if (!$half_day) {
    $manual_early_out = search_from_list($manual_early_out_list, $obj->date);
    if ($manual_early_out) {
      $early_out = $manual_early_out->early_out;
    } else if ($last_out != "" && $shift_check && $shift_check->void_early_out == "No") {
      $early_out = calculate_early_out($last_out, $shift_check->end_time, $obj->date, $overnight);
    }
  }

  $obj->early_out = "";
  $obj->clockings = $formatted_data;
  if ($result) {
    $v = $result[0];
  }

  $break_and_late_hours = calculate_break_and_late_hours($obj->clockings, $v);
  $work_hours = $break_and_late_hours->work_hours;
  $break_hours = $break_and_late_hours->break_hours;
  $breaks_array = $break_and_late_hours->breaks_array;
  $shift_break_hours = $break_and_late_hours->shift_break_hours;
  $shift_breaks_array = $break_and_late_hours->shift_breaks_array;

  foreach ($obj->clockings as $key => $value) {
    if ($key != 0) {
      $value->day_f = '';
    }
    $total_hours = add_time($total_hours, $value->total_time);
    if ($key == 0) {
      $manual_late = search_from_list($manual_late_list, $obj->date);
      if ($manual_late) {
        $late_hours = $manual_late->late_hours;
      } else if (isset($v) && $v->is_leave != "" && $v->is_leave != "yes" && $v->void_late_in == "No") {
        if ($v->grace_time != "") {
          if ($overnight) {
            $grace_time = $obj->date . " " . $v->grace_time . ":00";
            $grace_time_stamp = strtotime($grace_time);
            $mid_day = $obj->date . " 12:00:00";
            $mid_day_stamp = strtotime($mid_day);
            if ($mid_day_stamp > $grace_time_stamp) {
              $grace_time_stamp += 24 * 3600;
            }
            $clock_in_stamp = strtotime($v->clock_in_o);

            if ($clock_in_stamp > $grace_time_stamp) {
              $late_stamp = $clock_in_stamp - $grace_time_stamp;
              date_default_timezone_set('UTC');
              $late_hours = date('H:i', $late_stamp);
              date_default_timezone_set("Asia/Kuala_Lumpur");
            }
          } else if (intval(str_replace(":", "", $v->clock_in)) > intval(str_replace(":", "", $v->grace_time))) {
            $late_hours = sub_time($v->clock_in, $v->grace_time);
          }
        }
      }
    }
  }

  $break_not_taken = "00:00";
  $extra_break_not_taken = "00:00";
  if (isset($v)) {
    $break_not_taken = calculate_break_not_taken($break_hours, $breaks_array, $v);
  }
  if ($work_hours != "" && $work_hours != "00:00") {
    $work_hours = sub_time($work_hours, $break_not_taken);
  }
  if (isset($v)) {
    $extra_break_not_taken = calculate_extra_break_not_taken($breaks_array, $v, $work_hours);
  }
  if ($work_hours != "" && $work_hours != "00:00") {
    $work_hours = sub_time($work_hours, $extra_break_not_taken);
  }

  if (!$half_day) {
    $manual_short_hours = search_from_list($manual_short_hours_list, $obj->date);
    if ($manual_short_hours) {
      $short_hours = $manual_short_hours->short_hours;
    } else {
      $short_hours = calculate_short_hours($company_working_hours, $work_hours);
    }
  }

  $trip_a = search_from_list($trip_a_list, $obj->date);
  $trip_b = search_from_list($trip_b_list, $obj->date);
  if ($trip_a) {
    $tripA = $trip_a->no_of_trips;
  }
  if ($trip_b) {
    $tripB = $trip_b->no_of_trips;
  }

  $obj->total_hours = "";
  $obj->work_hours = "";
  $obj->break_hours = "";
  $obj->late_hours = "";
  $obj->short_hours = "";
  $obj->trip_a = $tripA;
  $obj->trip_b = $tripB;
  if (isset($v) && !$half_day) {
    $manual_late_break = search_from_list($manual_late_break_list, $obj->date);
    if ($manual_late_break) {
      $break_late_hours = $manual_late_break->late_hours_break;
    } else {
      if ($employee->ignore_breaks_after_endtime == 0) {
        $break_late_hours = calculate_break_late($break_hours, $breaks_array, $v, $work_hours, $obj->is_shift);
      } else {
        $break_late_hours = calculate_break_late($shift_break_hours, $shift_breaks_array, $v, $work_hours, $obj->is_shift);
      }
    }
  }
  $obj->break_late_hours = "";
  $days = "";
  if ($result) {
    $v = $result[0];
    $days = 1;
    if ($v->is_leave == "yes" && $v->half_day == "Yes") {
      $days = 0.5;
    }
  }
  $obj->days = "";
  $overtime = "";
  $early_overtime = "";
  $overtime_m = "";
  $overtime_type = "+";
  $is_manual_exist = false;
  $manual_ot = search_from_list($manual_ot_list, $obj->date);
  if ($manual_ot) {
    $overtime_m = $manual_ot->overtime;
    $overtime_type = $manual_ot->type;
    $is_manual_exist = true;
    if ($overtime_type == "-") {
      $overtime_m = "-" . $overtime_m;
    }
  }
  $round_of_ot = 1;
  if ($shift_check) {
    $round_of_ot = $shift_check->round_off_ot;
  }
  $overtime = calculate_final_overtime($result, $obj->clockings, $date_f, $overnight, $apply_overtime, $apply_early_overtime, $work_hours, $company_working_hours, $employee->ot_type, $employee->ot_round, $employee->round_first_hour_only, $employee->round_by_exact_hour, $employee->different_first_hour_rounding, $ot_settings, $obj->shift_hours, $round_of_ot, $company_working_hours_decimal, $employee->ot_round, $early_ot_settings);

  $obj->is_manual_exist = $is_manual_exist;
  $obj->clockings = [];
  $obj->overtime = $overtime;
  $obj->overtime_m = $overtime_m;
  $obj->overtime_type = $overtime_type;
  $obj->is_ot = $is_ot;
  $obj->is_late = $is_late;
  $obj->is_late_break = $is_late_break;
  $obj->is_early_out = $is_early_out;

  $is_extra_ot = false;
  if ($obj->shift_check) {
    if (is_extra_ot_given($work_hours, $obj->shift_check->extra_ot, $obj->shift_check->extra_ot_worked_hours_more_than, $obj->shift_check->extra_ot_hours)) {
      $is_extra_ot = true;
    }
  }

  $obj->is_extra_ot = $is_extra_ot;

  return $obj;
}

function get_replaced_data($id, $date, $employee, $obj, $ot_settings, $public_holidays, $rest_days, $is_ot_list, $approved_ot_list, $cid, &$worked_rest_days_array, &$worked_holidays_array, &$unpaid_leaves_absent_days)
{
  $apply_overtime = $employee->is_ot == 1 ? true : false;
  $apply_early_overtime = $employee->is_early_ot == 1 ? true : false;

  $inc_late_in = $employee->inc_late_in == 1 ? true : false;
  $inc_late_break = $employee->inc_late_break  == 1 ? true : false;
  $inc_early_out = $employee->inc_early_out == 1 ? true : false;
  $inc_short_hours = $employee->inc_short_hours == 1 ? true : false;
  $void_minutes = $employee->void_lateness_time_if_less_than;

  if ($employee->deduct_from_ot_single != "not_sure") {
    $deduct_from_ot = $employee->deduct_from_ot_single == "yes" ? true : false;
  } else {
    $deduct_from_ot = $employee->deduct_from_ot == 1 ? true : false;
  }
  $deduction_date = $employee->deduction_date;

  $employee->deduct_from_ot = $deduct_from_ot;
  $ci = &get_instance();

  $current_user = get_user();
  $cid = $current_user['company_id'];

  $mysql_date = $date->format('Y-m-d');
  $long_date = $date->format("d/m/Y");

  $company_working_hours = get_company_working_hours($cid);
  $company_working_hours = get_employee_working_hours($company_working_hours, $id);
  $company_working_hours = $company_working_hours->working_hours;
  $company_working_hours_decimal = toDecimal($company_working_hours);
  $shifts = $ci->db->select('id')->from('shifts')->where('branch_id', $employee->branch_id)->where('is_leave', 'no')->get()->result();

  $shift_ids = array(0);
  foreach ($shifts as $s) {
    $shift_ids[] = $s->id;
  }

  $shift_list = get_shift_list($id, $mysql_date, $mysql_date);
  $result_list = get_result_list(array($id), $mysql_date, $mysql_date);
  $result_list_overnight = get_result_list_overnight(array($id), $mysql_date, $mysql_date);
  $approved_ot_list = get_approved_ot_list($id, $mysql_date, $mysql_date);
  $is_ot_list = get_is_ot_list($id, $mysql_date, $mysql_date);
  $is_late_list = get_is_late_list($id, $mysql_date, $mysql_date);
  $is_late_break_list = get_is_late_break_list($id, $mysql_date, $mysql_date);
  $is_early_out_list = get_is_early_out_list($id, $mysql_date, $mysql_date);
  $manual_late_list = get_manual_late_list($id, $mysql_date, $mysql_date);
  $manual_late_break_list = get_manual_late_break_list($id, $mysql_date, $mysql_date);
  $shift_list = get_shift_list($id, $mysql_date, $mysql_date);
  $remark_list = get_remark_list($id, $mysql_date, $mysql_date);
  $manual_ot_list = array(); // changed
  $manual_early_out_list = get_manual_early_out_list($id, $mysql_date, $mysql_date);
  $manual_short_hours_list = get_manual_short_hours_list($id, $mysql_date, $mysql_date);
  $trip_a_list = get_trip_a_list($id, $mysql_date, $mysql_date);
  $trip_b_list = get_trip_b_list($id, $mysql_date, $mysql_date);

  $date_f = $date->format('d-m-Y');
  $obj->shift_hours = "";
  $is_ot = false;
  $is_late = true;
  $is_late_break = true;
  $is_early_out = true;
  $overnight = false;
  $is_shift = false;
  $shift_check = search_from_list($shift_list, $mysql_date);
  $obj->shift_check = $shift_check;
  $half_day = false;
  if ($shift_check) {
    $is_shift = true;
    if ($shift_check->half_day == "Yes") {
      $half_day = true;
    }
    $obj->shift_hours = $shift_check->shift_hours;
  }

  if ($shift_check && $shift_check->overnight == "Yes") {
    $result = search_clocking($result_list_overnight, $mysql_date);
    $overnight = true;
  } else {
    $result = search_clocking($result_list, $mysql_date);
    if (!$shift_check) {
      $result = remove_duplicate_clockings($result, $mysql_date, $shift_list, $result_list_overnight);
    }
  }

  $obj->overnight = $overnight ? "true" : "false";
  $obj->is_shift = $is_shift ? "true" : "false";
  // changed
  $obj->unpaid_leave = 0;
  $obj->absent_day = 0;
  $obj->paid_leave = 0;
  $obj->working_day = 0;
  $obj->worked_day = 0;
  $obj->worked_holiday = 0;
  $obj->worked_rest_day = 0;
  // changed
  if (!in_array($obj->date, $public_holidays) && !in_array($obj->day_name, $rest_days)) {
    $check = false;
    if ($shift_check) {
      $add_day = 1;
      if ($shift_check->half_day == "Yes") {
        $add_day = 0.5;
      }
      if ($shift_check->is_leave == "yes" && $shift_check->is_paid == "yes") {
        $obj->paid_leave += $add_day;
        $check = true;
      } else if ($shift_check->is_leave == "yes" && $shift_check->is_paid == "no") {
        $obj->unpaid_leave += $add_day;
        $check = true;
        if ($shift_check->start_time != NULL && $shift_check->end_time != NULL) {
          $unpaid_leaves_absent_days[$date->format("d/m/Y")][] = [
            "employee_special_id" => $employee->special_id,
            "unpaid_leave" => $add_day,
            "branch_id" => $employee->branch_id,
          ];
        }
      }
      $obj->working_day++;
    }
    if (!$check && empty($result) && $shift_check) {
      if ($obj->date <= date('Y-m-d')) {
        if ($shift_check->start_time != NULL && $shift_check->end_time != NULL) {
          $obj->absent_day++;
        }
        if ($shift_check->start_time != NULL && $shift_check->end_time != NULL) {
          $unpaid_leaves_absent_days[$date->format("d/m/Y")][] = [
            "employee_special_id" => $employee->special_id,
            "unpaid_leave" => $add_day,
            "branch_id" => $employee->branch_id,
          ];
        }
      }
    }
  }

  $total_hours = "";
  $work_hours = "";
  $break_hours = "";
  $late_hours = "";
  $break_late_hours = "";
  $early_out = "";
  $short_hours = "";
  $tripA = 0;
  $tripB = 0;
  $formatted_data = array();
  $is_ot_result = search_from_list($is_ot_list, $mysql_date);
  if ($is_ot_result) {
    $is_ot = $is_ot_result->is_ot == "Y" ? true : false;
  } else {
    $is_ot = get_is_ot_status($approved_ot_list, $shift_check, $mysql_date, $id);
  }

  $is_late_result = search_from_list($is_late_list, $mysql_date);
  if ($is_late_result) {
    $is_late = $is_late_result->is_late == "Y" ? true : false;
  }

  $is_late_break_result = search_from_list($is_late_break_list, $mysql_date);
  if ($is_late_break_result) {
    $is_late_break = $is_late_break_result->is_late_break == "Y" ? true : false;
  }

  $is_early_out_result = search_from_list($is_early_out_list, $mysql_date);
  if ($is_early_out_result) {
    $is_early_out = $is_early_out_result->is_early_out == "Y" ? true : false;
  }

  $last_out = "";
  foreach ($result as $key => $value) {
    if ($key == 0) $value->day_f = $obj->date_string;
    if ($key == 0) {
      $value->remark = "replacement leave from {$long_date}";
    }
    // $value->total_time = total_time($value->clock_in_1, $value->clock_out_1);
    $value->total_time = calculate_total_hours($value->clock_in_1, $value->clock_out_1, $value->start_time, $value->early_ot_start, $value->early_ot_end, $value->search_date);
    if ($value->name == "") {
      $value->name = "N/A";
    }
    if ($value->code == "") {
      $value->code = "N/A";
    }
    $value->is_break = false;

    $formatted_data[] = $value;
    if (array_key_exists($key + 1, $result)) {
      $x = new stdClass();
      $x->day_f = $obj->date_string;
      $x->overtime_starts = $value->overtime_starts;
      $x->early_ot_start = $value->early_ot_start;
      $x->early_ot_end = $value->early_ot_end;
      $x->grace_time = $value->grace_time;
      $x->clock_in = $value->clock_out;
      $x->clock_in_1 = $value->clock_out_1;
      $x->clock_out = $result[$key + 1]->clock_in;
      $x->clock_out_1 = $result[$key + 1]->clock_in_1;
      $x->name = "Break";
      $x->code = "Break";
      $x->is_break = true;
      $x->reason = "";
      $x->remark = "";
      $x->is_ot = $is_ot;
      $x->total_time = total_time($result[$key + 1]->clock_in_1, $value->clock_out_1);
      $formatted_data[] = $x;
    } else {
      $last_out = $value->clock_out_1;
    }
  }
  if (!$half_day) {
    $manual_early_out = search_from_list($manual_early_out_list, $mysql_date);
    if ($manual_early_out) {
      $early_out = $manual_early_out->early_out;
    } else if ($last_out != "" && $shift_check && $shift_check->void_early_out == "No") {
      $early_out = calculate_early_out($last_out, $shift_check->end_time, $mysql_date, $overnight);
    }
  }

  $obj->early_out = $early_out;
  $obj->clockings = $formatted_data;
  if ($result) {
    $v = $result[0];
  }

  $break_and_late_hours = calculate_break_and_late_hours($obj->clockings, $v);
  $work_hours = $break_and_late_hours->work_hours;
  $break_hours = $break_and_late_hours->break_hours;
  $breaks_array = $break_and_late_hours->breaks_array;
  $shift_break_hours = $break_and_late_hours->shift_break_hours;
  $shift_breaks_array = $break_and_late_hours->shift_breaks_array;

  foreach ($obj->clockings as $key => $value) {
    if ($key != 0) {
      $value->day_f = '';
    }
    $total_hours = add_time($total_hours, $value->total_time);
    if ($key == 0) {
      $manual_late = search_from_list($manual_late_list, $mysql_date);
      if ($manual_late) {
        $late_hours = $manual_late->late_hours;
      } else if (isset($v) && $v->is_leave != "" && $v->is_leave != "yes" && $v->void_late_in == "No") {
        if ($v->grace_time != "") {
          if ($overnight) {
            $grace_time = $mysql_date . " " . $v->grace_time . ":00";
            $grace_time_stamp = strtotime($grace_time);
            $mid_day = $mysql_date . " 12:00:00";
            $mid_day_stamp = strtotime($mid_day);
            if ($mid_day_stamp > $grace_time_stamp) {
              $grace_time_stamp += 24 * 3600;
            }
            $clock_in_stamp = strtotime($v->clock_in_o);

            if ($clock_in_stamp > $grace_time_stamp) {
              $late_stamp = $clock_in_stamp - $grace_time_stamp;
              date_default_timezone_set('UTC');
              $late_hours = date('H:i', $late_stamp);
              date_default_timezone_set("Asia/Kuala_Lumpur");
            }
          } else if (intval(str_replace(":", "", $v->clock_in)) > intval(str_replace(":", "", $v->grace_time))) {
            $late_hours = sub_time($v->clock_in, $v->grace_time);
          }
        }
      }
    }
  }

  $break_not_taken = "00:00";
  $extra_break_not_taken = "00:00";
  if (isset($v)) {
    $break_not_taken = calculate_break_not_taken($break_hours, $breaks_array, $v);
  }
  if ($work_hours != "" && $work_hours != "00:00") {
    $work_hours = sub_time($work_hours, $break_not_taken);
  }
  if (isset($v)) {
    $extra_break_not_taken = calculate_extra_break_not_taken($breaks_array, $v, $work_hours);
  }
  if ($work_hours != "" && $work_hours != "00:00") {
    $work_hours = sub_time($work_hours, $extra_break_not_taken);
  }

  if (!$half_day) {
    $manual_short_hours = search_from_list($manual_short_hours_list, $mysql_date);
    if ($manual_short_hours) {
      $short_hours = $manual_short_hours->short_hours;
    } else {
      $short_hours = calculate_short_hours($company_working_hours, $work_hours);
    }
  }

  $trip_a = search_from_list($trip_a_list, $mysql_date);
  $trip_b = search_from_list($trip_b_list, $mysql_date);
  if ($trip_a) {
    $tripA = $trip_a->no_of_trips;
  }
  if ($trip_b) {
    $tripB = $trip_b->no_of_trips;
  }

  $obj->total_hours = $total_hours;
  $obj->work_hours = $work_hours;
  $obj->break_hours = $break_hours;
  $obj->late_hours = $late_hours;
  $obj->short_hours = $short_hours;
  $obj->trip_a = $tripA;
  $obj->trip_b = $tripB;
  if (isset($v) && !$half_day) {
    $manual_late_break = search_from_list($manual_late_break_list, $mysql_date);
    if ($manual_late_break) {
      $break_late_hours = $manual_late_break->late_hours_break;
    } else {
      if ($employee->ignore_breaks_after_endtime == 0) {
        $break_late_hours = calculate_break_late($break_hours, $breaks_array, $v, $work_hours, $obj->is_shift);
      } else {
        $break_late_hours = calculate_break_late($shift_break_hours, $shift_breaks_array, $v, $work_hours, $obj->is_shift);
      }
    }
  }
  $obj->break_late_hours = $break_late_hours;
  $days = "";
  if ($result) {
    $v = $result[0];
    $days = 1;
    if ($v->is_leave == "yes" && $v->half_day == "Yes") {
      $days = 0.5;
    }
    if (in_array($obj->date, $public_holidays)) {
      $obj->worked_holiday += $days;
      $worked_holidays_array[$date->format("d/m/Y")][] = [
        "employee_special_id" => $employee->special_id,
        "worked_holiday" => 1,
        "branch_id" => $employee->branch_id,
      ];
    } else if (in_array($obj->day_name, $rest_days) || $v->name == "N/A") {
      $obj->worked_rest_day += $days;
      $worked_rest_days_array[$date->format("d/m/Y")][] = [
        "employee_special_id" => $employee->special_id,
        "worked_rest_day" => 1,
        "branch_id" => $employee->branch_id,
      ];
    } else {
      $obj->worked_day += $days;
    }
  }
  $obj->days = $days;
  $overtime = "";
  $early_overtime = "";
  $overtime_m = "";
  $overtime_type = "+";
  $is_manual_exist = false;
  $manual_ot = search_from_list($manual_ot_list, $mysql_date);
  if ($manual_ot) {
    $overtime_m = $manual_ot->overtime;
    $overtime_type = $manual_ot->type;
    $is_manual_exist = true;
    if ($overtime_type == "-") {
      $overtime_m = "-" . $overtime_m;
    }
  }

  $overtime = "";

  $obj->is_manual_exist = $is_manual_exist;
  $obj->overtime = $overtime;
  $obj->overtime_m = $overtime_m;
  $obj->overtime_type = $overtime_type;
  $obj->is_ot = $is_ot;
  $obj->is_late = $is_late;
  $obj->is_late_break = $is_late_break;
  $obj->is_early_out = $is_early_out;

  return $obj;
}

function calculate_extra_ot($overtime, $work_hours, $is_extra_ot, $worked_hours_more_than, $extra_ot_hours)
{
  if ($is_extra_ot == "N" || is_null($worked_hours_more_than) || is_null($extra_ot_hours)) return $overtime;

  if (strtotime($work_hours) > strtotime($worked_hours_more_than)) {
    return add_time($overtime, $extra_ot_hours);
  }

  return $overtime;
}

function is_extra_ot_given($work_hours, $is_extra_ot, $worked_hours_more_than, $extra_ot_hours)
{
  if ($is_extra_ot == "N" || is_null($worked_hours_more_than) || is_null($extra_ot_hours)) return false;
  if (strtotime($work_hours) > strtotime($worked_hours_more_than)) return true;
  return false;
}

function convert_date($from, $to, $date_string)
{
  $date = DateTime::createFromFormat($from, $date_string);
  return $date->format($to);
}

/**
 * get list of manually replaced public holidays for an employee
 *
 * @param int $id
 * @param string $first_day
 * @param string $last_day
 * @return array
 */
function get_replaced_ph_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('*')->from('replaced_ph_days')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

/**
 *  Funtion to calculate break hours and work_hours
 * 
 * @param array<stdClass> $clockings
 * @param stdClass $v
 * 
 * @return stdClass
 */
// function calculate_break_and_late_hours(&$clockings, &$v)
// {
//   $work_hours = "";
//   $break_hours = "00:00";
//   $breaks_array = [];
//   $clockings_count = count($clockings);
//   for ($i = 0, $j = 0; $i < $clockings_count; $i++) {
//     if ($i % 2 === 0) {
//       $work_hours = add_time($work_hours, $clockings[$i]->total_time);
//       // $j++;
//     } else {
//       if ($i < 12) {
//         $current_break = $v->{"break_" . ceil($i / 2)};
//         $current_consider_break = $v->{"consider_break_" . ceil($i / 2)};
//         $next_break = $v->{"break_" . (ceil($i / 2) + 1)};

//         if ($current_break != "") {
//           if ($current_consider_break == 0) {
//             if (!isset($clockings[$i]) || $clockings[$i]->total_time == "00:00" || $clockings[$i]->total_time > "00:40") {
//               $work_hours = add_time($work_hours, $current_break);
//               if (isset($clockings[$i]->total_time)) {
//                 $break_hours = add_time($break_hours, $clockings[$i]->total_time);
//               }
//               $j = $i;
//               $j++;
//             } else {
//               if ($clockings[$i]->total_time > $current_break) {
//                 $work_hours = add_time($work_hours, $current_break);
//                 $break_hours = add_time($break_hours, sub_time($clockings[$i]->total_time, $current_break));
//               } else {
//                 $work_hours = add_time($work_hours, $clockings[$i]->total_time);
//               }
//               // $j++;
//             }
//           } else {
//             if (isset($clockings[$i])) {
//               $break_hours = add_time($break_hours, $clockings[$i]->total_time);
//               // $j++;
//             }
//           }
//         } else {
//           $break_hours = add_time($break_hours, $clockings[$i]->total_time);
//         }

//         // if ($current_break != "" && $current_consider_break == 0) {
//         //   if ($clockings[$j]->total_time > $current_break) {
//         //     if ($clockings[$j]->total_time > "00:40") {
//         //       $break_hours = add_time($break_hours, $clockings[$j]->total_time);
//         //     } else {
//         //       $work_hours = add_time($work_hours, $current_break);
//         //       $break_hours = add_time($break_hours, sub_time($clockings[$j]->total_time, $current_break));
//         //       $j++; //should be done here
//         //     }
//         //   } else {
//         //     $work_hours = add_time($work_hours, $clockings[$j]->total_time);
//         //   }
//         // } else {
//         //   $break_hours = add_time($break_hours, $clockings[$j]->total_time);
//         //   $j++;
//         // }

//         // $break_hours = add_time($break_hours, $clockings[$j]->total_time);
//         // $j++;
//       } else {
//         $break_hours = add_time($break_hours, $clockings[$i]->total_time);
//       }
//       $breaks_array[] = $clockings[$i]->total_time;
//     }
//   }
//   // foreach ($clockings as $key => $value) {
//   //   if ($key % 2 == 0) {
//   //     $work_hours = add_time($work_hours, $value->total_time);
//   //   } else {
//   //     if ($key < 12) {
//   //       $current_break = $v->{"break_" . ceil($key / 2)};
//   //       $current_consider_break = $v->{"consider_break_" . ceil($key / 2)};
//   //       $next_break = $v->{"break_" . (ceil($key / 2) + 1)};
//   //       $next_consider_break = $v->{"consider_break_" . (ceil($key / 2) + 1)};

//   //       if (isset($current_break) && $current_consider_break == 0) {
//   //         if ($value->total_time > $current_break) {
//   //           if ($value->total_time > "00:40") {
//   //             $break_hours = add_time($break_hours, $value->total_time);
//   //           } else {
//   //             $work_hours = add_time($work_hours, $current_break);
//   //             $break_hours = add_time($break_hours, sub_time($value->total_time, $current_break));
//   //           }
//   //         } else {
//   //           $work_hours = add_time($work_hours, $value->total_time);
//   //         }
//   //       } else {
//   //         $break_hours = add_time($break_hours, $value->total_time);
//   //       }
//   //     } else {
//   //       $break_hours = add_time($break_hours, $value->total_time);
//   //     }
//   //     $breaks_array[] = $value->total_time;
//   //   }
//   // }
//   return (object)["work_hours" => $work_hours, "break_hours" => $break_hours, "breaks_array" => $breaks_array];
// }

function calculate_break_and_late_hours(&$clockings, &$v, $overnight = false)
{
  if(empty($v)){
    return (object) [
      "work_hours" => "",
      "break_hours" => "00:00",
      "breaks_array" => [],
      "shift_break_hours" => "00:00",
      "shift_breaks_array" => [],
      "after_ot_starts_break_hours" => "00:00"
    ];
  }

  $work_hours = "";
  $break_hours = "00:00";
  $shift_break_hours = "00:00";
  $after_ot_starts_break_hours = "00:00";
  $breaks_array = [];
  $shift_breaks_array = [];

  $end_time = $v->end_time;
  $overtime_starts = $v->overtime_starts;

  $total_breaks_taken = 0;
  foreach ($clockings as $key => &$value) {
    if ($key % 2 === 0) {
      $work_hours = add_time(
        $work_hours,
        $value->total_time
      );
    } else {
      $clock_in = $value->clock_in;
      $clock_out = $value->clock_out;

      if ($clock_in < $end_time && $clock_out <= $end_time) {
        $shift_breaks_array[] = $value->total_time;
      } else if ($clock_in < $end_time && $clock_out > $end_time) {
        $shift_breaks_array[] = sub_time($end_time, $clock_in);
      }

      $clock_in_1 = ($value->clock_in_1) ? DateTime::createFromFormat('d-m-Y H:i', $value->clock_in_1)->format('Y-m-d H:i') : null;
      $clock_out_1 = ($value->clock_out_1) ? DateTime::createFromFormat('d-m-Y H:i', $value->clock_out_1)->format('Y-m-d H:i') : null;
      if ($clock_in_1 && $clock_out_1) {
        $overtime_starts_full = $v->search_date . " " . $overtime_starts;
        if (($overnight && $overtime_starts && $overtime_starts < "12:00") || $overtime_starts == "00:00") {
          // overtime starts on the next day
          $overtime_starts_full = date('Y-m-d', strtotime($v->search_date . ' +1 day')) . " " . $overtime_starts;
        }
        if ($clock_in_1 <= $overtime_starts_full && $clock_out_1 > $overtime_starts_full) {
          $total_time = sub_time($clock_out, $overtime_starts);
          $after_ot_starts_break_hours = add_time($after_ot_starts_break_hours, $total_time);
        } else if ($clock_in_1 > $overtime_starts_full) {
          $after_ot_starts_break_hours = add_time($after_ot_starts_break_hours, $value->total_time);
        }
      }
      $total_breaks_taken++;
      $breaks_array[] = $value->total_time;
    }
  }

  for ($i = 0, $j = 0; $i < $total_breaks_taken; $i++, $j++) {
    $current_break = $v->{"break_" . ($j + 1)};
    $current_consider_break = $v->{"consider_break_" . ($j + 1)};
    // $next_break = 

    if ($current_break != "") {
      if ($current_consider_break == 0) {
        if ($breaks_array[$i] > "00:40") {
          $break_hours = add_time($break_hours, $breaks_array[$i]);
          $j++;
        } else {
          $work_hours = add_time($work_hours, $breaks_array[$i]); // 
          $break_hours = add_time($break_hours, sub_time($breaks_array[$i], $current_break)); //
        }
      } else {
        $break_hours = add_time($break_hours, $breaks_array[$i]);
      }
    } else {
      $break_hours = add_time($break_hours, $breaks_array[$i]);
    }
  }

  $j = 0;
  foreach ($shift_breaks_array as $break) {
    $current_break = $v->{"break_" . ($j + 1)};
    $current_consider_break = $v->{"consider_break_" . ($j + 1)};
    // $next_break = 

    if ($current_break != "") {
      if ($current_consider_break == 0) {
        if ($break > "00:40") {
          $shift_break_hours = add_time($shift_break_hours, $break);
          $j++;
        } else {
          $shift_break_hours = add_time($shift_break_hours, sub_time($break, $current_break)); //
        }
      } else {
        $shift_break_hours = add_time($shift_break_hours, $break);
      }
    } else {
      $shift_break_hours = add_time($shift_break_hours, $break);
    }
    $j++;
  }

  return (object) [
    "work_hours" => $work_hours,
    "break_hours" => $break_hours,
    "breaks_array" => $breaks_array,
    "shift_break_hours" => $shift_break_hours,
    "shift_breaks_array" => $shift_breaks_array,
    "after_ot_starts_break_hours" => $after_ot_starts_break_hours
  ];
}

function get_allowed_departments($user)
{
  $department_id = $user["department_id"];
  $departments_access = $user["departments_access"];
  if ($departments_access == "") {
    $departments = array($department_id);
  } else {
    $departments = explode(",", $departments_access);
    $departments[] = $department_id;
  }

  return implode(",", $departments);
}

/**
 * Insert the late hours in work hours if they are not deducted
 * 
 * @param  string $work_hours hours the person has worked
 * @param string $late_hours person's late hours
 * @param string $break_late_hours person's break late hours
 * @param string $early_out person's early out hours
 * @param boolean $inc_late_in weather to include late in or not
 * @param boolean $in_late_break weather to include late in break or not
 * @param boolean $inc_early_out weather to include early out or not
 * @param boolean $is_late weather to include late or not
 * @param boolean $is_late_break weather to include late break or not
 * @param boolean $is_early_out weather to include early out or not
 * 
 * @return string $work_hours with late hours added
 */
function add_deducted_time_in_work_hours(
  $work_hours,
  $late_hours,
  $break_late_hours,
  $early_out,
  $inc_late_in,
  $inc_late_break,
  $inc_early_out,
  $is_late,
  $is_late_break,
  $is_early_out,
  $ot_type = 'default'
) {
  if ($ot_type === 'monthly_ot') {
    $work_hours = add_time($work_hours, $late_hours);
    $work_hours = add_time($work_hours, $break_late_hours);

    return $work_hours;
  }
  if (!$inc_late_in || !$is_late) {
    $work_hours = add_time($work_hours, $late_hours);
  }
  if (!$inc_late_break || !$is_late_break) {
    $work_hours = add_time($work_hours, $break_late_hours);
  }
  if (!$inc_early_out || !$is_early_out) {
    $work_hours = add_time($work_hours, $early_out);
  }

  return $work_hours;
}

/**
 * Function to get offense list for an employee or a list of employees from start
 * date to end date
 *
 * @param int|array $employee_id
 * @param string $first_day
 * @param string $last_day
 * @return array
 */
function get_is_offense_list($employee_id, $first_day, $last_day)
{
  $ci = &get_instance();
  if (is_array($employee_id)) {
    $ci->db->where_in('employee_id', $employee_id);
  } else {
    $ci->db->where('employee_id', $employee_id);
  }
  return $ci->db->select('id, employee_id, is_offense, date')->from('offense_days')->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_manual_offense_list($id, $first_day, $last_day)
{
  $ci = &get_instance();
  return $ci->db->select('offense,points,type,date')->from('manual_offenses')->where('employee_id', $id)->where('date >=', $first_day)->where('date <=', $last_day)->get()->result();
}

function get_employee_points($employee_id, $year)
{
  $ci = &get_instance();
  $ci->db->select('points, month, year');
  $ci->db->where('year', $year);
  $ci->db->where('employee_id', $employee_id);
  return $ci->db->get('merit_points')->result();
}

function get_employee_points_by_month($employee_id, $year, $month)
{
  $ci = &get_instance();
  $ci->db->select('points');
  $ci->db->where('year', $year);
  $ci->db->where('month', $month);
  $ci->db->where('employee_id', $employee_id);
  return $ci->db->get('merit_points')->result();
}

function get_public_holiday_by_date($date, $branch_id, $company_id)
{
  // echo $date;die;
  $ci = &get_instance();
  $ci->db->like('holiday_date', $date, 'both');
  $ci->db->where('company_id', $company_id);
  $ci->db->where('(branch_id = ' . $ci->db->escape($branch_id) . ' or branch_id = 0)');
  return $ci->db->get('public_holidays')->row();
}

function default_offenses()
{
  return [
    'Late In', 'Half Day Paid', 'Half Day Unpaid', 'Medical Leave', 'Missing In / Out',
    'Late Break', 'Absent / Unpaid Leave', 'Early Out', 'Full Day Paid'
  ];
}

function map_offense($offense)
{
  $result = $offense;
  $default_offenses = default_offenses();
  switch ($offense) {
    case $default_offenses[0]:
      $result = "LI";
      break;

    case $default_offenses[1]:
      $result = "HDP";
      break;

    case $default_offenses[2]:
      $result = "HUP";
      break;

    case $default_offenses[3]:
      $result = "ML";
      break;

    case $default_offenses[4]:
      $result = "No In/Out";
      break;

    case $default_offenses[5]:
      $result = "LB";
      break;

    case $default_offenses[6]:
      $result = "A/UL";
      break;
    case $default_offenses[7]:
      $result = "EO";
      break;
  }

  return $result;
}

function round_by_exact_hour($clockings, $ot_settings)
{
  $last_clock_out = end($clockings)->clock_out_1;
  if ($last_clock_out) {
    $datetime = explode(" ", $last_clock_out);
    $time = round_off_ot($datetime[1], $ot_settings, false);
    if ($time == "") $time = "00:00";
    $last_clock_out = $datetime[0] . " " . $time;
    end($clockings)->clock_out_1 = $last_clock_out;
  }
  return $clockings;
}

function get_average_merit_points($year, $cid)
{
  $ci = &get_instance();
  $average_points = $ci->db->query("SELECT SUM(points) / 12 points, employee_id FROM merit_points WHERE year = {$year} AND company_id = {$cid} GROUP BY employee_id")->result();
  return $average_points;
}

function search_average_merit_points(&$average_points, $employee_id)
{
  $employee_ids = array_column($average_points, "employee_id");
  $index = array_search($employee_id, $employee_ids);
  if ($index === false) {
    return 0;
  }
  return $average_points[$index]->points;
}

function merit_system_grading($points)
{
  $points = doubleval($points);

  if ($points >= 90) return "A";
  if ($points >= 80) return "B";
  if ($points >= 70) return "C";
  if ($points >= 60) return "D";
  return "F";
}

function get_merit_points($year, $cid)
{
  $ci = &get_instance();
  return $ci->db->query("SELECT points, employee_id, month
  FROM merit_points
  WHERE company_id = {$cid} AND
  year = {$year}
  ORDER BY month ASC")->result();
}

function search_merit_points(&$compnay_merit_points, $employee_id)
{
  $result = [];
  foreach ($compnay_merit_points as &$merit_point) {
    if ($merit_point->employee_id == $employee_id) $result[] = $merit_point;
  }
  return $result;
}

function time_bw_original_times($time2, $time1)
{
  if (empty($time1) || empty($time2)) return "";
  $time1 = date_create_from_format("d-m-Y H:i", $time1)->format('U');
  $time2 = date_create_from_format("d-m-Y H:i", $time2)->format('U');
  $total_minutes = intval(($time2 - $time1) / 60);
  $hours = intval($total_minutes / 60);
  $minutes = $total_minutes % 60;
  $total_time = sprintf("%02d", $hours) . ":" . sprintf("%02d", $minutes);
  return $total_time;
}

function calculate_monthly_ot($work_hours, $company_working_hours_decimal)
{
  if ($work_hours == "" || $work_hours == "00:00") {
    return "";
  }
  $work_hours_decimal = toDecimal($work_hours);
  $company_working_hours = decimal_to_time($company_working_hours_decimal);

  if ($work_hours_decimal > $company_working_hours_decimal) {
    return sub_time($work_hours, $company_working_hours);
  }
  return "";
}

function admin_add_edit_check_clock_in($clocking_id)
{
  if(empty($clocking_id)) return false;
  $ci = &get_instance();
  $ci->db->select('add_by_admin, update_by_admin');
  $ci->db->where('id', $clocking_id);
  $result = $ci->db->get('clockings_news')->row();
  if ($result->add_by_admin == 1 || $result->update_by_admin == 1) {
    return true;
  } else {
    return false;
  }
}
function admin_add_edit_check_clock_out($clocking_id)
{
  if(empty($clocking_id)) return false;
  $ci = &get_instance();
  $ci->db->select('add_by_admin, update_by_admin');
  $ci->db->where('id', $clocking_id);
  $result = $ci->db->get('clockings_news')->row();
  if ($result->add_by_admin == 1 || $result->update_by_admin == 1) {
    return true;
  } else {
    return false;
  }
}

function multiply_time_by_scalar($time, $number = 1)
{
  if ($time === '' || $time === '00:00') return '00:00';
  $time_decimal = toDecimal($time);
  $multiplied_time_decimal = $time_decimal * $number;
  $multiplied_time = decimal_to_time($multiplied_time_decimal);
  return $multiplied_time;
}

function companies_allowed_for_merit()
{
  return [1, 3, 21, 39, 153, 155,];
}

function companies_allowed_for_monthly_ot()
{
  return [3, 19, 68, 85, 86, 87, 164];
}


/**
 * Get the list of companies allowed for leave application
 * @return array
 */
function companies_allowed_for_leave_application()
{
    return [3, 19, 95, 118, 17, 95, 125, 142, 153, 198, 197, 196, 206, 217, 223, 259, 268];
}

function companies_allowed_for_allowance_report()
{
  return [3, 199];
}

function get_allowances_for_report($employees_ids, $first_day, $last_day)
{
  $ci = &get_instance();
  $ci->db->select('employee_id, special_id, code, description, rate, count(rate) as work_unit, sum(rate) as amount')
  ->from('allowances_assignment')
  ->join('allowances_settings', 'allowances_assignment.allowance_id = allowances_settings.id')
  ->join('employees', 'allowances_assignment.employee_id = employees.id')
  ->where_in('employee_id', $employees_ids)
  ->where('date >=', $first_day)
  ->where('date <=', $last_day)
  ->group_by('employee_id, code, description, rate');
  return $ci->db->get()->result();
}

function deduct_hour_from_ot_rd($overtime)
{
  $overtime = add_time($overtime, "-01:00");
  if (strpos($overtime, '-') !== false) {
    $overtime = "";
  }
  return $overtime;
}

function calculate_merit(&$emp, &$calculated_data, $permissions_level, $merit_deduction_points, $first_day, $last_day, $default_offenses)
{
  $manual_offense_list = get_manual_offense_list($emp->id, $first_day, $last_day);

  $deduction_points = $merit_deduction_points;
  if ($permissions_level !== "Outlet") {
    $deduction_points = array_values(array_filter($merit_deduction_points, function ($item) use ($emp) {
      return $item->branch_id == $emp->branch_id;
    }));
  }

  // data definition for each date
  $temp["id"] = $emp->id;
  $temp["special_id"] = $emp->special_id;
  $temp["first_name"] = $emp->first_name;

  $days_wise = [];
  $total_points = 100;

  $offenses = array_column($deduction_points, 'offense');
  $offenses_count = [];
  $approved_offenses = [];
  $late_in_count = 0;
  $late_in_points = 0;
  $late_break_count = 0;
  $late_break_points = 0;
  $early_out_count = 0;
  $early_out_points = 0;
  $half_day_paid_count = 0;
  $half_day_paid_points = 0;
  $approved_half_day_paid_count = 0;
  $approved_half_day_paid_points = 0;
  $full_day_paid_count = 0;
  $full_day_paid_points = 0;
  $approved_full_day_paid_count = 0;
  $approved_full_day_paid_points = 0;
  $half_day_unpaid_count = 0;
  $half_day_unpaid_points = 0;
  $approved_half_day_unpaid_count = 0;
  $approved_half_day_unpaid_points = 0;
  $medical_leave_count = 0;
  $medical_leave_points = 0;
  $approved_medical_leave_count = 0;
  $approved_medical_leave_points = 0;
  $missing_IO_count = 0;
  $missing_IO_points = 0;
  $AU_count = 0;
  $AU_points = 0;
  $approved_AU_count = 0;
  $approved_AU_points = 0;
  $manual_offense_points = 0;
  foreach ($calculated_data["dates"] as &$date) {
    $date_object = DateTime::createFromFormat('Y-m-d', $date->date);
    $formatted_date = $date_object->format('d/m');
    $temp1 = [];
    $temp1["day"] = $date->date;
    $temp1["id"] = $emp->id;
    $temp1["is_offense"] = $date->merit_is_offense;
    $temp1["points"] = 0;
    $temp1["offenses_today"] = [];

    $manual_offense = search_from_list($manual_offense_list, $date->date);
    if ($manual_offense) {
      $temp1["is_offense"] = true;
      $temp1["offenses_today"][] = $manual_offense->offense;
      $approved_offenses[] = [
        "offense" => $manual_offense->offense,
        "date" => $formatted_date,
      ];
      $temp1["offense"] = $manual_offense->offense;
      $temp1["points"] = $manual_offense->points;
      $temp1["sign"] = $manual_offense->type;
      if ($manual_offense->type === "-") {
        $temp1["points"] = -$manual_offense->points;
      }
      $manual_offense_points += $manual_offense->points;
      $total_points -= $manual_offense->points;
    } else {
      $temp1["offense"] = "";
      foreach ($default_offenses as &$dof) {
        $index = array_search($dof, $offenses);
        if ($index !== false) {
          $offense = $deduction_points[$index];
          switch ($dof) {
            case "Late Break":
              if ($calculated_data["total_break_late"] > $offense->times_allowed) {
                if ($date->merit_is_break_late === true) {
                  $temp1["offenses_today"][] = $dof;  // this means
                  $temp1["points"] -= $offense->deduction_points;
                  if ($date->merit_is_offense) { // is offense means person approved the offense
                    if ($offense->deduct_after_threshold == 0) {
                      $late_break_count++;
                      $late_break_points += $offense->deduction_points;
                      $total_points -= $offense->deduction_points;
                      $approved_offenses[] = [
                        "offense" => $dof,
                        "date" => $formatted_date,
                      ];
                    } else {
                      if (array_key_exists($dof, $offenses_count)) {
                        $offenses_count[$dof] += 1;
                        if ($offenses_count[$dof] > $offense->times_allowed) {
                          $late_break_count++;
                          $late_break_points += $offense->deduction_points;
                          $total_points -= $offense->deduction_points;
                          $approved_offenses[] = [
                            "offense" => $dof,
                            "date" => $formatted_date,
                          ];
                        }
                      } else {
                        $offenses_count[$dof] = 1;
                      }
                    }
                  }
                }
              }
              if ($calculated_data["total_break_late"] <= $offense->bonus_if_not_more_than) {
                if ($date->clockings != "" && count($date->clockings) > 1) {
                  if ($date->merit_is_break_late === false) {
                    $temp1["points"] += $offense->bonus_points;
                  }
                }
              }
              break;
            case "Late In":
              if ($calculated_data["total_late_only_count"] > $offense->times_allowed) {
                if ($date->merit_is_late === true) {
                  $temp1["offenses_today"][] = $dof;
                  $temp1["points"] -= $offense->deduction_points;
                  if ($date->merit_is_offense) {
                    if ($offense->deduct_after_threshold == 0) {
                      $late_in_count++;
                      $late_in_points += $offense->deduction_points;
                      $total_points -= $offense->deduction_points;
                      $approved_offenses[] = [
                        "offense" => $dof,
                        "date" => $formatted_date,
                      ];
                    } else {
                      if (array_key_exists($dof, $offenses_count)) {
                        $offenses_count[$dof] += 1;
                        if ($offenses_count[$dof] > $offense->times_allowed) {
                          $late_in_count++;
                          $late_in_points += $offense->deduction_points;
                          $total_points -= $offense->deduction_points;
                          $approved_offenses[] = [
                            "offense" => $dof,
                            "date" => $formatted_date,
                          ];
                        }
                      } else {
                        $offenses_count[$dof] = 1;
                      }
                    }
                  }
                }
              }
              if ($calculated_data["total_late_only_count"] <= $offense->bonus_if_not_more_than) {
                if ($date->clockings != "" && count($date->clockings) > 1) {
                  if ($date->merit_is_late === false) {
                    $temp1["points"] += $offense->bonus_points;
                  }
                }
              }
              break;
            case "Early Out":
              if ($calculated_data["total_early_count"] > $offense->times_allowed) {
                if ($date->merit_is_early_out === true) {
                  $temp1["offenses_today"][] = $dof;
                  $temp1["points"] -= $offense->deduction_points;
                  if ($date->merit_is_offense) {
                    if ($offense->deduct_after_threshold == 0) {
                      $early_out_count++;
                      $early_out_points += $offense->deduction_points;
                      $total_points -= $offense->deduction_points;
                      $approved_offenses[] = [
                        "offense" => $dof,
                        "date" => $formatted_date,
                      ];
                    } else {
                      if (array_key_exists($dof, $offenses_count)) {
                        $offenses_count[$dof] += 1;
                        if ($offenses_count[$dof] > $offense->times_allowed) {
                          $early_out_count++;
                          $early_out_points += $offense->deduction_points;
                          $total_points -= $offense->deduction_points;
                          $approved_offenses[] = [
                            "offense" => $dof,
                            "date" => $formatted_date,
                          ];
                        }
                      } else {
                        $offenses_count[$dof] = 1;
                      }
                    }
                  }
                }
              }
              if ($calculated_data["total_early_count"] <= $offense->bonus_if_not_more_than) {
                if ($date->clockings != "" && count($date->clockings) > 1) {
                  if ($date->merit_is_early_out === false) {
                    $temp1["points"] += $offense->bonus_points;
                  }
                }
              }
              break;
            case "Half Day Paid":
              if ($calculated_data["total_half_day_paid"] > $offense->times_allowed) {
                if ($date->merit_is_half_day_paid === true) {
                  $temp1["offenses_today"][] = $dof;
                  if ($date->shift_check->is_approved == 0) {
                    $temp1["points"] -= $offense->deduction_points;
                  } else {
                    $temp1["points"] -= $offense->special_deduction_points;
                  }
                  if ($date->merit_is_offense) {
                    if ($offense->deduct_after_threshold == 0) {
                      $approved_offenses[] = [
                        "offense" => $dof,
                        "date" => $formatted_date,
                      ];
                      if ($date->shift_check->is_approved == 0) {
                        $half_day_paid_count++;
                        $half_day_paid_points += $offense->deduction_points;
                        $total_points -= $offense->deduction_points;
                      } else {
                        $approved_half_day_paid_count++;
                        $approved_half_day_paid_points += $offense->special_deduction_points;
                        $total_points -= $offense->special_deduction_points;
                      }
                    } else {
                      if (array_key_exists($dof, $offenses_count)) {
                        $offenses_count[$dof] += 1;
                        if ($offenses_count[$dof] > $offense->times_allowed) {
                          $approved_offenses[] = [
                            "offense" => $dof,
                            "date" => $formatted_date,
                          ];
                          if ($date->shift_check->is_approved == 0) {
                            $half_day_paid_count++;
                            $half_day_paid_points += $offense->deduction_points;
                            $total_points -= $offense->deduction_points;
                          } else {
                            $approved_half_day_paid_count++;
                            $approved_half_day_paid_points += $offense->special_deduction_points;
                            $total_points -= $offense->special_deduction_points;
                          }
                        }
                      } else {
                        $offenses_count[$dof] = 1;
                      }
                    }
                  }
                }
              }
              if ($calculated_data["total_half_day_paid"] <= $offense->bonus_if_not_more_than) {
                if ($date->merit_is_half_day_paid === false) {
                  $temp1["points"] += $offense->bonus_points;
                }
              }
              break;
            case "Full Day Paid":
              if ($calculated_data["total_full_day_paid"] > $offense->times_allowed) {
                if ($date->merit_is_full_day_paid === true) {
                  $temp1["offenses_today"][] = $dof;
                  if ($date->shift_check->is_approved == 0) {
                    $temp1["points"] -= $offense->deduction_points;
                  } else {
                    $temp1["points"] -= $offense->special_deduction_points;
                  }
                  if ($date->merit_is_offense) {
                    if ($offense->deduct_after_threshold == 0) {
                      $approved_offenses[] = [
                        "offense" => $dof,
                        "date" => $formatted_date,
                      ];
                      if ($date->shift_check->is_approved == 0) {
                        $full_day_paid_count++;
                        $full_day_paid_points += $offense->deduction_points;
                        $total_points -= $offense->deduction_points;
                      } else {
                        $approved_full_day_paid_count++;
                        $approved_full_day_paid_points += $offense->special_deduction_points;
                        $total_points -= $offense->special_deduction_points;
                      }
                    } else {
                      if (array_key_exists($dof, $offenses_count)) {
                        $offenses_count[$dof] += 1;
                        if ($offenses_count[$dof] > $offense->times_allowed) {
                          $approved_offenses[] = [
                            "offense" => $dof,
                            "date" => $formatted_date,
                          ];
                          if ($date->shift_check->is_approved == 0) {
                            $full_day_paid_count++;
                            $full_day_paid_points += $offense->deduction_points;
                            $total_points -= $offense->deduction_points;
                          } else {
                            $approved_full_day_paid_count++;
                            $approved_full_day_paid_points += $offense->special_deduction_points;
                            $total_points -= $offense->special_deduction_points;
                          }
                        }
                      } else {
                        $offenses_count[$dof] = 1;
                      }
                    }
                  }
                }
              }
              if ($calculated_data["total_full_day_paid"] <= $offense->bonus_if_not_more_than) {
                if ($date->merit_is_full_day_paid === false) {
                  $temp1["points"] += $offense->bonus_points;
                }
              }
              break;
            case "Half Day Unpaid":
              if ($calculated_data["total_half_day_unpaid"] > $offense->times_allowed) {
                if ($date->merit_is_half_day_unpaid === true) {
                  $temp1["offenses_today"][] = $dof;
                  if ($date->shift_check->is_approved == 0) {
                    $temp1["points"] -= $offense->deduction_points;
                  } else {
                    $temp1["points"] -= $offense->special_deduction_points;
                  }
                  if ($date->merit_is_offense) {
                    if ($offense->deduct_after_threshold == 0) {
                      $approved_offenses[] = [
                        "offense" => $dof,
                        "date" => $formatted_date,
                      ];
                      if ($date->shift_check->is_approved == 0) {
                        $half_day_unpaid_count++;
                        $half_day_unpaid_points += $offense->deduction_points;
                        $total_points -= $offense->deduction_points;
                      } else {
                        $approved_half_day_unpaid_count++;
                        $approved_half_day_unpaid_points += $offense->special_deduction_points;
                        $total_points -= $offense->special_deduction_points;
                      }
                    } else {
                      if (array_key_exists($dof, $offenses_count)) {
                        $offenses_count[$dof] += 1;
                        if ($offenses_count[$dof] > $offense->times_allowed) {
                          $approved_offenses[] = [
                            "offense" => $dof,
                            "date" => $formatted_date,
                          ];
                          if ($date->shift_check->is_approved == 0) {
                            $half_day_unpaid_count++;
                            $half_day_unpaid_points += $offense->deduction_points;
                            $total_points -= $offense->deduction_points;
                          } else {
                            $approved_half_day_unpaid_count++;
                            $approved_half_day_unpaid_points += $offense->special_deduction_points;
                            $total_points -= $offense->special_deduction_points;
                          }
                        }
                      } else {
                        $offenses_count[$dof] = 1;
                      }
                    }
                  }
                }
              }
              if ($calculated_data["total_half_day_unpaid"] <= $offense->bonus_if_not_more_than) {
                if ($date->merit_is_half_day_unpaid === false) {
                  $temp1["points"] += $offense->bonus_points;
                }
              }
              break;
            case "Medical Leave":
              if ($calculated_data["total_medical_leaves"] > $offense->times_allowed) {
                if ($date->merit_is_medical_leave === true) {
                  $temp1["offenses_today"][] = $dof;
                  if ($date->shift_check->is_approved == 0) {
                    $temp1["points"] -= $offense->deduction_points;
                  } else {
                    $temp1["points"] -= $offense->special_deduction_points;
                  }
                  if ($date->merit_is_offense) {
                    if ($offense->deduct_after_threshold == 0) {
                      $approved_offenses[] = [
                        "offense" => $dof,
                        "date" => $formatted_date,
                      ];
                      if ($date->shift_check->is_approved == 0) {
                        $medical_leave_count++;
                        $medical_leave_points += $offense->deduction_points;
                        $total_points -= $offense->deduction_points;
                      } else {
                        $approved_medical_leave_count++;
                        $approved_medical_leave_points += $offense->special_deduction_points;
                        $total_points -= $offense->special_deduction_points;
                      }
                    } else {
                      if (array_key_exists($dof, $offenses_count)) {
                        $offenses_count[$dof] += 1;
                        if ($offenses_count[$dof] > $offense->times_allowed) {
                          $approved_offenses[] = [
                            "offense" => $dof,
                            "date" => $formatted_date,
                          ];
                          if ($date->shift_check->is_approved == 0) {
                            $medical_leave_count++;
                            $medical_leave_points += $offense->deduction_points;
                            $total_points -= $offense->deduction_points;
                          } else {
                            $approved_medical_leave_count++;
                            $approved_medical_leave_points += $offense->special_deduction_points;
                            $total_points -= $offense->special_deduction_points;
                          }
                        }
                      } else {
                        $offenses_count[$dof] = 1;
                      }
                    }
                  }
                }
              }
              if ($calculated_data["total_medical_leaves"] <= $offense->bonus_if_not_more_than) {
                if ($date->merit_is_medical_leave === false) {
                  $temp1["points"] += $offense->bonus_points;
                }
              }
              break;
            case "Missing In / Out":
              if ($calculated_data["total_missing_in_out"] > $offense->times_allowed) {
                if ($date->merit_is_missing_in_out === true) {
                  $temp1["offenses_today"][] = $dof;
                  $temp1["points"] -= $offense->deduction_points;
                  if ($date->merit_is_offense) {
                    if ($offense->deduct_after_threshold == 0) {
                      $missing_IO_count++;
                      $missing_IO_points += $offense->deduction_points;
                      $approved_offenses[] = [
                        "offense" => $dof,
                        "date" => $formatted_date,
                      ];
                      $total_points -= $offense->deduction_points;
                    } else {
                      if (array_key_exists($dof, $offenses_count)) {
                        $offenses_count[$dof] += 1;
                        if ($offenses_count[$dof] > $offense->times_allowed) {
                          $approved_offenses[] = [
                            "offense" => $dof,
                            "date" => $formatted_date,
                          ];
                          $missing_IO_count++;
                          $missing_IO_points += $offense->deduction_points;
                          $total_points -= $offense->deduction_points;
                        }
                      } else {
                        $offenses_count[$dof] = 1;
                      }
                    }
                  }
                }
              }
              if ($calculated_data["total_missing_in_out"] <= $offense->bonus_if_not_more_than) {
                if ($date->clockings != "" && count($date->clockings) > 1) {
                  if ($date->merit_is_missing_in_out === false) {
                    $temp1["points"] += $offense->bonus_points;
                  }
                }
              }
              break;
            case "Absent / Unpaid Leave":
              if ($calculated_data["total_absent_unpaid"] > $offense->times_allowed) {
                if ($date->merit_is_absent_unpaid === true) {
                  $temp1["offenses_today"][] = $dof;
                  if ($date->shift_check->is_leave == "no") {
                    $temp1["points"] -= $offense->deduction_points;
                  } else {
                    if ($date->shift_check->is_approved == 0) {
                      $temp1["points"] -= $offense->deduction_points;
                    } else {
                      $temp1["points"] -= $offense->special_deduction_points;
                    }
                  }
                  if ($date->merit_is_offense) {
                    if ($offense->deduct_after_threshold == 0) {
                      $approved_offenses[] = [
                        "offense" => $dof,
                        "date" => $formatted_date,
                      ];
                      if ($date->shift_check->is_leave == "no") {
                        $AU_count++;
                        $AU_points += $offense->deduction_points;
                        $total_points -= $offense->deduction_points;
                      } else {
                        if ($date->shift_check->is_approved == 0) {
                          $AU_count++;
                          $AU_points += $offense->deduction_points;
                          $total_points -= $offense->deduction_points;
                        } else {
                          $approved_AU_count++;
                          $approved_AU_points += $offense->special_deduction_points;
                          $total_points -= $offense->special_deduction_points;
                        }
                      }
                    } else {
                      if (array_key_exists($dof, $offenses_count)) {
                        $offenses_count[$dof] += 1;
                        if ($offenses_count[$dof] > $offense->times_allowed) {
                          $approved_offenses[] = [
                            "offense" => $dof,
                            "date" => $formatted_date,
                          ];
                          if ($date->shift_check->is_leave == "no") {
                            $AU_count++;
                            $AU_points += $offense->deduction_points;
                            $total_points -= $offense->deduction_points;
                          } else {
                            if ($date->shift_check->is_approved == 0) {
                              $AU_count++;
                              $AU_points += $offense->deduction_points;
                              $total_points -= $offense->deduction_points;
                            } else {
                              $approved_AU_count++;
                              $approved_AU_points += $offense->special_deduction_points;
                              $total_points -= $offense->special_deduction_points;
                            }
                          }
                        }
                      } else {
                        $offenses_count[$dof] = 1;
                      }
                    }
                  }
                }
              }
              if ($calculated_data["total_absent_unpaid"] <= $offense->bonus_if_not_more_than) {
                if ($date->merit_is_absent_unpaid === false) {
                  $temp1["points"] += $offense->bonus_points;
                }
              }
              break;
          }
        }
      }
    }


    if ($temp1["points"] === 0) {
      $temp1["sign"] = "";
    } else if ($temp1["points"] > 0) {
      $temp1["sign"] = "+";
    } else {
      $temp1["sign"] = "-";
    }
    $temp1["points"] = abs($temp1["points"]);

    $temp1["points"] = $temp1["points"] === 0 ? "-" : $temp1["points"];

    $temp1["points"] = str_pad($temp1["points"], 2, " ", STR_PAD_BOTH);

    $temp1["offenses_today"] = implode("<br>", $temp1["offenses_today"]);

    $days_wise[] = $temp1;
  }

  $temp["offenses"] = $days_wise;
  if ($total_points > 100) {
    $total_points = 100;
  } else if ($total_points < 0) {
    $total_points = 0;
  }
  $temp["total_points"] = $total_points;
  $temp['approved_offenses'] = $approved_offenses;
  $temp['late_in_count'] = $late_in_count;
  $temp['late_in_points'] = $late_in_points;
  $temp['late_break_count'] = $late_break_count;
  $temp['late_break_points'] = $late_break_points;
  $temp['early_out_count'] = $early_out_count;
  $temp['early_out_points'] = $early_out_points;
  $temp['half_day_paid_count'] = $half_day_paid_count;
  $temp['half_day_paid_points'] = $half_day_paid_points;
  $temp['approved_half_day_paid_count'] = $approved_half_day_paid_count;
  $temp['approved_half_day_paid_points'] = $approved_half_day_paid_points;
  $temp['full_day_paid_count'] = $full_day_paid_count;
  $temp['full_day_paid_points'] = $full_day_paid_points;
  $temp['approved_full_day_paid_count'] = $approved_full_day_paid_count;
  $temp['approved_full_day_paid_points'] = $approved_full_day_paid_points;
  $temp['half_day_unpaid_count'] = $half_day_unpaid_count;
  $temp['half_day_unpaid_points'] = $half_day_unpaid_points;
  $temp['approved_half_day_unpaid_count'] = $approved_half_day_unpaid_count;
  $temp['approved_half_day_unpaid_points'] = $approved_half_day_unpaid_points;
  $temp['medical_leave_count'] = $medical_leave_count;
  $temp['medical_leave_points'] = $medical_leave_points;
  $temp['approved_medical_leave_count'] = $approved_medical_leave_count;
  $temp['approved_medical_leave_points'] = $approved_medical_leave_points;
  $temp['missing_IO_count'] = $missing_IO_count;
  $temp['missing_IO_points'] = $missing_IO_points;
  $temp['AU_count'] = $AU_count;
  $temp['AU_points'] = $AU_points;
  $temp['approved_AU_count'] = $approved_AU_count;
  $temp['approved_AU_points'] = $approved_AU_points;
  $temp['manual_offense_points'] = $manual_offense_points;

  return $temp;
}

function time_placeholder($time)
{
  if (empty($time) || $time == "00:00") {
    return "-";
  }
  return $time;
}

function calculate_absenties($employee_id, $first_day, $last_day, &$employee = false, &$result_list = false, &$result_list_overnight = false)
{
  $ci = &get_instance();
  $current_date = DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
  if ($employee === false) {
    $employee = $ci->db->query("SELECT employees.id,special_id, first_name, branch_id FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND roles.exclude_from_system = 'no' AND  employees.id = $employee_id ORDER BY special_id")->result();
  }
  $result = null;
  $shift_list = get_shift_list($employee_id, $first_day, $last_day);
  $replacement_leaves_list = get_replacement_leaves_list($employee_id, $first_day, $last_day);
  if ($result_list === false) {
    $result_list = get_result_list(array($employee_id), $first_day, $last_day);
  }
  if ($result_list_overnight === false) {
    $result_list_overnight = get_result_list_overnight(array($employee_id), $first_day, $last_day);
  }

  $period = new DatePeriod(
    new DateTime($first_day),
    new DateInterval('P1D'),
    (new DateTime($last_day))->add(new DateInterval('P1D'))
  );
  $data = [];
  $has_absenties = false;
  foreach ($period as $date) {
    $obj = new stdClass();
    $obj->date = $date->format('Y-m-d');
    $obj->day_name = $date->format('l');
    $obj->is_absent = false;
    $shift_check = search_from_list($shift_list, $obj->date);
    $replacement = is_replacement($replacement_leaves_list, $obj->date);

    if ($shift_check) {
      $obj->shift = $shift_check->name;
    }

    if ($shift_check && $shift_check->overnight == "Yes") {
      $result = search_clocking_by_id($result_list_overnight, $obj->date, $employee_id);
    } else {
      $result = search_clocking_by_id($result_list, $obj->date, $employee_id);
      // if (!$shift_check) {
      //     $result = remove_duplicate_clockings($result, $obj->date, $shift_list, $result_list_overnight);
      // }
    }

    $check = false;
    if (!$check && empty($result) && $shift_check) {
      if ($obj->date <= $current_date->format('Y-m-d')) {
        if ($replacement) {
          if ($replacement->to !== $obj->date) {
            if ($shift_check->start_time != NULL && $shift_check->end_time != NULL) {
              $obj->is_absent = true;
              $has_absenties = true;
            }
          }
        } else {
          if ($shift_check->start_time != NULL && $shift_check->end_time != NULL) {
            $obj->is_absent = true;
            $has_absenties = true;
          }
        }
      }
    }
    $data[] = $obj;
  }
  $result = new stdClass();
  $result->absenties = $data;
  $result->has_absenties = $has_absenties;

  return $result;
}

function render_ab_filters(&$data)
{
  $current_user = get_user();
  $CI = &get_instance();

  $input = [];

  foreach ($CI->input->get() as $filter_name => $value) {
    $input[$filter_name] = $value;
  }

  $data["selected_branch_id"] = 0;
  $data["selected_dep_id"] = 0;
  $data["selected_month"] = 0;
  $data["selected_year"] = 0;
  $data["selected_emp_id"] = 0;
  $data["selected_group_id"] = 0;

  $data["where_filter"] = "";
  $data["where_department_dropdown"] = "";
  $data["where_clock_date"] = "";
  $data["where_date"] = "";


  $cid = $current_user["company_id"];
  $bid = $current_user["branch_id"];

  $permissions_level = $current_user["permissions_level"];
  $limit_access_to_department = $current_user["limit_access_to_department"];
  $department_id = $current_user["department_id"];

  $dids =  $department_id . "," . $current_user["departments_access"];

  $dids = trim($dids, ",");

  $dids_array = explode(',', $dids);


  $data["where_branch_2"] = '';
  $data["where_department"] = '';
  $data["branch_where_filter"] = "";

  //echo $dids;die();

  if ($permissions_level == "Outlet") {

    $data["where_branch_2"] = " AND id = $bid ";

    $query_string = "?month=" . $input['month'] . "&year=" . $input['year'];
    if (!empty($input['emp'])) {
      $data["selected_emp_id"] = $input["emp"];
      $query_string .= "&emp=" . $input["emp"];
    }

    if ($limit_access_to_department == "yes") {

      //echo "aa"; die();

      $data["where_department"] = " AND id IN ($dids) ";
      if ($input["branch"] != $bid || (isset($input["dep_filter"]) && empty(array_intersect($input["dep_filter"], $dids_array)))) {
        $query_string .= "&dep_filter[]=$department_id&branch=$bid";
        redirect($data["filters_form_action"] . $query_string);
        return;
      }
    } else {
      if ($input["branch"] != $bid) {
        $query_string .= "&branch=$bid";
        redirect($data["filters_form_action"] . $query_string);
        return;
      }
    }
  } else {
    $query_string = "?month=" . date('m') . "&year=" . date('Y');

    if ($limit_access_to_department == "yes") {
      $data["where_department"] = " AND id IN ($dids) ";
      if (isset($input['dep_filter']) && empty(array_intersect($input['dep_filter'], $dids_array))) {
        $query_string .= "&dep_filter[]=$department_id";
        log_message('debug', __FUNCTION__ . ':' . __LINE__ . ' Redirecting to ' . $data["filters_form_action"] . $query_string);
        redirect($data["filters_form_action"] . $query_string);
        return;
      }
    }
  }


  if (!empty($input["branch"])) {
    $data["selected_branch_id"] = $input["branch"];
    $data["where_filter"] = $data["where_filter"] . " branch_id = " . $input["branch"] . " AND ";
    $data["branch_where_filter"] = $data["branch_where_filter"] . " AND employees.branch_id = " . $input["branch"];
  }

  if (!empty($input['dep_filter'])) {
    $dep_csv = implode(',', $input['dep_filter']);
    $data["where_filter"] = $data["where_filter"] . " department_id IN ($dep_csv) AND ";

    $data["where_department_dropdown"] = " AND department_id IN ($dep_csv)";
  }

  if (!empty($input["emp"])) {
    $data["selected_emp_id"] = $input["emp"];
    $data["where_filter"] = $data["where_filter"] . " employees.id = " . $input["emp"] . " AND ";
  }

  if (!empty($input["month"]) && !empty($input["year"])) {

    $data["selected_month"] = $input["month"];
    $data["selected_year"] = $input["year"];

    $month = $data["selected_month"];
    $year = $data["selected_year"];
    $start_date = date("Y-m-01", strtotime("$year-$month-01"));
    $end_date = date("Y-m-t", strtotime($start_date));

    $data["where_clock_date"] = " AND MONTH(clock_in) = " . $input["month"] . " AND YEAR(clock_in) = " . $input["year"];
    $data["where_date"] = " AND date BETWEEN '$start_date' AND '$end_date'";
  } else {

    log_message('debug', __FUNCTION__ . ':' . __LINE__ . ' Redirecting to ' . $data["filters_form_action"] . "?branch=" . $input["branch"] . "&month=" . date('m') . "&year=" . date('Y'));
    redirect($data["filters_form_action"] . "?branch=" . $input["branch"] . "&month=" . date('m') . "&year=" . date('Y'));
    return;
  }

  if (!empty($CI->input->get("emp_group"))) {
    $data["selected_group_id"] = $CI->input->get("emp_group");
    $data['where_filter'] = $data['where_filter'] . " egr.group_id = " . $CI->input->get("emp_group") . " AND ";
  }

  $data["where_filter"] = $data["where_filter"] . " employees.company_id = " . $cid;

  $data["where_filter"] = trim($data["where_filter"]);
  $data["where_filter"] = trim($data["where_filter"], "AND");

  $data["employees_dropdown"] = $CI->db->query("SELECT employees.id, special_id,first_name,last_name FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE employees.deleted_at IS NULL 
    AND (employee_status = 'active'
      OR (employee_status = 'terminated' AND termination_date IS NOT NULL AND termination_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
      OR (employee_status = 'resigned' AND resignation_date IS NOT NULL AND resignation_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
    )
    AND roles.exclude_from_system = 'no' AND employees.company_id = $cid " . $data["branch_where_filter"] . "  " . $data["where_department_dropdown"] . " ORDER BY special_id")->result();
  // echo count($data["employees_dropdown"]);die;



  $data["branches"] = $CI->db->query("SELECT * FROM branches WHERE company_id = $cid  " . $data["where_branch_2"] . " ORDER BY name")->result();

  $data["departments"] = $CI->db->query("SELECT * FROM departments WHERE company_id = $cid " . $data["where_department"] . " ORDER BY name")->result();
  $data['dep_filter'] = $input['dep_filter'];
  $data["employee_groups"] = $CI->db->query("SELECT * FROM employee_groups WHERE company_id = $cid " . $data["where_branch_2"] . " ORDER BY name")->result();
}

function in_array_r($needle, $haystack, $strict = false)
{
  foreach ($haystack as $item) {
    if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
      return true;
    }
  }

  return false;
}

function get_interval_minutes($cid, $time_format = false)
{
  $ci = &get_instance();
  $company = $ci->db->select('cut_off_time')->from('companies')->where('id', $cid)->get()->row();
  
  // $settings = [
  //   66 => "06:00",
  //   97 => "06:00",
  //   121 => "04:59",
  //   85 => "10:30",
  //   71 => "00:30"
  // ];

  // if (isset($settings[$cid])) {
  //   $time = $settings[$cid];
  // } else {
  //   $time = "07:00";
  // }

  if($company->cut_off_time){
    $time = $company->cut_off_time;
  }else{
    $time = "07:00";
  }

  if ($time_format) {
    return $time;
  }

  $time = explode(":", $time);
  $minutes = $time[0] * 60 + $time[1];
  
  return $minutes;
}

function remove_next_day_clockings($clockings, $shift, $next_shift)
{
  if($next_shift){
    $next_date = $next_shift->date;
  }else{
    $current_date = $shift->date;
    $next_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
  }
  $shift_cut_off_time = $next_date . " " . $shift->cut_off_time;
  $new_clockings = [];
  foreach ($clockings as $clocking) {
    // if $clocking->clock_in_o >= $shift_end_time and $clocking->clock_in_o contains $next_date
    if ($shift->cut_off_time && $clocking->clock_in_o >= $shift_cut_off_time && strpos($clocking->clock_in_o, $next_date) !== false) continue;
    $new_clockings[] = $clocking;
  }
  return $new_clockings;
}

function remove_next_day_clockings_timelog($clockings, $shift, $next_shift)
{
  if($next_shift){
    $next_date = $next_shift->date;
  }else{
    $current_date = $shift->date;
    $next_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
  }
  $shift_cut_off_time = $next_date . " " . $shift->cut_off_time;
  $new_clockings = [];
  $valid = true;
  foreach ($clockings as $clocking) {
    if (!$valid) continue;
    if ($shift->cut_off_time && $clocking->clock_time_o >= $shift_cut_off_time && strpos($clocking->clock_time_o, $next_date) !== false && $clocking->type == "in") {
      $valid = false;
      continue;
    }
    $new_clockings[] = $clocking;
  }
  return $new_clockings;
}

function get_attendance_allowance_leave_codes()
{
  return ['CL', 'CLM', 'CLN', 'ML', 'MLUP'];
}

function companies_allowed_for_att_all()
{
  return [3, 127];
}

function ot_deduction_from_shift_settings($overtime, $shift)
{
  if ($shift) {
    $if_ot_more_than = $shift->if_ot_more_than;
    $deduct_from_ot = $shift->deduct_from_ot;
    $max_ot_hours = $shift->max_ot_hours;
    if ($if_ot_more_than && $deduct_from_ot) {
      $if_ot_more_than = substr($if_ot_more_than, 0, -3);
      $deduct_from_ot = substr($deduct_from_ot, 0, -3);
    }
    if ($overtime > $if_ot_more_than) {
      $overtime = add_time($overtime, "-" . $deduct_from_ot);
    }
    if ($max_ot_hours) {
      $overtime = $overtime > $max_ot_hours ? substr($max_ot_hours, 0, -3) : $overtime;
    }
  }
  return $overtime;
}

function create_monthly_name_text($name, $special_id)
{
  $name_value = new PHPExcel_RichText();

  $name = $name_value->createTextRun($name);
  $name->getFont()->setBold(true);
  $name_value->createText("\n");
  $name_value->createTextRun($special_id);

  return $name_value;
}

function get_company_working_hours($company_id = false)
{
  $ci = &get_instance();

  if ($company_id === false) {
    $current_user = get_user();
    $company_id = $current_user["company_id"];
  }

  $ci->db->select('id, group_id, date_format(total_hours,"%H:%i") as working_hours, date_format(half_hours, "%H:%i") as half_hours');
  $ci->db->from('company_working_hours');
  $ci->db->where('company_id', $company_id);
  return $ci->db->get()->result();
}

function get_employee_working_hours(&$company_working_hours, $employee_id)
{
  $ci = &get_instance();

  // $removed_null = array_filter($company_working_hours, function ($cwh) {
  //   return $cwh->group_id != null;
  // });

  $group_ids = array_column($company_working_hours, 'group_id');
  $group_ids[] = 0;

  $group = $ci->db->select('*')->from('employee_groups_relation')->where_in('group_id', $group_ids)->where('employee_id', $employee_id)->get()->row();

  // echo "<pre>";
  // var_dump($ci->db->last_query());
  // echo "</pre>";
  // exit;
  

  // $working_hours = [];
  if ($group) {
    // $working_hours = array_search($company_working_hours, function ($cwh) use ($group) {
    //   return $cwh->group_id == $group->group_id;
    // });
    $index = array_search($group->group_id, array_column($company_working_hours, 'group_id'));
    return $company_working_hours[$index];
  }
  // else {
  //   $working_hours = array_filter($company_working_hours, function ($cwh) {
  //     return $cwh->group_id == null;
  //   });


  // }

  $index = array_search(null, array_column($company_working_hours, 'group_id'));
  return $company_working_hours[$index];

  // return array_values($working_hours);
}

function make_shift_list_basic($shift_data, $emp_id){
  $shift_list = [];
  foreach ($shift_data as $shift) {
    $employees = explode(',', $shift->employees);
    if(in_array($emp_id, $employees)){
      $shift_list[] = $shift;
    }
  }
  return $shift_list;
}

function merge_result_with_shifts($result2, $shift_data_list){
  // result2 have clocking_date, shift_data_list have date
  // add clock_in from result2 to shift_data_list if date matches otherwise add clock_in as null
  $result = [];
  foreach ($shift_data_list as $shift_data) {
    $result[] = (array) $shift_data;
    $result[count($result) - 1]["clock_in"] = null;
    foreach ($result2 as $result2_data) {
      if($result2_data["clocking_date"] == $shift_data->shift_date){
        $result[count($result) - 1]["clock_in"] = $result2_data["clock_in"];
        break;
      }
    }
  }
  return $result;
}

function get_current_employee_clockings($employees_min_clockings, $emp_id){
  $result = [];
  foreach ($employees_min_clockings as $emp_min_clocking) {
    if($emp_min_clocking["employee_id"] == $emp_id){
      $result[] = $emp_min_clocking;
    }
  }
  return $result;
}

function search_late_from_list($manual_late_list, $date, $emp_id){
  foreach ($manual_late_list as $manual_late) {
    if($manual_late->date == $date && $manual_late->employee_id == $emp_id){
      return $manual_late;
    }
  }
  return false;
}

function get_clocking_ids_to_update($date, $employee_id, $shift){
  $ci = &get_instance();

  $prev_day_overnight = false;

  $prev_date = date('Y-m-d', strtotime('-1 day', strtotime($date)));

  // select previous day shift for this employee
  $prev_day_shift = $ci->db->query("SELECT * FROM shift_days WHERE date = '$prev_date' AND FIND_IN_SET($employee_id,employees)")->row();
  if($prev_day_shift){
    $prev_day_shift = $ci->db->query("SELECT * FROM shifts WHERE id = $prev_day_shift->shift_id")->row();
    if($prev_day_shift->overnight == "Yes"){
      $prev_day_overnight = true;
    }
  }

  if($prev_day_overnight){
    $prev_day_clockings = get_result_list_overnight(array($employee_id), $prev_date, $prev_date);
  }else{
    $prev_day_clockings = get_result_list(array($employee_id), $prev_date, $prev_date);
  }

  $prev_day_clocking_ids = array();

  foreach($prev_day_clockings as $c){
    $prev_day_clocking_ids[] = $c->clock_in_id;
    $prev_day_clocking_ids[] = $c->clock_out_id;
  }

  $current_day_overnight = $shift && $shift->overnight == "Yes" ? true : false;

  if($current_day_overnight){
    $current_day_clockings = get_result_list_overnight(array($employee_id), $date, $date);
  }else{
    $current_day_clockings = get_result_list(array($employee_id), $date, $date);
  }

  $current_day_clocking_ids = array();

  foreach($current_day_clockings as $c){
    $current_day_clocking_ids[] = $c->clock_in_id;
    $current_day_clocking_ids[] = $c->clock_out_id;
  }

  // update shift_id in clockings_news where id in current day clocking ids and not in previous day clocking ids
  $clocking_ids_to_update = array_diff($current_day_clocking_ids, $prev_day_clocking_ids);
  $clocking_ids_to_update = array_filter($clocking_ids_to_update);

  // insert default clocking_id 0 if no clocking found
  if(empty($clocking_ids_to_update)){
    $clocking_ids_to_update[] = 0;
  }
  $clocking_ids_to_update = implode(",",$clocking_ids_to_update);

  return $clocking_ids_to_update;
}

function time_to_minutes($time){
  $time = explode(":", $time);
  if(count($time) == 2){
    return $time[0] * 60 + $time[1];
  }
  return 0;
}

function minutes_to_time($minutes){
  $hours = floor($minutes / 60);
  $minutes = $minutes % 60;
  return str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" . str_pad($minutes, 2, "0", STR_PAD_LEFT);
}

function get_clockings_from_previous_day($result, $result_list_overnight, $date, $emp_id, $shift_list)
{
  $prev_date = date('Y-m-d', strtotime('-1 day', strtotime($date)));
  $prev_day_shift = search_from_list($shift_list, $prev_date);
  if(!$prev_day_shift || ($prev_day_shift && $prev_day_shift->overnight == "No")){
    return $result;
  }
  $shift = search_from_list($shift_list, $date);

  $prev_day_clockings = search_clocking_by_id($result_list_overnight, $prev_date, $emp_id);
  $used_clockings = remove_next_day_clockings($prev_day_clockings, $prev_day_shift, $shift);
  
  $used_ids = [];
  foreach ($used_clockings as $clocking) {
    $used_ids[] = $clocking->id;
  }

  // add current day clockings to used_ids to avoid duplicate clockings
  foreach ($result as $clocking) {
    $used_ids[] = $clocking->id;
  }

  $unused_clockings = [];
  foreach ($prev_day_clockings as $clocking) {
    if (!in_array($clocking->id, $used_ids)) {
      $clocking->search_date = $date;
      $clocking->day_f = date('d/m D', strtotime($date));
      $clocking->unused = true;
      $unused_clockings[] = $clocking;
    }
  }

  return array_merge($unused_clockings, $result);
}

function get_clockings_from_previous_day_timelog($result, $result_list_overnight, $date, $emp_id, $shift_list)
{
  $prev_date = date('Y-m-d', strtotime('-1 day', strtotime($date)));
  $prev_day_shift = search_from_list($shift_list, $prev_date);
  if(!$prev_day_shift || ($prev_day_shift && $prev_day_shift->overnight == "No")){
    return $result;
  }
  $shift = search_from_list($shift_list, $date);

  $prev_day_clockings = search_clocking_by_id($result_list_overnight, $prev_date, $emp_id);
  $used_clockings = remove_next_day_clockings_timelog($prev_day_clockings, $prev_day_shift, $shift);
  
  $used_ids = [];
  foreach ($used_clockings as $clocking) {
    $used_ids[] = $clocking->id;
  }

  // add current day clockings to used_ids to avoid duplicate clockings
  foreach ($result as $clocking) {
    $used_ids[] = $clocking->id;
  }

  $unused_clockings = [];
  foreach ($prev_day_clockings as $clocking) {
    if (!in_array($clocking->id, $used_ids)) {
      $clocking->search_date = $date;
      $clocking->unused = true;
      $unused_clockings[] = $clocking;
    }
  }

  return array_merge($unused_clockings, $result);
}

function update_new_clockings($employee_id, $datetime, $to_datetime = false)
{
  $ci = &get_instance();

  $end_time = $datetime;

  if ($to_datetime) {
    $end_time = $to_datetime;
  }

  // make start_time from datetime (Y-m-d H:i:s)
  $start_time = date('Y-m-d 00:00:00', strtotime('-1 day', strtotime($datetime)));

  // make end_time from datetime (Y-m-d H:i:s)
  $end_time = date('Y-m-d 23:59:59', strtotime('+1 day', strtotime($end_time)));

  // delete existing new_clockings
  $ci->db->where('employee_id', $employee_id);
  $ci->db->where('clock_in >=', $start_time);
  $ci->db->where('clock_in <=', $end_time);
  $ci->db->delete('new_clockings');

  $clockings_query = "select `a`.`id` AS `id`,`a`.`employee_id` AS `employee_id`,`a`.`device_id` AS `device_id`,`a`.`shift_id` AS `shift_id`,`a`.`datetime` AS `clock_in`,`a`.`id` AS `clock_in_id`,`b`.`datetime` AS `clock_out`,`b`.`id` AS `clock_out_id`,`a`.`reason` AS `reason`,`a`.`remark` AS `remark`,`a`.`mode` AS `scan_type_in`,`b`.`mode` AS `scan_type_out`,`a`.`created_at` AS `created_at`,`b`.`created_at` AS `updated_at` from (`clockings_news` `a` left join `clockings_news` `b` on(((`a`.`employee_id` = `b`.`employee_id`) and (`a`.`type` = 'in') and (`b`.`type` = 'out') and (`b`.`datetime` = (select min(`c`.`datetime`) from `clockings_news` `c` where ((`c`.`datetime` > `a`.`datetime`) and (`c`.`employee_id` = `a`.`employee_id`) and isnull(`c`.`deleted_at`))))))) where (((`a`.`id` <> `b`.`id`) or isnull(`b`.`id`)) and isnull(`a`.`deleted_at`) and isnull(`b`.`deleted_at`) and (`a`.`type` = 'in') and ((`b`.`type` = 'out') or isnull(`b`.`type`))) ";
  $clockings_query .= " and `a`.`employee_id` = " . $employee_id . " and `a`.`datetime` >= '" . $start_time . "' and `a`.`datetime` <= '" . $end_time. "' order by `a`.`datetime`";

  $clockings = $ci->db->query($clockings_query)->result();

  // create or update new_clockings
  foreach ($clockings as $clocking) {
    $ci->db->replace('new_clockings', (array) $clocking);
  }
}

function flushLoadingBar($showPreloading = false)
{
?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<div style="height:100%">
  <div class="container" style="margin-top: 3rem;">
    <div class="panel panel-primary">
        <div class="panel-body">
            <h2>Do not close this window until the export is completed!</h3>
        </div>
    </div>

    <?php if ($showPreloading) : ?>
        <div class="loader-section">
            <h4>Preparing Data</h4>
            <div id="preparing-data">
              <div class="progress">
                  <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                      0%
                  </div>
              </div>
            </div>
        </div>
    <?php endif ?>

    <div class="loader-section">
        <h4>Extracting Data</h4>
        <div id="loading1">
          <div class="progress">
              <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                  0%
              </div>
          </div>
        </div>
    </div>
    <div class="loader-section">
        <h4>Generating File <img id="imm" style="width:70px; margin-bottom:5px" src="https://media.giphy.com/media/3oEjI6SIIHBdRxXI40/giphy.gif"></h4>
        <div id="loading2">
            <div class="progress">
                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                    0%
                </div>
            </div>
        </div>
    </div>
<script>$(document).ready(function () { $("#imm").hide() })</script>
<?php
  ob_flush();
  flush();
}

/**
 * Checks if date is available otherwise returns false
 *
 * @param int $start_day
 * @return DateTime|false
 */
function getStartDateIfAvailable($start_day)
{
  if (!empty($start_day)) {
    $date = DateTime::createFromFormat('Y-m-j', date("Y-m-$start_day"));
    if ($date->format('j') < 20) {
      return $date;
    }
    return $date->sub(new DateInterval('P1M'));
  }
  return false;
}

/**
 * Returns start and end dates with one month gap
 * 
 * @param int $start_day Start day from company settings
 * @return array First element is start date and second element is end date
 */
function getStartEndDatesWithOneMonthGap($start_day)
{
  if($companyStartDate = getStartDateIfAvailable($start_day)) {
    $endDate = DateTime::createFromFormat('Y-m-d', $companyStartDate->format('Y-m-d'))
      ->add(new DateInterval('P1M'))
      ->sub(new DateInterval('P1D'));
    return [$companyStartDate, $endDate];
  }
  return [DateTime::createFromFormat('Y-m-d', date('Y-m-01')), DateTime::createFromFormat('Y-m-d', date('Y-m-t'))];
}

/**
 * Returns date range filter URL string
 * 
 * @param int $start_day Start day from company settings
 * @return string
 */
function getDateRangeFilterURLString($start_day)
{
  $dates = getStartEndDatesWithOneMonthGap($start_day);
  return "daterange_filter=" . urlencode($dates[0]->format('d/m/Y') . " - " . $dates[1]->format('d/m/Y'));
}

function is_alternate_clockings($branch_id)
{
  $ci = &get_instance();
  $branch = $ci->db->select('clocking_type')->from('branches')->where('id', $branch_id)->get()->row();
  return $branch->clocking_type == 'alternate';
}

function redirect_if_not_permitted()
{
  $ci = &get_instance();
  $data["menus"] = get_menus();
  if (count($data['menus']) == 0) {
      $data['pageTitle'] = "Dashboard Overview";
      $data['active_menu'] = "overview";
      $ci->load->view('header', $data);
      $ci->load->view('sidebar', $data);
      $ci->load->view('not_permitted');
      $ci->load->view('footer');
      exit;
  } else {
      foreach ($data['menus'] as $menu) {
          if (is_null($menu["sub_menus"])) {
              redirect($menu['url']);
              exit;
          } else {
              redirect(reset($menu["sub_menus"])['url']);
              exit;
          }
          exit;
      }
  }
}

function map_address($address)
{
  return !empty($address) ? implode(', ', array_slice(array_map('trim', explode(',', $address)), 0, 3)) : '';
}
