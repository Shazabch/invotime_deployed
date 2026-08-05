<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['fcm-token'] = 'fcm_token/index';


$route['cron/shift-reminders'] = 'shift_reminder_cron/index';
$route['cron/shift-reminders/upcoming'] = 'shift_reminder_cron/upcoming';
$route['cron/shift-reminders/manual'] = 'shift_reminder_cron/manual';
$route['cron/shift-reminders/manual-test'] = 'shift_reminder_cron/manual_test';
$route['cron/shift-reminders/export-csv'] = 'shift_reminder_cron/export_csv';
$route['cron/shift-reminders/template'] = 'shift_reminder_cron/template';

// Async export download route - allows special characters in filename
$route['exports_async_api/download/(.*)'] = 'exports_async_api/download/$1';

// Month lock API explicit routes
$route['month_lock'] = 'month_lock/index';
$route['month_lock/details/(:num)'] = 'month_lock/details/$1';
$route['month_lock_api/create'] = 'month_lock_api/create';
$route['month_lock_api/list'] = 'month_lock_api/list_locks';
$route['month_lock_api/dashboard'] = 'month_lock_api/dashboard';
$route['month_lock_api/details/(:num)'] = 'month_lock_api/details/$1';
$route['month_lock_api/status/(:num)'] = 'month_lock_api/status/$1';
$route['month_lock_api/retry/(:num)'] = 'month_lock_api/retry/$1';
$route['month_lock_api/unlock/(:num)'] = 'month_lock_api/unlock/$1';
$route['month_lock_api/delete_lock_data/(:num)'] = 'month_lock_api/delete_lock_data/$1';

// $route['pkt'] = '/welcome';
// $route['megatrax'] = '/welcome';

//$route[':any'] = "maintenance/index/$1";
//$route['default_controller'] = "maintenance";

