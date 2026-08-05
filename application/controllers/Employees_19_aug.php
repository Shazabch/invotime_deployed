<?php
class Employees extends CI_Controller
{

    function __construct()
    {
        parent::__construct();

        if (is_null(get_user())) {
            redirect("welcome");
            //var_dump($this->session->userdata('antelope_user'));
        }
    }

    public function index()
    {
        if (!is_page_permitted('employees')) {
            redirect_if_not_permitted();
        }

        $data['pageTitle'] = "Active Employees";
        $data['active_menu'] = "employees";
        $current_user = get_user();
        $cid = $current_user["company_id"];

        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];
        $limit_access_to_department = $current_user["limit_access_to_department"];

        $where_branch_1 = '';
        $where_branch_2 = '';
        $where_branch_3 = '';
        $where_department = '';

        if ($permissions_level == "Outlet") {
            $where_branch_1 = " AND employees.branch_id = $bid ";
            $where_branch_2 = " AND id = $bid ";
            $where_branch_3 = " AND permissions_level = 'Personal' ";
        }

        if ($limit_access_to_department == 'yes') {
            $department_id = $current_user["department_id"];
            $allowed_departments = get_allowed_departments($current_user);
            $where_department = " AND employees.department_id in ($allowed_departments) ";
        }

        //echo $where_department;


        $data['employees'] = $this->db->select('employees.*,roles.job_name,roles.permissions_level,positions.title,departments.name as department_name,sections.title as section_title,branches.name as branch_name, date_format(hired_on, "%d %b, %Y") as joining_date, hired_on as joining_date_sort', false)->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('positions', 'employees.position_id = positions.id', 'left')->join('departments', 'employees.department_id = departments.id', 'left')->join('sections', 'employees.section_id = sections.id', 'left')->join('branches', 'employees.branch_id = branches.id', 'left')->where('roles.exclude_from_system', 'no')->where('employees.company_id', $cid)->where('employee_status', 'active')->where("employees.deleted_at is null $where_branch_1 $where_department")->order_by("special_id", "asc")->get()->result();

        //echo $this->db->last_query();

        $data['branches'] = $this->db->select('id,name')->from('branches')->where("company_id = $cid $where_branch_2")->order_by("name", "asc")->get()->result();
        $data['departments'] = $this->db->select('id,name')->from('departments')->where('company_id', $cid)->order_by("name", "asc")->get()->result();
        $data['positions'] = $this->db->select('id,title')->from('positions')->where('company_id', $cid)->order_by("title", "asc")->get()->result();
        $data['roles'] = $this->db->select('id,job_name')->from('roles')->where("company_id = $cid $where_branch_3")->order_by("job_name", "asc")->get()->result();

        $data["reasons"] = $this->db->select('id, reason')->from('termination_reasons')->where('company_id', $cid)->where('deleted_at is null')->get()->result();
        $data["employee_banks"] = $this->db->select('id, name')->from('employee_banks')->order_by('name', 'asc')->get()->result();
        $data["employee_groups"] = $this->db->select('id, name')->from('employee_groups')->where('company_id', $cid)->get()->result();
        $data["sections"] = $this->db->select('id, title')->from('sections')->where('company_id', $cid)->get()->result();
        $data["company_id"] = $cid;

