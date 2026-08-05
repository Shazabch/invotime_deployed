<?php
class Ot_days extends CI_Controller {
	function __construct()
	{
		parent::__construct();
		// if(is_null(get_user())){
		// 	redirect("welcome");
		// 	 //var_dump($this->session->userdata('antelope_user'));
		// }
	}
	function index(){
		$data['pageTitle'] = "OT Chart";
		$data['active_menu'] = "ot_days";
		$this->load->view('header',$data);
		$data["menus"] = get_menus();
		$this->load->view('sidebar',$data);
		$data["selected_branch_id"] = 0;
		$data["selected_dep_id"] = 0;
		$data["selected_month"] = 0;
		$cid = get_user()["company_id"];
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
				redirect("ot_days?branch=$bid&month=".date('m'));
				return;
			}
		}
		$where_filter = "";
		if(!empty($this->input->get("branch"))){

			$data["selected_branch_id"] = $this->input->get("branch");

			$where_filter = $where_filter . " branch_id = " . $this->input->get("branch") . " AND " ;

		}



		if(!empty($this->input->get("dep"))){

			$data["selected_dep_id"] = $this->input->get("dep");

			$where_filter = $where_filter . " department_id = " . $this->input->get("dep") . " AND " ;

		}



		if(!empty($this->input->get("month"))){

			$month = $data["selected_month"] = $this->input->get("month");

		}

		else{

			redirect("ot_days?month=".date('m'));

			return;

		}

		$year = date('Y');

		$where_filter = $where_filter . " company_id = " . $cid;



		$total_records = $this->db->query("SELECT count(id) as total_records FROM employees where $where_filter")->row()->total_records;



		$limit = 20;

		$total_pages = ceil($total_records / $limit);

		$page = 1;

		if(!empty($this->input->get("page"))){

			$page = $this->input->get("page");

		}

		$skip = ($page -1) * $limit;

		$result = $this->db->query("SELECT id, special_id,first_name FROM employees where $where_filter ORDER BY first_name LIMIT $skip,$limit")->result();

		$max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year);



		$days = array();

		$public_holidays = get_public_holidays();

		for($i = 1; $i <= $max_date; $i++){

			$d["date"] = $i;

			$d["day"] = date('D', strtotime("$year-$month-$i"));

			$d["holiday"] = in_array(sprintf("%04d-%02d-%02d", $year, $month, $i), $public_holidays) ? true : false;

			$days[] = $d;

		}



		$employees = array();

		foreach ($result as $emp) {

			$temp["id"] = $emp->id;

			$temp["special_id"] = $emp->special_id;

			$temp["first_name"] = $emp->first_name;



			$days_wise = array();

			for($j = 1; $j <= $max_date; $j++){

				$temp1['day'] = $year."-".$month."-".sprintf("%02d",$j);

				$temp1['id'] = $emp->id;

				$temp1['is_ot'] = false;

				$check = $this->db->select('is_ot')->from('ot_days')->where('employee_id',$emp->id)->where('ot_date',$temp1['day'])->where('is_ot','Y')->get()->row();

				if($check){

					$temp1['is_ot'] = $check ? true : false;

				}

				$temp1['overtime'] = $this->count_overtime($emp->id,$temp1['day']);

				$days_wise[] = $temp1;

			}

			$temp["ot_data"] = $days_wise;

			$data["total_pages"] = $total_pages;

			$data["page"] = $page;

			unset($_GET['page']);

			$currentURL = current_url();

			$data["pagination_url"] = $currentURL . '?' . http_build_query($_GET); 

			$employees[] = $temp;

		}

		$data["days"] = $days;

		$data["employees"] = $employees;

		$data["branches"] = $this->db->query("SELECT * FROM branches WHERE company_id = $cid  $where_branch_2 ORDER BY name")->result();

		$data["departments"] = $this->db->query("SELECT id,name FROM departments WHERE company_id = $cid ORDER BY name")->result();



		$this->load->view('ot_days',$data);

		$this->load->view('footer');

	}



	function change_status(){

		$request = $this->input->post();

		$employee_id = $request['id'];

		$ot_date = $request['day'];

		$is_ot = ($request['is_ot'] == 1) ? 'Y' : 'N';



		$data = array('employee_id' => $employee_id,

			'ot_date' => $ot_date,

			'is_ot' => $is_ot);

		$this->db->replace('ot_days', $data);



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

		$overtime = (empty($overtime)) ? "-" : $overtime;

		return $overtime;

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