<?php
class Invocore_payroll extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		if(!$this->session->userdata("payroll_user")){
			redirect("welcome");
		}

		// print_r($this->session->userdata("payroll_user"));
		// die();

	}

	function index(){
		$data["pageTitle"] = "Invocore Payroll";
		$data["menus"] = get_menus_payroll();
		$data["active_menu"] = "invocore_payroll";
		$this->load->view('payroll/index', $data);
	}

	function first_time_setup(){
		$data["pageTitle"] = "First Time Setup";
		$data["menus"] = get_menus_payroll();
		$data["active_menu"] = "invocore_payroll/first_time_setup";
		$this->load->view('payroll/first_time_setup', $data);
	}

	function process_payroll(){

		$data["pageTitle"] = "Process Payroll";
		$data["menus"] = get_menus_payroll();
		$data["active_menu"] = "invocore_payroll/process_payroll";
		$this->load->view('payroll/process_payroll', $data);
	}

	function getEmployeesForPayrollProcess(){

		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$month = $request->month;
		$year = $request->year;
		$max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year);

		$payroll_user = $this->session->userdata("payroll_user");
		$company_id = $payroll_user["company_id"];
		$branch_id = $payroll_user["branch_id"];
		$where_filter = "";
		$where_filter = $where_filter . " employees.company_id = " . $company_id;

		if($payroll_user["permissions_level"] != "Company"){
			$where_filter = $where_filter . " AND ((branch_id = $branch_id AND payroll_branch_id is null) OR payroll_branch_id = $branch_id)";
		}

		if($payroll_user["senior_staff_access"] == "no"){
	        $where_filter .= " AND level = 'junior_staff'";
	    }
		
		$where_filter = $where_filter . " AND employees.deleted_at is null";

		$employees = $this->db->query("SELECT employees.id, level, branches.name as branch_name, special_id, concat(special_id, ' - ',first_name) as name, roles.job_name as role_name, departments.name as department_name FROM employees INNER JOIN roles ON employees.role_id = roles.id INNER JOIN departments ON employees.department_id = departments.id LEFT JOIN branches on employees.branch_id = branches.id where employees.deleted_at IS NULL AND (employee_status = 'active' OR (employee_status = 'terminated' AND termination_date >= '$year-$month-01') OR (employee_status = 'resigned' AND  resignation_date >= '$year-$month-01')) AND roles.exclude_from_system = 'no' AND  (hired_on <= '$year-$month-$max_date' OR hired_on is null) AND $where_filter ORDER BY special_id, department_name, role_name")->result();

		foreach($employees as $emp){
			$emp->level = ucwords(str_replace("_", " ", $emp->level));
		}

		$grouped_employees = new stdClass();

		$outlet_employees = array();
		$department_employees = array();
		$level_employees = array();
		$role_employees = array();
		$outlet_department_employees = array();
		$outlet_level_employees = array();
		$outlet_role_employees = array();
		$department_level_employees = array();
		$department_role_employees = array();
		$level_role_employees = array();
		$outlet_department_level_employees = array();
		$outlet_department_role_employees = array();
		$outlet_level_role_employees = array();
		$department_level_role_employees = array();
		$outlet_department_level_role_employees = array();

		foreach ($employees as $emp) {

			$outlet = $emp->branch_name;
			$department = $emp->department_name;
			$level = $emp->level;
			$role = $emp->role_name;

			$outlet_employees[$outlet][] = $emp;
			$department_employees[$department][] = $emp;
			$level_employees[$level][] = $emp;
			$role_employees[$role][] = $emp;
			$outlet_department_employees[$outlet." - ".$department][] = $emp;
			$outlet_level_employees[$outlet." - ".$level][] = $emp;
			$outlet_role_employees[$outlet." - ".$role][] = $emp;
			$department_level_employees[$department." - ".$level][] = $emp;
			$department_role_employees[$department." - ".$role][] = $emp;
			$level_role_employees[$level." - ".$role][] = $emp;
			$outlet_department_level_employees[$outlet." - ".$department." - ".$level][] = $emp;
			$outlet_department_role_employees[$outlet." - ".$department." - ".$role][] = $emp;
			$outlet_level_role_employees[$outlet." - ".$level." - ".$role][] = $emp;
			$department_level_role_employees[$department." - ".$level." - ".$role][] = $emp;
			$outlet_department_level_role_employees[$outlet." - ".$department." - ".$level." - ".$role][] = $emp;
		}

		$grouped_employees->outlet = $outlet_employees;
		$grouped_employees->department = $department_employees;
		$grouped_employees->level = $level_employees;
		$grouped_employees->role = $role_employees;
		$grouped_employees->outlet_department = $outlet_department_employees;
		$grouped_employees->outlet_level = $outlet_level_employees;
		$grouped_employees->outlet_role = $outlet_role_employees;
		$grouped_employees->department_level = $department_level_employees;
		$grouped_employees->department_role = $department_role_employees;
		$grouped_employees->level_role = $level_role_employees;
		$grouped_employees->outlet_department_level = $outlet_department_level_employees;
		$grouped_employees->outlet_department_role = $outlet_department_role_employees;
		$grouped_employees->outlet_level_role = $outlet_level_role_employees;
		$grouped_employees->department_level_role = $department_level_role_employees;
		$grouped_employees->outlet_department_level_role = $outlet_department_level_role_employees;

		usort($employees, function($a, $b) {return strcmp($a->special_id, $b->special_id);});

		$data["employees"] = $employees;
		$data["grouped_employees"] = $grouped_employees;

		echo json_encode($data);
	}

	function get_data_process_payroll(){
		$data["company_id"] = $company_id = $this->session->userdata("payroll_user")["company_id"];
		$data["branch_id"] = $branch_id = $this->session->userdata("payroll_user")["branch_id"];
		$years = array();
		$data["current_year"] = $current_year = date('Y');
		for($i = $current_year + 1; $i > $current_year-3; $i--) $years[] = $i;
		$data["years"] = $years;
		$data["leave_cut_off"] = "20";

		$branches = array();

		if($this->session->userdata("payroll_user")["permissions_level"] == "Company"){
			$process_payrolls = $this->get_process_payrolls($data["company_id"]);
			$branches = $this->db->select('id, name')->from('branches')->where('company_id', $company_id)->get()->result();
			$admin_type = 'company';
		}else{
			$process_payrolls = $this->get_process_payrolls($data["company_id"], $data["branch_id"]);
			$admin_type = 'outlet';
		}

		


		$data["process_payrolls"] = $process_payrolls;
		$data["branches"] = $branches;
		$data["admin_type"] = $admin_type;
		
		echo json_encode($data);
	}


	function get_data(){
		$company_id = $this->session->userdata("payroll_user")["company_id"];
		$profile = $this->db->select('country_id, state_id, phone, address, epf_no, socso_no, employer_file_no, tax_number, hrdf_percentage, steps_done, payroll_company_name as name, autopay_code, company_registration_number, epf_percentage')->from('companies')->where('id', $company_id)->get()->row();
		$profile->company_id = $company_id;
		if($profile->country_id == 0) $profile->country_id = '';
		if($profile->state_id == 0) $profile->state_id = '';
		$data["steps_done"] = $profile->steps_done;
		$data["current_step"] = $profile->steps_done + 1;
		$data["profile"] = $profile;
		$data["countries"] = $this->db->select('id, name')->from('countries')->get()->result();
		$data["states"] = $this->db->select('id, name, country_id')->from('states')->get()->result();
		$data["company_id"] = $company_id;
		$data["malaysia_banks"] = $this->db->select('id, name')->from('malaysia_banks')->get()->result();
		$data["malaysia_states"] = $this->db->select('id, name')->from('states')->where('country_id', 132)->get()->result();
		$data["company_banks"] = $this->db->select('company_id, bank_id, state_id, b.id,mb.name as bank,s.name as state,account_no,is_main')->from('banks b')->join('malaysia_banks mb','mb.id = b.bank_id')->join('states s','s.id = b.state_id')->where('company_id', $company_id)->get()->result();
		$data["tax_rules"] = $this->db->select('id, description, limit_amount')->from('tax_exempted_rules')->get()->result();

		$default_allowances_exist = $this->db->select('id')->from('company_allowances')->where('company_id', $company_id)->where('is_default', 'Y')->get()->row();

		if(!$default_allowances_exist){
			$default_allowances_names = array("Overtime", "Overtime (RD)", "Overtime (PH)", "Worked (PH)", "Worked (RD)", "General Allowances", "Meal allowance", "Transport allowance", "Phone allowance", "Claims", "Commission", "Bonus");

			foreach($default_allowances_names as $name){
				$this->db->insert("company_allowances", array(
					"company_id" => $company_id,
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

		$data["allowances"] = $this->db->select('id, allowance_name, pay_epf, pay_socso, pay_tax, eligible_salary, pay_eis, company_id, "no" as can_edit, is_default')->from('company_allowances')->where('company_id', $company_id)->order_by('is_default', 'desc')->order_by('id', 'asc')->get()->result();
		$data["deductions"] = $this->db->select('id, deduction_name, pay_epf, pay_socso, pay_tax, pay_hrdf, pay_eis, company_id, "no" as can_edit')->from('company_deductions')->where('company_id', $company_id)->order_by('created_at', 'desc')->get()->result();
		$calendar = $this->db->select('calendar_code, calendar_state_id')->from('companies')->where('id', $company_id)->get()->row();
		if($calendar->calendar_state_id == 0) $calendar->calendar_state_id = '';
		$rest_days = $this->db->select('id, is_apply, month, week_day, day_type')->from('rest_days')->where('company_id', $company_id)->get()->result();
		if(!$rest_days){
			$temp_rest_day = new stdClass();
			$temp_rest_day->id = null;
			$temp_rest_day->is_apply = 'N';
			$temp_rest_day->month = '';
			$temp_rest_day->week_day = '';
			$temp_rest_day->day_type = '';
			$rest_days[] = $temp_rest_day;
			$rest_days[] = $temp_rest_day;
			$rest_days[] = $temp_rest_day;
			$rest_days[] = $temp_rest_day;
			$rest_days[] = $temp_rest_day;
		}
		$calendar->rest_days = $rest_days;
		$data["calendar"] = $calendar;
		echo json_encode($data);
	}

	function createAllowance(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;

		if($request->start_period == '') $request->start_period = null;
		if($request->end_period == '') $request->end_period = null;

		$allowance_data = array("company_id" => $company_id,
			"code" => $request->code,
			"description" => $request->description,
			"start_period" => $request->start_period,
			"end_period" => $request->end_period,
			"pay_epf" => $request->pay_epf,
			"pay_socso_eis" => $request->pay_socso_eis,
			"pay_tax" => $request->pay_tax,
			"eligible_salary" => $request->eligible_salary,
			"tax_rule_id" => $request->tax_rule_id);

		$this->db->insert('company_allowances', $allowance_data);


		$data["allowances"] = $this->db->select('id, allowance_name, pay_epf, pay_socso, pay_tax, eligible_salary, pay_eis, company_id')->from('company_allowances')->where('company_id', $company_id)->get()->result();
		echo json_encode($data);
	}

	function saveAllowances(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;
		$steps_done = $request->steps_done;
		$allowances = $request->allowances;
		$deleted_ids = $request->deleted_ids;
		$deleted_ids[] = '0';
		$this->db->where_in('id', $deleted_ids)->delete('company_allowances');

		foreach ($allowances as $a) {
			$allowance_data = array("company_id" => $company_id,
				"allowance_name" => $a->allowance_name,
				"pay_tax" => $a->pay_tax,
				"pay_epf" => $a->pay_epf,
				"eligible_salary" => $a->eligible_salary,
				"pay_socso" => $a->pay_socso,
				"pay_eis" => $a->pay_eis);
			if($a->id == null){
				$this->db->insert('company_allowances', $allowance_data);
			}else{
				$this->db->where('id', $a->id)->update('company_allowances', $allowance_data);
			}
		}

		$data["allowances"] = $this->db->select('id, allowance_name, pay_epf, pay_socso, pay_tax, eligible_salary, pay_eis, company_id, "no" as can_edit, is_default')->from('company_allowances')->where('company_id', $company_id)->order_by('is_default', 'desc')->order_by('id', 'asc')->get()->result();
		$data["steps_done"] = $steps_done;

		if($steps_done < 3){
			$data["steps_done"] = $company_data["steps_done"] = $request->steps_done + 1;
			$this->db->where('id', $company_id)->update('companies', $company_data);
		}

		
		echo json_encode($data);
	}

	function saveDeductions(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;
		$steps_done = $request->steps_done;
		$deductions = $request->deductions;
		$deleted_ids = $request->deleted_ids;
		$deleted_ids[] = '0';
		$this->db->where_in('id', $deleted_ids)->delete('company_deductions');

		foreach ($deductions as $a) {
			$deduction_data = array("company_id" => $company_id,
				"deduction_name" => $a->deduction_name,
				"pay_tax" => $a->pay_tax,
				"pay_epf" => $a->pay_epf,
				"pay_hrdf" => $a->pay_hrdf,
				"pay_socso" => $a->pay_socso,
				"pay_eis" => $a->pay_eis);
			if($a->id == null){
				$this->db->insert('company_deductions', $deduction_data);
			}else{
				$this->db->where('id', $a->id)->update('company_deductions', $deduction_data);
			}
		}

		$data["deductions"] = $this->db->select('id, deduction_name, pay_epf, pay_socso, pay_tax, pay_hrdf, pay_eis, company_id, "no" as can_edit')->from('company_deductions')->where('company_id', $company_id)->order_by('created_at', 'desc')->get()->result();
		$data["steps_done"] = $steps_done;

		if($steps_done < 4){
			$data["steps_done"] = $company_data["steps_done"] = $request->steps_done + 1;
			$this->db->where('id', $company_id)->update('companies', $company_data);
		}

		
		echo json_encode($data);
	}

	function createDeduction(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;

		if($request->start_period == '') $request->start_period = null;
		if($request->end_period == '') $request->end_period = null;

		$deduction_data = array("company_id" => $company_id,
			"code" => $request->code,
			"description" => $request->description,
			"start_period" => $request->start_period,
			"end_period" => $request->end_period,
			"pay_epf" => $request->pay_epf,
			"pay_socso_eis" => $request->pay_socso_eis,
			"pay_tax" => $request->pay_tax,
			"pay_hrdf" => $request->pay_hrdf);

		$this->db->insert('company_deductions', $deduction_data);


		$data["deductions"] = $this->db->select('id, code, description, start_period as start_period_date, date_format(start_period , "%M %Y") as start_period, end_period as end_period_date, date_format(end_period , "%M %Y") as end_period, pay_epf, pay_socso_eis, pay_tax, pay_hrdf, company_id')->from('company_deductions')->where('company_id', $company_id)->get()->result();
		echo json_encode($data);
	}

	function updateAllowance(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;

		if($request->start_period == '') $request->start_period = null;
		if($request->end_period == '') $request->end_period = null;
		$allowance_data = array("company_id" => $company_id,
			"code" => $request->code,
			"description" => $request->description,
			"start_period" => $request->start_period,
			"end_period" => $request->end_period,
			"pay_epf" => $request->pay_epf,
			"pay_socso_eis" => $request->pay_socso_eis,
			"pay_tax" => $request->pay_tax,
			"eligible_salary" => $request->eligible_salary,
			"tax_rule_id" => $request->tax_rule_id);


		$this->db->where('id', $request->id)->update('company_allowances', $allowance_data);


		$data["allowances"] = $this->db->select('id, allowance_name, pay_epf, pay_socso, pay_tax, eligible_salary, pay_eis, company_id')->from('company_allowances')->where('company_id', $company_id)->get()->result();
		echo json_encode($data);
	}

	function updateDeduction(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;

		if($request->start_period == '') $request->start_period = null;
		if($request->end_period == '') $request->end_period = null;
		$deduction_data = array("company_id" => $company_id,
			"code" => $request->code,
			"description" => $request->description,
			"start_period" => $request->start_period,
			"end_period" => $request->end_period,
			"pay_epf" => $request->pay_epf,
			"pay_socso_eis" => $request->pay_socso_eis,
			"pay_tax" => $request->pay_tax,
			"pay_hrdf" => $request->pay_hrdf);


		$this->db->where('id', $request->id)->update('company_deductions', $deduction_data);


		$data["deductions"] = $this->db->select('id, code, description, start_period as start_period_date, date_format(start_period , "%M %Y") as start_period, end_period as end_period_date, date_format(end_period , "%M %Y") as end_period, pay_epf, pay_socso_eis, pay_tax, pay_hrdf, company_id')->from('company_deductions')->where('company_id', $company_id)->get()->result();
		echo json_encode($data);
	}

	function save_profile(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;
		
		$profile_data = array("country_id" => $request->country_id,
			"state_id" => $request->state_id,
			"phone" => $request->phone,
			"address" => $request->address,
			"epf_no" => $request->epf_no,
			"socso_no" => $request->socso_no,
			"employer_file_no" => $request->employer_file_no,
			"tax_number" => $request->tax_number,
			"hrdf_percentage" => $request->hrdf_percentage,
			"epf_percentage" => $request->epf_percentage,
			"autopay_code" => $request->autopay_code,
			"company_registration_number" => $request->company_registration_number,
			"payroll_company_name" => $request->name);

		$data["steps_done"] = $request->steps_done;

		if($request->current_step > $request->steps_done){
			$data["steps_done"] = $profile_data["steps_done"] = $request->steps_done + 1;
		}

		$this->db->where('id', $company_id)->update('companies', $profile_data);

		echo json_encode($data);
	}

	function createBank(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;
		if($request->is_main == 'Y'){
			$this->db->set('is_main', 'N')->where('company_id', $company_id)->update('banks');
		}

		$bank_data = array("company_id" => $company_id,
			"bank_id" => $request->bank_id,
			"account_no" => $request->account_no,
			"state_id" => $request->state_id,
			"is_main" => $request->is_main);
		$this->db->insert('banks', $bank_data);

		$data["company_banks"] = $this->db->select('company_id, bank_id, state_id, b.id,mb.name as bank,s.name as state,account_no,is_main')->from('banks b')->join('malaysia_banks mb','mb.id = b.bank_id')->join('states s','s.id = b.state_id')->where('company_id', $company_id)->get()->result();

		echo json_encode($data);
	}

	function updateBank(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;
		if($request->is_main == 'Y'){
			$this->db->set('is_main', 'N')->where('company_id', $company_id)->update('banks');
		}

		$bank_data = array("company_id" => $company_id,
			"bank_id" => $request->bank_id,
			"account_no" => $request->account_no,
			"state_id" => $request->state_id,
			"is_main" => $request->is_main);
		$this->db->where('id', $request->id)->update('banks', $bank_data);

		$data["company_banks"] = $this->db->select('company_id, bank_id, state_id, b.id,mb.name as bank,s.name as state,account_no,is_main')->from('banks b')->join('malaysia_banks mb','mb.id = b.bank_id')->join('states s','s.id = b.state_id')->where('company_id', $company_id)->get()->result();

		echo json_encode($data);
	}

	function deleteBank(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;

		$this->db->where('id', $request->id)->delete('banks');

		$data["company_banks"] = $this->db->select('company_id, bank_id, state_id, b.id,mb.name as bank,s.name as state,account_no,is_main')->from('banks b')->join('malaysia_banks mb','mb.id = b.bank_id')->join('states s','s.id = b.state_id')->where('company_id', $company_id)->get()->result();

		echo json_encode($data);
	}

	function deletePayroll(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;
		$branch_id = $request->branch_id;

		$this->db->where('id', $request->id)->delete('process_payrolls');

		if($this->session->userdata("payroll_user")["permissions_level"] == "Company"){
			$process_payrolls = $this->get_process_payrolls($company_id);
		}else{
			$process_payrolls = $this->get_process_payrolls($company_id, $branch_id);
		}

		$data["process_payrolls"] = $process_payrolls;		

		echo json_encode($data);
	}

	function deleteAllowance(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;

		$this->db->where('id', $request->id)->delete('company_allowances');

		$data["allowances"] = $this->db->select('id, allowance_name, pay_epf, pay_socso, pay_tax, eligible_salary, pay_eis, company_id')->from('company_allowances')->where('company_id', $company_id)->get()->result();

		echo json_encode($data);
	}

	function deleteDeduction(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;

		$this->db->where('id', $request->id)->delete('company_deductions');

		$data["deductions"] = $this->db->select('id, code, description, start_period as start_period_date, date_format(start_period , "%M %Y") as start_period, end_period as end_period_date, date_format(end_period , "%M %Y") as end_period, pay_epf, pay_socso_eis, pay_tax, pay_hrdf, company_id')->from('company_deductions')->where('company_id', $company_id)->get()->result();

		echo json_encode($data);
	}

	function banksDone(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;

		$this->db->set('steps_done', 2)->where('id', $company_id)->update('companies');
	}

	function importDone(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;

		$this->db->set('steps_done', 5)->where('id', $company_id)->update('companies');
	}

	function allowancesDone(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;

		$this->db->set('steps_done', 3)->where('id', $company_id)->update('companies');
	}

	function deductionsDone(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;

		$this->db->set('steps_done', 4)->where('id', $company_id)->update('companies');
	}

	function saveNewProcess(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$payroll_data = array("company_id" => $request->company_id,
			"branch_id" => $request->branch_id,
			"type" => $request->type,
			"include_fix" => $request->include_fix,
			"description" => $request->description,
			"period" => $request->year."-".$request->month."-01",
			"leave_cut_off" => $request->leave_cut_off,
			"bonus_months" => $request->bonus_months,
			"employees_group" => implode(",", $request->employees_group),
			"employees" => implode(",", $request->employees));

		$this->db->insert("process_payrolls", $payroll_data);

		if($this->session->userdata("payroll_user")["permissions_level"] == "Company"){
			$process_payrolls = $this->get_process_payrolls($request->company_id);
		}else{
			$process_payrolls = $this->get_process_payrolls($request->company_id, $request->branch_id);
		}
		$data["process_payrolls"] = $process_payrolls;
		echo json_encode($data);
	}

	function updateProcess(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$payroll_data = array("type" => $request->type,
			"include_fix" => $request->include_fix,
			"description" => $request->description,
			"period" => $request->year."-".$request->month."-01",
			"leave_cut_off" => $request->leave_cut_off,
			"bonus_months" => $request->bonus_months,
			"employees_group" => implode(",", $request->employees_group),
			"employees" => implode(",", $request->employees),
			"branch_id" => $request->branch_id
		);

		$this->db->where('id', $request->id)->update("process_payrolls", $payroll_data);

		if($this->session->userdata("payroll_user")["permissions_level"] == "Company"){
			$process_payrolls = $this->get_process_payrolls($request->company_id);
		}else{
			$process_payrolls = $this->get_process_payrolls($request->company_id, $request->branch_id);
		}
		$data["process_payrolls"] = $process_payrolls;
		
		echo json_encode($data);
	}

	function get_process_payrolls($company_id, $branch_id = false){
		$this->db->select('p.id,b.name as branch_name,period,date_format(period, "%M %Y") as period_formatted,date_format(p.created_at,"%d/%m/%Y") as date,leave_cut_off,type,employees_group,employees, description, is_committed,p.company_id,branch_id as payroll_branch_id,bonus_months,include_fix')->from('process_payrolls p')->join('branches b', 'b.id = p.branch_id')->where('p.company_id', $company_id);

		if($branch_id){
			$this->db->where('branch_id', $branch_id);
		}

		$process_payrolls = $this->db->order_by('period', 'desc')->get()->result();

		foreach ($process_payrolls as $p) {
			$p->payroll_type = ucwords(str_replace("_", " ", $p->type));
			$p->employee_count = count(explode(",", $p->employees));
			if($p->type == "second_half") $p->payroll_type = "Month End / Second Half";
			$dates = explode("-", $p->period);
			$p->year = $dates[0];
			$p->month = $dates[1];
			$p->employees_group = explode(",", $p->employees_group);

			$payrolls = $this->db->query("SELECT p.confirm FROM payroll p right join employees e on p.employee_id = e.id and process_id = $p->id where e.id in ($p->employees)")->result();

			$is_committed = true;

			foreach ($payrolls as $payroll) {
				if($payroll->confirm != "Y"){
					$is_committed = false;
					break;
				}
			}

			$p->is_committed = $is_committed;

			$p->employees = explode(",", $p->employees);
		}

		return $process_payrolls;
	}

	function save_calendar_setting(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$company_id = $request->company_id;
		$company_data = array("calendar_code" => $request->calendar_code,
			"calendar_state_id" => $request->calendar_state_id);
		$data["steps_done"] = $request->steps_done;

		if($request->current_step > $request->steps_done){
			$data["steps_done"] = $company_data["steps_done"] = $request->steps_done + 1;
		}
		$this->db->where('id', $company_id)->update('companies', $company_data);

		$rest_days = $request->rest_days;

		foreach($rest_days as $r){
			$rest_day_data = array("company_id" => $company_id,
				"month" => $r->month,
				"week_day" => $r->week_day,
				"day_type" => $r->day_type,
				"is_apply" => $r->is_apply);
			if($r->id == null){
				$this->db->insert('rest_days', $rest_day_data);
			}else{
				$this->db->where('id', $r->id)->update('rest_days', $rest_day_data);
			}
		}

		echo json_encode($data);
	}

	public function import_basic_info()
	{

		$cid = $this->session->userdata("payroll_user")["company_id"];

		$response = array();

		$insert_success = 0;
		$insert_failed = 0;

		$rows_error = array();

		//var_dump($_POST["json"]);

		$data = $_POST["json"];

		$required_missing = false;

		$department = null;
		$position = null;
		$role = null;
		$branch = null;
		$employee = null;

		$banks = $this->db->select('id, name')->from('employee_banks')->get()->result();


		foreach ($data as $key => $val) {
			// echo $emp["first_name"] . " ";
			$required_missing = false;

			$val["department"] = trim($val['department']);
			$val["position"] = trim($val['position']);
			$val["role"] = trim($val['role']);
			$val["outlet"] = trim($val['outlet']);


			$department = $this->db->get_where('departments', array('company_id =' => $cid, 'TRIM(name) =' => $val["department"]))->row();
			$position = $this->db->get_where('positions', array('company_id =' => $cid, 'TRIM(title) = ' => $val["position"]))->row();
			$role = $this->db->get_where('roles', array('company_id =' => $cid, 'TRIM(job_name) = ' => $val["role"]))->row();
			$branch = $this->db->get_where('branches', array('company_id =' => $cid, 'TRIM(name) = ' => $val["outlet"]))->row();

			$employee = $this->db->get_where('employees', array('deleted_at =' => NULL, 'company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();

			//echo $this->db->last_query();


			// var_dump($department);
			// var_dump($position);
			// var_dump($role);
			// var_dump($branch);

			//die();

			if ($employee) {
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = "<b>" . $val["employee_id"] . " </b>employee already exists";
				$rows_error[] = $err;
			}

			if (!$department) {
				// Todo create department
				// Add department into $department var
				// $required_missing = true;
				// $err = array();
				// $err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				// $err["error"] = "<b>".$val["department"] . " </b>department not found";
				// $rows_error[] = $err;
				// Insert department first

				$is_department_inserted = $this->db->insert('departments', array('company_id' => $cid, 'name' => $val["department"]));

				if ($is_department_inserted) {
					$department = $this->db->get_where('departments', array('company_id =' => $cid, 'name = ' => $val["department"]))->row();
				} else {
					$required_missing = true;
					$err = array();
					$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
					$err["error"] = "<b>" . $val["department"] . " </b>department could not be inserted";
					$rows_error[] = $err;
				}
			}

			if (!$position) {
				// Same goes for position
				// $required_missing = true;
				// $err = array();
				// $err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				// $err["error"] = "<b>".$val["position"] . " </b>position not found";
				// $rows_error[] = $err;
				$is_position_inserted = $this->db->insert('positions', array('company_id' => $cid, 'title' => $val["position"]));

				if ($is_position_inserted) {
					$position = $this->db->get_where('positions', array('company_id =' => $cid, 'title = ' => $val["position"]))->row();
				} else {
					$required_missing = true;
					$err = array();
					$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
					$err["error"] = "<b>" . $val["position"] . " </b>position could not be inserted";
					$rows_error[] = $err;
				}
			}

			$val["ic_passport"] = trim($val["ic_passport"]);
			if(strpos($val["ic_passport"], '-') !== false || strpos($val["ic_passport"], ' ') !== false){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = "<b>ic_passport</b> should not contain spaces and dashes";
				$rows_error[] = $err;
			}



			if (!$role) {
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = "<b>" . $val["role"] . " </b>role not found";
				$rows_error[] = $err;
			}

			if (!$branch) {
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = "<b>" . $val["outlet"] . "</b> outlet not found";
				$rows_error[] = $err;
			} else {

				// $permissions_level = get_user()["permissions_level"];

				// if ($permissions_level == "Outlet") {
				// 	if ($branch->id != get_user()["branch_id"]) {
				// 		$required_missing = true;
				// 		$err = array();
				// 		$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				// 		$err["error"] = "not allowed to import to <b>" . $val["outlet"] . "</b> outlet";
				// 		$rows_error[] = $err;
				// 	}
				// }
			}

			if(get_employee_bank_id($banks, $val["bank_name"]) == null){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = "Bank <b>" . $val["bank_name"] . " </b>not found. Check Bank Names file to get correct name of bank or contact us if bank not found in file";
				$rows_error[] = $err;
			}


			if ($required_missing) {
				$insert_failed = $insert_failed + 1;

				//echo $required_missing;
				continue;
			}
		}

		if (empty($rows_error)) {

			foreach ($data as $key => $val) {

				$employee_bank_id = get_employee_bank_id($banks, $val["bank_name"]);

				$department = $this->db->get_where('departments', array('company_id =' => $cid, 'name = ' => $val["department"]))->row();
				$position = $this->db->get_where('positions', array('company_id =' => $cid, 'title = ' => $val["position"]))->row();
				$role = $this->db->get_where('roles', array('company_id =' => $cid, 'job_name = ' => $val["role"]))->row();
				$branch = $this->db->get_where('branches', array('company_id =' => $cid, 'name = ' => $val["outlet"]))->row();

				$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();

				//var_dump($val);

				$val['dob'] = str_replace("/", "-", $val['dob']);
				$val['hired_on'] = str_replace("/", "-", $val['hired_on']);
				$val['license_expiry'] = str_replace("/", "-", $val['license_expiry']);

				// set it null if it is empty.
				$val['hired_on'] = $val['hired_on'] == '' ? NULL : date("Y-m-d", strtotime($val["hired_on"]));
				$val['dob'] = $val['dob'] == '' ? NULL : date("Y-m-d", strtotime($val["dob"]));
				$val['license_expiry'] = $val['license_expiry'] == '' ? NULL : date("Y-m-d", strtotime($val["license_expiry"]));

				$val['is_ot'] = strtolower($val['is_ot']);

				if($val['is_ot'] == "no"){
					$val['is_ot'] = 0;
				}else{
					$val['is_ot'] = 1;
				}

				$val["employment_type"] = str_replace(" ", "_", strtolower($val["employment_type"]));

				if($val["employment_type"] != "full_time" && $val["employment_type"] != "part_time"){
					$val["employment_type"] = "";
				}

				$val["level"] = str_replace(" ", "_", strtolower($val["level"]));

				if($val["level"] != "junior_staff" && $val["level"] != "senior_staff"){
					$val["level"] = "";
				}

				$d = array(
					'first_name' => $val["full_name"],
					'special_id' => $val["employee_id"],
					'sex' => $val["sex"],
					'department_id' => $department->id,
					'position_id' => $position->id,
					'role_id' => $role->id,
					'branch_id' => $branch->id,
					'company_id' => $cid,
					'grade' => $val["job_grade"],
					'employment_type' => $val["employment_type"],
					'hired_on' => $val['hired_on'],
					'dob' => $val['dob'],
					'pob' => $val["pob"],
					'ic_passport' => $val["ic_passport"],
					'race' => $val["race"],
					'religion' => $val["religion"],
					'nationality' => $val["nationality"],
					'perm_address' => $val["perm_address"],
					'perm_address_postcode' => $val["perm_address_postcode"],
					'perm_address_city' => $val["perm_address_city"],
					'perm_address_state' => $val["perm_address_state"],
					'temp_address' => $val["temp_address"],
					'temp_address_postcode' => $val["temp_address_postcode"],
					'temp_address_city' => $val["temp_address_city"],
					'temp_address_state' => $val["temp_address_state"],
					'telephone' => $val["telephone"],
					'mobile' => $val["mobile"],
					'email' => $val["email"],
					'marital_status' => $val["marital_status"],
					'basic_wage' => $val["basic_wage"],
					'epf_no' => $val["epf_no"],
					'socso' => $val["socso"],
					'eis' => $val["eis"],
					'income_tax_no' => $val["income_tax_no"],
					'income_tax_branch' => $val["income_tax_branch"],
					'is_ot' => $val["is_ot"],
					'qr_barcode' => $val["qr_barcode"],
					'bank_account_no' => $val["bank_account_no"],
					'employee_bank_id' => $employee_bank_id,
					'license_class' => $val["license_class"],
					'license_no' => $val["license_no"],
					'license_expiry' => $val['license_expiry'],
					'level' => $val['level']
				);

				if ($this->db->insert('employees', $d)) {
					$insert_success = $insert_success + 1;
				} else {
					$insert_failed = $insert_failed + 1;
					$err = array();
					$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
					$err["error"] = $this->db->error()["message"];
					$rows_error[] = $err;
				}
			}
		}

		// var_dump($insert_failed);
		// var_dump($rows_error);


		$temp = $new = array();
		foreach ($rows_error as $val) {
			$temp[$val['row']][] = $val['error'];
		}

		foreach ($temp as $key => $value) {
			$values = implode(',', array_unique(explode(',', implode('<hr>', $value))));
			$new[] = array('row' => $key, 'error' => $values); // store 
		}

		$response["insert_success"] = $insert_success;
		$response["insert_failed"] = $insert_failed;
		$response["rows_error"] = json_encode($new);

		$response["msg"] = '';
		if ($insert_success > 0) {
			$text = ' employee imported successfully!';
			if($insert_success > 1) $text = ' employees imported successfully!';
			$response["msg"] = ' <span style="color:blue">OK: ' . $insert_success. $text . '<span>';
		}
		if ($insert_failed > 0) {
			$response["msg"] =  $response["msg"]  . '   <span style="color:red">Errors: ' . $insert_failed . '<span>';
		}

		echo json_encode($response);
	}

	public function table($table_name)
	{

		$active_menu = $table_name;
		$page = $table_name;
		$data['pageTitle'] = ucwords(str_replace("_"," ",$table_name));


		if(is_callable(array($this->antelope, $table_name), false, $table_name)){

			$this->load->helper('xcrud');
			$xcrud = xcrud_get_instance($table_name . "_" . time());
			$xcrud->unset_title();

			$xcrud  = call_user_func_array(array($this->antelope, $table_name),  array($xcrud));

			$data['table_content'] = $xcrud;

		}else{

			$data['table_content'] = "<div class='alert alert-danger'>
			<h4>Could not find <strong>$active_menu</strong> function in <strong>Application</strong>  > <strong> Models</strong>  > <strong> antelope.php</strong> </h4>
			</div>";

		}

		$data['active_menu'] = "invocore_payroll/table/".$active_menu;




		$data["menus"] = get_menus_payroll();

		if (is_page_permitted_payroll($page)) {
			$this->load->view('payroll/table',$data);
		}
		else{
			$this->load->view('payroll/not_permitted', $data);
		}
	}

}