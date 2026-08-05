<?php
class Dashboard extends CI_Controller {

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
		redirect("overview");
	}


  public function table($table_name)
	{

			$active_menu = $table_name;
			$page = $table_name;
			$data['pageTitle'] = ucwords(str_replace("_"," ",$table_name));


			if(is_callable(array($this->antelope, $table_name), false, $table_name)){

					$this->load->helper('xcrud');
					$xcrud = xcrud_get_instance($table_name . "_" . time());
		      $xcrud->unset_title();

		      $xcrud  = call_user_func_array(array($this->antelope, $table_name),  array($xcrud));

		      $data['table_content'] = $xcrud;

			}else{

				$data['table_content'] = "<div class='alert alert-danger'>
					<h4>Could not find <strong>$active_menu</strong> function in <strong>Application</strong>  > <strong> Models</strong>  > <strong> antelope.php</strong> </h4>
				</div>";

			}

			$data['active_menu'] = "dashboard/table/".$active_menu;
			$this->load->view('header',$data);



			$data["menus"] = get_menus();
			$this->load->view('sidebar',$data);

			if (is_page_permitted($page)) {
					$this->load->view('table',$data);
			}
			else{
					$this->load->view('not_permitted');
			}

			$this->load->view('footer',$data);
	}
}
?>
