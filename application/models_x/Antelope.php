<?php
class Antelope extends CI_Model {
  public function __construct()
  {
    parent::__construct();
  }
        //Antelope functions start ----------
  public function admin_accounts($xcrud){
    $xcrud->table('employees');
    $xcrud->unset_remove(true,'role_id','=',1);
    $xcrud->where('role_id =', 1);
    $xcrud->where('company_id =', get_user()["company_id"]);


    $xcrud->change_type('password', 'password', 'md5', 32);
    $xcrud->change_type('avatar','image','',array('width'=>200, 'height'=>200,'ratio'=>1.0, 'manual_crop'=>true)); // auto-crop
          //$xcrud->set_attr('permissions',array('class'=>'permissions_list'));
          //$xcrud->change_type('permissions','multiselect','',get_menus_for_user_management());
          $xcrud->fields('role_id,company_id,email_verified,api_token,updated_at,deleted_at,permissions,created_at', true);


          

          return '<h3>Page under construction</h3>';

          //return $xcrud->render();
        }

        public function my_profile($xcrud){
          $xcrud->table('employees');
          $xcrud->where('id =', get_user()["id"]);
          $xcrud->unset_remove();
          $xcrud->unset_add();
          $xcrud->unset_print();
          $xcrud->unset_csv();
          $xcrud->unset_search();
          $xcrud->unset_pagination();
          $xcrud->unset_limitlist();
          $xcrud->unset_sortable();
          $xcrud->unset_list();
          $xcrud->columns('role_id,permissions', true);
          $xcrud->fields('role_id,permissions', true);
          $xcrud->change_type('password', 'password', 'md5', 16);
          $xcrud->change_type('avatar','image','',array('width'=>200, 'height'=>200,'ratio'=>1.0, 'manual_crop'=>true)); // auto-crop
          return '<h3>Page under construction</h3>';

          return $xcrud->render('edit', get_user()["id"]);


        }
        //Antelope functions end ------------


//****************************************************************************************************************


        //Your functions start here
        // public function employees($xcrud){
        //   $xcrud->table('employees');
        //   $xcrud->where('role_id <>', 1);
        //   $xcrud->where('company_id =', get_user()["company_id"]);
        //   $xcrud->pass_var('company_id', get_user()["company_id"]);
        //   //$xcrud->join('department_id','departments','id','departments',true);
        //   $xcrud->relation('department_id','departments','id','name',array('company_id' => get_user()["company_id"]))->label('department_id','Department');
        //   $xcrud->relation('position_id','positions','id','title',array('company_id' => get_user()["company_id"]))->label('position_id','Position');

        //   $xcrud->fields('role_id,company_id,email_verified,api_token,updated_at,deleted_at,permissions,created_at', true);
        //   $xcrud->columns('photo,first_name,last_name,email,department_id,position_id');
        //   $xcrud->change_type('photo','image','',array('width'=>200, 'height'=>200, 'crop'=>true));
        //   $xcrud->change_type('password', 'password', 'md5', 32);

          
        //   return $xcrud->render();
        // }

         public function leaves($xcrud){
          $xcrud->table('shifts');
          $cid = get_user()["company_id"];
          //$xcrud->pass_var('company_id', get_user()["company_id"]);
          $xcrud->fields('company_id,name,color,code,is_paid');
          $xcrud->columns('company_id,name,color,code,is_paid');

          $xcrud->relation('company_id','companies','id','name',array('id' => $cid));
          $xcrud->label('company_id','Company');
          $xcrud->unset_print();
          $xcrud->unset_csv();

          $today = date('Y-m-d');

          // echo "SELECT a.date, GROUP_CONCAT(b.first_name ORDER BY b.id) emp FROM shift_days a INNER JOIN employees b ON FIND_IN_SET(b.id, a.employees) WHERE a.date = '$today' AND shift_id={id} LIMIT 1";
          // die();

          // $xcrud->subselect('Employees Today',"SELECT GROUP_CONCAT(b.first_name, '(' ,b.special_id, ')'  ORDER BY b.first_name SEPARATOR ', ') emp FROM shift_days a INNER JOIN employees b ON FIND_IN_SET(b.id, a.employees) WHERE a.date = '$today' AND shift_id={id} LIMIT 1");

          $cid = get_user()["company_id"];

           //if($cid != 1){

              $xcrud->where('company_id = ', $cid);

           //}

           $xcrud->where('is_leave = ', 'yes');

           $xcrud->pass_var('is_leave', 'yes');


           // $shift_days = $xcrud->nested_table('shift_days','id','shift_days','shift_id');
           // $shift_days->columns('date,employees,created_at');
           // $shift_days->fields('date,employees');
           // $shift_days->no_editor('employees');
           // $shift_days->duplicate_button();
           // //$shift_days->before_insert('check_shift_overlap');

           // //var_dump(get_company_employees());
           // //die();
           // $shift_days->change_type('employees','multiselect','',get_company_employees());
           // $shift_days->order_by('date','DESC');

            //naveed


          return $xcrud->render();
        }

