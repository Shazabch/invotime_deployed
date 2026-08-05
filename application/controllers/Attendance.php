<?php defined('BASEPATH') or exit('No direct script access allowed');

class Attendance extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (is_null(get_user())) redirect('welcome');
    }

    public function absenties()
    {
        if (!is_page_permitted('absenties')) {
            redirect_if_not_permitted();
        }

        $current_user = (object)get_user();

        $data['pageTitle'] = "Absent Sheet";
        $data['active_menu'] = "attendance/absenties";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);

        $data["filters_form_action"] = "attendance/absenties";

        render_ab_filters($data);
        $where_filter = $data["where_filter"];

        $total_records = $this->db->query("SELECT COUNT(DISTINCT employees.id) as total_records FROM employees INNER JOIN roles ON employees.role_id = roles.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = employees.id WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND roles.exclude_from_system = 'no' AND $where_filter")->row()->total_records;
        $limit = 20;
        $total_pages = ceil($total_records / $limit);
        $page = 1;
        if (!empty($this->input->get("page"))) {
            $page = $this->input->get("page");
        }
        $skip = ($page - 1) * $limit;

        $employees = $this->db->query("SELECT employees.id,special_id, first_name, branch_id FROM employees INNER JOIN roles ON employees.role_id = roles.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = employees.id WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND roles.exclude_from_system = 'no' AND  $where_filter 
            GROUP BY employees.id,special_id, first_name, branch_id ORDER BY special_id LIMIT $skip,$limit")->result();

        $employees_ids = array();
        foreach ($employees as $employee) {
            $employees_ids[] = $employee->id;
        }

        $first_day = $data['selected_year'] . '-' . $data['selected_month'] . '-' . '01';
        $last_day = date('Y-m-t', strtotime($first_day));

        $public_holidays_all = get_public_holidays();

        foreach ($employees as &$employee) {
            $absenty_data = calculate_absenties($employee->id, $first_day, $last_day, $employee);
            $employee->data = $absenty_data->absenties;
        }

        $data['user'] = $current_user;
        $data['employees'] = $employees;
        $data['public_holidays'] = $public_holidays_all;

        $data["filters"] = $this->load->view('attendance/ab_filters', $data, true);
        $currentURL = current_url();
        $query_string = http_build_query($_GET);
        $data["pagination_url"] = $currentURL . '?' . $query_string;
        $data['total_pages'] = $total_pages;
        $data['page'] = $page;
        $data['skip'] = $skip;
        $data["attendance_sheet_export_url"] = base_url() . "attendance/absenties_pdf?$query_string";


        $this->load->view('attendance/absenties', $data);
        $this->load->view('footer', $data);
    }

    public function absenties_pdf()
    {

        echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>';
        echo '<div style="height:100%; background-color: #EFF3F6">';
        echo '</br> <b><div style="" class="panel panel-primary container"></br><div style="" class="panel panel-primary"><div class="panel-body"><center><h3><b>Do not close this window until the export is completed!</b></h3></center></div></div></br><div style="margin-left:40px; margin-top:40px;">Extracting Data <div></div> </b></br>';
        echo '<div id="loading1"><div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div></div></div><br />';
        echo '</br></br> <b>Generating File</b> </br><br>';
        echo '<div id="loading2"><div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">0%</div></div></div><br />';
        echo str_pad('', 4096);

        ob_flush();
        flush();

        $current_user = (object)get_user();

        $data["filters_form_action"] = "attendance/absenties";
        render_ab_filters($data);
        $where_filter = $data["where_filter"];

        $employees = $this->db->query("SELECT employees.id,special_id, first_name, branch_id FROM employees INNER JOIN roles ON employees.role_id = roles.id LEFT JOIN employee_groups_relation egr ON egr.employee_id = employees.id WHERE employees.deleted_at IS NULL AND employee_status = 'active' AND roles.exclude_from_system = 'no' AND  $where_filter ORDER BY special_id")->result();

        $employees_ids = array();
        foreach ($employees as $employee) {
            $employees_ids[] = $employee->id;
        }

        $first_day = $data['selected_year'] . '-' . $data['selected_month'] . '-' . '01';
        $last_day = date('Y-m-t', strtotime($first_day));

        $public_holidays_all = get_public_holidays();

        $total = count($employees);
        $count = 1;

        foreach ($employees as $key => &$employee) {
            $absenty_data = calculate_absenties($employee->id, $first_day, $last_day, $employee);
            if ($absenty_data->has_absenties === false) unset($employees[$key]);
            else {
                $employee->data = $absenty_data->absenties;
            }
            $percentage = floor(($count++ / $total) * 100);

            echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
            echo str_pad('', 4096);
            ob_flush();
            flush();
        }

        $data['current_user'] = $current_user;
        $data['employees'] = $employees;
        $data['public_holidays'] = $public_holidays_all;


        $html2 = $this->load->view('attendance/absenties_pdf', $data, true);
        $this->dompdf->reset();
        $this->dompdf->loadHtml($html2);
        $this->dompdf->setPaper("A4", "landscape");
        $this->dompdf->render();
        $file_name = "Absent Sheet - $first_day to $last_day " . time() . ".pdf";

        $output = $this->dompdf->output();
        $new_file = "uploads/summary/" . $file_name;
        file_put_contents($new_file, $output);

        echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
        echo str_pad('', 4096);
        ob_flush();
        flush();

        insert_log("Simple", ["action" => "Exported,Absent Sheet"]);

        header('Content-Type: application/pdf');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=" . $file_name);
        echo '</br> <br> <b>Export Completed</b> </br>';

        $path = base_url() . $new_file;

        echo "</br> <center><div style='width:40%'><a href='$path' download='$file_name'><button style='margin-bottom: 40px' class='btn btn-primary btn-block'>Download File</button></a></div></center>";

        echo '</div>';
    }
}
