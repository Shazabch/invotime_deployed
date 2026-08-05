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

		$data = $_POST["json"];

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
			$response["msg"] =  $response["msg"]  . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
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

		$data = $_POST["json"];



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
		$response["msg"] =  $response["msg"]  . '   <span style="color:red">Skipped: ' . $insert_failed . '<span>';
		//}

		//if($update_success > 0){
		$response["msg"] =  $response["msg"]  . '   <span style="color:green">Updated: ' . $update_success . '<span>';
		//}


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

		$data = $_POST["json"];

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
			$response["msg"] =  $response["msg"]  . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
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

		$data = $_POST["json"];

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
			$response["msg"] =  $response["msg"]  . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
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

		$data = $_POST["json"];

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
			$response["msg"] =  $response["msg"]  . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
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

		$data = $_POST["json"];

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
			$response["msg"] =  $response["msg"]  . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
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

		$data = $_POST["json"];

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
			$response["msg"] =  $response["msg"]  . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
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

		$data = $_POST["json"];

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
			$response["msg"] =  $response["msg"]  . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
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

		$data = $_POST["json"];

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
			$response["msg"] =  $response["msg"]  . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
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

		$data = $_POST["json"];

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
			$response["msg"] =  $response["msg"]  . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
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
			$response["msg"] =  $response["msg"]  . '   <span style="color:red">Errors: ' . $insert_failed . '<span>';
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

			$val["Department"] = trim($val['Department']);
			$val["Position"] = trim($val['Position']);
			$val["Section"] = trim($val['Section']);
			$val["Outlet"] = trim($val['Outlet']);


			$department = $this->db->get_where('departments', array('company_id =' => $cid, 'TRIM(name) =' => $val["Department"]))->row();
			$position = $this->db->get_where('positions', array('company_id =' => $cid, 'TRIM(title) = ' => $val["Position"]))->row();
			$section = $this->db->get_where('sections', array('company_id =' => $cid, 'TRIM(title) = ' => $val["Section"]))->row();
			$branch = $this->db->get_where('branches', array('company_id =' => $cid, 'TRIM(name) = ' => $val["Outlet"]))->row();

			$employee = $this->db->get_where('employees', array('deleted_at =' => NULL, 'company_id =' => $cid, 'id = ' => $val['Device_ID']))->row();

			if (!$employee) {
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val['Device_ID'] . ")";
				$err["error"] = "<b>" . $val['Device_ID'] . " </b>employee not exists";
				$rows_error[] = $err;
			}

			if (!$department) {
				$is_department_inserted = $this->db->insert('departments', array('company_id' => $cid, 'name' => $val["Department"]));

				if ($is_department_inserted) {
					$department = $this->db->get_where('departments', array('company_id =' => $cid, 'name = ' => $val["Department"]))->row();
				} else {
					$required_missing = true;
					$err = array();
					$err["row"] = $key + 1 . " (" . $val['Device_ID'] . ")";
					$err["error"] = "<b>" . $val["Department"] . " </b>department could not be inserted";
					$rows_error[] = $err;
				}
			}

			if (!$position) {
				$is_position_inserted = $this->db->insert('positions', array('company_id' => $cid, 'title' => $val["Position"]));

				if ($is_position_inserted) {
					$position = $this->db->get_where('positions', array('company_id =' => $cid, 'title = ' => $val["Position"]))->row();
				} else {
					$required_missing = true;
					$err = array();
					$err["row"] = $key + 1 . " (" . $val['Device_ID'] . ")";
					$err["error"] = "<b>" . $val["Position"] . " </b>position could not be inserted";
					$rows_error[] = $err;
				}
			}
			
			if (!$branch) {
				$is_branch_inserted = $this->db->insert('branches', array('company_id' => $cid, 'name' => $val["Ourlet"]));

				if ($is_branch_inserted) {
					$branch = $this->db->get_where('branches', array('company_id =' => $cid, 'name = ' => $val["Ourlet"]))->row();
				} else {
					$required_missing = true;
					$err = array();
					$err["row"] = $key + 1 . " (" . $val['Device_ID'] . ")";
					$err["error"] = "<b>" . $val["Ourlet"] . " </b>outlet could not be inserted";
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
						$err["row"] = $key + 1 . " (" . $val['Device_ID'] . ")";
						$err["error"] = "<b>" . $val["Section"] . " </b>section could not be inserted";
						$rows_error[] = $err;
					}
				}
			}
			

			if (!$branch) {
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (" . $val['Device_ID'] . ")";
				$err["error"] = "<b>" . $val["Outlet"] . "</b> outlet not found";
				$rows_error[] = $err;
			} else {

				$permissions_level = get_user()["permissions_level"];

				if ($permissions_level == "Outlet") {
					if ($branch->id != get_user()["branch_id"]) {
						$required_missing = true;
						$err = array();
						$err["row"] = $key + 1 . " (" . $val['Device_ID'] . ")";
						$err["error"] = "not allowed to import to <b>" . $val["Outlet"] . "</b> outlet";
						$rows_error[] = $err;
					}
				}
			}

			if ($required_missing) {
				$update_failed = $update_failed + 1;

				continue;
			}
		}

		if (empty($rows_error)) {

			foreach ($data as $key => $val) {

				$department = $this->db->get_where('departments', array('company_id =' => $cid, 'name = ' => $val["Department"]))->row();
				$position = $this->db->get_where('positions', array('company_id =' => $cid, 'title = ' => $val["Position"]))->row();
				$section = $this->db->get_where('sections', array('company_id =' => $cid, 'title = ' => $val["Section"]))->row();
				$branch = $this->db->get_where('branches', array('company_id =' => $cid, 'name = ' => $val["Outlet"]))->row();

				$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'id = ' => $val['Device_ID']))->row();


				$val['Joining_Date'] = str_replace("/", "-", $val['Joining_Date']);

				$date = DateTime::createFromFormat('d M, Y', $val['Joining_Date']);

				$val['Joining_Date'] = $date ? $date->format('Y-m-d') : null;

				// $val['Joining_Date'] = $val['Joining_Date'] == '' ? NULL : date("Y-m-d", strtotime($val["Joining_Date"]));


				$d = array(
					'first_name' => $val["Name"],
					'special_id' => $val["Employee_ID"],
					'department_id' => $department->id,
					'position_id' => $position->id,
					'section_id' => $section->id ?? "",
					'branch_id' => $branch->id,
					'company_id' => $cid,
					'ic_no' => $val['IC_No'],
					'hired_on' => $val['Joining_Date'],
					'telephone' => $val["Phone"]
				);


				// Update employee
				$this->db->where('id', $employee->id);

				if ($this->db->update('employees', $d)) {
					$update_success = $update_success + 1;
				} else {
					$update_failed = $update_failed + 1;
					$err = array();
					$err["row"] = $key + 1 . " (" . $val['Device_ID'] . ")";
					$err["error"] = $this->db->error()["message"];
					$rows_error[] = $err;
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

		$response["update_success"] = $update_success;
		$response["update_failed"] = $update_failed;
		$response["rows_error"] = json_encode($new);

		$response["msg"] = '';
		if ($update_success > 0) {
			$response["msg"] = ' <span style="color:blue">OK: ' . $update_success . '<span>';
		}
		if ($update_failed > 0) {
			$response["msg"] =  $response["msg"]  . '   <span style="color:red">Errors: ' . $update_failed . '<span>';
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
					// 'religion' => $val["religion"],
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
			$response["msg"] =  $response["msg"]  . '   <span style="color:red">Errors: ' . $insert_failed . '<span>';
		}

		echo json_encode($response);
	}
}