        public function shifts($xcrud){
          $xcrud->table('shifts');
          $cid = get_user()["company_id"];
          $xcrud->pass_var('company_id', $cid);
          //echo "nav " . $cid;
          $xcrud->fields('updated_at,deleted_at,created_at,is_paid,is_leave', true);
          $xcrud->columns('updated_at,deleted_at,created_at,is_paid,is_leave', true);

          $xcrud->relation('company_id','companies','id','name',array('id' => $cid));
          $xcrud->label('company_id','Company');

          $today = date('Y-m-d');
          $xcrud->unset_print();
          $xcrud->unset_csv();

          // echo "SELECT a.date, GROUP_CONCAT(b.first_name ORDER BY b.id) emp FROM shift_days a INNER JOIN employees b ON FIND_IN_SET(b.id, a.employees) WHERE a.date = '$today' AND shift_id={id} LIMIT 1";
          // die();

          // $xcrud->subselect('Employees Today',"SELECT GROUP_CONCAT(b.first_name, '(' ,b.special_id, ')'  ORDER BY b.first_name SEPARATOR ', ') emp FROM shift_days a INNER JOIN employees b ON FIND_IN_SET(b.id, a.employees) WHERE a.date = '$today' AND shift_id={id} LIMIT 1");

           //if($cid != 1){

          $xcrud->where('company_id = ', $cid);

           //}

           $xcrud->where('is_leave = ', 'no');

           // $shift_days = $xcrud->nested_table('shift_days','id','shift_days','shift_id');
           // $shift_days->columns('date,employees,created_at');
           // $shift_days->fields('date,employees');
           // $shift_days->no_editor('employees');
           // $shift_days->duplicate_button();
           // //$shift_days->before_insert('check_shift_overlap');

           // //var_dump(get_company_employees());
           // //die();
           // $shift_days->change_type('employees','multiselect','',get_company_employees());
           // $shift_days->order_by('date','DESC');

            //naveed


          return $xcrud->render();
        }

        public function holiday_rates($xcrud){
          $xcrud->table('companies');
          $cid = get_user()["company_id"];
          $xcrud->where('id', $cid);
          $xcrud->fields('normal_weekend,public_holiday_normal,public_holiday_weekend',false,'Standard Hours');
          $xcrud->fields('normal_weekend_overtime,public_holiday_normal_overtime,public_holiday_weekend_overtime',false,'Overtime Hours');
          // $xcrud->columns('normal_weekend,public_holiday_normal,public_holiday_weekend');
          $xcrud->label('public_holiday_normal','Public Holiday Weekday');
          $xcrud->label('normal_weekend_overtime','Normal Weekend');
          $xcrud->label('public_holiday_normal_overtime','Public Holiday Weekday');
          $xcrud->label('public_holiday_weekend_overtime','Public Holiday Weekend');
          $xcrud->unset_add();
          $xcrud->unset_remove();
          $xcrud->unset_print();
          // $xcrud->unset_edit();
          $xcrud->unset_csv();
          $xcrud->unset_view();
          $xcrud->unset_list();
          $xcrud->unset_pagination();
          $xcrud->unset_search();
          return $xcrud->render('edit',$cid);
        }

