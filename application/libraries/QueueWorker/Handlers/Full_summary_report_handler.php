<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Full_summary_report_handler
{
    private $load;
    private $session;

    public function __construct($params = array())
    {
        $CI = get_instance();
        $this->load = isset($params['load']) ? $params['load'] : $CI->load;
        $this->session = isset($params['session']) ? $params['session'] : $CI->session;
    }

    public function generate($all_data, $branch_name, $first_day, $last_day, $first_day_formatted, $last_day_formatted)
    {
        $this->load->library('excel');

        $files = array();
        $session_user = $this->session->userdata('antelope_user');

        foreach ($all_data as $entry) {
            $entry['from_f'] = $first_day_formatted;
            $entry['to_f'] = $last_day_formatted;

            $this->session->set_userdata('antelope_user', array('id' => (int)$entry['employee']->id));
            $object = generate_full_summary_excel($entry);

            if ($session_user !== null) {
                $this->session->set_userdata('antelope_user', $session_user);
            } else {
                $this->session->unset_userdata('antelope_user');
            }

            $object->getActiveSheet()->setCellValueByColumnAndRow(7, 2, 'Queue Worker');

            $file_name = str_replace('/', '-', $entry['employee']->special_id) . ' - ' . str_replace('/', '-', $entry['employee']->first_name) . ' ' . $first_day . ' to ' . $last_day . ' - Summary.xlsx';
            $file_path = FCPATH . 'uploads/summary/' . $file_name;
            $writer = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
            $writer->save($file_path);
            $files[] = $file_path;
        }

        if (count($files) > 1) {
            $file_name = '(' . $branch_name . ') Full Summary - ' . $first_day . ' to ' . $last_day . ' ' . time() . '.zip';
            $file_path = FCPATH . 'uploads/summary/' . $file_name;
            $zip = new ZipArchive();
            $zip->open($file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();
        } else {
            $file_path = $files[0];
            $file_name = basename($files[0]);
        }

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
