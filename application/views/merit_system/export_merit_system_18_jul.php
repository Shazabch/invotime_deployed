<div class="page-wrapper" ng-app="myApp" ng-controller="exportCtrl" ng-init="getData()">
  <div class="content container-fluid" ng-cloak>

    <div class="page-content-wrapperx ">
      <div class="containerx">
        <div class="row">
          <div class="col-sm-12">

            <div class="panel panel-primary">
              <div class="panel-body">
                <h4 class="page-title"><?php echo $pageTitle ?></h4>

                <div>
                  <form target="_blank" action="" method="post">
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="sel1">Outlets</label>
                        <select class="form-control outlet_select" id="branch" name="branch[]" style="width: 100%" multiple="" ng-model="branch" ng-change="getEmployees()">
                          <option ng-repeat="o in outlets" value="{{o.id}}">{{o.name}}</option>

                        </select>
                      </div>

                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="sel1">Departments</label>
                        <select class="form-control department_select" id="department" name="dep[]" style="width: 100%" multiple="" ng-model="department" ng-change="getEmployees()">
                          <option ng-repeat="d in departments" value="{{d.id}}">{{d.name}}</option>

                        </select>
                      </div>

                    </div>

                    <div class="col-md-4">
                      <div class="form-group">
                        <label for="sel1">Positions</label>
                        <select class="form-control position_select" id="position" name="position[]" style="width: 100%" multiple="" ng-model="position" ng-change="getEmployees()">
                          <option ng-repeat="p in positions" value="{{p.id}}">{{p.name}}</option>

                        </select>
                      </div>

                    </div>

                    <div class="col-md-12">
                      <div class="form-group">
                        <label for="sel1">Select Employees</label>
                        <select class="form-control employee_select" id="employee" name="emp[]" style="width: 100%" multiple="">
                          <option ng-repeat="e in filtered_employees" value="{{e.id}}">{{e.special_id}} - {{e.first_name}}</option>

                        </select>
                      </div>

                    </div>
                    <div class="col-md-12">
                      <div class="form-group">
                        <label for="sel1">Exclude Employees</label>
                        <select class="form-control exclude_employee_select" id="exclude_employee" name="exclude_employee[]" style="width: 100%" multiple="">
                          <option ng-repeat="e in filtered_employees" value="{{e.id}}">{{e.special_id}} - {{e.first_name}}</option>

                        </select>
                      </div>

                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="sel1">Month</label>
                        <select class="form-control" id="sel1" name="month">
                          <option <?php echo ('01' == $selected_month) ? 'selected' : '' ?> value="01">January</option>
                          <option <?php echo ('02' == $selected_month) ? 'selected' : '' ?> value="02">February</option>
                          <option <?php echo ('03' == $selected_month) ? 'selected' : '' ?> value="03">March</option>
                          <option <?php echo ('04' == $selected_month) ? 'selected' : '' ?> value="04">April</option>
                          <option <?php echo ('05' == $selected_month) ? 'selected' : '' ?> value="05">May</option>
                          <option <?php echo ('06' == $selected_month) ? 'selected' : '' ?> value="06">June</option>
                          <option <?php echo ('07' == $selected_month) ? 'selected' : '' ?> value="07">July</option>
                          <option <?php echo ('08' == $selected_month) ? 'selected' : '' ?> value="08">August</option>
                          <option <?php echo ('09' == $selected_month) ? 'selected' : '' ?> value="09">September</option>
                          <option <?php echo ('10' == $selected_month) ? 'selected' : '' ?> value="10">October</option>
                          <option <?php echo ('11' == $selected_month) ? 'selected' : '' ?> value="11">November</option>
                          <option <?php echo ('12' == $selected_month) ? 'selected' : '' ?> value="12">December</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="sel1">Year</label>
                        <select class="form-control" id="sel1" name="year">
                          <option <?php echo ('2019' == $selected_year) ? 'selected' : '' ?> value="2019">2019</option>
                          <option <?php echo ('2020' == $selected_year) ? 'selected' : '' ?> value="2020">2020</option>
                          <option <?php echo ('2021' == $selected_year) ? 'selected' : '' ?> value="2021">2021</option>
                          <option <?php echo ('2022' == $selected_year) ? 'selected' : '' ?> value="2022">2022</option>
                          <option <?php echo ('2023' == $selected_year) ? 'selected' : '' ?> value="2023">2023</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="sel1">Summary Type</label>
                        <select class="form-control summary_type" id="type" name="type">
                          <!-- <option value="">All</option> -->
                          <option value="monthly">Monthly</option>
                          <option value="yearly">Yearly</option>
                          <option value="full">Full</option>
                          <option value="monthly_merit_report">Monthly Merit Report</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="sel1">File Type</label>
                        <select class="form-control" id="file_type" name="file_type">
                          <!-- <option value="">All</option> -->
                          <option value="pdf">PDF</option>
                          <!-- <option value="excel">Excel</option> -->
                        </select>
                      </div>
                    </div>

                    <div class="col-md-4 col-md-offset-4">
                      <label for="sel1">&nbsp;</label>
                      <button class="btn btn-primary btn-block" formaction="<?php echo site_url() ?>exports_merit_system/summary_pdf">Export</button>
                      <!-- <button class="btn btn-primary btn-block preview-btn">Preview</button> -->
                      <button class="btn btn-primary btn-block preview-btn" formaction="<?php echo site_url() ?>exports_merit_system/full_merit_report_preview_pdf">Preview</button>

                    </div>
                  </form>


                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>







  </div>

