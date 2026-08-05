<?php
class Get_qr extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		if(is_null(get_user())){
			redirect("welcome");
			 //var_dump($this->session->userdata('antelope_user'));
		}
	}

	function device($id){
		$row = $this->db->select('mac_address, location')->from('devices')->where('device_id', $id)->get()->row();
		$this->load->library('ciqrcode');
		$params['data'] = $row->mac_address;
		$params['level'] = 'H';
		$params['size'] = 20;
		$params['savename'] = "uploads/".$row->location.".png";
		$this->ciqrcode->generate($params);

		redirect($params['savename']);
	}

}