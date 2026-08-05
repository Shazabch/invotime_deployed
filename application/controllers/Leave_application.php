<?php

class Leave_application extends CI_Controller
{
    function __construct()
    {
        parent::__construct();

        if (is_null(get_user())) {
            redirect("welcome");
        }
    }

    function import()
    {
        $data['pageTitle'] = "Import Leave Application";
        $data['active_menu'] = "leave_application/import";
        $this->load->view('header', $data);
        $data["menus"] = get_menus();
        $this->load->view('sidebar', $data);


        $this->load->view('leave_application_import', $data);
        $this->load->view('footer', $data);
    }

    function import_file()
    {
        $current_user = get_user();
        $cid = $current_user["company_id"];

        $success = false;
        $message = "";
        $errors = [];
        $count = 0;

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $filePath = $_FILES['file']['tmp_name'];

            $this->load->library("excel");

            $file = PHPExcel_IOFactory::createReader('Excel2007')->load($filePath);
            $sheet = $file->getActiveSheet();
            $data = $sheet->toArray();

            if (empty($data) || !isset($data[0])) {
                $message = "The uploaded file is empty.";
                echo json_encode(array("success" => $success, "message" => $message, "errors" => $errors, "count" => $count));
                return;
            }

            $header = array_map('trim', $data[0]);

            if (!(in_array("Employee", $header) && in_array("Leave Date", $header) && in_array("Leave Type", $header))) {
                $message = "Invalid file format. Required columns: Employee, Leave Date, Leave Type.";
                echo json_encode(array("success" => $success, "message" => $message, "errors" => $errors, "count" => $count));
                return;
            }

            $employee_index = array_search("Employee", $header);
            $date_index = array_search("Leave Date", $header);
            $type_index = array_search("Leave Type", $header);

            $leaves = [];

            for ($i = 1; $i < count($data); $i++) {
                $employee = isset($data[$i][$employee_index]) ? trim((string)$data[$i][$employee_index]) : "";
                $date = $data[$i][$date_index];
                $type = isset($data[$i][$type_index]) ? trim((string)$data[$i][$type_index]) : "";

                if ($employee == "" && $date == "" && $type == "") {
                    continue;
                }

                if ($employee == "" || $date == "" || $type == "") {
                    $errors[] = "Row: " . ($i + 1) . " - Missing Employee, Leave Date, or Leave Type.";
                    continue;
                }

                $leaves[] = [
                    "row" => $i + 1,
                    "employee" => $employee,
                    "date" => $date,
                    "type" => $type,
                ];
            }

            $company_leaves = $this->db->select('id, code')->from('shifts')->where('company_id', $cid)->where('is_leave', 'yes')->get()->result();

            foreach ($leaves as $leave) {
                $special_id = trim((string)$leave["employee"]);

                $employee = $this->db->select('id')->from('employees')->where('special_id', $special_id)->where('company_id', $cid)->get()->row();

                if (!$employee) {
                    $errors[] = "Row: " . $leave["row"] . " - Employee " . $special_id . " not found.";
                    continue;
                }

                $employee_id = $employee->id;

                // get id from company leaves
                $company_leave_id = null;
                foreach ($company_leaves as $company_leave) {
                    if (strtoupper(trim((string)$company_leave->code)) == strtoupper(trim((string)$leave["type"]))) {
                        $company_leave_id = $company_leave->id;
                        break;
                    }
                }

                if (!$company_leave_id) {
                    $errors[] = "Row: " . $leave["row"] . " - Leave type " . $leave["type"] . " not found.";
                    continue;
                }

                $date = $this->normalize_leave_date($leave["date"]);
                if (!$date) {
                    $errors[] = "Row: " . $leave["row"] . " - Invalid Leave Date '" . $leave["date"] . "'. Expected format: d/m/Y (example: 30/03/2026).";
                    continue;
                }

                $this->db->trans_begin();

                $shift_day_prev_list = $this->db->query("SELECT * FROM shift_days WHERE date = '$date' AND FIND_IN_SET($employee_id,employees)")->result();

                $shift_cleanup_failed = false;

                foreach ($shift_day_prev_list as $shift_day_prev) {
                    $employees = array();

                    if (!empty($shift_day_prev->employees)) {
                        $employees = explode(",", $shift_day_prev->employees);
                    }

                    $employees = array_values(array_filter(array_diff($employees, array($employee_id)), 'strlen'));

                    $remove_data = array(
                        'employees' => trim(implode(",", $employees), ",")
                    );
                    $this->db->where('id', $shift_day_prev->id);
                    if (!$this->db->update('shift_days', $remove_data)) {
                        $shift_cleanup_failed = true;
                        break;
                    }
                }

                if ($shift_cleanup_failed) {
                    $this->db->trans_rollback();
                    $errors[] = "Row: " . $leave["row"] . " - Failed to clear previous shift assignment.";
                    continue;
                }

                $shift_day_list = $this->db->query("SELECT * FROM shift_days WHERE shift_id = $company_leave_id AND date = '$date'")->result();

                if (count($shift_day_list) > 1) {
                    $this->db->trans_rollback();
                    $errors[] = "Row: " . $leave["row"] . " - Duplicate leave shift rows already exist for " . $leave["date"] . ".";
                    continue;
                }

                if (count($shift_day_list) === 1) {
                    $shift_day = $shift_day_list[0];
                    $employees_new = array();

                    if (!empty($shift_day->employees)) {
                        $employees_new = explode(",", $shift_day->employees);
                    }

                    $employees_new = array_values(array_filter(array_diff($employees_new, array($employee_id)), 'strlen'));
                    $employees_new[] = $employee_id;

                    $update_data = array(
                        'employees' => trim(implode(",", $employees_new), ",")
                    );

                    $this->db->where('id', $shift_day->id);
                    if (!$this->db->update('shift_days', $update_data)) {
                        $this->db->trans_rollback();
                        $errors[] = "Row: " . $leave["row"] . " - Failed to update leave shift row.";
                        continue;
                    }
                } else {
                    $insert_data = array(
                        'shift_id' => $company_leave_id,
                        'date' => $date,
                        'employees' => $employee_id
                    );

                    if (!$this->db->insert('shift_days', $insert_data)) {
                        $this->db->trans_rollback();
                        $errors[] = "Row: " . $leave["row"] . " - Failed to create leave shift row.";
                        continue;
                    }
                }

                $verify_leave_shift = $this->db->query("SELECT id FROM shift_days WHERE shift_id = $company_leave_id AND date = '$date' AND FIND_IN_SET($employee_id,employees)")->result();
                $verify_other_shifts = $this->db->query("SELECT id, shift_id FROM shift_days WHERE date = '$date' AND shift_id <> $company_leave_id AND FIND_IN_SET($employee_id,employees)")->result();

                if (count($verify_leave_shift) !== 1 || count($verify_other_shifts) > 0) {
                    $this->db->trans_rollback();
                    $errors[] = "Row: " . $leave["row"] . " - Leave assignment did not fully replace the previous shift for " . $leave["date"] . ".";
                    continue;
                }

                if (!$this->db->query("UPDATE clockings_news SET shift_id = $company_leave_id WHERE DATE(datetime) = '$date' AND employee_id = $employee_id")) {
                    $this->db->trans_rollback();
                    $errors[] = "Row: " . $leave["row"] . " - Failed to update clockings_news.";
                    continue;
                }

                if (!$this->db->query("UPDATE new_clockings SET shift_id = $company_leave_id WHERE employee_id = $employee_id AND DATE(clock_in) = '$date'")) {
                    $this->db->trans_rollback();
                    $errors[] = "Row: " . $leave["row"] . " - Failed to update new_clockings.";
                    continue;
                }

                $this->db->trans_commit();

                $count++;
            }

            if (count($errors) == 0 && $count > 0) {
                $success = true;
                $message = "File imported successfully.";
            } elseif ($count == 0 && count($errors) == 0) {
                $message = "No valid leave rows were found in the file.";
            } else {
                $message = "File imported with errors.";
            }
        } else {
            $message = "No file uploaded or upload error.";
        }

        echo json_encode(array("success" => $success, "message" => $message, "errors" => $errors, "count" => $count));
    }

    private function normalize_leave_date($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return null;
        }

        $date = DateTime::createFromFormat('d/m/Y', $value);
        if ($date instanceof DateTime) {
            $formatted = $date->format('d/m/Y');
            if ($formatted === $value) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }
}
