<?php
class Admin_panel extends CI_Controller {

	public function invotime1704()
	{
        $this->db->select('companies.id,companies.name,address,phone,organization_id,package,additional_staff,packages.id as package_id,packages.name as package_name');
        // $this->db->limit(3);
        $this->db->order_by('companies.id', 'DESC');
        $this->db->join('packages', 'packages.id = companies.package', 'left');
        $data['companies'] = $this->db->get('companies')->result();
        // $this->db->select('id,name');
        $data['packages'] = $this->db->get('packages')->result();

        $data['companies_xcrud'] = $this->get_company_xcrud();
        $data['announcements_xcrud'] = $this->get_announcements_xcrud();

        $data['resellers_xcrud'] = $this->get_resellers_xcrud();

        $this->load->view('new_company', $data);
	}

    function getSubscriptions(){
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $company_id = $request->company_id;
        $company_name = $this->db->select('name')->from('companies')->where('id', $company_id)->get()->row()->name;
        $this->load->helper('xcrud');
        $xcrud = xcrud_get_instance();
        $xcrud->table('subscriptions');
        $xcrud->table_name($company_name." Subscriptions");
        $xcrud->where('company_id', $company_id);
        $xcrud->columns('company_id,created_at,updated_at,deleted_at', true);
        $xcrud->fields('company_id,created_at,updated_at,deleted_at', true);
        $xcrud->pass_var('company_id',$company_id);
        $xcrud->unset_print();
        $xcrud->unset_csv();
        $xcrud->unset_search();
        $xcrud->unset_pagination();
        $xcrud->unset_limitlist();
        $xcrud->unset_sortable();
        $data["table"] = $xcrud->render();
        $subscriptions = $this->load->view('subscriptions', $data, TRUE);
        echo $subscriptions;
    }

    function resetPassword(){
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);

        $email = $request->email;
        $password = $request->password;

        $data = array();

        $user = $this->db->select('id')->from('employees')->where('email', $email)->get()->row();

        if($user){
            $this->db->set('password', md5($password))->where('id', $user->id)->update('employees');
            $data["success"] = true;
            $data["message"] = "Password reset successfully for ".$email;
        }else{
            $data["success"] = false;
            $data["message"] = $email." does not exist";
        }