        public function departments($xcrud){
          $xcrud->table('departments');
          $cid = get_user()["company_id"];
          //$xcrud->where('company_id =', get_user()["company_id"]);
          //$xcrud->pass_var('company_id', get_user()["company_id"]);
          $xcrud->fields('updated_at,deleted_at,created_at', true);
          $xcrud->columns('company_id,updated_at,deleted_at,created_at', true);

          $xcrud->relation('company_id','companies','id','name',array('id' => $cid));
          $xcrud->label('company_id','Company');

          

           //if($cid != 1){

              $xcrud->where('company_id = ', $cid);

            //}

            $permissions_level = get_user()["permissions_level"];

            if($permissions_level != "Company"){
                $xcrud->unset_remove();
                $xcrud->unset_add();
                $xcrud->unset_edit();
    
            }

          return $xcrud->render();
        }

        public function add_clocking($xcrud){
          $xcrud->table('clockings_news');
          
          $xcrud->join('employee_id','employees','id','employees',true);

          $xcrud->join('employees.branch_id','branches','id','branches',true);
          
          $cid = get_user()["company_id"];

          $xcrud->where('employees.company_id = ', $cid);

          $permissions_level = get_user()["permissions_level"];
          $limit_access_to_department = get_user()["limit_access_to_department"];
          $department_id = get_user()["department_id"];

          $device_where = array('company_id' => $cid);
          $employees_where = array('company_id' => $cid);

            if($permissions_level == "Company"){
                // $xcrud->unset_remove();
                // $xcrud->unset_add();
                // $xcrud->unset_edit();

                //$xcrud->where('company_id =', get_user()["company_id"]);
            }

            if($permissions_level == "Outlet"){
              // $xcrud->unset_remove();
              // $xcrud->unset_add();
              // $xcrud->unset_edit();

              //$xcrud->where('branch_id =', get_user()["branch_id"]);

              $device_where["branch_id"] = get_user()["branch_id"];
              $employees_where["branch_id"] = get_user()["branch_id"];
              $xcrud->where('employees.branch_id = ', get_user()["branch_id"]);

              
          }

          if($limit_access_to_department == "yes"){
            $employees_where["department_id"] = $department_id;
            $xcrud->where('employees.department_id = ', $department_id);
          }
        

          $xcrud->label("device_id","Device");
          $xcrud->label("shift_id","Shifts");
          $xcrud->label("branches.name","Outlet");
          $xcrud->label("employee_id","Employee");

          $xcrud->unset_remove();
          $xcrud->unset_print();
          $xcrud->unset_edit();
          $xcrud->unset_csv();
          $xcrud->unset_view();

        
          //add conditions for company and outlet where clause to the below line
          
          $xcrud->relation('shift_id',
          'shifts',
          'id',
          array('name','code'),
          array('company_id' => $cid),
          'name asc',
          false,
          ' - ');


          $xcrud->relation('device_id','devices','device_id',array('mac_address','location'),$device_where);

          $xcrud->relation('employee_id','employees','id','first_name',
          $employees_where,
          'first_name asc'

        );

          $xcrud->columns('employee_id,branches.name,shift_id,device_id,datetime');
          $xcrud->fields('employee_id,shift_id,device_id,datetime,mode,type');
          //$xcrud->order_by('datetime','desc');
          // $shift_days->fields('date,employees');

        
          //$xcrud->where('company_id = ', $cid);

            
          $xcrud->order_by('datetime','desc');


          return $xcrud->render();
        }


