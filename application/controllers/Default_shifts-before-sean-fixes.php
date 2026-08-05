<?php
class Default_shifts extends CI_Controller
{




	//hello brother, this is by naveed

	function __construct()
	{
		parent::__construct();

		if (is_null(get_user())) {
			redirect("welcome");
			//var_dump($this->session->userdata('antelope_user'));
		}
	}

	function index()
	{
		if (!is_page_permitted('default_shifts')) {
			redirect_if_not_permitted();
		}

		$data['pageTitle'] = "Yearly Shifts";
		$data['active_menu'] = "default_shifts";
		$this->load->view('header', $data);
		$data["menus"] = get_menus();
		$this->load->view('sidebar', $data);

		$this->load->view('default_shifts', $data);
		$this->load->view('footer');
	}

	function getBranchesAndOutlets()
	{
		$current_user = get_user();
		$cid = $current_user["company_id"];

		$bid = $current_user["branch_id"];
		$permissions_level = $current_user["permissions_level"];
		$where_filter = "";
		$where_branch_2 = '';

		if ($permissions_level == "Outlet") {
			$where_branch_2 = " AND id = $bid ";
			$where_filter . " employees.branch_id = " . $bid . " AND ";
		}

		$data["branches"] = $this->db->query("SELECT id,name FROM branches WHERE company_id = $cid  $where_branch_2 ORDER BY name")->result();
		$data["departments"] = $this->db->query("SELECT id,name FROM departments WHERE company_id = $cid ORDER BY name")->result();
		$data["sections"] = $this->db->query("SELECT id,title as name FROM sections WHERE company_id = $cid ORDER BY name")->result();
		$data["positions"] = $this->db->query("SELECT id,title as name FROM positions WHERE company_id = $cid ORDER BY name")->result();
		$data["sections"] = $this->db->query("SELECT id,title as name FROM sections WHERE company_id = $cid ORDER BY name")->result();
		$data["holidays"] = get_public_holidays_for_default_shift();

		echo json_encode($data);
	}

	function getShifts()
	{
		$cid = get_user()["company_id"];
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$branch_id = $request->branch_id;
		if (empty($branch_id) or is_null($branch_id)) {
			$data["shifts"] = $this->db->select('id, concat(name, " - ", COALESCE(code, "")) as name')->from('shifts')->where('company_id', $cid)->where('(branch_id = 0 OR branch_id is NULL)')->where('is_leave', 'no')->where('deleted_at is null')->where('active', 1)->order_by('name', 'asc')->get()->result();
		} else {
			$data["shifts"] = $this->db->select('id, concat(name, " - ", COALESCE(code, "")) as name')->from('shifts')->where('company_id', $cid)->where('(branch_id = 0 OR branch_id is NULL OR branch_id = ' . $branch_id . ')')->where('is_leave', 'no')->where('deleted_at is null')->where('active', 1)->order_by('name', 'asc')->get()->result();
		}
		echo json_encode($data);
	}

	function assignShifts()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$branch_id = $request->branch;
		$department_id = $request->department;
		$section_id = $request->section;
		$position_id = $request->position;
		$shift_id = $request->shift_id;
		$shift_days = $request->shift_days;
		$public_holidays = $request->public_holidays;
		$selected_employees = $request->employees;
		$type = $request->type;
		$session_id = $request->session_id;
		$groups = $request->groups;

		$shift_type = $request->shift_type;
		$pattern_id = $request->pattern_id;
		$starting_week = $request->starting_week;

		$shift_ids = [$shift_id];
		if ($shift_type == 'shift_pattern') {
			$shiftPattern = $this->db->where('id', $pattern_id)->get('shift_patterns')->row();
			$pattern = json_decode($shiftPattern->pattern);

			$shift_ids = $this->getOrderedShiftIds($pattern, $starting_week, $request->from_date);
		}

		$this->db->replace('default_shift_progress', [
			"session_id" => $session_id,
			"progress" => 0
		]);

		session_write_close();

