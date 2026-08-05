<?php
class Import extends CI_Controller {

	function __construct()
    {
      parent::__construct();

			if(is_null(get_user())){
				redirect("welcome");
			 //var_dump($this->session->userdata('antelope_user'));
			}
    }

    public function Index()
	{

		$data['pageTitle'] = "Import Data";
        $data['active_menu'] = "import";
        $this->load->view('header',$data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar',$data);


        $this->load->view('import',$data);
        $this->load->view('footer',$data);


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

		foreach ($data as $key=>$val) {
			$required_missing = false;
			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();
			if(!$employee){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = "<b>".$val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if($required_missing ){
				$insert_failed = $insert_failed + 1;
				continue;
			}

			$d = array(
			        'employee_id' => $employee->id,
			        'allowance_name' => $val["allowance_name"],
			        'amount' => $val["amount"]
			    );

			if($this->db->insert('allowances', $d)){
				$insert_success = $insert_success + 1;
			}
			else{
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}

		}

		$temp = $new = array();
		foreach($rows_error as $val) {
		    $temp[$val['row']][] = $val['error'];
		}

		foreach($temp as $key => $value) {
		    $values = implode(',', array_unique(explode(',', implode('<hr>', $value)))); 
		    $new[] = array('row' => $key, 'error' => $values); // store 
		}

		$response["insert_success"] = $insert_success;
		$response["insert_failed"] = $insert_failed;
		$response["rows_error"] = json_encode($new);
		$response["msg"] = '';
		if($insert_success > 0){
			$response["msg"] = ' <span style="color:blue">Imported: ' . $insert_success . '<span>';
		}
		if($insert_failed > 0){
			$response["msg"] =  $response["msg"]  . '   <span style="color:red">Failed: ' . $insert_failed . '<span>';
		}


		echo json_encode($response);

	}

	public function import_clockings_new()
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

		foreach ($data as $key=>$val) {
			$required_missing = false;

			//let the following query search with special_id, employee id, and qr code

			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'id = ' => $val["employee_id"]))->row();

			//var_dump($employee);

			$device = $this->db->get_where('devices', array('company_id =' => $cid, 'mac_address = ' => $val["device_serial"]))->row();

			// var_dump($device);
			// die();

			if(!$employee){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = "<b>".$val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			
			if(!$device){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = "<b>".$val["device_serial"] . " </b>device not found";
				$rows_error[] = $err;
			}

			if($required_missing ){
				$insert_failed = $insert_failed + 1;
				continue;
			}


			$d = array(
					'device_id' => $device->device_id,
			        'employee_id' => $employee->id,
			        'no' => $val["no"],
			        'name' => $val["name"],
			        'mode' => $val["mode"],
			        'type' => $val["type"],		        
			        'datetime' => date("Y-m-d H:i:s", strtotime($val["datetime"]))
			);


			if($this->db->insert('clockings_news', $d)){
				$insert_success = $insert_success + 1;
			}
			else{
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}

		}

		$temp = $new = array();
		foreach($rows_error as $val) {
		    $temp[$val['row']][] = $val['error'];
		}

		foreach($temp as $key => $value) {
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
		if($insert_failed > 0){
			$response["msg"] =  $response["msg"]  . '   <span style="color:red">Skipped: ' . $insert_failed . '<span>';
		}


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

		foreach ($data as $key=>$val) {
			$required_missing = false;

			//let the following query search with special_id, employee id, and qr code

			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'id = ' => $val["employee_id"]))->row();



			//var_dump($employee);

			$device = $this->db->get_where('devices', array('company_id =' => $cid, 'mac_address = ' => $val["device_mac_address"]))->row();




			// var_dump($device);
			// die();

			if(!$employee){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = "<b>".$val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if(!$device){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = "<b>".$val["device_mac_address"] . " </b>device not found";
				$rows_error[] = $err;
			}

			if($required_missing ){
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

			
			if($temp_date_in == '1970-01-01 00:00:00'){
				$temp_date_in = NULL;
			}

			if($temp_date_out == '1970-01-01 00:00:00'){
				$temp_date_out = NULL;
			}
			

			$in_out_same = $this->db->get_where('clockings', 
				array(

					'employee_id =' => $employee->id, 
					'clock_in = ' => $temp_date_in, 
					'clock_out = ' => $temp_date_out

			))->row();

			//var_dump($in_out_same);
			//die();

			
			if($in_out_same){
				//$required_missing = true;
				continue;
			}

			$in_same = $this->db->get_where('clockings', 
				array(

					'employee_id = ' => $employee->id, 
					'clock_in = ' => $temp_date_in,
					'clock_out IS NULL ' => null

			))->row();

			

			
			//die();

			if($in_same){
				//$required_missing = true;
				$this->db->where('id', $in_same->id);
				$this->db->update('clockings', $d);
				$insert_success = $insert_success + 1;
				continue;
			}

			$out_same = $this->db->get_where('clockings', 
				array(

					'employee_id = ' => $employee->id, 
					'clock_in IS NULL ' => null,
					'clock_out = ' => $temp_date_out

			))->row();


			
			

			

			if($out_same){
				//$required_missing = true;
				$this->db->where('id', $out_same->id);
				$this->db->update('clockings', $d);
				$insert_success = $insert_success + 1;
				continue;
			}

			//naveed
			//var_dump($d);
			//die();

			if($this->db->insert('clockings', $d)){
				$insert_success = $insert_success + 1;
			}
			else{
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}


		}

		$temp = $new = array();
		foreach($rows_error as $val) {
		    $temp[$val['row']][] = $val['error'];
		}

		foreach($temp as $key => $value) {
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
		if($insert_failed > 0){
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

		foreach ($data as $key=>$val) {
			$required_missing = false;
			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();
			if(!$employee){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = "<b>".$val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if($required_missing ){
				$insert_failed = $insert_failed + 1;
				continue;
			}

			$d = array(
			        'employee_id' => $employee->id,
			        'incentive_name' => $val["incentive_name"],
			        'amount' => $val["amount"]
			    );

			if($this->db->insert('incentives', $d)){
				$insert_success = $insert_success + 1;
			}
			else{
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}

		}

		$temp = $new = array();
		foreach($rows_error as $val) {
		    $temp[$val['row']][] = $val['error'];
		}

		foreach($temp as $key => $value) {
		    $values = implode(',', array_unique(explode(',', implode('<hr>', $value)))); 
		    $new[] = array('row' => $key, 'error' => $values); // store 
		}

		$response["insert_success"] = $insert_success;
		$response["insert_failed"] = $insert_failed;
		$response["rows_error"] = json_encode($new);
		$response["msg"] = '';
		if($insert_success > 0){
			$response["msg"] = ' <span style="color:blue">Imported: ' . $insert_success . '<span>';
		}
		if($insert_failed > 0){
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

		foreach ($data as $key=>$val) {
			$required_missing = false;
			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();
			if(!$employee){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = "<b>".$val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if($required_missing ){
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

			if($this->db->insert('emergency_contacts', $d)){
				$insert_success = $insert_success + 1;
			}
			else{
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}

		}

		$temp = $new = array();
		foreach($rows_error as $val) {
		    $temp[$val['row']][] = $val['error'];
		}

		foreach($temp as $key => $value) {
		    $values = implode(',', array_unique(explode(',', implode('<hr>', $value)))); 
		    $new[] = array('row' => $key, 'error' => $values); // store 
		}

		$response["insert_success"] = $insert_success;
		$response["insert_failed"] = $insert_failed;
		$response["rows_error"] = json_encode($new);
		$response["msg"] = '';
		if($insert_success > 0){
			$response["msg"] = ' <span style="color:blue">Imported: ' . $insert_success . '<span>';
		}
		if($insert_failed > 0){
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

		foreach ($data as $key=>$val) {
			$required_missing = false;
			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();
			if(!$employee){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = "<b>".$val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if($required_missing ){
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

			if($this->db->insert('family_members', $d)){
				$insert_success = $insert_success + 1;
			}
			else{
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}

		}

		$temp = $new = array();
		foreach($rows_error as $val) {
		    $temp[$val['row']][] = $val['error'];
		}

		foreach($temp as $key => $value) {
		    $values = implode(',', array_unique(explode(',', implode('<hr>', $value)))); 
		    $new[] = array('row' => $key, 'error' => $values); // store 
		}

		$response["insert_success"] = $insert_success;
		$response["insert_failed"] = $insert_failed;
		$response["rows_error"] = json_encode($new);
		$response["msg"] = '';
		if($insert_success > 0){
			$response["msg"] = ' <span style="color:blue">Imported: ' . $insert_success . '<span>';
		}
		if($insert_failed > 0){
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

		foreach ($data as $key=>$val) {
			$required_missing = false;
			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();
			if(!$employee){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = "<b>".$val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if($required_missing ){
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

			if($this->db->insert('qualifications', $d)){
				$insert_success = $insert_success + 1;
			}
			else{
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}

		}

		$temp = $new = array();
		foreach($rows_error as $val) {
		    $temp[$val['row']][] = $val['error'];
		}

		foreach($temp as $key => $value) {
		    $values = implode(',', array_unique(explode(',', implode('<hr>', $value)))); 
		    $new[] = array('row' => $key, 'error' => $values); // store 
		}

		$response["insert_success"] = $insert_success;
		$response["insert_failed"] = $insert_failed;
		$response["rows_error"] = json_encode($new);
		$response["msg"] = '';
		if($insert_success > 0){
			$response["msg"] = ' <span style="color:blue">Imported: ' . $insert_success . '<span>';
		}
		if($insert_failed > 0){
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

		foreach ($data as $key=>$val) {
			$required_missing = false;
			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();
			if(!$employee){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = "<b>".$val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if($required_missing ){
				$insert_failed = $insert_failed + 1;
				continue;
			}

			$d = array(
			        'employee_id' => $employee->id,
			        'language' => $val["language"],
			        'writing_skill' => $val["writing_skill"],
			        'speaking_skill' => $val["speaking_skill"]
			    );

			if($this->db->insert('languages', $d)){
				$insert_success = $insert_success + 1;
			}
			else{
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}

		}

		$temp = $new = array();
		foreach($rows_error as $val) {
		    $temp[$val['row']][] = $val['error'];
		}

		foreach($temp as $key => $value) {
		    $values = implode(',', array_unique(explode(',', implode('<hr>', $value)))); 
		    $new[] = array('row' => $key, 'error' => $values); // store 
		}

		$response["insert_success"] = $insert_success;
		$response["insert_failed"] = $insert_failed;
		$response["rows_error"] = json_encode($new);
		$response["msg"] = '';
		if($insert_success > 0){
			$response["msg"] = ' <span style="color:blue">Imported: ' . $insert_success . '<span>';
		}
		if($insert_failed > 0){
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

		foreach ($data as $key=>$val) {
			$required_missing = false;
			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();
			if(!$employee){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = "<b>".$val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if($required_missing ){
				$insert_failed = $insert_failed + 1;
				continue;
			}

			$d = array(
			        'employee_id' => $employee->id,
			        'skill' => $val["skill"],
			        'level' => $val["level"],
			        'notes' => $val["notes"]
			    );

			if($this->db->insert('skills', $d)){
				$insert_success = $insert_success + 1;
			}
			else{
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}

		}

		$temp = $new = array();
		foreach($rows_error as $val) {
		    $temp[$val['row']][] = $val['error'];
		}

		foreach($temp as $key => $value) {
		    $values = implode(',', array_unique(explode(',', implode('<hr>', $value)))); 
		    $new[] = array('row' => $key, 'error' => $values); // store 
		}

		$response["insert_success"] = $insert_success;
		$response["insert_failed"] = $insert_failed;
		$response["rows_error"] = json_encode($new);
		$response["msg"] = '';
		if($insert_success > 0){
			$response["msg"] = ' <span style="color:blue">Imported: ' . $insert_success . '<span>';
		}
		if($insert_failed > 0){
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

		foreach ($data as $key=>$val) {
			$required_missing = false;
			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();
			if(!$employee){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = "<b>".$val["employee_id"] . " </b>employee ID not found";
				$rows_error[] = $err;
			}
			if($required_missing ){
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

			if($this->db->insert('employment_history', $d)){
				$insert_success = $insert_success + 1;
			}
			else{
				$insert_failed = $insert_failed + 1;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = $this->db->error()["message"];
				$rows_error[] = $err;
			}

		}

		$temp = $new = array();
		foreach($rows_error as $val) {
		    $temp[$val['row']][] = $val['error'];
		}

		foreach($temp as $key => $value) {
		    $values = implode(',', array_unique(explode(',', implode('<hr>', $value)))); 
		    $new[] = array('row' => $key, 'error' => $values); // store 
		}

		$response["insert_success"] = $insert_success;
		$response["insert_failed"] = $insert_failed;
		$response["rows_error"] = json_encode($new);

		$response["msg"] = '';
		if($insert_success > 0){
			$response["msg"] = ' <span style="color:blue">Imported: ' . $insert_success . '<span>';
		}
		if($insert_failed > 0){
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


		foreach ($data as $key=>$val) {
		   // echo $emp["first_name"] . " ";
			$required_missing = false;


			$department = $this->db->get_where('departments', array('company_id =' => $cid, 'name = ' => $val["department"]))->row();
			$position = $this->db->get_where('positions', array('company_id =' => $cid, 'title = ' => $val["position"]))->row();
			$role = $this->db->get_where('roles', array('company_id =' => $cid, 'job_name = ' => $val["role"]))->row();
			$branch = $this->db->get_where('branches', array('company_id =' => $cid, 'name = ' => $val["outlet"]))->row();

			$employee = $this->db->get_where('employees', array('deleted_at =' => NULL,'company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();

			//echo $this->db->last_query();


			// var_dump($department);
			// var_dump($position);
			// var_dump($role);
			// var_dump($branch);

			//die();

			if($employee){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = "<b>".$val["employee_id"] . " </b>employee already exists";
				$rows_error[] = $err;
			}

			if(!$department){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = "<b>".$val["department"] . " </b>department not found";
				$rows_error[] = $err;
			}

			if(!$position){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = "<b>".$val["position"] . " </b>position not found";
				$rows_error[] = $err;
			}

			if(!$role){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = "<b>".$val["role"] . " </b>role not found";
				$rows_error[] = $err;
			}

			if(!$branch){
				$required_missing = true;
				$err = array();
				$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
				$err["error"] = "<b>".$val["outlet"] . "</b> outlet not found";
				$rows_error[] = $err;
			}
			else{

				$permissions_level = get_user()["permissions_level"];

				if($permissions_level == "Outlet"){
					if($branch->id != get_user()["branch_id"]){
						$required_missing = true;
						$err = array();
						$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
						$err["error"] = "not allowed to import to <b>".$val["outlet"] . "</b> outlet";
						$rows_error[] = $err;
					}

					
				}
			}


			if($required_missing ){
				$insert_failed = $insert_failed + 1;

				//echo $required_missing;
				continue;
			}

		}

	if(empty($rows_error)){

		foreach ($data as $key=>$val) {

			$department = $this->db->get_where('departments', array('company_id =' => $cid, 'name = ' => $val["department"]))->row();
			$position = $this->db->get_where('positions', array('company_id =' => $cid, 'title = ' => $val["position"]))->row();
			$role = $this->db->get_where('roles', array('company_id =' => $cid, 'job_name = ' => $val["role"]))->row();
			$branch = $this->db->get_where('branches', array('company_id =' => $cid, 'name = ' => $val["outlet"]))->row();

			$employee = $this->db->get_where('employees', array('company_id =' => $cid, 'special_id = ' => $val["employee_id"]))->row();

			//var_dump($val);

				$d = array(
			        'first_name' => $val["first_name"],
			        'last_name' => $val["last_name"],
			        'special_id' => $val["employee_id"],
			        'sex' => $val["sex"],
			        'department_id' => $department->id,
			        'position_id' => $position->id,
			        'role_id' => $role->id,
			        'branch_id' => $branch->id,	        
			        'company_id' => $cid,
			        'grade' => $val["job_grade"],
			        'employment_type' => $val["employment_type"],
			        'hired_on' => date("Y-m-d", strtotime($val["hired_on"])),
			        'dob' => date("Y-m-d", strtotime($val["dob"])),
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
			        'bank_name' => $val["bank_name"],
			        'license_class' => $val["license_class"],
			        'license_no' => $val["license_no"],
			        'license_expiry' => $val["license_expiry"]
			);

				if($this->db->insert('employees', $d)){
					$insert_success = $insert_success + 1;
				}
				else{
					$insert_failed = $insert_failed + 1;
					$err = array();
					$err["row"] = $key + 1 . " (". $val["employee_id"] .")";
					$err["error"] = $this->db->error()["message"];
					$rows_error[] = $err;
				}

		}
	}

		// var_dump($insert_failed);
		// var_dump($rows_error);


		$temp = $new = array();
		foreach($rows_error as $val) {
		    $temp[$val['row']][] = $val['error'];
		}

		foreach($temp as $key => $value) {
		    $values = implode(',', array_unique(explode(',', implode('<hr>', $value)))); 
		    $new[] = array('row' => $key, 'error' => $values); // store 
		}

		$response["insert_success"] = $insert_success;
		$response["insert_failed"] = $insert_failed;
		$response["rows_error"] = json_encode($new);

		$response["msg"] = '';
		if($insert_success > 0){
			$response["msg"] = ' <span style="color:blue">OK: ' . $insert_success . '<span>';
		}
		if($insert_failed > 0){
			$response["msg"] =  $response["msg"]  . '   <span style="color:red">Errors: ' . $insert_failed . '<span>';
		}

		echo json_encode($response);

	}


}