        public function devices($xcrud){
          $xcrud->table('devices');

          $cid = get_user()["company_id"];
          $bid = get_user()["branch_id"];

          $permissions_level = get_user()["permissions_level"];

          if($permissions_level == "Outlet"){
           $xcrud->unset_remove(true,'branch_id','!=',$bid);
           $xcrud->unset_edit(true,'branch_id','!=',$bid);

          }


          //$xcrud->pass_var('company_id', get_user()["company_id"]);
          $xcrud->fields('updated_at,deleted_at,created_at', true);
          $xcrud->columns('updated_at,deleted_at,created_at', true);


          $xcrud->relation('company_id','companies','id','name',array('id' => $cid));

          if($permissions_level == "Company"){
            $xcrud->relation('branch_id','branches','id','name','','','','','','company_id','company_id');

          }else{
            $xcrud->relation('branch_id','branches','id','name',array('id' => $bid),'','','','','company_id','company_id');

          }


          $xcrud->label('company_id','Company');
          $xcrud->label('branch_id','Outlet');

          $cid = get_user()["company_id"];

           //if($cid != 1){

          $xcrud->where('company_id = ', $cid);

            //}


          return $xcrud->render();
        }

        public function positions($xcrud){
          $xcrud->table('positions');
          //$xcrud->where('company_id =', get_user()["company_id"]);
          //$xcrud->pass_var('company_id', get_user()["company_id"]);
          $xcrud->fields('updated_at,deleted_at,created_at,department_id', true);
          $xcrud->columns('company_id,updated_at,deleted_at,created_at,department_id', true);

          

          $xcrud->label('company_id','Company');
          //$xcrud->label('department_id','Department');

          $cid = get_user()["company_id"];
          
          $xcrud->relation('company_id','companies','id','name',array('id' => $cid));
          //$xcrud->relation('department_id','departments','id','name',array('id' => $cid));
          //$xcrud->relation('department_id','departments','id','name','','','','','','company_id','company_id');
           //if($cid != 1){

              $xcrud->where('company_id = ', $cid);

            //}

            

            $permissions_level = get_user()["permissions_level"];

            if($permissions_level != "Company"){
                $xcrud->unset_remove();
                $xcrud->unset_add();
                $xcrud->unset_edit();
    
            }
            

            
          return $xcrud->render();
        }



        public function bakar($xcrud){

           return $xcrud->render();
        }


        public function students($xcrud){

          $xcrud->table('students');
          $xcrud->columns('name,father_name,photo');
          $xcrud->fields('name,father_name,photo');
          

          //$xcrud->change_type('photo','image','',array('width'=>300, 'height'=>300,'ratio'=>1.0, 'manual_crop'=>true)); // 
          //$xcrud->where('name = ', 'naveed');
          //$xcrud->where('id = ', 2);



           return $xcrud->render();
        }


        public function manage_subjects($xcrud){
          $xcrud->table('subjects');
          $xcrud->columns('subject_name,subject_teacher,student_id');
          $xcrud->fields('subject_name,subject_teacher');
          $xcrud->where('id = ', 2);



           return $xcrud->render();
        }



        //uvtivket code starts from here
        public function outlets($xcrud){

                    


           $xcrud->table('branches');
           $xcrud->buttons_position('left');



           $cid = get_user()["company_id"];
           $bid = get_user()["branch_id"];

           $permissions_level = get_user()["permissions_level"];

           if($permissions_level == "Outlet"){
            
            $xcrud->unset_edit(true,'id','!=',$bid);

           }

           $xcrud->unset_remove();
            $xcrud->unset_add();

          //  if($cid != 1){

             $xcrud->where('company_id = ', $cid);
          //   $xcrud->unset_remove();

          // }
          // else{
          //   $xcrud->unset_remove(true,'company_id','=',$cid);
          // }

           $xcrud->label('company_id','Company');
           $xcrud->label('pic','PIC');
           $xcrud->label('pic_contact','PIC Contact');
           $xcrud->validation_required('name,address,phone,pic,pic_contact');

           if($cid != 1){

           
            $xcrud->unset_add();
            //$xcrud->unset_edit();

           }


           $xcrud->columns('created_at,updated_at,deleted_at,weather_widget,logo_big,logo_small,timezone', true);
           $xcrud->fields('created_at,updated_at,deleted_at', true);


          $xcrud->change_type('timezone','select','Asia/Kuala_Lumpur',implode (",", timezone_identifiers_list()));

         //$xcrud->change_type('logo','image','',array('width'=>200, 'height'=>200,'ratio'=>1.0, 'manual_crop'=>true)); // 

          $xcrud->change_type('logo_big','image','',array('quality'=>95)); // 
          $xcrud->change_type('logo_small','image','',array('quality'=>95)); // 
          $xcrud->no_editor('weather_widget'); // 





        $xcrud->relation('company_id','companies','id','name',array('id' => $cid));


          $xcrud->order_by('id','desc');

        
          return $xcrud->render();
        }


   


