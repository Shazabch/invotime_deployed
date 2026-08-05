<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Mcb01_clocking_report_handler
{
    private $ci;

    public function __construct()
    {
        $this->ci = get_instance();
    }

    public function generate($all_data, $branch_name, $first_day, $last_day, $file_type)
    {
        $rows = $this->flatten_rows($all_data);

        if ($file_type === 'pdf') {
            return $this->generate_pdf($rows, $branch_name, $first_day, $last_day);
        }

        return $this->generate_excel($rows, $branch_name, $first_day, $last_day, $file_type);
    }

    private function flatten_rows($all_data)
    {
        $rows = array();

        foreach ($all_data as $entry) {
            $employee = isset($entry['employee']) ? $entry['employee'] : (object) array();
            $employee_id = isset($employee->special_id) ? $employee->special_id : '';
            $employee_name = isset($employee->first_name) ? $employee->first_name : '';

            if (empty($entry['dates'])) {
                continue;
            }

            foreach ($entry['dates'] as $day) {
                $date_value = isset($day->date) ? $day->date : '';
                $shift_name = isset($day->shift_name) ? $day->shift_name : '';
                $clockings = isset($day->clockings) && is_array($day->clockings) ? $day->clockings : array();

                if (empty($clockings)) {
                    $rows[] = array(
                        'employee_id' => $employee_id,
                        'employee_name' => $employee_name,
                        'date' => $date_value,
                        'shift' => $shift_name,
                        'clock_in' => isset($day->first_in) ? $day->first_in : '',
                        'clock_out' => isset($day->last_out) ? $day->last_out : '',
                        'type' => '',
                        'datetime' => '',
                        'mode' => '',
                        'location' => '',
                        'remark' => ''
                    );
                    continue;
                }

                foreach ($clockings as $clock) {
                    $rows[] = array(
                        'employee_id' => $employee_id,
                        'employee_name' => $employee_name,
                        'date' => $date_value,
                        'shift' => $shift_name,
                        'clock_in' => isset($clock->clock_in) ? $clock->clock_in : '',
                        'clock_out' => isset($clock->clock_out) ? $clock->clock_out : '',
                        'type' => isset($clock->type) ? $clock->type : '',
                        'datetime' => isset($clock->datetime) ? $clock->datetime : '',
                        'mode' => isset($clock->mode) ? $clock->mode : '',
                        'location' => isset($clock->clocking_location) ? $clock->clocking_location : '',
                        'remark' => isset($clock->remark) ? $clock->remark : ''
                    );
                }
            }
        }

        return $rows;
    }

    private function generate_pdf($rows, $branch_name, $first_day, $last_day)
    {
        $this->ci->load->library('dompdf_lib');

        $html = '<html><head><style>body{font-family:DejaVu Sans,sans-serif;font-size:10px;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #999;padding:4px;} th{background:#f3f3f3;}</style></head><body>';
        $html .= '<h3>(' . htmlspecialchars($branch_name) . ') Clocking Records - ' . htmlspecialchars($first_day) . ' to ' . htmlspecialchars($last_day) . '</h3>';
        $html .= '<table><thead><tr>';
        $headers = array('Emp ID', 'Employee', 'Date', 'Shift', 'Clock In', 'Clock Out', 'Type', 'Datetime', 'Mode', 'Location', 'Remark');
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars((string)$row['employee_id']) . '</td>';
            $html .= '<td>' . htmlspecialchars((string)$row['employee_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars((string)$row['date']) . '</td>';
            $html .= '<td>' . htmlspecialchars((string)$row['shift']) . '</td>';
            $html .= '<td>' . htmlspecialchars((string)$row['clock_in']) . '</td>';
            $html .= '<td>' . htmlspecialchars((string)$row['clock_out']) . '</td>';
            $html .= '<td>' . htmlspecialchars((string)$row['type']) . '</td>';
            $html .= '<td>' . htmlspecialchars((string)$row['datetime']) . '</td>';
            $html .= '<td>' . htmlspecialchars((string)$row['mode']) . '</td>';
            $html .= '<td>' . htmlspecialchars((string)$row['location']) . '</td>';
            $html .= '<td>' . htmlspecialchars((string)$row['remark']) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        $this->ci->dompdf_lib->reset();
        $this->ci->dompdf_lib->loadHtml($html);
        $this->ci->dompdf_lib->setPaper('A4', 'landscape');
        $this->ci->dompdf_lib->render();

        $file_name = '(' . $branch_name . ') Clocking Records - ' . $first_day . ' to ' . $last_day . ' ' . time() . '.pdf';
        $file_path = FCPATH . 'uploads/summary/' . $file_name;
        file_put_contents($file_path, $this->ci->dompdf_lib->output());

        return array(
            'status' => 'success',
            'file_path' => $file_path,
            'file_name' => $file_name,
            'summary' => array(
                'branch' => $branch_name,
                'period' => array('from' => $first_day, 'to' => $last_day),
                'employee_count' => count($rows),
                'file_type' => 'pdf',
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => 'Queue Worker'
            )
        );
    }

    private function generate_excel($rows, $branch_name, $first_day, $last_day, $file_type)
    {
        $this->ci->load->library('excel');

        $object = new PHPExcel();
        $object->setActiveSheetIndex(0);
        $sheet = $object->getActiveSheet();

        $headers = array('Emp ID', 'Employee', 'Date', 'Shift', 'Clock In', 'Clock Out', 'Type', 'Datetime', 'Mode', 'Location', 'Remark');
        $column = 0;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($column, 1, $header);
            $sheet->getStyleByColumnAndRow($column, 1)->getFont()->setBold(true);
            $column++;
        }

        $row_number = 2;
        foreach ($rows as $row) {
            $sheet->setCellValueByColumnAndRow(0, $row_number, $row['employee_id']);
            $sheet->setCellValueByColumnAndRow(1, $row_number, $row['employee_name']);
            $sheet->setCellValueByColumnAndRow(2, $row_number, $row['date']);
            $sheet->setCellValueByColumnAndRow(3, $row_number, $row['shift']);
            $sheet->setCellValueByColumnAndRow(4, $row_number, $row['clock_in']);
            $sheet->setCellValueByColumnAndRow(5, $row_number, $row['clock_out']);
            $sheet->setCellValueByColumnAndRow(6, $row_number, $row['type']);
            $sheet->setCellValueByColumnAndRow(7, $row_number, $row['datetime']);
            $sheet->setCellValueByColumnAndRow(8, $row_number, $row['mode']);
            $sheet->setCellValueByColumnAndRow(9, $row_number, $row['location']);
            $sheet->setCellValueByColumnAndRow(10, $row_number, $row['remark']);
            $row_number++;
        }

        foreach (range('A', 'K') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        if ($file_type === 'excel' || $file_type === 'xls') {
            $extension = 'xls';
            $writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
        } else {
            $extension = 'xlsx';
            $writer = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
        }

        $file_name = '(' . $branch_name . ') Clocking Records - ' . $first_day . ' to ' . $last_day . ' ' . time() . '.' . $extension;
        $file_path = FCPATH . 'uploads/summary/' . $file_name;
        $writer->save($file_path);

        return array(
            'status' => 'success',
            'file_path' => $file_path,
            'file_name' => $file_name,
            'summary' => array(
                'branch' => $branch_name,
                'period' => array('from' => $first_day, 'to' => $last_day),
                'employee_count' => count($rows),
                'file_type' => $extension,
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => 'Queue Worker'
            )
        );
    }
}
