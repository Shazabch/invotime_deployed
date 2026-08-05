<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ApiController extends CI_Controller
{
    public function __construct()
    {

        parent::__construct();
        $this->load->helper('url');
        $this->load->model('MyModel');

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
            echo json_encode([ ["type" => "unknown", "datetime" => "unknown"] ]);
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

            $params = json_decode(file_get_contents('php://input'), TRUE);



            // $employee_code = (isset($params['employee_code']) ? $params['employee_code'] : null);
            // $mac_address = (isset($params['mac_address']) ? $params['mac_address'] : null);
            // $scan_type = (isset($params['scan_type']) ? $params['scan_type'] : null);
            // $action = (isset($params['action']) ? $params['action'] : null);
            // $datetime = (isset($params['datetime']) ? $params['datetime'] : null);
            // $latlon = (isset($params['latlon']) ? $params['latlon'] : null);
            // $temprature = (isset($params['temprature']) ? $params['temprature'] : null);
            // $sync_mode = (isset($params['sync_mode']) ? $params['sync_mode'] : null);

            $employee_code = $params['employee_code'];
            $mac_address = $params['mac_address'];
            $scan_type = $params['scan_type'];
            $action = $params['action'];
            $datetime = $params['datetime'];
            $latlon = $params['latlon'];
            $temprature = $params['temprature'];
            if (isset($params['clocking_remark'])) {
                $clocking_remark = $params['clocking_remark'];
            } else {
                $clocking_remark = '';
            }
            if (isset($params['sync_mode']))
                $sync_mode = $params['sync_mode'];



            if (!$latlon) {
                $response['errors'][] = 'latlon_parameter_missing';
                return;
            }
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

                if ($lat != null && $lon != null) {
                    $address = $this->getAddress($lat, $lon);
                } else {
                    $address = null;
                }

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
                    'address' => $address
                );

                //var_dump($data);
                //die();

                $clocking = $this->db->insert('clockings_news', $data);

                update_new_clockings($user[0]->id, $datetime);

                $response['data']['datetime'] = $datetime;
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
}