        public function overtime($xcrud){

          $xcrud->table('companies');
           $xcrud->buttons_position('left');



           $cid = get_user()["company_id"];

           $permissions_level = get_user()["permissions_level"];

           if($permissions_level == "Outlet"){
           
              //$xcrud->unset_edit();
           }


           $xcrud->columns('pay_overtime,pay_after_hours,overtime_rate');
           $xcrud->fields('pay_overtime,pay_after_hours,overtime_rate');

           $xcrud->unset_list();
           $xcrud->unset_add();
           $xcrud->label('pay_overtime','Do you want to pay overtime?');
           $xcrud->label('pay_after_hours','Pay overtime after how many hours?');
           $xcrud->label('overtime_rate','What is the overtime rate?');
           $xcrud->field_tooltip('overtime_rate','For example 1.5 indicates normal hour rate * 1.5');


        
           return '<div class="col-md-6">'.$xcrud->render('edit', $cid).'</div>';



        }


        public function companies($xcrud){

                    


           $xcrud->table('companies');
           $xcrud->buttons_position('left');



           $cid = get_user()["company_id"];

           $permissions_level = get_user()["permissions_level"];

           if($permissions_level == "Outlet"){
           
              $xcrud->unset_edit();
            }

           //if($cid != 1){

            $xcrud->where('id = ', $cid);
            $xcrud->unset_remove();

          // }
          // else{
          //   $xcrud->unset_remove(true,'id','=',$cid);
          // }

           $xcrud->label('pic','PIC');
           $xcrud->label('pic_contact','PIC Contact');
           //$xcrud->label('industry_id','Industry');
           $xcrud->validation_required('name,address,phone,pic,pic_contact');

           //$xcrud->relation('industry_id','industries','id','name');

           //if($cid != 1){

           
            $xcrud->unset_add();
            //$xcrud->unset_edit();

           //}
           $xcrud->columns('name,address,phone,pic,pic_contact,logo');
           $xcrud->fields('name,address,phone,pic,pic_contact,logo');


        //$xcrud->relation('this_table_relation_id','other_table_name','other_table_id','other_table_display_field');

          $xcrud->change_type('logo','image','',array('quality'=>95)); // 
          //$xcrud->change_type('logo_small','image','',array('quality'=>100)); // 

          $xcrud->order_by('id','desc');

        
          return $xcrud->render();
        }

        public function tickets($xcrud){

          //var_dump(get_user()["permissions"]);

           $xcrud->table('tickets'); //this
           $xcrud->buttons_position('left');

           $xcrud->label('event_id','Event');
           $cid = get_user()["company_id"];


           if($cid != 1){

              $xcrud->where('company_id = ', $cid);

            }

           if($cid != 1){
        
            $xcrud->relation('company_id','companies','id','name',array('id' => $cid));

          }
          else
          { 
            $xcrud->relation('company_id','companies','id','name');
          }

          $xcrud->relation('event_id','events','id','event_name_english','','','','','','company_id','company_id');
          $xcrud->validation_required('ticket_type,ticket_price,ticket_limit,winner_ticket,event_id,company_id');
         

          $xcrud->subselect('Sold','SELECT COUNT(1) FROM ticket_transactions WHERE ticket_id = {id}');
          $xcrud->subselect('Scanned','SELECT COUNT(1) FROM ticket_scans WHERE ticket_id = {id}');

           
           

            $xcrud->columns('ticket_type,ticket_price,ticket_limit,Sold,Scanned,winner_ticket,company_id,event_id');
            $xcrud->fields('ticket_type,ticket_price,ticket_limit,winner_ticket,company_id,event_id');
            $xcrud->label('company_id','Company');
        //$xcrud->relation('event_id','events','id','event_name_english');

          //$xcrud->change_type('photo','image','',array('width'=>300, 'height'=>300,'ratio'=>1.0, 'manual_crop'=>true)); //

          $xcrud->order_by('id','desc'); 

        
          return $xcrud->render();
        }