		// check if public holidays contains none
		if (in_array('none', $public_holidays)) {
			$public_holidays = [];
		} else if (empty($public_holidays)) {
			$user = (object)get_user();
			$bid = $user->branch_id;
			$permissions_level = $user->permissions_level;
			if ($permissions_level === 'Outlet') {
				$public_holidays = get_public_holidays($bid);
			} else {
				$public_holidays = get_public_holidays();
			}
		}



		$this->db->select('e.id')->from('employees e')->join('roles r', 'e.role_id = r.id');

		if ($groups) {
			$this->db->join('employee_groups_relation egr', 'e.id = egr.employee_id');
			$this->db->where_in('egr.group_id', $groups);
		}

		$this->db->where('e.deleted_at is null')->where('branch_id', $branch_id);
		if (count($department_id) > 0) {
			$this->db->where_in('department_id', $department_id);
		}
		if (count($section_id) > 0) {
			$this->db->where_in('section_id', $section_id);
		}
		if (count($position_id) > 0) {
			$this->db->where_in('position_id', $position_id);
		}

		if ($selected_employees) {
			$this->db->where_in('e.id', $selected_employees);
		}

		$this->db->where('exclude_from_system', 'no');

		$employees = $this->db->get()->result();

		$employees_ids = array();

		foreach ($employees as $emp) {
			$employees_ids[] = $emp->id;
		}

		// make Y-m-d dates from $request->from_date to $request->to_date, format is d/m/Y
		$first_day = DateTime::createFromFormat('d/m/Y', $request->from_date)->format('Y-m-d');
		$last_day = DateTime::createFromFormat('d/m/Y', $request->to_date)->format('Y-m-d');

		$total_days = round((strtotime($last_day) - strtotime($first_day)) / (60 * 60 * 24) + 1);

		$period = new DatePeriod(
			new DateTime($first_day),
			new DateInterval('P1D'),
			(new DateTime($last_day))->add(new DateInterval('P1D'))
		);

		$count = 0;

		foreach ($period as $p) {
			$shift_id = $shift_ids[$count % count($shift_ids)];

			$date = $p->format('Y-m-d');
			if ($shift_id && (in_array($p->format('l'), $shift_days) || $shift_type == 'shift_pattern') && !in_array($date, $public_holidays) && $type != "delete-all") {
				$eligible = array();
				foreach ($employees_ids as $e) {
					$already_assigned = $this->db->select('id, employees')->from('shift_days')->where('FIND_IN_SET(' . $e . ', employees)')->where('date', $date)->get()->row();
					// if type is other than empty (default)
					if ($type) {
						// remove employee from already_assigned if exist
						if ($already_assigned) {
							$this->modify_shift_employees($already_assigned, $e, "remove");
						}
						$eligible[] = $e;
					} else if (empty($already_assigned)) {
						$eligible[] = $e;
					}
				}

				$current_shift_exist = $this->db->select('id,employees')->from('shift_days')->where('shift_id', $shift_id)->where('date', $date)->get()->row();
				if ($current_shift_exist) {
					$this->modify_shift_employees($current_shift_exist, $eligible, "add");
				} else {
					$new_employees = implode(",", $eligible);
					$data = array("date" => $date, "shift_id" => $shift_id, "employees" => $new_employees);
					$this->db->insert('shift_days', $data);
				}
				$this->db->set('shift_id', $shift_id)->where_in('employee_id', $eligible)->where('date(datetime)', $date)->update('clockings_news');
				$this->db->set('shift_id', $shift_id)->where_in('employee_id', $eligible)->where('date(clock_in)', $date)->update('new_clockings');
			} else if ($type == "delete-overwrite" || $type == "delete-all") {
				// remove shift of employees if assigned on this day
				foreach ($employees_ids as $e) {
					$assigned_shift = $this->db->select('id,employees')->from('shift_days')->where('FIND_IN_SET(' . $e . ', employees)')->where('date', $date)->get()->row();
					if ($assigned_shift) {
						$this->modify_shift_employees($assigned_shift, $e, "remove");
					}
					// remove shift_id from clockings_news
					$clocking_ids_to_update = get_clocking_ids_to_update($date, $e, $assigned_shift);
					$this->db->query("UPDATE clockings_news SET shift_id = '' WHERE id IN ($clocking_ids_to_update)");

					$datetime = $date . " 00:00:00";

					foreach ($employees_ids as $employee_id) {
						update_new_clockings($employee_id, $datetime);
					}
				}
			}
			$count++;

			$progress = floor($count / $total_days * 100);

			$this->db->update('default_shift_progress', [
				"session_id" => $session_id,
				"progress" => $progress
			]);
		}

