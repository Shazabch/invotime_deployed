<?php
class Api extends CI_Controller {

	 function __construct()
    {
      parent::__construct();

    }

	public function Index()
	{

	}


	public function login_ticket()
	{

		$username = $this->input->get('username');
		$password = md5($this->input->get('password'));
		// $passcode = "shani";
		$response['success'] = false;
		//$response['data'] = null;

		$employee_data = $this->db->select('*')->from('employees')->where('username',$username)->where('password',$password)->get()->row();

		//var_dump($employee_data);
		//die();
		

		if($employee_data){

			$job_data = $this->db->select('*')->from('jobs')->where('id',$employee_data->job_id)->get()->row();

			if($employee_data){
				$data = $employee_data;
				$token = sha1($data->first_name.$data->username.uniqid());
				$this->db->set('token',$token)->where('username',$username)->where('password',$password)->update('employees');

				$employee_data = $this->db->select('employees.first_name as person_name,employees.photo as avatar,employees.phone as mobile_number,companies.name as company_name')
				->join('companies','employees.company_id = companies.id')
				->from('employees')->where('username',$username)->where('password',$password)->get();

				$response['success'] = true;
				$response['data'] = $employee_data->row_array();
				$response['data']["token"] = $token;


				if(strpos($job_data->permissions, 'ticket_transactions') !== false){
					$response['data']["user_type"] = 'pos';
				}	
				else if(strpos($job_data->permissions, 'ticket_scans') !== false){
					$response['data']["user_type"] = 'gate';
				}
				else{
					$response['success'] = false;
				}

				



				$response['data']["avatar"] =  'http://uvschools.com/uvticket/uploads/'.$response['data']["avatar"];
			}
		}

		// $response['success'] = true;
		// $response['data']["company_name"] = "Company ABC";
		// $response['data']["avatar"] = "https://www.google.com.pk/images/branding/googlelogo/2x/googlelogo_color_120x44dp.png";
		// $response['data']["mobile_number"] = "mobile_number";
		// $response['data']["person_name"] = "person_name";

		// $response['data']["token"] = "token";



		return $this->output
		->set_content_type('application/json')
		->set_status_header(200)
		->set_output(json_encode($response));
	}



	public function ticketGuyStats(){

		$token = $this->input->get('token');
		$employee_row = $this->db->get_where('employees', array('token' => $token))->row();


		//$cars = $this->db->select('*')->from('cars')->where("driver_id =",$driver_data->row()->id)->get();
		// if(!$this->authenticate($token)){
		// 	$response['error'] = "Wrong authentication";
		// 	return $this->output
		// 	->set_content_type('application/json')
		// 	->set_status_header(200)
		// 	->set_output(json_encode($response));
		// }



		$response['success'] = true;
		$response['data'] = array();

		$employee_id = $employee_row->id;
		
		$query = $this->db->query("SELECT events.event_name_english as event, count(1) as count FROM ticket_scans INNER JOIN events ON ticket_scans.event_id = events.id WHERE scanned_by = $employee_id GROUP BY event_id");

		foreach ($query->result() as $row)
		{
		        $response['data'][] = $row;
		}


		// $event = array();
		// $event["event"] = "First Event";
		// $event["count"] = 10;


		// $response['data'][] = $event;
		// $response['data'][] = $event;
		// $response['data'][] = $event;
		// $response['data'][] = $event;
		// $response['data'][] = $event;
		// $response['data'][] = $event;
		// $response['data'][] = $event;
		// $response['data'][] = $event;
		// $response['data'][] = $event;
		// $response['data'][] = $event;
		


		return $this->output
			->set_content_type('application/json')
			->set_status_header(200)
			->set_output(json_encode($response));

	}

	public function time_test(){

		$date = new DateTime(date("Y-m-d H:i:s"));
        $date->setTimezone(new DateTimeZone("Africa/Khartoum"));

        echo $date->format('Y-m-d H:i:s');



		date_default_timezone_set("Asia/Karachi");


		$time_now_temp = date("Y-m-d H:i:s");
        $scan_time_temp = "2018-11-20 03:00:00";

        $time_now = strtotime($time_now_temp);
        $scan_time = strtotime($scan_time_temp);

        if ($time_now > $scan_time) { 
            echo "time_now is bigger";
        }
        else{

            echo "scan_time is bigger";
        }

    	echo '</br>'.$time_now_temp;
	}

