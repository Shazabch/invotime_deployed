<?php

use Svg\Tag\Group;

defined('BASEPATH') OR exit('No direct script access allowed');

class MyModel extends CI_Model
{


  
    public
    function getEmployeeProfle($employeeId)
    {
        $q = $this->db->select('employees.id,employees.first_name, employees.sex,employees.special_id, branches.name branchName,departments.name DepartmentName,positions.title PostitionName,  roles.job_name RolesName, employees.marital_status')->from('employees')
            ->join('departments', 'departments.id=employees.department_id')
            ->join('positions', 'positions.id=employees.position_id')
            ->join('roles', 'roles.id=employees.role_id')
            ->join('branches', 'branches.id=employees.branch_id')
            ->where('employees.id', $employeeId)
            ->get()->row();
        if ($q == null) {
            return array('status' => 204, 'message' => 'Employee profile not updated.');
        } else {
                return array('status' => 200, 'message' => 'Successfully updated.', 'data' => $q);


        }
    }



    public
    function getLastInsertedClockingeEmployeeRecord($employeeId)
    {
        $q = $this->db
            ->where('clockings_news.employee_id', $employeeId)
            ->order_by('id',"desc")->limit(1)->get('clockings_news')->row();

        if ($q == null) {
            return array('status' => 204, 'message' => 'Unable to retrieve your last action');
        } else {


            return array('status' => 200, 'message' => 'Last action retrieved successfully', 'data' => $q);


        }
    }

