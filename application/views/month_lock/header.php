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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css"
    integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">


  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/line-awesome.min.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/plugins/morris/morris.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/style.css?v=1.2">

  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/dataTables.bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/select2.min.css">



  <!--New Theme CSS End-->

  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/toast/src/jquery.toast.css">



  <script type="text/javascript">
    var js_base_url = "<?php echo base_url() ?>";
  </script>

  <script src="<?php echo base_url(); ?>assets/js/jquery.min.js" type="text/javascript"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"
    type="text/javascript"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular.min.js"></script>


  <script src="https://code.highcharts.com/highcharts.js"></script>
  <script src="https://code.highcharts.com/modules/data.js"></script>
  <script src="https://code.highcharts.com/modules/exporting.js"></script>
  <script src="https://code.highcharts.com/gantt/modules/gantt.js"></script>
  <script src='<?php echo base_url(); ?>blue/assets/js/loadingOverLay.js'></script>
  <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/json-viewer.css">



  <link rel='stylesheet' href='<?php echo base_url(); ?>assets/fullcalendar/fullcalendar.css' />
  <script src='<?php echo base_url(); ?>assets/fullcalendar/lib/moment.min.js'></script>
  <script src='<?php echo base_url(); ?>assets/fullcalendar/fullcalendar.js'></script>
  <script src='<?php echo base_url(); ?>assets/toast/src/jquery.toast.js'></script>

  <link rel="stylesheet" type="text/css"
    href="<?php echo base_url(); ?>blue/assets/css/bootstrap-datetimepicker.min.css">

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
  <script src='<?php echo base_url(); ?>assets/js/selectable.min.js?v=1.1'></script>
  <script src='<?php echo base_url(); ?>assets/js/selectable.table.min.js'></script>

  <script type="text/javascript" src="<?php echo base_url(); ?>blue/assets/js/freeze-table.min.js"></script>
  <script src="<?php echo base_url(); ?>assets/js/daterangepicker.js"></script>
  <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/daterangepicker.css">



  <style>
    .notifyjs-corner {
      top: 60px !important;
    }

    /* ============================================================
       Google-style "Quick Access" Favourites Dropdown Panel
    ============================================================ */

    /* Trigger link clean-up */
    .user-menu .user-link {
      display: flex !important;
      align-items: center;
      gap: 6px;
      padding: 6px 14px !important;
      border-radius: 30px !important;
      transition: background 0.2s ease;
      text-decoration: none !important;
    }

    .user-menu .user-link:hover {
      background: rgba(0, 0, 255, 0.06) !important;
    }

    /* The panel itself */
    .user-menu .dropdown-menu.user-favourites-panel {
      min-width: 300px !important;
      padding: 0 !important;
      border-radius: 20px !important;
      border: 1px solid #e8eaed !important;
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.16), 0 4px 12px rgba(0, 0, 0, 0.08) !important;
      overflow: hidden;
      background: #ffffff !important;
      right: 0 !important;
      left: auto !important;
      margin-top: 12px !important;
      animation: favPanelIn 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
      transform-origin: top right;
    }

    @keyframes favPanelIn {
      from {
        opacity: 0;
        transform: scale(0.92) translateY(-10px);
      }

      to {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
    }

    /* Panel header row */
    .user-favourites-panel .fav-panel-header {
      display: flex !important;
      align-items: center !important;
      justify-content: space-between !important;
      padding: 18px 22px 14px !important;
      border-bottom: 1px solid #f1f3f4 !important;
      background: #ffffff !important;
    }

    .fav-panel-title {
      font-family: 'Inter', 'Montserrat', sans-serif !important;
      font-size: 14px !important;
      font-weight: 700 !important;
      color: #202124 !important;
      letter-spacing: 0.1px !important;
    }

    .fav-panel-edit-btn {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: #f1f3f4;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      cursor: pointer;
      transition: background 0.2s, transform 0.2s;
      color: #5f6368;
      font-size: 13px;
      border: none;
      outline: none;
    }

    .fav-panel-edit-btn:hover {
      background: #e2e5e9;
      color: #202124;
      transform: rotate(15deg);
    }

    /* Grid wrapper — must be block for Bootstrap list-item */
    .user-favourites-panel .fav-panel-grid {
      display: grid !important;
      grid-template-columns: repeat(3, 1fr) !important;
      gap: 2px !important;
      padding: 14px 10px 18px !important;
      background: #ffffff !important;
    }

    /* Individual item */
    .user-favourites-panel .fav-item {
      display: flex !important;
      flex-direction: column !important;
      align-items: center !important;
      justify-content: center !important;
      padding: 14px 10px !important;
      border-radius: 14px !important;
      text-decoration: none !important;
      transition: background 0.18s ease, transform 0.18s ease !important;
      gap: 10px !important;
      cursor: pointer !important;
      border: none !important;
    }

    .user-favourites-panel .fav-item:hover {
      background: #f5f7fa !important;
      transform: translateY(-3px) !important;
      text-decoration: none !important;
    }

    .user-favourites-panel .fav-item:active {
      transform: translateY(0) scale(0.96) !important;
    }

    /* Coloured circle icon */
    .fav-icon {
      width: 56px !important;
      height: 56px !important;
      border-radius: 50% !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      font-size: 22px !important;
      color: #ffffff !important;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18) !important;
      transition: box-shadow 0.2s, transform 0.2s !important;
      flex-shrink: 0 !important;
    }

    .user-favourites-panel .fav-item:hover .fav-icon {
      box-shadow: 0 8px 22px rgba(0, 0, 0, 0.25) !important;
      transform: scale(1.08) !important;
    }

    /* Label text */
    .fav-label {
      font-family: 'Inter', 'Montserrat', sans-serif !important;
      font-size: 12px !important;
      font-weight: 500 !important;
      color: #3c4043 !important;
      text-align: center !important;
      line-height: 1.3 !important;
      white-space: nowrap !important;
    }

    /* Divider before logout */
    .user-favourites-panel .fav-divider {
      height: 1px;
      background: #f1f3f4;
      margin: 0 16px 4px;
    }

    /* Mobile dropdown — keep existing simple style */
    .mobile-user-menu .dropdown-menu li a {
      padding: 8px 16px;
    }
  </style>

</head>

<body class="<?php echo (isset($is_month_lock) && $is_month_lock) ? 'month-lock-light' : ''; ?>">

  <?php if (isset($is_month_lock) && $is_month_lock): ?>
    <style>
      /* Premium Month Lock Light Theme Overrides */
      body.month-lock-light {
        background-color: #f4f6f9 !important;
      }

      /* Modern Light Scrollbar */
      body.month-lock-light *::-webkit-scrollbar {
        width: 8px;
        height: 8px;
      }

      body.month-lock-light *::-webkit-scrollbar-track {
        background: #f1f5f9;
      }

      body.month-lock-light *::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
        border: 2px solid #f1f5f9;
      }

      body.month-lock-light *::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
      }

      body.month-lock-light .header {
        background: #ffffff !important;
        border-bottom: 1px solid #edf2f7 !important;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.03) !important;
        height: 64px !important;
      }

      body.month-lock-light .header-left {
        background: #ffffff !important;
        border-right: 1px solid #edf2f7 !important;
        height: 64px !important;
      }

      body.month-lock-light #toggle_btn {
        color: #94a3b8 !important;
        margin-top: 20px !important;
      }

      body.month-lock-light .page-title-box {
        padding: 20px !important;
      }

      body.month-lock-light .page-title-box p {
        color: #2d3748 !important;
        font-weight: 700 !important;
        font-size: 16px !important;
        margin: 0 !important;
      }

      /* Modernized Light Sidebar */
      body.month-lock-light #sidebar {
        background-color: #ffffff !important;
        border-right: 1px solid #edf2f7 !important;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.03) !important;
        top: 64px !important;
      }

      body.month-lock-light #sidebar .sidebar-inner {
        background-color: #ffffff !important;
      }

      body.month-lock-light .sidebar-menu ul li a {
        color: #64748b !important;
        font-weight: 500 !important;
        padding: 8px 15px !important;
        margin: 1px 12px !important;
        border-radius: 8px !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
      }

      /* Submenu Enhancement */
      body.month-lock-light .sidebar-menu ul.nested {
        background-color: #ffffff !important;
        padding: 2px 0 5px 35px !important;
        position: relative !important;
      }

      body.month-lock-light .sidebar-menu ul.nested::before {
        content: '';
        position: absolute;
        left: 24px;
        top: 0;
        bottom: 15px;
        width: 2px;
        background: #f1f5f9;
        border-radius: 2px;
      }

      body.month-lock-light .sidebar-menu ul.nested li a {
        margin: 1px 8px 1px 0 !important;
        padding: 6px 12px !important;
        font-size: 12px !important;
        background: transparent !important;
        box-shadow: none !important;
      }

      body.month-lock-light .sidebar-menu ul.nested li a:hover {
        background: #f8fafc !important;
        color: #4f46e5 !important;
      }

      body.month-lock-light .sidebar-menu ul.nested li a.active {
        color: #4f46e5 !important;
        background: #f5f3ff !important;
        font-weight: 600 !important;
      }

      body.month-lock-light .sidebar-menu ul li a:hover {
        background: #f8fafc !important;
        color: #4f46e5 !important;
        transform: translateX(3px);
      }

      body.month-lock-light .sidebar-menu ul li.active a {
        background: #f5f3ff !important;
        color: #4f46e5 !important;
        font-weight: 700 !important;
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1) !important;
      }

      /* Colorful Icons */
      body.month-lock-light .sidebar-menu ul li a i {
        font-size: 16px !important;
        margin-right: 8px !important;
        width: 20px !important;
        text-align: center !important;
        transition: transform 0.2s;
      }

      body.month-lock-light .sidebar-menu ul li a:hover i {
        transform: scale(1.15);
      }

      /* Custom Icon Colors based on title */
      body.month-lock-light a[title="OT Sheet"] i {
        color: #f59e0b !important;
      }

      body.month-lock-light a[title="Late Sheet"] i {
        color: #ef4444 !important;
      }

      body.month-lock-light a[title="Late Break Sheet"] i {
        color: #8b5cf6 !important;
      }

      body.month-lock-light a[title="Early Out Sheet"] i {
        color: #ec4899 !important;
      }

      body.month-lock-light a[title="Main Sheet"] i {
        color: #10b981 !important;
      }

      body.month-lock-light a[title="Attendance"] i {
        color: #10b981 !important;
      }

      body.month-lock-light a[title="Locked Data"] i {
        color: #3b82f6 !important;
      }

      body.month-lock-light a[title="Dashboard"] i {
        color: #6366f1 !important;
      }

      body.month-lock-light a[title="Latest Activity"] i {
        color: #f59e0b !important;
      }

      body.month-lock-light a[title="Absents"] i {
        color: #6b7280 !important;
      }

      body.month-lock-light a[title="Lates"] i {
        color: #ef4444 !important;
      }

      body.month-lock-light a[title="All Reports"] i {
        color: #06b6d4 !important;
      }

      body.month-lock-light .sidebar-menu li.menu-title {
        color: #94a3b8 !important;
        font-size: 10px !important;
        text-transform: uppercase !important;
        letter-spacing: 1.5px !important;
        padding: 25px 30px 10px !important;
        font-weight: 700 !important;
      }

      /* Content Cards with Cute Shadows */
      body.month-lock-light .panel,
      body.month-lock-light .card {
        border-radius: 20px !important;
        border: 1px solid #edf2f7 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
        background: #fff !important;
        transition: transform 0.3s;
      }

      body.month-lock-light .panel:hover {
        transform: translateY(-2px);
      }

      /* Sidebar Search Box */
      body.month-lock-light .sidebar-search-wrapper {
        background: #ffffff !important;
        border-bottom: 1px solid #f1f5f9 !important;
      }

      body.month-lock-light #ml-menu-search:focus {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.05) !important;
        outline: none !important;
      }

      body.month-lock-light .panel-heading {
        background: #fafbfc !important;
        border-bottom: 1px solid #edf2f7 !important;
        padding: 20px 25px !important;
        border-radius: 20px 20px 0 0 !important;
      }
      /* Attendance Status Colors */
      .fa-calendar-check { color: #10b981 !important; } /* Present - Emerald */
      .fa-calendar-plus { color: #6366f1 !important; }  /* Leave - Indigo */
      .fa-calendar-times { color: #f43f5e !important; } /* Absent - Rose */
      .fa-calendar-o { color: #94a3b8 !important; }     /* Rest/Off - Slate */
      .fa-calendar-minus { color: #f59e0b !important; } /* Other/Late - Amber */

    </style>
  <?php endif; ?>

<style>
    .page-wrapper {
        padding-top: 0 !important;
      }
</style>
  <div class="main-wrapper">


    <div class="header">
      <div class="header-left">
        <a href="<?php echo base_url() ?>overview" class="logo logo-big">
          <img
            src="<?php echo base_url() ?>uploads/<?php echo ($current_user["permissions_level"] == "Company") ? $current_user["company_logo"] : $current_user["logo_big"] ?>"
            height="40" alt="">
        </a>
        <a href="<?php echo base_url() ?>overview" class="logo logo-small">
          <img
            src="<?php echo base_url() ?>uploads/<?php echo ($current_user["permissions_level"] == "Company") ? $current_user["company_logo"] : $current_user["logo_small"] ?>"
            height="30" alt="">
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

      <!-- ======================================================
           RIGHT NAVBAR — Desktop user dropdown (Google-style panel)
      ====================================================== -->
      <ul class="nav navbar-nav navbar-right user-menu pull-right">

        <li class="dropdown">
          <a href="#" class="dropdown-toggle user-link"  data-toggle="dropdown" title="Admin">

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

          <!-- ── Google-style Favourites Panel ── -->
          <ul class="dropdown-menu user-favourites-panel">

            <!-- Header -->
            <li class="fav-panel-header">
              <span class="fav-panel-title">Quick Access</span>
              <span class="fav-panel-edit-btn"><i class="fa fa-pen"></i></span>
            </li>

            <!-- Icon Grid -->
            <li class="fav-panel-grid">

              <!-- My Profile -->
              <a href="<?php echo base_url() ?>my_profile" class="fav-item">
                <span class="fav-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                  <i class="fa fa-user"></i>
                </span>
                <span class="fav-label">My Profile</span>
              </a>

              <!-- Settings -->
              <!-- <a href="#" class="fav-item">
                <span class="fav-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                  <i class="fa fa-cog"></i>
                </span>
                <span class="fav-label">Settings</span>
              </a> -->

              <!-- Logout -->
              <a href="<?php echo base_url() ?>user_management/logout" class="fav-item">
                <span class="fav-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                  <i class="fa fa-sign-out-alt"></i>
                </span>
                <span class="fav-label">Logout</span>
              </a>
              <a href="<?php echo base_url() ?>welcome" class="fav-item">
                <span class="fav-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                  <i class="fa fa-arrow-left"></i>
                </span>
                <span class="fav-label">Back to Main</span>
              </a>

            </li>
            <!-- /Icon Grid -->

          </ul>
          <!-- /Google-style Favourites Panel -->

        </li>
      </ul>

      <!-- ======================================================
           Mobile dropdown — unchanged logic, styling kept simple
      ====================================================== -->
      <div class="dropdown mobile-user-menu pull-right">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i
            class="fa fa-ellipsis-v"></i></a>
        <ul class="dropdown-menu pull-right">
          <li><a href="<?php echo base_url() ?>my_profile">My Profile</a></li>
          <li><a href="#">Settings</a></li>
          <li><a href="<?php echo base_url() ?>user_management/logout">Logout</a></li>
        </ul>
      </div>

    </div>