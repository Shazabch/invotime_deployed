<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Merit extends CI_Model
{
    /**
     * Default merit points
     *
     * @var integer
     */
    public $default_merit_points = 100;

    /**
     * Table to process
     *
     * @var string
     */
    private $table_name = 'merit_points';

    /**
     * Deduction points table
     * 
     * @var string
     */
    private $deduction_points_table = "merit_deduction_points";

    /**
     * Function to check if an employees's merit is added for a given month
     *
     * @param string $employee_id
     * @param int $month
     * @param int $year
     * @return boolean
     */
    public function is_merit_found($employee_id, $month, $year)
    {
        $this->db->select("count(1) count")->from($this->table_name)
            ->where("employee_id", $employee_id)->where("year", $year)->where("month", $month);
        $count = $this->db->get()->row()->count;
        if ($count === "0") {
            return false;
        }
        return true;
    }

    /**
     * Function to add merit points for an employee
     *
     * @param array $data
     * 
     * @return boolean
     */
    public function add_merit_points($data)
    {
        $this->db->insert_batch($this->table_name, $data);
        return true;
    }

    public function get_deduction_points($cid, $bid = null)
    {
        if ($bid === null) {
            return $this->db->get_where($this->deduction_points_table, ['company_id' => $cid])->result();
        }
        return $this->db->get_where($this->deduction_points_table, ['company_id' => $cid, 'branch_id' => $bid])->result();
    }
}