	public function ticket_scan(){


		$keyword = $this->input->get('keyword'); //this is qr code
		$token = $this->input->get('token');


		$employee_row = $this->db->get_where('employees', array('token' => $token))->row();

		$transation_row = $this->db->get_where('ticket_transactions', array('qr_code' => $keyword))->row();


		

			if(!$transation_row){
				$response["data"]['status'] = "code_not_found";

			}
			else{
				$event_row = $this->db->get_where('events', array('id' => $transation_row->event_id))->row();

		
		        $event_roles_row = $this->db->get_where('event_roles', array('employee_id' => $employee_row->id, 'event_id' => $event_row->id))->row();

				if(!$event_roles_row){
					$response["data"]['status']  = "You don't have permission to scan tickets for event " . $event_row->event_name_english;
				}else{


			$scanning_started = false;

			$ticket_row = $this->db->get_where('tickets', array('id' => $transation_row->ticket_id))->row();
			$event_row = $this->db->get_where('events', array('id' => $transation_row->event_id))->row();

			
			$date = new DateTime(date("Y-m-d H:i:s"));
        	$date->setTimezone(new DateTimeZone($event_row->timezone));

        	$scan_date = new DateTime($event_row->scanning_starts_at);
        	$scan_date->setTimezone(new DateTimeZone('UTC'));


        	$date2 = new DateTime($date->format('Y-m-d H:i:s'));
        	$scan_date2 = new DateTime($scan_date->format('Y-m-d H:i:s'));



        	// var_dump($date2);
        	// var_dump($scan_date2);

        	// echo ($scan_date > $date); //scan bigger than date

        	// echo "======";
        	// echo ($date > $scan_date);


			if($scan_date2 > $date2){

				$response["data"]['status'] = "event_not_started";

			}
			else{


				
				
				$ticket_scans_row = $this->db->get_where('ticket_scans', array('ticket_transaction_id' => $transation_row->id))->row();

				if(!$ticket_scans_row){


					$d = new DateTime(date("Y-m-d H:i:s"));
			        $d->setTimezone(new DateTimeZone($event_row->timezone));


					$data = array(
					        'ticket_transaction_id' => $transation_row->id,
					        'time_in' => $d->format('Y-m-d H:i:s'),
					        'scanned_by' => $employee_row->id,
					        'event_id' => $transation_row->event_id,
					        'ticket_id' => $transation_row->ticket_id,
					        'company_id' => $event_row->company_id
					);

					$this->db->insert('ticket_scans', $data);

					$response["data"]['status'] = "scanned_success";
					$response["data"]['visitor_name'] = $transation_row->visitor_name;
					$response["data"]['event_name'] = $event_row->event_name_english;
					$response["data"]['ticket_type'] = $ticket_row->ticket_type;
					$response["data"]['time_in'] = $d->format('Y-m-d H:i:s');

				}
				else{


					$response["data"]['status'] = "already_scanned";
					$response["data"]['visitor_name'] = $transation_row->visitor_name;
					$response["data"]['event_name'] = $event_row->event_name_english;
					$response["data"]['ticket_type'] = $ticket_row->ticket_type;
					$response["data"]['time_in'] = $ticket_scans_row->time_in;

				}
			}

		}
			

			}
		



		return $this->output
			->set_content_type('application/json')
			->set_status_header(200)
			->set_output(json_encode($response));
	}

	public function pull_winner(){

		$event_id = $this->input->get('event_id');

		
		$row = $this->db->query("SELECT *  FROM ticket_scans WHERE event_id = $event_id ORDER BY RAND() LIMIT 1")->row();

		$winner_row = null;
		//var_dump($row);

		if($row){
			$tt_id = $row->ticket_transaction_id;
			$winner_row = $this->db->query("SELECT *  FROM ticket_transactions WHERE id = $tt_id")->row();
		}
		
		

		$response["winner"] = $winner_row;
		

		return $this->output
			->set_content_type('application/json')
			->set_status_header(200)
			->set_output(json_encode($response));

	}


