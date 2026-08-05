<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class ApiController extends CI_Controller
{
    public function __construct()
    {

        parent::__construct();
        $this->load->helper('url');
        $this->load->model('MyModel');
        $this->load->library('upload');

        $this->load->library('session');
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Headers: Content-Type, Content-Range, Content-Disposition, Content-Description');
    }

    public function index()
    {
        echo "running";
    }
    public function AttendanceHistoryList($EmployeeCode)
    {
        $month = $this->input->get('month');
        $year = $this->input->get('year');
        if (!$EmployeeCode) {
            echo json_encode(NULL, 400);
        }

        $r    = $this->MyModel->AttendanceHistoryList($EmployeeCode, $year, $month);

        if ($r) {
            echo json_encode($r, 200);
        } else {
            echo json_encode([["type" => "unknown", "datetime" => "unknown"]]);
        }
    }




    public function update_user_device()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            echo json_encode(array('status' => 400, 'message' => 'Bad request.'));
        } else {
            //            $check_auth_client = $this->MyModel->check_auth_client();
            //            if($check_auth_client == true){
            $params = json_decode(file_get_contents('php://input'), TRUE);
            $employeeId = $params['employeeid'];
            $user_device_id = $params['user_device_id'];
            $user_device_type = $params['user_device_type'];

            //            print_r($params);
            //            die();



            $response = $this->MyModel->update_user_device($employeeId, $user_device_id, $user_device_type);
            echo json_encode($response);
            //}
        }
    }
    public function clocking()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            echo json_encode(array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $response = array();
        $response['data'] = null;
        $response['errors'] = null;
        $response['success'] = false;

        $employee_code     = $this->input->post('employee_code');
        $mac_address       = $this->input->post('mac_address');
        $scan_type         = $this->input->post('scan_type');
        $action            = $this->input->post('action');
        $datetime          = $this->input->post('datetime');
        $latlon            = $this->input->post('latlon');
        $temprature        = $this->input->post('temprature');
        $clocking_remark   = $this->input->post('clocking_remark') ?? '';
        $sync_mode         = $this->input->post('sync_mode');
        $address           = $this->input->post('address');

        if (!$employee_code) {
            $response['errors'][] = 'employee_code_parameter_missing';
            return $this->_send_error($response);
        }
        if (!$mac_address) {
            $response['errors'][] = 'mac_address_parameter_missing';
            return $this->_send_error($response);
        }
        if (!$scan_type) {
            $response['errors'][] = 'scan_type_parameter_missing';
            return $this->_send_error($response);
        }
        if (!$action) {
            $response['errors'][] = 'action_parameter_missing';
            return $this->_send_error($response);
        }

        if (isset($sync_mode) && $sync_mode == "1") {
            $datetime = date("Y-m-d H:i:s");
        }

        $device = null;
        $scan_distance = NULL;

        if ($scan_type != 'SCK') {
            $device = $this->db->select('*')->from('devices')->where('mac_address', $mac_address)->or_where("uuid", $mac_address)->get()->result();
            if (!$device) {
                $response["errors"] = "User or device not found";
                return $this->output->set_content_type('application/json')->set_status_header(200)->set_output(json_encode($response));
            }

            $device_lat_lon = $device[0]->coordinate;
            if (!empty($latlon) && !empty($device_lat_lon)) {
                $device_lat_lon_array = explode(",", $device_lat_lon);
                $latlon_array = explode(",", $latlon);
                $scan_distance = (getDistance(trim($device_lat_lon_array[0]), trim($device_lat_lon_array[1]), trim($latlon_array[0]), trim($latlon_array[1])) * 1000);
            }
            $company_id = $device[0]->company_id;
        } else {
            $company_id = $mac_address;
        }

        if (!$latlon) {
            $latlon = 'N/A';
        }

        $user = $this->db->select('*')->from('employees')->where("(`company_id` = '$company_id' AND `qr_barcode` = '$employee_code') OR ( `special_id` = '$employee_code' AND `company_id` = '$company_id') OR ( `id` = '$employee_code' AND `company_id` = '$company_id')")->get()->result();

        if ($user) {
            $response['success'] = true;
            $response['data']['special_id'] = $user[0]->special_id;
            $response['data']['first_name'] = $user[0]->first_name;
            $response['data']['last_name'] = $user[0]->last_name;
            $response['data']['qr_barcode'] = $user[0]->qr_barcode;

            if ($temprature == null || $temprature == "") $temprature = null;

            $latlon_array = array_map('trim', explode(",", $latlon));
            $lat = isset($latlon_array[0]) ? doubleval($latlon_array[0]) : null;
            $lon = isset($latlon_array[1]) ? doubleval($latlon_array[1]) : null;

            // Handle temporary local image upload
            $image_path = null;
            if (!empty($_FILES['photo']['name'])) {
                $upload_dir = FCPATH . 'uploads/pending_clockings/';
                if (!is_dir($upload_dir)) @mkdir($upload_dir, 0777, true);

                $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $fileName = 'clocking_tmp_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
                $local_target = $upload_dir . $fileName;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $local_target)) {
                    $image_path = 'uploads/pending_clockings/' . $fileName;
                }
            }

            // Create Job Payload
            $payload = [
                'user_id' => $user[0]->id,
                'employee_code' => $employee_code,
                'mac_address' => $mac_address,
                'scan_type' => $scan_type,
                'action' => $action,
                'datetime' => $datetime,
                'latlon' => $latlon,
                'lat' => $lat,
                'lon' => $lon,
                'temprature' => $temprature,
                'clocking_remark' => $clocking_remark,
                'sync_mode' => $sync_mode,
                'address' => $address,
                'scan_distance' => $scan_distance,
                'company_id' => $company_id,
                'device_id' => $device ? $device[0]->device_id : null
            ];

            // Insert into fast queue
            $insert_job = $this->db->insert('clocking_jobs', [
                'payload' => json_encode($payload),
                'image_path' => $image_path,
                'status' => 'pending'
            ]);

            if (!$insert_job) {
                // If the table doesn't exist or DB is locked, catch the error
                $response['success'] = false;
                $response['errors'][] = "Database Error: clocking_jobs table missing or insert failed.";
                $response['data'] = null;

                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode($response));
            }

            // Fake S3 local return just so UI doesn't crash if it looks for photo_url
            if (!empty($_FILES['photo']['name'])) {
                $response['status'] = 'success';
            }
            $response['data']['photo_url'] = $image_path ? base_url($image_path) : '';
            $response['data']['datetime'] = $datetime;
            $response['data']['latlon'] = $latlon;
            $response['data']['scan_distance'] = $scan_distance;
            $response['data']['type'] = $action;
        } else {
            $response['errors'][] = "User or device not found";
        }

        if ($response['errors'] != null) {
            $response['errors'] = implode(",", $response['errors']);
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($response));
    }

    private function _send_error($response)
    {
        if (is_array($response['errors'])) $response['errors'] = implode(",", $response['errors']);
        return $this->output->set_content_type('application/json')->set_status_header(200)->set_output(json_encode($response));
    }

    public function clocking_old()
    {

        // $UPLOAD_FOLDER_URL = env('UPLOAD_FOLDER_URL', 'localhost');
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            echo json_encode(array('status' => 400, 'message' => 'Bad request.'));
        } else {


            $response = array(); //NAVEED: always declare a response object.
            $response['data'] = null;
            $response['errors'] = null;
            $response['success'] = false;
            // $employee_code = "0";

            // $params = json_decode(file_get_contents('php://input'), TRUE);



            // $employee_code = (isset($params['employee_code']) ? $params['employee_code'] : null);
            // $mac_address = (isset($params['mac_address']) ? $params['mac_address'] : null);
            // $scan_type = (isset($params['scan_type']) ? $params['scan_type'] : null);
            // $action = (isset(sync_modeaction']) ? $params['action'] : null);
            // $datetime = (isset($params['datetime']) ? $params['datetime'] : null);
            // $latlon = (isset($params['latlon']) ? $params['latlon'] : null);
            // $temprature = (isset($params['temprature']) ? $params['temprature'] : null);
            // $sync_mode = (isset($params['sync_mode']) ? $params['sync_mode'] : null);

            // $employee_code = $params['employee_code'];
            // $mac_address = $params['mac_address'];
            // $scan_type = $params['scan_type'];
            // $action = $params['action'];
            // $datetime = $params['datetime'];
            // $latlon = $params['latlon'];
            // $temprature = $params['temprature'];

            $employee_code     = $this->input->post('employee_code');
            $mac_address       = $this->input->post('mac_address');
            $scan_type         = $this->input->post('scan_type');
            $action            = $this->input->post('action');
            $datetime          = $this->input->post('datetime');
            $latlon            = $this->input->post('latlon');
            $temprature        = $this->input->post('temprature');
            $clocking_remark   = $this->input->post('clocking_remark') ?? '';
            $sync_mode         = $this->input->post('sync_mode');
            $address           = $this->input->post('address');

            // if (isset($params['clocking_remark'])) {
            //     $clocking_remark = $params['clocking_remark'];
            // } else {
            //     $clocking_remark = '';
            // }
            // if (isset($params['sync_mode']))
            //     $sync_mode = $params['sync_mode'];


            // if (!$latlon) {
            //     $response['errors'][] = 'latlon_parameter_missing';
            //     return;
            // }
            if (!$employee_code) {
                $response['errors'][] = 'employee_code_parameter_missing';
                return;
            }
            if (!$mac_address) {
                $response['errors'][] = 'mac_address_parameter_missing';
                return;
            }
            if (!$scan_type) {
                $response['errors'][] = 'scan_type_parameter_missing';
                return;
            }

            if (!$action) {
                $response['errors'][] = 'action_parameter_missing';
                return;
            }

            if (!isset($sync_mode)) {
            } else {
                if ($sync_mode == "1") {
                    $datetime = date("Y-m-d H:i:s");
                }
            }


            // if(!$request->has('weather')){
            //     $response['errors'][] = 'weather_parameter_missing';
            // }


            //var_dump(count($response['errors']));


            //$user = Employee::where('qr_barcode',$request->employee_code)->first();


            // var_dump($user);
            // var_dump($response);
            // die();
            $device = null;
            $scan_distance = NULL;
            if ($scan_type != 'SCK') {
                $device = $this->db->select('*')->from('devices')->where('mac_address', $mac_address)->or_where("uuid", $mac_address)->get()->result();
                if (!$device) {
                    $response["errors"] = "User or device not found";
                    return $this->output
                        ->set_content_type('application/json')
                        ->set_status_header(200)
                        ->set_output(json_encode($response));
                }

                $device_lat_lon = $device[0]->coordinate;

                if (!empty($latlon) && !empty($device_lat_lon)) {

                    $device_lat_lon_array = explode(",", $device_lat_lon);
                    $latlon_array = explode(",", $latlon);

                    $scan_distance = (getDistance(trim($device_lat_lon_array[0]), trim($device_lat_lon_array[1]), trim($latlon_array[0]), trim($latlon_array[1])) * 1000);
                }

                $company_id = $device[0]->company_id;
            } else {
                $company_id = $mac_address;
            }

            if (!$latlon) {
                $latlon = 'N/A';
            }
            // $user =  Employee::where(function ($query) use ($employee_code,$company_id) {
            //   $query->where('company_id', '=', $company_id)
            //     ->where('qr_barcode', '=', $employee_code)
            //     ->orWhere('special_id', '=', $employee_code)
            //     ->orWhere('user_device_id', '=', $employee_code);
            //   })->toSql();

            // $user = $this->db->select('e.id, e.special_id, e.first_name, e.last_name, e.qr_barcode, c.name company, b.name branch')
            // ->from('employees e')->join("companies c", "c.id = e.company_id")->join("branches b", "e.branch_id = b.id")
            // ->where('e.company_id', $company_id)->where('e.special_id', $employee_code)->or_where('qr_barcode', $employee_code)->get()->result();

            $user =   $this->db->select('*')->from('employees')->where("(`company_id` = '$company_id' AND `qr_barcode` = '$employee_code') OR ( `special_id` = '$employee_code' AND `company_id` = '$company_id') OR ( `id` = '$employee_code' AND `company_id` = '$company_id')")->get()->result();

            // $user = Employee::where('company_id', '=', $company_id)
            // ->where(function ($query) use ($employee_code, $company_id) {
            // $query->orWhere('qr_barcode', '=', $employee_code)
            // ->orWhere('special_id', '=', $employee_code)
            // ->orWhere('id', '=', $employee_code)
            // ->orWhere('user_device_id', '=', $employee_code);
            // })->first();

            // var_dump($user);
            // die();


            if ($user) {

                // $shift = DB::table('shift_days')->whereRaw("date = '" . date("Y-m-d") . "' AND FIND_IN_SET(" . $user->id . ",employees)")->first();
                $shift = $this->db->select('*')->from('shift_days')->where("date = '" . date("Y-m-d") . "' AND FIND_IN_SET(" . $user[0]->id . ",employees)")->get()->result();

                $shift_id = 0;
                if (!empty($shift)) {
                    $shift_id = $shift[0]->shift_id;
                }

                $response['success'] = true;
                $response['data']['special_id'] = $user[0]->special_id;
                $response['data']['first_name'] = $user[0]->first_name;
                $response['data']['last_name'] = $user[0]->last_name;
                //$response['data']['photo'] = $UPLOAD_FOLDER_URL . $user[0]->photo;
                $response['data']['qr_barcode'] = $user[0]->qr_barcode;
                // $response['data']['company'] = $user[0]->company;
                // $response['data']['branch'] = $user[0]->branch;
                // $response['data']['company'] = $user[0]->company->name;
                // $response['data']['branch'] = $user[0]->branch->name;

                //$response['data']['company'] = $user->company;
                //$response['data']['position'] = $user->position;
                //$response['data']['department'] = $user->department;
                //$response['data']['branch'] = $user->branch;
                //$response['data']['role'] = $user->role;

                // var_dump($device->branch);
                // die();

                // $dt = Mcarbon::now($device[0]->timezone);

                // //var_dump($dt);

                // if ($datetime) {

                //     $datetime_temp = str_replace("-T", " ", $datetime);
                //     $datetime_temp = str_replace("Z", "", $datetime);

                //     //die("datetime exists");
                //     $dt = Mcarbon::parse($datetime_temp, $device[0]->timezone);
                // }

                //var_dump(date("Y-m-d H:i:s", time() - date("Z")));


                // var_dump($dt);
                // var_dump($dt->toDateTimeString());
                // die();


                //                    $checkData = $this->db->select('*')->from('clockings_news')->where('type', $action)->where('datetime', $dt->toDateTimeString())->order_by('id', 'ASC')->get()->result();
                //
                //                    // var_dump($checkData);
                //                    // die();
                //
                //                    //nav
                //                    if ($checkData == null) {

                // if($request->action == "in"){
                //   $data = ['employee_id' => $user->id,'clock_in'=>$dt->toDateTimeString(),'device_id'=> $device->device_id,'scan_type_in'=> $request->scan_type,'weather'=>$request->weather,'shift_id'=>$shift_id];

                // }else{
                //   $data = ['employee_id' => $user->id,'clock_out'=>$dt->toDateTimeString(),'device_id'=> $device->device_id,'scan_type_in'=> $request->scan_type,'weather'=>$request->weather,'shift_id'=>$shift_id];

                // }

                //die('if $checkData == null');

                if ($temprature == null || $temprature == "") {
                    $temprature = null;
                }
                // Change latlon to address
                // explode latlon but also apply trim on it,  in single line
                $latlon_array = array_map('trim', explode(",", $latlon));
                $lat = isset($latlon_array[0]) ? doubleval($latlon_array[0]) : null;
                $lon = isset($latlon_array[1]) ? doubleval($latlon_array[1]) : null;

                // Normalize address: prefer a non-empty provided address; if empty, fall back to reverse geocoding
                $address = trim((string)$address);
                if ($address !== '') {
                    // use provided address as-is (first priority)
                } else if ($lat !== null && $lon !== null) {
                    // only attempt reverse geocoding when both lat & lon are present
                    $address = $this->getAddress($lat, $lon);
                } else {
                    $address = null;
                }

                // Handle image upload
                $image_url = '';
                if (!empty($_FILES['photo']['name'])) {
                    // AWS S3 Configuration
                    $bucket     = env('AWS_BUCKET', 'invotime');
                    $region     = env('AWS_DEFAULT_REGION', 'ap-southeast-1');
                    $accessKey  = env('AWS_ACCESS_KEY_ID', '');
                    $secretKey  = env('AWS_SECRET_ACCESS_KEY', '');

                    // Generate unique file name and S3 key
                    $extension  = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                    $fileName   = 'clocking_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
                    $s3Key      = 'clocking_images/' . $fileName;

                    // Initialize S3 client
                    $s3 = new S3Client([
                        'version'     => 'latest',
                        'region'      => $region,
                        'credentials' => [
                            'key'    => $accessKey,
                            'secret' => $secretKey,
                        ],
                    ]);

                    try {
                        // Upload file using putObject
                        $result = $s3->putObject([
                            'Bucket'      => $bucket,
                            'Key'         => $s3Key,
                            'SourceFile'  => $_FILES['photo']['tmp_name'],
                            // 'ACL'         => 'public-read', // Use 'private' if not exposing
                            'ContentType' => $_FILES['photo']['type'],
                        ]);

                        $image_url = $result['ObjectURL']; // You can store this in DB

                        // Return success response
                        $response['status'] = 'success';
                        $response['data']['photo_url'] = $image_url;
                    } catch (AwsException $e) {
                        $response['status'] = 'error';
                        $response['message'] = 'S3 Upload Error';
                        $response['aws_message'] = $e->getAwsErrorMessage();
                        $response['aws_code'] = $e->getAwsErrorCode();
                        $response['aws_type'] = $e->getAwsErrorType();
                        $response['raw'] = $e->getMessage(); // Optional: full raw message

                        return $this->output
                            ->set_content_type('application/json')
                            ->set_status_header(200)
                            ->set_output(json_encode($response));
                    }
                }
                //  else {
                //     $response['status'] = 'error';
                //     $response['message'] = 'No photo uploaded.';
                //     // Output JSON response
                //     return $this->output
                //     ->set_content_type('application/json')
                //     ->set_status_header(200)
                //     ->set_output(json_encode($response));
                // }

                $data = array(
                    'employee_id' => $user[0]->id,
                    'latlon' => $latlon,
                    'type' => $action,
                    'datetime' => $datetime,
                    'device_id' => $device ? $device[0]->device_id : null,
                    'mode' => $scan_type,
                    'weather' => null,
                    'shift_id' => $shift_id,
                    'scan_distance' => $scan_distance,
                    'temprature' => $temprature,
                    'clocking_remark' => $clocking_remark,
                    'sync_mode' => $sync_mode,
                    'address' => $address,
                    'selfie' => $image_url
                );

                //var_dump($data);
                //die();

                $clocking = $this->db->insert('clockings_news', $data);

                update_new_clockings($user[0]->id, $datetime);

                $response['data']['datetime'] = $datetime;
                $response['data']['latlon'] = $latlon;
                $response['data']['scan_distance'] = $scan_distance;
                $response['data']['type'] = $action;

                // } else {
                //
                // $response['data']['datetime'] = $checkData[0]->datetime;
                // $response['data']['type'] = $checkData[0]->type;
                //
                //                    }
                // else{


                //     $checkclockin = Clockings::where('employee_id',$user->id)->orderBy('id', 'DESC')->first();


                //     if($request->action == "in"){

                //       //die("in");
                //         $data = ['employee_id' => $user->id,'clock_in'=>$dt->toDateTimeString(),'device_id'=> $device->device_id,'scan_type_in'=>$request->scan_type,'weather'=>$request->weather,'shift_id'=>$shift_id];

                //         $clocking = Clockings::create($data);

                //     }
                //     else
                //     {
                //       //die("out");
                //         $data = ['employee_id' => $user->id,'clock_out'=>$dt->toDateTimeString(),'device_id'=> $device->device_id,'scan_type_out'=>$request->scan_type,'weather'=>$request->weather];


                //         Clockings::where('id', $checkclockin->id)->update($data);

                //         $clocking = Clockings::where('id',$checkclockin->id)->first();

                //     }

                //     $response['data']['clock_in'] = $clocking->clock_in;
                //     $response['data']['clock_out'] = $clocking->clock_out;

                // }
            } else {
                $response['errors'][] = "User or device not found";
            }

            // } else {
            //     $response['errors'][] = "User or device not found";
            // }

            if ($response['errors'] != null) {
                $response['errors'] = implode(",", $response['errors']);
            }

            //var_dump($clocking->clock_in);
            //die();
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($response));
        }
    }






    public function getLastInsertedClockingeEmployeeRecord()
    {

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            echo json_encode(array('status' => 400, 'message' => 'Bad request.'));
        } else {
            //            $check_auth_client = $this->MyModel->check_auth_client();
            //            if($check_auth_client == true){
            $params = json_decode(file_get_contents('php://input'), TRUE);
            $employeeId = $params['employeeid'];


            //            print_r($params);
            //            die();



            $response = $this->MyModel->getLastInsertedClockingeEmployeeRecord($employeeId);
            echo json_encode($response);
            //}
        }
    }

    public function getEmployeeProfle()
    {

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            echo json_encode(array('status' => 400, 'message' => 'Bad request.'));
        } else {
            //            $check_auth_client = $this->MyModel->check_auth_client();
            //            if($check_auth_client == true){
            $params = json_decode(file_get_contents('php://input'), TRUE);
            $employeeId = $params['employeeid'];


            //            print_r($params);
            //            die();



            $response = $this->MyModel->getEmployeeProfle($employeeId);
            echo json_encode($response);
            //}
        }
    }

    public function save_staff_remark()
    {
        // Get POST data (form-data or JSON)
        $employee_id = $this->input->post('employee_id');
        $remark      = $this->input->post('remark');
        $date        = $this->input->post('date');

        // Fallback: If JSON raw input is sent
        if (!$employee_id || !$date) {
            $input       = json_decode($this->input->raw_input_stream, true);
            $employee_id = isset($input['employee_id']) ? $input['employee_id'] : null;
            $remark      = isset($input['remark']) ? $input['remark'] : null;
            $date        = isset($input['date']) ? $input['date'] : null;
        }

        if (!$employee_id || !$date) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status'  => false,
                    'message' => 'Missing required fields'
                ]));
        }

        // Clean and format date
        $date = trim($date, '"');
        $date = date('Y-m-d', strtotime($date));

        $data = [
            'employee_id' => $employee_id,
            'remark'      => $remark,
            'remark_date' => $date,
        ];

        // Check if remark already exists for employee + date
        $exists = $this->db->get_where('staff_remarks', [
            'employee_id' => $employee_id,
            'remark_date' => $date
        ])->row();

        if ($exists) {
            if ($remark == "") {
                // Delete existing remark if new remark is empty
                $this->db->where('employee_id', $employee_id)
                    ->where('remark_date', $date)
                    ->delete('staff_remarks');
            } else {
                // Update existing remark
                $this->db->where('employee_id', $employee_id)
                    ->where('remark_date', $date)
                    ->update('staff_remarks', ['remark' => $remark]);
            }
        } else {
            if ($remark != "") {
                // Insert new remark
                $this->db->insert('staff_remarks', $data);
            } else {
                return $this->output
                    ->set_content_type('application/json')
                    ->set_status_header(400)
                    ->set_output(json_encode([
                        'status'  => false,
                        'message' => 'Remark Missing'
                    ]));
            }
        }

        // Update new_clockings table
        $this->db->set("staff_remark", $remark)
            ->where("employee_id", $employee_id)
            ->where("date(clock_in)", $date)
            ->update("new_clockings");

        // Get employee name for log
        $emp = $this->db->select('first_name')
            ->from('employees')
            ->where('id', $employee_id)
            ->get()
            ->row();

        $emp_name = $emp ? $emp->first_name : '';

        insert_log("Staff Remarks", [
            "action"      => "Edited, Staff Remarks",
            "target_id"   => $employee_id,
            "target_name" => $emp_name,
            "for_date"    => $date,
        ]);

        // Success response
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'status'  => true,
                'message' =>  !$exists ? 'Staff remark saved successfully' : (empty($remark) ? 'Staff remark deleted successfully' : 'Staff remark updated successfully'),
                'data'    => $data
            ]));
    }

    public function get_staff_remarks()
    {
        // Get POST data (form-data or JSON)
        $employee_id = $this->input->post('employee_id');
        $from_date   = $this->input->post('from_date');
        $to_date     = $this->input->post('to_date');

        // Fallback: If JSON raw input is sent
        if (!$employee_id || !$from_date || !$to_date) {
            $input       = json_decode($this->input->raw_input_stream, true);
            $employee_id = isset($input['employee_id']) ? $input['employee_id'] : null;
            $from_date   = isset($input['from_date']) ? $input['from_date'] : null;
            $to_date     = isset($input['to_date']) ? $input['to_date'] : null;
        }

        // Validate required fields
        if (!$employee_id || !$from_date || !$to_date) {
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode([
                    'status'  => false,
                    'message' => 'Missing required fields'
                ]));
        }

        // Clean and format dates
        $from_date = trim($from_date, '"');
        $to_date   = trim($to_date, '"');
        $from_date = date('Y-m-d', strtotime($from_date));
        $to_date   = date('Y-m-d', strtotime($to_date));

        // Fetch remarks
        $remarks = $this->db->select('id, employee_id, remark, remark_date')
            ->from('staff_remarks')
            ->where('employee_id', $employee_id)
            ->where('remark_date >=', $from_date)
            ->where('remark_date <=', $to_date)
            ->order_by('remark_date', 'ASC')
            ->get()
            ->result();

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode([
                'status'  => true,
                'message' => count($remarks) > 0 ? 'Remarks fetched successfully' : 'No remarks found',
                'remarks'    => $remarks
            ]));
    }

    public function login()
    {

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            echo json_encode(array('status' => 400, 'message' => 'Bad request.'));
        } else {
            //            $check_auth_client = $this->MyModel->check_auth_client();
            //            if($check_auth_client == true){
            $params = json_decode(file_get_contents('php://input'), TRUE);
            $employeeId = $params['employeeid'];
            $organizationid = $params['organizationid'];
            $password = $params['password'];

            //            print_r($params);
            //            die();



            $response = $this->MyModel->login($employeeId, $organizationid, $password);
            echo json_encode($response);
            //}
        }
    }

    public function get_profile()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            echo json_encode(array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $params = json_decode(file_get_contents('php://input'), TRUE);
        $id = $params['id'];

        if (empty($id)) {
            echo json_encode(array('status' => 400, 'message' => 'Bad request.'));
            return;
        }

        $response = $this->MyModel->get_profile($id);
        return $this->output->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($response));
    }



    public function register()
    {

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            echo json_encode(array('status' => 400, 'message' => 'Bad request.'));
        } else {
            //            $check_auth_client = $this->MyModel->check_auth_client();
            //            if($check_auth_client == true){
            $params = json_decode(file_get_contents('php://input'), TRUE);
            $employeeId = $params['employeeid'];
            $organizationid = $params['organizationid'];
            $password = $params['password'];

            //            print_r($params);
            //            die();



            $response = $this->MyModel->register($employeeId, $organizationid, $password);
            echo json_encode($response);
            //}
        }
    }

    public function reset_device_id()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            return send_json_response(array('status' => 400, 'message' => 'Bad request.'), 400);
        }

        $params = json_decode(file_get_contents('php://input'), true);
        $employeeId = $params['employeeId'];

        $response = $this->MyModel->reset_device_id($employeeId);
        if ($response['status'] === 200) {
            return send_json_response($response, 200);
        }
        return send_json_response($response, 500);
    }

    /**
     * Reverse geocodes a given latitude and longitude using Nominatim service
     *
     * @param float $lat
     * @param float $lon
     * @return string|null
     */
    private function getAddress($lat, $lon)
    {
        // Nominatim requires a User-Agent
        $url = "https://nominatim.openstreetmap.org/reverse.php?lat={$lat}&lon={$lon}&zoom=18&format=jsonv2";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'invotime/1.0'); // Nominatim requires a User-Agent
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept-Language: en"
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code !== 200 || !$response) {
            return null;
        }

        $data = json_decode($response, true);
        if (isset($data['display_name'])) {
            return $data['display_name'];
        }

        return null;
    }

    public function get_device_employees()
    {
        $response = [
            'data' => null,
            'errors' => null,
            'success' => false
        ];

        $mac_address = $this->input->get('mac_address');

        if (!$mac_address) {
            $response['errors'] = 'mac_address_parameter_missing';
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($response));
        }

        $query = $this->db
            // your select list (unchanged)
            ->select('employees.id, first_name, special_id, department_id, qr_barcode, device_role, device_password')

            // start from employees
            ->from('employees')

            // join only the one device row
            ->join(
                'devices',
                'employees.company_id = devices.company_id'
                    . ' AND devices.mac_address = ' . $this->db->escape($mac_address),
                null,
                false
            )

            // exclude soft‐deleted employees
            ->where('employees.deleted_at', null)

            // ───── sync_action logic ─────
            ->group_start()
            // include everyone flagged “all”
            ->where('employees.sync_action', 'SetUserDataAll')

            // OR branch‐scoped + same branch
            ->or_group_start()
            ->where('employees.sync_action', 'SetUserData')
            ->where('employees.branch_id = devices.branch_id', null, false)
            ->group_end()
            ->group_end()
            ->group_start()
            ->where('employees.employee_status', 'active')
            ->or_where(
                "employees.employee_status = 'terminated'
                    AND employees.termination_date IS NOT NULL
                    AND employees.termination_date >= DATE_FORMAT(NOW(), '%Y-%m-01')",
                null,
                false
            )
            ->or_where(
                "employees.employee_status = 'resigned'
                    AND employees.resignation_date IS NOT NULL
                    AND employees.resignation_date >= DATE_FORMAT(NOW(), '%Y-%m-01')",
                null,
                false
            )
            ->group_end()

            // execute
            ->get();

        $employees = $query->result();

        $uniqueEmployees = [];
        $seenIds = [];
        foreach ($employees as $employee) {
            if (!in_array($employee->id, $seenIds)) {
                $seenIds[] = $employee->id;
                $uniqueEmployees[] = $employee;
            }
        }
        $employees = $uniqueEmployees;

        if ($employees) {
            $response['data'] = $employees;
            $response['success'] = true;
        } else {
            $response['success'] = false;
            $response['errors'] = "Employees not found.";
        }

        return $this->output
            ->set_content_type("application/json")
            ->set_status_header(200)
            ->set_output(json_encode($response));
    }
    public function check_device()
    {
        $response = [
            'data'    => null,
            'errors'  => null,
            'success' => false
        ];

        $mac_address = $this->input->get('mac_address');

        if (!$mac_address) {
            $response['errors'] = 'mac_address_parameter_missing';
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($response));
        }

        $device = $this->db->select('*')->from('devices')->where('mac_address', $mac_address)->or_where("uuid", $mac_address)->get()->result();
        if (!$device) {
            $response["errors"] = "Device not found";
            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($response));
        }

        $response['data']    = $device;
        $response['success'] = true;

        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(200)
            ->set_output(json_encode($response));
    }
    /**
     * Helper function to get an employee's targeting context (location, role info).
     * @param int $employee_id
     * @return array|null Employee details needed for targeting
     */
    private function _get_employee_context($employee_id)
    {
        // ASSUMPTION confirmed by Antelope.php: 'employees' table is available and contains all necessary FKs.
        $employee = $this->db
            ->select('company_id, branch_id, department_id, position_id, section_id')
            ->where('id', $employee_id)
            ->get('employees')
            ->row_array();

        return $employee;
    }
    /**
     * API Endpoint 1: Fetch all visible announcements for a single employee.
     * Includes a flag for whether the announcement has been read.
     *
     * URL: /ApiController/announcements_for_employee
     * Method: POST
     * Body: { "employee_id": 123 }
     */
    public function announcements_for_employee()
    {
        // $postdata = file_get_contents("php://input");
        // $request = json_decode($postdata, true);
        // return $postdata; die;
        // $employee_id = isset($request['employee_id']) ? (int)$request['employee_id'] : null;
        $employee_id = $this->input->post('employee_id');
        $filter_start = $this->input->post('start_date');
        $filter_end   = $this->input->post('end_date');

        if (!$employee_id) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Employee ID is required.']));
        }

        $context = $this->_get_employee_context($employee_id);

        if (empty($context)) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Employee not found or context missing.']));
        }

        $cid = (int)$context['company_id'];
        $branch_id = (int)$context['branch_id'];
        $department_id = (int)$context['department_id'];
        $position_id = (int)$context['position_id'];
        $section_id = (int)$context['section_id'];

        $this->db->select('a.*, (av.id IS NOT NULL) AS is_read', false)
            ->from('announcements a')
            ->join('announcement_views av', "av.announcement_id = a.id AND av.employee_id = {$employee_id}", 'left')
            // --- BASE FILTERS ---
            ->where('a.company_id', $cid)
            ->where('a.deleted_at IS NULL', null, false)
            ->where('a.status', 'active') // Assuming 'active' is a status column
            ->where('a.end_date >=', date('Y-m-d H:i:s'))
            // ->where('NOW() BETWEEN a.start_date AND a.end_date', null, false)

            // --- COMPLEX TARGETING LOGIC (ANY of these conditions must be TRUE) ---
            ->group_start()
            // 1. All Staff Announcement (The main override)
            ->or_where('a.all_staff', 1)

            // 2. Individual Employee Target (Explicitly named)
            ->or_where("EXISTS (SELECT 1 FROM announcement_employees ae WHERE ae.announcement_id = a.id AND ae.employee_id = {$employee_id})", null, false)

            // 3. Targeted by Outlet (Branch)
            ->or_where("EXISTS (SELECT 1 FROM announcement_outlets ao WHERE ao.announcement_id = a.id AND ao.branch_id = {$branch_id})", null, false)

            // 4. Targeted by Department
            ->or_where("EXISTS (SELECT 1 FROM announcement_departments ad WHERE ad.announcement_id = a.id AND ad.department_id = {$department_id})", null, false)

            // 5. Targeted by Position
            ->or_where("EXISTS (SELECT 1 FROM announcement_positions ap WHERE ap.announcement_id = a.id AND ap.position_id = {$position_id})", null, false)

            // 6. Targeted by Section
            ->or_where("EXISTS (SELECT 1 FROM announcement_sections asn WHERE asn.announcement_id = a.id AND asn.section_id = {$section_id})", null, false)
            ->group_end()

            // --- ORDERING ---
            // Order by Priority (urgent > important > normal) and then newest first
            ->order_by("FIELD(a.priority, 'urgent', 'important', 'normal') DESC", false)
            ->order_by('a.created_at', 'DESC');
        // Date range filter (only if both dates are supplied)
        if (!empty($filter_start) && !empty($filter_end)) {

            // Include entire end day if only a date is passed
            if (strlen($filter_start) == 10) {
                $filter_start .= ' 00:00:00';
            }

            if (strlen($filter_end) == 10) {
                $filter_end .= ' 23:59:59';
            }

            $this->db->where('a.start_date <=', $filter_end);
            $this->db->where('a.end_date >=', $filter_start);
        }

        $announcements = $this->db->get()->result();

        $this->output->set_output(json_encode([
            'success' => true,
            'data' => $announcements,
            'employee_context' => $context // Useful for debugging mobile targeting
        ]));
    }

    /**
     * API Endpoint 2: Mark an announcement as read by an employee.
     *
     * URL: /ApiController/mark_as_read
     * Method: POST
     * Body: { "employee_id": 123, "announcement_id": 456 }
     */
    public function mark_as_read()
    {
        //     $postdata = file_get_contents("php://input");
        //     $request = json_decode($postdata, true);
        //     $employee_id = isset($request['employee_id']) ? (int)$request['employee_id'] : null;
        //     $announcement_id = isset($request['announcement_id']) ? (int)$request['announcement_id'] : null;
        $employee_id = $this->input->post('employee_id');
        $announcement_id = $this->input->post('announcement_id');

        if (!$employee_id || !$announcement_id) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Missing employee_id or announcement_id.']));
        }

        $data = [
            'announcement_id' => $announcement_id,
            'employee_id' => $employee_id,
            'read_at' => date('Y-m-d H:i:s')
        ];

        // Using ON DUPLICATE KEY UPDATE to handle idempotency.
        // It ensures the record is inserted if new, or does nothing if it already exists.
        $sql = $this->db->insert_string('announcement_views', $data) . ' ON DUPLICATE KEY UPDATE read_at=read_at';
        $query = $this->db->query($sql);

        if ($query) {
            $this->output->set_output(json_encode(['success' => true, 'message' => 'Announcement marked as read.']));
        } else {
            // Log the error for review
            error_log("DB Error marking announcement read: " . $this->db->error()['message']);
            $this->output->set_output(json_encode(['success' => false, 'message' => 'Failed to mark as read.']));
        }
    }

    /**
     * API Endpoint 3 (Useful Extra): Get a summary of announcements (Total visible and Unread count).
     * This is great for displaying badge counters in the mobile app's navigation bar.
     *
     * URL: /ApiController/get_summary
     * Method: POST
     * Body: { "employee_id": 123 }
     */
    public function get_summary()
    {
        // $postdata = file_get_contents("php://input");
        // $request = json_decode($postdata, true);
        // $employee_id = isset($request['employee_id']) ? (int)$request['employee_id'] : null;
        $employee_id = $this->input->post('employee_id');

        if (!$employee_id) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Employee ID is required.']));
        }

        $context = $this->_get_employee_context($employee_id);
        if (empty($context)) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Employee not found.']));
        }

        $cid = (int)$context['company_id'];
        $branch_id = (int)$context['branch_id'];
        $department_id = (int)$context['department_id'];
        $position_id = (int)$context['position_id'];
        $section_id = (int)$context['section_id'];

        // Subquery to get IDs of all visible announcements
        $this->db
            ->select('a.id')
            ->from('announcements a')
            ->where('a.company_id', $cid)
            ->where('a.deleted_at IS NULL', null, false)
            ->where('a.status', 'active')
            ->where('NOW() BETWEEN a.start_date AND a.end_date', null, false)
            ->group_start()
            ->or_where('a.all_staff', 1)
            ->or_where("EXISTS (SELECT 1 FROM announcement_employees ae WHERE ae.announcement_id = a.id AND ae.employee_id = {$employee_id})", null, false)
            ->or_where("EXISTS (SELECT 1 FROM announcement_outlets ao WHERE ao.announcement_id = a.id AND ao.branch_id = {$branch_id})", null, false)
            ->or_where("EXISTS (SELECT 1 FROM announcement_departments ad WHERE ad.announcement_id = a.id AND ad.department_id = {$department_id})", null, false)
            ->or_where("EXISTS (SELECT 1 FROM announcement_positions ap WHERE ap.announcement_id = a.id AND ap.position_id = {$position_id})", null, false)
            ->or_where("EXISTS (SELECT 1 FROM announcement_sections asn WHERE asn.announcement_id = a.id AND asn.section_id = {$section_id})", null, false)
            ->group_end();
        $visible_announcements_query = $this->db->get_compiled_select();


        // Main query using the subquery to get total count
        $total_visible = $this->db->query("SELECT COUNT(1) as total FROM ({$visible_announcements_query}) AS visible_anns")->row()->total;

        // Count read announcements from the visible set
        // NOTE: CI does not handle subqueries in where_in gracefully, so we use the raw SQL approach again.
        $read_count = $this->db
            ->where("employee_id = {$employee_id} AND announcement_id IN ({$visible_announcements_query})", null, false)
            ->from('announcement_views')
            ->count_all_results();

        $unread_count = $total_visible - $read_count;

        $this->output->set_output(json_encode([
            'success' => true,
            'summary' => [
                'total_visible' => (int)$total_visible,
                'total_unread' => (int)$unread_count
            ]
        ]));
    }

    /**
     * API Endpoint 4 (Useful Extra): Get details of a single announcement.
     * Useful when a user clicks a push notification that links directly to an announcement ID.
     *
     * URL: /ApiController/get_details
     * Method: POST
     * Body: { "employee_id": 123, "announcement_id": 456 }
     */
    public function get_details()
    {
        // $postdata = file_get_contents("php://input");
        // $request = json_decode($postdata, true);
        // $employee_id = isset($request['employee_id']) ? (int)$request['employee_id'] : null;
        // $announcement_id = isset($request['announcement_id']) ? (int)$request['announcement_id'] : null;
        $employee_id = $this->input->post('employee_id');
        $announcement_id = $this->input->post('announcement_id');

        if (!$employee_id || !$announcement_id) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Missing IDs.']));
        }

        // We should check that the announcement is actually visible to the user
        // A simpler check is just by company_id and active status.
        $context = $this->_get_employee_context($employee_id);
        if (empty($context)) {
            return $this->output->set_output(json_encode(['success' => false, 'message' => 'Employee context not found.']));
        }

        $announcement = $this->db
            ->select('a.*, (av.id IS NOT NULL) AS is_read', false)
            ->from('announcements a')
            ->join('announcement_views av', "av.announcement_id = a.id AND av.employee_id = {$employee_id}", 'left')
            ->where('a.id', $announcement_id)
            ->where('a.company_id', (int)$context['company_id'])
            ->where('a.deleted_at IS NULL', null, false)
            ->where('a.status', 'active')
            ->where('NOW() BETWEEN a.start_date AND a.end_date', null, false)
            ->get()
            ->row();

        if ($announcement) {
            $this->output->set_output(json_encode(['success' => true, 'data' => $announcement]));
        } else {
            $this->output->set_output(json_encode(['success' => false, 'message' => 'Announcement not found or not active.']));
        }
    }
}
