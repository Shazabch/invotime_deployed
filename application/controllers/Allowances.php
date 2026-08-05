<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Allowances extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function assignment()
    {
        if (!is_page_permitted('assignment')) {
            redirect_if_not_permitted();
        }

        $current_user = get_user();
        $data['pageTitle'] = "Allowances Assignment";
        $data['active_menu'] = "allowances/assignment";
        $this->load->view('header',$data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar',$data);
        
        $data["filters_form_action"] = "allowances/assignment";
        render_all_filters($data);

        $where_filter = $data["where_filter"];
        $where_date = $data["where_date"];

        

        $cid = $current_user["company_id"];
        $data['positions'] = $this->db->select('id, title as name')->from('positions')->where("company_id = '$cid'")->get()->result();

        $total_records = $this->db->query("SELECT COUNT(DISTINCT employees.id) as total_records FROM employees INNER JOIN roles ON employees.role_id = roles.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = employees.id WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND roles.exclude_from_system = 'no' AND $where_filter ")->row()->total_records;        

        $limit = 20;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if(!empty($this->input->get("page"))){
            $page = $this->input->get("page");
        }
        $skip = ($page -1) * $limit;


        $result = $this->db->query("SELECT employees.id, special_id,first_name FROM employees INNER JOIN roles ON employees.role_id = roles.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = employees.id WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND roles.exclude_from_system = 'no' AND $where_filter
            GROUP BY employees.id, special_id,first_name ORDER BY special_id LIMIT $skip,$limit")->result_array();

        $year = $data["selected_year"];
        $first_day = $data['formatted_date']['start_date']->format('Y-m-d');
        $last_day = $data['formatted_date']['end_date']->format('Y-m-d');

        $data["period_of_dates"] = new DatePeriod(
            new DateTime($first_day),
            new DateInterval('P1D'),
            (new DateTime($last_day))->add(new DateInterval('P1D'))
        );

        foreach($result as &$row){


            $emp_id = $row["id"];



            $result2 = $this->db->query("SELECT allowances_settings.*, date FROM allowances_assignment INNER JOIN allowances_settings ON allowances_assignment.allowance_id = allowances_settings.id where employee_id = $emp_id $where_date ")->result_array();

            // $max_date = cal_days_in_month(CAL_GREGORIAN, $data["selected_month"], $year);


            // for ($x = 1; $x <= $max_date; $x++){
            foreach ($data["period_of_dates"] as $periodDate) {
                $dd = $periodDate->format('Y-m-d');
                $row[$dd] = array("applicable"=>"false", "assigned"=>"-", "allowance1_id"=>"", "allowance1_code"=>"", "allowance2_id"=>"", "allowance2_code"=>"");
            }

            foreach($result2 as &$row2){

                $assigned = "yes";
                $allowance_id = $row2["id"];

                if($row[$row2["date"]]["allowance1_id"] == ""){
                    $row[$row2["date"]]["allowance1_id"] = $allowance_id;
                    $row[$row2["date"]]["allowance1_code"] = $row2["code"];
                } else {
                    $row[$row2["date"]]["allowance2_id"] = $allowance_id;
                    $row[$row2["date"]]["allowance2_code"] = $row2["code"];
                }
                
                $row[$row2["date"]]["applicable"] = "true";
                $row[$row2["date"]]["assigned"] = $assigned;
            }

            // make code for each date by combining the codes of the two allowances
            // for ($x = 1; $x <= $max_date; $x++){
            foreach ($data["period_of_dates"] as $periodDate) {
                $dd = $periodDate->format('Y-m-d');
                $code = $row[$dd]["allowance1_code"];
                if ($row[$dd]["allowance2_code"] != "") {
                    $code = $code . ", " . $row[$dd]["allowance2_code"];
                }
                $row[$dd]["code"] = $code;
            }

        }

        $data["employees"] = $result;


        $data["total_pages"] = $total_pages;
        $data["page"] = $page;
        $data["skip"] = $skip;
        unset($_GET['page']);
        $currentURL = current_url();
        $query_string = http_build_query($_GET);
        $data["pagination_url"] = $currentURL . '?' . $query_string;
        $data["summary_export_url"] = base_url() . "overview/allowances_assignment_pdf?$query_string"; 
        $permissions_level = $current_user["permissions_level"];
        $bid = $current_user["branch_id"];
        $selected_branch = $data["selected_branch_id"];

        $data['allowances'] = $this->db->select("*")->from("allowances_settings")->where("company_id = '$cid'")->get()->result();
        
        $bid = $current_user["branch_id"];
        if($permissions_level == "Outlet"){
            $holidays_with_names = get_public_holidays_with_name($bid);
            $data["public_holidays"] = $holidays_with_names[0];
            $data["public_holidays_names"] = $holidays_with_names[1];
        }else{
            $holidays_with_names = get_public_holidays_with_name();
            $data["public_holidays"] = $holidays_with_names[0];
            $data["public_holidays_names"] = $holidays_with_names[1];
        }

        $data['permissions_level'] = $permissions_level;
        $data["filters"] = $this->load->view('filters', $data, true);


        $this->load->view('allowances_assignment',$data);
        $this->load->view('footer',$data);
    }

    public function save_assignment()
    {
        $data = explode(',',$this->input->post('data'));

        $result = array();

        foreach($data as $d){
            $row = explode('|',$d);
            $employee_id = $row[0];
            $date = $row[1];
            $allowance1_id = $row[2];
            $allowance2_id = $row[3];

            $this->db->query("DELETE FROM allowances_assignment WHERE employee_id = $employee_id AND date = '$date'");

            if($allowance1_id != ""){
                $this->db->query("INSERT INTO allowances_assignment (employee_id, date, allowance_id) VALUES ($employee_id, '$date', $allowance1_id)");
            }

            if($allowance2_id != ""){
                $this->db->query("INSERT INTO allowances_assignment (employee_id, date, allowance_id) VALUES ($employee_id, '$date', $allowance2_id)");
            }

            $code = "";
            $allowance1_id = "";
            $allowance2_id = "";
            
            $allowances = $this->db->query("SELECT allowance_id, code FROM allowances_settings join allowances_assignment on allowances_settings.id = allowances_assignment.allowance_id WHERE employee_id = $employee_id AND date = '$date' order by allowances_assignment.id asc")->result_array();
            $codes = array();
            foreach($allowances as $a){
                $codes[] = $a["code"];
                if ($allowance1_id == "") {
                    $allowance1_id = $a["allowance_id"];
                } else {
                    $allowance2_id = $a["allowance_id"];
                }
            }
            $code = implode(", ",$codes);

            $data = array(
                "code" => $code,
                "allowance1_id" => $allowance1_id,
                "allowance2_id" => $allowance2_id,
                "date" => $date,
                "employee_id" => $employee_id
            );

            $result[] = $data;
        }

        echo json_encode($result);
    }

    public function delete_assignment(){
        $data= explode(',',$this->input->post('data'));

        $response_records = array();

        foreach($data as $d) {
            $d_exploded = explode('|',$d);

            $employee_id = $d_exploded[0];
            $date = $d_exploded[1];

            $this->db->query("DELETE FROM allowances_assignment WHERE employee_id = $employee_id AND date = '$date'");

            $data = array(
                'date' => $date,
                'employee_id' => $employee_id,
                'allowance1_id' => '',
                'allowance2_id' => '',
            );

            $response_records[] = $data;

        }

        echo json_encode($response_records);




    }

}

