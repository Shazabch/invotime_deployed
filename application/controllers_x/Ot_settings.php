<?php
class Ot_settings extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		if(is_null(get_user())){
			redirect("welcome");
			 //var_dump($this->session->userdata('antelope_user'));
		}
	}

	function index(){
		$data['pageTitle'] = "OT Settings";
		$data['active_menu'] = "ot_settings";
		$this->load->view('header',$data);
		$data["menus"] = get_menus();
		$this->load->view('sidebar',$data);
		$this->load->view('ot_settings');
		$this->load->view('footer');
	}

	function getMinutes(){
		$cid = get_user()["company_id"];
		$data["skip_time"] = "no";
		$ot = $this->db->select('skip_time')->from('ot_settings')->where('company_id',$cid)->get()->row();
		if($ot && $ot->skip_time != 0){
			$data["skip_time"] = $ot->skip_time;
		}
		echo json_encode($data);
	}

	function updateMinutes(){
		$cid = get_user()["company_id"];
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$skip_time = $request->skip_time;
		if($skip_time == "no"){
			$skip_time = 0;
		}
		$data = array('company_id' => $cid, 'skip_time' => $skip_time);
		$this->db->replace('ot_settings', $data);
	}


}