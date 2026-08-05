<?php
class BMI_Report extends CI_Controller {

	function __construct()
	{
		parent::__construct();

		if (is_null(get_user())) {
			redirect("welcome");
		}

	}

	public function index(){
		$data['pageTitle'] = "BMI Report";
		$data['active_menu'] = "BMI_Report";
		$this->load->view('header', $data);
		$data["menus"] = get_menus();
		$this->load->view('sidebar', $data);

		$first_day = date('Y-m-01');
		$last_day  = date('Y-m-t');
		$date = DateTime::createFromFormat('Y-m-d', $first_day);
		$data['from_f'] = $date->format('d/m/Y');
		$date = DateTime::createFromFormat('Y-m-d', $last_day);
		$data['to_f'] = $date->format('d/m/Y');

		$this->load->view('bmi_report', $data);
		$this->load->view('footer', $data);
	}

	public function process_file(){

		$this->load->library("excel");

		$company_id = get_user()["company_id"];

		$date1 = DateTime::createFromFormat('d/m/Y', $_POST['from']);
		$date2 = DateTime::createFromFormat('d/m/Y', $_POST['to']);

		if ($date1 > $date2) {
			$first_day = $date2->format('Y-m-d');
			$last_day = $date1->format('Y-m-d');
		} else {
			$first_day = $date1->format('Y-m-d');
			$last_day = $date2->format('Y-m-d');
		}

		// $company_working_hours = $this->db->select('date_format(working_hours,"%H:%i") as working_hours', false)->from('companies')->where('id', $company_id)->get()->row()->working_hours;
		$company_working_hours = get_company_working_hours($company_id);

		$public_holidays_all = get_public_holidays_all();

		$company_ot_settings = get_company_ot_settings($company_id);

		$company_early_ot_settings = get_company_early_ot_settings($company_id);

		$shifts = $this->db->select('id')->from('shifts')->where('company_id', $company_id)->where('is_leave', 'no')->get()->result();

		$shift_ids = array(0);
		foreach ($shifts as $s) {
			$shift_ids[] = $s->id;
		}

		$approved_ot_list = get_approved_ot_list($shift_ids, $first_day, $last_day);

		$branch_rest_days = $this->db->select('id,rest_days,off_days')->from('branches')->where('company_id', $company_id)->get()->result();

		$bmi_file = $_FILES['bmi_file']['tmp_name'];

		$special_ids = array("default-special-id");

		// load template
		$objectExcel = PHPExcel_IOFactory::createReader('Excel2007')->load($bmi_file);

		$sheet_count = $objectExcel->getSheetCount();

		$i = 0;
	    while ($objectExcel->setActiveSheetIndex($i)){

	        $object = $objectExcel->getActiveSheet();
			
	        $comments = $object->getComments();
			$object->setComments(array());
			foreach($comments as $key => $comment){
				$object->getComment($key)->setAuthor($comment->getAuthor());
				$object->getComment($key)->getText()->createTextRun($comment->getText()->getPlainText());
			}

			$special_id = trim($object->getCell('E3')->getValue());

			if($special_id != ""){
				$special_ids[] = $special_id;
			}
			
	        if($i < $sheet_count - 1 ){
	        	$i++;
	        } else {
	        	break;
	        }
	    }

	    $employees = $this->db->select('employees.id,employees.first_name,special_id,employees.is_daily_waged, d.name as department, p.title as position,employees.branch_id,is_ot,is_early_ot,inc_late_in,inc_late_break, inc_early_out, inc_short_hours,ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,round_first_hour_only,void_lateness_time_if_less_than,deduct_from_ot,deduct_from_ot_single,deduction_date')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('departments d', 'd.id = employees.department_id', 'left')->join('branches b', 'b.id = employees.branch_id', 'left')->join('positions p', 'p.id = employees.position_id', 'left')->where('employees.company_id', $company_id)->where('employees.deleted_at is null AND employee_status = "active" ')->where('roles.exclude_from_system', 'no')->where_in('special_id', $special_ids)->get()->result();

		$employees_ids = array(0);

		foreach ($employees as $emp) {
			$employees_ids[] = $emp->id;
		}

		$result_list = get_result_list($employees_ids, $first_day, $last_day);
		$result_list_overnight = get_result_list_overnight($employees_ids, $first_day, $last_day);
		$all_data = array();
		foreach ($employees as $emp) {
			$data = calculate_summary_data($emp->id, $first_day, $last_day, "summary", $emp,
				$result_list,
				$result_list_overnight,
				$company_working_hours,
				false, 
					$company_ot_settings, $company_early_ot_settings, $approved_ot_list, $branch_rest_days);

			$all_data[$emp->special_id] = $data;
			$data = array();
		}

		$month_rows = [];

		$i = 0;
	    while ($objectExcel->setActiveSheetIndex($i)){

	        $object = $objectExcel->getActiveSheet();

			$special_id = trim($object->getCell('E3')->getValue());

			$current_employee_data = array();
			$public_holidays = array();
			$rest_days = array();
			$ot_settings = array();
			$round_first_hour_only = 0;
			if(isset($all_data[$special_id])){
				$current_employee_data = $all_data[$special_id]["dates"];
				$public_holidays = $all_data[$special_id]["public_holidays"];		
				$rest_days = $all_data[$special_id]["rest_days"];
				$ot_settings = search_from_list_by_branch_id($company_ot_settings, $all_data[$special_id]["employee"]->branch_id);
				$round_first_hour_only = $all_data[$special_id]["employee"]->round_first_hour_only;
			}
			if($current_employee_data){
				$period = new DatePeriod(
					new DateTime($first_day),
					new DateInterval('P1D'),
					(new DateTime($last_day))->add(new DateInterval('P1D'))
				);
				$current_month = null;
				$row = 1;
				foreach ($period as $date) {
					$current_date = $date->format('Y-m-d');
					$month = $date->format('F');

					if($current_month != $month){
						$months = [
							$date->format('F Y'),$date->format('F-Y'),$date->format('M Y'),$date->format('M-Y'),
							$date->format('F y'),$date->format('F-y'),$date->format('M y'),$date->format('M-y')
						];
						foreach($months as &$m){
							$m = strtolower($m);
						}
						if(isset($month_rows[$month])){
							$current_month = $month;
							$row = $month_rows[$month] + $date->format('j');
						}else{
							for($row ; $row <= 420 ; $row++){
								if(in_array(strtolower($object->getCellByColumnAndRow(0, $row)->getFormattedValue()), $months)){
									$current_month = $month;
									$row = $row - 1 + $date->format('j');
									$month_rows[$month] = $row - 1;
									break;
								}
							}
						}
					}
					$today_clockings = $this->getTodayData($current_employee_data, $current_date, $public_holidays, $rest_days, $ot_settings, $round_first_hour_only);
					$in = $today_clockings["in"];
					$out = $today_clockings["out"];
					$ot = $today_clockings["ot"];
					$ot_rd = $today_clockings["ot_rd"];
					$ot_ph1 = $today_clockings["ot_ph1"];
					$ot_ph2 = $today_clockings["ot_ph2"];
					$days = $today_clockings["days"];
					// if($object->getCellByColumnAndRow(4, $row)->getValue() == "" && $in != ""){
					// 	$object->setCellValueByColumnAndRow(4, $row, 1);
					// }
					// $object->getStyle('F'.$row)->getNumberFormat()
					// 	->setFormatCode(
	    //     				PHPExcel_Style_NumberFormat::FORMAT_TEXT
	    // 				);
	    // 			$object->getStyle('G'.$row)->getNumberFormat()
					// 	->setFormatCode(
	    //     				PHPExcel_Style_NumberFormat::FORMAT_TEXT
	    // 				);
					$object->setCellValueByColumnAndRow(5, $row, $in);
					$object->setCellValueByColumnAndRow(6, $row, $out);
					$object->setCellValueByColumnAndRow(7, $row, $days);
					$object->setCellValueByColumnAndRow(8, $row, $ot);
					$object->setCellValueByColumnAndRow(9, $row, $ot_rd);
					$object->setCellValueByColumnAndRow(10, $row, $ot_ph1);
					$object->setCellValueByColumnAndRow(11, $row, $ot_ph2);
					$row++;
				}
			}
			
	        if($i < $sheet_count - 1 ){
	        	$i++;
	        } else {
	        	break;
	        }
	    }

	    $objectExcel->setActiveSheetIndex(0);

		// set file name here
		$file_name = "BMI Report - " . time();

		$object_writer = PHPExcel_IOFactory::createWriter($objectExcel, 'Excel2007');
		$object_writer->setPreCalculateFormulas(false);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $file_name . '.xlsx"');
		setCookie("downloadStarted", 1, time() + 20, '/', "", false, false);
		$object_writer->save('php://output');

	}

	public function getTodayData(&$current_employee_data, $date, $public_holidays, $rest_days, $ot_settings, $round_first_hour_only){
		$today_data = array_filter($current_employee_data, function($obj) use($date) {
	        if ($obj->date == $date) return true;
		    return false;
		});

		$current_employee_data = array_filter($current_employee_data, function($obj) use($date) {
	        if ($obj->date == $date) return false;
		    return true;
		});

		$in = "";
		$out = "";
		$today_data = $d = array_values($today_data)[0];
		$ot = $ot_rd = $ot_ph1 = $ot_ph2 = "";
		$days = $d->days;
		if($d->is_ot){
			if(in_array($d->date,$public_holidays) || $d->is_replaced_ph){
				$ot_ph = $d->work_hours;
				$ot_ph = toDecimal($ot_ph);
				if($ot_ph > 8){
					$ot_ph1 = 8;
					$ot_ph2 = $ot_ph - 8;
				}else{
					$ot_ph1 = $ot_ph;
				}
			}else if (!in_array($d->date,$public_holidays) && (in_array($d->day_name, $rest_days) || $d->is_shift == 'false')){
				$ot_rd = $d->total_hours;
				$ot_rd = round_off_ot($ot_rd, $ot_settings, $round_first_hour_only);
				$ot_rd = toDecimal($ot_rd);
			}else{
				if($d->is_shift == 'false'){
					$ot = $d->overtime_m;
				}else{
					$ot = add_time_minus($d->overtime, $d->overtime_m);
				}
				$ot = toDecimal($ot);
			}
		}

		$today_clockings = $today_data->clockings;
		if($today_clockings){
			if($today_clockings[0]->clock_in != ""){
				$in = $today_clockings[0]->clock_in;
				$in = toDecimal($in);
				if(end($today_clockings)->clock_out != ""){
					$out = end($today_clockings)->clock_out;
					$out = toDecimal($out);
				}
			}
		}
		$ot = $ot == 0 ? "" : $ot;
		$ot_rd = $ot_rd == 0 ? "" : $ot_rd;
		$ot_ph1 = $ot_ph1 == 0 ? "" : $ot_ph1;
		$ot_ph2 = $ot_ph2 == 0 ? "" : $ot_ph2;
		return array("in" => $in, "out" => $out, "ot" => $ot, "ot_rd" => $ot_rd, "ot_ph1" => $ot_ph1, "ot_ph2" => $ot_ph2, "days" => $days);
	}

}