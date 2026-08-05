<?php
class Overview extends CI_Controller {

	 function __construct()
    {
      parent::__construct();

			if(is_null(get_user())){
				redirect("welcome");
			 //var_dump($this->session->userdata('antelope_user'));
			}
    }

	public function Index()
	{


    $branch = null;
    $branches = null;


    $branch_id = $this->input->get('branch_id');
    $cid = get_user()["company_id"];

    if($branch_id){
        $branch = $this->db->get_where('branches', array('id' => $branch_id))->row();
    }

    


    $bid = get_user()["branch_id"];
    $permissions_level = get_user()["permissions_level"];

    //$where_branch_1 = '';
    $where_branch_2 = '';
    //$where_branch_3 = '';


    //if($cid != 1){
        $branches = $this->db->get_where('branches', array('company_id' => $cid))->result();
    // }
    // else{
    //     $branches = $this->db->get('branches')->result();
    // }
    
    if($permissions_level == "Outlet"){

        $branches = $this->db->get_where('branches', array('id' => $bid))->result();
        
        //$where_branch_1 = " AND branch_id = $bid ";
        //$where_branch_2 = " AND id = $bid ";
        //$where_branch_3 = " AND permissions_level = 'Personal' ";

        if(empty($this->input->get("branch_id")) || $this->input->get("branch_id") != $bid){
            redirect("overview?branch_id=$bid");
            return;
        }

        
    }

    $data['pageTitle'] = "Dashboard Overview";
    $data['active_menu'] = "overview";
    $this->load->view('header',$data);
    $data["menus"] = get_menus();
    $this->load->view('sidebar',$data);

    //Boxes and graphs start here------------------------------

     $boxes = array();


    

    $join_roles = array();
    $join_roles['table'] = 'roles';
    $join_roles['on'] = 'employees.role_id = roles.id';
    


    // if($cid != 1){
        
        if(isset($branch)){

            $boxes[] = stats_box("Employees in <b>" . $branch->name . "</b>", "employees","COUNT(1)","3",array('employees.company_id' => $cid,'employees.branch_id' => $branch->id,'roles.exclude_from_system' => 'no'),$join_roles);

            //$boxes[] = stats_box("Devices in <b>" . $branch->name . "</b>", "devices","COUNT(device_id)","3",array('company_id' => $cid,'branch_id' => $branch->id));

            //$boxes[] = stats_box("Total Shifts", "shifts","COUNT(id)","3",array('company_id' => $cid));
            
            // $boxes[] = stats_box("Total Cost", "ticket_transactions","SUM(paid_amount)","4",array('company_id' => $cid,'event_id' => $event->id),$join1);


        }
        else{
            $boxes[] = stats_box("Employees", "employees","COUNT(1)","3",array('employees.company_id' => $cid,'roles.exclude_from_system' => 'no'),$join_roles);

           // $boxes[] = stats_box("Departments", "departments","COUNT(1)","3",array('company_id' => $cid));

            //print_r(stats_box("Total Departments", "departments","COUNT(1)","4",array('company_id' => $cid)));
            $employee_turnover = array();
            $employee_turnover["box_title"] = "Turnover";
            $employee_turnover["box_count"] = 0;
            $employee_turnover["width"] = 3;

            $boxes[] = $employee_turnover;


            $gender_ratio_box = array();
            $gender_ratio_box["box_title"] = "Gender Ratio";

            $q_male = $this->db->query("SELECT COUNT(id) AS count FROM employees WHERE sex = 'Male' AND company_id = $cid")->row();
            $q_female = $this->db->query("SELECT COUNT(id) AS count FROM employees WHERE sex = 'Female' AND company_id = $cid")->row();

            // var_dump((int)$q_male->count);
            // var_dump((int)$q_female->count);

            $total = ((int)$q_male->count + (int)$q_female->count);
            
            

             $male_percent = number_format(((int)$q_male->count  / $total) * 100,0);

             $female_percent = number_format(((int)$q_female->count / $total) * 100,0);

            //  var_dump($male_percent);
            // var_dump($female_percent);


            $gender_ratio_box["box_count"] = "<span style='color:#0D53CA'>$male_percent</span>:<span style='color:#E3457A'>$female_percent</span>";
            $gender_ratio_box["width"] = 3;

            $boxes[] = $gender_ratio_box;

            $attendance_percentage = array();
            $attendance_percentage["box_title"] = "Attendance %";
            $attendance_percentage["box_count"] = "100%";
            $attendance_percentage["width"] = 3;

            $boxes[] = $attendance_percentage;

            


            //$boxes[] = stats_box("Total Shifts", "shifts","COUNT(id)","3",array('company_id' => $cid));

            

            // $boxes[] = stats_box("Present Employees (Today)", "clockings","COUNT(1)","4",array('clock_date' => $cid),$join1);
            
            // $boxes[] = stats_box("Total Visitors", "ticket_scans","COUNT(id)","3",array('company_id' => $cid));
            // $boxes[] = stats_box("Total Cost", "ticket_transactions","SUM(paid_amount)","3",array('company_id' => $cid),$join1);
        }

        

    // }
    // else{

    //     if(isset($event)){

    //         $boxes[] = stats_box("Total Tickets Sold", "ticket_transactions","COUNT(id)","4",array('event_id' => $event->id));
    //         $boxes[] = stats_box("Total Visitors", "ticket_scans","COUNT(id)","4",array('event_id' => $event->id));
    //         $boxes[] = stats_box("Total Cost", "ticket_transactions","SUM(paid_amount)","4",array('event_id' => $event->id));

    //     }
    //     else{
    //         $boxes[] = stats_box("Total Companies", "events","COUNT(id)","4");
    //         $boxes[] = stats_box("Total Branches", "ticket_transactions","COUNT(id)","4");
    //         $boxes[] = stats_box("Total Employees", "ticket_scans","COUNT(id)","4");
    //         //$boxes[] = stats_box("Total Shifts", "ticket_transactions","SUM(paid_amount)","4");
    //     }
    // }

      $data["boxes"] = $boxes;

      $charts = array();

    // if($cid != 1){

    $join1 = array();
    $join1['table'] = 'events';
    $join1['on'] = 'ticket_transactions.event_id = events.id';

    $join2 = array();
    $join2['table'] = 'tickets';
    $join2['on'] = 'ticket_transactions.ticket_id = tickets.id';

    $join3 = array();
    $join3['table'] = 'employees';
    $join3['on'] = 'clockings.employee_id = employees.id';

        if(isset($branch)){

            // $charts[] = single_series_chart("Sales by POS",'ticket_transactions','first_name,COUNT(1)','first_name','column','Total','12',array('events.company_id' => $cid,'ticket_transactions.event_id' => $event->id),$join1,$join3);


            // $charts[] = single_series_chart("Sales by Day",'ticket_transactions','DATE(created_at) AS reg_d,COUNT(1)','reg_d','line','Total','12',array('company_id' => $cid,'ticket_transactions.event_id' => $event->id),$join1);

            // $charts[] = single_series_chart("Sales by Ticket Type",'ticket_transactions','ticket_type,COUNT(1)','ticket_type','column','Total','12',array('company_id' => $cid,'ticket_transactions.event_id' => $event->id),$join2);

            //$charts[] = single_series_chart("Clocking by Employees (Today)",'clockings','first_name,COUNT(1)','first_name','column','No of Clockings','12',array('employees.company_id' => $cid,'employees.branch_id' => $branch->id, 'DATE(clock_in)'=>'CURRENT_DATE()'),$join3);

            //$charts[] = single_series_chart("Attendance (This Month)",'clockings','DATE(clock_in) AS reg_d,COUNT(DISTINCT employee_id)','reg_d','line','No of Employees','12',array('employees.company_id' => $cid,'employees.branch_id' => $branch->id, "MONTH(clock_in)" => "MONTH(CURRENT_DATE())"),$join3);

        }
        else{

            // echo date('Y-m-d');
            // die();

            //$charts[] = single_series_chart("Clocking by Employees (Today)",'clockings','first_name,COUNT(1)','first_name','column','No of Clockings','12',array('employees.company_id' => $cid, 'DATE(clock_in)'=>'CURRENT_DATE()'),$join3);

            //$charts[] = single_series_chart("Attendance (This Month)",'clockings','DATE(clock_in) AS reg_d,COUNT(DISTINCT employee_id)','reg_d','line','No of Employees','12',array('employees.company_id' => $cid, "MONTH(clock_in)" => "MONTH(CURRENT_DATE())"),$join3);

            //$charts[] = single_series_chart_query("Attendance by Day","SELECT DATE(clock_date) AS reg_d, COUNT(DISTINCT employee_id) FROM clockings GROUP BY reg_d",'reg_d','line','Number of Employees','12');

            //$charts[] = single_series_chart("Present Employees (Today)",'clockings','first_name,COUNT(1)','first_name','column','No of Clockings','12',array('employees.company_id' => $cid, 'clock_date'=>date('Y-m-d')),$join3);

            // var_dump($charts);
            // die();

            // $charts[] = single_series_chart("Attendance by Day",'ticket_transactions','DATE(created_at) AS reg_d,COUNT(1)','reg_d','line','Total','12',array('company_id' => $cid),$join1);

            // $charts[] = single_series_chart("Attendance by Company",'ticket_transactions','event_name_english,COUNT(1)','event_name_english','column','Total','12',array('company_id' => $cid),$join1);
        }


    // }
    // else{

    //     if(isset($event)){

    //       $charts[] = single_series_chart("Sales by POS",'ticket_transactions','first_name,COUNT(1)','first_name','column','Total','12',array('ticket_transactions.event_id' => $event->id),$join3);

    //       $charts[] = single_series_chart("Sales by Day",'ticket_transactions','DATE(created_at) AS reg_d,COUNT(1)','reg_d','line','Total','12',array('event_id' => $event->id));

    //       $charts[] = single_series_chart("Sales by Ticket Type",'ticket_transactions','ticket_type,COUNT(1)','ticket_type','column','Total','12',array('ticket_transactions.event_id' => $event->id),$join2);

    //     }
    //     else{
    //       $charts[] = single_series_chart("Attendance by Employee (Today)",'ticket_transactions','first_name,COUNT(1)','first_name','column','Total','12',false,$join3);

    //       $charts[] = single_series_chart("Attendance by Day (Week)",'ticket_transactions','DATE(created_at) AS reg_d,COUNT(1)','reg_d','line','Total','12');

    //       $charts[] = single_series_chart("Attendance by Branch (Today)",'ticket_transactions','event_name_english,COUNT(1)','event_name_english','column','Total','12',false,$join1);
    //   }

    // }
     

      //$charts[] = single_series_chart("Sales by POS",'ticket_transactions','DATE(created_at) AS reg_d,COUNT(id)','reg_d','column','Total','12');
     
     // $charts[] = single_series_chart("Total by Date",'app_downloads',"DATE_FORMAT(reg,'%Y-%m') AS reg_d, SUM(price)",'reg_d','line','Total Money','12');

      $data["charts"] = $charts;
      $data["branch"] = $branch;
      $data["branches"] = $branches;

      $branch_where_clause = "";
      if(isset($branch)){
        $branch_where_clause = " employees.branch_id = ".$branch->id." AND ";
      }

      $data["chart1_data"] = $this->db->query("SELECT department_name, 

            SUM(case when status = 'early' then 1 else 0 end) as early,
            SUM(case when status = 'late' then 1 else 0 end) as late,
            SUM(case when status = 'ontime' then 1 else 0 end) as ontime

            FROM (SELECT 'early' as status, departments.name as department_name, clockings.*,shifts.grace_time, shifts.start_time FROM clockings
            INNER JOIN shift_days ON DATE(clockings.clock_in) = shift_days.date 
            INNER JOIN shifts ON clockings.shift_id = shifts.id
            INNER JOIN employees on clockings.employee_id = employees.id
            INNER JOIN departments on employees.department_id = departments.id
            WHERE departments.company_id = $cid AND $branch_where_clause shift_days.shift_id = clockings.shift_id
            GROUP BY department_id,DATE(clock_in) HAVING DATE_FORMAT(clockings.clock_in,'%H:%i') < DATE_FORMAT(shifts.start_time,'%H:%i')

            UNION ALL 

            SELECT 'late' as status, departments.name as department_name, clockings.*, shifts.grace_time,shifts.start_time FROM clockings
            INNER JOIN shift_days ON DATE(clockings.clock_in) = shift_days.date 
            INNER JOIN shifts ON clockings.shift_id = shifts.id
            INNER JOIN employees on clockings.employee_id = employees.id
            INNER JOIN departments on employees.department_id = departments.id
            WHERE departments.company_id = $cid AND $branch_where_clause shift_days.shift_id = clockings.shift_id
            GROUP BY department_id,DATE(clock_in) HAVING DATE_FORMAT(clockings.clock_in,'%H:%i') > DATE_FORMAT(shifts.start_time,'%H:%i')
              
            UNION ALL

            SELECT 'ontime' as status, departments.name as department_name, clockings.*, shifts.grace_time, shifts.start_time FROM clockings
            INNER JOIN shift_days ON DATE(clockings.clock_in) = shift_days.date 
            INNER JOIN shifts ON clockings.shift_id = shifts.id
            INNER JOIN employees on clockings.employee_id = employees.id
            INNER JOIN departments on employees.department_id = departments.id
            WHERE departments.company_id = $cid AND $branch_where_clause shift_days.shift_id = clockings.shift_id
            GROUP BY department_id,DATE(clock_in) HAVING DATE_FORMAT(clockings.clock_in,'%H:%i') >= DATE_FORMAT(shifts.start_time,'%H:%i') AND DATE_FORMAT(clockings.clock_in,'%H:%i') <= DATE_FORMAT(shifts.grace_time,'%H:%i')
                 ) as xx GROUP BY department_name")->result();

      // echo $this->db->last_query();
      // die();


        $data["chart2_data"] = $this->db->query("SELECT departments.name as department_name, IFNULL(SUM(TIME_FORMAT(TIMEDIFF(a.`clock_out`, a.`clock_in` ),'%H')), 0) AS hours FROM `clockings` a INNER JOIN employees ON a.employee_id = employees.id INNER JOIN departments ON employees.department_id = departments.id WHERE $branch_where_clause clock_out IS NOT NULL AND departments.company_id = $cid GROUP BY department_name")->result();

        $data["chart3_data"] = $this->db->query("SELECT department_name, 

            SUM(case when status = 'male' then 1 else 0 end) as male,
            SUM(case when status = 'female' then 1 else 0 end) as female

            FROM (

             SELECT 'male' as status, departments.name as department_name, clockings.*, employees.sex FROM clockings INNER JOIN employees on clockings.employee_id = employees.id INNER JOIN departments on employees.department_id = departments.id WHERE $branch_where_clause departments.company_id = $cid GROUP BY department_id,employees.sex,DATE(clock_in) HAVING employees.sex = 'Male'     

            UNION ALL 

            SELECT 'female' as status, departments.name as department_name, clockings.*,employees.sex FROM clockings INNER JOIN employees on clockings.employee_id = employees.id INNER JOIN departments on employees.department_id = departments.id WHERE $branch_where_clause departments.company_id = $cid GROUP BY department_id,employees.sex,DATE(clock_in) HAVING employees.sex = 'Female'
            
            ) as xx GROUP BY department_name")->result();


        $data["chart4_data"] = $this->db->query("SELECT sex, COUNT(1) as count FROM(SELECT employees.sex, clockings.* FROM clockings INNER JOIN employees on clockings.employee_id = employees.id WHERE $branch_where_clause employees.company_id = $cid GROUP BY employees.sex,DATE(clock_in)) as xx GROUP BY sex")->result();

       // echo $this->db->last_query();
       // die();
    //Boxes and graphs end here--------------------------------



    if (is_page_permitted('overview')) {
        $this->load->view('overview_view',$data);
    }
    else{
        //$this->load->view('not_permitted');
        //redirect("welcome");

        //print_r(get_menus());

        if(count(get_menus()) == 0){
            $this->load->view('not_permitted');

        }else{

            foreach (get_menus() as $menu) {

                var_dump(is_null($menu["sub_menus"]));

                if(is_null($menu["sub_menus"])){
                    redirect($menu['url']);
                    return;
                }
                else{
                    redirect($menu["sub_menus"][0]['url']);
                    return;
                }

                return;
            }
        }
        
    }
    
    $this->load->view('footer',$data);

	}


    public function manual_clocking_new(){

        $data['pageTitle'] = "Clocking Data";
        $data['active_menu'] = "overview/manual_clocking_new";
        $this->load->view('header',$data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar',$data);

        $data["selected_branch_id"] = 0;
        $data["selected_emp_id"] = 0;
        $data["selected_month"] = 0;

        

        $cid = get_user()["company_id"];

        $where_filter = "";
        $branch_where_filter = "";
        $where_clock_date = "";
        $where_date = "";

        $bid = get_user()["branch_id"];
        $permissions_level = get_user()["permissions_level"];

        //$where_branch_1 = '';
        $where_branch_2 = '';
        //$where_branch_3 = '';

        
        if($permissions_level == "Outlet"){
            
            //$where_branch_1 = " AND branch_id = $bid ";
            $where_branch_2 = " AND id = $bid ";
            //$where_branch_3 = " AND permissions_level = 'Personal' ";

            if(empty($this->input->get("branch")) || $this->input->get("branch") != $bid){
                redirect("overview/manual_clocking_new?branch=$bid&month=".date('m'));
                return;
            }

            
        }

        if(!empty($this->input->get("branch"))){
            $data["selected_branch_id"] = $this->input->get("branch");
            $where_filter = $where_filter . " employees.branch_id = " . $this->input->get("branch") . " AND " ;
            $branch_where_filter = $branch_where_filter . " AND employees.branch_id = " . $this->input->get("branch");
        }

        if(!empty($this->input->get("emp"))){
            $data["selected_emp_id"] = $this->input->get("emp");
            $where_filter = $where_filter . " employee_id = " . $this->input->get("emp") . " AND " ;
        }

       

        if(!empty($this->input->get("month"))){
            $data["selected_month"] = $this->input->get("month");
            $where_date = " AND MONTH(datetime) = " . $this->input->get("month");
        }
        else{
            redirect("overview/manual_clocking_new?month=".date('m'));
            return;
        }

        $where_filter = $where_filter . " employees.company_id = " . $cid;

        $where_filter = trim($where_filter);
        $where_filter = trim($where_filter,"AND");
        

        // if(!empty($where_filter)){
        //     $where_filter = " WHERE " . $where_filter;
        // }


        $total_records = $this->db->query("SELECT COUNT(1) as total_records FROM clockings_news INNER JOIN employees ON clockings_news.employee_id = employees.id INNER JOIN roles ON employees.role_id = roles.id INNER JOIN branches ON employees.branch_id = branches.id WHERE roles.exclude_from_system = 'no' AND clockings_news.deleted_at IS NULL AND $where_filter $where_date")->row()->total_records;

        $limit = 100;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if(!empty($this->input->get("page"))){
            $page = $this->input->get("page");
        }
        $skip = ($page -1) * $limit;





        $result = $this->db->query("SELECT clockings_news.*, shifts.name as shift_name, devices.mac_address, employees.first_name, employees.last_name, employees.special_id,employees.branch_id,branches.name as branch_name FROM clockings_news INNER JOIN employees ON clockings_news.employee_id = employees.id INNER JOIN roles ON employees.role_id = roles.id INNER JOIN branches ON employees.branch_id = branches.id INNER JOIN devices ON clockings_news.device_id = devices.device_id LEFT JOIN shifts ON clockings_news.shift_id = shifts.id WHERE roles.exclude_from_system = 'no' AND clockings_news.deleted_at IS NULL AND $where_filter $where_date ORDER BY clockings_news.datetime DESC LIMIT $skip,$limit")->result_array();

        $dateComponents = getdate();
            //$month = $dateComponents['mon'];                  
        $year = $dateComponents['year'];

        //foreach($result as &$row){

            //$result2 = $this->db->query("SELECT shift_id,date as shift_date FROM shift_days WHERE FIND_IN_SET(".$row["id"].",shift_days.employees)")->result_array();

            // $emp_id = $row["id"];


            // $result2 = $this->db->query("SELECT shift_days.*, shifts.color as color, shifts.code as code, shifts.name as shift, shifts.id = shift_id FROM shift_days INNER JOIN shifts ON shift_days.shift_id = shifts.id where FIND_IN_SET($emp_id,shift_days.employees) $where_date")->result_array();

            // // echo $this->db->last_query();
            // // die();

            

            // $max_date = cal_days_in_month(CAL_GREGORIAN, $data["selected_month"], $year);
            // //die();

            // for ($x = 1; $x <= $max_date; $x++){
            //     $dd = $year."-".$data["selected_month"]."-".sprintf("%02d",$x);            
            //     $row[$dd] = array("applicable"=>"false","assigned"=>"-","shift"=>"","shift_id"=>"","color"=>"","code"=>"");
            // }

            // foreach($result2 as &$row2){
            //     $assigned = "yes";
            //     $shift = $row2["shift"];
            //     $shift_id = $row2["shift_id"];
            //     $color = $row2["color"];
            //     $code = $row2["code"];

            //     $row[$row2["date"]] = array("applicable"=>"true","assigned"=>$assigned,"shift"=>$shift,"shift_id"=>$shift_id,"color"=>$color,"code"=>$code);
               
            // }

        //}

        $data["clockings"] = $result;

        $data["employees_dropdown"] = $this->db->query("SELECT employees.id, special_id,first_name,last_name FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE roles.exclude_from_system = 'no' AND employees.company_id = $cid $branch_where_filter ORDER BY special_id")->result();


        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        $data["skip"] = $skip;
        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET); 



        $data["branches"] = $this->db->query("SELECT * FROM branches WHERE company_id = $cid  $where_branch_2 ORDER BY name")->result();
        $data["departments"] = $this->db->query("SELECT * FROM departments WHERE company_id = $cid ORDER BY name")->result();
        $data["shifts"] = $this->db->query("SELECT * FROM shifts WHERE company_id = $cid ORDER BY is_leave DESC,name ASC")->result();
        $this->load->view('manual_clocking_new',$data);
        $this->load->view('footer',$data);

    }


    public function branch_report(){
        $data['pageTitle'] = "Branch Report";
        $data['active_menu'] = "overview/branch_report";
        $this->load->view('header',$data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar',$data);

        $cid = get_user()["company_id"];
        $data["selected_dep_id"] = 0;
        $data["selected_month"] = 0;
        $where_filter = "";


        $bid = get_user()["branch_id"];
        $permissions_level = get_user()["permissions_level"];

        //$where_branch_1 = '';
        $where_branch_2 = '';
        //$where_branch_3 = '';

        
        if($permissions_level == "Outlet"){
            
            //$where_branch_1 = " AND branch_id = $bid ";
            $where_branch_2 = " AND id = $bid ";
            //$where_branch_3 = " AND permissions_level = 'Personal' ";

            // if(empty($this->input->get("branch")) || $this->input->get("branch") != $bid){
            //     redirect("overview/shifts_calendar?branch=$bid&month=".date('m'));
            //     return;
            // }

            
        }


        if(!empty($this->input->get("dep"))){
            $data["selected_dep_id"] = $this->input->get("dep");
            $where_filter = $where_filter . " department_id = " . $this->input->get("dep") . " AND " ;
        }

        if(!empty($this->input->get("month"))){
            $data["selected_month"] = $this->input->get("month");
            $where_clock_date = " AND MONTH(clock_in) = " . $this->input->get("month");
            $where_date = " AND MONTH(date) = " . $this->input->get("month");
        }
        else{
            redirect("overview/branch_report?month=".date('m'));
            return;
        }

        $dateComponents = getdate();
            //$month = $dateComponents['mon'];                  
        $year = $dateComponents['year'];

        $data["branches"] = $this->db->query("SELECT * FROM branches WHERE company_id = $cid $where_branch_2 ORDER BY name")->result();

        //naveed

        // echo "SELECT GROUP_CONCAT(employees) as all_employees FROM shift_days WHERE YEAR(date) = 2019 AND MONTH(date) = ".$data["selected_month"]." AND $where_filter employees <> ''";
        //     die();

        foreach ($data["branches"] as $branch){

            $all_employees = $this->db->query("SELECT GROUP_CONCAT(employees) as all_employees FROM shift_days WHERE YEAR(date) = 2019 AND MONTH(date) = ".$data["selected_month"]." AND employees <> '' ")->row()->all_employees;

            $all_employees = trim($all_employees,',');
            //die($all_employees);

            // var_dump($all_employees);
            // die();

            $total_employees = $this->db->query("SELECT COUNT(id) as total_employees FROM employees WHERE branch_id = " . $branch->id . " AND deleted_at is NULL")->row()->total_employees;


            $branch->total_employees = $total_employees;
            

            $branch_id = $branch->id;

            if(!empty($all_employees)){
                
                $all_employees_array = explode(",",$all_employees);

                $emps_to_remove = $this->db->query("SELECT GROUP_CONCAT(id) as employees_to_remove FROM employees WHERE id NOT IN($all_employees) AND $where_filter branch_id = $branch_id")->row()->employees_to_remove;

                $emps_to_remove_array = explode(",",$emps_to_remove);

                //var_dump($emps_to_remove_array);
                // var_dump($all_employees_array);

                $all_employees_array = array_diff($all_employees_array, $emps_to_remove_array);
                
                $branch->shifts = count($all_employees_array);
                $branch->absent = 0;
                $branch->on_leave = 0;
                //$branch->total_employees = count($all_employees_array) + count($emps_to_remove_array);

            }
            else{
                $branch->shifts = 0;
                $branch->absent = 0;
                $branch->on_leave = 0;
                //$branch->total_employees = 0;
            }

            



            
        }

        //var_dump($data["branches"]);

        //die();


        $data["departments"] = $this->db->query("SELECT * FROM departments WHERE company_id = $cid ORDER BY name")->result();


        $this->load->view('branch_report',$data);
        $this->load->view('footer',$data);

    }


    public function attendance_report(){
        $data['pageTitle'] = "Attendance Overview";
        $data['active_menu'] = "overview/branch_report";
        $this->load->view('header',$data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar',$data);

        $cid = get_user()["company_id"];

        $data["selected_branch_id"] = 0;
        $data["selected_dep_id"] = 0;
        $data["selected_month"] = 0;


        $where_filter = "";
        $where_clock_date = "";
        $where_date = "";


        if(!empty($this->input->get("branch"))){
            $data["selected_branch_id"] = $this->input->get("branch");
            $where_filter = $where_filter . " branch_id = " . $this->input->get("branch") . " AND " ;
        }
        if(!empty($this->input->get("dep"))){
            $data["selected_dep_id"] = $this->input->get("dep");
            $where_filter = $where_filter . " department_id = " . $this->input->get("dep") . " AND " ;
        }

        if(!empty($this->input->get("month"))){
            $data["selected_month"] = $this->input->get("month");
            $where_clock_date = " AND MONTH(clock_in) = " . $this->input->get("month");
            $where_date = " AND MONTH(date) = " . $this->input->get("month");
        }
        else{
            redirect("overview/attendance_report?month=".date('m'));
            return;
        }

        $where_filter = $where_filter . " company_id = " . $cid;

        $where_filter = trim($where_filter);
        $where_filter = trim($where_filter,"AND");
        

        if(!empty($where_filter)){
            $where_filter = " WHERE " . $where_filter;
        }



        $employees = $this->db->query("SELECT id,special_id,first_name,last_name FROM employees $where_filter ORDER BY first_name")->result();


        foreach ($employees as $emp)
        {
            $emp_id = $emp->id;

            $hours_row = $this->db->query("SELECT IFNULL(SUM(TIME_FORMAT(TIMEDIFF(a.`clock_out`, a.`clock_in` ),'%H')), 0) AS hours FROM `clockings` a WHERE a.employee_id=$emp_id AND clock_out IS NOT NULL " . $where_clock_date)->row();
            

            $leaves_row = $this->db->query("SELECT  count(1) as leaves FROM shift_days WHERE shift_days.date < CURDATE() AND NOT EXISTS(SELECT null FROM clockings WHERE DATE(clockings.clock_in) = shift_days.date AND clockings.employee_id = $emp_id $where_clock_date) AND FIND_IN_SET($emp_id,shift_days.employees) $where_date")->row();

            //die($this->db->last_query());


            $early_row = $this->db->query("SELECT clockings.id,clockings.employee_id,MIN(clock_in) as clock_in,clock_out,grace_time,start_time,end_time FROM clockings 
INNER JOIN shift_days ON DATE(clockings.clock_in) = shift_days.date 
INNER JOIN shifts ON clockings.shift_id = shifts.id
WHERE FIND_IN_SET($emp_id,shift_days.employees) AND clockings.employee_id = $emp_id AND shift_days.shift_id=clockings.shift_id
GROUP BY DATE(clock_in) HAVING DATE_FORMAT(clock_in,'%H:%i') < DATE_FORMAT(start_time,'%H:%i') $where_clock_date");

            // echo $this->db->last_query();
            // die();

            $late_row = $this->db->query("SELECT clockings.id,clockings.employee_id,MIN(clock_in) as clock_in,clock_out,grace_time,start_time,end_time FROM clockings 
INNER JOIN shift_days ON DATE(clockings.clock_in) = shift_days.date 
INNER JOIN shifts ON clockings.shift_id = shifts.id
WHERE FIND_IN_SET($emp_id,shift_days.employees) AND clockings.employee_id = $emp_id AND shift_days.shift_id=clockings.shift_id GROUP BY DATE(clock_in) 
HAVING DATE_FORMAT(clock_in,'%H:%i') > DATE_FORMAT(grace_time,'%H:%i') $where_clock_date");



            


// echo "SELECT * FROM clockings 
// INNER JOIN shift_days ON clockings.clock_date = shift_days.date 
// INNER JOIN shifts ON clockings.shift_id = shifts.id
// WHERE FIND_IN_SET($emp_id,shift_days.employees) AND clockings.employee_id = $emp_id AND shift_days.shift_id=clockings.shift_id 
// GROUP BY clock_date HAVING clockings.clock_in > shifts.grace_time $where_clock_date";

//  die();


            $emp->hours = $hours_row->hours;
            $emp->leaves = $leaves_row->leaves;
            $emp->early = $early_row->num_rows();
            $emp->late = $late_row->num_rows();

        }

        $data["employees"] = $employees;



        $data["branches"] = $this->db->query("SELECT * FROM branches WHERE company_id = $cid ORDER BY name")->result();
        $data["departments"] = $this->db->query("SELECT * FROM departments WHERE company_id = $cid ORDER BY name")->result();




        $this->load->view('attendance_report',$data);
        $this->load->view('footer',$data);
    }

    public function employee_report($emp_id){

        $emp = $this->db->query("SELECT * FROM employees WHERE id = $emp_id")->row();


        $data['pageTitle'] = $emp->first_name;
        $data['active_menu'] = "overview/branch_report";
        $this->load->view('header',$data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar',$data);

        $data["selected_month"] = 0;
        $where_clock_date = "";
        $where_date = "";

        if(!empty($this->input->get("month"))){
            $data["selected_month"] = $this->input->get("month");
            $where_clock_date = " AND MONTH(clock_in) = " . $this->input->get("month");
            $where_date = " AND MONTH(date) = " . $this->input->get("month");
        }
        else{
            redirect("overview/employee_report/".$emp_id."?month=".date('m'));
            return;
        }




        //$shift_days = $this->db->query("SELECT shift_days.shift_id,shifts.name, date, employees, (SELECT MIN(clock_in) FROM clockings where clockings.clock_date = shift_days.date AND shift_days.shift_id=clockings.shift_id AND clockings.employee_id=$emp_id) as clock_in,(SELECT MAX(clock_out) FROM clockings where clockings.clock_date = shift_days.date AND shift_days.shift_id=clockings.shift_id AND clockings.employee_id=$emp_id) as clock_out, (SELECT IFNULL(SUM(TIME_FORMAT(TIMEDIFF(a.`clock_out`, a.`clock_in` ),'%H')), 0) AS hours FROM `clockings` a WHERE a.employee_id=$emp_id AND clock_out IS NOT NULL AND a.clock_date = shift_days.date) as hours, (SELECT grace_time FROM shifts where id = shift_days.shift_id) as shift_grace_time,(SELECT end_time FROM shifts where id = shift_days.shift_id) as shift_end_time,(SELECT start_time FROM shifts where id = shift_days.shift_id) as shift_start_time FROM shift_days INNER JOIN shifts ON shift_days.shift_id = shifts.id WHERE FIND_IN_SET($emp_id,shift_days.employees) $where_date ORDER BY shift_days.date")->result();

        // $shift_days = $this->db->query("SELECT (SELECT MIN(id) FROM clockings where DATE(clockings.clock_in) = shift_days.date AND shift_days.shift_id=clockings.shift_id AND clockings.employee_id=$emp_id) as id,

        //     (SELECT reason FROM clockings where DATE(clockings.clock_in) = shift_days.date AND shift_days.shift_id=clockings.shift_id AND clockings.employee_id=$emp_id LIMIT 1) as reason,

        //     shift_days.shift_id,shifts.name, date, employees,

        // (SELECT MIN(clock_in) FROM clockings where DATE(clockings.clock_in) = shift_days.date AND shift_days.shift_id=clockings.shift_id AND clockings.employee_id=$emp_id) as clock_in,

        // (SELECT MAX(clock_out) FROM clockings where DATE(clockings.clock_in) = shift_days.date AND shift_days.shift_id=clockings.shift_id AND clockings.employee_id=$emp_id) as clock_out,

        //  (SELECT auto_clock_out FROM clockings where DATE(clockings.clock_in) = shift_days.date AND shift_days.shift_id=clockings.shift_id AND clockings.employee_id=$emp_id ORDER BY id DESC LIMIT 1) as auto_clock_out,

        // (SELECT IFNULL(SUM(TIME_FORMAT(TIMEDIFF(a.`clock_out`, a.`clock_in` ),'%H')), 0) AS hours FROM `clockings` a WHERE a.employee_id=$emp_id AND clock_out IS NOT NULL AND DATE(a.clock_in) = shift_days.date) as hours,

        // (SELECT grace_time FROM shifts where id = shift_days.shift_id) as shift_grace_time,

        // (SELECT end_time FROM shifts where id = shift_days.shift_id) as shift_end_time,

        // (SELECT start_time FROM shifts where id = shift_days.shift_id) as shift_start_time 

        // FROM shift_days INNER JOIN shifts ON shift_days.shift_id = shifts.id WHERE FIND_IN_SET($emp_id,shift_days.employees) $where_date ORDER BY shift_days.date")->result();

         $shift_days = $this->db->query("SELECT (SELECT MIN(id) FROM clockings where DATE(clockings.clock_in) = shift_days.date AND clockings.employee_id=$emp_id) as id,

            (SELECT reason FROM clockings where DATE(clockings.clock_in) = shift_days.date AND clockings.employee_id=$emp_id LIMIT 1) as reason,

            (SELECT GROUP_CONCAT(DISTINCT name SEPARATOR ', ') FROM clockings INNER JOIN shifts ON clockings.shift_id = shifts.id where DATE(clockings.clock_in) = shift_days.date AND clockings.employee_id=$emp_id LIMIT 1) as shifts, 

            shift_days.shift_id,shifts.name, date, employees,

        (SELECT MIN(clock_in) FROM clockings where DATE(clockings.clock_in) = shift_days.date AND clockings.employee_id=$emp_id) as clock_in,

        (SELECT MAX(clock_out) FROM clockings where DATE(clockings.clock_in) = shift_days.date  AND clockings.employee_id=$emp_id) as clock_out,

        (SELECT auto_clock_out FROM clockings where DATE(clockings.clock_in) = shift_days.date  AND clockings.employee_id=$emp_id ORDER BY id DESC LIMIT 1) as auto_clock_out,

        (SELECT IFNULL(SUM(TIME_FORMAT(TIMEDIFF(a.`clock_out`, a.`clock_in` ),'%H')), 0) AS hours FROM `clockings` a WHERE a.employee_id=$emp_id AND clock_out IS NOT NULL AND DATE(a.clock_in) = shift_days.date) as hours,

        (SELECT IFNULL(SUM(TIME_FORMAT(TIMEDIFF(a.`clock_out`, a.`clock_in` ),'%i')), 0) AS hours FROM `clockings` a WHERE a.employee_id=$emp_id AND clock_out IS NOT NULL AND DATE(a.clock_in) = shift_days.date) as minutes,

        TIME_FORMAT(TIMEDIFF((SELECT MAX(clock_out) FROM clockings where DATE(clockings.clock_in) = shift_days.date AND clockings.employee_id=$emp_id), (SELECT Min(clock_in) FROM clockings where DATE(clockings.clock_in) = shift_days.date AND clockings.employee_id=$emp_id)),'%H:%i') as total_time,

        (SELECT grace_time FROM shifts where id = shift_days.shift_id) as shift_grace_time,

        (SELECT end_time FROM shifts where id = shift_days.shift_id) as shift_end_time,

        (SELECT start_time FROM shifts where id = shift_days.shift_id) as shift_start_time,

        (SELECT is_leave FROM shifts where id = shift_days.shift_id) as shift_is_leave,
        (SELECT is_paid FROM shifts where id = shift_days.shift_id) as shift_is_paid,
        (SELECT color FROM shifts where id = shift_days.shift_id) as shift_color

        FROM shift_days INNER JOIN shifts ON shift_days.shift_id = shifts.id WHERE FIND_IN_SET($emp_id,shift_days.employees) $where_date ORDER BY shift_days.date")->result();




        
        // echo $this->db->last_query();
        // die();


        $data["shift_days"] = $shift_days;

         //var_dump($shift_days);
         //die();


        $data["emp"] = $emp;

        $this->load->view('employee_report',$data);
        $this->load->view('footer',$data);
    }

    public function save_reason(){
        $id = $this->input->get('id');
        $reason = $this->input->get('reason');

        $data = array(
        'reason' => $reason
        );

        $this->db->where('id', $id);
        echo $this->db->update('clockings', $data);

    }

    public function delete_assignment(){

        // $temp_sql_x = $this->db->query("SELECT * FROM shift_days WHERE shift_id = 18 AND FIND_IN_SET(883,employees) AND date = '2019-03-03'");

        // var_dump($temp_sql_x->row());


        $dataa= explode(',',$this->input->post('data'));

        //var_dump($dataa);
        $response_records = array();


        foreach($dataa as $d) {
            $d_exploded = explode('|',$d);

            $employee_id = $d_exploded[0];
            $date = $d_exploded[1];
            $shift_id = $d_exploded[2];

            

            $shift_day = $this->db->query("SELECT * FROM shift_days WHERE shift_id = $shift_id AND FIND_IN_SET($employee_id,employees) AND date = '$date'")->row();

            //var_dump($this->db->last_query());

            $employees = explode(",",$shift_day->employees);

            $employees = array_diff($employees, array($employee_id));

            $remove_data = array(
                    'employees' => trim(implode(",",$employees),",")
                );

            //var_dump(empty(trim(implode(",",$employees),",")));
            //die();


                //var_dump($remove_data);

            $this->db->where('id', $shift_day->id);

            if(!empty(trim(implode(",",$employees),","))){
                $this->db->update('shift_days', $remove_data);
            }
            else{
                $this->db->delete('shift_days');
            }

            //echo "done";

             $data = array(
                'shift_id' => $shift_id,
                'date' => $date,
                'employee_id' => $employee_id
            );

            $response_records[] = $data;

        }

        echo json_encode($response_records);




    }


    public function save_clocking(){

        $clocking_id = $this->input->post('clocking_id');
        $clocking_type = $this->input->post('clocking_type');

        $response = array();

        $response["success"] = true;
        $response["clocking_id"] = $clocking_id;
        $response["clocking_type"] = $clocking_type;

        //naveed

        $data = array(
            'type' => $clocking_type
        );

        $this->db->where('id', $clocking_id);
        $this->db->update('clockings_news', $data);

        echo json_encode($response);


    }

    public function delete_clocking(){

        $clocking_id = $this->input->post('clocking_id');

        $response = array();

        $response["success"] = true;
        $response["clocking_id"] = $clocking_id;


        $data = array(
            'deleted_at' => date("Y-m-d H:i:s")
        );

        $this->db->where('id', $clocking_id);
        $this->db->update('clockings_news', $data);

        echo json_encode($response);


    }
    

    public function save_assignment(){

        // $temp_sql_x = $this->db->query("SELECT * FROM shift_days WHERE FIND_IN_SET(883,employees) AND shift_days.date = '2019-03-03'");

        // var_dump($temp_sql_x->row());
        // die();


        $dataa = explode(',',$this->input->post('data'));

        $response_records = array();

        //var_dump($dataa);



        foreach($dataa as $d) {
            $d_exploded = explode('|',$d);
            $employee_id = $d_exploded[0];
            $date = $d_exploded[1];
            $shift_id = $d_exploded[2];

            // var_dump($employee_id);
            // var_dump($date);
            // var_dump($shift_id);

        // $shift_id = $this->input->get('shift_id');
        // $date = $this->input->get('date');
        // $employee_id = $this->input->get('employee_id');

        $data = array(
            'shift_id' => $shift_id,
            'date' => $date,
            'employee_id' => $employee_id
        );

        $shift_day = $this->db->query("SELECT * FROM shift_days WHERE shift_id = $shift_id AND date = '$date'")->row();
        $shift = $this->db->query("SELECT id,name,color,code FROM shifts WHERE id = $shift_id")->row();

        //var_dump($data);

        if($shift_day){

            
            //var_dump($shift_day);
            $employees_new = explode(",",$shift_day->employees);

            $shift_day_prev = $this->db->query("SELECT * FROM shift_days WHERE date = '$date' AND FIND_IN_SET($employee_id,employees)")->row();

            $employees = array();

            if($shift_day_prev){
                $employees = explode(",",$shift_day_prev->employees);
            }

            $employees = array_diff($employees, array($employee_id));
            $employees_new = array_diff($employees_new, array($employee_id));


            if($shift_day_prev){
                $remove_data = array(
                    'employees' => trim(implode(",",$employees),",")
                );
                $this->db->where('id', $shift_day_prev->id);
                $this->db->update('shift_days', $remove_data);
            }


            array_push($employees_new,$employee_id);

            $update_data = array(
                'employees' => trim(implode(",",$employees_new),",")
            );



            $this->db->where('id', $shift_day->id);
            $this->db->update('shift_days', $update_data);


            
        }else{
            

            $shift_day_prev = $this->db->query("SELECT * FROM shift_days WHERE date = '$date' AND FIND_IN_SET($employee_id,employees)")->row();

            $employees =  array();
           
            if($shift_day_prev){
                $employees = explode(",",$shift_day_prev->employees);
            }

            $employees = array_diff($employees, array($employee_id));


            if($shift_day_prev){
                $remove_data = array(
                    'employees' => trim(implode(",",$employees),",")
                );

                $this->db->where('id', $shift_day_prev->id);
                $this->db->update('shift_days', $remove_data);
            }

            $insert_data = array(
                'shift_id' => $shift_id,
                'date' => $date,
                'employees' => $employee_id
            );

            $this->db->insert('shift_days', $insert_data);

        }

        //var_dump($shift_day);


        $update_shift_id_in_clockings = $this->db->query("UPDATE clockings_news SET shift_id = $shift_id WHERE DATE(datetime) = '$date' AND employee_id = $employee_id");


        //var_dump($update_shift_id_in_clockings);


        //print_r($data);
            $data["name"] = $shift->name;
            $data["color"] = $shift->color;
            $data["code"] = $shift->code;
            $response_records[] = $data;

        }

        echo json_encode($response_records);

        //$this->db->where('id', $id);
        //echo $this->db->update('clockings', $data);



    }

    public function clocking_details_modal(){

        $date = $this->input->get('date');
        $emp_id = $this->input->get('emp_id');

        // var_dump($date);
        // var_dump($emp_id);

        //$clocking_data = array();

        $clocking_data = $this->db->query("SELECT clockings.*, shifts.name as shift_name FROM clockings LEFT JOIN shifts ON clockings.shift_id = shifts.id WHERE DATE(clockings.clock_in) = '$date' AND clockings.employee_id = $emp_id")->result();

        // echo $this->db->last_query();
        // die();

        //var_dump($clocking_data);
        //die();
        //header('Content-Type: application/json');

        //echo json_encode($clocking_data);
        $data["clockings"] = $clocking_data;
        $data["date"] = $date;
        $string = $this->load->view('clocking_details_modal', $data, TRUE);

        echo $string;

    }

    public function attendance_sheet(){

        $data['pageTitle'] = "Attendance Sheet";
        $data['active_menu'] = "overview/branch_report";
        $this->load->view('header',$data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar',$data);

        $data["selected_branch_id"] = 0;
        $data["selected_dep_id"] = 0;
        $data["selected_month"] = 0;

        

        $cid = get_user()["company_id"];

        $where_filter = "";
        $where_clock_date = "";
        $where_date = "";

        $bid = get_user()["branch_id"];
        $permissions_level = get_user()["permissions_level"];

        //$where_branch_1 = '';
        $where_branch_2 = '';
        //$where_branch_3 = '';

        
        if($permissions_level == "Outlet"){
            
            //$where_branch_1 = " AND branch_id = $bid ";
            $where_branch_2 = " AND id = $bid ";
            //$where_branch_3 = " AND permissions_level = 'Personal' ";

            if(empty($this->input->get("branch")) || $this->input->get("branch") != $bid){
                redirect("overview/attendance_sheet?branch=$bid&month=".date('m'));
                return;
            }

            
        }


        if(!empty($this->input->get("branch"))){
            $data["selected_branch_id"] = $this->input->get("branch");
            $where_filter = $where_filter . " branch_id = " . $this->input->get("branch") . " AND " ;
        }
        if(!empty($this->input->get("dep"))){
            $data["selected_dep_id"] = $this->input->get("dep");
            $where_filter = $where_filter . " department_id = " . $this->input->get("dep") . " AND " ;
        }

        if(!empty($this->input->get("month"))){
            $data["selected_month"] = $this->input->get("month");
            $where_clock_date = " AND MONTH(clock_in) = " . $this->input->get("month");
            $where_date = " AND MONTH(date) = " . $this->input->get("month");
        }
        else{
            redirect("overview/attendance_sheet?month=".date('m'));
            return;
        }

        $where_filter = $where_filter . " employees.company_id = " . $cid;

        $where_filter = trim($where_filter);
        $where_filter = trim($where_filter,"AND");
        

        if(!empty($where_filter)){
            // /$where_filter = " WHERE " . $where_filter;
        }



       // $month_template[]

        $total_records = $this->db->query("SELECT COUNT(1) as total_records FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE roles.exclude_from_system = 'no' AND $where_filter")->row()->total_records;
        $limit = 20;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if(!empty($this->input->get("page"))){
            $page = $this->input->get("page");
        }
        $skip = ($page -1) * $limit;


        $result = $this->db->query("SELECT employees.id,special_id, first_name FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE roles.exclude_from_system = 'no' AND  $where_filter ORDER BY first_name LIMIT $skip,$limit")->result_array();

        $dateComponents = getdate();
            //$month = $dateComponents['mon'];                  
        $year = $dateComponents['year'];

        foreach($result as &$row){

            //$result2 = $this->db->query("SELECT shift_id,date as shift_date FROM shift_days WHERE FIND_IN_SET(".$row["id"].",shift_days.employees)")->result_array();

            $emp_id = $row["id"];


            $result2 = $this->db->query("SELECT (SELECT MIN(id) FROM clockings where DATE(clockings.clock_in) = shift_days.date AND shift_days.shift_id=clockings.shift_id AND clockings.employee_id=$emp_id) as id,

            (SELECT reason FROM clockings where DATE(clockings.clock_in) = shift_days.date AND shift_days.shift_id=clockings.shift_id AND clockings.employee_id=$emp_id LIMIT 1) as reason,

            shift_days.shift_id,shifts.name, date as shift_date, employees,

        (SELECT MIN(clock_in) FROM clockings where DATE(clockings.clock_in) = shift_days.date AND shift_days.shift_id=clockings.shift_id AND clockings.employee_id=$emp_id) as clock_in,

        (SELECT MAX(clock_out) FROM clockings where DATE(clockings.clock_in) = shift_days.date AND shift_days.shift_id=clockings.shift_id AND clockings.employee_id=$emp_id) as clock_out, 

        (SELECT IFNULL(SUM(TIME_FORMAT(TIMEDIFF(a.`clock_out`, a.`clock_in` ),'%H')), 0) AS hours FROM `clockings` a WHERE a.employee_id=$emp_id AND clock_out IS NOT NULL AND DATE(a.clock_in) = shift_days.date) as hours,

        (SELECT grace_time FROM shifts where id = shift_days.shift_id) as shift_grace_time,

        (SELECT end_time FROM shifts where id = shift_days.shift_id) as shift_end_time,

        (SELECT start_time FROM shifts where id = shift_days.shift_id) as shift_start_time,
        (SELECT is_leave FROM shifts where id = shift_days.shift_id) as shift_is_leave,
        (SELECT is_paid FROM shifts where id = shift_days.shift_id) as shift_is_paid

        FROM shift_days INNER JOIN shifts ON shift_days.shift_id = shifts.id WHERE FIND_IN_SET($emp_id,shift_days.employees) $where_date ORDER BY shift_days.date")->result_array();

            

            $max_date = cal_days_in_month(CAL_GREGORIAN, $data["selected_month"], $year);
            //die();

            for ($x = 1; $x <= $max_date; $x++){
                $dd = $year."-".$data["selected_month"]."-".sprintf("%02d",$x);            
                $row[$dd] = array("applicable"=>"false","presence"=>"-","status"=>"-");
            }

            foreach($result2 as &$row2){
                $presence = "-";
                $status = "-";
                $tooltip = "";

                //var_dump($row2);
                //die();

                $explode_employees = explode(',',$row2["employees"]);


                if($row2["id"] == NULL){
                    //$presence = "times";
                    //$status = "absent";

                    if(in_array($emp_id, $explode_employees)){

                        if($row2["shift_is_leave"] == "no"){
                            $presence = "calendar-times";
                            $status = "absent";
                            $tooltip = "Absent<br/> Shift: " . $row2["name"];
                        }

                        if($row2["shift_is_leave"] == "yes"){

                            if($row2["shift_is_paid"] == "yes"){
                                $presence = "calendar-plus";
                                $status = "leave";
                                $tooltip = "Paid Leave<br/> Shift: " . $row2["name"];
                            }
                            else{
                                $presence = "calendar-minus";
                                $status = "leave";
                                $tooltip = "Unpaid Leave<br/> Shift: " . $row2["name"];
                            }
                        }

                    }

                }
                else{

                    
                        
                        $presence = "calendar-check";
                        if(beautify_time($row2["clock_in"]) < beautify_time($row2["shift_start_time"])){
                            $status = "early";
                            $tooltip = "Early <br/>Clock in: " . beautify_time($row2["clock_in"]) ."<br/> Shift: " . $row2["name"];;
                        }
                        

                        if(beautify_time($row2["clock_in"]) > beautify_time($row2["shift_grace_time"])){
                            $status = "late";
                            $tooltip = "Late <br/>Clock in: " . beautify_time($row2["clock_in"]) ."<br/> Shift: " . $row2["name"];
                        }

                        if((beautify_time($row2["clock_in"]) >= beautify_time($row2["shift_start_time"])) && (beautify_time($row2["clock_in"]) <= beautify_time($row2["shift_grace_time"]))){
                            $status = "ontime";
                            $tooltip = "Ontime <br/>Clock in: " . beautify_time($row2["clock_in"]) ."<br/> Shift: " . $row2["name"];
                        }
                    

                    //$status = "absent";

                }


               // var_dump(date("Y-m-d", strtotime(($row2["shift_date"]))));

                //echo '---';

                //var_dump(date($row2["shift_date"]));
                //var_dump(strtotime($row2["shift_date"]) < strtotime(date("Y-m-d")));

                $s_d = strtotime($row2["shift_date"]);
                $t = strtotime(date("Y-m-d"));

                //var_dump($s_d > $t);

                //var_dump( $row2["shift_date"] . "-" . $s_d . " **** " . date("Y-m-d") . '-' .$t);

                if($s_d > $t){
                   // var_dump($row[$row2["shift_date"]]);
                    //echo " bigger ";
                    //var_dump($row2["shift_date"]);
                    $row[$row2["shift_date"]] = array("applicable"=>"false","presence"=>"-","status"=>"","tooltip"=>"");

                }
                else{
                    $row[$row2["shift_date"]] = array("applicable"=>"true","presence"=>$presence,"status"=>$status,"tooltip"=>$tooltip);

                }

               
            }

        }

        $data["employees"] = $result;
        
        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        $data["skip"] = $skip;
        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET); 

        // print_r($data["employees"]);
        // die();


        $data["branches"] = $this->db->query("SELECT * FROM branches WHERE company_id = $cid $where_branch_2 ORDER BY name")->result();
        $data["departments"] = $this->db->query("SELECT * FROM departments WHERE company_id = $cid ORDER BY name")->result();
        $this->load->view('attendance_sheet',$data);
        $this->load->view('footer',$data);

    }

    public function shifts_assignment(){

        $data['pageTitle'] = "Shift Assignment";
        $data['active_menu'] = "overview/shifts_assignment";
        $this->load->view('header',$data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar',$data);

        $data["selected_branch_id"] = 0;
        $data["selected_dep_id"] = 0;
        $data["selected_month"] = 0;

        

        $cid = get_user()["company_id"];

        $where_filter = "";
        $where_clock_date = "";
        $where_date = "";

        $bid = get_user()["branch_id"];
        $permissions_level = get_user()["permissions_level"];

        //$where_branch_1 = '';
        $where_branch_2 = '';
        //$where_branch_3 = '';

        
        if($permissions_level == "Outlet"){
            
            //$where_branch_1 = " AND branch_id = $bid ";
            $where_branch_2 = " AND id = $bid ";
            //$where_branch_3 = " AND permissions_level = 'Personal' ";

            if(empty($this->input->get("branch")) || $this->input->get("branch") != $bid){
                redirect("overview/shifts_assignment?branch=$bid&month=".date('m'));
                return;
            }

            
        }



        



        if(!empty($this->input->get("branch"))){
            $data["selected_branch_id"] = $this->input->get("branch");
            $where_filter = $where_filter . " branch_id = " . $this->input->get("branch") . " AND " ;
        }

        if(!empty($this->input->get("dep"))){
            $data["selected_dep_id"] = $this->input->get("dep");
            $where_filter = $where_filter . " department_id = " . $this->input->get("dep") . " AND " ;
        }

        if(!empty($this->input->get("month"))){
            $data["selected_month"] = $this->input->get("month");
            $where_clock_date = " AND MONTH(clock_in) = " . $this->input->get("month");
            $where_date = " AND MONTH(date) = " . $this->input->get("month");
        }
        else{
            redirect("overview/shifts_assignment?month=".date('m'));
            return;
        }

        $where_filter = $where_filter . " employees.company_id = " . $cid;

        $where_filter = trim($where_filter);
        $where_filter = trim($where_filter,"AND");
        

        // if(!empty($where_filter)){
        //     $where_filter = " WHERE " . $where_filter;
        // }


        $total_records = $this->db->query("SELECT COUNT(1) as total_records FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE roles.exclude_from_system = 'no' AND $where_filter")->row()->total_records;
        $limit = 20;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if(!empty($this->input->get("page"))){
            $page = $this->input->get("page");
        }
        $skip = ($page -1) * $limit;


        $result = $this->db->query("SELECT employees.id, special_id,first_name FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE roles.exclude_from_system = 'no' AND $where_filter ORDER BY first_name LIMIT $skip,$limit")->result_array();

        $dateComponents = getdate();
            //$month = $dateComponents['mon'];                  
        $year = $dateComponents['year'];

        foreach($result as &$row){

            //$result2 = $this->db->query("SELECT shift_id,date as shift_date FROM shift_days WHERE FIND_IN_SET(".$row["id"].",shift_days.employees)")->result_array();

            $emp_id = $row["id"];


            $result2 = $this->db->query("SELECT shift_days.*, shifts.color as color, shifts.code as code, shifts.name as shift, shifts.id = shift_id FROM shift_days INNER JOIN shifts ON shift_days.shift_id = shifts.id where FIND_IN_SET($emp_id,shift_days.employees) $where_date")->result_array();

            // echo $this->db->last_query();
            // die();

            

            $max_date = cal_days_in_month(CAL_GREGORIAN, $data["selected_month"], $year);
            //die();

            for ($x = 1; $x <= $max_date; $x++){
                $dd = $year."-".$data["selected_month"]."-".sprintf("%02d",$x);            
                $row[$dd] = array("applicable"=>"false","assigned"=>"-","shift"=>"","shift_id"=>"","color"=>"","code"=>"");
            }

            foreach($result2 as &$row2){
                $assigned = "yes";
                $shift = $row2["shift"];
                $shift_id = $row2["shift_id"];
                $color = $row2["color"];
                $code = $row2["code"];

                $row[$row2["date"]] = array("applicable"=>"true","assigned"=>$assigned,"shift"=>$shift,"shift_id"=>$shift_id,"color"=>$color,"code"=>$code);
               
            }

        }

        $data["employees"] = $result;

        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        $data["skip"] = $skip;
        unset($_GET['page']);
        $currentURL = current_url();
        $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET); 



        $data["branches"] = $this->db->query("SELECT * FROM branches WHERE company_id = $cid  $where_branch_2 ORDER BY name")->result();
        $data["departments"] = $this->db->query("SELECT * FROM departments WHERE company_id = $cid ORDER BY name")->result();
        $data["shifts"] = $this->db->query("SELECT * FROM shifts WHERE company_id = $cid ORDER BY is_leave DESC,name ASC")->result();
        $this->load->view('shifts_assignment',$data);
        $this->load->view('footer',$data);

    }

    public function shifts_calendar(){

        $data['pageTitle'] = "Shifts Calendar";
        $data['active_menu'] = "overview/shifts_calendar";
        $this->load->view('header',$data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar',$data);

        $cid = get_user()["company_id"];

        $data["selected_branch_id"] = 0;
        $data["selected_dep_id"] = 0;
        $data["selected_month"] = 0;
        
        $where_filter = "";
        $where_clock_date = "";
        $where_date = "";

        $bid = get_user()["branch_id"];
        $permissions_level = get_user()["permissions_level"];

        //$where_branch_1 = '';
        $where_branch_2 = '';
        //$where_branch_3 = '';

        
        if($permissions_level == "Outlet"){
            
            //$where_branch_1 = " AND branch_id = $bid ";
            $where_branch_2 = " AND id = $bid ";
            //$where_branch_3 = " AND permissions_level = 'Personal' ";

            if(empty($this->input->get("branch")) || $this->input->get("branch") != $bid){
                redirect("overview/shifts_calendar?branch=$bid&month=".date('m'));
                return;
            }

            
        }


        if(!empty($this->input->get("branch"))){
            $data["selected_branch_id"] = $this->input->get("branch");
            $where_filter = $where_filter . " branch_id = " . $this->input->get("branch") . " AND " ;
        }
        if(!empty($this->input->get("dep"))){
            $data["selected_dep_id"] = $this->input->get("dep");
            $where_filter = $where_filter . " department_id = " . $this->input->get("dep") . " AND " ;
        }

        if(!empty($this->input->get("month"))){
            $data["selected_month"] = $this->input->get("month");
            $where_clock_date = " AND MONTH(clock_in) = " . $this->input->get("month");
            $where_date = " AND MONTH(date) = " . $this->input->get("month");
        }
        else{
            redirect("overview/shifts_calendar?month=".date('m'));
            return;
        }

        $where_filter = $where_filter . " company_id = " . $cid;

        $where_filter = trim($where_filter);
        $where_filter = trim($where_filter,"AND");
        

        if(!empty($where_filter)){
            $where_filter = " WHERE " . $where_filter;
        }

        $dateComponents = getdate();
        //$month = $dateComponents['mon'];                  
        $year = $dateComponents['year'];

        $max_date = cal_days_in_month(CAL_GREGORIAN, $data["selected_month"], $year);
        //die();


        for ($x = 1; $x <= $max_date; $x++){

            $dd = $year."-".$data["selected_month"]."-".sprintf("%02d",$x);

            $result = $this->db->query("SELECT shifts.name, IFNULL((SELECT (LENGTH(shift_days.employees) - LENGTH(REPLACE(shift_days.employees, ',', ''))+ 1) FROM shift_days where shift_days.shift_id = shifts.id AND shift_days.date = '$dd'),0) as count FROM shifts where shifts.company_id = $cid")->result_array();

            $total_assigned_emp = array_sum(array_column($result, 'count'));

            
            $result2 = $this->db->query("SELECT DATE(clock_in) as clock_in,COUNT(employee_id) as cnt FROM (SELECT clockings.employee_id,MIN(clock_in) as clock_in FROM clockings 
                INNER JOIN shifts ON clockings.shift_id = shifts.id WHERE shifts.company_id = $cid AND DATE(clock_in) = '$dd'
                GROUP BY DATE(clock_in),clockings.employee_id) as xx")->row_array();

            //var_dump($result2);

            $result[] = array("name"=>"Absent","count"=>($total_assigned_emp - $result2["cnt"]));



             $data["shifts_calendar_data"][$dd] = $result;

        }

        // echo $this->db->last_query();
        // die();

        // print_r($data["shifts_calendar_data"]);
        // die();


       



        $data["branches"] = $this->db->query("SELECT * FROM branches WHERE company_id = $cid $where_branch_2 ORDER BY name")->result();
        $data["departments"] = $this->db->query("SELECT * FROM departments WHERE company_id = $cid ORDER BY name")->result();

        $this->load->view('shifts_calendar',$data);
        $this->load->view('footer',$data);
    }


}
?>
