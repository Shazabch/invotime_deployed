<?php
class New_client extends CI_Controller {

	function __construct()
    {
      parent::__construct();

			
			//var_dump(get_user());
    }

	public function Index()
	{
		
		
		$code = $this->input->get("code");
		$company = $this->input->get("company");
		$outlet = $this->input->get("outlet");
		$company_admin_email = $this->input->get("company_admin_email");
		$outlet_admin_email = $this->input->get("outlet_admin_email");

		if($code != "nashnash"){
			die("Unauthorized Access");
		}


		//Insert company
		$query = $this->db->query("INSERT INTO `companies` (`industry_id`, `name`, `address`, `phone`, `pic`, `pic_contact`, `logo`, `normal_weekend`, `public_holiday_normal`, `public_holiday_weekend`, `normal_weekend_overtime`, `public_holiday_normal_overtime`, `public_holiday_weekend_overtime`) VALUES(NULL, '$company', 'Address', '+60-00-00000', 'PIC', '+60-00-00000', 'sample_logo.png', 0, 0, 0, 0, 0, 0)");

		$insert_company_id = $this->db->insert_id();

		//Insert outlet
		$query = $this->db->query("INSERT INTO `branches` (`company_id`, `name`, `address`, `timezone`, `phone`, `pic`, `pic_contact`, `logo_big`, `logo_small`, `weather_widget`) VALUES($insert_company_id, '$outlet', 'Address', 'Asia/Kuala_Lumpur', '+60-00-0000', 'pic', '0000', 'sample_logo.png', 'sample_logo.png', '<a class=\"weatherwidget-io\" href=\"https://forecast7.com/en/2d91101d47/telok-panglima-garang/\" data-label_1=\"TELOK PANGLIMA GARANG\" data-label_2=\"WEATHER\" data-theme=\"pure\" >TELOK PANGLIMA GARANG WEATHER</a>\r\n<script>\r\n!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=\'https://weatherwidget.io/js/widget.min.js\';fjs.parentNode.insertBefore(js,fjs);}}(document,\'script\',\'weatherwidget-io-js\');\r\n</script>')");

		$insert_outlet_id = $this->db->insert_id();

		//Insert roles
		$query = $this->db->query("INSERT INTO `roles` (`company_id`, `job_name`, `permissions`, `permissions_level`, `limit_access_to_department`, `exclude_from_system`) VALUES ($insert_company_id, 'Company Admin', 'everything', 'Company', 'no', 'yes')");

		$insert_company_role_id = $this->db->insert_id();

		$query = $this->db->query("INSERT INTO `roles` (`company_id`, `job_name`, `permissions`, `permissions_level`, `limit_access_to_department`, `exclude_from_system`) VALUES ($insert_company_id, 'Outlet Admin', 'everything', 'Outlet', 'no', 'no')");

		$insert_branch_role_id = $this->db->insert_id();

		// $query = $this->db->query("INSERT INTO `roles` (`company_id`, `job_name`, `permissions`, `permissions_level`, `limit_access_to_department`, `exclude_from_system`) VALUES ($insert_company_id, 'Outlet Admin', 'everything', 'Outlet', 'no', 'no')");

		// $insert_employee_role_id = $this->db->insert_id();

		//Insert department
		$query = $this->db->query("INSERT INTO `departments` (`company_id`, `name`, `location`) VALUES ($insert_company_id, 'Sys Admin Dep', NULL)");

		$insert_department_id = $this->db->insert_id();

		//Insert position
		$query = $this->db->query("INSERT INTO `positions` (`company_id`, `department_id`, `title`, `description`) VALUES ($insert_company_id, $insert_department_id, 'Sys Admin Pos', 'Position Description')");

		$insert_position_id = $this->db->insert_id();

		//Insert device
		$query = $this->db->query("INSERT INTO `devices` (`mac_address`, `company_id`, `branch_id`, `location`, `coordinate`) VALUES ('0000000000$insert_company_id', $insert_company_id, $insert_outlet_id, 'Sample Device Location', '0,0')");

		$insert_device_id = $this->db->insert_id();

		//Insert company admin
		$query = $this->db->query("INSERT INTO `employees` (`special_id`, `user_device_id`, `sync_action`, `position_id`, `department_id`, `departments_access`, `role_id`, `company_id`, `branch_id`, `email`, `password`, `email_verified`, `first_name`, `last_name`, `dob`, `pob`, `sex`, `race`, `religion`, `nationality`, `address`, `temp_address`, `mobile`, `telephone`, `ic_passport`, `epf_no`, `bank_account_no`, `bank_name`, `license_class`, `license_no`, `license_expiry`, `photo`, `hired_on`, `is_ot`, `grade`, `api_token`, `fcm_token`, `permissions`, `qr_barcode`, `employment_type`, `perm_address`, `perm_address_postcode`, `perm_address_city`, `perm_address_state`, `temp_address_postcode`, `temp_address_city`, `temp_address_state`, `marital_status`, `basic_wage`, `socso`, `eis`, `income_tax_no`, `income_tax_branch`, `recovery_key`, `face_data`, `fingerprint_data`, `employee_type`, `compassionate_leaves`, `paternity_leaves`, `marriage_leaves`, `hospitalisation_leaves`, `study_leaves`, `replacement_leaves`, `unpaid_leaves`, `emergency_leaves`) VALUES ('CADMIN00$insert_company_id', NULL, 'SetUserData', $insert_position_id, $insert_department_id, '', $insert_company_role_id, $insert_company_id, $insert_outlet_id, '$company_admin_email', '482c811da5d5b4bc6d497ffa98491e38', NULL, 'Company Admin', 'Admin', '1970-01-01', '', 'Male', '', '', '', NULL, '', '', '', '', 0, '', '', '', '', '0000-00-00', 'avatar.png', '2017-09-17', 0, 'HQ', NULL, NULL, NULL, 'CADMIN00$insert_company_id', 'full_time', '', '', '', '', '', '', '', '', 0, 0, 0, '', '', '', NULL, NULL, 'm', 0, 0, 0, 0, 0, 0, 0, 0)");

		$insert_company_admin_id = $this->db->insert_id();

		//Insert outlet admin
		$query = $this->db->query("INSERT INTO `employees` (`special_id`, `user_device_id`, `sync_action`, `position_id`, `department_id`, `departments_access`, `role_id`, `company_id`, `branch_id`, `email`, `password`, `email_verified`, `first_name`, `last_name`, `dob`, `pob`, `sex`, `race`, `religion`, `nationality`, `address`, `temp_address`, `mobile`, `telephone`, `ic_passport`, `epf_no`, `bank_account_no`, `bank_name`, `license_class`, `license_no`, `license_expiry`, `photo`, `hired_on`, `is_ot`, `grade`, `api_token`, `fcm_token`, `permissions`, `qr_barcode`, `employment_type`, `perm_address`, `perm_address_postcode`, `perm_address_city`, `perm_address_state`, `temp_address_postcode`, `temp_address_city`, `temp_address_state`, `marital_status`, `basic_wage`, `socso`, `eis`, `income_tax_no`, `income_tax_branch`, `recovery_key`, `face_data`, `fingerprint_data`, `employee_type`, `compassionate_leaves`, `paternity_leaves`, `marriage_leaves`, `hospitalisation_leaves`, `study_leaves`, `replacement_leaves`, `unpaid_leaves`, `emergency_leaves`) VALUES ('OADMIN00$insert_company_id', NULL, 'SetUserData', $insert_position_id, $insert_department_id, '', $insert_branch_role_id, $insert_company_id, $insert_outlet_id, '$outlet_admin_email', '482c811da5d5b4bc6d497ffa98491e38', NULL, 'Outlet Admin', 'Admin', '1970-01-01', '', 'Male', '', '', '', NULL, '', '', '', '', 0, '', '', '', '', '0000-00-00', 'avatar.png', '2017-09-17', 0, 'HQ', NULL, NULL, NULL, 'OADMIN00$insert_company_id', 'full_time', '', '', '', '', '', '', '', '', 0, 0, 0, '', '', '', NULL, NULL, 'm', 0, 0, 0, 0, 0, 0, 0, 0)");

		$insert_outlet_admin_id = $this->db->insert_id();




		echo "Done $company_admin_email - $outlet_admin_email";
		

	}


}


?>