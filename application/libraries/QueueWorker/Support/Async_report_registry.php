<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Async_report_registry
{
    public function get_export_handler_methods()
    {
        return array(
            'short' => '_generate_short_report',
            'export_short_report' => '_generate_short_report',
            'accounts' => '_generate_accounts_report',
            'over_time_summary' => '_generate_over_time_summary_report',
            'lateness_report' => '_generate_lateness_report',
            'weekly_ot' => '_generate_weekly_ot_report',
            'weekly_ot_reports' => '_generate_weekly_ot_report',
            'full' => '_generate_full_summary_report',
            'full_summary' => '_generate_full_summary_report',
            'full_merged' => '_generate_full_summary_report',
            'sql' => '_generate_sql_report',
            'mcb01_clocking' => '_generate_mcb01_clocking_report',
            'tsf01_csv_report' => '_generate_tsf01_csv_report',
            'daily_time_card' => '_generate_daily_time_card_report',
            'work_hours_summary' => '_generate_work_hours_summary_report',
            'gni01_payroll_process' => '_generate_gni01_payroll_process_report',
            'cjc01_payroll' => '_generate_cjc01_payroll_report',
            'bmi_summary' => '_generate_bmi_summary_report',
            'bmi_summary_short' => '_generate_bmi_summary_short_report',
            'mm01_report' => '_generate_mm01_report'
        );
    }

    public function get_supported_async_report_types()
    {
        return array(
            'short',
            'accounts',
            'over_time_summary',
            'lateness_report',
            'weekly_ot',
            'weekly_ot_reports',
            'full',
            'full_summary',
            'full_merged',
            'sql',
            'mcb01_clocking',
            'tsf01_csv_report',
            'daily_time_card',
            'work_hours_summary',
            'gni01_payroll_process',
            'cjc01_payroll',
            'bmi_summary',
            'bmi_summary_short',
            'mm01_report'
        );
    }

    public function is_supported_async_report_type($type)
    {
        return in_array((string)$type, $this->get_supported_async_report_types(), true);
    }
}
