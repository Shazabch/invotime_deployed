<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Accounts_report_handler
{
    private $load;

    public function __construct($params = array())
    {
        $CI = get_instance();
        $this->load = isset($params['load']) ? $params['load'] : $CI->load;
    }

    public function generate($all_data, $branch_name, $first_day, $last_day, $first_day_formatted, $last_day_formatted)
    {
        $this->load->library('excel');

        $object = new PHPExcel();
        $object->setActiveSheetIndex(0);
        $object->getDefaultStyle()->applyFromArray(array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            )
        ));

        $header_rows = array(
            array('', 'The official work day of the month exclude Rest Day &  Holiday', 'The total number of day an employee has worked in Working Day', 'The total number of day an employee has absent', 'The total number of day an employee has taken leaves (exclude unpaid leaves)', 'The total number of day an employee has taken unpaid leaves', 'The total number of day an employee has worked in Rest Day', 'The total number of day an employee has worked in Holiday', 'The total number of overtime hour an employee has worked in Working Day', 'The total number of overtime hour an employee has worked in Rest Day', 'The total number of overtime hour an employee has worked in Holiday', 'The total number of Lateness count', 'The total number of Lateness hour', 'The total number of Early Out count', 'The total number of Early Out hour'),
            array('NvarChar(20)', 'Decimal(9, 1)', 'Decimal(9, 1)', 'Decimal(9, 1)', 'Decimal(9, 1)', 'Decimal(9, 1)', 'Decimal(9, 1)', 'Decimal(9, 1)', 'Decimal(9, 5)', 'Decimal(9, 5)', 'Decimal(9, 5)', 'Integer', 'Decimal(9, 2)', 'Integer', 'Decimal(9, 2)'),
            array('Employee Code', 'Working Days', 'Worked Days', 'Absent Days', 'Leave Days', 'Unpaid Leave Days', 'Worked Rest Days', 'Worked Holidays', 'OT', 'OT For Rest Days', 'OT For Holidays', 'Lateness Count', 'Lateness Time', 'Early Out Count', 'Early Out Time')
        );

        foreach ($header_rows as $index => $row) {
            $column = 0;
            foreach ($row as $field) {
                $object->getActiveSheet()->setCellValueByColumnAndRow($column, $index + 1, $field);
                if ($index === 2) {
                    $object->getActiveSheet()->getStyleByColumnAndRow($column, $index + 1)->getFont()->setBold(true);
                } else {
                    $object->getActiveSheet()->getStyleByColumnAndRow($column, $index + 1)->getAlignment()->setWrapText(true);
                }
                $column++;
            }
        }

        $row = 4;
        foreach ($all_data as $r) {
            $object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $r['employee']->special_id);
            $object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $r['working_days']);
            $object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $r['worked_days']);
            $object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $r['absent_days']);
            $object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $r['paid_leaves']);
            $object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $r['unpaid_leaves']);
            $object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $r['worked_rest_days']);
            $object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $r['worked_holidays']);
            $object->getActiveSheet()->setCellValueByColumnAndRow(8, $row, $r['month_overtime_deducted']);
            $object->getActiveSheet()->setCellValueByColumnAndRow(9, $row, isset($r['month_overtime_rd']) ? $r['month_overtime_rd'] : 0);
            $object->getActiveSheet()->setCellValueByColumnAndRow(10, $row, isset($r['month_overtime_ph']) ? $r['month_overtime_ph'] : (isset($r['month_overtime_ph_x2']) ? $r['month_overtime_ph_x2'] : 0));
            $object->getActiveSheet()->setCellValueByColumnAndRow(11, $row, $r['late_count']);
            $object->getActiveSheet()->setCellValueByColumnAndRow(12, $row, $r['lateness_time_deducted']);
            $object->getActiveSheet()->setCellValueByColumnAndRow(13, $row, isset($r['total_early_count']) ? $r['total_early_count'] : 0);
            $object->getActiveSheet()->setCellValueByColumnAndRow(14, $row, isset($r['total_early']) ? $r['total_early'] : 0);
            $row++;
        }

        foreach (range('A', 'Z') as $columnID) {
            $object->getActiveSheet()->getColumnDimension($columnID)->setWidth(20);
        }

        $file_name = '(' . $branch_name . ') AutoCount Payroll - ' . $first_day . ' to ' . $last_day . '.xlsx';
        $file_path = FCPATH . 'uploads/summary/' . $file_name;
        $object_writer = new PHPExcel_Writer_Excel2007($object);
        $object_writer->save($file_path);

        return array(
            'status' => 'success',
            'file_path' => $file_path,
            'file_name' => $file_name,
            'summary' => array(
                'branch' => $branch_name,
                'period' => array(
                    'from' => $first_day_formatted,
                    'to' => $last_day_formatted
                ),
                'employee_count' => count($all_data),
                'file_type' => 'xlsx',
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => 'Queue Worker'
            )
        );
    }
}
