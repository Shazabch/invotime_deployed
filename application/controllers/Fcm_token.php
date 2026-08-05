<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Fcm_token extends CI_Controller
{
    public function index()
    {
        $this->load->view('fcm_token');
    }
}
