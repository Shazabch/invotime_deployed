<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public $current_user;

    public function __construct()
    {
        parent::__construct();
        $this->current_user = get_user();
        if (!$this->current_user) {
            redirect('welcome');
        }
    }

    public function panel()
    {
        if (!is_page_permitted('panel')) {
            redirect_if_not_permitted();
        }

        if ($this->current_user['permissions_level'] != 'Company') {
            redirect('welcome');
        }

        $data = [];
        $data['pageTitle'] = "Admin Panel";
        $data['active_menu'] = "admin/panel";
        $this->load->view('header', $data);
        $data['menus'] = get_menus();
        $this->load->view('sidebar', $data);

        $data['admins'] = $this->db->select('e.id id, e.special_id employee_id, e.first_name, r.job_name, e.email')
            ->from('employees e')
            ->join('roles r', 'r.id = e.role_id')
            ->where('e.company_id', $this->current_user['company_id'])
            ->where_in('r.permissions_level', ['Outlet', 'Company'])
            ->where('r.role_type', 'invotime')
            ->where('e.id !=', $this->current_user['id'])
            ->where('e.employee_status', 'active')
            ->get()
            ->result();

        $this->load->view('admin/panel', $data);
        $this->load->view('footer');
    }

    public function change_password($adminId)
    {
        if ($this->current_user['permissions_level'] != 'Company') {
            redirect('admin/panel');
        }

        $data = [];
        $data['adminId'] = $adminId;
        $data['pageTitle'] = "Admin Panel | Change Password";
        $data['active_menu'] = "admin/change_password_form";
        $this->load->view('header', $data);
        $data['menus'] = get_menus();
        $this->load->view('sidebar', $data);

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]');
            $this->form_validation->set_rules('passconf', 'Password Confirmation', 'required|matches[password]');

            if ($this->form_validation->run() === TRUE) {
                $this->db->set('password', md5($this->input->post('password')))
                    ->where('id', $adminId)
                    ->update('employees');
                $this->session->set_flashdata('success', 'Password changed successfully!');
                return redirect('admin/panel');
            }

            $this->load->view('admin/change_password_form', $data);
        } else {
            $this->load->view('admin/change_password_form', $data);
        }
        $this->load->view('footer');
    }

    public function delete($adminId)
    {
        if ($this->current_user['permissions_level'] != 'Company') {
            redirect('admin/panel');
        }

        $admin = $this->db->get_where('employees', ['id' => $adminId])->row();

        if ($admin->company_id != $this->current_user['company_id']) {
            $this->session->set_flashdata('error', 'You are not allowed to delete this admin!');
            redirect('admin/panel');
        }

        $this->db->where('id', $adminId)
            ->delete('employees');

        $this->session->set_flashdata('success', 'Admin deleted successfully!');
        redirect('admin/panel');
    }

    public function add()
    {
        if ($this->current_user['permissions_level'] != 'Company') {
            redirect('admin/panel');
        }

        $data = [];
        $data['pageTitle'] = "Admin Panel | Add Admin";
        $data['active_menu'] = "admin/add";
        $this->load->view('header', $data);
        $data['menus'] = get_menus();
        $this->load->view('sidebar', $data);
        $data['branches'] = $this->db
            ->select('id, name')
            ->from('branches')
            ->where('company_id', $this->current_user['company_id'])
            ->get()
            ->result();

        $data['roles'] = $this->db
            ->select('id, job_name')
            ->from('roles')
            ->where('company_id', $this->current_user['company_id'])
            ->where_in('permissions_level', ['Outlet', 'Company'])
            ->where('role_type', 'invotime')
            ->get()
            ->result();

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('outlet', 'Branch', 'required');
            $this->form_validation->set_rules('adminType', 'Admin Type', 'required');
            $this->form_validation->set_rules('name', 'Name', 'required');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');

            if ($this->form_validation->run() === TRUE) {
                $role_exist = $this->db->select('id')->from('roles')->where('id', $this->input->post('adminType'))->get()->row();
                if ($role_exist) {
                    $role_id = $role_exist->id;
                    $this->db->insert('employees', [
                        'first_name' => $this->input->post('name'),
                        'email' => $this->input->post('email'),
                        'password' => md5($this->input->post('password')),
                        'role_id' => $role_id,
                        'company_id' => $this->current_user['company_id'],
                        'employee_status' => 'active',
                        'branch_id' => $this->input->post('outlet')
                    ]);
                    $insert_id = $this->db->insert_id();
                    log_message('debug', __METHOD__ . ':' . __LINE__ . ' Inserted employee id: ' . $insert_id);
                    $this->session->set_flashdata('success', 'Admin added successfully!');
                    redirect('admin/panel');
                } else {
                    $this->session->set_flashdata('error', 'Role not found!');
                    redirect('admin/panel');
                }
                $this->load->view('admin/add', $data);
            } else {
                $this->load->view('admin/add', $data);
            }
        } else {
            $this->load->view('admin/add', $data);
        }
        $this->load->view('footer');
    }

    public function edit($adminId)
    {
        if ($this->current_user['permissions_level'] != 'Company') {
            redirect('admin/panel');
        }

        $data = [];
        $data['pageTitle'] = "Admin Panel | Edit Admin";
        $data['active_menu'] = "admin/edit";
        $this->load->view('header', $data);
        $data['menus'] = get_menus();
        $this->load->view('sidebar', $data);
        $data['branches'] = $this->db
            ->select('id, name')
            ->from('branches')
            ->where('company_id', $this->current_user['company_id'])
            ->get()
            ->result();

        $data['roles'] = $this->db
            ->select('id, job_name')
            ->from('roles')
            ->where('company_id', $this->current_user['company_id'])
            ->where_in('permissions_level', ['Outlet', 'Company'])
            ->where('role_type', 'invotime')
            ->get()
            ->result();

        $data['admin'] = $this->db->select('id, first_name, email, branch_id, role_id')->get_where('employees', ['id' => $adminId])->row();

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $this->load->library('form_validation');
            $this->form_validation->set_rules('outlet', 'Branch', 'required');
            $this->form_validation->set_rules('adminType', 'Admin Type', 'required');
            $this->form_validation->set_rules('name', 'Name', 'required');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email');

            if ($this->form_validation->run() === TRUE) {
                $role_exist = $this->db->select('id')->from('roles')->where('company_id', $this->current_user['company_id'])->where('id', $this->input->post('adminType'))->get()->row();
                if ($role_exist) {
                    $role_id = $role_exist->id;
                    $this->db->where('id', $adminId)
                        ->update('employees', [
                            'first_name' => $this->input->post('name'),
                            'email' => $this->input->post('email'),
                            'role_id' => $role_id,
                            'branch_id' => $this->input->post('outlet')
                        ]);
                    $this->session->set_flashdata('success', 'Admin updated successfully!');
                    redirect('admin/panel');
                } else {
                    $this->session->set_flashdata('error', 'Role not found!');
                    redirect('admin/panel');
                }
            }

            $this->load->view('admin/edit', $data);
        } else {
            $this->load->view('admin/edit', $data);
        }
        $this->load->view('footer');
    }
}
