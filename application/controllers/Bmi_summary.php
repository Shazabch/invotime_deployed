<?php
class BMI_summary extends CI_Controller
{

	function __construct()
	{
		parent::__construct();

		if (is_null(get_user())) {
			redirect("welcome");
			//var_dump($this->session->userdata('antelope_user'));
		}
	}

	public function bmi_view($id = 0, $dep = false)
	{
		$current_user = get_user();
		// Check if it is HOD
		$is_HOD = $current_user["limit_access_to_department"] == "yes" ? TRUE : FALSE;
		$is_emp_summary_editable = $current_user["is_emp_summary_editable"] === "yes" ? TRUE : FALSE;
		$data["is_HOD"] = $is_HOD;
		$data["is_emp_summary_editable"] = $is_emp_summary_editable;

		$cid = $current_user["company_id"];

		$bid = $current_user["branch_id"];
		$branch_where_filter = "";
		$permissions_level = $current_user["permissions_level"];

		if ($permissions_level == "Outlet") {
			$branch_where_filter = " AND branch_id = $bid ";
		}
		$department_filter = '';
		$data["selected_department"] = '';

		if ($is_HOD) {
			$hod_department_id = $current_user["department_id"];
			$accessible_department_ids_array = array();
			if ($current_user["departments_access"] != "")
				$accessible_department_ids_array = array_map("trim", explode(",", $current_user["departments_access"]));
			
			$accessible_department_ids_array[] = $hod_department_id;
			$accessible_department_ids = implode(",", $accessible_department_ids_array);
			$data["departments"] = $this->db->query("SELECT * FROM departments WHERE id in( " . $accessible_department_ids . ")")->result();
			if ($dep) {
				$data["selected_department"] = $dep;
				$department_filter = "AND employees.department_id = " . $dep;
			} else {
				$data["selected_department"] = $hod_department_id;
				$department_filter = "AND employees.department_id = " . $hod_department_id;
			}
		} else {
			$data["departments"] = $this->db->query("SELECT * FROM departments WHERE company_id = $cid ORDER BY name")->result();
			$temp = new stdClass();
			$temp->id = "all";
			$temp->name = "All";
			array_unshift($data["departments"], $temp);

			if ($dep) {
				if ($dep != "all") {
					$department_filter = "AND employees.department_id = " . $dep;
				}
				$data["selected_department"] = $dep;
			}
		}

		$data["employees_dropdown"] = $this->db->query("SELECT employees.id, special_id,first_name,last_name FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND roles.exclude_from_system = 'no' $department_filter AND employees.company_id = $cid $branch_where_filter ORDER BY special_id")->result();

		if ($dep && $id == 0) {
			$data['employee'] = $this->db->select('e.id as emp_id,first_name,special_id,d.name as department,p.title as position,is_ot,is_early_ot,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,inc_late_in,inc_late_break,inc_early_out,inc_short_hours,void_lateness_time_if_less_than,branch_id,deduct_from_ot,deduct_from_ot_single,deduction_date,min_worked_hours_meal,ta_rate,ma_rate,ca_rate,spa_rate,aca_rate,aa_rate,nsa_rate,fl_rate,cw_rate,mo_rate,shift1_rate,shift2_rate,shift3_rate,food_rate')->from('employees e')->join('departments d', 'd.id = e.department_id', 'left')->join('positions p', 'p.id = e.position_id', 'left')->join('branches b', 'b.id = e.branch_id', 'left')->where('e.id', $data["employees_dropdown"][0]->id)->get()->row();
			$id = $data["employees_dropdown"][0]->id;
		} else {
			$data['employee'] = $this->db->select('e.id as emp_id,first_name,special_id,d.name as department,p.title as position,is_ot,is_early_ot,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,inc_late_in,inc_late_break,inc_early_out,inc_short_hours,void_lateness_time_if_less_than,branch_id,deduct_from_ot,deduct_from_ot_single,deduction_date,min_worked_hours_meal,ta_rate,ma_rate,ca_rate,spa_rate,aca_rate,aa_rate,nsa_rate,fl_rate,cw_rate,mo_rate,shift1_rate,shift2_rate,shift3_rate,food_rate')->from('employees e')->join('departments d', 'd.id = e.department_id', 'left')->join('positions p', 'p.id = e.position_id', 'left')->join('branches b', 'b.id = e.branch_id', 'left')->where('e.id', $id)->get()->row();
		}

		if (!$data['employee']) {
			redirect('bmi_summary/bmi_view/' . $data["employees_dropdown"][0]->id);
			//var_dump($data["employees_dropdown"][0]->id);
			die();
		}

		if (empty($_GET)) {
			$first_day = date('Y-m-01');
			$last_day  = date('Y-m-t');
		} else {
			$date1 = DateTime::createFromFormat('d/m/Y', $_GET['from']);
			$date2 = DateTime::createFromFormat('d/m/Y', $_GET['to']);
			if ($date1 > $date2) {
				$first_day = $date2->format('Y-m-d');
				$last_day = $date1->format('Y-m-d');
			} else {
				$first_day = $date1->format('Y-m-d');
				$last_day = $date2->format('Y-m-d');
			}
		}

		$calculated_data = calculate_summary_data($data["employee"]->emp_id, $first_day, $last_day);
		$data = array_merge($data, $calculated_data);

		$date = DateTime::createFromFormat('Y-m-d', $first_day);
		$data['from_f'] = $date->format('d/m/Y');
		$data['from_p'] = $first_day;
		$date = DateTime::createFromFormat('Y-m-d', $last_day);
		$data['to_f'] = $date->format('d/m/Y');
		$data['to_p'] = $last_day;
		$data['emp_id'] = $id;
		$data['pageTitle'] = "BMI Summary";
		$data['active_menu'] = "bmi_summary/bmi_view/";
		$this->load->view('header', $data);
		$data["menus"] = get_menus();

		$this->load->view('sidebar', $data);
		$this->load->view('bmi_summary', $data);
		$this->load->view('footer');
	}

