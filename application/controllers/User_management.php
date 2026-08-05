<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User_management extends CI_Controller
{
	public function index()
	{
		// echo "test";
		// die();
	}

	public function login()
	{
		$username = $this->input->post("email");
		$password = md5($this->input->post("password"));

		$query = $this->db->query("SELECT employees.*, companies.name as company_name, companies.current_status as current_status from employees
			LEFT JOIN companies ON employees.company_id = companies.id
			LEFT JOIN roles on employees.role_id = roles.id 
			WHERE employees.employee_status = 'active' AND role_type = 'invotime' AND employees.email =? AND employees.password=?", array($username, $password));

		if (is_null($query->row())) {
			$data["error"] = true;
			$data["company"] = false;

			$this->load->view('login', $data);
		} else {

			$row = $query->row_array();

			// Check if company status is suspended
			if (!empty($row['current_status']) && $row['current_status'] == 'suspended') {
				$data["suspended_error"] = true;
				$data["company"] = false;

				$this->load->view('login', $data);
				return;
			}

			$this->session->set_userdata('antelope_user', $row);
			$bid = get_user()["branch_id"];

			$permissions_level = get_user()["permissions_level"];

			insert_log('AUTH', ['action' => 'Logged,In']);

			if ($permissions_level == "Outlet") {
				redirect("overview?branch_id=$bid");
			} else {
				redirect("overview");
			}
		}
	}

	public function payroll_login()
	{
		$username = $this->input->post("email");
		$password = md5($this->input->post("password"));

		$query = $this->db->query("SELECT companies.*, roles.*, employees.*, roles.permissions as payroll_permissions, companies.name as company_name, branches.name as outlet_name, companies.logo as company_logo from employees
			LEFT JOIN companies ON employees.company_id = companies.id
			LEFT JOIN branches ON employees.branch_id = branches.id
			LEFT JOIN roles on employees.role_id = roles.id 
			WHERE employees.employee_status = 'active' AND role_type = 'payroll' AND employees.email =? AND employees.password=?", array($username, $password));

		if (is_null($query->row())) {
			$data["payroll_error"] = true;
			$data["company"] = false;

			$this->load->view('login', $data);
		} else {

			$row = $query->row_array();

			$this->session->set_userdata('payroll_user', $row);

			redirect("invocore_payroll");
			
		}
	}

	public function logout()
	{
		insert_log('AUTH', ['action' => 'Logged,Out']);
		$this->session->unset_userdata('antelope_user');
		redirect("welcome");
	}

	public function payroll_logout(){
		$this->session->unset_userdata('payroll_user');
		redirect("welcome");
	}
}
