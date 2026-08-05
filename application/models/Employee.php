<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * The model class for Employee.
 */
class Employee extends CI_Model
{
    /**
     * The function to get general info of all employees
     *
     * @param array $where empty array will return all employees
     * @return array<stdClass>
     */
    public function get_employees($where)
    {
        $this->db->select("id, special_id, first_name, branch_id, company_id")->from("employees");
        if (empty($where)) {
            return $this->db->get()->result();
        }
        $this->db->where($where);
        return $this->db->get()->result();
    }
}
