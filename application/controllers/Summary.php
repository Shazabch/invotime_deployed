
<?php
class Summary extends CI_Controller
{

	function __construct()
	{
		parent::__construct();

		if (is_null(get_user())) {
			redirect("welcome");
			//var_dump($this->session->userdata('antelope_user'));
		}
	}

	public function view($id = 0, $dep = false)
	{
		if (!is_page_permitted('view')) {
			redirect_if_not_permitted();
		}

		$current_user = get_user();
		// Check if it is HOD
		$is_HOD = $current_user["limit_access_to_department"] == "yes" ? TRUE : FALSE;
		$is_emp_summary_editable = $current_user["is_emp_summary_editable"] === "yes" ? TRUE : FALSE;
		$data["is_HOD"] = $is_HOD;
		$data["is_emp_summary_editable"] = $is_emp_summary_editable;

		$cid = $current_user["company_id"];
		$data['company_id'] = $cid;

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

		$data["employees_dropdown"] = $this->db->query("SELECT employees.id, special_id,first_name,last_name FROM employees INNER JOIN roles ON employees.role_id = roles.id WHERE employees.deleted_at IS NULL
			AND (employee_status = 'active'
				OR (employee_status = 'terminated' AND termination_date IS NOT NULL AND termination_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
				OR (employee_status = 'resigned' AND resignation_date IS NOT NULL AND resignation_date >= DATE_FORMAT(NOW(), '%Y-%m-01'))
			) AND roles.exclude_from_system = 'no' $department_filter AND employees.company_id = $cid $branch_where_filter ORDER BY special_id")->result();
		// echo count($data["employees_dropdown"]);die;

		if ($dep && $id == 0) {
			$data['employee'] = $this->db->select('e.id as emp_id,first_name,special_id,d.name as department,p.title as position,is_ot,is_early_ot,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,round_by_exact_hour,different_first_hour_rounding,inc_late_in,inc_late_break,inc_early_out,inc_short_hours,void_lateness_time_if_less_than,branch_id,deduct_from_ot,deduct_from_ot_single,deduction_date')->from('employees e')->join('departments d', 'd.id = e.department_id', 'left')->join('positions p', 'p.id = e.position_id', 'left')->join('branches b', 'b.id = e.branch_id', 'left')->where('e.id', $data["employees_dropdown"][0]->id)->get()->row();
			$id = $data["employees_dropdown"][0]->id;
		} else {
			$data['employee'] = $this->db->select('e.id as emp_id,first_name,special_id,d.name as department,p.title as position,is_ot,is_early_ot,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,round_by_exact_hour,different_first_hour_rounding,inc_late_in,inc_late_break,inc_early_out,inc_short_hours,void_lateness_time_if_less_than,branch_id,deduct_from_ot,deduct_from_ot_single,deduction_date')->from('employees e')->join('departments d', 'd.id = e.department_id', 'left')->join('positions p', 'p.id = e.position_id', 'left')->join('branches b', 'b.id = e.branch_id', 'left')->where('e.id', $id)->get()->row();
		}

		if (!$data['employee']) {
			redirect('summary/view/' . $data["employees_dropdown"][0]->id);
			//var_dump($data["employees_dropdown"][0]->id);
			die();
		}

		if (empty($_GET)) {
			$dates = getStartEndDatesWithOneMonthGap($current_user['start_day']);
			$first_day = $dates[0]->format('Y-m-d');
			$last_day = $dates[1]->format('Y-m-d');
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

		$data["is_alternate_clockings"] = is_alternate_clockings($data["employee"]->branch_id);

		$calculated_data = calculate_summary_data($data["employee"]->emp_id, $first_day, $last_day);
		// print_r($calculated_data);die;
		$data = array_merge($data, $calculated_data);

		$date = DateTime::createFromFormat('Y-m-d', $first_day);
		$data['from_f'] = $date->format('d/m/Y');
		$data['from_p'] = $first_day;
		$date = DateTime::createFromFormat('Y-m-d', $last_day);
		$data['to_f'] = $date->format('d/m/Y');
		$data['to_p'] = $last_day;
		$data['emp_id'] = $id;
		$data['pageTitle'] = "Summary";
		$data['active_menu'] = "summary/view";
		$this->load->view('header', $data);
		$data["menus"] = get_menus();

		$this->load->view('sidebar', $data);
		$this->load->view('summary', $data);
		$this->load->view('footer');
	}

	public function fix_clockings($id, $clocking_date = null)
	{
		if ($clocking_date) {
			$first_day = $clocking_date;
			$last_day = $clocking_date;
		} else if (empty($_GET)) {
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

		$period = new DatePeriod(
			new DateTime($first_day),
			new DateInterval('P1D'),
			(new DateTime($last_day))->add(new DateInterval('P1D'))
		);

		$shift_list = get_shift_list($id, $first_day, $last_day);

		$company_id = get_user()["company_id"];
		$interval_minutes = get_interval_minutes($company_id);

		$last_day_clocking_ids = [];

		foreach ($period as $date) {
			$current_date = $date->format('Y-m-d');

			$shift = search_from_list($shift_list, $current_date);

			$shift_interval_minutes = $interval_minutes;

			if ($shift && $shift->cut_off_time) {
				$cut_off_time = explode(":", $shift->cut_off_time);
				$shift_interval_minutes = $cut_off_time[0] * 60 + $cut_off_time[1];
			}

			$overnight = false;

			if ($shift && $shift->overnight == "Yes") {
				$overnight = true;
			}

			$clockings = $this->db->select('id, type, datetime, type_modified')->from('clockings_news')->where('employee_id', $id);

			if ($overnight) {
				$clockings = $clockings->where('date(date_sub(datetime, interval ' . $shift_interval_minutes . ' minute)) = ', $current_date);
			} else {
				$clockings = $clockings->where('date(datetime)', $current_date);
			}

			if ($last_day_clocking_ids) {
				$clockings = $clockings->where_not_in('id', $last_day_clocking_ids);
			}

			$clockings = $clockings->where('deleted_at is null')->order_by('datetime')->get()->result();

			$last_day_clocking_ids = [];
			$type = 'in';

			foreach ($clockings as $clocking) {
				if ($type != $clocking->type) {
					$this->db->set('type', $type);
					$this->db->set('type_modified', $clocking->type_modified ? 0 : 1);
					$this->db->where('id', $clocking->id);
					$this->db->update('clockings_news');
				}

				$type = $type == 'in' ? 'out' : 'in';

				$last_day_clocking_ids[] = $clocking->id;
			}
		}

		update_new_clockings($id, $first_day . " 00:00:00", $last_day . " 23:59:59");

		if (!$clocking_date) {
			redirect('summary/view/' . $id . '/?from=' . $_GET['from'] . '&to=' . $_GET['to']);
		}
	}

	public function reset_clockings($id)
	{
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

		$this->db->set('type', "CASE WHEN type = 'in' THEN 'out' ELSE 'in' END", FALSE);
		$this->db->set('type_modified', 0);
		$this->db->where('employee_id', $id);
		$this->db->where('datetime >=', $first_day . " 00:00:00");
		$this->db->where('datetime <=', $last_day . " 23:59:59");
		$this->db->where('type_modified', 1);
		$this->db->update('clockings_news');

		update_new_clockings($id, $first_day . " 00:00:00", $last_day . " 23:59:59");

		redirect('summary/view/' . $id . '/?from=' . $_GET['from'] . '&to=' . $_GET['to']);
	}

	public function updateOT()
	{
		$request = $this->input->post('data');
		$data["overtime"] = $request[0]['value'];
		$data2["employee_id"] = $data["employee_id"] = $request[1]['value'];
		$data2["ot_date"] = $data["date"] = $request[2]['value'];
		if (isset($request[3]['value']) && $request[0]['value'] != "00:00") {
			$data["type"] = "-";
		} else {
			$data["type"] = "+";
		}
		$data2["is_ot"] = "Y";

		// Get previously stored late hours
		$previous_record = $this->db->get_where('manual_ot', [
			'date' => $data['date'],
			'employee_id' => $data['employee_id']
		])->row();

		$this->db->replace("manual_ot", $data);
		// $this->db->replace('ot_days', $data2);

		// Insert log
		$from_time = NULL;
		$log_action = 'Added,Manual OT';

		if (!is_null($previous_record)) {
			$from_time = (($previous_record->type === '-') ? $previous_record->type : '')
				. $previous_record->overtime;
			$log_action = 'Edited,Manual OT';
		}

		$ot_with_signs = (($data['type'] === '-') ? $data['type'] : '') . $data['overtime'];

		$log_data = [
			'action' => $log_action,
			'target_id' => $data['employee_id'],
			'from_time' => $from_time,
			'to_time' => $ot_with_signs,
			'for_date' => $data['date']

		];
		insert_log('Manual OT', $log_data);

		echo "Success";
	}

	public function updateLateHours()
	{
		$request = $this->input->post('data');
		$data["late_hours"] = $request[0]['value'];
		$data["employee_id"] = $request[1]['value'];
		$data["date"] = $request[2]['value'];

		// Get previously stored late hours
		$previous_record = $this->db->get_where('manual_late', [
			'date' => $data['date'],
			'employee_id' => $data['employee_id']
		])->row();

		// Update hours
		$this->db->replace("manual_late", $data);
		// Insert log
		$from_time = NULL;
		$log_action = 'Added,Late In';

		if (!is_null($previous_record)) {
			$from_time = $previous_record->late_hours;
			$log_action = 'Edited,Late In';
		}

		$log_data = [
			'action' => $log_action,
			'target_id' => $data['employee_id'],
			'from_time' => $from_time,
			'to_time' => $data['late_hours'],
			'for_date' => $data['date']
		];
		insert_log('Late In', $log_data);

		echo "Success";
	}

	public function updateLateHoursBreak()
	{
		$request = $this->input->post('data');
		$data["late_hours_break"] = $request[0]['value'];
		$data["employee_id"] = $request[1]['value'];
		$data["date"] = $request[2]['value'];

		// Get previously stored late hours break
		$previous_record = $this->db->get_where('manual_late_break', [
			'date' => $data['date'],
			'employee_id' => $data['employee_id']
		])->row();

		$this->db->replace("manual_late_break", $data);

		// Insert log
		$from_time = NULL;
		$log_action = 'Added,Late Break';

		if (!is_null($previous_record)) {
			$from_time = $previous_record->late_hours_break;
			$log_action = 'Edited,Late Break';
		}

		$log_data = [
			'action' => $log_action,
			'target_id' => $data['employee_id'],
			'from_time' => $from_time,
			'to_time' => $data['late_hours_break'],
			'for_date' => $data['date'],
		];
		insert_log('Late Break', $log_data);

		echo "Success";
	}

	public function deleteLateHoursBreak()
	{
		$request = $this->input->post('data');
		// echo "<pre>";
		// print_r($request);
		// echo "</pre>";
		// $data["late_hours_break"] = $request[0]['value'];
		// echo $data["late_hours_break"];exit;
		$data["employee_id"] = $request[0]['value'];
		$data["date"] = $request[1]['value'];

		// Get previously stored late hours break
		$previous_record = $this->db->get_where('manual_late_break', [
			'date' => $data['date'],
			'employee_id' => $data['employee_id']
		])->row();

		// Deleting record from database
		$this->db->where('employee_id', $data["employee_id"]);
		$this->db->where('date', $data["date"]);
		$this->db->delete("manual_late_break");

		// Insert log
		$from_time = NULL;
		$log_action = 'Deleted,Late Break';

		if (!is_null($previous_record)) {
			$from_time = $previous_record->late_hours_break;
		}

		$log_data = [
			'action' => $log_action,
			'target_id' => $data['employee_id'],
			'from_time' => $from_time,
			'to_time' => '00:00',
			'for_date' => $data['date'],
		];
		insert_log('Late Break', $log_data);

		echo "Success";
	}

	public function updateEarlyOutHours()
	{
		$request = $this->input->post('data');
		$data["early_out"] = $request[0]['value'];
		$data["employee_id"] = $request[1]['value'];
		$data["date"] = $request[2]['value'];

		// Get previously stored early out hours
		$previous_record = $this->db->get_where('manual_early_out', [
			'date' => $data['date'],
			'employee_id' => $data['employee_id']
		])->row();

		$this->db->replace("manual_early_out", $data);

		// Insert log
		$from_time = NULL;
		$log_action = 'Added,Early Out';

		if (!is_null($previous_record)) {
			$from_time = $previous_record->early_out;
			$log_action = 'Edited,Early Out';
		}

		$log_data = [
			'action' => $log_action,
			'target_id' => $data['employee_id'],
			'from_time' => $from_time,
			'to_time' => $data['early_out'],
			'for_date' => $data['date'],
		];
		insert_log('Early Out', $log_data);

		echo "Success";
	}

	public function updateShortHours()
	{
		$request = $this->input->post('data');
		$data["short_hours"] = $request[0]['value'];
		$data["employee_id"] = $request[1]['value'];
		$data["date"] = $request[2]['value'];
		$this->db->replace("manual_short_hours", $data);
		echo "Success";
	}



	public function updateClocking()
	{
		$request = $this->input->post('data');
		$time = $request[0]['value'] . ":00";
		$id = $request[1]['value'];
		$clocking = $this->db->select('employee_id, datetime')->from('clockings_news')->where('id', $id)->get()->row();

		$oldTime = $clocking->datetime;
		$oldTime = explode(" ", $oldTime);
		$oldTime[1] = $time;
		$newTime = implode(" ", $oldTime);
		// to catch db errors
		$db_debug = $this->db->db_debug; // save settings
		$this->db->db_debug = FALSE; // change setting
		$check = $this->db->set('datetime', $newTime)->where('id', $id)->update('clockings_news');
		$this->db->db_debug = $db_debug; // restore setting
		if (!$check) {
			$newTime = rtrim($newTime, "0") . "1";
			$this->db->set('datetime', $newTime)->where('id', $id)->update('clockings_news');
		}
		$this->fixAlternateClockings($clocking->employee_id, $oldTime[0]);
		echo "Success";
	}

	public function deleteOT()
	{
		$request = $this->input->post('data');
		$employee_id = $request[1]['value'];
		$date = $request[2]['value'];

		// Get previously stored early out hours
		$previous_record = $this->db->get_where('manual_ot', [
			'date' => $date,
			'employee_id' => $employee_id
		])->row();

		$this->db->where('employee_id', $employee_id)->where('date', $date)->delete('manual_ot');

		// Insert log
		$from_time = NULL;

		if (!is_null($previous_record)) {
			$from_time = (($previous_record->type === '-') ? $previous_record->type : '')
				. $previous_record->overtime;
		}

		$log_data = [
			'action' => 'Deleted,Manual OT',
			'target_id' => $employee_id,
			'from_time' => $from_time,
			'for_date' => $date,
		];
		insert_log('Manual OT', $log_data);

		echo "Success";
	}

	public function pdf($id, $first_day, $last_day)
	{

		$cid = get_user()["company_id"];

		$data = calculate_summary_data($id, $first_day, $last_day);
		$date = DateTime::createFromFormat('Y-m-d', $first_day);
		$data['from_f'] = $date->format('d/m/Y');
		$date = DateTime::createFromFormat('Y-m-d', $last_day);
		$data['to_f'] = $date->format('d/m/Y');
		$data['merged'] = false;
		$data['is_merged'] = true;
		if ($cid == 206 || $cid == 17 || $cid == 172) {
			$summary_body = $this->load->view('summary_pdf_body_generic', $data, true);
		} else {
			// $summary_body = $this->load->view('summary_pdf_body', $data, true);
			$summary_body = $this->load->view('summary_pdf_body_generic', $data, true);
		}
		$this->load->view('summary_pdf', ['summary_body' => $summary_body]);
		$html = $this->output->get_output();
		$this->dompdf->loadHtml($html);
		$this->dompdf->setPaper("A4", "landscape");
		$this->dompdf->render();
		$output = $this->dompdf->output();
		$file_name = str_replace("/", "_", $data['employee']->first_name) . " " . $data['employee']->special_id . " " . $data["month_name"] . " - Summary (" . time() . ").pdf";
		$new_file = "uploads/summary/" . $file_name;
		file_put_contents($new_file, $output);

		$path = "uploads/summary/" . $file_name;

		header('Content-Type: application/pdf');
		header("Content-Transfer-Encoding: Binary");
		header("Content-disposition: inline; filename=" . $file_name);
		readfile($path);
		// $this->dompdf->stream($data['employee']->first_name . " " . $data['employee']->special_id . " " . $data["month_name"] . " - Summary", array("Attachment" => 0));
		unlink($path);
		insert_log("Simple", ["action" => "Exported,Employee Summary PDF"]);
	}

	public function getClockings()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$employee_id = $request->id;
		$date = $request->date;
		$overnight = $request->overnight;
		$cut_off_time = $request->cut_off_time;

		$company_id = get_user()["company_id"];

		$company_interval_minutes = get_interval_minutes($company_id);

		if ($cut_off_time) {
			$cut_off_time = explode(":", $cut_off_time);
			$interval_minutes = $cut_off_time[0] * 60 + $cut_off_time[1];
		} else {
			$interval_minutes = $company_interval_minutes;
		}

		$last_ids = array(0);
		// if(!$shift){
		$prev_date = date('Y-m-d', strtotime('-1 day', strtotime($date)));
		$last_shift_check = $this->db->select('overnight, cut_off_time')->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->where('FIND_IN_SET(' . $employee_id . ',employees)>', 0)->where('date', $prev_date)->get()->row();
		if ($last_shift_check && $last_shift_check->overnight == "Yes") {
			if ($last_shift_check->cut_off_time) {
				$cut_off_time = explode(":", $last_shift_check->cut_off_time);
				$last_interval_minutes = $cut_off_time[0] * 60 + $cut_off_time[1];
			} else {
				$last_interval_minutes = $company_interval_minutes;
			}
			$prev_clockings = $this->db->select('id')->from('clockings_news')->where('employee_id', $employee_id)->where('date(date_sub(datetime, interval ' . $last_interval_minutes . ' minute)) = ', $prev_date)->get()->result();
			foreach ($prev_clockings as $c) {
				$last_ids[] = $c->id;
			}
		}
		// }

		$clockings = $this->db->select('id, type, DATE_FORMAT(datetime, "%H:%i") as time')->from('clockings_news')->where('employee_id', $employee_id)->where('deleted_at is null');

		if (!$overnight) {
			$clockings->where('date(datetime)', $date);
		} else {
			$clockings->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) = "' . $date . '"');
		}

		$clockings->where_not_in('id', $last_ids);

		$clockings = $clockings->order_by('datetime')->get()->result();

		$data["clockings"] = $clockings;

		echo json_encode($data);
	}

	public function saveClocking()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$cut_off_time = get_interval_minutes(get_user()["company_id"], true);

		$employee_id = $request->employee_id;
		$id = $request->id;
		$type = $request->type;
		$date = $request->date;
		$time = $request->time . ":00";
		$overnight = $request->overnight;
		$add_by_admin = 0;
		$update_by_admin = 0;

		$shift_id = 0;

		$query = 'SELECT shift_id, cut_off_time FROM shift_days join shifts on shifts.id = shift_days.shift_id where FIND_IN_SET(' . $employee_id . ', employees) > 0 and date = "' . $date . '"';
		$result = $this->db->query($query)->row();

		if ($result) {
			$shift_id = $result->shift_id;
			if ($result->cut_off_time) {
				$cut_off_time = $result->cut_off_time;
			}
		}

		if ($id) {
			$old_clocking = $this->db->select('datetime, type, add_by_admin')->from('clockings_news')->where('id', $id)->get()->row();
			$old_datetime = explode(" ", $old_clocking->datetime);
			$old_time = $old_datetime[1];

			$log_data = [
				'action' => 'Edited,Clocking',
				'target_id' => $employee_id,
				'from_time' => $old_time,
				'to_time' => $time,
				'for_date' => $date,
				'clocking_type' => $type,
			];

			$add_by_admin = $old_clocking->add_by_admin;

			$checking['old_clocking_type'] = $old_clocking->type;
			$checking['new_clocking_type'] = $type;
			$checking['old_clocking_time'] = $old_time;
			$checking['new_clocking_time'] = $time;
			if (
				$checking['old_clocking_type'] != $checking['new_clocking_type']
				&& $checking['old_clocking_time'] == $checking['new_clocking_time']
			) {
				//Do nothing;
			} else {
				$this->db->query("INSERT INTO edited_clockings (clocking_id, employee_id, `type`, `datetime`)
					VALUES ('$id', '$employee_id', '" . $old_clocking->type . "', '" . $old_clocking->datetime . "')
						ON DUPLICATE KEY UPDATE employee_id='$employee_id', `type`='" . $old_clocking->type . "', `datetime`= '" . $old_clocking->datetime . "'");
				$update_by_admin = 1;
			}
		} else {
			$log_data = [
				'action' => 'Added,Clocking',
				'target_id' => $employee_id,
				'to_time' => $time,
				'for_date' => $date,
				'clocking_type' => $type,
			];

			$add_by_admin = 1;
		}

		insert_log("Clockings", $log_data);

		if ($overnight && $time <= $cut_off_time) {
			$date = date('Y-m-d', strtotime($date . ' +1 day'));
		}

		$datetime = $date . " " . $time;

		$clocking_data = [
			'employee_id' => $employee_id,
			'type' => $type,
			'datetime' => $datetime,
			'shift_id' => $shift_id,
			'add_by_admin' => $add_by_admin,
			'update_by_admin' => $update_by_admin,
		];

		if ($id) {
			$this->db->where('id', $id)->update('clockings_news', $clocking_data);
		} else {
			$this->db->insert('clockings_news', $clocking_data);
		}

		update_new_clockings($employee_id, $datetime);

		$this->fixAlternateClockings($employee_id, $date);

		$data["success"] = true;
		$data["msg"] = $id ? "Clocking updated successfully" : "Clocking added successfully";

		echo json_encode($data);
	}

	public function deleteClocking()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$id = $request->id;

		$clocking = $this->db->select('employee_id,datetime,type')->from('clockings_news')->where('id', $id)->get()->row();

		$deleted_at = date('Y-m-d H:i:s');

		// update deleted_at and delete_by_admin in clockings_news
		$this->db->set('deleted_at', $deleted_at)->set('delete_by_admin', 1)->where('id', $id)->update('clockings_news');

		// delete from new_clockings where clock_in_id = id or clock_out_id = id based on type
		$where_column = $clocking->type == 'in' ? 'clock_in_id' : 'clock_out_id';
		$this->db->where($where_column, $id)->delete('new_clockings');

		update_new_clockings($clocking->employee_id, $clocking->datetime);

		// update deleted_at in edited_clockings
		$this->db->set('deleted_at', $deleted_at)->where('clocking_id', $id)->update('edited_clockings');

		$old_datetime = explode(" ", $clocking->datetime);
		$date = $old_datetime[0];
		$time = $old_datetime[1];

		$log_data = [
			'action' => 'Removed,Clocking',
			'target_id' => $clocking->employee_id,
			'from_time' => $time,
			'for_date' => $date,
			'clocking_type' => $clocking->type,
		];
		insert_log('Clockings', $log_data);

		$this->fixAlternateClockings($clocking->employee_id, $date);

		$data["success"] = true;
		$data["msg"] = "Clocking deleted successfully";

		echo json_encode($data);
	}

	public function fixAlternateClockings($employee_id, $date)
	{
		if (empty($date)) return;
		$employee = $this->db->get_where('employees', ['id' => $employee_id])->row();
		if (is_alternate_clockings($employee->branch_id)) {
			$this->fix_clockings($employee_id, $date);
		}
	}

	public function getXCRUD()
	{
		$emp_id = $this->input->get('emp_id');
		$date = $this->input->get('date');
		$overnight = $this->input->get('overnight');
		$shift = $this->input->get('shift');

		$company_id = get_user()["company_id"];
		$interval_minutes = get_interval_minutes($company_id);



		$this->load->helper('xcrud');
		$xcrud = xcrud_get_instance();
		$xcrud->table('clockings_news');
		$xcrud->where('employee_id', $emp_id);


		if ($overnight == "false") {
			$xcrud->where('date(datetime) = "' . $date . '"');
		} else {
			$xcrud->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) = "' . $date . '"');
		}
		//remove morning clocking in case of overnight shift
		// if($shift == "false"){
		$last_ids = array(0);
		$prev_date = date('Y-m-d', strtotime('-1 day', strtotime($date)));
		$last_shift_check = $this->db->select('overnight')->from('shift_days sd')->join('shifts s', 's.id = sd.shift_id')->where('FIND_IN_SET(' . $emp_id . ',employees)>', 0)->where('date', $prev_date)->get()->row();
		if ($last_shift_check && $last_shift_check->overnight == "Yes") {
			$clockings = $this->db->select('id')->from('clockings_news')->where('employee_id', $emp_id)->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) = ', $prev_date)->get()->result();
			foreach ($clockings as $c) {
				$last_ids[] = $c->id;
			}
		}
		$xcrud->where('id !', $last_ids);
		// }


