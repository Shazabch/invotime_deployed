<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Alya01CustomReportService
{
    private $CI;

    private function ensureSummaryDirectory()
    {
        $directory = FCPATH . 'uploads/summary/';
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return $directory;
    }

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    public function generate(array $context)
    {
        $employees = $context['employees'];
        $first_day = $context['first_day'];
        $last_day = $context['last_day'];
        $branch_name = $context['branch_name'];
        $current_user = $context['current_user'];
        $file_type = $context['file_type'];
        $today = date('Y-m-d');
        $effective_last_day = min($last_day, $today);

        $employee_ids = array_map(function ($emp) {
            return $emp->id;
        }, $employees);

        if (empty($employee_ids)) {
            throw new Exception('No employees found for selected filters.');
        }

        $employee_ids_str = implode(',', $employee_ids);
        $query = "SELECT
                clockings_news.*,
                employees.first_name,
                employees.last_name,
                employees.special_id,
                branches.name as branch_name,
                shifts.name as shift_name,
                branches1.name as branch_name_clocking
            FROM clockings_news
            INNER JOIN employees ON clockings_news.employee_id = employees.id
            INNER JOIN roles ON employees.role_id = roles.id
            LEFT JOIN branches ON employees.branch_id = branches.id
            LEFT JOIN devices ON clockings_news.device_id = devices.device_id
            LEFT JOIN shifts ON clockings_news.shift_id = shifts.id
            LEFT JOIN branches branches1 ON branches1.id = devices.branch_id
            WHERE roles.exclude_from_system = 'no'
            AND clockings_news.deleted_at IS NULL
            AND clockings_news.employee_id IN ($employee_ids_str)
            AND DATE(clockings_news.datetime) BETWEEN '$first_day' AND '$effective_last_day'
            ORDER BY employees.first_name, employees.last_name, clockings_news.datetime";

        $clocking_data = $this->CI->db->query($query)->result_array();
        $grouped_data = [];
        $total_clockings = count($clocking_data);

        $employee_day_meta = [];
        foreach ($employees as $employee) {
            $employee_id = (int)$employee->id;
            $branch_id = isset($employee->branch_id) ? $employee->branch_id : false;

            $shift_map = [];
            if (function_exists('get_shift_list')) {
                $shift_list = get_shift_list($employee_id, $first_day, $effective_last_day);
                foreach ($shift_list as $shift) {
                    if (isset($shift->date)) {
                        $shift_map[$shift->date] = $shift;
                    }
                }
            }

            $holiday_map = [];
            if (function_exists('get_public_holidays_mine')) {
                $holiday_dates = get_public_holidays_mine($employee_id, $branch_id, $first_day, $effective_last_day);
                foreach ($holiday_dates as $holiday_date) {
                    $holiday_map[$holiday_date] = true;
                }
            }

            $replacement_from = [];
            $replacement_to = [];
            if (function_exists('get_replacement_leaves_list')) {
                $replacement_leaves = get_replacement_leaves_list($employee_id, $first_day, $effective_last_day);
                foreach ($replacement_leaves as $replacement) {
                    if (!empty($replacement->from)) {
                        $replacement_from[$replacement->from] = true;
                    }
                    if (!empty($replacement->to)) {
                        $replacement_to[$replacement->to] = true;
                    }
                }
            }

            $employee_day_meta[$employee_id] = [
                'shift_map' => $shift_map,
                'holiday_map' => $holiday_map,
                'replacement_from' => $replacement_from,
                'replacement_to' => $replacement_to,
            ];
        }

        $emp_clockings = [];
        foreach ($clocking_data as $i => $clocking) {
            $emp_clockings[$clocking['employee_id']][] = $clocking;

            if ($total_clockings > 0) {
                $percentage = floor((($i + 1) / $total_clockings) * 100);
                echo "<script>$('#loading1 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
                ob_flush();
                flush();
            }
        }

        foreach ($emp_clockings as $emp_id => $clockings) {
            $current_pair = null;
            $pairs = [];
            foreach ($clockings as $c) {
                $type = strtolower($c['type']);
                if ($type === 'in') {
                    if ($current_pair && $current_pair['in']) {
                        $pairs[] = $current_pair;
                    }
                    $current_pair = ['in' => $c, 'out' => null];
                } else if ($type === 'out') {
                    if ($current_pair && !$current_pair['out']) {
                        $current_pair['out'] = $c;
                        $pairs[] = $current_pair;
                        $current_pair = null;
                    } else {
                        $pairs[] = ['in' => null, 'out' => $c];
                    }
                }
            }
            if ($current_pair) {
                $pairs[] = $current_pair;
            }

            foreach ($pairs as $p) {
                $ref_c = $p['in'] ? $p['in'] : $p['out'];
                $emp_key = $ref_c['employee_id'];
                $date_key = date('Y-m-d', strtotime($ref_c['datetime']));
                $key = $emp_key . '_' . $date_key;

                if (!isset($grouped_data[$key])) {
                    $grouped_data[$key] = [
                        'employee_id' => $ref_c['employee_id'],
                        'employee_name' => trim($ref_c['first_name'] . ' ' . $ref_c['last_name']),
                        'special_id' => $ref_c['special_id'],
                        'branch_name' => $ref_c['branch_name'],
                        'date' => $date_key,
                        'shift_name' => $ref_c['shift_name'],
                        'pairs' => []
                    ];
                }

                $in_time = $p['in'] ? date('H:i', strtotime($p['in']['datetime'])) : '';
                $out_time = $p['out'] ? date('H:i', strtotime($p['out']['datetime'])) : '';
                $in_remark = $p['in'] ? trim($p['in']['clocking_remark'] ?? '') : '';
                $out_remark = $p['out'] ? trim($p['out']['clocking_remark'] ?? '') : '';

                $grouped_data[$key]['pairs'][] = [
                    'in_time' => $in_time,
                    'out_time' => $out_time,
                    'in_remark' => $in_remark,
                    'out_remark' => $out_remark,
                ];
            }
        }

        if ($total_clockings === 0) {
            echo "<script>$('#loading1 .progress-bar').css('width', '100%').attr('aria-valuenow', 100).html('100%');</script>";
            ob_flush();
            flush();
        }

        $date = DateTime::createFromFormat('Y-m-d', $first_day);
        $data['from_f'] = $date->format('d/m/Y');
        $date = DateTime::createFromFormat('Y-m-d', $effective_last_day);
        $data['to_f'] = $date->format('d/m/Y');
        $data['branch_name'] = $branch_name;
        $data['generated_at'] = date('d/m/Y H:i:s');
        $data['generated_by'] = $current_user['first_name'];

        $period_dates = [];
        $period = new DatePeriod(
            new DateTime($first_day),
            new DateInterval('P1D'),
            (new DateTime($effective_last_day))->add(new DateInterval('P1D'))
        );
        foreach ($period as $dt) {
            $period_dates[] = $dt->format('Y-m-d');
        }

        $employee_sections = [];
        foreach ($employees as $employee) {
            $employee_id = (int)$employee->id;
            $section_key = trim(($employee->special_id ?? '') . ' - ' . trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? '')));
            if ($section_key === '-' || $section_key === '') {
                $section_key = 'Emp ID - Employee Name';
            }

            $section_rows = [];

            foreach ($period_dates as $date_key) {
                $meta = isset($employee_day_meta[$employee_id]) ? $employee_day_meta[$employee_id] : [];
                $shift = isset($meta['shift_map'][$date_key]) ? $meta['shift_map'][$date_key] : null;
                $is_holiday = isset($meta['holiday_map'][$date_key]);
                $is_replacement_from = isset($meta['replacement_from'][$date_key]);
                $is_replacement_to = isset($meta['replacement_to'][$date_key]);
                $is_leave_day = ($shift && isset($shift->is_leave) && strtolower((string)$shift->is_leave) === 'yes') || $is_replacement_to;
                // $include_without_clocking = $is_leave_day || $is_holiday;
                $include_without_clocking = $is_leave_day;

                $group_key = $employee_id . '_' . $date_key;
                $group = isset($grouped_data[$group_key]) ? $grouped_data[$group_key] : null;

                // Keep leave/public holiday rows in the report even without clockings.
                if (!$group && !$include_without_clocking) {
                    continue;
                }

                $shift_label = $group['shift_name'] ?? '';
                if ($shift && !empty($shift->name)) {
                    $shift_label = $shift->name;
                }

                $day_type = 'Work Day';
                $leave_label = '';

                if ($shift && isset($shift->is_leave) && strtolower((string)$shift->is_leave) === 'yes') {
                    $day_type = 'Leave';
                    $leave_label = $shift_label;
                } elseif ($is_holiday) {
                    $day_type = 'Public Holiday';
                } elseif ($shift && isset($shift->is_rest_day) && strtolower((string)$shift->is_rest_day) === 'yes') {
                    $day_type = 'Rest Day';
                }

                if ($is_replacement_from && $day_type === 'Work Day') {
                    $day_type = 'Replacement Day';
                }

                if ($is_replacement_to && $leave_label === '') {
                    $leave_label = 'Replacement Leave';
                    if ($day_type === 'Work Day') {
                        $day_type = 'Leave';
                    }
                }

                $pairs = $group && isset($group['pairs']) ? $group['pairs'] : [];

                $section_rows[] = [
                    'date' => date('d/m/Y', strtotime($date_key)),
                    'day_type' => $day_type,
                    'shift' => $shift_label,
                    'leave' => $leave_label,
                    'pairs' => $pairs,
                ];
            }

            if (!empty($section_rows)) {
                $employee_sections[$section_key] = $section_rows;
            }
        }

        foreach ($employee_sections as &$section_dates) {
            usort($section_dates, function ($a, $b) {
                $ad = DateTime::createFromFormat('d/m/Y', $a['date']);
                $bd = DateTime::createFromFormat('d/m/Y', $b['date']);
                if (!$ad || !$bd) return 0;
                return $ad <=> $bd;
            });
        }
        unset($section_dates);

        if ($file_type == 'pdf') {
            $html2 = $this->CI->load->view('alya01_custom_report_pdf', [
                'employee_sections' => $employee_sections,
                'branch_name' => $data['branch_name'],
                'from_f' => $data['from_f'],
                'to_f' => $data['to_f'],
                'generated_at' => $data['generated_at'],
                'generated_by' => $data['generated_by'],
            ], true);

            $file_name = "($branch_name) ALYA01 Custom Report - $first_day to $effective_last_day " . time() . '.pdf';

            $this->CI->dompdf->reset();
            $this->CI->dompdf->loadHtml($html2);
            $this->CI->dompdf->setPaper('A4', 'landscape');
            $this->CI->dompdf->render();

            $output = $this->CI->dompdf->output();
            $new_file = 'uploads/summary/' . $file_name;
            $absolute_file = $this->ensureSummaryDirectory() . $file_name;
            if (file_put_contents($absolute_file, $output) === false) {
                throw new Exception('Unable to generate PDF file.');
            }

            echo "<script>$('#loading2 .progress-bar').css('width', '100%').attr('aria-valuenow', 100).html('100%');</script>";
            ob_flush();
            flush();
        } else {
            $object = new PHPExcel();
            $object->setActiveSheetIndex(0);
            $sheet = $object->getActiveSheet();
            $sheet->setTitle('Sheet1');

            $sheet->getDefaultStyle()->getFont()->setName('Aptos Narrow')->setSize(10);
            $sheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('A1', 'Date');
            $sheet->setCellValue('B1', 'Day Type');
            $sheet->setCellValue('C1', 'Shift');
            $sheet->setCellValue('D1', 'Leave');
            $sheet->setCellValue('E1', 'Clockings');
            $sheet->setCellValue('G1', 'Remarks');
            $sheet->setCellValue('E2', 'IN');
            $sheet->setCellValue('F2', 'Out');
            $sheet->setCellValue('G2', 'IN Remark');
            $sheet->setCellValue('H2', 'Out Remark');

            $sheet->mergeCells('A1:A2');
            $sheet->mergeCells('B1:B2');
            $sheet->mergeCells('C1:C2');
            $sheet->mergeCells('D1:D2');
            $sheet->mergeCells('E1:F1');
            $sheet->mergeCells('G1:H1');

            $sheet->getStyle('A1:H2')->getFont()->setName('Arial Narrow')->setSize(8)->setBold(false);
            $sheet->getStyle('A1:H2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:H2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A1:H2')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
            $sheet->getStyle('A1:H2')->getBorders()->getAllBorders()->getColor()->setRGB('BFBFBF');
            $sheet->getRowDimension(1)->setRowHeight(18);
            $sheet->getRowDimension(2)->setRowHeight(18);

            $sheet->getColumnDimension('A')->setWidth(10);
            $sheet->getColumnDimension('B')->setWidth(10);
            $sheet->getColumnDimension('C')->setWidth(8);
            $sheet->getColumnDimension('D')->setWidth(6);
            $sheet->getColumnDimension('E')->setWidth(8);
            $sheet->getColumnDimension('F')->setWidth(8);
            $sheet->getColumnDimension('G')->setWidth(30);
            $sheet->getColumnDimension('H')->setWidth(30);

            $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);
            $sheet->setShowGridLines(true);
            $sheet->freezePane('A3');

            $row = 3;
            $sections_count = count($employee_sections);
            $section_index = 0;
            foreach ($employee_sections as $employee_label => $date_rows) {
                $sheet->setCellValue('A' . $row, $employee_label);
                $sheet->mergeCells('A' . $row . ':H' . $row);
                $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setUnderline(true);
                $row++;

                foreach ($date_rows as $date_row) {
                    $pairs = $date_row['pairs'];
                    if (empty($pairs)) {
                        $pairs = [['in_time' => '', 'out_time' => '', 'in_remark' => '', 'out_remark' => '']];
                    }

                    $first_pair_row = $row;
                    $last_pair_row = $row + count($pairs) - 1;

                    foreach ($pairs as $pair_index => $pair) {
                        if ($pair_index === 0) {
                            $sheet->setCellValueByColumnAndRow(0, $row, $date_row['date']);
                            $sheet->setCellValueByColumnAndRow(1, $row, $date_row['day_type']);
                            $sheet->setCellValueByColumnAndRow(2, $row, $date_row['shift']);
                            $sheet->setCellValueByColumnAndRow(3, $row, $date_row['leave']);
                        }
                        $sheet->setCellValueByColumnAndRow(4, $row, $pair['in_time']);
                        $sheet->setCellValueByColumnAndRow(5, $row, $pair['out_time']);
                        $sheet->setCellValueByColumnAndRow(6, $row, $pair['in_remark']);
                        $sheet->setCellValueByColumnAndRow(7, $row, $pair['out_remark']);
                        $sheet->getStyleByColumnAndRow(6, $row)->getAlignment()->setWrapText(true);
                        $sheet->getStyleByColumnAndRow(7, $row)->getAlignment()->setWrapText(true);
                        $sheet->getStyleByColumnAndRow(6, $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                        $sheet->getStyleByColumnAndRow(7, $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                        $sheet->getStyleByColumnAndRow(0, $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                        $sheet->getStyleByColumnAndRow(1, $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                        $sheet->getStyleByColumnAndRow(2, $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                        $sheet->getStyleByColumnAndRow(3, $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                        $sheet->getStyleByColumnAndRow(4, $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                        $sheet->getStyleByColumnAndRow(5, $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
                        $sheet->getStyleByColumnAndRow(6, $row)->getFont()->setName('Arial Narrow')->setSize(8);
                        $sheet->getStyleByColumnAndRow(7, $row)->getFont()->setName('Arial Narrow')->setSize(8);
                        $remark_lines = 1;
                        foreach (array($pair['in_remark'], $pair['out_remark']) as $remark_text) {
                            $remark_text = trim((string) $remark_text);
                            if ($remark_text === '') {
                                continue;
                            }
                            $line_count = substr_count($remark_text, "\n") + 1;
                            $remark_lines = max($remark_lines, $line_count, (int) ceil(strlen($remark_text) / 32));
                        }
                        $sheet->getRowDimension($row)->setRowHeight(max(18, $remark_lines * 14));

                        $sheet->getStyle('A' . $row . ':H' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('G' . $row . ':H' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                        $row++;
                    }

                    $sheet->getStyle('A' . $first_pair_row . ':H' . $last_pair_row)
                        ->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                    $sheet->getStyle('A' . $first_pair_row . ':H' . $last_pair_row)
                        ->getBorders()->getAllBorders()->getColor()->setRGB('C0C0C0');
                    if ($first_pair_row > 3) {
                        $sheet->getStyle('A' . $first_pair_row . ':H' . $first_pair_row)
                            ->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
                        $sheet->getStyle('A' . $first_pair_row . ':H' . $first_pair_row)
                            ->getBorders()->getTop()->getColor()->setRGB('C0C0C0');
                    }
                }

                $section_index++;
                if ($section_index < $sections_count) {
                    $row++;
                }

                $percentage = $sections_count > 0 ? floor(($section_index / $sections_count) * 100) : 100;
                echo "<script>$('#loading2 .progress-bar').css('width', '" . $percentage . "%').attr('aria-valuenow', " . $percentage . ").html('" . $percentage . "%');</script>";
                ob_flush();
                flush();
            }

            if ($file_type == 'excel') {
                $file_name = "($branch_name) ALYA01 Custom Report - $first_day to $effective_last_day " . time() . '.xls';
                $object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel5');
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment;filename="' . $file_name . '"');
                $new_file = 'uploads/summary/' . $file_name;
                $absolute_file = $this->ensureSummaryDirectory() . $file_name;
                $object_writer->save($absolute_file);
            } else {
                $file_name = "($branch_name) ALYA01 Custom Report - $first_day to $effective_last_day " . time() . '.xlsx';
                $object_writer = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment;filename="' . $file_name . '"');
                $new_file = 'uploads/summary/' . $file_name;
                $absolute_file = $this->ensureSummaryDirectory() . $file_name;
                $object_writer->save($absolute_file);
            }
        }

        return [
            'new_file' => $new_file,
            'file_name' => $file_name,
        ];
    }
}
