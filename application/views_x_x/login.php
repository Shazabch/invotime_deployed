<head>

   <link rel="shortcut icon" href="<?php echo base_url(); ?>favicon.ico" type="image/x-icon">
        <link rel="icon" href="<?php echo base_url(); ?>favicon.ico" type="image/x-icon">

  <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet" type="text/css" />
  <link href="https://cdn.materialdesignicons.com/1.7.22/css/materialdesignicons.min.css" rel="stylesheet">

  <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />


  <!-- <link href="<?php echo base_url(); ?>assets/css/style.css" rel="stylesheet" type="text/css" /> -->

          <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/style.css?v=1.0">

  <title>Invotime - <?php 
          if($company){
            echo $company;
          } 
          ?> 
        Login</title>

   <script src="<?php echo base_url(); ?>assets/js/jquery.min.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js" type="text/javascript"></script>

    <link href="<?php echo base_url(); ?>assets/toast/src/jquery.toast.css" rel="stylesheet" type="text/css">


<style>
   <?php

    if(antelope_config()["antelope_custom_theme"]):
      $primary = antelope_config()["antelope_theme_color_primary"];
      $primary_text = antelope_config()["antelope_theme_color_primary_text"];
      $secondary = antelope_config()["antelope_theme_color_secondary"];
      $secondary_text = antelope_config()["antelope_theme_color_secondary_text"];
      ?>

      .logo{
        color: <?php echo $primary ?> !important;
      }

      .btn-primary {
        background-color: <?php echo $primary ?>  !important;
        border: 1px solid <?php echo $primary ?>  !important;
      }

      .btn-primary:hover {
        opacity: 0.9 !important;
        background-color: <?php echo $primary ?>  !important;
        border: 1px solid <?php echo $primary ?>  !important;
      }

      .panel-primary{
        border: 3px solid <?php echo $primary ?> !important;
      }

   <?php endif; ?>
   </style>


<link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/style.css">

<script type="text/javascript">
  var js_base_url = "<?php echo base_url() ?>";
</script>


