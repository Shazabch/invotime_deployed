<!DOCTYPE html>

<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
  <meta charset="utf-8" />
  <title><?php

          $current_user = get_user();

          if ($current_user["permissions_level"] == "Company") {
            echo $current_user["company_name"];
          } else {
            echo $current_user["branch_name"];
          }

          ?> | <?php echo $pageTitle ?></title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="width=device-width, initial-scale=1" name="viewport" />
  <meta content="" name="description" />
  <meta content="" name="author" />
  <meta http-equiv="content-type" content="text/html;charset=UTF-8">

  <link rel="shortcut icon" href="<?php echo base_url(); ?>favicon.ico" type="image/x-icon">
  <link rel="icon" href="<?php echo base_url(); ?>favicon.ico" type="image/x-icon">

  <!-- 
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />

        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">



        <link href="https://cdn.materialdesignicons.com/1.7.22/css/materialdesignicons.min.css" rel="stylesheet">

        <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" type="text/css" />

 -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">





  <!-- <link href="<?php echo base_url(); ?>assets/css/style.css?v=1.0" rel="stylesheet" type="text/css" /> -->


  <!--New Theme CSS Start-->

  <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">


  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/line-awesome.min.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/plugins/morris/morris.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/style.css?v=1.1">

  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/dataTables.bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/select2.min.css">
  


  <!--New Theme CSS End-->

  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/toast/src/jquery.toast.css">



  <script type="text/javascript">
    var js_base_url = "<?php echo base_url() ?>";
  </script>

  <script src="<?php echo base_url(); ?>assets/js/jquery.min.js" type="text/javascript"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular.min.js"></script>


  <script src="https://code.highcharts.com/highcharts.js"></script>
  <script src="https://code.highcharts.com/modules/data.js"></script>
  <script src="https://code.highcharts.com/modules/exporting.js"></script>
  <script src="https://code.highcharts.com/gantt/modules/gantt.js"></script>
  <script src="https://unpkg.com/gasparesganga-jquery-loading-overlay@2.1.6/dist/loadingoverlay.min.js"></script>

  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/json-viewer.css">



  <link rel='stylesheet' href='<?php echo base_url(); ?>assets/fullcalendar/fullcalendar.css' />
  <script src='<?php echo base_url(); ?>assets/fullcalendar/lib/moment.min.js'></script>
  <script src='<?php echo base_url(); ?>assets/fullcalendar/fullcalendar.js'></script>
  <script src='<?php echo base_url(); ?>assets/toast/src/jquery.toast.js'></script>

  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/bootstrap-datetimepicker.min.css">

  <script type="text/template" id="task-template">
    <li class="task">
              <div class="task-container">
                <span class="task-action-btn task-check">
                  <span class="action-circle large complete-btn" title="Mark Complete">
                    <i class="material-icons">check</i>
                  </span>
                </span>
                <span class="task-label" contenteditable="true"></span>
                <span class="task-action-btn task-btn-right">
                  <span class="action-circle large" title="Assign">
                    <i class="material-icons">person_add</i>
                  </span>
                  <span class="action-circle large delete-btn" title="Delete Task">
                    <i class="material-icons">delete</i>
                  </span>
                </span>
              </div>
            </li>
          </script>
  <script src='<?php echo base_url(); ?>assets/js/task.js'></script>
  <script src='https://unpkg.com/selectable.js@latest/selectable.min.js'></script>
  <script src='https://unpkg.com/selectable-table-plugin@latest/selectable.table.min.js'></script>

  <script type="text/javascript" src="<?php echo base_url(); ?>blue/assets/js/freeze-table.min.js"></script>
  <script src="<?php echo base_url(); ?>assets/js/daterangepicker.js"></script>
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/daterangepicker.css">



  <style>
    .notifyjs-corner {
      top: 60px !important;
    }
  </style>

</head>

<body>


  <div class="main-wrapper">


    <div class="header">
      <div class="header-left">
        <a href="<?php echo base_url() ?>overview" class="logo logo-big">
          <img src="<?php echo base_url() ?>uploads/<?php echo ($current_user["permissions_level"] == "Company") ?  $current_user["company_logo"] : $current_user["logo_big"] ?>" height="40" alt="">
        </a>
        <a href="<?php echo base_url() ?>overview" class="logo logo-small">
          <img src="<?php echo base_url() ?>uploads/<?php echo ($current_user["permissions_level"] == "Company") ?  $current_user["company_logo"] : $current_user["logo_small"] ?>" height="30" alt="">
        </a>
      </div>
      <a id="toggle_btn" href="javascript:void(0);"><i class="la la-bars"></i></a>
      <div class="page-title-box pull-left">
        <p class="lead" style="color:white">Welcome <?php echo $current_user["first_name"] ?>, <b>

            <?php
            if ($current_user["permissions_level"] == "Company") {
              echo $current_user["company_name"];
            } else {
              echo $current_user["branch_name"];
            }

            $limit_access_to_department = $current_user["limit_access_to_department"];
            $department_name = $current_user["department_name"];

            if ($limit_access_to_department == 'yes') {

              echo "<span style='font-size:12px'> $department_name </span>";
            }


            ?></b></p>
      </div>
      <a id="mobile_btn" class="mobile_btn pull-left" href="#sidebar"><i class="fa fa-bars" aria-hidden="true"></i></a>
      <ul class="nav navbar-nav navbar-right user-menu pull-right">


        <li class="dropdown">
          <a href="#" class="dropdown-toggle user-link" data-toggle="dropdown" title="Admin">
            <!-- <span class="user-img"><img class="img-circle" src="<?php echo $current_user["photo"] ?>" width="40">
            </span>
              <span><?php //echo $current_user["first_name"] 
                    ?></span> -->


            <?php

            $permissions = explode(',', $current_user["permissions"]);
            $permissions_level = $current_user["permissions_level"];
            $limit_access_to_department = $current_user["limit_access_to_department"];
            $department_name = $current_user["department_name"];


            //if (in_array('everything', $permissions)) {

            if ($permissions_level == "Company") {

              echo '<span style="font-size:12px"> (Company Admin)</span>';
              if ($limit_access_to_department == 'yes') {

                echo "<span style='font-size:12px'> $department_name Department </span>";
              }
            }

            if ($permissions_level == "Outlet") {

              echo '<span style="font-size:12px"> Outlet Admin </span>';
              if ($limit_access_to_department == 'yes') {

                echo "<span style='font-size:12px'> ($department_name) </span>";
              }
            }

            //}

            ?>




            <i class="caret"></i>



          </a>

          <ul class="dropdown-menu">
            <li><a href="<?php echo base_url() ?>my_profile">My Profile</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="<?php echo base_url() ?>user_management/logout">Logout</a></li>
          </ul>
        </li>
      </ul>
      <div class="dropdown mobile-user-menu pull-right">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
        <ul class="dropdown-menu pull-right">
          <li><a href="<?php echo base_url() ?>my_profile">My Profile</a></li>
          <li><a href="#">Settings</a></li>
          <li><a href="<?php echo base_url() ?>user_management/logout">Logout</a></li>
        </ul>
      </div>
    </div>