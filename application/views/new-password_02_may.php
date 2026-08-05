<head>

 <link rel="shortcut icon" href="<?php echo base_url(); ?>favicon.ico" type="image/x-icon">
 <link rel="icon" href="<?php echo base_url(); ?>favicon.ico" type="image/x-icon">

 <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
 <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet" type="text/css" />
 <link href="https://cdn.materialdesignicons.com/1.7.22/css/materialdesignicons.min.css" rel="stylesheet">

 <link href="<?php echo base_url(); ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />


 <link href="<?php echo base_url(); ?>assets/css/style.css" rel="stylesheet" type="text/css" />
 <link href="<?php echo base_url(); ?>assets/toast/src/jquery.toast.css" rel="stylesheet" type="text/css">


 <script src="<?php echo base_url(); ?>assets/js/jquery.min.js" type="text/javascript"></script>
 <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js" type="text/javascript"></script>


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


</head>
<body>
 <div class="wrapper-page" ng-app="myApp" ng-controller="resetCtrl" ng-init="pwd.id = '<?php echo $emp->id;?>'">
  <div class="panel panel-color panel-primary panel-pages">
   <div class="panel-body">
    <h3 class="text-center m-t-0 m-b-15"> <a href="#" class="logo logo-admin"><?php echo $this->config->item("antelope_config")["antelope_brand_name"] ?></a></h3>
    <h4 class="text-muted text-center m-t-0"><b><?php echo $emp->first_name; ?></b></h4>
    <form ng-submit="reset_pwd()">
     <div class="form-group">
      <label for="new_pwd">New Password</label>
      <input type="password" class="form-control" id="new_pwd" ng-model="pwd.new_password" required="">
    </div>
    <div class="form-group">
      <label for="c_pwd">Confirm Password</label>
      <input type="password" class="form-control" id="c_pwd" ng-model="pwd.confirm_password" required="">
    </div>


    <div class="form-group text-center m-t-40">
      <div class="col-xs-12"> <button class="btn btn-primary btn-block btn-lg waves-effect waves-light" type="submit">Reset Password</button></div>

    </div>

  </form>
</div>
</div>



</div>



<script src="<?php echo base_url(); ?>assets/js/modernizr.min.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/detect.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/fastclick.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/jquery.slimscroll.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/jquery.blockUI.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/waves.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/wow.min.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/jquery.nicescroll.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/jquery.scrollTo.min.js" type="text/javascript"></script>
<script src="<?php echo base_url(); ?>assets/js/app.js" type="text/javascript"></script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular.min.js"></script>
<script src='<?php echo base_url(); ?>assets/toast/src/jquery.toast.js'></script>
<script src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.6/dist/loadingoverlay.min.js"></script>
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
  app.controller('resetCtrl', function($scope,$http) {

    $scope.reset_pwd = function(){
      $('body').LoadingOverlay("show");
      if($scope.pwd.new_password != $scope.pwd.confirm_password){
        showNotification("Error","New Password and Confirm Password does not match!","error");
        $('body').LoadingOverlay("hide");
      }else{
        $http.post(base_url + 'recover_password/reset_pwd', $scope.pwd, config).then(function (response) {
          if(response.data.success){
            $scope.pwd.new_password = '';
            $scope.pwd.confirm_password = '';
            showNotification("Success",response.data.msg,"success");
            $('body').LoadingOverlay("hide");
            setTimeout(function() {
              location.reload();
            }, 3000);
          }else{
            showNotification("Error",response.data.msg,"error");
            $('body').LoadingOverlay("hide");
          }
        }, function (error) {
          console.log(error.data);
        });
      }


    }
  });
</script>

</body>
