<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Employee;
use DB;
use Hash;


class EmployeesController extends Controller
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
    
    public function login(Request $request)
    {

        // $validatedData = $request->validate([
        //     'email' => 'required',
        //     'passwordx' => 'required'
        // ]);

        // var_dump($validatedData);



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
              }
            
          } else {
                $response['errors'] = "User not found.";
          }

          return response()->json($response); //NAVEED: always return response at the end of every API

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
}
