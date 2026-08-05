<?php
class Cronjob extends CI_Controller {

	function __construct()
    {
      parent::__construct();
      $this->load->model("employee");
      $this->load->model("merit");
    }

	public function Index()
	{
		
		date_default_timezone_set("Asia/Kuala_Lumpur");


		$code = $this->input->get('code');

		if($code == "nashnash"){

			$date_now = new DateTime();
			$date_now_string = $date_now->format("H:i:s");

			$date_only_today_string = $date_now->format("Y-m-d");

			//$datetime1 = new DateTime('2009-10-11 10:10:00');
			//$datetime2 = new DateTime('2009-10-11 10:15:10');

			//var_dump($date_now);

			//var_dump($date_now > $datetime1);

			// $interval = $datetime1->diff($datetime2);

			// var_dump($interval);
			// echo "<br/>";
			// echo $interval->format('%R%a days');

			$forgot_to_clockout = $this->db->query("SELECT clockings.*, shifts.start_time,shifts.end_time, shifts.auto_clockout_time FROM clockings INNER JOIN shifts ON clockings.shift_id=shifts.id WHERE clock_out IS NULL AND '$date_now_string' > auto_clockout_time")->result();

			foreach ($forgot_to_clockout as $row)
			{
			        $data = array(
					        'clock_out' => $date_only_today_string . " " . $row->auto_clockout_time,
					        'auto_clock_out' => 'Yes'
					);

					//print_r($data);

					$this->db->where('id', $row->id);
					$this->db->update('clockings', $data);
			}

			echo  count($forgot_to_clockout) . ' auto clocked out';

			//echo $this->db->last_query() . "<br/><br/>";

			//var_dump($forgot_to_clockout);
		}
		else{
			die("Access denied!");
		}

		


	}

	public function temp_clocking_table(){


		$maketemp = "CREATE TEMPORARY TABLE temp_table_1 (
			      `itineraryId` int NOT NULL,
			      `live` varchar(1),
			      `shipCode` varchar(10),
			      `description` text,
			      `duration` varchar(10),
			      PRIMARY KEY(itineraryId)
			    )"; 

		$this->db->query($maketemp);

		echo $this->db->last_query();


