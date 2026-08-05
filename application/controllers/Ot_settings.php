<?php
class Ot_settings extends CI_Controller
{

	function __construct()
	{
		parent::__construct();

		if (is_null(get_user())) {
			redirect("welcome");
			//var_dump($this->session->userdata('antelope_user'));
		}
	}

	function index()
	{
		if (!is_page_permitted('ot_settings')) {
            redirect_if_not_permitted();
        }
		$data['pageTitle'] = "OT Settings";
		$data['active_menu'] = "ot_settings";
		$this->load->view('header', $data);
		$data["menus"] = get_menus();
		$data["company_id"] = get_user()["company_id"];
		$this->load->view('sidebar', $data);
		$this->load->view('ot_settings', $data);
		$this->load->view('footer');
	}

	function getMinutes()
	{
		$cid = get_user()["company_id"];
		$data["skip_time"] = "no";
		$ot = $this->db->select('skip_time')->from('ot_settings')->where('company_id', $cid)->get()->row();
		if ($ot && $ot->skip_time != 0) {
			$data["skip_time"] = $ot->skip_time;
		}
		echo json_encode($data);
	}

	/**
	 * When Page loads send valid data to page.
	 * This function also sends data for outlet admins
	 *
	 * @return array Data to be returned to the settings view
	 */
	function getSettings()
	{
		$user = get_user();
		$permssion_level = $user['permissions_level'];

		// Default data for company admin
		$data['ot_type'] = "default";
		$data['ot_round'] = false;
		$data['early_ot_round'] = false;
		$data['use_half_hours_for_saturdays'] = false;
		$data['round_first_hour_only'] = false;
		$data['round_by_exact_hour'] = false;
		$data['worked_hours_ot_rd'] = false;
		$data['worked_hours_ot_ph'] = false;
		$data['worked_hours_ot_off'] = false;
		$data['deduct_hour_ot_rd'] = false;
		$data['deduct_hour_ot_ph'] = false;
		$data['deduct_hour_ot_off'] = false;
		$data['ignore_breaks_after_endtime'] = false;
		$data['ot_weekly_hours'] = 0;
		// $data['ot_daily_hours'] = 0;
		$data['first_day_of_week'] = '';
		$data['bid'] = null;
		$data['ot_round_settings'] = [];
		$data['early_ot_round_settings'] = [];

		if ($permssion_level === "Company") {
			$data['outlets'] = get_company_outlets();
			echo json_encode($data);
		} else {
			// Load outlet data
			$bid = $user['branch_id'];
			$data['bid'] = $bid;
			$data['outlets'] = get_company_outlets($bid);
			$ot_settings = $this->db->select('ot_type, ot_round, early_ot_round, round_first_hour_only, round_by_exact_hour, different_first_hour_rounding, ot_weekly_hours, first_day_of_week, worked_hours_ot_rd, worked_hours_ot_ph, deduct_hour_ot_rd, deduct_hour_ot_ph, worked_hours_ot_off, deduct_hour_ot_off, ignore_breaks_after_endtime, use_half_hours_for_saturdays')->from('branches')->where('id', $bid)->get()->row();

			if ($ot_settings) {
				$data['ot_type'] = $ot_settings->ot_type;
				$data['ot_round'] = ($ot_settings->ot_round == 0) ? false : true;
				$data['early_ot_round'] = ($ot_settings->early_ot_round == 0) ? false : true;
				$data['use_half_hours_for_saturdays'] = ($ot_settings->use_half_hours_for_saturdays == 0) ? false : true;
				$data['round_first_hour_only'] = ($ot_settings->round_first_hour_only == 0) ? false : true;
				$data['round_by_exact_hour'] = ($ot_settings->round_by_exact_hour == 0) ? false : true;
				$data['worked_hours_ot_rd'] = ($ot_settings->worked_hours_ot_rd == 0) ? false : true;
				$data['worked_hours_ot_ph'] = ($ot_settings->worked_hours_ot_ph == 0) ? false : true;
				$data['worked_hours_ot_off'] = ($ot_settings->worked_hours_ot_off == 0) ? false : true;
				$data['deduct_hour_ot_rd'] = ($ot_settings->deduct_hour_ot_rd == 0) ? false : true;
				$data['deduct_hour_ot_ph'] = ($ot_settings->deduct_hour_ot_ph == 0) ? false : true;
				$data['deduct_hour_ot_off'] = ($ot_settings->deduct_hour_ot_off == 0) ? false : true;
				$data['ignore_breaks_after_endtime'] = ($ot_settings->ignore_breaks_after_endtime == 0) ? false : true;
				$data['ot_weekly_hours'] = (float)$ot_settings->ot_weekly_hours;
				// $data['ot_daily_hours'] = (double)$ot_settings->ot_daily_hours;
				$data['first_day_of_week'] = $ot_settings->first_day_of_week;
			}

			$ot_round_settings = $this->db->select("start, end, round_to, branch_id")->from("ot_round_settings")->where("branch_id", $bid)->get()->result();

			// Conversion to number is needed to use input:number on front end
			foreach ($ot_round_settings as $key => $v) {
				$ot_round_settings[$key]->start = (float)$v->start;
				$ot_round_settings[$key]->end = (float)$v->end;
				$ot_round_settings[$key]->round_to = (float)$v->round_to;
			}

			$data['ot_round_settings'] = $ot_round_settings;

			$early_ot_round_settings = $this->db->select("start, end, round_to, branch_id")->from("early_ot_round_settings")->where("branch_id", $bid)->get()->result();

			// Conversion to number is needed to use input:number on front end
			foreach ($early_ot_round_settings as $key => $v) {
				$early_ot_round_settings[$key]->start = (float)$v->start;
				$early_ot_round_settings[$key]->end = (float)$v->end;
				$early_ot_round_settings[$key]->round_to = (float)$v->round_to;
			}

			$data['early_ot_round_settings'] = $early_ot_round_settings;

			echo json_encode($data);
		}
	}

