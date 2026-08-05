
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