	public function updateAllowance(){
		$request = $this->input->post('data');
		$value = $request[0]['value'];
		$employee_id = $request[1]['value'];
		$date = $request[2]['value'];
		$type = $request[3]['value'];
		$remove = $request[4]['value'];

		if($remove == "yes"){
			$this->db->where('employee_id', $employee_id)->where('date', $date)->delete("manual_" . $type);
			$result = array("msg" => strtoupper($type) . " Allowance reset successfully!", "removed" => true);
		}else{
			$data = array(
				"employee_id" => $employee_id,
				"date" => $date,
				"value" => $value
				);

			$this->db->replace("manual_" . $type, $data);

			$result = array("msg" => strtoupper($type) . " Allowance updated successfully!", "removed" => false, "value" => number_format($value, 2));
		}

		

		echo json_encode($result);
	}

	public function allowances(){
		$data['pageTitle'] = "BMI Allowances";
		$data['active_menu'] = "bmi_summary/allowances/";
		$this->load->view('header', $data);
		$data["menus"] = get_menus();

		$this->load->view('sidebar', $data);
		$this->load->view('bmi_allowances');
		$this->load->view('footer');
	}

	public function updateAllowances(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$branch_id = $request->branch;
		$department_id = $request->department;
		$position_id = $request->position;
		$employees = $request->employees;
		$exclude_employees = $request->exclude_employees;
		$allowances = $request->allowances;
		$date = $request->date;
		$value = $request->value;

		$date = DateTime::createFromFormat('d/m/Y', $date)->format('Y-m-d');

		$current_user = get_user();
		$cid = $current_user["company_id"];

		$permissions_level = $current_user["permissions_level"];
		if ($permissions_level == "Outlet") {
			$branch_id = array($current_user["branch_id"]);
		}

		if ($employees) {
			$employee_group_arr = array();
			foreach ($employees as $key) {
				if (strpos($key, '-') !== false) {
					$arr = explode("-", $key);
					$key1 = $arr[0];
					array_push($employee_group_arr, $key1);
				}
			}
			$employees_from_group = array();
			foreach ($employee_group_arr as $group_id) {
				$this->db->where('group_id', $group_id);
				$results = $this->db->get('employee_groups_relation')->result();
				foreach ($results as $result) {
					$employees_from_group[] = $result->employee_id;
				}
			}
			$employees_from_group = array_unique($employees_from_group);
		}

		$this->db->select('employees.id')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null')->where('roles.exclude_from_system', 'no')
			->where("(employees.employee_status = 'active' 
					OR (employees.employee_status = 'terminated' AND employees.termination_date IS NOT NULL AND employees.termination_date >= DATE_FORMAT('$date', '%Y-%m-01'))
					OR (employees.employee_status = 'resigned' AND employees.resignation_date IS NOT NULL AND employees.resignation_date >= DATE_FORMAT('$date', '%Y-%m-01'))
				)");
		if ($branch_id) {
			$this->db->where_in('employees.branch_id', $branch_id);
		}
		if ($department_id) {
			$this->db->where_in('employees.department_id', $department_id);
		}
		if ($position_id) {
			$this->db->where_in('employees.position_id', $position_id);
		}
		if ($employees) {
			$employee_group_arr = array();
			$employee_arr = array();
			foreach ($employees as $key) {
				if (strpos($key, '-') !== false) {
					// Nothing to do...
				} else {
					$employee_arr[] =  $key;
				}
			}
			$employees_array = array_merge($employees_from_group, $employee_arr);
			$employees_array = array_unique($employees_array);

			$this->db->where_in('employees.id', $employees_array);
		}
		if ($exclude_employees) {
			$this->db->where_not_in('employees.id', $exclude_employees);
		}
		$this->db->order_by('special_id', 'asc');

		$employees = $this->db->get()->result();

		$employees_ids = array();
		foreach ($employees as $emp) {
			$employees_ids[] = $emp->id;
		}

		if (count($employees_ids) == 0) {
			$result = array("success" => false, "msg" => "No employees found!");
			echo json_encode($result);
			die();
		}

		foreach($allowances as $type) {
			// Get existing allowances for specified employee IDs and date
			$existing_allowances = $this->db->select('employee_id, value')
				->from("manual_" . $type)
				->where_in('employee_id', $employees_ids)
				->where('date', $date)
				->get()
				->result();
		
			// Convert existing allowances to an associative array for quick lookup
			$existing_map = array();
			foreach ($existing_allowances as $ea) {
				$existing_map[$ea->employee_id] = $ea->value;
			}
		
			// Prepare data for insertion and update
			$data = array();
			foreach ($employees as $emp) {
				$emp_id = $emp->id;
		
				if (isset($existing_map[$emp_id])) {
					// If the record exists, update it by adding the new value to the existing one
					$new_value = $existing_map[$emp_id] + $value;
		
					$this->db->where('employee_id', $emp_id)
						->where('date', $date)
						->update("manual_" . $type, array("value" => $new_value));
				} else {
					// If the record doesn't exist, prepare it for insertion
					$data[] = array(
						"employee_id" => $emp_id,
						"date" => $date,
						"value" => $value
					);
				}
			}
		
			// Insert new records if any
			if (count($data) > 0) {
				$this->db->insert_batch("manual_" . $type, $data);
			}
		}

		$result = array("success" => true, "msg" => "Allowances updated successfully!");

		echo json_encode($result);
	}
}