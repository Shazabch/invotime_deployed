<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sql_report_handler
{
    public function generate($context, $cid, $branch_name, $first_day, $last_day, $first_day_formatted, $last_day_formatted)
    {
        require_once APPPATH . 'controllers/Exports.php';
        $CI = get_instance();
        $CI->load->library('excel');

        $all_data = isset($context['all_data']) ? $context['all_data'] : array();
        $worked_rest_days_array = isset($context['worked_rest_days_array']) ? $context['worked_rest_days_array'] : array();
        $worked_off_days_array = isset($context['worked_off_days_array']) ? $context['worked_off_days_array'] : array();
        $worked_holidays_array = isset($context['worked_holidays_array']) ? $context['worked_holidays_array'] : array();
        $unpaid_leaves_absent_days = isset($context['unpaid_leaves_absent_days']) ? $context['unpaid_leaves_absent_days'] : array();
        $paid_leaves_array = isset($context['paid_leaves_array']) ? $context['paid_leaves_array'] : array();
        $daily_ot_array = isset($context['daily_ot_array']) ? $context['daily_ot_array'] : array();
        $daily_late_array = isset($context['daily_late_array']) ? $context['daily_late_array'] : array();
        $employees_ids = isset($context['employees_ids']) ? $context['employees_ids'] : array();

        ksort($unpaid_leaves_absent_days);
        ksort($paid_leaves_array);
        ksort($daily_ot_array);
        ksort($daily_late_array);

        $first_day_display = $first_day;
        $last_day_display = $last_day;
        $date_obj = DateTime::createFromFormat('Y-m-d', $first_day);
        if ($date_obj) {
            $first_day_display = $date_obj->format('d M, Y');
        }
        $date_obj = DateTime::createFromFormat('Y-m-d', $last_day);
        if ($date_obj) {
            $last_day_display = $date_obj->format('d M, Y');
        }

        $date2 = DateTime::createFromFormat('Y-m-d', $last_day);
        if (!$date2) {
            throw new Exception('Invalid SQL report end date');
        }

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $ref = new ReflectionClass('Exports');
        $exports = $ref->newInstanceWithoutConstructor();

        $files = array();
        $files[] = $exports->pendingOvertimeLogFile($style, $all_data, $date2, $branch_name, $first_day_display, $last_day_display);
        $files[] = $exports->pendingUnpaidLeavesLogFile($style, $unpaid_leaves_absent_days, $cid, $date2, $branch_name, $first_day_display, $last_day_display);

        if ($cid == 153 || $cid == 255) {
            $files[] = $exports->pendingAbsentLogFile($style, $unpaid_leaves_absent_days, $date2, $branch_name, $first_day_display, $last_day_display);
        }

        if ($cid == 153) {
            $files[] = $exports->pendingDailyOTLogFile($style, $daily_ot_array, $date2, $branch_name, $first_day_display, $last_day_display);
            $files[] = $exports->pendingDailyLateLogFile($style, $daily_late_array, $date2, $branch_name, $first_day_display, $last_day_display);
        }

        $files[] = $exports->pendingWorkedRestDaysLogFile($style, $cid, $all_data, $worked_rest_days_array, $date2, $branch_name, $first_day_display, $last_day_display);
        $files[] = $exports->pendingWorkedOffDaysLogFile($style, $worked_off_days_array, $date2, $branch_name, $first_day_display, $last_day_display);
        $files[] = $exports->pendingWorkedPublicHolidaysLogFile($style, $worked_holidays_array, $date2, $branch_name, $first_day_display, $last_day_display);
        $files[] = $exports->pendingDailyWageLogFile($style, $all_data, $date2, $branch_name, $first_day_display, $last_day_display);
        $files[] = $exports->pendingEarlyLateLogFile($style, $all_data, $date2, $branch_name, $first_day_display, $last_day_display);
        $files[] = $exports->pendingDeductionLogFile($style, $all_data, $cid, $date2, $branch_name, $first_day_display, $last_day_display);
        $files[] = $exports->pendingShiftWorkedHoursFile($style, $all_data, $date2, $branch_name, $first_day_display, $last_day_display);
        $files[] = $exports->pendingWorkedHoursFile($style, $all_data, $date2, $branch_name, $first_day_display, $last_day_display);

        if (in_array($cid, companies_allowed_for_leave_application(), true)) {
            $files[] = $exports->pendingLeaveApplicationLogFile($style, $paid_leaves_array, $branch_name, $first_day_display, $last_day_display);
        }

        if (
            in_array($cid, companies_allowed_for_att_all(), true) ||
            in_array($cid, companies_allowed_for_meal_allowance(), true) ||
            in_array($cid, companies_allowed_for_shift_allowance(), true) ||
            $cid == 215 || $cid == 152 || $cid == 206 || $cid == 229
        ) {
            $files[] = $exports->pendingAllowanceLogFile($style, $all_data, $cid, $date2, $branch_name, $first_day_display, $last_day_display);
        }

        if (in_array($cid, companies_allowed_for_allowance_report(), true) && !empty($employees_ids)) {
            $allowances = get_allowances_for_report($employees_ids, $first_day, $last_day);
            $files[] = $exports->pendingAllowanceReportLogFile($style, $allowances, $date2, $branch_name, $first_day_display, $last_day_display);
        }

        $file_name = '(' . $branch_name . ') SQL Payroll - ' . $first_day_display . ' to ' . $last_day_display . ' ' . time() . '.zip';
        $zip_path = FCPATH . 'uploads/summary/' . $file_name;

        $zip = new ZipArchive();
        if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Failed to create SQL report ZIP file');
        }

        foreach ($files as $file) {
            $abs = FCPATH . $file;
            if (is_file($abs)) {
                $zip->addFile($abs, basename($abs));
            }
        }
        $zip->close();

        foreach ($files as $file) {
            $abs = FCPATH . $file;
            if (is_file($abs)) {
                @unlink($abs);
            }
        }

        return array(
            'status' => 'success',
            'file_path' => $zip_path,
            'file_name' => $file_name,
            'summary' => array(
                'branch' => $branch_name,
                'period' => array(
                    'from' => $first_day_formatted,
                    'to' => $last_day_formatted
                ),
                'employee_count' => count($all_data),
                'file_type' => 'zip',
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => 'Queue Worker'
            )
        );
    }
}
