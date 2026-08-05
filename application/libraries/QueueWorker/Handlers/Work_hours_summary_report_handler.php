<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Work_hours_summary_report_handler
{
    private $ci;

    public function __construct()
    {
        $this->ci = get_instance();
    }

    public function generate($all_data, $branch_name, $first_day, $last_day, $file_type)
    {
        $this->ci->load->library('excel');

        $object = PHPExcel_IOFactory::load(FCPATH . 'assets/work-hours-summary.xlsx');
        $object->setActiveSheetIndex(0);

        $active_sheet = $object->getActiveSheet();
        $active_sheet->setCellValueByColumnAndRow(1, 1, $branch_name);
        $active_sheet->setCellValueByColumnAndRow(1, 2, $first_day . ' to ' . $last_day);
        $active_sheet->setCellValueByColumnAndRow(1, 3, date('Y-m-d H:i:s'));

        $period = new DatePeriod(
            new DateTime($first_day),
            new DateInterval('P1D'),
            (new DateTime($last_day))->add(new DateInterval('P1D'))
        );

        $dates = array();
        foreach ($period as $value) {
            $dates[] = $value->format('j');
        }

        $dates_count = count($dates);
        if ($dates_count < 30) {
            $active_sheet->removeColumnByIndex(4, 30 - $dates_count);
        } else if ($dates_count > 30) {
            $active_sheet->insertNewColumnBefore('AH', $dates_count - 30);
        }

        $active_sheet->setCellValueByColumnAndRow(4, 4, 'Calendar Days');

        $row = 5;
        $column = 4;
        foreach ($dates as $date) {
            $active_sheet->setCellValueByColumnAndRow($column++, $row, $date);
        }

        $row = 6;
        foreach ($all_data as $entry) {
            $active_sheet->insertNewRowBefore($row + 1, 1);
            $active_sheet->setCellValueByColumnAndRow(0, $row, $entry['employee']->special_id);
            $active_sheet->setCellValueByColumnAndRow(1, $row, $entry['employee']->first_name);
            $active_sheet->setCellValueByColumnAndRow(2, $row, $entry['employee']->position);
            $active_sheet->setCellValueByColumnAndRow(3, $row, $entry['employee']->department);

            $column = 4;
            if (!empty($entry['dates'])) {
                foreach ($entry['dates'] as $date_entry) {
                    $work_hours_whole = isset($date_entry->work_hours_whole) ? $date_entry->work_hours_whole : 0;
                    $active_sheet->setCellValueByColumnAndRow($column++, $row, $work_hours_whole == 0 ? '' : $work_hours_whole);
                }
            }

            $row++;
        }

        $active_sheet->removeRow($row, 1);

        $column = 4;
        foreach ($dates as $date) {
            $active_sheet->setCellValueByColumnAndRow(
                $column,
                $row,
                '=SUM(' . PHPExcel_Cell::stringFromColumnIndex($column) . '6:' . PHPExcel_Cell::stringFromColumnIndex($column) . ($row - 1) . ')'
            );
            $column++;
        }

        foreach (range('A', 'D') as $columnID) {
            $active_sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        if ($file_type === 'excel' || $file_type === 'xls') {
            $file_name = '(' . $branch_name . ') Work Hours Summary - ' . $first_day . ' - ' . $last_day . ' ' . time() . '.xls';
            $writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
        } else {
            $file_name = '(' . $branch_name . ') Work Hours Summary - ' . $first_day . ' - ' . $last_day . ' ' . time() . '.xlsx';
            $writer = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
        }

        $file_path = FCPATH . 'uploads/summary/' . $file_name;
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
                'file_type' => $file_type,
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => 'Queue Worker'
            )
        );
    }
}
