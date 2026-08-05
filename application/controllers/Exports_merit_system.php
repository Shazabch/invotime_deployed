<?php
class Exports_merit_system extends CI_Controller
{

	function __construct()
	{
		parent::__construct();

		if (is_null(get_user())) {
			redirect("welcome");
		}
	}

	public function index()
	{
		if (!is_page_permitted('exports_merit_system')) {
            redirect_if_not_permitted();
        }

		$data['pageTitle'] = "Export Merit System";
		$data['active_menu'] = "exports_merit_system";
		$this->load->view('header', $data);
		$data["menus"] = get_menus();
		$this->load->view('sidebar', $data);

		$current_date = DateTime::createFromFormat('Y-m-d', date('Y-m-d'));

		$data['company_id'] = get_user()["company_id"];
		$data['selected_year'] = $current_date->format('Y');
		$data['selected_month'] = $current_date->format('m');

		$this->load->view('merit_system/export_merit_system', $data);
		$this->load->view('footer', $data);
	}

	public function summary_pdf()
	{
		$current_user = get_user();
		$data['current_user'] = $current_user;
		$data["selected_branch_id"] = 0;
		$data["selected_dep_id"] = 0;
		$data["selected_month"] = 0;
		$cid = $current_user["company_id"];
		$bid = $current_user["branch_id"];
		$permissions_level = $current_user["permissions_level"];
		$interval_minutes = get_interval_minutes($cid);

		$where_filter = "";
		// if (!empty($this->input->post("branch"))) {
		//     $data["selected_branch_id"] = $this->input->post("branch");
		//     $where_filter = $where_filter . " branch_id = " . $this->input->post("branch") . " AND ";
		// }

		// if (!empty($this->input->post("emp"))) {
		//     $data["selected_emp_id"] = $this->input->post("emp");
		//     $where_filter = $where_filter . " e.id = " . $this->input->post("emp") . " AND ";
		// }

		// if (!empty($this->input->post("dep"))) {
		//     $data["selected_dep_id"] = $this->input->post("dep");
		//     $where_filter = $where_filter . " department_id = " . $this->input->get("dep") . " AND ";
		// }

		if (!empty($this->input->post("month")) && !empty($this->input->post("year"))) {
			$month = $data["selected_month"] = $this->input->post("month");
			$year = $data["selected_year"] = $this->input->post("year");
		} else {
			redirect("merit_system?month=" . date('m') . "&year=" . date('Y'));
			return;
		}

		$data['month_f'] = $month_f = $month;
		$data['year_f'] = $year_f = $year;

		$year = $data["selected_year"];
		$where_filter = $where_filter . " e.company_id = " . $cid;
		$branch_id = array();
		if (isset($_POST["branch"])) {
			$branch_id = $_POST["branch"];
		}
		if ($permissions_level == "Outlet") {
			$branch_id = array($current_user["branch_id"]);
		}
		$branch_name = "needs to be fixed";
		if ($branch_id) {
			$branch_name = $this->db->select('group_concat(name) as name')->from('branches')->where_in('id', $branch_id)->get()->row()->name;
		} else {
			$branch_name = "All";
		}
		$data['branch_name'] = $branch_name;

		if ($_POST['type'] == "monthly") {
			flushLoadingBar(true);
			// $employees = $this->db->select('e.id,e.first_name,special_id,e.is_daily_waged, d.name as department, p.title as position,e.branch_id,is_ot,is_early_ot,inc_late_in,inc_late_break, inc_early_out, inc_short_hours,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,void_lateness_time_if_less_than,deduct_from_ot,deduct_from_ot_single,deduction_date')->from('employees e')->join('roles r', 'e.role_id = r.id', 'left')->join('departments d', 'd.id = e.department_id', 'left')->join('branches b', 'b.id = e.branch_id', 'left')->join('positions p', 'p.id = e.position_id', 'left')->where("$where_filter AND r.exclude_from_system = 'no' AND e.deleted_at is NULL AND employee_status = 'active'")->order_by('e.special_id', 'ASC')->get()->result();;
			$this->db->select('e.id,e.first_name,special_id,e.is_daily_waged, d.name as department, p.title as position,e.branch_id,is_ot,is_early_ot,inc_late_in,inc_late_break, inc_early_out, inc_short_hours,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,void_lateness_time_if_less_than,deduct_from_ot,deduct_from_ot_single,deduction_date')->from('employees e')->join('roles r', 'e.role_id = r.id', 'left')->join('departments d', 'd.id = e.department_id', 'left')->join('branches b', 'b.id = e.branch_id', 'left')->join('positions p', 'p.id = e.position_id', 'left')->where("$where_filter AND r.exclude_from_system = 'no' AND e.deleted_at is NULL
			AND (
				e.employee_status = 'active' 
				OR (e.employee_status = 'terminated' AND e.termination_date IS NOT NULL AND e.termination_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
				OR (e.employee_status = 'resigned' AND e.resignation_date IS NOT NULL AND e.resignation_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
			)")->order_by('e.special_id', 'ASC');

			if (!empty($this->input->post("emp"))) {
				$this->db->where_in('e.id', $this->input->post("emp"));
			}
			if (!empty($this->input->post("branch"))) {
				$this->db->where_in('e.branch_id', $this->input->post("branch"));
			}
			if (!empty($this->input->post("dep"))) {
				$this->db->where_in('e.department_id', $this->input->post("dep"));
			}
			if (!empty($this->input->post("position"))) {
				$this->db->where_in('e.position_id', $this->input->post("position"));
			}
			if (!empty($this->input->post("exclude_employee"))) {
				$this->db->where_not_in('e.id', $this->input->post("exclude_employee"));
			}
			$employees = $this->db->get()->result();

			$max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year);

			$employees_ids = ['0'];
			foreach ($employees as $employee) {
				$employees_ids[] = $employee->id;
			}

			$chunkedEmployeeIds = array_chunk($employees_ids, 20);
			$chunksCount = count($chunkedEmployeeIds);

			$first_day = sprintf("%04d-%02d-%02d", $year, $month, 1);
			$last_day = sprintf("%04d-%02d-%02d", $year, $month, $max_date);

			$company_working_hours = get_company_working_hours($cid);
			$company_ot_settings = get_company_ot_settings($cid);
			$company_early_ot_settings = get_company_early_ot_settings($cid);
			$branch_rest_days = $this->db->select('id,rest_days,off_days')->from('branches')->where('company_id', $cid)->get()->result();
			$public_holidays_all = get_public_holidays_with_name();

			$public_holidays = $public_holidays_all[0];
			$public_holidays_names = $public_holidays_all[1];

			$public_holidays_all = get_public_holidays_all();

			$merit_deduction_points = [];

			$result_list = [];
			$result_list_overnight = [];
			$clockings_news = [];
			$clockings_news_overnight = [];

			foreach ($chunkedEmployeeIds as $i => $chunk) {
				$result_list = array_merge($result_list, get_result_list($chunk, $first_day, $last_day));
				$result_list_overnight = array_merge($result_list_overnight, get_result_list_overnight($chunk, $first_day, $last_day));
				$clockings_news = array_merge($clockings_news, $this->db->select('id,employee_id,type,shift_id,date_format(datetime,"%H:%i") as clock_time,date_format(datetime, "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false)->from('clockings_news')->where('date(datetime) >=', $first_day)->where('date(datetime) <=', $last_day)->where_in('employee_id', $chunk)->where('deleted_at is null')->order_by('datetime')->get()->result());
				$clockings_news_overnight = array_merge($clockings_news_overnight, $this->db->select('id,employee_id,type,shift_id, date_format(datetime,"%H:%i") as clock_time,date_format(date_sub(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false)->from('clockings_news')->where('date(date_sub(datetime, interval ' . $interval_minutes .  ' minute)) >=', $first_day)->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $chunk)->where('deleted_at is null')->order_by('datetime')->get()->result());
				$percentage = floor((($i + 1) / $chunksCount) * 100);
				echo "<script>$('#preparing-data .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();
			}

			if ($permissions_level == "Outlet") {
				$shifts = $this->db->select('id')->from('shifts')->where('branch_id', $bid)->where('is_leave', 'no')->get()->result();
				$merit_deduction_points = $this->Merit->get_deduction_points($cid, $bid);
			} else {
				$shifts = $this->db->select('id')->from('shifts')->where('company_id', $cid)->where('is_leave', 'no')->get()->result();
				$this->load->model('Merit');
				$merit_deduction_points = $this->Merit->get_deduction_points($cid);
			}

			$shift_ids = array(0);
			foreach ($shifts as $s) {
				$shift_ids[] = $s->id;
			}

			$approved_ot_list = get_approved_ot_list($shift_ids, $first_day, $last_day);

			$output_employees = [];
			$days = array();

			for ($i = 1; $i <= $max_date; $i++) {
				$d["date"] = $i;
				$d["day"] = date('D', strtotime("$year-$month-$i"));
				$d["holiday"] = false;
				$holiday_index = array_search(sprintf("%04d-%02d-%02d", $year, $month, $i), $public_holidays);
				if ($holiday_index > -1) {
					$d["holiday"] = true;
					$d["holiday_name"] = $public_holidays_names[$holiday_index];
				}
				$days[] = $d;
			}

			$default_offenses = default_offenses();
			
			$filler_array = [];
			$employee_count = count($employees);
			foreach ($employees as $i => $employee) {
				$calculated_data = calculate_summary_data($employee->id, $first_day, $last_day, "merit_system", $employee, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days, $cid, $filler_array, $filler_array, $filler_array, $clockings_news, $clockings_news_overnight);
				$temp = calculate_merit($employee, $calculated_data, $permissions_level, $merit_deduction_points, $first_day, $last_day, $default_offenses);
				$output_employees[] = $temp;
				$percentage = floor((($i + 1) / $employee_count) * 100);

				echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();
			}

			// $data["total_pages"] = $total_pages;
			// $data["page"] = $page;
			// unset($_GET['page']);
			// $currentURL = current_url();
			// $data["pagination_url"] = $currentURL . '?' . http_build_query($_GET);
			$data["days"] = $days;
			$data["employees"] = $output_employees;
			// $data["filters"] = $this->load->view('filters', $data, true);
			$data["first_day_f"] = to_html_date($first_day);
			$data["last_day_f"] = to_html_date($last_day);

			$this->load->view('merit_system/merit_system_pdf', $data);
			$html = $this->output->get_output();


			$date = DateTime::createFromFormat('Y-m-d', $first_day);
			$data['from_f'] = $date->format('d/m/Y');
			$date = DateTime::createFromFormat('Y-m-d', $last_day);
			$data['to_f'] = $date->format('d/m/Y');

			// $data["all_data"] = $all_data;
			// $data["branch_name"] = $branch_name;

			if ($_POST['file_type'] == "pdf") {
				// $html2 = $this->load->view('merit_system/merit_system_pdf', $data, true);

				$this->dompdf->reset();
				$this->dompdf->loadHtml($html);
				$this->dompdf->setPaper("A4", "landscape");
				$this->dompdf->render();

				$output = $this->dompdf->output();
				$file_name = "$branch_name - Monthly Merit Report - $first_day to $last_day " . time() . ".pdf";
				$new_file = "uploads/summary/" . $file_name;
				file_put_contents($new_file, $output);

				$path = "uploads/summary/" . $file_name;

				echo "<script>$('#loading2 .progress-bar').css('width', '" . 100 . "%').attr('aria-valuenow', " . 100 . ").html('" . 100 . "%');</script>";
				ob_flush();
				flush();

				header('Content-Type: application/pdf');
				header("Content-Transfer-Encoding: Binary");
				header("Content-disposition: attachment; filename=" . $file_name);
				// readfile($path);
			} else {
				$this->load->library("excel");

				$object = new PHPExcel();

				$object->setActiveSheetIndex(0);

				$active_sheet = $object->getActiveSheet();


				$style = array(
					'alignment' => array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					)
				);

				$object->getDefaultStyle()->applyFromArray($style);

				$active_sheet->setCellValueByColumnAndRow(0, 1, "$branch_name - Merit Sheet ($month_f/$year_f)");
				$active_sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

				$active_sheet->mergeCellsByColumnAndRow(0, 3, 5, 3);
				$caption = new PHPExcel_RichText();
				$caption->createTextRun('Generated at ');
				$caption_date = $caption->createTextRun(date('d/m/Y H:i:s'));
				$caption_date->getFont()->setBold(true);
				$caption->createText(' by ');
				$caption_name = $caption->createTextRun($current_user['first_name']);
				$caption_name->getFont()->setBold(true);

				$active_sheet->setCellValueByColumnAndRow(0, 3, $caption);
				$active_sheet->getStyleByColumnAndRow(0, 3)->getFont()->setSize(14);

				$row = 5;
				$column = 0;

				$active_sheet->setCellValueByColumnAndRow($column++, $row, 'Name');
				$active_sheet->setCellValueByColumnAndRow($column++, $row, 'Totals');
				$active_sheet->getStyle('A5:B5')->getFont()->setBold(true);

				foreach ($days as $day) {
					$active_sheet->setCellValueByColumnAndRow($column, $row, $day['date'] . "\n" . $day['day']);
					$active_sheet->getStyleByColumnAndRow($column++, $row)->getFont()->setBold(true);
				}
				$row++;

				foreach ($output_employees as $i => $employee) {
					$column = 0;
					$active_sheet->setCellValueByColumnAndRow($column++, $row, create_monthly_name_text($employee['first_name'], $employee['special_id']));
					$active_sheet->setCellValueByColumnAndRow($column, $row, $employee['total_points']);
					$active_sheet->getStyleByColumnAndRow($column++, $row)->getFont()->setBold(true);
					foreach ($employee['offenses'] as $offense) {
						$active_sheet->setCellValueByColumnAndRow($column, $row, $offense['sign'] . $offense['points']);
						if (!$offense['is_offense']) {
							$active_sheet->getStyleByColumnAndRow($column++, $row)->getFont()->setStrikethrough(true);
						} else $column++;
					}
					$row++;
					$percentage = floor((($i + 1) / $employee_count) * 100);

					echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
					ob_flush();
					flush();
				}

				$file_name = "$branch_name - Monthly Merit Report - $first_day to $last_day " . time() . '.xlsx';
				$object_writer = new PHPExcel_Writer_Excel2007($object, 'Excel5');
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment;filename="' . $file_name . '"');
				$new_file = "uploads/summary/" . $file_name;
				$object_writer->save($new_file);
			}


			echo '</br> <br> <b>Export Completed</b> </br>';

			$path = base_url() . "uploads/summary/" . $file_name;

			echo "</br> <center><div style='width:40%'><a href='$path' download='$file_name'><button style='' class='btn btn-primary btn-block'>Download File</button></a></div></center>";

			echo "</br> <center><div style='width:40%'><a href='" . base_url() . "exports_merit_system'><button style='margin-bottom	:40px' class='btn btn-primary btn-block'>Back to Export Summary</button></a></div></center>";
			echo '</div>';
			insert_log("Simple", ["action" => "Exported Merit Sheet,Monthly"]);
			die;
		} else if ($_POST['type'] == "yearly") {
			flushLoadingBar();

			$data['year'] = $year;
			$this->db->select("employees.id, employees.special_id, employees.first_name")->from("employees");
			$this->db->join("roles", "employees.role_id = roles.id", "INNER");
			$this->db->where("employees.deleted_at IS NULL");
			$this->db->where("employees.employee_status", "active");
			$this->db->where("roles.exclude_from_system", "no");
			$this->db->where("employees.company_id", $cid);
			if (!empty($this->input->post("emp"))) {
				$this->db->where_in('employees.id', $this->input->post("emp"));
			}
			if (!empty($this->input->post("branch"))) {
				$this->db->where_in('employees.branch_id', $this->input->post("branch"));
			}
			if (!empty($this->input->post("dep"))) {
				$this->db->where_in('employees.department_id', $this->input->post("dep"));
			}
			if (!empty($this->input->post("position"))) {
				$this->db->where_in('employees.position_id', $this->input->post("position"));
			}
			if (!empty($this->input->post("exclude_employee"))) {
				$this->db->where_not_in('employees.id', $this->input->post("exclude_employee"));
			}
			$this->db->order_by("employees.special_id");
			$employees = $this->db->get()->result();

			$max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year);

			$company_average_merit_points = get_average_merit_points($year, $cid);
			$company_merit_points = get_merit_points($year, $cid);

			$employees_ids = ['0'];
			$employee_count = count($employees);
			foreach ($employees as $i => &$employee) {
				$employees_ids[] = $employee->id;
				$points = search_average_merit_points($company_average_merit_points, $employee->id);
				$employee->average_merit_points = number_format($points, 2);
				$employee->grade = merit_system_grading($points);
				$employee->merit_points = search_merit_points($company_merit_points, $employee->id);

				$percentage = floor((($i + 1) / $employee_count) * 100);

				echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();
			}

			$first_day = sprintf("%04d-%02d-%02d", $year, $month, 1);
			$last_day = sprintf("%04d-%02d-%02d", $year, $month, $max_date);

			$data["employees"] = $employees;


			$this->load->view('merit_system/yearly_report_pdf', $data);
			$html = $this->output->get_output();

			$date = DateTime::createFromFormat('Y-m-d', $first_day);
			$data['from_f'] = $date->format('d/m/Y');
			$date = DateTime::createFromFormat('Y-m-d', $last_day);
			$data['to_f'] = $date->format('d/m/Y');

			// $data["all_data"] = $all_data;
			// $data["branch_name"] = $branch_name;
			if ($_POST['file_type'] == "pdf") {
				// $html2 = $this->load->view('merit_system/merit_system_pdf', $data, true);
				$file_name = "Yearly Merit Report - $first_day to $last_day " . time() . ".pdf";

				$this->dompdf->reset();
				$this->dompdf->loadHtml($html);
				$this->dompdf->setPaper("A4", "landscape");
				$this->dompdf->render();

				$output = $this->dompdf->output();
				$new_file = "uploads/summary/" . $file_name;
				file_put_contents($new_file, $output);

				$path = "uploads/summary/" . $file_name;

				echo "<script>$('#loading2 .progress-bar').css('width', '" . 100 . "%').attr('aria-valuenow', " . 100 . ").html('" . 100 . "%');</script>";
				ob_flush();
				flush();

				header('Content-Type: application/pdf');
				header("Content-Transfer-Encoding: Binary");
				header("Content-disposition: attachment; filename=" . $file_name);
				// readfile($path);
			}


			echo '</br> <br> <b>Export Completed</b> </br>';

			$path = base_url() . "uploads/summary/" . $file_name;

			echo "</br> <center><div style='width:40%'><a href='$path' download='$file_name'><button style='' class='btn btn-primary btn-block'>Download File</button></a></div></center>";

			echo "</br> <center><div style='width:40%'><a href='" . base_url() . "exports_merit_system'><button style='margin-bottom	:40px' class='btn btn-primary btn-block'>Back to Export Summary</button></a></div></center>";
			echo '</div>';
			insert_log("Simple", ["action" => "Exported Merit Sheet,Yearly"]);
			die;
		} else if ($_POST['type'] == "monthly_merit_report") {
			flushLoadingBar(true);

			$max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year);

			$first_day = sprintf("%04d-%02d-%02d", $year, $month, 1);
			$last_day = sprintf("%04d-%02d-%02d", $year, $month, $max_date);

			$date1 = DateTime::createFromFormat('Y-m-d', $first_day);

			$month_name_for_merit_report = $date1->format('F Y');

			$public_holidays_all = get_public_holidays_all();

			$branch_id = array();
			$department_id = array();
			$position_id = array();
			$employee_id = array();
			$exclude_employees = array();
			if (isset($_POST["branch"])) {
				$branch_id = $_POST["branch"];
			}
			if (isset($_POST["dep"])) {
				$department_id = $_POST["dep"];
			}
			if (isset($_POST["position"])) {
				$position_id = $_POST["position"];
			}
			if (isset($_POST["emp"])) {
				$employee_id = $_POST["emp"];
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
			if ($employee_id) {
				$employee_group_arr = array();
				foreach ($employee_id as $key) {
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
					// print_r($result);die;
				}
				$employees_from_group = array_unique($employees_from_group);
				// print_r($employees_from_group);die;
			}

			$this->db->select('employees.id,employees.first_name,special_id,employees.is_daily_waged, d.name as department, p.title as position,employees.branch_id,b.name as branch,is_ot,is_early_ot,inc_late_in,inc_late_break, inc_early_out, inc_short_hours,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,round_by_exact_hour,different_first_hour_rounding,worked_hours_ot_rd,worked_hours_ot_ph,deduct_hour_ot_rd,deduct_hour_ot_ph,worked_hours_ot_off,deduct_hour_ot_off,ignore_breaks_after_endtime,void_lateness_time_if_less_than,deduct_from_ot,deduct_from_ot_single,deduction_date,min_worked_hours_meal,ta_rate,ma_rate,ca_rate,spa_rate,aca_rate,aa_rate,nsa_rate,fl_rate,cw_rate,mo_rate,shift1_rate,shift2_rate,shift3_rate,food_rate,basic_wage,ot_group,special_incentive')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('branches b', 'b.id = employees.branch_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null')->where('roles.exclude_from_system', 'no')
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
			if ($employee_id) {
				// print_r($employee_id);die;
				$employee_group_arr = array();
				$employee_arr = array();
				foreach ($employee_id as $key) {
					if (strpos($key, '-') !== false) {
						// Nothing to do...
					} else {
						$employee_arr[] =  $key;
					}
				}
				// echo '<pre>';
				// print_r($employee_arr);
				// print_r($employees_from_group);
				// echo '</pre>';die;
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

			$chunkedEmployeeIds = array_chunk($employees_ids, 20);
			$chunksCount = count($chunkedEmployeeIds);

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

			$approved_ot_list = get_approved_ot_list($shift_ids, $first_day, $last_day);

			$this->load->library("excel");
			$this->load->model("Merit");


			$interval_minutes = get_interval_minutes($cid);

			$result_list = [];
			$result_list_overnight = [];
			$clockings_news = [];
			$clockings_news_overnight = [];
			foreach ($chunkedEmployeeIds as $i => $chunk) {
				$result_list = array_merge($result_list, get_result_list($chunk, $first_day, $last_day));
				$result_list_overnight = array_merge($result_list_overnight, get_result_list_overnight($chunk, $first_day, $last_day));
				$clockings_news = array_merge($clockings_news, $this->db->select('id,employee_id,type,shift_id,date_format(datetime,"%H:%i") as clock_time,date_format(datetime, "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false)->from('clockings_news')->where('date(datetime) >=', $first_day)->where('date(datetime) <=', $last_day)->where_in('employee_id', $chunk)->where('deleted_at is null')->order_by('datetime')->get()->result());
				$clockings_news_overnight = array_merge($clockings_news_overnight, $this->db->select('id,employee_id,type,shift_id, date_format(datetime,"%H:%i") as clock_time,date_format(date_sub(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false)->from('clockings_news')->where('date(date_sub(datetime, interval ' . $interval_minutes .  ' minute)) >=', $first_day)->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $chunk)->where('deleted_at is null')->order_by('datetime')->get()->result());
				$percentage = floor((($i + 1) / $chunksCount) * 100);
				echo "<script>$('#preparing-data .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();
			}

			$merit_deduction_points = [];
			if ($permissions_level == "Outlet") {
				$shifts = $this->db->select('id')->from('shifts')->where('branch_id', $bid)->where('is_leave', 'no')->get()->result();
				$merit_deduction_points = $this->Merit->get_deduction_points($cid, $bid);
			} else {
				$shifts = $this->db->select('id')->from('shifts')->where('company_id', $cid)->where('is_leave', 'no')->get()->result();
				$merit_deduction_points = $this->Merit->get_deduction_points($cid);
			}

			$default_offenses = default_offenses();

			$branches = [];

			foreach ($employees as $emp) {
				$branches[] = [
					'branch_id' => $emp->branch_id,
					'branch' => $emp->branch
				];
			}

			$branches = array_values(array_unique($branches, SORT_REGULAR));

			$object = PHPExcel_IOFactory::load("assets/merit_report_template.xlsx");

			$object->setActiveSheetIndex(0);

			$active_sheet = $object->getActiveSheet();
			$active_sheet->setCellValueByColumnAndRow(0, 2, $c_name);
			$active_sheet->setCellValueByColumnAndRow(0, 4, '(' . $month_name_for_merit_report . ')');

			foreach ($branches as $key => $value) {
				if ($key == 0) {
					$object->getSheet(0)->setTitle($value['branch']);
					$object->getSheet(0)->setCellValueByColumnAndRow(0, 6, "CAWANGAN: {$value['branch']}");
				} else {
					$tempSheet = $object->getSheet(0)->copy();
					$tempSheet->setTitle($value['branch']);
					$tempSheet->setCellValueByColumnAndRow(0, 6, "CAWANGAN: {$value['branch']}");

					$object->addSheet($tempSheet);
				}
			}
			$employees_done = 1;
			$employees_count = count($employees);
			$branch_count = count($branches);
			foreach ($branches as $key => $value) {
				$branch_id = $value['branch_id'];
				$branch_employees = array_filter($employees, function ($obj) use ($branch_id) {
					return $obj->branch_id == $branch_id;
				});
				$filler_array = [];
				$output_employees = [];
				foreach ($branch_employees as $emp) {
					$calculated_data = calculate_summary_data($emp->id, $first_day, $last_day, "merit_system", $emp, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days, $cid, $filler_array, $filler_array, $filler_array, $clockings_news, $clockings_news_overnight);
					// echo "<pre>"; print_r($calculated_data); die;
					$temp = calculate_merit($emp, $calculated_data, $permissions_level, $merit_deduction_points, $first_day, $last_day, $default_offenses);
					$temp["position"] = $calculated_data["employee"]->position;
					$output_employees[] = $temp;

					$percentage = floor(($employees_done / $employees_count) * 100);

					echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
					ob_flush();
					flush();
					$employees_done++;
				}
				// echo "<pre>"; print_r($output_employees); die;
				$object->setActiveSheetIndex($key);
				$active_sheet = $object->getActiveSheet();
				$serial = 1;
				$row = 11;
				foreach ($output_employees as $out_emp) {
					// echo "<pre>"; print_r($out_emp); die;
					$active_sheet->setCellValueByColumnAndRow(0, $row, $serial++);
					$active_sheet->setCellValueByColumnAndRow(1, $row, $out_emp['special_id']);
					$active_sheet->setCellValueByColumnAndRow(2, $row, $out_emp['first_name']);
					$active_sheet->setCellValueByColumnAndRow(3, $row, $out_emp['position']);
					$active_sheet->setCellValueByColumnAndRow(4, $row, '100');
					$active_sheet->setCellValueByColumnAndRow(5, $row, $out_emp['full_day_paid_count'] + $out_emp['approved_full_day_paid_count']);
					$active_sheet->setCellValueByColumnAndRow(6, $row, $out_emp['half_day_paid_count'] + $out_emp['approved_half_day_paid_count']);
					$active_sheet->setCellValueByColumnAndRow(7, $row, $out_emp['half_day_unpaid_count'] + $out_emp['approved_half_day_unpaid_count']);
					$active_sheet->setCellValueByColumnAndRow(8, $row, $out_emp['approved_medical_leave_count']);
					$active_sheet->setCellValueByColumnAndRow(9, $row, $out_emp['medical_leave_count']);
					$active_sheet->setCellValueByColumnAndRow(10, $row, $out_emp['AU_count']);
					$active_sheet->setCellValueByColumnAndRow(11, $row, '');
					$active_sheet->setCellValueByColumnAndRow(12, $row, '');
					$active_sheet->setCellValueByColumnAndRow(13, $row, $out_emp['early_out_count']);
					$active_sheet->setCellValueByColumnAndRow(14, $row, $out_emp['late_in_count']);
					$active_sheet->setCellValueByColumnAndRow(15, $row, $out_emp['missing_IO_count']);
					$active_sheet->setCellValueByColumnAndRow(16, $row, $out_emp['late_break_count']);

					$active_sheet->setCellValueByColumnAndRow(17, $row, $out_emp['full_day_paid_points'] + $out_emp['approved_full_day_paid_points']);
					$active_sheet->setCellValueByColumnAndRow(18, $row, $out_emp['half_day_paid_points'] + $out_emp['approved_half_day_paid_points']);
					$active_sheet->setCellValueByColumnAndRow(19, $row, $out_emp['half_day_unpaid_points'] + $out_emp['approved_half_day_unpaid_points']);
					$active_sheet->setCellValueByColumnAndRow(20, $row, $out_emp['approved_medical_leave_points']);
					$active_sheet->setCellValueByColumnAndRow(21, $row, $out_emp['medical_leave_points']);
					$active_sheet->setCellValueByColumnAndRow(22, $row, $out_emp['AU_points']);
					$active_sheet->setCellValueByColumnAndRow(23, $row, $out_emp['early_out_points']);
					$active_sheet->setCellValueByColumnAndRow(24, $row, $out_emp['late_in_points']);
					$active_sheet->setCellValueByColumnAndRow(25, $row, $out_emp['missing_IO_points']);
					$active_sheet->setCellValueByColumnAndRow(26, $row, $out_emp['late_break_points']);
					$active_sheet->setCellValueByColumnAndRow(27, $row, $out_emp['manual_offense_points']);
					$active_sheet->setCellValueByColumnAndRow(28, $row, "=IF(SUM(R{$row}:AB{$row}) > 100, 100, SUM(R{$row}:AB{$row}))");
					$active_sheet->setCellValueByColumnAndRow(29, $row, "=IF(E{$row}-AC{$row} < 0, 0, E{$row}-AC{$row})");

					$active_sheet->insertNewRowBefore($row + 1, 1);
					$row++;
				}
				$active_sheet->removeRow($row);
				$branch_percentage = floor((($key + 1) / $branch_count) * 100);

				echo "<script>$('#loading1 .progress-bar').css('width', '" . $branch_percentage . "%').attr('aria-valuenow', " . $branch_percentage . ").html('" . $branch_percentage . "%');</script>";
				ob_flush();
				flush();
			}

			if ($employees_done - 1 != $employees_count) {
				$percentage = 100;
				echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();
			}

			$file_name = "($branch_name) Monthly Merit Report - $first_day to $last_day " . time() . ".xlsx";

			// for ($i=1; $i <= count($employees); $i++) { 
			// 	$percentage = floor(($i / count($employees)) * 100);

			// 	echo "<script>$('#loading2 .progress-bar').css('width', '".$percentage."%').attr('aria-valuenow', ".$percentage.").html('".$percentage."%');</script>";
			// 	ob_flush();
			// 	flush();
			// }

			$object_writer = new PHPExcel_Writer_Excel2007($object, 'Excel2007');
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="' . $file_name . '"');
			$new_file = "uploads/summary/" . $file_name;
			$object_writer->save($new_file);

			echo '</br> <br> <b>Export Completed</b> </br>';

			$path = base_url() . "uploads/summary/" . $file_name;

			echo "</br> <center><div style='width:40%'><a href='$path' download='$file_name'><button style='' class='btn btn-primary btn-block'>Download File</button></a></div></center>";

			echo "</br> <center><div style='width:40%'><a href='" . base_url() . "exports'><button style='margin-bottom	:40px' class='btn btn-primary btn-block'>Back to Export Summary</button></a></div></center>";
			echo '</div>';

			insert_log("Simple", ["action" => "Exported,Monthly Merit Report"]);
		} else {
			flushLoadingBar(true);
			$this->db->select('e.id,e.first_name,special_id,e.is_daily_waged, e.mobile, b.name as branch_name, d.name as department, p.title as position,e.branch_id,is_ot,is_early_ot,inc_late_in,inc_late_break, inc_early_out, inc_short_hours,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,void_lateness_time_if_less_than,deduct_from_ot,deduct_from_ot_single,deduction_date')->from('employees e')->join('roles r', 'e.role_id = r.id', 'left')->join('departments d', 'd.id = e.department_id', 'left')->join('branches b', 'b.id = e.branch_id', 'left')->join('positions p', 'p.id = e.position_id', 'left')->where("$where_filter AND r.exclude_from_system = 'no' AND e.deleted_at is NULL
			AND (
				e.employee_status = 'active' 
				OR (e.employee_status = 'terminated' AND e.termination_date IS NOT NULL AND e.termination_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
				OR (e.employee_status = 'resigned' AND e.resignation_date IS NOT NULL AND e.resignation_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
			)")->order_by('e.special_id', 'ASC');

			if (!empty($this->input->post("emp"))) {
				$this->db->where_in('e.id', $this->input->post("emp"));
			}
			if (!empty($this->input->post("branch"))) {
				$this->db->where_in('e.branch_id', $this->input->post("branch"));
			}
			if (!empty($this->input->post("dep"))) {
				$this->db->where_in('e.department_id', $this->input->post("dep"));
			}
			if (!empty($this->input->post("position"))) {
				$this->db->where_in('e.position_id', $this->input->post("position"));
			}
			if (!empty($this->input->post("exclude_employee"))) {
				$this->db->where_not_in('e.id', $this->input->post("exclude_employee"));
			}
			$employees = $this->db->get()->result();
			// print_r($employees);die;
			$employees_ids = ['0'];
			foreach ($employees as $employee) {
				$employees_ids[] = $employee->id;
			}
			$chunkedEmployeeIds = array_chunk($employees_ids, 20);
			$chunksCount = count($chunkedEmployeeIds);
			$max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year);
			$first_day = sprintf("%04d-%02d-%02d", $year, $month, 1);
			$last_day = sprintf("%04d-%02d-%02d", $year, $month, $max_date);

			$company_working_hours = get_company_working_hours($cid);
			$company_ot_settings = get_company_ot_settings($cid);
			$company_early_ot_settings = get_company_early_ot_settings($cid);
			$company_data = $this->db->select("*")->from("companies c")->where("c.id", $cid)->get()->row();
			$branch_rest_days = $this->db->select('id,rest_days,off_days')->from('branches')->where('company_id', $cid)->get()->result();
			$public_holidays_all = get_public_holidays_with_name();

			$public_holidays = $public_holidays_all[0];
			$public_holidays_names = $public_holidays_all[1];

			$public_holidays_all = get_public_holidays_all();

			$result_list = [];
			$result_list_overnight = [];
			$clockings_news = [];
			$clockings_news_overnight = [];

			foreach ($chunkedEmployeeIds as $i => $chunk) {
				$result_list = array_merge($result_list, get_result_list($chunk, $first_day, $last_day));
				$result_list_overnight = array_merge($result_list_overnight, get_result_list_overnight($chunk, $first_day, $last_day));
				$clockings_news = array_merge($clockings_news, $this->db->select('id,employee_id,type,shift_id,date_format(datetime,"%H:%i") as clock_time,date_format(datetime, "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false)->from('clockings_news')->where('date(datetime) >=', $first_day)->where('date(datetime) <=', $last_day)->where_in('employee_id', $chunk)->where('deleted_at is null')->order_by('datetime')->get()->result());
				$clockings_news_overnight = array_merge($clockings_news_overnight, $this->db->select('id,employee_id,type,shift_id, date_format(datetime,"%H:%i") as clock_time,date_format(date_sub(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false)->from('clockings_news')->where('date(date_sub(datetime, interval ' . $interval_minutes .  ' minute)) >=', $first_day)->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $chunk)->where('deleted_at is null')->order_by('datetime')->get()->result());
				$percentage = floor((($i + 1) / $chunksCount) * 100);
				echo "<script>$('#preparing-data .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();
			}
			
			$merit_deduction_points = [];

			if ($permissions_level == "Outlet") {
				$shifts = $this->db->select('id')->from('shifts')->where('branch_id', $bid)->where('is_leave', 'no')->get()->result();
				$merit_deduction_points = $this->Merit->get_deduction_points($cid, $bid);
			} else {
				$shifts = $this->db->select('id')->from('shifts')->where('company_id', $cid)->where('is_leave', 'no')->get()->result();
				$this->load->model('Merit');
				$merit_deduction_points = $this->Merit->get_deduction_points($cid);
			}

			$shift_ids = array(0);
			foreach ($shifts as $s) {
				$shift_ids[] = $s->id;
			}
			$approved_ot_list = get_approved_ot_list($shift_ids, $first_day, $last_day);

			$default_offenses = default_offenses();
			$count = 1;
			$response = [];
			$employee_count = count($employees);
			foreach ($employees as $i => $employee) {
				$temp = [];
				$data['employee'] = $employee;
				$date = DateTime::createFromFormat('Y-m-d', $first_day);
				$data['from_f'] = $date->format('d/m/Y');
				$date = DateTime::createFromFormat('Y-m-d', $last_day);
				$data['to_f'] = $date->format('d/m/Y');
				$data['month'] = $month;

				$calculated_data = calculate_summary_data($employee->id, $first_day, $last_day, "merit_system", $employee, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days, $cid, $filler_array, $filler_array, $filler_array, $clockings_news, $clockings_news_overnight);
				$temp = calculate_merit($employee, $calculated_data, $permissions_level, $merit_deduction_points, $first_day, $last_day, $default_offenses);
				$data["offenses_data"] = $temp;

				$percentage = floor((($i + 1) / $employee_count) * 100);

				echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
				ob_flush();
				flush();

				$final_output = [];
				$final_output["company_logo"] = base_url("uploads/" . ($current_user["permissions_level"] == "Company" ? $current_user["company_logo"] : $current_user["logo_big"]));
				$final_output["signature"] = ($current_user["merit_system_sign"] == "") ? null : base_url("uploads/" . $current_user["merit_system_sign"]);
				$final_output["position"] = $current_user["merit_system_position_text"];
				$final_output["offenses_data"] = $temp;
				$final_output["offenses_data"]["list_of_offenses"] = $temp['approved_offenses'];
				$final_output["employee"] = $employee;
				$final_output['from_f'] = $data['from_f'];
				$final_output['to_f'] = $data['to_f'];
				$final_output['month'] = $month;
				$final_output['company_data'] = $company_data;

				if ($temp['total_points'] !== 100)
					$response[] = $final_output;

				$data = array();
			}

			$response_size = count($response);
			$files = [];
			for ($i = 0; $i < $response_size; $i++) {
				$output_data = [];
				$output_data["records"][] = $response[$i];
				$output_data["records"][] = $response[$i];
				if ($_POST['file_type'] == 'pdf') {
					$html = $this->load->view('merit_system/full_monthly_report', $output_data, true);

					$this->dompdf->reset();
					$this->dompdf->loadHtml($html);
					$this->dompdf->setPaper("A4", "portrait");
					$this->dompdf->render();


					$output = $this->dompdf->output();

					$file_name = str_replace("/", "-", $response[$i]['employee']->first_name) . " (" . $response[$i]['employee']->special_id . ') ' . $first_day . ' to ' . $last_day . ' - Monthly Merit Report.pdf';
					$new_file = "uploads/summary/" . $file_name;

					file_put_contents($new_file, $output);

					$percentage = floor(($count++ / ($response_size / 2)) * 100);
					if ($percentage > 100) $percentage = 100;

					echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
					ob_flush();
					flush();
				}

				$files[] = $new_file;
			}

			echo '</br> <br> <b>Export Completed</b> </br>';
			if (count($files) === 0) {
				echo '<p>No data to export, please go back.</p>';
				echo "</br> <center><div style='width:40%'><a href='" . base_url() . "exports_merit_system'><button style='margin-bottom	:40px' class='btn btn-primary btn-block'>Back to Export Summary</button></a></div></center>";
				echo '</div>';
			} else {
				if (count($files) > 1) {
					$file_name = "Full Summary - $first_day to $last_day " . time() . ".zip";
					foreach ($files as $file) {
						$this->zip->read_file(FCPATH .  $file);
						unlink($file);
					}
					$this->zip->archive(FCPATH . 'uploads/summary/' . $file_name);
				}

				$path = base_url() . "uploads/summary/" . $file_name;

				insert_log("Simple", ["action" => "Exported,Full Data"]);

				echo "</br> <center><div style='width:40%'><a href='$path' download='$file_name'><button style='' class='btn btn-primary btn-block'>Download File</button></a></div></center>";

				echo "</br> <center><div style='width:40%'><a href='" . base_url() . "exports_merit_system'><button style='margin-bottom	:40px' class='btn btn-primary btn-block'>Back to Export Summary</button></a></div></center>";
				echo '</div>';
			}
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
		$xcrud->label(array('sql_ot3_name' => 'Name', 'sql_ot3_code' => 'Code', 'sql_ot3_description' => 'Description', 'sql_ot3_rate' => 'Rate'));
		$xcrud->label(array('sql_ul_name' => 'Name', 'sql_ul_code' => 'Code', 'sql_ul_description' => 'Description', 'sql_ul_rate' => 'Rate'));
		$xcrud->label(array('sql_dw_name' => 'Name', 'sql_dw_code' => 'Code', 'sql_dw_description' => 'Description', 'sql_dw_rate' => 'Rate'));
		$xcrud->label(array('sql_dd1_name' => 'Name', 'sql_dd1_code' => 'Code', 'sql_dd1_description' => 'Description', 'sql_dd1_rate' => 'Rate'));
		$xcrud->label(array('sql_dd2_name' => 'Name', 'sql_dd2_code' => 'Code', 'sql_dd2_description' => 'Description', 'sql_dd2_rate' => 'Rate'));
		$xcrud->label(array('sql_e_l_name' => 'Name', 'sql_e_l_code' => 'Code', 'sql_e_l_description' => 'Description', 'sql_e_l_rate' => 'Rate'));
		$xcrud->label(array('sql_wrd_name' => 'Name', 'sql_wrd_code' => 'Code', 'sql_wrd_description' => 'Description', 'sql_wrd_rate' => 'Rate'));
		$xcrud->label(array('sql_wph_name' => 'Name', 'sql_wph_code' => 'Code', 'sql_wph_description' => 'Description', 'sql_wph_rate' => 'Rate'));
		$xcrud->label(array('w_ot_name' => 'Name', 'w_ot_code' => 'Code', 'w_ot_description' => 'Description', 'w_ot_rate' => 'Rate'));
		$xcrud->label(['w_ot_r_name' => 'Name', 'w_ot_r_code' => 'Code', 'w_ot_r_description' => 'Description', 'w_ot_r_rate' => 'Rate']);
		$xcrud->label(['w_ot_p_name' => 'Name', 'w_ot_p_code' => 'Code', 'w_ot_p_description' => 'Description', 'w_ot_p_rate' => 'Rate']);
		$xcrud->label(['sql_wsh_name' => 'Name', 'sql_wsh_code' => 'Code', 'sql_wsh_description' => 'Description', 'sql_wsh_rate' => 'Rate']);

		$xcrud->fields('sql_ot1_name, sql_ot1_code, sql_ot1_description, sql_ot1_rate', false, 'Overtime 1');
		$xcrud->fields('sql_ot2_name, sql_ot2_code, sql_ot2_description, sql_ot2_rate', false, 'Overtime 2');
		$xcrud->fields('sql_ot3_name, sql_ot3_code, sql_ot3_description, sql_ot3_rate', false, 'Overtime 3');
		$xcrud->fields('sql_ul_name, sql_ul_code, sql_ul_description, sql_ul_rate', false, 'Unpaid Leave');
		$xcrud->fields('sql_e_l_name, sql_e_l_code, sql_e_l_description, sql_e_l_rate', false, 'Early / Late');
		$xcrud->fields('sql_dw_name, sql_dw_code, sql_dw_description, sql_dw_rate', false, 'Daily Wage');
		$xcrud->fields('sql_dd1_name, sql_dd1_code, sql_dd1_description, sql_dd1_rate', false, 'Deduction 1');
		$xcrud->fields('sql_dd2_name, sql_dd2_code, sql_dd2_description, sql_dd2_rate', false, 'Deduction 2');
		$xcrud->fields('sql_wrd_name, sql_wrd_code, sql_wrd_description, sql_wrd_rate', false, 'Worked RD');
		$xcrud->fields('sql_wph_name, sql_wph_code, sql_wph_description, sql_wph_rate', false, 'Worked PH');
		$xcrud->fields('w_ot_name, w_ot_code, w_ot_description, w_ot_rate', false, 'Weekly OT Normal');
		$xcrud->fields('w_ot_r_name, w_ot_r_code, w_ot_r_description, w_ot_r_rate', false, 'Weekly OT RD');
		$xcrud->fields('w_ot_p_name, w_ot_p_code, w_ot_p_description, w_ot_p_rate', false, 'Weekly OT PH');
		$xcrud->fields('sql_wsh_name, sql_wsh_code, sql_wsh_description, sql_wsh_rate', false, 'Shift Worked Hours');


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

	public function full_merit_report_preview_pdf()
	{
		$current_user = get_user();
		$data['current_user'] = $current_user;
		$data["selected_branch_id"] = 0;
		$data["selected_dep_id"] = 0;
		$data["selected_month"] = 0;
		$cid = $current_user["company_id"];
		$bid = $current_user["branch_id"];
		$permissions_level = $current_user["permissions_level"];
		$interval_minutes = get_interval_minutes($cid);

		$where_filter = "";
		// if (!empty($this->input->post("branch"))) {
		//     $data["selected_branch_id"] = $this->input->post("branch");
		//     $where_filter = $where_filter . " branch_id = " . $this->input->post("branch") . " AND ";
		// }

		// if (!empty($this->input->post("emp"))) {
		//     $data["selected_emp_id"] = $this->input->post("emp");
		//     $where_filter = $where_filter . " e.id = " . $this->input->post("emp") . " AND ";
		// }

		// if (!empty($this->input->post("dep"))) {
		//     $data["selected_dep_id"] = $this->input->post("dep");
		//     $where_filter = $where_filter . " department_id = " . $this->input->get("dep") . " AND ";
		// }

		if (!empty($this->input->post("month")) && !empty($this->input->post("year"))) {
			$month = $data["selected_month"] = $this->input->post("month");
			$year = $data["selected_year"] = $this->input->post("year");
		} else {
			redirect("merit_system?month=" . date('m') . "&year=" . date('Y'));
			return;
		}

		$year = $data["selected_year"];
		$where_filter = $where_filter . " e.company_id = " . $cid;

		if ($_POST['type'] == "full") {

			$this->db->select('e.id,e.first_name,special_id,e.is_daily_waged, e.mobile, b.name as branch_name, d.name as department, p.title as position,e.branch_id,is_ot,is_early_ot,inc_late_in,inc_late_break, inc_early_out, inc_short_hours,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,void_lateness_time_if_less_than,deduct_from_ot,deduct_from_ot_single,deduction_date')->from('employees e')->join('roles r', 'e.role_id = r.id', 'left')->join('departments d', 'd.id = e.department_id', 'left')->join('branches b', 'b.id = e.branch_id', 'left')->join('positions p', 'p.id = e.position_id', 'left')->where("$where_filter AND r.exclude_from_system = 'no' AND e.deleted_at is NULL
			AND (
				e.employee_status = 'active' 
				OR (e.employee_status = 'terminated' AND e.termination_date IS NOT NULL AND e.termination_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
				OR (e.employee_status = 'resigned' AND e.resignation_date IS NOT NULL AND e.resignation_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
			)")->order_by('e.special_id', 'ASC');

			if (!empty($this->input->post("emp"))) {
				$this->db->where_in('e.id', $this->input->post("emp"));
			}
			if (!empty($this->input->post("branch"))) {
				$this->db->where_in('e.branch_id', $this->input->post("branch"));
			}
			if (!empty($this->input->post("dep"))) {
				$this->db->where_in('e.department_id', $this->input->post("dep"));
			}
			if (!empty($this->input->post("position"))) {
				$this->db->where_in('e.position_id', $this->input->post("position"));
			}
			if (!empty($this->input->post("exclude_employee"))) {
				$this->db->where_not_in('e.id', $this->input->post("exclude_employee"));
			}
			$employees = $this->db->get()->result();
			// print_r($employees);die;
			$employees_ids = ['0'];
			foreach ($employees as $employee) {
				$employees_ids[] = $employee->id;
			}
			$max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year);
			$first_day = sprintf("%04d-%02d-%02d", $year, $month, 1);
			$last_day = sprintf("%04d-%02d-%02d", $year, $month, $max_date);

			$company_working_hours = get_company_working_hours($cid);
			$company_ot_settings = get_company_ot_settings($cid);
			$company_early_ot_settings = get_company_early_ot_settings($cid);
			$company_data = $this->db->select("*")->from("companies c")->where("c.id", $cid)->get()->row();
			$branch_rest_days = $this->db->select('id,rest_days,off_days')->from('branches')->where('company_id', $cid)->get()->result();
			$public_holidays_all = get_public_holidays_with_name();

			$public_holidays = $public_holidays_all[0];
			$public_holidays_names = $public_holidays_all[1];

			$public_holidays_all = get_public_holidays_all();

			$result_list = get_result_list($employees_ids, $first_day, $last_day);
			$result_list_overnight = get_result_list_overnight($employees_ids, $first_day, $last_day);

			$merit_deduction_points = [];

			$clockings_news = $this->db->select('id,employee_id,type,shift_id,date_format(datetime,"%H:%i") as clock_time,date_format(datetime, "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false)->from('clockings_news')->where('date(datetime) >=', $first_day)->where('date(datetime) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();
			$clockings_news_overnight = $this->db->select('id,employee_id,type,shift_id, date_format(datetime,"%H:%i") as clock_time,date_format(date_sub(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false)->from('clockings_news')->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) >=', $first_day)->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('employee_id', $employees_ids)->where('deleted_at is null')->order_by('datetime')->get()->result();

			if ($permissions_level == "Outlet") {
				$shifts = $this->db->select('id')->from('shifts')->where('branch_id', $bid)->where('is_leave', 'no')->get()->result();
				$merit_deduction_points = $this->Merit->get_deduction_points($cid, $bid);
			} else {
				$shifts = $this->db->select('id')->from('shifts')->where('company_id', $cid)->where('is_leave', 'no')->get()->result();
				$this->load->model('Merit');
				$merit_deduction_points = $this->Merit->get_deduction_points($cid);
			}

			$shift_ids = array(0);
			foreach ($shifts as $s) {
				$shift_ids[] = $s->id;
			}
			$approved_ot_list = get_approved_ot_list($shift_ids, $first_day, $last_day);

			$default_offenses = default_offenses();
			$total = count($employees);
			$count = 1;
			$response = [];
			foreach ($employees as $employee) {
				$temp = [];
				$data['employee'] = $employee;
				$date = DateTime::createFromFormat('Y-m-d', $first_day);
				$data['from_f'] = $date->format('d/m/Y');
				$date = DateTime::createFromFormat('Y-m-d', $last_day);
				$data['to_f'] = $date->format('d/m/Y');
				$data['month'] = $month;

				$calculated_data = calculate_summary_data($employee->id, $first_day, $last_day, "merit_system", $employee, $result_list, $result_list_overnight, $company_working_hours, false, $company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days, $cid, $filler_array, $filler_array, $filler_array, $clockings_news, $clockings_news_overnight);
				$temp = calculate_merit($employee, $calculated_data, $permissions_level, $merit_deduction_points, $first_day, $last_day, $default_offenses);
				$data["offenses_data"] = $temp;

				$final_output = [];
				$final_output["company_logo"] = base_url("uploads/" . ($current_user["permissions_level"] == "Company" ? $current_user["company_logo"] : $current_user["logo_big"]));
				$final_output["signature"] = ($current_user["merit_system_sign"] == "") ? null : base_url("uploads/" . $current_user["merit_system_sign"]);
				$final_output["position"] = $current_user["merit_system_position_text"];
				$final_output["offenses_data"] = $temp;
				$final_output["offenses_data"]["list_of_offenses"] = $temp['approved_offenses'];
				$final_output["employee"] = $employee;
				$final_output['from_f'] = $data['from_f'];
				$final_output['to_f'] = $data['to_f'];
				$final_output['month'] = $month;
				$final_output['company_data'] = $company_data;

				if ($temp['total_points'] !== 100)
					$response[] = $final_output;

				$data = array();
			}
			// print_r($response);die;
			$output_data["records"] = $response;
			if ($_POST['file_type'] == 'pdf') {
				// $html = $this->load->view('merit_system/full_monthly_report', $output_data, true);

				// $this->dompdf->reset();
				// $this->dompdf->loadHtml($html);
				// $this->dompdf->setPaper("A4", "portrait");
				// $this->dompdf->render();


				// $output = $this->dompdf->output();

				$this->load->view('merit_system/full_monthly_report_preview', $output_data);
				$html = $this->output->get_output();
				// print_r($html);die;
				$this->dompdf->loadHtml($html);
				$this->dompdf->setPaper("A4", "portrait");
				$this->dompdf->render();

				$this->dompdf->stream($data["selected_month"] . "-" . $data["selected_year"] .
					" - Merit System - " . time(), array("Attachment" => 0));
			}
		}
	}
}