</head>
<body style="background: url(https://cdn.pixabay.com/photo/2015/07/28/22/01/office-865091_960_720.jpg); background-size: cover;">

   <div class="main-wrapper" ng-app="myApp" ng-controller="recoveryCtrl">
      <div class="account-page">
        <div class="container">
          <h2 style="color:white" class="account-title"><?php 

          if($company){
            echo "Invotime - ". $company;
          }
          else{
            echo $this->config->item("antelope_config")["antelope_brand_name"];

          }


           ?> Login</h2>
          <div class="account-box">
            <div class="account-wrapper">
              <div class="account-logo">


              </div>

               <?php if(isset($error)): ?>
               <div class="alert alert-danger">
                <strong>Wrong Credentials</strong> please try again.
                </div>
              <?php endif; ?>

              <form  method="post" class="form-horizontal m-t-20" action="<?php echo base_url() ?>user_management/login">
                <div class="form-group form-focus">
                  <label class="control-label">Email</label>
                  <input required="" name="email" class="form-control floating" type="text">
                </div>
                <div class="form-group form-focus">
                  <label class="control-label">Password</label>
                  <input required="" name="password" class="form-control floating" type="password">
                </div>
                <div class="form-group text-center">
                  <button class="btn btn-primary btn-block account-btn" type="submit">Login</button>
                </div>

                <div class="text-center">
                  <a href="#" data-toggle="modal" data-target="#recoveryModal">Forgot your password?</a>
                </div>

              </form>
            </div>
          </div>
        </div>
      </div>


      <div id="recoveryModal" class="modal fade" role="dialog">
            <div class="modal-dialog">

              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">Reset Password</h4>
                </div>
                <div class="modal-body">
                  <form ng-submit="recover_password()">
                    <div class="form-group">
                      <label for="email">Enter your email address</label>
                      <input type="email" class="form-control" id="email" ng-model="email" required="">
                    </div>


                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                  </form>
                </div>
              </div>

            </div>
          </div>




        </div>
        <div class="sidebar-overlay" data-reff="#sidebar"></div>
        <script type="text/javascript" src="<?php echo base_url(); ?>blue/assets/js/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>blue/assets/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>blue/assets/js/app.js"></script>

        <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular.min.js"></script>
        <script src='<?php echo base_url(); ?>assets/toast/src/jquery.toast.js'></script>
        <script src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.6/dist/loadingoverlay.min.js"></script>


 <!--   <div class="wrapper-page">
      <div class="panel panel-color panel-primary panel-pages">
         <div class="panel-body">
            <h3 class="text-center m-t-0 m-b-15"> <a href="#" class="logo logo-admin"><?php echo $this->config->item("antelope_config")["antelope_brand_name"] ?></a></h3>
            <h4 class="text-muted text-center m-t-0"><b>Sign In</b></h4>
            <form method="post" class="form-horizontal m-t-20" action="<?php echo base_url() ?>user_management/login">
               <div class="form-group">
                  <div class="col-xs-12"> <input name="email" class="form-control" type="email" required="" placeholder="Email"></div>
               </div>
               <div class="form-group">
                  <div class="col-xs-12"> <input name="password" class="form-control" type="password" required="" placeholder="Password"></div>
               </div>
               <div class="form-group">
                  <div class="col-xs-12">
                     <div class="checkbox checkbox-primary"> <input id="checkbox-signup" type="checkbox"> <label for="checkbox-signup"> Remember me </label></div>
                  </div>
               </div>
               <?php if(isset($error)): ?>
               <div class="alert alert-danger">
                <strong>Wrong Credentials</strong> please try again.
              </div>
            <?php endif; ?>

               <div class="form-group text-center m-t-40">
                  <div class="col-xs-12"> <button class="btn btn-primary btn-block btn-lg waves-effect waves-light" type="submit">Log In</button></div>
               </div>
               <div class="form-group m-t-30 m-b-0">
                  <div class="col-sm-7"> <a href="pages-recoverpw.html" class="text-muted"><i class="fa fa-lock m-r-5"></i> Forgot your password?</a></div>
                  <div class="col-sm-5 text-right"> <a href="pages-register.html" class="text-muted">Create an account</a></div>
               </div> 
            </form>
         </div>
      </div>
   </div> -->


   <script>
         function showNotification(heading, message, icon) {
          $.toast({
            heading: heading,
            showHideTransition: 'slide',
            text: message,
            textColor: "#ffffff",
            position: 'bottom-right',
            loaderBg: '#fff',
            icon: icon,
            hideAfter: 3000,
            stack: 10
          });
        }
      </script>
      <script>
        var config = {
          headers: {
            'Content-Type': 'application/json;charset=utf-8;'
          }
        };
        var js_base_url = "<?php echo base_url() ?>";
        var base_url = js_base_url;
        var app = angular.module('myApp', []);
        app.controller('recoveryCtrl', function($scope,$http) {

          $scope.recover_password = function(){
            $('body').LoadingOverlay("show");
            $http.post(base_url + 'recover_password/submit_email', {email : $scope.email}, config).then(function (response) {
              if(response.data.success){
                showNotification("Success",response.data.msg,"success");
                $scope.email = '';
                $("#recoveryModal").modal("hide");
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
                $('body').LoadingOverlay("hide");
              }else{
                showNotification("Error",response.data.msg,"error");
                $('body').LoadingOverlay("hide");
              }
            }, function (error) {
              console.log(error.data);
            });

          }
        });
      </script>

   <!-- <script src="<?php echo base_url(); ?>assets/js/modernizr.min.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/detect.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/fastclick.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/jquery.slimscroll.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/jquery.blockUI.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/waves.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/wow.min.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/jquery.nicescroll.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/jquery.scrollTo.min.js" type="text/javascript"></script>
   <script src="<?php echo base_url(); ?>assets/js/app.js" type="text/javascript"></script> -->

</body>