		$this->db->where('session_id', $session_id)->delete('default_shift_progress');

		$data = array();
		$data["success"] = true;
		echo json_encode($data);
	}

	function getEmployees()
	{
		$current_user = get_user();
		$cid = $current_user["company_id"];
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$branch_id = $request->branch;
		$department_id = $request->department;
		$section_id = $request->section;
		$position_id = $request->position;
		$groups = $request->groups;

		$this->db->select('e.id, e.first_name as name, e.special_id')->from('employees e')->join('roles r', 'e.role_id = r.id');

		if ($groups) {
			$this->db->join('employee_groups_relation egr', 'e.id = egr.employee_id');
			$this->db->where_in('egr.group_id', $groups);
		}

		$this->db->where('e.deleted_at is null')->where('e.company_id', $cid);
		if ($branch_id != '') {
			$this->db->where('branch_id', $branch_id);
		}
		if (count($department_id) > 0) {
			$this->db->where_in('department_id', $department_id);
		}
		if (count($section_id) > 0) {
			$this->db->where_in('section_id', $section_id);
		}
		if (count($position_id) > 0) {
			$this->db->where_in('position_id', $position_id);
		}

		$this->db->where('exclude_from_system', 'no')->where('e.employee_status', 'active');

		$this->db->order_by('e.special_id', 'asc');

		$data["employees"] = $this->db->get()->result();
		echo json_encode($data);
	}

	function modify_shift_employees($shift_data, $e, $action)
	{
		$old_employees = array();
		if ($shift_data->employees != "") {
			$old_employees = explode(",", $shift_data->employees);
		}
		if ($action == "add") {
			$new_employees = array_merge($old_employees, $e);
		} else {
			$new_employees = array_diff($old_employees, array($e));
		}
		$new_employees = implode(",", array_unique($new_employees));
		$this->db->set('employees', $new_employees)->where('id', $shift_data->id)->update('shift_days');
	}

	function getProgress()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$session_id = $request->session_id;

		$progress_session = $this->db->select('progress')->from('default_shift_progress')->where('session_id', $session_id)->get()->row();

		$progress = 0;

		if ($progress_session) {
			$progress = $progress_session->progress;
		}

		echo json_encode([
			'progress' => $progress
		]);
	}

	function getGroups()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$branch_id = $request->branch_id;
		$current_user = get_user();
		$cid = $current_user["company_id"];

		$data["groups"] = $this->db->select('id, name')->from('employee_groups')->where('company_id', $cid)->where('branch_id', $branch_id)->get()->result();

		echo json_encode($data);
	}

	function getOrderedShiftIds($pattern, $startWeek, $from_date)
	{
		// get name of day from date, from date is in format d/m/Y
		$startDay = DateTime::createFromFormat('d/m/Y', $from_date)->format('D');
		$startDay = strtolower($startDay);

		$days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
		$dayIndex = array_flip($days);

		$all = [];
		foreach ($pattern as $weekPattern) {
			foreach ($weekPattern->pattern as $entry) {
				$all[] = $entry->shift_id;
			}
		}

		// Calculate start position
		$startWeek = $startWeek ? $startWeek : 1;
		$startPos = ($startWeek - 1) * 7 + $dayIndex[$startDay];

		// Reorder the array from startPos circularly
		$ordered = array_merge(
			array_slice($all, $startPos),
			array_slice($all, 0, $startPos)
		);

		return $ordered;
	}
}
