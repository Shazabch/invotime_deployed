<?php
class My_profile extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		if(is_null(get_user())){
			redirect("welcome");
				//var_dump($this->session->userdata('antelope_user'));
		}

	}

	function index(){
		$data['pageTitle'] = "My Profile";
		$data['active_menu'] = "";
		$this->load->view('header',$data);
		$data["menus"] = get_menus();
		$this->load->view('sidebar',$data);
		$this->load->view('my_profile',$data);
		$this->load->view('footer');
	}

	function change_password(){
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$current_password = md5($request->current_password);
		$new_password = $request->new_password;
		$confirm_password = $request->confirm_password;
		$old_password = $this->session->userdata('antelope_user')["password"];
		if($old_password != $current_password){
			$data["success"] = false;
			$data["msg"] = "Current Password is not correct!";
		}else if($new_password != $confirm_password){
			$data["success"] = false;
			$data["msg"] = "New Password and Confirm Password does not match!";
		}else{
			$id = $this->session->userdata('antelope_user')["id"];
			if($this->db->set('password',md5($new_password))->where('id',$id)->update('employees')){
				$data["success"] = true;
				$data["msg"] = "Password changed successfully!";
				$oldValues = $this->session->userdata("antelope_user");
            	$oldValues["password"] = md5($new_password);     
            	$this->session->set_userdata("antelope_user",$oldValues);
			}else{
				$data["success"] = false;
				$data["msg"] = "Some error occurred!";
			}
		}

		echo json_encode($data);

	}



}