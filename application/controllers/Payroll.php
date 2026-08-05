<?php
class Payroll extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		if(!$this->session->userdata("payroll_user")){
			redirect("welcome");
		}
	}

	function sendAll(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$process_id = $request->process;
		$department_id = $request->department;

		

		$cid = $this->session->userdata("payroll_user")["company_id"];
		$bid = $this->session->userdata("payroll_user")["branch_id"];

		$where_filter = "";


		if(!empty($department_id)){
			$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
		}

		$where_filter = $where_filter . " e.company_id = " . $cid;

		$where_filter = $where_filter . " AND e.deleted_at is null AND employee_status = 'active'";



		

		$employees = $this->db->select('pr.id,email,special_id,p.title as position,d.name as department,special_id,c.address,c.name as company,c.phone,ic_passport,first_name as employee')->from('employees e')->join('companies c','e.company_id = c.id','left')->join('departments d','e.department_id = d.id','left')->join('positions p','e.position_id = p.id','left')->join('payroll pr', 'pr.employee_id = e.id')->where('process_id', $process_id)->where($where_filter)->get()->result_array();

		$payroll_settings = $this->db->select('date_format(period, "%M %Y") as period_formatted, type')->from('process_payrolls')->where('id', $process_id)->get()->row();

		$payroll_settings->payroll_type = ucwords(str_replace("_", " ", $payroll_settings->type));
		if($payroll_settings->type == "second_half") $payroll_settings->payroll_type = "Month End / Second Half";

		$payroll_name= $payroll_settings->period_formatted." - ".$payroll_settings->payroll_type;

		

		foreach ($employees as $employee) {
			$data = array();
			$data = $employee;
			$data["payroll_name"] = $payroll_name;
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

			$payroll = $this->db->select('*')->from('payroll')->where('id',$employee["id"])->get()->row();
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
			if(is_null($allowances)) $allowances = array();
			foreach ($allowances as $a) {
				$a->amount = number_format($a->amount,2,'.',',');
			}

			$temp = json_decode($payroll->deductions);
			if(is_null($temp)) $temp = array();
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
			$html = $this->load->view('slip',$data, true);
			$this->load->library('pdf');
			$this->dompdf->reset();
			$this->dompdf->loadHtml($html);
			$customPaper = array(0,0,596,420);
			$this->dompdf->setPaper($customPaper);
			$this->dompdf->render();
		// $this->dompdf->stream($data["employee"]."_".$data["month"]."_".$data["year"], array("Attachment"=>0));
			$output = $this->dompdf->output();
			file_put_contents("uploads/".$employee["employee"]."(".$data["payroll_name"].").pdf", $output);

			

			$link = base_url() . "uploads/".$employee["employee"]."(".$data["payroll_name"].").pdf";
			$subject = 'Pay Slip of '.$data["payroll_name"];
			$message_data = array(
				"name" => $employee["employee"],
				"payroll_name" => $payroll_name,
				"link" => $link
			);
			$message = $this->load->view('slip_template.php', $message_data, TRUE);

			$email_data = array("email" => $employee["email"],
				"message" => $message,
				"subject" => $subject);
			$this->db->insert('slip_emails', $email_data);
		}



		
	}

	function sendSlip(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$employee_id = $request->employee_id;
		$process_id = $request->process_id;

		$employee = $this->db->select('email,special_id,p.title as position,d.name as department,special_id,c.address,c.name as company,c.phone,ic_passport,first_name as employee')->from('employees e')->join('companies c','e.company_id = c.id','left')->join('departments d','e.department_id = d.id','left')->join('positions p','e.position_id = p.id','left')->where('e.id',$employee_id)->get()->row_array();

		$data = $employee;


		
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

		$payroll = $this->db->select('*')->from('payroll')->where('employee_id',$employee_id)->where('process_id',$process_id)->get()->row();

		$payroll_settings = $this->db->select('date_format(period, "%M %Y") as period_formatted, type')->from('process_payrolls')->where('id', $process_id)->get()->row();

		$payroll_settings->payroll_type = ucwords(str_replace("_", " ", $payroll_settings->type));
		if($payroll_settings->type == "second_half") $payroll_settings->payroll_type = "Month End / Second Half";

		$data["payroll_name"] = $payroll_settings->period_formatted." - ".$payroll_settings->payroll_type;


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
			if(is_null($allowances)) $allowances = array();
			foreach ($allowances as $a) {
				$a->amount = number_format($a->amount,2,'.',',');
			}

			$temp = json_decode($payroll->deductions);
			if(is_null($temp)) $temp = array();
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
		$html = $this->load->view('slip',$data, true);
		$this->load->library('pdf');
		$this->dompdf->loadHtml($html);
		$customPaper = array(0,0,596,420);
		$this->dompdf->setPaper($customPaper);
		$this->dompdf->render();
		// $this->dompdf->stream($data["employee"]."_".$data["month"]."_".$data["year"], array("Attachment"=>0));
		$output = $this->dompdf->output();
		file_put_contents("uploads/".$data["employee"]."(".$data["payroll_name"].").pdf", $output);

		$link = base_url() . "uploads/".$data["employee"]."(".$data["payroll_name"].").pdf";
		$subject = 'Pay Slip of '.$data["payroll_name"];
		$message_data = array(
			"name" => $data["employee"],
			"payroll_name" => $data["payroll_name"],
			"link" => $link
		);
		$message = $this->load->view('slip_template.php', $message_data, TRUE);

		$email_data = array("email" => $data["email"],
			"message" => $message,
			"subject" => $subject);
		$this->db->insert('slip_emails', $email_data);

		$slip["name"] = $data["employee"];



		echo json_encode($slip);

	}

	function slip($employee_id, $process_id){

		$employee = $this->db->select('special_id,p.title as position,d.name as department,special_id,c.address,c.name as company,c.phone,ic_passport,first_name as employee')->from('employees e')->join('companies c','e.company_id = c.id','left')->join('departments d','e.department_id = d.id','left')->join('positions p','e.position_id = p.id','left')->where('e.id',$employee_id)->get()->row_array();

		$data = $employee;
		
		$payroll_settings = $this->db->select('date_format(period, "%M %Y") as period_formatted, type')->from('process_payrolls')->where('id', $process_id)->get()->row();

		$payroll_settings->payroll_type = ucwords(str_replace("_", " ", $payroll_settings->type));
		if($payroll_settings->type == "second_half") $payroll_settings->payroll_type = "Month End / Second Half";

		$data["payroll_name"]= $payroll_settings->period_formatted." - ".$payroll_settings->payroll_type;


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

		$payroll = $this->db->select('*')->from('payroll')->where('employee_id',$employee_id)->where('process_id',$process_id)->get()->row();
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
			if(is_null($allowances)) $allowances = array();
			foreach ($allowances as $a) {
				$a->amount = number_format($a->amount,2,'.',',');
			}

			$temp = json_decode($payroll->deductions);
			if(is_null($temp)) $temp = array();
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
		$this->dompdf->stream($data["employee"]."_".$data["payroll_name"], array("Attachment"=>0));
	}

	function new_slip($employee_id, $process_id){
		$data = $this->db->select('special_id,d.name as department,special_id,c.address,c.name as company_name,c.phone,ic_passport,first_name as name, bank_account_no,e.epf_no,e.income_tax_no,e.socso as socso_no,company_registration_number')->from('employees e')->join('companies c','e.company_id = c.id','left')->join('departments d','e.department_id = d.id','left')->where('e.id',$employee_id)->get()->row_array();
		$payroll_settings = $this->db->select('period,date_format(period, "%M %Y") as period_formatted, date_format(period, "%Y") as year')->from('process_payrolls')->where('id', $process_id)->get()->row();

		$data["period"] = $payroll_settings->period_formatted;
		$year = $payroll_settings->year;
		$period = $payroll_settings->period;
		$payroll = $this->db->select('*')->from('payroll')->where('employee_id',$employee_id)->where('process_id',$process_id)->get()->row();
		$basic_amount = $payroll->basic_amount;
		$data["basic_salary"] = number_format($basic_amount, 2);
		$total_allowance = $payroll->total_allowance;

		$other_allowances = [];

		$allowances = json_decode($payroll->allowances);
		$overtime = 0;

		$overtimes = [];
		
		if(is_null($allowances)) $allowances = array();
		foreach ($allowances as $a) {
			// if name includes overtime
			if(strpos(strtolower($a->allowance_name), 'overtime') !== false && $a->db == "true"){
				$overtime += $a->amount;
				if($a->amount > 0){
					$a->amount = number_format($a->amount, 2);
					$a->value = number_format($a->value, 2);
					$a->multiplier = number_format($a->multiplier, 2);
					$overtimes[] = $a;
				}
			}else if($a->amount > 0){
				$other_allowances[] = $a;
			}
		}
		$total_allowance -= $overtime;
		$data["total_allowance"] = number_format($total_allowance, 2);
		$data["overtime"] = number_format($overtime, 2);
		$total_earnings = $basic_amount + $total_allowance + $overtime;
		$data["total_earnings"] = number_format($total_earnings, 2);

		$epf = $payroll->epf;
		$socso = $payroll->socso;
		$eis = $payroll->eis;

		$data["epf"] = ($epf > 0 ? "-" : "") . number_format($epf, 2);
		$data["socso"] = ($socso > 0 ? "-" : "") . number_format($socso, 2);
		$data["eis"] = ($eis > 0 ? "-" : "") . number_format($eis, 2);

		$fixed_deductions = [
			"epf",
			"socso",
			"eis"
		];

		$other_deductions = [];

		$deductions = json_decode($payroll->deductions);
		if(is_null($deductions)) $deductions = array();
		$deduction = 0;
		foreach($deductions as $d){
			if(!in_array(strtolower($d->name), $fixed_deductions)){
				$deduction += $d->amount;
				if($d->amount > 0){
					$other_deductions[] = $d;
				}
			}
		}
		

		$adjustments = json_decode($payroll->adjustments);
		if(is_null($adjustments)) $adjustments = array();

		$adjustment = 0;
		foreach($adjustments as $a){
			$adjustment += $a->amount;
			if($a->amount > 0){
				$other_deductions[] = $a;
			}
		}

		$deduction += $adjustment;

		$total_deduction = $deduction + $epf + $socso + $eis;

		$data["deduction"] = ($deduction > 0 ? "-" : "") . number_format($deduction, 2);
		$data["total_deduction"] = ($total_deduction > 0 ? "-" : "") . number_format($total_deduction, 2);

		foreach($other_allowances as $a){
			$a->amount = number_format($a->amount, 2);
		}
		foreach($other_deductions as $d){
			$d->amount = number_format($d->amount, 2);
		}
		$data["other_allowances"] = $other_allowances;
		$data["other_deductions"] = $other_deductions;

		$data["overtimes"] = $overtimes;
		$data["rate_hour"] = number_format($payroll->rate_hour, 2);

		$data["net_pay"] = number_format($total_earnings - $total_deduction, 2);

		$data["tax"] = number_format($payroll->tax, 2);
		$data["epf_e"] = number_format($payroll->epf, 2);
		$data["socso_e"] = number_format($payroll->socso, 2);
		$data["eis_e"] = number_format($payroll->eis, 2);
		$data["epf_c"] = number_format($payroll->epf_c, 2);
		$data["socso_c"] = number_format($payroll->socso_c, 2);
		$data["eis_c"] = number_format($payroll->eis_c, 2);

		$ytd_data = $this->db->select('sum(tax) as tax, sum(epf) as epf, sum(epf_c) as epf_c, sum(socso) as socso, sum(socso_c) as socso_c, sum(eis) as eis, sum(eis_c) as eis_c')
			->from('payroll p')->join('process_payrolls pp', 'p.process_id = pp.id')
			->where('p.employee_id', $employee_id)
			->where('pp.period >=', $year . "-01-01")
			->where('pp.period 	<=', $period)->get()->row();
		
		$data["tax_ytd"] = number_format($ytd_data->tax, 2);
		$data["epf_ytd"] = number_format($ytd_data->epf, 2);
		$data["socso_ytd"] = number_format($ytd_data->socso, 2);
		$data["eis_ytd"] = number_format($ytd_data->eis, 2);
		$data["epf_c_ytd"] = number_format($ytd_data->epf_c, 2);
		$data["socso_c_ytd"] = number_format($ytd_data->socso_c, 2);
		$data["eis_c_ytd"] = number_format($ytd_data->eis_c, 2);
		// print_r($data);die;
		// print_r($this->db->last_query());die;
		$this->load->view('new_slip', $data);
		$html = $this->output->get_output();
		$this->load->library('pdf');
		$this->dompdf->loadHtml($html);
		// $customPaper = array(0,0,596,420);
		$this->dompdf->setPaper('A3');
		$this->dompdf->render();
		$this->dompdf->stream("New Slip", array("Attachment"=>0));
	}

	function calculator($employee_id = false, $process_id = false){
		$data["employee_id"] = $employee_id;
		$data["process_id"] = $process_id;
		$data['pageTitle'] = "Payroll Calculator";

		$data["menus"] = get_menus_payroll();
		$data["active_menu"] = "payroll/calculator";
		
		$this->load->view('payroll',$data);

	}


	public function getData(){
		

		$cid = $this->session->userdata("payroll_user")["company_id"];
		$bid = $this->session->userdata("payroll_user")["branch_id"];

		$this->db->select('id, period as period_original, date_format(period, "%M %Y") as period, type, description')->from('process_payrolls')->where('company_id', $cid);

		if($this->session->userdata("payroll_user")["permissions_level"] != "Company"){
			$this->db->where('branch_id', $bid);
		}

		$payroll_processes = $this->db->order_by('period_original', 'desc')->get()->result();

		foreach ($payroll_processes as $p) {
			$p->payroll_type = ucwords(str_replace("_", " ", $p->type));
			if($p->type == "second_half") $p->payroll_type = "Month End / Second Half";
		}

		$data["payroll_processes"] = $payroll_processes;
		
		$data["departments"] = $this->db->query("SELECT id,name FROM departments WHERE company_id = $cid ORDER BY name")->result();

		
		$data["epf_m_table"] = $this->db->select('*')->from('epf_m')->get()->result();
		$data["epf_n_table"] = $this->db->select('*')->from('epf_n')->get()->result();
		$data["epf_c_table"] = $this->db->select('*')->from('epf_c')->get()->result();
		$data["epf_d_table"] = $this->db->select('*')->from('epf_d')->get()->result();
		$data["epf_e_table"] = $this->db->select('*')->from('epf_e')->get()->result();
		$data["socso_table"] = $this->db->select('*')->from('socso')->get()->result();
		$data["eis_table"] = $this->db->select('*')->from('eis')->get()->result();
		$data["epf_nine_table"] = $this->db->select('*')->from('epf_nine')->get()->result();
		$data["epf_settings"] = $this->db->select('*')->from('epf_settings')->get()->result();

		

		echo json_encode($data);
	}

	public function filterEmployees(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$process = $request->process;
		$department_id = $request->department;

		$cid = $this->session->userdata("payroll_user")["company_id"];
		$bid = $this->session->userdata("payroll_user")["branch_id"];
		
		$where_filter = "AND";

		if(!empty($department_id)){
			$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
		}

		$where_filter = $where_filter . " employees.company_id = " . $cid;

		$payroll_employees = $this->db->select('employees')->from('process_payrolls')->where('id', $process)->get()->row()->employees;

		$data["employees"] = $this->db->query("SELECT employees.id, concat(special_id, ' - ', first_name) as name FROM employees INNER JOIN roles ON employees.role_id = roles.id where employees.deleted_at IS NULL AND roles.exclude_from_system = 'no' $where_filter AND employees.id in ($payroll_employees) ORDER BY special_id")->result();

		echo json_encode($data);

	}

	public function check_committed(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$id = $request->id;
		$is_committed = "N";
		if($id != ""){
			$is_committed = $this->db->select('confirm')->from('payroll')->where('id', $id)->get()->row()->confirm;
		}
		$data["is_committed"] = $is_committed;
		echo json_encode($data);
	}

	public function getEmployee(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$process = $request->process;
		$employee_id = $request->employee_id;
		$department_id = $request->department_id;
		$reset_flag = $request->reset_flag;
		$cid = $this->session->userdata("payroll_user")["company_id"];
		$bid = $this->session->userdata("payroll_user")["branch_id"];
		// $permissions_level = get_user()["permissions_level"];
		$where_filter = "";

		$payroll_settings = $this->db->select('period, date_format(period, "%M %Y") as period_formatted, employees, type')->from('process_payrolls')->where('id', $process)->get()->row();

		$payroll_settings->payroll_type = ucwords(str_replace("_", " ", $payroll_settings->type));
		if($payroll_settings->type == "second_half") $payroll_settings->payroll_type = "Month End / Second Half";

		$payroll_employees = $payroll_settings->employees;

		$period = explode("-", $payroll_settings->period);
		$year = $period[0];
		$month = $period[1];


		if(!empty($department_id)){
			$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
		}

		$where_filter = $where_filter . " company_id = " . $cid;

		$where_filter = $where_filter . " AND deleted_at is null";

		$employees = $this->db->query("SELECT id FROM employees where $where_filter AND id in ($payroll_employees) ORDER BY special_id")->result_array();
		$emp = array_column($employees,"id");
		$total_employees_indexes = count($emp) - 1;
		$key = array_search($employee_id, $emp);
		$next = false;
		$previous = false;
		if($key != $total_employees_indexes){
			$next = $emp[$key + 1];
		}
		if($key != 0){
			$previous = $emp[$key - 1];
		}
		$payroll = array();
		$result = $this->db->select('p.*, first_name as name, dob, employee_type, permanent_resident, etc_on, etc_under')->from('payroll p')->join('employees e','e.id = p.employee_id')->where('employee_id',$employee_id)->where('process_id', $process)->get()->row();
		if($result && !$reset_flag){
			$payroll["id"] = $result->id;
			$payroll["period"] = $payroll_settings->period_formatted." - ".$payroll_settings->payroll_type;
			$payroll["process_id"] = $process;
			$payroll["employee_id"] = $employee_id;
			$payroll["employee_name"] = $result->name;
			$payroll["epf_category"] = $this->getEPFCategory($result->dob, $result->employee_type, $result->permanent_resident, $result->etc_on, $result->etc_under);
			$payroll["socso_secondary"] = $this->isSOCSOSecondary($result->dob, $result->employee_type);
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
			$payroll["eligible_amount"] = $result->eligible_amount;
			$payroll["daily"] = $result->daily;
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
			$payroll["basic_amount"] = $result->basic_amount;

			$db_allowances = json_decode($result->allowances);
			// change null amounts to 0
			foreach($db_allowances as $a){
				$a->amount = $a->amount == null ? 0 : $a->amount;
			}
			$payroll["allowances"] = $db_allowances;
			$payroll["deductions"] = json_decode($result->deductions);
			$payroll["adjustments"] = json_decode($result->adjustments);
			$payroll["earnings"] = json_decode($result->earnings);
			if($payroll["earnings"] == null){
				$payroll["earnings"] = array();
			}
			$payroll["extra_earnings"] = $result->extra_earnings;
			$payroll["epf_c"] = $result->epf_c;
			$payroll["socso_c"] = $result->socso_c;
			$payroll["eis_c"] = $result->eis_c;
			$payroll["tax"] = $result->tax;
			$payroll["tax_total"] = $result->tax_total;
			$payroll["month_days"] = $result->month_days;
			$payroll["rate_day"] = $result->rate_day;
			$payroll["rate_day_worked"] = $result->rate_day_worked;
			$payroll["rate_hour"] = $result->rate_hour;
			$payroll["rate_hour_late"] = $result->rate_hour_late;
			$payroll["epf_type"] = $result->epf_type;
			$payroll["late_count"] = $result->late_count;
		}else{
			$payroll["id"] = "";
			$employee = $this->db->select('e.id,first_name as name, dob, employee_type, permanent_resident, etc_on, etc_under, basic_wage, branch_id, is_ot, is_early_ot,inc_late_in,inc_late_break, inc_early_out, inc_short_hours,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,void_lateness_time_if_less_than,deduct_from_ot,deduct_from_ot_single,deduction_date')->from('employees e')->join('branches b', 'e.branch_id = b.id')->where('e.id', $employee_id)->get()->row();
			$payroll["process_id"] = $process;
			$payroll["period"] = $payroll_settings->period_formatted." - ".$payroll_settings->payroll_type;
			$payroll["employee_id"] = $employee_id;
			$payroll["employee_name"] = $employee->name;
			$payroll["epf_category"] = $this->getEPFCategory($employee->dob, $employee->employee_type, $employee->permanent_resident, $employee->etc_on, $employee->etc_under);
			$payroll["socso_secondary"] = $this->isSOCSOSecondary($employee->dob, $employee->employee_type);
			$payroll["month"] = $month;
			$payroll["year"] = $year;
			$payroll["db"] = "false";
			// for confirm payroll
			$payroll["edit_mode"] = "true";
			if($reset_flag){
				$payroll["db"] = "true";
				// In resetting for edit mode enabled (start)
				$payroll["resetting"] = "true";
				$payroll["edit_mode"] = "true";
				$payroll["confirm"] = "false";
				// In resetting for edit mode enabled (end)
			}
			$payroll["basic"] = $basic_wage = $employee->basic_wage;
			$payroll["eligible_amount"] = $employee->basic_wage;
			$payroll["daily"] = 0;
			$payroll["basic2"] = $employee->basic_wage;
			$payroll["tax_total"] = $employee->basic_wage;
			$payroll["salary_type"] = "monthly";
			$payroll["type2"] = "month(s)";
			$payroll["type3"] = "month";
			$payroll["unit"] = 1;
			$payroll["tax"] = 0;
			$payroll["basic_amount"] = $payroll["basic"] * $payroll["unit"];
			$payroll["allowances"] = $this->db->select('allowance_name,amount,"true" as db,"false" as epf, "false" as eis, "false" as socso, "false" as tax, "false" as eligible_salary, "test_template.html" as template')->from('allowances')->where('employee_id',$employee_id)->get()->result();
			$payroll["earnings"] = array();
			$payroll["extra_earnings"] = 0;

			$auto_count = get_auto_count_data($employee, $month, $year, $cid);
			$payroll["late_count"] = $auto_count->late_count;
			$payroll["rate_day"] = $rate_day = $auto_count->per_day;
			$payroll["rate_day_worked"] = $rate_day_worked = $auto_count->per_day_worked;
			$payroll["rate_hour"] = $rate_hour = $auto_count->per_hour;
			$payroll["rate_hour_late"] = $rate_hour_late = $auto_count->per_hour_late;
			$payroll["month_days"] = $auto_count->month_days;

			$payroll["epf_type"] = "eleven";

			$default_allowances_exist = $this->db->select('id')->from('company_allowances')->where('company_id', $cid)->where('is_default', 'Y')->get()->row();

			if(!$default_allowances_exist){
				$default_allowances_names = array("Overtime", "Overtime (RD)", "Overtime (PH)", "Worked (PH)", "Worked (RD)", "General Allowances", "Meal allowance", "Transport allowance", "Phone allowance", "Claims", "Commission", "Bonus");

				foreach($default_allowances_names as $name){
					$this->db->insert("company_allowances", array(
						"company_id" => $cid,
						"allowance_name" => $name,
						"pay_epf" => "N",
						"pay_socso" => "N",
						"pay_tax" => "N",
						"eligible_salary" => "N",
						"pay_eis" => "N",
						"is_default" => "Y"
					));
				}
			}

			$company_allowances = $this->db->select('*')->from('company_allowances')->where('company_id', $cid)->order_by('is_default', 'desc')->order_by('id', 'asc')->get()->result();

			

			$fixed_allowances = $this->makeFixedAllowances($company_allowances, $auto_count, $rate_hour, $rate_day_worked);
			

			$payroll["allowances"] = array_merge($fixed_allowances, $payroll["allowances"]);


			
			$deductions = array();

			$temp = new stdClass();
			$temp->name = "EPF";
			$temp->percentage = "false";
			$temp->type = "sure";
			$temp->db = "true";
			$temp->is_apply = "true";
			$temp->show_apply = "true";
			// $temp->amount = $this->db->select('epf_no')->from('employees')->where('id', $employee_id)->get()->row()->epf_no;
			$temp->amount = 0;

			if($employee->basic_wage == 0){
				$temp->epf_percentage = 0;
			}else{
				$temp->epf_percentage = ($temp->amount * 100) / $employee->basic_wage;
			}

			$deductions[] = $temp;

			

			$temp = new stdClass();
			$temp->name = "SOCSO";
			$temp->percentage = "false";
			$temp->type = "sure";
			$temp->db = "true";
			$temp->is_apply = "true";
			$temp->show_apply = "true";
			// $temp->amount = $this->db->select('socso')->from('employees')->where('id', $employee_id)->get()->row()->socso;
			$temp->amount = 0;

			$deductions[] = $temp;

			

			$temp = new stdClass();
			$temp->name = "EIS";
			$temp->percentage = "false";
			$temp->type = "sure";
			$temp->db = "true";
			$temp->is_apply = "true";
			$temp->show_apply = "true";
			// $temp->amount = $this->db->select('eis')->from('employees')->where('id', $employee_id)->get()->row()->eis;
			$temp->amount = 0;

			$deductions[] = $temp;

			$temp = new stdClass();
			$temp->name = "PCB";
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


			$temp = new stdClass();
			$temp->name = "Absent Days";
			$temp->percentage = "false";
			$temp->type = "sure";
			$temp->db = "false";
			$temp->is_apply = "true";
			$temp->show_apply = "false";
			$temp->remove = "false";
			$temp->fixed = "yes";
			$temp->amount = round($auto_count->absent_days * $rate_day , 2);
			$temp->description = $auto_count->absent_days."d";
			$temp->value = $auto_count->absent_days;
			$temp->type2 = "rate_day";
			$temp->show_settings = "true";
			$temp->epf = "false";
			$temp->socso = "false";
			$temp->eis = "false";
			$temp->template = "deduction_template.html";

			$deductions[] = $temp;

			$temp = new stdClass();
			$temp->name = "Unpaid Leaves";
			$temp->percentage = "false";
			$temp->type = "sure";
			$temp->db = "false";
			$temp->is_apply = "true";
			$temp->show_apply = "false";
			$temp->remove = "false";
			$temp->fixed = "yes";
			$temp->amount = round($auto_count->unpaid_leaves * $rate_day , 2);
			$temp->description = $auto_count->unpaid_leaves."d";
			$temp->value = $auto_count->unpaid_leaves;
			$temp->type2 = "rate_day";
			$temp->show_settings = "true";
			$temp->epf = "false";
			$temp->socso = "false";
			$temp->eis = "false";
			$temp->template = "deduction_template.html";

			$deductions[] = $temp;

			$temp = new stdClass();
			$temp->name = "Lateness Time";
			$temp->percentage = "false";
			$temp->type = "sure";
			$temp->db = "false";
			$temp->is_apply = "true";
			$temp->show_apply = "false";
			$temp->remove = "false";
			$temp->fixed = "yes";
			$temp->amount = round($auto_count->late * $rate_hour_late, 2);
			$temp->description = $auto_count->late."h";
			$temp->value = $auto_count->late;
			$temp->type2 = "rate_hour_late";
			$temp->show_settings = "true";
			$temp->epf = "false";
			$temp->socso = "false";
			$temp->eis = "false";
			$temp->template = "deduction_template.html";

			$deductions[] = $temp;

			$payroll["deductions"] = $deductions;

			$adjustments = array();

			$temp = new stdClass();
			$temp->name = "Loan";
			$temp->percentage = "false";
			$temp->type = "sure";
			$temp->db = "true";
			$temp->is_apply = "true";
			$temp->show_apply = "false";
			$temp->amount = 0;

			$adjustments[] = $temp;

			$temp = new stdClass();
			$temp->name = "Advance";
			$temp->percentage = "false";
			$temp->type = "sure";
			$temp->db = "true";
			$temp->is_apply = "true";
			$temp->show_apply = "false";
			$temp->amount = 0;

			$adjustments[] = $temp;

			$temp = new stdClass();
			$temp->name = "In lieu of notice";
			$temp->percentage = "false";
			$temp->type = "sure";
			$temp->db = "true";
			$temp->is_apply = "true";
			$temp->show_apply = "false";
			$temp->amount = 0;

			$adjustments[] = $temp;

			$payroll["adjustments"] = $adjustments;

			$epf_c = 0;
			$socso_c = 0;
			$eis_c = 0;
			
			if($basic_wage != 0){
				$eis_row = $this->db->select('employer')->from('eis')->where('start <', $basic_wage)->where('end >=', $basic_wage)->get()->row();
				if($eis_row){
					$eis_c = $eis_row->employer;
				}

				$socso_row = $this->db->select('employer')->from('socso')->where('start <', $basic_wage)->where('end >=', $basic_wage)->get()->row();
				if($socso_row){
					$socso_c = $socso_row->employer;
				}

				$epf_row = $this->db->select('employer')->from($payroll["epf_category"])->where('start <= ', $basic_wage)->where('end >= ',$basic_wage)->get()->row();
				if($epf_row){
					$epf_c = $epf_row->employer;
				}
			}

			// $payroll["eis_c"] = $eis_c;
			// $payroll["epf_c"] = $epf_c;
			// $payroll["socso_c"] = $socso_c;

			$payroll["eis_c"] = 0;
			$payroll["epf_c"] = 0;
			$payroll["socso_c"] = 0;
			

			
		}

		$payroll["next"] = $next;
		$payroll["previous"] = $previous;

		$payroll["carry"] = "true";

		echo json_encode($payroll);
	}

	function addNewAllowance($name, $allowance, $amount = 0, $description = '', $value = false, $multiplier = false, $type = false){
		$temp = new stdClass();
		$temp->allowance_name = $name;
		$temp->amount = round($amount, 2);
		$temp->description = $description;
		$temp->db = "true";
		$temp->epf = $allowance->pay_epf == "Y" ? "true" : "false";
		$temp->eis = $allowance->pay_eis == "Y" ? "true" : "false";
		$temp->socso = $allowance->pay_socso == "Y" ? "true" : "false";
		$temp->tax = $allowance->pay_tax == "Y" ? "true" : "false";
		$temp->eligible_salary = $allowance->eligible_salary == "Y" ? "true" : "false";
		$temp->template = "test_template.html";
		$temp->value = $value;
		$temp->multiplier = $multiplier;
		if($type){
			$temp->type2 = $type;
		}
		
		return $temp;
	}

	function save_data(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$employee_id = $request->employee_id;
		$employee_name = $this->db->select('first_name')->from('employees')->where('id', $employee_id)->get()->row()->first_name;
		$process_id = $request->process_id;
		
		
		$salary_type = $request->salary_type;
		$tax_total = $request->tax_total;
		$unit = $request->unit;
		$basic = $request->basic;
		$daily = $request->daily;
		$eligible_amount = $request->eligible_amount;
		$is_db = $request->db;
		$epf_type = $request->epf_type;
		if($basic == ""){
			$basic = 0;
		}
		if($daily == ""){
			$daily = 0;
		}
		
		$basic_amount = $request->basic_amount;
		$allowances = $request->allowances;
		foreach ($allowances as $a) {
			$a->allowance_name = strip_tags($a->allowance_name);
			if($a->allowance_name == ""){
				$a->allowance_name = "Other";
			}
			unset($a->new);
		}
		$adjustments = $request->adjustments;
		foreach ($adjustments as $ad) {
			$ad->name = strip_tags($ad->name);
			if($ad->name == ""){
				$ad->name = "Other";
			}
			if($ad->amount == ""){
				$ad->amount = 0;
			}
		}
		$adjustments = json_encode($adjustments);
		$earnings = json_encode($request->earnings);
		$extra_earnings = $request->extra_earnings;
		$allowances = json_encode($request->allowances);
		$deductions = $request->deductions;
		$total_adjustments = $request->total_adjustments;
		$total_allowance = $request->total_allowance;
		$total_deductions = $request->total_deductions;
		$gross_pay = $basic_amount + $total_allowance;
		$epf = 0;
		$socso = 0;
		$eis = 0;
		$tax = 0;
		$epf_c = 0;
		$socso_c = 0;
		$eis_c = 0;
		$late_count = $request->late_count;
		foreach ($deductions as $d) {
			$d->name = strip_tags($d->name);
			if($d->name == ""){
				$d->name = "Other";
			}

			if($d->amount == ""){
				$d->amount = 0;
			}

			if($d->name == "EPF" && $d->db == "true" && $d->is_apply == "true"){
				$epf = $d->amount;
				$epf_c = $request->epf_c;
			}else if($d->name == "SOCSO" && $d->db == "true" && $d->is_apply == "true"){
				$socso = $d->amount;
				$socso_c = $request->socso_c;
			}else if($d->name == "EIS" && $d->db == "true" && $d->is_apply == "true"){
				$eis = $d->amount;
				$eis_c = $request->eis_c;
			}else if($d->name == "PCB" && $d->db == "true" && $d->is_apply == "true"){
				if($deductions[3]->is_apply == "false"){
					$tax = 0;
				}else if($d->percentage == "true"){
					$tax = $tax_total * $d->amount / 100;
				}else{
					$tax = $d->amount;
				}
			}else if($d->name == "CP38" && $d->db = "true"){
				if($d->percentage == "true"){
					$cp38 = round($gross_pay * $d->amount / 100, 2);
				}else{
					$cp38 = $d->amount;
				}
			}
		}

		
		$deductions = json_encode($request->deductions);
		
		
		$net_pay = $request->net_pay;

		

		$data = array("employee_id" => $employee_id,
			"process_id" => $process_id,
			"salary_type" => $salary_type,
			"unit" => $unit,
			"basic" => $basic,
			"daily" => $daily,
			"eligible_amount" => $eligible_amount,
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
			"eis_c" => $eis_c,
			"cp38" => $cp38,
			"earnings" => $earnings,
			"extra_earnings" => $extra_earnings,
			"month_days" => $request->month_days,
			"rate_day" => $request->rate_day,
			"rate_hour" => $request->rate_hour,
			"rate_hour_late" => $request->rate_hour_late,
			"rate_day_worked" => $request->rate_day_worked,
			"epf_type" => $epf_type,
			"adjustments" => $adjustments,
			"total_adjustments" => $total_adjustments,
			"late_count" => $late_count);
		$response["success"] = false;
		$response["msg"] = "Some error occurred";

		if($this->db->replace('payroll', $data)){
			$response["success"] = true;
			if($is_db == 'false'){
				$response["msg"] = "Payroll saved successfully for ".$employee_name;
			}else{
				$response["msg"] = "Payroll updated successfully for ".$employee_name;
			}
			
		}

		echo json_encode($response);

	}

	public function report($process_id = false){
		$data["process_id"] = $process_id;
		$data['pageTitle'] = "Payroll Report";

		$data["menus"] = get_menus_payroll();
		$data["active_menu"] = "payroll/report";

		$this->load->view('payroll_report',$data);
	}

	public function full_report($process_id = false){
		$data["process_id"] = $process_id;
		$data['pageTitle'] = "Full Report";

		$data["menus"] = get_menus_payroll();
		$data["active_menu"] = "payroll/full_report";

		$data["action_link"] = "getFullReport";

		$this->load->view('report_page',$data);
	}

	public function payroll_customized_summaries($process_id = false){
		$data["process_id"] = $process_id;
		$data['pageTitle'] = "Payroll Summary Customization";

		$data['payroll_summary_types'] = $this->db->query("SELECT id,name FROM payroll_summaries")->result();
		$data["menus"] = get_menus_payroll();
		$data["active_menu"] = "payroll/payroll_customized_summaries";

		$this->load->view('customize_report_page',$data);
	}

	public function getSummaryColumns(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$report_id = $request->summary_type;

		$payroll_user = $this->session->userdata("payroll_user");

		$cid = $payroll_user["company_id"];

		$summary_columns = $this->db->select('c.id as column_id,v.id as value_id,column_name,value_name')
			->from('payroll_custom_columns c')
			->join('payroll_column_values v', 'c.id = v.custom_column_id and v.company_id = ' . $cid, 'left')
			->where('c.report_id', $report_id)
			->get()
			->result();

		$allowances = $this->db->select('allowance_name')->from('company_allowances')->where('company_id', $cid)->get()->result();
		$deductions = $this->db->select('deduction_name')->from('company_deductions')->where('company_id', $cid)->get()->result();

		$values = [];
		foreach($allowances as $a){
			$values[] = $a->allowance_name;
		}
		foreach($deductions as $d){
			$values[] = $d->deduction_name;
		}

		$data["summary_columns"] = $summary_columns;
		$data["values"] = $values;

		echo json_encode($data);
	}
	
	public function saveSummaryColumns(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
	
		$summary_columns = $request->summary_columns;
	
		$payroll_user = $this->session->userdata("payroll_user");
		$cid = $payroll_user["company_id"];
	
		foreach ($summary_columns as $column) {
			$value_id = $column->value_id; // Retrieve value_id
			$data = [
				'custom_column_id' => $column->column_id,
				'value_name'  => $column->value_name,
				'company_id' => $cid
			];
	
			if ($value_id) {
				// Check if value_id exists in the table
				$exists = $this->db->where('id', $value_id)->get('payroll_column_values');
	
				if ($exists) {
					// Update the existing record
					$this->db->where('id', $value_id)->update('payroll_column_values', $data);
				} else {
					// Insert a new record since value_id doesn't exist
					$this->db->insert('payroll_column_values', $data);
				}
			} else {
				// Insert a new record if value_id is null
				$this->db->insert('payroll_column_values', $data);
			}
		}
	
		echo json_encode(['success' => true, 'message' => 'Columns settings saved successfully.']);
	}
	

	public function payroll_summary_1($process_id = false){
		$data["process_id"] = $process_id;
		$data['pageTitle'] = "Payroll Summary 1";

		$data["menus"] = get_menus_payroll();
		$data["active_menu"] = "payroll/payroll_summary";

		$data["action_link"] = "getPayrollSummary1";

		$data["target"] = "_blank";

		$this->load->view('report_page',$data);
	}
	public function payroll_summary_2($process_id = false){
		$data["process_id"] = $process_id;
		$data['pageTitle'] = "Payroll Summary 2";

		$data["menus"] = get_menus_payroll();
		$data["active_menu"] = "payroll/payroll_summary";

		$data["action_link"] = "getPayrollSummary2";

		$data["target"] = "_blank";

		$this->load->view('report_page',$data);
	}
	public function payroll_summary_group($process_id = false){
		$data["process_id"] = $process_id;
		$data['pageTitle'] = "Payroll Summary Group";

		$data["menus"] = get_menus_payroll();
		$data["active_menu"] = "payroll/payroll_summary";

		$data["action_link"] = "getPayrollSummaryGroup";

		$data["target"] = "_blank";

		$this->load->view('report_page',$data);
	}

	public function summary_report($process_id = false){
		$data["process_id"] = $process_id;
		$data['pageTitle'] = "Payroll Summary Report";

		$data["menus"] = get_menus_payroll();
		$data["active_menu"] = "payroll/summary_report";

		$data["action_link"] = "getSummaryReport";

		$this->load->view('report_page',$data);
	}

	public function no_time_pay_off_report($process_id = false){
		$data["process_id"] = $process_id;
		$data['pageTitle'] = "No Time Pay Off Report";

		$data["menus"] = get_menus_payroll();
		$data["active_menu"] = "payroll/no_time_pay_off_report";

		$data["action_link"] = "getNoTimePayOffReport";

		$this->load->view('report_page',$data);
	}

	public function extra_earnings_report($process_id = false){
		$data["process_id"] = $process_id;
		$data['pageTitle'] = "Extra Earnings Report";

		$data["menus"] = get_menus_payroll();
		$data["active_menu"] = "payroll/extra_earnings_report";

		$data["action_link"] = "getExtraEarningsReport";

		$this->load->view('report_page',$data);
	}

	public function deductions_report($process_id = false){
		$data["process_id"] = $process_id;
		$data['pageTitle'] = "Deductions Report";

		$data["menus"] = get_menus_payroll();
		$data["active_menu"] = "payroll/deductions_report";

		$data["action_link"] = "getDeductionsReport";

		$this->load->view('report_page',$data);
	}

	public function adjustment_report($process_id = false){
		$data["process_id"] = $process_id;
		$data['pageTitle'] = "Adjustment Report";

		$data["menus"] = get_menus_payroll();
		$data["active_menu"] = "payroll/adjustment_report";

		$data["action_link"] = "getAdjustmentReport";

		$this->load->view('report_page',$data);
	}

	public function approval($process_id = false){
		$data["process_id"] = $process_id;
		$data['pageTitle'] = "Payroll Approval";

		$data["menus"] = get_menus_payroll();
		$data["active_menu"] = "payroll/approval";

		$this->load->view('payroll_approval',$data);
	}

	public function getPayrollProcessByBranch(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$branch_id = $request->branch_id;

		$payroll_user = $this->session->userdata("payroll_user");

		$cid = $payroll_user["company_id"];
		$bid = $payroll_user["branch_id"];

		$this->db->select('id, period as period_original, date_format(period, "%M %Y") as period, type, description')->from('process_payrolls')->where('company_id', $cid);

		if($branch_id){
			$this->db->where('branch_id', $branch_id);
		}else if($payroll_user["permissions_level"] != "Company"){
			$this->db->where('branch_id', $bid);
		}

		

		$payroll_processes = $this->db->order_by('period_original', 'desc')->get()->result();

		foreach ($payroll_processes as $p) {
			$p->payroll_type = ucwords(str_replace("_", " ", $p->type));
			if($p->type == "second_half") $p->payroll_type = "Month End / Second Half";
		}

		$data["payroll_processes"] = $payroll_processes;

		echo json_encode($data);
	}

	public function getDataReport(){
		

		$cid = $this->session->userdata("payroll_user")["company_id"];
		$bid = $this->session->userdata("payroll_user")["branch_id"];

		$this->db->select('id, period as period_original, date_format(period, "%M %Y") as period, type, description')->from('process_payrolls')->where('company_id', $cid);

		if($this->session->userdata("payroll_user")["permissions_level"] != "Company"){
			$this->db->where('branch_id', $bid);
		}

		$payroll_processes = $this->db->order_by('period_original', 'desc')->get()->result();

		foreach ($payroll_processes as $p) {
			$p->payroll_type = ucwords(str_replace("_", " ", $p->type));
			if($p->type == "second_half") $p->payroll_type = "Month End / Second Half";
		}

		$data["payroll_processes"] = $payroll_processes;

		$this->db->select('id, name')->from('branches')->where('company_id', $cid);

		if($this->session->userdata("payroll_user")["permissions_level"] != "Company"){
			$this->db->where('id', $bid);
		}

		$data["branches"] = $this->db->get()->result();
		
		$data["departments"] = $this->db->query("SELECT id,name FROM departments WHERE company_id = $cid ORDER BY name")->result();

		echo json_encode($data);
	}
	public function getdataCustomizedReport(){
		$data['payroll_summary_types'] = $this->db->query("SELECT id,name FROM payroll_summaries")->result();
		
		echo json_encode($data);
	}

	public function confirmAll(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$process_id = $request->process;
		$department_id = $request->department;

		$cid = $this->session->userdata("payroll_user")["company_id"];
		$bid = $this->session->userdata("payroll_user")["branch_id"];

		$where_filter = "";


		

		if(!empty($department_id)){
			$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
		}

		$where_filter = $where_filter . " company_id = " . $cid;
		$where_filter = $where_filter . " AND e.deleted_at is null AND employee_status = 'active'";



		$this->db->query("Update payroll p left join employees e on p.employee_id = e.id set confirm = 'Y' where $where_filter AND process_id = $process_id");
	}

	public function approveAll(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$process_id = $request->process;
		$department_id = $request->department;

		$cid = $this->session->userdata("payroll_user")["company_id"];
		$bid = $this->session->userdata("payroll_user")["branch_id"];

		$where_filter = "";


		

		if(!empty($department_id)){
			$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
		}

		$where_filter = $where_filter . " company_id = " . $cid;
		$where_filter = $where_filter . " AND e.deleted_at is null";



		$this->db->query("Update payroll p left join employees e on p.employee_id = e.id set approved = 'Y' where $where_filter AND process_id = $process_id AND confirm = 'Y'");
	}

	public function getFullReport(){

		$process_id = $_POST["process"];
		$department_id = $_POST["department"];
		$branch_id = $_POST["branch"];

		$cid = $this->session->userdata("payroll_user")["company_id"];
		$bid = $this->session->userdata("payroll_user")["branch_id"];

		$where_filter = "";

		if(!empty($department_id)){
			$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
		}

		if(!empty($branch_id)){
			$where_filter = $where_filter . " branch_id = " . $branch_id . " AND " ;
		}

		$where_filter = $where_filter . " company_id = " . $cid;

		$where_filter = $where_filter . " AND e.deleted_at is null";

		$payroll_settings = $this->db->select('date_format(period, "%M %Y") as period_formatted, type, employees, description')->from('process_payrolls')->where('id', $process_id)->get()->row();

		$payroll_settings->payroll_type = ucwords(str_replace("_", " ", $payroll_settings->type));
		if($payroll_settings->type == "second_half") $payroll_settings->payroll_type = "Month End / Second Half";

		$payroll_name = $payroll_settings->period_formatted." - ".$payroll_settings->payroll_type." | ".$payroll_settings->description;



		$employees = $this->db->query("SELECT p.*, e.id as employee_id, $process_id as process_id, first_name as name, employee_status as status, basic_wage, date_format(hired_on, '%d/%m/%Y') as hired_on, termination_reason, date_format(termination_date, '%d/%m/%Y') as termination_date, resignation_reason, date_format(resignation_date, '%d/%m/%Y') as resignation_date FROM payroll p right join employees e on p.employee_id = e.id and process_id = $process_id where $where_filter AND e.id in ($payroll_settings->employees) ORDER BY special_id")->result();

		$active_employees = array();
		$other_employees = array();
		foreach($employees as $emp){
			if($emp->status == "active"){
				$active_employees[] = $emp;
			}else{
				$other_employees[] = $emp;
			}
		}
		$this->load->library("excel");

		$style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			)
		);

		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);

		$right_align = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
			)
		);

		$yellow_backgroud = array(
	        'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => 'FFFFCC')
	        ),
	        'borders' => array(
	            'allborders' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => 'DDDDDD')
	            )
	        )
	    );

	    $orange_background = array(
	        'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => 'FFCC00')
	        ),
	        'borders' => array(
	            'allborders' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => 'DDDDDD')
	            )
	        )
	    );

		$table_columns = array("NO", "NAME", "DATE JOINED", "BASIC + ALLOWANCE + INCENTIVE", "SALARY OFFERED", "BASIC SALARY FOR THE MONTH", "UPL", "NO PAY TIME OFF (UNPAID)", "NET BASIC", "AL", "PH" => array("DAY", "RM"), "PH OT" => array("HR", "RM"), "OT" => array("HR", "RM"), "PERFORMANCE ALLOWANCE", "GROSS SALARY", "EPF" => array("Yee", "Yer"), "SOCSO" => array("Yee", "Yer"), "EIS" => array("Yee", "Yer"), "ADV", "BALANCE SALARY PAID AS PER PAY SLIP", "IN LIEU OF NOTICE", "BALANCE SALARY BANK", "REMARK", "DATE RESIGN");

		$column = 0;
		$row = 4;

		foreach($table_columns as $key => $field)
		{
			if(is_array($field)){
				$object->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $key);
				$object->getActiveSheet()->mergeCellsByColumnAndRow($column, $row, $column + 1, $row);
				foreach($field as $subfield){
					$object->getActiveSheet()->setCellValueByColumnAndRow($column++, $row + 1, $subfield);
				}
			}else{
				$object->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $field);
				$object->getActiveSheet()->mergeCellsByColumnAndRow($column, $row, $column, $row + 1);
				if($field == "BASIC + ALLOWANCE + INCENTIVE"){
					$object->getActiveSheet()->getStyleByColumnAndRow($column, $row)->applyFromArray($yellow_backgroud);
					$object->getActiveSheet()->getStyleByColumnAndRow($column, $row + 1)->applyFromArray($yellow_backgroud);
				}
				$column++;
			}
			

			
		}

		$current_total = $resigned_total = array(
			"basic_plus_allowance" => 0,
			"basic_wage" => 0,
			"eligible_amount" => 0,
			"unpaid_leaves" => 0,
			"lateness_time" => 0,
			"net_basic" => 0,
			"al" => "-",
			"worked_ph" => 0,
			"worked_ph_amount" => 0,
			"overtime_ph" => 0,
			"overtime_ph_amount" => 0,
			"overtime" => 0,
			"overtime_amount" => 0,
			"performance_allowance" => 0,
			"gross_salary" => 0,
			"epf" => 0,
			"epf_c" => 0,
			"socso" => 0,
			"socso_c" => 0,
			"eis" => 0,
			"eis_c" => 0,
			"advance" => 0,
			"salary_paid"=> 0,
			"notice" => 0,
			"balance_salary" => 0
		);
		$row = $row + 2;
		$count = 1;
		foreach ($active_employees as $emp) {
			$total_allowances = 0;
			$total_deductions = 0;
			$total_adjustments = 0;
			$unpaid_leaves = 0;
			$lateness_time = 0;				
			$deductions = json_decode($emp->deductions);
			if(is_null($deductions)) $deductions = array();
			$no_time_pay_off = 0;
			foreach($deductions as $d){
				if($d->name == "Unpaid Leaves" && $d->fixed == "yes"){
					$unpaid_leaves = $d->amount;
				}else if($d->name == "Lateness Time" && $d->fixed == "yes"){
					$lateness_time = $d->amount;
				}
				if(isset($d->fixed) && $d->fixed == "yes"){
					$no_time_pay_off += $d->amount;
				}
				$total_deductions += $d->amount;
			}
			$advance = 0;
			$notice = 0;
			$adjustments = json_decode($emp->adjustments);
			if(is_null($adjustments)) $adjustments = array();
			foreach($adjustments as $a){
				if($a->name == "Advance" && $a->db){
					$advance = $a->amount;
				}else if($a->name == "In lieu of notice" && $a->db){
					$notice = $a->amount;
				}
				$total_adjustments += $a->amount;
			}
			$worked_ph = 0;
			$worked_ph_amount = 0;
			$overtime_ph = 0;
			$overtime_ph_amount = 0;
			$overtime = 0;
			$overtime_amount = 0;
			$performance_allowance = 0;
			$allowances = json_decode($emp->allowances);
			if(is_null($allowances)) $allowances = array();
			foreach($allowances as $a){
				if($a->allowance_name == "Worked (PH)" && $a->db){
					$worked_ph = $a->value == 0 ? "" : $a->value;
					$worked_ph_amount = $a->amount == 0 ? "" : $a->amount;
				}else if($a->allowance_name == "Overtime (PH)" && $a->db){
					$overtime_ph = $a->value == 0 ? "" : $a->value;
					$overtime_ph_amount = $a->amount == 0 ? "" : $a->amount;
				}else if($a->allowance_name == "Overtime" && $a->db){
					$overtime = $a->value == 0 ? "" : $a->value;
					$overtime_amount = $a->amount == 0 ? "" : $a->amount;
				}else if($a->allowance_name == "Performance Allowance"){
					$performance_allowance = $a->amount == 0 ? "" : $a->amount;
				}
				$total_allowances += $a->amount;
			}
			$basic_plus_allowance = $emp->eligible_amount + $performance_allowance;
			$net_basic = $emp->eligible_amount - $no_time_pay_off;
			$gross_salary = $emp->eligible_amount - $no_time_pay_off + $total_allowances;
			$salary_paid = $emp->eligible_amount - $total_deductions + $total_allowances - $total_adjustments;
			$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $count++);
			$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $emp->name);
			$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $emp->hired_on);
			$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $this->coma_format($basic_plus_allowance));// BASIC + ALLOWANCE + INCENTIVE
			$current_total["basic_plus_allowance"] += $basic_plus_allowance;
			$object->getActiveSheet()->getStyleByColumnAndRow(3, $row)->applyFromArray($yellow_backgroud);
			$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $this->coma_format($emp->basic_wage));
			$current_total["basic_wage"] += $emp->basic_wage;
			$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $this->coma_format($emp->eligible_amount));
			$current_total["eligible_amount"] += $emp->eligible_amount;
			$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $this->coma_format($unpaid_leaves));
			$current_total["unpaid_leaves"] += $unpaid_leaves;
			$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $this->coma_format($lateness_time));
			$current_total["lateness_time"] += $lateness_time;
			$object->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $this->coma_format($net_basic));// Net basic
			$current_total["net_basic"] += $net_basic;
			$object->getActiveSheet()->setCellValueByColumnAndRow(9, $row, ''); // AL
			$object->getActiveSheet()->setCellValueByColumnAndRow(10, $row, $this->coma_format($worked_ph));// PH Day
			$current_total["worked_ph"] += $worked_ph;
			$object->getActiveSheet()->setCellValueByColumnAndRow(11, $row, $this->coma_format($worked_ph_amount));// PH RM
			$current_total["worked_ph_amount"] += $worked_ph_amount;
			$object->getActiveSheet()->setCellValueByColumnAndRow(12, $row, $this->coma_format($overtime_ph));// PH OT hours
			$current_total["overtime_ph"] += $overtime_ph;
			$object->getActiveSheet()->setCellValueByColumnAndRow(13, $row, $this->coma_format($overtime_ph_amount));// PH OT RM
			$current_total["overtime_ph_amount"] += $overtime_ph_amount;
			$object->getActiveSheet()->setCellValueByColumnAndRow(14, $row, $this->coma_format($overtime));// OT HR
			$current_total["overtime"] += $overtime;
			$object->getActiveSheet()->setCellValueByColumnAndRow(15, $row, $this->coma_format($overtime_amount));// OT RM
			$current_total["overtime_amount"] += $overtime_amount;
			$object->getActiveSheet()->setCellValueByColumnAndRow(16, $row, $this->coma_format($performance_allowance));// Allowances
			$current_total["performance_allowance"] += $performance_allowance;
			$object->getActiveSheet()->setCellValueByColumnAndRow(17, $row, $this->coma_format($gross_salary));// Gross salary
			$current_total["gross_salary"] += $gross_salary;
			$object->getActiveSheet()->setCellValueByColumnAndRow(18, $row, $this->coma_format($emp->epf));
			$current_total["epf"] += $emp->epf;
			$object->getActiveSheet()->setCellValueByColumnAndRow(19, $row, $this->coma_format($emp->epf_c));
			$current_total["epf_c"] += $emp->epf_c;
			$object->getActiveSheet()->setCellValueByColumnAndRow(20, $row, $this->coma_format($emp->socso));
			$current_total["socso"] += $emp->socso;
			$object->getActiveSheet()->setCellValueByColumnAndRow(21, $row, $this->coma_format($emp->socso_c));
			$current_total["socso_c"] += $emp->socso_c;
			$object->getActiveSheet()->setCellValueByColumnAndRow(22, $row, $this->coma_format($emp->eis));
			$current_total["eis"] += $emp->eis;
			$object->getActiveSheet()->setCellValueByColumnAndRow(23, $row, $this->coma_format($emp->eis_c));
			$current_total["eis_c"] += $emp->eis_c;
			$object->getActiveSheet()->setCellValueByColumnAndRow(24, $row, $this->coma_format($advance));
			$current_total["advance"] += $advance;
			$object->getActiveSheet()->setCellValueByColumnAndRow(25, $row, $this->coma_format($salary_paid));// salary paid
			$current_total["salary_paid"] += $salary_paid;
			$object->getActiveSheet()->setCellValueByColumnAndRow(26, $row, $this->coma_format($notice));
			$current_total["notice"] += $notice;
			$object->getActiveSheet()->setCellValueByColumnAndRow(27, $row, $this->coma_format($salary_paid));// balance salary bank
			$current_total["balance_salary"] += $salary_paid;
			// remark
			// date resign
			$row++;
		}

		$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, "SUB TOTAL");
		$object->getActiveSheet()->getStyleByColumnAndRow(1, $row)->getFont()->setBold(true);
		$object->getActiveSheet()->getStyleByColumnAndRow(1, $row)->applyFromArray($right_align);
		$total_column = 3;
		foreach($current_total as $total){
			$object->getActiveSheet()->setCellValueByColumnAndRow($total_column, $row, $this->format_total($total));
			$object->getActiveSheet()->getStyleByColumnAndRow($total_column, $row)->getFont()->setBold(true);
			$total_column++;
		}
		$object->getActiveSheet()->getStyle("A".$row.":AD".$row)->applyFromArray($yellow_backgroud);
		$row++;
		if($other_employees){
			foreach ($other_employees as $emp) {
				$total_allowances = 0;
				$total_deductions = 0;
				$total_adjustments = 0;
				$unpaid_leaves = 0;
				$lateness_time = 0;				
				$deductions = json_decode($emp->deductions);
				if(is_null($deductions)) $deductions = array();
				$no_time_pay_off = 0;
				foreach($deductions as $d){
					if($d->name == "Unpaid Leaves" && $d->fixed == "yes"){
						$unpaid_leaves = $d->amount;
					}else if($d->name == "Lateness Time" && $d->fixed == "yes"){
						$lateness_time = $d->amount;
					}
					if(isset($d->fixed) && $d->fixed == "yes"){
						$no_time_pay_off += $d->amount;
					}
					$total_deductions += $d->amount;
				}
				$advance = 0;
				$notice = 0;
				$adjustments = json_decode($emp->adjustments);
				if(is_null($adjustments)) $adjustments = array();
				foreach($adjustments as $a){
					if($a->name == "Advance" && $a->db){
						$advance = $a->amount;
					}else if($a->name == "In lieu of notice" && $a->db){
						$notice = $a->amount;
					}
					$total_adjustments += $a->amount;
				}
				$worked_ph = 0;
				$worked_ph_amount = 0;
				$overtime_ph = 0;
				$overtime_ph_amount = 0;
				$overtime = 0;
				$overtime_amount = 0;
				$performance_allowance = 0;
				$allowances = json_decode($emp->allowances);
				if(is_null($allowances)) $allowances = array();
				foreach($allowances as $a){
					if($a->allowance_name == "Worked (PH)" && $a->db){
						$worked_ph = $a->value == 0 ? "" : $a->amount;
						$worked_ph_amount = $a->amount == 0 ? "" : $a->amount;
					}else if($a->allowance_name == "Overtime (PH)" && $a->db){
						$overtime_ph = $a->value == 0 ? "" : $a->amount;
						$overtime_ph_amount = $a->amount == 0 ? "" : $a->amount;
					}else if($a->allowance_name == "Overtime" && $a->db){
						$overtime = $a->value == 0 ? "" : $a->amount;
						$overtime_amount = $a->amount == 0 ? "" : $a->amount;
					}else if($a->allowance_name == "Performance Allowance"){
						$performance_allowance = $a->amount == 0 ? "" : $a->amount;
					}
					$total_allowances += $a->amount;
				}
				$basic_plus_allowance = $emp->eligible_amount + $performance_allowance;
				$net_basic = $emp->eligible_amount - $no_time_pay_off;
				$gross_salary = $emp->eligible_amount - $no_time_pay_off + $total_allowances;
				$salary_paid = $emp->eligible_amount - $total_deductions + $total_allowances - $total_adjustments;
				$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $count++);
				$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $emp->name);
				$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $emp->hired_on);
				$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $this->coma_format($basic_plus_allowance));// BASIC + ALLOWANCE + INCENTIVE
				$resigned_total["basic_plus_allowance"] += $basic_plus_allowance;
				$object->getActiveSheet()->getStyleByColumnAndRow(3, $row)->applyFromArray($yellow_backgroud);
				$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $this->coma_format($emp->basic_wage));
				$resigned_total["basic_wage"] += $emp->basic_wage;
				$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $this->coma_format($emp->eligible_amount));
				$resigned_total["eligible_amount"] += $emp->eligible_amount;
				$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $this->coma_format($unpaid_leaves));
				$resigned_total["unpaid_leaves"] += $unpaid_leaves;
				$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $this->coma_format($lateness_time));
				$resigned_total["lateness_time"] += $lateness_time;
				$object->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $this->coma_format($net_basic));// Net basic
				$resigned_total["net_basic"] += $net_basic;
				$object->getActiveSheet()->setCellValueByColumnAndRow(9, $row, ''); // AL
				$object->getActiveSheet()->setCellValueByColumnAndRow(10, $row, $this->coma_format($worked_ph));// PH Day
				$resigned_total["worked_ph"] += $worked_ph;
				$object->getActiveSheet()->setCellValueByColumnAndRow(11, $row, $this->coma_format($worked_ph_amount));// PH RM
				$resigned_total["worked_ph_amount"] += $worked_ph_amount;
				$object->getActiveSheet()->setCellValueByColumnAndRow(12, $row, $this->coma_format($overtime_ph));// PH OT hours
				$resigned_total["overtime_ph"] += $overtime_ph;
				$object->getActiveSheet()->setCellValueByColumnAndRow(13, $row, $this->coma_format($overtime_ph_amount));// PH OT RM
				$resigned_total["overtime_ph_amount"] += $overtime_ph_amount;
				$object->getActiveSheet()->setCellValueByColumnAndRow(14, $row, $this->coma_format($overtime));// OT HR
				$resigned_total["overtime"] += $overtime;
				$object->getActiveSheet()->setCellValueByColumnAndRow(15, $row, $this->coma_format($overtime_amount));// OT RM
				$resigned_total["overtime_amount"] += $overtime_amount;
				$object->getActiveSheet()->setCellValueByColumnAndRow(16, $row, $this->coma_format($performance_allowance));// Allowances
				$resigned_total["performance_allowance"] += $performance_allowance;
				$object->getActiveSheet()->setCellValueByColumnAndRow(17, $row, $this->coma_format($gross_salary));// Gross salary
				$resigned_total["gross_salary"] += $gross_salary;
				$object->getActiveSheet()->setCellValueByColumnAndRow(18, $row, $this->coma_format($emp->epf));
				$resigned_total["epf"] += $emp->epf;
				$object->getActiveSheet()->setCellValueByColumnAndRow(19, $row, $this->coma_format($emp->epf_c));
				$resigned_total["epf_c"] += $emp->epf_c;
				$object->getActiveSheet()->setCellValueByColumnAndRow(20, $row, $this->coma_format($emp->socso));
				$resigned_total["socso"] += $emp->socso;
				$object->getActiveSheet()->setCellValueByColumnAndRow(21, $row, $this->coma_format($emp->socso_c));
				$resigned_total["socso_c"] += $emp->socso_c;
				$object->getActiveSheet()->setCellValueByColumnAndRow(22, $row, $this->coma_format($emp->eis));
				$resigned_total["eis"] += $emp->eis;
				$object->getActiveSheet()->setCellValueByColumnAndRow(23, $row, $this->coma_format($emp->eis_c));
				$resigned_total["eis_c"] += $emp->eis_c;
				$object->getActiveSheet()->setCellValueByColumnAndRow(24, $row, $this->coma_format($advance));
				$resigned_total["advance"] += $advance;
				$object->getActiveSheet()->setCellValueByColumnAndRow(25, $row, $this->coma_format($salary_paid));// salary paid
				$resigned_total["salary_paid"] += $salary_paid;
				$object->getActiveSheet()->setCellValueByColumnAndRow(26, $row, $this->coma_format($notice));
				$resigned_total["notice"] += $notice;
				$object->getActiveSheet()->setCellValueByColumnAndRow(27, $row, $this->coma_format($salary_paid));// balance salary bank
				$resigned_total["balance_salary"] += $salary_paid;
				if($emp->status == "terminated"){
					$remark = $emp->termination_reason;
					$date = $emp->termination_date;
				}else{
					$remark = $emp->resignation_reason;
					$date = $emp->resignation_date;
				}
				$object->getActiveSheet()->setCellValueByColumnAndRow(28, $row, $remark);
				$object->getActiveSheet()->setCellValueByColumnAndRow(29, $row, $date);
				$row++;
			}

			$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, "SUB TOTAL");
			$object->getActiveSheet()->getStyleByColumnAndRow(1, $row)->getFont()->setBold(true);
			$object->getActiveSheet()->getStyleByColumnAndRow(1, $row)->applyFromArray($right_align);
			$total_column = 3;
			foreach($resigned_total as $total){
				$object->getActiveSheet()->setCellValueByColumnAndRow($total_column, $row, $this->format_total($total));
				$object->getActiveSheet()->getStyleByColumnAndRow($total_column, $row)->getFont()->setBold(true);
				$total_column++;
			}
			$object->getActiveSheet()->getStyle("A".$row.":AD".$row)->applyFromArray($yellow_backgroud);
			$row++;
		}

		$all_total = array();

		foreach($current_total as $key => $value){
			if($value == "-"){
				$all_total[$key] = "-";
			}else{
				$all_total[$key] = $value + $resigned_total[$key];
			}			
		}

		$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, "TOTAL");
		$object->getActiveSheet()->getStyleByColumnAndRow(1, $row)->getFont()->setBold(true);
		$total_column = 3;
		foreach($all_total as $total){
			$object->getActiveSheet()->setCellValueByColumnAndRow($total_column, $row, $this->format_total($total));
			$object->getActiveSheet()->getStyleByColumnAndRow($total_column, $row)->getFont()->setBold(true);
			$total_column++;
		}
		$object->getActiveSheet()->getStyle("A".$row.":AD".$row)->applyFromArray($orange_background);

		// $object->getDefaultStyle()->getAlignment()->setWrapText(true);

		$object->getActiveSheet()->getStyle('A4:AD5')->getAlignment()->setWrapText(true);

		$object->getActiveSheet()->getColumnDimension('B')->setWidth(50);
		$object->getActiveSheet()->getColumnDimension('C')->setWidth(20);

		$file_name = $payroll_name ." - ". time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.xls"');
		$object_writer->save('php://output');
	}

	public function getSummaryReport(){
		$process_id = $_POST["process"];
		$department_id = $_POST["department"];
		$branch_id = $_POST["branch"];

		$cid = $this->session->userdata("payroll_user")["company_id"];
		$bid = $this->session->userdata("payroll_user")["branch_id"];

		$where_filter = "";

		if(!empty($department_id)){
			$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
		}

		if(!empty($branch_id)){
			$where_filter = $where_filter . " branch_id = " . $branch_id . " AND " ;
		}

		$where_filter = $where_filter . " company_id = " . $cid;

		$where_filter = $where_filter . " AND e.deleted_at is null";

		$payroll_settings = $this->db->select('date_format(period, "%M %Y") as period_formatted, type, employees, description')->from('process_payrolls')->where('id', $process_id)->get()->row();

		$payroll_settings->payroll_type = ucwords(str_replace("_", " ", $payroll_settings->type));
		if($payroll_settings->type == "second_half") $payroll_settings->payroll_type = "Month End / Second Half";

		$payroll_name = $payroll_settings->period_formatted." - ".$payroll_settings->payroll_type." | ".$payroll_settings->description;



		$employees = $this->db->query("SELECT p.*, e.id as employee_id, $process_id as process_id, first_name as name, employee_status as status, basic_wage, date_format(hired_on, '%d/%m/%Y') as hired_on, termination_reason, date_format(termination_date, '%d/%m/%Y') as termination_date, resignation_reason, date_format(resignation_date, '%d/%m/%Y') as resignation_date FROM payroll p right join employees e on p.employee_id = e.id and process_id = $process_id where $where_filter AND e.id in ($payroll_settings->employees) ORDER BY special_id")->result();

		$this->load->library("excel");

		$style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			)
		);

		$gray_backgroud = array(
	        'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => 'A9A9A9')
	        ),
	        'borders' => array(
	            'allborders' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => 'DDDDDD')
	            )
	        )
	    );

	    $left_align = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
			)
		);

		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);

		$row = 4;

		$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, "Payroll Summary Report");
		$object->getActiveSheet()->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true);
		$object->getActiveSheet()->mergeCellsByColumnAndRow(0, $row, 1, $row);
		$object->getActiveSheet()->getStyleByColumnAndRow(0, $row)->applyFromArray($left_align);



		$table_columns = array("No.", "Employee", "Net Basic", "Extra Earnings", "Gross Salary", "Total Deductions", "Net Pay", "Total Adjustment", "Net Payable");

		$column = 0;
		$row = 5;

		foreach($table_columns as $key => $field)
		{
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, $row)->getFont()->setBold(true);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, $row)->applyFromArray($gray_backgroud);
			$column++;	
		}


		$total = array(
			"net_basic" => 0,
			"total_allowances" => 0,
			"gross_salary" => 0,
			"total_deductions" => 0,
			"net_pay" => 0,
			"total_adjustments" => 0,
			"salary_paid" => 0
		);

		$row = 6;
		$count = 1;

		foreach ($employees as $emp) {
			$total_deductions = 0;
			$no_time_pay_off = 0;
			$deductions = json_decode($emp->deductions);
			if(is_null($deductions)) $deductions = array();
			foreach($deductions as $d){
				if(isset($d->fixed) && $d->fixed == "yes"){
					$no_time_pay_off += $d->amount;
				}
				$total_deductions += $d->amount;
			}

			$net_basic = $emp->eligible_amount - $no_time_pay_off;

			$total_allowances = 0;
			$allowances = json_decode($emp->allowances);
			if(is_null($allowances)) $allowances = array();
			foreach($allowances as $a){
				$total_allowances += $a->amount;
			}

			$gross_salary = $emp->eligible_amount - $no_time_pay_off + $total_allowances;
			$net_pay = $gross_salary - $total_deductions;

			$total_adjustments = 0;
			$adjustments = json_decode($emp->adjustments);
			if(is_null($adjustments)) $adjustments = array();
			foreach($adjustments as $a){
				$total_adjustments += $a->amount;
			}

			$salary_paid = $emp->eligible_amount - $total_deductions + $total_allowances - $total_adjustments;


			$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $count++);
			$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $emp->name);
			$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $this->coma_format($net_basic));
			$total["net_basic"] += $net_basic;
			$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $this->coma_format($total_allowances));
			$total["total_allowances"] += $total_allowances;
			$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $this->coma_format($gross_salary));
			$total["gross_salary"] += $gross_salary;
			$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $this->coma_format($total_deductions));
			$total["total_deductions"] += $total_deductions;
			$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $this->coma_format($net_pay));
			$total["net_pay"] += $net_pay;
			$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $this->coma_format($total_adjustments));
			$total["total_adjustments"] += $total_adjustments;
			$object->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $this->coma_format($salary_paid));
			$total["salary_paid"] += $salary_paid;


			$row++;
		}

		$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, "Total");
		$object->getActiveSheet()->getStyleByColumnAndRow(1, $row)->getFont()->setBold(true);
		$column = 2;
		foreach($total as $value){
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $this->coma_format($value));
			$object->getActiveSheet()->getStyleByColumnAndRow($column++, $row)->getFont()->setBold(true);
		}

		foreach (range('A', 'I') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
			->setAutoSize(true);
		}
		$object->getActiveSheet()->getColumnDimension("B")->setAutoSize(false);
		$object->getActiveSheet()->getColumnDimension('B')->setWidth(50);

		$file_name = "Payroll Summary Report - " . $payroll_name ." - ". time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.xls"');
		$object_writer->save('php://output');
	}

	public function getNoTimePayOffReport(){
		$process_id = $_POST["process"];
		$department_id = $_POST["department"];
		$branch_id = $_POST["branch"];

		$cid = $this->session->userdata("payroll_user")["company_id"];
		$bid = $this->session->userdata("payroll_user")["branch_id"];

		$where_filter = "";

		if(!empty($department_id)){
			$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
		}

		if(!empty($branch_id)){
			$where_filter = $where_filter . " branch_id = " . $branch_id . " AND " ;
		}

		$where_filter = $where_filter . " company_id = " . $cid;

		$where_filter = $where_filter . " AND e.deleted_at is null";

		$payroll_settings = $this->db->select('date_format(period, "%M %Y") as period_formatted, type, employees, description')->from('process_payrolls')->where('id', $process_id)->get()->row();

		$payroll_settings->payroll_type = ucwords(str_replace("_", " ", $payroll_settings->type));
		if($payroll_settings->type == "second_half") $payroll_settings->payroll_type = "Month End / Second Half";

		$payroll_name = $payroll_settings->period_formatted." - ".$payroll_settings->payroll_type." | ".$payroll_settings->description;



		$employees = $this->db->query("SELECT p.*, e.id as employee_id, $process_id as process_id, first_name as name, employee_status as status, basic_wage, date_format(hired_on, '%d/%m/%Y') as hired_on, termination_reason, date_format(termination_date, '%d/%m/%Y') as termination_date, resignation_reason, date_format(resignation_date, '%d/%m/%Y') as resignation_date FROM payroll p right join employees e on p.employee_id = e.id and process_id = $process_id where $where_filter AND e.id in ($payroll_settings->employees) ORDER BY special_id")->result();

		$this->load->library("excel");

		$style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			)
		);

		$gray_backgroud = array(
	        'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => 'A9A9A9')
	        ),
	        'borders' => array(
	            'allborders' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => 'DDDDDD')
	            )
	        )
	    );

	    $left_align = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
			)
		);

		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);

		$row = 4;

		$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, "No Time Pay Off Report");
		$object->getActiveSheet()->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true);
		$object->getActiveSheet()->mergeCellsByColumnAndRow(0, $row, 1, $row);
		$object->getActiveSheet()->getStyleByColumnAndRow(0, $row)->applyFromArray($left_align);



		$table_columns = array("No.", "Employee", "Absent Days", "Absent Amount", "Unpaid Leaves", "Unpaid Leaves Amount", "Lateness Count", "Lateness Time", "Lateness Amount");

		$column = 0;
		$row = 5;

		foreach($table_columns as $key => $field)
		{
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, $row)->getFont()->setBold(true);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, $row)->applyFromArray($gray_backgroud);
			$column++;	
		}


		$total = array(
			"absent_days" => 0,
			"absent_amount" => 0,
			"unpaid_leaves" => 0,
			"unpaid_leaves_amount" => 0,
			"lateness_count" => 0,
			"lateness_time" => "00:00",
			"lateness_amount" => 0
		);

		$row = 6;
		$count = 1;

		foreach ($employees as $emp) {
			$absent_days = 0;
			$absent_amount = 0;
			$unpaid_leaves = 0;
			$unpaid_leaves_amount = 0;
			$lateness_count = 0;
			$lateness_time = "";
			$lateness_amount = 0;

			$deductions = json_decode($emp->deductions);
			if(is_null($deductions)) $deductions = array();
			foreach($deductions as $d){
				if($d->name == "Absent Days" && $d->fixed == "yes"){
					$absent_days = $d->value;
					$absent_amount = $d->amount;
				}else if($d->name == "Unpaid Leaves" && $d->fixed == "yes"){
					$unpaid_leaves = $d->value;
					$unpaid_leaves_amount = $d->amount;
				}else if($d->name == "Lateness Time" && $d->fixed == "yes"){
					$lateness_time = $d->value;
					$lateness_amount = $d->amount;
				}
			}

			$lateness_count = $emp->late_count;

			$lateness_time = decimal_to_time($lateness_time);

			$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $count++);
			$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $emp->name);
			$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $this->coma_format($absent_days));
			$total["absent_days"] += $absent_days;
			$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $this->coma_format($absent_amount));
			$total["absent_amount"] += $absent_amount;
			$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $this->coma_format($unpaid_leaves));
			$total["unpaid_leaves"] += $unpaid_leaves;
			$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $this->coma_format($unpaid_leaves_amount));
			$total["unpaid_leaves_amount"] += $unpaid_leaves_amount;
			$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $lateness_count);
			$total["lateness_count"] += $lateness_count;
			$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $lateness_time);
			$total["lateness_time"] = add_time($total["lateness_time"], $lateness_time);
			$object->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $this->coma_format($lateness_amount));
			$total["lateness_amount"] += $lateness_amount;


			$row++;
		}

		$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, "Total");
		$object->getActiveSheet()->getStyleByColumnAndRow(1, $row)->getFont()->setBold(true);
		$column = 2;
		foreach($total as $value){
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $this->coma_format($value));
			$object->getActiveSheet()->getStyleByColumnAndRow($column++, $row)->getFont()->setBold(true);
		}

		foreach (range('A', 'I') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
			->setAutoSize(true);
		}
		$object->getActiveSheet()->getColumnDimension("B")->setAutoSize(false);
		$object->getActiveSheet()->getColumnDimension('B')->setWidth(50);

		$file_name = "No Time Pay Off Report - " . $payroll_name ." - ". time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.xls"');
		$object_writer->save('php://output');
	}

	public function getExtraEarningsReport(){
		$process_id = $_POST["process"];
		$department_id = $_POST["department"];
		$branch_id = $_POST["branch"];

		$cid = $this->session->userdata("payroll_user")["company_id"];
		$bid = $this->session->userdata("payroll_user")["branch_id"];

		$where_filter = "";

		if(!empty($department_id)){
			$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
		}

		if(!empty($branch_id)){
			$where_filter = $where_filter . " branch_id = " . $branch_id . " AND " ;
		}

		$where_filter = $where_filter . " company_id = " . $cid;

		$where_filter = $where_filter . " AND e.deleted_at is null";

		$payroll_settings = $this->db->select('date_format(period, "%M %Y") as period_formatted, type, employees, description')->from('process_payrolls')->where('id', $process_id)->get()->row();

		$payroll_settings->payroll_type = ucwords(str_replace("_", " ", $payroll_settings->type));
		if($payroll_settings->type == "second_half") $payroll_settings->payroll_type = "Month End / Second Half";

		$payroll_name = $payroll_settings->period_formatted." - ".$payroll_settings->payroll_type." | ".$payroll_settings->description;



		$employees = $this->db->query("SELECT p.*, e.id as employee_id, $process_id as process_id, first_name as name, employee_status as status, basic_wage, date_format(hired_on, '%d/%m/%Y') as hired_on, termination_reason, date_format(termination_date, '%d/%m/%Y') as termination_date, resignation_reason, date_format(resignation_date, '%d/%m/%Y') as resignation_date FROM payroll p right join employees e on p.employee_id = e.id and process_id = $process_id where $where_filter AND e.id in ($payroll_settings->employees) ORDER BY special_id")->result();

		$this->load->library("excel");

		$style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			)
		);

		$gray_backgroud = array(
	        'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => 'A9A9A9')
	        ),
	        'borders' => array(
	            'allborders' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => 'DDDDDD')
	            )
	        )
	    );

	    $left_align = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
			)
		);

		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);

		$row = 4;

		$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, "Extra Earnings Report");
		$object->getActiveSheet()->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true);
		$object->getActiveSheet()->mergeCellsByColumnAndRow(0, $row, 1, $row);
		$object->getActiveSheet()->getStyleByColumnAndRow(0, $row)->applyFromArray($left_align);



		$table_columns = array("No.", "Employee", "OT Hrs", "Overtime Amount", "OT RD Hrs", "OT RD Amount", "OT PH Hrs", "OT PH Amount");

		$column = 0;
		$row = 5;

		foreach($table_columns as $key => $field)
		{
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, $row)->getFont()->setBold(true);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, $row)->applyFromArray($gray_backgroud);
			$column++;	
		}


		$total = array(
			"ot_hours" => 0,
			"ot_amount" => 0,
			"ot_rd_hours" => 0,
			"ot_rd_amount" => 0,
			"ot_ph_hours" => 0,
			"ot_ph_amount" => 0
		);

		$row = 6;
		$count = 1;

		foreach ($employees as $emp) {

			$ot_hours = 0;
			$ot_amount = 0;
			$ot_rd_hours = 0;
			$ot_rd_amount = 0;
			$ot_ph_hours = 0;
			$ot_ph_amount = 0;
			$allowances = json_decode($emp->allowances);
			if(is_null($allowances)) $allowances = array();
			foreach($allowances as $a){
				if($a->allowance_name == "Overtime" && $a->db){
					$ot_hours = $a->value;
					$ot_amount = $a->amount;
				}else if($a->allowance_name == "Overtime (RD)" && $a->db){
					$ot_rd_hours = $a->value;
					$ot_rd_amount = $a->amount;
				}else if($a->allowance_name == "Overtime (PH)" && $a->db){
					$ot_ph_hours = $a->value;
					$ot_ph_amount = $a->amount;
				}
			}
			


			$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $count++);
			$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $emp->name);

			$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $this->coma_format($ot_hours));
			$total["ot_hours"] += $ot_hours;
			$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $this->coma_format($ot_amount));
			$total["ot_amount"] += $ot_amount;
			$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $this->coma_format($ot_rd_hours));
			$total["ot_rd_hours"] += $ot_rd_hours;
			$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $this->coma_format($ot_rd_amount));
			$total["ot_rd_amount"] += $ot_rd_amount;
			$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $this->coma_format($ot_ph_hours));
			$total["ot_ph_hours"] += $ot_ph_hours;
			$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $this->coma_format($ot_ph_amount));
			$total["ot_ph_amount"] += $ot_ph_amount;


			$row++;
		}

		$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, "Total");
		$object->getActiveSheet()->getStyleByColumnAndRow(1, $row)->getFont()->setBold(true);
		$column = 2;
		foreach($total as $value){
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $this->coma_format($value));
			$object->getActiveSheet()->getStyleByColumnAndRow($column++, $row)->getFont()->setBold(true);
		}

		foreach (range('A', 'H') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
			->setAutoSize(true);
		}
		$object->getActiveSheet()->getColumnDimension("B")->setAutoSize(false);
		$object->getActiveSheet()->getColumnDimension('B')->setWidth(50);

		$file_name = "Extra Earnings Report - " . $payroll_name ." - ". time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.xls"');
		$object_writer->save('php://output');
	}

	public function getDeductionsReport(){
		$process_id = $_POST["process"];
		$department_id = $_POST["department"];
		$branch_id = $_POST["branch"];

		$cid = $this->session->userdata("payroll_user")["company_id"];
		$bid = $this->session->userdata("payroll_user")["branch_id"];

		$where_filter = "";

		if(!empty($department_id)){
			$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
		}

		if(!empty($branch_id)){
			$where_filter = $where_filter . " branch_id = " . $branch_id . " AND " ;
		}

		$where_filter = $where_filter . " company_id = " . $cid;

		$where_filter = $where_filter . " AND e.deleted_at is null";

		$payroll_settings = $this->db->select('date_format(period, "%M %Y") as period_formatted, type, employees, description')->from('process_payrolls')->where('id', $process_id)->get()->row();

		$payroll_settings->payroll_type = ucwords(str_replace("_", " ", $payroll_settings->type));
		if($payroll_settings->type == "second_half") $payroll_settings->payroll_type = "Month End / Second Half";

		$payroll_name = $payroll_settings->period_formatted." - ".$payroll_settings->payroll_type." | ".$payroll_settings->description;



		$employees = $this->db->query("SELECT p.*, e.id as employee_id, $process_id as process_id, first_name as name, employee_status as status, basic_wage, date_format(hired_on, '%d/%m/%Y') as hired_on, termination_reason, date_format(termination_date, '%d/%m/%Y') as termination_date, resignation_reason, date_format(resignation_date, '%d/%m/%Y') as resignation_date FROM payroll p right join employees e on p.employee_id = e.id and process_id = $process_id where $where_filter AND e.id in ($payroll_settings->employees) ORDER BY special_id")->result();

		$this->load->library("excel");

		$style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			)
		);

		$gray_backgroud = array(
	        'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => 'A9A9A9')
	        ),
	        'borders' => array(
	            'allborders' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => 'DDDDDD')
	            )
	        )
	    );

	    $left_align = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
			)
		);

		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);

		$row = 4;

		$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, "Deductions Report");
		$object->getActiveSheet()->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true);
		$object->getActiveSheet()->mergeCellsByColumnAndRow(0, $row, 1, $row);
		$object->getActiveSheet()->getStyleByColumnAndRow(0, $row)->applyFromArray($left_align);



		$table_columns = array("No.", "Employee", "EPF 'yee", "EPF 'yer", "SOCSO 'yee", "SOCSO 'yer", "EIS 'yee", "EIS 'yer", "PCB");

		$column = 0;
		$row = 5;

		foreach($table_columns as $key => $field)
		{
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, $row)->getFont()->setBold(true);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, $row)->applyFromArray($gray_backgroud);
			$column++;	
		}


		$total = array(
			"epf" => 0,
			"epf_c" => 0,
			"socso" => 0,
			"socso_c" => 0,
			"eis" => 0,
			"eis_c" => 0,
			"pcb" => 0
		);

		$row = 6;
		$count = 1;

		foreach ($employees as $emp) {
			
			$epf = $emp->epf;
			$epf_c = $emp->epf_c;
			$socso = $emp->socso;
			$socso_c = $emp->socso_c;
			$eis = $emp->eis;
			$eis_c = $emp->eis_c;
			$pcb = $emp->tax;

			$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $count++);
			$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $emp->name);
			$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $this->coma_format($epf));
			$total["epf"] += $epf;
			$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $this->coma_format($epf_c));
			$total["epf_c"] += $epf_c;
			$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $this->coma_format($socso));
			$total["socso"] += $socso;
			$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $this->coma_format($socso_c));
			$total["socso_c"] += $socso_c;
			$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $this->coma_format($eis));
			$total["eis"] += $eis;
			$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $this->coma_format($eis_c));
			$total["eis_c"] += $eis_c;
			$object->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $this->coma_format($pcb));
			$total["pcb"] += $pcb;


			$row++;
		}

		$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, "Total");
		$object->getActiveSheet()->getStyleByColumnAndRow(1, $row)->getFont()->setBold(true);
		$column = 2;
		foreach($total as $value){
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $this->coma_format($value));
			$object->getActiveSheet()->getStyleByColumnAndRow($column++, $row)->getFont()->setBold(true);
		}

		foreach (range('A', 'I') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
			->setAutoSize(true);
		}
		$object->getActiveSheet()->getColumnDimension("B")->setAutoSize(false);
		$object->getActiveSheet()->getColumnDimension('B')->setWidth(50);

		$file_name = "Deductions Report - " . $payroll_name ." - ". time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.xls"');
		$object_writer->save('php://output');
	}

	public function getAdjustmentReport(){
		$process_id = $_POST["process"];
		$department_id = $_POST["department"];
		$branch_id = $_POST["branch"];

		$cid = $this->session->userdata("payroll_user")["company_id"];
		$bid = $this->session->userdata("payroll_user")["branch_id"];

		$where_filter = "";

		if(!empty($department_id)){
			$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
		}

		if(!empty($branch_id)){
			$where_filter = $where_filter . " branch_id = " . $branch_id . " AND " ;
		}

		$where_filter = $where_filter . " company_id = " . $cid;

		$where_filter = $where_filter . " AND e.deleted_at is null";

		$payroll_settings = $this->db->select('date_format(period, "%M %Y") as period_formatted, type, employees, description')->from('process_payrolls')->where('id', $process_id)->get()->row();

		$payroll_settings->payroll_type = ucwords(str_replace("_", " ", $payroll_settings->type));
		if($payroll_settings->type == "second_half") $payroll_settings->payroll_type = "Month End / Second Half";

		$payroll_name = $payroll_settings->period_formatted." - ".$payroll_settings->payroll_type." | ".$payroll_settings->description;



		$employees = $this->db->query("SELECT p.*, e.id as employee_id, $process_id as process_id, first_name as name, employee_status as status, basic_wage, date_format(hired_on, '%d/%m/%Y') as hired_on, termination_reason, date_format(termination_date, '%d/%m/%Y') as termination_date, resignation_reason, date_format(resignation_date, '%d/%m/%Y') as resignation_date FROM payroll p right join employees e on p.employee_id = e.id and process_id = $process_id where $where_filter AND e.id in ($payroll_settings->employees) ORDER BY special_id")->result();

		$this->load->library("excel");

		$style = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			)
		);

		$gray_backgroud = array(
	        'fill' => array(
	            'type' => PHPExcel_Style_Fill::FILL_SOLID,
	            'color' => array('rgb' => 'A9A9A9')
	        ),
	        'borders' => array(
	            'allborders' => array(
	                'style' => PHPExcel_Style_Border::BORDER_THIN,
	                'color' => array('rgb' => 'DDDDDD')
	            )
	        )
	    );

	    $left_align = array(
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
			)
		);

		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);

		$row = 4;

		$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, "Adjustment Report");
		$object->getActiveSheet()->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true);
		$object->getActiveSheet()->mergeCellsByColumnAndRow(0, $row, 1, $row);
		$object->getActiveSheet()->getStyleByColumnAndRow(0, $row)->applyFromArray($left_align);



		$table_columns = array("No.", "Employee", "Loan", "Advance", "In Leau of Notice");

		$column = 0;
		$row = 5;

		foreach($table_columns as $key => $field)
		{
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, $row)->getFont()->setBold(true);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, $row)->applyFromArray($gray_backgroud);
			$column++;	
		}


		$total = array(
			"loan" => 0,
			"advance" => 0,
			"notice" => 0
		);

		$row = 6;
		$count = 1;

		foreach ($employees as $emp) {

			$loan = 0;
			$advance = 0;
			$notice = 0;

			$adjustments = json_decode($emp->adjustments);
			if(is_null($adjustments)) $adjustments = array();
			foreach($adjustments as $a){
				if($a->name == "Loan" && $a->db){
					$loan = $a->amount;
				}else if($a->name == "Advance" && $a->db){
					$advance = $a->amount;
				}else if($a->name == "In lieu of notice" && $a->db){
					$notice = $a->amount;
				}
			}


			$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $count++);
			$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $emp->name);
			$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $this->coma_format($loan));
			$total["loan"] += $loan;
			$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $this->coma_format($advance));
			$total["advance"] += $advance;
			$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $this->coma_format($notice));
			$total["notice"] += $notice;


			$row++;
		}

		$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, "Total");
		$object->getActiveSheet()->getStyleByColumnAndRow(1, $row)->getFont()->setBold(true);
		$column = 2;
		foreach($total as $value){
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, $row, $this->coma_format($value));
			$object->getActiveSheet()->getStyleByColumnAndRow($column++, $row)->getFont()->setBold(true);
		}

		foreach (range('A', 'E') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
			->setAutoSize(true);
		}
		$object->getActiveSheet()->getColumnDimension("B")->setAutoSize(false);
		$object->getActiveSheet()->getColumnDimension('B')->setWidth(50);

		$file_name = "Adjustment Report - " . $payroll_name ." - ". time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.xls"');
		$object_writer->save('php://output');
	}

	public function format_total($value){
		if($value == 0){
			return "-";
		}
		return number_format($value, 2);
	}

	public function coma_format($number){
		if(is_numeric($number)){
			return number_format($number, 2);
		}
		return $number;
	}


	public function getEmployeeReport(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$process_id = $request->process;
		$department_id = $request->department;
		$branch_id = $request->branch;

		// $salary_date = $year."-".$month."-"."01";

		// $date = DateTime::createFromFormat('Y-m-d', $salary_date);
		// date_sub($date,date_interval_create_from_date_string("1 month"));

		// $last_month_date = date_format($date, 'Y-m-d');

		$cid = $this->session->userdata("payroll_user")["company_id"];
		$bid = $this->session->userdata("payroll_user")["branch_id"];
		// $permissions_level = get_user()["permissions_level"];
		$where_filter = "";
		$where_filter_all = "";

		if(!empty($department_id)){
			$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
		}

		if(!empty($branch_id)){
			$where_filter = $where_filter . " branch_id = " . $branch_id . " AND " ;
		}

		$where_filter = $where_filter . " company_id = " . $cid;
		$where_filter_all = $where_filter_all . " company_id = " . $cid;

		$where_filter = $where_filter . " AND e.deleted_at is null";
		$where_filter_all = $where_filter_all . " AND e.deleted_at is null";

		$payroll_settings = $this->db->select('date_format(period, "%M %Y") as period_formatted, type, employees, description')->from('process_payrolls')->where('id', $process_id)->get()->row();

		$payroll_settings->payroll_type = ucwords(str_replace("_", " ", $payroll_settings->type));
		if($payroll_settings->type == "second_half") $payroll_settings->payroll_type = "Month End / Second Half";

		$data["payroll_name"] = $payroll_settings->period_formatted." - ".$payroll_settings->payroll_type." | ".$payroll_settings->description;



		$employees = $this->db->query("SELECT p.*, e.id as employee_id, $process_id as process_id, first_name as name FROM payroll p right join employees e on p.employee_id = e.id and process_id = $process_id where $where_filter_all AND e.id in ($payroll_settings->employees) ORDER BY special_id")->result();

		$approved = true;

		foreach($employees as $emp){
			if($emp->approved != "Y"){
				$approved = false;
				break;
			}
		}

		$data["approved"] = $approved;

		$data["department"] = $department_id;

		$totals = array(
			"net_basic" => 0,
			"total_allowances" => 0,
			"gross_salary" => 0,
			"total_deductions" => 0,
			"net_pay" => 0,
			"total_adjustments" => 0,
			"salary_paid" => 0,
			"absent_days" => 0,
			"absent_amount" => 0,
			"unpaid_leaves" => 0,
			"unpaid_leaves_amount" => 0,
			"lateness_count" => 0,
			"lateness_time" => "00:00",
			"lateness_amount" => 0,
			"ot_hours" => 0,
			"ot_amount" => 0,
			"ot_rd_hours" => 0,
			"ot_rd_amount" => 0,
			"ot_ph_hours" => 0,
			"ot_ph_amount" => 0,
			"loan" => 0,
			"advance" => 0,
			"notice" => 0,
			"epf" => 0,
			"epf_c" => 0,
			"socso" => 0,
			"socso_c" => 0,
			"eis" => 0,
			"eis_c" => 0,
			"tax" => 0,
		);

		$employees = $this->db->query("SELECT p.*, e.id as employee_id, $process_id as process_id, first_name as name FROM payroll p right join employees e on p.employee_id = e.id and process_id = $process_id where $where_filter AND e.id in ($payroll_settings->employees) ORDER BY special_id")->result();

		foreach($employees as $emp){

			// Payroll Summary Tab Data Start
			$total_deductions = 0;
			$no_time_pay_off = 0;
			$deductions = json_decode($emp->deductions);
			if(is_null($deductions)) $deductions = array();
			foreach($deductions as $d){
				if(isset($d->fixed) && $d->fixed == "yes"){
					$no_time_pay_off += $d->amount;
				}
				$total_deductions += $d->amount;
			}

			$net_basic = $emp->eligible_amount - $no_time_pay_off;

			$total_allowances = 0;
			$allowances = json_decode($emp->allowances);
			if(is_null($allowances)) $allowances = array();
			foreach($allowances as $a){
				$total_allowances += $a->amount;
			}

			$gross_salary = $emp->eligible_amount - $no_time_pay_off + $total_allowances;
			$net_pay = $gross_salary - $total_deductions;

			$total_adjustments = 0;
			$adjustments = json_decode($emp->adjustments);
			if(is_null($adjustments)) $adjustments = array();
			foreach($adjustments as $a){
				$total_adjustments += $a->amount;
			}

			$salary_paid = $emp->eligible_amount - $total_deductions + $total_allowances - $total_adjustments;
			$emp->net_basic = $net_basic;
			$emp->total_allowances = $total_allowances;
			$emp->gross_salary = $gross_salary;
			$emp->total_deductions = $total_deductions;
			$emp->net_pay = $net_pay;
			$emp->total_adjustments = $total_adjustments;
			$emp->salary_paid = $salary_paid;

			$totals["net_basic"] += $net_basic;
			$totals["total_allowances"] += $total_allowances;
			$totals["gross_salary"] += $gross_salary;
			$totals["total_deductions"] += $total_deductions;
			$totals["net_pay"] += $net_pay;
			$totals["total_adjustments"] += $total_adjustments;
			$totals["salary_paid"] += $salary_paid;
			// Payroll Summary Tab Data End

			// No Time Pay Off Tab Data Start
			$absent_days = 0;
			$absent_amount = 0;
			$unpaid_leaves = 0;
			$unpaid_leaves_amount = 0;
			$lateness_count = 0;
			$lateness_time = "";
			$lateness_amount = 0;

			$deductions = json_decode($emp->deductions);
			if(is_null($deductions)) $deductions = array();
			foreach($deductions as $d){
				if($d->name == "Absent Days" && $d->fixed == "yes"){
					$absent_days = $d->value;
					$absent_amount = $d->amount;
				}else if($d->name == "Unpaid Leaves" && $d->fixed == "yes"){
					$unpaid_leaves = $d->value;
					$unpaid_leaves_amount = $d->amount;
				}else if($d->name == "Lateness Time" && $d->fixed == "yes"){
					$lateness_time = $d->value;
					$lateness_amount = $d->amount;
				}
			}

			$lateness_time = decimal_to_time($lateness_time);
			$lateness_count = $emp->late_count;
			$emp->absent_days = $absent_days;
			$emp->absent_amount = $absent_amount;
			$emp->unpaid_leaves = $unpaid_leaves;
			$emp->unpaid_leaves_amount = $unpaid_leaves_amount;
			$emp->lateness_count = $lateness_count;
			$emp->lateness_time = $lateness_time;
			$emp->lateness_amount = $lateness_amount;
			$totals["absent_days"] += $absent_days;
			$totals["absent_amount"] += $absent_amount;
			$totals["unpaid_leaves"] += $unpaid_leaves;
			$totals["unpaid_leaves_amount"] += $unpaid_leaves_amount;
			$totals["lateness_count"] += $lateness_count;
			$totals["lateness_time"] = add_time($totals["lateness_time"], $lateness_time);
			$totals["lateness_amount"] += $lateness_amount;
			// No Time Pay Off Tab Data End

			// Extra Earnings Tab Data Start
			$ot_hours = 0;
			$ot_amount = 0;
			$ot_rd_hours = 0;
			$ot_rd_amount = 0;
			$ot_ph_hours = 0;
			$ot_ph_amount = 0;
			$allowances = json_decode($emp->allowances);
			if(is_null($allowances)) $allowances = array();
			foreach($allowances as $a){
				if($a->allowance_name == "Overtime" && $a->db){
					$ot_hours = $a->value;
					$ot_amount = $a->amount;
				}else if($a->allowance_name == "Overtime (RD)" && $a->db){
					$ot_rd_hours = $a->value;
					$ot_rd_amount = $a->amount;
				}else if($a->allowance_name == "Overtime (PH)" && $a->db){
					$ot_ph_hours = $a->value;
					$ot_ph_amount = $a->amount;
				}
			}
			$emp->ot_hours = $ot_hours;
			$emp->ot_amount = $ot_amount;
			$emp->ot_rd_hours = $ot_rd_hours;
			$emp->ot_rd_amount = $ot_rd_amount;
			$emp->ot_ph_hours = $ot_ph_hours;
			$emp->ot_ph_amount = $ot_ph_amount;
			$totals["ot_hours"] = $ot_hours;
			$totals["ot_amount"] = $ot_amount;
			$totals["ot_rd_hours"] = $ot_rd_hours;
			$totals["ot_rd_amount"] = $ot_rd_amount;
			$totals["ot_ph_hours"] = $ot_ph_hours;
			$totals["ot_ph_amount"] = $ot_ph_amount;
			// Extra Earnings Tab Data End

			// Adjustments Tab Data Start
			$loan = 0;
			$advance = 0;
			$notice = 0;

			$adjustments = json_decode($emp->adjustments);
			if(is_null($adjustments)) $adjustments = array();
			foreach($adjustments as $a){
				if($a->name == "Loan" && $a->db){
					$loan = $a->amount;
				}else if($a->name == "Advance" && $a->db){
					$advance = $a->amount;
				}else if($a->name == "In lieu of notice" && $a->db){
					$notice = $a->amount;
				}
			}
			$emp->loan = $loan;
			$emp->advance = $advance;
			$emp->notice = $notice;
			$totals["loan"] += $loan;
			$totals["advance"] += $advance;
			$totals["notice"] += $notice;
			// Adjustments Tab Data End


			// Totals of Deductions
			$totals["epf"] += $emp->epf;
			$totals["epf_c"] += $emp->epf_c;
			$totals["socso"] += $emp->socso;
			$totals["socso_c"] += $emp->socso_c;
			$totals["eis"] += $emp->eis;
			$totals["eis_c"] += $emp->eis_c;
			$totals["tax"] += $emp->tax;
		}

		$data["employees"] = $employees;


		$data["v_report"] = $totals;
		

		// $data["v_report"] = $this->db->select('ifnull(sum(epf), 0) as epf, ifnull(sum(epf_c), 0) as epf_c, ifnull(sum(p.socso), 0) as socso, ifnull(sum(p.socso_c), 0) as socso_c, ifnull(sum(p.eis), 0) as eis, ifnull(sum(p.eis_c), 0) as eis_c, ifnull(sum(tax), 0) as tax, ifnull(sum(cp38), 0) as cp38, ifnull(sum(net_pay), 0) as net_pay')->from('payroll p')->join('employees e','p.employee_id = e.id')->where('process_id', $process_id)->where($where_filter)->get()->row();
		// $data["v_report_last"] = $this->db->select('ifnull(sum(epf), 0) as epf, ifnull(sum(epf_c), 0) as epf_c, ifnull(sum(p.socso), 0) as socso, ifnull(sum(p.socso_c), 0) as socso_c, ifnull(sum(p.eis), 0) as eis, ifnull(sum(p.eis_c), 0) as eis_c, ifnull(sum(tax), 0) as tax, ifnull(sum(cp38), 0) as cp38, ifnull(sum(net_pay), 0) as net_pay')->from('payroll p')->join('employees e','p.employee_id = e.id')->where('salary_date', $last_month_date)->where($where_filter)->get()->row();

		$data["net_report"] = $this->db->select('net_pay,first_name,special_id,eb.name as employee_bank,bank_account_no')->from('payroll p')->join('employees e','p.employee_id = e.id')->join('employee_banks eb', 'eb.id = e.employee_bank_id', 'left')->where('process_id', $process_id)->where($where_filter)->get()->result();
		$data["net_total"] = $this->db->select('sum(net_pay) as total')->from('payroll p')->join('employees e','e.id = p.employee_id')->where('process_id', $process_id)->where($where_filter)->get()->row()->total; 
		// variance report code
		// $report = $this->db->select('e.id,first_name,special_id')->from('payroll p')->join('employees e','e.id = p.employee_id')->where('process_id', $process_id)->where('e.deleted_at is null')->where($where_filter)->get()->result();

		// foreach ($report as $r) {
		// 	$r->current_month = $this->db->select('*')->from('payroll')->where('employee_id', $r->id)->where('salary_date', $salary_date)->get()->row();
		// 	$r->current_month->gross = $r->current_month->basic_amount + $r->current_month->total_allowance;
		// 	$r->current_month->total_deductions = $r->current_month->total_deductions - $r->current_month->epf - $r->current_month->socso - $r->current_month->eis - $r->current_month->tax;
		// 	$last_month = $this->db->select('*')->from('payroll')->where('employee_id', $r->id)->where('salary_date', $last_month_date)->get()->row();


		// 	if($last_month){
		// 		$last_month->gross = $last_month->basic_amount + $last_amount->total_allowance;
		// 	}else{
		// 		$temp = new stdClass();
		// 		$temp->basic_amount = 0;
		// 		$temp->total_allowance = 0;
		// 		$temp->total_deductions = 0;
		// 		$temp->gross = 0;
		// 		$temp->net_pay = 0;
		// 		$temp->epf = 0;
		// 		$temp->socso = 0;
		// 		$temp->eis = 0;
		// 		$temp->tax = 0;
		// 		$temp->epf_c = 0;
		// 		$temp->socso_c = 0;
		// 		$temp->eis_c = 0;
		// 		$last_month = $temp;
		// 	}

		// 	$r->last_month = $last_month;
		// }

		// $data["variance_report"] = $report;

		echo json_encode($data);

	}

	public function confirm(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$employee_id = $request->employee_id;
		$process_id = $request->process_id;

		$this->db->set('confirm', 'Y');
		$this->db->where('employee_id', $employee_id);
		$this->db->where('process_id', $process_id);
		$this->db->where('approved', 'N');
		$this->db->update('payroll');
	}

	public function unconfirm(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$employee_id = $request->employee_id;
		$process_id = $request->process_id;

		$this->db->set('confirm', 'N');
		$this->db->where('employee_id', $employee_id);
		$this->db->where('process_id', $process_id);
		$this->db->where('approved', 'N');
		$this->db->update('payroll');
	}

	public function approve(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$employee_id = $request->employee_id;
		$process_id = $request->process_id;

		$this->db->set('approved', 'Y');
		$this->db->where('employee_id', $employee_id);
		$this->db->where('process_id', $process_id);
		$this->db->where('confirm', 'Y');
		$this->db->update('payroll');
	}

	public function disapprove(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$employee_id = $request->employee_id;
		$process_id = $request->process_id;

		$this->db->set('approved', 'N');
		$this->db->where('employee_id', $employee_id);
		$this->db->where('process_id', $process_id);
		$this->db->where('confirm', 'Y');
		$this->db->update('payroll');
	}

	public function download_file(){

		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$process_id = $request->process_id;
		$department_id = $request->department_id;
		$bank_name = $request->bank_name;

		$cid = $this->session->userdata("payroll_user")["company_id"];

		if($bank_name == "public_bank"){

			$public_bank_id = 36;

			$payment_date = date("d/m/Y");

			$reference_date = date("dMY")." Payment";

			$file_date = date("dmy");

			$where_filter = "";

			if(!empty($department_id)){
				$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
			}

			$where_filter = $where_filter . " company_id = " . $cid;

			$where_filter = $where_filter . " AND e.deleted_at is null AND employee_status = 'active'";

			$payroll_settings = $this->db->select('employees')->from('process_payrolls')->where('id', $process_id)->get()->row();

			$employees = $this->db->query("SELECT net_pay, bank_account_no, employee_bank_id, first_name as name, bic_code, ic_passport FROM payroll p inner join employees e on p.employee_id = e.id and process_id = $process_id left join employee_banks eb on eb.id = e.employee_bank_id where $where_filter AND e.id in ($payroll_settings->employees) ORDER BY special_id")->result();

			$valid = true;

			foreach ($employees as $e) {
				if(empty($e->bank_account_no) || empty($e->bic_code) || empty($e->name) || empty($e->ic_passport)){
					$valid = false;
					break;
				}
			}

			if($valid){
				$this->load->library("excel");

				$style = array(
					'font' => array(
						'size' => 8,
						'name'  => 'Arial'
					)
				);

				$border = array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					),
					'borders' => array(
						'allborders' => array(
							'style' => PHPExcel_Style_Border::BORDER_THIN
						)
					)
				);

				$center = array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					)
				);


				$object = PHPExcel_IOFactory::load("assets/public_bank.xls");

				$object->setActiveSheetIndex(0);

				$object->getDefaultStyle()->applyFromArray($style);

				$object->getActiveSheet()->setCellValueByColumnAndRow(1, 1, $payment_date);

		// dynamic data
				$total = 0;
				$row = 4;
				foreach ($employees as $emp) {
					$bank_code = "";
					if(!empty($emp->employee_bank_id)){
						$bank_code = $emp->employee_bank_id == $public_bank_id ? "PBB" : "IBG";
					}
					$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $bank_code);
				// $object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $emp->bank_account_no);
					$object->getActiveSheet()->getCellByColumnAndRow(1, $row)->setValueExplicit($emp->bank_account_no, PHPExcel_Cell_DataType::TYPE_STRING);
					$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $emp->bic_code);
					$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $emp->name);
					$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $emp->ic_passport);
					$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, number_format($emp->net_pay,2,'.',','));
					$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $reference_date);


					for($i = 0; $i < 21; $i++){
						$object->getActiveSheet()->getStyleByColumnAndRow($i, $row)->applyFromArray($border);
					}
					$total += $emp->net_pay;
					$row++;
				}

				$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, "TOTAL:");
				$object->getActiveSheet()->getStyleByColumnAndRow(0, $row)->getFont()->setBold(true);
				$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, number_format($total,2,'.',','));
				$object->getActiveSheet()->getStyleByColumnAndRow(6, $row)->applyFromArray($center);

				$company_account_no = "";

				$company_bank = $this->db->select('account_no')->from('banks')->where('company_id', $cid)->where('is_main', "Y")->get()->row();

				if($company_bank){
					$company_account_no = $company_bank->account_no;
				}

				$file_count_exist = $this->db->select('count')->from('bank_file_no')->where('bank_name', 'public_bank')->where('date', date("Y-m-d"))->where('company_id', $cid)->get()->row();

				if($file_count_exist){
					$file_count = $file_count_exist->count + 1;
					$this->db->set('count', $file_count)->where('date', date("Y-m-d"))->where("bank_name", "public_bank")->where('company_id', $cid)->update('bank_file_no');
				}else{
					$file_count = 1;
					$this->db->insert('bank_file_no', array("bank_name" => "public_bank", "count" => 1, "date" => date("Y-m-d"), "company_id" => $cid));
				}

				$file_count = sprintf('%02d', $file_count);


				$file_name = $company_account_no."PR".$file_date.$file_count;

				$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="'.$file_name.'.xls"');
				$new_file = "uploads/bank_files/$file_name.xls";
				$object_writer->save($new_file);

				$data["success"] = true;
				$data["file_name"] = $file_name.".xls";
			}else{
				$data["success"] = false;
			}


			echo json_encode($data);
	
		}else if($bank_name == "cimb_bank"){
			$company = $this->db->select('name, autopay_code')->from('companies')->where('id', $cid)->get()->row();

			$dmy = date("dmY");

			$file_name = "AP".date("YmdHis").".txt";

			$where_filter = "";

			if(!empty($department_id)){
				$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
			}

			$where_filter = $where_filter . " company_id = " . $cid;

			$where_filter = $where_filter . " AND e.deleted_at is null AND employee_status = 'active'";

			$payroll_settings = $this->db->select('employees, date_format(period, "%b") as period, type')->from('process_payrolls')->where('id', $process_id)->get()->row();

			$period = $payroll_settings->period;
			$type = ucwords(str_replace("_", " ", $payroll_settings->type));
			if($payroll_settings->type == "second_half") $type = "Month End";

			$employees = $this->db->query("SELECT net_pay, bank_account_no, employee_bank_id, first_name as name, bnm_code, ic_passport, special_id FROM payroll p inner join employees e on p.employee_id = e.id and process_id = $process_id left join employee_banks eb on eb.id = e.employee_bank_id where $where_filter AND e.id in ($payroll_settings->employees) ORDER BY special_id")->result();


			$content = "01";

			
			$autopay_code = sprintf('%05d', $company->autopay_code);

			$content .= $autopay_code;
			$content .= $company->name;

			$spaces = 40 - strlen($company->name);

			if($spaces > 0){
				$content .= str_repeat(" ", $spaces);
			}

			$content .= $dmy."0000000000000000  \n";

			$total = 0;

			foreach($employees as $emp){
				$content .= "02".sprintf('%02d', $emp->bnm_code)."00000".sprintf('%016d', $emp->bank_account_no).$emp->name;
				$spaces = 40 - strlen($emp->name);

				if($spaces > 0){
					$content .= str_repeat(" ", $spaces);
				}

				$total += $emp->net_pay;

				$payment_amount = number_format((float)$emp->net_pay, 2, '', '');

				$content .= sprintf('%011d', $payment_amount).$emp->special_id;

				$spaces = 20 - strlen($emp->special_id);

				if($spaces > 0){
					$content .= str_repeat(" ", $spaces);
				}

				$content .= $emp->ic_passport;

				$spaces = 20 - strlen($emp->ic_passport);

				if($spaces > 0){
					$content .= str_repeat(" ", $spaces);
				}

				$content .= str_repeat(" ", 8)."2";

				$payment_description = $period." ".$type;

				$content .= $payment_description;

				$spaces = 20 - strlen($payment_description);

				if($spaces > 0){
					$content .= str_repeat(" ", $spaces);
				}

				$content .= "\n";
			}

			$content .= "03000".sprintf('%03d', count($employees))."000";

			$total = number_format((float)$total, 2, '', '');

			$content .= sprintf('%010d', $total);


			$fp = fopen(APPPATH . "/../uploads/bank_files/".$file_name,"wb");
			fwrite($fp,$content);
			fclose($fp);

			$data["success"] = true;
			$data["file_name"] = $file_name;

			echo json_encode($data);
		}
		
	}

	function download_bank_file($file_name){
		redirect(base_url()."uploads/bank_files/".$file_name);
	}

	function makeFixedAllowances($company_allowances, $auto_count, $rate_hour, $rate_day_worked){
		$fixed_allowances = array();

		foreach($company_allowances as $allowance){
			if($allowance->allowance_name == "Overtime" && $allowance->is_default == "Y"){
				$fixed_allowances[] = $this->addNewAllowance("Overtime", $allowance, $auto_count->overtime * round(1.5 * $rate_hour, 2), $auto_count->overtime."h", $auto_count->overtime, 1.5, "per_hour");
			}else if($allowance->allowance_name == "Overtime (RD)" && $allowance->is_default == "Y"){
				$fixed_allowances[] = $this->addNewAllowance("Overtime (RD)", $allowance, $auto_count->overtime_rd * round(2 * $rate_hour, 2), $auto_count->overtime_rd."h", $auto_count->overtime_rd, 2, "per_hour");
			}else if($allowance->allowance_name == "Overtime (PH)" && $allowance->is_default == "Y"){
				$fixed_allowances[] = $this->addNewAllowance("Overtime (PH)", $allowance, $auto_count->overtime_ph * round(3 * $rate_hour, 2), $auto_count->overtime_ph."h", $auto_count->overtime_ph, 3, "per_hour");
			}else if($allowance->allowance_name == "Worked (PH)" && $allowance->is_default == "Y"){
				$fixed_allowances[] = $this->addNewAllowance("Worked (PH)", $allowance, $auto_count->worked_holiday * round(2 * $rate_day_worked, 2), $auto_count->worked_holiday."d", $auto_count->worked_holiday, 2, "per_day_worked");
			}else if($allowance->allowance_name == "Worked (RD)" && $allowance->is_default == "Y"){
				$fixed_allowances[] = $this->addNewAllowance("Worked (RD)", $allowance, $auto_count->worked_rest_day * round(2 * $rate_day_worked, 2), $auto_count->worked_rest_day."d", $auto_count->worked_rest_day, 2, "per_day_worked");
			}else{
				$fixed_allowances[] = $this->addNewAllowance($allowance->allowance_name, $allowance);
			}
		}

		return $fixed_allowances;
	}

	function getEPFCategory($dob, $employee_type, $permanent_resident, $etc_on, $etc_under) {
		$age = $this->getAge($dob);
		$malaysian = $employee_type == 'm';
		$etc_before_1998 = $etc_on && $etc_on < "1998-08-01";
		$etc_after_1998 = $etc_on && $etc_on >= "1998-08-01";
		$etc_after_1998_para_3 = $etc_on && $etc_on >= "1998-08-01" && $etc_under == "para_3";
		$etc_after_2001_para_6 = $etc_on && $etc_on >= "2001-08-01" && $etc_under == "para_6";
		if ($age < 60) {
			if ($malaysian || (!$malaysian && ($permanent_resident || $etc_before_1998))) {
				return "epf_m";
			} else if (!$malaysian && ($etc_after_1998 || $etc_after_1998_para_3 || $etc_after_2001_para_6)) {
				return "epf_n";
			}
		} else {
			if (!$malaysian && ($permanent_resident || $etc_before_1998)) {
				return "epf_c";
			} else if (!$malaysian && ($etc_after_1998 || $etc_after_1998_para_3 || $etc_after_2001_para_6)) {
				return "epf_d";
			} else if ($malaysian) {
				return "epf_e";
			}
		}

		return "epf_" . $employee_type;
	}

	function getAge($dob){
		if(empty($dob)) return 0;
		$from = new DateTime($dob);
		$to   = new DateTime('today');
		return $from->diff($to)->y;
	}

	function isSOCSOSecondary($dob, $employee_type){
		$malaysian = $employee_type == 'm';
		if(!$malaysian) return true;
		$age = $this->getAge($dob);
		if($age >= 60) return true;

		return false;
	}

	function getPayrollSummary1(){
		$process_id = $_POST["process"];
		$department_id = $_POST["department"];
		$branch_id = $_POST["branch"];

		$cid = $this->session->userdata("payroll_user")["company_id"];
		$bid = $this->session->userdata("payroll_user")["branch_id"];
		
		$department = "All";
		$branch = "All";

		$where_filter = "";

		if(!empty($department_id)){
			$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
			$department = $this->db->select('name')->from('departments')->where('id', $department_id)->get()->row()->name;
		}

		if(!empty($branch_id)){
			$where_filter = $where_filter . " branch_id = " . $branch_id . " AND " ;
			$branch = $this->db->select('name')->from('branches')->where('id', $branch_id)->get()->row()->name;
		}

		$where_filter = $where_filter . " company_id = " . $cid;

		$where_filter = $where_filter . " AND e.deleted_at is null";

		$payroll_settings = $this->db->select('date_format(period, "%M %Y") as period_formatted, type, employees, description')->from('process_payrolls')->where('id', $process_id)->get()->row();

		$payroll_name = $payroll_settings->period_formatted." | ".$payroll_settings->description;

		$employees = $this->db->query("SELECT p.*, e.id as employee_id, $process_id as process_id, first_name as name, employee_status as status, basic_wage, date_format(hired_on, '%d/%m/%Y') as hired_on, termination_reason, date_format(termination_date, '%d/%m/%Y') as termination_date, resignation_reason, date_format(resignation_date, '%d/%m/%Y') as resignation_date FROM payroll p right join employees e on p.employee_id = e.id and process_id = $process_id where $where_filter AND e.id in ($payroll_settings->employees) ORDER BY special_id")->result();

		$columns_values = $this->db->select('c.id as column_id,v.id as value_id,column_name,value_name')
			->from('payroll_custom_columns c')
			->join('payroll_column_values v', 'c.id = v.custom_column_id and v.company_id = ' . $cid, 'left')
			->where('c.report_id', 1)
			->get()
			->result();
		
		// Create a mapping of column_name to value_name
		$columnValueMap = [];
		foreach ($columns_values as $column) {
			$columnValueMap[$column->column_name] = $column->value_name;
		}

		// echo "<pre>";
		// print_r($columnValueMap);
		// die;
		$totals = new stdClass();
		$totals->basic_amount = 0;
		$totals->total_allowance = 0;
		$totals->gross_pay = 0;
		$totals->epf = 0;
		$totals->socso = 0;
		$totals->eis = 0;
		$totals->late = 0;
		$totals->advance = 0;
		foreach($employees as $emp){
			$totals->basic_amount += $emp->basic_amount;
			$totals->epf += $emp->epf;
			$totals->socso += $emp->socso;
			$totals->eis += $emp->eis;
			$totals->late += $emp->late;
			$totals->advance += $emp->advance;

			$emp->gross_pay = $emp->basic_amount + $emp->total_allowance;
			$totals->gross_pay += $emp->gross_pay;
			
			$allowances = json_decode($emp->allowances);

			$travel_allowance = 0;
			$car_allowance = 0;
			$sales_commission = 0;
			$target_commission = 0;

			foreach ($allowances as $allowance) {
				$allowance_name = $allowance->allowance_name;
			
				// Check the column value map for the relevant value_name
				$mappedValue = $columnValueMap[$allowance_name] ?? $allowance_name;
			
				switch ($mappedValue) {
					case "Travel Allowance":
						$travel_allowance = $allowance->amount;
						break;
					case "Car Allowance":
						$car_allowance = $allowance->amount;
						break;
					case "Sales Commission":
						$sales_commission = $allowance->amount;
						break;
					case "Target Commission":
						$target_commission = $allowance->amount;
						break;
				}
			}

			$emp->travel_allowance = $travel_allowance;
			$emp->car_allowance = $car_allowance;
			$emp->sales_commission = $sales_commission;
			$emp->target_commission = $target_commission;

			$emp->total_allowances = $car_allowance + $travel_allowance;
			$emp->total_commissions = $sales_commission + $target_commission;
			$emp->others = $emp->total_allowance - $emp->total_allowances - $emp->total_commissions;


			$totals->total_sales_commission += $sales_commission;
			$totals->total_target_commission += $target_commission;
			$totals->total_travel_allowance += $travel_allowance;
			$totals->total_car_allowance += $car_allowance;
			$totals->total_allowance += $emp->total_allowance;
			$totals->total_commission += $emp->total_commission;
			$totals->others += $emp->others;
		}

		// echo "<pre>";
		// print_r($employees);
		// die;

		$data["employees"] = $employees;
		$data["payroll_name"] = $payroll_name;
		$data["company"] = $this->session->userdata("payroll_user")["name"];
		$data["company_registration_number"] = $this->session->userdata("payroll_user")["company_registration_number"];
		$data["department"] = $department;
		$data["branch"] = $branch;
		// 11/08/2024 11:58:14 AM
		$data["date"] = date("d/m/Y h:i:s A");
		$data["user"] = $this->session->userdata("payroll_user")["first_name"];

		$data["totals"] = $totals;

		$this->load->view('payroll/payroll_summary_1', $data);
		$html = $this->output->get_output();
		$this->load->library('pdf');
		$this->dompdf->set_option('isPhpEnabled', true);
		$this->dompdf->loadHtml($html);
		$this->dompdf->setPaper('A3');
		$this->dompdf->render();

		$this->injectPageCount($this->dompdf);

		$this->dompdf->stream("Payroll Summary", array("Attachment"=>0));
	}
	function getPayrollSummary2(){
		$process_id = $_POST["process"];
		$department_id = $_POST["department"];
		$branch_id = $_POST["branch"];

		$cid = $this->session->userdata("payroll_user")["company_id"];
		$bid = $this->session->userdata("payroll_user")["branch_id"];
		
		$department = "All";
		$branch = "All";

		$where_filter = "";

		if(!empty($department_id)){
			$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
			$department = $this->db->select('name')->from('departments')->where('id', $department_id)->get()->row()->name;
		}

		if(!empty($branch_id)){
			$where_filter = $where_filter . " branch_id = " . $branch_id . " AND " ;
			$branch = $this->db->select('name')->from('branches')->where('id', $branch_id)->get()->row()->name;
		}

		$where_filter = $where_filter . " company_id = " . $cid;

		$where_filter = $where_filter . " AND e.deleted_at is null";

		$payroll_settings = $this->db->select('date_format(period, "%M %Y") as period_formatted, type, employees, description')->from('process_payrolls')->where('id', $process_id)->get()->row();

		$payroll_name = $payroll_settings->period_formatted." | ".$payroll_settings->description;

		$employees = $this->db->query("SELECT p.*, e.id as employee_id, $process_id as process_id, first_name as name, employee_status as status, basic_wage, date_format(hired_on, '%d/%m/%Y') as hired_on, termination_reason, date_format(termination_date, '%d/%m/%Y') as termination_date, resignation_reason, date_format(resignation_date, '%d/%m/%Y') as resignation_date FROM payroll p right join employees e on p.employee_id = e.id and process_id = $process_id where $where_filter AND e.id in ($payroll_settings->employees) ORDER BY special_id")->result();
		
		$columns_values = $this->db->select('c.id as column_id,v.id as value_id,column_name,value_name')
			->from('payroll_custom_columns c')
			->join('payroll_column_values v', 'c.id = v.custom_column_id and v.company_id = ' . $cid, 'left')
			->where('c.report_id', 2)
			->get()
			->result();
		
		// Create a mapping of column_name to value_name
		$columnValueMap = [];
		foreach ($columns_values as $column) {
			$columnValueMap[$column->column_name] = $column->value_name;
		}

		$totals = new stdClass();
		$totals->basic_amount = 0;
		$totals->total_allowance = 0;
		$totals->gross_pay = 0;
		$totals->epf = 0;
		$totals->socso = 0;
		$totals->eis = 0;
		$totals->late = 0;
		$totals->advance = 0;
		foreach($employees as $emp){
			$totals->basic_amount += $emp->basic_amount;
			$totals->epf += $emp->epf;
			$totals->socso += $emp->socso;
			$totals->eis += $emp->eis;
			$totals->late += $emp->late;
			$totals->advance += $emp->advance;

			$emp->gross_pay = $emp->basic_amount + $emp->total_allowance;
			$totals->gross_pay += $emp->gross_pay;
			$late = 0;
			$advance = 0;
			$deductions = json_decode($emp->deductions);
			if(is_null($deductions)) $deductions = array();
			foreach($deductions as $d){
				if($d->name == "Lateness Time" && $d->fixed == "yes"){
					$late = $d->amount;
				}
				if($d->name == "Advance" && $d->db){
					$advance = $d->amount;
				}
			}
			$emp->late = $late;
			$emp->advance = $advance;
			$allowances = json_decode($emp->allowances);

			$travel_allowance = 0;
			$car_allowance = 0;
			$sales_commission = 0;
			$target_commission = 0;

			foreach ($allowances as $allowance) {
				$allowance_name = $allowance->allowance_name;
			
				// Check the column value map for the relevant value_name
				$mappedValue = $columnValueMap[$allowance_name] ?? $allowance_name;
			
				switch ($mappedValue) {
					case "Travel Allowance":
						$travel_allowance = $allowance->amount;
						break;
					case "Car Allowance":
						$car_allowance = $allowance->amount;
						break;
					case "Sales Commission":
						$sales_commission = $allowance->amount;
						break;
					case "Target Commission":
						$target_commission = $allowance->amount;
						break;
				}
			}

			$emp->travel_allowance = $travel_allowance;
			$emp->car_allowance = $car_allowance;
			$emp->sales_commission = $sales_commission;
			$emp->target_commission = $target_commission;

			$emp->total_allowances = $car_allowance + $travel_allowance;
			$emp->total_commissions = $sales_commission + $target_commission;
			$emp->others = $emp->total_allowance - $emp->total_allowances - $emp->total_commissions;

			$totals->total_sales_commission += $sales_commission;
			$totals->total_target_commission += $target_commission;
			$totals->total_travel_allowance += $travel_allowance;
			$totals->total_car_allowance += $car_allowance;
			$totals->total_allowance += $emp->total_allowances;
			$totals->total_commission += $emp->total_commissions;
			$totals->others += $emp->others;
		}
		// echo "<pre>";
		// print_r($employees);
		// die;

		$data["employees"] = $employees;
		$data["payroll_name"] = $payroll_name;
		$data["company"] = $this->session->userdata("payroll_user")["name"];
		$data["company_registration_number"] = $this->session->userdata("payroll_user")["company_registration_number"];
		$data["department"] = $department;
		$data["branch"] = $branch;
		// 11/08/2024 11:58:14 AM
		$data["date"] = date("d/m/Y h:i:s A");
		$data["user"] = $this->session->userdata("payroll_user")["first_name"];

		$data["totals"] = $totals;

		$this->load->view('payroll/payroll_summary_2', $data);
		$html = $this->output->get_output();
		$this->load->library('pdf');
		$this->dompdf->set_option('isPhpEnabled', true);
		$this->dompdf->loadHtml($html);
		$this->dompdf->setPaper('A3');
		$this->dompdf->render();

		$this->injectPageCount($this->dompdf);

		$this->dompdf->stream("Payroll Summary", array("Attachment"=>0));
	}
	function getPayrollSummaryGroup(){
		$process_id = $_POST["process"];
		$department_id = $_POST["department"];
		$branch_id = $_POST["branch"];

		$cid = $this->session->userdata("payroll_user")["company_id"];
		$bid = $this->session->userdata("payroll_user")["branch_id"];
		
		$department = "All";
		$branch = "All";

		$where_filter = "";

		if(!empty($department_id)){
			$where_filter = $where_filter . " department_id = " . $department_id . " AND " ;
			$department = $this->db->select('name')->from('departments')->where('id', $department_id)->get()->row()->name;
		}

		if(!empty($branch_id)){
			$where_filter = $where_filter . " branch_id = " . $branch_id . " AND " ;
			$branch = $this->db->select('name')->from('branches')->where('id', $branch_id)->get()->row()->name;
		}

		$where_filter = $where_filter . " company_id = " . $cid;

		$where_filter = $where_filter . " AND e.deleted_at is null";

		$payroll_settings = $this->db->select('date_format(period, "%M %Y") as period_formatted, type, employees, description')->from('process_payrolls')->where('id', $process_id)->get()->row();

		$payroll_name = $payroll_settings->period_formatted." | ".$payroll_settings->description;

		$employees = $this->db->query("SELECT p.*, e.id as employee_id, $process_id as process_id, first_name as name, employee_status as status, basic_wage, date_format(hired_on, '%d/%m/%Y') as hired_on, termination_reason, date_format(termination_date, '%d/%m/%Y') as termination_date, resignation_reason, date_format(resignation_date, '%d/%m/%Y') as resignation_date FROM payroll p right join employees e on p.employee_id = e.id and process_id = $process_id where $where_filter AND e.id in ($payroll_settings->employees) ORDER BY special_id")->result();

		// echo "<pre>";
		// print_r($employees);
		// die;
		// $columns_values = $this->db->select('c.id as column_id,v.id as value_id,column_name,value_name')
		// 	->from('payroll_custom_columns c')
		// 	->join('payroll_column_values v', 'c.id = v.custom_column_id and v.company_id = ' . $cid, 'left')
		// 	->where('c.report_id', 3)
		// 	->get()
		// 	->result();
		
		// // Create a mapping of column_name to value_name
		// $columnValueMap = [];
		// foreach ($columns_values as $column) {
		// 	$columnValueMap[$column->column_name] = $column->value_name;
		// }

		$totals = new stdClass();
		$totals->basic_amount = 0;
		$totals->total_allowance = 0;
		$totals->gross_pay = 0;
		$totals->epf = 0;
		$totals->socso = 0;
		$totals->eis = 0;
		$totals->late = 0;
		$totals->advance = 0;
		foreach($employees as $emp){
			$totals->basic_amount += $emp->basic_amount;
			$totals->total_allowance += $emp->total_allowance;
			$totals->epf += $emp->epf;
			$totals->socso += $emp->socso;
			$totals->eis += $emp->eis;
			$totals->late += $emp->late;
			$totals->advance += $emp->advance;

			$emp->gross_pay = $emp->basic_amount + $emp->total_allowance;
			$totals->gross_pay += $emp->gross_pay;
			$late = 0;
			$advance = 0;
			$deductions = json_decode($emp->deductions);
			if(is_null($deductions)) $deductions = array();
			foreach($deductions as $d){
				if($d->name == "Lateness Time" && $d->fixed == "yes"){
					$late = $d->amount;
				}
				if($d->name == "Advance" && $d->db){
					$advance = $d->amount;
				}
			}
			$emp->late = $late;
			$emp->advance = $advance;

			$allowances = json_decode($emp->allowances);

			$total_allowances = 0;
			$total_commissions = 0;
			$total_bonuses = 0;
			$total_overtime = 0;

			foreach ($allowances as $allowance) {
				if (stripos($allowance->allowance_name, 'Allowance') !== false) {
					$total_allowances += $allowance->amount;
				} elseif (stripos($allowance->allowance_name, 'Commission') !== false) {
					$total_commissions += $allowance->amount;
				} elseif (stripos($allowance->allowance_name, 'Bonus') !== false) {
					$total_bonuses += $allowance->amount;
				} elseif (stripos($allowance->allowance_name, 'Overtime') !== false) {
					$total_overtime += $allowance->amount;
				}
			}

			$emp->total_allowances = $total_allowances;
			$emp->total_commissions = $total_commissions;
			$emp->total_bonuses = $total_bonuses;
			$emp->total_overtime = $total_overtime;

		}

		// echo "<pre>";
		// print_r($employees);
		// die;

		$data["employees"] = $employees;
		$data["payroll_name"] = $payroll_name;
		$data["company"] = $this->session->userdata("payroll_user")["name"];
		$data["company_registration_number"] = $this->session->userdata("payroll_user")["company_registration_number"];
		$data["department"] = $department;
		$data["branch"] = $branch;
		// 11/08/2024 11:58:14 AM
		$data["date"] = date("d/m/Y h:i:s A");
		$data["user"] = $this->session->userdata("payroll_user")["first_name"];

		$data["totals"] = $totals;

		$this->load->view('payroll/payroll_summary_group', $data);
		$html = $this->output->get_output();
		$this->load->library('pdf');
		$this->dompdf->set_option('isPhpEnabled', true);
		$this->dompdf->loadHtml($html);
		$this->dompdf->setPaper('A3');
		$this->dompdf->render();

		$this->injectPageCount($this->dompdf);

		$this->dompdf->stream("Payroll Summary", array("Attachment"=>0));
	}

	function injectPageCount($dompdf): void{
		/** @var CPDF $canvas */
		$canvas = $dompdf->getCanvas();
		$pdf = $canvas->get_cpdf();
		foreach ($pdf->objects as &$o) {
			if ($o['t'] === 'contents') {
				$o['c'] = str_replace('#!', $canvas->get_page_count(), $o['c']);
			}
		}
	}
}