        //var_dump($data['employees']);
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);
        $this->load->view('employees', $data);
        $this->load->view('footer');
    }

    public function export()
    {
        $current_user = get_user();

        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];
        $limit_access_to_department = $current_user["limit_access_to_department"];

        $where_branch = "";
        $where_department = "";
        $is_outlet = false;
        if ($permissions_level === "Outlet") {
            $where_branch = " AND e.branch_id = $bid ";
            $is_outlet = true;
        }
        if ($limit_access_to_department === "yes") {
            $department_id = $current_user["department_id"];
            $where_department = " AND e.department_id = $department_id ";
        }

        $data = [];
        $data["type"] = "Active";
        $data["current_user"] = $current_user;
        $data["employees"] = $this->db->select("e.id, e.first_name name, e.special_id employee_id, p.title position, d.name department, s.title section, b.name outlet, date_format(hired_on, '%d %b, %Y')  joining_date, e.ic_no, e.mobile", false)
            ->from("employees e")->join("roles r", "e.role_id = r.id", "left")
            ->join("positions p", "e.position_id = p.id", "left")
            ->join("departments d", "e.department_id = d.id", "left")
            ->join("branches b", "e.branch_id = b.id", "left")
            ->join("sections s", "e.section_id = s.id", "left")
            ->where("r.exclude_from_system", "no")->where("e.company_id", $cid)
            ->where("employee_status", "active")->where("e.deleted_at is null $where_branch $where_department")
            ->order_by("e.special_id", "asc")->get()->result();

        $employees = $data["employees"];

        $outlet_name = $is_outlet ? $current_user["branch_name"] : "All";
        $file_name = $current_user["company_name"] . " - $outlet_name - Active Employees " . time();

        if ($this->input->get("type") === "excel") {
            $this->load->library("excel");
            $style = array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            );
            $object = new PHPExcel();
            $object->setActiveSheetIndex(0);
            $object->getDefaultStyle()->applyFromArray($style);

            $object->getActiveSheet()->setCellValue("A1", "Employees");
            $object->getActiveSheet()->setCellValue("B1", "(Active)");
            $object->getActiveSheet()->setCellValue("A2", "Generated On");
            $object->getActiveSheet()->setCellValue("B2", date("d/m/Y H:i:s"));
            $object->getActiveSheet()->setCellValue("C2", "Generated By");
            $object->getActiveSheet()->setCellValue("D2", $current_user["first_name"]);

            $object->getActiveSheet()->getStyle("A1:B1")->getFont()->setBold(true);

            $name_columns = array("Name", "Employee_ID", "Device_ID", "IC_No", "Phone", "Position", "Department","Section", "Joining_Date", "Outlet");
            $column = 0;
            foreach ($name_columns as $field) {
                $object->getActiveSheet()->setCellValueByColumnAndRow($column, 4, $field);
                $object->getActiveSheet()->getStyleByColumnAndRow($column, 4)->getFont()->setBold(true);
                $column++;
            }
            $row = 5;
            foreach ($employees as $employee) {
                $object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $employee->name);
                $object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $employee->employee_id);
                $object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $employee->id);
                $object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $employee->ic_no);
                $object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $employee->mobile);
                $object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $employee->position);
                $object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $employee->department);
                $object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $employee->section);
                $object->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $employee->joining_date);
                $object->getActiveSheet()->setCellValueByColumnAndRow(9, $row, $employee->outlet);
                $row++;
            }

            foreach (range('A', 'S') as $columnID) {
                $object->getActiveSheet()->getColumnDimension($columnID)
                    ->setAutoSize(true);
            }
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Disposition: attachment;filename=\"$file_name.xls\"");
            $object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
            $object_writer->save("php://output");
            insert_log("Simple", ["action" => "Exported,Employees Excel"]);
        } else {
            $this->load->view("employees/employees_pdf", $data);
            $html = $this->output->get_output();
            $this->dompdf->loadHtml($html);
            $this->dompdf->setPaper("A4", "landscape");
            $this->dompdf->render();
            // $this->dompdf->stream($file_name, array("Attachment" => 0));
            $output = $this->dompdf->output();
            $file_name = $file_name . ".pdf";
            $new_file = "uploads/summary/" . $file_name;
            file_put_contents($new_file, $output);

            $path = "uploads/summary/" . $file_name;

            header('Content-Type: application/pdf');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: inline; filename=" . $file_name);

            readfile($path);
            unlink($path);
            insert_log("Simple", ["action" => "Exported,Employees PDF"]);
        }
    }

    public function terminated()
    {
        if (!is_page_permitted('terminated')) {
            redirect_if_not_permitted();
        }

        $data['pageTitle'] = "Terminated Employees";
        $data['active_menu'] = "employees/terminated";
        $current_user = get_user();
        $cid = $current_user["company_id"];

        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];
        $limit_access_to_department = $current_user["limit_access_to_department"];


        $where_branch_1 = '';
        $where_branch_2 = '';
        $where_branch_3 = '';
        $where_department = '';

        if ($permissions_level == "Outlet") {
            $where_branch_1 = " AND employees.branch_id = $bid ";
            $where_branch_2 = " AND id = $bid ";
            $where_branch_3 = " AND permissions_level = 'Personal' ";
        }

        if ($limit_access_to_department == 'yes') {
            $department_id = $current_user["department_id"];
            $allowed_departments = get_allowed_departments($current_user);
            $where_department = " AND employees.department_id in ($allowed_departments) ";
        }

        //echo $where_department;


        $data['employees'] = $this->db->select('employees.*,roles.job_name,roles.permissions_level,positions.title,departments.name as department_name,branches.name as branch_name, date_format(termination_date, "%d %b, %Y") as termination_date, date_format(termination_notice_date, "%d %b, %Y") as termination_notice_date, termination_date as termination_date_sort, termination_notice_date as termination_notice_date_sort, tr.reason as termination_reason_text', false)->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('positions', 'employees.position_id = positions.id', 'left')->join('departments', 'employees.department_id = departments.id', 'left')->join('branches', 'employees.branch_id = branches.id', 'left')->join('termination_reasons tr', 'tr.id = employees.termination_reason', 'left')->where('roles.exclude_from_system', 'no')->where('employees.company_id', $cid)->where('employee_status', 'terminated')->where("employees.deleted_at is null $where_branch_1 $where_department")->order_by("special_id", "asc")->get()->result();

        //echo $this->db->last_query();

        $data['branches'] = $this->db->select('id,name')->from('branches')->where("company_id = $cid $where_branch_2")->order_by("name", "asc")->get()->result();
        $data['departments'] = $this->db->select('id,name')->from('departments')->where('company_id', $cid)->order_by("name", "asc")->get()->result();
        $data['positions'] = $this->db->select('id,title')->from('positions')->where('company_id', $cid)->order_by("title", "asc")->get()->result();
        $data['roles'] = $this->db->select('id,job_name')->from('roles')->where("company_id = $cid $where_branch_3")->order_by("job_name", "asc")->get()->result();
        $data["reasons"] = $this->db->select('id, reason')->from('termination_reasons')->where('company_id', $cid)->where('deleted_at is null')->get()->result();

        //var_dump($data['employees']);
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);
        $this->load->view('employees_terminated');
        $this->load->view('footer');
    }

    public function terminated_export()
    {
        $current_user = get_user();

        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];
        $limit_access_to_department = $current_user["limit_access_to_department"];

        $is_outlet = false;
        $where_branch = "";
        $where_department = "";
        if ($permissions_level === "Outlet") {
            $where_branch = " AND e.branch_id = $bid ";
            $is_outlet = true;
        }
        if ($limit_access_to_department === "yes") {
            $department_id = $current_user["department_id"];
            $where_department = " AND e.department_id = $department_id ";
        }

        $data = [];
        $data["type"] = "Terminated";
        $data["title"] = "Employees (Terminated)";
        $data["current_user"] = $current_user;
        $data["employees"] = $this->db->select("e.first_name name, e.special_id employee_id, e.termination_type, date_format(e.termination_date, '%d %b, %Y') termination_date, tr.reason, date_format(e.termination_notice_date, '%d %b, %Y') notice_date, e.ic_no, e.mobile", false)
            ->from("employees e")->join("roles r", "e.role_id = r.id", "left")
            ->join("departments d", "e.department_id = d.id", "left")
            ->join("branches b", "e.branch_id = b.id", "left")
            ->join("termination_reasons tr", "e.termination_reason = tr.id", "left")
            ->where("r.exclude_from_system", "no")->where("e.company_id", $cid)
            ->where("employee_status", "terminated")->where("e.deleted_at is null $where_branch $where_department")
            ->order_by("e.special_id", "asc")->get()->result();

        $employees = $data["employees"];
        $outlet_name = $is_outlet ? $current_user["branch_name"] : "All";
        $file_name = $current_user["company_name"] . " - $outlet_name - Terminated Employees " . time();

        if ($this->input->get("type") === "excel") {
            $this->load->library("excel");
            $style = array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            );
            $object = new PHPExcel();
            $object->setActiveSheetIndex(0);
            $object->getDefaultStyle()->applyFromArray($style);

            $object->getActiveSheet()->setCellValue("A1", "Employees");
            $object->getActiveSheet()->setCellValue("B1", "(Terminated)");
            $object->getActiveSheet()->setCellValue("A2", "Generated On");
            $object->getActiveSheet()->setCellValue("B2", date("d/m/Y H:i:s"));
            $object->getActiveSheet()->setCellValue("C2", "Generated By");
            $object->getActiveSheet()->setCellValue("D2", $current_user["first_name"]);

            $object->getActiveSheet()->getStyle("A1:B1")->getFont()->setBold(true);

            $name_columns = array("Name", "Employee ID", "IC NO.", "Phone", "Termination Type", "Termination Date", "Reason", "Notice Date");
            $column = 0;
            foreach ($name_columns as $field) {
                $object->getActiveSheet()->setCellValueByColumnAndRow($column, 4, $field);
                $object->getActiveSheet()->getStyleByColumnAndRow($column, 4)->getFont()->setBold(true);
                $column++;
            }
            $row = 5;
            foreach ($employees as $employee) {
                $object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $employee->name);
                $object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $employee->employee_id);
                $object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $employee->ic_no);
                $object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $employee->mobile);
                $object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $employee->termination_type);
                $object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $employee->termination_date);
                $object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $employee->reason);
                $object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $employee->notice_date);
                $row++;
            }

            foreach (range('A', 'S') as $columnID) {
                $object->getActiveSheet()->getColumnDimension($columnID)
                    ->setAutoSize(true);
            }
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Disposition: attachment;filename=\"$file_name.xls\"");
            $object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
            $object_writer->save("php://output");
            insert_log("Simple", ["action" => "Exported,Terminated Employees Excel"]);
        } else {
            $this->load->view("employees/terminated_pdf", $data);
            $html = $this->output->get_output();
            $this->dompdf->loadHtml($html);
            $this->dompdf->setPaper("A4", "landscape");
            $this->dompdf->render();
            $this->dompdf->stream($file_name, array("Attachment" => 0));
            insert_log("Simple", ["action" => "Exported,Terminated Employees PDF"]);
        }
    }

    public function resigned()
    {
        if (!is_page_permitted('resigned')) {
            redirect_if_not_permitted();
        }

        $data['pageTitle'] = "Resigned Employees";
        $data['active_menu'] = "employees/resigned";
        $current_user = get_user();
        $cid = $current_user["company_id"];

        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];
        $limit_access_to_department = $current_user["limit_access_to_department"];


        $where_branch_1 = '';
        $where_branch_2 = '';
        $where_branch_3 = '';
        $where_department = '';

        if ($permissions_level == "Outlet") {
            $where_branch_1 = " AND employees.branch_id = $bid ";
            $where_branch_2 = " AND id = $bid ";
            $where_branch_3 = " AND permissions_level = 'Personal' ";
        }

        if ($limit_access_to_department == 'yes') {
            $department_id = $current_user["department_id"];
            $allowed_departments = get_allowed_departments($current_user);
            $where_department = " AND employees.department_id in ($allowed_departments) ";
        }

        //echo $where_department;


        $data['employees'] = $this->db->select('employees.*,roles.job_name,roles.permissions_level,positions.title,departments.name as department_name,branches.name as branch_name, date_format(resignation_date, "%d %b, %Y") as resignation_date, resignation_date as resignation_date_sort, date_format(resignation_notice_date, "%d %b, %Y") as resignation_notice_date, resignation_notice_date as resignation_notice_date_sort', false)->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('positions', 'employees.position_id = positions.id', 'left')->join('departments', 'employees.department_id = departments.id', 'left')->join('branches', 'employees.branch_id = branches.id', 'left')->where('roles.exclude_from_system', 'no')->where('employees.company_id', $cid)->where('employee_status', 'resigned')->where("employees.deleted_at is null $where_branch_1 $where_department")->order_by("special_id", "asc")->get()->result();

        //echo $this->db->last_query();

        $data['branches'] = $this->db->select('id,name')->from('branches')->where("company_id = $cid $where_branch_2")->order_by("name", "asc")->get()->result();
        $data['departments'] = $this->db->select('id,name')->from('departments')->where('company_id', $cid)->order_by("name", "asc")->get()->result();
        $data['positions'] = $this->db->select('id,title')->from('positions')->where('company_id', $cid)->order_by("title", "asc")->get()->result();
        $data['roles'] = $this->db->select('id,job_name')->from('roles')->where("company_id = $cid $where_branch_3")->order_by("job_name", "asc")->get()->result();
        $data["reasons"] = $this->db->select('id, reason')->from('termination_reasons')->where('company_id', $cid)->where('deleted_at is null')->get()->result();

        //var_dump($data['employees']);
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);
        $this->load->view('employees_resigned');
        $this->load->view('footer');
    }

    public function resigned_export()
    {
        $current_user = get_user();

        $cid = $current_user["company_id"];
        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];
        $limit_access_to_department = $current_user["limit_access_to_department"];

        $is_outlet = false;
        $where_branch = "";
        $where_department = "";
        if ($permissions_level === "Outlet") {
            $where_branch = " AND e.branch_id = $bid ";
            $is_outlet = true;
        }
        if ($limit_access_to_department === "yes") {
            $department_id = $current_user["department_id"];
            $where_department = " AND e.department_id = $department_id ";
        }

        $data = [];
        $data["type"] = "Resigned";
        $data["title"] = "Employees (Resigned)";
        $data["current_user"] = $current_user;
        $data["employees"] = $this->db->select("e.first_name name, e.special_id employee_id, e.resignation_type, date_format(e.resignation_date, '%d %b, %Y') resignation_date, e.resignation_reason, date_format(e.resignation_notice_date, '%d %b, %Y') notice_date, e.ic_no, e.mobile", false)
            ->from("employees e")->join("roles r", "e.role_id = r.id", "left")
            ->join("departments d", "e.department_id = d.id", "left")
            ->join("branches b", "e.branch_id = b.id", "left")
            ->where("r.exclude_from_system", "no")->where("e.company_id", $cid)
            ->where("employee_status", "resigned")->where("e.deleted_at is null $where_branch $where_department")
            ->order_by("e.special_id", "asc")->get()->result();

        $employees = $data["employees"];
        $outlet_name = $is_outlet ? $current_user["branch_name"] : "All";
        $file_name = $current_user["company_name"] . " - $outlet_name - Resigned Employees " . time();

        if ($this->input->get("type") === "excel") {
            $this->load->library("excel");
            $style = array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                )
            );
            $object = new PHPExcel();
            $object->setActiveSheetIndex(0);
            $object->getDefaultStyle()->applyFromArray($style);

            $object->getActiveSheet()->setCellValue("A1", "Employees");
            $object->getActiveSheet()->setCellValue("B1", "(Resigned)");
            $object->getActiveSheet()->setCellValue("A2", "Generated On");
            $object->getActiveSheet()->setCellValue("B2", date("d/m/Y H:i:s"));
            $object->getActiveSheet()->setCellValue("C2", "Generated By");
            $object->getActiveSheet()->setCellValue("D2", $current_user["first_name"]);

            $object->getActiveSheet()->getStyle("A1:B1")->getFont()->setBold(true);

            $name_columns = array("Name", "Employee ID", "IC NO.", "Phone", "Resignation Type", "Resignation Date", "Reason", "Notice Date");
            $column = 0;
            foreach ($name_columns as $field) {
                $object->getActiveSheet()->setCellValueByColumnAndRow($column, 4, $field);
                $object->getActiveSheet()->getStyleByColumnAndRow($column, 4)->getFont()->setBold(true);
                $column++;
            }
            $row = 5;
            foreach ($employees as $employee) {
                $object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $employee->name);
                $object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $employee->employee_id);
                $object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $employee->ic_no);
                $object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $employee->mobile);
                $object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $employee->resignation_type);
                $object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $employee->resignation_date);
                $object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $employee->resignation_reason);
                $object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $employee->notice_date);
                $row++;
            }

            foreach (range('A', 'S') as $columnID) {
                $object->getActiveSheet()->getColumnDimension($columnID)
                    ->setAutoSize(true);
            }
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Disposition: attachment;filename=\"$file_name.xls\"");
            $object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
            $object_writer->save("php://output");
            insert_log("Simple", ["action" => "Exported,Resigned Employees Excel"]);
        } else {
            $this->load->view("employees/resigned_pdf", $data);
            $html = $this->output->get_output();
            $this->dompdf->loadHtml($html);
            $this->dompdf->setPaper("A4", "landscape");
            $this->dompdf->render();
            $this->dompdf->stream($file_name, array("Attachment" => 0));
            insert_log("Simple", ["action" => "Exported,Resigned Employees PDF"]);
        }
    }


    public function getEmployees()
    {
        $cid = get_user()["company_id"];
        $data['employees'] = $this->db->select('employees.*,roles.job_name,positions.title,departments.name as department_name,branches.name as branch_name')->from('employees')->join('roles', 'employees.role_id = roles.id', 'left')->join('positions', 'employees.position_id = positions.id', 'left')->join('departments', 'employees.department_id = departments.id', 'left')->join('branches', 'employees.branch_id = branches.id', 'left')->where('employees.company_id', $cid)->where('employees.deleted_at is null')->order_by("employee_status", "asc")->order_by("first_name", "asc")->get()->result();
        $filtered = $this->load->view('employee_filter', $data, true);
        echo $filtered;
    }

    public function getSingleEmployee()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $id = $request->id;
        $current_user = get_user();
        $cid = $current_user["company_id"];
        $emp = $this->db->select('*')->select("date_format(license_expiry,'%d/%m/%Y') as license_expiry", false)->select("date_format(dob,'%d/%m/%Y') as dob", false)->select("date_format(hired_on,'%d/%m/%Y') as hired_on", false)->select("date_format(termination_date,'%d/%m/%Y') as termination_date", false)->select("date_format(termination_notice_date,'%d/%m/%Y') as termination_notice_date", false)->select("date_format(resignation_date,'%d/%m/%Y') as resignation_date", false)->select("date_format(resignation_notice_date,'%d/%m/%Y') as resignation_notice_date", false)->select("date_format(etc_on,'%d/%m/%Y') as etc_on", false)->from('employees')->where('id', $id)->get()->row();
        // print_r($emp);die;
        $this->db->select('group_id');
        $this->db->where('employee_id', $id);
        $emp_groups = $this->db->get('employee_groups_relation')->result();
        $list = implode(', ', array_column($emp_groups, 'group_id'));
        $emp->groups = $list;
        // print_r($emp);die;

        $emp->is_ot = ($emp->is_ot == 0) ? "no" : "yes";
        $emp->is_early_ot = ($emp->is_early_ot == 0) ? "no" : "yes";
        $emp->is_daily_waged = ($emp->is_daily_waged == 0) ? false : true;
        $emp->is_shift_hours = ($emp->is_shift_hours == 0) ? false : true;
        $emp->is_att_all = ($emp->is_att_all == 0) ? "no" : "yes";
        $emp->level = ($emp->level == null) ? '' : $emp->level;
        $emp->permanent_resident = ($emp->permanent_resident == 0) ? "no" : "yes";
        if ($emp->basic_wage == 0) {
            $emp->basic_wage = '';
        }
        $emp->new_password = '';

        $bid = $current_user["branch_id"];
        $permissions_level = $current_user["permissions_level"];

        $where_branch_1 = '';
        $where_branch_2 = '';
        $where_branch_3 = '';

        if ($permissions_level == "Outlet") {
            $where_branch_1 = " AND employees.branch_id = $bid ";
            $where_branch_2 = " AND id = $bid ";
            $where_branch_3 = " AND permissions_level = 'Personal' ";
        }

        $data['employee'] = $emp;
        $data['branches'] = $this->db->select('id,name')->from('branches')->where("company_id = $cid $where_branch_2")->get()->result();
        $data['departments'] = $this->db->select('id,name')->from('departments')->where('company_id', $cid)->get()->result();
        $data['roles'] = $this->db->select('id,job_name')->from('roles')->where('company_id', $cid)->get()->result();
        $data['positions'] = $this->db->select('id,title')->from('positions')->where('company_id', $cid)->get()->result();
        $data['sections'] = $this->db->select('id, title')->from('sections')->where('company_id', $cid)->get()->result();
        $data["reasons"] = $this->db->select('id, reason')->from('termination_reasons')->where('company_id', $cid)->where('deleted_at is null')->get()->result();
        $data["device_roles"] = ["Administrator" => "Manager", "User" => "User", "Register" => "Register", "Querier" => "Querier"];
        $data["races"] = ["Malay", "Chinese", "Indian", "Others"];
        $data["nationalities"] = ["Malaysian", "Others"];
        $data["ot_groups"] = [
            ["key" => "day", "value" => "Day"],
            ["key" => "hours", "value" => "Hours"]
        ];

        $data["employee_banks"] = $this->db->select('id, name')->from('employee_banks')->order_by('name', 'asc')->get()->result();

        $data["user_device_id"] = false;

        if ($emp->user_device_id != "" && $emp->user_device_id != null) {
            $data["user_device_id"] = true;
        }

        // echo $this->db->last_query();
        // die();
        echo json_encode($data);
    }

    public function delete_employee()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $this->db->set('deleted_at', 'NOW()', false)->where('id', $request->id)->update('employees');
    }

    public function reset_device()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $this->db->set('user_device_id', NULL)->where('id', $request->id)->update('employees');
    }

    public function access_all_outlet()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $this->db->set('sync_action', 'SetUserDataAll')->where('id', $request->id)->update('employees');
    }


    public function getPositions()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $cid = get_user()["company_id"];
        $data['positions'] = $this->db->select('id,title')->from('positions')->where('department_id', $request->department_id)->where('company_id', $cid)->get()->result();
        echo json_encode($data);
    }

    public function save()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $cid = get_user()["company_id"];
        $this->db->select('companies.package, companies.additional_staff, packages.max_outlets, packages.max_active_staff');
        $this->db->join('packages', 'packages.id = companies.package', 'left');
        $this->db->where('companies.id', $cid);
        $company_details = $this->db->get('companies')->row();
        // print_r($company_details);die;
        $company_employee_limit = $company_details->max_active_staff + $company_details->additional_staff;
        if ($company_employee_limit != 0) {
            $this->db->join('roles', 'roles.id = employees.role_id', 'left');
            $this->db->where('employees.company_id', $cid);
            $this->db->where('employees.employee_status', 'active');
            $this->db->where('roles.permissions_level', 'personal');
            $employees_of_company = $this->db->get('employees')->result();
            $employees_of_company_count = count($employees_of_company);
            // echo $employees_of_company_count;die;
            if ($employees_of_company_count >= $company_employee_limit) {
                $data['success'] = false;
                $data['msg'] = "You have reached the maximum quota for active employees. Please kindly contact administrator for upgrade.";
                echo json_encode($data);
                die;
            }
        }

        $emp_exist = $this->db->select('id')->from('employees')->where('special_id', $request->special_id)->where('company_id', $cid)->where('deleted_at is null')->get()->result();
        if (count($emp_exist) > 0) {
            $data['success'] = false;
            $data['msg'] = 'Employee ID already exists!';
        } else {
            $request->is_ot = ($request->is_ot == 'yes') ? 1 : 0;
            $request->is_early_ot = ($request->is_early_ot == 'yes') ? 1 : 0;
            $request->is_daily_waged = ($request->is_daily_waged == true) ? 1 : 0;
            $request->is_shift_hours = ($request->is_shift_hours == true) ? 1 : 0;
            $request->permanent_resident = ($request->permanent_resident == 'yes') ? 1 : 0;
            if (in_array($cid, companies_allowed_for_att_all()))
                $request->is_att_all = ($request->is_att_all == 'yes') ? 1 : 0;

            if ($request->dob != '') {
                $request->dob = str_replace('/', '-', $request->dob);
                $request->dob = date('Y-m-d', strtotime($request->dob));
            } else {
                $request->dob = null;
            }
            if ($request->hired_on != '') {
                $request->hired_on = str_replace('/', '-', $request->hired_on);
                $request->hired_on = date('Y-m-d', strtotime($request->hired_on));
            } else {
                $request->hired_on = null;
            }
            if ($request->license_expiry != '') {
                $request->license_expiry = str_replace('/', '-', $request->license_expiry);
                $request->license_expiry = date('Y-m-d', strtotime($request->license_expiry));
            } else {
                $request->license_expiry = null;
            }
            if ($request->etc_on != '') {
                $request->etc_on = str_replace('/', '-', $request->etc_on);
                $request->etc_on = date('Y-m-d', strtotime($request->etc_on));
            } else {
                $request->etc_on = null;
            }
            $emp_data = array(
                'first_name' => $request->first_name,
                'sex' => $request->sex,
                'dob' => $request->dob,
                'pob' => $request->pob,
                'company_id' => $cid,
                'race' => $request->race,
                'religion' => $request->religion,
                'nationality' => $request->nationality,
                'email' => $request->email,
                'ic_no' => $request->ic_no,
                'old_ic_no' => $request->old_ic_no,
                'branch_id' => $request->branch_id,
                'department_id' => $request->department_id,
                'role_id' => $request->role_id,
                // 'groups' => (implode(", ",$request->groups)),
                'position_id' => $request->position_id,
                'special_id' => $request->special_id,
                'grade' => $request->grade,
                'employment_type' => $request->employment_type,
                'hired_on' => $request->hired_on,
                'ic_passport' => $request->ic_passport,
                'perm_address' => $request->perm_address,
                'perm_address_city' => $request->perm_address_city,
                'perm_address_state' => $request->perm_address_state,
                'perm_address_postcode' => $request->perm_address_postcode,
                'temp_address' => $request->temp_address,
                'temp_address_city' => $request->temp_address_city,
                'temp_address_state' => $request->temp_address_state,
                'temp_address_postcode' => $request->temp_address_postcode,
                'telephone' => $request->telephone,
                'mobile' => $request->mobile,
                'marital_status' => $request->marital_status,
                'basic_wage' => $request->basic_wage,
                'epf_no' => $request->epf_no,
                'socso' => $request->socso,
                'eis' => $request->eis,
                'income_tax_no' => $request->income_tax_no,
                'income_tax_branch' => $request->income_tax_branch,
                'qr_barcode' => $request->qr_barcode,
                'bank_account_no' => $request->bank_account_no,
                'license_class' => $request->license_class,
                'license_no' => $request->license_no,
                'license_expiry' => $request->license_expiry,
                'is_ot' => $request->is_ot,
                'is_early_ot' => $request->is_early_ot,
                'is_daily_waged' => $request->is_daily_waged,
                'is_shift_hours' => $request->is_shift_hours,
                'employee_type' => $request->employee_type,
                'password' => md5($request->password),
                'device_role' => $request->device_role,
                'compassionate_leaves' => $request->compassionate_leaves,
                'paternity_leaves' => $request->paternity_leaves,
                'marriage_leaves' => $request->marriage_leaves,
                'hospitalisation_leaves' => $request->hospitalisation_leaves,
                'study_leaves' => $request->study_leaves,
                'replacement_leaves' => $request->replacement_leaves,
                'unpaid_leaves' => $request->unpaid_leaves,
                'emergency_leaves' => $request->emergency_leaves,
                'employee_bank_id' => $request->employee_bank_id == '' ? null : $request->employee_bank_id,
                'level' => $request->level,
                'payroll_branch_id' => $request->payroll_branch_id == '' ? null : $request->payroll_branch_id,
                'ot_group' => $request->ot_group,
                'special_incentive' => $request->special_incentive,
                'device_password' => $request->device_password,
                'permanent_resident' => $request->permanent_resident,
                'etc_on' => $request->etc_on,
                'etc_under' => $request->etc_under,
            );
            if (in_array($cid, companies_allowed_for_att_all())) {
                $emp_data['att_all_code'] = $request->att_all_code;
                $emp_data['att_all_desc'] = $request->att_all_desc;
                $emp_data['att_all_amount'] = $request->att_all_amount;
                $emp_data['is_att_all'] = $request->is_att_all;
            }

            if ($cid == 66) {
                $emp_data["ta_rate"] = $request->ta_rate ? $request->ta_rate : 0;
                $emp_data["ma_rate"] = $request->ma_rate ? $request->ma_rate : 0;
                $emp_data["ca_rate"] = $request->ca_rate ? $request->ca_rate : 0;
                $emp_data["spa_rate"] = $request->spa_rate ? $request->spa_rate : 0;
                $emp_data["aca_rate"] = $request->aca_rate ? $request->aca_rate : 0;
                $emp_data["fl_rate"] = $request->fl_rate ? $request->fl_rate : 0;
                $emp_data["cw_rate"] = $request->cw_rate ? $request->cw_rate : 0;
                $emp_data["mo_rate"] = $request->mo_rate ? $request->mo_rate : 0;
                $emp_data["aa_rate"] = $request->aa_rate ? $request->aa_rate : 0;
                $emp_data["shift1_rate"] = $request->shift1_rate ? $request->shift1_rate : 0;
                $emp_data["shift2_rate"] = $request->shift2_rate ? $request->shift2_rate : 0;
                $emp_data["shift3_rate"] = $request->shift3_rate ? $request->shift3_rate : 0;
            }

            if ($cid == 215) {
                $emp_data["aa_rate"] = $request->aa_rate ? $request->aa_rate : 0;
                $emp_data["nsa_rate"] = $request->nsa_rate ? $request->nsa_rate : 0;
            }

            if ($cid == 152) {
                $emp_data["aa_rate"] = $request->aa_rate ? $request->aa_rate : 0;
                $emp_data["ta_rate"] = $request->ta_rate ? $request->ta_rate : 0;
            }

            if ($cid == 196) {
                $emp_data["mi_mo_rate"] = $request->mi_mo_rate ? $request->mi_mo_rate : 0;
                $emp_data["lateness_deduction_99"] = $request->lateness_deduction_99 ? $request->lateness_deduction_99 : 0;
                $emp_data["lateness_deduction_100"] = $request->lateness_deduction_100 ? $request->lateness_deduction_100 : 0;
                $emp_data["rest_day_entitlement"] = $request->rest_day_entitlement ? $request->rest_day_entitlement : 0;
            }

            if ($cid == 206) {
                $emp_data["food_rate"] = $request->food_rate ? $request->food_rate : 0;
            }

            if ($cid == 146) {
                $min_worked_hours_meal = "13:00";
                if ($request->min_worked_hours_meal) {
                    $min_worked_hours_meal = $request->min_worked_hours_meal;
                }
                $emp_data["min_worked_hours_meal"] = $min_worked_hours_meal . ":00";
            }

            if ($this->db->insert('employees', $emp_data)) {
                $emp_id = $this->db->insert_id();
                $groups = $request->groups ? $request->groups : [];
                foreach ($groups as $group) {
                    // echo $group.' / '.$emp_id;die;
                    $abc['employee_id'] = $emp_id;
                    $abc['group_id'] = $group;
                    $this->db->insert('employee_groups_relation', $abc);
                }
                $data['success'] = true;
                $data['msg'] = "Employee created successfully!";
                $branch = $this->db->get_where("branches", ["id" => $request->branch_id])->row();

                $log_data = [
                    'action' => 'Added,Employee', 'target_id' => $emp_id, 'target_name' => $emp_data['first_name'],
                    'to_branch_id' => $branch->id, 'to_outlet' => $branch->name
                ];

                insert_log("Employees", $log_data);
            } else {
                $data['success'] = false;
                $data['msg'] = "Employee could not add!";
            }
        }
        echo json_encode($data);
    }

    public function getDeductions()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        $employee_type = $request->employee_type;
        $basic_wage = $request->basic_wage;
        $eis = 0;
        $socso = 0;
        $epf = 0;
        if ($basic_wage != 0) {
            $eis_row = $this->db->select('employee')->from('eis')->where('start <', $basic_wage)->where('end >=', $basic_wage)->get()->row();
            if ($eis_row) {
                $eis = $eis_row->employee;
            }

            $socso_row = $this->db->select('employee')->from('socso')->where('start <', $basic_wage)->where('end >=', $basic_wage)->get()->row();
            if ($socso_row) {
                $socso = $socso_row->employee;
            }

            $epf_row = $this->db->select('employee')->from('epf_' . $employee_type)->where('start <= ', $basic_wage)->where('end >= ', $basic_wage)->get()->row();
            if ($epf_row) {
                $epf = $epf_row->employee;
            }
        }

        $data["eis"] = $eis;
        $data["socso"] = $socso;
        $data["epf"] = $epf;


        echo json_encode($data);
    }

    public function update()
    {
        $postdata = file_get_contents("php://input");
        $request = json_decode($postdata);
        // print_r((implode(",",$request->groups)));die;
        $id = $request->id;
        $current_user = get_user();
        $cid = $current_user["company_id"];
        $this->db->select('companies.package, companies.additional_staff, packages.max_outlets, packages.max_active_staff');
        $this->db->join('packages', 'packages.id = companies.package', 'left');
        $this->db->where('companies.id', $cid);
        $company_details = $this->db->get('companies')->row();
        // print_r($company_details);die;
        $company_employee_limit = $company_details->max_active_staff + $company_details->additional_staff;
        if ($company_employee_limit != 0) {
            $this->db->join('roles', 'roles.id = employees.role_id', 'left');
            $this->db->where('employees.company_id', $cid);
            $this->db->where('employees.employee_status', 'active');
            $this->db->where('roles.permissions_level', 'personal');
            $employees_of_company = $this->db->get('employees')->result();
            $employees_of_company_count = count($employees_of_company);
            // echo $employees_of_company_count;die;
            if ($employees_of_company_count >= $company_employee_limit) {
                $data['success'] = false;
                $data['msg'] = "You have reached the maximum quota for active employees. Please kindly contact administrator for upgrade.";
                echo json_encode($data);
                die;
            }
        }
        $emp_exist = $this->db->select('id')->from('employees')->where('special_id', $request->special_id)->where('deleted_at is null')->where('id !=', $id)->where('company_id', $cid)->get()->row();

        if ($emp_exist) {
            echo json_encode([
                'success' => false,
                'duplicate' => true,
                'msg' => 'Employee ID already exists!'
            ]);
            return;
        }

        $this->db->where('employee_id', $id);
        $this->db->delete('employee_groups_relation');

        // $employee_groups_data = $this->db->select('*')->from('employee_groups_relation')->where('employee_id', $id)->get()->result();
        // print_r($employee_groups_data);die;

        $request->is_ot = ($request->is_ot == 'yes') ? 1 : 0;
        $request->is_early_ot = ($request->is_early_ot == 'yes') ? 1 : 0;
        $request->is_daily_waged = ($request->is_daily_waged == true) ? 1 : 0;
        $request->is_shift_hours = ($request->is_shift_hours == true) ? 1 : 0;
        $request->permanent_resident = ($request->permanent_resident == 'yes') ? 1 : 0;
        if (in_array($cid, companies_allowed_for_att_all()))
            $request->is_att_all = ($request->is_att_all == 'yes') ? 1 : 0;

        if ($request->dob != '') {
            $request->dob = str_replace('/', '-', $request->dob);
            $request->dob = date('Y-m-d', strtotime($request->dob));
        } else {
            $request->dob = null;
        }
        if ($request->hired_on != '') {
            $request->hired_on = str_replace('/', '-', $request->hired_on);
            $request->hired_on = date('Y-m-d', strtotime($request->hired_on));
        } else {
            $request->hired_on = null;
        }
        if ($request->license_expiry != '') {
            $request->license_expiry = str_replace('/', '-', $request->license_expiry);
            $request->license_expiry = date('Y-m-d', strtotime($request->license_expiry));
        } else {
            $request->license_expiry = null;
        }
        if ($request->termination_date != '') {
            $request->termination_date = str_replace('/', '-', $request->termination_date);
            $request->termination_date = date('Y-m-d', strtotime($request->termination_date));
        } else {
            $request->termination_date = null;
        }
        if ($request->termination_notice_date != '') {
            $request->termination_notice_date = str_replace('/', '-', $request->termination_notice_date);
            $request->termination_notice_date = date('Y-m-d', strtotime($request->termination_notice_date));
        } else {
            $request->termination_notice_date = null;
        }
        if ($request->resignation_date != '') {
            $request->resignation_date = str_replace('/', '-', $request->resignation_date);
            $request->resignation_date = date('Y-m-d', strtotime($request->resignation_date));
        } else {
            $request->resignation_date = null;
        }
        if ($request->resignation_notice_date != '') {
            $request->resignation_notice_date = str_replace('/', '-', $request->resignation_notice_date);
            $request->resignation_notice_date = date('Y-m-d', strtotime($request->resignation_notice_date));
        } else {
            $request->resignation_notice_date = null;
        }
        if ($request->etc_on != '') {
            $request->etc_on = str_replace('/', '-', $request->etc_on);
            $request->etc_on = date('Y-m-d', strtotime($request->etc_on));
        } else {
            $request->etc_on = null;
        }
        if ($request->new_password != '') {
            $new_password = md5($request->new_password);
        } else {
            $new_password = $request->password;
        }

        if ($request->employee_status == 'active') {
            $request->termination_type = null;
            $request->termination_date = null;
            $request->termination_reason = null;
            $request->termination_notice_date = null;
            $request->resignation_type = null;
            $request->resignation_date = null;
            $request->resignation_reason = null;
            $request->resignation_notice_date = null;
        }
        $emp_data = array(
            'first_name' => $request->first_name,
            'sex' => $request->sex,
            'dob' => $request->dob,
            'pob' => $request->pob,
            'race' => $request->race,
            'religion' => $request->religion,
            'nationality' => $request->nationality,
            'email' => $request->email,
            'ic_no' => $request->ic_no,
            'old_ic_no' => $request->old_ic_no,
            'branch_id' => $request->branch_id,
            'department_id' => $request->department_id,
            'role_id' => $request->role_id,
            // 'groups'=> implode(", ",$request->groups),
            'position_id' => $request->position_id,
            'section_id' => $request->section_id,
            'special_id' => $request->special_id,
            'grade' => $request->grade,
            'employment_type' => $request->employment_type,
            'hired_on' => $request->hired_on,
            'ic_passport' => $request->ic_passport,
            'perm_address' => $request->perm_address,
            'perm_address_city' => $request->perm_address_city,
            'perm_address_state' => $request->perm_address_state,
            'perm_address_postcode' => $request->perm_address_postcode,
            'temp_address' => $request->temp_address,
            'temp_address_city' => $request->temp_address_city,
            'temp_address_state' => $request->temp_address_state,
            'temp_address_postcode' => $request->temp_address_postcode,
            'telephone' => $request->telephone,
            'mobile' => $request->mobile,
            'marital_status' => $request->marital_status,
            'basic_wage' => $request->basic_wage,
            'epf_no' => $request->epf_no,
            'socso' => $request->socso,
            'eis' => $request->eis,
            'income_tax_no' => $request->income_tax_no,
            'income_tax_branch' => $request->income_tax_branch,
            'qr_barcode' => $request->qr_barcode,
            'bank_account_no' => $request->bank_account_no,
            'license_class' => $request->license_class,
            'license_no' => $request->license_no,
            'license_expiry' => $request->license_expiry,
            'is_ot' => $request->is_ot,
            'is_early_ot' => $request->is_early_ot,
            'is_daily_waged' => $request->is_daily_waged,
            'is_shift_hours' => $request->is_shift_hours,
            'employee_type' => $request->employee_type,
            'employee_status' => $request->employee_status,
            'password' => $new_password,
            'device_role' => $request->device_role,
            'compassionate_leaves' => $request->compassionate_leaves,
            'paternity_leaves' => $request->paternity_leaves,
            'marriage_leaves' => $request->marriage_leaves,
            'hospitalisation_leaves' => $request->hospitalisation_leaves,
            'study_leaves' => $request->study_leaves,
            'replacement_leaves' => $request->replacement_leaves,
            'unpaid_leaves' => $request->unpaid_leaves,
            'emergency_leaves' => $request->emergency_leaves,
            'termination_type' => $request->termination_type,
            'termination_reason' => $request->termination_reason,
            'termination_date' => $request->termination_date,
            'termination_notice_date' => $request->termination_notice_date,
            'resignation_type' => $request->resignation_type,
            'resignation_reason' => $request->resignation_reason,
            'resignation_date' => $request->resignation_date,
            'resignation_notice_date' => $request->resignation_notice_date,
            'employee_bank_id' => $request->employee_bank_id == '' ? null : $request->employee_bank_id,
            'level' => $request->level,
            'payroll_branch_id' => $request->payroll_branch_id == '' ? null : $request->payroll_branch_id,
            'ot_group' => $request->ot_group,
            'special_incentive' => $request->special_incentive,
            'device_password' => $request->device_password,
            'permanent_resident' => $request->permanent_resident,
            'etc_on' => $request->etc_on,
            'etc_under' => $request->etc_under,
        );
        $emp_data['att_all_code'] = $request->att_all_code;
        $emp_data['att_all_desc'] = $request->att_all_desc;
        $emp_data['att_all_amount'] = $request->att_all_amount;
        $emp_data['is_att_all'] = $request->is_att_all;

        if ($cid == 66) {
            $emp_data["ta_rate"] = $request->ta_rate ? $request->ta_rate : 0;
            $emp_data["ma_rate"] = $request->ma_rate ? $request->ma_rate : 0;
            $emp_data["ca_rate"] = $request->ca_rate ? $request->ca_rate : 0;
            $emp_data["spa_rate"] = $request->spa_rate ? $request->spa_rate : 0;
            $emp_data["aca_rate"] = $request->aca_rate ? $request->aca_rate : 0;
            $emp_data["fl_rate"] = $request->fl_rate ? $request->fl_rate : 0;
            $emp_data["cw_rate"] = $request->cw_rate ? $request->cw_rate : 0;
            $emp_data["mo_rate"] = $request->mo_rate ? $request->mo_rate : 0;
            $emp_data["aa_rate"] = $request->aa_rate ? $request->aa_rate : 0;
            $emp_data["shift1_rate"] = $request->shift1_rate ? $request->shift1_rate : 0;
            $emp_data["shift2_rate"] = $request->shift2_rate ? $request->shift2_rate : 0;
            $emp_data["shift3_rate"] = $request->shift3_rate ? $request->shift3_rate : 0;
        }

        if ($cid == 215) {
            $emp_data["aa_rate"] = $request->aa_rate ? $request->aa_rate : 0;
            $emp_data["nsa_rate"] = $request->nsa_rate ? $request->nsa_rate : 0;
        }

        if ($cid == 152) {
            $emp_data["aa_rate"] = $request->aa_rate ? $request->aa_rate : 0;
            $emp_data["ta_rate"] = $request->ta_rate ? $request->ta_rate : 0;
        }

        if ($cid == 196) {
            $emp_data["mi_mo_rate"] = $request->mi_mo_rate ? $request->mi_mo_rate : 0;
            $emp_data["lateness_deduction_99"] = $request->lateness_deduction_99 ? $request->lateness_deduction_99 : 0;
            $emp_data["lateness_deduction_100"] = $request->lateness_deduction_100 ? $request->lateness_deduction_100 : 0;
            $emp_data["rest_day_entitlement"] = $request->rest_day_entitlement ? $request->rest_day_entitlement : 0;
        }

        if ($cid == 206) {
            $emp_data["food_rate"] = $request->food_rate ? $request->food_rate : 0;
        }

        if ($cid == 146) {
            $min_worked_hours_meal = "13:00";
            if ($request->min_worked_hours_meal) {
                $min_worked_hours_meal = $request->min_worked_hours_meal;
            }
            $emp_data["min_worked_hours_meal"] = $min_worked_hours_meal . ":00";
        }
        
        $groups = $request->groups ? $request->groups : [];
        foreach ($groups as $group) {
            // echo $group.' / '.$emp_id;die;
            $abc['employee_id'] = $id;
            $abc['group_id'] = $group;
            $this->db->insert('employee_groups_relation', $abc);
        }

        $this->db->where('id', $id)->update('employees', $emp_data);

        $log_data = ["action" => "Edited,Employee", "target_id" => $id, "target_name" => $emp_data["first_name"]];

        if ($request->current_branch_id != $request->branch_id) {
            if ($request->transfer_date != '') {
                $request->transfer_date = str_replace('/', '-', $request->transfer_date);
                $request->transfer_date = date('Y-m-d', strtotime($request->transfer_date));
            } else {
                $request->transfer_date = null;
            }

            $data = array(
                'company_id' => $current_user["company_id"],
                'transfer_date' => $request->transfer_date,
                'transfer_reason' => $request->transfer_reason,
                'employee_id' => $id,
                'old_branch_id' => $request->current_branch_id,
                'branch_id' => $request->branch_id
            );
            $this->db->insert('transfers', $data);
            $log_data["to_branch_id"] = $request->branch_id;
            $log_data["from_branch_id"] = $request->current_branch_id;
            $old_branch_name = $this->db->select("name")->from("branches")->where("id", $request->current_branch_id)->get()->row()->name;
            $log_data["from_outlet"] = $old_branch_name;
            $branch_name = $this->db->select("name")->from("branches")->where("id", $request->branch_id)->get()->row()->name;

            $log_data["to_outlet"] = $branch_name;
        } else if ($request->current_emp_status !== $request->employee_status) {
            $log_data["from_branch_id"] = $request->current_branch_id;
            $old_branch_name = $this->db->select("name")->from("branches")->where("id", $request->current_branch_id)->get()->row()->name;
            $log_data["from_outlet"] = $old_branch_name;
            if ($request->employee_status === 'terminated') {
                $log_data['action'] = "Terminated,Employee";
            } else if ($request->employee_status === 'resigned') {
                $log_data['action'] = 'Resigned,Employee';
            } else if ($request->employee_status === 'active') {
                $log_data['action'] = 'Activated,Employee';
            }
        }

        insert_log('Employees', $log_data);

        $data["success"] = true;
        echo json_encode($data);
    }
    public function get_datalist_options()
    {
        $user = (object)get_user();
        $cid = $user->company_id;

        $data["success"] = true;
        $distinct_races = $this->db->select('distinct(race) race')->from('employees')->where('company_id', $cid)->where('race IS NOT NULL')->where("race <> ''")->order_by('race', 'asc')->get()->result();
        $distinct_nationalities = $this->db->select('distinct(nationality) nationality')->from('employees')->where('company_id', $cid)->where('nationality IS NOT NULL')->where("nationality <> ''")->order_by('nationality', 'asc')->get()->result();

        $data['distinct_races'] = array_map(function ($race) {
            return $race->race;
        }, $distinct_races);

        $data['distinct_nationalities'] = array_map(function ($nationality) {
            return $nationality->nationality;
        }, $distinct_nationalities);

        return $this->output
            ->set_status_header(200)
            ->set_content_type("application/json")
            ->set_output(json_encode($data));
    }
}
