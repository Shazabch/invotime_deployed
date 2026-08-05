<?php
class Exports extends CI_Controller
{

	function __construct()
	{
		parent::__construct();

		if (is_null(get_user())) {
			redirect("welcome");
		}
	}

	public function getData()
	{
		$cid = get_user()["company_id"];
		$bid = get_user()["branch_id"];
		$permissions_level = get_user()["permissions_level"];

		$where_branch = '';

		$employees_branch_where = '';
		if ($permissions_level == "Outlet") {
			$where_branch = " AND id = $bid ";
			$employees_branch_where = " AND employees.branch_id = $bid ";
		}

		$data["outlets"] = $this->db->query("SELECT id,name FROM branches WHERE company_id = $cid  $where_branch ORDER BY name")->result();
		$data["departments"] = $this->db->query("SELECT id,name FROM departments WHERE company_id = $cid ORDER BY name")->result();
		$data["positions"] = $this->db->query("SELECT id,title as name FROM positions WHERE company_id = $cid ORDER BY name")->result();
		$data["sections"] = $this->db->query("SELECT id,title as name FROM sections WHERE company_id = $cid ORDER BY name")->result();
		// $this->db->select('employees.id,concat(special_id, " - ",employees.first_name) as name, branch_id, position_id,employees.department_id')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null AND employee_status = "active" ')->where('roles.exclude_from_system', 'no');
		// if ($permissions_level == "Outlet") {
		// $this->db->where('branch_id', $bid);
		// }
		// $data["active_employees_dropdown"] = $this->db->query("SELECT employees.id, branch_id, position_id, department_id, special_id,first_name,last_name,resignation_date,termination_date,employee_status FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE employees.deleted_at IS NULL AND employees.employee_status = 'active' AND roles.exclude_from_system = 'no' AND employees.company_id = $cid ORDER BY special_id")->result();
		// $data["resigned_employees_dropdown"] = $this->db->query("SELECT employees.id, branch_id, position_id, department_id, special_id,first_name,last_name,resignation_date,termination_date,employee_status FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE employees.deleted_at IS NULL AND employees.employee_status != 'active' AND IF(employees.employee_status = 'resigned' AND employees.resignation_date IS NOT NULL, Month(employees.resignation_date) = MONTH(CURRENT_TIMESTAMP) AND YEAR(employees.resignation_date) = YEAR(CURRENT_TIMESTAMP), 'NULL') AND roles.exclude_from_system = 'no' AND employees.company_id = $cid ORDER BY special_id")->result();
		// $data["terminated_employees_dropdown"] = $this->db->query("SELECT employees.id, branch_id, position_id, department_id, special_id,first_name,last_name,resignation_date,termination_date,employee_status FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE employees.deleted_at IS NULL AND employees.employee_status != 'active' AND IF(employees.employee_status = 'terminated' AND employees.termination_date IS NOT NULL, Month(employees.termination_date) = MONTH(CURRENT_TIMESTAMP) AND YEAR(employees.termination_date) = YEAR(CURRENT_TIMESTAMP), 'NULL') AND roles.exclude_from_system = 'no' AND employees.company_id = $cid ORDER BY special_id")->result();

		$data["employees"] = $this->db->query("SELECT employees.id, branch_id, position_id, section_id, department_id, special_id, first_name, last_name, resignation_date, termination_date, employee_status FROM employees INNER JOIN roles ON employees.role_id = roles.id 
			WHERE employees.deleted_at IS NULL AND roles.exclude_from_system = 'no' AND employees.company_id = $cid $employees_branch_where
			AND (
				employees.employee_status = 'active' 
				OR (employees.employee_status = 'terminated' AND employees.termination_date IS NOT NULL AND employees.termination_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
				OR (employees.employee_status = 'resigned' AND employees.resignation_date IS NOT NULL AND employees.resignation_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
			)
			ORDER BY special_id")->result();
		$data['groups'] = $this->db->query("SELECT * from employee_groups WHERE company_id = " . $cid . "")->result();
		// print_r($data['groups']);die;
		// $data["employees"] = array_merge($data["active_employees_dropdown"], $data["resigned_employees_dropdown"], $data["terminated_employees_dropdown"]);
		// if ($permissions_level == "Outlet") {
		// 	$this->db->where('branch_id', $bid);
		// }
		// echo count($data["employees_dropdown"]);die;

		// $data["employees"] = $this->db->order_by('special_id', 'asc')->get()->result();
		// echo count($data["employees"]);die;

		// allowances for BMI
		$data["allowances"] = [
			["key" => "ta", "value" => "TA"],
			["key" => "ma", "value" => "MA"],
			["key" => "ca", "value" => "CA"],
			["key" => "spa", "value" => "SPA"],
			["key" => "aca", "value" => "ACA"],
			["key" => "fl", "value" => "FL Inc"],
			["key" => "cw", "value" => "C/wash"],
			["key" => "mo", "value" => "M/ope"],
			["key" => "shift1", "value" => "Shift 1"],
			["key" => "shift2", "value" => "Shift 2"],
			["key" => "shift3", "value" => "Shift 3"],
		];

		echo json_encode($data);
	}

	public function index()
	{
		if (!is_page_permitted('exports')) {
            redirect_if_not_permitted();
        }

		$data['pageTitle'] = "Export Summary";
		$data['active_menu'] = "exports";
		$this->load->view('header', $data);
		$data["menus"] = get_menus();
		$this->load->view('sidebar', $data);
		$current_user = get_user();
		$data['company_id'] = $current_user["company_id"];

		$dates = getStartEndDatesWithOneMonthGap($current_user['start_day']);
		$data['from_f'] = $dates[0]->format('d/m/Y');
		$data['to_f'] = $dates[1]->format('d/m/Y');

		$data['ot_from_f'] = date('21/m/Y', strtotime($dates[0]->format('Y-m-d') . ' -1 month'));
		$data['ot_to_f'] = date('20/m/Y', strtotime($dates[0]->format('Y-m-d')));

		$this->load->view('export_summary', $data);
		$this->load->view('footer', $data);
	}

	public function summary_pdf()
	{
		$this->load->library("excel");

		$current_user = get_user();

		$public_holidays_all = get_public_holidays_all();
		if ($_POST["type"] == "daily_time_card") {
			$_POST["to"] = $_POST["from"];
		}

		$date1 = DateTime::createFromFormat('d/m/Y', $_POST['from']);
		$date2 = DateTime::createFromFormat('d/m/Y', $_POST['to']);

		$ot_date1 = DateTime::createFromFormat('d/m/Y', $_POST['ot_from']);
		$ot_date2 = DateTime::createFromFormat('d/m/Y', $_POST['ot_to']);

		$month_name_for_merit_report = $date1->format('F Y');
		$first_day_name = $date1->format('l');
		$time_card_date = $date1->format('mdY');

		if ($_POST["type"] === "weekly_ot") {
			$date2 = add_days_to_date($date1, 6);

			$first_day = $date1->format('Y-m-d');
			$last_day = $date2->format('Y-m-d');
		} else {
			if ($date1 > $date2) {
				$first_day = $date2->format('Y-m-d');
				$last_day = $date1->format('Y-m-d');
				$month_name = $date2->format('F');
			} else {
				$first_day = $date1->format('Y-m-d');
				$last_day = $date2->format('Y-m-d');
				$month_name = $date1->format('F');
			}
			if (empty($ot_date1) || empty($ot_date2)) {
				$ot_first_day = date('Y-m-21', strtotime($first_day . ' -1 month'));
				$ot_last_day = date('Y-m-20', strtotime($first_day));
			} else if ($ot_date1 > $ot_date2) {
				$ot_first_day = $ot_date2->format('Y-m-d');
				$ot_last_day = $ot_date1->format('Y-m-d');
			} else {
				$ot_first_day = $ot_date1->format('Y-m-d');
				$ot_last_day = $ot_date2->format('Y-m-d');
			}
		}

		$first_day_original_format = $first_day;
		$last_day_original_format = $last_day;

		$files = array();
		$branch_id = array();
		$department_id = array();
		$position_id = array();
		$employee_id = array();
		$exclude_employees = array();
		if (isset($_POST["branch"])) {
			$branch_id = $_POST["branch"];
		}
		if (isset($_POST["department"])) {
			$department_id = $_POST["department"];
		}
		if (isset($_POST["position"])) {
			$position_id = $_POST["position"];
		}
		if (isset($_POST["section"])) {
			$section_id = $_POST["section"];
		}
		if (isset($_POST["employee"])) {
			$employee_id = $_POST["employee"];
		}
		if (isset($_POST["exclude_employee"])) {
			$exclude_employees = $_POST["exclude_employee"];
		}
		// print_R($employee_id);die;
		$permissions_level = $current_user["permissions_level"];

		if ($permissions_level == "Outlet") {
			$branch_id = array($current_user["branch_id"]);
		}


		$branch_name = "needs to be fixed";
		if ($branch_id) {
			$branch_name = $this->db->select('group_concat(name) as name')->from('branches')->where_in('id', $branch_id)->get()->row()->name;
		} else {
			$branch_name = "All";
		}
		if (!empty($branch_id)) {
			$this->db->where_in('id', $branch_id);
		}
		$selected_branches = $this->db->get('branches')->result();
		$selected_branches = count($selected_branches);

		$this->db->where('company_id', $current_user["company_id"]);
		$all_branches = $this->db->get('branches')->result();
		$all_branches = count($all_branches);

		// echo $selected_branches.'  /  '.$all_branches;die;

		$cid = $current_user["company_id"];
		$bid = $current_user["branch_id"];
		$c_name = $current_user["company_name"];


		$company_working_hours = get_company_working_hours($cid);

		$company_ot_settings = get_company_ot_settings($cid);
		$company_early_ot_settings = get_company_early_ot_settings($cid);

		$employees_from_group = array();
		$excluded_employees_from_group = array();

		if ($employee_id) {
			$employee_group_arr = array();
			foreach ($employee_id as $key) {
				if (strpos($key, '-') !== false) {
					$arr = explode("-", $key);
					$key1 = $arr[0];
					array_push($employee_group_arr, $key1);
				} else {
					array_push($employees_from_group, $key);
				}
			}
			foreach ($employee_group_arr as $group_id) {
				$this->db->where('group_id', $group_id);
				$results = $this->db->get('employee_groups_relation')->result();
				foreach ($results as $result) {
					$employees_from_group[] = $result->employee_id;
				}
			}
			$employees_from_group = array_unique($employees_from_group);
		}

		if ($exclude_employees) {
			$employee_group_arr = array();
			foreach ($exclude_employees as $key) {
				if (strpos($key, '-') !== false) {
					$arr = explode("-", $key);
					$key1 = $arr[0];
					array_push($employee_group_arr, $key1);
				} else {
					$excluded_employees_from_group[] = $key;
				}
			}
			foreach ($employee_group_arr as $group_id) {
				$this->db->where('group_id', $group_id);
				$results = $this->db->get('employee_groups_relation')->result();
				foreach ($results as $result) {
					$excluded_employees_from_group[] = $result->employee_id;
				}
			}
			$excluded_employees_from_group = array_unique($excluded_employees_from_group);
		}

		$this->db->select('employees.id,employees.first_name,special_id,employees.is_daily_waged, d.name as department,s.title as section, p.title as position,employees.branch_id,b.name as branch,is_ot,is_early_ot,inc_late_in,inc_late_break, inc_early_out, inc_short_hours,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,round_by_exact_hour,different_first_hour_rounding,worked_hours_ot_rd,worked_hours_ot_ph,deduct_hour_ot_rd,deduct_hour_ot_ph,worked_hours_ot_off,deduct_hour_ot_off,ignore_breaks_after_endtime,void_lateness_time_if_less_than,deduct_from_ot,deduct_from_ot_single,deduction_date,ta_rate,ma_rate,ca_rate,spa_rate,aca_rate,aa_rate,nsa_rate,fl_rate,cw_rate,mo_rate,shift1_rate,shift2_rate,shift3_rate,food_rate,basic_wage,ot_group,special_incentive, att_all_code, att_all_desc, att_all_amount, is_att_all, mi_mo_rate, lateness_deduction_99, lateness_deduction_100, rest_day_entitlement, is_shift_hours')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('branches b', 'b.id = employees.branch_id', 'left')->join("sections s", "employees.section_id = s.id", "left")->join('positions p', 'p.id = employees.position_id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null')->where('roles.exclude_from_system', 'no')
			->where("(employees.employee_status = 'active' 
					OR (employees.employee_status = 'terminated' AND employees.termination_date IS NOT NULL AND employees.termination_date >= DATE_FORMAT('$first_day', '%Y-%m-01'))
					OR (employees.employee_status = 'resigned' AND employees.resignation_date IS NOT NULL AND employees.resignation_date >= DATE_FORMAT('$first_day', '%Y-%m-01'))
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
		if ($section_id) {
			$this->db->where_in('employees.section_id', $section_id);
		}
		if ($employees_from_group) {
			$this->db->where_in('employees.id', $employees_from_group);
		}
		if ($excluded_employees_from_group) {
			$this->db->where_not_in('employees.id', $excluded_employees_from_group);
		}

		$this->db->order_by('special_id', 'asc');

		$employees = $this->db->get()->result();

		$employees_ids = array();
		foreach ($employees as $emp) {
			$employees_ids[] = $emp->id;
		}
		$chunkedEmployeeIds = array_chunk($employees_ids, 20);
		$branch_rest_days = $this->db->select('id,rest_days,off_days')->from('branches')->where('company_id', $cid)->get()->result();


		// $days_settings = $this->db->select('from_hour,to_hour,days')->from('days_settings')->where('company_id', $cid)->get()->result();
		if ($permissions_level == "Outlet") {
			$shifts = $this->db->select('id')->from('shifts')->where('branch_id', $bid)->where('is_leave', 'no')->get()->result();
		} else {
			$shifts = $this->db->select('id')->from('shifts')->where('company_id', $cid)->where('is_leave', 'no')->get()->result();
		}

		$shift_ids = array(0);
		foreach ($shifts as $s) {
			$shift_ids[] = $s->id;
		}

		$combined_first_day = $first_day;

		if ($_POST['type'] == "gni01_payroll_process") {
			$combined_first_day = $ot_first_day;
		}

		$approved_ot_list = get_approved_ot_list($shift_ids, $combined_first_day, $last_day);
		if ($_POST['type'] === "daily_time_card" && $_POST['file_type'] === 'pdf') {
			$isDailyTimeCardPdf = true;
		} else {
			flushLoadingBar(true);
			$isDailyTimeCardPdf = false;
		}
		// Preloading data, fetching the result lists by chunks to timeout issues
		$result_list = [];
		$result_list_overnight = [];
		$chunksCount = count($chunkedEmployeeIds);
		foreach ($chunkedEmployeeIds as $i => $chunk) {
			$result_list = array_merge($result_list, get_result_list($chunk, $combined_first_day, $last_day));
			$result_list_overnight = array_merge($result_list_overnight, get_result_list_overnight($chunk, $combined_first_day, $last_day));
			if (!$isDailyTimeCardPdf) {
				$percentage = floor((($i + 1) / $chunksCount) * 100);
				echo "<script>$('#preparing-data .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();
			}
		}

		if ($_POST['type'] == "accounts") {
			$all_data = array();
			$employee_count = count($employees);

			foreach ($employees as $i => $emp) {
				$data = calculate_summary_data($emp->id, $first_day, $last_day, "accounts", $emp, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days);
				$percentage = floor((($i + 1) / count($employees)) * 100);

				echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();

				$all_data[] = $data;
				$data = array();
				$dates = array();
			}

			$date = DateTime::createFromFormat('Y-m-d', $first_day);
			$first_day = $date->format('d M, Y');
			$date = DateTime::createFromFormat('Y-m-d', $last_day);
			$last_day = $date->format('d M, Y');

			$style = array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				)
			);


			$object = new PHPExcel();

			$object->setActiveSheetIndex(0);
			$object->getDefaultStyle()->applyFromArray($style);

			$header_rows = [
				['', 'The official work day of the month exclude Rest Day &  Holiday', 'The total number of day an employee has worked in Working Day', 'The total number of day an employee has absent', 'The total number of day an employee has taken leaves (exclude unpaid leaves)', 'The total number of day an employee has taken unpaid leaves', 'The total number of day an employee has worked in Rest Day', 'The total number of day an employee has worked in Holiday', 'The total number of overtime hour an employee has worked in Working Day', 'The total number of overtime hour an employee has worked in Rest Day', 'The total number of overtime hour an employee has worked in Holiday', 'The total number of Lateness count', 'The total number of Lateness hour', 'The total number of Early Out count', 'The total number of Early Out hour',],
				['NvarChar(20)', 'Decimal(9, 1)', 'Decimal(9, 1)', 'Decimal(9, 1)', 'Decimal(9, 1)', 'Decimal(9, 1)', 'Decimal(9, 1)', 'Decimal(9, 1)', 'Decimal(9, 5)', 'Decimal(9, 5)', 'Decimal(9, 5)', 'Integer', 'Decimal(9, 2)', 'Integer', 'Decimal(9, 2)'],
				["Employee Code", "Working Days", "Worked Days", "Absent Days", "Leave Days", "Unpaid Leave Days", "Worked Rest Days", "Worked Holidays", "OT", "OT For Rest Days", "OT For Holidays", "Lateness Count", "Lateness Time", "Early Out Count", "Early Out Time"],
			];

			foreach ($header_rows as $key => $row) {
				$column = 0;
				foreach ($row as $field) {
					$object->getActiveSheet()->setCellValueByColumnAndRow($column, $key + 1, $field);
					if ($key === 2)
						$object->getActiveSheet()->getStyleByColumnAndRow($column, $key + 1)->getFont()->setBold(true);
					else
						$object->getActiveSheet()->getStyleByColumnAndRow($column, $key + 1)->getAlignment()->setWrapText(true);

					$column++;
				}
			}

			$row = 4;

			foreach ($all_data as $i => $r) {
				$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $r["employee"]->special_id);
				$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $r["working_days"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r["worked_days"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $r["absent_days"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $r["paid_leaves"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $r["unpaid_leaves"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $r["worked_rest_days"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $r["worked_holidays"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $r["month_overtime_deducted"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(9, $row, $r["month_overtime_rd"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(10, $row, $r["month_overtime_ph"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(11, $row, $r["late_count"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(12, $row, $r["lateness_time_deducted"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(13, $row, $r["total_early_count"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(14, $row, $r["total_early"]);
				$row++;

				$percentage = floor((($i + 1) / $employee_count) * 100);

				echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();
			}

			foreach (range('A', 'Z') as $columnID) {
				$object->getActiveSheet()->getColumnDimension($columnID)
					->setWidth(20);
			}

			// $file_name = "$c_name ($branch_name) AutoCount Payroll - $first_day to $last_day " . time();
			$file_name = "($branch_name) AutoCount Payroll - $first_day to $last_day " . time() . '.xlsx';

			$object_writer = new PHPExcel_Writer_Excel2007($object);
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="' . $file_name . '"');
			$new_file = "uploads/summary/" . $file_name;
			$object_writer->save($new_file);

			echo '</br> <br> <b>Export Completed</b> </br>';

			$path = base_url() . $new_file;

			echo "</br> <center><div style='width:40%'><a href='$path' download='$file_name'><button style='' class='btn btn-primary btn-block'>Download File</button></a></div></center>";

			echo "</br> <center><div style='width:40%'><a href='" . base_url() . "exports'><button style='margin-bottom	:40px' class='btn btn-primary btn-block'>Back to Export Summary</button></a></div></center>";
			echo '</div>';

			insert_log("Simple", ["action" => "Exported,Accounts Data"]);
		} else if ($_POST['type'] == "short") {
			$all_data = array();
			$employee_count = count($employees);
			foreach ($employees as $i => $emp) {
				$data = calculate_summary_data($emp->id, $first_day, $last_day, "short", $emp, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days);
				$percentage = floor((($i + 1) / $employee_count) * 100);

				echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();

				$all_data[] = $data;

				$data = array();
				$dates = array();
			}

			$date = DateTime::createFromFormat('Y-m-d', $first_day);
			$data['from_f'] = $date->format('d/m/Y');
			$date = DateTime::createFromFormat('Y-m-d', $last_day);
			$data['to_f'] = $date->format('d/m/Y');

			$data["all_data"] = $all_data;
			$data["branch_name"] = $branch_name;

			if ($_POST['file_type'] == "pdf") {
				$html2 = $this->load->view('short_summary_pdf', $data, true);
				$file_name = "($branch_name) Short Summary - $first_day to $last_day " . time() . ".pdf";

				$this->dompdf->reset();
				$this->dompdf->loadHtml($html2);
				$this->dompdf->setPaper("A4", "landscape");
				$this->dompdf->render();

				$output = $this->dompdf->output();
				$new_file = "uploads/summary/" . $file_name;
				file_put_contents($new_file, $output);

				$path = "uploads/summary/" . $file_name;

				echo "<script>$('#loading2 .progress-bar').css('width', '" . 100 . "%').attr('aria-valuenow', " . 100 . ").html('" . 100 . "%');</script>";


				header('Content-Type: application/pdf');
				header("Content-Transfer-Encoding: Binary");
				header("Content-disposition: attachment; filename=" . $file_name);
				// readfile($path);
			} else {
				//excel here
				$style = array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					)
				);

				$object = new PHPExcel();

				$object->setActiveSheetIndex(0);
				$object->getDefaultStyle()->applyFromArray($style);
				$name_columns = array("Branch", "From", "To", "Generated at", "Generated by");
				$table_columns = array("Name",	"Employee ID",	"Working Days",	"Worked Days",	"Absent Days",	"Leave Days",	"Unpaid Leave Days",	"Worked Rest Days",	"Worked Holidays",	"OT",	"OT (PHx2)", "OT (PHx3)",	"OT (RD)", "OT (OFF)",	"Lateness Count",	"Lateness Time",	"Trips A",	"Trips B");

				$column = 0;

				foreach ($name_columns as $field) {
					$object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
					$object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

					$column++;
				}

				$object->getActiveSheet()->setCellValueByColumnAndRow(0, 2, $data["branch_name"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(1, 2, $data["from_f"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(2, 2, $data["to_f"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(3, 2, date("d/m/Y H:i:s"));
				$object->getActiveSheet()->setCellValueByColumnAndRow(4, 2, $current_user["first_name"]);

				$column = 0;

				foreach ($table_columns as $field) {
					$object->getActiveSheet()->setCellValueByColumnAndRow($column, 4, $field);
					$object->getActiveSheet()->getStyleByColumnAndRow($column, 4)->getFont()->setBold(true);

					$column++;
				}

				$row = 5;

				foreach ($all_data as $i => $r) {
					$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $r["employee"]->first_name);
					$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $r["employee"]->special_id);
					$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r["working_days"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $r["worked_days"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $r["absent_days"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $r["paid_leaves"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $r["unpaid_leaves"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $r["worked_rest_days"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $r["worked_holidays"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(9, $row, $r["month_overtime_deducted"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(10, $row, $r["month_overtime_ph_x2"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(11, $row, $r["month_overtime_ph_x3"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(12, $row, $r["month_overtime_rd"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(13, $row, $r["month_overtime_off"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(14, $row, $r["late_count"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(15, $row, $r["lateness_time_deducted"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(16, $row, $r["total_trip_a"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(17, $row, $r["total_trip_b"]);
					$row++;

					$percentage = floor((($i + 1) / $employee_count) * 100);

					echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
					ob_flush();
					flush();
				}

				foreach (range('A', 'R') as $columnID) {
					$object->getActiveSheet()->getColumnDimension($columnID)
						->setAutoSize(true);
				}

				if ($_POST['file_type'] == 'xlsx') {
					$file_name = "($branch_name) Short Summary - $first_day to $last_day " . time() . ".xlsx";

					$object_writer = new PHPExcel_Writer_Excel2007($object);

					header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					header('Content-Disposition: attachment;filename="' . $file_name . '"');
					$new_file = "uploads/summary/" . $file_name;
					$object_writer->save($new_file);
					//excel ends
				} else {
					$file_name = "($branch_name) Short Summary - $first_day to $last_day " . time() . ".xls";

					$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="' . $file_name . '"');
					$new_file = "uploads/summary/" . $file_name;
					$object_writer->save($new_file);
					//excel ends
				}
			}

			echo '</br> <br> <b>Export Completed</b> </br>';

			$path = base_url() . $new_file;

			echo "</br> <center><div style='width:40%'><a href='$path' download='$file_name'><button style='' class='btn btn-primary btn-block'>Download File</button></a></div></center>";

			echo "</br> <center><div style='width:40%'><a href='" . base_url() . "exports'><button style='margin-bottom	:40px' class='btn btn-primary btn-block'>Back to Export Summary</button></a></div></center>";
			echo '</div>';
			insert_log("Simple", ["action" => "Exported,Short Data"]);
		} else if ($_POST['type'] == "sql") {
			$unpaid_leaves_absent_days = [];
			$worked_rest_days_array = [];
			$worked_off_days_array = [];
			$worked_holidays_array = [];
			$paid_leaves_array = [];
			$daily_ot_array = [];
			$daily_late_array = [];

			$all_data = array();
			$employee_count = count($employees);
			foreach ($employees as $i => $emp) {
				$clockings_news = [];
				$clockings_news_overnight = [];
				if ($cid == 196) {
					$interval_minutes = get_interval_minutes($cid);
					$clockings_news = $this->db->select('id,employee_id,type,shift_id,date_format(datetime,"%H:%i") as clock_time,date_format(datetime, "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false)->from('clockings_news')->where('date(datetime) >=', $first_day)->where('date(datetime) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();
					$clockings_news_overnight = $this->db->select('id,employee_id,type,shift_id, date_format(datetime,"%H:%i") as clock_time,date_format(date_sub(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false)->from('clockings_news')->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) >=', $first_day)->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();
				}
				$data = calculate_summary_data($emp->id, $first_day, $last_day, "sql", $emp, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days, null, $worked_rest_days_array, $worked_off_days_array, $worked_holidays_array, $unpaid_leaves_absent_days, $clockings_news, $clockings_news_overnight, $paid_leaves_array, $daily_ot_array, $daily_late_array);
				$percentage = floor((($i + 1) / $employee_count) * 100);

				echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();

				$all_data[] = $data;

				$data = array();
				$dates = array();
			}

			ksort($unpaid_leaves_absent_days);
			ksort($paid_leaves_array);
			ksort($daily_ot_array);
			ksort($daily_late_array);

			$date = DateTime::createFromFormat('Y-m-d', $first_day);
			$first_day = $date->format('d M, Y');
			$date = DateTime::createFromFormat('Y-m-d', $last_day);
			$last_day = $date->format('d M, Y');

			$style = array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				)
			);

			$files_count = 16;

			$files = [];

			$payroll_csv_date = date('t/m/Y', strtotime($first_day_original_format));

			$files[] = $this->pendingOvertimeLogFile($style, $all_data, $date2, $branch_name, $first_day, $last_day);
			$this->changeLoadingBar2(floor((1 / $files_count) * 100));

			$files[] = $this->pendingUnpaidLeavesLogFile($style, $unpaid_leaves_absent_days, $cid, $date2, $branch_name, $first_day, $last_day);
			$this->changeLoadingBar2(floor((2 / $files_count) * 100));

			if ($cid == 153) {
				$files[] = $this->pendingAbsentLogFile($style, $unpaid_leaves_absent_days, $date2, $branch_name, $first_day, $last_day);
				$this->changeLoadingBar2(floor((3 / $files_count) * 100));

				$files[] = $this->pendingDailyOTLogFile($style, $daily_ot_array, $date2, $branch_name, $first_day, $last_day);
				$this->changeLoadingBar2(floor((4 / $files_count) * 100));

				$files[] = $this->pendingDailyLateLogFile($style, $daily_late_array, $date2, $branch_name, $first_day, $last_day);
				$this->changeLoadingBar2(floor((5 / $files_count) * 100));
			}

			$files[] = $this->pendingWorkedRestDaysLogFile($style, $cid, $all_data, $worked_rest_days_array, $date2, $branch_name, $first_day, $last_day);
			$this->changeLoadingBar2(floor((6 / $files_count) * 100));

			$files[] = $this->pendingWorkedOffDaysLogFile($style, $worked_off_days_array, $date2, $branch_name, $first_day, $last_day);
			$this->changeLoadingBar2(floor((7 / $files_count) * 100));

			$files[] = $this->pendingWorkedPublicHolidaysLogFile($style, $worked_holidays_array, $date2, $branch_name, $first_day, $last_day);
			$this->changeLoadingBar2(floor((8 / $files_count) * 100));

			$files[] = $this->pendingDailyWageLogFile($style, $all_data, $date2, $branch_name, $first_day, $last_day);
			$this->changeLoadingBar2(floor((9 / $files_count) * 100));

			$files[] = $this->pendingEarlyLateLogFile($style, $all_data, $date2, $branch_name, $first_day, $last_day);
			$this->changeLoadingBar2(floor((10 / $files_count) * 100));

			$files[] = $this->pendingDeductionLogFile($style, $all_data, $cid, $date2, $branch_name, $first_day, $last_day);
			$this->changeLoadingBar2(floor((11 / $files_count) * 100));

			$files[] = $this->pendingShiftWorkedHoursFile($style, $all_data, $date2, $branch_name, $first_day, $last_day);
			$this->changeLoadingBar2(floor((12 / $files_count) * 100));

			$files[] = $this->pendingWorkedHoursFile($style, $all_data, $date2, $branch_name, $first_day, $last_day);
			$this->changeLoadingBar2(floor((13 / $files_count) * 100));

			if (in_array($cid, companies_allowed_for_leave_application())) {
				$files[] = $this->pendingLeaveApplicationLogFile($style, $paid_leaves_array, $branch_name, $first_day, $last_day);
				$this->changeLoadingBar2(floor((14 / $files_count) * 100));
			}

			if (in_array($cid, companies_allowed_for_att_all()) || $cid == 215 || $cid == 152 || $cid == 206) {
				$files[] = $this->pendingAllowanceLogFile($style, $all_data, $cid, $date2, $branch_name, $first_day, $last_day);
				$this->changeLoadingBar2(floor((15 / $files_count) * 100));
			}

			if (in_array($cid, companies_allowed_for_allowance_report())) {
				$allowances = get_allowances_for_report($employees_ids, $first_day_original_format, $last_day_original_format);
				$files[] = $this->pendingAllowanceReportLogFile($style, $allowances, $date2, $branch_name, $first_day, $last_day);
			}

			$this->changeLoadingBar2(floor((16 / $files_count) * 100));

			$file_name = "($branch_name) SQL Payroll - $first_day to $last_day " . time() . ".zip";

			foreach ($files as $file) {
				$this->zip->read_file(FCPATH .  $file);
				unlink($file);
			}

			$this->zip->archive(FCPATH . 'uploads/summary/' . $file_name);

			$path = base_url() . "uploads/summary/" . $file_name;

			foreach ($files as $file) {
				$this->zip->read_file(FCPATH .  $file);
				unlink($file);
			}

			echo '</br> <br> <b>Export Completed</b> </br>';

			$this->zip->archive(FCPATH . 'uploads/summary/' . $file_name);

			$path = base_url() . "uploads/summary/" . $file_name;

			echo "</br> <center><div style='width:40%'><a href='$path'><button style='' class='btn btn-primary btn-block'>Download File</button></a></div></center>";

			echo "</br> <center><div style='width:40%'><a href='" . base_url() . "exports'><button style='margin-bottom	:40px' class='btn btn-primary btn-block'>Back to Export Summary</button></a></div></center>";
			echo '</div>';

			insert_log("Simple", ["action" => "Exported,SQL Data"]);
			redirect($path);
		} else if ($_POST["type"] === "weekly_ot") {
			$employee_count = count($employees);
			foreach ($employees as $i => $emp) {
				$data = calculate_summary_data(
					$emp->id,
					$first_day,
					$last_day,
					"summary",
					$emp,
					$result_list,
					$result_list_overnight,
					$company_working_hours,
					false,
					$company_ot_settings,
					$company_early_ot_settings,
					$approved_ot_list,
					$branch_rest_days,
					$cid
				);

				$percentage = floor((($i + 1) / $employee_count) * 100);

				echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();


				$date = DateTime::createFromFormat('Y-m-d', $first_day);
				$data['from_f'] = $date->format('d/m/Y');
				$date = DateTime::createFromFormat('Y-m-d', $last_day);
				$data['to_f'] = $date->format('d/m/Y');



				if ($_POST['file_type'] == 'pdf') {
					$html = $this->load->view('weekly_ot/view_pdf', $data, true);

					$this->dompdf->reset();
					$this->dompdf->loadHtml($html);
					$this->dompdf->setPaper("A4", "landscape");
					$this->dompdf->render();


					$output = $this->dompdf->output();

					$file_name = str_replace("/", "-", $data['employee']->special_id) . " - " . str_replace("/", "-", $data['employee']->first_name) . " " . $first_day . " to " . $last_day . " - Weekly OT Summary.pdf";
					$new_file = "uploads/summary/" . $file_name;

					file_put_contents($new_file, $output);
					$percentage = floor((($i + 1) / $employee_count) * 100);

					echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
					ob_flush();
					flush();
				} else {

					$style = array(
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						)
					);


					$object = new PHPExcel();

					$object->setActiveSheetIndex(0);
					$object->getDefaultStyle()->applyFromArray($style);

					$table_columns = array("Date", "Shift", "Shift Work Hours", "Actual Work Hours", "OT", "OT (M)", "OT (PH)", "OT (RD)");

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
					$object->getActiveSheet()->setCellValueByColumnAndRow(7, 2, $current_user["first_name"]);

					$column = 0;

					foreach ($table_columns as $field) {
						$object->getActiveSheet()->setCellValueByColumnAndRow($column, 4, $field);
						$object->getActiveSheet()->getStyleByColumnAndRow($column, 4)->getFont()->setBold(true);

						$column++;
					}

					$row = 5;
					foreach ($data['dates'] as $d) {
						$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $d->date_string);
						$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $d->shift_name);
						$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $d->shift_hours);
						$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $d->work_hours);
						$ot_value = create_ot_rich_text($d);
						if (!in_array($d->date, $data["public_holidays"]) && !in_array($d->day_name, $data["rest_days"]) && $d->is_shift == 'true' && !$d->is_replaced_ph) {
							$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $ot_value);
						}

						if (in_array($d->date, $data["public_holidays"]) || $d->is_replaced_ph) {
							$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $ot_value);
						}

						if (!in_array($d->date, $data["public_holidays"]) && (in_array($d->day_name, $data["rest_days"]) || $d->is_shift == 'false')) {
							$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $ot_value);
						}
						$row++;
					}

					// total row
					$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, "Total");
					$object->getActiveSheet()->mergeCellsByColumnAndRow(0, $row, 1, $row);
					$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $data["total_shift_hours"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $data["work"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $data["month_overtime_deducted"]);
					$object->getActiveSheet()->mergeCellsByColumnAndRow(4, $row, 5, $row);
					$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $data["month_overtime_ph"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $data["month_overtime_rd"]);

					foreach (range('A', 'S') as $columnID) {
						$object->getActiveSheet()->getColumnDimension($columnID)
							->setAutoSize(true);
					}

					if ($_POST['file_type'] == 'excel') {
						$file_name = str_replace("/", "-", $data['employee']->special_id) . " - " . str_replace("/", "-", $data['employee']->first_name) . " " . $first_day . " to " . $last_day . " - Weekly OT Summary.xls";
						$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
						header('Content-Type: application/vnd.ms-excel');
						header('Content-Disposition: attachment;filename="' . $file_name . '"');
						$new_file = "uploads/summary/" . $file_name;
						$object_writer->save($new_file);
					} else {
						$file_name = str_replace("/", "-", $data['employee']->special_id) . " - " . str_replace("/", "-", $data['employee']->first_name) . " " . $first_day . " to " . $last_day . " - Weekly OT Summary.xlsx";
						$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
						header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
						header('Content-Disposition: attachment;filename="' . $file_name . '"');
						$new_file = "uploads/summary/" . $file_name;
						$object_writer->save($new_file);
					}
				}

				$percentage = floor((($i + 1) / $employee_count) * 100);

				echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();

				$files[] = $new_file;

				$data = array();
				$dates = array();
			}

			if (count($files) > 1) {
				$file_name = "($branch_name) Weekly OT Summary - $first_day to $last_day " . time() . ".zip";
				foreach ($files as $file) {
					$this->zip->read_file(FCPATH .  $file);
					unlink($file);
				}
				$this->zip->archive(FCPATH . 'uploads/summary/' . $file_name);
			}

			$path = base_url() . "uploads/summary/" . $file_name;

			echo '</br> <br> <b>Export Completed</b> </br>';

			echo "</br> <center><div style='width:40%'><a href='$path' download='$file_name'><button style='' class='btn btn-primary btn-block'>Download File</button></a></div></center>";

			echo "</br> <center><div style='width:40%'><a href='" . base_url() . "exports'><button style='margin-bottom	:40px' class='btn btn-primary btn-block'>Back to Export Summary</button></a></div></center>";
			echo '</div>';

			insert_log("Simple", ["action" => "Exported,Weekly OT Data"]);
			redirect($path);
		} else if ($_POST["type"] === "weekly_ot_reports") {
			$all_data = array();
			$employee_count = count($employees);
			foreach ($employees as $i => $emp) {
				$data = calculate_summary_data($emp->id, $first_day, $last_day, "sql weekly ot report", $emp, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days, null, $worked_rest_days_array, $worked_off_days_array, $worked_holidays_array, $unpaid_leaves_absent_days);

				$percentage = floor((($i + 1) / $employee_count) * 100);

				echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();

				$all_data[] = $data;

				$data = array();
			}

			$date = DateTime::createFromFormat('Y-m-d', $first_day);
			$first_day = $date->format('d M, Y');
			$date = DateTime::createFromFormat('Y-m-d', $last_day);
			$last_day = $date->format('d M, Y');

			$style = array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				)
			);

			$files = [];

			// OT CSV Object
			$otObject = new PHPExcel();
			$otObject->setActiveSheetIndex(0);
			$otObject->getDefaultStyle()->applyFromArray($style);
			$table_columns = array("Date", "Employee", "Code", "Description", "Work Unit", "Rate");
			$column = 0;
			foreach ($table_columns as $field) {
				$otObject->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
				$otObject->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);
				$column++;
			}
			$ot_row = 2;

			// OT RD CSV Object
			$otRdObject = new PHPExcel();
			$otRdObject->setActiveSheetIndex(0);
			$otRdObject->getDefaultStyle()->applyFromArray($style);
			$table_columns = array("Date", "Employee", "Code", "Description", "Work Unit", "Rate");
			$column = 0;
			foreach ($table_columns as $field) {
				$otRdObject->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
				$otRdObject->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);
				$column++;
			}
			$ot_rd_row = 2;

			// OT PH CSV Object
			$otPhObject = new PHPExcel();
			$otPhObject->setActiveSheetIndex(0);
			$otPhObject->getDefaultStyle()->applyFromArray($style);
			$table_columns = array("Date", "Employee", "Code", "Description", "Work Unit", "Rate");
			$column = 0;
			foreach ($table_columns as $field) {
				$otPhObject->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
				$otPhObject->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);
				$column++;
			}
			$ot_ph_row = 2;

			$payroll_csv_date = date('t/m/Y', strtotime($first_day_original_format));
			foreach ($all_data as  $i => $r) {
				$sql_data_of_emp = get_sql_data($r["employee"]->branch_id);
				if ($r['month_overtime_deducted'] != 0) {
					$otObject->getActiveSheet()->setCellValueByColumnAndRow(0, $ot_row, $payroll_csv_date);
					$otObject->getActiveSheet()->setCellValueByColumnAndRow(1, $ot_row, $r["employee"]->special_id);
					$otObject->getActiveSheet()->setCellValueByColumnAndRow(2, $ot_row, $sql_data_of_emp["w_ot_code"]);
					$otObject->getActiveSheet()->setCellValueByColumnAndRow(3, $ot_row, $sql_data_of_emp["w_ot_description"]);
					$otObject->getActiveSheet()->setCellValueByColumnAndRow(4, $ot_row, $r["month_overtime_deducted"]);
					$otObject->getActiveSheet()->setCellValueByColumnAndRow(5, $ot_row, $sql_data_of_emp["w_ot_rate"]);
					$ot_row++;
				}
				if ($r['month_overtime_rd'] != 0) {
					$otRdObject->getActiveSheet()->setCellValueByColumnAndRow(0, $ot_rd_row, $payroll_csv_date);
					$otRdObject->getActiveSheet()->setCellValueByColumnAndRow(1, $ot_rd_row, $r["employee"]->special_id);
					$otRdObject->getActiveSheet()->setCellValueByColumnAndRow(2, $ot_rd_row, $sql_data_of_emp["w_ot_r_code"]);
					$otRdObject->getActiveSheet()->setCellValueByColumnAndRow(3, $ot_rd_row, $sql_data_of_emp["w_ot_r_description"]);
					$otRdObject->getActiveSheet()->setCellValueByColumnAndRow(4, $ot_rd_row, $r["month_overtime_rd"]);
					$otRdObject->getActiveSheet()->setCellValueByColumnAndRow(5, $ot_rd_row, $sql_data_of_emp["w_ot_r_rate"]);
					$ot_rd_row++;
				}
				if ($r['month_overtime_ph'] != 0) {
					$otPhObject->getActiveSheet()->setCellValueByColumnAndRow(0, $ot_ph_row, $payroll_csv_date);
					$otPhObject->getActiveSheet()->setCellValueByColumnAndRow(1, $ot_ph_row, $r["employee"]->special_id);
					$otPhObject->getActiveSheet()->setCellValueByColumnAndRow(2, $ot_ph_row, $sql_data_of_emp["w_ot_p_code"]);
					$otPhObject->getActiveSheet()->setCellValueByColumnAndRow(3, $ot_ph_row, $sql_data_of_emp["w_ot_p_description"]);
					$otPhObject->getActiveSheet()->setCellValueByColumnAndRow(4, $ot_ph_row, $r["month_overtime_ph"]);
					$otPhObject->getActiveSheet()->setCellValueByColumnAndRow(5, $ot_ph_row, $sql_data_of_emp["w_ot_p_rate"]);
					$ot_ph_row++;
				}

				$percentage = floor(($i / $employee_count) * 100);

				echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();
			}

			foreach (range('A', 'M') as $columnID) {
				$otObject->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
				$otRdObject->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
				$otPhObject->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
			}


			$file_name = "($branch_name) Weekly OT Log - $first_day to $last_day " . time();
			$otObject_writer = PHPExcel_IOFactory::createWriter($otObject, 'CSV');
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="' . $file_name . '.csv"');
			//$object_writer->save('php://output');
			$new_file = "uploads/summary/weekly-OT-log-" . time() . ".csv";
			$otObject_writer->save($new_file);
			$files[] = $new_file;

			$file_name = "($branch_name) Weekly OT RD Log - $first_day to $last_day " . time();
			$otRdObjectWriter = PHPExcel_IOFactory::createWriter($otRdObject, 'CSV');
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="' . $file_name . '.csv"');
			$new_file = "uploads/summary/weekly-OT-RD-log-" . time() . ".csv";
			$otRdObjectWriter->save($new_file);
			$files[] = $new_file;

			$file_name = "($branch_name) Weekly OT PH Log - $first_day to $last_day " . time();
			$otPhObjectWriter = PHPExcel_IOFactory::createWriter($otPhObject, 'CSV');
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="' . $file_name . '.csv"');
			$new_file = "uploads/summary/weekly-OT-PH-log-" . time() . ".csv";
			$otPhObjectWriter->save($new_file);
			$files[] = $new_file;

			$file_name = "($branch_name) Weekly OT Report - $first_day to $last_day " . time() . ".zip";

			foreach ($files as $file) {
				$this->zip->read_file(FCPATH .  $file);
				unlink($file);
			}


			$this->zip->archive(FCPATH . 'uploads/summary/' . $file_name);

			$path = base_url() . "uploads/summary/" . $file_name;

			echo '</br> <br> <b>Export Completed</b> </br>';

			echo "</br> <center><div style='width:40%'><a href='$path' download='$file_name'><button style='' class='btn btn-primary btn-block'>Download File</button></a></div></center>";

			echo "</br> <center><div style='width:40%'><a href='" . base_url() . "exports'><button style='margin-bottom	:40px' class='btn btn-primary btn-block'>Back to Export Summary</button></a></div></center>";
			echo '</div>';

			insert_log("Simple", ["action" => "Exported,Weekly OT Report"]);
			redirect($path);
		} elseif ($_POST["type"] === "bmi_summary") {
			$all_data = array();
			$employee_count = count($employees);
			foreach ($employees as $i => $emp) {
				$data = calculate_summary_data($emp->id, $first_day, $last_day, "summary", $emp, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days);

				$percentage = floor((($i + 1) / $employee_count) * 100);

				echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();

				$all_data[] = $data;

				$data = array();
				$dates = array();
			}

			$date = DateTime::createFromFormat('Y-m-d', $first_day);
			$data['from_f'] = $date->format('d/m/Y');
			$date = DateTime::createFromFormat('Y-m-d', $last_day);
			$data['to_f'] = $date->format('d/m/Y');

			$data["all_data"] = $all_data;
			$data["branch_name"] = $branch_name;


			//excel here
			$style = array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				)
			);

			$object = new PHPExcel();


			$sheet = 0;
			$count = 1;
			$total = count($all_data);
			foreach ($all_data as $bmi) {
				$bmi_employee = $bmi["employee"];
				if ($sheet) {
					$object->createSheet();
				}
				$object->setActiveSheetIndex($sheet++);

				$object->getDefaultStyle()->applyFromArray($style);

				$active_sheet = $object->getActiveSheet();

				$active_sheet->setTitle($bmi_employee->special_id);

				$name_columns = array("Name", "Employee ID", "Department", "Position", "Branch", "From", "To", "Generated at", "Generated by");
				$table_columns = array("Date", "Acting", "Shift", "In", "Out", "WD", "OT", "Sun", "PH < 8", "PH > 8", "TA (" . number_format($bmi_employee->ta_rate, 2) . ")", "MA (" . number_format($bmi_employee->ma_rate, 2) . ")", "CA (" . number_format($bmi_employee->ca_rate, 2) . ")", "SPA (" . number_format($bmi_employee->spa_rate, 2) . ")", "ACA (" . number_format($bmi_employee->aca_rate, 2) . ")", "FL Inc (" . number_format($bmi_employee->spa_rate, 2) . ")", "C/wash (" . number_format($bmi_employee->spa_rate, 2) . ")", "M/ope (" . number_format($bmi_employee->spa_rate, 2) . ")", "Shift1 (" . number_format($bmi_employee->shift1_rate, 2) . ")", "Shift2 (" . number_format($bmi_employee->shift2_rate, 2) . ")", "Shift3 (" . number_format($bmi_employee->shift3_rate, 2) . ")");

				$column = 0;

				foreach ($name_columns as $field) {
					$active_sheet->setCellValueByColumnAndRow($column, 1, $field);
					$active_sheet->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

					$column++;
				}

				$active_sheet->setCellValueByColumnAndRow(0, 2, $bmi_employee->first_name);
				$active_sheet->setCellValueByColumnAndRow(1, 2, $bmi_employee->special_id);
				$active_sheet->setCellValueByColumnAndRow(2, 2, $bmi_employee->department);
				$active_sheet->setCellValueByColumnAndRow(3, 2, $bmi_employee->position);
				$active_sheet->setCellValueByColumnAndRow(4, 2, $data["branch_name"]);
				$active_sheet->setCellValueByColumnAndRow(5, 2, $data["from_f"]);
				$active_sheet->setCellValueByColumnAndRow(6, 2, $data["to_f"]);
				$active_sheet->setCellValueByColumnAndRow(7, 2, date("d/m/Y H:i:s"));
				$active_sheet->setCellValueByColumnAndRow(8, 2, $current_user["first_name"]);

				$column = 0;

				foreach ($table_columns as $field) {
					$active_sheet->setCellValueByColumnAndRow($column, 4, $field);
					$active_sheet->getStyleByColumnAndRow($column, 4)->getFont()->setBold(true);

					$column++;
				}

				$row = 5;

				foreach ($bmi["dates"] as $bmi_date) {
					foreach ($bmi_date->clockings as $key => $clock) {
						if ($key == 0) {
							$active_sheet->setCellValueByColumnAndRow(0, $row, $clock->day_f);
							$active_sheet->setCellValueByColumnAndRow(1, $row, $bmi_date->acting_code);
							$active_sheet->setCellValueByColumnAndRow(2, $row, $bmi_date->shift_name);
							$active_sheet->setCellValueByColumnAndRow(3, $row, $bmi_date->first_in);
							$active_sheet->setCellValueByColumnAndRow(4, $row, $bmi_date->last_out);
							$active_sheet->setCellValueByColumnAndRow(5, $row, $bmi_date->days);
							$active_sheet->setCellValueByColumnAndRow(6, $row, $bmi_date->bmi_ot);
							$active_sheet->setCellValueByColumnAndRow(7, $row, $bmi_date->bmi_ot_sunday);
							$active_sheet->setCellValueByColumnAndRow(8, $row, $bmi_date->bmi_ph_1);
							$active_sheet->setCellValueByColumnAndRow(9, $row, $bmi_date->bmi_ph_2);
							$active_sheet->setCellValueByColumnAndRow(10, $row, $bmi_date->bmi_ta_final);
							$active_sheet->setCellValueByColumnAndRow(11, $row, $bmi_date->bmi_ma_final);
							$active_sheet->setCellValueByColumnAndRow(12, $row, $bmi_date->bmi_ca_final);
							$active_sheet->setCellValueByColumnAndRow(13, $row, $bmi_date->bmi_spa_final);
							$active_sheet->setCellValueByColumnAndRow(14, $row, $bmi_date->bmi_aca_final);
							$active_sheet->setCellValueByColumnAndRow(15, $row, $bmi_date->bmi_fl_final);
							$active_sheet->setCellValueByColumnAndRow(16, $row, $bmi_date->bmi_cw_final);
							$active_sheet->setCellValueByColumnAndRow(17, $row, $bmi_date->bmi_mo_final);
							$active_sheet->setCellValueByColumnAndRow(18, $row, $bmi_date->bmi_shift1_final);
							$active_sheet->setCellValueByColumnAndRow(19, $row, $bmi_date->bmi_shift2_final);
							$active_sheet->setCellValueByColumnAndRow(20, $row, $bmi_date->bmi_shift3_final);
						}
					}
					$row++;
				}

				foreach (range('A', 'U') as $columnID) {
					$active_sheet->getColumnDimension($columnID)
						->setAutoSize(true);
				}

				$percentage = floor(($count++ / $total) * 100);

				echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();
			}



			$object->setActiveSheetIndex(0);




			$file_name = "($branch_name) BMI Full Summary - $first_day to $last_day " . time();

			if ($_POST['file_type'] == 'xlsx') {
				$object_writer = new PHPExcel_Writer_Excel2007($object);
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="' . $file_name . '.xlsx"');
				$new_file = "uploads/summary/" . $file_name . ".xlsx";
				$object_writer->save($new_file);
			} else {
				$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="' . $file_name . '.xls"');
				$new_file = "uploads/summary/" . $file_name . ".xls";
				$object_writer->save($new_file);
			}
			//excel ends

			$files[] = $new_file;
			$file_name = "($branch_name) BMI Full Summary - $first_day to $last_day " . time() . ".zip";

			foreach ($files as $file) {
				$this->zip->read_file(FCPATH .  $file);
				unlink($file);
			}

			echo '</br> <br> <b>Export Completed</b> </br>';

			$this->zip->archive(FCPATH . 'uploads/summary/' . $file_name);

			$path = base_url() . "uploads/summary/" . $file_name;

			echo "</br> <center><div style='width:40%'><a href='$path'><button style='' class='btn btn-primary btn-block'>Download File</button></a></div></center>";

			echo "</br> <center><div style='width:40%'><a href='" . base_url() . "exports'><button style='margin-bottom	:40px' class='btn btn-primary btn-block'>Back to Export Summary</button></a></div></center>";
			echo '</div>';
			insert_log("Simple", ["action" => "Exported,BMI Full Summary"]);
		} elseif ($_POST["type"] === "bmi_summary_short") {
			$all_data = array();
			$employee_count = count($employees);
			foreach ($employees as $i => $emp) {
				$data = calculate_summary_data($emp->id, $first_day, $last_day, "summary", $emp, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days);

				$percentage = floor((($i + 1) / $employee_count) * 100);

				echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();

				$all_data[] = $data;

				$data = array();
				$dates = array();
			}

			$date = DateTime::createFromFormat('Y-m-d', $first_day);
			$data['from_f'] = $date->format('d/m/Y');
			$sheet_name = $date->format('MY');
			$date = DateTime::createFromFormat('Y-m-d', $last_day);
			$data['to_f'] = $date->format('d/m/Y');

			$data["all_data"] = $all_data;
			$data["branch_name"] = $branch_name;


			//excel here

			$object = PHPExcel_IOFactory::load("assets/bmi-short-template.xlsx");

			$object->setActiveSheetIndex(0);

			$active_sheet = $object->getActiveSheet();

			$active_sheet->setTitle($sheet_name);

			$count = 1;
			$total = count($all_data);
			if ($total > 2) {
				$active_sheet->insertNewRowBefore(13, $total - 2);

				$row = 13 + $total - 2;

				for ($i = 0; $i < 25; $i++) {
					$cell_value = $active_sheet->getCellByColumnAndRow($i, $row)->getValue();
					$cell_value = str_replace('12', $row - 1, $cell_value);
					$active_sheet->setCellValueByColumnAndRow($i, $row, $cell_value);
				}
			}


			$row = 11;

			foreach ($all_data as $bmi) {
				$bmi_employee = $bmi["employee"];

				$active_sheet->setCellValueByColumnAndRow(0, $row, $count);
				$active_sheet->setCellValueByColumnAndRow(1, $row, $bmi_employee->special_id);
				$active_sheet->setCellValueByColumnAndRow(2, $row, $bmi_employee->first_name);
				$active_sheet->setCellValueByColumnAndRow(5, $row, $bmi_employee->basic_wage);
				$active_sheet->setCellValueByColumnAndRow(6, $row, $bmi["working_days"]);
				$active_sheet->setCellValueByColumnAndRow(8, $row, $bmi["total_bmi_ot"]);
				$active_sheet->setCellValueByColumnAndRow(10, $row, $bmi["total_bmi_ot_sunday"]);
				$active_sheet->setCellValueByColumnAndRow(11, $row, $bmi["total_bmi_ph_1"]);
				$active_sheet->setCellValueByColumnAndRow(12, $row, $bmi["total_bmi_ph_2"]);
				$active_sheet->setCellValueByColumnAndRow(13, $row, $bmi["total_bmi_shift1"]);
				$active_sheet->setCellValueByColumnAndRow(14, $row, $bmi["total_bmi_shift2"]);
				$active_sheet->setCellValueByColumnAndRow(15, $row, $bmi["total_bmi_shift3"]);
				$active_sheet->setCellValueByColumnAndRow(17, $row, $bmi["total_bmi_ta"]);
				$active_sheet->setCellValueByColumnAndRow(18, $row, $bmi["total_bmi_ma"]);
				$active_sheet->setCellValueByColumnAndRow(19, $row, $bmi["total_bmi_ca"]);
				$active_sheet->setCellValueByColumnAndRow(20, $row, $bmi["total_bmi_spa"] + $bmi["total_bmi_fl"] + $bmi["total_bmi_cw"] + $bmi["total_bmi_mo"]);
				$attendance_allowance = $bmi["bmi_attendance_allowance"] ? $bmi_employee->aa_rate : 0;
				$active_sheet->setCellValueByColumnAndRow(21, $row, $attendance_allowance);
				$active_sheet->setCellValueByColumnAndRow(24, $row, $bmi["total_bmi_aca"]);
				$active_sheet->setCellValueByColumnAndRow(28, $row, $bmi["absent_days"]);
				$active_sheet->setCellValueByColumnAndRow(31, $row, $bmi["unpaid_leaves"]);

				$row++;

				$percentage = floor(($count++ / $total) * 100);

				echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();
			}




			$file_name = "($branch_name) BMI Short Summary - $first_day to $last_day " . time();

			$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="' . $file_name . '.xlsx"');
			$new_file = "uploads/summary/" . $file_name . ".xlsx";
			$object_writer->save($new_file);
			//excel ends

			$files[] = $new_file;
			$file_name = "($branch_name) BMI Short Summary - $first_day to $last_day " . time() . ".zip";

			foreach ($files as $file) {
				$this->zip->read_file(FCPATH .  $file);
				unlink($file);
			}

			echo '</br> <br> <b>Export Completed</b> </br>';

			$this->zip->archive(FCPATH . 'uploads/summary/' . $file_name);

			$path = base_url() . "uploads/summary/" . $file_name;

			echo "</br> <center><div style='width:40%'><a href='$path'><button style='' class='btn btn-primary btn-block'>Download File</button></a></div></center>";

			echo "</br> <center><div style='width:40%'><a href='" . base_url() . "exports'><button style='margin-bottom	:40px' class='btn btn-primary btn-block'>Back to Export Summary</button></a></div></center>";
			echo '</div>';
			insert_log("Simple", ["action" => "Exported,BMI Short Summary"]);
		} else if ($_POST['type'] == "cjc01_payroll") {
			$all_data = array();
			$employee_count = count($employees);
			foreach ($employees as $i => $emp) {
				$data = calculate_summary_data($emp->id, $first_day, $last_day, "summary", $emp, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days);

				$percentage = floor((($i + 1) / $employee_count) * 100);

				echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();

				$all_data[] = $data;
				$data = array();
				$dates = array();
			}

			$date = DateTime::createFromFormat('Y-m-d', $first_day);
			$first_day = $date->format('d M, Y');
			$date = DateTime::createFromFormat('Y-m-d', $last_day);
			$last_day = $date->format('d M, Y');

			$style = array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				)
			);


			$object = new PHPExcel();

			$object->setActiveSheetIndex(0);
			$object->getDefaultStyle()->applyFromArray($style);

			$headings = [
				"EMP_NO",
				"EMP_NAME",
				"OT1.5C",
				"OT2.0C",
				"1.0 DAY-C",
				"OT3.0C",
				"2.0 DAY-C",
				"INCENTV",
				"SPPA",
				"RDPHALLW",
				"SPEC_INC"
			];

			$column = 0;
			foreach ($headings as $heading) {
				$object->getActiveSheet()->setCellValueByColumnAndRow($column++, 1, $heading);
			}

			$row = 2;

			foreach ($all_data as $i => $r) {
				$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $r["employee"]->special_id);
				$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $r["employee"]->first_name);
				$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, toDecimal($r["month_overtime"]));
				$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, toDecimal($r["month_overtime_rd"]));
				$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $r["worked_rest_days"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, toDecimal($r["month_overtime_ph"]));
				$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $r["worked_holidays"]);
				$ot_group_value = "RM0.00";
				if ($r["employee"]->ot_group == "day") {
					$ot_group_value = "RM" . number_format((toDecimal($r["month_overtime"]) + toDecimal($r["month_overtime_rd"]) + toDecimal($r["month_overtime_ph"])) * 15, 2);
				}
				$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $ot_group_value);
				$allowance_value = "RM0.00";
				if ($r["employee"]->ot_group != "day") {
					$allowance_value = "RM" . number_format(($r["worked_rest_days"] + $r["worked_holidays"]) * 30, 2);
				}
				$object->getActiveSheet()->setCellValueByColumnAndRow(9, $row, $allowance_value);
				$object->getActiveSheet()->setCellValueByColumnAndRow(10, $row, "RM" . number_format($r["employee"]->special_incentive, 2));
				$row++;

				$percentage = floor((($i + 1) / count($employees)) * 100);

				echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();
			}

			foreach (range('A', 'K') as $columnID) {
				$object->getActiveSheet()->getColumnDimension($columnID)->setWidth(12);
			}
			$object->getActiveSheet()->getColumnDimension('B')->setWidth(35);

			// $file_name = "$c_name ($branch_name) AutoCount Payroll - $first_day to $last_day " . time();
			$file_name = "($branch_name) CJC01 Payroll - $first_day to $last_day " . time();

			$object_writer = new PHPExcel_Writer_Excel2007($object, 'Excel5');
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="' . $file_name . '.xlsx"');
			$new_file = "uploads/summary/" . $file_name . ".xlsx";
			$object_writer->save($new_file);
			$files[] = $new_file;
			foreach ($files as $file) {
				$this->zip->read_file(FCPATH .  $file);
				unlink($file);
			}

			echo '</br> <br> <b>Export Completed</b> </br>';

			$this->zip->archive(FCPATH . 'uploads/summary/' . $file_name);

			$path = base_url() . "uploads/summary/" . $file_name;

			echo "</br> <center><div style='width:40%'><a href='$path'><button style='' class='btn btn-primary btn-block'>Download File</button></a></div></center>";

			echo "</br> <center><div style='width:40%'><a href='" . base_url() . "exports'><button style='margin-bottom	:40px' class='btn btn-primary btn-block'>Back to Export Summary</button></a></div></center>";
			echo '</div>';

			insert_log("Simple", ["action" => "Exported,CJC01 Payroll"]);
		} else if ($_POST["type"] == "daily_time_card") {
			if ($_POST['file_type'] == "pdf") {
				$all_data = array();
				foreach ($employees as $emp) {
					$data = calculate_summary_data($emp->id, $first_day, $last_day, "summary", $emp, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days);

					$all_data[] = $data;
				}
				// echo "<pre>"; print_r($all_data); die;
				$this->load->view("exports/daily_time_card", ["all_data" => $all_data, "actual_date" => $_POST["from"], "day_name" => $first_day_name]);
				$html = $this->output->get_output();
				$this->dompdf->loadHtml($html);
				$this->dompdf->setPaper("A4", "landscape");
				$this->dompdf->render();
				$output = $this->dompdf->output();
				$file_name = $branch_name . " - " . $time_card_date . ".pdf";
				$file_name = str_replace("/", "_", $file_name);
				$new_file = "uploads/summary/" . $file_name;
				file_put_contents($new_file, $output);

				$path = "uploads/summary/" . $file_name;
				$path = base64_encode($path);

				// replace disallowed characters in path
				$path = str_replace(array('+', '/', '='), array('-', '_', '~'), $path);

				redirect("exports/download/" . $path . "/" . $file_name);
				// header('Content-Type: application/pdf');
				// header("Content-Transfer-Encoding: Binary");
				// header("Content-disposition: inline; filename=" . $file_name);
				// readfile($path);
				// unlink($path);
			} else {
				$all_data = array();
				$employee_count = count($employees);
				foreach ($employees as $i => $emp) {
					$data = calculate_summary_data($emp->id, $first_day, $last_day, "summary", $emp, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days);

					$percentage = floor((($i + 1) / $employee_count) * 100);

					echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
					ob_flush();
					flush();
					$all_data[] = $data;
				}

				$style = array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					)
				);

				$object = new PHPExcel();

				$object->setActiveSheetIndex(0);
				$object->getDefaultStyle()->applyFromArray($style);

				$object->getActiveSheet()->setCellValueByColumnAndRow(0, 1, "Date");
				$object->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);

				$object->getActiveSheet()->setCellValueByColumnAndRow(1, 1, $_POST["from"]);

				$object->getActiveSheet()->setCellValueByColumnAndRow(2, 1, "Day Name");
				$object->getActiveSheet()->getStyleByColumnAndRow(2, 1)->getFont()->setBold(true);

				$object->getActiveSheet()->setCellValueByColumnAndRow(3, 1, $first_day_name);

				$table_columns = array("Employee", "Shift", "Time In", "Time Out", "Work", "OT1", "OT2", "OT3", "Total", "Break", "Late", "Early", "Attend", "Absent", "Offday", "Leave", "Holiday");

				$column = 0;

				foreach ($table_columns as $field) {
					$object->getActiveSheet()->setCellValueByColumnAndRow($column, 3, $field);
					$object->getActiveSheet()->getStyleByColumnAndRow($column, 3)->getFont()->setBold(true);

					$column++;
				}

				$row = 4;

				foreach ($all_data as $i => $r) {
					$shift_data = $r["dates"][0];
					$public_holidays = $r["public_holidays"];
					$rest_days = $r["rest_days"];
					$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $r["employee"]->special_id . " - " . $r["employee"]->first_name);
					$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $shift_data->shift_name ? $shift_data->shift_name : '-');
					$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $shift_data->first_in);
					$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $shift_data->last_out);
					$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, time_placeholder($shift_data->work_hours));
					$ot1 = $ot2 = $ot3 = "";
					if ($shift_data->is_ot) {
						if (!in_array($shift_data->day_name, $rest_days) && !in_array($shift_data->date, $public_holidays)) {
							$ot1 = add_time_minus($shift_data->overtime, $shift_data->overtime_m);
						} else if (in_array($shift_data->day_name, $rest_days)) {
							$ot2 = add_time_minus($shift_data->overtime, $shift_data->overtime_m);
						} else if (in_array($shift_data->date, $public_holidays)) {
							$ot3 = add_time_minus($shift_data->overtime, $shift_data->overtime_m);
						}
					}
					$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, time_placeholder($ot1));
					$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, time_placeholder($ot2));
					$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, time_placeholder($ot3));
					$object->getActiveSheet()->setCellValueByColumnAndRow(8, $row, time_placeholder($shift_data->total_hours));
					$object->getActiveSheet()->setCellValueByColumnAndRow(9, $row, time_placeholder($shift_data->break_hours));
					$object->getActiveSheet()->setCellValueByColumnAndRow(10, $row, time_placeholder($shift_data->late_in));
					$object->getActiveSheet()->setCellValueByColumnAndRow(11, $row, time_placeholder($shift_data->early_out));
					$rest_day = (in_array($shift_data->day_name, $rest_days) || empty($shift_data->shift_name)) ? 1 : 0;
					$holiday = (in_array($shift_data->date, $public_holidays)) ? 1 : 0;
					$attend = (!empty($shift_data->first_in) && !empty($shift_data->last_out)) ? 1 : 0;
					$absent = (!$rest_day && !$holiday && empty($shift_data->first_in) && empty($shift_data->last_out)) ? 1 : 0;
					$object->getActiveSheet()->setCellValueByColumnAndRow(12, $row, $attend ? $attend : "-");
					$object->getActiveSheet()->setCellValueByColumnAndRow(13, $row, $absent ? $absent : "-");
					$object->getActiveSheet()->setCellValueByColumnAndRow(14, $row, $rest_day ? $rest_day : "-");
					$object->getActiveSheet()->setCellValueByColumnAndRow(15, $row, 0.0);
					$row++;

					$percentage = floor((($i + 1) / count($employees)) * 100);

					echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
					ob_flush();
					flush();
				}

				foreach (range('A', 'Q') as $columnID) {
					$object->getActiveSheet()->getColumnDimension($columnID)
						->setAutoSize(true);
				}

				if ($_POST['file_type'] == 'excel') {
					$file_name = "($branch_name) Daily Time Card - $first_day " . time() . ".xls";

					$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="' . $file_name . '"');
					$new_file = "uploads/summary/" . $file_name;
					$object_writer->save($new_file);
				} else {
					$file_name = "($branch_name) Daily Time Card - $first_day " . time() . ".xlsx";

					$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
					header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					header('Content-Disposition: attachment;filename="' . $file_name . '"');
					$new_file = "uploads/summary/" . $file_name;
					$object_writer->save($new_file);
				}

				echo '</br> <br> <b>Export Completed</b> </br>';

				$path = base_url() . $new_file;

				echo "</br> <center><div style='width:40%'><a href='$path' download='$file_name'><button style='' class='btn btn-primary btn-block'>Download File</button></a></div></center>";

				echo "</br> <center><div style='width:40%'><a href='" . base_url() . "exports'><button style='margin-bottom	:40px' class='btn btn-primary btn-block'>Back to Export Summary</button></a></div></center>";
				echo '</div>';
			}
		} else if ($_POST["type"] == "work_hours_summary") {
			$all_data = array();
			$employee_count = count($employees);
			foreach ($employees as $i => $emp) {
				$data = calculate_summary_data($emp->id, $first_day, $last_day, "summary", $emp, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days);

				$percentage = floor((($i + 1) / $employee_count) * 100);

				echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();

				$all_data[] = $data;
			}

			$object = PHPExcel_IOFactory::load("assets/work-hours-summary.xlsx");

			$object->setActiveSheetIndex(0);

			$active_sheet = $object->getActiveSheet();

			$active_sheet->setCellValueByColumnAndRow(1, 1, $branch_name);
			$active_sheet->setCellValueByColumnAndRow(1, 2, $first_day . " to " . $last_day);
			$active_sheet->setCellValueByColumnAndRow(1, 3, date("Y-m-d H:i:s"));

			$period = new DatePeriod(
				new DateTime($first_day),
				new DateInterval('P1D'),
				(new DateTime($last_day))->add(new DateInterval('P1D'))
			);

			$dates = array();

			foreach ($period as $key => $value) {
				$dates[] = $value->format('j');
			}

			$dates_count = count($dates);

			if ($dates_count < 30) {
				$active_sheet->removeColumnByIndex(4, 30 - $dates_count);
			} else if ($dates_count > 30) {
				$active_sheet->insertNewColumnBefore("AH", $dates_count - 30);
			}

			$active_sheet->setCellValueByColumnAndRow(4, 4, "Calendar Days");

			$row = 5;
			$column = 4;

			foreach ($dates as $date) {
				$active_sheet->setCellValueByColumnAndRow($column++, $row, $date);
			}

			$row = 6;

			foreach ($all_data as $i => $r) {
				// insert a row
				$active_sheet->insertNewRowBefore($row + 1, 1);

				$active_sheet->setCellValueByColumnAndRow(0, $row, $r["employee"]->special_id);
				$active_sheet->setCellValueByColumnAndRow(1, $row, $r["employee"]->first_name);
				$active_sheet->setCellValueByColumnAndRow(2, $row, $r["employee"]->position);
				$active_sheet->setCellValueByColumnAndRow(3, $row, $r["employee"]->department);

				$column = 4;
				foreach ($r["dates"] as $rd) {
					$active_sheet->setCellValueByColumnAndRow($column++, $row, $rd->work_hours_whole == 0 ? "" : $rd->work_hours_whole);
				}

				$row++;

				$percentage = floor((($i + 1) / $employee_count) * 100);

				echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();
			}

			// delete the template row
			$active_sheet->removeRow($row, 1);

			$column = 4;
			foreach ($dates as $date) {
				$active_sheet->setCellValueByColumnAndRow($column, $row, "=SUM(" . PHPExcel_Cell::stringFromColumnIndex($column) . "6:" . PHPExcel_Cell::stringFromColumnIndex($column) . ($row - 1) . ")");
				$column++;
			}

			foreach (range('A', 'D') as $columnID) {
				$active_sheet->getColumnDimension($columnID)
					->setAutoSize(true);
			}

			if ($_POST['file_type'] == 'excel') {
				$file_name = "($branch_name) Work Hours Summary - $first_day - $last_day " . time() . ".xls";
				$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="' . $file_name . '"');
				$new_file = "uploads/summary/" . $file_name;
				$object_writer->save($new_file);
			} else {
				$file_name = "($branch_name) Work Hours Summary - $first_day - $last_day " . time() . ".xlsx";
				$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="' . $file_name . '"');
				$new_file = "uploads/summary/" . $file_name;
				$object_writer->save($new_file);
			}

			echo '</br> <br> <b>Export Completed</b> </br>';

			$path = base_url() . $new_file;

			echo "</br> <center><div style='width:40%'><a href='$path' download='$file_name'><button style='' class='btn btn-primary btn-block'>Download File</button></a></div></center>";

			echo "</br> <center><div style='width:40%'><a href='" . base_url() . "exports'><button style='margin-bottom	:40px' class='btn btn-primary btn-block'>Back to Export Summary</button></a></div></center>";
			echo '</div>';
		} else if ($_POST['type'] == "gni01_payroll_process") {
			$clockings_news = [];
			$clockings_news_overnight = [];

			$unpaid_leaves_absent_days = [];
			$worked_rest_days_array = [];
			$worked_off_days_array = [];
			$worked_holidays_array = [];
			$paid_leaves_array = [];
			$daily_ot_array = [];
			$daily_late_array = [];

			$ot_unpaid_leaves_absent_days = [];
			$ot_worked_rest_days_array = [];
			$ot_worked_off_days_array = [];
			$ot_worked_holidays_array = [];
			$ot_paid_leaves_array = [];
			$ot_daily_ot_array = [];
			$ot_daily_late_array = [];

			$all_sql_data = array();
			$all_sql_ot_data = array();
			$all_short_data = array();
			$all_short_ot_data = array();

			$employee_count = count($employees);

			foreach ($employees as $i => $emp) {
				$short_data = calculate_summary_data($emp->id, $first_day, $last_day, "short", $emp, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days);
				$short_ot_data = calculate_summary_data($emp->id, $ot_first_day, $ot_last_day, "short", $emp, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days);
				$sql_data = calculate_summary_data($emp->id, $first_day, $last_day, "sql", $emp, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days, null, $worked_rest_days_array, $worked_off_days_array, $worked_holidays_array, $unpaid_leaves_absent_days, $clockings_news, $clockings_news_overnight, $paid_leaves_array, $daily_ot_array, $daily_late_array);
				$sql_ot_data = calculate_summary_data($emp->id, $ot_first_day, $ot_last_day, "sql", $emp, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days, null, $ot_worked_rest_days_array, $ot_worked_off_days_array, $ot_worked_holidays_array, $ot_unpaid_leaves_absent_days, $clockings_news, $clockings_news_overnight, $ot_paid_leaves_array, $ot_daily_ot_array, $ot_daily_late_array);

				$percentage = floor((($i + 1) / $employee_count) * 100);

				echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();

				$all_sql_data[] = $sql_data;
				$all_sql_ot_data[] = $sql_ot_data;
				$all_short_data[] = $short_data;
				$all_short_ot_data[] = $short_ot_data;

				$short_data = array();
				$short_ot_data = array();
				$sql_data = array();
				$sql_ot_data = array();
				$dates = array();
			}

			$data['short_data'] = $all_short_data;
			$data['short_ot_data'] = $all_short_ot_data;

			ksort($unpaid_leaves_absent_days);
			ksort($paid_leaves_array);
			ksort($daily_ot_array);
			ksort($daily_late_array);

			ksort($ot_unpaid_leaves_absent_days);
			ksort($ot_paid_leaves_array);
			ksort($ot_daily_ot_array);
			ksort($ot_daily_late_array);

			$date = DateTime::createFromFormat('Y-m-d', $first_day);
			$first_day = $date->format('d M, Y');
			$data['from_f'] = $date->format('d/m/Y');
			$date = DateTime::createFromFormat('Y-m-d', $last_day);
			$last_day = $date->format('d M, Y');
			$data['to_f'] = $date->format('d/m/Y');
			$date = DateTime::createFromFormat('Y-m-d', $ot_first_day);
			$ot_first_day = $date->format('d M, Y');
			$date = DateTime::createFromFormat('Y-m-d', $ot_last_day);
			$ot_last_day = $date->format('d M, Y');

			$data['branch_name'] = $branch_name;

			$files = array();
			$files_count = 9;

			$style = array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				)
			);

			$files[] = $this->pendingOvertimeLogFile($style, $all_sql_ot_data, $date2, $branch_name, $ot_first_day, $ot_last_day, true, $all_short_ot_data);
			$this->changeLoadingBar2(floor((1 / $files_count) * 100));

			$files[] = $this->pendingWorkedRestDaysLogFile($style, $cid, $all_sql_ot_data, $ot_worked_rest_days_array, $date2, $branch_name, $ot_first_day, $ot_last_day);
			$this->changeLoadingBar2(floor((2 / $files_count) * 100));

			$files[] = $this->pendingWorkedPublicHolidaysLogFile($style, $ot_worked_holidays_array, $date2, $branch_name, $ot_first_day, $ot_last_day);
			$this->changeLoadingBar2(floor((3 / $files_count) * 100));

			$files[] = $this->pendingLeaveApplicationLogFile($style, $paid_leaves_array, $branch_name, $first_day, $last_day);
			$this->changeLoadingBar2(floor((4 / $files_count) * 100));

			$files[] = $this->pendingEarlyLateLogFile($style, $all_sql_data, $date2, $branch_name, $first_day, $last_day);
			$this->changeLoadingBar2(floor((5 / $files_count) * 100));

			$files[] = $this->pendingUnpaidLeavesLogFile($style, $unpaid_leaves_absent_days, $cid, $date2, $branch_name, $first_day, $last_day);
			$this->changeLoadingBar2(floor((6 / $files_count) * 100));

			$files[] = $this->pendingAbsentLogFile($style, $unpaid_leaves_absent_days, $date2, $branch_name, $first_day, $last_day);
			$this->changeLoadingBar2(floor((7 / $files_count) * 100));

			$html2 = $this->load->view('short_summary_104', $data, true);
			$file_name = "($branch_name) Short Summary - $first_day to $last_day " . time() . ".pdf";

			$this->dompdf->reset();
			$this->dompdf->loadHtml($html2);
			$this->dompdf->setPaper("A4", "landscape");
			$this->dompdf->render();

			$output = $this->dompdf->output();
			$short_file = "uploads/summary/" . $file_name;
			file_put_contents($short_file, $output);

			$this->changeLoadingBar2(floor((8 / $files_count) * 100));

			$ot_balance_file = $this->otBalanceSheet($style, $all_short_data, $all_short_ot_data, $branch_name, $first_day, $last_day);
			$this->changeLoadingBar2(floor((9 / $files_count) * 100));

			$file_name = "($branch_name) GNI01 Payroll Payroll Process - $first_day to $last_day " . time() . ".zip";

			foreach ($files as $file) {
				$this->zip->read_file(FCPATH .  $file, "SQL Payroll/" . basename($file));
				unlink($file);
			}

			$this->zip->read_file(FCPATH .  $short_file, basename($short_file));
			unlink($short_file);

			$this->zip->read_file(FCPATH .  $ot_balance_file, basename($ot_balance_file));
			unlink($ot_balance_file);

			$this->zip->archive(FCPATH . 'uploads/summary/' . $file_name);

			$path = base_url() . "uploads/summary/" . $file_name;

			echo '</br> <br> <b>Export Completed</b> </br>';

			echo "</br> <center><div style='width:40%'><a href='$path'><button style='' class='btn btn-primary btn-block'>Download File</button></a></div></center>";

			echo "</br> <center><div style='width:40%'><a href='" . base_url() . "exports'><button style='margin-bottom	:40px' class='btn btn-primary btn-block'>Back to Export Summary</button></a></div></center>";
			echo '</div>';

			insert_log("Simple", ["action" => "Exported,GNI01 Payroll Process"]);
			redirect($path);
		} else {
			$total = count($employees);
			$count = 1;

			if ($_POST['type'] === 'full_merged') {
				$summary_body = '';
				foreach ($employees as $i => $emp) {
					$data = calculate_summary_data(
						$emp->id,
						$first_day,
						$last_day,
						"summary",
						$emp,
						$result_list,
						$result_list_overnight,
						$company_working_hours,
						false,
						$company_ot_settings,
						$company_early_ot_settings,
						$approved_ot_list,
						$branch_rest_days
					);

					$percentage  = floor((($i + 1) / $total) * 100);

					echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
					ob_flush();
					flush();

					$date = DateTime::createFromFormat('Y-m-d', $first_day);
					$data['from_f'] = $date->format('d/m/Y');
					$date = DateTime::createFromFormat('Y-m-d', $last_day);
					$data['to_f'] = $date->format('d/m/Y');
					$data['merged'] = true;
					$data['is_merged'] = isset($employees[$i + 1]);
					$summary_body .= $this->load->view('summary_pdf_body', $data, true);
				}

				$html = $this->load->view('summary_pdf', ['summary_body' => $summary_body], true);

				$this->dompdf->reset();
				$this->dompdf->loadHtml($html);
				$this->dompdf->setPaper("A4", "landscape");
				$this->dompdf->render();

				$output = $this->dompdf->output();
				$file_name = "($branch_name) Full Mreged Summary - $first_day to $last_day.pdf";
				$new_file = "uploads/summary/" . $file_name;

				file_put_contents($new_file, $output);

				$percentage = 100;

				echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();
			} else {
				foreach ($employees as $i => $emp) {
					$data = calculate_summary_data(
						$emp->id,
						$first_day,
						$last_day,
						"summary",
						$emp,
						$result_list,
						$result_list_overnight,
						$company_working_hours,
						false,
						$company_ot_settings,
						$company_early_ot_settings,
						$approved_ot_list,
						$branch_rest_days
					);

					$percentage  = floor((($i + 1) / $total) * 100);

					echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
					ob_flush();
					flush();

					$date = DateTime::createFromFormat('Y-m-d', $first_day);
					$data['from_f'] = $date->format('d/m/Y');
					$date = DateTime::createFromFormat('Y-m-d', $last_day);
					$data['to_f'] = $date->format('d/m/Y');

					$merged_data[] = $data; // Collect data for merging

					if ($_POST['file_type'] == 'pdf') {
						$summary_body = $this->load->view('summary_pdf_body', $data, true);
						$html = $this->load->view('summary_pdf', ['summary_body' => $summary_body], true);

						$this->dompdf->reset();
						$this->dompdf->loadHtml($html);
						$this->dompdf->setPaper("A4", "landscape");
						$this->dompdf->render();


						$output = $this->dompdf->output();

						$file_name = str_replace("/", "-", $data['employee']->special_id) . " - " . str_replace("/", "-", $data['employee']->first_name) . " " . $first_day . " to " . $last_day . " - Summary.pdf";
						$new_file = "uploads/summary/" . $file_name;

						file_put_contents($new_file, $output);

						$percentage = floor(($count++ / $total) * 100);

						echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
						ob_flush();
						flush();
					} else {
						$percentage = floor(($count++ / $total) * 100);

						echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
						ob_flush();
						flush();
						if ($_POST['file_type'] == 'excel') {
							$file_name = str_replace("/", "-", $data['employee']->special_id) . " - " . str_replace("/", "-", $data['employee']->first_name) . " " . $first_day . " to " . $last_day . " - Summary.xls";
							$object = generate_full_summary_excel($data);
							$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
							header('Content-Type: application/vnd.ms-excel');
							header('Content-Disposition: attachment;filename="' . $file_name . '"');
							$new_file = "uploads/summary/" . $file_name;

							$object_writer->save($new_file);
						} else {
							$file_name = str_replace("/", "-", $data['employee']->special_id) . " - " . str_replace("/", "-", $data['employee']->first_name) . " " . $first_day . " to " . $last_day . " - Summary.xlsx";
							$object = generate_full_summary_excel($data);
							$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
							header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
							header('Content-Disposition: attachment;filename="' . $file_name . '"');
							$new_file = "uploads/summary/" . $file_name;

							$object_writer->save($new_file);
						}
					}

					$files[] = $new_file;

					$data = array();
					$dates = array();
				}

				if (count($files) > 1) {
					$file_name = "($branch_name) Full Summary - $first_day to $last_day " . time() . ".zip";
					foreach ($files as $file) {
						$this->zip->read_file(FCPATH .  $file);
						unlink($file);
					}
					$this->zip->archive(FCPATH . 'uploads/summary/' . $file_name);
				}
			}

			$path = base_url() . "uploads/summary/" . $file_name;

			insert_log("Simple", ["action" => "Exported,Full Data"]);

			echo '</br> <br> <b>Export Completed</b> </br>';

			echo "</br> <center><div style='width:40%'><a href='$path' download='$file_name'><button style='' class='btn btn-primary btn-block'>Download File</button></a></div></center>";

			echo "</br> <center><div style='width:40%'><a href='" . base_url() . "exports'><button style='margin-bottom	:40px' class='btn btn-primary btn-block'>Back to Export Summary</button></a></div></center>";
			echo '</div>';
			// redirect($path);
		}
	}

	function excel($id, $first_day, $last_day)
	{
		$this->load->library("excel");
		$data = calculate_summary_data($id, $first_day, $last_day);
		$date = DateTime::createFromFormat('Y-m-d', $first_day);
		$data['from_f'] = $date->format('d/m/Y');
		$date = DateTime::createFromFormat('Y-m-d', $last_day);
		$data['to_f'] = $date->format('d/m/Y');

		$object = generate_full_summary_excel($data);
		$object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $data['employee']->first_name . " " . $data['employee']->special_id . " " . $data["month_name"] . " - Summary" . '.xls"');
		$object_writer->save('php://output');
		insert_log("Simple", ["action" => "Exported,Employee Summary excel"]);
	}

	public function sql_payroll_settings()
	{
        if (!is_page_permitted('sql_payroll_settings')) {
            redirect_if_not_permitted();
        }
          
		$user = (object)get_user();

		$this->db->select("id, name")->from("branches")->where("company_id = '$user->company_id' and deleted_at IS NULL");
		if ($user->permissions_level === 'Outlet') $this->db->where("id = '$user->branch_id'");
		$branches = $this->db->get()->result();

		$data['branches'] = $branches;
		$data['pageTitle'] = "SQL Payroll Settings";
		$data['active_menu'] = "exports/sql_payroll_settings";
		$this->load->view('header', $data);
		$data["menus"] = get_menus();

		$this->load->view('sidebar', $data);
		$this->load->view('sql_payroll_settings', $data);
		$this->load->view('footer', $data);
	}

	public function get_sql_xcrud()
	{
		$cid = get_user()["company_id"];
		$branch_id = $this->input->get('branch_id');
		if ($branch_id === '') {
			echo "<div class='alert alert-warning'>Please select an outlet</div>";
			return;
		}
		$this->load->helper('xcrud');
		$xcrud = xcrud_get_instance();

		$xcrud->table('branches');

		$xcrud->where('id =', $branch_id);

		$xcrud->label(array('sql_ot1_name' => 'Name', 'sql_ot1_code' => 'Code', 'sql_ot1_description' => 'Description', 'sql_ot1_rate' => 'Rate'));
		$xcrud->label(array('sql_ot2_name' => 'Name', 'sql_ot2_code' => 'Code', 'sql_ot2_description' => 'Description', 'sql_ot2_rate' => 'Rate'));
		$xcrud->label(array('sql_ot_off_name' => 'Name', 'sql_ot_off_code' => 'Code', 'sql_ot_off_description' => 'Description', 'sql_ot_off_rate' => 'Rate'));
		$xcrud->label(array('sql_ot3_name' => 'Name', 'sql_ot3_code' => 'Code', 'sql_ot3_description' => 'Description', 'sql_ot3_rate' => 'Rate'));
		$xcrud->label(array('sql_ot3_name_x2' => 'Name', 'sql_ot3_code_x2' => 'Code', 'sql_ot3_description_x2' => 'Description', 'sql_ot3_rate_x2' => 'Rate'));

		$xcrud->label(array('sql_ab_name' => 'Name', 'sql_ab_code' => 'Code', 'sql_ab_description' => 'Description', 'sql_ab_rate' => 'Rate'));
		$xcrud->label(array('sql_ul_name' => 'Name', 'sql_ul_code' => 'Code', 'sql_ul_description' => 'Description', 'sql_ul_rate' => 'Amount'));
		$xcrud->label(array('sql_dw_name' => 'Name', 'sql_dw_code' => 'Code', 'sql_dw_description' => 'Description', 'sql_dw_rate' => 'Rate'));
		$xcrud->label(array('sql_dd1_name' => 'Name', 'sql_dd1_code' => 'Code', 'sql_dd1_description' => 'Description', 'sql_dd1_rate' => 'Rate'));
		$xcrud->label(array('sql_dd2_name' => 'Name', 'sql_dd2_code' => 'Code', 'sql_dd2_description' => 'Description', 'sql_dd2_rate' => 'Rate'));
		$xcrud->label(array('sql_e_l_name' => 'Name', 'sql_e_l_code' => 'Code', 'sql_e_l_description' => 'Description', 'sql_e_l_rate' => 'Amount'));
		$xcrud->label(array('sql_wrd_name' => 'Name', 'sql_wrd_code' => 'Code', 'sql_wrd_description' => 'Description', 'sql_wrd_rate' => 'Rate'));
		$xcrud->label(array('sql_w_off_name' => 'Name', 'sql_w_off_code' => 'Code', 'sql_w_off_description' => 'Description', 'sql_w_off_rate' => 'Rate'));
		$xcrud->label(array('sql_wph_name' => 'Name', 'sql_wph_code' => 'Code', 'sql_wph_description' => 'Description', 'sql_wph_rate' => 'Rate'));
		// $xcrud->label(array('w_ot_name' => 'Name', 'w_ot_code' => 'Code', 'w_ot_description' => 'Description', 'w_ot_rate' => 'Rate'));
		$xcrud->label(array('sql_wph_name_x2' => 'Name', 'sql_wph_code_x2' => 'Code', 'sql_wph_description_x2' => 'Description', 'sql_wph_rate_x2' => 'Rate'));

		// $xcrud->label(['w_ot_r_name' => 'Name', 'w_ot_r_code' => 'Code', 'w_ot_r_description' => 'Description', 'w_ot_r_rate' => 'Rate']);
		// $xcrud->label(['w_ot_p_name' => 'Name', 'w_ot_p_code' => 'Code', 'w_ot_p_description' => 'Description', 'w_ot_p_rate' => 'Rate']);
		$xcrud->label(['sql_wsh_name' => 'Name', 'sql_wsh_code' => 'Code', 'sql_wsh_description' => 'Description', 'sql_wsh_rate' => 'Rate']);
		$xcrud->label(['sql_wh_name' => 'Name', 'sql_wh_code' => 'Code', 'sql_wh_description' => 'Description', 'sql_wh_rate' => 'Rate']);


		// $xcrud->fields('sql_ot1_name, sql_ot1_code, sql_ot1_description, sql_ot1_rate', false, 'Overtime 1');
		// $xcrud->fields('sql_ot2_name, sql_ot2_code, sql_ot2_description, sql_ot2_rate', false, 'Overtime 2');
		// $xcrud->fields('sql_ot3_name, sql_ot3_code, sql_ot3_description, sql_ot3_rate', false, 'Overtime 3');
		// $xcrud->fields('sql_ul_name, sql_ul_code, sql_ul_description, sql_ul_rate', false, 'Unpaid Leave');
		// $xcrud->fields('sql_e_l_name, sql_e_l_code, sql_e_l_description, sql_e_l_rate', false, 'Early / Late');
		// $xcrud->fields('sql_dw_name, sql_dw_code, sql_dw_description, sql_dw_rate', false, 'Daily Wage');
		// $xcrud->fields('sql_dd1_name, sql_dd1_code, sql_dd1_description, sql_dd1_rate', false, 'Deduction 1');
		// $xcrud->fields('sql_dd2_name, sql_dd2_code, sql_dd2_description, sql_dd2_rate', false, 'Deduction 2');
		// $xcrud->fields('sql_wrd_name, sql_wrd_code, sql_wrd_description, sql_wrd_rate', false, 'Worked RD');
		// $xcrud->fields('sql_wph_name, sql_wph_code, sql_wph_description, sql_wph_rate', false, 'Worked PH');
		// $xcrud->fields('w_ot_name, w_ot_code, w_ot_description, w_ot_rate', false, 'Weekly OT Normal');
		// $xcrud->fields('w_ot_r_name, w_ot_r_code, w_ot_r_description, w_ot_r_rate', false, 'Weekly OT RD');
		// $xcrud->fields('w_ot_p_name, w_ot_p_code, w_ot_p_description, w_ot_p_rate', false, 'Weekly OT PH');
		// $xcrud->fields('sql_wsh_name, sql_wsh_code, sql_wsh_description, sql_wsh_rate', false, 'Shift Worked Hours');

		$xcrud->label(array('sql_d_ot_name' => 'Name', 'sql_d_ot_code' => 'Code', 'sql_d_ot_description' => 'Description', 'sql_d_ot_rate' => 'Rate'));
		$xcrud->label(array('sql_d_late_name' => 'Name', 'sql_d_late_code' => 'Code', 'sql_d_late_description' => 'Description', 'sql_d_late_rate' => 'Rate'));

		if(in_array($cid, [152, 215, 206])) {
			$xcrud->label(array('sql_aa_name' => 'Name', 'sql_aa_code' => 'Code', 'sql_aa_description' => 'Description', 'sql_aa_rate' => 'Rate'));
		}
		if(in_array($cid, [215, 152])) {
			$xcrud->label(array('sql_nsa_name' => 'Name', 'sql_nsa_code' => 'Code', 'sql_nsa_description' => 'Description', 'sql_nsa_rate' => 'Rate'));
		}

		$xcrud->fields('sql_ot1_name, sql_ot1_code, sql_ot1_description, sql_ot1_rate', false, 'OT Normal');
		$xcrud->fields('sql_ot2_name, sql_ot2_code, sql_ot2_description, sql_ot2_rate', false, 'OT RD');
		$xcrud->fields('sql_ot_off_name, sql_ot_off_code, sql_ot_off_description, sql_ot_off_rate', false, 'OT Off');
		$xcrud->fields('sql_ot3_name, sql_ot3_code, sql_ot3_description, sql_ot3_rate', false, 'OT PH x3');
		$xcrud->fields('sql_ot3_name_x2 , sql_ot3_code_x2, sql_ot3_description_x2, sql_ot3_rate_x2', false, 'OT PH x2');

		$xcrud->fields('sql_ab_name, sql_ab_code, sql_ab_description, sql_ab_rate', false, 'Absent');
		$xcrud->fields('sql_ul_name, sql_ul_code, sql_ul_description, sql_ul_rate', false, 'Unpaid Leave');
		$xcrud->fields('sql_e_l_name, sql_e_l_code, sql_e_l_description, sql_e_l_rate', false, 'Lateness Deduction');
		$xcrud->fields('sql_dw_name, sql_dw_code, sql_dw_description, sql_dw_rate', false, 'Daily Wage');
		$xcrud->fields('sql_dd1_name, sql_dd1_code, sql_dd1_description, sql_dd1_rate', false, 'Deduction 1');
		$xcrud->fields('sql_dd2_name, sql_dd2_code, sql_dd2_description, sql_dd2_rate', false, 'Deduction 2');
		$xcrud->fields('sql_wrd_name, sql_wrd_code, sql_wrd_description, sql_wrd_rate', false, 'Worked RD');
		$xcrud->fields('sql_w_off_name, sql_w_off_code, sql_w_off_description, sql_w_off_rate', false, 'Worked Off');
		$xcrud->fields('sql_wph_name, sql_wph_code, sql_wph_description, sql_wph_rate', false, 'Worked PH x3');
		$xcrud->fields('sql_wph_name_x2, sql_wph_code_x2, sql_wph_description_x2, sql_wph_rate_x2', false, 'Worked PH x2');

		// $xcrud->fields('w_ot_name, w_ot_code, w_ot_description, w_ot_rate', false, 'Weekly OT Normal');
		// $xcrud->fields('w_ot_r_name, w_ot_r_code, w_ot_r_description, w_ot_r_rate', false, 'Weekly OT RD');
		// $xcrud->fields('w_ot_p_name, w_ot_p_code, w_ot_p_description, w_ot_p_rate', false, 'Weekly OT PH');
		$xcrud->fields('sql_wsh_name, sql_wsh_code, sql_wsh_description, sql_wsh_rate', false, 'Shift Worked Hours');
		$xcrud->fields('sql_wh_name, sql_wh_code, sql_wh_description, sql_wh_rate', false, 'Worked Hours');

		$xcrud->fields('sql_d_ot_name, sql_d_ot_code, sql_d_ot_description, sql_d_ot_rate', false, 'Daily OT');
		$xcrud->fields('sql_d_late_name, sql_d_late_code, sql_d_late_description, sql_d_late_rate', false, 'Daily Late');

		if(in_array($cid, [152, 215, 206])) {
			$tabName = 'Attendance Allowance';
			if($cid == 206) {
				$tabName = 'Food Allowance';
			} else if($cid == 152) {
				$tabName = 'Special Allowance';
			}
			$xcrud->fields('sql_aa_name, sql_aa_code, sql_aa_description, sql_aa_rate', false, $tabName);
		}
		if(in_array($cid, [215, 152])) {
			$tabName = 'Night Shift Allowance';
			if($cid == 152) {
				$tabName = 'Attendance Allowance';
			}
			$xcrud->fields('sql_nsa_name, sql_nsa_code, sql_nsa_description, sql_nsa_rate', false, $tabName);
		}

		$xcrud->before_update('before_sql_payroll_settings_update');

		$xcrud->unset_remove();
		$xcrud->unset_add();
		$xcrud->unset_print();
		$xcrud->unset_csv();
		$xcrud->unset_search();
		$xcrud->unset_pagination();
		$xcrud->unset_limitlist();
		$xcrud->unset_sortable();
		$xcrud->unset_list();
		$xcrud->unset_title();

		$data['sql_payroll_settings'] = $xcrud->render('edit', $branch_id);
		$xcrud_payroll_settings = $this->load->view('sql_payroll_xcrud', $data, TRUE);
		echo $xcrud_payroll_settings;
	}

	public function download($path, $file_name)
	{
		// replace the disallowed characters back in the path
		$path = str_replace(array('-', '_', '~'), array('+', '/', '='), $path);

		$path = base64_decode($path);
		header('Content-Type: application/pdf');
		header("Content-Transfer-Encoding: Binary");
		header("Content-disposition: inline; filename=" . $file_name);
		readfile($path);
	}

	public function pendingOvertimeLogFile($style, $all_data, $date2, $branch_name, $first_day, $last_day, $gni01 = false, $short_ot_data = [])
	{
		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);
		$table_columns = array("Trans Date", "Post Date", "Employee", "Code", "Description", "Work Unit", "Rate");

		$column = 0;

		foreach ($table_columns as $field) {
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

			$column++;
		}

		$row = 2;

		foreach ($all_data as $key => $r) {
			$sql_data_of_emp = get_sql_data($r["employee"]->branch_id);

			$month_overtime_deducted = $r['month_overtime_deducted'];
				
			if ($gni01) {
				$ot_data = $short_ot_data[$key];

				$ot_balance = 0;

				$ot = toDecimal($ot_data["month_overtime_deducted"]);
				$extra_ot = ($ot_data["worked_rest_days"] + $ot_data["worked_holidays"]) * 8;
				$extra_ot += toDecimal($ot_data["month_overtime_ph_x2"]);
				$extra_ot += toDecimal($ot_data["month_overtime_ph_x3"]);
				$extra_ot += toDecimal($ot_data["month_overtime_rd"]);
				$extra_ot += toDecimal($ot_data["month_overtime_off"]);
				if ($ot + $extra_ot > 104) {
					$new_ot = 104 - $extra_ot;
					$ot_balance = $ot - $new_ot;
				}

				$month_overtime_deducted = $month_overtime_deducted - $ot_balance;
				$month_overtime_deducted = $month_overtime_deducted < 0 ? 0 : $month_overtime_deducted;
			}
			
			if ($month_overtime_deducted != 0) {
				$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $date2->format('d/m/Y'));
				$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
				$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r["employee"]->special_id);
				$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_ot1_code"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_ot1_description"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $month_overtime_deducted);
				$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $sql_data_of_emp["sql_ot1_rate"]);
				$row++;
			}
			if ($r['month_overtime_rd'] != 0) {
				$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $date2->format('d/m/Y'));
				$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
				$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r["employee"]->special_id);
				$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_ot2_code"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_ot2_description"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $r["month_overtime_rd"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $sql_data_of_emp["sql_ot2_rate"]);
				$row++;
			}
			if ($r['month_overtime_ph_x3'] != 0) {
				$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $date2->format('d/m/Y'));
				$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
				$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r["employee"]->special_id);
				$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_ot3_code"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_ot3_description"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $r["month_overtime_ph_x3"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $sql_data_of_emp["sql_ot3_rate"]);
				$row++;
			}
			if ($r['month_overtime_ph_x2'] != 0) {
				$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $date2->format('d/m/Y'));
				$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
				$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r["employee"]->special_id);
				$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_ot3_code_x2"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_ot3_description_x2"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $r["month_overtime_ph_x2"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $sql_data_of_emp["sql_ot3_rate_x2"]);
				$row++;
			}
			if ($r['month_overtime_off'] != 0) {
				$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $date2->format('d/m/Y'));
				$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
				$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r["employee"]->special_id);
				$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_ot_off_code"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_ot_off_description"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $r["month_overtime_off"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $sql_data_of_emp["sql_ot_off_rate"]);
				$row++;
			}
		}

		foreach (range('A', 'M') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}


		$file_name = "($branch_name) SQL Payroll - $first_day to $last_day " . time() . ".csv";

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '"');
		//$object_writer->save('php://output');
		$new_file = "uploads/summary/pending-overtime-log-" . time() . ".csv";
		$object_writer->save($new_file);

		return $new_file;
	}

	public function pendingUnpaidLeavesLogFile($style, $unpaid_leaves_absent_days, $cid, $date2, $branch_name, $first_day, $last_day)
	{
		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);
		$table_columns = array("Trans Date", "Post Date", "Employee", "Code", "Description", "Leave Days", "Amount");



		$column = 0;

		foreach ($table_columns as $field) {
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

			$column++;
		}

		$row = 2;

		foreach ($unpaid_leaves_absent_days as $key => $dated_array) {
			foreach ($dated_array as $unpaid_leave) {
				if ($unpaid_leave["type"] == "absent" && $cid == 153) {
					continue;
				}
				$sql_data_of_emp = get_sql_data($unpaid_leave['branch_id']);
				$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $key);
				$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
				$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $unpaid_leave["employee_special_id"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_ul_code"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_ul_description"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $unpaid_leave['unpaid_leave']);
				$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $sql_data_of_emp["sql_ul_rate"]);

				$row += 1;
			}
		}

		foreach (range('A', 'M') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}


		$file_name = "($branch_name) SQL Payroll Leaves - $first_day to $last_day " . time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.csv"');
		//$object_writer->save('php://output');
		$new_file = "uploads/summary/pending-unpaid-leaves-log-" . time() . ".csv";
		$object_writer->save($new_file);

		return $new_file;
	}

	public function pendingAbsentLogFile($style, $unpaid_leaves_absent_days, $date2, $branch_name, $first_day, $last_day)
	{
		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);
		$table_columns = array("Trans Date", "Post Date", "Employee", "Code", "Description", "Leave Days", "Amount");



		$column = 0;

		foreach ($table_columns as $field) {
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

			$column++;
		}

		$row = 2;

		foreach ($unpaid_leaves_absent_days as $key => $dated_array) {
			foreach ($dated_array as $unpaid_leave) {
				if ($unpaid_leave["type"] == "unpaid_leave") {
					continue;
				}
				$sql_data_of_emp = get_sql_data($unpaid_leave['branch_id']);
				$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $key);
				$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
				$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $unpaid_leave["employee_special_id"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_ab_code"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_ab_description"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $unpaid_leave['unpaid_leave']);
				$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $sql_data_of_emp["sql_ab_rate"]);

				$row += 1;
			}
		}

		foreach (range('A', 'M') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}


		$file_name = "($branch_name) SQL Payroll Absent - $first_day to $last_day " . time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.csv"');
		//$object_writer->save('php://output');
		$new_file = "uploads/summary/pending-absents-log-" . time() . ".csv";
		$object_writer->save($new_file);

		return $new_file;
	}

	public function pendingDailyOTLogFile($style, $daily_ot_array, $date2, $branch_name, $first_day, $last_day)
	{
		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);
		$table_columns = array("Trans Date", "Post Date", "Employee", "Code", "Description", "Daily OT", "Amount");



		$column = 0;

		foreach ($table_columns as $field) {
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

			$column++;
		}

		$row = 2;

		foreach ($daily_ot_array as $key => $dated_array) {
			foreach ($dated_array as $daily_ot) {
				$sql_data_of_emp = get_sql_data($daily_ot['branch_id']);
				$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $key);
				$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
				$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $daily_ot["employee_special_id"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_d_ot_code"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_d_ot_description"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $daily_ot['daily_overtime']);
				$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $sql_data_of_emp["sql_d_ot_rate"]);

				$row += 1;
			}
		}

		foreach (range('A', 'M') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}


		$file_name = "($branch_name) SQL Payroll Daily OT - $first_day to $last_day " . time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.csv"');
		//$object_writer->save('php://output');
		$new_file = "uploads/summary/pending-daily-ot-log-" . time() . ".csv";
		$object_writer->save($new_file);

		return $new_file;
	}

	public function pendingDailyLateLogFile($style, $daily_late_array, $date2, $branch_name, $first_day, $last_day)
	{
		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);
		$table_columns = array("Trans Date", "Post Date", "Employee", "Code", "Description", "Daily Late", "Amount");



		$column = 0;

		foreach ($table_columns as $field) {
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

			$column++;
		}

		$row = 2;

		foreach ($daily_late_array as $key => $dated_array) {
			foreach ($dated_array as $daily_late) {
				$sql_data_of_emp = get_sql_data($daily_late['branch_id']);
				$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $key);
				$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
				$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $daily_late["employee_special_id"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_d_late_code"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_d_late_description"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $daily_late['daily_late']);
				$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $sql_data_of_emp["sql_d_late_rate"]);

				$row += 1;
			}
		}

		foreach (range('A', 'M') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}


		$file_name = "($branch_name) SQL Payroll Daily Late - $first_day to $last_day " . time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.csv"');
		//$object_writer->save('php://output');
		$new_file = "uploads/summary/pending-daily-late-log-" . time() . ".csv";
		$object_writer->save($new_file);

		return $new_file;
	}

	public function pendingWorkedRestDaysLogFile($style, $cid, $all_data, $worked_rest_days_array, $date2, $branch_name, $first_day, $last_day)
	{
		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);
		$table_columns = array("Trans Date", "Post Date", "Employee", "Code", "Description", "Worked Days", "Rate");

		$column = 0;

		foreach ($table_columns as $field) {
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

			$column++;
		}

		$row = 2;
		if ($cid == 196) {
			foreach ($all_data as $r) {
				$sql_data_of_emp = get_sql_data($r["employee"]->branch_id);
				$rest_day_entitlement = $r["employee"]->rest_day_entitlement;
				$total_rest_days_used = $r["total_rest_days_used"];
				$balance_rest_days = $rest_day_entitlement - $total_rest_days_used;
				if ($balance_rest_days != 0) {
					$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $date2->format('d/m/Y'));
					$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
					$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r["employee"]->special_id);
					$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_wrd_code"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_wrd_description"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $balance_rest_days);
					$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $sql_data_of_emp["sql_wrd_rate"]);

					$row += 1;
				}
			}
		} else {
			foreach ($worked_rest_days_array as $key => $dated_array) {
				foreach ($dated_array as $worked_rd) {
					$sql_data_of_emp = get_sql_data($worked_rd['branch_id']);
					$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $key);
					$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
					$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $worked_rd["employee_special_id"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_wrd_code"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_wrd_description"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $worked_rd['worked_rest_day']);
					$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $sql_data_of_emp["sql_wrd_rate"]);

					$row += 1;
				}
			}
		}

		foreach (range('A', 'M') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}


		$file_name = "($branch_name) SQL Payroll Leaves - $first_day to $last_day " . time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.csv"');
		//$object_writer->save('php://output');
		$new_file = "uploads/summary/pending-worked-rest-days-log-" . time() . ".csv";
		$object_writer->save($new_file);

		return $new_file;
	}

	public function pendingWorkedOffDaysLogFile($style, $worked_off_days_array, $date2, $branch_name, $first_day, $last_day)
	{
		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);
		$table_columns = array("Trans Date", "Post Date", "Employee", "Code", "Description", "Worked Days", "Rate");

		$column = 0;

		foreach ($table_columns as $field) {
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

			$column++;
		}

		$row = 2;

		foreach ($worked_off_days_array as $key => $dated_array) {
			foreach ($dated_array as $worked_off) {
				$sql_data_of_emp = get_sql_data($worked_off['branch_id']);
				$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $key);
				$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
				$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $worked_off["employee_special_id"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_w_off_code"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_w_off_description"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $worked_off['worked_off_day']);
				$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $sql_data_of_emp["sql_w_off_rate"]);

				$row += 1;
			}
		}

		foreach (range('A', 'M') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}

		$file_name = "($branch_name) SQL Payroll Leaves - $first_day to $last_day " . time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.csv"');
		//$object_writer->save('php://output');
		$new_file = "uploads/summary/pending-worked-off-days-log-" . time() . ".csv";
		$object_writer->save($new_file);

		return $new_file;
	}

	public function pendingWorkedPublicHolidaysLogFile($style, $worked_holidays_array, $date2, $branch_name, $first_day, $last_day)
	{
		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);
		$table_columns = array("Trans Date", "Post Date", "Employee", "Code", "Description", "Worked Days", "Rate");

		$column = 0;

		foreach ($table_columns as $field) {
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

			$column++;
		}

		$row = 2;

		foreach ($worked_holidays_array as $key => $dated_array) {
			foreach ($dated_array as $worked_hd) {
				$sql_data_of_emp = get_sql_data($worked_hd['branch_id']);
				// echo '<pre>';
				// print_r($worked_hd["holiday_rate"]);
				// echo '<pre>';die;
				if ($worked_hd["holiday_rate"] == "x3") {
					$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $key);
					$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
					$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $worked_hd["employee_special_id"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_wph_code"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_wph_description"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $worked_hd['worked_holiday']);
					$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $sql_data_of_emp["sql_wph_rate"]);
				} else {
					$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $key);
					$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
					$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $worked_hd["employee_special_id"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_wph_code_x2"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_wph_description_x2"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $worked_hd['worked_holiday']);
					$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $sql_data_of_emp["sql_wph_rate_x2"]);
				}


				$row += 1;
			}
		}

		foreach (range('A', 'M') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}


		$file_name = "($branch_name) SQL Payroll Leaves - $first_day to $last_day " . time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.csv"');
		//$object_writer->save('php://output');
		$new_file = "uploads/summary/pending-worked-public-holidays-log-" . time() . ".csv";
		$object_writer->save($new_file);

		return $new_file;
	}

	public function pendingDailyWageLogFile($style, $all_data, $date2, $branch_name, $first_day, $last_day)
	{
		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);
		$table_columns = array("Trans Date", "Post Date", "Employee", "Code", "Description", "Work Unit", "Amount");

		$column = 0;

		foreach ($table_columns as $field) {
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

			$column++;
		}

		$row = 2;

		foreach ($all_data as $r) {
			$sql_data_of_emp = get_sql_data($r["employee"]->branch_id);

			if ($r['employee']->is_daily_waged == 0) continue;
			$daily_wage_value = $r["worked_days"] + $r["worked_rest_days"] + $r["worked_holidays"];
			if ($daily_wage_value != 0) {
				$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $date2->format('d/m/Y'));
				$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
				$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r["employee"]->special_id);
				$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_dw_code"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_dw_description"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $daily_wage_value);
				$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, "");
				$row++;
			}
		}

		foreach (range('A', 'M') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}

		$file_name = "($branch_name) SQL Payroll - $first_day to $last_day " . time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.csv"');
		//$object_writer->save('php://output');
		$new_file = "uploads/summary/pending-daily-wage-log-" . time() . ".csv";
		$object_writer->save($new_file);

		return $new_file;
	}

	public function pendingEarlyLateLogFile($style, $all_data, $date2, $branch_name, $first_day, $last_day)
	{
		$early_late_obj = new PHPExcel();

		$early_late_obj->setActiveSheetIndex(0);
		$early_late_obj->getDefaultStyle()->applyFromArray($style);
		$early_late_columns = array("Trans Date", "Post Date", "Employee", "Code", "Description", "Leave Days", "Amount");

		$column = 0;
		foreach ($early_late_columns as $field) {
			$early_late_obj->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$early_late_obj->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

			$column++;
		}

		$early_late_row = 2;

		foreach ($all_data as $r) {
			$sql_data_of_emp = get_sql_data($r["employee"]->branch_id);

			if ($r['late_days'] != 0) {
				$early_late_obj->getActiveSheet()->setCellValueByColumnAndRow(0, $early_late_row, $date2->format('d/m/Y'));
				$early_late_obj->getActiveSheet()->setCellValueByColumnAndRow(1, $early_late_row, $date2->format('d/m/Y'));
				$early_late_obj->getActiveSheet()->setCellValueByColumnAndRow(2, $early_late_row, $r["employee"]->special_id);
				$early_late_obj->getActiveSheet()->setCellValueByColumnAndRow(3, $early_late_row, $sql_data_of_emp["sql_e_l_code"]);
				$early_late_obj->getActiveSheet()->setCellValueByColumnAndRow(4, $early_late_row, $sql_data_of_emp["sql_e_l_description"]);
				$early_late_obj->getActiveSheet()->setCellValueByColumnAndRow(5, $early_late_row, $r['late_days']);
				$early_late_obj->getActiveSheet()->setCellValueByColumnAndRow(6, $early_late_row, "");
				$early_late_row++;
			}
		}

		foreach (range('A', 'M') as $columnID) {
			$early_late_obj->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}

		$early_late_file_name = "($branch_name) SQL Early Late - $first_day to $last_day " . time();

		$object_writer = PHPExcel_IOFactory::createWriter($early_late_obj, 'CSV');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $early_late_file_name . '.csv"');
		//$object_writer->save('php://output');
		$early_late_file = "uploads/summary/pending-early-late-log-" . time() . ".csv";
		$object_writer->save($early_late_file);

		return $early_late_file;
	}

	public function pendingDeductionLogFile($style, $all_data, $cid, $date2, $branch_name, $first_day, $last_day)
	{
		$deductions_obj = new PHPExcel();
		
		$deductions_obj->setActiveSheetIndex(0);
		$deductions_obj->getDefaultStyle()->applyFromArray($style);
		$deductions_obj_columns = array("Trans Date", "Post Date", "Employee", "Code", "Description", "Work Unit", "Rate", "Amount");

		$column = 0;
		foreach ($deductions_obj_columns as $field) {
			$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$deductions_obj->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

			$column++;
		}

		$deductions_row = 2;
		
		foreach ($all_data as $r) {
			$sql_data_of_emp = get_sql_data($r["employee"]->branch_id);
			// check company id for JL01
			if ($cid == 196) {
				$mi_mo = $r["total_missing_in_out"];
				if ($mi_mo != 0) {
					$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(0, $deductions_row, $date2->format('d/m/Y'));
					$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(1, $deductions_row, $date2->format('d/m/Y'));
					$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(2, $deductions_row, $r["employee"]->special_id);
					$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(3, $deductions_row, $sql_data_of_emp["sql_dd1_code"]);
					$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(4, $deductions_row, $sql_data_of_emp["sql_dd1_description"]);
					$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(5, $deductions_row, $mi_mo);
					$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(6, $deductions_row, $r["employee"]->mi_mo_rate);
					$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(7, $deductions_row, $mi_mo * $r["employee"]->mi_mo_rate);
					$deductions_row++;
				}
				$lateness_time = time_to_minutes($r["lateness_time"]);
				if ($lateness_time != 0) {
					$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(0, $deductions_row, $date2->format('d/m/Y'));
					$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(1, $deductions_row, $date2->format('d/m/Y'));
					$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(2, $deductions_row, $r["employee"]->special_id);
					$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(3, $deductions_row, $sql_data_of_emp["sql_dd2_code"]);
					$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(4, $deductions_row, $sql_data_of_emp["sql_dd2_description"]);
					$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(5, $deductions_row, $lateness_time);
					$employee_rate = $lateness_time < 100 ? $r["employee"]->lateness_deduction_99 : $r["employee"]->lateness_deduction_100;
					$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(6, $deductions_row, $employee_rate);
					$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(7, $deductions_row, $lateness_time * $employee_rate);
					$deductions_row++;
				}
			} else {
				$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(0, $deductions_row, $date2->format('d/m/Y'));
				$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(1, $deductions_row, $date2->format('d/m/Y'));
				$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(2, $deductions_row, $r["employee"]->special_id);
				$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(3, $deductions_row, $sql_data_of_emp["sql_dd1_code"]);
				$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(4, $deductions_row, $sql_data_of_emp["sql_dd1_description"]);
				$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(5, $deductions_row, "1");
				$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(6, $deductions_row, $sql_data_of_emp["sql_dd1_rate"]);
				$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(7, $deductions_row, "");
				$deductions_row++;
				$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(0, $deductions_row, $date2->format('d/m/Y'));
				$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(1, $deductions_row, $date2->format('d/m/Y'));
				$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(2, $deductions_row, $r["employee"]->special_id);
				$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(3, $deductions_row, $sql_data_of_emp["sql_dd2_code"]);
				$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(4, $deductions_row, $sql_data_of_emp["sql_dd2_description"]);
				$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(5, $deductions_row, "1");
				$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(6, $deductions_row, $sql_data_of_emp["sql_dd2_rate"]);
				$deductions_obj->getActiveSheet()->setCellValueByColumnAndRow(7, $deductions_row, "");
				$deductions_row++;
			}
		}

		foreach (range('A', 'M') as $columnID) {
			$deductions_obj->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}

		$deductions_file_name = "($branch_name) SQL deductions - $first_day to $last_day " . time();

		$object_writer = PHPExcel_IOFactory::createWriter($deductions_obj, 'CSV');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $deductions_file_name . '.csv"');
		//$object_writer->save('php://output');
		$deductions_file = "uploads/summary/pending-deductions-log-" . time() . ".csv";
		$object_writer->save($deductions_file);

		return $deductions_file;
	}

	public function pendingShiftWorkedHoursFile($style, $all_data, $date2, $branch_name, $first_day, $last_day)
	{
		$worked_shift_days_object = new PHPExcel();

		$worked_shift_days_object->setActiveSheetIndex(0);
		$worked_shift_days_object->getDefaultStyle()->applyFromArray($style);
		$table_columns = array("Trans Date", "Post Date", "Employee", "Code", "Description", "Worked Days", "Rate");

		$column = 0;

		foreach ($table_columns as $field) {
			$worked_shift_days_object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$worked_shift_days_object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

			$column++;
		}

		$row = 2;

		foreach ($all_data as $r) {
			$sql_data_of_emp = get_sql_data($r["employee"]->branch_id);

			$shift_hours = ($r["worked_days"] + $r["worked_rest_days"] + $r["worked_holidays"]) * 8;
			if ($r["employee"]->is_shift_hours) {
				$shift_hours = toDecimal($r["shift_hours_total"]);
			}

			$worked_shift_days_object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $date2->format('d/m/Y'));
			$worked_shift_days_object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
			$worked_shift_days_object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r["employee"]->special_id);
			$worked_shift_days_object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_wsh_code"]);
			$worked_shift_days_object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_wsh_description"]);
			$worked_shift_days_object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $shift_hours);
			$worked_shift_days_object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $sql_data_of_emp["sql_wsh_rate"]);
			$row++;
		}

		foreach (range('A', 'M') as $columnID) {
			$worked_shift_days_object->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}


		$file_name = "($branch_name) pending-shift-worked-hours - $first_day to $last_day " . time();

		$object_writer = PHPExcel_IOFactory::createWriter($worked_shift_days_object, 'CSV');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.csv"');
		//$object_writer->save('php://output');
		$new_file = "uploads/summary/pending-shift-worked-hours-" . time() . ".csv";
		$object_writer->save($new_file);

		return $new_file;
	}

	public function pendingWorkedHoursFile($style, $all_data, $date2, $branch_name, $first_day, $last_day)
	{
		$worked_shift_days_object = new PHPExcel();

		$worked_shift_days_object->setActiveSheetIndex(0);
		$worked_shift_days_object->getDefaultStyle()->applyFromArray($style);
		$table_columns = array("Trans Date", "Post Date", "Employee", "Code", "Description", "Worked hours", "Rate");

		$column = 0;

		foreach ($table_columns as $field) {
			$worked_shift_days_object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$worked_shift_days_object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

			$column++;
		}

		$row = 2;

		foreach ($all_data as $r) {
			$sql_data_of_emp = get_sql_data($r["employee"]->branch_id);
			$worked_shift_days_object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $date2->format('d/m/Y'));
			$worked_shift_days_object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
			$worked_shift_days_object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r["employee"]->special_id);
			$worked_shift_days_object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_wh_code"]);
			$worked_shift_days_object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_wh_description"]);
			$worked_shift_days_object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, toDecimal($r["work"]));
			$worked_shift_days_object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $sql_data_of_emp["sql_wh_rate"]);
			$row++;
		}

		foreach (range('A', 'M') as $columnID) {
			$worked_shift_days_object->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}


		$file_name = "($branch_name) pending-worked-hours - $first_day to $last_day " . time();

		$object_writer = PHPExcel_IOFactory::createWriter($worked_shift_days_object, 'CSV');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.csv"');
		//$object_writer->save('php://output');
		$new_file = "uploads/summary/pending-worked-hours-" . time() . ".csv";
		$object_writer->save($new_file);

		return $new_file;
	}

	public function pendingLeaveApplicationLogFile($style, $paid_leaves_array, $branch_name, $first_day, $last_day)
	{
		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);
		$table_columns = array("Code", "Date", "Leave Type", "Description", "Day");

		$column = 0;

		foreach ($table_columns as $field) {
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

			$column++;
		}

		$row = 2;

		foreach ($paid_leaves_array as $key => $dated_array) {
			foreach ($dated_array as $paid_leave) {
				$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $paid_leave["employee_special_id"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $key);
				$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $paid_leave["leave_type"]);
				$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, "");
				$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $paid_leave['paid_leave']);

				$row += 1;
			}
		}

		foreach (range('A', 'M') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}


		$file_name = "($branch_name) SQL Payroll Leaves - $first_day to $last_day " . time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.csv"');
		//$object_writer->save('php://output');
		$new_file = "uploads/summary/pending-leave-application-log-" . time() . ".csv";
		$object_writer->save($new_file);

		return $new_file;
	}

	public function pendingAllowanceLogFile($style, $all_data, $cid, $date2, $branch_name, $first_day, $last_day)
	{
		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);
		$table_columns = array("Trans Date", "Post Date", "Employee", "Code", "Description", "Work Unit", "Rate", "Amount");



		$column = 0;

		foreach ($table_columns as $field) {
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

			$column++;
		}

		$row = 2;
		if ($cid == 215) {
			foreach ($all_data as $r) {
				$sql_data_of_emp = get_sql_data($r["employee"]->branch_id);
				$attendance_allowance = $r["gbr_attendance_allowance"];
				$night_shifts = $r["gbr_night_shifts"];
				if ($attendance_allowance) {
					$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $date2->format('d/m/Y'));
					$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
					$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r["employee"]->special_id);
					$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_aa_code"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_aa_description"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, 1);
					$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $r["employee"]->aa_rate);
					$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $r["employee"]->aa_rate);

					$row += 1;
				}

				if ($night_shifts) {
					$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $date2->format('d/m/Y'));
					$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
					$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r["employee"]->special_id);
					$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_nsa_code"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_nsa_description"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $night_shifts);
					$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $r["employee"]->nsa_rate);
					$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $night_shifts * $r["employee"]->nsa_rate);

					$row += 1;
				}
			}
		} else if ($cid == 152) {
			foreach ($all_data as $r) {
				$sql_data_of_emp = get_sql_data($r["employee"]->branch_id);
				$worked_days = $r["worked_days"] + $r["total_holidays"] + $r["worked_rest_days"];
				$amount = $worked_days * $r["employee"]->aa_rate;
				if ($worked_days) {
					$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $date2->format('d/m/Y'));
					$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
					$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r["employee"]->special_id);
					$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_aa_code"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_aa_description"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $worked_days);
					$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $r["employee"]->aa_rate);
					$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $amount);

					$row += 1;
				}

				$lsk_non_worked_days = $r["lsk_non_worked_days"];
				$amount = $lsk_non_worked_days * 50;
				$total_allowance = $r["employee"]->ta_rate ? $r["employee"]->ta_rate : 100;
				$final_amount = $total_allowance - $amount;

				if ($final_amount > 0) {
					$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $date2->format('d/m/Y'));
					$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
					$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r["employee"]->special_id);
					$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_nsa_code"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_nsa_description"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, 1);
					$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $final_amount);
					$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $final_amount);

					$row += 1;
				}
			}
		} else if ($cid == 206) {
			foreach ($all_data as $r) {
				$sql_data_of_emp = get_sql_data($r["employee"]->branch_id);
				$food_allowance_days = $r["food_allowance_days"];
				if ($food_allowance_days) {
					$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $date2->format('d/m/Y'));
					$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
					$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r["employee"]->special_id);
					$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $sql_data_of_emp["sql_aa_code"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $sql_data_of_emp["sql_aa_description"]);
					$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $food_allowance_days);
					$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $r["employee"]->food_rate);
					$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $food_allowance_days * $r["employee"]->food_rate);

					$row += 1;
				}
			}
		} else {
			foreach ($all_data as $r) {
				$employee = &$r["employee"];
				if ($employee->is_att_all == 1) {
					$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $date2->format('d/m/Y'));
					$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
					$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $employee->special_id);
					$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $employee->att_all_code);
					$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $employee->att_all_desc);
					$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, 1);
					$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $employee->att_all_amount);
					$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $employee->att_all_amount);
					$row++;
				}
			}
		}

		foreach (range('A', 'M') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}


		$file_name = "($branch_name) SQL Payroll Pending Allowance - $first_day to $last_day " . time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.csv"');
		//$object_writer->save('php://output');
		$new_file = "uploads/summary/pending-allowance-log-" . time() . ".csv";
		$object_writer->save($new_file);

		return $new_file;
	}

	public function pendingAllowanceReportLogFile($style, $allowances, $date2, $branch_name, $first_day, $last_day)
	{
		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);
		$table_columns = array("Trans Date", "Post Date", "Employee", "Code", "Description", "Work Unit", "Rate", "Amount");



		$column = 0;

		foreach ($table_columns as $field) {
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

			$column++;
		}

		$row = 2;
		foreach ($allowances as $allowance) {
			$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $date2->format('d/m/Y'));
			$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $date2->format('d/m/Y'));
			$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $allowance->special_id);
			$object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $allowance->code);
			$object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $allowance->description);
			$object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $allowance->work_unit);
			$object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $allowance->rate);
			$object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $allowance->amount);
			$row++;
		}

		foreach (range('A', 'M') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}


		$file_name = "($branch_name) SQL Payroll Allowance Report - $first_day to $last_day " . time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.csv"');
		//$object_writer->save('php://output');
		$new_file = "uploads/summary/pending-allowance-report-log-" . time() . ".csv";
		$object_writer->save($new_file);

		return $new_file;
	}

	public function changeLoadingBar2($percentage)
	{
		echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
		ob_flush();
		flush();
	}

	public function otBalanceSheet($style, $all_short_data, $all_short_ot_data, $branch_name, $first_day, $last_day)
	{
		$object = new PHPExcel();

		$object->setActiveSheetIndex(0);
		$object->getDefaultStyle()->applyFromArray($style);
		$table_columns = array("Name", "Employee ID", "OT Balance");

		$column = 0;

		foreach ($table_columns as $field) {
			$object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $field);
			$object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);

			$column++;
		}

		$row = 2;

		foreach ($all_short_data as $key => $r) {
			$ot_data = $all_short_ot_data[$key];

			$ot_balance = 0;

			$ot = toDecimal($ot_data["month_overtime_deducted"]);
			$extra_ot = ($ot_data["worked_rest_days"] + $ot_data["worked_holidays"]) * 8;
			$extra_ot += toDecimal($ot_data["month_overtime_ph_x2"]);
			$extra_ot += toDecimal($ot_data["month_overtime_ph_x3"]);
			$extra_ot += toDecimal($ot_data["month_overtime_rd"]);
			$extra_ot += toDecimal($ot_data["month_overtime_off"]);
			if ($ot + $extra_ot > 104) {
				$new_ot = 104 - $extra_ot;
				$ot_balance = $ot - $new_ot;
			}

			if ($ot_balance == 0) continue;

			$object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $r["employee"]->first_name);
			$object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $r["employee"]->special_id);
			$object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $ot_balance);

			$row += 1;
		}

		foreach (range('A', 'M') as $columnID) {
			$object->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}

		$file_name = "($branch_name) OT Balance Sheet - $first_day to $last_day " . time();

		$object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.csv"');
		//$object_writer->save('php://output');
		$new_file = "uploads/summary/OT Balance Sheet-" . time() . ".csv";
		$object_writer->save($new_file);

		return $new_file;
	}
}
