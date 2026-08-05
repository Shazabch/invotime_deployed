    <div class="page-wrapper" ng-app="app" ng-controller="AppCtrl">
    <div class="content container-fluid">
        <div class="page-content-wrapperx ">
            <div class="containerx">
                <div class="row">
                    <div class="col-sm-12"  ng-init="getDeductionSettings()">
                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4 class="page-title"><?php echo $pageTitle ?></h4>
                                <div>
                                    <form ng-submit="onSubmit()">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <h5>Outlets</h5>
                                                <div class="form-group">
                                                    <select id="ot-outlets" class="form-control" ng-model = "model.bid" ng-change="outletChange()">
                                                        <option value="">Select an outlet</option>
                                                        <option ng-repeat="outlet in outlets" value="{{outlet.id}}">{{outlet.name}}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div ng-show="model.bid != ''">
                                            <h5>Settings</h5>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="void-lateness-time">Void Lateness Time If Less Than</label>
                                                        <input type="number" class="form-control" id="void-lateness-time" ng-model="model.lateness_time">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="checkbox">
                                                    <label for="late-in"><input type="checkbox" ng-change="onChange()" ng-model="model.inc_late_in" id="late-in"><b>Late In</b></label>
                                                </div>

                                            </div>
                                            <div class="form-group">
                                                <div class="checkbox">
                                                    <label for="late-break"><input type="checkbox" ng-change="onChange()" ng-model="model.inc_late_break" id="late-break"><b>Late Break</b></label>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="checkbox">
                                                    <label for="early-out"><input type="checkbox" ng-change="onChange()" ng-model="model.inc_early_out" id="early-out"><b>Early Out</b></label>
                                                </div>
                                            </div>
                                            <!-- <div class="form-group">
                                                <div class="checkbox">
                                                    <label for="short-hours"><input type="checkbox" ng-change="onChange()" ng-model="model.inc_short_hours" id="short-hours"><b>Short Hours</b></label>
                                                </div>
                                            </div> -->
                                            <br>
                                            <h5>Deduction from OT</h5>
                                            <div class="form-group">
                                                <div class="checkbox">
                                                    <label for="deduct-ot"><input type="checkbox" ng-model="model.deduct_from_ot" id="deduct-ot"><b>Deduct lateness time from OT</b></label>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="deduction-date">Deduction date for each month</label>
                                                        <input type="number" class="form-control" id="deduction-date" ng-model="model.deduction_date" ng-disabled="!model.deduct_from_ot" ng-required="model.deduct_from_ot" min="1" max="31" >
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="m-t-20">
                                                <button class="btn btn-primary" type="submit">Update</button>
                                            </div>
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

<script>
    const app = angular.module('app', []);
    const config = {
        headers: {
            'Content-Type': 'application/json;charset=utf-8;'
        }
    };

    app.controller('AppCtrl', function($scope, $http) {

        $scope.model = {bid: ''};
        $scope.outlets = [];

        $scope.getDeductionSettings = function() {
			$("body").LoadingOverlay("show", {size:50});
			$http.post('<?php echo base_url(); ?>' + 'overview/get_deduction_settings', '', config).then(function(response) {
				$scope.model.inc_late_in = response.data.inc_late_in;
                $scope.model.inc_late_break = response.data.inc_late_break;
                $scope.model.inc_short_hours = response.data.inc_short_hours;
                $scope.model.inc_early_out = response.data.inc_early_out;
                $scope.model.bid = response.data.bid;
                $scope.model.lateness_time = response.data.lateness_time;
                $scope.model.deduct_from_ot = response.data.deduct_from_ot;
                $scope.model.deduction_date = parseInt(response.data.deduction_date);
				
				$scope.outlets = response.data.outlets;
                $("body").LoadingOverlay("hide");
			});
			
        }

        $scope.onSubmit = function() {
            $('form').LoadingOverlay("show",{maxSize:50});
            $http.post('<?php echo base_url() ?>overview/update_lateness_deduction_settings', $scope.model, config).then(function(response) {
                $('form').LoadingOverlay("hide");
                if(response.data.success)
                    showNotification("Success",'Settings changed successfully!',"success");
                else
                    showNotification("Error", response.data.message, "error");

            }, function(error) {
                $('form').LoadingOverlay("hide");
                console.log(error);
            });
        }

        $scope.outletChange = function() {
			$("body").LoadingOverlay("show", {size:50});
			$http.post('<?php echo base_url() ?>' + 'overview/get_outlet_deduction_settings', {outletId: $scope.model.bid}, config).then(function(response) {
				$scope.model.inc_late_in = response.data.inc_late_in;
                $scope.model.inc_late_break = response.data.inc_late_break;
                $scope.model.inc_short_hours = response.data.inc_short_hours;
                $scope.model.inc_early_out = response.data.inc_early_out;
				$scope.model.bid = response.data.bid;
                $scope.model.lateness_time = response.data.lateness_time;
                $scope.model.deduct_from_ot = response.data.deduct_from_ot;
                $scope.model.deduction_date = parseInt(response.data.deduction_date);
                $("body").LoadingOverlay("hide");
			});
			
		}
    });
</script>