<?php
class Employees extends CI_Controller {

  function __construct()
  {
      parent::__construct();

      if(is_null(get_user())){
        redirect("welcome");
			 //var_dump($this->session->userdata('antelope_user'));
    }
}

public function index(){
    $data['pageTitle'] = "Branch Report";
    $data['active_menu'] = "employees";
    $cid = get_user()["company_id"];

    $bid = get_user()["branch_id"];
    $permissions_level = get_user()["permissions_level"];
    $limit_access_to_department = get_user()["limit_access_to_department"];


    $where_branch_1 = '';
    $where_branch_2 = '';
    $where_branch_3 = '';
    $where_department = '';

    if($permissions_level == "Outlet"){
        $where_branch_1 = " AND employees.branch_id = $bid ";
        $where_branch_2 = " AND id = $bid ";
        $where_branch_3 = " AND permissions_level = 'Personal' ";
    }

    if($limit_access_to_department == 'yes'){
        $department_id = get_user()["department_id"];
        $where_department = " AND employees.department_id = $department_id ";
    }

    //echo $where_department;


    $data['employees'] = $this->db->select('employees.*,roles.job_name,roles.permissions_level,positions.title,departments.name as department_name,branches.name as branch_name')->from('employees')->join('roles','employees.role_id = roles.id','left')->join('positions','employees.position_id = positions.id','left')->join('departments','employees.department_id = departments.id','left')->join('branches','employees.branch_id = branches.id','left')->where('roles.exclude_from_system','no')->where('employees.company_id',$cid)->where("employees.deleted_at is null $where_branch_1 $where_department")->order_by("first_name", "asc")->get()->result();

    //echo $this->db->last_query();

    $data['branches'] = $this->db->select('id,name')->from('branches')->where("company_id = $cid $where_branch_2")->order_by("name", "asc")->get()->result();
    $data['departments'] = $this->db->select('id,name')->from('departments')->where('company_id',$cid)->order_by("name", "asc")->get()->result();
    $data['positions'] = $this->db->select('id,title')->from('positions')->where('company_id',$cid)->order_by("title", "asc")->get()->result();
    $data['roles'] = $this->db->select('id,job_name')->from('roles')->where("company_id = $cid $where_branch_3")->order_by("job_name", "asc")->get()->result();

    //var_dump($data['employees']);
    $this->load->view('header',$data);
    $data["menus"] = get_menus();
    $this->load->view('sidebar',$data);
    $this->load->view('employees');
    $this->load->view('footer');
}

public function getEmployees(){
    $cid = get_user()["company_id"];
    $data['employees'] = $this->db->select('employees.*,roles.job_name,positions.title,departments.name as department_name,branches.name as branch_name')->from('employees')->join('roles','employees.role_id = roles.id','left')->join('positions','employees.position_id = positions.id','left')->join('departments','employees.department_id = departments.id','left')->join('branches','employees.branch_id = branches.id','left')->where('employees.company_id',$cid)->where('employees.deleted_at is null')->get()->result();
    $filtered = $this->load->view('employee_filter',$data,true);
    echo $filtered;
}

public function getSingleEmployee(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $id = $request->id;
    $cid = get_user()["company_id"];
    $emp = $this->db->select('*')->select("date_format(license_expiry,'%d/%m/%Y') as license_expiry",false)->select("date_format(dob,'%d/%m/%Y') as dob",false)->select("date_format(hired_on,'%d/%m/%Y') as hired_on",false)->from('employees')->where('id',$id)->get()->row();
    if($emp->is_ot == 0){
        $emp->is_ot = false;
    }else{
        $emp->is_ot = true;
    }
    if($emp->basic_wage == 0){
        $emp->basic_wage = '';
    }
    $emp->new_password = '';
    
    $bid = get_user()["branch_id"];
    $permissions_level = get_user()["permissions_level"];

    $where_branch_1 = '';
    $where_branch_2 = '';
    $where_branch_3 = '';

    if($permissions_level == "Outlet"){
        $where_branch_1 = " AND employees.branch_id = $bid ";
        $where_branch_2 = " AND id = $bid ";
        $where_branch_3 = " AND permissions_level = 'Personal' ";
    }

    $data['employee'] = $emp;
    $data['branches'] = $this->db->select('id,name')->from('branches')->where("company_id = $cid $where_branch_2")->get()->result();
    $data['departments'] = $this->db->select('id,name')->from('departments')->where('company_id',$cid)->get()->result();
    $data['roles'] = $this->db->select('id,job_name')->from('roles')->where('company_id',$cid)->get()->result();
    $data['positions'] = $this->db->select('id,title')->from('positions')->where('company_id',$cid)->get()->result();

    // echo $this->db->last_query();
    // die();
    echo json_encode($data);
}

public function delete_employee(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $this->db->set('deleted_at','NOW()',false)->where('id',$request->id)->update('employees');
}


public function getPositions(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $cid = get_user()["company_id"];
    $data['positions'] = $this->db->select('id,title')->from('positions')->where('department_id',$request->department_id)->where('company_id',$cid)->get()->result();
    echo json_encode($data);
}

public function save(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $cid = get_user()["company_id"];

    $emp_exist = $this->db->select('id')->from('employees')->where('special_id',$request->special_id)->where('company_id',$cid)->where('deleted_at is null')->get()->result();
    if(count($emp_exist) > 0){
        $data['success'] = false;
        $data['msg'] = 'Employee ID already exists!';
    }else{
        if($request->is_ot == true){
            $request->is_ot = 1;
        }else{
            $request->is_ot = 0;
        }
        if($request->dob != ''){
            $request->dob = str_replace('/', '-', $request->dob);
            $request->dob = date('Y-m-d', strtotime($request->dob));
        }else{
            $request->dob = null;
        }
        if($request->hired_on != ''){
            $request->hired_on = str_replace('/', '-', $request->hired_on);
            $request->hired_on = date('Y-m-d', strtotime($request->hired_on));
        }else{
            $request->hired_on = null;
        }
        if($request->license_expiry != ''){
            $request->license_expiry = str_replace('/', '-', $request->license_expiry);
            $request->license_expiry = date('Y-m-d', strtotime($request->license_expiry));
        }else{
            $request->license_expiry = null;
        }
        $emp_data = array('first_name'=>$request->first_name,
            'sex'=>$request->sex,
            'dob'=>$request->dob,
            'pob'=>$request->pob,
            'company_id'=>$cid,
            'race'=>$request->race,
            'religion'=>$request->religion,
            'nationality'=>$request->nationality,
            'email'=>$request->email,
            'branch_id'=>$request->branch_id,
            'department_id'=>$request->department_id,
            'role_id'=>$request->role_id,
            'position_id'=>$request->position_id,
            'special_id'=>$request->special_id,
            'grade'=>$request->grade,
            'employment_type'=>$request->employment_type,
            'hired_on'=>$request->hired_on,
            'ic_passport'=>$request->ic_passport,
            'perm_address'=>$request->perm_address,
            'perm_address_city'=>$request->perm_address_city,
            'perm_address_state'=>$request->perm_address_state,
            'perm_address_postcode'=>$request->perm_address_postcode,
            'temp_address'=>$request->temp_address,
            'temp_address_city'=>$request->temp_address_city,
            'temp_address_state'=>$request->temp_address_state,
            'temp_address_postcode'=>$request->temp_address_postcode,
            'telephone'=>$request->telephone,
            'mobile'=>$request->mobile,
            'marital_status'=>$request->marital_status,
            'basic_wage'=>$request->basic_wage,
            'epf_no'=>$request->epf_no,
            'socso'=>$request->socso,
            'eis'=>$request->eis,
            'income_tax_no'=>$request->income_tax_no,
            'income_tax_branch'=>$request->income_tax_branch,
            'qr_barcode'=>$request->qr_barcode,
            'bank_name'=>$request->bank_name,
            'bank_account_no'=>$request->bank_account_no,
            'license_class'=>$request->license_class,
            'license_no'=>$request->license_no,
            'license_expiry'=>$request->license_expiry,
            'is_ot'=>$request->is_ot,
            'employee_type'=>$request->employee_type,
            'password' => md5($request->password),
            'compassionate_leaves' => $request->compassionate_leaves,
            'paternity_leaves' => $request->paternity_leaves,
            'marriage_leaves' => $request->marriage_leaves,
            'hospitalisation_leaves' => $request->hospitalisation_leaves,
            'study_leaves' => $request->study_leaves,
            'replacement_leaves' => $request->replacement_leaves,
            'unpaid_leaves' => $request->unpaid_leaves,
            'emergency_leaves' => $request->emergency_leaves
        );
        if($this->db->insert('employees',$emp_data)){
            $data['success'] = true;
            $data['msg'] = "Employee created successfully!";
        }else{
            $data['success'] = false;
            $data['msg'] = "Employee could not add!";
        }

    }
    echo json_encode($data);
}

public function getDeductions(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $employee_type = $request->employee_type;
    $basic_wage = $request->basic_wage;
    $eis = 0;
    $socso = 0;
    $epf = 0;
    if($basic_wage != 0){
        $eis_row = $this->db->select('employee')->from('eis')->where('start <', $basic_wage)->where('end >=', $basic_wage)->get()->row();
        if($eis_row){
            $eis = $eis_row->employee;
        }

        $socso_row = $this->db->select('employee')->from('socso')->where('start <', $basic_wage)->where('end >=', $basic_wage)->get()->row();
        if($socso_row){
            $socso = $socso_row->employee;
        }

        $epf_row = $this->db->select('employee')->from('epf_'.$employee_type)->where('start <= ', $basic_wage)->where('end >= ',$basic_wage)->get()->row();
        if($epf_row){
            $epf = $epf_row->employee;
        }
    }

    $data["eis"] = $eis;
    $data["socso"] = $socso;
    $data["epf"] = $epf;

    
    echo json_encode($data);
}

public function update(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $id = $request->id;

    if($request->is_ot == true){
        $request->is_ot = 1;
    }else{
        $request->is_ot = 0;
    }
    if($request->dob != ''){
        $request->dob = str_replace('/', '-', $request->dob);
        $request->dob = date('Y-m-d', strtotime($request->dob));
    }else{
        $request->dob = null;
    }
    if($request->hired_on != ''){
        $request->hired_on = str_replace('/', '-', $request->hired_on);
        $request->hired_on = date('Y-m-d', strtotime($request->hired_on));
    }else{
        $request->hired_on = null;
    }
    if($request->license_expiry != ''){
        $request->license_expiry = str_replace('/', '-', $request->license_expiry);
        $request->license_expiry = date('Y-m-d', strtotime($request->license_expiry));
    }else{
        $request->license_expiry = null;
    }
    if($request->new_password != ''){
        $new_password = md5($request->new_password);
    }else{
        $new_password = $request->password;
    }
    $emp_data = array('first_name'=>$request->first_name,
        'sex'=>$request->sex,
        'dob'=>$request->dob,
        'pob'=>$request->pob,
        'race'=>$request->race,
        'religion'=>$request->religion,
        'nationality'=>$request->nationality,
        'email'=>$request->email,
        'branch_id'=>$request->branch_id,
        'department_id'=>$request->department_id,
        'role_id'=>$request->role_id,
        'position_id'=>$request->position_id,
        'special_id'=>$request->special_id,
        'grade'=>$request->grade,
        'employment_type'=>$request->employment_type,
        'hired_on'=>$request->hired_on,
        'ic_passport'=>$request->ic_passport,
        'perm_address'=>$request->perm_address,
        'perm_address_city'=>$request->perm_address_city,
        'perm_address_state'=>$request->perm_address_state,
        'perm_address_postcode'=>$request->perm_address_postcode,
        'temp_address'=>$request->temp_address,
        'temp_address_city'=>$request->temp_address_city,
        'temp_address_state'=>$request->temp_address_state,
        'temp_address_postcode'=>$request->temp_address_postcode,
        'telephone'=>$request->telephone,
        'mobile'=>$request->mobile,
        'marital_status'=>$request->marital_status,
        'basic_wage'=>$request->basic_wage,
        'epf_no'=>$request->epf_no,
        'socso'=>$request->socso,
        'eis'=>$request->eis,
        'income_tax_no'=>$request->income_tax_no,
        'income_tax_branch'=>$request->income_tax_branch,
        'qr_barcode'=>$request->qr_barcode,
        'bank_name'=>$request->bank_name,
        'bank_account_no'=>$request->bank_account_no,
        'license_class'=>$request->license_class,
        'license_no'=>$request->license_no,
        'license_expiry'=>$request->license_expiry,
        'is_ot'=>$request->is_ot,
        'employee_type'=>$request->employee_type,
        'password' => $new_password,
        'compassionate_leaves' => $request->compassionate_leaves,
        'paternity_leaves' => $request->paternity_leaves,
        'marriage_leaves' => $request->marriage_leaves,
        'hospitalisation_leaves' => $request->hospitalisation_leaves,
        'study_leaves' => $request->study_leaves,
        'replacement_leaves' => $request->replacement_leaves,
        'unpaid_leaves' => $request->unpaid_leaves,
        'emergency_leaves' => $request->emergency_leaves);
    $this->db->where('id',$id)->update('employees',$emp_data);
    $data["success"] = true;
    echo json_encode($data);

}

}