    public
    function login($employeeId, $organizationid, $password)
    {


        $employee=$this->db->select('employees.*,companies.organization_id')->from('employees')
            ->join('companies', 'companies.id=employees.company_id')
            ->where('employees.special_id', $employeeId)
            ->where('companies.organization_id', $organizationid)


            ->get()->row();


//        print_r($q);
//        die();

         if ($employee->special_id != $employeeId) {
            return array('status' => 204, 'message' => 'Employee ID or Organization ID not found.');
        }
        else if ($employee->organization_id != $organizationid) {
            return array('status' => 204, 'message' => 'Employee ID or Organization ID not found.');
        }
        else if ($employee->password != md5($password)) {
            return array('status' => 204, 'message' => 'Password is incorrect.');
        }


        $q = $this->db->select('employees.*,companies.organization_id, coordinate, branches.invalid_clocking_distance')->from('employees')
            ->join('companies', 'companies.id=employees.company_id')
            ->join('branches', 'employees.branch_id = branches.id')
            ->join('devices', 'employees.branch_id = devices.branch_id', 'left')
            ->where('employees.special_id', $employeeId)
            ->where('employees.password', md5($password))
            ->where('companies.organization_id',$organizationid)

            ->get()->row();
            $q->lat = "";
            $q->long = "";
            $coordinate = explode(",", $q->coordinate);
            if(count($coordinate) == 2){
                $q->lat = $coordinate[0];
                $q->long = $coordinate[1];
            }

if($q==null){
    return array('status' => 204, 'message' => 'Employee not found.');
}
        else {

                $last_login = date('Y-m-d H:i:s');
                $token = md5(rand());
                $expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
                $this->db->trans_start();
//                $this->db->where('id',$id)->update('users',array('last_login' => $last_login));
//                $this->db->insert('users_authentication',array('users_id' => $id,'token' => $token,'expired_at' => $expired_at));
                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    return array('status' => 500, 'message' => 'Internal server error.');
                } else {
                    $this->db->trans_commit();
                    return array('status' => 200, 'message' => 'Successfully login.', 'data' => $q, 'token' => $token);
                }

        }
    }

    public function get_profile($id)
    {
        $q = $this->db->select('employees.*,companies.organization_id, companies.qr_code as is_qr, companies.include_in_qr_code, companies.exclude_from_qr_code, companies.self_clocking as is_self_clocking, include_in_self_clocking, exclude_from_self_clocking, companies.bluetooth as is_bluetooth, include_in_bluetooth, exclude_from_bluetooth, device_id, coordinate, branches.invalid_clocking_distance')->from('employees')
            ->join('companies', 'companies.id=employees.company_id')
            ->join('branches', 'employees.branch_id = branches.id')
            ->join('devices', 'employees.branch_id = devices.branch_id', 'left')
            ->where('employees.id', $id)->get()->row();

        if ($q == null) {
            return array('status' => 204, 'message' => 'Employee not found.');
        }

        if ($q->is_qr == 1) {
            if (empty($q->include_in_qr_code) && empty($q->exclude_from_qr_code)) {
                $q->is_qr == "1";
            }else{
                if (!empty($q->include_in_qr_code)) {
                    $group_ids = explode (",", $q->include_in_qr_code);
                    foreach ($group_ids as $group_id) {
                        $this->db->where('group_id', $group_id);
                        $this->db->where('employee_id', $id);
                        $results = $this->db->get('employee_groups_relation')->result();
                        if (!empty($results)) {
                            foreach ($results as $result) {
                                if ($result->employee_id == $id) {
                                    $q->is_qr = "1";
                                    break 2;
                                }else{
                                    $q->is_qr = "0";
                                    break 2;
                                }
                            }
                        }else{
                            $q->is_qr = "0";
                        }
                    }
                }else{
                    $group_ids = explode (",", $q->exclude_from_qr_code);
                    foreach ($group_ids as $group_id) {
                        $this->db->where('group_id', $group_id);
                        $this->db->where('employee_id', $id);
                        $results = $this->db->get('employee_groups_relation')->result();
                        if (!empty($results)) {
                            foreach ($results as $result) {
                                if ($result->employee_id == $id) {
                                    $q->is_qr = "0";
                                    break 2;
                                }else{
                                    $q->is_qr = "1";
                                    break 2;
                                }
                            }
                        }else{
                            $q->is_qr = "1";
                        }
                    }  
                }
            } 
        }
        if ($q->is_bluetooth == 1) {
            if (empty($q->include_in_bluetooth) && empty($q->exclude_from_bluetooth)) {
                $q->is_qr == "1";
            }else{
                if (!empty($q->include_in_bluetooth)) {
                    $group_ids = explode (",", $q->include_in_bluetooth);
                    foreach ($group_ids as $group_id) {
                        $this->db->where('group_id', $group_id);
                        $this->db->where('employee_id', $id);
                        $results = $this->db->get('employee_groups_relation')->result();
                        if (!empty($results)) {
                            foreach ($results as $result) {
                                if ($result->employee_id == $id) {
                                    $q->is_bluetooth = "1";
                                    break 2;
                                }else{
                                    $q->is_bluetooth = "0";
                                    break 2;
                                }
                            }
                        }else{
                            $q->is_bluetooth = "0";
                        }
                    }            
                }else{
                    $group_ids = explode (",", $q->exclude_from_bluetooth);
                    foreach ($group_ids as $group_id) {
                        $this->db->where('group_id', $group_id);
                        $this->db->where('employee_id', $id);
                        $results = $this->db->get('employee_groups_relation')->result();
                        if (!empty($results)) {
                            foreach ($results as $result) {
                                if ($result->employee_id == $id) {
                                    $q->is_bluetooth = "0";
                                    break 2;
                                }else{
                                    $q->is_bluetooth = "1";
                                    break 2;
                                }
                            }
                        }else{
                            $q->is_bluetooth = "1";
                        }
                    }  
                }
            }
        }
        if ($q->is_self_clocking == 1) {
            if (empty($q->include_in_self_clocking) && empty($q->exclude_from_self_clocking)) {
                $q->is_qr == "1";
            }else{
                if (!empty($q->include_in_self_clocking)) {
                    $group_ids = explode (",", $q->include_in_self_clocking);
                    foreach ($group_ids as $group_id) {
                        $this->db->where('group_id', $group_id);
                        $this->db->where('employee_id', $id);
                        $results = $this->db->get('employee_groups_relation')->result();
                        if (!empty($results)) {
                            foreach ($results as $result) {
                                if ($result->employee_id == $id) {
                                    $q->is_self_clocking = "1";
                                    break 2;
                                }else{
                                    $q->is_self_clocking = "0";
                                    break 2;
                                }
                            }
                        }else{
                            $q->is_self_clocking = "0";
                        }
                    }            
                }else{
                    $group_ids = explode (",", $q->exclude_from_self_clocking);
                    foreach ($group_ids as $group_id) {
                        $this->db->where('group_id', $group_id);
                        $this->db->where('employee_id', $id);
                        $results = $this->db->get('employee_groups_relation')->result();
                        if (!empty($results)) {
                            foreach ($results as $result) {
                                if ($result->employee_id == $id) {
                                    $q->is_self_clocking = "0";
                                    break 2;
                                }else{
                                    $q->is_self_clocking = "1";
                                    break 2;
                                }
                            }
                        }else{
                            $q->is_self_clocking = "1";
                        }
                    }  
                }
            }
        }

        $q->lat = "";
        $q->long = "";
        $coordinate = explode(",", $q->coordinate);

        if (count($coordinate) == 2) {
            $q->lat = $coordinate[0];
            $q->long = $coordinate[1];
        }

        $devices = $this->db->select("mac_address, coordinate, uuid")->from("devices")->where("company_id", $q->company_id)->get()->result();

        foreach($devices as $d){
            $d->lat = "";
            $d->long = "";
            $coordinate = explode(",", $d->coordinate);

            if (count($coordinate) == 2) {
                $d->lat = $coordinate[0];
                $d->long = $coordinate[1];
            }
            unset($d->coordinate);
        }

        $token = md5(rand());

        $this->db->trans_start();
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return array('status' => 500, 'message' => 'Internal server error.');
        }
        
        $this->db->trans_commit();
        return array('status' => 200, 'message' => 'Successfully login.', 'data' => $q, 'other_devices' => $devices, 'token' => $token);
    }

    public function AttendanceHistoryList($employeeId, $year, $month){

        $where_filter = "";

        if($year != ""){
            $where_filter .= " AND year(datetime) = $year";
        }
        if($month != ""){
            $where_filter .= " AND month(datetime) = $month";
        }

        $query = $this->db->query("select * from `clockings_news` where employee_id = $employeeId $where_filter AND deleted_at is NULL order  by datetime desc ");
        


        
        return $query->result_array();
//        $q = $this->db->select('*')->from('clockings_news')
//
//            ->where('employee_id', $employeeId)
//        ->get()->row();
//        return array( $q);
//
//
//        result_array();



    }

    public
    function register($employeeId, $organizationid, $password)
    {
        $employee=$this->db->select('employees.*,companies.organization_id')->from('employees')
            ->join('companies', 'companies.id=employees.company_id')
            ->where('employees.special_id', $employeeId)
            ->where('companies.organization_id', $organizationid)


            ->get()->row();


//        print_r($q);
//        die();

        if ($employee->special_id != $employeeId) {
            return array('status' => 204, 'message' => 'Employee ID or Organization ID not found.');
        }
        else if ($employee->organization_id != $organizationid) {
            return array('status' => 204, 'message' => 'Employee ID or Organization ID not found.');
        }

        $q = $this->db->select('employees.*,companies.organization_id')->from('employees')
            ->join('companies', 'companies.id=employees.company_id')
            ->where('employees.special_id', $employeeId)

            ->where('companies.organization_id',$organizationid)

            ->get()->row();

        if($q==null){
            return array('status' => 204, 'message' => 'Employee not found.');
        }



        else {

            $last_login = date('Y-m-d H:i:s');
            $token = md5(rand());
            $expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
            $this->db->trans_start();
               $this->db->where('id',$q->id)->update('employees',array('password' => md5($password)));
//                $this->db->insert('users_authentication',array('users_id' => $id,'token' => $token,'expired_at' => $expired_at));
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return array('status' => 500, 'message' => 'Internal server error.');
            } else {
                $this->db->trans_commit();
                return array('status' => 200, 'message' => 'Successfully registered.', 'data' => $q, 'token' => $token);
            }

        }
    }



    public
    function update_user_device($employeeId, $user_device_id, $user_device_type)
    {

//        print_r($employeeId);
//        print_r($organizationid);
//        print_r($password);
//        die();
        $q = $this->db->select('employees.*')->from('employees')

            ->where('employees.id', $employeeId)



            ->get()->row();
//        print_r($q);
//        die();


        if ($q == null) {
            return array('status' => 204, 'message' => 'Employee not found.');
        } else {

            $last_login = date('Y-m-d H:i:s');
            $token = md5(rand());
            $expired_at = date("Y-m-d H:i:s", strtotime('+12 hours'));
            $this->db->trans_start();
            $this->db->where('id',$q->id)->update('employees',array('user_device_id' => $user_device_id,'user_device_type' => $user_device_type));
//                $this->db->insert('users_authentication',array('users_id' => $id,'token' => $token,'expired_at' => $expired_at));
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return array('status' => 500, 'message' => 'Internal server error.');
            } else {
                $this->db->trans_commit();
                return array('status' => 200, 'message' => 'Successfully registered.', 'data' => $q, 'token' => $token);
            }

        }
    }

}