	function getOutletSettings()
	{
		$cid = get_user()["company_id"];
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

    $year = date('Y');

		$data = [];
		$companies_allowed_for_monthly_ot = companies_allowed_for_monthly_ot();
		$is_monthly_ot = in_array($cid, $companies_allowed_for_monthly_ot);
		$data['is_monthly_ot'] = $is_monthly_ot;
		$data['is_weekly_ot'] = in_array($cid, [171,3]);


		$bid = $request->outletId;
		$data['ot_type'] = 'default';
		$data['ot_round'] = false;
		$data['early_ot_round'] = false;
		$data['use_half_hours_for_saturdays'] = false;
		$data['round_first_hour_only'] = false;
		$data['round_by_exact_hour'] = false;
		$data['different_first_hour_rounding'] = false;
		$data['worked_hours_ot_rd'] = false;
		$data['worked_hours_ot_ph'] = false;
		$data['worked_hours_ot_off'] = false;
		$data['deduct_hour_ot_rd'] = false;
		$data['deduct_hour_ot_ph'] = false;
		$data['deduct_hour_ot_off'] = false;
		$data['ignore_breaks_after_endtime'] = false;
		$data['ot_weekly_hours'] = 0;
		// $data['ot_daily_hours'] = 0;
		$data['first_day_of_week'] = '';
		$data['bid'] = $request->outletId;
		$data['ot_round_settings'] = [];
		$data['first_hour_ot_round_settings'] = [];
		$data['early_ot_round_settings'] = [];
		// $ot_settings = $this->db->get_where('branches', array('id' => $request->outletId))->row();
		$ot_settings = $this->db->select('ot_type, ot_round, early_ot_round, round_first_hour_only, round_by_exact_hour, different_first_hour_rounding, ot_weekly_hours, first_day_of_week, worked_hours_ot_rd, worked_hours_ot_ph, deduct_hour_ot_rd, deduct_hour_ot_ph, worked_hours_ot_off, deduct_hour_ot_off, ignore_breaks_after_endtime, use_half_hours_for_saturdays')->from('branches')->where('id', $bid)->get()->row();

		if ($ot_settings) {
			$data['ot_type'] = $ot_settings->ot_type;
			$data['ot_round'] = ($ot_settings->ot_round == 0) ? false : true;
			$data['early_ot_round'] = ($ot_settings->early_ot_round == 0) ? false : true;
			$data['use_half_hours_for_saturdays'] = ($ot_settings->use_half_hours_for_saturdays == 0) ? false : true;
			$data['round_first_hour_only'] = ($ot_settings->round_first_hour_only == 0) ? false : true;
			$data['round_by_exact_hour'] = ($ot_settings->round_by_exact_hour == 0) ? false : true;
			$data['different_first_hour_rounding'] = ($ot_settings->different_first_hour_rounding == 0) ? false : true;
			$data['worked_hours_ot_rd'] = ($ot_settings->worked_hours_ot_rd == 0) ? false : true;
			$data['worked_hours_ot_ph'] = ($ot_settings->worked_hours_ot_ph == 0) ? false : true;
			$data['worked_hours_ot_off'] = ($ot_settings->worked_hours_ot_off == 0) ? false : true;
			$data['deduct_hour_ot_rd'] = ($ot_settings->deduct_hour_ot_rd == 0) ? false : true;
			$data['deduct_hour_ot_ph'] = ($ot_settings->deduct_hour_ot_ph == 0) ? false : true;
			$data['deduct_hour_ot_off'] = ($ot_settings->deduct_hour_ot_off == 0) ? false : true;
			$data['ignore_breaks_after_endtime'] = ($ot_settings->ignore_breaks_after_endtime == 0) ? false : true;
    		$data['ot_weekly_hours'] = (float)$ot_settings->ot_weekly_hours;
			$data['first_day_of_week'] = $ot_settings->first_day_of_week;
		}

		$ot_round_settings = $this->db->select("start, end, round_to, branch_id")->from("ot_round_settings")->where("branch_id", $bid)->where("first_hour", 0)->get()->result();

		// Conversion to number is needed to use input:number on front end
		foreach ($ot_round_settings as $key => $v) {
			$ot_round_settings[$key]->start = (float)$v->start;
			$ot_round_settings[$key]->end = (float)$v->end;
			$ot_round_settings[$key]->round_to = (float)$v->round_to;
		}

		$data['ot_round_settings'] = $ot_round_settings;

		$first_hour_ot_round_settings = $this->db->select("start, end, round_to, branch_id")->from("ot_round_settings")->where("branch_id", $bid)->where("first_hour", 1)->get()->result();

		// Conversion to number is needed to use input:number on front end
		foreach ($first_hour_ot_round_settings as $key => $v) {
			$first_hour_ot_round_settings[$key]->start = (float)$v->start;
			$first_hour_ot_round_settings[$key]->end = (float)$v->end;
			$first_hour_ot_round_settings[$key]->round_to = (float)$v->round_to;
		}

		$data['first_hour_ot_round_settings'] = $first_hour_ot_round_settings;

		$early_ot_round_settings = $this->db->select("start, end, round_to, branch_id")->from("early_ot_round_settings")->where("branch_id", $bid)->get()->result();

		// Conversion to number is needed to use input:number on front end
		foreach ($early_ot_round_settings as $key => $v) {
			$early_ot_round_settings[$key]->start = (float)$v->start;
			$early_ot_round_settings[$key]->end = (float)$v->end;
			$early_ot_round_settings[$key]->round_to = (float)$v->round_to;
		}

		$data['early_ot_round_settings'] = $early_ot_round_settings;

		$data['months'] = $this->monthlyWorkingDays($year, $bid);

		return send_json_response($data);
	}

