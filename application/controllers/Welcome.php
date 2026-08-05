<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		if($this->session->userdata("payroll_user")){
			redirect("invocore_payroll");
		}else if(!is_null(get_user())){
			redirect("overview");
				//var_dump($this->session->userdata('antelope_user'));
		}

	}

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{


		$data = array();
		$data["company"] = false;
		if($this->input->get("company")){
			$data["company"] = $this->input->get("company");

		}

		$this->load->view('login',$data);

	}
}