         public function ticket_transactions($xcrud){

           $xcrud->table('ticket_transactions');
           $xcrud->buttons_position('left');

           $xcrud->button(base_url().'print_ticket?qr={qr_code}','Print','fa fa-print','',array('target'=>'_blank'));

           $xcrud->label('event_id','Event');
           $xcrud->label('ticket_id','Ticket');
           $xcrud->label('qr_code','QR Code');
           $xcrud->label('external_ticket','External');
           $xcrud->buttons_position('left');

           $xcrud->join('employee_id','employees','id','employees',true);
           $xcrud->join('ticket_id','tickets','id','tickets',true);
           $xcrud->column_cut(8);


           $cid = get_user()["company_id"];

           if($cid != 1){

              $xcrud->where('tickets.company_id = ', $cid);

            }



           $xcrud->label('employees.first_name','Sold By');

           //$xcrud->pass_var('qr_code','{event_id}{ticket_id}{visitor_name}{visitor_email}','edit');

           //$xcrud->before_update('hash_that_shit');
           $xcrud->replace_insert('hash_that_shit');


           $xcrud->columns('event_id,ticket_id,qr_code,visitor_name,paid_amount,employees.first_name,created_at,external_ticket');
           $xcrud->fields('event_id,ticket_id,visitor_name,visitor_phone,visitor_company,visitor_email,paid_amount');
           $xcrud->pass_var('employee_id', get_user()["id"], 'create');



           $xcrud->validation_required('event_id,ticket_id,visitor_name,visitor_phone,paid_amount');

           $xcrud->relation('event_id','events','id','event_name_english');
       
        //$xcrud->relation('ticket_id','tickets','id','ticket_type');
          $xcrud->relation('ticket_id','tickets','id',array('ticket_type','ticket_price'),'','','',' $','','event_id','event_id');

          $xcrud->order_by('id','desc');
          $xcrud->readonly('qr_code');

          //$xcrud->change_type('photo','image','',array('width'=>300, 'height'=>300,'ratio'=>1.0, 'manual_crop'=>true)); //    
          return $xcrud->render();
        }

        public function fast_ticket_transactions($xcrud){

           $xcrud->table('ticket_transactions');
           $xcrud->buttons_position('left');

           //$xcrud->columns('event_id,ticket_id,qr_code,paid_amount');
           //$xcrud->fields('event_id,ticket_id,paid_amount');
           $xcrud->label('event_id','Event');
           $xcrud->label('ticket_id','Ticket');
           $xcrud->label('qr_code','QR Code');
           $xcrud->label('external_ticket','External');
           $xcrud->label('employees.first_name','Sold By');
           $xcrud->column_cut(8);
          $xcrud->readonly('qr_code');

           $xcrud->button(base_url().'print_ticket?qr={qr_code}','Print','fa fa-print','',array('target'=>'_blank'));

           //$xcrud->pass_var('qr_code','{event_id}{ticket_id}{visitor_name}{visitor_email}','edit');

           //$xcrud->before_update('hash_that_shit');
           //$xcrud->before_insert('hash_that_shit');
           $xcrud->replace_insert('hash_that_shit');



           //$xcrud->columns('qr_code',true);
           //$xcrud->fields('qr_code',true);

           $xcrud->join('ticket_id','tickets','id','tickets',true);

           $cid = get_user()["company_id"];

           if($cid != 1){

              $xcrud->where('tickets.company_id = ', $cid);

            }

           $xcrud->join('employee_id','employees','id','employees',true);
           $xcrud->columns('event_id,ticket_id,qr_code,visitor_name,paid_amount,employees.first_name,created_at,external_ticket');
           $xcrud->fields('event_id,ticket_id,paid_amount');
           $xcrud->pass_var('employee_id', get_user()["id"], 'create');
           $xcrud->order_by('id','desc');

           $xcrud->validation_required('event_id,ticket_id,paid_amount');

           $xcrud->relation('event_id','events','id','event_name_english');
       
        //$xcrud->relation('ticket_id','tickets','id','ticket_type');
          $xcrud->relation('ticket_id','tickets','id',array('ticket_type','ticket_price'),'','','',' $','','event_id','event_id');

          $xcrud->order_by('id','desc');

          //$xcrud->change_type('photo','image','',array('width'=>300, 'height'=>300,'ratio'=>1.0, 'manual_crop'=>true)); //    
          return $xcrud->render();
        }


