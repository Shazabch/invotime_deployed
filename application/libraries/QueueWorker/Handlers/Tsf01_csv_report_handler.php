<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tsf01_csv_report_handler
{
    public function generate($all_data, $branch_name, $first_day, $last_day)
    {
        $file_name = '(' . $branch_name . ') TSF01 CSV Report - ' . $first_day . ' to ' . $last_day . ' ' . time() . '.csv';
        $file_path = FCPATH . 'uploads/summary/' . $file_name;

        $handle = fopen($file_path, 'w');
        if ($handle === false) {
            throw new Exception('Failed to create TSF01 CSV file');
        }

        fputcsv($handle, array(
            'EMP_NO',
            'EMP_NAME',
            'WORKING_DAYS',
            'WORKED_DAYS',
            'ABSENT_DAYS',
            'PAID_LEAVES',
            'UNPAID_LEAVES',
            'OT',
            'OT_RD',
            'OT_PH',
            'LATE_COUNT',
            'LATE_TIME'
        ));

        foreach ($all_data as $entry) {
            $employee = isset($entry['employee']) ? $entry['employee'] : (object) array();
            fputcsv($handle, array(
                isset($employee->special_id) ? $employee->special_id : '',
                isset($employee->first_name) ? $employee->first_name : '',
                isset($entry['working_days']) ? $entry['working_days'] : 0,
                isset($entry['worked_days']) ? $entry['worked_days'] : 0,
                isset($entry['absent_days']) ? $entry['absent_days'] : 0,
                isset($entry['paid_leaves']) ? $entry['paid_leaves'] : 0,
                isset($entry['unpaid_leaves']) ? $entry['unpaid_leaves'] : 0,
                toDecimal(isset($entry['month_overtime']) ? $entry['month_overtime'] : 0),
                toDecimal(isset($entry['month_overtime_rd']) ? $entry['month_overtime_rd'] : 0),
                toDecimal(isset($entry['month_overtime_ph']) ? $entry['month_overtime_ph'] : 0),
                isset($entry['late_count']) ? $entry['late_count'] : 0,
                isset($entry['lateness_time_deducted']) ? $entry['lateness_time_deducted'] : 0
            ));
        }

        fclose($handle);

        return array(
            'status' => 'success',
            'file_path' => $file_path,
            'file_name' => $file_name,
            'summary' => array(
                'branch' => $branch_name,
                'period' => array(
                    'from' => $first_day,
                    'to' => $last_day
                ),
                'employee_count' => count($all_data),
                'file_type' => 'csv',
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => 'Queue Worker'
            )
        );
    }
}
