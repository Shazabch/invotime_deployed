<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Over_time_summary_report_handler
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

        $title = 'SUMMARY OF OVERTIME HOURS FOR FACTORY WORKERS';
        $month_year = (new DateTime($first_day))->format('F Y');
        $object->getActiveSheet()->setCellValue('A1', $title);
        $object->getActiveSheet()->mergeCells('A1:AH1');
        $object->getActiveSheet()->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $object->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $object->getActiveSheet()->setCellValue('A2', 'FOR THE MONTH OF ' . strtoupper($month_year));
        $object->getActiveSheet()->mergeCells('A2:AH2');
        $object->getActiveSheet()->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $object->getActiveSheet()->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $object->getActiveSheet()->setCellValue('A3', 'BRANCH: ' . strtoupper($branch_name));
        $object->getActiveSheet()->mergeCells('A3:AH3');

        $period = new DatePeriod(new DateTime($first_day), new DateInterval('P1D'), (new DateTime($last_day))->modify('+1 day'));
        $table_columns = array('Employee ID', 'Name', 'Department', 'Outlet', 'Team');
        $table_columns2 = array('', '', '', '', '');

        foreach ($period as $dateObj) {
            $table_columns[] = $dateObj->format('d');
            $table_columns2[] = $dateObj->format('D');
        }

        $table_columns = array_merge($table_columns, array('OT (1.5)', 'OT(SAT)', 'Total OT (1.5)', 'OT(2.0)', 'OT(PH)', 'Total OT(2.0)', 'Meal Allowance', 'Day Shift  (DSA)', 'Night Shift (NSA)'));
        $table_columns2 = array_merge($table_columns2, array('', '', '', '', '', '', '', '', ''));

        $header_row = 8;
        $column = 0;
        foreach ($table_columns as $field) {
            $object->getActiveSheet()->setCellValueByColumnAndRow($column, $header_row, $field);
            $object->getActiveSheet()->getStyleByColumnAndRow($column, $header_row)->getFont()->setBold(true);
            $column++;
        }

        $header_row2 = 9;
        $column = 0;
        foreach ($table_columns2 as $field) {
            $object->getActiveSheet()->setCellValueByColumnAndRow($column, $header_row2, $field);
            $object->getActiveSheet()->getStyleByColumnAndRow($column, $header_row2)->getFont()->setBold(true);
            $column++;
        }

        $row = 10;
        foreach ($all_data as $entry) {
            $employee = $entry['employee'];
            $object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, isset($employee->special_id) ? $employee->special_id : '');
            $object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, isset($employee->first_name) ? $employee->first_name : '');
            $object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, isset($employee->department) ? $employee->department : '');
            $object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, isset($employee->branch) ? $employee->branch : $branch_name);
            $object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, isset($employee->group_names) ? $employee->group_names : '');

            $col = 5;
            foreach ($entry['dates'] as $day) {
                $daily_ot = '00:00';
                if (isset($day->date)) {
                    $daily_ot = getOvertimeValue($day, $entry['public_holidays'], $entry['rest_days'], $entry['off_days']);
                }
                $object->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $daily_ot);
            }

            $month_total_ot_decimal = toDecimal(isset($entry['month_overtime']) ? $entry['month_overtime'] : 0);
            $month_total_ot_rd_decimal = toDecimal(isset($entry['month_overtime_rd']) ? $entry['month_overtime_rd'] : 0);
            $month_total_ot_one_point_zero_decimal = $month_total_ot_decimal + $month_total_ot_rd_decimal;
            $month_total_ot_off_decimal = toDecimal(isset($entry['month_overtime_off']) ? $entry['month_overtime_off'] : 0);
            $month_total_ot_ph_decimal = toDecimal(isset($entry['month_overtime_ph_x3']) ? $entry['month_overtime_ph_x3'] : 0);
            $month_total_ot_two_point_zero_decimal = $month_total_ot_off_decimal + $month_total_ot_ph_decimal;

            $object->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $month_total_ot_decimal);
            $object->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $month_total_ot_rd_decimal);
            $object->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $month_total_ot_one_point_zero_decimal);
            $object->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $month_total_ot_off_decimal);
            $object->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $month_total_ot_ph_decimal);
            $object->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, $month_total_ot_two_point_zero_decimal);
            $object->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, isset($entry['food_allowance_days']) ? $entry['food_allowance_days'] : '');
            $object->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, isset($entry['monthly_dsa_count']) ? $entry['monthly_dsa_count'] : '');
            $object->getActiveSheet()->setCellValueByColumnAndRow($col++, $row, isset($entry['monthly_nsa_count']) ? $entry['monthly_nsa_count'] : '');
            $row++;
        }

        foreach (range('A', $object->getActiveSheet()->getHighestColumn()) as $columnID) {
            $object->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
        }

        $file_name = '(' . $branch_name . ') OT Summary - ' . date('F Y', strtotime($first_day)) . '.xlsx';
        $file_path = FCPATH . 'uploads/summary/' . $file_name;
        $writer = new PHPExcel_Writer_Excel2007($object);
        $writer->save($file_path);

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