        public function employees($xcrud){

           $xcrud->table('employees');
           $xcrud->buttons_position('left');

           $cid = get_user()["company_id"];

           //if($cid != 1){
            $xcrud->where('company_id = ', $cid);
           //}
           



            if($cid != 1){
              
              $xcrud->relation('company_id','companies','id','name',array('id' => $cid));

            }
            else
            { 
              $xcrud->relation('company_id','companies','id','name');
            }
         
            $xcrud->relation('role_id','roles','id','job_name','','','','','','company_id','company_id');

            $xcrud->relation('department_id','departments','id','name','','','','','','company_id','company_id');
            $xcrud->relation('position_id','positions','id','title','','','','','','department_id','department_id');
            $xcrud->relation('branch_id','branches','id','name','','','','','','company_id','company_id');

        //$xcrud->relation('position','roles','id','job_name','','','','','','company_id','company_id');

            //$xcrud->subselect('Shift Today',"SELECT GROUP_CONCAT(b.first_name, '(' ,b.special_id, ')'  ORDER BY b.first_name SEPARATOR ', ') emp FROM shift_days a INNER JOIN employees b ON FIND_IN_SET(b.id, a.employees) WHERE a.date = '$today' AND shift_id={id} LIMIT 1");

            //$xcrud->subselect('Shift Today',"SELECT GROUP_CONCAT(b.first_name, '(' ,b.special_id, ')'  ORDER BY b.first_name SEPARATOR ', ') emp FROM shift_days a INNER JOIN employees b ON FIND_IN_SET(b.id, a.employees) WHERE a.date = '$today' AND shift_id={id} LIMIT 1");


        

        $xcrud->label('role_id','Role');
        $xcrud->label('department_id','Department');
        $xcrud->label('position_id','Position');
        $xcrud->label('branch_id','Branch');
        $xcrud->label('qr_barcode','Code');
        $xcrud->label('dob','DOB');
        $xcrud->label('pob','POB');
        $xcrud->label('epf_no','EPF No');
        $xcrud->label('address','Permanent Address');
        $xcrud->label('temp_address','Temporary Address');

        $xcrud->field_tooltip('qr_barcode','Leave it empty to generate automatically');


        //$xcrud->pass_var('qr_barcode', random_string(8), 'edit');
        //$xcrud->pass_var('qr_barcode', date('Y-m-d H:i:s'), 'edit');

        $xcrud->before_insert('generate_qr_barcode');
        $xcrud->before_update('generate_qr_barcode_update');

          //$xcrud->change_type('photo','image','',array('width'=>300, 'height'=>300,'ratio'=>1.0, 'manual_crop'=>true)); // 

        $xcrud->label('company_id','Company');
        $xcrud->columns('company_id,branch_id,first_name,photo,role_id,department_id,position_id,qr_barcode');

         $xcrud->fields('special_id,company_id,branch_id,role_id,department_id,position_id,first_name,last_name,email,password,address,temp_address,epf_no,dob,pob,sex,race,religion,nationality,mobile,house_phone,ic_passport,photo,hired_on,qr_barcode',false,'Basic Information');

         $xcrud->fields('salary,is_ot,ot_rate_percentage,grade,incentive,ot_hourly_rate',false,'Salary & OT');
         
         $xcrud->fields('emergency_relation,emergency_mobile,emergency_email,emergency_house_phone,emergency_office,emergency_address',false,'Emergency Contact');

         $xcrud->fields('license_class,license_no,license_expiry',false,'License Details');

         $xcrud->fields('bank_account_no,bank_name',false,'Bank Details');




        //$xcrud->validation_required('company_id,email,first_name,last_name,address,phone,role_id,username,location');
        $xcrud->change_type('photo','image','',array('width'=>200, 'height'=>200,'ratio'=>1.0, 'manual_crop'=>true));


        $xcrud->change_type('password', 'password', 'md5', 32);

        $xcrud->validation_required('special_id,branch_id,department_id,position_id,company_id,email,first_name,last_name,address,phone,photo,role_id,username,location,permissions');

        $xcrud->order_by('id','desc');

        //$xcrud->columns('job_no,updated_at,deleted_at,created_at',true);
        //$xcrud->fields('job_no,updated_at,deleted_at,created_at',true);

        
          return "<h2>Not uploaded yet</h2>";//$xcrud->render();
        }


