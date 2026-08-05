<?php
class Dashboard extends CI_Controller {




	//hello brother, this is by naveed

	 function __construct()
    {
      parent::__construct();

			if(is_null(get_user())){
				redirect("welcome");
				//var_dump($this->session->userdata('antelope_user'));
			}

    }
    // comment by umar
	public function Index()
	{
		redirect("overview");
	}
	// another comment by umar


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

    public function copy_holidays($last_year)
    {
		$user = get_user();

		$current_year = $last_year + 1;
		$last_year_holidays = $this->db->select("*")->from("public_holidays")->where("company_id", get_user()["company_id"])->where("YEAR(holiday_date) = " . $last_year);
		if($user["permissions_level"] == "Outlet") {
			$last_year_holidays = $last_year_holidays->where("branch_id", $user["branch_id"]);
		}
		$last_year_holidays = $last_year_holidays->get()->result();
		
		$insert_count = 0;

		foreach($last_year_holidays as $holiday) {
			$parts_of_date = explode("-", $holiday->holiday_date);
			$cur_year_where_date = $current_year . '-' . $parts_of_date[1] . '-' . $parts_of_date[2];
			$cur_year_holiday = $this->db->select("*")->from("public_holidays")->where("company_id", get_user()["company_id"])->where("branch_id", $holiday->branch_id)->where("holiday_date", $cur_year_where_date)->get()->row();

			if(is_null($cur_year_holiday)) {
				if($this->db->insert("public_holidays", array(
					"company_id" => $holiday->company_id,
					"branch_id" => $holiday->branch_id,
					"title" => $holiday->title,
					"holiday_date" => $cur_year_where_date,
					"rate" => $holiday->rate,
					"include_groups" => $holiday->include_groups,
					"exclude_groups" => $holiday->exclude_groups
				)) === true) {
					$insert_count++;
				}

			}
		}

		if($insert_count > 0) {
			echo json_encode(array(
				"status" => "success",
				"msg" => "Holidays updated",
				"reset" => 1
			));
			insert_log('Simple', ['action' => 'Copied,Holidays']);
		} else {
			echo json_encode(array(
				"status" => "success",
				"msg" => "All holidays are updated"
			));
		}

    }
}
?>
