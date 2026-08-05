<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mm01_report_handler
{
    private $ci;

    public function __construct()
    {
        $this->ci = get_instance();
    }

    public function generate($all_data, $branch_name, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $file_type)
    {
        if ($file_type === 'pdf') {
            $this->ci->load->library('dompdf_lib');

            foreach ($all_data as &$row) {
                $row['type'] = 'mm01_report_pdf';
            }
            unset($row);

            $summary_body = $this->ci->load->view('mm01_report_pdf', array('all_data' => $all_data), true);
            $html = $this->ci->load->view('summary_pdf', array('summary_body' => $summary_body), true);

            $this->ci->dompdf_lib->reset();
            $this->ci->dompdf_lib->loadHtml($html);
            $this->ci->dompdf_lib->setPaper('A4', 'portrait');
            $this->ci->dompdf_lib->render();

            $output = $this->ci->dompdf_lib->output();
            $file_name = 'MM01 Report - ' . $first_day . ' to ' . $last_day . '.pdf';
            $file_path = FCPATH . 'uploads/summary/' . $file_name;
            file_put_contents($file_path, $output);

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
                    'file_type' => 'pdf',
                    'generated_at' => date('Y-m-d H:i:s'),
                    'generated_by' => 'Queue Worker'
                )
            );
        }

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
            'Employee ID',
            'Employee Name',
            'Working Days',
            'Worked Days',
            'Absent Days',
            'Paid Leave',
            'Unpaid Leave',
            'OT',
            'OT RD',
            'OT PH',
            'Late Count',
            'Late Time'
        );

        $column = 0;
        foreach ($headings as $heading) {
            $object->getActiveSheet()->setCellValueByColumnAndRow($column, 1, $heading);
            $object->getActiveSheet()->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);
            $column++;
        }

        $row = 2;
        foreach ($all_data as $entry) {
            $employee = $entry['employee'];
            $object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, isset($employee->special_id) ? $employee->special_id : '');
            $object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, isset($employee->first_name) ? $employee->first_name : '');
            $object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, isset($entry['working_days']) ? $entry['working_days'] : 0);
            $object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, isset($entry['worked_days']) ? $entry['worked_days'] : 0);
            $object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, isset($entry['absent_days']) ? $entry['absent_days'] : 0);
            $object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, isset($entry['paid_leaves']) ? $entry['paid_leaves'] : 0);
            $object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, isset($entry['unpaid_leaves']) ? $entry['unpaid_leaves'] : 0);
            $object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, toDecimal(isset($entry['month_overtime']) ? $entry['month_overtime'] : 0));
            $object->getActiveSheet()->setCellValueByColumnAndRow(8, $row, toDecimal(isset($entry['month_overtime_rd']) ? $entry['month_overtime_rd'] : 0));
            $object->getActiveSheet()->setCellValueByColumnAndRow(9, $row, toDecimal(isset($entry['month_overtime_ph']) ? $entry['month_overtime_ph'] : 0));
            $object->getActiveSheet()->setCellValueByColumnAndRow(10, $row, isset($entry['late_count']) ? $entry['late_count'] : 0);
            $object->getActiveSheet()->setCellValueByColumnAndRow(11, $row, isset($entry['lateness_time_deducted']) ? $entry['lateness_time_deducted'] : 0);
            $row++;
        }

        foreach (range('A', 'L') as $columnID) {
            $object->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
        }

        if ($file_type === 'excel' || $file_type === 'xls') {
            $extension = 'xls';
            $writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
        } else {
            $extension = 'xlsx';
            $writer = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
        }

        $file_name = 'MM01 Report - ' . $first_day . ' to ' . $last_day . '.' . $extension;
        $file_path = FCPATH . 'uploads/summary/' . $file_name;
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
                'file_type' => $extension,
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => 'Queue Worker'
            )
        );
    }
}