         public function roles($xcrud){

           $xcrud->table('roles');
           $cid = get_user()["company_id"];
           $xcrud->buttons_position('left');

           //if($cid != 1){

              $xcrud->where('company_id = ', $cid);
           //}
           


          //if($cid != 1){

            $xcrud->relation('company_id','companies','id','name',array('id' => $cid));

          // }
          // else{ 
          
          //   $xcrud->relation('company_id','companies','id','name');

          // }

          //$xcrud->relation('department_id','departments','id','name',array('company_id' => get_user()["company_id"]))->label('department_id','Department');

        $xcrud->change_type('permissions','multiselect','',get_menus_for_user_management());

        $permissions_level = get_user()["permissions_level"];

        if($permissions_level != "Company"){
            $xcrud->unset_remove();
            $xcrud->unset_add();
            $xcrud->unset_edit();

        }
        


        $xcrud->label('company_id','Company');
        $xcrud->label('job_name','Role Name');
        $xcrud->columns('company_id,permissions,updated_at,deleted_at,created_at',true);
        $xcrud->fields('updated_at,deleted_at,created_at',true);
        $xcrud->validation_required('company_id,job_name');

        $xcrud->order_by('id','desc');

          //$xcrud->change_type('photo','image','',array('width'=>300, 'height'=>300,'ratio'=>1.0, 'manual_crop'=>true));

        
          return $xcrud->render();
        }

        public function ticket_scans($xcrud){

          $xcrud->table('ticket_scans');
           $cid = get_user()["company_id"];
           $xcrud->buttons_position('left');

           if($cid != 1){

              $xcrud->where('events.company_id = ', $cid);
           }

           $xcrud->join('ticket_transaction_id','ticket_transactions','id','ticket_transactions',true);
           $xcrud->join('scanned_by','employees','id','employees',true);

           $xcrud->join('ticket_transactions.ticket_id','tickets','id','tickets',true);

           $xcrud->join('tickets.event_id','events','id','events',true);
           $xcrud->join('events.company_id','companies','id','companies',true);

           $xcrud->columns('employees.first_name,companies.name, events.event_name_english,tickets.ticket_type,ticket_transactions.visitor_name,time_in');
           
          $xcrud->label('employees.first_name','Scanned By');
          $xcrud->label('companies.name','Company');
          $xcrud->label('events.event_name_english','Event');
          $xcrud->label('events.event_name_english','Event');
          $xcrud->label('time_in','Scan Time');

          $xcrud->order_by('time_in','desc');

           

           $xcrud->unset_remove();
           $xcrud->unset_edit();
           $xcrud->unset_add();

          return $xcrud->render();

        }


        public function public_holidays($xcrud){

          $xcrud->table('public_holidays');
          $xcrud->pass_var('company_id', get_user()["company_id"]);

          $xcrud->where('company_id =', get_user()["company_id"]);
          $xcrud->fields('company_id,updated_at,deleted_at,created_at', true);
          $xcrud->columns('company_id,updated_at,deleted_at,created_at', true);
          $xcrud->order_by('holiday_date', 'asc');


          return $xcrud->render();

        }






}
?>