	function updateMinutes()
	{
		$cid = get_user()["company_id"];
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);
		$skip_time = $request->skip_time;
		if ($skip_time == "no") {
			$skip_time = 0;
		}
		$data = array('company_id' => $cid, 'skip_time' => $skip_time);
		$this->db->replace('ot_settings', $data);
		insert_log("Simple", ["action" => "Edited,OT Minutes"]);
	}

	function updateSettings()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		if ($request->bid === NULL || $request->bid === '') {
			$data['success'] = false;
			$data['message'] = "Please select an outlet";
			echo json_encode($data);
			return;
		}

		// if ($request->ot_type === "eight_hours" && $request->ot_round === true) {
		// 	$data['success'] = false;
		// 	$data['message'] = "Can't set eight hours with Round OT";
		// 	echo json_encode($data);
		// 	return;
		// }

		if ($request->ot_type === "weekly_hours" && ($request->ot_weekly_hours <= 0 ||
			$request->first_day_of_week === "" || $request->first_day_of_week === null)) {
			$data["success"] = false;
			$data["message"] = "Weekly hours should be greater than 0 and weekday must be selected";
			echo json_encode($data);
			return;
		}

		// if ($request->ot_type === "monthly_ot" && $request->ot_daily_hours <= 0) {
		// 	$data["success"] = false;
		// 	$data["message"] = "Daily hours should be greater than 0";
		// 	echo json_encode($data);
		// 	return;
		// }

		$data = array('ot_type' => $request->ot_type);
		$data['ot_round'] = ($request->ot_round == true) ? 1 : 0;
		$data['early_ot_round'] = ($request->early_ot_round == true) ? 1 : 0;
		$data['use_half_hours_for_saturdays'] = ($request->use_half_hours_for_saturdays == true) ? 1 : 0;
		$data['round_first_hour_only'] = ($request->round_first_hour_only == true) ? 1 : 0;
		$data['round_by_exact_hour'] = ($request->round_by_exact_hour == true) ? 1 : 0;
		$data['different_first_hour_rounding'] = ($request->different_first_hour_rounding == true) ? 1 : 0;
		$data['worked_hours_ot_rd'] = ($request->worked_hours_ot_rd == true) ? 1 : 0;
		$data['worked_hours_ot_ph'] = ($request->worked_hours_ot_ph == true) ? 1 : 0;
		$data['worked_hours_ot_off'] = ($request->worked_hours_ot_off == true) ? 1 : 0;
		$data['deduct_hour_ot_rd'] = ($request->deduct_hour_ot_rd == true) ? 1 : 0;
		$data['deduct_hour_ot_ph'] = ($request->deduct_hour_ot_ph == true) ? 1 : 0;
		$data['deduct_hour_ot_off'] = ($request->deduct_hour_ot_off == true) ? 1 : 0;
		$data['ignore_breaks_after_endtime'] = ($request->ignore_breaks_after_endtime == true) ? 1 : 0;
		$data["ot_weekly_hours"] = (($request->ot_type === "weekly_hours") ? $request->ot_weekly_hours : null);
		$data["first_day_of_week"] = (($request->ot_type === "weekly_hours") ? $request->first_day_of_week : null);
		// $data["ot_daily_hours"] = (($request->ot_type === "monthly_ot") ? $request->ot_daily_hours : null);

		$this->db->where('id', $request->bid);
		$this->db->update('branches', $data);
		insert_log("Simple", ["action" => "Edited,OT Settings"]);

		$data['success'] = true;
		echo json_encode($data);
	}

	public function updateOTRoundSettings()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		if ($request->bid === NULL || $request->bid === '') {
			$data['success'] = false;
			$data['message'] = "Please select an outlet";
			echo json_encode($data);
			return;
		}

		if ($this->is_OT_range_overlapping($request->round_settings)) {
			$data['success'] = false;
			$data['message'] = "OT Round settings are overlapping";
			echo json_encode($data);
			return;
		}
		// Validated now update records

		$this->db->delete('ot_round_settings', ['branch_id' => $request->bid, 'first_hour' => 0]);

		$this->db->insert_batch('ot_round_settings', $request->round_settings);

		$branch = $this->db->select('name')->from('branches')->where('id', $request->bid)->get()->row();

		$log_data = [
			'action' => 'Edited,OT Round Settings',
			'to_branch_id' => $request->bid,
			'to_outlet' => $branch->name,
		];
		insert_log("OT Round Settings", $log_data);

		$data['success'] = true;
		echo json_encode($data);
	}

	public function updateFirstHourOTRoundSettings()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		if ($request->bid === NULL || $request->bid === '') {
			$data['success'] = false;
			$data['message'] = "Please select an outlet";
			echo json_encode($data);
			return;
		}

		if ($this->is_OT_range_overlapping($request->round_settings)) {
			$data['success'] = false;
			$data['message'] = "OT Round settings are overlapping";
			echo json_encode($data);
			return;
		}
		// Validated now update records

		$this->db->delete('ot_round_settings', ['branch_id' => $request->bid, 'first_hour' => 1]);

		foreach ($request->round_settings as $key => $value) {
			$request->round_settings[$key]->first_hour = 1;
		}

		$this->db->insert_batch('ot_round_settings', $request->round_settings);

		$branch = $this->db->select('name')->from('branches')->where('id', $request->bid)->get()->row();

		$log_data = [
			'action' => 'Edited,First Hour OT Round Settings',
			'to_branch_id' => $request->bid,
			'to_outlet' => $branch->name,
		];
		insert_log("First Hour OT Round Settings", $log_data);

		$data['success'] = true;
		echo json_encode($data);
	}

	public function updateEarlyOTRoundSettings()
	{
		$postdata = file_get_contents("php://input");
		$request = json_decode($postdata);

		if ($request->bid === NULL || $request->bid === '') {
			$data['success'] = false;
			$data['message'] = "Please select an outlet";
			echo json_encode($data);
			return;
		}

		// if (count($request->round_settings) === 0) {
		// 	$data['success'] = false;
		// 	$data['message'] = "Please add at least one Early OT Round setting";
		// 	echo json_encode($data);
		// 	return;
		// }

		if ($this->is_OT_range_overlapping($request->round_settings)) {
			$data['success'] = false;
			$data['message'] = "Early OT Round settings are overlapping";
			echo json_encode($data);
			return;
		}
		// Validated now update records

		$this->db->delete('early_ot_round_settings', ['branch_id' => $request->bid]);

		$this->db->insert_batch('early_ot_round_settings', $request->round_settings);

		$branch = $this->db->select('name')->from('branches')->where('id', $request->bid)->get()->row();

		$log_data = [
			'action' => 'Edited,Early OT Round Settings',
			'to_branch_id' => $request->bid,
			'to_outlet' => $branch->name,
		];
		insert_log("Early OT Round Settings", $log_data);

		$data['success'] = true;
		echo json_encode($data);
	}

	private function is_OT_range_overlapping(array $collection)
	{
		$length = count($collection);

		for ($i = 0; $i < $length; $i++) {
			if ($collection[$i]->start < 0 || $collection[$i]->end > 59) {
				return true;
			}
			for ($j = 0; $j < $length; $j++) {
				if ($i != $j && $collection[$j]->start <= $collection[$i]->end && $collection[$j]->end >= $collection[$i]->start) {
					return true;
				}
			}
		}
		return false;
	}

  private function monthlyWorkingDays($year, $bid)
  {
    $cid = get_user()['company_id'];

    $months = $this->db->select('month, year, days')->from('monthly_working_days')
    ->where('branch_id', $bid)->where('company_id', $cid)->where('year', $year)->order_by('month', 'asc')->get()->result();
    if (count($months) === 0) {
      for ($i = 0; $i < 12; $i++) {
        $months[] = (object)[
          'month' => $i + 1,
          'year' => $year,
          'days' => 0
        ];
      }
    } else {
      $lookup = array_column($months, null, 'month');
      $all_months = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"];
      $result = [];

      foreach ($all_months as $month) {
        $result[] = $lookup[$month] ? $lookup[$month] : (object)[
          'month' => $month,
          'year' => $year,
          'days' => 0
        ];
      }
      $months = $result;
    }

    return $months;
  }

  public function getMonthlyWorkingDays()
  {
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);

    $months = $this->monthlyWorkingDays($request->year, $request->branch_id);

    $data['success'] = true;
    $data['months'] = $months;

    return send_json_response($data);
  }

  public function updateMonthlyWorkingDays()
  {
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);

    $cid = get_user()['company_id'];

    $this->db->delete('monthly_working_days', ['branch_id' => $request->branch_id, 'company_id' => $cid, 'year' => $request->year]);

    foreach ($request->months as $month) {
      $month->branch_id = $request->branch_id;
      $month->company_id = $cid;
    }

    $this->db->insert_batch('monthly_working_days', $request->months);

    insert_log("Simple", ["action" => "Edited,Monthly Working Days"]);

    $data['success'] = true;
    $data['message'] = "Monthly Working Days Updated";

    return send_json_response($data);
  }
}
