<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Daily_time_card_report_handler
{
    private $ci;

    public function __construct()
    {
        $this->ci = get_instance();
    }

    public function generate($all_data, $branch_name, $first_day, $file_type)
    {
        $date = DateTime::createFromFormat('Y-m-d', $first_day);
        if (!$date) {
            throw new Exception('Invalid date for Daily Time Card report');
        }

        $actual_date = $date->format('d/m/Y');
        $first_day_name = $date->format('l');
        $time_card_date = $date->format('mdY');

        if ($file_type === 'pdf') {
            $this->ci->load->library('dompdf_lib');

            $html = $this->ci->load->view('exports/daily_time_card', array(
                'all_data' => $all_data,
                'actual_date' => $actual_date,
                'day_name' => $first_day_name
            ), true);

            $this->ci->dompdf_lib->reset();
            $this->ci->dompdf_lib->loadHtml($html);
            $this->ci->dompdf_lib->setPaper('A4', 'landscape');
            $this->ci->dompdf_lib->render();

            $output = $this->ci->dompdf_lib->output();
            $file_name = str_replace('/', '_', $branch_name . ' - ' . $time_card_date . '.pdf');
            $file_path = FCPATH . 'uploads/summary/' . $file_name;
            file_put_contents($file_path, $output);
        } else {
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

            $object->getActiveSheet()->setCellValueByColumnAndRow(0, 1, 'Date');
            $object->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()->setBold(true);
            $object->getActiveSheet()->setCellValueByColumnAndRow(1, 1, $actual_date);

            $object->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'Day Name');
            $object->getActiveSheet()->getStyleByColumnAndRow(2, 1)->getFont()->setBold(true);
            $object->getActiveSheet()->setCellValueByColumnAndRow(3, 1, $first_day_name);

            $table_columns = array('Employee', 'Shift', 'Time In', 'Time Out', 'Work', 'OT1', 'OT2', 'OT3', 'Total', 'Break', 'Late', 'Early', 'Attend', 'Absent', 'Offday', 'Leave', 'Holiday');
            $column = 0;
            foreach ($table_columns as $field) {
                $object->getActiveSheet()->setCellValueByColumnAndRow($column, 3, $field);
                $object->getActiveSheet()->getStyleByColumnAndRow($column, 3)->getFont()->setBold(true);
                $column++;
            }

            $row = 4;
            foreach ($all_data as $entry) {
                if (empty($entry['dates']) || !isset($entry['dates'][0])) {
                    continue;
                }

                $shift_data = $entry['dates'][0];
                $public_holidays = isset($entry['public_holidays']) ? $entry['public_holidays'] : array();
                $rest_days = isset($entry['rest_days']) ? $entry['rest_days'] : array();

                $object->getActiveSheet()->setCellValueByColumnAndRow(0, $row, $entry['employee']->special_id . ' - ' . $entry['employee']->first_name);
                $object->getActiveSheet()->setCellValueByColumnAndRow(1, $row, !empty($shift_data->shift_name) ? $shift_data->shift_name : '-');
                $object->getActiveSheet()->setCellValueByColumnAndRow(2, $row, isset($shift_data->first_in) ? $shift_data->first_in : '');
                $object->getActiveSheet()->setCellValueByColumnAndRow(3, $row, isset($shift_data->last_out) ? $shift_data->last_out : '');
                $object->getActiveSheet()->setCellValueByColumnAndRow(4, $row, time_placeholder(isset($shift_data->work_hours) ? $shift_data->work_hours : null));

                $ot1 = '';
                $ot2 = '';
                $ot3 = '';
                if (!empty($shift_data->is_ot)) {
                    if (!in_array($shift_data->day_name, $rest_days) && !in_array($shift_data->date, $public_holidays)) {
                        $ot1 = add_time_minus($shift_data->overtime, $shift_data->overtime_m);
                    } else if (in_array($shift_data->day_name, $rest_days)) {
                        $ot2 = add_time_minus($shift_data->overtime, $shift_data->overtime_m);
                    } else if (in_array($shift_data->date, $public_holidays)) {
                        $ot3 = add_time_minus($shift_data->overtime, $shift_data->overtime_m);
                    }
                }

                $object->getActiveSheet()->setCellValueByColumnAndRow(5, $row, time_placeholder($ot1));
                $object->getActiveSheet()->setCellValueByColumnAndRow(6, $row, time_placeholder($ot2));
                $object->getActiveSheet()->setCellValueByColumnAndRow(7, $row, time_placeholder($ot3));
                $object->getActiveSheet()->setCellValueByColumnAndRow(8, $row, time_placeholder(isset($shift_data->total_hours) ? $shift_data->total_hours : null));
                $object->getActiveSheet()->setCellValueByColumnAndRow(9, $row, time_placeholder(isset($shift_data->break_hours) ? $shift_data->break_hours : null));
                $object->getActiveSheet()->setCellValueByColumnAndRow(10, $row, time_placeholder(isset($shift_data->late_in) ? $shift_data->late_in : null));
                $object->getActiveSheet()->setCellValueByColumnAndRow(11, $row, time_placeholder(isset($shift_data->early_out) ? $shift_data->early_out : null));

                $rest_day = (in_array($shift_data->day_name, $rest_days) || empty($shift_data->shift_name)) ? 1 : 0;
                $holiday = in_array($shift_data->date, $public_holidays) ? 1 : 0;
                $attend = (!empty($shift_data->first_in) && !empty($shift_data->last_out)) ? 1 : 0;
                $absent = (!$rest_day && !$holiday && empty($shift_data->first_in) && empty($shift_data->last_out)) ? 1 : 0;

                $object->getActiveSheet()->setCellValueByColumnAndRow(12, $row, $attend ? $attend : '-');
                $object->getActiveSheet()->setCellValueByColumnAndRow(13, $row, $absent ? $absent : '-');
                $object->getActiveSheet()->setCellValueByColumnAndRow(14, $row, $rest_day ? $rest_day : '-');
                $object->getActiveSheet()->setCellValueByColumnAndRow(15, $row, 0.0);
                $object->getActiveSheet()->setCellValueByColumnAndRow(16, $row, $holiday ? $holiday : '-');

                $row++;
            }

            foreach (range('A', 'Q') as $columnID) {
                $object->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
            }

            if ($file_type === 'excel' || $file_type === 'xls') {
                $file_name = '(' . $branch_name . ') Daily Time Card - ' . $first_day . ' ' . time() . '.xls';
                $object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
            } else {
                $file_name = '(' . $branch_name . ') Daily Time Card - ' . $first_day . ' ' . time() . '.xlsx';
                $object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
            }

            $file_path = FCPATH . 'uploads/summary/' . $file_name;
            $object_writer->save($file_path);
        }

        return array(
            'status' => 'success',
            'file_path' => $file_path,
            'file_name' => $file_name,
            'summary' => array(
                'branch' => $branch_name,
                'period' => array(
                    'from' => $actual_date,
                    'to' => $actual_date
                ),
                'employee_count' => count($all_data),
                'file_type' => $file_type,
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => 'Queue Worker'
            )
        );
    }
}