</div>
</div>


</div>

<script type="text/javascript">
  $(document).ready(function() {


    $(".outlet_select").select2({
      placeholder: "All Outlets"
    });
    $(".department_select").select2({
      placeholder: "All Departments"
    });
    $(".position_select").select2({
      placeholder: "All Positions"
    });
    $(".employee_select").select2({
      placeholder: "All Employees"
    });
    $(".exclude_employee_select").select2({
      placeholder: "Exclude Employees"
    });

  });
  var base_url = '<?php echo base_url(); ?>';

  var config = {
    headers: {
      'Content-Type': 'application/json;charset=utf-8;'
    }
  };
  var app = angular.module('myApp', []);

  app.controller('exportCtrl', function($scope, $http) {

    $scope.outlets = [];
    $scope.departments = [];
    $scope.positions = [];
    $scope.employees = [];
    $scope.filtered_employees = [];
    $scope.branch = [];
    $scope.department = [];
    $scope.position = [];

    $scope.getData = function() {
      $('body').LoadingOverlay("show", {
        maxSize: 50
      });
      $http.post(base_url + 'exports/getData', '', config).then(function(response) {
        $scope.outlets = response.data.outlets;
        $scope.departments = response.data.departments;
        $scope.positions = response.data.positions;
        $scope.employees = response.data.employees;
        $scope.filtered_employees = response.data.employees;

        $('body').LoadingOverlay("hide");
      }, function(error) {
        console.log(error.data);
      });
    }

    $scope.getEmployees = function() {
      $('body').LoadingOverlay("show", {
        maxSize: 50
      });
      var total = $scope.filtered_employees = angular.copy($scope.employees);
      var newArray;
      if ($scope.branch.length != 0) {
        newArray = total = total.filter(function(el) {
          return $scope.branch.includes(el.branch_id)
        });
        $scope.filtered_employees = newArray;
      }
      if ($scope.department.length != 0) {
        newArray = total = total.filter(function(el) {
          return $scope.department.includes(el.department_id)
        });
        $scope.filtered_employees = newArray;
      }
      if ($scope.position.length != 0) {
        newArray = total.filter(function(el) {
          return $scope.position.includes(el.position_id)
        });
        $scope.filtered_employees = newArray;
      }
      $('body').LoadingOverlay("hide");
    }


  });

  $(document).ready(function(){
    $('.preview-btn').hide();
    $('.summary_type').change(function(){
      var summary_type = $('.summary_type').val();
      if (summary_type == 'full') {
        $('.preview-btn').show();
      }else{
        $('.preview-btn').hide();
      }
    })
  })

</script>