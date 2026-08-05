<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gni01_payroll_process_report_handler
{
    private $ci;

    public function __construct()
    {
        $this->ci = get_instance();
    }

    public function generate($cid, $branch_name, $first_day, $last_day, $first_day_formatted, $last_day_formatted, $all_short_data, $all_short_ot_data, $all_sql_data, $all_sql_ot_data)
    {
        $this->ci->load->library('excel');
        $this->ci->load->library('zip');
        $this->ci->load->library('dompdf_lib');

        require_once APPPATH . 'controllers/Exports.php';
        $ref = new ReflectionClass('Exports');
        $exports = $ref->newInstanceWithoutConstructor();

        $style = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
            )
        );

        $date2 = DateTime::createFromFormat('Y-m-d', $last_day);
        if (!$date2) {
            throw new Exception('Invalid end date for GNI01 report');
        }

        $files = array();

        // Reuse Exports helper generators where possible.
        $files[] = $exports->pendingOvertimeLogFile($style, $all_sql_ot_data, $date2, $branch_name, $first_day, $last_day, true, $all_short_ot_data);
        $files[] = $exports->pendingEarlyLateLogFile($style, $all_sql_data, $date2, $branch_name, $first_day, $last_day);
        $files[] = $exports->pendingWorkedHoursFile($style, $all_sql_data, $date2, $branch_name, $first_day, $last_day);
        $files[] = $exports->pendingShiftWorkedHoursFile($style, $all_sql_data, $date2, $branch_name, $first_day, $last_day);
        $files[] = $exports->otBalanceSheet($style, $all_short_data, $all_short_ot_data, $branch_name, $first_day, $last_day);

        // Add short summary PDF similar to sync GNI flow.
        $summary_data = array(
            'short_data' => $all_short_data,
            'short_ot_data' => $all_short_ot_data,
            'branch_name' => $branch_name,
            'from_f' => $first_day_formatted,
            'to_f' => $last_day_formatted
        );
        $html = $this->ci->load->view('short_summary_104', $summary_data, true);

        $this->ci->dompdf_lib->reset();
        $this->ci->dompdf_lib->loadHtml($html);
        $this->ci->dompdf_lib->setPaper('A4', 'landscape');
        $this->ci->dompdf_lib->render();

        $short_file_name = '(' . $branch_name . ') Short Summary - ' . $first_day . ' to ' . $last_day . ' ' . time() . '.pdf';
        $short_file_path = FCPATH . 'uploads/summary/' . $short_file_name;
        file_put_contents($short_file_path, $this->ci->dompdf_lib->output());

        $zip_name = '(' . $branch_name . ') GNI01 Payroll Payroll Process - ' . $first_day . ' to ' . $last_day . ' ' . time() . '.zip';
        $zip_path = FCPATH . 'uploads/summary/' . $zip_name;

        $zip = new ZipArchive();
        if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception('Failed to create GNI01 ZIP file');
        }

        foreach ($files as $file) {
            $absolute = FCPATH . $file;
            if (is_file($absolute)) {
                $zip->addFile($absolute, 'SQL Payroll/' . basename($absolute));
            }
        }

        if (is_file($short_file_path)) {
            $zip->addFile($short_file_path, basename($short_file_path));
        }

        $zip->close();

        foreach ($files as $file) {
            $absolute = FCPATH . $file;
            if (is_file($absolute)) {
                @unlink($absolute);
            }
        }
        if (is_file($short_file_path)) {
            @unlink($short_file_path);
        }

        return array(
            'status' => 'success',
            'file_path' => $zip_path,
            'file_name' => $zip_name,
            'summary' => array(
                'branch' => $branch_name,
                'period' => array(
                    'from' => $first_day_formatted,
                    'to' => $last_day_formatted
                ),
                'employee_count' => count($all_short_data),
                'file_type' => 'zip',
                'generated_at' => date('Y-m-d H:i:s'),
                'generated_by' => 'Queue Worker'
            )
        );
    }
}
