<div class="page-wrapper" ng-app="myApp" ng-controller="bmiAllowancesCtrl" ng-init="getData()">
    <div class="content container-fluid" ng-cloak>

        <div class="page-content-wrapperx ">
            <div class="containerx">
                <div class="row">
                    <div class="col-sm-12">

                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4 class="page-title"><?php echo $pageTitle ?></h4>

                                <div>
                                    <form ng-submit="applyAllowances()">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="sel1">Outlets</label>
                                                <select class="form-control outlet_select" id="branch" name="branch[]" style="width: 100%" multiple="" ng-model="formModel.branch" ng-change="getEmployees()">
                                                    <option ng-repeat="o in outlets" value="{{o.id}}">{{o.name}}</option>

                                                </select>
                                            </div>

                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="sel1">Departments</label>
                                                <select class="form-control department_select" id="department" name="department[]" style="width: 100%" multiple="" ng-model="formModel.department" ng-change="getEmployees()">
                                                    <option ng-repeat="d in departments" value="{{d.id}}">{{d.name}}</option>

                                                </select>
                                            </div>

                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="sel1">Positions</label>
                                                <select class="form-control position_select" id="position" name="position[]" style="width: 100%" multiple="" ng-model="formModel.position" ng-change="getEmployees()">
                                                    <option ng-repeat="p in positions" value="{{p.id}}">{{p.name}}</option>

                                                </select>
                                            </div>

                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="sel1">Select Employees</label>
                                                <select class="form-control employee_select" id="employee" name="employee[]" style="width: 100%" multiple="" ng-model="formModel.employees">
                                                    <option ng-repeat="g in groups" value="{{g.id}}-group">group - {{g.name}}</option>
                                                    <option ng-repeat="e in filtered_employees" value="{{e.id}}">{{e.special_id}} - {{e.first_name}}</option>

                                                </select>
                                            </div>

                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="sel1">Exclude Employees</label>
                                                <select class="form-control exclude_employee_select" id="exclude_employee" name="exclude_employee[]" style="width: 100%" multiple="" ng-model="formModel.exclude_employees">
                                                    <option ng-repeat="g in groups" value="{{g.id}} - group">group - {{g.name}}</option>
                                                    <option ng-repeat="e in filtered_employees" value="{{e.id}}">{{e.special_id}} - {{e.first_name}}</option>

                                                </select>
                                            </div>

                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="sel1">Allowances<span class="text-danger">*</span></label>
                                                <select class="form-control allowance_select" id="allowance" name="allowance[]" style="width: 100%" multiple="" ng-model="formModel.allowances" required="">
                                                    <option ng-repeat="a in allowances" value="{{a.key}}">{{a.value}}</option>

                                                </select>
                                            </div>

                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Date<span class="text-danger">*</span></label>
                                                <input class="form-control datetimepicker" type="text" id="date" required="" name="date" autocomplete="off" ng-model="formModel.date">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="sel1">Value<span class="text-danger">*</span></label>
                                                <input class="form-control" type="number" id="value" name="value" required="" ng-model="formModel.value">
                                            </div>

                                        </div>

                                        <div class="col-md-4 col-md-offset-4">
                                            <label for="sel1">&nbsp;</label>
                                            <button class="btn btn-primary btn-block">Apply</button>

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
    $(document).ready(function() {
        $('#date').val('<?php echo date("d/m/Y") ?>');
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {


        $(".outlet_select").select2({
                placeholder: "All Outlets"
            }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
            .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
            .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
            .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));

        $(".department_select").select2({
                placeholder: "All Departments"
            }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
            .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
            .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
            .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));

        $(".position_select").select2({
                placeholder: "All Positions"
            }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
            .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
            .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
            .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));

        $(".employee_select").select2({
                placeholder: "All Employees",
                closeOnSelect: false,
            }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
            .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
            .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
            .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));

        $(".exclude_employee_select").select2({
                closeOnSelect: false,
                placeholder: "Exclude Employees"
            }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
            .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
            .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
            .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));

        $(".allowance_select").select2({
                closeOnSelect: false,
                placeholder: "Select Allowances"
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

    app.controller('bmiAllowancesCtrl', function($scope, $http) {

        $scope.outlets = [];
        $scope.departments = [];
        $scope.positions = [];
        $scope.employees = [];
        $scope.filtered_employees = [];
        $scope.allowances = [];

        $scope.formModel = {
            branch: [],
            department: [],
            position: [],
            employees: [],
            exclude_employees: [],
            allowances: [],
            date: '',
            value: ''
        };

        $scope.getData = function() {
            $('body').LoadingOverlay("show", {
                maxSize: 50
            });
            $http.post(base_url + 'exports/getData', '', config).then(function(response) {
                // console.log(response);
                $scope.outlets = response.data.outlets;
                $scope.departments = response.data.departments;
                $scope.positions = response.data.positions;
                $scope.employees = response.data.employees;
                $scope.filtered_employees = response.data.employees;
                $scope.groups = response.data.groups;
                $scope.allowances = response.data.allowances;

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
            if ($scope.formModel.branch.length != 0) {
                newArray = total = total.filter(function(el) {
                    return $scope.formModel.branch.includes(el.branch_id)
                });
                $scope.filtered_employees = newArray;
            }
            if ($scope.formModel.department.length != 0) {
                newArray = total = total.filter(function(el) {
                    return $scope.formModel.department.includes(el.department_id)
                });
                $scope.filtered_employees = newArray;
            }
            if ($scope.formModel.position.length != 0) {
                newArray = total.filter(function(el) {
                    return $scope.formModel.position.includes(el.position_id)
                });
                $scope.filtered_employees = newArray;
            }
            $('body').LoadingOverlay("hide");
        }

        $scope.applyAllowances = function() {
            $('body').LoadingOverlay("show", {
                maxSize: 50
            });
            $scope.formModel.date = $('#date').val();
            $http.post(base_url + 'bmi_summary/updateAllowances', $scope.formModel, config).then(function(response) {
                if (response.data.success){
                    showNotification("Success", response.data.msg, "success");
                    $scope.resetForm();
                } else {
                    showNotification("Error", response.data.msg, "error");
                }
                $('body').LoadingOverlay("hide");
            }, function(error) {
                console.log(error.data);
            });
        }

        $scope.resetForm = function() {
            $scope.formModel = {
                branch: [],
                department: [],
                position: [],
                employees: [],
                exclude_employees: [],
                allowances: [],
                date: '',
                value: ''
            };
            setTimeout(function() {
                $(".outlet_select").val('').trigger('change');
                $(".department_select").val('').trigger('change');
                $(".position_select").val('').trigger('change');
                $(".employee_select").val('').trigger('change');
                $(".exclude_employee_select").val('').trigger('change');
                $(".allowance_select").val('').trigger('change');
            }, 100);
            $('#date').val('<?php echo date("d/m/Y") ?>');
        }

    });
</script>