<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Employee;
use App\Company;
use App\Branch;

use App\PublicHolidays;
//use App\Clockings;
use App\Clockings_new;
use App\Devices;
use DB;
use Hash;
use Carbon\Carbon;


class ApiController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   public function __construct()
   {

     
   }
    
    public function index()
    {
        //
    }
    
     public function get_deviceDetail(Request $request)
    {

        $response = array(); //NAVEED: always declare a response object.
        $response['data'] = null;
        $response['errors'] = null;

        $device = Devices::where('mac_address',$request->mac_address)->first();  
       
          if($device) {
               
                $response['data'] = $device;
                $response['data']['branch'] =$device->branch->company;
                //$response['data']['company'] =$device->branch->company;
                $response['success'] = true;
                // $response['data']['company'] = $device->branch->company;
             

                          
          } else {
                $response['success'] = false;
                $response['errors'] = "Device not found.";
          }

          return response()->json($response); //NAVEED: always return response at the end of every API

    }
         public function get_employees(Request $request)
    {

        $response = array(); //NAVEED: always declare a response object.
        $response['data'] = null;
        $response['errors'] = null;

        $mac_address = $request->mac_address;

        //$employees = Employee::all('id' , 'first_name', 'last_name' , 'special_id' , 'department_id' , 'qr_barcode');
         $employees = DB::select("SELECT employees.id,special_id as first_name,last_name,special_id,department_id,qr_barcode,device_role FROM employees 
INNER JOIN devices ON employees.company_id = devices.company_id 
WHERE employees.branch_id = devices.branch_id AND sync_action = 'SetUserData' AND devices.mac_address = '$mac_address' AND employees.deleted_at IS NULL AND employees.termination_type IS NULL AND employees.resignation_type IS NULL

UNION ALL

SELECT employees.id,special_id as first_name,last_name,special_id,department_id,qr_barcode,device_role FROM employees 
INNER JOIN devices ON employees.company_id = devices.company_id 
WHERE sync_action = 'SetUserDataAll' AND devices.mac_address = '$mac_address' AND employees.deleted_at IS NULL AND employees.termination_type IS NULL AND employees.resignation_type IS NULL");


         //echo "SELECT id,first_name,last_name,special_id,department_id,qr_barcode FROM employees INNER JOIN devices ON employees.company_id = devices.company_id WHERE devices.mac_address = '$mac_address'";
       
          if($employees) {
               
                $response['data'] = $employees;
                //$response['data']['branch'] =$device->branch->company;
                //$response['data']['company'] =$device->branch->company;
                $response['success'] = true;
                // $response['data']['company'] = $device->branch->company;
             

                          
          } else {
                $response['success'] = false;
                $response['errors'] = "Employees not found.";
          }

          return response()->json($response); //NAVEED: always return response at the end of every API

    }

    public function enter_fp_data(Request $request)
    {

        $response = array(); //NAVEED: always declare a response object.
        $response['data'] = null;
        $response['errors'] = null;

        $employee = Employee::findOrFail($request->employee_code);
       
          if($employee) {
                              
                $employee->fingerprint_data = $request->fingerprint_data;
                $employee->save(); 

                //$response['data'] = $employee;
                //$response['data']['branch'] =$device->branch->company;
                //$response['data']['company'] =$device->branch->company;
                $response['success'] = true;
                // $response['data']['company'] = $device->branch->company;
             

                          
          } else {
                $response['success'] = false;
                $response['errors'] = "Device not found.";
          }

          return response()->json($response); //NAVEED: always return response at the end of every API
    }

    public function login(Request $request)
    {

        $response = array(); //NAVEED: always declare a response object.
        $response['data'] = null;
        $response['errors'] = null;

        $user = Employee::where('email',$request->email)
        ->where('password',md5($request->password))
        ->first();  
        unset($user->password);
        unset($user->id);

          if($user) {
              // Update Token
              $apiToken = hash('sha256',uniqid(str_random(32)));

              $postArray = ['api_token' => $apiToken];
              $login = Employee::where('email',$request->email)->update($postArray);
              
              if($login) {
                $response['data'] = $user->toArray();
                $response['data']['company'] = $user->company;
                $response['data']['position'] = $user->position;
                $response['data']['department'] = $user->department;
                $response['data']['branch'] = $user->branch;
                $response['data']['role'] = $user->role;

              }
            
          } else {
                $response['errors'] = "User not found.";
          }

          return response()->json($response); //NAVEED: always return response at the end of every API

    }
    public function clocking(Request $request)
    {

        $UPLOAD_FOLDER_URL = env('UPLOAD_FOLDER_URL', 'localhost');



        $response = array(); //NAVEED: always declare a response object.
        $response['data'] = null;
        $response['errors'] = null;
        $response['success'] = false;
        $employee_code = "0";

        if(!$request->has('employee_code')){
            $response['errors'][] = 'employee_code_parameter_missing';
            
        }else{
          $employee_code = $request->employee_code;
        }

        if(!$request->has('mac_address')){
            $response['errors'][] = 'mac_address_parameter_missing';
        }

        if(!$request->has('scan_type') && !$request->has('scan_type')){
            $response['errors'][] = 'scan_type_parameter_missing';
        }

        if(!$request->has('action')){
            $response['errors'][] = 'action_parameter_missing';
        }


        // if(!$request->has('weather')){
        //     $response['errors'][] = 'weather_parameter_missing';
        // }

        

        //var_dump(count($response['errors']));


        //$user = Employee::where('qr_barcode',$request->employee_code)->first();

        

         

        // var_dump($user);
        // var_dump($response);
        // die();

            $device=Devices::where('mac_address',$request->mac_address)->first();



            if($device){

              $company_id = $device->company_id;

      $interval_minutes = $this->get_interval_minutes($company_id);

              // $user =  Employee::where(function ($query) use ($employee_code,$company_id) {
              //   $query->where('company_id', '=', $company_id)
              //     ->where('qr_barcode', '=', $employee_code)
              //     ->orWhere('special_id', '=', $employee_code)
              //     ->orWhere('user_device_id', '=', $employee_code);
              //   })->toSql();

                $user =  Employee::where('company_id', '=', $company_id)
                ->where(function($query) use ($employee_code,$company_id)
                {
                    $query->orWhere('qr_barcode', '=', $employee_code)
                    ->orWhere('special_id', '=', $employee_code)
                    ->orWhere('id', '=', $employee_code)
                    ->orWhere('user_device_id', '=', $employee_code);
                })->first();

              // var_dump($user);
              // die();


            if($user) {

                $datetime_tempx = str_replace("-T"," ",$request->datetime);
                $datetime_tempx = str_replace("Z","",$datetime_tempx);

                  //die("datetime exists");
                $dtx = Carbon::parse($datetime_tempx,$device->branch->timezone);

                $clocking_type = $device->branch->clocking_type;

                $current_date = $dt_temp = $dtx->toDateString();

                $prev_date = date("Y-m-d", strtotime($dt_temp." -1 day"));


                //$shift = DB::table('shift_days')->whereRaw("date = '".date("Y-m-d")."' AND FIND_IN_SET(".$user->id.",employees)")->first();
                $shift = DB::table('shift_days')->whereRaw("date = '".$dt_temp."' AND FIND_IN_SET(".$user->id.",employees)")->first();

                $prev_shift = DB::table('shift_days')->whereRaw("date = '".$prev_date."' AND FIND_IN_SET(".$user->id.",employees)")->first();

                $shift_id = 0;
                $prev_shift_id = 0;
                
                $overnight = false;
                $prev_overnight = false;

                if(!empty($shift)){
                  $shift_id = $shift->shift_id;
                  $shift_data = DB::table('shifts')->whereRaw("id = ".$shift_id)->first();


                  if($shift_data && $shift_data->overnight == "Yes"){
                    $overnight = true;
                  }


                }

                if(!empty($prev_shift)){
                  $prev_shift_id = $prev_shift->shift_id;
                  $shift_data = DB::table('shifts')->whereRaw("id = ".$prev_shift_id)->first();

                  if($shift_data && $shift_data->overnight == "Yes"){
                    $prev_overnight = true;
                  }
                }

                
                    $response['success'] = true;
                    $response['data']['special_id'] = $user->special_id;
                    $response['data']['first_name'] = $user->first_name;
                    $response['data']['last_name'] = $user->last_name;
                    $response['data']['photo'] = $UPLOAD_FOLDER_URL ."/". $user->photo;
                    $response['data']['qr_barcode'] = $user->qr_barcode;
                    $response['data']['company'] = $user->company->name;
                    $response['data']['branch'] = $user->branch->name;
                    //$response['data']['company'] = $user->company;
                    //$response['data']['position'] = $user->position;
                    //$response['data']['department'] = $user->department;
                    //$response['data']['branch'] = $user->branch;
                    //$response['data']['role'] = $user->role;

                    // var_dump($device->branch);
                    // die();

                    $dt = Carbon::now($device->branch->timezone);

                    //var_dump($dt);

                    if($request->has("datetime")){

                      $datetime_temp = str_replace("-T"," ",$request->datetime);
                      $datetime_temp = str_replace("Z","",$datetime_temp);

                      //die("datetime exists");
                      $dt = Carbon::parse($datetime_temp,$device->branch->timezone);
                    }

                    //var_dump(date("Y-m-d H:i:s", time() - date("Z")));



                    // var_dump($dt);
                    // var_dump($dt->toDateTimeString());
                    // die();



                    $checkData = Clockings_new::where('employee_id',$user->id)->where('datetime',$dt->toDateTimeString())->orderBy('id', 'ASC')->first();

                    // var_dump($checkData);
                    // die();

                    //nav
                    if($checkData == null){

                      // if($request->action == "in"){
                      //   $data = ['employee_id' => $user->id,'clock_in'=>$dt->toDateTimeString(),'device_id'=> $device->device_id,'scan_type_in'=> $request->scan_type,'weather'=>$request->weather,'shift_id'=>$shift_id];

                      // }else{
                      //   $data = ['employee_id' => $user->id,'clock_out'=>$dt->toDateTimeString(),'device_id'=> $device->device_id,'scan_type_in'=> $request->scan_type,'weather'=>$request->weather,'shift_id'=>$shift_id];

                      // }

                        //die('if $checkData == null');

                      

                      if($clocking_type == "alternate"){
                        // normal day shifts
                        if(($shift_id != 0 && !$overnight) || ($shift_id == 0 && !$prev_overnight)){
                          $last_clocking = Clockings_new::where('employee_id',$user->id)->whereRaw('date(datetime) = "'.$current_date.'"')->orderBy('datetime', 'DESC')->first();
                          if(!empty($last_clocking)){
                            $last_type = $last_clocking->type;
                            $current_type = $last_type == "out" ? "in" : "out";
                          }else{
                            $current_type = "in";
                          }

                          $data = ['employee_id' => $user->id,'type' => $current_type,'datetime'=>$dt->toDateTimeString(),'device_id'=> $device->device_id,'mode'=> $request->scan_type,'weather'=>$request->weather,'shift_id'=>$shift_id];
                        }
                        // overnight shifts
                        else if($overnight){
                          $datetime = $dt->toDateTimeString();

                          $hour = explode(":", explode(" ", $datetime)[1])[0];

                          if($hour > 12){
                            $last_clocking = Clockings_new::where('employee_id',$user->id)->whereRaw('datetime > "'.$current_date.' 12:00:00"')->orderBy('datetime', 'DESC')->first();
                            if(!empty($last_clocking)){
                              $last_type = $last_clocking->type;
                              $current_type = $last_type == "out" ? "in" : "out";
                            }else{
                              $current_type = "in";
                            }

                            $data = ['employee_id' => $user->id,'type' => $current_type,'datetime'=>$dt->toDateTimeString(),'device_id'=> $device->device_id,'mode'=> $request->scan_type,'weather'=>$request->weather,'shift_id'=>$shift_id];
                          }else if($prev_overnight){
                            $last_clocking = Clockings_new::where('employee_id',$user->id)->whereRaw('date(DATE_ADD(datetime, INTERVAL '.$interval_minutes.' MINUTE)) = "'.$current_date.'"')->orderBy('datetime', 'DESC')->first();
                            if(!empty($last_clocking)){
                              $last_type = $last_clocking->type;
                              $current_type = $last_type == "out" ? "in" : "out";
                            }else{
                              $current_type = "in";
                            }

                            $data = ['employee_id' => $user->id,'type' => $current_type,'datetime'=>$dt->toDateTimeString(),'device_id'=> $device->device_id,'mode'=> $request->scan_type,'weather'=>$request->weather,'shift_id'=>$prev_shift_id];
                          }else{
                            $last_clocking = Clockings_new::where('employee_id',$user->id)->whereRaw('date(datetime) = "'.$current_date.'"')->orderBy('datetime', 'DESC')->first();
                            if(!empty($last_clocking)){
                              $last_type = $last_clocking->type;
                              $current_type = $last_type == "out" ? "in" : "out";
                            }else{
                              $current_type = "in";
                            }

                            $data = ['employee_id' => $user->id,'type' => $current_type,'datetime'=>$dt->toDateTimeString(),'device_id'=> $device->device_id,'mode'=> $request->scan_type,'weather'=>$request->weather,'shift_id'=>$shift_id];
                          }

                        }
                        // previous overnight shifts
                        else if($prev_overnight){
                          $datetime = $dt->toDateTimeString();

                          $hour = explode(":", explode(" ", $datetime)[1])[0];

                          if($hour < 12){
                            $last_clocking = Clockings_new::where('employee_id',$user->id)->whereRaw('date(DATE_ADD(datetime, INTERVAL '.$interval_minutes.' MINUTE)) = "'.$current_date.'"')->orderBy('datetime', 'DESC')->first();
                            if(!empty($last_clocking)){
                              $last_type = $last_clocking->type;
                              $current_type = $last_type == "out" ? "in" : "out";
                            }else{
                              $current_type = "in";
                            }

                            $data = ['employee_id' => $user->id,'type' => $current_type,'datetime'=>$dt->toDateTimeString(),'device_id'=> $device->device_id,'mode'=> $request->scan_type,'weather'=>$request->weather,'shift_id'=>$prev_shift_id];
                          }else{
                           $last_clocking = Clockings_new::where('employee_id',$user->id)->whereRaw('date(datetime) = "'.$current_date.'"')->orderBy('datetime', 'DESC')->first();
                            if(!empty($last_clocking)){
                              $last_type = $last_clocking->type;
                              $current_type = $last_type == "out" ? "in" : "out";
                            }else{
                              $current_type = "in";
                            }

                            $data = ['employee_id' => $user->id,'type' => $current_type,'datetime'=>$dt->toDateTimeString(),'device_id'=> $device->device_id,'mode'=> $request->scan_type,'weather'=>$request->weather,'shift_id'=>$shift_id]; 
                          }
                          
                        }
                      }else{
                        $data = ['employee_id' => $user->id,'type' => $request->action,'datetime'=>$dt->toDateTimeString(),'device_id'=> $device->device_id,'mode'=> $request->scan_type,'weather'=>$request->weather,'shift_id'=>$shift_id];
                      }
                        

                        //var_dump($data);
                        //die();
                        
                        $clocking = Clockings_new::create($data);

                        $response['data']['datetime'] = $clocking->datetime;
                        $response['data']['type'] = $clocking->type;

                    }
                    else{

                      $response['data']['datetime'] = $checkData->datetime;
                      $response['data']['type'] = $checkData->type;

                    }
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
        }
        else {
            $response['errors'][] = "user_not_found";
        }

        }
                else{
                    $response['errors'][] = "device_not_found";    
                }

        if($response['errors'] != null){
            $response['errors'] = implode(",",$response['errors']);
        }

        //var_dump($clocking->clock_in);
        //die();

        return response()->json($response); //NAVEED: always return response at the end of every API
    }

    


    public function fcmtoken_referesh(Request $request){
        
        $response = array(); //NAVEED: always declare a response object.
        $response['success'] = null;
        $fcm_token = ['fcm_token' => $request->fcm_token];
        $update = Employee::where('api_token',$request->api_token)->update($fcm_token);
        if($update) {
            $response['success'] = true;
        }else {
            $response['success'] = false;
        }
        return response()->json($response); 
    }  
     public function get_publicholidays(Request $request){
        
        $response = array(); //NAVEED: always declare a response object.
        $response['data'] = null;
          $response['errors'] = null;
      
        $user = Employee::where('api_token',$request->api_token)->first();
        if($user){
      
            $company_id=$user->company->id;
            $get_publicholidays=PublicHolidays::where('company_id',$company_id)->first();
            if($get_publicholidays) {
                $response['data'] =  $get_publicholidays;
            }else {
                $response['data'] = false;
            }
        }
        else{
            $response['errors'] = "User not found.";
        }  
         return response()->json($response); 
    }  
   

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    function get_interval_minutes($cid){
      $settings = [
        66 => "06:00",
        97 => "06:00",
        121 => "04:59",
        85 => "10:30",
        71 => "00:30"
      ];
      
      if(isset($settings[$cid])){
        $time = $settings[$cid];
      }else{
        $time = "07:00";
      }
    
      $time = explode(":", $time);
      $minutes = $time[0] * 60 + $time[1];
    
      return $minutes;
    }
}
