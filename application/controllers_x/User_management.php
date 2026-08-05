<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_management extends CI_Controller {


  	public function index()
  	{
      // echo "test";
      // die();
  	}

    public function login()
    {



         $username = $this->input->post("email");
         $password = md5($this->input->post("password"));

         $query = $this->db->query("SELECT employees.*, companies.name as company_name from employees 
  
  LEFT JOIN companies ON employees.company_id = companies.id 

  WHERE employees.email =? AND employees.password=?",array($username,$password));

        if(is_null($query->row())){
          $data["error"] = true;
          $data["company"] = false;

          $this->load->view('login',$data);
        }
        else {

          $row = $query->row_array();

          $this->session->set_userdata('antelope_user', $row);

          redirect("overview");
        }

    }

    public function logout()
    {
      $this->session->unset_userdata('antelope_user');
      redirect("welcome");
    }



}
