<?php
class Import extends CI_Controller
{

	function __construct()
	{
		parent::__construct();

		if (is_null(get_user())) {
			redirect("welcome");
			//var_dump($this->session->userdata('antelope_user'));
		}
	}

	public function Index()
	{
		if (!is_page_permitted('import')) {
			redirect_if_not_permitted();
		}
		$data['pageTitle'] = "Import Data";
		$data['active_menu'] = "import";
		$this->load->view('header', $data);
		$data["menus"] = get_menus();
		$this->load->view('sidebar', $data);


		$this->load->view('import', $data);
		$this->load->view('footer', $data);
	}

	public function csv_converter()
	{
		if (!is_page_permitted('csv_converter')) {
			redirect_if_not_permitted();
		}
		$data['pageTitle'] = "CSV Converter";
		$data['active_menu'] = "import/csv_converter";
		$this->load->view('header', $data);
		$data["menus"] = get_menus();
		$this->load->view('sidebar', $data);

		$cid = get_user()["company_id"];
		$bid = get_user()["branch_id"];



		$data["devices"] = $this->db->query("SELECT * FROM devices WHERE company_id = $cid ORDER BY device_id")->result();


		$this->load->view('csv_converter', $data);
		$this->load->view('footer', $data);
	}


	public function import_allowances()
	{
		$cid = get_user()["company_id"];

		$response = array();

		$insert_success = 0;
		$insert_failed = 0;

		$rows_error = array();

		// Read JSON from request body
		$json_input = file_get_contents('php://input');
		$input_data = json_decode($json_input, true);
		$data = isset($input_data['json']) ? $input_data['json'] : (isset($_POST["json"]) ? $_POST["json"] : array());

		$required_missing = false;

		foreach ($data as $key => $val) {
			$required_missing = false;
			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();
			if (!$employee) {
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = "<b>" . $val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if ($required_missing) {
				$insert_failed = $insert_failed + 1;
				continue;
			}

			$d = array(
				'employee_id' => $employee->id,
				'allowance_name' => $val["allowance_name"],
				'amount' => $val["amount"]
			);

			if ($this->db->insert('allowances', $d)) {
				$insert_success = $insert_success + 1;
			} else {
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}
		}

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
			$response["msg"] = ' <span style="color:blue">Imported: ' . $insert_success . '<span>';
		}
		if ($insert_failed > 0) {
			$response["msg"] = $response["msg"] . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
		}


		echo json_encode($response);
	}

	public function update_shifts($device_id)
	{
		update_shifts($device_id);
	}

	public function import_clockings_new()
	{

		//die();
		$cid = get_user()["company_id"];

		//var_dump($cid);

		$response = array();

		$insert_success = 0;
		$insert_failed = 0;
		$update_success = 0;

		$rows_error = array();

		// Read JSON from request body
		$json_input = file_get_contents('php://input');
		$input_data = json_decode($json_input, true);
		$data = isset($input_data['json']) ? $input_data['json'] : (isset($_POST["json"]) ? $_POST["json"] : array());



		$required_missing = false;

		foreach ($data as $key => $val) {
			$required_missing = false;

			//let the following query search with special_id, employee id, and qr code

			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'id = ' => $val["employee_id"]))->row();

			//var_dump($employee);

			$device = $this->db->get_where('devices', array('company_id =' => $cid, 'mac_address = ' => $val["device_serial"]))->row();

			// var_dump($device);
			// die();

			if (!$employee) {
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = "<b>" . $val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}

			if (!$device) {
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = "<b>" . $val["device_serial"] . " </b>device not found";
				$rows_error[] = $err;
			}

			if ($required_missing) {
				$insert_failed = $insert_failed + 1;
				continue;
			}

			$dd = date('Y-m-d', strtotime($val["datetime"]));
			$employee_id = $employee->id;

			$shift_day = $this->db->query("SELECT * FROM shift_days WHERE DATE(date) = '$dd' AND FIND_IN_SET($employee_id,employees)")->row();
			$shift_idd = 0;
			if ($shift_day) {
				$shift_idd = $shift_day->shift_id;
			}


			$d = array(
				'device_id' => $device->device_id,
				'employee_id' => $employee->id,
				'shift_id' => $shift_idd,
				'no' => $val["no"],
				'name' => $val["name"],
				'mode' => $val["mode"],
				'type' => $val["type"],
				'datetime' => date("Y-m-d H:i:s", strtotime($val["datetime"]))
			);


