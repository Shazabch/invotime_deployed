<?php
class Cronjob extends CI_Controller {

	function __construct()
    {
      parent::__construct();

			
			//var_dump(get_user());
    }

	public function Index()
	{
		
		date_default_timezone_set("Asia/Kuala_Lumpur");


		$code = $this->input->get('code');

		if($code == "nashnash"){

			$date_now = new DateTime();
			$date_now_string = $date_now->format("H:i:s");

			$date_only_today_string = $date_now->format("Y-m-d");

			//$datetime1 = new DateTime('2009-10-11 10:10:00');
			//$datetime2 = new DateTime('2009-10-11 10:15:10');

			//var_dump($date_now);

			//var_dump($date_now > $datetime1);

			// $interval = $datetime1->diff($datetime2);

			// var_dump($interval);
			// echo "<br/>";
			// echo $interval->format('%R%a days');

			$forgot_to_clockout = $this->db->query("SELECT clockings.*, shifts.start_time,shifts.end_time, shifts.auto_clockout_time FROM clockings INNER JOIN shifts ON clockings.shift_id=shifts.id WHERE clock_out IS NULL AND '$date_now_string' > auto_clockout_time")->result();

			foreach ($forgot_to_clockout as $row)
			{
			        $data = array(
					        'clock_out' => $date_only_today_string . " " . $row->auto_clockout_time,
					        'auto_clock_out' => 'Yes'
					);

					//print_r($data);

					$this->db->where('id', $row->id);
					$this->db->update('clockings', $data);
			}

			echo  count($forgot_to_clockout) . ' auto clocked out';

			//echo $this->db->last_query() . "<br/><br/>";

			//var_dump($forgot_to_clockout);
		}
		else{
			die("Access denied!");
		}

		


	}


}


?>