<?php
class Payroll extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		if(is_null(get_user())){
			redirect("welcome");
			 //var_dump($this->session->userdata('antelope_user'));
		}
	}

	function slip($employee_id, $year, $month){

		$employee = $this->db->select('special_id,p.title as position,d.name as department,special_id,c.address,c.name as company,c.phone,ic_passport,first_name as employee')->from('employees e')->join('companies c','e.company_id = c.id','left')->join('departments d','e.department_id = d.id','left')->join('positions p','e.position_id = p.id','left')->where('e.id',$employee_id)->get()->row_array();

		$data = $employee;
		$data["month"] = date("F", mktime(0, 0, 0, $month, 10));
		$data["year"] = $year;
		$data["today"] = date("d/m/Y");
		$eis = 0;
		$socso = 0;
		$epf = 0;
		$tax = 0;

		$eis_c = 0;
		$socso_c = 0;
		$epf_c = 0;

		$net_pay = 0;
		$basic_amount = 0;
		$total_allowance = 0;
		$total_deductions = 0;
		$gross_pay = 0;
		$allowances = array();
		$deductions = array();

		$payroll = $this->db->select('*')->from('payroll')->where('employee_id',$employee_id)->where('salary_date',$year.'-'.$month.'-01')->get()->row();
		if($payroll){
			$eis = $payroll->eis;
			$socso = $payroll->socso;
			$epf = $payroll->epf;
			$tax = $payroll->tax;

			$eis_c = $payroll->eis_c;
			$socso_c = $payroll->socso_c;
			$epf_c = $payroll->epf_c;

			$net_pay = $payroll->net_pay;
			$basic_amount = $payroll->basic_amount;
			$total_allowance = $payroll->total_allowance;
			$total_deductions = $payroll->total_deductions;
			$gross_pay = $basic_amount + $total_allowance;
			$allowances = json_decode($payroll->allowances);
			foreach ($allowances as $a) {
				$a->amount = number_format($a->amount,2,'.',',');
			}

			$temp = json_decode($payroll->deductions);
			$d = array();
			for($i = 4; $i < count($temp); $i++){
				$n = new stdClass();
				$n->name = $temp[$i]->name;
				if($temp[$i]->percentage == "false"){
					$n->amount = $temp[$i]->amount;
				}else{
					$n->amount = ($gross_pay * $temp[$i]->amount) / 100;
				}
				$d[] = $n;
			}
			foreach ($d as $dd) {
				$dd->amount = number_format($dd->amount,2,'.',',');
			}
			$deductions = $d;
		}

		$data["eis"] = number_format($eis,2,'.',',');
		$data["socso"] = number_format($socso,2,'.',',');
		$data["epf"] = number_format($epf,2,'.',',');
		$data["tax"] = number_format($tax,2,'.',',');
		$data["eis_c"] = number_format($eis_c,2,'.',',');
		$data["socso_c"] = number_format($socso_c,2,'.',',');
		$data["epf_c"] = number_format($epf_c,2,'.',',');
		$data["net_pay"] = number_format($net_pay,2,'.',',');
		$data["basic_amount"] = number_format($basic_amount,2,'.',',');
		$data["total_allowance"] = number_format($total_allowance,2,'.',',');
		$data["total_deductions"] = number_format($total_deductions,2,'.',',');
		$data["gross_pay"] = number_format($gross_pay,2,'.',',');
		$data["allowances"] = $allowances;
		$data["deductions"] = $deductions;
		$this->load->view('slip',$data);
		$html = $this->output->get_output();
		$this->load->library('pdf');
		$this->dompdf->loadHtml($html);
		$customPaper = array(0,0,596,420);
		$this->dompdf->setPaper($customPaper);
		$this->dompdf->render();
		$this->dompdf->stream($data["employee"]."_".$data["month"]."_".$data["year"], array("Attachment"=>0));
	}

	function calculator($employee_id = false, $year = false, $month = false){
		$data["employee_id"] = $employee_id;
		$data["year"] = $year;
		$data["month"] = $month;
		$data['pageTitle'] = "Payroll Calculator";
		$data['active_menu'] = "ot_days";
		$this->load->view('header',$data);
		$data["menus"] = get_menus();
		$this->load->view('sidebar',$data);

		$this->load->view('payroll',$data);
		$this->load->view('footer');

	}


	public function getData(){
		$month = date('m');
		$year = date('Y');

		$months = array();
		$years = array();
		for($i = 1; $i<= 12 ; $i++){
			$temp = new stdClass();
			$temp->id = sprintf("%02d", $i);
			$temp->name = date("F", mktime(0, 0, 0, $i, 10));
			$months[] = $temp;
		}
		for($i = $year; $i > $year-5 ; $i--){
			$temp = new stdClass();
			$temp->id = $i;
			$temp->name = $i;
			$years[] = $temp;
		}

		$cid = get_user()["company_id"];

		$bid = get_user()["branch_id"];
		$permissions_level = get_user()["permissions_level"];
		$where_filter = "";
		$where_branch_2 = '';

		if($permissions_level == "Outlet"){
			$where_branch_2 = " AND id = $bid ";   
			$where_filter . " branch_id = " . $bid . " AND " ;
		}

		$data["branches"] = $this->db->query("SELECT id,name FROM branches WHERE company_id = $cid  $where_branch_2 ORDER BY name")->result();
		$data["departments"] = $this->db->query("SELECT id,name FROM departments WHERE company_id = $cid ORDER BY name")->result();
		$where_filter = $where_filter . " company_id = " . $cid;

		$data["employees"] = $this->db->query("SELECT id, first_name as name FROM employees where $where_filter ORDER BY first_name")->result();
		$data["epf_m_table"] = $this->db->select('*')->from('epf_m')->get()->result();
		$data["epf_n_table"] = $this->db->select('*')->from('epf_n')->get()->result();
		$data["socso_table"] = $this->db->select('*')->from('socso')->get()->result();
		$data["eis_table"] = $this->db->select('*')->from('eis')->get()->result();

		$data["months"] = $months;
		$data["month"] = $month;
		$data["years"] = $years;
		$data["year"] = $year;

		echo json_encode($data);
	}

	public function filterEmployees(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$branch_id = $request->branch;
		$department_id = $request->department;

		$cid = get_user()["company_id"];

		$bid = get_user()["branch_id"];
		$permissions_level = get_user()["permissions_level"];
		$where_filter = "";
		$where_branch_2 = '';


		if(!empty($branch_id)){
			$where_filter = $where_filter . " branch_id = " . $branch_id . " AND " ;
		}else if($permissions_level == "Outlet"){
			$where_branch_2 = " AND id = $bid ";   
			$where_filter . " branch_id = " . $bid . " AND " ;
		}

		if(!empty($department_id)){
			$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
		}

		$where_filter = $where_filter . " company_id = " . $cid;

		$data["employees"] = $this->db->query("SELECT id, first_name as name FROM employees where $where_filter ORDER BY first_name")->result();

		echo json_encode($data);

	}

	public function getEmployee(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$employee_id = $request->employee_id;
		$month = $request->month;
		$year = $request->year;
		$payroll = array();
		$result = $this->db->select('p.*,first_name as name,employee_type')->from('payroll p')->join('employees e','e.id = p.employee_id')->where('employee_id',$employee_id)->where('salary_date',$year.'-'.$month.'-01')->get()->row();
		if($result){
			$payroll["employee_id"] = $employee_id;
			$payroll["employee_name"] = $result->name;
			$payroll["employee_type"] = $result->employee_type;
			$payroll["month"] = $month;
			$payroll["year"] = $year;
			$payroll["db"] = "true";
			// for confirm payroll
			if($result->confirm == 'Y'){
				$payroll["confirm"] = "true";
			}else{
				$payroll["confirm"] = "false";
			}
			$payroll["edit_mode"] = "false";
			$payroll["basic"] = $result->basic;
			$payroll["basic2"] = $result->basic;
			$payroll["salary_type"] = $result->salary_type;
			if($result->salary_type == "monthly"){
				$payroll["type2"] = "month(s)";
				$payroll["type3"] = "month";
			}else if($result->salary_type == "daily"){
				$payroll["type2"] = "day(s)";
				$payroll["type3"] = "day";
			}else if($result->salary_type == "hourly"){
				$payroll["type2"] = "hour(s)";
				$payroll["type3"] = "hour";
			}
			$payroll["unit"] = $result->unit;
			$payroll["basic_amount"] = $payroll["basic"] * $payroll["unit"];
			$payroll["allowances"] = json_decode($result->allowances);
			$payroll["deductions"] = json_decode($result->deductions);
			$payroll["epf_c"] = $result->epf_c;
			$payroll["socso_c"] = $result->socso_c;
			$payroll["eis_c"] = $result->eis_c;
			$payroll["tax"] = $result->tax;
			$payroll["tax_total"] = $result->tax_total;
		}else{
			$employee = $this->db->select('first_name as name,basic_wage,employee_type')->from('employees')->where('id', $employee_id)->get()->row();
			$payroll["employee_id"] = $employee_id;
			$payroll["employee_name"] = $employee->name;
			$payroll["employee_type"] = $employee->employee_type;
			$payroll["month"] = $month;
			$payroll["year"] = $year;
			$payroll["db"] = "false";
			// for confirm payroll
			$payroll["edit_mode"] = "true";
			$payroll["basic"] = $basic_wage = $employee->basic_wage;
			$payroll["basic2"] = $employee->basic_wage;
			$payroll["salary_type"] = "monthly";
			$payroll["type2"] = "month(s)";
			$payroll["type3"] = "month";
			$payroll["unit"] = 1;
			$payroll["tax"] = 0;
			$payroll["basic_amount"] = $payroll["basic"] * $payroll["unit"];
			$payroll["allowances"] = $this->db->select('allowance_name,amount,"true" as db,"false" as epf, "false" as eis, "false" as socso, "false" as tax, "test_template.html" as template')->from('allowances')->where('employee_id',$employee_id)->get()->result();

			$fixed_allowances = array();
			$fixed_allowances[] = $this->addNewAllowance("General Allowances");
			$fixed_allowances[] = $this->addNewAllowance("Meal allowance");
			$fixed_allowances[] = $this->addNewAllowance("Transport allowance");
			$fixed_allowances[] = $this->addNewAllowance("Phone allowance");
			$fixed_allowances[] = $this->addNewAllowance("Claims");
			$fixed_allowances[] = $this->addNewAllowance("Commission");
			$fixed_allowances[] = $this->addNewAllowance("Bonus");
			$fixed_allowances[] = $this->addNewAllowance("Overtime");

			$payroll["allowances"] = array_merge($payroll["allowances"],$fixed_allowances);


			
			$deductions = array();

			$temp = new stdClass();
			$temp->name = "EPF";
			$temp->percentage = "false";
			$temp->type = "sure";
			$temp->db = "true";
			$temp->is_apply = "false";
			$temp->show_apply = "true";
			$temp->amount = $this->db->select('epf_no')->from('employees')->where('id', $employee_id)->get()->row()->epf_no;

			$deductions[] = $temp;

			$temp = new stdClass();
			$temp->name = "SOCSO";
			$temp->percentage = "false";
			$temp->type = "sure";
			$temp->db = "true";
			$temp->is_apply = "false";
			$temp->show_apply = "true";
			$temp->amount = $this->db->select('socso')->from('employees')->where('id', $employee_id)->get()->row()->socso;

			$deductions[] = $temp;

			

			$temp = new stdClass();
			$temp->name = "EIS";
			$temp->percentage = "false";
			$temp->type = "sure";
			$temp->db = "true";
			$temp->is_apply = "false";
			$temp->show_apply = "true";
			$temp->amount = $this->db->select('eis')->from('employees')->where('id', $employee_id)->get()->row()->eis;

			$deductions[] = $temp;

			$temp = new stdClass();
			$temp->name = "Income tax";
			$temp->percentage = "false";
			$temp->type = "not_sure";
			$temp->db = "true";
			$temp->is_apply = "false";
			$temp->show_apply = "true";
			$temp->amount = 0;

			$deductions[] = $temp;

			$temp = new stdClass();
			$temp->name = "Zakat";
			$temp->percentage = "false";
			$temp->type = "not_sure";
			$temp->db = "true";
			$temp->is_apply = "true";
			$temp->show_apply = "false";
			$temp->amount = 0;

			$deductions[] = $temp;

			$temp = new stdClass();
			$temp->name = "CP38";
			$temp->percentage = "false";
			$temp->type = "not_sure";
			$temp->db = "true";
			$temp->is_apply = "true";
			$temp->show_apply = "false";
			$temp->amount = 0;

			$deductions[] = $temp;

			$payroll["deductions"] = $deductions;

			$epf_c = 0;
			$socso_c = 0;
			$eis_c = 0;
			$employee_type = $this->db->select('employee_type')->from('employees')->where('id',$employee_id)->get()->row()->employee_type;
			if($basic_wage != 0){
				$eis_row = $this->db->select('employer')->from('eis')->where('start <', $basic_wage)->where('end >=', $basic_wage)->get()->row();
				if($eis_row){
					$eis_c = $eis_row->employer;
				}

				$socso_row = $this->db->select('employer')->from('socso')->where('start <', $basic_wage)->where('end >=', $basic_wage)->get()->row();
				if($socso_row){
					$socso_c = $socso_row->employer;
				}

				$epf_row = $this->db->select('employer')->from('epf_'.$employee_type)->where('start <= ', $basic_wage)->where('end >= ',$basic_wage)->get()->row();
				if($epf_row){
					$epf_c = $epf_row->employer;
				}
			}

			$payroll["eis_c"] = $eis_c;
			$payroll["epf_c"] = $epf_c;
			$payroll["socso_c"] = $socso_c;

			
		}

		echo json_encode($payroll);
	}

	function addNewAllowance($name){
		$temp = new stdClass();
		$temp->allowance_name = $name;
		$temp->amount = 0;
		$temp->db = "true";
		$temp->epf = "false";
		$temp->eis = "false";
		$temp->socso = "false";
		$temp->tax = "false";
		$temp->template = "test_template.html";
		return $temp;
	}

	function save_data(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$employee_id = $request->employee_id;
		$salary_date = $request->year.'-'.$request->month.'-01';
		$salary_type = $request->salary_type;
		$tax_total = $request->tax_total;
		$unit = $request->unit;
		$basic = $request->basic;
		if($basic == ""){
			$basic = 0;
		}
		
		$basic_amount = $request->basic_amount;
		$allowances = $request->allowances;
		foreach ($allowances as $a) {
			$a->allowance_name = strip_tags($a->allowance_name);
			if($a->allowance_name == ""){
				$a->allowance_name = "Other";
			}
		}
		$allowances = json_encode($request->allowances);
		$deductions = $request->deductions;
		$total_allowance = $request->total_allowance;
		$total_deductions = $request->total_deductions;
		$epf = 0;
		$socso = 0;
		foreach ($deductions as $d) {
			$d->name = strip_tags($d->name);
			if($d->name == ""){
				$d->name = "Other";
			}

			if($d->amount == ""){
				$d->amount = 0;
			}

			if($d->name == "EPF" && $d->db == "true"){
				$epf = $d->amount;
			}else if($d->name == "SOCSO" && $d->db == "true"){
				$socso = $d->amount;
			}else if($d->name == "EIS" && $d->db == "true"){
				$eis = $d->amount;
			}else if($d->name == "Income tax" && $d->db == "true"){
				if($deductions[3]->is_apply == "false"){
					$tax = 0;
				}else if($d->percentage == "true"){
					$tax = $tax_total * $d->amount / 100;
				}else{
					$tax = $d->amount;
				}
			}
		}

		
		$deductions = json_encode($request->deductions);
		
		
		$net_pay = $request->net_pay;

		$epf_c = $request->epf_c;
		$socso_c = $request->socso_c;
		$eis_c = $request->eis_c;

		$data = array("employee_id" => $employee_id,
			"salary_date" => $salary_date,
			"salary_type" => $salary_type,
			"unit" => $unit,
			"basic" => $basic,
			"allowances" => $allowances,
			"deductions" => $deductions,
			"basic_amount" => $basic_amount,
			"total_allowance" => $total_allowance,
			"total_deductions" => $total_deductions,
			"tax_total" => $tax_total,
			"net_pay" => $net_pay,
			"epf" => $epf,
			"socso" => $socso,
			"eis" => $eis,
			"tax" => $tax,
			"epf_c" => $epf_c,
			"socso_c" => $socso_c,
			"eis_c" => $eis_c);
		$response["success"] = false;
		$response["msg"] = "Some error occurred";

		if($this->db->replace('payroll', $data)){
			$response["success"] = true;
			$response["msg"] = "Payroll changed successfully";
		}

		echo json_encode($response);

	}

	public function report(){
		$data['pageTitle'] = "Payroll Report";
		$data['active_menu'] = "ot_days";
		$this->load->view('header',$data);
		$data["menus"] = get_menus();
		$this->load->view('sidebar',$data);

		$this->load->view('payroll_report',$data);
		$this->load->view('footer');
	}

	public function getDataReport(){
		$month = date('m');
		$year = date('Y');

		$months = array();
		$years = array();
		for($i = 1; $i<= 12 ; $i++){
			$temp = new stdClass();
			$temp->id = sprintf("%02d", $i);
			$temp->name = date("F", mktime(0, 0, 0, $i, 10));
			$months[] = $temp;
		}
		for($i = $year; $i > $year-5 ; $i--){
			$temp = new stdClass();
			$temp->id = $i;
			$temp->name = $i;
			$years[] = $temp;
		}

		$cid = get_user()["company_id"];

		$bid = get_user()["branch_id"];
		$permissions_level = get_user()["permissions_level"];
		$where_branch_2 = '';

		if($permissions_level == "Outlet"){
			$where_branch_2 = " AND id = $bid ";
		}

		$data["branches"] = $this->db->query("SELECT id,name FROM branches WHERE company_id = $cid  $where_branch_2 ORDER BY name")->result();
		$data["departments"] = $this->db->query("SELECT id,name FROM departments WHERE company_id = $cid ORDER BY name")->result();

		$data["months"] = $months;
		$data["month"] = $month;
		$data["years"] = $years;
		$data["year"] = $year;

		echo json_encode($data);
	}


	public function getEmployeeReport(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$branch_id = $request->branch;
		$department_id = $request->department;
		$month = $request->month;
		$year = $request->year;

		$cid = get_user()["company_id"];

		$bid = get_user()["branch_id"];
		$permissions_level = get_user()["permissions_level"];
		$where_filter = "";


		if(!empty($branch_id)){
			$where_filter = $where_filter . " branch_id = " . $branch_id . " AND " ;
		}else if($permissions_level == "Outlet"){
			$where_filter . " branch_id = " . $bid . " AND " ;
		}

		if(!empty($department_id)){
			$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
		}

		$where_filter = $where_filter . " company_id = " . $cid;

		$data["employees"] = $this->db->query("SELECT p.*, first_name as name FROM payroll p left join employees e on p.employee_id = e.id where $where_filter AND salary_date = '".$year.'-'.$month.'-01'."' ORDER BY first_name")->result();
		$data["department"] = $department_id;
		$data["branch"] = $branch_id;
		$data["month"] = $month;
		$data["year"] = $year;

		echo json_encode($data);

	}

	public function confirm(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$id = $request->id;

		$this->db->set('confirm', 'Y');
		$this->db->where('id', $id);
		$this->db->update('payroll');
	}

	public function unconfirm(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$id = $request->id;

		$this->db->set('confirm', 'N');
		$this->db->where('id', $id);
		$this->db->update('payroll');
	}



}