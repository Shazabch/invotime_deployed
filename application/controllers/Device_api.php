<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Device_api extends CI_Controller
{
    public function connect()
    {
        $data = $this->input->raw_input_stream;

        $this->db->insert("device_logs", ["raw_data" => $data]);

        echo $data;
    }
}
