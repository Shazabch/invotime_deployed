<?php
class Recover_password extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		if(!is_null(get_user())){
			redirect("welcome");
				//var_dump($this->session->userdata('antelope_user'));
		}

	}

	function submit_email(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$email = $request->email;
		$emp = $this->db->select('*')->from('employees')->where('email',$email)->where('deleted_at is null AND employee_status = "active" ')->get()->row();
		if($emp){
			$recovery_key = md5(uniqid('invocore' . date("H:i:s")));
			$this->db->set('recovery_key',$recovery_key)->where('id',$emp->id)->update('employees');
			$link = base_url() .'recover_password/reset/' . $recovery_key;
            $subject = "Password Recovery";
            $data = array(
                "dear_sir" => "Dear Sir/Madam,",
                "msg" => "You requested for password recovery. Please follow the link to reset your password.",
                "thanks" => " - Thanks",
                "link" => $link
            );
            $message = $this->load->view('password_recovery.php', $data, TRUE);
            $this->send_email($email, $message, $subject);
            $data["success"] = true;
            $data["msg"] = "Recovery mail sent to your email address!";
		}else{
			$data["success"] = false;
			$data["msg"] = "Email does not exist!";
		}
		echo json_encode($data);
	}

	function send_email($email, $message, $subject){
		$url = 'https://sendgrid.com/api/mail.send.json';
               $params = array(
            'api_user'  => 'invocore',
            'api_key'   => '!nv0cOremy',
            'to'        => $email,
            'subject'   => $subject,
            'html'      => $message,
            'text'      => $message,
            'from'      => 'support@invocore.com.my',
            'fromname'  => 'Invocore'
          );
                $session = curl_init($url);
        // Tell curl to use HTTP POST
        curl_setopt ($session, CURLOPT_POST, true);
        // Tell curl that this is the body of the POST
        curl_setopt ($session, CURLOPT_POSTFIELDS, $params);
        // Tell curl not to return headers, but do return the response
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

        // obtain response
        $response = curl_exec($session);
        curl_close($session);
	}

	function reset($key){
		$emp = $this->db->select('id,first_name')->from('employees')->where('recovery_key',$key)->get()->row();
		if($emp){
			$data["emp"] = $emp;
			$this->load->view('new-password',$data);
		}else{
			redirect(base_url());
		}
	}

	function reset_pwd(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$id = $request->id;
		$new_password = $request->new_password;
		$confirm_password = $request->confirm_password;
		if($this->db->set('password',md5($new_password))->where('id',$id)->update('employees')){
			$data["success"] = true;
			$data["msg"] = "Password changed successfully!";
			$this->db->set('recovery_key','')->where('id',$id)->update('employees');
		}else{
			$data["success"] = false;
			$data["msg"] = "Some error occurred!";
		}
		echo json_encode($data);
		
	}

}