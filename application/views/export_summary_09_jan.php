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
                <form target="_blank" action="<?php echo site_url() ?>exports/summary_pdf" method="post">
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
                      <select class="form-control department_select" id="department" name="department[]" style="width: 100%" multiple="" ng-model="department" ng-change="getEmployees()">
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
                      <select class="form-control employee_select" id="employee" name="employee[]" style="width: 100%" multiple="">
                          <option ng-repeat="g in groups" value="{{g.id}}-group">group - {{g.name}}</option>
                          <option ng-repeat="e in filtered_employees" value="{{e.id}}">{{e.special_id}} - {{e.first_name}}</option>

                      </select>
                    </div>

                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="sel1">Exclude Employees</label>
                      <select class="form-control exclude_employee_select" id="exclude_employee" name="exclude_employee[]" style="width: 100%" multiple="">
                          <option ng-repeat="g in groups" value="{{g.id}} - group">group - {{g.name}}</option>
                          <option ng-repeat="e in filtered_employees" value="{{e.id}}">{{e.special_id}} - {{e.first_name}}</option>

                      </select>
                    </div>

                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="control-label">From<span class="text-danger">*</span></label>
                      <input class="form-control datetimepicker" type="text" id="from" required="" name="from" autocomplete="off">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label class="control-label">To<span class="text-danger">*</span></label>
                      <input class="form-control datetimepicker" type="text" id="to" required="" name="to" autocomplete="off">
                    </div>
                  </div>

                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="sel1">Summary Type</label>
                      <select  class="form-control" id="type" name="type">
                        <!-- <option value="">All</option> -->
                        <option value="short">Short</option>
                        <option value="full">Full</option>
                        <option value="accounts">AutoCount Payroll</option>
                        <option value="sql">SQL Payroll</option>
                        <option value="weekly_ot">Weekly OT</option>
                        <option value="weekly_ot_reports">SQL Weekly OT</option>
                        <?php if($company_id == 66): ?>
                          <option value="bmi_summary">BMI Full Summary</option>
                          <option value="bmi_summary_short">BMI Short Summary</option>
                        <?php endif; ?>
                        <?php if($company_id == 102): ?>
                          <option value="cjc01_payroll">CJC01 Payroll</option>
                        <?php endif; ?>
                        <option value="daily_time_card">Daily Time Card</option>
                        <?php if($company_id == 95): ?>
                        <option value="work_hours_summary">Work Hours Summary</option>
                        <?php endif; ?>
                      </select>
                    </div>
                  </div>

                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="sel1">File Type</label>
                      <select  class="form-control" id="file_type" name="file_type">
                        <!-- <option value="">All</option> -->
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel 97-2003 Workbook (.xls)</option>
                        <option value="xlsx">Excel Workbook (.xlsx)</option>
                      </select>
                    </div>
                  </div>

                  





                  <div class="col-md-4 col-md-offset-4">
                    <label for="sel1">&nbsp;</label>
                    <button class="btn btn-primary btn-block">Export</button>

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

<script>
  $(document).ready(function(){
    $('#from').val('<?php echo $from_f; ?>');
    $('#to').val('<?php echo $to_f; ?>');

  });
</script>

<script type="text/javascript">
    $(document).ready(function () {


      $(".outlet_select").select2({
        placeholder : "All Outlets"
      }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
        .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
        .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
        .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));

      $(".department_select").select2({
        placeholder : "All Departments"
      }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
        .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
        .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
        .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));

      $(".position_select").select2({
        placeholder : "All Positions"
      }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
        .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
        .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
        .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));

      $(".employee_select").select2({
        placeholder : "All Employees",
        closeOnSelect: false,
      }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
        .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
        .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
        .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));

      $(".exclude_employee_select").select2({
        closeOnSelect: false,
        placeholder : "Exclude Employees"
      }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
        .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
        .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
        .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));

    });
    var base_url = '<?php echo base_url(); ?>';

    var config = {
      headers: {
        'Content-Type': 'application/json;charset=utf-8;'
      }
    }; 
    var app = angular.module('myApp', []);

    app.controller('exportCtrl', function($scope,$http) {

      $scope.outlets = [];
      $scope.departments = [];
      $scope.positions = [];
      $scope.employees = [];
      $scope.filtered_employees = [];
      $scope.branch = [];
      $scope.department = [];
      $scope.position = [];

      $scope.getData = function(){
        $('body').LoadingOverlay("show",{maxSize:50});
        $http.post(base_url + 'exports/getData', '', config).then(function (response) {
          // console.log(response);
          $scope.outlets = response.data.outlets;
          $scope.departments = response.data.departments;
          $scope.positions = response.data.positions;
          $scope.employees = response.data.employees;
          $scope.filtered_employees = response.data.employees;
          $scope.groups = response.data.groups;
          
          $('body').LoadingOverlay("hide");
        }, function (error) {
          console.log(error.data);
        });
      }

      $scope.getEmployees = function(){
        $('body').LoadingOverlay("show",{maxSize:50});
        var total = $scope.filtered_employees = angular.copy($scope.employees);
        var newArray;
        if($scope.branch.length != 0){
          newArray = total = total.filter(function(el){
            return $scope.branch.includes(el.branch_id)
          });
          $scope.filtered_employees = newArray;
        }
        if($scope.department.length != 0){
          newArray = total = total.filter(function(el){
            return $scope.department.includes(el.department_id)
          });
          $scope.filtered_employees = newArray;
        }
        if($scope.position.length != 0){
          newArray = total.filter(function(el){
            return $scope.position.includes(el.position_id)
          });
          $scope.filtered_employees = newArray;
        }
        $('body').LoadingOverlay("hide");
      }

      
    });
  </script>