		$xcrud->where('deleted_at is null');
		$xcrud->columns('type,datetime');
		$xcrud->fields('type,datetime');
		$xcrud->order_by('datetime');
		$xcrud->change_type('datetime', 'time', '', array("placeholder" => "HH:MM"));
		$xcrud->pass_var('employee_id', $emp_id);
		$xcrud->pass_var('current_date', $date);
		if ($overnight == "false") {
			$xcrud->before_insert('makeClockingTime');
			$xcrud->before_update('makeClockingTimeUpdate');
		} else {
			$xcrud->before_insert('makeClockingTime_overnight');
			$xcrud->before_update('makeClockingTimeUpdate_overnight');
		}

		$xcrud->replace_remove('replace_remove_clocking');

		$xcrud->validation_required('datetime');
		$xcrud->hide_button('save_edit');
		$xcrud->unset_print();
		$xcrud->unset_csv();
		$xcrud->unset_search();
		$xcrud->unset_pagination();
		$xcrud->unset_limitlist();
		$xcrud->unset_sortable();
		$xcrud->unset_title();
		$xcrud->unset_view();
		$xcrud->label('datetime', 'Time');
		$xcrud->limit('all');
		$data["clockings"] = $xcrud->render();
		$clockings = $this->load->view('clockingXCRUD', $data, TRUE);
		echo $clockings;
	}



	public function save_trips()
	{
		$id = $this->input->get('id');
		$trips = $this->input->get('trips');
		$date = $this->input->get('date');
		$type = $this->input->get('type');
		$type_breakdown = explode("-", $type);
		$type = $type_breakdown[0];
		if ($type_breakdown[1] == "up") {
			$trips += 1;
		} else {
			$trips -= 1;
		}

		// Get previously stored late hours
		$previous_record = $this->db->get_where('trips', [
			'date' => $date,
			'employee_id' => $id,
			'type' => $type,
		])->row();

		$data = array(
			'employee_id' => $id,
			'no_of_trips' => $trips,
			'date' => $date,
			'type' => $type
		);


		$this->db->replace('trips', $data);

		// Insert log
		$from_trips = 0;

		if (!is_null($previous_record)) {
			$from_trips = $previous_record->no_of_trips;
		}

		$to_trips = $data['no_of_trips'];

		$log_data = [
			'action' => 'Edited,Trips Count',
			'target_id' => $data['employee_id'],
			'for_date' => $data['date'],
			'from_trips' => $from_trips,
			'to_trips' => $to_trips,
			'trip_type' => $type
		];
		insert_log('Trips Update', $log_data);

		echo json_encode(array("type" => $type, "trips" => $trips));
	}

	function change_deduction_setting()
	{

		$request = $this->input->post();

		$employee_id = $request['id'];
		$deduct = $request['deduct'];
		$deduct = ($deduct == 1) ? 'yes' : 'no';

		$this->db->set('deduct_from_ot_single', $deduct)->where('id', $employee_id)->update('employees');

		$employee_name = $this->db->select('first_name')->from('employees')->where('id', $employee_id)->get()->row()->first_name;
		insert_log("OT Deduction", ['action' => 'Changed,OT Deduction Settings', 'target_id' => $employee_id, 'is_ot_deducted' => ucfirst($deduct), 'target_name' => $employee_name]);
	}

	function getSettings()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$employee_id = $request->id;
		$date = $request->date;

		$current_user = get_user();
		$cid = $current_user["company_id"];
		$permissions_level = $current_user["permissions_level"];

		$employee = $this->db->select('first_name as name, special_id, branch_id, is_ot, is_early_ot, ot_type, ot_round, early_ot_round,round_first_hour_only, round_by_exact_hour, different_first_hour_rounding, inc_late_in, inc_early_out, inc_late_break, use_half_hours_for_saturdays')->from('employees e')->join('branches b', 'e.branch_id = b.id')->where('e.id', $employee_id)->get()->row();
		$ot_type_data = $this->db->select("ot_weekly_hours, ot_type")->from("branches")->where("company_id", $cid)
			->where("id", $employee->branch_id)->get()->row();
		$inc_late_in = $employee->inc_late_in == 1 ? true : false;
		$inc_late_break = $employee->inc_late_break == 1 ? true : false;
		$inc_early_out = $employee->inc_early_out == 1 ? true : false;
		$data["name"] = $employee->name;
		$data["special_id"] = $employee->special_id;
		$data["date"] = $date;
		$data["employee_id"] = $employee_id;
		$data["company_id"] = $cid;

		$data["date_s"] = date('d M, Y (l)', strtotime($date));
		$day_name = date('l', strtotime($date));
		$branch_id = $employee->branch_id;

		// $branch_shifts = $this->db->select('id, name')->from('shifts')->where('company_id', $cid)->where('(branch_id = ' . $branch_id . ' or branch_id is null)')->where('is_leave', 'no')->where('active', 1)->get()->result();
		// $leaves = $this->db->select('id, name')->from('shifts')->where('company_id', $cid)->where('is_leave', 'yes')->get()->result();
		// $branch_shifts = $this->db->select('id, name')->from('shifts')->where('company_id', $cid)->where('(branch_id = ' . $branch_id . ' or branch_id is null)')->where('active', 1)->order_by('is_leave DESC, name ASC')->get()->result();
		$shifts_query = $this->db->select("s.id, s.name, b.name as branch_name")->from("shifts s")
			->join("branches b", "s.branch_id = b.id", "left")->where("s.company_id = '$cid'")->where('active', 1);
		// if($permissions_level === 'Company') $shifts_query->where("(s.is_leave = 'yes' or s.branch_id is null)");
		if ($permissions_level === 'Outlet') $shifts_query->where("(s.branch_id = '$branch_id' or s.is_leave = 'yes' or s.branch_id is null)");
		$data['shifts'] = $shifts_query->order_by('s.is_leave DESC, s.name ASC')->get()->result();
		foreach ($data['shifts'] as $shi) {
			$shi->combined_name = $shi->name;
			if (!is_null($shi->branch_name)) {
				$shi->combined_name = "{$shi->branch_name} - {$shi->name}";
			}
		}

		// $data["shifts"] = array_merge($branch_shifts, $leaves);
		// $data['shifts'] = $branch_shifts;
		$shift_id = '';
		$shift = $this->db->select('shift_id')->from('shift_days sd')->where('FIND_IN_SET(' . $employee_id . ',employees)>', 0)->where('date', $date)->get()->row();
		if ($shift) $shift_id = $shift->shift_id;
		$data["shift_id"] = $shift_id;

		// approve/reject data

		$overtime = "00:00";
		$late_hours = "00:00";
		$break_late_hours = "00:00";
		$early_out = "00:00";

		$apply_overtime = $employee->is_ot == 1 ? true : false;
		$apply_early_overtime = $employee->is_early_ot == 1 ? true : false;

		$result_list = get_result_list(array($employee_id), $date, $date);
		$result_list_overnight = get_result_list_overnight(array($employee_id), $date, $date);

		$result_list_preshift = get_result_list_preshift(array($employee_id), $date, $date, true);

		$shifts = $this->db->select('id')->from('shifts')->where('branch_id', $employee->branch_id)->where('is_leave', 'no')->get()->result();

		$shift_ids = array(0);
		foreach ($shifts as $s) {
			$shift_ids[] = $s->id;
		}

		$approved_ot_list = get_approved_ot_list($shift_ids, $date, $date);

		$shift_list = get_shift_list($employee_id, $date, $date);

		$is_ot_list = get_is_ot_list($employee_id, $date, $date);

		$is_late_list = get_is_late_list($employee_id, $date, $date);

		$is_late_break_list = get_is_late_break_list($employee_id, $date, $date);

		$is_early_out_list = get_is_early_out_list($employee_id, $date, $date);


		$manual_late_list = get_manual_late_list($employee_id, $date, $date);

		$manual_late_break_list = get_manual_late_break_list($employee_id, $date, $date);

		$manual_early_out_list = get_manual_early_out_list($employee_id, $date, $date);

		$replacement_leaves_list = get_replacement_leaves_list($employee_id, $date, $date);

		$replacement_leave_check = search_replacement_leave($replacement_leaves_list, $date);

		$replaced_ph_list = get_replaced_ph_list($employee_id, $date, $date);

		if (!is_null($replacement_leave_check))
			$data["replacement_leave"] = to_html_date($replacement_leave_check->to);
		else $data["replacement_leave"] = "";

		$company_working_hours = get_company_working_hours($cid);
		$company_working_hours = get_employee_working_hours($company_working_hours, $employee_id);
		$company_half_hours = $company_working_hours->half_hours;
		$company_half_hours_decimal = toDecimal($company_half_hours);
		$company_working_hours = $company_working_hours->working_hours;
		$company_working_hours_decimal = toDecimal($company_working_hours);

		$ot_settings = get_ot_settings($employee->branch_id);
		$early_ot_settings = get_early_ot_settings($employee->branch_id);



		$is_ot = false;
		$is_late = true;
		$is_late_break = true;
		$is_early_out = true;
		$is_shift = false;

		$overnight = false;
		$preshift = false;
		$half_day = false;



		$shift_hours = "";
		$shift_check = search_from_list($shift_list, $date);
		if ($shift_check) {
			$shift_hours = $shift_check->shift_hours;
			$is_shift = true;
		}
		if ($shift_check && $shift_check->half_day == "Yes") {
			$half_day = true;
		}

		if ($shift_check && $shift_check->overnight == "Yes") {
			$result = search_clocking($result_list_overnight, $date);
			$overnight = true;
			$preshift = false;
		} elseif ($shift_check && $shift_check->is_preshift == "Yes") {
			$result = search_clocking($result_list_preshift, $date);
			$overnight = false;
			$preshift = true;
		} else {
			$result = search_clocking($result_list, $date);
			$overnight = false;
			$preshift = false;

			if (!$shift_check) {
				$result = remove_duplicate_clockings($result, $date, $shift_list, $result_list_overnight);
			}
		}
		$obj = new stdClass();
		$obj->is_shift = $is_shift ? "true" : "false";

		$is_ot_result = search_from_list($is_ot_list, $date);
		if ($is_ot_result) {
			$is_ot = $is_ot_result->is_ot == "Y" ? true : false;
		} else {
			$is_ot = get_is_ot_status($approved_ot_list, $shift_check, $date, $employee_id);
		}


		$is_late_result = search_from_list($is_late_list, $date);
		if ($is_late_result) {
			$is_late = $is_late_result->is_late == "Y" ? true : false;
		}

		$is_late_break_result = search_from_list($is_late_break_list, $date);
		if ($is_late_break_result) {
			$is_late_break = $is_late_break_result->is_late_break == "Y" ? true : false;
		}

		$is_early_out_result = search_from_list($is_early_out_list, $date);
		if ($is_early_out_result) {
			$is_early_out = $is_early_out_result->is_early_out == "Y" ? true : false;
		}
		$last_out = "";

		$formatted_data = [];
		foreach ($result as $key => $value) {
			// $value->total_time = total_time($value->clock_in_1, $value->clock_out_1);
			$value->total_time = calculate_total_hours($value->clock_in_1, $value->clock_out_1, $value->start_time, $value->early_ot_start, $value->early_ot_end, $value->search_date);

			$formatted_data[] = $value;
			if (array_key_exists($key + 1, $result)) {
				$x = new stdClass();
				$x->overtime_starts = $value->overtime_starts;
				$x->grace_time = $value->grace_time;
				$x->clock_in = $value->clock_out;
				$x->clock_in_1 = $value->clock_out_1;
				$x->early_ot_start = $value->early_ot_start;
				$x->early_ot_end = $value->early_ot_end;
				$x->clock_out = $result[$key + 1]->clock_in;
				$x->clock_out_1 = $result[$key + 1]->clock_in_1;
				$x->is_ot = $is_ot;
				$x->total_time = total_time($result[$key + 1]->clock_in_1, $value->clock_out_1);
				$formatted_data[] = $x;
			} else {
				$last_out = $value->clock_out_1;
			}
		}
		$obj->clockings = $formatted_data;
		if ($result) {
			$v = $result[0];
		}

		$break_and_late_hours = calculate_break_and_late_hours($obj->clockings, $v);
		$work_hours = $break_and_late_hours->work_hours;
		$break_hours = $break_and_late_hours->break_hours;
		$breaks_array = $break_and_late_hours->breaks_array;

		foreach ($obj->clockings as $key => $value) {
			if ($key == 0) {
				$manual_late = search_from_list($manual_late_list, $date);
				if ($manual_late) {
					$late_hours = $manual_late->late_hours;
					$late_hours = round_off_late_in($late_hours, get_late_in_settings($employee->branch_id), false);
				} else if (isset($v) && $v->is_leave != "" && $v->is_leave != "yes" && $v->void_late_in == "No") {
					if ($v->grace_time != "") {
						if ($overnight) {
							$grace_time = $date . " " . $v->grace_time . ":00";
							$grace_time_stamp = strtotime($grace_time);
							$mid_day = $date . " 12:00:00";
							$mid_day_stamp = strtotime($mid_day);
							if (in_array($shift_check->same_day_overnight, ['default', 'next'])) {
								if ($mid_day_stamp > $grace_time_stamp) {
									$grace_time_stamp += 24 * 3600;
								}
							}
							$clock_in_stamp = strtotime($v->clock_in_o);


							if ($clock_in_stamp > $grace_time_stamp) {
								$late_stamp = $clock_in_stamp - $grace_time_stamp;
								date_default_timezone_set('UTC');
								$late_hours = date('H:i', $late_stamp);
								date_default_timezone_set("Asia/Kuala_Lumpur");
							}
						} elseif ($preshift) {
							// Pre-shift logic
							// For preshift, the grace time should be calculated on the next day
							$next_day = date('Y-m-d', strtotime($date . ' +1 day'));
							$grace_time = $next_day . " " . $v->grace_time . ":00";
							$grace_time_stamp = strtotime($grace_time);

							$clock_in_stamp = strtotime($v->clock_in_o);

							// For preshift, clock_in is typically after midnight but before grace time
							// If clock_in is on the previous day, add 24 hours
							$clock_in_date = date('Y-m-d', $clock_in_stamp);
							if ($clock_in_date < $next_day) {
								$clock_in_stamp += 24 * 3600;
							}

							if ($clock_in_stamp > $grace_time_stamp) {
								$late_stamp = $clock_in_stamp - $grace_time_stamp;
								date_default_timezone_set('UTC');
								$late_hours = date('H:i', $late_stamp);
								date_default_timezone_set("Asia/Kuala_Lumpur");
							} else {
								$late_hours = "00:00";
							}
						} else if (intval(str_replace(":", "", $v->clock_in)) > intval(str_replace(":", "", $v->grace_time))) {
							$late_hours = sub_time($v->clock_in, $v->grace_time);
						}
					}
				}
			}
		}

		$break_not_taken = "00:00";
		$extra_break_not_taken = "00:00";
		if (isset($v)) {
			$break_not_taken = calculate_break_not_taken($break_hours, $breaks_array, $v);
		}
		if ($work_hours != "" && $work_hours != "00:00") {
			$work_hours = sub_time($work_hours, $break_not_taken);
		}
		if (isset($v)) {
			$extra_break_not_taken = calculate_extra_break_not_taken($breaks_array, $v, $extra_break_not_taken);
		}
		if ($work_hours != "" && $work_hours != "00:00") {
			$work_hours = sub_time($work_hours, $extra_break_not_taken);
		}


		if (isset($v) && !$half_day) {
			$manual_late_break = search_from_list($manual_late_break_list, $date);
			if ($manual_late_break) {
				$break_late_hours = $manual_late_break->late_hours_break;
			} else {
				$break_late_hours = calculate_break_late($break_hours, $breaks_array, $v, $work_hours, $obj->is_shift);
			}
		}

		if (!$half_day) {
			$manual_early_out = search_from_list($manual_early_out_list, $date);
			if ($manual_early_out) {
				$early_out = $manual_early_out->early_out;
			} else if ($last_out != "" && $shift_check && $shift_check->void_early_out == "No") {
				$early_out = calculate_early_out($last_out, $shift_check->end_time, $date, $overnight);
			}
		}
		if ($preshift && $shift_check && $shift_check->shift_hours != "") {
			$company_working_hours = $shift_check->shift_hours;
			$company_working_hours_decimal = toDecimal($shift_check->shift_hours);
		}
		if ($employee->ot_type == "eight_hours") {
			$decimal_work_hours = toDecimal($work_hours);
			$company_working_hours_decimal = toDecimal($company_working_hours);
			// if company working hours is 8 hours and employee worked less than 8 hours then calculate early out
			if ($company_working_hours_decimal && $decimal_work_hours < $company_working_hours_decimal && $decimal_work_hours > 0) {
				$decimal_early_out = $company_working_hours_decimal - $decimal_work_hours;
				$eight_hours_early_out = decimal_to_time($decimal_early_out);
				if (!$half_day) {
					$manual_early_out = search_from_list($manual_early_out_list, $date);
					if ($manual_early_out) {
						$early_out = $manual_early_out->early_out;
					} else if ($last_out != "" && $shift_check && $shift_check->void_early_out == "No") {
						$early_out = $eight_hours_early_out;
					}
				}
			}
		}

		$work_hours = add_deducted_time_in_work_hours($work_hours, $late_hours, $break_late_hours, $early_out, $inc_late_in, $inc_late_break, $inc_early_out, $is_late, $is_late_break, $is_early_out, $ot_type_data->ot_type);

		$is_replaced_ph = search_from_list($replaced_ph_list, $date);
		$is_replaced_ph = $is_replaced_ph ? true : false;

		$is_employee_off_day = is_employee_off_day($employee_id, $date);
		$date_f = date("d-m-Y", strtotime($date));

		$round_of_ot = 1;
		if ($shift_check) {
			$round_of_ot = $shift_check->round_off_ot;
		}
		$final_company_working_hours = $company_working_hours;
		$final_company_working_hours_decimal = $company_half_hours_decimal;
		if ($employee->ot_type == 'eight_hours' && $day_name == 'Saturday' && $employee->use_half_hours_for_saturdays) {
			$final_company_working_hours = $company_half_hours;
			$final_company_working_hours_decimal = $company_half_hours_decimal;
		}
		if ($preshift && $shift_check && $shift_check->shift_hours != "") {
			$final_company_working_hours = $shift_check->shift_hours;
			$final_company_working_hours_decimal = toDecimal($shift_check->shift_hours);
		}
		$overtime = calculate_final_overtime($result, $obj->clockings, $date_f, $overnight, $apply_overtime, $apply_early_overtime, $work_hours, $final_company_working_hours, $employee->ot_type, $employee->ot_round, $employee->round_first_hour_only, $employee->round_by_exact_hour, $employee->different_first_hour_rounding, $ot_settings, $shift_hours, $round_of_ot, $final_company_working_hours_decimal, $employee->early_ot_round, $early_ot_settings);

		$late_hours = round_off_late_in($late_hours, get_late_in_settings($employee->branch_id), true);

		$data["overtime"] = $overtime == "00:00" || $overtime == "" ? "-" : $overtime;
		$data["late_hours"] = $late_hours == "00:00" || $late_hours == "" ? "-" : $late_hours;
		$data["break_late_hours"] = $break_late_hours == "00:00" || $break_late_hours == "" ? "-" : $break_late_hours;
		$data["early_out"] = $early_out == "00:00" || $early_out == "" ? "-" : $early_out;

		$data["is_ot"] = $is_ot;
		$data["is_late"] = $is_late;
		$data["is_late_break"] = $is_late_break;
		$data["is_early_out"] = $is_early_out;
		$data["is_replaced_ph"] = $is_replaced_ph;
		$data["is_employee_off_day"] = $is_employee_off_day;

		echo json_encode($data);
	}

	function update_shift()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$employee_id = $request->employee_id;
		$shift_id = $request->shift;
		$date = $request->date;

		$company_id = get_user()["company_id"];
		$interval_minutes = get_interval_minutes($company_id);

		$shift_day = $this->db->query("SELECT * FROM shift_days WHERE shift_id = $shift_id AND date = '$date'")->row();

		$shift = $this->db->query("SELECT id,name,color,code,overnight FROM shifts WHERE id = $shift_id")->row();

		if ($shift_day) {

			$employees_new = explode(",", $shift_day->employees);

			$shift_day_prev = $this->db->query("SELECT * FROM shift_days WHERE date = '$date' AND FIND_IN_SET($employee_id,employees)")->row();

			$employees = array();

			if ($shift_day_prev) {
				$employees = explode(",", $shift_day_prev->employees);
			}

			$employees = array_diff($employees, array($employee_id));
			$employees_new = array_diff($employees_new, array($employee_id));


			if ($shift_day_prev) {
				$remove_data = array(
					'employees' => trim(implode(",", $employees), ",")
				);
				$this->db->where('id', $shift_day_prev->id);
				$this->db->update('shift_days', $remove_data);
			}


			array_push($employees_new, $employee_id);

			$update_data = array(
				'employees' => trim(implode(",", $employees_new), ",")
			);



			$this->db->where('id', $shift_day->id);
			$this->db->update('shift_days', $update_data);
		} else {


			$shift_day_prev = $this->db->query("SELECT * FROM shift_days WHERE date = '$date' AND FIND_IN_SET($employee_id,employees)")->row();

			$employees =  array();

			if ($shift_day_prev) {
				$employees = explode(",", $shift_day_prev->employees);
			}

			$employees = array_diff($employees, array($employee_id));


			if ($shift_day_prev) {
				$remove_data = array(
					'employees' => trim(implode(",", $employees), ",")
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

		$clocking_ids_to_update = get_clocking_ids_to_update($date, $employee_id, $shift);

		$this->db->query("UPDATE clockings_news SET shift_id = $shift_id WHERE id IN ($clocking_ids_to_update)");

		$datetime = $date . " 00:00:00";
		update_new_clockings($employee_id, $datetime);

		$data["msg"] = "Shift changed successfully. Reload page to see changes.";

		echo json_encode($data);
	}

	function delete_shift()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$employee_id = $request->employee_id;
		$shift_id = $request->shift;
		$date = $request->date;

		$company_id = get_user()["company_id"];
		$interval_minutes = get_interval_minutes($company_id);

		$shift_day = $this->db->query("SELECT * FROM shift_days WHERE shift_id = $shift_id AND date = '$date'")->row();

		$shift = $this->db->query("SELECT id,name,color,code,overnight FROM shifts WHERE id = $shift_id")->row();

		if (!empty($shift_day)) {

			$employees_new = explode(",", $shift_day->employees);

			$shift_day_prev = $this->db->query("SELECT * FROM shift_days WHERE date = '$date' AND FIND_IN_SET($employee_id,employees)")->row();

			$employees = array();

			if ($shift_day_prev) {
				$employees = explode(",", $shift_day_prev->employees);
			}

			$employees = array_diff($employees, array($employee_id));
			$employees_new = array_diff($employees_new, array($employee_id));


			if ($shift_day_prev) {
				$remove_data = array(
					'employees' => trim(implode(",", $employees), ",")
				);
				$this->db->where('id', $shift_day_prev->id);
				$this->db->update('shift_days', $remove_data);
			}


			array_push($employees_new, $employee_id);

			$update_data = array(
				'employees' => trim(implode(",", $employees_new), ",")
			);



			$this->db->where('id', $shift_day->id);
			$this->db->delete('shift_days', $update_data);
			$data["msg"] = "Shift deleted successfully. Reload page to see changes.";
		} else {
			$data["msg"] = "No Shift Available!";
			echo json_encode($data);
			die;
		}

		$clocking_ids_to_update = get_clocking_ids_to_update($date, $employee_id, $shift);

		$this->db->query("UPDATE clockings_news SET shift_id = '' WHERE id IN ($clocking_ids_to_update)");

		$datetime = $date . " 00:00:00";

		update_new_clockings($employee_id, $datetime);

		echo json_encode($data);
	}

	function refresh_shift()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$employee_id = $request->employee_id;
		$shift_id = $request->shift ? $request->shift : 0;
		$date = $request->date;

		$shift = $this->db->query("SELECT id,name,color,code,overnight FROM shifts WHERE id = $shift_id")->row();

		$clocking_ids_to_update = get_clocking_ids_to_update($date, $employee_id, $shift);

		$this->db->query("UPDATE clockings_news SET shift_id = $shift_id WHERE id IN ($clocking_ids_to_update)");

		$datetime = $date . " 00:00:00";

		update_new_clockings($employee_id, $datetime);

		$data["msg"] = "Clokings shift refreshed successfully. Reload page to see changes.";

		echo json_encode($data);
	}

	public function refresh_shifts($id)
	{
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

		$period = new DatePeriod(
			new DateTime($first_day),
			new DateInterval('P1D'),
			(new DateTime($last_day))->add(new DateInterval('P1D'))
		);

		foreach ($period as $date) {
			$current_date = $date->format('Y-m-d');

			$shift_id = '';
			$shift = null;

			$assigned_shift = $this->db->query("SELECT * FROM shift_days WHERE date = '$current_date' AND FIND_IN_SET($id,employees)")->row();

			if ($assigned_shift) {
				$shift_id = $assigned_shift->shift_id;
			}

			if ($shift_id) {
				$shift = $this->db->query("SELECT id,name,color,code,overnight FROM shifts WHERE id = $shift_id")->row();
			}

			$clocking_ids_to_update = get_clocking_ids_to_update($current_date, $id, $shift);

			$this->db->query("UPDATE clockings_news SET shift_id = $shift_id WHERE id IN ($clocking_ids_to_update)");
		}

		$datetime = $first_day . " 00:00:00";
		$last_datetime = $last_day . " 23:59:59";

		update_new_clockings($id, $datetime, $last_datetime);

		redirect('summary/view/' . $id . '/?from=' . $_GET['from'] . '&to=' . $_GET['to']);
	}

	function change_status()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$employee_id = $request->employee_id;
		$date = $request->date;
		$type = $request->type;
		$status = $request->status ? 'Y' : 'N';
		$status_for_log = $request->status ? 'Yes' : 'No';

		$first_name = $this->db->select('first_name')->from('employees')->where('id', $employee_id)->get()->row()->first_name;
		$log_data = [
			'action' => 'Changed,Summary Status',
			'summary_status' => $status_for_log,
			'for_date' => $date,
			'target_name' => $first_name,
			'target_id' => $employee_id,
		];

		if ($type == "late_hours") {
			$new_data = array('employee_id' => $employee_id, 'late_date' => $date, 'is_late' => $status);
			$this->db->replace('late_days', $new_data);
			$data["msg"] = "Late In status changed. Reload page to see changes.";
			$log_data['summary_status_type'] = 'Late In';
		} else if ($type == "break_late_hours") {
			$new_data = array('employee_id' => $employee_id, 'late_break_date' => $date, 'is_late_break' => $status);
			$this->db->replace('late_break_days', $new_data);
			$data["msg"] = "Late (Break) status changed. Reload page to see changes.";
			$log_data['summary_status_type'] = 'Late Break';
		} else if ($type == "early_out") {
			$new_data = array('employee_id' => $employee_id, 'early_out_date' => $date, 'is_early_out' => $status);
			$this->db->replace('early_out_days', $new_data);
			$data["msg"] = "Early Out status changed. Reload page to see changes.";
			$log_data['summary_status_type'] = 'Early Out';
		} else if ($type == "overtime") {
			$new_data = array('employee_id' => $employee_id, 'ot_date' => $date, 'is_ot' => $status);
			$this->db->replace('ot_days', $new_data);
			$data["msg"] = "Overtime status changed. Reload page to see changes.";
			$log_data['summary_status_type'] = 'Overtime';
		}

		insert_log('Summary Status', $log_data);
		echo json_encode($data);
	}

	public function update_replacement_leave()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$to = to_mysql_date($request->replacement_date);
		$to_date = new DateTime($to);
		$before_to_date = $to_date->modify("-1 day");
		$before_to_date = $to_date->format("Y-m-d");


		$employee_id = $request->employee_id;
		$from = $request->date;

		$from_date = DateTime::createFromFormat("Y-m-d", $from);
		$to_date = DateTime::createFromFormat("Y-m-d", $to);

		$response = ["msg" => "", "success" => false];

		// from date must not be a leave
		$from_shift_list = get_shift_list($employee_id, $from, $from);
		$from_shift = search_from_list($from_shift_list, $from);
		if (!is_array($from_shift) && $from_shift->is_leave == "yes") {
			$response["msg"] = "{$from_date->format("d/m")} is a leave";
			return send_json_response($response);
		}

		// to date must not be a leave
		$to_shift_list = get_shift_list($employee_id, $to, $to);
		$to_shift = search_from_list($to_shift_list, $to);
		if (!is_array($to_shift) && $to_shift->is_leave == "yes") {
			$response["msg"] = "{$to_date->format("d/m")} is a leave";
			return send_json_response($response);
		}

		$from_clockings_count = $this->db->select("count(1) as count")->from("clockings_news")->where("employee_id", $employee_id)->where("DATE(`datetime`)", $from)->get()->row()->count;
		if ($from_clockings_count == 0) {
			$response["msg"] = "{$from_date->format("d/m")} does not contain clockings";
			return send_json_response($response);
		}

		// Cannot replace same dates
		if ($from == $to) {
			$response["msg"] = "Leave and Replacement dates can't be same";
			return send_json_response($response);
		}

		// Cannot replace same date for two different dates
		$is_date_replaced = $this->db->get_where("replacement_leave_dates", ["employee_id" => $employee_id, "to" => $to, "from <>" => $from])->row();
		if (!is_null($is_date_replaced)) {
			$response["msg"] = "{$to_date->format("d/m")} is already set as replacement leave";
			return send_json_response($response);
		}

		// Cannot replace a date which is already replaced
		$is_set_as_replaced = $this->db->get_where("replacement_leave_dates", ["employee_id" => $employee_id, "to" => $from])->row();
		if (!is_null($is_set_as_replaced)) {
			$response["msg"] = "{$to_date->format("d/m")} is already set as replacement leave for {$from_date->format("d/m")}";
			return send_json_response($response);
		}

		// Cannot use a date to replace other date if it is replaced
		$is_replacement_a_leave = $this->db->get_where("replacement_leave_dates", ["employee_id" => $employee_id, "from" => $to])->row();
		if (!is_null($is_replacement_a_leave)) {
			$response["msg"] = "{$to_date->format("d/m")} is already replaced";
			return send_json_response($response);
		}

		// replacement must be done between same month dates
		// if(!are_dates_in_same_month($from, $to)) {
		// 	$response["msg"] = "Dates should be in same month";
		// 	return send_json_response($response);
		// }

		// `from` date must be smaller than `to` date
		// if($from_date > $to_date) {
		// 	$response["msg"] = "Cannot replace bigger date with smaller";
		// 	return send_json_response($response);
		// }

		$to_clockings_count = $this->db->select("count(1) as count")->from("clockings_news")->where("employee_id", $employee_id)->where("DATE(`datetime`)", $to)->get()->row()->count;
		if ($to_clockings_count > 0) {
			$before_to_date_shifts = get_shift_list($employee_id, $before_to_date, $before_to_date);
			$before_to_date_shift = search_from_list($before_to_date_shifts, $before_to_date);
			if (
				count($before_to_date_shift) === 0 ||
				(!is_array($before_to_date_shift) && $before_to_date_shift->overnight !== "Yes")
			) {
				$response["msg"] = "{$to_date->format("d/m")} contains clockings, cannot replace it";
				return send_json_response($response);
			}
		}

		$this->db->replace("replacement_leave_dates", array("employee_id" => $employee_id, "from" => $from, "to" => $to));

		$response = ["msg" => "Replacement leave set successfully", "success" => true];
		return send_json_response($response);
	}

	public function remove_replacement_leave()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$date_object = DateTime::createFromFormat("Y-m-d", $request->date);

		$this->db->delete("replacement_leave_dates", ["employee_id" => $request->employee_id, "from" => $request->date]);
		$deleted_rows = $this->db->affected_rows();
		$response = ["msg" => "{$date_object->format("d/m")} doesn't replace any other date", "success" => false];

		if ($deleted_rows > 0) {
			$response = ["msg" => "Replacement from {$date_object->format("d/m")} removed successfully", "success" => true];
		}

		return send_json_response($response);
	}

	/**
	 * Update manual PH for a date and employee
	 *
	 * @return Output
	 */
	public function update_replacement_ph_status()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$data = [
			"employee_id" => $request->employee_id,
			"date" => $request->date,
		];

		if ($request->is_replaced_ph === true) {
			$this->db->insert("replaced_ph_days", $data);
		} else {
			$this->db->delete("replaced_ph_days", $data);
		}


		return send_json_response(["msg" => "Please reload the page"]);
	}
	public function update_employee_off_day()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$data = [
			"employee_id" => $request->employee_id,
			"date" => $request->date,
		];


		if ($request->is_employee_off_day === true) {
			$this->db->insert("employee_off_days", $data);
		} else {
			$this->db->delete("employee_off_days", $data);
		}

		return send_json_response(["msg" => "Please reload the page"]);
	}
	public function get_manual_late()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$manual_late = $this->db->get_where("manual_late", ["employee_id" => $request->employee_id, "date" => $request->date])->row();
		if (is_null($manual_late)) {
			return send_json_response(['data' => null], 404);
		}
		return send_json_response(["data" => $manual_late]);
	}

	public function delete_manual_late()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		$this->db->delete("manual_late", ["id" => $request->id]);
		return send_json_response(["message" => "Manual late deleted successfully. Please reload the page"]);
	}

}
