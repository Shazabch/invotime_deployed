<?php
class Exports extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		if(is_null(get_user())){
			redirect("welcome");
				//var_dump($this->session->userdata('antelope_user'));
		}

	}

	public function index(){
		$data['pageTitle'] = "Export Summary";
		$data['active_menu'] = "exports";
		$this->load->view('header',$data);
		$data["menus"] = get_menus();
		$this->load->view('sidebar',$data);

		$data["selected_branch_id"] = 0;
		$first_day = date('Y-m-01');
		$last_day  = date('Y-m-t');
		$date = DateTime::createFromFormat('Y-m-d', $first_day);
		$data['from_f'] = $date->format('d/m/Y');
		$date = DateTime::createFromFormat('Y-m-d', $last_day);
		$data['to_f'] = $date->format('d/m/Y');




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
				redirect("exports?branch=$bid&month=".date('m'));
				return;
			}


		}


		if(!empty($this->input->get("branch"))){
            $data["selected_branch_id"] = $this->input->get("branch");
            //$where_filter = $where_filter . " branch_id = " . $this->input->get("branch") . " AND " ;
        }




		$where_filter = $where_filter . " employees.company_id = " . $cid;

		$where_filter = trim($where_filter);
		$where_filter = trim($where_filter,"AND");







		$data["branches"] = $this->db->query("SELECT * FROM branches WHERE company_id = $cid  $where_branch_2 ORDER BY name")->result();

		$this->load->view('export_summary',$data);
		$this->load->view('footer',$data);
	}

	public function summary_pdf(){
		$date1 = DateTime::createFromFormat('d/m/Y', $_GET['from']);
		$date2 = DateTime::createFromFormat('d/m/Y', $_GET['to']);
		if($date1 > $date2){
			$first_day = $date2->format('Y-m-d');
			$last_day = $date1->format('Y-m-d');
			$month_name = $date2->format('F');
		}else{
			$first_day = $date1->format('Y-m-d');
			$last_day = $date2->format('Y-m-d');
			$month_name = $date1->format('F');
		}

		$files = array();

		$fazool = "";



		$branch_id = $_GET["branch"];

		if($branch_id == ""){
			$branch_name = "All";
		}else{
			$branch_name = $this->db->select('name')->from('branches')->where('id', $branch_id)->get()->row()->name;
		}

		$cid = get_user()["company_id"];
		$c_name = get_user()["company_name"];

		


		$this->db->select('employees.id')->from('employees')->join('roles','employees.role_id = roles.id','left')->where('employees.company_id', $cid)->where('employees.deleted_at is null')->where('roles.exclude_from_system','no');
		if($branch_id != ""){
			$this->db->where('employees.branch_id', $branch_id);
		}
		$employees = $this->db->get()->result();

		foreach($employees as $emp){
			$data['employee'] = $this->db->select('e.id as emp_id,first_name,special_id,d.name as department,p.title as position')->from('employees e')->join('departments d','d.id = e.department_id')->join('positions p','p.id = e.position_id')->where('e.id',$emp->id)->get()->row();
			

			
			
			$period = new DatePeriod(
				new DateTime($first_day),
				new DateInterval('P1D'),
				(new DateTime($last_day))->add(new DateInterval('P1D'))
			);
			$total = "00:00";
			$work = "00:00";
			$break = "00:00";
			$month_overtime = "00:00";
			foreach($period as $date) {
				$obj = new stdClass();
				$obj->date = $date->format('Y-m-d');
				$date_f = $date->format('d-m-Y');
				$date_string = $date->format('d/m D');
				$is_ot = false;
				$result = $this->db->select('c.id,"'.$date_string.'" as day_f, date_format(clock_in,"%H:%i") as clock_in, date_format(clock_in,"%d-%m-%Y %H:%i") as clock_in_1,date_format(clock_out,"%H:%i") as clock_out,date_format(clock_out,"%d-%m-%Y %H:%i") as clock_out_1,s.name,reason,date_format(overtime_starts,"%H:%i") as overtime_starts,is_ot,time_format(timediff(end_time,start_time),"%H:%i") as shift_hours',false)->from('clockings c')->join('shifts s','c.shift_id = s.id','left')->join('ot_days od','od.employee_id = c.employee_id and od.ot_date = date(clock_in)','left')->where('date(clock_in)',$obj->date)->where('c.employee_id',$emp->id)->get()->result();

				
			// if($result){
			// 	print_r($result[0]);
			// 	die();
			// }

				$total_hours = "";
				$work_hours = "";
				$break_hours = "";
				$formatted_data = array();
				if($result && $result[0]->is_ot == "Y"){
					$is_ot = true;
				}
				foreach ($result as $key => $value) {
					$value->total_time = $this->total_time($value->clock_in_1,$value->clock_out_1);
					if($value->name == ""){
						$value->name = "N/A";
					}

					$formatted_data[] = $value;
					if(array_key_exists($key+1, $result)){
						$x = new stdClass();
						$x->day_f = $value->day_f;
						$x->overtime_starts = $value->overtime_starts;
						$x->clock_in = $value->clock_out;
						$x->clock_in_1 = $value->clock_out_1;
						$x->clock_out = $result[$key+1]->clock_in;
						$x->clock_out_1 = $result[$key+1]->clock_in_1;
						$x->name = "Break";
						$x->reason = "";
						$x->is_ot = $value->is_ot;
						$x->total_time = $this->total_time($result[$key+1]->clock_in_1,$value->clock_out_1);
						$formatted_data[] = $x;
					}
				}
				$obj->clockings = $formatted_data;
				foreach ($obj->clockings as $key => $value) {
					if($key !=0){
						$value->day_f = '';
					}
					$total_hours = $this->add_time($total_hours,$value->total_time);
					if(($key + 1) % 2 == 1){
						$work_hours = $this->add_time($work_hours,$value->total_time);
					}else{
						$break_hours = $this->add_time($break_hours,$value->total_time);
					}
				}
				$obj->total_hours = $total_hours;
				$obj->work_hours = $work_hours;
				$obj->break_hours = $break_hours;
				$overtime = "";
				foreach($obj->clockings as $clock){
					$overtime = $this->overtime2($overtime, $clock->clock_in_1, $clock->clock_out_1, $clock->overtime_starts, $date_f);
				}
				$obj->overtime = $overtime;
				$obj->is_ot = $is_ot;


				$dates[] = $obj;
				if($is_ot){
					$month_overtime = $this->add_time($month_overtime,$overtime);
				}

				if(!$obj->clockings){
					$shift_name = "";
					$shift = $this->db->select('name')->from('shift_days sd')->join('shifts s','s.id = sd.shift_id')->where('FIND_IN_SET('.$emp->id.',employees)>',0)->where('date',$obj->date)->get()->row();
					
					if($shift){
						$shift_name = $shift->name;
					}

					$no_data = new stdClass();
					$no_data->day_f = $date_string;
					$no_data->name = $shift_name;
					$no_data->clock_in = "";
					$no_data->clock_out = "";
					$no_data->reason = "";
					$no_data->total_time = "";
					$obj->clockings[0] = $no_data;
				}
				$total = $this->add_time($total,$total_hours);
				$work = $this->add_time($work,$work_hours);
				$break = $this->add_time($break,$break_hours);
			}
			$data['total'] = $total;
			$data['work'] = $work;
			$data['break'] = $break;
			$data['month_overtime'] = $month_overtime;
			$data['dates'] = $dates;
		// echo "<pre>";
		// print_r($dates);
		// die();
			$date = DateTime::createFromFormat('Y-m-d', $first_day);
			$data['from_f'] = $date->format('d/m/Y');
			$date = DateTime::createFromFormat('Y-m-d', $last_day);
			$data['to_f'] = $date->format('d/m/Y');

			$html = $this->load->view('summary_pdf', $data, true);
			// echo $html;
			// echo "<hr>";
			$this->dompdf->reset();
			$this->dompdf->loadHtml($html);
			$this->dompdf->setPaper("A4");
			$this->dompdf->render();
				
			
			$output = $this->dompdf->output();
			$new_file = "uploads/summary/".$data['employee']->first_name." ".$data['employee']->special_id." ".$month_name." - Summary.pdf";
			file_put_contents($new_file, $output);
			// $this->dompdf->stream($data['employee']->first_name." ".$data['employee']->special_id." ".$month_name." - Summary", array("Attachment"=>0));
			
			$files[] = $new_file;

			$data = array();
			$dates = array();
			
			
		}



		$file_name = $c_name." (".$branch_name.") Employees Summary - ".$month_name." - ".time().".zip";


		foreach ($files as $file) {
			$this->zip->read_file(FCPATH .  $file);
			unlink($file);
		}

		$this->zip->archive(FCPATH . 'uploads/summary/' . $file_name);


		$path = base_url() . "uploads/summary/" . $file_name;

		redirect($path);

	}

	public function add_time($time1,$time2){
		if($time2 == null){
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
		if($hours == "00" && $minutes == "00"){
			return "";
		}
		return $hours.":".$minutes;
	}

	public function total_time($a , $b){
		if($a == null || $b == null){
			return "";
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

}