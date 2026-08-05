
<div class="sidebar" id="sidebar">
           <div class="sidebar-inner slimscroll">

               <div id="sidebar-menu" class="sidebar-menu">
                   <ul>
                     <?php
$cid = get_user()["company_id"];

 foreach ($menus as $key => $value) {

     $select_this = false;

     if($value['url'] != null){
         $select_this = ($active_menu == $value["url"]) ? true : false;
     }

     
     if($value['sub_menus'] != null){


         $submenu_is_selected = array_search($active_menu, array_column($value['sub_menus'], 'url'));


         if(!is_bool($submenu_is_selected)){
             $select_this = true;
         }

     }

     //$select_this = true;

     //var_dump($active_menu);
     //var_dump($select_this);
     //die();



?>
<!-- show payroll for demo accounts -->
<?php if($value["title"] != "Payroll" || $cid == 3 || $cid == 1): ?>

   
<li class="<?php echo ($value["sub_menus"]) ? ' submenu ' : ''; ?> <?php echo ($select_this) ? ' active' : ''; ?>">
  <a href="<?php echo ($value["url"]) ?  base_url(). $value["url"] : 'javascript:void(0);' ?>" class=" main-menu waves-effect <?php echo ($select_this) ? ' active subdrop' : ''; ?>">
    <i class="<?php echo $value["icon"] ?>"></i>
    <span> <?php echo $value["title"] ?></span>

    <?php echo ($value["sub_menus"]) ? '   <span class="menu-arrow"></span> ' : ''; ?>





  </a>


    <?php if ($value['sub_menus'] != null) {
       $sub_menus = $value["sub_menus"];
     ?>


     <ul class="list-unstyled nested">
         <?php foreach ($sub_menus as $key_submenu => $value_submenu) {
         $select_this_submenu = false;


              if($value_submenu['url'] != null){
                 $select_this_submenu = ($active_menu == $value_submenu["url"]) ? true : false;

             }
         ?>

         <li class="nav-item <?php echo ($select_this_submenu) ? ' active ' : ''; ?>">
             <a class=" " href="<?php echo base_url(). $value_submenu["url"] ?>">
                 <i class="<?php echo $value_submenu["icon"] ?>"></i>
                 <span class="title"><?php echo $value_submenu["title"] ?></span>
             </a>
         </li>
           <?php } ?>

     </ul>

     <?php } ?>

 </li>

<?php endif; ?>







<?php

 }

?>


                     </ul>

                     <br/>
                     <br/>
                     <br/>
                     <br/>
                   </div>
                 </div>
               </div>


<!-- 
<div class="sidebar" id="sidebar">
 <div class="sidebar-inner slimscroll">

   <div id="sidebar-menu" class="sidebar-menu">
     <ul>
      <li class="">
        <a href="<?php echo base_url(); ?>invocore_payroll" class=" main-menu waves-effect  active subdrop">
          <i class="fa fa-chart-line"></i>
          <span> Dashboard</span>
        </a>
      </li>

      <li class=" submenu  ">
        <a href="javascript:void(0);" class=" main-menu waves-effect ">
          <i class="fa fa-user"></i>
          <span> Employees</span>

          <span class="menu-arrow"></span> 
        </a>




        <ul class="list-unstyled nested">

         <li class="nav-item ">
           <a class=" " href="<?php echo base_url(); ?>payroll_employees">
             <i class=""></i>
             <span class="title">Active</span>
           </a>
         </li>

         <li class="nav-item ">
           <a class=" " href="<?php echo base_url(); ?>payroll_employees/terminated">
             <i class=""></i>
             <span class="title">Terminated</span>
           </a>
         </li>

         <li class="nav-item ">
           <a class=" " href="<?php echo base_url(); ?>payroll_employees/resigned">
             <i class=""></i>
             <span class="title">Resigned</span>
           </a>
         </li>

       </ul>


     </li>


      <li class=" submenu  ">
        <a href="javascript:void(0);" class=" main-menu waves-effect ">
          <i class="fa fa-dollar"></i>
          <span> Payroll Management</span>

          <span class="menu-arrow"></span> 
        </a>




        <ul class="list-unstyled nested">

         <li class="nav-item ">
           <a class=" " href="<?php echo base_url(); ?>invocore_payroll/process_payroll">
             <i class=""></i>
             <span class="title">Process Payroll</span>
           </a>
         </li>

         <li class="nav-item ">
           <a class=" " href="<?php echo base_url(); ?>payroll/calculator">
             <i class=""></i>
             <span class="title">Calculator</span>
           </a>
         </li>

         <li class="nav-item ">
           <a class=" " href="<?php echo base_url(); ?>payroll/report">
             <i class=""></i>
             <span class="title">Check</span>
           </a>
         </li>

         <li class="nav-item ">
           <a class=" " href="javascript:void(0);">
             <i class=""></i>
             <span class="title">Approval</span>
           </a>
         </li>

       </ul>


     </li>

     <li class=" submenu  ">
      <a href="javascript:void(0);" class=" main-menu waves-effect ">
        <i class="fa fa-clipboard-list"></i>
        <span> Reports</span>

        <span class="menu-arrow"></span> 
      </a>




      <ul class="list-unstyled nested">

       <li class="nav-item ">
         <a class=" " href="javascript:void(0);">
           <i class=""></i>
           <span class="title">Report 1</span>
         </a>
       </li>

       <li class="nav-item ">
         <a class=" " href="javascript:void(0);">
           <i class=""></i>
           <span class="title">Report 2</span>
         </a>
       </li>

       <li class="nav-item ">
         <a class=" " href="javascript:void(0);">
           <i class=""></i>
           <span class="title">Report 3</span>
         </a>
       </li>

     </ul>


   </li>

   <li class=" submenu  ">
    <a href="javascript:void(0);" class=" main-menu waves-effect ">
      <i class="fa fa-gear"></i>
      <span> Settings</span>

      <span class="menu-arrow"></span> 
    </a>




    <ul class="list-unstyled nested">

     <li class="nav-item ">
       <a class=" " href="javascript:void(0);">
         <i class=""></i>
         <span class="title">Setting 1</span>
       </a>
     </li>

     <li class="nav-item ">
       <a class=" " href="javascript:void(0);">
         <i class=""></i>
         <span class="title">Setting 2</span>
       </a>
     </li>

     <li class="nav-item ">
       <a class=" " href="javascript:void(0);">
         <i class=""></i>
         <span class="title">Setting 3</span>
       </a>
     </li>

   </ul>


 </li>

 <li class="">
        <a href="<?php echo base_url(); ?>invocore_payroll/first_time_setup" class=" main-menu waves-effect  active subdrop">
          <i class="fa fa-cogs"></i>
          <span> First Time Setup</span>
        </a>
      </li>


</ul>

<br/>
<br/>
<br/>
<br/>
</div>
</div>
</div>
 -->