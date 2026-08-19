<?php

/**
 * SQL Payroll API Controller
 * Optimized & Fixed for PHP < 7.4 Compatibility
 */

class Payroll_api extends CI_Controller
{
    private $_sql_data_cache = [];
    private $API_KEY = 'inv-T1m3-P@yr0ll-2026-s3cur3K3y!';
     private $company_id = null;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('general_helper');
        require_once APPPATH . 'helpers/payroll_bulk_helper.php';
    }

 /**
     * Validate the Authorization: Bearer <key> header.
     * Returns true if valid, otherwise sends 401 and exits.
     * Also sets $this->company_id from the token.
     */
    private function authenticate()
    {
        $header = $this->input->get_request_header('Authorization', TRUE);
        if (!$header || strpos($header, 'Bearer ') !== 0) {
            $this->response_json(['status' => 'error', 'message' => 'Missing or invalid Authorization header'], 401);
            return false;
        }
        $token = substr($header, 7); // strip "Bearer "
        // match token from payroll_licenses table with status = 1 and expires_at > now()
        $this->db->select('id, token, company_id, status, expires_at');
        $query = $this->db->get_where('payroll_licenses', ['token' => $token, 'status' => 1]);

        if ($query->num_rows() === 0) {
            $this->response_json(['status' => 'error', 'message' => 'Invalid or inactive token'], 401);
            return false;
        }

        $token_data = $query->row();

        // Check expiry if set
        if (!empty($token_data->expires_at) && strtotime($token_data->expires_at) < time()) {
            $this->response_json(['status' => 'error', 'message' => 'Token has expired'], 401);
            return false;
        }

        // Store company_id for use in queries
        $this->company_id = (int)$token_data->company_id;

        return true;
    }

    /**
     * Cached wrapper for get_sql_data() — avoids repeated DB hits for the same branch_id.
     */
    private function get_sql_data_cached($branch_id)
    {
        if (!isset($this->_sql_data_cache[$branch_id])) {
            $this->_sql_data_cache[$branch_id] = get_sql_data($branch_id);
        }
        return $this->_sql_data_cache[$branch_id];
    }

    /**
     * POST /payroll_api/sql
     * Generate complete SQL payroll data
     */
    public function summary($type)
    {
        if (!$this->authenticate()) {
            return;
        }

        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);

        // Accept both GET and POST (JSON body / form-data / query string)
        $raw = file_get_contents("php://input");
        $input = json_decode($raw, true);
        if (empty($input) || !is_array($input)) {
            // Fallback: merge GET and POST form data
            $input = array_merge($_GET, $_POST);
        }

        // Validate required fields
        if (!isset($input['from_date']) || !isset($input['to_date'])) {
            $this->response_json(['error' => 'Missing required fields'], 400);
        }
        $cid = $this->company_id;
        // Parse dates
        $date1 = DateTime::createFromFormat('d/m/Y', $input['from_date']);
        $date2 = DateTime::createFromFormat('d/m/Y', $input['to_date']);
        if (!$date1 || !$date2) {
            $date1 = DateTime::createFromFormat('Y-m-d', $input['from_date']);
            $date2 = DateTime::createFromFormat('Y-m-d', $input['to_date']);
        }
        if (!$date1 || !$date2) {
            $this->response_json(['error' => 'Invalid date format'], 400);
        }
        $first_day = $date1->format('Y-m-d');
        $last_day = $date2->format('Y-m-d');
        $branch_id = isset($input["branch"]) ? $input["branch"] : array();
        $department_id = isset($input["department"]) ? $input["department"] : array();
        $position_id = isset($input["position"]) ? $input["position"] : array();
        $section_id = isset($input["section"]) ? $input["section"] : null;
        $employee_id = isset($input["employee"]) ? $input["employee"] : array();
        $exclude_employees = isset($input["exclude_employee"]) ? $input["exclude_employee"] : array();
        // Get branch name
        $branch_name = "All";
        if ($branch_id) {
            $branch_name_row = $this->db->select('group_concat(name) as name')->from('branches')->where_in('id', $branch_id)->get()->row();
            if ($branch_name_row) {
                $branch_name = $branch_name_row->name;
            }
        }
        $calcParams = [
            'input' => $input,
            'cid' => $cid,
            'first_day' => $first_day,
            'last_day' => $last_day,
            'branch_id' => $branch_id,
            'branch_name' => $branch_name,
            'department_id' => $department_id,
            'position_id' => $position_id,
            'section_id' => $section_id,
            'employee_id' => $employee_id,
            'exclude_employees' => $exclude_employees
        ];
        $date = DateTime::createFromFormat('Y-m-d', $first_day);
        $first_day_formatted = $date->format('d M, Y');
        $date = DateTime::createFromFormat('Y-m-d', $last_day);
        $last_day_formatted = $date->format('d M, Y');


        $final_data = null;
        switch ($type) {
            case 'pending_overtime':
                $calculation_results = $this->dataCalculations($calcParams);
                $all_data = $calculation_results['all_data'];
                $final_data = $this->get_pending_overtime_report($all_data, $date2, $branch_name, $first_day_formatted, $last_day_formatted);
                break;
            case 'pending_allowance':
                if (!in_array($cid, companies_allowed_for_att_all()) && !in_array($cid, companies_allowed_for_meal_allowance()) && !in_array($cid, companies_allowed_for_shift_allowance()) && $cid != 215 && $cid != 152 && $cid != 206 && $cid != 229) {
                    $this->response_json(['status' => 'not_available', 'message' => 'This report is not available for this company'], 200);
                    return;
                }
                $calculation_results = $this->dataCalculations($calcParams);
                $all_data = $calculation_results['all_data'];
                $final_data = $this->get_pending_allowance_report_api($all_data, $cid, $date2);
                break;
            case 'pending_unpaid_leaves':
                $calculation_results = $this->dataCalculations($calcParams);
                $unpaid_leaves_absent_days = $calculation_results['unpaid_leaves_absent_days'];
                $final_data = $this->get_pending_unpaid_leaves_data($unpaid_leaves_absent_days, $cid, $date2);
                break;
            case 'pending_absent':
                if ($cid != 153 && $cid != 255) {
                    $this->response_json(['status' => 'not_available', 'message' => 'This report is not available for this company'], 200);
                    return;
                }
                $calculation_results = $this->dataCalculations($calcParams);
                $unpaid_leaves_absent_days = $calculation_results['unpaid_leaves_absent_days'];
                $final_data = $this->get_pending_absent_data($unpaid_leaves_absent_days, $date2);
                break;
            case 'pending_daily_ot':
                if ($cid != 153) {
                    $this->response_json(['status' => 'not_available', 'message' => 'This report is not available for this company'], 200);
                    return;
                }
                $calculation_results = $this->dataCalculations($calcParams);
                $daily_ot_array = $calculation_results['daily_ot_array'];
                $final_data = $this->get_pending_daily_ot_data($daily_ot_array, $date2);
                break;
            case 'pending_daily_late':
                if ($cid != 153) {
                    $this->response_json(['status' => 'not_available', 'message' => 'This report is not available for this company'], 200);
                    return;
                }
                $calculation_results = $this->dataCalculations($calcParams);
                $daily_late_array = $calculation_results['daily_late_array'];
                $final_data = $this->get_pending_daily_late_data($daily_late_array, $date2);
                break;
            case 'pending_worked_rest_days':
                $calculation_results = $this->dataCalculations($calcParams);
                $all_data = $calculation_results['all_data'];
                $worked_rest_days_array = $calculation_results['worked_rest_days_array'];
                $final_data = $this->get_pending_worked_rest_days_data($cid, $all_data, $worked_rest_days_array, $date2);
                break;
            case 'pending_worked_off_days':
                $calculation_results = $this->dataCalculations($calcParams);
                $worked_off_days_array = $calculation_results['worked_off_days_array'];
                $final_data = $this->get_pending_worked_off_days_data($worked_off_days_array, $date2);
                break;
            case 'pending_worked_holidays':
                $calculation_results = $this->dataCalculations($calcParams);
                $worked_holidays_array = $calculation_results['worked_holidays_array'];
                $final_data = $this->get_pending_worked_public_holidays_data($worked_holidays_array, $date2);
                break;
            case 'pending_daily_waged':
                $calculation_results = $this->dataCalculations($calcParams);
                $all_data = $calculation_results['all_data'];
                $final_data = $this->get_pending_daily_wage_data($all_data, $date2);
                break;
            case 'pending_early_lates':
                $calculation_results = $this->dataCalculations($calcParams);
                $all_data = $calculation_results['all_data'];
                $final_data = $this->get_pending_early_late_data($all_data, $date2);
                break;
            case 'pending_deductions':
                $calculation_results = $this->dataCalculations($calcParams);
                $all_data = $calculation_results['all_data'];
                $final_data = $this->get_pending_deductions_data($all_data, $cid, $date2);
                break;
            case 'pending_shift_worked_hours':
                $calculation_results = $this->dataCalculations($calcParams);
                $all_data = $calculation_results['all_data'];
                $final_data = $this->get_pending_shift_worked_hours_data($all_data, $date2);
                break;
            case 'pending_worked_hours':
                $calculation_results = $this->dataCalculations($calcParams);
                $all_data = $calculation_results['all_data'];
                $final_data = $this->get_pending_worked_hours_data($all_data, $date2);
                break;

            case 'pending_leave_application':
                if (!in_array($cid, companies_allowed_for_leave_application())) {
                    $this->response_json(['status' => 'not_available', 'message' => 'This report is not available for this company'], 200);
                    return;
                }
                $calculation_results = $this->dataCalculations($calcParams);
                $paid_leaves_array = $calculation_results['paid_leaves_array'];
                $final_data = $this->get_pending_leave_application_data($paid_leaves_array);
                break;

            case 'pending_allowance_report':
                if (!in_array($cid, companies_allowed_for_allowance_report())) {
                    $this->response_json(['status' => 'not_available', 'message' => 'This report is not available for this company'], 200);
                    return;
                }
                $calculation_results = $this->dataCalculations($calcParams);
                $employees_ids = $calculation_results['employees_ids'];
                $allowances = get_allowances_for_report($employees_ids, $first_day, $last_day);
                $final_data = $this->get_pending_allowance_report_data($allowances, $date2);
                break;

            default:
                $this->response_json(['error' => 'Invalid report type'], 400);
                break;
        }

        $response = [
            'status' => 'success',
            'report_type' => $type,
            'period' => [
                'from_date' => $first_day_formatted ?? null,
                'to_date' => $last_day_formatted ?? null,
            ],
            'summary' => [
                'branch' => $branch_name,
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => 'Admin API'
            ],
            'count' => is_array($final_data) ? count($final_data) : 0,
            'data' => $final_data ?? null
        ];

        $this->response_json($response, 200);
    }

    public function dataCalculations($params)
    {
        $cid = $params['cid'];
        $first_day = $params['first_day'];
        $last_day = $params['last_day'];
        $branch_id = $params['branch_id'];
        $department_id = $params['department_id'];
        $position_id = $params['position_id'];
        $section_id = $params['section_id'];
        $employee_id = $params['employee_id'];
        $exclude_employees = $params['exclude_employees'];

        // Main try-catch block for error handling
        try {

            // Fetch Shifts for context
            $shifts = $this->db->select('id')->from('shifts')->where('company_id', $cid)->where('is_leave', 'no')->get()->result();
            $shift_ids = array(0);
            foreach ($shifts as $s) {
                $shift_ids[] = $s->id;
            }
            $combined_first_day = $first_day;

            // --- PRE-FETCH SETTINGS (Optimization) ---
            $company_working_hours = $this->get_company_working_hours($cid);
            $company_ot_settings = get_company_ot_settings($cid);
            $company_early_ot_settings = get_company_early_ot_settings($cid);

            // Memoize branch rest days to avoid repeating inside calculate_summary_data if possible,
            // though the function logic often re-queries. We pass this for optimizations.
            $branch_rest_days = $this->db->select('id,rest_days,off_days')->from('branches')->where('company_id', $cid)->get()->result();

            $approved_ot_list = get_approved_ot_list($shift_ids, $combined_first_day, $last_day);

            // --- EMPLOYEE FILTERING LOGIC ---
            $employees_from_group = array();
            $excluded_employees_from_group = array();

            if ($employee_id) {
                $employee_group_arr = array();
                foreach ($employee_id as $key) {
                    if (strpos($key, '-') !== false) {
                        $arr = explode("-", $key);
                        $key1 = $arr[0];
                        array_push($employee_group_arr, $key1);
                    } else {
                        array_push($employees_from_group, $key);
                    }
                }
                if (!empty($employee_group_arr)) {
                    $results = $this->db->where_in('group_id', $employee_group_arr)->get('employee_groups_relation')->result();
                    foreach ($results as $result) {
                        $employees_from_group[] = $result->employee_id;
                    }
                }
                $employees_from_group = array_unique($employees_from_group);
            }

            if ($exclude_employees) {
                $employee_group_arr = array();
                foreach ($exclude_employees as $key) {
                    if (strpos($key, '-') !== false) {
                        $arr = explode("-", $key);
                        $key1 = $arr[0];
                        array_push($employee_group_arr, $key1);
                    } else {
                        $excluded_employees_from_group[] = $key;
                    }
                }
                if (!empty($employee_group_arr)) {
                    $results = $this->db->where_in('group_id', $employee_group_arr)->get('employee_groups_relation')->result();
                    foreach ($results as $result) {
                        $excluded_employees_from_group[] = $result->employee_id;
                    }
                }
                $excluded_employees_from_group = array_unique($excluded_employees_from_group);
            }

            // Build Employee Query
            $this->db->select('
                employees.id,
                employees.first_name,
                special_id,
                employees.is_daily_waged,
                d.name as department,
                s.title as section,
                p.title as position,
                employees.branch_id,
                b.name as branch,
                is_ot,is_early_ot,inc_late_in,inc_late_break,inc_early_out,inc_short_hours,
                ot_type,ot_round,early_ot_round,use_half_hours_for_saturdays,
                round_first_hour_only,round_by_exact_hour,different_first_hour_rounding,
                worked_hours_ot_rd,worked_hours_ot_ph,deduct_hour_ot_rd,deduct_hour_ot_ph,
                worked_hours_ot_off,deduct_hour_ot_off,ignore_breaks_after_endtime,
                void_lateness_time_if_less_than,deduct_from_ot,deduct_from_ot_single,
                deduction_date,min_worked_hours_meal,
                ta_rate,ma_rate,ca_rate,spa_rate,aca_rate,aa_rate,nsa_rate,dsa_rate,
                fl_rate,cw_rate,mo_rate,shift1_rate,shift2_rate,shift3_rate,food_rate,
                basic_wage,ot_group,special_incentive,
                att_all_code, att_all_desc, att_all_amount, is_att_all,
                mi_mo_rate, lateness_deduction_99, lateness_deduction_100,
                rest_day_entitlement, is_shift_hours,
                GROUP_CONCAT(DISTINCT eg.name ORDER BY eg.name SEPARATOR ", ") as group_names
            ', FALSE)
                ->from('employees')
                ->join('roles', 'employees.role_id = roles.id', 'left')
                ->join('departments d', 'd.id = employees.department_id', 'left')
                ->join('branches b', 'b.id = employees.branch_id', 'left')
                ->join('sections s', 'employees.section_id = s.id', 'left')
                ->join('positions p', 'p.id = employees.position_id', 'left')
                ->join('employee_groups_relation egr', 'egr.employee_id = employees.id', 'left')
                ->join('employee_groups eg', 'eg.id = egr.group_id', 'left')
                ->where('employees.company_id', $cid)
                ->where('employees.deleted_at is null')
                ->where('roles.exclude_from_system', 'no')
                ->where("(employees.employee_status = 'active'
                OR (employees.employee_status = 'terminated' AND employees.termination_date >= DATE_FORMAT('$first_day', '%Y-%m-01'))
                OR (employees.employee_status = 'resigned' AND employees.resignation_date >= DATE_FORMAT('$first_day', '%Y-%m-01'))
            )");

            if ($branch_id) $this->db->where_in('employees.branch_id', $branch_id);
            if ($department_id) $this->db->where_in('employees.department_id', $department_id);
            if ($position_id) $this->db->where_in('employees.position_id', $position_id);
            if ($section_id) $this->db->where_in('employees.section_id', $section_id);
            if ($employees_from_group) $this->db->where_in('employees.id', $employees_from_group);
            if ($excluded_employees_from_group) $this->db->where_not_in('employees.id', $excluded_employees_from_group);

            $this->db->group_by('employees.id');
            $this->db->order_by('special_id', 'asc');

            $employees = $this->db->get()->result();

            if (empty($employees)) {
                $this->response_json(['error' => 'No employees found'], 404);
            }

            // Gather IDs for bulk fetching
            $employees_ids = array();
            foreach ($employees as $emp) {
                $employees_ids[] = $emp->id;
            }

            // --- OPTIMIZATION START: Bulk Fetch Data ---

            // 1. Bulk Fetch Clockings (Moved OUT of the loop)
            $all_clockings_news = [];
            $all_clockings_news_overnight = [];
            $interval_minutes = 0;

            if ($cid == 196) {
                $interval_minutes = get_interval_minutes($cid);

                // Fetch Normal Clockings
                $this->db->select('id,employee_id,type,shift_id,date_format(datetime,"%H:%i") as clock_time,date_format(datetime, "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false);
                $this->db->from('clockings_news');
                $this->db->where('date(datetime) >=', $first_day);
                $this->db->where('date(datetime) <=', $last_day);
                $this->db->where_in('employee_id', $employees_ids);
                $this->db->where('deleted_at is null');
                $this->db->order_by('datetime');
                $all_clockings_news = $this->db->get()->result();

                // Fetch Overnight Clockings
                $this->db->select('id,employee_id,type,shift_id, date_format(datetime,"%H:%i") as clock_time,date_format(date_sub(datetime, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, add_by_admin, delete_by_admin, update_by_admin', false);
                $this->db->from('clockings_news');
                $this->db->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) >=', $first_day);
                $this->db->where('date(date_sub(datetime, interval ' . $interval_minutes . ' minute)) <=', $last_day);
                $this->db->where_in('employee_id', $employees_ids);
                $this->db->where('deleted_at is null');
                $this->db->order_by('datetime');
                $all_clockings_news_overnight = $this->db->get()->result();
            }

            // 2. Fetch Result List in Chunks (Preserved from original, but clean)
            $result_list = [];
            $result_list_overnight = [];
            $chunkedEmployeeIds = array_chunk($employees_ids, 100);

            foreach ($chunkedEmployeeIds as $chunk) {
                $result_list = array_merge($result_list, get_result_list($chunk, $combined_first_day, $last_day));
                $result_list_overnight = array_merge($result_list_overnight, $this->get_result_list_overnight($chunk, $combined_first_day, $last_day, $cid));
            }

            // --- OPTIMIZATION END ---

            $data = [];
            $all_data = array();

            // --- PRE-FETCH per-company/branch data (avoids N+1 queries in loop) ---
            $days_settings = $this->db->select('from_hour,to_hour,days')->from('days_settings')->where('company_id', $cid)->get()->result();

            // Pre-fetch ot_type_data for all branches at once
            $ot_type_data_map = [];
            $ot_type_rows = $this->db->select("id, ot_weekly_hours, ot_type")->from("branches")->where("company_id", $cid)->get()->result();
            foreach ($ot_type_rows as $row) {
                $ot_type_data_map[$row->id] = $row;
            }

            // --- BULK PRE-FETCH per-employee data using PayrollBulkHelper (eliminates N+1 queries) ---
            $branch_ids = array_unique(array_map(function($e) { return $e->branch_id; }, $employees));
            $bulk = new PayrollBulkHelper();
            $bulk->prefetch($employees_ids, $first_day, $last_day, $cid, $branch_ids);

            // Initialization arrays
            $unpaid_leaves_absent_days = [];
            $worked_rest_days_array = [];
            $worked_off_days_array = [];
            $worked_holidays_array = [];
            $paid_leaves_array = [];
            $daily_ot_array = [];
            $daily_late_array = [];

            foreach ($employees as $i => $emp) {
                $clockings_news = [];
                $clockings_news_overnight = [];

                if ($cid == 196) {
                    // Filter in Memory (PHP 7.3 compatible closure)
                    $current_emp_id = $emp->id;

                    $clockings_news = array_values(array_filter($all_clockings_news, function ($item) use ($current_emp_id) {
                        return $item->employee_id == $current_emp_id;
                    }));

                    $clockings_news_overnight = array_values(array_filter($all_clockings_news_overnight, function ($item) use ($current_emp_id) {
                        return $item->employee_id == $current_emp_id;
                    }));
                }

                // Call the logic-heavy function
                $data = $this->calculate_summary_data(
                    $emp,
                    $first_day,
                    $last_day,
                    $result_list,
                    $result_list_overnight,
                    $company_working_hours,
                    false,
                    $company_ot_settings,
                    $company_early_ot_settings,
                    $approved_ot_list,
                    $branch_rest_days,
                    $cid,
                    $worked_rest_days_array,
                    $worked_off_days_array,
                    $worked_holidays_array,
                    $unpaid_leaves_absent_days,
                    $clockings_news,
                    $clockings_news_overnight,
                    $paid_leaves_array,
                    $daily_ot_array,
                    $daily_late_array,
                    $days_settings,
                    $ot_type_data_map,
                    $bulk
                );

                $all_data[] = $data;
            }

            // Note: Removed the early return of settings present in your original file
            // as it prevented the actual report generation.

            ksort($unpaid_leaves_absent_days);
            ksort($paid_leaves_array);
            ksort($daily_ot_array);
            ksort($daily_late_array);

            // Note: If type is 'sql', we return $all_data which is populated above.
            return [
                "all_data" => $all_data,
                "unpaid_leaves_absent_days" => $unpaid_leaves_absent_days,
                "paid_leaves_array" => $paid_leaves_array,
                "daily_ot_array" => $daily_ot_array,
                "daily_late_array" => $daily_late_array,
                "worked_rest_days_array" => $worked_rest_days_array,
                "worked_off_days_array" => $worked_off_days_array,
                "worked_holidays_array" => $worked_holidays_array,
                "employees_ids" => $employees_ids
            ];
        } catch (Exception $e) {
            log_message('error', 'Payroll SQL API Error: ' . $e->getMessage());
            $this->response_json(['error' => $e->getMessage()], 500);
        }
    }

    public function get_pending_overtime_report($all_data, $date2, $branch_name, $first_day, $last_day, $gni01 = false, $short_ot_data = [])
    {
        $report_data = [];

        // Ensure date is formatted correctly (matches "d/m/Y" from your Excel logic)
        // If $date2 is a DateTime object (as per your summary function), format it.
        // If it's a string, convert it.
        $trans_date = ($date2 instanceof DateTime) ? $date2->format('d/m/Y') : date('d/m/Y', strtotime($date2));

        foreach ($all_data as $key => $r) {
            // Retrieve SQL Mapping data for the employee's branch
            $sql_data_of_emp = $this->get_sql_data_cached($r["employee"]->branch_id);

            $month_overtime_deducted = $r['month_overtime_deducted'];

            // Logic for GNI01 (if applicable)
            if ($gni01 && isset($short_ot_data[$key])) {
                $ot_data = $short_ot_data[$key];
                $ot_balance = 0;

                $ot = toDecimal($ot_data["month_overtime_deducted"]);
                $extra_ot = ($ot_data["worked_rest_days"] + $ot_data["worked_holidays"]) * 8;
                $extra_ot += toDecimal($ot_data["month_overtime_ph_x2"]);
                $extra_ot += toDecimal($ot_data["month_overtime_ph_x3"]);
                $extra_ot += toDecimal($ot_data["month_overtime_rd"]);
                $extra_ot += toDecimal($ot_data["month_overtime_off"]);

                if ($ot + $extra_ot > 104) {
                    $new_ot = 104 - $extra_ot;
                    $ot_balance = $ot - $new_ot;
                }

                $month_overtime_deducted = $month_overtime_deducted - $ot_balance;
                $month_overtime_deducted = $month_overtime_deducted < 0 ? 0 : $month_overtime_deducted;
            }

            // 1. Normal Overtime Row
            if ($month_overtime_deducted != 0) {
                $report_data[] = [
                    "Trans Date"  => $trans_date,
                    "Post Date"   => $trans_date,
                    "Employee"    => $r["employee"]->special_id,
                    "Code"        => $sql_data_of_emp["sql_ot1_code"],
                    "Description" => $sql_data_of_emp["sql_ot1_description"],
                    "Work Unit"   => $month_overtime_deducted,
                    "Rate"        => $sql_data_of_emp["sql_ot1_rate"]
                ];
            }

            // 2. Rest Day Overtime Row
            if ($r['month_overtime_rd'] != 0) {
                $report_data[] = [
                    "Trans Date"  => $trans_date,
                    "Post Date"   => $trans_date,
                    "Employee"    => $r["employee"]->special_id,
                    "Code"        => $sql_data_of_emp["sql_ot2_code"],
                    "Description" => $sql_data_of_emp["sql_ot2_description"],
                    "Work Unit"   => $r["month_overtime_rd"],
                    "Rate"        => $sql_data_of_emp["sql_ot2_rate"]
                ];
            }

            // 3. Public Holiday (x3) Overtime Row
            if ($r['month_overtime_ph_x3'] != 0) {
                $report_data[] = [
                    "Trans Date"  => $trans_date,
                    "Post Date"   => $trans_date,
                    "Employee"    => $r["employee"]->special_id,
                    "Code"        => $sql_data_of_emp["sql_ot3_code"],
                    "Description" => $sql_data_of_emp["sql_ot3_description"],
                    "Work Unit"   => $r["month_overtime_ph_x3"],
                    "Rate"        => $sql_data_of_emp["sql_ot3_rate"]
                ];
            }

            // 4. Public Holiday (x2) Overtime Row
            if ($r['month_overtime_ph_x2'] != 0) {
                $report_data[] = [
                    "Trans Date"  => $trans_date,
                    "Post Date"   => $trans_date,
                    "Employee"    => $r["employee"]->special_id,
                    "Code"        => $sql_data_of_emp["sql_ot3_code_x2"],
                    "Description" => $sql_data_of_emp["sql_ot3_description_x2"],
                    "Work Unit"   => $r["month_overtime_ph_x2"],
                    "Rate"        => $sql_data_of_emp["sql_ot3_rate_x2"]
                ];
            }

            // 5. Off Day Overtime Row
            if ($r['month_overtime_off'] != 0) {
                $report_data[] = [
                    "Trans Date"  => $trans_date,
                    "Post Date"   => $trans_date,
                    "Employee"    => $r["employee"]->special_id,
                    "Code"        => $sql_data_of_emp["sql_ot_off_code"],
                    "Description" => $sql_data_of_emp["sql_ot_off_description"],
                    "Work Unit"   => $r["month_overtime_off"],
                    "Rate"        => $sql_data_of_emp["sql_ot_off_rate"]
                ];
            }
        }

        return $report_data;
    }
    public function get_pending_allowance_report_api($all_data, $cid, $date2)
    {
        $report_data = [];

        // Ensure date is formatted correctly
        $trans_date = ($date2 instanceof DateTime) ? $date2->format('d/m/Y') : date('d/m/Y', strtotime($date2));

        foreach ($all_data as $r) {
            $employee = $r["employee"];
            $sql_data_of_emp = $this->get_sql_data_cached($employee->branch_id);

            // --- SECTION 1: Company Specific Logic ---

            if ($cid == 215) {
                // GBR Attendance Allowance
                if ($r["gbr_attendance_allowance"]) {
                    $report_data[] = $this->format_allowance_row($trans_date, $employee->special_id, $sql_data_of_emp["sql_aa_code"], $sql_data_of_emp["sql_aa_description"], 1, $employee->aa_rate);
                }
                // Night Shifts
                if ($r["gbr_night_shifts"]) {
                    $report_data[] = $this->format_allowance_row($trans_date, $employee->special_id, $sql_data_of_emp["sql_nsa_code"], $sql_data_of_emp["sql_nsa_description"], $r["gbr_night_shifts"], $employee->nsa_rate);
                }
            } else if ($cid == 152) {
                $worked_days = $r["worked_days"] + $r["total_holidays"] + $r["worked_rest_days"];
                $aa_amount = $worked_days * $employee->aa_rate;
                if ($worked_days && $aa_amount) {
                    $report_data[] = $this->format_allowance_row($trans_date, $employee->special_id, $sql_data_of_emp["sql_aa_code"], $sql_data_of_emp["sql_aa_description"], $worked_days, $employee->aa_rate);
                }

                // LSK Non-worked logic
                $lsk_non_worked_days = $r["lsk_non_worked_days"];
                $total_allowance = $employee->ta_rate ?? 100;
                $final_amount = $total_allowance - ($lsk_non_worked_days * 50);

                if ($final_amount > 0) {
                    $report_data[] = $this->format_allowance_row($trans_date, $employee->special_id, $sql_data_of_emp["sql_nsa_code"], $sql_data_of_emp["sql_nsa_description"], 1, $final_amount);
                }
            } else if ($cid == 229) {
                $worked_days = $r["ln01_attendance_allowance_days"] + $r["worked_rest_days"] - $r["ln01_waived_days"];
                $aa_amount = $worked_days * $employee->aa_rate;
                if ($worked_days > 0 && $aa_amount > 0) {
                    $report_data[] = $this->format_allowance_row($trans_date, $employee->special_id, $sql_data_of_emp["sql_aa_code"], $sql_data_of_emp["sql_aa_description"], $worked_days, $employee->aa_rate);
                }
            } else if ($cid == 206) {
                if ($r["food_allowance_days"]) {
                    $report_data[] = $this->format_allowance_row($trans_date, $employee->special_id, $sql_data_of_emp["sql_aa_code"], $sql_data_of_emp["sql_aa_description"], $r["food_allowance_days"], $employee->food_rate);
                }
            } else {
                // Default logic for other companies
                if ($employee->is_att_all == 1) {
                    $report_data[] = $this->format_allowance_row($trans_date, $employee->special_id, $employee->att_all_code, $employee->att_all_desc, 1, $employee->att_all_amount);
                }
            }

            // --- SECTION 2: General/Global Allowance Checks ---

            // Meal Allowance
            if (in_array($cid, companies_allowed_for_meal_allowance())) {
                if ($r["food_allowance_days"]) {
                    $report_data[] = $this->format_allowance_row($trans_date, $employee->special_id, $sql_data_of_emp["sql_aa_code"], $sql_data_of_emp["sql_aa_description"], $r["food_allowance_days"], $employee->food_rate);
                }
            }

            // Shift Allowance
            if (in_array($cid, companies_allowed_for_shift_allowance())) {
                if ($r["monthly_dsa_count"]) {
                    $report_data[] = $this->format_allowance_row($trans_date, $employee->special_id, $sql_data_of_emp["sql_dsa_code"], $sql_data_of_emp["sql_dsa_description"], $r["monthly_dsa_count"], $employee->dsa_rate);
                }
                if ($r["monthly_nsa_count"]) {
                    $report_data[] = $this->format_allowance_row($trans_date, $employee->special_id, $sql_data_of_emp["sql_nsa_code"], $sql_data_of_emp["sql_nsa_description"], $r["monthly_nsa_count"], $employee->nsa_rate);
                }
            }
        }

        return $report_data;
    }

    /**
     * Helper to keep the code DRY (Don't Repeat Yourself)
     */
    private function format_allowance_row($date, $emp_id, $code, $desc, $unit, $rate)
    {
        return [
            "Trans Date"  => $date,
            "Post Date"   => $date,
            "Employee"    => $emp_id,
            "Code"        => $code,
            "Description" => $desc,
            "Work Unit"   => $unit,
            "Rate"        => $rate,
            "Amount"      => $unit * $rate
        ];
    }
    public function get_pending_unpaid_leaves_data($unpaid_leaves_absent_days, $cid, $date2)
    {
        $report_data = [];

        // Ensure date is formatted correctly
        $trans_date = ($date2 instanceof DateTime) ? $date2->format('d/m/Y') : date('d/m/Y', strtotime($date2));

        foreach ($unpaid_leaves_absent_days as $key => $dated_array) {
            foreach ($dated_array as $unpaid_leave) {
                if ($unpaid_leave["type"] == "absent" && in_array($cid, [153, 255])) {
                    continue;
                }

                $sql_data_of_emp = $this->get_sql_data_cached($unpaid_leave['branch_id']);

                $report_data[] = [
                    "Trans Date"  => $key,
                    "Post Date"   => $trans_date,
                    "Employee"    => $unpaid_leave["employee_special_id"],
                    "Code"        => $sql_data_of_emp["sql_ul_code"] ?? '',
                    "Description" => $sql_data_of_emp["sql_ul_description"] ?? '',
                    "Leave Days"  => $unpaid_leave['unpaid_leave'],
                    "Amount"      => $sql_data_of_emp["sql_ul_rate"] ?? 0
                ];
            }
        }

        return $report_data;
    }
    public function get_pending_absent_data($unpaid_leaves_absent_days, $date2)
    {
        $report_data = [];

        // Ensure date is formatted correctly
        $trans_date = ($date2 instanceof DateTime) ? $date2->format('d/m/Y') : date('d/m/Y', strtotime($date2));

        foreach ($unpaid_leaves_absent_days as $key => $dated_array) {
            foreach ($dated_array as $absent) {
                // Skip unpaid leave entries — only process absent type
                if ($absent["type"] == "unpaid_leave") {
                    continue;
                }

                $sql_data_of_emp = $this->get_sql_data_cached($absent['branch_id']);

                $report_data[] = [
                    "Trans Date"  => $key,
                    "Post Date"   => $trans_date,
                    "Employee"    => $absent["employee_special_id"],
                    "Code"        => $sql_data_of_emp["sql_ab_code"] ?? '',
                    "Description" => $sql_data_of_emp["sql_ab_description"] ?? '',
                    "Leave Days"  => $absent['unpaid_leave'],     // still using this key (as in original)
                    "Amount"      => $sql_data_of_emp["sql_ab_rate"] ?? 0
                ];
            }
        }

        return $report_data;
    }
    public function get_pending_daily_ot_data($daily_ot_array, $date2)
    {
        $report_data = [];

        // Ensure date is formatted correctly
        $trans_date = ($date2 instanceof DateTime) ? $date2->format('d/m/Y') : date('d/m/Y', strtotime($date2));

        foreach ($daily_ot_array as $key => $dated_array) {
            foreach ($dated_array as $daily_ot) {
                $sql_data_of_emp = $this->get_sql_data_cached($daily_ot['branch_id']);

                $report_data[] = [
                    "Trans Date"  => $key,
                    "Post Date"   => $trans_date,
                    "Employee"    => $daily_ot["employee_special_id"],
                    "Code"        => $sql_data_of_emp["sql_d_ot_code"] ?? '',
                    "Description" => $sql_data_of_emp["sql_d_ot_description"] ?? '',
                    "Daily OT"    => $daily_ot['daily_overtime'],
                    "Amount"      => $sql_data_of_emp["sql_d_ot_rate"] ?? 0
                ];
            }
        }

        return $report_data;
    }
    public function get_pending_daily_late_data($daily_late_array, $date2)
    {
        $report_data = [];

        // Ensure date is formatted correctly
        $trans_date = ($date2 instanceof DateTime) ? $date2->format('d/m/Y') : date('d/m/Y', strtotime($date2));

        foreach ($daily_late_array as $key => $dated_array) {
            foreach ($dated_array as $daily_late) {
                $sql_data_of_emp = $this->get_sql_data_cached($daily_late['branch_id']);

                $report_data[] = [
                    "Trans Date"  => $key,
                    "Post Date"   => $trans_date,
                    "Employee"    => $daily_late["employee_special_id"],
                    "Code"        => $sql_data_of_emp["sql_d_late_code"] ?? '',
                    "Description" => $sql_data_of_emp["sql_d_late_description"] ?? '',
                    "Daily Late"  => $daily_late['daily_late'],
                    "Amount"      => $sql_data_of_emp["sql_d_late_rate"] ?? 0
                ];
            }
        }

        return $report_data;
    }
    public function get_pending_worked_rest_days_data($cid, $all_data, $worked_rest_days_array, $date2)
    {
        $report_data = [];

        $trans_date = ($date2 instanceof DateTime) ? $date2->format('d/m/Y') : date('d/m/Y', strtotime($date2));

        if ($cid == 196) {
            foreach ($all_data as $r) {
                $sql_data_of_emp = $this->get_sql_data_cached($r["employee"]->branch_id);

                $rest_day_entitlement = $r["employee"]->rest_day_entitlement;
                $total_rest_days_used  = $r["total_rest_days_used"];
                $balance_rest_days     = $rest_day_entitlement - $total_rest_days_used;

                if ($balance_rest_days != 0) {
                    $report_data[] = [
                        "Trans Date"  => $trans_date,
                        "Post Date"   => $trans_date,
                        "Employee"    => $r["employee"]->special_id,
                        "Code"        => $sql_data_of_emp["sql_wrd_code"] ?? '',
                        "Description" => $sql_data_of_emp["sql_wrd_description"] ?? '',
                        "Worked Days" => $balance_rest_days,
                        "Rate"        => $sql_data_of_emp["sql_wrd_rate"] ?? 0
                    ];
                }
            }
        } else {
            foreach ($worked_rest_days_array as $key => $dated_array) {
                foreach ($dated_array as $worked_rd) {
                    $sql_data_of_emp = $this->get_sql_data_cached($worked_rd['branch_id']);

                    $report_data[] = [
                        "Trans Date"  => $key,
                        "Post Date"   => $trans_date,
                        "Employee"    => $worked_rd["employee_special_id"],
                        "Code"        => $sql_data_of_emp["sql_wrd_code"] ?? '',
                        "Description" => $sql_data_of_emp["sql_wrd_description"] ?? '',
                        "Worked Days" => $worked_rd['worked_rest_day'],
                        "Rate"        => $sql_data_of_emp["sql_wrd_rate"] ?? 0
                    ];
                }
            }
        }

        return $report_data;
    }
    public function get_pending_worked_off_days_data($worked_off_days_array, $date2)
    {
        $report_data = [];

        $trans_date = ($date2 instanceof DateTime) ? $date2->format('d/m/Y') : date('d/m/Y', strtotime($date2));

        foreach ($worked_off_days_array as $key => $dated_array) {
            foreach ($dated_array as $worked_off) {
                $sql_data_of_emp = $this->get_sql_data_cached($worked_off['branch_id']);

                $report_data[] = [
                    "Trans Date"  => $key,
                    "Post Date"   => $trans_date,
                    "Employee"    => $worked_off["employee_special_id"],
                    "Code"        => $sql_data_of_emp["sql_w_off_code"] ?? '',
                    "Description" => $sql_data_of_emp["sql_w_off_description"] ?? '',
                    "Worked Days" => $worked_off['worked_off_day'],
                    "Rate"        => $sql_data_of_emp["sql_w_off_rate"] ?? 0
                ];
            }
        }

        return $report_data;
    }
    public function get_pending_worked_public_holidays_data($worked_holidays_array, $date2)
    {
        $report_data = [];

        $trans_date = ($date2 instanceof DateTime) ? $date2->format('d/m/Y') : date('d/m/Y', strtotime($date2));

        foreach ($worked_holidays_array as $key => $dated_array) {
            foreach ($dated_array as $worked_hd) {
                $sql_data_of_emp = $this->get_sql_data_cached($worked_hd['branch_id']);

                if ($worked_hd["holiday_rate"] == "x3") {
                    $code        = $sql_data_of_emp["sql_wph_code"] ?? '';
                    $description = $sql_data_of_emp["sql_wph_description"] ?? '';
                    $rate        = $sql_data_of_emp["sql_wph_rate"] ?? 0;
                } else {
                    $code        = $sql_data_of_emp["sql_wph_code_x2"] ?? '';
                    $description = $sql_data_of_emp["sql_wph_description_x2"] ?? '';
                    $rate        = $sql_data_of_emp["sql_wph_rate_x2"] ?? 0;
                }

                $report_data[] = [
                    "Trans Date"  => $key,
                    "Post Date"   => $trans_date,
                    "Employee"    => $worked_hd["employee_special_id"],
                    "Code"        => $code,
                    "Description" => $description,
                    "Worked Days" => $worked_hd['worked_holiday'],
                    "Rate"        => $rate
                ];
            }
        }

        return $report_data;
    }
    public function get_pending_daily_wage_data($all_data, $date2)
    {
        $report_data = [];

        $trans_date = ($date2 instanceof DateTime) ? $date2->format('d/m/Y') : date('d/m/Y', strtotime($date2));

        foreach ($all_data as $r) {
            if ($r['employee']->is_daily_waged == 0) {
                continue;
            }

            $sql_data_of_emp = $this->get_sql_data_cached($r["employee"]->branch_id);

            $daily_wage_value = $r["worked_days"] + $r["worked_rest_days"] + $r["worked_holidays"];

            if ($daily_wage_value != 0) {
                $report_data[] = [
                    "Trans Date"  => $trans_date,
                    "Post Date"   => $trans_date,
                    "Employee"    => $r["employee"]->special_id,
                    "Code"        => $sql_data_of_emp["sql_dw_code"] ?? '',
                    "Description" => $sql_data_of_emp["sql_dw_description"] ?? '',
                    "Work Unit"   => $daily_wage_value,
                    "Amount"      => ""   // empty as in original
                ];
            }
        }

        return $report_data;
    }
    public function get_pending_early_late_data($all_data, $date2)
    {
        $report_data = [];

        $trans_date = ($date2 instanceof DateTime) ? $date2->format('d/m/Y') : date('d/m/Y', strtotime($date2));

        foreach ($all_data as $r) {
            if ($r['late_days'] == 0) {
                continue;
            }

            $sql_data_of_emp = $this->get_sql_data_cached($r["employee"]->branch_id);

            $report_data[] = [
                "Trans Date"  => $trans_date,
                "Post Date"   => $trans_date,
                "Employee"    => $r["employee"]->special_id,
                "Code"        => $sql_data_of_emp["sql_e_l_code"] ?? '',
                "Description" => $sql_data_of_emp["sql_e_l_description"] ?? '',
                "Leave Days"  => $r['late_days'],
                "Amount"      => ""   // empty as in original
            ];
        }

        return $report_data;
    }
    public function get_pending_deductions_data($all_data, $cid, $date2)
    {
        $report_data = [];

        $trans_date = ($date2 instanceof DateTime) ? $date2->format('d/m/Y') : date('d/m/Y', strtotime($date2));

        foreach ($all_data as $r) {
            $sql_data_of_emp = $this->get_sql_data_cached($r["employee"]->branch_id);

            if ($cid == 196) {
                // Missing in/out
                $mi_mo = $r["total_missing_in_out"];
                if ($mi_mo != 0) {
                    $report_data[] = [
                        "Trans Date"  => $trans_date,
                        "Post Date"   => $trans_date,
                        "Employee"    => $r["employee"]->special_id,
                        "Code"        => $sql_data_of_emp["sql_dd1_code"] ?? '',
                        "Description" => $sql_data_of_emp["sql_dd1_description"] ?? '',
                        "Work Unit"   => $mi_mo,
                        "Rate"        => $r["employee"]->mi_mo_rate ?? 0,
                        "Amount"      => $mi_mo * ($r["employee"]->mi_mo_rate ?? 0)
                    ];
                }

                // Lateness
                $lateness_time = time_to_minutes($r["lateness_time"]);
                if ($lateness_time != 0) {
                    $employee_rate = $lateness_time < 100
                        ? ($r["employee"]->lateness_deduction_99 ?? 0)
                        : ($r["employee"]->lateness_deduction_100 ?? 0);

                    $report_data[] = [
                        "Trans Date"  => $trans_date,
                        "Post Date"   => $trans_date,
                        "Employee"    => $r["employee"]->special_id,
                        "Code"        => $sql_data_of_emp["sql_dd2_code"] ?? '',
                        "Description" => $sql_data_of_emp["sql_dd2_description"] ?? '',
                        "Work Unit"   => $lateness_time,
                        "Rate"        => $employee_rate,
                        "Amount"      => $lateness_time * $employee_rate
                    ];
                }
            } else {
                // Default two fixed deduction lines
                $report_data[] = [
                    "Trans Date"  => $trans_date,
                    "Post Date"   => $trans_date,
                    "Employee"    => $r["employee"]->special_id,
                    "Code"        => $sql_data_of_emp["sql_dd1_code"] ?? '',
                    "Description" => $sql_data_of_emp["sql_dd1_description"] ?? '',
                    "Work Unit"   => "1",
                    "Rate"        => $sql_data_of_emp["sql_dd1_rate"] ?? 0,
                    "Amount"      => ""
                ];

                $report_data[] = [
                    "Trans Date"  => $trans_date,
                    "Post Date"   => $trans_date,
                    "Employee"    => $r["employee"]->special_id,
                    "Code"        => $sql_data_of_emp["sql_dd2_code"] ?? '',
                    "Description" => $sql_data_of_emp["sql_dd2_description"] ?? '',
                    "Work Unit"   => "1",
                    "Rate"        => $sql_data_of_emp["sql_dd2_rate"] ?? 0,
                    "Amount"      => ""
                ];
            }
        }

        return $report_data;
    }
    public function get_pending_shift_worked_hours_data($all_data, $date2)
    {
        $report_data = [];

        $trans_date = ($date2 instanceof DateTime) ? $date2->format('d/m/Y') : date('d/m/Y', strtotime($date2));

        foreach ($all_data as $r) {
            $sql_data_of_emp = $this->get_sql_data_cached($r["employee"]->branch_id);

            $shift_hours = ($r["worked_days"] + $r["worked_rest_days"] + $r["worked_holidays"]) * 8;

            if ($r["employee"]->is_shift_hours) {
                $shift_hours = toDecimal($r["shift_hours_total"]);
            }

            $report_data[] = [
                "Trans Date"  => $trans_date,
                "Post Date"   => $trans_date,
                "Employee"    => $r["employee"]->special_id,
                "Code"        => $sql_data_of_emp["sql_wsh_code"] ?? '',
                "Description" => $sql_data_of_emp["sql_wsh_description"] ?? '',
                "Worked Days" => $shift_hours,
                "Rate"        => $sql_data_of_emp["sql_wsh_rate"] ?? 0
            ];
        }

        return $report_data;
    }
    public function get_pending_worked_hours_data($all_data, $date2)
    {
        $report_data = [];

        $trans_date = ($date2 instanceof DateTime) ? $date2->format('d/m/Y') : date('d/m/Y', strtotime($date2));

        foreach ($all_data as $r) {
            $sql_data_of_emp = $this->get_sql_data_cached($r["employee"]->branch_id);

            $report_data[] = [
                "Trans Date"  => $trans_date,
                "Post Date"   => $trans_date,
                "Employee"    => $r["employee"]->special_id,
                "Code"        => $sql_data_of_emp["sql_wh_code"] ?? '',
                "Description" => $sql_data_of_emp["sql_wh_description"] ?? '',
                "Worked hours" => toDecimal($r["work"]),
                "Rate"        => $sql_data_of_emp["sql_wh_rate"] ?? 0
            ];
        }

        return $report_data;
    }
    public function get_pending_leave_application_data($paid_leaves_array)
    {
        $report_data = [];

        foreach ($paid_leaves_array as $key => $dated_array) {
            foreach ($dated_array as $paid_leave) {
                $report_data[] = [
                    "Code"        => $paid_leave["employee_special_id"],
                    "Date"        => $key,                    // leave date (from array key)
                    "Leave Type"  => $paid_leave["leave_type"],
                    "Description" => "",                      // empty as in original
                    "Day"         => $paid_leave['paid_leave']
                ];
            }
        }

        return $report_data;
    }

    /**
     * Pending Allowance Report — maps to pendingAllowanceReportLogFile in Exports.php
     * Columns: Trans Date, Post Date, Employee, Code, Description, Work Unit, Rate, Amount
     */
    public function get_pending_allowance_report_data($allowances, $date2)
    {
        $report_data = [];
        $trans_date = ($date2 instanceof DateTime) ? $date2->format('d/m/Y') : date('d/m/Y', strtotime($date2));

        foreach ($allowances as $allowance) {
            $report_data[] = [
                "Trans Date"  => $trans_date,
                "Post Date"   => $trans_date,
                "Employee"    => $allowance->special_id,
                "Code"        => $allowance->code,
                "Description" => $allowance->description,
                "Work Unit"   => $allowance->work_unit,
                "Rate"        => $allowance->rate,
                "Amount"      => $allowance->amount
            ];
        }

        return $report_data;
    }
    function get_company_working_hours($company_id = false)
    {
        $ci = &get_instance();
        $ci->db->select('id, group_id, date_format(total_hours,"%H:%i") as working_hours, date_format(half_hours, "%H:%i") as half_hours');
        $ci->db->from('company_working_hours');
        $ci->db->where('company_id', $company_id);
        return $ci->db->get()->result();
    }

    public function get_result_list_overnight($employees, $first_day, $last_day, $company_id)
    {
        $clockings_table = get_clockings_table_name($first_day);
        $first_day = date('Y-m-d', strtotime('-1 day', strtotime($first_day)));
        $ci = &get_instance();

        $interval_minutes = get_interval_minutes($company_id);

        $result = $ci->db->select('c.employee_id,c.id,date_format(date_sub(clock_in, interval ' . $interval_minutes . ' minute),"%d/%m %a") as day_f,clock_in as clock_in_o, date_format(clock_in,"%H:%i") as clock_in, date_format(clock_in,"%d-%m-%Y %H:%i") as clock_in_1,date_format(clock_out,"%H:%i") as clock_out,date_format(clock_out,"%d-%m-%Y %H:%i") as clock_out_1,clock_in_id,clock_out_id,s.grace_time as grace_time_o, date_format(s.end_time,"%H:%i") as end_time, date_format(s.grace_time,"%H:%i") as grace_time, s.start_time as start_time_o, date_format(s.start_time, "%H:%i") as start_time, s.name,s.code,reason,c.remark,date_format(end_time,"%H:%i") as end_time,date_format(overtime_starts,"%H:%i") as overtime_starts,date_format(early_ot_start,"%H:%i") as early_ot_start,date_format(early_ot_end,"%H:%i") as early_ot_end,time_format(timediff(end_time,start_time),"%H:%i") as shift_hours, fixed_ot, fixed_overtime, auto_approve_ot, r.remark as shift_remark, sr.remark as staff_remark, is_leave,void_late_in,void_early_out, date_format(break_duration,"%H:%i") as break_duration, date_format(break_1,"%H:%i") as break_1, consider_break_1, date_format(break_2,"%H:%i") as break_2, consider_break_2, date_format(break_3,"%H:%i") as break_3, consider_break_3, date_format(break_4,"%H:%i") as break_4, consider_break_4, date_format(break_5,"%H:%i") as break_5, consider_break_5, date_format(break_6,"%H:%i") as break_6, consider_break_6, half_day,date_format(date_sub(clock_in, interval ' . $interval_minutes . ' minute), "%Y-%m-%d") as search_date, s.extra_ot, date_format(s.extra_ot_worked_hours_more_than, "%H:%i") as extra_ot_worked_hours_more_than, date_format(s.extra_ot_hours, "%H:%i") as extra_ot_hours, date_format(extra_break_1,"%H:%i") as extra_break_1, date_format(extra_break_2,"%H:%i") as extra_break_2, date_format(extra_break_3,"%H:%i") as extra_break_3, date_format(extra_break_4,"%H:%i") as extra_break_4, date_format(extra_break_5,"%H:%i") as extra_break_5, date_format(extra_break_6,"%H:%i") as extra_break_6, extra_break, date_format(extra_break_worked_hours_more_than, "%H:%i") as extra_break_worked_hours_more_than', false)->from($clockings_table . ' c')->join('shifts s', 'c.shift_id = s.id', 'left')->join('remarks r', 'r.remark_date = date(date_sub(clock_in, interval ' . $interval_minutes . ' minute)) and r.employee_id = c.employee_id', 'left')->join('staff_remarks sr', 'sr.remark_date = date(date_sub(clock_in, interval ' . $interval_minutes . ' minute)) and sr.employee_id = c.employee_id', 'left')->where('date(date_sub(clock_in, interval ' . $interval_minutes . ' minute)) >=', $first_day)->where('date(date_sub(clock_in, interval ' . $interval_minutes . ' minute)) <=', $last_day)->where_in('c.employee_id', $employees)->order_by('clock_in_o')->get()->result();

        return $result;
    }

    private function response_json($data, $status_code = 200)
    {
        http_response_code($status_code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }

    // --- MAIN CALCULATION FUNCTION (PRESERVED EXACTLY AS REQUESTED) ---
    function calculate_summary_data(
        $employee,
        $first_day,
        $last_day,
        $result_list,
        $result_list_overnight,
        $company_working_hours,
        $public_holidays = false,
        $ot_settings,
        $early_ot_settings,
        $approved_ot_list,
        $rest_and_off_days,
        $cid,
        &$worked_rest_days_array = [],
        &$worked_off_days_array = [],
        &$worked_holidays_array = [],
        &$unpaid_leaves_absent_days = [],
        $clockings_news = null,
        $clockings_news_overnight = null,
        &$paid_leaves_array = [],
        &$daily_ot_array = [],
        &$daily_late_array = [],
        $days_settings = null,
        $ot_type_data_map = null,
        $bulk = null
    ) {

        $ci = &get_instance();

        $emp_id = $employee->id;
        $companies_allowed_for_monthly_ot = companies_allowed_for_monthly_ot();
        $tsf_custom_summary = false;
        $custom_in_outs = false;


        $data = [];
        $data['tsf_custom_summary'] = $tsf_custom_summary;
        $data['custom_in_outs'] = $custom_in_outs;

        if ($cid == 223 || $cid == 259) $custom_in_outs = true;
        if ($cid == 146) $tsf_custom_summary = true;

        $company_working_hours = get_employee_working_hours($company_working_hours, $emp_id);

        $company_half_hours = $company_working_hours->half_hours;
        $company_half_hours_decimal = toDecimal($company_half_hours);
        $company_working_hours = $company_working_hours->working_hours;
        $company_working_hours_decimal = toDecimal($company_working_hours);

        if ($rest_and_off_days === false) {
            $rest_and_off_days = $ci->db->select('rest_days,off_days')->from('branches')->where('id', $employee->branch_id)->get()->row();
            $rest_days = explode(",", $rest_and_off_days->rest_days);
            $off_days = explode(",", $rest_and_off_days->off_days);
        } else {
            $rest_days = explode(",", search_from_rest_days($rest_and_off_days, $employee->branch_id));
            $off_days = explode(",", search_from_off_days($rest_and_off_days, $employee->branch_id));
        }
        // Use bulk helper if available, otherwise query per employee (backward compat)
        if ($bulk !== null) {
            $public_holidays = $bulk->get_public_holidays_mine($emp_id, $employee->branch_id);
        } else {
            $public_holidays = get_public_holidays_mine($emp_id, $employee->branch_id, $first_day, $last_day);
        }


        $apply_overtime = $employee->is_ot == 1 ? true : false;
        $apply_early_overtime = $employee->is_early_ot == 1 ? true : false;

        $inc_late_in = $employee->inc_late_in == 1 ? true : false;
        $inc_late_break = $employee->inc_late_break  == 1 ? true : false;
        $inc_early_out = $employee->inc_early_out == 1 ? true : false;
        $inc_short_hours = $employee->inc_short_hours == 1 ? true : false;
        $void_minutes = $employee->void_lateness_time_if_less_than;

        if ($employee->deduct_from_ot_single != "not_sure") {
            $deduct_from_ot = $employee->deduct_from_ot_single == "yes" ? true : false;
        } else {
            $deduct_from_ot = $employee->deduct_from_ot == 1 ? true : false;
        }

        $deduction_date = $employee->deduction_date;
        $employee->deduct_from_ot = $deduct_from_ot;

        $period = new DatePeriod(
            new DateTime($first_day),
            new DateInterval('P1D'),
            (new DateTime($last_day))->add(new DateInterval('P1D'))
        );

        $total = "00:00";
        $work = "00:00";
        $shift_hours_total = "00:00";
        $break = "00:00";
        $late = "00:00";
        $total_late = "00:00";
        $late_count = 0;
        $break_late = "00:00";
        $total_days = 0;
        $total_meal_days = 0;
        $total_trip_a = 0;
        $total_trip_b = 0;
        $paid_leaves = 0;
        $unpaid_leaves = 0;
        $full_unpaid_leaves = 0;
        $allowance_leaves = 0;
        $total_holidays = 0;
        $worked_holidays = 0;
        $worked_rest_days = 0;
        $worked_off_days = 0;
        $worked_days = 0;
        $lsk_non_worked_days = 0;
        $ln01_waived_days = 0;
        $working_days = 0;
        $absent_days = 0;
        $total_short = "00:00";
        $total_early = "00:00";
        $month_overtime = "00:00";
        $month_overtime_ph_x2 = "00:00";
        $month_overtime_ph_x3 = "00:00";
        $month_overtime_ph = "00:00";
        $month_overtime_rd = "00:00";
        $month_overtime_off = "00:00";
        $total_shift_hours = "00:00";
        $total_early_count = 0;
        $total_half_day_paid = 0;
        $total_half_day_unpaid = 0;
        $total_full_day_paid = 0;
        $total_medical_leaves = 0;
        $total_break_late = 0;
        $total_missing_in_out = 0;
        $total_absent_unpaid = 0;
        $total_early_late = 0;
        $total_off_days = 0;
        $total_late_only_count = 0;
        $total_rest_days_used = 0;

        $total_bmi_ot = 0;
        $total_bmi_ot_sunday = 0;
        $total_bmi_ph_1 = 0;
        $total_bmi_ph_2 = 0;
        $total_bmi_ta = 0;
        $total_bmi_ma = 0;
        $total_bmi_ca = 0;
        $total_bmi_spa = 0;
        $total_bmi_aca = 0;
        $total_bmi_fl = 0;
        $total_bmi_cw = 0;
        $total_bmi_mo = 0;
        $total_bmi_shift1 = 0;
        $total_bmi_shift2 = 0;
        $total_bmi_shift3 = 0;

        $bmi_attendance_allowance = true;
        $bmi_late_more_than_10 = 0;

        $gbr_attendance_allowance = true;
        $gbr_night_shifts = 0;
        $monthly_nsa_count = 0;
        $monthly_dsa_count = 0;

        $food_allowance_days = 0;
        $ln01_attendance_allowance_days = 0;

        // Use pre-fetched days_settings if available, otherwise query (backward compat)
        if ($days_settings === null) {
            $days_settings = $ci->db->select('from_hour,to_hour,days')->from('days_settings')->where('company_id', $cid)->get()->result();
        }

        // --- Use PayrollBulkHelper when available, otherwise fall back to per-employee queries ---
        if ($bulk !== null) {
            $is_ot_list = $bulk->get_is_ot_list($emp_id);
            $is_late_list = $bulk->get_is_late_list($emp_id);
            $is_late_break_list = $bulk->get_is_late_break_list($emp_id);
            $is_early_out_list = $bulk->get_is_early_out_list($emp_id);
            $manual_late_list = $bulk->get_manual_late_list($emp_id);
            $manual_late_break_list = $bulk->get_manual_late_break_list($emp_id);
            $shift_list = $bulk->get_shift_list($emp_id);
            $remark_list = $bulk->get_remark_list($emp_id);
            $staff_remark_list = $bulk->get_staff_remark_list($emp_id);
            $manual_ot_list = $bulk->get_manual_ot_list($emp_id);
            $manual_early_out_list = $bulk->get_manual_early_out_list($emp_id);
            $manual_short_hours_list = $bulk->get_manual_short_hours_list($emp_id);
            $trip_a_list = $bulk->get_trip_a_list($emp_id);
            $trip_b_list = $bulk->get_trip_b_list($emp_id);
        } else {
            $is_ot_list = get_is_ot_list($emp_id, $first_day, $last_day);
            $is_late_list = get_is_late_list($emp_id, $first_day, $last_day);
            $is_late_break_list = get_is_late_break_list($emp_id, $first_day, $last_day);
            $is_early_out_list = get_is_early_out_list($emp_id, $first_day, $last_day);
            $manual_late_list = get_manual_late_list($emp_id, $first_day, $last_day);
            $manual_late_break_list = get_manual_late_break_list($emp_id, $first_day, $last_day);
            $shift_list = get_shift_list($emp_id, $first_day, $last_day);
            $remark_list = get_remark_list($emp_id, $first_day, $last_day);
            $staff_remark_list = get_staff_remark_list($emp_id, $first_day, $last_day);
            $manual_ot_list = get_manual_ot_list($emp_id, $first_day, $last_day);
            $manual_early_out_list = get_manual_early_out_list($emp_id, $first_day, $last_day);
            $manual_short_hours_list = get_manual_short_hours_list($emp_id, $first_day, $last_day);
            $trip_a_list = get_trip_a_list($emp_id, $first_day, $last_day);
            $trip_b_list = get_trip_b_list($emp_id, $first_day, $last_day);
        }

        $is_offense_list = [];

        if ($cid == 66) {
            if ($bulk !== null) {
                $manual_ta_list = $bulk->get_manual_ta_list($emp_id);
                $manual_ma_list = $bulk->get_manual_ma_list($emp_id);
                $manual_ca_list = $bulk->get_manual_ca_list($emp_id);
                $manual_spa_list = $bulk->get_manual_spa_list($emp_id);
                $manual_aca_list = $bulk->get_manual_aca_list($emp_id);
                $manual_fl_list = $bulk->get_manual_fl_list($emp_id);
                $manual_cw_list = $bulk->get_manual_cw_list($emp_id);
                $manual_mo_list = $bulk->get_manual_mo_list($emp_id);
                $manual_shift1_list = $bulk->get_manual_shift1_list($emp_id);
                $manual_shift2_list = $bulk->get_manual_shift2_list($emp_id);
                $manual_shift3_list = $bulk->get_manual_shift3_list($emp_id);
            } else {
                $manual_ta_list = get_manual_ta_list($emp_id, $first_day, $last_day);
                $manual_ma_list = get_manual_ma_list($emp_id, $first_day, $last_day);
                $manual_ca_list = get_manual_ca_list($emp_id, $first_day, $last_day);
                $manual_spa_list = get_manual_spa_list($emp_id, $first_day, $last_day);
                $manual_aca_list = get_manual_aca_list($emp_id, $first_day, $last_day);
                $manual_fl_list = get_manual_fl_list($emp_id, $first_day, $last_day);
                $manual_cw_list = get_manual_cw_list($emp_id, $first_day, $last_day);
                $manual_mo_list = get_manual_mo_list($emp_id, $first_day, $last_day);
                $manual_shift1_list = get_manual_shift1_list($emp_id, $first_day, $last_day);
                $manual_shift2_list = get_manual_shift2_list($emp_id, $first_day, $last_day);
                $manual_shift3_list = get_manual_shift3_list($emp_id, $first_day, $last_day);
            }
        }

        $ot_settings = search_from_list_by_branch_id($ot_settings, $employee->branch_id);
        $early_ot_settings = search_from_list_by_branch_id($early_ot_settings, $employee->branch_id);


        // Use pre-fetched ot_type_data if available, otherwise query (backward compat)
        if ($ot_type_data_map !== null && isset($ot_type_data_map[$employee->branch_id])) {
            $ot_type_data = $ot_type_data_map[$employee->branch_id];
        } else {
            $ot_type_data = $ci->db->select("ot_weekly_hours, ot_type")->from("branches")->where("company_id", $cid)
                ->where("id", $employee->branch_id)->get()->row();
        }

        // Use bulk helper for replacement leaves and replaced PH, or fall back to per-employee queries
        if ($bulk !== null) {
            $replacement_leaves_list = $bulk->get_replacement_leaves_list($emp_id);
            $replaced_ph_list = $bulk->get_replaced_ph_list($emp_id);
        } else {
            $replacement_leaves_list = get_replacement_leaves_list($emp_id, $first_day, $last_day);
            $replaced_ph_list = get_replaced_ph_list($emp_id, $first_day, $last_day);
        }
        $jl01_paid_leaves = $paid_leaves_array;

        // Use bulk helper for branch settings, or fall back to per-branch queries
        if ($bulk !== null) {
            $_cached_late_in_settings = $bulk->get_late_in_settings($employee->branch_id);
            $_cached_late_break_settings = $bulk->get_late_break_settings($employee->branch_id);
            $_cached_early_out_settings = $bulk->get_early_out_settings($employee->branch_id);
        } else {
            $_cached_late_in_settings = get_late_in_settings($employee->branch_id);
            $_cached_late_break_settings = get_late_break_settings($employee->branch_id);
            $_cached_early_out_settings = get_early_out_settings($employee->branch_id);
        }

        $last_ids = [];
        $dates = []; // Initialize dates array

        foreach ($period as $date) {
            $obj = new stdClass();
            $obj->date = $date->format('Y-m-d');
            $obj->day_name = $date->format('l');
            $date_f = $date->format('d-m-Y');
            $date_string = $date->format('d/m D');
            $obj->date_string = $date_string;
            $obj->shift_hours = "";
            $obj->full_shift_hours = "";
            $obj->is_extra_ot = false;
            // Attendance-state flags consumed by Queue_worker::_build_month_lock_detail_rows()
            // when persisting month_lock_details. Default false; set true below at the exact
            // points where each state is actually determined.
            $obj->is_worked = false;
            $obj->is_absent = false;
            $obj->is_off_day = false;
            $obj->is_worked_rest_day = false;
            $obj->is_worked_off_day = false;
            $obj->is_worked_holiday = false;

            if ($tsf_custom_summary) {
                $address_distance = $ci->db->select('address, scan_distance')->from('clockings_news')
                    ->where('employee_id', $emp_id)
                    ->where('datetime >=', $obj->date . ' 00:00:00')
                    ->where('datetime <=', $obj->date . ' 23:59:59')
                    ->limit(1)
                    ->get()
                    ->row();
                $obj->location = isset($address_distance->address) ? $address_distance->address : "";
                $obj->distance = isset($address_distance->scan_distance) ? $address_distance->scan_distance : "";
            }

            $replacement = is_replacement($replacement_leaves_list, $obj->date);
            $is_ot = false;
            $is_late = true;
            $is_late_break = true;
            $is_early_out = true;
            $overnight = false;
            $is_shift = false;

            $shift_check = search_from_list($shift_list, $obj->date);
            $next_shift_check = search_from_list($shift_list, add_days_to_date($date, 1)->format("Y-m-d"));
            $obj->shift_check = $shift_check;
            $obj->shift_name = "";
            $obj->acting_code = "";
            $obj->cut_off_time = "";
            $acting_codes = [];
            $half_day = false;

            if ($shift_check) {
                $is_shift = true;
                if ($shift_check->half_day == "Yes") {
                    $half_day = true;
                }
                $obj->shift_hours = $shift_check->shift_hours;
                $obj->full_shift_hours = $shift_check->shift_hours;
                $total_shift_hours = add_time($total_shift_hours, $obj->shift_hours);
                $obj->shift_name = $shift_check->name;
                $obj->acting_code = str_replace(",", "|", $shift_check->acting_code);
                $obj->cut_off_time = $shift_check->cut_off_time;
                $acting_codes = explode(",", $shift_check->acting_code);

                if ($shift_check->code == "N") {
                    $gbr_night_shifts++;
                }
            }
            if ($shift_check && $shift_check->overnight == "Yes") {
                $result = search_clocking_by_id($result_list_overnight, $obj->date, $emp_id);
                $overnight = true;
                $result = remove_next_day_clockings($result, $shift_check, $next_shift_check);
            } else {
                $result = search_clocking_by_id($result_list, $obj->date, $emp_id);
                $result = remove_duplicate_clockings($result, $obj->date, $shift_list, $result_list_overnight);
            }
            $result = get_clockings_from_previous_day($result, $result_list_overnight, $obj->date, $emp_id, $shift_list);
            $obj->overnight = $overnight ? "true" : "false";
            $obj->is_shift = $is_shift ? "true" : "false";
            $obj->is_leave = $shift_check && $shift_check->is_leave == "yes";
            $obj->is_paid_leave = $shift_check && $shift_check->is_leave == "yes" && $shift_check->is_paid == "yes";
            $obj->is_unpaid_leave = $shift_check && $shift_check->is_leave == "yes" && $shift_check->is_paid == "no";

            $is_replaced_ph = search_from_list($replaced_ph_list, $obj->date) ? true : false;
            $obj->is_replaced_ph = $is_replaced_ph;
            $obj->merit_is_half_day_paid = false;
            $obj->merit_is_full_day_paid = false;
            $obj->merit_is_half_day_unpaid = false;
            $obj->merit_is_medical_leave = false;
            $obj->merit_is_break_late = false;
            $obj->merit_is_missing_in_out = false;
            $obj->merit_is_absent_unpaid = false;
            $obj->merit_is_offense =  false;
            $obj->merit_is_early_out = false;
            $obj->merit_is_late = false;

            if ($overnight) {
                $clockings_news_result = search_clocking_by_id($clockings_news_overnight, $obj->date, $emp_id);
            } else {
                $clockings_news_result = search_clocking_by_id($clockings_news, $obj->date, $emp_id);
            }

            $clockings_news_result = remove_last_ids($clockings_news_result, $last_ids);
            $last_ids = [];

            if (!empty($clockings_news_result)) {
                if ($clockings_news_result[0]->type === "out" || $clockings_news_result[0]->add_by_admin == 1) {
                    $obj->merit_is_missing_in_out = true;
                }
                if (end($clockings_news_result)->type === "in" || end($clockings_news_result)->add_by_admin == 1) {
                    $obj->merit_is_missing_in_out = true;
                }
            }
            $missing_in_out_counter = 0;
            foreach ($clockings_news_result as $key => $value) {
                $last_ids[] = $value->id;
                if (($missing_in_out_counter % 2 === 0 && $value->type === "out") || $value->add_by_admin == 1) {
                    $obj->merit_is_missing_in_out = true;
                    $missing_in_out_counter++;
                } else if (($missing_in_out_counter % 2 === 1 && $value->type === "in") || $value->add_by_admin == 1) {
                    $obj->merit_is_missing_in_out = true;
                    $missing_in_out_counter++;
                }
                $missing_in_out_counter++;
            }

            if ($obj->merit_is_missing_in_out === true) $total_missing_in_out++;

            if ($shift_check && $shift_check->is_rest_day) {
                if (in_array($obj->date, $public_holidays)) {
                    $total_rest_days_used += $shift_check->public_holiday_deduction;
                } else if (in_array($obj->day_name, ["Saturday", "Sunday"])) {
                    $total_rest_days_used += $shift_check->weekend_deduction;
                } else {
                    $total_rest_days_used += $shift_check->weekday_deduction;
                }
            }

            if (!$shift_check && empty($result) && $obj->date <= date('Y-m-d')) {
                $total_off_days++;
            }

            if (!in_array($obj->date, $public_holidays) && !in_array($obj->day_name, $rest_days) && !$obj->is_replaced_ph && !in_array($obj->day_name, $off_days) && !($shift_check && $shift_check->is_rest_day)) {
                $check = false;
                if ($shift_check) {
                    $add_day = 1;
                    if ($shift_check->half_day == "Yes") {
                        $add_day = 0.5;
                    }
                    if ($shift_check->is_leave == "yes" && $shift_check->is_paid == "yes") {
                        $paid_leaves += $add_day;
                        if (in_array($shift_check->code, get_attendance_allowance_leave_codes())) {
                            $allowance_leaves += $add_day;
                        }
                        $paid_leaves_array[$date->format("d/m/Y")][] = [
                            "employee_special_id" => $employee->special_id,
                            "paid_leave" => $add_day,
                            "branch_id" => $employee->branch_id,
                            'leave_type' => $shift_check->code
                        ];
                        $check = true;
                        if (stripos($shift_check->name, 'medical leave') !== false) {
                            $obj->merit_is_medical_leave = true;
                            $total_medical_leaves++;
                            if ($total_medical_leaves > 1) {
                                $bmi_attendance_allowance = false;
                            }
                            $gbr_attendance_allowance = false;
                            $lsk_non_worked_days++;
                        }
                        if ($add_day === 0.5) {
                            $obj->merit_is_half_day_paid = true;
                            $total_half_day_paid++;
                        } else if (stripos($shift_check->name, 'medical leave') === false) {
                            $obj->merit_is_full_day_paid = true;
                            $total_full_day_paid++;
                        }
                    } else if ($shift_check->is_leave == "yes" && $shift_check->is_paid == "no") {
                        $unpaid_leaves += $add_day;
                        if (in_array($shift_check->code, get_attendance_allowance_leave_codes())) {
                            $allowance_leaves += $add_day;
                        }
                        if ($add_day === 0.5) {
                            $obj->merit_is_half_day_unpaid = true;
                            $total_half_day_unpaid++;
                        } else {
                            $obj->merit_is_absent_unpaid = true;
                            $total_absent_unpaid++;
                            $full_unpaid_leaves++;
                        }
                        $check = true;
                        $unpaid_leaves_absent_days[$date->format("d/m/Y")][] = [
                            "employee_special_id" => $employee->special_id,
                            "unpaid_leave" => $add_day,
                            "branch_id" => $employee->branch_id,
                            "type" => "unpaid_leave"
                        ];
                        $bmi_attendance_allowance = false;
                        $lsk_non_worked_days++;
                    }
                    $working_days++;
                }
                if (!$check && empty($result) && $shift_check) {
                    if ($obj->date <= date('Y-m-d')) {
                        if ($replacement) {
                            if ($replacement->to !== $obj->date) {
                                if ($shift_check->start_time != NULL && $shift_check->end_time != NULL) {
                                    $absent_days++;
                                    $obj->merit_is_absent_unpaid = true;
                                    $obj->is_absent = true;
                                    $total_absent_unpaid++;
                                    $unpaid_leaves_absent_days[$date->format("d/m/Y")][] = [
                                        "employee_special_id" => $employee->special_id,
                                        "unpaid_leave" => $add_day,
                                        "branch_id" => $employee->branch_id,
                                        "type" => "absent"
                                    ];
                                }
                            }
                        } else {
                            if ($shift_check->start_time != NULL && $shift_check->end_time != NULL) {
                                $absent_days++;
                                $obj->merit_is_absent_unpaid = true;
                                $obj->is_absent = true;
                                $total_absent_unpaid++;
                                $unpaid_leaves_absent_days[$date->format("d/m/Y")][] = [
                                    "employee_special_id" => $employee->special_id,
                                    "unpaid_leave" => $add_day,
                                    "branch_id" => $employee->branch_id,
                                    "type" => "absent"
                                ];
                            }
                        }
                        $bmi_attendance_allowance = false;
                        $gbr_attendance_allowance = false;
                        $lsk_non_worked_days++;
                    }
                }
            }

            if ($cid == 196 && $shift_check && $shift_check->is_leave == "yes") {
                if (in_array($obj->date, $public_holidays)) {
                    $deduction = $shift_check->public_holiday_deduction;
                } else if (in_array($obj->day_name, ["Saturday", "Sunday"])) {
                    $deduction = $shift_check->weekend_deduction;
                } else {
                    $deduction = $shift_check->weekday_deduction;
                }
                $jl01_paid_leaves[$date->format("d/m/Y")][] = [
                    "employee_special_id" => $employee->special_id,
                    "paid_leave" => $deduction,
                    "branch_id" => $employee->branch_id,
                    'leave_type' => $shift_check->code
                ];
            }

            $total_hours = "";
            $work_hours = "";
            $break_hours = "";
            $late_hours = "";
            $break_late_hours = "";
            $early_out = "";
            $short_hours = "";
            $tripA = 0;
            $tripB = 0;
            $total_clockings = count($result);
            $formatted_data = array();

            $is_ot_result = search_from_list($is_ot_list, $obj->date);
            if ($is_ot_result) {
                $is_ot = $is_ot_result->is_ot == "Y" ? true : false;
            } else {
                $is_ot = get_is_ot_status($approved_ot_list, $shift_check, $obj->date, $emp_id, $total_clockings, $cid);
            }

            $is_late_result = search_from_list($is_late_list, $obj->date);
            if ($is_late_result) {
                $is_late = $is_late_result->is_late == "Y" ? true : false;
            }

            $is_late_break_result = search_from_list($is_late_break_list, $obj->date);
            if ($is_late_break_result) {
                $is_late_break = $is_late_break_result->is_late_break == "Y" ? true : false;
            }

            $is_early_out_result = search_from_list($is_early_out_list, $obj->date);
            if ($is_early_out_result) {
                $is_early_out = $is_early_out_result->is_early_out == "Y" ? true : false;
            }

            $is_offense_result = search_from_list($is_offense_list, $obj->date);
            if ($is_offense_result) {
                $obj->merit_is_offense = $is_offense_result->is_offense == "Y" ? true : false;
            }

            $in_outs = [];
            $in_outs_ids = [];
            $last_out = "";
            foreach ($result as $key => $value) {
                $in_outs_ids[] = $value->clock_in_id;
                $in_outs_ids[] = $value->clock_out_id;
                $in_outs[] = $value->clock_in;
                $in_outs[] = $value->clock_out;

                if ($key == 0 && $value->shift_remark != null && $value->shift_remark != "") {
                    $value->remark = $value->shift_remark;
                }
                $value->total_time = calculate_total_hours($value->clock_in_1, $value->clock_out_1, $value->start_time, $value->early_ot_start, $value->early_ot_end, $value->search_date);
                if ($value->name == "") $value->name = "N/A";
                if ($value->code == "") $value->code = "N/A";
                $value->is_break = false;

                $formatted_data[] = $value;
                if (array_key_exists($key + 1, $result)) {
                    $x = new stdClass();
                    $x->day_f = $value->day_f;
                    $x->overtime_starts = $value->overtime_starts;
                    $x->early_ot_start = $value->early_ot_start;
                    $x->early_ot_end = $value->early_ot_end;
                    $x->grace_time = $value->grace_time;
                    $x->clock_in = $value->clock_out;
                    $x->clock_in_1 = $value->clock_out_1;
                    $x->clock_out = $result[$key + 1]->clock_in;
                    $x->clock_out_1 = $result[$key + 1]->clock_in_1;
                    $x->name = "Break";
                    $x->code = "Break";
                    $x->is_break = true;
                    $x->reason = "";
                    $x->remark = "";
                    $x->staff_remark = "";
                    $x->is_ot = $is_ot;
                    $x->total_time = total_time($result[$key + 1]->clock_in_1, $value->clock_out_1);
                    $formatted_data[] = $x;
                } else {
                    $last_out = $value->clock_out_1;
                }
            }
            if (!$half_day) {
                $manual_early_out = search_from_list($manual_early_out_list, $obj->date);
                if ($manual_early_out) {
                    $early_out = $manual_early_out->early_out;
                    $early_out = round_off_early_out($early_out, $_cached_early_out_settings, false);
                } else if ($last_out != "" && $shift_check && $shift_check->void_early_out == "No") {
                    $early_out = calculate_early_out($last_out, $shift_check->end_time, $obj->date, $overnight);
                }
            }

            if ($cid == 206 && $obj->day_name != "Sunday") {
                $last_out_time = $last_out ? explode(" ", $last_out)[1] : "";
                if ($last_out_time >= "19:00") {
                    $food_allowance_days++;
                }
            }

            $obj->early_out = round_off_early_out($early_out, $_cached_early_out_settings, false);
            $obj->clockings = $formatted_data;
            $obj->in_outs = $in_outs;
            $obj->in_outs_id = $in_outs_ids;
            if ($result) {
                $v = $result[0];
            }

            $break_and_late_hours = calculate_break_and_late_hours($obj->clockings, $v, $overnight);
            $work_hours = $break_and_late_hours->work_hours;
            $break_hours = $break_and_late_hours->break_hours;
            $breaks_array = $break_and_late_hours->breaks_array;
            $shift_break_hours = $break_and_late_hours->shift_break_hours;
            $shift_breaks_array = $break_and_late_hours->shift_breaks_array;
            $after_ot_starts_break_hours = $break_and_late_hours->after_ot_starts_break_hours;

            foreach ($obj->clockings as $key => $value) {
                if ($key != 0) {
                    $value->day_f = '';
                }
                $total_hours = add_time($total_hours, $value->total_time);
                if ($key == 0) {
                    $manual_late = search_from_list($manual_late_list, $obj->date);
                    if ($manual_late) {
                        $late_hours = $manual_late->late_hours;
                        $late_hours = round_off_late_in($late_hours, $_cached_late_in_settings, false);
                    } else if (isset($v) && $v->is_leave != "" && $v->is_leave != "yes" && $v->void_late_in == "No") {
                        if ($v->grace_time != "") {
                            if ($overnight) {
                                $grace_time = $obj->date . " " . $v->grace_time . ":00";
                                $grace_time_stamp = strtotime($grace_time);
                                $mid_day = $obj->date . " 12:00:00";
                                $mid_day_stamp = strtotime($mid_day);
                                if (in_array($shift_check->same_day_overnight, ['default', 'next'])) {
                                    if ($mid_day_stamp > $grace_time_stamp) {
                                        $grace_time_stamp += 24 * 3600;
                                    }
                                }
                                $clock_in_stamp = strtotime($v->clock_in_o);

                                if ($clock_in_stamp > $grace_time_stamp) {
                                    $late_stamp = $clock_in_stamp - $grace_time_stamp;
                                    date_default_timezone_set('UTC');
                                    $late_hours = date('H:i', $late_stamp);
                                    date_default_timezone_set("Asia/Kuala_Lumpur");
                                }
                            } else if (intval(str_replace(":", "", $v->clock_in)) > intval(str_replace(":", "", $v->grace_time))) {
                                $late_hours = sub_time($v->clock_in, $v->grace_time);
                            }
                        }
                    }
                }
            }

            if ($late_hours > "00:10") {
                $bmi_late_more_than_10++;
                if ($bmi_late_more_than_10 >= 3) {
                    $bmi_attendance_allowance = false;
                }
            }
            if ($late_hours > "00:00") {
                $gbr_attendance_allowance = false;
            }
            if ($late_hours >= "01:00") {
                $ln01_waived_days++;
            }
            if (($early_out != "" && $early_out != "00:00" && $is_early_out) || ($late_hours != "" && $late_hours != "00:00" && $is_late)) {
                $total_early_late++;
                $obj->merit_is_early_late = true;
            }

            $break_not_taken = "00:00";
            $extra_break_not_taken = "00:00";
            if (isset($v)) {
                $break_not_taken = calculate_break_not_taken($break_hours, $breaks_array, $v);
            }
            if ($work_hours != "" && $work_hours != "00:00") {
                $work_hours = sub_time($work_hours, $break_not_taken);
            }
            if (isset($v)) {
                $extra_break_not_taken = calculate_extra_break_not_taken($breaks_array, $v, $work_hours);
            }
            if ($work_hours != "" && $work_hours != "00:00") {
                $work_hours = sub_time($work_hours, $extra_break_not_taken);
            }
            if (!$half_day) {
                $manual_short_hours = search_from_list($manual_short_hours_list, $obj->date);
                if ($manual_short_hours) {
                    $short_hours = $manual_short_hours->short_hours;
                } else {
                    $short_hours = calculate_short_hours($company_working_hours, $work_hours);
                }
            }

            $trip_a = search_from_list($trip_a_list, $obj->date);
            $trip_b = search_from_list($trip_b_list, $obj->date);
            if ($trip_a) {
                $tripA = $trip_a->no_of_trips;
                $total_trip_a += $trip_a->no_of_trips;
            }
            if ($trip_b) {
                $tripB = $trip_b->no_of_trips;
                $total_trip_b += $trip_b->no_of_trips;
            }

            if (isset($v) && !$half_day) {
                $manual_late_break = search_from_list($manual_late_break_list, $obj->date);
                if ($manual_late_break) {
                    $break_late_hours = $manual_late_break->late_hours_break;
                    $break_late_hours = round_off_late_break($break_late_hours, $_cached_late_break_settings, false);
                } else {
                    if ($employee->ignore_breaks_after_endtime == 0) {
                        $break_late_hours = calculate_break_late($break_hours, $breaks_array, $v, $work_hours, $obj->is_shift);
                    } else {
                        $break_late_hours = calculate_break_late($shift_break_hours, $shift_breaks_array, $v, $work_hours, $obj->is_shift);
                    }
                }
            }

            if ($break_late_hours != "" && $break_late_hours != "00:00" && $is_late_break === true) {
                $obj->merit_is_break_late = true;
                $total_break_late++;
            }
            $obj->break_late_hours = round_off_late_break($break_late_hours, $_cached_late_break_settings, false);;

            $work_hours = add_deducted_time_in_work_hours($work_hours, $late_hours, $break_late_hours, $early_out, $inc_late_in, $inc_late_break, $inc_early_out, $is_late, $is_late_break, $is_early_out, $ot_type_data->ot_type);
            if (
                $work_hours > 0 &&
                in_array($cid, companies_allowed_for_shift_allowance()) &&
                isset($shift_check->shift_code)
            ) {
                if ($shift_check->shift_code === "DSA") {
                    $monthly_dsa_count++;
                } elseif ($shift_check->shift_code === "NSA") {
                    $monthly_nsa_count++;
                }
            }

            $days = "";
            $is_rest_day = false;
            $is_off_day = false;
            $is_ph_day = false;

            if (in_array($obj->date, $public_holidays) || $obj->is_replaced_ph) {
                $is_ph_day = true;
                $total_holidays++;
            }

            // Use bulk helper for per-day holiday lookup instead of per-day DB query
            if ($bulk !== null) {
                $ph = $bulk->get_public_holiday_by_date($obj->date, $employee->branch_id);
            } else {
                $ph = get_public_holiday_by_date($obj->date, $employee->branch_id, $cid);
            }
            if ($result) {
                $v = $result[0];
                $obj->is_worked = true;
                if ($days_settings) {
                    $days = calculate_days($work_hours, $days_settings);
                } else {
                    $days = 1;
                }
                if ($v->is_leave == "yes" && $v->half_day == "Yes") {
                    $days = 0.5;
                }
                if ($is_ph_day) {
                    $obj->is_worked_holiday = true;
                    if (!$employee->worked_hours_ot_ph && (!$ph || !$ph->replacement_ph)) {
                        $worked_holidays += (is_numeric($days) ? $days : 0);
                        if (($ph && $ph->rate == "x3") || $obj->is_replaced_ph) {
                            $worked_holidays_array[$date->format("d/m/Y")][] = [
                                "employee_special_id" => $employee->special_id,
                                "worked_holiday" => $days,
                                "branch_id" => $employee->branch_id,
                                "holiday_rate" => 'x3'
                            ];
                        } else {
                            $worked_holidays_array[$date->format("d/m/Y")][] = [
                                "employee_special_id" => $employee->special_id,
                                "worked_holiday" => $days,
                                "branch_id" => $employee->branch_id,
                                "holiday_rate" => 'x2'
                            ];
                        }
                    }

                    if ($cid == 229 && !empty($result)) {
                        $ln01_halfdays = ['HPAM', 'HPPM'];
                        if (!($obj->shift_check && $obj->shift_check->code && in_array($obj->shift_check->code, $ln01_halfdays)) && !$obj->is_leave) {
                            $ln01_attendance_allowance_days += ($days && $days >= 1 && $days != "-" ? $days : 0);
                        }
                    }

                    if ($cid == 66 && $obj->day_name != "Sunday") {
                        $worked_days++;
                    }
                } else if (in_array($obj->day_name, $off_days)) {
                    $is_off_day = true;
                    $obj->is_worked_off_day = true;
                    if (!$employee->worked_hours_ot_off) {
                        $worked_off_days += (is_numeric($days) ? $days : 0);
                        $worked_off_days_array[$date->format("d/m/Y")][] = [
                            "employee_special_id" => $employee->special_id,
                            "worked_off_day" => $days,
                            "branch_id" => $employee->branch_id,
                        ];
                    }
                } else if (in_array($obj->day_name, $rest_days) || $v->name == "N/A" || !$obj->shift_check || ($shift_check && $shift_check->is_rest_day)) {
                    $is_rest_day = true;
                    $obj->is_worked_rest_day = true;
                    if (!$employee->worked_hours_ot_rd) {
                        $worked_rest_days += (is_numeric($days) ? $days : 0);
                        $worked_rest_days_array[$date->format("d/m/Y")][] = [
                            "employee_special_id" => $employee->special_id,
                            "worked_rest_day" => $days,
                            "branch_id" => $employee->branch_id,
                        ];
                    }
                } else {
                    if ($cid == 229) {
                        $ln01_halfdays = ['HPAM', 'HPPM'];
                        if ($obj->shift_check && $obj->shift_check->code && in_array($obj->shift_check->code, $ln01_halfdays)) {
                        } elseif ($obj->is_leave) {
                        } else {
                            $ln01_attendance_allowance_days += ($days && $days >= 1 && $days != "-" ? $days : 0);
                        }
                    }
                    $worked_days += ($days && $days != "-" ? $days : 0);
                }
            } else {
                if (in_array($cid, $companies_allowed_for_monthly_ot)) {
                    if ($obj->is_paid_leave) {
                        if ($shift_check->half_day == "Yes") {
                            $days = 0.5;
                            $work_hours = $company_half_hours;
                        } else {
                            $work_hours = $company_working_hours;
                            $days = 1;
                        }
                    }
                }
            }

            $obj->is_rest_day = $is_rest_day;
            $obj->is_off_day = $is_off_day;

            if ($cid == 87) {
                if ($obj->is_paid_leave) {
                    if ($shift_check && $shift_check->half_day == 'Yes') {
                        $days = 0.5;
                        $work_hours = $company_half_hours;
                    } else {
                        $days = 1;
                        $work_hours = $company_working_hours;
                    }
                }
            }

            $obj->first_in = "";
            $obj->last_out = "";
            $obj->first_in_o = "";
            $obj->last_out_o = "";

            if ($result) {
                $obj->first_in = $result[0]->clock_in;
                $obj->last_out = end($result)->clock_out;
                $obj->first_in_o = $result[0]->clock_in_1;
                $obj->last_out_o = end($result)->clock_out_1;
            }

            $obj->total_hours = $total_hours;
            $obj->work_hours = $work_hours;
            $obj->work_hours_whole = floor(toDecimal($work_hours));

            $final_company_working_hours = $company_working_hours;
            $final_company_working_hours_decimal = $company_working_hours_decimal;
            if ($obj->day_name == 'Saturday' && $employee->use_half_hours_for_saturdays) {
                $final_company_working_hours = $company_half_hours;
                $final_company_working_hours_decimal = $company_half_hours_decimal;
            }

            if ($employee->ot_type == "eight_hours") {
                $decimal_work_hours = toDecimal($work_hours);
                if ($final_company_working_hours_decimal && $decimal_work_hours < $final_company_working_hours_decimal && $decimal_work_hours > 0) {
                    $decimal_early_out = $final_company_working_hours_decimal - $decimal_work_hours;
                    $eight_hours_early_out = decimal_to_time($decimal_early_out);
                    if (!$half_day) {
                        $manual_early_out = search_from_list($manual_early_out_list, $obj->date);
                        if ($manual_early_out) {
                            $early_out = $manual_early_out->early_out;
                        } else if ($last_out != "" && $shift_check && $shift_check->void_early_out == "No") {
                            $obj->early_out = $early_out = $eight_hours_early_out;
                        }
                    }
                }
            }
            $obj->break_hours = $break_hours;
            $obj->late_hours = round_off_late_in($late_hours, $_cached_late_in_settings, false);
            $obj->short_hours = $short_hours;
            $obj->trip_a = $tripA;
            $obj->trip_b = $tripB;
            $obj->days = (($is_rest_day && $employee->worked_hours_ot_rd) || ($is_ph_day && $employee->worked_hours_ot_ph) || ($is_off_day && $employee->worked_hours_ot_off)) ? "" : $days;
            $overtime = "";
            $early_overtime = "";
            $overtime_m = "";
            $overtime_type = "+";
            $is_manual_exist = false;
            $manual_ot = search_from_list($manual_ot_list, $obj->date);
            if ($manual_ot) {
                $overtime_m = $manual_ot->overtime;
                $overtime_type = $manual_ot->type;
                $is_manual_exist = true;
                if ($overtime_type == "-") {
                    $overtime_m = "-" . $overtime_m;
                }
            }
            $round_of_ot = 1;
            if ($shift_check) {
                $round_of_ot = $shift_check->round_off_ot;
            }
            if (($is_rest_day && $employee->worked_hours_ot_rd) || ($is_ph_day && $employee->worked_hours_ot_ph) || ($is_off_day && $employee->worked_hours_ot_off)) {
                if ($apply_overtime) {
                    $overtime = $work_hours;
                }
                $overtime = round_off_ot($overtime, $ot_settings, $employee->round_first_hour_only);
            } else {
                $overtime = calculate_final_overtime($result, $obj->clockings, $date_f, $overnight, $apply_overtime, $apply_early_overtime, $work_hours, $final_company_working_hours, $employee->ot_type, $employee->ot_round, $employee->round_first_hour_only, $employee->round_by_exact_hour, $employee->different_first_hour_rounding, $ot_settings, $obj->shift_hours, $round_of_ot, $final_company_working_hours_decimal, $employee->early_ot_round, $early_ot_settings);
                if ($employee->ignore_breaks_after_endtime == 1 && ($apply_overtime || $apply_early_overtime)) {
                    $overtime = add_time($overtime, "-" . $after_ot_starts_break_hours);
                    if ($overtime == "00:00") {
                        $overtime = "";
                    }
                }
            }

            if (($is_rest_day && $employee->deduct_hour_ot_rd) || ($is_ph_day && $employee->deduct_hour_ot_ph) || ($is_off_day && $employee->deduct_hour_ot_off)) {
                $overtime = deduct_hour_from_ot_rd($overtime);
            }

            if ($cid == 66) {
                $obj->bmi_ot = "";
                $obj->bmi_ot_sunday = "";
                $obj->bmi_ph_1 = "";
                $obj->bmi_ph_2 = "";
                $obj->bmi_ta_final = $obj->bmi_ta = $obj->bmi_ta_manual = "";
                $obj->bmi_ma_final = $obj->bmi_ma = $obj->bmi_ma_manual = "";
                $obj->bmi_ca_final = $obj->bmi_ca = $obj->bmi_ca_manual = "";
                $obj->bmi_spa_final = $obj->bmi_spa = $obj->bmi_spa_manual = "";
                $obj->bmi_aca_final = $obj->bmi_aca = $obj->bmi_aca_manual = "";
                $obj->bmi_fl_final = $obj->bmi_fl = $obj->bmi_fl_manual = "";
                $obj->bmi_cw_final = $obj->bmi_cw = $obj->bmi_cw_manual = "";
                $obj->bmi_mo_final = $obj->bmi_mo = $obj->bmi_mo_manual = "";
                $obj->bmi_shift1_final = $obj->bmi_shift1 = $obj->bmi_shift1_manual = "";
                $obj->bmi_shift2_final = $obj->bmi_shift2 = $obj->bmi_shift2_manual = "";
                $obj->bmi_shift3_final = $obj->bmi_shift3 = $obj->bmi_shift3_manual = "";

                $bmi_total_time_original_format = time_bw_original_times($obj->last_out_o, $obj->first_in_o);
                $bmi_total_time = toDecimal(time_bw_original_times($obj->last_out_o, $obj->first_in_o));

                if (in_array($obj->date, $public_holidays)) {
                    if ($bmi_total_time > 8) {
                        $obj->bmi_ph_1 = "8.00";
                        $obj->bmi_ph_2 = number_format($bmi_total_time - 8, 2);
                    } else if ($bmi_total_time) {
                        $obj->bmi_ph_1 = number_format($bmi_total_time, 2);
                    }
                } else if (in_array($obj->day_name, $rest_days)) {
                    if ($bmi_total_time && $obj->last_out) {
                        $bmi_ot_sunday = round_off_ot($bmi_total_time_original_format, $ot_settings, $employee->round_first_hour_only);
                        $bmi_ot_sunday = toDecimal($bmi_ot_sunday);
                        if ($employee->deduct_hour_ot_rd) {
                            $bmi_ot_sunday = $bmi_ot_sunday - 1;
                            if ($bmi_ot_sunday < 0) $bmi_ot_sunday = 0;
                        }
                        $obj->bmi_ot_sunday = round($bmi_ot_sunday, 2);
                    }
                } else if ($is_ot) {
                    $manual_added_overtime = add_time_minus($overtime, $overtime_m);
                    $obj->bmi_ot = $manual_added_overtime ? number_format(toDecimal($manual_added_overtime), 2) : "";
                }

                if ($bmi_total_time > 5 && $days == 1) {
                    $obj->bmi_ta_final = $obj->bmi_ta = number_format($employee->ta_rate, 2);
                }
                if ($obj->bmi_ot >= 2.5 && $days == 1) {
                    $obj->bmi_ma_final = $obj->bmi_ma = number_format($employee->ma_rate, 2);
                }
                if (in_array("CA", $acting_codes) && $days == 1) {
                    $obj->bmi_ca_final = $obj->bmi_ca = number_format($employee->ca_rate, 2);
                }
                if (in_array("SPA", $acting_codes) && $days == 1) {
                    $obj->bmi_spa_final = $obj->bmi_spa = number_format($employee->spa_rate, 2);
                }
                if (in_array("ACA", $acting_codes) && $days == 1) {
                    $obj->bmi_aca_final = $obj->bmi_aca = number_format($employee->aca_rate, 2);
                }
                if (in_array("FL Inc", $acting_codes) && $days == 1) {
                    $obj->bmi_fl_final = $obj->bmi_fl = number_format($employee->fl_rate, 2);
                }
                if (in_array("C/wash", $acting_codes) && $days == 1) {
                    $obj->bmi_cw_final = $obj->bmi_cw = number_format($employee->cw_rate, 2);
                }
                if (in_array("M/ope", $acting_codes) && $days == 1) {
                    $obj->bmi_mo_final = $obj->bmi_mo = number_format($employee->mo_rate, 2);
                }
                if (in_array("Shift1", $acting_codes) && $days == 1) {
                    $obj->bmi_shift1_final = $obj->bmi_shift1 = number_format($employee->shift1_rate, 2);
                }
                if (in_array("Shift2", $acting_codes) && $days == 1) {
                    $obj->bmi_shift2_final = $obj->bmi_shift2 = number_format($employee->shift2_rate, 2);
                }
                if (in_array("Shift3", $acting_codes) && $days == 1) {
                    $obj->bmi_shift3_final = $obj->bmi_shift3 = number_format($employee->shift3_rate, 2);
                }

                $manual_ta = search_from_list($manual_ta_list, $obj->date);
                if ($manual_ta) $obj->bmi_ta_final = $obj->bmi_ta_manual = number_format($manual_ta->value, 2);

                $manual_ma = search_from_list($manual_ma_list, $obj->date);
                if ($manual_ma) $obj->bmi_ma_final = $obj->bmi_ma_manual = number_format($manual_ma->value, 2);

                $manual_ca = search_from_list($manual_ca_list, $obj->date);
                if ($manual_ca) $obj->bmi_ca_final = $obj->bmi_ca_manual = number_format($manual_ca->value, 2);

                $manual_spa = search_from_list($manual_spa_list, $obj->date);
                if ($manual_spa) $obj->bmi_spa_final = $obj->bmi_spa_manual = number_format($manual_spa->value, 2);

                $manual_aca = search_from_list($manual_aca_list, $obj->date);
                if ($manual_aca) $obj->bmi_aca_final = $obj->bmi_aca_manual = number_format($manual_aca->value, 2);

                $manual_fl = search_from_list($manual_fl_list, $obj->date);
                if ($manual_fl) $obj->bmi_fl_final = $obj->bmi_fl_manual = number_format($manual_fl->value, 2);

                $manual_cw = search_from_list($manual_cw_list, $obj->date);
                if ($manual_cw) $obj->bmi_cw_final = $obj->bmi_cw_manual = number_format($manual_cw->value, 2);

                $manual_mo = search_from_list($manual_mo_list, $obj->date);
                if ($manual_mo) $obj->bmi_mo_final = $obj->bmi_mo_manual = number_format($manual_mo->value, 2);

                $manual_shift1 = search_from_list($manual_shift1_list, $obj->date);
                if ($manual_shift1) $obj->bmi_shift1_final = $obj->bmi_shift1_manual = number_format($manual_shift1->value, 2);

                $manual_shift2 = search_from_list($manual_shift2_list, $obj->date);
                if ($manual_shift2) $obj->bmi_shift2_final = $obj->bmi_shift2_manual = number_format($manual_shift2->value, 2);

                $manual_shift3 = search_from_list($manual_shift3_list, $obj->date);
                if ($manual_shift3) $obj->bmi_shift3_final = $obj->bmi_shift3_manual = number_format($manual_shift3->value, 2);

                $total_bmi_ot += ($obj->bmi_ot ? $obj->bmi_ot : 0);
                $total_bmi_ot_sunday += ($obj->bmi_ot_sunday ? $obj->bmi_ot_sunday : 0);
                $total_bmi_ph_1 += ($obj->bmi_ph_1 ? $obj->bmi_ph_1 : 0);
                $total_bmi_ph_2 += ($obj->bmi_ph_2 ? $obj->bmi_ph_2 : 0);
                $total_bmi_ta += ($obj->bmi_ta_final ? $obj->bmi_ta_final : 0);
                $total_bmi_ma += ($obj->bmi_ma_final ? $obj->bmi_ma_final : 0);
                $total_bmi_ca += ($obj->bmi_ca_final ? $obj->bmi_ca_final : 0);
                $total_bmi_spa += ($obj->bmi_spa_final ? $obj->bmi_spa_final : 0);
                $total_bmi_aca += ($obj->bmi_aca_final ? $obj->bmi_aca_final : 0);
                $total_bmi_fl += ($obj->bmi_fl_final ? $obj->bmi_fl_final : 0);
                $total_bmi_cw += ($obj->bmi_cw_final ? $obj->bmi_cw_final : 0);
                $total_bmi_mo += ($obj->bmi_mo_final ? $obj->bmi_mo_final : 0);
                $total_bmi_shift1 += ($obj->bmi_shift1_final ? $obj->bmi_shift1_final : 0);
                $total_bmi_shift2 += ($obj->bmi_shift2_final ? $obj->bmi_shift2_final : 0);
                $total_bmi_shift3 += ($obj->bmi_shift3_final ? $obj->bmi_shift3_final : 0);
            }

            if ($cid == "102") {
                if ($employee->ot_group == "hours" && toDecimal($overtime) > 3) {
                    $overtime = add_time($overtime, "-00:30");
                }
            }

            $overtime = ot_deduction_from_shift_settings($overtime, $shift_check);

            $obj->is_manual_exist = $is_manual_exist;
            $obj->overtime = $overtime;
            $obj->overtime_m = $overtime_m;
            $obj->overtime_type = $overtime_type;
            $obj->is_ot = $is_ot;
            $obj->is_late = $is_late;
            $obj->is_late_break = $is_late_break;
            $obj->is_early_out = $is_early_out;
            $obj->overtime_ph_x2 = "";
            $obj->overtime_ph_x3 = "";
            $obj->x2 = false;
            $obj->x3 = false;

            $daily_overtime = "";

            if (isEligibleForMealAllowance($cid, $obj, $public_holidays, $off_days, $overtime)) {
                $food_allowance_days++;
            }

            if ($is_ot) {
                $daily_overtime = $overtime;
            }

            if ($is_manual_exist) {
                $daily_overtime = add_time_minus($daily_overtime, $overtime_m);
            }

            if (toDecimal($daily_overtime) != 0) {
                $daily_ot_array[$date->format("d/m/Y")][] = [
                    "employee_special_id" => $employee->special_id,
                    "daily_overtime" => toDecimal($daily_overtime),
                    "branch_id" => $employee->branch_id
                ];
            }

            $is_extra_ot = false;
            if ($obj->shift_check) {
                if (is_extra_ot_given($obj->work_hours, $obj->shift_check->extra_ot, $obj->shift_check->extra_ot_worked_hours_more_than, $obj->shift_check->extra_ot_hours)) {
                    $is_extra_ot = true;
                }
            }

            $obj->is_extra_ot = $is_extra_ot;
            $dates[] = $obj;

            if (in_array($obj->date, $public_holidays) || $is_replaced_ph) {
                if (($ph && $ph->rate == "x3") || $is_replaced_ph) {
                    $obj->x3 = true;
                } else {
                    $obj->x2 = true;
                }
            }

            if ($obj->is_ot) {
                if (in_array($obj->date, $public_holidays) || $is_replaced_ph) {
                    if ($obj->x3) {
                        $month_overtime_ph_x3 = add_time_minus($month_overtime_ph_x3, $obj->overtime);
                        $obj->overtime_ph_x3 = $obj->overtime;
                    } else {
                        $month_overtime_ph_x2 = add_time_minus($month_overtime_ph_x2, $obj->overtime);
                        $obj->overtime_ph_x2 = $obj->overtime;
                    }
                    $month_overtime_ph = add_time_minus($month_overtime_ph, $obj->overtime);
                } else if (in_array($obj->day_name, $off_days)) {
                    $month_overtime_off = add_time_minus($month_overtime_off, $obj->overtime);
                } else if (in_array($obj->day_name, $rest_days) || !$obj->shift_check || $obj->shift_check->is_rest_day) {
                    $month_overtime_rd = add_time_minus($month_overtime_rd, $obj->overtime);
                } else {
                    $month_overtime = add_time_minus($month_overtime, $obj->overtime);
                }
            }
            if ($obj->is_manual_exist) {
                if (in_array($obj->date, $public_holidays) || $is_replaced_ph) {
                    if ($obj->x3) {
                        $month_overtime_ph_x3 = add_time_minus($month_overtime_ph_x3, $obj->overtime_m);
                        $obj->overtime_ph_x3 = add_time_minus($obj->overtime_ph_x3, $obj->overtime_m);
                    } else {
                        $month_overtime_ph_x2 = add_time_minus($month_overtime_ph_x2, $obj->overtime_m);
                        $obj->overtime_ph_x2 = add_time_minus($obj->overtime_ph_x2, $obj->overtime_m);
                    }
                    $month_overtime_ph = add_time_minus($month_overtime_ph, $obj->overtime_m);
                } else if (in_array($obj->day_name, $off_days)) {
                    $month_overtime_off = add_time_minus($month_overtime_off, $obj->overtime_m);
                } else if (in_array($obj->day_name, $rest_days) || !$obj->shift_check || $obj->shift_check->is_rest_day) {
                    $month_overtime_rd = add_time_minus($month_overtime_rd, $obj->overtime_m);
                } else {
                    $month_overtime = add_time_minus($month_overtime, $obj->overtime_m);
                }
            }

            $obj->overtime_ph_x2 = $obj->overtime_ph_x2 == "00:00" ? "" : $obj->overtime_ph_x2;
            $obj->overtime_ph_x3 = $obj->overtime_ph_x3 == "00:00" ? "" : $obj->overtime_ph_x3;

            if (!$obj->clockings) {
                $shift_name = "";
                $shift_code = "";
                $cut_off_time = "";
                $shift = search_from_list($shift_list, $obj->date);
                if ($shift) {
                    $shift_name = $shift->name;
                    $shift_code = $shift->code;
                    $cut_off_time = $shift->cut_off_time;
                }

                $remark = search_from_list($remark_list, $obj->date);
                $staff_remark = search_from_list($staff_remark_list, $obj->date);

                $no_data = new stdClass();
                $no_data->day_f = $date_string;
                $no_data->name = $shift_name;
                $no_data->code = $shift_code;
                $no_data->cut_off_time = $cut_off_time;
                $no_data->clock_in = "";
                $no_data->clock_out = "";
                $no_data->reason = "";
                $no_data->remark = "";
                if ($remark) {
                    $no_data->remark = $remark->remark;
                }
                $no_data->staff_remark = "";
                if ($staff_remark) {
                    $no_data->staff_remark = $staff_remark->remark;
                }
                $no_data->total_time = "";
                $obj->clockings[0] = $no_data;
                $obj->shift_name = $shift_name;
                $obj->shift_code = $shift_code;
                $obj->cut_off_time = $cut_off_time;
            }

            if ($replacement) {
                if ($replacement->to === $obj->date) {
                    $obj->clockings[0]->name = "RL";
                    $obj->clockings[0]->code = "RL";
                    $formatted_from_date = convert_date("Y-m-d", "d/m/Y", $replacement->from);
                    $obj->clockings[0]->remark = "Replacement leave from {$formatted_from_date}";
                }
            }

            $total = add_time($total, $obj->total_hours);
            $work = add_time($work, $obj->work_hours);
            $break = add_time($break, $obj->break_hours);
            if ($obj->is_late) {
                $late = add_time($late, $obj->late_hours);
            }
            if ($obj->is_late_break) {
                $break_late = add_time($break_late, $obj->break_late_hours);
            }
            $total_short = add_time($total_short, $obj->short_hours);
            if ($obj->is_early_out) {
                $total_early = add_time($total_early, $obj->early_out);
                if ($obj->early_out != "") {
                    $total_early_count++;
                    $obj->merit_is_early_out = true;
                }
            }
            $total_days = add_days($total_days, $obj->days);
            $late_result = get_lateness_time($total_late, $obj->late_hours, $obj->break_late_hours, $obj->early_out, $obj->short_hours, $inc_late_in, $inc_late_break, $inc_early_out && $is_early_out, $inc_short_hours, $late_count);
            $total_late = $late_result[0];
            $late_count = $late_result[1];
            $today_late = $late_result[2];
            if ($inc_late_in && check_if_time_exist($obj->late_hours)) {
                $obj->merit_is_late = true;
                $total_late_only_count++;
            }

            if (toDecimal($today_late) != 0) {
                $total_late_day = round(beautiful_time_to_minutes($today_late) / ($company_working_hours_decimal * 60), 3);
                $daily_late_array[$date->format("d/m/Y")][] = [
                    "employee_special_id" => $employee->special_id,
                    "daily_late" => $total_late_day,
                    "branch_id" => $employee->branch_id
                ];
            }
            if ($custom_in_outs) {
                $obj->clockings = [$obj->clockings[0]];
            }
            $obj->employee_shift_hours = "";
            if ($result && $last_out != "" && !$is_ph_day && !$is_rest_day) {
                $obj->employee_shift_hours = add_time($obj->full_shift_hours, "-" . $today_late);
            }
            $shift_hours_total = add_time($shift_hours_total, $obj->employee_shift_hours);

            if ($cid == 146) {
                $obj->meal_days = 0;
                if (toDecimal($obj->work_hours) >= toDecimal($employee->min_worked_hours_meal) && $employee->department == "Worker") {
                    $obj->meal_days = 1;
                }
            }
            $total_meal_days += $obj->meal_days ?? 0;
        }

        if ($cid == 196) {
            $paid_leaves_array = $jl01_paid_leaves;
        }

        $lateness_result = get_lateness_time("00:00", $late, $break_late, $total_early, $total_short, $inc_late_in, $inc_late_break, $inc_early_out && $is_early_out, $inc_short_hours, 0);
        $lateness_time = void_late_minutes($lateness_result[0], $void_minutes);

        $monthly_working_hours = "00:00";

        if ($ot_type_data->ot_type === "weekly_hours") {
            $work_decimal = toDecimal($work);
            $ot_weekly_hours_decimal = $ot_type_data->ot_weekly_hours;

            if ($ot_weekly_hours_decimal > $work_decimal) {
                $month_overtime = "00:00";
                $month_overtime_deducted = "00:00";

                foreach ($dates as $d) {
                    $is_manual_exist = false;
                    $manual_ot = search_from_list($manual_ot_list, $d->date);
                    $shift_check = search_from_list($shift_list, $d->date);
                    if ($manual_ot) {
                        $overtime_m = $manual_ot->overtime;
                        $overtime_type = $manual_ot->type;
                        $is_manual_exist = true;
                        if ($overtime_type == "-") {
                            $overtime_m = "-" . $overtime_m;
                        }
                    }
                    if (!in_array($d->date, $public_holidays) && !in_array($d->day_name, $rest_days) && $shift_check && $is_replaced_ph && !$shift_check->is_rest_day) {
                        if ($is_manual_exist) {
                            $month_overtime = add_time_minus($month_overtime, $overtime_m);
                        }
                        if ($deduct_from_ot) {
                            $after_deduction = deduct_from_ot($month_overtime, $lateness_time, $deduction_date, $last_day);
                            $month_overtime_deducted = $after_deduction[0];
                        }
                        $d->overtime = "";
                    }
                }
            }
        } else if ($ot_type_data->ot_type === "monthly_ot") {
            $days_in_month = (int)date('t', strtotime($obj->date));
            $no_of_working_days = $ci->db->select("id, days")->from("monthly_working_days")
                ->where('month', $date->format('m'))
                ->where('year', $date->format('Y'))
                ->where('company_id', $cid)
                ->where('branch_id', $employee->branch_id)->get()->row();

            $work_decimal = toDecimal($work);
            $off_days_m = 0;
            if (is_null($no_of_working_days)) {
                $no_of_working_days = $days_in_month - 4;
                if ($total_off_days > 4) $off_days_m = $total_off_days - 4;
            } else {
                $no_of_working_days = (int)$no_of_working_days->days;
                $remaining_days = $days_in_month - $no_of_working_days;
                if ($total_off_days > $remaining_days) $off_days_m = $total_off_days - $remaining_days;
            }

            $monthly_working_hours_decimal = $no_of_working_days * $company_working_hours_decimal;
            $monthly_working_hours = decimal_to_time($monthly_working_hours_decimal);
            $off_days_time = multiply_time_by_scalar($company_working_hours, $off_days_m);
            $month_overtime = add_time($month_overtime, "-{$off_days_time}");
            $absent_days_time = multiply_time_by_scalar($company_working_hours, $absent_days);
            $month_overtime = add_time($month_overtime, "-{$absent_days_time}");
            $unpaid_leaves_time = multiply_time_by_scalar($company_working_hours, $full_unpaid_leaves);
            $month_overtime = add_time($month_overtime, "-{$unpaid_leaves_time}");
            if ($monthly_working_hours_decimal > $work_decimal) {
                $month_overtime = "00:00";
                $month_overtime_deducted = "00:00";

                foreach ($dates as $d) {
                    $is_manual_exist = false;
                    $manual_ot = search_from_list($manual_ot_list, $d->date);
                    $shift_check = search_from_list($shift_list, $d->date);
                    if ($manual_ot) {
                        $overtime_m = $manual_ot->overtime;
                        $overtime_type = $manual_ot->type;
                        $is_manual_exist = true;
                        if ($overtime_type == "-") {
                            $overtime_m = "-" . $overtime_m;
                        }
                    }

                    if (!in_array($d->date, $public_holidays) && !in_array($d->day_name, $rest_days) && $shift_check && $is_replaced_ph && !$shift_check->is_rest_day) {
                        if ($is_manual_exist) {
                            $month_overtime = add_time_minus($month_overtime, $overtime_m);
                        }
                        if ($deduct_from_ot) {
                            $after_deduction = deduct_from_ot($month_overtime, $lateness_time, $deduction_date, $last_day);
                            $month_overtime_deducted = $after_deduction[0];
                        }
                        $d->overtime = "";
                    }
                }
            }
        }

        $month_overtime_deducted = $month_overtime;
        $lateness_time_deducted = $lateness_time;

        if ($deduct_from_ot) {
            $after_deduction = deduct_from_ot($month_overtime, $lateness_time, $deduction_date, $last_day);
            $month_overtime_deducted = $after_deduction[0];
            $lateness_time_deducted = $after_deduction[1];
        }

        if (in_array($cid, companies_allowed_for_att_all())) {
            if ($employee->is_att_all == 1) {
                if ($absent_days > 0 || $allowance_leaves > 2) {
                    $employee->att_all_amount = 0;
                } else if ($allowance_leaves == 1) {
                    $employee->att_all_amount = 75;
                } else if ($allowance_leaves == 2) {
                    $employee->att_all_amount = 50;
                }
            }
        }

        $yrdata = strtotime($first_day);
        $month_name = date('F', $yrdata);

        $data["current_user"] = 'Admin API';
        $data["employee"] = $employee;
        if ($custom_in_outs) $data['custom_in_outs'] = true;
        if ($tsf_custom_summary) $data['tsf_custom_summary'] = true;

        $data['lateness_time'] = $lateness_time;
        $data['lateness_time_deducted'] = $lateness_time_deducted;
        $data['late'] = $late;
        $data['late_count'] = $late_count;

        $data['total'] = $total;
        $data['work'] = $work;
        $data['work_hours']= $work;
        $data['shift_hours_total'] = remove_seconds($shift_hours_total);
        $data['break'] = $break;
        $data['break_late'] = $break_late;
        $data['total_days'] = $total_days;
        $data['total_meal_days'] = $total_meal_days;
        $data['total_short'] = $total_short;
        $data['total_early'] = $total_early;
        $data['total_early_count'] = $total_early_count;
        $data['total_trip_a'] = $total_trip_a;
        $data['total_trip_b'] = $total_trip_b;
        $data['total_late_only_count'] = $total_late_only_count;

        $data['month_overtime'] = $month_overtime;
        $data['month_overtime_deducted'] = $month_overtime_deducted;
        $data['month_overtime_ph'] = $month_overtime_ph;
        $data['month_overtime_ph_x2'] = $month_overtime_ph_x2;
        $data['month_overtime_ph_x3'] = $month_overtime_ph_x3;
        $data['month_overtime_rd'] = $month_overtime_rd;
        $data['month_overtime_off'] = $month_overtime_off;
        $data['monthly_working_hours'] = $monthly_working_hours;


        $data['lateness_time_deducted'] = toDecimal($lateness_time_deducted);
        $data['month_overtime_deducted'] = toDecimal($month_overtime_deducted);
        $data['month_overtime_ph'] = toDecimal($month_overtime_ph);
        $data['month_overtime_ph_x2'] = toDecimal($month_overtime_ph_x2);
        $data['month_overtime_ph_x3'] = toDecimal($month_overtime_ph_x3);
        $data['month_overtime_rd'] = toDecimal($month_overtime_rd);
        $data['month_overtime_off'] = toDecimal($month_overtime_off);
        $lateness_time_deducted_decimal = toDecimal($lateness_time_deducted);
        $company_working_hours_decimal = toDecimal($company_working_hours);
        $data['late_days'] = calculate_late_days($lateness_time_deducted_decimal, $company_working_hours_decimal);


        $data['working_days'] = $working_days;
        $data['worked_days'] = $worked_days;
        $data['worked_rest_days'] = $worked_rest_days;
        $data['worked_off_days'] = $worked_off_days;
        $data['worked_holidays'] = $worked_holidays;
        $data['total_holidays'] = $total_holidays;
        $data['absent_days'] = $absent_days;
        $data['paid_leaves'] = $paid_leaves;
        $data['unpaid_leaves'] = $unpaid_leaves;

        $data['worked_holidays_array'] = $worked_holidays_array;
        $data['worked_rest_days_array'] = $worked_rest_days_array;
        $data['worked_off_days_array'] = $worked_off_days_array;
        $data['unpaid_leaves_absent_days'] = $unpaid_leaves_absent_days;

        $data['dates'] = $dates;

        $data["rest_days"] = $rest_days;
        $data["off_days"] = $off_days;
        $data['public_holidays'] = $public_holidays;

        $data['month_name'] = $month_name;
        $data['total_shift_hours'] = $total_shift_hours;

        $data["total_half_day_paid"] = $total_half_day_paid;
        $data["total_full_day_paid"] = $total_full_day_paid;
        $data["total_half_day_unpaid"] = $total_half_day_unpaid;
        $data["total_medical_leaves"] = $total_medical_leaves;
        $data["total_break_late"] = $total_break_late;
        $data["total_missing_in_out"] = $total_missing_in_out;
        $data["total_absent_unpaid"] = $total_absent_unpaid;
        $data["total_early_late"] = $total_early_late;
        $data["total_rest_days_used"] = $total_rest_days_used;

        if ($cid == 66) {
            $data["total_bmi_ot"] = $total_bmi_ot;
            $data["total_bmi_ot_sunday"] = $total_bmi_ot_sunday;
            $data["total_bmi_ph_1"] = $total_bmi_ph_1;
            $data["total_bmi_ph_2"] = $total_bmi_ph_2;
            $data["total_bmi_ta"] = $total_bmi_ta;
            $data["total_bmi_ma"] = $total_bmi_ma;
            $data["total_bmi_ca"] = $total_bmi_ca;
            $data["total_bmi_spa"] = $total_bmi_spa;
            $data["total_bmi_aca"] = $total_bmi_aca;
            $data["total_bmi_fl"] = $total_bmi_fl;
            $data["total_bmi_cw"] = $total_bmi_cw;
            $data["total_bmi_mo"] = $total_bmi_mo;
            $data["total_bmi_shift1"] = $total_bmi_shift1;
            $data["total_bmi_shift2"] = $total_bmi_shift2;
            $data["total_bmi_shift3"] = $total_bmi_shift3;
            $data["bmi_attendance_allowance"] = $bmi_attendance_allowance;
        }

        if ($cid == 215) {
            $data["gbr_attendance_allowance"] = $gbr_attendance_allowance;
            $data["gbr_night_shifts"] = $gbr_night_shifts;
        }
        if (in_array($cid, companies_allowed_for_shift_allowance())) {
            $data["monthly_dsa_count"] = $monthly_dsa_count;
            $data["monthly_nsa_count"] = $monthly_nsa_count;
        }
        if ($cid == 152) {
            $data['lsk_non_worked_days'] = $lsk_non_worked_days;
        }

        if ($cid == 229) {
            $data['ln01_waived_days'] = $ln01_waived_days;
            $data['ln01_attendance_allowance_days'] = $ln01_attendance_allowance_days;
        }

        if ($cid == 206 || in_array($cid, companies_allowed_for_meal_allowance())) {
            $data["food_allowance_days"] = $food_allowance_days;
        }

        return $data;
    }
}