		echo "test";


	}

	function save_overtimes(){
        $date = $this->input->get('date');
        $m = date('m');
        $d = date('d');
        $y = date('Y');
        if($date != ""){
            $day = $date;
        }else{
            $day = $y."-".$m."-".sprintf("%02d",$d);
        }
        $employees = $this->db->select('e.id,e.company_id,e.branch_id,e.department_id')->from('employees e')->join('roles r','e.role_id = r.id')->where('e.deleted_at is null')->where('exclude_from_system', 'no')->get()->result();
        
        
        foreach ($employees as $emp) {
            $overtime = $this->count_overtime($emp->id,$day);
            $overtime = $this->getHours($overtime);
            $data = array("employee_id" => $emp->id,
                "company_id" => $emp->company_id,
                "branch_id" => $emp->branch_id,
                "department_id" => $emp->branch_id,
                "date" => $day,
                "overtime" => $overtime);
            $this->db->replace('overtimes', $data);            
        }

        echo "Overtimes added successfully for date ".$day;
    }

    function count_overtime($id,$date){

        $date_obj = DateTime::createFromFormat('Y-m-d', $date);

        $date_f = $date_obj->format('d-m-Y');

        $result = $this->db->select('c.id,date_format(clock_in, "%d %b %Y, %a") as day_f, date_format(clock_in,"%H:%i") as clock_in, date_format(clock_in,"%d-%m-%Y %H:%i") as clock_in_1,date_format(clock_out,"%H:%i") as clock_out,date_format(clock_out,"%d-%m-%Y %H:%i") as clock_out_1,date_format(overtime_starts,"%H:%i") as overtime_starts,is_ot, fixed_ot, fixed_overtime',false)->from('clockings c')->join('shifts s','c.shift_id = s.id','left')->join('ot_days od','od.employee_id = c.employee_id and od.ot_date = date(clock_in)','left')->where('date(clock_in)',$date)->where('c.employee_id',$id)->get()->result();




        $overtime = "";

        $formatted_data = array();

        $obj = new stdClass();

        

        if($result){
            $v = $result[0];
            // if is_ot is "N"
            if($v->is_ot == null or $v->is_ot == "N"){
                return "00:00";
            }
            if($v->fixed_ot == 'Y'){
                $formatted_ot = $v->fixed_overtime;
                if($formatted_ot == "00:00:00"){
                    $formatted_ot = "";
                }else{
                    $formatted_ot = explode(":", $formatted_ot);
                    unset($formatted_ot[2]);
                    $formatted_ot = implode(":", $formatted_ot);
                }
                $overtime = $formatted_ot;
            }else{
                foreach ($result as $key => $value) {



                    $formatted_data[] = $value;

                    if(array_key_exists($key+1, $result)){

                        $x = new stdClass();

                        $x->overtime_starts = $value->overtime_starts;

                        $x->clock_in = $value->clock_out;

                        $x->clock_in_1 = $value->clock_out_1;

                        $x->clock_out = $result[$key+1]->clock_in;

                        $x->clock_out_1 = $result[$key+1]->clock_in_1;

                        $x->name = "Break";

                        $formatted_data[] = $x;

                    }

                }

                foreach($formatted_data as $clock){

                    $overtime = $this->overtime2($overtime, $clock->clock_in_1, $clock->clock_out_1, $clock->overtime_starts, $date_f);

                }
            }
        }

        $manual_ot = $this->db->select('overtime')->from('manual_ot')->where('employee_id', $id)->where('date', $date)->get()->row();
        if($manual_ot){
            $overtime = $this->add_time($overtime, $manual_ot->overtime);
        }

        $overtime = (empty($overtime)) ? "00:00" : $overtime;

        return $overtime;

    }

    public function overtime2($overtime, $clock_in_1, $clock_out_1, $overtime_starts, $date){

        if(empty($clock_in_1) || empty($clock_out_1) || $overtime_starts == ""){

            return "";

        }

        $overtime_starts = $date." ".$overtime_starts;

        $overtime_starts = DateTime::createFromFormat('d-m-Y H:i', $overtime_starts);

        $clock_in = DateTime::createFromFormat('d-m-Y H:i', $clock_in_1);

        $clock_out = DateTime::createFromFormat('d-m-Y H:i', $clock_out_1);

        

        if($clock_in > $overtime_starts){

            $interval = $this->total_time($clock_in_1,$clock_out_1);

            $overtime = $this->add_time($overtime,$interval);

        }else if($clock_out > $overtime_starts){

            $interval = $this->total_time(date_format($overtime_starts,"d-m-Y H:i"),$clock_out_1);

            $overtime = $this->add_time($overtime,$interval);

        }



        return $overtime;

    }

    function getHours($time){
        $time = explode(":", $time);
        return round($time[0] + ($time[1]/60), 2);
    }

    public function add_time($time1,$time2){

        if($time2 == null || $time2 == "" || $time2 == "00:00"){

            return $time1;

        }

        if(empty($time1)){

            $time1 = "00:00";

        }

        $time1 = explode(":", $time1);

        $time2 = explode(":", $time2);

        $hours = $time1[0] + $time2[0];

        $minutes = $time1[1] + $time2[1];

        if($minutes >= 60){

            $minutes -= 60;

            $hours = $hours + 1;

        }

        $hours = sprintf("%02d", $hours);

        $minutes = sprintf("%02d", $minutes);

        return $hours.":".$minutes;

    }

    public function total_time($a , $b){

        if($a == null || $b == null){

            return "00:00";

        }

        $time1 = DateTime::createFromFormat('d-m-Y H:i', $a);

        $time2 = DateTime::createFromFormat('d-m-Y H:i', $b);

        $interval = date_diff($time1,$time2);

        $days = $interval->format('%a');

        $format = $interval->format('%H:%i');

        $format = explode(":", $format);

        $format[0] = $format[0] + ($days * 24);

        $format[0] = sprintf("%02d", $format[0]);

        $format[1] = sprintf("%02d", $format[1]);

        $format = implode(":", $format);

        return $format;

    }

    function auto_clock_out(){
        $date_now = new DateTime();
        $time_string = $date_now->format("H:i:s");
        $date_string = $date_now->format("Y-m-d");
        $shifts = $this->db->select('id,auto_clockout_time,overnight')->from('shifts')->where('auto_clockout_time is not null')->where('auto_clockout_time <=', $time_string)->get()->result();
            $count = 0;
        foreach ($shifts as $shift) {
            
            $get_employees = $this->db->select('employees')->from('shift_days')->where('date', $date_string)->where('shift_id', $shift->id)->get()->row();
            if($get_employees){
                $employees = $get_employees->employees;
                $employees = explode(",", $employees);
                if($shift->overnight == "No"){
                    $result_list = get_result_list($employees, $date_string, $date_string);
                }else{
                    $result_list = get_result_list_overnight($employees, $date_string, $date_string);
                }

                foreach($employees as $emp){
                    $clockings = search_clocking_by_id($result_list, $date_string, $emp);
                    $last_clocking = end($clockings);
                    if($last_clocking){
                        if($last_clocking->clock_out == ""){
                            $check = $this->db->select('id')->from('clockings_news')->where('shift_id', $shift->id)->where('employee_id', $emp)->where('datetime', $date_string." ".$shift->auto_clockout_time)->get()->row();
                            if(!$check){
                                $count++;
                                $data = array("shift_id" => $shift->id, "employee_id" => $emp, "mode" => "AUTO", "type" => "out", "datetime" => $date_string." ".$shift->auto_clockout_time);
                                $this->db->insert('clockings_news', $data);
                            }
                            
                        }
                    }
                }

                
            }
        }

        echo $count." clock outs inserted";
    }

    public function auto_approve_days(){
        $shifts = $this->db->select('id,auto_approve_ot')->from('shifts')->get()->result();
        $date = date('Y-m-d');
        foreach ($shifts as $shift) {
            $auto_approve_ot = $shift->auto_approve_ot;
            $approve_data = array('shift_id' => $shift->id,
             'approve_date' => $date,
             'is_approved' => $auto_approve_ot);
            $this->db->replace('auto_approve_days', $approve_data);
        }
        echo count($shifts)." shift(s) auto approve status updated in auto_approve_days table.";
    }

    /**
     * The cronjob function to populate merit points every month
     *
     * @return void
     */
    public function populate_merit_points()
    {
        if (!$this->input->is_cli_request()) {
            echo "Access denied.";
            exit;
        }

        $employees = $this->employee->get_employees([]);
        $current_date =  DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
        $month = $current_date->format('m');
        $year = $current_date->format('Y');
        $merit_points_added = 0;
        $data = [];
        foreach ($employees as $employee) {
            $is_merit_found = $this->merit->is_merit_found($employee->id, $month, $year);
            if (!$is_merit_found) {
                $data[] = [
                    'employee_id' => $employee->id,
                    'month' => $month,
                    'year' => $year,
                    'points' => $this->merit->default_merit_points,
                ];
                $merit_points_added++;
            }
        }
        if (!empty($data)) {
            $this->merit->add_merit_points($data);
        }
        echo $merit_points_added . " merit points added\n";
    }

    public function save_merit_points()
    {
        if (!$this->input->is_cli_request()) {
            echo "Access denied.";
            exit;
        }

        echo "Save merit points cronjob started!" . PHP_EOL;
        $now = new DateTime();
        $previousMonth = $now->modify("first day of previous month");
        $month = $previousMonth->format("m");
        $year = $previousMonth->format("Y");

        $companies = [39, 2, 3];
        echo "Company IDs" . PHP_EOL;
        var_dump($companies);
        foreach ($companies as $cid) {
            $interval_minutes = get_interval_minutes($cid);
            $employees = $this->db->select('e.id,e.company_id, e.first_name,special_id,e.is_daily_waged, d.name as department, p.title as position,e.branch_id,is_ot,is_early_ot,inc_late_in,inc_late_break, inc_early_out, inc_short_hours,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,round_by_exact_hour,different_first_hour_rounding,void_lateness_time_if_less_than,deduct_from_ot,deduct_from_ot_single,deduction_date')->from('employees e')->join('roles r', 'e.role_id = r.id', 'left')->join('departments d', 'd.id = e.department_id', 'left')->join('branches b', 'b.id = e.branch_id', 'left')->join('positions p', 'p.id = e.position_id', 'left')->where(" e.company_id = $cid AND r.exclude_from_system = 'no' AND e.deleted_at is NULL AND employee_status = 'active'")->order_by('e.special_id', 'ASC')->get()->result();
            $max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            $employees_ids = ['0'];
            foreach ($employees as &$employee) {
                $employees_ids[] = $employee->id;
            }

            $first_day = sprintf("%04d-%02d-%02d", $year, $month, 1);
            $last_day = sprintf("%04d-%02d-%02d", $year, $month, $max_date);

            $company_working_hours = get_company_working_hours($cid);
            $company_ot_settings = get_company_ot_settings($cid);
            $company_early_ot_settings = get_company_early_ot_settings($cid);
            $branch_rest_days = $this->db->select('id,rest_days,off_days')->from('branches')->where('company_id', $cid)->get()->result();
            // todo
            // $public_holidays_all = get_public_holidays_with_name();

            // $public_holidays = $public_holidays_all[0];
            // $public_holidays_names = $public_holidays_all[1];

            // $public_holidays_all = get_public_holidays_all();

            $merit_deduction_points = [];

            $result_list = get_result_list($employees_ids, $first_day, $last_day);
            $result_list_overnight = get_result_list_overnight($employees_ids, $first_day, $last_day);

            $clockings_news = $this->db->select('id,employee_id,type,shift_id,date_format(datetime,"%H:%i") as clock_time,date_format(datetime, "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false)->from('clockings_news')->where('date(datetime) >=', $first_day)->where('date(datetime) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();
            $clockings_news_overnight = $this->db->select('id,employee_id,type,shift_id, date_format(datetime,"%H:%i") as clock_time,date_format(date_sub(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false)->from('clockings_news')->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) >=', $first_day)->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();

            // if ($permissions_level == "Outlet") {
            //     $shifts = $this->db->select('id')->from('shifts')->where('branch_id', $bid)->where('is_leave', 'no')->get()->result();
            //     $this->load->model("Merit");
            //     $merit_deduction_points = $this->Merit->get_deduction_points($cid, $bid);
            // } else {
            $shifts = $this->db->select('id')->from('shifts')->where('company_id', $cid)->where('is_leave', 'no')->get()->result();
            $this->load->model("Merit");
            $merit_deduction_points = $this->Merit->get_deduction_points($cid);
            // }

            $shift_ids = array(0);
            foreach ($shifts as $s) {
                $shift_ids[] = $s->id;
            }

            $approved_ot_list = get_approved_ot_list($shift_ids, $first_day, $last_day);

            $output_employees = [];

            $default_offenses = default_offenses();

            $filler_array = [];
            foreach ($employees as $employee) {
                $calculated_data = calculate_summary_data($employee->id, $first_day, $last_day, "merit_system", $employee, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days, $cid, $filler_array, $filler_array, $filler_array, $clockings_news, $clockings_news_overnight);
                $temp = calculate_merit($employee, $calculated_data, $default_offenses, $merit_deduction_points, $first_day, $last_day, $default_offenses);
                $this->db->where('employee_id', $employee->id);
                $this->db->where('month', $month);
                $this->db->where('year', $year);
                $record = $this->db->get('merit_points')->row();
                // print_r($record);die;
                if (!empty($record)) {
                    $this->db->where('employee_id', $employee->id);
                    $this->db->where('month', $month);
                    $this->db->where('year', $year);
                    $this->db->delete('merit_points');
                }

                $this->db->where('employee_id', $employee->id);
                $this->db->where('month', date('m'));
                $this->db->where('year', date('Y'));
                $this->db->insert('merit_points', array('employee_id' => $employee->id, 'company_id' => $employee->company_id, 'points' => $temp["total_points"], 'month' => $month, 'year' => $year));
                echo "Saved merit points " . $temp["total_points"] . " for employee " . $employee->special_id . " for month " . $month . " for year " . $year . PHP_EOL;
            }
        }
    }

    public function duplicate_shifts($company_id = 0, $branch_id = 0)
    {
        if ($company_id == 0) {
            echo 'No company id found';
            return;
        }

        // get the names of columns except id, so they can be batch inserted
        $shift_columns_list = $this->db->query("SELECT GROUP_CONCAT(column_name) AS columns_to_select 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE table_schema = '" . $this->db->database . "' AND table_name = 'shifts' 
            AND column_name NOT IN ('created_at', 'updated_at', 'deleted_at');")
        ->row()->columns_to_select;

        $this->db->select($shift_columns_list)->from('shifts')->where('company_id', $company_id)
            ->where('is_leave', 'no')->where('active', 1);
        if ($branch_id != 0) {
            $this->db->where('branch_id', $branch_id);
        }
        $shifts = $this->db->get()->result_array();

        $failed_records = [];
        $success_count = 0;
        if (!empty($shifts)) {
            foreach ($shifts as $shift) {
                $id = $shift['id'];
                unset($shift['id']);

                $this->db->insert('shifts', $shift);

                if ($this->db->affected_rows() > 0) {
                    $success_count++;
                } else {
                    $failed_records[] = $id;
                }
            }

            if ($success_count > 0) {
                echo 'Inserted ' . $success_count . ' records successfully' . PHP_EOL;
            }
            if (count($failed_records) > 0) {
                echo "<pre>";
                var_dump($failed_records);
                echo "</pre>";
            }
        } else {
            echo 'Records not found.';
        }
    }
}
