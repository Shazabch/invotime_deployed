<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee_groups_api extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();

        // Check if user is logged in
        if (is_null(get_user())) {
            $this->output
                ->set_status_header(401)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Unauthorized']))
                ->_display();
            exit;
        }
    }

    /**
     * Get all employee groups for the current company
     */
    public function get_groups()
    {
        $user = get_user();
        $company_id = $user['company_id'];

        $groups = $this->db
            ->select('employee_groups.*, branches.name as branch_name, COUNT(DISTINCT egr.employee_id) as employee_count')
            ->from('employee_groups')
            ->join('branches', 'employee_groups.branch_id = branches.id', 'left')
            ->join('employee_groups_relation as egr', 'employee_groups.id = egr.group_id', 'left')
            ->where('employee_groups.company_id', $company_id)
            ->group_by('employee_groups.id')
            ->order_by('employee_groups.name', 'ASC')
            ->get()
            ->result_array();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data' => $groups
            ]));
    }

    /**
     * Get single group with employees
     */
    public function get_group($group_id)
    {
        $user = get_user();
        $company_id = $user['company_id'];

        // Get group details
        $group = $this->db
            ->select('employee_groups.*, branches.name as branch_name')
            ->from('employee_groups')
            ->join('branches', 'employee_groups.branch_id = branches.id', 'left')
            ->where('employee_groups.id', $group_id)
            ->where('employee_groups.company_id', $company_id)
            ->get()
            ->row_array();

        if (!$group) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Group not found']));
            return;
        }

        // Get employees in this group
        $employees = $this->db
            ->select('employees.id, employees.special_id, employees.first_name')
            ->from('employee_groups_relation')
            ->join('employees', 'employee_groups_relation.employee_id = employees.id')
            ->where('employee_groups_relation.group_id', $group_id)
            ->get()
            ->result_array();

        $group['employees'] = $employees;

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data' => $group
            ]));
    }

    /**
     * Get branches for dropdown
     */
    public function get_branches()
    {
        $user = get_user();
        $company_id = $user['company_id'];

        $branches = $this->db
            ->select('id, name')
            ->from('branches')
            ->where('company_id', $company_id)
            ->order_by('name', 'ASC')
            ->get()
            ->result_array();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data' => $branches
            ]));
    }

    /**
     * Get employees (optionally filtered by branch)
     */
    public function get_employees()
    {
        $user = get_user();
        $company_id = $user['company_id'];
        $branch_id = $this->input->get('branch_id');

        $this->db
            ->select('id, special_id, first_name, branch_id')
            ->from('employees')
            ->where('special_id IS NOT NULL')
            ->where('company_id', $company_id);

        if ($branch_id && $branch_id != '0') {
            $this->db->where('branch_id', $branch_id);
        }

        $employees = $this->db
            ->order_by('special_id', 'ASC')
            ->get()
            ->result_array();

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data' => $employees
            ]));
    }

    /**
     * Create new employee group
     */
    public function create_group()
    {
        $user = get_user();
        $company_id = $user['company_id'];

        $input = json_decode($this->input->raw_input_stream, true);

        if (!isset($input['name']) || empty(trim($input['name']))) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Group name is required']));
            return;
        }

        $branch_id = isset($input['branch_id']) && $input['branch_id'] ? $input['branch_id'] : 0;
        $employee_ids = isset($input['employee_ids']) ? $input['employee_ids'] : [];

        // Start transaction
        $this->db->trans_start();

        // Insert group
        $this->db->insert('employee_groups', [
            'name' => trim($input['name']),
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $group_id = $this->db->insert_id();

        // Insert employee relations
        if (!empty($employee_ids)) {
            $relations = [];
            foreach ($employee_ids as $emp_id) {
                $relations[] = [
                    'group_id' => $group_id,
                    'employee_id' => $emp_id
                ];
            }
            $this->db->insert_batch('employee_groups_relation', $relations);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Failed to create group']));
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Group created successfully',
                'group_id' => $group_id
            ]));
    }

    /**
     * Update employee group
     */
    public function update_group($group_id)
    {
        $user = get_user();
        $company_id = $user['company_id'];

        $input = json_decode($this->input->raw_input_stream, true);

        // Verify group belongs to company
        $exists = $this->db
            ->where('id', $group_id)
            ->where('company_id', $company_id)
            ->get('employee_groups')
            ->row();

        if (!$exists) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Group not found']));
            return;
        }

        if (!isset($input['name']) || empty(trim($input['name']))) {
            $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Group name is required']));
            return;
        }

        $branch_id = isset($input['branch_id']) && $input['branch_id'] ? $input['branch_id'] : 0;
        $employee_ids = isset($input['employee_ids']) ? $input['employee_ids'] : [];

        // Start transaction
        $this->db->trans_start();

        // Update group
        $this->db->update('employee_groups', [
            'name' => trim($input['name']),
            'branch_id' => $branch_id,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $group_id]);

        // Delete existing relations
        $this->db->delete('employee_groups_relation', ['group_id' => $group_id]);

        // Insert new relations
        if (!empty($employee_ids)) {
            $relations = [];
            foreach ($employee_ids as $emp_id) {
                $relations[] = [
                    'group_id' => $group_id,
                    'employee_id' => $emp_id
                ];
            }
            $this->db->insert_batch('employee_groups_relation', $relations);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Failed to update group']));
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Group updated successfully'
            ]));
    }

    /**
     * Delete employee group
     */
    public function delete_group($group_id)
    {
        $user = get_user();
        $company_id = $user['company_id'];

        // Verify group belongs to company
        $exists = $this->db
            ->where('id', $group_id)
            ->where('company_id', $company_id)
            ->get('employee_groups')
            ->row();

        if (!$exists) {
            $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Group not found']));
            return;
        }

        // Start transaction
        $this->db->trans_start();

        // Delete relations first
        $this->db->delete('employee_groups_relation', ['group_id' => $group_id]);

        // Delete group
        $this->db->delete('employee_groups', ['id' => $group_id]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Failed to delete group']));
            return;
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Group deleted successfully'
            ]));
    }
}
