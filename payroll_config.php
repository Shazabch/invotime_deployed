<?php
defined('BASEPATH') OR exit('No direct script access allow');

$payroll_config_json  = $str = file_get_contents('payroll_config.json');

$payroll_config = json_decode($payroll_config_json,true);

?>