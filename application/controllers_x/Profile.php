<?php
class Profile extends CI_Controller {

  function __construct()
  {
      parent::__construct();

      if(is_null(get_user())){
        redirect("welcome");
			 //var_dump($this->session->userdata('antelope_user'));
    }
}

public function index($id){
    $data['pageTitle'] = "Branch Report";
    $data['active_menu'] = "employees";
    $this->load->view('header',$data);
    $data["menus"] = get_menus();
    $this->load->view('sidebar',$data);
    $emp = $this->db->select('e.*,e.id,first_name,p.title,special_id,r.job_name,d.name as department,b.name as branch,mobile,email')->select('date_format(dob,"%D %b") as bd',false)->from('employees e')->join('positions p','e.position_id = p.id')->join('roles r','e.role_id = r.id')->join('departments d','e.department_id = d.id')->join('branches b','e.branch_id = b.id')->where('e.id',$id)->get()->row();
    // if($emp->mobile==''){
    //     $emp->mobile = '-';
    // }
    // if($emp->email==''){
    //     $emp->email = '-';
    // }

    

    foreach ($emp as $key => $value) {
        if(trim($emp->$key) == "" || $emp->$key == null){
            $emp->$key = "-";
        }
    }

    if($emp->employment_type == "full_time"){
        $emp->employment_type = "Full Time";
    }else if($emp->employment_type == "part_time"){
        $emp->employment_type = "Part Time";
    }

    $emp->marital_status = ucfirst($emp->marital_status);
    
    if($emp->employee_type == "n"){
        $emp->employee_type = "Non Malaysian";
    }else if($emp->employee_type == "m"){
        $emp->employee_type = "Malaysian";
    }

    if($emp->is_ot == 0){
        $emp->is_ot = "No";
    }else if($emp->is_ot == 1){
        $emp->is_ot = "Yes";
    }
    
    $data['emp'] = $emp;
    //var_dump($emp);
    $this->load->view('profile',$data);
    $this->load->view('footer');
}

public function get_emp_data(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $id = $request->id;
    $data["family"] = $this->db->select('*')->from('family_members')->where('employee_id',$id)->get()->result();
    $data["contacts"] = $this->db->select('*')->from('emergency_contacts')->where('employee_id',$id)->get()->result();
    $data["languages"] = $this->db->select('*')->from('languages')->where('employee_id',$id)->get()->result();
    $data["incentives"] = $this->db->select('*')->from('incentives')->where('employee_id',$id)->get()->result();
    $data["allowances"] = $this->db->select('*')->from('allowances')->where('employee_id',$id)->get()->result();
    $data["skills"] = $this->db->select('*')->from('skills')->where('employee_id',$id)->get()->result();
    $data["educations"] = $this->db->select('*')->select('date_format(period_from,"%Y") as start',false)->select('date_format(period_to,"%Y") as end',false)->from('qualifications')->where('employee_id',$id)->get()->result();
    $data["experience"] = $this->db->select('*')->select('date_format(period_from,"%b %Y") as start',false)->select('date_format(period_to,"%b %Y") as end',false)->from('employment_history')->where('employee_id',$id)->get()->result();
    echo json_encode($data);
}

public function save_family(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $family_data = array('employee_id'=>$request->emp_id,'first_name'=>$request->first_name,'last_name'=>$request->last_name,'relation'=>$request->relation,'age'=>$request->age,'mobile'=>$request->mobile,'job'=>$request->job);
    if($this->db->insert('family_members',$family_data)){
        $data["success"] = true;
        $data["family"] = $this->db->select('*')->from('family_members')->where('employee_id',$request->emp_id)->get()->result();
        $data["msg"] = "New family member added successfully!";
    }else{
        $data["success"] = false;
        $data["msg"] = "Family member could not add!";
    }
    echo json_encode($data);
}

public function save_skill(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $skill_data = array('employee_id'=>$request->emp_id,'skill'=>$request->skill,'level'=>$request->level,'notes'=>$request->notes);
    if($this->db->insert('skills',$skill_data)){
        $data["success"] = true;
        $data["skills"] = $this->db->select('*')->from('skills')->where('employee_id',$request->emp_id)->get()->result();
        $data["msg"] = "New skill added successfully!";
    }else{
        $data["success"] = false;
        $data["msg"] = "Skill could not add!";
    }
    echo json_encode($data);
}

public function update_skill(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $skill_data = array('skill'=>$request->skill,'level'=>$request->level,'notes'=>$request->notes);
    if($this->db->where('id',$request->id)->update('skills',$skill_data)){
        $data["success"] = true;
        $data["skills"] = $this->db->select('*')->from('skills')->where('employee_id',$request->emp_id)->get()->result();
        $data["msg"] = "Skill updated successfully!";
    }else{
        $data["success"] = false;
        $data["msg"] = "Skill could not update!";
    }
    echo json_encode($data);
}

public function save_education(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $request->period_from = str_replace('/', '-', $request->period_from);
    $request->period_from = date('Y-m-d', strtotime($request->period_from));
    $request->period_to = str_replace('/', '-', $request->period_to);
    $request->period_to = date('Y-m-d', strtotime($request->period_to));
    $education_data = array('employee_id'=>$request->emp_id,'institution'=>$request->institution,'country'=>$request->country,'course_field'=>$request->course_field,'period_from'=>$request->period_from,'period_to'=>$request->period_to,'highest_qualification_attained'=>$request->highest_qualification_attained);
    if($this->db->insert('qualifications',$education_data)){
        $data["success"] = true;
        $data["educations"] = $this->db->select('*')->select('date_format(period_from,"%Y") as start',false)->select('date_format(period_to,"%Y") as end',false)->from('qualifications')->where('employee_id',$request->emp_id)->get()->result();
        $data["msg"] = "New qualification added successfully!";
    }else{
        $data["success"] = false;
        $data["msg"] = "Qualification could not add!";
    }
    echo json_encode($data);
}
public function save_experience(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $request->period_from = str_replace('/', '-', $request->period_from);
    $request->period_from = date('Y-m-d', strtotime($request->period_from));
    $request->period_to = str_replace('/', '-', $request->period_to);
    $request->period_to = date('Y-m-d', strtotime($request->period_to));
    $experience_data = array('employee_id'=>$request->emp_id,'company_name'=>$request->company_name,'industry'=>$request->industry,'position'=>$request->position,'period_from'=>$request->period_from,'period_to'=>$request->period_to,'basic_salary'=>$request->basic_salary,'bonus'=>$request->bonus,'allowance'=>$request->allowance);
    if($this->db->insert('employment_history',$experience_data)){
        $data["success"] = true;
        $data["experience"] = $this->db->select('*')->select('date_format(period_from,"%b %Y") as start',false)->select('date_format(period_to,"%b %Y") as end',false)->from('employment_history')->where('employee_id',$request->emp_id)->get()->result();
        $data["msg"] = "New experience added successfully!";
    }else{
        $data["success"] = false;
        $data["msg"] = "Experience could not add!";
    }
    echo json_encode($data);
}
public function update_experience(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $request->period_from = str_replace('/', '-', $request->period_from);
    $request->period_from = date('Y-m-d', strtotime($request->period_from));
    $request->period_to = str_replace('/', '-', $request->period_to);
    $request->period_to = date('Y-m-d', strtotime($request->period_to));
    $experience_data = array('company_name'=>$request->company_name,'industry'=>$request->industry,'position'=>$request->position,'period_from'=>$request->period_from,'period_to'=>$request->period_to,'basic_salary'=>$request->basic_salary,'bonus'=>$request->bonus,'allowance'=>$request->allowance);
    if($this->db->where('id',$request->id)->update('employment_history',$experience_data)){
        $data["success"] = true;
        $data["experience"] = $this->db->select('*')->select('date_format(period_from,"%b %Y") as start',false)->select('date_format(period_to,"%b %Y") as end',false)->from('employment_history')->where('employee_id',$request->emp_id)->get()->result();
        $data["msg"] = "Experience updated successfully!";
    }else{
        $data["success"] = false;
        $data["msg"] = "Experience could not update!";
    }
    echo json_encode($data);
}
public function update_education(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $request->period_from = str_replace('/', '-', $request->period_from);
    $request->period_from = date('Y-m-d', strtotime($request->period_from));
    $request->period_to = str_replace('/', '-', $request->period_to);
    $request->period_to = date('Y-m-d', strtotime($request->period_to));
    $education_data = array('institution'=>$request->institution,'country'=>$request->country,'course_field'=>$request->course_field,'period_from'=>$request->period_from,'period_to'=>$request->period_to,'highest_qualification_attained'=>$request->highest_qualification_attained);
    if($this->db->where('id',$request->id)->update('qualifications',$education_data)){
        $data["success"] = true;
        $data["educations"] = $this->db->select('*')->select('date_format(period_from,"%Y") as start',false)->select('date_format(period_to,"%Y") as end',false)->from('qualifications')->where('employee_id',$request->emp_id)->get()->result();
        $data["msg"] = "Qualification updated successfully!";
    }else{
        $data["success"] = false;
        $data["msg"] = "Qualification could not update!";
    }
    echo json_encode($data);
}

public function update_family(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $family_data = array('first_name'=>$request->first_name,'last_name'=>$request->last_name,'relation'=>$request->relation,'age'=>$request->age,'mobile'=>$request->mobile,'job'=>$request->job);
    if($this->db->where('id',$request->id)->update('family_members',$family_data)){
        $data["success"] = true;
        $data["family"] = $this->db->select('*')->from('family_members')->where('employee_id',$request->emp_id)->get()->result();
        $data["msg"] = "Family member updated successfully!";
    }else{
        $data["success"] = false;
        $data["msg"] = "Family member could not update!";
    }
    echo json_encode($data);
}

public function save_contact(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $contact_data = array('employee_id'=>$request->emp_id,'first_name'=>$request->first_name,'last_name'=>$request->last_name,'relation'=>$request->relation,'email'=>$request->email,'telephone'=>$request->telephone,'office_no'=>$request->office_no,'mobile'=>$request->mobile,'address'=>$request->address,'address_city'=>$request->address_city,'address_state'=>$request->address_state,'address_postcode'=>$request->address_postcode);
    if($this->db->insert('emergency_contacts',$contact_data)){
        $data["success"] = true;
        $data["contacts"] = $this->db->select('*')->from('emergency_contacts')->where('employee_id',$request->emp_id)->get()->result();
        $data["msg"] = "New emergency contact added successfully!";
    }else{
        $data["success"] = false;
        $data["msg"] = "Emergency contact could not add!";
    }
    echo json_encode($data);
}
public function update_contact(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $contact_data = array('first_name'=>$request->first_name,'last_name'=>$request->last_name,'relation'=>$request->relation,'email'=>$request->email,'telephone'=>$request->telephone,'office_no'=>$request->office_no,'mobile'=>$request->mobile,'address'=>$request->address,'address_city'=>$request->address_city,'address_state'=>$request->address_state,'address_postcode'=>$request->address_postcode);
    if($this->db->where('id',$request->id)->update('emergency_contacts',$contact_data)){
        $data["success"] = true;
        $data["contacts"] = $this->db->select('*')->from('emergency_contacts')->where('employee_id',$request->emp_id)->get()->result();
        $data["msg"] = "Emergency contact updated successfully!";
    }else{
        $data["success"] = false;
        $data["msg"] = "Emergency contact could not update!";
    }
    echo json_encode($data);
}
public function save_language(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $language_data = array('employee_id'=>$request->emp_id,'language'=>$request->language,'writing_skill'=>$request->writing_skill,'speaking_skill'=>$request->speaking_skill);
    if($this->db->insert('languages',$language_data)){
        $data["success"] = true;
        $data["languages"] = $this->db->select('*')->from('languages')->where('employee_id',$request->emp_id)->get()->result();
        $data["msg"] = "New language added!";
    }else{
        $data["success"] = false;
        $data["msg"] = "Language could not add!";
    }
    echo json_encode($data);
}
public function update_language(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $language_data = array('language'=>$request->language,'writing_skill'=>$request->writing_skill,'speaking_skill'=>$request->speaking_skill);
    if($this->db->where('id',$request->id)->update('languages',$language_data)){
        $data["success"] = true;
        $data["languages"] = $this->db->select('*')->from('languages')->where('employee_id',$request->emp_id)->get()->result();
        $data["msg"] = "Language updated successfully!";
    }else{
        $data["success"] = false;
        $data["msg"] = "Language could not update!";
    }
    echo json_encode($data);
}
public function get_language(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $data["language"] = $this->db->select('*')->from('languages')->where('id',$request->id)->get()->row();
    $data["success"] = true;
    echo json_encode($data);
}
public function get_incentive(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $data["incentive"] = $this->db->select('*')->from('incentives')->where('id',$request->id)->get()->row();
    $data["success"] = true;
    echo json_encode($data);
}
public function get_allowance(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $data["allowance"] = $this->db->select('*')->from('allowances')->where('id',$request->id)->get()->row();
    $data["success"] = true;
    echo json_encode($data);
}
public function get_education(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $data["education"] = $this->db->select('*')->select("date_format(period_from,'%d/%m/%Y') as period_from",false)->select("date_format(period_to,'%d/%m/%Y') as period_to",false)->from('qualifications')->where('id',$request->id)->get()->row();
    $data["success"] = true;
    echo json_encode($data);
}
public function get_experience(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $data["experience"] = $this->db->select('*')->select("date_format(period_from,'%d/%m/%Y') as period_from",false)->select("date_format(period_to,'%d/%m/%Y') as period_to",false)->from('employment_history')->where('id',$request->id)->get()->row();
    $data["success"] = true;
    echo json_encode($data);
}
public function get_qualification(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $data["education"] = $this->db->select('*')->select("date_format(period_from,'%d/%m/%Y') as period_from",false)->select("date_format(period_to,'%d/%m/%Y') as period_to",false)->from('employment_history')->where('id',$request->id)->get()->row();
    $data["success"] = true;
    echo json_encode($data);
}
public function get_family(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $data["family"] = $this->db->select('*')->from('family_members')->where('id',$request->id)->get()->row();
    $data["success"] = true;
    echo json_encode($data);
}
public function get_contact(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $data["contact"] = $this->db->select('*')->from('emergency_contacts')->where('id',$request->id)->get()->row();
    $data["success"] = true;
    echo json_encode($data);
}
public function get_skill(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $data["skill"] = $this->db->select('*')->from('skills')->where('id',$request->id)->get()->row();
    $data["success"] = true;
    echo json_encode($data);
}
public function save_incentive(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $incentive_data = array('employee_id'=>$request->emp_id,'incentive_name'=>$request->incentive_name,'amount'=>$request->amount);
    if($this->db->insert('incentives',$incentive_data)){
        $data["success"] = true;
        $data["incentives"] = $this->db->select('*')->from('incentives')->where('employee_id',$request->emp_id)->get()->result();
        $data["msg"] = "New incentive added successfully!";
    }else{
        $data["success"] = false;
        $data["msg"] = "Incentive could not add!";
    }
    echo json_encode($data);
}
public function update_incentive(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $incentive_data = array('incentive_name'=>$request->incentive_name,'amount'=>$request->amount);
    if($this->db->where('id',$request->id)->update('incentives',$incentive_data)){
        $data["success"] = true;
        $data["incentives"] = $this->db->select('*')->from('incentives')->where('employee_id',$request->emp_id)->get()->result();
        $data["msg"] = "Incentive updated successfully!";
    }else{
        $data["success"] = false;
        $data["msg"] = "Incentive could not update!";
    }
    echo json_encode($data);
}
public function save_allowance(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $allowance_data = array('employee_id'=>$request->emp_id,'allowance_name'=>$request->allowance_name,'amount'=>$request->amount);
    if($this->db->insert('allowances',$allowance_data)){
        $data["success"] = true;
        $data["allowances"] = $this->db->select('*')->from('allowances')->where('employee_id',$request->emp_id)->get()->result();
        $data["msg"] = "New allowance added successfully!";
    }else{
        $data["success"] = false;
        $data["msg"] = "Allowance could not add!";
    }
    echo json_encode($data);
}
public function update_allowance(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);
    $allowance_data = array('allowance_name'=>$request->allowance_name,'amount'=>$request->amount);
    if($this->db->where('id',$request->id)->update('allowances',$allowance_data)){
        $data["success"] = true;
        $data["allowances"] = $this->db->select('*')->from('allowances')->where('employee_id',$request->emp_id)->get()->result();
        $data["msg"] = "Allowance updated successfully!";
    }else{
        $data["success"] = false;
        $data["msg"] = "Allowance could not update!";
    }
    echo json_encode($data);
}
public function delete_data(){
    $postdata = file_get_contents("php://input");
    $request = json_decode($postdata);

    if($request->type == 'family'){
        $this->db->where('id',$request->id)->delete('family_members');
        $data["success"] = true;
        $data["msg"] = "Family member deleted successfully!";
    }else if($request->type == 'contact'){
        $this->db->where('id',$request->id)->delete('emergency_contacts');
        $data["success"] = true;
        $data["msg"] = "Emergency contact deleted successfully!";
    }else if($request->type == 'language'){
        $this->db->where('id',$request->id)->delete('languages');
        $data["success"] = true;
        $data["msg"] = "Language deleted successfully!";
    }else if($request->type == 'incentive'){
        $this->db->where('id',$request->id)->delete('incentives');
        $data["success"] = true;
        $data["msg"] = "Incentive deleted successfully!";
    }else if($request->type == 'allowance'){
        $this->db->where('id',$request->id)->delete('allowances');
        $data["success"] = true;
        $data["msg"] = "Allowance deleted successfully!";
    }else if($request->type == 'education'){
        $this->db->where('id',$request->id)->delete('qualifications');
        $data["success"] = true;
        $data["msg"] = "Qualification deleted successfully!";
    }else if($request->type == 'experience'){
        $this->db->where('id',$request->id)->delete('employment_history');
        $data["success"] = true;
        $data["msg"] = "Experience deleted successfully!";
    }else if($request->type == 'skill'){
        $this->db->where('id',$request->id)->delete('skills');
        $data["success"] = true;
        $data["msg"] = "Skill deleted successfully!";
    }

    echo json_encode($data);
}
}