        echo json_encode($data);
    }

	function new_company(){
		$postdata = file_get_contents("php://input");
    	$request = json_decode($postdata);
    	$company_name = $request->name;
    	$company_address = $request->address;
    	$company_phone = $request->phone;
    	$admin_name = $request->admin;
    	$admin_email = $request->email;
    	$admin_password = $request->password;
    	$package = $request->package;
    	$additional_staff = $request->additional_staff;
        $organization_id = $request->organization_id;

    	$company_data = array("name" => $company_name, "address" => $company_address, "phone" => $company_phone, "package" => $package, "additional_staff" => $additional_staff, "organization_id" => $organization_id);

    	$row = $this->db->select('*')->from('employees')->where('email', $admin_email)->where('deleted_at is null')->get()->row();
    	if($row){
    		$data["success"] = false;
    		$data["message"] = "Email already exists";
    	}else{
    		$this->db->insert('companies', $company_data);
    		$company_id = $this->db->insert_id();
            
    		$this->db->insert('roles', array("company_id" => $company_id, "job_name" => "Company Admin", "permissions" => "everything", "permissions_level" => "Company", "is_emp_summary_editable" => "yes", "exclude_from_system" => "yes"));
    		$role_id = $this->db->insert_id();

    		$this->db->insert('roles', array("company_id" => $company_id, "job_name" => "Outlet Admin", "permissions" => "everything", "permissions_level" => "Outlet", "company_id" => $company_id, "is_emp_summary_editable" => "yes", "exclude_from_system" => "yes"));

            $this->db->insert('roles', array("company_id" => $company_id, "job_name" => "Employee", "permissions_level" => "Personal"));

    		$this->db->insert('employees', array("first_name" => $admin_name, "email" => $admin_email, "password" => md5($admin_password), "company_id" => $company_id, "role_id" => $role_id));

            $this->db->insert('days_settings', array("from_hour" => 01, "to_hour" => 04, "days" => 0.5, "company_id" => $company_id));

            $this->db->insert('days_settings', array("from_hour" => 04, "to_hour" => 24, "days" => 01, "company_id" => $company_id));

            $this->db->insert('company_working_hours', array("company_id" => $company_id, "total_hours" => "08:00:00", "half_hours" => "04:00:00"));

            $data = array(
                        array('company_id'=>$company_id,'name'=>'Annual Leave','color'=>'Blue','code'=>'AL','is_paid'=>'yes','half_day'=>'no','void_late_in'=>'no','void_early_out'=>'no','is_leave'=>'yes','is_approved'=>1),
                        array('company_id'=>$company_id,'name'=>'Half Day Paid','color'=>'Blue','code'=>'HDP','is_paid'=>'yes','half_day'=>'yes','void_late_in'=>'yes','void_early_out'=>'yes','is_leave'=>'yes','is_approved'=>1),
                        array('company_id'=>$company_id,'name'=>'Unpaid Leave','color'=>'Orange','code'=>'UL','is_paid'=>'no','half_day'=>'no','void_late_in'=>'no','void_early_out'=>'no','is_leave'=>'yes','is_approved'=>1),
                        array('company_id'=>$company_id,'name'=>'Half Day Unpaid','color'=>'Orange','code'=>'HDU','is_paid'=>'no','half_day'=>'yes','void_late_in'=>'yes','void_early_out'=>'yes','is_leave'=>'yes','is_approved'=>1),
                        array('company_id'=>$company_id,'name'=>'Medical Leave','color'=>'Red','code'=>'MC','is_paid'=>'yes','half_day'=>'no','void_late_in'=>'no','void_early_out'=>'no','is_leave'=>'yes','is_approved'=>1),
                        array('company_id'=>$company_id,'name'=>'Hospitalization Leave','color'=>'Purple','code'=>'HL','is_paid'=>'yes','half_day'=>'no','void_late_in'=>'no','void_early_out'=>'no','is_leave'=>'yes','is_approved'=>1),
                        array('company_id'=>$company_id,'name'=>'Maternity Leave','color'=>'Pink','code'=>'ML','is_paid'=>'yes','half_day'=>'no','void_late_in'=>'no','void_early_out'=>'no','is_leave'=>'yes','is_approved'=>1)
                    );

            $this->db->insert_batch('shifts', $data);

    		$data["success"] = true;
    		$data["message"] = "New company created successfully";
    	}
    	echo json_encode($data);
	}

    function get_company_settings($id)
    {
        $this->db->where('id', $id);
        $company = $this->db->get('companies')->row();
        // print_r($company);die;
        return $company;
    }

    function get_package_settings($id)
    {
        $this->db->where('id', $id);
        $package = $this->db->get('packages')->row();
        echo json_encode($package);
    }
    
    function update_package_settings()
    {
        $package_id = $this->input->post('id');
        $package_name = $this->input->post('name');
    	$max_outlets = $this->input->post('max_outlets');
    	$max_active_staff = $this->input->post('max_active_staff');

    	$package_data = array("name" => $package_name, "max_outlets" => $max_outlets, "max_active_staff" => $max_active_staff);
        echo json_encode($package_data);
        $this->db->where('id', $package_id);
        $this->db->update('packages', $package_data);
    }

	function new_outlet(){
		$postdata = file_get_contents("php://input");
    	$request = json_decode($postdata);
    	$outlet_name = $request->name;
    	$outlet_address = $request->address;
    	$outlet_phone = $request->phone;
    	$admin_name = $request->admin;
    	$admin_email = $request->email;
    	$admin_password = $request->password;
    	$company_id = $request->company;
        
        $cid = get_user()["company_id"];
        $this->db->select('companies.package, packages.max_outlets');
        $this->db->join('packages', 'packages.id = companies.package', 'left');
        $this->db->where('companies.id', $cid);
        $company_details = $this->db->get('companies')->row();
        // print_r($company_details->max_outlets);die;
        if ($company_details->max_outlets != 0) {
            $this->db->where('company_id', $cid);
            $branches_of_company = $this->db->get('branches')->result();
            $branches_of_company_count = count($branches_of_company);
            // echo $employees_of_company_count;die;
            if ($branches_of_company_count >= $company_details->max_outlets) {
                $data['success'] = false;
                $data['message'] = "Outlet limit is full!";
                echo json_encode($data);die;
            }
        }

    	$outlet_data = array("company_id" => $company_id, "name" => $outlet_name, "address" => $outlet_address, "phone" => $outlet_phone);

    	$row = $this->db->select('*')->from('employees')->where('email', $admin_email)->where('deleted_at is null')->get()->row();
    	if($row){
    		$data["success"] = false;
    		$data["message"] = "Email already exists";
    	}else{
    		$this->db->insert('branches', $outlet_data);
    		$outlet_id = $this->db->insert_id();

    		$role = $this->db->select('id')->from('roles')->where('company_id', $company_id)->where('permissions', "everything")->where('permissions_level', "Outlet")->where('limit_access_to_department', 'no')->get()->row();
    		if($role){
    			$role_id = $role->id;
    		}else{
    			$this->db->insert('roles', array("company_id" => $company_id, "job_name" => "Outlet Admin", "permissions" => "everything", "permissions_level" => "Outlet"));
    			$role_id = $this->db->insert_id();
    		}


    		$this->db->insert('employees', array("first_name" => $admin_name, "email" => $admin_email, "password" => md5($admin_password), "branch_id" => $outlet_id, "role_id" => $role_id, "company_id" => $company_id));

    		$data["success"] = true;
    		$data["message"] = "New outlet created successfully";
    	}
    	echo json_encode($data);
	}

    function add_package(){

        $package_name = $this->input->post('name');
    	$max_outlets = $this->input->post('max_outlets');
    	$max_active_staff = $this->input->post('max_active_staff');

    	$package_data = array("name" => $package_name, "max_outlets" => $max_outlets, "max_active_staff" => $max_active_staff);
        // print_r($package_data);die;
        $check = $this->db->insert('packages', $package_data);
        if ($check) {
            $data["success"] = true;
            $data["message"] = "New package created successfully";
            echo json_encode($data);
            return redirect(base_url().'admin_panel/invotime1704#');
        }
	}

    function delete_package($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('packages');
        return redirect(base_url().'admin_panel/invotime1704#');
    }

    public function get_packages_xcrud()
    {
        $this->load->helper('xcrud');
        $xcrud = xcrud_get_instance();
        $xcrud->table('packages');
        $xcrud->validation_required('name,max_outlets,max_active_staff');
        $xcrud->fields('created_at, updated_at', true);
        $xcrud->columns('updated_at', true);
        return $xcrud->render();
        // $data['packages'] = $xcrud->render();
        // $packages = $this->load->view('packages_xcrud', $data, TRUE);
        // echo $packages;
    }

    public function get_company_xcrud()
    {
        $this->load->helper('xcrud');
        $xcrud1 = xcrud_get_instance();
        $xcrud1->table('companies');
        $xcrud1->order_by('created_at','desc');
        $xcrud1->relation('package', 'packages', 'id', 'name');
        $xcrud1->fields('name, address, phone, organization_id, package, active_staffs, outlets, additional_staff, cut_off_time, status, start_date, reseller_id', false, false, 'view');
        $xcrud1->fields('name, address, phone, organization_id, package, additional_staff, cut_off_time, status, start_date, reseller_id', false, false, 'create');
        $xcrud1->fields('name, address, phone, organization_id, package, additional_staff, cut_off_time, status, start_date, reseller_id', false, false, 'edit');
        $xcrud1->columns('name, address, phone, organization_id, package, active_staffs, outlets, additional_staff, cut_off_time, status, start_date, reseller_id', false);

        $xcrud1->subselect('active_staffs', 'id');
        $xcrud1->label("active_staffs", "Active Staffs");
        $xcrud1->column_callback('active_staffs', 'get_total_active_staffs');

        $xcrud1->subselect('outlets', 'id');
        $xcrud1->label("outlets", "Outlets");
        $xcrud1->column_callback('outlets', 'get_total_outlets');

        // reseller_id relation
        $xcrud1->relation('reseller_id', 'resellers', 'id', 'name');
        $xcrud1->label('reseller_id', 'Reseller');
        $xcrud1->unset_add();
        return $xcrud1->render();
        // // $xcrud1->unset_remove();
        // $data['companies'] = $xcrud1->render();
        // $companies = $this->load->view('companies_xcrud', $data, TRUE);
        // echo $companies;
    }

	function getCompanies(){
		$data["companies"] = $this->db->select('id, name')->from('companies')->get()->result();
		$data["success"] = true;
		echo json_encode($data);
	}

    function getOutlets(){
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $company_id = $request->id;
        $data["outlets"] = $this->db->select('id, name')->from('branches')->where('company_id', $company_id)->get()->result();
        $data["success"] = true;
        echo json_encode($data);
    }

    function makePayrollAdmin(){
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $company_id = $request->company;
        $branch_id = $request->outlet;
        $first_name = $request->admin;
        $email = $request->email;
        $password = $request->password;

        $type = $request->type;

        $exist = $this->db->select('id')->from('employees')->where('email', $email)->get()->row();

        if($exist){
            $data["success"] = false;
            $data["message"] = "Email already exist!";
        }else{

            if($type == "company"){
                $role_exist = $this->db->select('id')->from('roles')->where('company_id', $company_id)->where('permissions_level', "Company")->where('role_type', "payroll")->where("senior_staff_access", "yes")->where("check_payroll_access", "yes")->where("approve_payroll_access", "yes")->where("exclude_from_system", "yes")->get()->row();
                if($role_exist){
                    $role_id = $role_exist->id;
                }else{
                    $this->db->insert('roles', array("company_id" => $company_id, "job_name" => "Company Admin - Payroll", "permissions" => "everything", "permissions_level" => "Company", "role_type" => "payroll", "senior_staff_access" => "yes", "check_payroll_access" => "yes", "approve_payroll_access" => "yes", "exclude_from_system" => "yes"));
                    $role_id = $this->db->insert_id();
                }
            }else if($type == "outlet"){
                $role_exist = $this->db->select('id')->from('roles')->where('company_id', $company_id)->where('permissions_level', "Outlet")->where('role_type', "payroll")->where("senior_staff_access", "yes")->where("check_payroll_access", "yes")->where("approve_payroll_access", "yes")->where("exclude_from_system", "yes")->get()->row();
                if($role_exist){
                    $role_id = $role_exist->id;
                }else{
                    $this->db->insert('roles', array("company_id" => $company_id, "job_name" => "Outlet Admin - Payroll", "permissions" => "everything", "permissions_level" => "Outlet", "role_type" => "payroll", "senior_staff_access" => "yes", "check_payroll_access" => "yes", "approve_payroll_access" => "yes", "exclude_from_system" => "yes"));
                    $role_id = $this->db->insert_id();
                }
            }

            $employee_data = array("company_id" => $company_id,
                                    "branch_id" => $branch_id,
                                    "first_name" => $first_name,
                                    "email" => $email,
                                    "password" => md5($password),
                                    "role_id" => $role_id);
            $this->db->insert('employees', $employee_data);

            $data["success"] = true;
            $data["message"] = "Payroll admin added successfully!";
        }

        
        echo json_encode($data);
    }

    public function makeLeaveAdmin(){
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $company_id = $request->company;
        $branch_id = $request->outlet;
        $first_name = $request->admin;
        $email = $request->email;
        $password = $request->password;

        $type = $request->type;

        $exist = $this->db->select('id')->from('employees')->where('email', $email)->get()->row();

        if($exist){
            $data["success"] = false;
            $data["message"] = "Email already exist!";
        }else{
            if($type == "company"){
                $role_exist = $this->db->select('id')->from('roles')->where('company_id', $company_id)->where('permissions_level', "Company")->where('role_type', "leave")->where("exclude_from_system", "yes")->get()->row();
                if($role_exist){
                    $role_id = $role_exist->id;
                }else{
                    $this->db->insert('roles', array("company_id" => $company_id, "job_name" => "Company Admin - Leave", "permissions" => "everything", "permissions_level" => "Company", "role_type" => "leave", "exclude_from_system" => "yes"));
                    $role_id = $this->db->insert_id();
                }
            }else if($type == "outlet"){
                $role_exist = $this->db->select('id')->from('roles')->where('company_id', $company_id)->where('permissions_level', "Outlet")->where('role_type', "leave")->where("exclude_from_system", "yes")->get()->row();
                if($role_exist){
                    $role_id = $role_exist->id;
                }else{
                    $this->db->insert('roles', array("company_id" => $company_id, "job_name" => "Outlet Admin - Leave", "permissions" => "everything", "permissions_level" => "Outlet", "role_type" => "leave", "exclude_from_system" => "yes"));
                    $role_id = $this->db->insert_id();
                }
            }

            $employee_data = array("company_id" => $company_id,
                                    "branch_id" => $branch_id,
                                    "first_name" => $first_name,
                                    "email" => $email,
                                    "password" => md5($password),
                                    "role_id" => $role_id);
            $this->db->insert('employees', $employee_data);

            $data["success"] = true;
            $data["message"] = "Leave admin added successfully!";
        }

        
        echo json_encode($data);
    }

    public function get_announcements_xcrud()
    {
        $this->load->helper('xcrud');
        $xcrud1 = xcrud_get_instance();
        $xcrud1->table('announcements');
        $xcrud1->order_by('created_at','desc');
        $xcrud1->relation('package', 'packages', 'id', 'name');
        $xcrud1->fields('title, announcement, active');
        $xcrud1->columns('title, announcement, active');
        $xcrud1->change_type('active', 'select', '1', array('1' => 'Yes', '0' => 'No'));
        return $xcrud1->render();
        // $data['companies'] = $xcrud1->render();
        // $companies = $this->load->view('companies_xcrud', $data, TRUE);
        // echo $companies;
    }

    public function get_resellers_xcrud()
    {
        $this->load->helper('xcrud');
        $xcrud1 = xcrud_get_instance();
        $xcrud1->table('resellers');
        $xcrud1->fields('name, pic, contact, state');
        $xcrud1->columns('name, pic, contact, state');
        $xcrud1->label('pic', 'PIC');
        return $xcrud1->render();
    }

}