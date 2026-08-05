<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'controllers/agency/Admin_Controller.php';

class Company extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        // ===== CORS HEADERS =====
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Allow-Credentials: false");
        header("Content-Type: application/json; charset=UTF-8");

        // Handle preflight OPTIONS request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        $this->output->set_content_type('application/json');
        // $this->require_admin();
    }


    // ==================================================================
    // 2. COMPANIES MANAGEMENT API
    // ==================================================================

    /**
     * Get all companies with optional filters
     */
    public function index()
    {

        try {
            // Get query parameters with defaults
            $package_id = $this->input->get('package_id');
            $reseller_id = $this->input->get('reseller_id');
            $admin_id = $this->input->get('admin_id');
            $status = $this->input->get('status');
            $search = $this->input->get('search');
            $limit = $this->input->get('limit') ? (int)$this->input->get('limit') : 50;
            $offset = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;
            $with_package = $this->input->get('with_package') !== 'false';
            $with_reseller = $this->input->get('with_reseller') === 'true';
            $with_stats = $this->input->get('with_stats') === 'true';
            $sort_by = $this->input->get('sort_by') ? $this->input->get('sort_by') : 'daysLeft';
            $sort_order = $this->input->get('sort_order') ? strtoupper($this->input->get('sort_order')) : 'ASC';

            // Validate sort_order
            if (!in_array($sort_order, ['ASC', 'DESC'])) {
                $sort_order = 'ASC';
            }

            // Start building query
            $this->db->select('c.*');

            // Count active staff: employee_status='active', NOT deleted, role.exclude_from_system = 'no'
            $this->db->select("(
                SELECT COUNT(e.id)
                FROM employees e
                JOIN roles r ON r.id = e.role_id
                WHERE e.company_id = c.id
                AND e.employee_status = 'active'
                AND e.deleted_at IS NULL
                AND r.exclude_from_system = 'no'
            ) AS active_staff", false);

            // Count total staff: all non-deleted employees
            $this->db->select("(
                SELECT COUNT(e.id)
                FROM employees e
                WHERE e.company_id = c.id
                AND e.deleted_at IS NULL
            ) AS total_staff", false);

            // Calculate expiry_date per provided logic
            $this->db->select("CASE\n    WHEN c.contract_months IS NULL OR c.contract_months <= 0 OR TRIM(c.contract_months) = '' THEN NULL\n    WHEN COALESCE(NULLIF(TRIM(c.last_renewal_date), ''), NULLIF(TRIM(c.start_date), '')) IS NULL THEN NULL\n    WHEN COALESCE(NULLIF(TRIM(c.last_renewal_date), '0000-00-00'), NULLIF(TRIM(c.start_date), '0000-00-00')) IS NULL THEN NULL\n    ELSE LAST_DAY(\n        DATE_ADD(\n            COALESCE(\n                NULLIF(TRIM(c.last_renewal_date), '0000-00-00'),\n                NULLIF(TRIM(c.last_renewal_date), ''),\n                NULLIF(TRIM(c.start_date), '0000-00-00'),\n                NULLIF(TRIM(c.start_date), '')\n            ),\n            INTERVAL c.contract_months - 1 MONTH\n        )\n    )\nEND AS expiry_date", false);

            // Calculate days_left per provided logic
            $this->db->select("CASE\n    WHEN c.status = 'terminated' THEN NULL\n    WHEN c.contract_months IS NULL OR c.contract_months <= 0 OR TRIM(c.contract_months) = '' THEN NULL\n    WHEN COALESCE(NULLIF(TRIM(c.last_renewal_date), ''), NULLIF(TRIM(c.start_date), '')) IS NULL THEN NULL\n    WHEN COALESCE(NULLIF(TRIM(c.last_renewal_date), '0000-00-00'), NULLIF(TRIM(c.start_date), '0000-00-00')) IS NULL THEN NULL\n    ELSE DATEDIFF(\n        LAST_DAY(\n            DATE_ADD(\n                COALESCE(\n                    NULLIF(TRIM(c.last_renewal_date), '0000-00-00'),\n                    NULLIF(TRIM(c.last_renewal_date), ''),\n                    NULLIF(TRIM(c.start_date), '0000-00-00'),\n                    NULLIF(TRIM(c.start_date), '')\n                ),\n                INTERVAL c.contract_months - 1 MONTH\n            )\n        ),\n        CURDATE()\n    )\nEND AS days_left", false);

            if ($with_package) {
                $this->db->select('p.id as package_id, p.name as package_name, p.max_outlets');
            }

            if ($with_reseller) {
                $this->db->select('r.id as reseller_id, r.name as reseller_name');
            }

            $this->db->from('companies c');

            // Always join packages table (needed for max_active_staff calculation)
            $this->db->join('packages p', 'p.id = c.package', 'left');

            // Calculate max_active_staff (package limit + additional staff) after join
            $this->db->select("(p.max_active_staff + COALESCE(c.additional_staff, 0)) AS max_active_staff", false);

            if ($with_reseller) {
                $this->db->join('resellers r', 'r.id = c.reseller_id', 'left');
            }

            // Apply filters
            if (!empty($package_id)) {
                $this->db->where('c.package', $package_id);
            }

            if (!empty($reseller_id)) {
                $this->db->where('c.reseller_id', $reseller_id);
            }
            if (!empty($admin_id)) {
                $this->db->where('c.admin_id', $admin_id);
            }

            if (!empty($status)) {
                $this->db->where('c.status', $status);
            }

            // Apply search
            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('c.name', $search);
                $this->db->or_like('c.organization_id', $search);
                $this->db->or_like('c.phone', $search);
                $this->db->or_like('r.name', $search);
                $this->db->or_like('p.name', $search);
                $this->db->or_like('c.address', $search);
                $this->db->or_like('c.email', $search);
                $this->db->group_end();
            }

            // Clone the query builder for counting total records
            $count_query = clone $this->db;
            $total = $count_query->count_all_results();

            // Apply ordering based on sort_by parameter
            if ($sort_by === 'daysLeft') {
                if ($sort_order === 'ASC') {
                    // ASC: Expired first (days_left <= 0) → Valid (days_left > 0, min to max) → Empty (NULL)
                    $this->db->order_by("CASE
                        WHEN days_left <= 0 THEN 0
                        WHEN days_left > 0 THEN 1
                        ELSE 2
                    END", 'ASC', false);
                    $this->db->order_by('days_left', 'ASC', false);
                    $this->db->order_by('c.id', 'ASC');
                } else {
                    // DESC: Valid (days_left > 0, max to min) → Expired (days_left <= 0) → Empty (NULL)
                    $this->db->order_by("CASE
                        WHEN days_left > 0 THEN 0
                        WHEN days_left <= 0 THEN 1
                        ELSE 2
                    END", 'ASC', false);
                    $this->db->order_by('days_left', 'DESC', false);
                    $this->db->order_by('c.id', 'DESC');
                }
            } else {
                // Default sorting if no valid sort_by specified
                $this->db->order_by("CASE
                    WHEN days_left <= 0 THEN 0
                    WHEN days_left > 0 THEN 1
                    ELSE 2
                END", 'ASC', false);
                $this->db->order_by('days_left', 'ASC', false);
                $this->db->order_by('c.id', 'ASC');
            }
            $this->db->limit($limit, $offset);

            // Execute query with error handling
            $query = $this->db->get();

            if (!$query) {
                throw new Exception('Database query failed: ' . $this->db->error()['message']);
            }

            $companies = $query->result();

            // Add additional stats if requested
            if ($with_stats && !empty($companies)) {
                foreach ($companies as $company) {
                    $company->total_users = $this->getCompanyUsersCount($company->id);
                    $company->active_users = $this->getActiveUsersCount($company->id);
                    $company->storage_used = $this->getCompanyStorageUsed($company->id);
                }
            }

            $this->json([
                'success' => true,
                'data' => $companies,
                'pagination' => [
                    'total' => (int)$total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => ($offset + count($companies)) < $total
                ]
            ]);
        } catch (Exception $e) {
            log_message('error', 'Companies API Error: ' . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to fetch companies',
                'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    /**
     * Get all companies with optional filters
     */
    public function download_excel()
    {

        try {
            // Get query parameters with defaults
            $package_id = $this->input->get('package_id');
            $reseller_id = $this->input->get('reseller_id');
            $admin_id = $this->input->get('admin_id');
            $status = $this->input->get('status');
            $search = $this->input->get('search');
            // No default cap for export — only apply a limit if the caller explicitly asks for one
            $limit = $this->input->get('limit') ? (int)$this->input->get('limit') : null;
            $offset = $this->input->get('offset') ? (int)$this->input->get('offset') : 0;
            $with_package = $this->input->get('with_package') !== 'false';
            $with_reseller = $this->input->get('with_reseller') === 'true';
            $with_stats = $this->input->get('with_stats') === 'true';
            $sort_by = $this->input->get('sort_by') ? $this->input->get('sort_by') : 'id';
            $sort_order = $this->input->get('sort_order') ? strtoupper($this->input->get('sort_order')) : 'ASC';

            // Validate sort_order
            if (!in_array($sort_order, ['ASC', 'DESC'])) {
                $sort_order = 'ASC';
            }

            // Start building query
            $this->db->select('c.*');

            // Count active staff: employee_status='active', NOT deleted, role.exclude_from_system = 'no'
            $this->db->select("(
            SELECT COUNT(e.id)
            FROM employees e
            JOIN roles r ON r.id = e.role_id
            WHERE e.company_id = c.id
            AND e.employee_status = 'active'
            AND e.deleted_at IS NULL
            AND r.exclude_from_system = 'no'
        ) AS active_staff", false);

            // Count total staff: all non-deleted employees
            $this->db->select("(
            SELECT COUNT(e.id)
            FROM employees e
            WHERE e.company_id = c.id
            AND e.deleted_at IS NULL
        ) AS total_staff", false);

            // Calculate expiry_date per provided logic
            $this->db->select("CASE\n    WHEN c.contract_months IS NULL OR c.contract_months <= 0 OR TRIM(c.contract_months) = '' THEN NULL\n    WHEN COALESCE(NULLIF(TRIM(c.last_renewal_date), ''), NULLIF(TRIM(c.start_date), '')) IS NULL THEN NULL\n    WHEN COALESCE(NULLIF(TRIM(c.last_renewal_date), '0000-00-00'), NULLIF(TRIM(c.start_date), '0000-00-00')) IS NULL THEN NULL\n    ELSE LAST_DAY(\n        DATE_ADD(\n            COALESCE(\n                NULLIF(TRIM(c.last_renewal_date), '0000-00-00'),\n                NULLIF(TRIM(c.last_renewal_date), ''),\n                NULLIF(TRIM(c.start_date), '0000-00-00'),\n                NULLIF(TRIM(c.start_date), '')\n            ),\n            INTERVAL c.contract_months - 1 MONTH\n        )\n    )\nEND AS expiry_date", false);

            // Calculate days_left per provided logic
            $this->db->select("CASE\n    WHEN c.status = 'terminated' THEN NULL\n    WHEN c.contract_months IS NULL OR c.contract_months <= 0 OR TRIM(c.contract_months) = '' THEN NULL\n    WHEN COALESCE(NULLIF(TRIM(c.last_renewal_date), ''), NULLIF(TRIM(c.start_date), '')) IS NULL THEN NULL\n    WHEN COALESCE(NULLIF(TRIM(c.last_renewal_date), '0000-00-00'), NULLIF(TRIM(c.start_date), '0000-00-00')) IS NULL THEN NULL\n    ELSE DATEDIFF(\n        LAST_DAY(\n            DATE_ADD(\n                COALESCE(\n                    NULLIF(TRIM(c.last_renewal_date), '0000-00-00'),\n                    NULLIF(TRIM(c.last_renewal_date), ''),\n                    NULLIF(TRIM(c.start_date), '0000-00-00'),\n                    NULLIF(TRIM(c.start_date), '')\n                ),\n                INTERVAL c.contract_months - 1 MONTH\n            )\n        ),\n        CURDATE()\n    )\nEND AS days_left", false);

            if ($with_package) {
                $this->db->select('p.id as package_id, p.name as package_name, p.max_outlets');
            }

            if ($with_reseller) {
                $this->db->select('r.id as reseller_id, r.name as reseller_name');
            }

            $this->db->from('companies c');

            // Always join packages table (needed for max_active_staff calculation)
            $this->db->join('packages p', 'p.id = c.package', 'left');

            // Calculate max_active_staff (package limit + additional staff) after join
            $this->db->select("(p.max_active_staff + COALESCE(c.additional_staff, 0)) AS max_active_staff", false);

            if ($with_reseller) {
                $this->db->join('resellers r', 'r.id = c.reseller_id', 'left');
            }

            // Apply filters
            if (!empty($package_id)) {
                $this->db->where('c.package', $package_id);
            }

            if (!empty($reseller_id)) {
                $this->db->where('c.reseller_id', $reseller_id);
            }
            if (!empty($admin_id)) {
                $this->db->where('c.admin_id', $admin_id);
            }

            if (!empty($status)) {
                $this->db->where('c.status', $status);
            }

            // Apply search
            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('c.name', $search);
                $this->db->or_like('c.organization_id', $search);
                $this->db->or_like('c.phone', $search);
                $this->db->or_like('r.name', $search);
                $this->db->or_like('p.name', $search);
                $this->db->or_like('c.address', $search);
                $this->db->or_like('c.email', $search);
                $this->db->group_end();
            }

            // Sort by primary key (PK) — replaces the previous days_left-based ordering
            $this->db->order_by('c.id', $sort_order);

            // Only cap results if the caller explicitly passed a limit — export defaults to ALL matching rows
            if (!empty($limit)) {
                $this->db->limit($limit, $offset);
            }

            // Execute query with error handling
            $query = $this->db->get();

            if (!$query) {
                throw new Exception('Database query failed: ' . $this->db->error()['message']);
            }

            $companies = $query->result();

            // Add additional stats if requested
            if ($with_stats && !empty($companies)) {
                foreach ($companies as $company) {
                    $company->total_users = $this->getCompanyUsersCount($company->id);
                    $company->active_users = $this->getActiveUsersCount($company->id);
                    $company->storage_used = $this->getCompanyStorageUsed($company->id);
                }
            }

            /////   Excel Generation
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
            $object->getDefaultStyle()->getFont()->setName('Calibri')->setSize(9); // smaller base font

            $sheet = $object->getActiveSheet();
            $sheet->setTitle('Companies');
            $sheet->getTabColor()->setRGB('1F6F50');

            // ---- Pre-compute all totals needed for the top summary + right-side table ----
            $totalCompanies = count($companies);
            $totalActiveStaff = 0;
            $totalStaff = 0;
            $grandRecurringRm = 0;
            $grandRecurringDealer = 0;

            // status => ['count'=>.., 'recurring_rm'=>.., 'recurring_dealer'=>..]
            $statusTotals = [];

            foreach ($companies as $company) {
                $totalActiveStaff += isset($company->active_staff) ? (int)$company->active_staff : 0;
                $totalStaff += isset($company->total_staff) ? (int)$company->total_staff : 0;

                $rm = isset($company->recurring_rm) ? (float)$company->recurring_rm : 0;
                $dealer = isset($company->recurring_dealer) ? (float)$company->recurring_dealer : 0;

                $grandRecurringRm += $rm;
                $grandRecurringDealer += $dealer;

                $statusKey = (isset($company->status) && $company->status !== '') ? $company->status : 'unknown';

                if (!isset($statusTotals[$statusKey])) {
                    $statusTotals[$statusKey] = [
                        'count' => 0,
                        'recurring_rm' => 0,
                        'recurring_dealer' => 0,
                    ];
                }

                $statusTotals[$statusKey]['count']++;
                $statusTotals[$statusKey]['recurring_rm'] += $rm;
                $statusTotals[$statusKey]['recurring_dealer'] += $dealer;
            }

            // Map of column header label => property name expected on each $company row
            $headers = [
                'Company Name'     => 'name',
                'Recurring RM'     => 'recurring_rm',
                'Status'           => 'status',
                'Recurring Dealer' => 'recurring_dealer',
            ];

            // Staff columns
            $headers['Active Staff']     = 'active_staff';
            $headers['Total Staff']      = 'total_staff';
            $headers['Max Active Staff'] = 'max_active_staff';

            $lastColIndex = count($headers) - 1;
            $lastColLetter = PHPExcel_Cell::stringFromColumnIndex($lastColIndex);

            // ==========================================================
            // ---- Row layout ----
            // Rows 1-3   : logo
            // Row 4      : blank
            // Row 5      : "SUMMARY" title
            // Rows 6..N  : summary label/value pairs (dynamic count of statuses)
            // Row N+1    : blank spacer
            // Row N+2    : main table header row (also right-table header row)
            // ==========================================================



            // ---- Top summary block ----
            $summaryTitleRow = 5;
            $sheet->setCellValue('A' . $summaryTitleRow, 'SUMMARY');
            $sheet->getStyle('A' . $summaryTitleRow)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A' . $summaryTitleRow)->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle('A' . $summaryTitleRow)->getFont()->getColor()->setRGB('FFFFFF');
            $sheet->mergeCells('A' . $summaryTitleRow . ':D' . $summaryTitleRow);

            $summaryRow = $summaryTitleRow + 1;

            $summaryLines = [];
            $summaryLines[] = ['Total Companies', $totalCompanies];
            $summaryLines[] = ['Total Active Staff', $totalActiveStaff];
            $summaryLines[] = ['Total Staff', $totalStaff];

            foreach ($statusTotals as $statusKey => $totals) {
                $summaryLines[] = [ucfirst($statusKey) . ' Companies', $totals['count']];
            }

            $summaryLines[] = ['Grand Total Recurring RM', number_format($grandRecurringRm)];
            $summaryLines[] = ['Grand Total Recurring Dealer', number_format($grandRecurringDealer)];

            foreach ($summaryLines as $line) {
                $sheet->setCellValue('A' . $summaryRow, $line[0]);
                $sheet->setCellValue('B' . $summaryRow, $line[1]);
                $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true)->setSize(9);
                $sheet->getStyle('B' . $summaryRow)->getFont()->setSize(9);
                $summaryRow++;
            }

            // Box the summary section
            $summaryBoxEndRow = $summaryRow - 1;
            $sheet->getStyle('A' . $summaryTitleRow . ':B' . $summaryBoxEndRow)->getBorders()->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
                ->getColor()->setRGB('BFBFBF');

            // ---- Blank spacer row before the main table ----
            $headerRowNum = $summaryBoxEndRow + 2;

            // ==========================================================
            // ---- 1. Main company-wise table (left) ----
            // ==========================================================
            $col = 0;
            foreach ($headers as $label => $field) {
                $sheet->setCellValueByColumnAndRow($col, $headerRowNum, $label);
                $col++;
            }

            $headerRange = 'A' . $headerRowNum . ':' . $lastColLetter . $headerRowNum;
            $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle($headerRange)->getFont()->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle($headerRange)->getBorders()->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
                ->getColor()->setRGB('B0B0B0');
            $sheet->getRowDimension($headerRowNum)->setRowHeight(20);

            $sheet->freezePane('A' . ($headerRowNum + 1));
            $sheet->setAutoFilter($headerRange);

            // Write data rows
            $rowNum = $headerRowNum + 1;
            foreach ($companies as $company) {
                $col = 0;
                foreach ($headers as $field) {
                    $value = isset($company->$field) ? $company->$field : '';
                    $sheet->setCellValueByColumnAndRow($col, $rowNum, $value);
                    $col++;
                }
                $rowNum++;
            }

            $lastDataRow = $rowNum - 1;
            $firstDataRow = $headerRowNum + 1;

            if ($lastDataRow >= $firstDataRow) {
                $dataRange = 'A' . $firstDataRow . ':' . $lastColLetter . $lastDataRow;
                $sheet->getStyle($dataRange)->getFont()->setSize(8); // smaller data font
                $sheet->getStyle($dataRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
                    ->getColor()->setRGB('D9D9D9');

                for ($r = $firstDataRow; $r <= $lastDataRow; $r++) {
                    if (($r - $firstDataRow) % 2 === 1) {
                        $rowRange = 'A' . $r . ':' . $lastColLetter . $r;
                        $sheet->getStyle($rowRange)->getFill()
                            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F2F7F2');
                    }
                }
            }

            foreach (range('A', $lastColLetter) as $columnLetter) {
                $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            }

            // ==========================================================
            // ---- 2/3. Right-side "Recurring Summary" table ----
            // Columns: Group | Count | Recurring RM | Recurring Dealer
            // Rows: All (grand total), then one row per status
            // ==========================================================
            $rightStartColIndex = $lastColIndex + 2; // leave one blank column as a gap
            $rightHeaders = ['Group', 'Count', 'Recurring RM', 'Recurring Dealer'];
            $rightLastColIndex = $rightStartColIndex + count($rightHeaders) - 1;
            $rightStartColLetter = PHPExcel_Cell::stringFromColumnIndex($rightStartColIndex);
            $rightLastColLetter = PHPExcel_Cell::stringFromColumnIndex($rightLastColIndex);

            // Title row above the right table
            $sheet->setCellValue($rightStartColLetter . ($headerRowNum - 1), 'RECURRING SUMMARY (ALL & STATUS-WISE)');
            $sheet->getStyle($rightStartColLetter . ($headerRowNum - 1))->getFont()->setBold(true)->setSize(10);
            $sheet->mergeCells($rightStartColLetter . ($headerRowNum - 1) . ':' . $rightLastColLetter . ($headerRowNum - 1));

            // Right table header row (aligned with main table header row)
            $c = $rightStartColIndex;
            foreach ($rightHeaders as $label) {
                $sheet->setCellValueByColumnAndRow($c, $headerRowNum, $label);
                $c++;
            }
            $rightHeaderRange = $rightStartColLetter . $headerRowNum . ':' . $rightLastColLetter . $headerRowNum;
            $sheet->getStyle($rightHeaderRange)->getFont()->setBold(true)->setSize(9);
            $sheet->getStyle($rightHeaderRange)->getFont()->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($rightHeaderRange)->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()->setRGB('2E7D32');
            $sheet->getStyle($rightHeaderRange)->getBorders()->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
                ->getColor()->setRGB('B0B0B0');

            // Row 1 of right table: grand total across ALL companies (all statuses combined)
            $rightRow = $headerRowNum + 1;
            $sheet->setCellValueByColumnAndRow($rightStartColIndex, $rightRow, 'All');
            $sheet->setCellValueByColumnAndRow($rightStartColIndex + 1, $rightRow, $totalCompanies);
            $sheet->setCellValueByColumnAndRow($rightStartColIndex + 2, $rightRow, number_format($grandRecurringRm));
            $sheet->setCellValueByColumnAndRow($rightStartColIndex + 3, $rightRow, number_format($grandRecurringDealer));
            $sheet->getStyle($rightStartColLetter . $rightRow . ':' . $rightLastColLetter . $rightRow)->getFont()->setBold(true)->setSize(9);
            $rightRow++;

            // One row per status (separate breakdown)
            foreach ($statusTotals as $statusKey => $totals) {
                $sheet->setCellValueByColumnAndRow($rightStartColIndex, $rightRow, ucfirst($statusKey));
                $sheet->setCellValueByColumnAndRow($rightStartColIndex + 1, $rightRow, $totals['count']);
                $sheet->setCellValueByColumnAndRow($rightStartColIndex + 2, $rightRow, number_format($totals['recurring_rm']));
                $sheet->setCellValueByColumnAndRow($rightStartColIndex + 3, $rightRow, number_format($totals['recurring_dealer']));
                $sheet->getStyle($rightStartColLetter . $rightRow . ':' . $rightLastColLetter . $rightRow)->getFont()->setSize(9);
                $rightRow++;
            }

            $rightLastRow = $rightRow - 1;

            // Style the right-table data rows (borders + light amber banding)
            $rightDataRange = $rightStartColLetter . ($headerRowNum + 1) . ':' . $rightLastColLetter . $rightLastRow;
            $sheet->getStyle($rightDataRange)->getBorders()->getAllBorders()
                ->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN)
                ->getColor()->setRGB('BFBFBF');
            $sheet->getStyle($rightDataRange)->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFF2CC');

            foreach (range($rightStartColLetter, $rightLastColLetter) as $columnLetter) {
                $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            }

            // Discard any buffered output (e.g. the JSON content-type header set in the constructor
            // still applies, but we override it below before streaming the binary file)
            if (ob_get_length()) {
                ob_end_clean();
            }

            $filename = 'ALL Companies - Clients Sheet and Ledger Sheet' . date('Y-m-d_His') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');

            $writer = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
            $writer->save('php://output');
            exit;
        } catch (Exception $e) {
            log_message('error', 'Companies Excel Export API Error: ' . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to export companies',
                'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get single company details
     */
    public function view($id)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid company ID'
                ], 400);
                return;
            }

            $this->db->select('c.*');
            $this->db->select('p.id as package_id, p.name as package_name, p.max_outlets, p.max_active_staff');
            $this->db->select('r.id as reseller_id, r.name as reseller_name');

            $this->db->from('companies c');
            $this->db->join('packages p', 'p.id = c.package', 'left');
            $this->db->join('resellers r', 'r.id = c.reseller_id', 'left');
            $this->db->where('c.id', $id);

            $query = $this->db->get();

            if (!$query) {
                $error = $this->db->error();
                throw new Exception('Database query failed: ' . $error['message']);
            }

            $company = $query->row();
            $company_id = $company->id;
            $company->outlets = $this->db->select('id, name')->from('branches')->where('company_id', $company_id)->get()->result();


            if (!$company) {
                $this->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], 404);
                return;
            }


            $this->json([
                'success' => true,
                'data' => $company
            ]);
        } catch (Exception $e) {
            log_message('error', 'Company Details API Error: ' . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to fetch company details',
                'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
    public function getOutlets($company_id)
    {

        $data["outlets"] = $this->db->select('id, name')->from('branches')->where('company_id', $company_id)->get()->result();
        $data["success"] = true;
        echo json_encode($data);
    }


    /**
     * Create new company
     */

    public function create()
    {
        $input = json_decode(file_get_contents('php://input'));

        // Required fields
        $required = ['name', 'address', 'phone', 'admin', 'email', 'package'];
        foreach ($required as $f) {
            if (empty($input->$f)) {
                return $this->json(['success' => false, 'message' => "$f required"], 400);
            }
        }

        // Check email exists
        $exists = $this->db
            ->where('email', $input->email)
            ->where('deleted_at IS NULL', null, false)
            ->get('employees')
            ->row();

        if ($exists) {
            return $this->json(['success' => false, 'message' => 'Email already exists'], 409);
        }

        // Check organization_id uniqueness if provided
        if (!empty($input->organization_id)) {
            $orgExists = $this->db
                ->where('organization_id', $input->organization_id)
                ->where('deleted_at IS NULL', null, false)
                ->get('companies')
                ->row();

            if ($orgExists) {
                return $this->json([
                    'success' => false,
                    'message' => 'A company with this organization ID already exists'
                ], 409);
            }
        }

        // Insert company
        $data = [
            'reseller_id'       => $input->reseller_id ?? null,
            'name'              => $input->name,
            'organization_id'   => $input->organization_id ?? null,
            'phone'             => $input->phone,
            'address'           => $input->address,
            'start_date'        => $input->start_date ?? null,
            'package'           => $input->package,
            'contract_months'   => $input->contract_months ?? null,
            'recurring_rm'      => $input->recurring_rm ?? null,
            'billing_cycle'      => $input->billing_cycle ?? null,
            'renewal_status'    => $input->renewal_status ?? null,
            'last_renewal_date'    => $input->last_renewal_date ?? null,
            'recurring_dealer'    => $input->recurring_dealer ?? null,
            'status'            => $input->status ?? 1,
            'current_status'    => $input->current_status ?? 'active',
            'additional_staff'  => $input->additional_staff ?? 0,
            'cut_off_time'      => $input->cut_off_time ?? null,
            'admin'             => $input->admin,
            'admin_id'             => $input->admin_id ?? null,
            'email'             => $input->email,
            'remarks'           => $input->remarks ?? null,
            'salesperson'       => $input->salesperson ?? null
        ];

        $this->db->insert('companies', $data);

        // Check DB errors
        if ($this->db->error()['code'] != 0) {
            return $this->json([
                'success' => false,
                'message' => 'Database error',
                'error'   => $this->db->error()['message']
            ], 500);
        }

        $company_id = $this->db->insert_id();

        if (!$company_id) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to create company'
            ], 500);
        }

        // Seed defaults
        $this->_seed_company_defaults($company_id, $input);

        return $this->json([
            'success' => true,
            'message' => 'Company created',
            'company_id' => $company_id
        ], 201);
    }

    public function update($id = 0)
    {
        if (!$id) {
            return $this->json(['success' => false, 'message' => 'Company ID required'], 400);
        }

        $input = json_decode(file_get_contents('php://input'));

        // Required fields
        $required = ['name', 'address', 'phone', 'admin', 'email', 'package'];
        foreach ($required as $f) {
            if (empty($input->$f)) {
                return $this->json(['success' => false, 'message' => "$f required"], 400);
            }
        }

        // Check company exists
        $company = $this->db->where('id', $id)->get('companies')->row();
        if (!$company) {
            return $this->json(['success' => false, 'message' => 'Company not found'], 404);
        }

        // Check organization_id uniqueness if provided
        if (!empty($input->organization_id)) {
            $orgExists = $this->db
                ->where('organization_id', $input->organization_id)
                ->where('id !=', $id)
                ->get('companies')
                ->row();

            if ($orgExists) {
                return $this->json([
                    'success' => false,
                    'message' => 'A company with this organization ID already exists'
                ], 409);
            }
        }
        $is_notified = 0;
        $noti_message = '';


        //// Is notified will be 1 if updated fileds are one them or all package,renewal_status,recurring_dealer or status
        if (
            (isset($input->package) && $input->package != $company->package) ||
            (isset($input->renewal_status) && $input->renewal_status != $company->renewal_status) ||
            (isset($input->recurring_dealer) && $input->recurring_dealer != $company->recurring_dealer) ||
            (isset($input->status) && $input->status != $company->status)
        ) {
            $is_notified = 1;
            $noti_message = ' updated "' . $company->name . '":';
            if (isset($input->package) && $input->package != $company->package) {
                $noti_message .= 'Package changed from ' . $company->package . ' to ' . $input->package . '. ';
            }
            if (isset($input->renewal_status) && $input->renewal_status != $company->renewal_status) {
                $noti_message .= 'Renewal status changed from ' . $company->renewal_status . ' to ' . $input->renewal_status . '. ';
            }
            if (isset($input->recurring_dealer) && $input->recurring_dealer != $company->recurring_dealer) {
                $noti_message .= 'Recurring dealer changed from ' . $company->recurring_dealer . ' to ' . $input->recurring_dealer . '. ';
            }
            if (isset($input->status) && $input->status != $company->status) {
                $noti_message .= 'Status changed from ' . $company->status . ' to ' . $input->status . '. ';
            }
        }

        // Prepare update data
        $data = [
            'reseller_id'       => $input->reseller_id ?? $company->reseller_id,
            'name'              => $input->name,
            'organization_id'   => $input->organization_id ?? $company->organization_id,
            'phone'             => $input->phone,
            'address'           => $input->address,
            'start_date'        => $input->start_date ?? $company->start_date,
            'package'           => $input->package,
            'contract_months'   => $input->contract_months ?? $company->contract_months,
            'billing_cycle'   => $input->billing_cycle ?? $company->billing_cycle,
            'recurring_rm'      => $input->recurring_rm ?? $company->recurring_rm,
            'renewal_status'    => $input->renewal_status ?? $company->renewal_status,
            'last_renewal_date'    => $input->last_renewal_date ?? $company->last_renewal_date,
            'recurring_dealer'    => $input->recurring_dealer ?? $company->recurring_dealer,
            'status'            => $input->status ?? $company->status,
            'current_status'    => $input->current_status ?? $company->current_status,
            'additional_staff'  => $input->additional_staff ?? 0,
            'cut_off_time'      => $input->cut_off_time ?? null,
            'admin'             => $input->admin,
            'admin_id'             => $input->admin_id ?? $company->admin_id,
            'email'             => $input->email,
            'remarks'           => $input->remarks ?? $company->remarks,
            'salesperson'       => $input->salesperson ?? $company->salesperson
        ];

        $this->db->where('id', $id)->update('companies', $data);

        // Check DB errors
        if ($this->db->error()['code'] != 0) {
            return $this->json([
                'success' => false,
                'message' => 'Database error',
                'error'   => $this->db->error()['message']
            ], 500);
        }

        return $this->json([
            'success' => true,
            'message' => 'Company updated',
            'company_id' => $id,
            'is_notified' => $is_notified,
            'noti_message' => $noti_message
        ], 200);
    }
    public function delete($id = 0)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Invalid company ID'
                ], 400);
            }

            // Check if company exists
            $company = $this->db->where('id', $id)->get('companies')->row();
            if (!$company) {
                return $this->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], 404);
            }

            // Check related tables safely
            $has_users = $this->db->table_exists('users')
                ? $this->db->where('company_id', $id)->count_all_results('users') > 0
                : false;

            $has_employees = $this->db->table_exists('employees')
                ? $this->db->where('company_id', $id)->count_all_results('employees') > 0
                : false;

            $has_branches = $this->db->table_exists('branches')
                ? $this->db->where('company_id', $id)->count_all_results('branches') > 0
                : false;

            $has_data = $has_users || $has_employees || $has_branches;

            if ($has_data) {
                // Soft delete company and related tables
                $timestamp = date('Y-m-d H:i:s');
                $this->db->where('id', $id)->update('companies', [
                    'status' => 'deleted',
                    'deleted_at' => $timestamp,
                    'updated_at' => $timestamp
                ]);

                if ($has_employees) {
                    $this->db->where('company_id', $id)->update('employees', ['deleted_at' => $timestamp]);
                }
                if ($has_branches) {
                    $this->db->where('company_id', $id)->update('branches', ['deleted_at' => $timestamp]);
                }

                $message = 'Company marked as deleted (soft delete)';
            } else {
                // Hard delete company
                $delete_result = $this->db->where('id', $id)->delete('companies');
                if (!$delete_result) {
                    throw new Exception('Failed to delete company: ' . $this->db->error()['message']);
                }
                $message = 'Company deleted successfully';
            }

            return $this->json([
                'success' => true,
                'message' => $message,
                'company_id' => $id
            ]);
        } catch (Exception $e) {
            log_message('error', 'Delete Company API Error: ' . $e->getMessage());

            return $this->json([
                'success' => false,
                'message' => 'Failed to delete company',
                'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    // ==================================================================
    // 7. COMPANY SEEDING (Fixed & Working)
    // ==================================================================
    private function _seed_company_defaults($company_id, $input)
    {
        $this->db->insert('roles', [
            'company_id'             => $company_id,
            'job_name'               => 'Company Admin',
            'permissions'            => 'everything',
            'permissions_level'      => 'Company',
            'is_emp_summary_editable' => 'yes',
            'exclude_from_system'    => 'yes'
        ]);
        $admin_role_id = $this->db->insert_id();

        $this->db->insert('roles', [
            'company_id'        => $company_id,
            'job_name'          => 'Outlet Admin',
            'permissions'       => 'everything',
            'permissions_level' => 'Outlet',
            'exclude_from_system' => 'yes'
        ]);

        $this->db->insert('roles', [
            'company_id'        => $company_id,
            'job_name'          => 'Employee',
            'permissions_level' => 'Personal'
        ]);

        // Generate email + passwords from orgid
        $orgid   = $input->organization_id;
        $orgid   = str_replace(' ', '', strtolower($orgid));
        $email   = $orgid . '@invocore.com.my';
        $pass   = $orgid . '!@#';
        $final_password = md5($pass);  // or md5($pass2)

        // Insert employee using generated credentials
        $this->db->insert('employees', [
            'first_name' => $input->admin,
            'email'      => $email,
            'password'   => $final_password,
            'company_id' => $company_id,
            'role_id'    => $admin_role_id
        ]);


        $this->db->insert('days_settings', ['from_hour' => '1', 'to_hour' => '4', 'days' => '0.5', 'company_id' => $company_id]);
        $this->db->insert('days_settings', ['from_hour' => '4', 'to_hour' => '24', 'days' => '1', 'company_id' => $company_id]);
        $this->db->insert('company_working_hours', ['company_id' => $company_id, 'total_hours' => '08:00:00', 'half_hours' => '04:00:00']);

        $leaves = [
            ['company_id' => $company_id, 'name' => 'Annual Leave', 'color' => 'Blue', 'code' => 'AL', 'is_paid' => 'yes', 'half_day' => 'no', 'void_late_in' => 'no', 'void_early_out' => 'no', 'is_leave' => 'yes', 'is_approved' => '1'],
            ['company_id' => $company_id, 'name' => 'Half Day Paid', 'color' => 'Blue', 'code' => 'HDP', 'is_paid' => 'yes', 'half_day' => 'yes', 'void_late_in' => 'yes', 'void_early_out' => 'yes', 'is_leave' => 'yes', 'is_approved' => '1'],
            ['company_id' => $company_id, 'name' => 'Unpaid Leave', 'color' => 'Orange', 'code' => 'UL', 'is_paid' => 'no', 'half_day' => 'no', 'void_late_in' => 'no', 'void_early_out' => 'no', 'is_leave' => 'yes', 'is_approved' => '1'],
            ['company_id' => $company_id, 'name' => 'Half Day Unpaid', 'color' => 'Orange', 'code' => 'HDU', 'is_paid' => 'no', 'half_day' => 'yes', 'void_late_in' => 'yes', 'void_early_out' => 'yes', 'is_leave' => 'yes', 'is_approved' => '1'],
            ['company_id' => $company_id, 'name' => 'Medical Leave', 'color' => 'Red', 'code' => 'MC', 'is_paid' => 'yes', 'half_day' => 'no', 'void_late_in' => 'no', 'void_early_out' => 'no', 'is_leave' => 'yes', 'is_approved' => '1'],
            ['company_id' => $company_id, 'name' => 'Hospitalization Leave', 'color' => 'Purple', 'code' => 'HL', 'is_paid' => 'yes', 'half_day' => 'no', 'void_late_in' => 'no', 'void_early_out' => 'no', 'is_leave' => 'yes', 'is_approved' => '1'],
            ['company_id' => $company_id, 'name' => 'Maternity Leave', 'color' => 'Pink', 'code' => 'ML', 'is_paid' => 'yes', 'half_day' => 'no', 'void_late_in' => 'no', 'void_early_out' => 'no', 'is_leave' => 'yes', 'is_approved' => '1']
        ];

        $this->db->insert_batch('shifts', $leaves);
    }


    /**
     * Get company statistics
     */
    public function companyStats($id)
    {
        try {
            if (empty($id) || !is_numeric($id)) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid company ID'
                ], 400);
                return;
            }

            // Check if company exists
            $company = $this->db->where('id', $id)->get('companies')->row();

            if (!$company) {
                $this->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], 404);
                return;
            }

            $stats = [
                'total_users' => $this->getCompanyUsersCount($id),
                'active_users' => $this->getActiveUsersCount($id),
                'inactive_users' => $this->getInactiveUsersCount($id),
                'storage_used' => $this->getCompanyStorageUsed($id),
                'storage_used_mb' => round($this->getCompanyStorageUsed($id) / 1024 / 1024, 2),
                'projects_count' => $this->getCompanyProjectsCount($id),
                'active_projects' => $this->getActiveProjectsCount($id),
                'last_login' => $this->getLastLoginDate($id)
            ];

            $this->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            log_message('error', 'Company Stats API Error: ' . $e->getMessage());

            $this->json([
                'success' => false,
                'message' => 'Failed to fetch company statistics',
                'error' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    // ==================================================================
    // HELPER FUNCTIONS with error handling
    // ==================================================================

    private function getCompanyUsersCount($company_id)
    {
        try {
            $this->db->where('company_id', $company_id);
            return $this->db->count_all_results('users');
        } catch (Exception $e) {
            log_message('error', 'getCompanyUsersCount Error: ' . $e->getMessage());
            return 0;
        }
    }

    private function getActiveUsersCount($company_id)
    {
        try {
            $this->db->where('company_id', $company_id)
                ->where('status', 'active');
            return $this->db->count_all_results('users');
        } catch (Exception $e) {
            log_message('error', 'getActiveUsersCount Error: ' . $e->getMessage());
            return 0;
        }
    }

    private function getInactiveUsersCount($company_id)
    {
        try {
            $this->db->where('company_id', $company_id)
                ->where('status', 'inactive');
            return $this->db->count_all_results('users');
        } catch (Exception $e) {
            log_message('error', 'getInactiveUsersCount Error: ' . $e->getMessage());
            return 0;
        }
    }

    private function getCompanyStorageUsed($company_id)
    {
        // Placeholder - implement based on your actual storage structure
        return 0;
    }

    private function getCompanyProjectsCount($company_id)
    {
        // Placeholder - implement based on your actual projects table
        return 0;
    }

    private function getActiveProjectsCount($company_id)
    {
        // Placeholder - implement based on your actual projects table
        return 0;
    }

    private function getLastLoginDate($company_id)
    {
        try {
            $query = $this->db->select('last_login')
                ->where('company_id', $company_id)
                ->where('last_login IS NOT NULL')
                ->order_by('last_login', 'DESC')
                ->limit(1)
                ->get('users');

            if ($query && $query->num_rows() > 0) {
                return $query->row()->last_login;
            }
            return null;
        } catch (Exception $e) {
            log_message('error', 'getLastLoginDate Error: ' . $e->getMessage());
            return null;
        }
    }

    private function getCompanyRecentActivity($company_id)
    {
        // Placeholder - implement based on your actual activity logs
        return [];
    }
}
