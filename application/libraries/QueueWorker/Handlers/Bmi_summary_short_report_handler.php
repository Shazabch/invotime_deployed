<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bmi_summary_short_report_handler
{
    private $ci;

    public function __construct()
    {
        $this->ci = get_instance();
    }

    public function generate($all_data, $branch_name, $first_day, $last_day)
    {
        $this->ci->load->library('excel');

        $object = PHPExcel_IOFactory::load(FCPATH . 'assets/bmi-short-template.xlsx');
        $object->setActiveSheetIndex(0);
        $sheet = $object->getActiveSheet();

        $date = DateTime::createFromFormat('Y-m-d', $first_day);
        $from_f = $date ? $date->format('d/m/Y') : $first_day;
        $sheet_name = $date ? $date->format('MY') : 'BMI';
        $date = DateTime::createFromFormat('Y-m-d', $last_day);
        $to_f = $date ? $date->format('d/m/Y') : $last_day;

        $sheet->setTitle(substr($sheet_name, 0, 30));
        $total = count($all_data);

        if ($total > 2) {
            $sheet->insertNewRowBefore(13, $total - 2);
            $row = 13 + $total - 2;
            for ($i = 0; $i < 25; $i++) {
                $cell_value = $sheet->getCellByColumnAndRow($i, $row)->getValue();
                $cell_value = str_replace('12', (string)($row - 1), (string)$cell_value);
                $sheet->setCellValueByColumnAndRow($i, $row, $cell_value);
            }
        }

        $row = 11;
        $count = 1;
        foreach ($all_data as $bmi) {
            $employee = $bmi['employee'];

            $sheet->setCellValueByColumnAndRow(0, $row, $count);
            $sheet->setCellValueByColumnAndRow(1, $row, isset($employee->special_id) ? $employee->special_id : '');
            $sheet->setCellValueByColumnAndRow(2, $row, isset($employee->first_name) ? $employee->first_name : '');
            $sheet->setCellValueByColumnAndRow(5, $row, isset($employee->basic_wage) ? $employee->basic_wage : 0);
            $sheet->setCellValueByColumnAndRow(6, $row, isset($bmi['working_days']) ? $bmi['working_days'] : 0);
            $sheet->setCellValueByColumnAndRow(8, $row, isset($bmi['total_bmi_ot']) ? $bmi['total_bmi_ot'] : 0);
            $sheet->setCellValueByColumnAndRow(10, $row, isset($bmi['total_bmi_ot_sunday']) ? $bmi['total_bmi_ot_sunday'] : 0);
            $sheet->setCellValueByColumnAndRow(11, $row, isset($bmi['total_bmi_ph_1']) ? $bmi['total_bmi_ph_1'] : 0);
            $sheet->setCellValueByColumnAndRow(12, $row, isset($bmi['total_bmi_ph_2']) ? $bmi['total_bmi_ph_2'] : 0);
            $sheet->setCellValueByColumnAndRow(13, $row, isset($bmi['total_bmi_shift1']) ? $bmi['total_bmi_shift1'] : 0);
            $sheet->setCellValueByColumnAndRow(14, $row, isset($bmi['total_bmi_shift2']) ? $bmi['total_bmi_shift2'] : 0);
            $sheet->setCellValueByColumnAndRow(15, $row, isset($bmi['total_bmi_shift3']) ? $bmi['total_bmi_shift3'] : 0);
            $sheet->setCellValueByColumnAndRow(17, $row, isset($bmi['total_bmi_ta']) ? $bmi['total_bmi_ta'] : 0);
            $sheet->setCellValueByColumnAndRow(18, $row, isset($bmi['total_bmi_ma']) ? $bmi['total_bmi_ma'] : 0);
            $sheet->setCellValueByColumnAndRow(19, $row, isset($bmi['total_bmi_ca']) ? $bmi['total_bmi_ca'] : 0);

            $count++;
            $row++;
        }

        $xlsx_name = '(' . $branch_name . ') BMI Short Summary - ' . $first_day . ' to ' . $last_day . ' ' . time() . '.xlsx';
        $xlsx_path = FCPATH . 'uploads/summary/' . $xlsx_name;
        $writer = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
        $writer->save($xlsx_path);

        $zip_name = '(' . $branch_name . ') BMI Short Summary - ' . $first_day . ' to ' . $last_day . ' ' . time() . '.zip';
        $zip_path = FCPATH . 'uploads/summary/' . $zip_name;
        $zip = new ZipArchive();
        if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Failed to create BMI short summary ZIP');
        }
        $zip->addFile($xlsx_path, basename($xlsx_path));
        $zip->close();
        @unlink($xlsx_path);

        return array(
            'status' => 'success',
            'file_path' => $zip_path,
            'file_name' => $zip_name,
            'summary' => array(
                'branch' => $branch_name,
                'period' => array(
                    'from' => $from_f,
                    'to' => $to_f
                ),
                'employee_count' => count($all_data),
                'file_type' => 'zip',
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => 'Queue Worker'
            )
        );
    }
}