			if ($this->db->insert('clockings_news', $d)) {
				$insert_success = $insert_success + 1;
			} else {
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;

				$update_data = array(
					'device_id' => $device->device_id
				);

				$upd = $this->db->update('clockings_news', $update_data, array(
					'datetime' => date("Y-m-d H:i:s", strtotime($val["datetime"])),
					'employee_id' => $employee->id
				));

				if ($upd) {
					if ($this->db->affected_rows()) {
						$update_success = $update_success + 1;
					}
				}
			}
		}

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
		//if($insert_success > 0){
		$response["msg"] = ' <span style="color:blue">Synced: ' . $insert_success . '<span>';
		//}
		//if($insert_failed > 0){
		$response["msg"] = $response["msg"] . '   <span style="color:red">Skipped: ' . $insert_failed . '<span>';
		//}

		//if($update_success > 0){
		$response["msg"] = $response["msg"] . '   <span style="color:green">Updated: ' . $update_success . '<span>';
		//}


		echo json_encode($response);
	}




	public function import_clockings_v2()
	{
		$cid = get_user()["company_id"];

		$insert_success = 0;
		$insert_failed = 0;
		$update_success = 0;
		$skipped_duplicates = 0;
		$rows_error = array();

		// Mode mapping: biometric type codes ΓåÆ human-readable names (unchanged)
		$mode_map = array(
			'1' => 'Fingerprint',
			'8' => 'Face',
			'16' => 'Palm',
		);

		// ΓöÇΓöÇ Read input (unchanged logic) ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
		$json_input = file_get_contents('php://input');
		$input_data = json_decode($json_input, true);
		$data = isset($input_data['json'])
			? $input_data['json']
			: (isset($_POST['json']) ? $_POST['json'] : array());

		// ΓöÇΓöÇ 1. Validate device (unchanged logic) ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
		$selected_device_mac = isset($input_data['device_mac']) ? trim($input_data['device_mac']) : '';

		if (empty($selected_device_mac)) {
			echo json_encode(array(
				'insert_success' => 0,
				'insert_failed' => count($data),
				'rows_error' => json_encode(array(array('row' => 'All', 'error' => 'No device selected on frontend'))),
				'msg' => '<span style="color:red">Error: No device selected.</span>',
			));
			return;
		}

		$device = $this->db->query(
			"SELECT * FROM devices
         WHERE company_id = ?
           AND (mac_address = ? OR uuid = ? OR device_id = ?)
         LIMIT 1",
			array($cid, $selected_device_mac, $selected_device_mac, $selected_device_mac)
		)->row();

		if (!$device) {
			echo json_encode(array(
				'insert_success' => 0,
				'insert_failed' => count($data),
				'rows_error' => json_encode(array(array('row' => 'All', 'error' => "Device ($selected_device_mac) not found in database"))),
				'msg' => '<span style="color:red">Error: Invalid device.</span>',
			));
			return;
		}

		// ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
		// OPTIMISATION 1 ΓÇö Batch employee lookup
		//   Original: 1 SELECT per row  ΓåÆ  Now: 1 SELECT for ALL rows combined
		// ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
		$raw_emp_ids = array();
		foreach ($data as $val) {
			$eid = isset($val['EnNo']) ? trim($val['EnNo']) : '';
			if ($eid !== '') {
				$raw_emp_ids[$eid] = true;
			}
		}

		// employee_map: CSV EnNo value  ΓåÆ  employee row object
		$employee_map = array();
		if (!empty($raw_emp_ids)) {
			$ids = array_keys($raw_emp_ids);
			$ph = implode(',', array_fill(0, count($ids), '?'));
			// Bind: cid once, then id/special_id/qr_barcode each get the same id list
			$params = array_merge(array($cid), $ids, $ids, $ids);

			$employees = $this->db->query(
				"SELECT * FROM employees
             WHERE company_id = ?
               AND (id IN ($ph) OR special_id IN ($ph) OR qr_barcode IN ($ph))",
				$params
			)->result();

			foreach ($employees as $emp) {
				// Index by every identifier so any CSV value resolves instantly
				$employee_map[(string) $emp->id] = $emp;
				if (!empty($emp->special_id))
					$employee_map[(string) $emp->special_id] = $emp;
				if (!empty($emp->qr_barcode))
					$employee_map[(string) $emp->qr_barcode] = $emp;
			}
		}

		// ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
		// FIRST PASS ΓÇö validate rows, build $pending (logic unchanged)
		// Device lookup per-row is REMOVED ΓÇö $device is already resolved above
		// ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
		$pending = array();   // [emp_db_id][date_key][] = entry array

		foreach ($data as $key => $val) {
			$emp_id = isset($val['EnNo']) ? trim($val['EnNo']) : '';
			$device_serial = isset($val['TMNo']) ? trim($val['TMNo']) : '';
			$required_missing = false;

			if (empty($emp_id)) {
				$required_missing = true;
				$rows_error[] = array('row' => $key + 1, 'error' => 'EnNo (Employee ID) is missing');
			}
			if (empty($device_serial)) {
				$required_missing = true;
				$rows_error[] = array('row' => $key + 1, 'error' => 'TMNo (Device MAC) is missing');
			}
			if ($required_missing) {
				$insert_failed++;
				continue;
			}

			// O(1) map lookup instead of a DB query
			$employee = isset($employee_map[$emp_id]) ? $employee_map[$emp_id] : null;
			if (!$employee) {
				$insert_failed++;
				$rows_error[] = array(
					'row' => ($key + 1) . " ($emp_id)",
					'error' => "<b>$emp_id </b>employee ID not found",
				);
				continue;
			}

			// $device is already validated before the loop ΓÇö no per-row DB hit needed
			// (TMNo is overridden to selected MAC on the frontend anyway)

			$datetime_val = isset($val['DateTime']) ? $val['DateTime'] : (isset($val['datetime']) ? $val['datetime'] : '');
			if (empty($datetime_val)) {
				$insert_failed++;
				$rows_error[] = array('row' => ($key + 1) . " ($emp_id)", 'error' => '<b>DateTime</b> is missing or invalid');
				continue;
			}

			$dt = date('Y-m-d H:i:s', strtotime($datetime_val));
			$date_key = date('Y-m-d', strtotime($datetime_val));

			$pending[$employee->id][$date_key][] = array(
				'device_id' => $device->device_id,
				'datetime' => $dt,
				'no' => isset($val['No']) ? $val['No'] : (isset($val['no']) ? $val['no'] : ''),
				'name' => isset($val['Name']) ? $val['Name'] : (isset($val['name']) ? $val['name'] : ''),
				'orig_mode' => isset($val['Mode']) ? $val['Mode'] : (isset($val['mode']) ? $val['mode'] : ''),
				'type' => isset($val['type']) ? $val['type'] : '',
				'row' => $key + 1,
			);
		}

		if (empty($pending)) {
			// Nothing survived validation ΓÇö build response and return early
			goto build_response;
		}

		// ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
		// OPTIMISATION 2 ΓÇö Batch existing-clockings lookup
		//   Original: 1 SELECT per (emp, date) pair  ΓåÆ  Now: 1 SELECT for ALL pairs
		// ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
		$existing_map = array();   // [emp_id][date_key][datetime] = true
		{
			$conditions = array();
			$params = array();
			foreach ($pending as $emp_id => $dates) {
				foreach ($dates as $date_key => $_) {
					$conditions[] = '(employee_id = ? AND DATE(datetime) = ?)';
					$params[] = $emp_id;
					$params[] = $date_key;
				}
			}
			if (!empty($conditions)) {
				$rows = $this->db->query(
					"SELECT employee_id, datetime FROM clockings_news WHERE " . implode(' OR ', $conditions),
					$params
				)->result();

				foreach ($rows as $r) {
					$d = date('Y-m-d', strtotime($r->datetime));
					$existing_map[$r->employee_id][$d][$r->datetime] = true;
				}
			}
		}

		// ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
		// OPTIMISATION 3 ΓÇö Batch shift_days lookup
		//   Original: 1 SELECT per date  ΓåÆ  Now: 1 SELECT for ALL dates
		// ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
		$shift_map = array();   // [date_key][emp_id] = shift_id
		{
			$all_dates = array();
			foreach ($pending as $_ => $dates) {
				foreach ($dates as $date_key => $__) {
					$all_dates[$date_key] = true;
				}
			}

			if (!empty($all_dates)) {
				$date_list = array_keys($all_dates);
				$ph = implode(',', array_fill(0, count($date_list), '?'));

				$shift_rows = $this->db->query(
					"SELECT shift_id, date, employees FROM shift_days WHERE DATE(date) IN ($ph)",
					$date_list
				)->result();

				foreach ($shift_rows as $sd) {
					$sd_date = date('Y-m-d', strtotime($sd->date));
					foreach (explode(',', $sd->employees) as $sid) {
						$sid = trim($sid);
						if ($sid !== '') {
							$shift_map[$sd_date][$sid] = $sd->shift_id;
						}
					}
				}
			}
		}

		// ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
		// SECOND PASS ΓÇö determine IN/OUT order, collect rows for bulk insert
		//   Logic is IDENTICAL to the original; we just collect instead of inserting
		// ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
		$bulk_rows = array();   // rows ready to INSERT

		foreach ($pending as $emp_id => $dates) {
			foreach ($dates as $date_key => $entries) {
				$existing_datetimes = isset($existing_map[$emp_id][$date_key]) ? $existing_map[$emp_id][$date_key] : array();

				// Deduplicate (same logic as original)
				$new_entries = array();
				foreach ($entries as $entry) {
					if (isset($existing_datetimes[$entry['datetime']])) {
						$skipped_duplicates++;
						continue;
					}
					$new_entries[] = $entry;
				}
				if (empty($new_entries)) {
					continue;
				}

				// Merge existing + new for chronological IN/OUT ordering (unchanged)
				$all_datetimes = array();
				foreach ($existing_datetimes as $edt => $_) {
					$all_datetimes[] = array('datetime' => $edt, 'is_existing' => true);
				}
				foreach ($new_entries as $entry) {
					$all_datetimes[] = array('datetime' => $entry['datetime'], 'is_existing' => false, 'entry' => $entry);
				}
				usort($all_datetimes, function ($a, $b) {
					return strcmp($a['datetime'], $b['datetime']);
				});

				$shift_idd = isset($shift_map[$date_key][$emp_id]) ? $shift_map[$date_key][$emp_id] : 0;

				foreach ($all_datetimes as $i => $item) {
					if ($item['is_existing']) {
						continue;
					}
					$entry = $item['entry'];

					// Alternate IN/OUT by chronological position (unchanged logic)
					$mode = ($i % 2 === 0) ? 'IN' : 'OUT';
					$type_val_db = strtolower($mode);
					$mode_code = trim($entry['orig_mode']);
					$mode_val_db = isset($mode_map[$mode_code]) ? $mode_map[$mode_code] : (!empty($mode_code) ? $mode_code : 'MANUAL');

					$bulk_rows[] = array(
						'device_id' => $entry['device_id'],
						'employee_id' => $emp_id,
						'shift_id' => $shift_idd,
						'no' => $entry['no'],
						'name' => $entry['name'],
						'mode' => $mode_val_db,
						'type' => $type_val_db,
						'datetime' => $entry['datetime'],
						// meta ΓÇö not written to DB, used only for error reporting
						'_row' => $entry['row'],
					);
				}
			}
		}

		// ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
		// OPTIMISATION 4 ΓÇö Chunked bulk INSERT
		//   Original: 1 INSERT per row  ΓåÆ  Now: 1 INSERT per 500 rows
		//   On chunk failure: falls back to row-by-row with original update logic
		// ΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉΓòÉ
		$db_columns = array('device_id', 'employee_id', 'shift_id', 'no', 'name', 'mode', 'type', 'datetime');
		$chunk_size = 500;

		foreach (array_chunk($bulk_rows, $chunk_size) as $chunk) {
			// Build  INSERT INTO clockings_news (col1,...) VALUES (?,?,...),(?,?,...)
			$row_ph = '(' . implode(',', array_fill(0, count($db_columns), '?')) . ')';
			$all_ph = implode(',', array_fill(0, count($chunk), $row_ph));
			$bind = array();
			foreach ($chunk as $row) {
				foreach ($db_columns as $col) {
					$bind[] = $row[$col];
				}
			}

			$sql_bulk = 'INSERT INTO clockings_news (' . implode(',', $db_columns) . ') VALUES ' . $all_ph;

			if ($this->db->query($sql_bulk, $bind)) {
				$insert_success += $this->db->affected_rows();
			} else {
				// Chunk failed ΓÇö fall back row-by-row to isolate the bad rows
				// and preserve the original update-on-duplicate fallback logic
				foreach ($chunk as $row) {
					$d = array_intersect_key($row, array_flip($db_columns));

					if ($this->db->insert('clockings_news', $d)) {
						$insert_success++;
					} else {
						$insert_failed++;
						$rows_error[] = array(
							'row' => $row['_row'] . ' (' . $row['employee_id'] . ')',
							'error' => $this->db->error()['message'],
						);

						// Attempt update on existing record (unchanged fallback logic)
						$upd = $this->db->update(
							'clockings_news',
							array('device_id' => $row['device_id']),
							array('datetime' => $row['datetime'], 'employee_id' => $row['employee_id'])
						);
						if ($upd && $this->db->affected_rows()) {
							$update_success++;
						}
					}
				}
			}
		}

		// ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
		// BUILD RESPONSE  (unchanged logic)
		// ΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇΓöÇ
		build_response:
		$temp = array();
		$new = array();
		foreach ($rows_error as $val) {
			$temp[$val['row']][] = $val['error'];
		}
		foreach ($temp as $key => $value) {
			$values = implode(',', array_unique(explode(',', implode('<hr>', $value))));
			$new[] = array('row' => $key, 'error' => $values);
		}

		$response = array(
			'insert_success' => $insert_success,
			'insert_failed' => $insert_failed,
			'skipped_duplicates' => $skipped_duplicates,
			'rows_error' => json_encode($new),
			'msg' => '<span style="color:blue">Synced: ' . $insert_success . '</span>'
				. ' &nbsp;<span style="color:red">Skipped: ' . $insert_failed . '</span>'
				. ' &nbsp;<span style="color:green">Updated: ' . $update_success . '</span>'
				. ' &nbsp;<span style="color:orange">Duplicates: ' . $skipped_duplicates . '</span>',
		);

		echo json_encode($response);
	}
	public function import_clockings()
	{

		//die();
		$cid = get_user()["company_id"];

		//var_dump($cid);

		$response = array();

		$insert_success = 0;
		$insert_failed = 0;

		$rows_error = array();

		// Read JSON from request body
		$json_input = file_get_contents('php://input');
		$input_data = json_decode($json_input, true);
		$data = isset($input_data['json']) ? $input_data['json'] : (isset($_POST["json"]) ? $_POST["json"] : array());

		$required_missing = false;

		foreach ($data as $key => $val) {
			$required_missing = false;

			//let the following query search with special_id, employee id, and qr code

			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'id = ' => $val["employee_id"]))->row();



			//var_dump($employee);

			$device = $this->db->get_where('devices', array('company_id =' => $cid, 'mac_address = ' => $val["device_mac_address"]))->row();




			// var_dump($device);
			// die();

			if (!$employee) {
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = "<b>" . $val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if (!$device) {
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = "<b>" . $val["device_mac_address"] . " </b>device not found";
				$rows_error[] = $err;
			}

			if ($required_missing) {
				$insert_failed = $insert_failed + 1;
				continue;
			}


			$d = array(
				'employee_id' => $employee->id,
				'device_id' => $device->device_id,
				'clock_in' => date("Y-m-d H:i:s", strtotime($val["clock_in"])),
				'clock_out' => date("Y-m-d H:i:s", strtotime($val["clock_out"]))
			);


			$val_clock_in = NULL;

			$temp_date_in = date("Y-m-d H:i:s", strtotime($val["clock_in"]));
			$temp_date_out = date("Y-m-d H:i:s", strtotime($val["clock_out"]));


			if ($temp_date_in == '1970-01-01 00:00:00') {
				$temp_date_in = NULL;
			}

			if ($temp_date_out == '1970-01-01 00:00:00') {
				$temp_date_out = NULL;
			}


			$in_out_same = $this->db->get_where(
				'clockings',
				array(

					'employee_id =' => $employee->id,
					'clock_in = ' => $temp_date_in,
					'clock_out = ' => $temp_date_out

				)
			)->row();

			//var_dump($in_out_same);
			//die();


			if ($in_out_same) {
				//$required_missing = true;
				continue;
			}

			$in_same = $this->db->get_where(
				'clockings',
				array(

					'employee_id = ' => $employee->id,
					'clock_in = ' => $temp_date_in,
					'clock_out IS NULL ' => null

				)
			)->row();




			//die();

			if ($in_same) {
				//$required_missing = true;
				$this->db->where('id', $in_same->id);
				$this->db->update('clockings', $d);
				$insert_success = $insert_success + 1;
				continue;
			}

			$out_same = $this->db->get_where(
				'clockings',
				array(

					'employee_id = ' => $employee->id,
					'clock_in IS NULL ' => null,
					'clock_out = ' => $temp_date_out

				)
			)->row();







			if ($out_same) {
				//$required_missing = true;
				$this->db->where('id', $out_same->id);
				$this->db->update('clockings', $d);
				$insert_success = $insert_success + 1;
				continue;
			}

			//naveed
			//var_dump($d);
			//die();

			if ($this->db->insert('clockings', $d)) {
				$insert_success = $insert_success + 1;
			} else {
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}
		}

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
		//if($insert_success > 0){
		$response["msg"] = ' <span style="color:blue">Synced: ' . $insert_success . '<span>';
		//}
		if ($insert_failed > 0) {
			$response["msg"] = $response["msg"] . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
		}


		echo json_encode($response);
	}

	public function import_incentives()
	{
		$cid = get_user()["company_id"];

		$response = array();

		$insert_success = 0;
		$insert_failed = 0;

		$rows_error = array();

		// Read JSON from request body
		$json_input = file_get_contents('php://input');
		$input_data = json_decode($json_input, true);
		$data = isset($input_data['json']) ? $input_data['json'] : (isset($_POST["json"]) ? $_POST["json"] : array());

		$required_missing = false;

		foreach ($data as $key => $val) {
			$required_missing = false;
			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();
			if (!$employee) {
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = "<b>" . $val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if ($required_missing) {
				$insert_failed = $insert_failed + 1;
				continue;
			}

			$d = array(
				'employee_id' => $employee->id,
				'incentive_name' => $val["incentive_name"],
				'amount' => $val["amount"]
			);

			if ($this->db->insert('incentives', $d)) {
				$insert_success = $insert_success + 1;
			} else {
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}
		}

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
			$response["msg"] = ' <span style="color:blue">Imported: ' . $insert_success . '<span>';
		}
		if ($insert_failed > 0) {
			$response["msg"] = $response["msg"] . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
		}


		echo json_encode($response);
	}

	public function import_emergency_contacts()
	{
		$cid = get_user()["company_id"];

		$response = array();

		$insert_success = 0;
		$insert_failed = 0;

		$rows_error = array();

		// Read JSON from request body
		$json_input = file_get_contents('php://input');
		$input_data = json_decode($json_input, true);
		$data = isset($input_data['json']) ? $input_data['json'] : (isset($_POST["json"]) ? $_POST["json"] : array());

		$required_missing = false;

		foreach ($data as $key => $val) {
			$required_missing = false;
			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();
			if (!$employee) {
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = "<b>" . $val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if ($required_missing) {
				$insert_failed = $insert_failed + 1;
				continue;
			}

			$d = array(
				'employee_id' => $employee->id,
				'first_name' => $val["first_name"],
				'last_name' => $val["last_name"],
				'relation' => $val["relation"],
				'email' => $val["email"],
				'telephone' => $val["telephone"],
				'office_no' => $val["office_no"],
				'mobile' => $val["mobile"],
				'address' => $val["address"],
				'address_postcode' => $val["address_postcode"],
				'address_city' => $val["address_city"],
				'address_state' => $val["address_state"]
			);

			if ($this->db->insert('emergency_contacts', $d)) {
				$insert_success = $insert_success + 1;
			} else {
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}
		}

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
			$response["msg"] = ' <span style="color:blue">Imported: ' . $insert_success . '<span>';
		}
		if ($insert_failed > 0) {
			$response["msg"] = $response["msg"] . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
		}


		echo json_encode($response);
	}

	public function import_family_members()
	{
		$cid = get_user()["company_id"];

		$response = array();

		$insert_success = 0;
		$insert_failed = 0;

		$rows_error = array();

		// Read JSON from request body
		$json_input = file_get_contents('php://input');
		$input_data = json_decode($json_input, true);
		$data = isset($input_data['json']) ? $input_data['json'] : (isset($_POST["json"]) ? $_POST["json"] : array());

		$required_missing = false;

		foreach ($data as $key => $val) {
			$required_missing = false;
			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();
			if (!$employee) {
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = "<b>" . $val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if ($required_missing) {
				$insert_failed = $insert_failed + 1;
				continue;
			}

			$d = array(
				'employee_id' => $employee->id,
				'first_name' => $val["first_name"],
				'last_name' => $val["last_name"],
				'relation' => $val["relation"],
				'age' => $val["age"],
				'mobile' => $val["mobile"],
				'job' => $val["job"]
			);

			if ($this->db->insert('family_members', $d)) {
				$insert_success = $insert_success + 1;
			} else {
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}
		}

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
			$response["msg"] = ' <span style="color:blue">Imported: ' . $insert_success . '<span>';
		}
		if ($insert_failed > 0) {
			$response["msg"] = $response["msg"] . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
		}


		echo json_encode($response);
	}

	public function import_qualifications()
	{
		$cid = get_user()["company_id"];

		$response = array();

		$insert_success = 0;
		$insert_failed = 0;

		$rows_error = array();

		// Read JSON from request body
		$json_input = file_get_contents('php://input');
		$input_data = json_decode($json_input, true);
		$data = isset($input_data['json']) ? $input_data['json'] : (isset($_POST["json"]) ? $_POST["json"] : array());

		$required_missing = false;

		foreach ($data as $key => $val) {
			$required_missing = false;
			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();
			if (!$employee) {
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = "<b>" . $val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if ($required_missing) {
				$insert_failed = $insert_failed + 1;
				continue;
			}

			$d = array(
				'employee_id' => $employee->id,
				'institution' => $val["institution"],
				'country' => $val["country"],
				'course_field' => $val["course_field"],
				'period_from' => date("Y-m-d", strtotime($val["period_from"])),
				'period_to' => date("Y-m-d", strtotime($val["period_to"])),
				'highest_qualification_attained' => $val["highest_qualification_attained"]
			);

			if ($this->db->insert('qualifications', $d)) {
				$insert_success = $insert_success + 1;
			} else {
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}
		}

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
			$response["msg"] = ' <span style="color:blue">Imported: ' . $insert_success . '<span>';
		}
		if ($insert_failed > 0) {
			$response["msg"] = $response["msg"] . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
		}


		echo json_encode($response);
	}

	public function import_languages()
	{
		$cid = get_user()["company_id"];

		$response = array();

		$insert_success = 0;
		$insert_failed = 0;

		$rows_error = array();

		// Read JSON from request body
		$json_input = file_get_contents('php://input');
		$input_data = json_decode($json_input, true);
		$data = isset($input_data['json']) ? $input_data['json'] : (isset($_POST["json"]) ? $_POST["json"] : array());

		$required_missing = false;

		foreach ($data as $key => $val) {
			$required_missing = false;
			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();
			if (!$employee) {
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = "<b>" . $val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if ($required_missing) {
				$insert_failed = $insert_failed + 1;
				continue;
			}

			$d = array(
				'employee_id' => $employee->id,
				'language' => $val["language"],
				'writing_skill' => $val["writing_skill"],
				'speaking_skill' => $val["speaking_skill"]
			);

			if ($this->db->insert('languages', $d)) {
				$insert_success = $insert_success + 1;
			} else {
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}
		}

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
			$response["msg"] = ' <span style="color:blue">Imported: ' . $insert_success . '<span>';
		}
		if ($insert_failed > 0) {
			$response["msg"] = $response["msg"] . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
		}


		echo json_encode($response);
	}

	public function import_skills()
	{
		$cid = get_user()["company_id"];

		$response = array();

		$insert_success = 0;
		$insert_failed = 0;

		$rows_error = array();

		// Read JSON from request body
		$json_input = file_get_contents('php://input');
		$input_data = json_decode($json_input, true);
		$data = isset($input_data['json']) ? $input_data['json'] : (isset($_POST["json"]) ? $_POST["json"] : array());

		$required_missing = false;

		foreach ($data as $key => $val) {
			$required_missing = false;
			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();
			if (!$employee) {
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = "<b>" . $val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if ($required_missing) {
				$insert_failed = $insert_failed + 1;
				continue;
			}

			$d = array(
				'employee_id' => $employee->id,
				'skill' => $val["skill"],
				'level' => $val["level"],
				'notes' => $val["notes"]
			);

			if ($this->db->insert('skills', $d)) {
				$insert_success = $insert_success + 1;
			} else {
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}
		}

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
			$response["msg"] = ' <span style="color:blue">Imported: ' . $insert_success . '<span>';
		}
		if ($insert_failed > 0) {
			$response["msg"] = $response["msg"] . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
		}


		echo json_encode($response);
	}

	public function import_employment_history()
	{
		$cid = get_user()["company_id"];

		$response = array();

		$insert_success = 0;
		$insert_failed = 0;

		$rows_error = array();

		// Read JSON from request body
		$json_input = file_get_contents('php://input');
		$input_data = json_decode($json_input, true);
		$data = isset($input_data['json']) ? $input_data['json'] : (isset($_POST["json"]) ? $_POST["json"] : array());

		$required_missing = false;

		foreach ($data as $key => $val) {
			$required_missing = false;
			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();
			if (!$employee) {
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = "<b>" . $val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if ($required_missing) {
				$insert_failed = $insert_failed + 1;
				continue;
			}

			$d = array(
				'employee_id' => $employee->id,
				'company_name' => $val["company_name"],
				'industry' => $val["industry"],
				'period_from' => date("Y-m-d", strtotime($val["period_from"])),
				'period_to' => date("Y-m-d", strtotime($val["period_From"])),
				'position' => $val["position"],
				'basic_salary' => $val["basic_salary"],
				'bonus' => $val["bonus"],
				'allowance' => $val["allowance"]
			);

			if ($this->db->insert('employment_history', $d)) {
				$insert_success = $insert_success + 1;
			} else {
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}
		}

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
			$response["msg"] = ' <span style="color:blue">Imported: ' . $insert_success . '<span>';
		}
		if ($insert_failed > 0) {
			$response["msg"] = $response["msg"] . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
		}

		echo json_encode($response);
	}

	public function import_basic_info()
	{

		$cid = get_user()["company_id"];

		$response = array();

		$insert_success = 0;
		$insert_failed = 0;

		$rows_error = array();

		// Read JSON from request body
		$json_input = file_get_contents('php://input');
		$input_data = json_decode($json_input, true);
		$data = isset($input_data['json']) ? $input_data['json'] : (isset($_POST["json"]) ? $_POST["json"] : array());

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
			$val["section"] = trim($val['section']);
			$val["role"] = trim($val['role']);
			$val["outlet"] = trim($val['outlet']);


			$department = $this->db->get_where('departments', array('company_id =' => $cid, 'TRIM(name) =' => $val["department"]))->row();
			$position = $this->db->get_where('positions', array('company_id =' => $cid, 'TRIM(title) = ' => $val["position"]))->row();
			$role = $this->db->get_where('roles', array('company_id =' => $cid, 'TRIM(job_name) = ' => $val["role"]))->row();
			$branch = $this->db->get_where('branches', array('company_id =' => $cid, 'TRIM(name) = ' => $val["outlet"]))->row();
			$section = $this->db->get_where('sections', array('company_id =' => $cid, 'TRIM(title) = ' => $val["Section"]))->row();

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

			if (!$section) {
				if ($val['Section'] != '') {
					$is_section_inserted = $this->db->insert('sections', array('company_id' => $cid, 'title' => $val["Section"]));
					if ($is_section_inserted) {
						$section = $this->db->get_where('sections', array('company_id =' => $cid, 'title = ' => $val["Section"]))->row();
					} else {

						$required_missing = true;
						$err = array();
						$err["row"] = $key + 1 . " (" . $val["Employee_ID"] . ")";
						$err["error"] = "<b>" . $val["Section"] . " </b>section could not be inserted";
						$rows_error[] = $err;
					}
				}
			}

			// if (!empty(trim($val["section"]))) {
			// 	// Trim the section value to avoid whitespace issues
			// 	$section_title = trim($val["section"]);

			// 	// Try to get section
			// 	$section = $this->db->select('*')
			// 		->from('sections')
			// 		->where('company_id', $cid)
			// 		->where('TRIM(title) =', $section_title, false) // use false to prevent escaping
			// 		->get();

			// 	// If not found, insert it
			// 	if (!$section) {
			// 		$is_section_inserted = $this->db->insert('sections', array(
			// 			'company_id' => $cid,
			// 			'title' => $section_title
			// 		));

			// 		if ($is_section_inserted) {
			// 			// Re-fetch inserted section
			// 			$section = $this->db->get_where('sections', array(
			// 				'company_id' => $cid,
			// 				'title' => $section_title
			// 			))->row();
			// 		} else {
			// 			$required_missing = true;
			// 			$rows_error[] = array(
			// 				"row" => ($key + 1) . " (" . $val["employee_id"] . ")",
			// 				"error" => "<b>" . htmlspecialchars($section_title) . "</b> section could not be inserted"
			// 			);
			// 		}
			// 	}
			// }


			$val["ic_passport"] = trim($val["ic_passport"]);
			if (strpos($val["ic_passport"], '-') !== false || strpos($val["ic_passport"], ' ') !== false) {
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

				$permissions_level = get_user()["permissions_level"];

				if ($permissions_level == "Outlet") {
					if ($branch->id != get_user()["branch_id"]) {
						$required_missing = true;
						$err = array();
						$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
						$err["error"] = "not allowed to import to <b>" . $val["outlet"] . "</b> outlet";
						$rows_error[] = $err;
					}
				}
			}

			// if(get_employee_bank_id($banks, $val["bank_name"]) == null){
			// 	$required_missing = true;
			// 	$err = array();
			// 	$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
			// 	$err["error"] = "Bank <b>" . $val["bank_name"] . " </b>not found. Check Bank Names file to get correct name of bank or contact us if bank not found in file";
			// 	$rows_error[] = $err;
			// }


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
				$section = $this->db->get_where('sections', array('company_id =' => $cid, 'title = ' => $val["section"]))->row();
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

				if ($val['is_ot'] == "no") {
					$val['is_ot'] = 0;
				} else {
					$val['is_ot'] = 1;
				}

				$val["employment_type"] = str_replace(" ", "_", strtolower($val["employment_type"]));

				if ($val["employment_type"] != "full_time" && $val["employment_type"] != "part_time") {
					$val["employment_type"] = "";
				}

				$val["level"] = str_replace(" ", "_", strtolower($val["level"]));

				if ($val["level"] != "junior_staff" && $val["level"] != "senior_staff") {
					$val["level"] = "";
				}

				$d = array(
					'first_name' => $val["full_name"],
					'special_id' => $val["employee_id"],
					'sex' => $val["sex"],
					'department_id' => $department->id,
					'position_id' => $position->id,
					'section_id' => $section->id ?? "",
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
			$response["msg"] = ' <span style="color:blue">OK: ' . $insert_success . '<span>';
		}
		if ($insert_failed > 0) {
			$response["msg"] = $response["msg"] . '   <span style="color:red">Errors: ' . $insert_failed . '<span>';
		}

		echo json_encode($response);
	}
	public function import_bulk_update_info()
	{
		$cid = get_user()["company_id"];
		$response = array();
		$update_success = 0;
		$update_failed = 0;
		$rows_error = array();

		$json_input = file_get_contents('php://input');
		$input_data = json_decode($json_input, true);
		$data = isset($input_data['json']) ? $input_data['json'] : (isset($_POST["json"]) ? $_POST["json"] : array());

		$normalize_key = function ($value) {
			$value = trim((string) $value);
			$value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
			$value = strtolower($value);
			$value = preg_replace('/[^a-z0-9]+/', '_', $value);
			return trim($value, '_');
		};

		$row_value = function ($row, array $possible_keys) use ($normalize_key) {
			if (!is_array($row)) {
				return '';
			}

			$normalized_row = array();
			foreach ($row as $key => $value) {
				$normalized_row[$normalize_key($key)] = is_string($value) ? trim($value) : $value;
			}

			foreach ($possible_keys as $key) {
				$normalized_key = $normalize_key($key);
				if (array_key_exists($normalized_key, $normalized_row)) {
					return trim((string) $normalized_row[$normalized_key]);
				}
			}

			return '';
		};

		$date_value = function ($value) {
			$value = trim((string) $value);
			if ($value === '') {
				return null;
			}

			$value = str_replace('/', '-', $value);
			$formats = array('d-m-Y', 'Y-m-d', 'd M, Y', 'd M Y');
			foreach ($formats as $format) {
				$date = DateTime::createFromFormat($format, $value);
				if ($date) {
					$errors = DateTime::getLastErrors();
					if (empty($errors['warning_count']) && empty($errors['error_count'])) {
						return $date->format('Y-m-d');
					}
				}
			}

			$timestamp = strtotime($value);
			return ($timestamp === false) ? null : date('Y-m-d', $timestamp);
		};

		if (!is_array($data) || empty($data)) {
			$response["update_success"] = 0;
			$response["update_failed"] = 0;
			$response["rows_error"] = json_encode(array(array('row' => 'All', 'error' => 'No rows were provided')));
			$response["msg"] = '<span style="color:red">No rows were provided.</span>';
			echo json_encode($response);
			return;
		}

		$department_map = array();
		foreach ($this->db->select('id, name')->from('departments')->where('company_id', $cid)->get()->result() as $department_row) {
			$department_map[$normalize_key($department_row->name)] = $department_row;
		}

		$position_map = array();
		foreach ($this->db->select('id, title')->from('positions')->where('company_id', $cid)->get()->result() as $position_row) {
			$position_map[$normalize_key($position_row->title)] = $position_row;
		}

		$section_map = array();
		foreach ($this->db->select('id, title')->from('sections')->where('company_id', $cid)->get()->result() as $section_row) {
			$section_map[$normalize_key($section_row->title)] = $section_row;
		}

		$branch_map = array();
		foreach ($this->db->select('id, name')->from('branches')->where('company_id', $cid)->get()->result() as $branch_row) {
			$branch_map[$normalize_key($branch_row->name)] = $branch_row;
		}

		$employee_ids = array();
		foreach ($data as $row) {
			$device_id = $row_value($row, array('Device_ID', 'device_id'));
			if ($device_id !== '') {
				$employee_ids[$device_id] = true;
			}
		}

		$employee_map = array();
		if (!empty($employee_ids)) {
			$ids = array_keys($employee_ids);
			foreach ($this->db->where('company_id', $cid)->where_in('id', $ids)->get('employees')->result() as $employee_row) {
				$employee_map[(string) $employee_row->id] = $employee_row;
			}
		}

		$seen_device_ids = array();
		$seen_employee_codes = array();
		$prepared_rows = array();
		$rows_report = array();
		$permissions_level = get_user()["permissions_level"];
		$branch_id = get_user()["branch_id"];

		$total_departments_created = 0;
		$total_positions_created = 0;
		$total_sections_created = 0;

		foreach ($data as $index => $row) {
			if (!is_array($row)) {
				continue;
			}

			$row_number = $index + 1;
			$device_id = $row_value($row, array('Device_ID', 'device_id'));
			$employee_code = $row_value($row, array('Employee_ID', 'employee_id'));
			$name = $row_value($row, array('Name', 'name', 'full_name'));
			$ic_no = $row_value($row, array('IC_No', 'ic_no', 'ic_passport'));
			$phone = $row_value($row, array('Phone', 'phone', 'telephone', 'mobile'));
			$position_name = $row_value($row, array('Position', 'position'));
			$department_name = $row_value($row, array('Department', 'department'));
			$section_name = $row_value($row, array('Section', 'section'));
			$joining_date_raw = $row_value($row, array('Joining_Date', 'joining_date', 'hired_on'));
			$outlet_name = $row_value($row, array('Outlet', 'outlet', 'branch'));
			$row_label = $row_number . ($device_id !== '' ? ' (' . $device_id . ')' : '');
			$row_errors = array();
			$row_messages = array();

			if ($device_id === '') {
				$row_errors[] = 'Device_ID is required';
			} else if (!ctype_digit($device_id)) {
				$row_errors[] = 'Device_ID must be numeric';
			} else {
				if (isset($seen_device_ids[$device_id])) {
					$row_errors[] = 'Device_ID is duplicated in the file';
				} else {
					$seen_device_ids[$device_id] = true;
				}

				if (!isset($employee_map[$device_id])) {
					$row_errors[] = 'Employee not found for Device_ID ' . $device_id;
				}
			}

			if ($employee_code === '') {
				$row_errors[] = 'Employee_ID is required';
			} else if (isset($seen_employee_codes[$employee_code])) {
				$row_errors[] = 'Employee_ID is duplicated in the file';
			} else {
				$seen_employee_codes[$employee_code] = true;
			}

			if ($name === '') {
				$row_errors[] = 'Name is required';
			}

			if ($position_name === '') {
				$row_errors[] = 'Position is required';
			}
			if ($department_name === '') {
				$row_errors[] = 'Department is required';
			}
			if ($outlet_name === '') {
				$row_errors[] = 'Outlet is required';
			}

			$department = $department_name !== '' ? (isset($department_map[$normalize_key($department_name)]) ? $department_map[$normalize_key($department_name)] : null) : null;
			$position = $position_name !== '' ? (isset($position_map[$normalize_key($position_name)]) ? $position_map[$normalize_key($position_name)] : null) : null;
			$section = $section_name !== '' ? (isset($section_map[$normalize_key($section_name)]) ? $section_map[$normalize_key($section_name)] : null) : null;
			$branch = $outlet_name !== '' ? (isset($branch_map[$normalize_key($outlet_name)]) ? $branch_map[$normalize_key($outlet_name)] : null) : null;

			if ($department_name !== '' && !$department) {
				$this->db->insert('departments', array('company_id' => $cid, 'name' => $department_name));
				$department = (object) array('id' => $this->db->insert_id(), 'name' => $department_name);
				$department_map[$normalize_key($department_name)] = $department;
				$total_departments_created++;
				$row_messages[] = 'Department <b>' . $department_name . '</b> created';
			}
			if ($position_name !== '' && !$position) {
				$dep_id = $department ? $department->id : 0;
				$this->db->insert('positions', array('company_id' => $cid, 'department_id' => $dep_id, 'title' => $position_name));
				$position = (object) array('id' => $this->db->insert_id(), 'title' => $position_name);
				$position_map[$normalize_key($position_name)] = $position;
				$total_positions_created++;
				$row_messages[] = 'Position <b>' . $position_name . '</b> created';
			}
			if ($section_name !== '' && !$section) {
				$dep_id = $department ? $department->id : 0;
				$this->db->insert('sections', array('company_id' => $cid, 'department_id' => $dep_id, 'title' => $section_name));
				$section = (object) array('id' => $this->db->insert_id(), 'title' => $section_name);
				$section_map[$normalize_key($section_name)] = $section;
				$total_sections_created++;
				$row_messages[] = 'Section <b>' . $section_name . '</b> created';
			}
			if ($outlet_name !== '' && !$branch) {
				$row_errors[] = 'Outlet <b>' . $outlet_name . '</b> not found';
			}

			if ($branch && $permissions_level == 'Outlet' && $branch->id != $branch_id) {
				$row_errors[] = 'not allowed to import to <b>' . $outlet_name . '</b> outlet';
			}

			$joining_date = $date_value($joining_date_raw);
			if ($joining_date_raw !== '' && $joining_date === null) {
				$row_errors[] = 'Joining_Date <b>' . $joining_date_raw . '</b> is invalid';
			}

			if (!empty($row_errors)) {
				$rows_report[] = array(
					'row' => $row_label,
					'status' => 'skipped',
					'error' => implode('<hr>', $row_errors)
				);
				foreach ($row_errors as $row_error) {
					$rows_error[] = array('row' => $row_label, 'error' => $row_error);
				}
				$update_failed++;
				continue;
			}

			$current_employee = $employee_map[$device_id];
			$duplicate_employee = $this->db->select('id')->where('company_id', $cid)->where('special_id', $employee_code)->where('id !=', $current_employee->id)->get('employees')->row();
			if ($duplicate_employee) {
				$rows_report[] = array(
					'row' => $row_label,
					'status' => 'skipped',
					'error' => 'Employee_ID <b>' . $employee_code . '</b> is already used by another employee'
				);
				$rows_error[] = array('row' => $row_label, 'error' => 'Employee_ID <b>' . $employee_code . '</b> is already used by another employee');
				$update_failed++;
				continue;
			}

			$prepared_rows[] = array(
				'row' => $row_label,
				'employee_id' => $current_employee->id,
				'first_name' => $name,
				'special_id' => $employee_code,
				'department_id' => $department->id,
				'position_id' => $position->id,
				'section_id' => $section ? $section->id : null,
				'branch_id' => $branch->id,
				'ic_no' => $ic_no,
				'telephone' => $phone,
				'hired_on' => $joining_date
			);

			$rows_report[] = array(
				'row' => $row_label,
				'status' => 'saved',
				'error' => !empty($row_messages) ? implode('<hr>', $row_messages) : ''
			);
		}

		if (empty($prepared_rows) && !empty($rows_error)) {
			$temp = $new = array();
			foreach ($rows_error as $val) {
				$temp[$val['row']][] = $val['error'];
			}

			foreach ($temp as $key => $value) {
				$values = implode(',', array_unique(explode(',', implode('<hr>', $value))));
				$new[] = array('row' => $key, 'error' => $values);
			}

			$response["update_success"] = 0;
			$response["update_failed"] = $update_failed;
			$response["rows_error"] = json_encode($new);
			$response["rows_report"] = json_encode($rows_report);

			$response["msg"] = '<span style="color:red">Validation failed: no valid rows were available to save.</span>';
			$created_msgs = array();
			if ($total_departments_created > 0)
				$created_msgs[] = $total_departments_created . ' Departments';
			if ($total_positions_created > 0)
				$created_msgs[] = $total_positions_created . ' Positions';
			if ($total_sections_created > 0)
				$created_msgs[] = $total_sections_created . ' Sections';
			if (!empty($created_msgs)) {
				$response["msg"] .= '   <span style="color:green">Created: ' . implode(', ', $created_msgs) . '</span>';
			}

			echo json_encode($response);
			return;
		}

		$this->db->trans_begin();

		foreach ($prepared_rows as $prepared_row) {
			$update_data = array(
				'first_name' => $prepared_row['first_name'],
				'special_id' => $prepared_row['special_id'],
				'department_id' => $prepared_row['department_id'],
				'position_id' => $prepared_row['position_id'],
				'section_id' => $prepared_row['section_id'],
				'branch_id' => $prepared_row['branch_id'],
				'ic_no' => $prepared_row['ic_no'] === '' ? null : $prepared_row['ic_no'],
				'hired_on' => $prepared_row['hired_on'],
				'telephone' => $prepared_row['telephone'] === '' ? null : $prepared_row['telephone']
			);

			$this->db->where('id', $prepared_row['employee_id']);
			if ($this->db->update('employees', $update_data)) {
				$update_success++;
			} else {
				$update_failed++;
				$rows_error[] = array('row' => $prepared_row['row'], 'error' => $this->db->error()["message"]);
				$this->db->trans_rollback();
				break;
			}
		}

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			if (empty($rows_error)) {
				$rows_error[] = array('row' => 'All', 'error' => 'Database transaction failed');
			}
			$update_success = 0;
		} else {
			$this->db->trans_commit();
		}

		$temp = $new = array();
		foreach ($rows_error as $val) {
			$temp[$val['row']][] = $val['error'];
		}

		foreach ($temp as $key => $value) {
			$values = implode(',', array_unique(explode(',', implode('<hr>', $value))));
			$new[] = array('row' => $key, 'error' => $values);
		}

		$response["update_success"] = $update_success;
		$response["update_failed"] = count($new) > 0 ? count($new) : $update_failed;
		$response["rows_error"] = json_encode($new);
		$response["rows_report"] = json_encode($rows_report);
		$response["msg"] = '';
		if ($update_success > 0) {
			$response["msg"] = ' <span style="color:blue">Saved: ' . $update_success . '</span>';
		}
		if (!empty($new)) {
			$response["msg"] = $response["msg"] . '   <span style="color:red">Skipped: ' . count($new) . '</span>';
		}
		if ($update_success > 0 && !empty($new)) {
			$response["msg"] = $response["msg"] . '   <span style="color:green">Partial import completed</span>';
		}
		if ($update_success == 0 && !empty($new)) {
			$response["msg"] = '<span style="color:red">No rows were saved.</span>';
		}

		$created_msgs = array();
		if ($total_departments_created > 0)
			$created_msgs[] = $total_departments_created . ' Departments';
		if ($total_positions_created > 0)
			$created_msgs[] = $total_positions_created . ' Positions';
		if ($total_sections_created > 0)
			$created_msgs[] = $total_sections_created . ' Sections';
		if (!empty($created_msgs)) {
			$response["msg"] .= '   <span style="color:green">Created: ' . implode(', ', $created_msgs) . '</span>';
		}

		echo json_encode($response);
	}
	public function import_basic_info_new()
	{

		$cid = get_user()["company_id"];

		$response = array();

		$insert_success = 0;
		$insert_failed = 0;

		$rows_error = array();

		// Read JSON from request body
		$json_input = file_get_contents('php://input');
		$input_data = json_decode($json_input, true);
		$data = isset($input_data['json']) ? $input_data['json'] : (isset($_POST["json"]) ? $_POST["json"] : array());

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
			$val["section"] = trim($val['section']);
			$val["role"] = trim($val['role']);
			$val["outlet"] = trim($val['outlet']);


			$department = $this->db->get_where('departments', array('company_id =' => $cid, 'TRIM(name) =' => $val["department"]))->row();
			$position = $this->db->get_where('positions', array('company_id =' => $cid, 'TRIM(title) = ' => $val["position"]))->row();
			$role = $this->db->get_where('roles', array('company_id =' => $cid, 'TRIM(job_name) = ' => $val["role"]))->row();
			$branch = $this->db->get_where('branches', array('company_id =' => $cid, 'TRIM(name) = ' => $val["outlet"]))->row();

			// validation loop
			$section = $this->db->get_where('sections', array('company_id =' => $cid, 'TRIM(title) =' => $val["section"]))->row();

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

			if (!$section) {
				if ($val['section'] != '') {
					$is_section_inserted = $this->db->insert('sections', array('company_id' => $cid, 'title' => $val["section"]));
					if ($is_section_inserted) {
						$section = $this->db->get_where('sections', array('company_id =' => $cid, 'title = ' => $val["section"]))->row();
					} else {
						$required_missing = true;
						$err = array();
						$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
						$err["error"] = "<b>" . $val["section"] . " </b>section could not be inserted";
						$rows_error[] = $err;
					}
				}
			}

			// if (!empty(trim($val["section"]))) {
			// 	// Trim the section value to avoid whitespace issues
			// 	$section_title = trim($val["section"]);

			// 	// Try to get section
			// 	$section = $this->db->select('*')
			// 		->from('sections')
			// 		->where('company_id', $cid)
			// 		->where('TRIM(title) =', $section_title, false) // use false to prevent escaping
			// 		->get();

			// 	// If not found, insert it
			// 	if (!$section) {
			// 		$is_section_inserted = $this->db->insert('sections', array(
			// 			'company_id' => $cid,
			// 			'title' => $section_title
			// 		));

			// 		if ($is_section_inserted) {
			// 			// Re-fetch inserted section
			// 			$section = $this->db->get_where('sections', array(
			// 				'company_id' => $cid,
			// 				'title' => $section_title
			// 			))->row();
			// 		} else {
			// 			$required_missing = true;
			// 			$rows_error[] = array(
			// 				"row" => ($key + 1) . " (" . $val["employee_id"] . ")",
			// 				"error" => "<b>" . htmlspecialchars($section_title) . "</b> section could not be inserted"
			// 			);
			// 		}
			// 	}
			// }

			// $val["ic_passport"] = trim($val["ic_passport"]);
			// if(strpos($val["ic_passport"], '-') !== false || strpos($val["ic_passport"], ' ') !== false){
			// 	$required_missing = true;
			// 	$err = array();
			// 	$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
			// 	$err["error"] = "<b>ic_passport</b> should not contain spaces and dashes";
			// 	$rows_error[] = $err;
			// }

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

				$permissions_level = get_user()["permissions_level"];

				if ($permissions_level == "Outlet") {
					if ($branch->id != get_user()["branch_id"]) {
						$required_missing = true;
						$err = array();
						$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
						$err["error"] = "not allowed to import to <b>" . $val["outlet"] . "</b> outlet";
						$rows_error[] = $err;
					}
				}
			}

			// if(get_employee_bank_id($banks, $val["bank_name"]) == null){
			// 	$required_missing = true;
			// 	$err = array();
			// 	$err["row"] = $key + 1 . " (" . $val["employee_id"] . ")";
			// 	$err["error"] = "Bank <b>" . $val["bank_name"] . " </b>not found. Check Bank Names file to get correct name of bank or contact us if bank not found in file";
			// 	$rows_error[] = $err;
			// }


			if ($required_missing) {
				$insert_failed = $insert_failed + 1;

				//echo $required_missing;
				continue;
			}
		}

		if (empty($rows_error)) {

			foreach ($data as $key => $val) {

				$employee_bank_id = get_employee_bank_id($banks, $val["bank_name"]);

				$val["department"] = trim($val['department']);
				$val["position"] = trim($val['position']);
				$val["section"] = trim($val['section']);
				$val["role"] = trim($val['role']);
				$val["outlet"] = trim($val['outlet']);

				$department = $this->db->get_where('departments', array('company_id =' => $cid, 'name = ' => $val["department"]))->row();
				$position = $this->db->get_where('positions', array('company_id =' => $cid, 'title = ' => $val["position"]))->row();
				$section = $this->db->get_where('sections', array('company_id =' => $cid, 'title = ' => $val["section"]))->row();
				$role = $this->db->get_where('roles', array('company_id =' => $cid, 'job_name = ' => $val["role"]))->row();
				$branch = $this->db->get_where('branches', array('company_id =' => $cid, 'name = ' => $val["outlet"]))->row();

				$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();

				//var_dump($val);

				// $val['dob'] = str_replace("/", "-", $val['dob']);
				// $val['hired_on'] = str_replace("/", "-", $val['hired_on']);
				// $val['license_expiry'] = str_replace("/", "-", $val['license_expiry']);

				// set it null if it is empty.
				// $val['hired_on'] = $val['hired_on'] == '' ? NULL : date("Y-m-d", strtotime($val["hired_on"]));
				// $val['dob'] = $val['dob'] == '' ? NULL : date("Y-m-d", strtotime($val["dob"]));
				// $val['license_expiry'] = $val['license_expiry'] == '' ? NULL : date("Y-m-d", strtotime($val["license_expiry"]));

				// $val['is_ot'] = strtolower($val['is_ot']);

				// if($val['is_ot'] == "no"){
				// 	$val['is_ot'] = 0;
				// }else{
				// 	$val['is_ot'] = 1;
				// }

				// $val["employment_type"] = str_replace(" ", "_", strtolower($val["employment_type"]));

				// if($val["employment_type"] != "full_time" && $val["employment_type"] != "part_time"){
				// 	$val["employment_type"] = "";
				// }

				// $val["level"] = str_replace(" ", "_", strtolower($val["level"]));

				// if($val["level"] != "junior_staff" && $val["level"] != "senior_staff"){
				// 	$val["level"] = "";
				// }

				$d = array(
					'first_name' => $val["full_name"],
					'special_id' => $val["employee_id"],
					'sex' => $val["sex"],
					'department_id' => $department->id,
					'position_id' => $position->id,
					'section_id' => $section->id ?? "",
					'role_id' => $role->id,
					'branch_id' => $branch->id,
					'company_id' => $cid,
					// 'grade' => $val["job_grade"],
					'employment_type' => $val["employment_type"],
					// 'hired_on' => $val['hired_on'],
					// 'dob' => $val['dob'],
					// 'pob' => $val["pob"],
					// 'ic_passport' => $val["ic_passport"],
					'race' => $val["race"],
					'religion' => $val["religion"],
					'nationality' => $val["nationality"],
					// 'perm_address' => $val["perm_address"],
					// 'perm_address_postcode' => $val["perm_address_postcode"],
					// 'perm_address_city' => $val["perm_address_city"],
					// 'perm_address_state' => $val["perm_address_state"],
					// 'temp_address' => $val["temp_address"],
					// 'temp_address_postcode' => $val["temp_address_postcode"],
					// 'temp_address_city' => $val["temp_address_city"],
					// 'temp_address_state' => $val["temp_address_state"],
					// 'telephone' => $val["telephone"],
					// 'mobile' => $val["mobile"],
					// 'email' => $val["email"],
					// 'marital_status' => $val["marital_status"],
					// 'basic_wage' => $val["basic_wage"],
					// 'epf_no' => $val["epf_no"],
					// 'socso' => $val["socso"],
					// 'eis' => $val["eis"],
					// 'income_tax_no' => $val["income_tax_no"],
					// 'income_tax_branch' => $val["income_tax_branch"],
					// 'is_ot' => $val["is_ot"],
					// 'qr_barcode' => $val["qr_barcode"],
					// 'bank_account_no' => $val["bank_account_no"],
					// 'employee_bank_id' => $employee_bank_id,
					// 'license_class' => $val["license_class"],
					// 'license_no' => $val["license_no"],
					// 'license_expiry' => $val['license_expiry'],
					// 'level' => $val['level']
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
			$response["msg"] = ' <span style="color:blue">OK: ' . $insert_success . '<span>';
		}
		if ($insert_failed > 0) {
			$response["msg"] = $response["msg"] . '   <span style="color:red">Errors: ' . $insert_failed . '<span>';
		}

		echo json_encode($response);
	}
}
