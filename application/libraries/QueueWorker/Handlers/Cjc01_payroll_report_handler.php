<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cjc01_payroll_report_handler
{
    private $ci;

    public function __construct()
    {
        $this->ci = get_instance();
    }

    public function generate($all_data, $branch_name, $first_day, $last_day)
    {
        $this->ci->load->library('excel');

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $object = new PHPExcel();
        $object->setActiveSheetIndex(0);
        $object->getDefaultStyle()->applyFromArray($style);

        $headings = array(
            'EMP_NO',
            'EMP_NAME',
            'OT1.5C',
            'OT2.0C',
            '1.0 DAY-C',
            'OT3.0C',
            '2.0 DAY-C',
            'INCENTV',
            'SPPA',
            'RDPHALLW',
            'SPEC_INC'
        );

        $column = 0;
        foreach ($headings as $heading) {
            $object->getActiveSheet()->setCellValueByColumnAndRow($column++, 1, $heading);
        }

        $row = 2;
        foreach ($all_data as $entry) {
            $employee = $entry['employee'];

            $object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, isset($employee->special_id) ? $employee->special_id : '');
            $object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, isset($employee->first_name) ? $employee->first_name : '');
            $object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, toDecimal(isset($entry['month_overtime']) ? $entry['month_overtime'] : 0));
            $object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, toDecimal(isset($entry['month_overtime_rd']) ? $entry['month_overtime_rd'] : 0));
            $object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, isset($entry['worked_rest_days']) ? $entry['worked_rest_days'] : 0);
            $object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, toDecimal(isset($entry['month_overtime_ph']) ? $entry['month_overtime_ph'] : 0));
            $object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, isset($entry['worked_holidays']) ? $entry['worked_holidays'] : 0);

            $ot_group_value = 'RM0.00';
            if (isset($employee->ot_group) && $employee->ot_group === 'day') {
                $ot_group_value = 'RM' . number_format((
                    toDecimal(isset($entry['month_overtime']) ? $entry['month_overtime'] : 0) +
                    toDecimal(isset($entry['month_overtime_rd']) ? $entry['month_overtime_rd'] : 0) +
                    toDecimal(isset($entry['month_overtime_ph']) ? $entry['month_overtime_ph'] : 0)
                ) * 15, 2);
            }
            $object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $ot_group_value);

            $object->getActiveSheet()->setCellValueByColumnAndRow(8, $row, '');

            $allowance_value = 'RM0.00';
            if (!isset($employee->ot_group) || $employee->ot_group !== 'day') {
                $allowance_value = 'RM' . number_format(((float)(isset($entry['worked_rest_days']) ? $entry['worked_rest_days'] : 0) + (float)(isset($entry['worked_holidays']) ? $entry['worked_holidays'] : 0)) * 30, 2);
            }
            $object->getActiveSheet()->setCellValueByColumnAndRow(9, $row, $allowance_value);

            $special_incentive = isset($employee->special_incentive) ? (float)$employee->special_incentive : 0;
            $object->getActiveSheet()->setCellValueByColumnAndRow(10, $row, 'RM' . number_format($special_incentive, 2));

            $row++;
        }

        foreach (range('A', 'K') as $columnID) {
            $object->getActiveSheet()->getColumnDimension($columnID)->setWidth(12);
        }
        $object->getActiveSheet()->getColumnDimension('B')->setWidth(35);

        $file_name = '(' . $branch_name . ') CJC01 Payroll - ' . $first_day . ' to ' . $last_day . ' ' . time() . '.xlsx';
        $file_path = FCPATH . 'uploads/summary/' . $file_name;
        $writer = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
        $writer->save($file_path);

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
                'file_type' => 'xlsx',
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => 'Queue Worker'
            )
        );
    }
}