	public function pos_scan(){

		$keyword = $this->input->get('keyword'); //this is qr code
		$token = $this->input->get('token');

		$employee_row = $this->db->get_where('employees', array('token' => $token))->row();
		
		
		$response['success'] = true;
	
		$response["data"]['can_register'] = false;

		$ticket_transactions_row = $this->db->get_where('ticket_transactions', array('qr_code' => $keyword))->row();
		//$ticket_row = $this->db->get_where('ticket_transactions', array('qr_code' => $keyword))->row();

		if(!$ticket_transactions_row){

			$response["data"]['can_register'] = true;


		}
		else{
			$response["data"]['error_message'] = "Ticket Already Registered";
		}

		$tickets = $this->db->order_by('id', 'desc')->get_where('tickets', array('company_id' => $employee_row->company_id))->result();

		//var_dump($tickets);

		foreach ($tickets as $row)
		{
			$event = array();
			$event["id"] = $row->id;
			$event["ticket_price"] = $row->ticket_price;
			$event["ticket_name"] = $row->ticket_type;

			$response["data"]["tickets"][] = $event;
		}
		
		
		return $this->output
			->set_content_type('application/json')
			->set_status_header(200)
			->set_output(json_encode($response));
	}

	public function register_pos(){

		$keyword = $this->input->get('keyword'); //this is qr code
		$token = $this->input->get('token');
		$ticket_id = $this->input->get('ticket_id');
		$amount_paid = $this->input->get('amount_paid');

		$response['status'] = false;


		$employee_row = $this->db->get_where('employees', array('token' => $token))->row();
		$ticket_row = $this->db->get_where('tickets', array('id' => $ticket_id))->row();
		$event_row = $this->db->get_where('events', array('id' => $ticket_row->event_id))->row();

		$event_roles_row = $this->db->get_where('event_roles', array('employee_id' => $employee_row->id, 'event_id' => $event_row->id))->row();

		if(!$event_roles_row){
			$response['error_message'] = "You don't have permission to register tickets for event " . $event_row->event_name_english;
		}else{

			 $this->db->select('COUNT(1) as cnt');
			 $this->db->where('ticket_id',$ticket_row->id);
			 $cnt_row = $this->db->get('ticket_transactions')->row();

			if($ticket_row->ticket_limit > $cnt_row->cnt){
				$data = array(
			        'event_id' => $ticket_row->event_id,
			        'employee_id' => $employee_row->id,
			        'ticket_id' => $ticket_row->id,
			        'qr_code' => $keyword,
			        'external_ticket' => 1,
			        'paid_amount' => $amount_paid
					);

					$insert = $this->db->insert('ticket_transactions', $data);
				
					if($insert){
						$response['status'] = true;
					}
					else{
						$response['error_message'] = "Could not add ticket";
					}

			}
			else{
				$response['error_message'] = "Ticket limit exceeded (".$ticket_row->ticket_type." is limited to ".$ticket_row->ticket_limit." tickets)";
			}

		}



		


		
		return $this->output
			->set_content_type('application/json')
			->set_status_header(200)
			->set_output(json_encode($response));
	}

	public function posGuyStats(){

		$token = $this->input->get('token');
		$employee_row = $this->db->get_where('employees', array('token' => $token))->row();


		//$cars = $this->db->select('*')->from('cars')->where("driver_id =",$driver_data->row()->id)->get();
		// if(!$this->authenticate($token)){
		// 	$response['error'] = "Wrong authentication";
		// 	return $this->output
		// 	->set_content_type('application/json')
		// 	->set_status_header(200)
		// 	->set_output(json_encode($response));
		// }



		$response['success'] = true;
		$response['data'] = array();

		$employee_id = $employee_row->id;
		
		$query = $this->db->query("SELECT events.event_name_english as event, count(1) as count FROM ticket_transactions INNER JOIN events ON ticket_transactions.event_id = events.id WHERE employee_id = $employee_id GROUP BY event_id");

		foreach ($query->result() as $row)
		{
		        $response['data'][] = $row;
		}


		// $event = array();
		// $event["event"] = "First Event";
		// $event["count"] = 10;


		// $response['data'][] = $event;
		// $response['data'][] = $event;
		// $response['data'][] = $event;
		// $response['data'][] = $event;
		// $response['data'][] = $event;
		// $response['data'][] = $event;
		// $response['data'][] = $event;
		// $response['data'][] = $event;
		// $response['data'][] = $event;
		// $response['data'][] = $event;
		


		return $this->output
			->set_content_type('application/json')
			->set_status_header(200)
			->set_output(json_encode($response));

	}




}
?>
