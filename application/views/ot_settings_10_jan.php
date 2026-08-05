<div class="page-wrapper" ng-app="myApp" ng-controller="otCtrl">
	<div class="content container-fluid" ng-cloak>
		<div class="row">
			<div class="col-xs-4">
				<h4 class="page-title">OT Settings</h4>
			</div>
		</div>
		<div class="row card-box" ng-init="getOtSettings()">
			<div class="col-md-4">
				<form ng-submit="onSubmit()">
					<h5>Outlets</h5>
					<div class="form-group">
						<select id="ot-outlets" class="form-control" ng-model = "ot.bid" ng-change="outletChange()">
							<option value="">Select an outlet</option>
							<option ng-repeat="outlet in outlets" value="{{outlet.id}}">{{outlet.name}}</option>
						</select>
					</div>
					<h5>OT Calculations</h5>
					<div class="form-group">
						<select ng-model="ot.ot_type" id="ot-settings" class="form-control" ng-change="otChange()">
							<option value="default">Default OT</option>
							<option value="eight_hours">Eight Hours</option>
							<option value="weekly_hours">Weekly OT</option>
							<option ng-if="isMonthlyOT" value="monthly_ot">Monthly OT</option>
						</select>
					</div>
					<div ng-if="ot.ot_type == 'weekly_hours'">
						<h5>Weekly Working Hours</h5>
						<div class="form-group">
							<input type="number" ng-model="ot.ot_weekly_hours" id="weekly-hours" class="form-control" min="0" step="0.1">	
						</div>
					</div>
					<!-- <div ng-if="ot.ot_type == 'monthly_ot'">
						<h5>Daily Working Hours</h5>
						<div class="form-group">
							<input type="number" ng-model="ot.ot_daily_hours" id="daily-hours" class="form-control" min="0" step="0.1">
						</div>
					</div> -->
					<div ng-if="ot.ot_type == 'weekly_hours'">
						<h5>Week Selection</h5>
						<div class="form-group">
							<select id="first-day-of-week" class="form-control" ng-model="ot.first_day_of_week">
								<option value="">Select a day</option>
								<option value="monday">Monday - Sunday</option>
								<option value="tuesday">Tuesday - Monday</option>
								<option value="wednesday">Wednesday - Tuesday</option>
								<option value="thursday">Thursday - Wednesday</option>
								<option value="friday">Friday - Thursday</option>
								<option value="saturday">Saturday - Friday</option>
								<option value="sunday">Sunday - Saturday</option>
							</select>
						</div>
					</div>
					<div class="checkbox">
						<label for="round-ot"><input ng-model="ot.ot_round" type="checkbox" id="round-ot" ng-change="otRoundChanged()"><b>Round Off OT</b></label>
					</div>
					<div class="checkbox">
						<label for="first-hour"><input ng-model="ot.round_first_hour_only" type="checkbox" id="first-hour" ng-disabled="!ot.ot_round"><b>Round first hour only</b></label>
					</div>
					<div class="checkbox">
						<label for="exact-hour"><input ng-model="ot.round_by_exact_hour" type="checkbox" id="exact-hour" ng-disabled="!ot.ot_round"><b>Round by exact hour</b></label>
					</div>
					<div class="checkbox">
						<label for="ot-rd"><input ng-model="ot.worked_hours_ot_rd" type="checkbox" id="ot-rd"><b>Worked hours as OT for Rest Day</b></label>
					</div>
					<div class="checkbox">
						<label for="deduct-ot-rd"><input ng-model="ot.deduct_hour_ot_rd" type="checkbox" id="deduct-ot-rd"><b>Deduct 1 hour from OT(RD)</b></label>
					</div>
					<div class="m-t-20" ng-if="bid">
						<button class="btn btn-primary" type="submit">Update</button>
					</div>
				</form>
			</div>
			<div class="col-md-8">
				<form ng-submit="onOTRoundSubmit()">
					<div ng-repeat="item in round_settings" class="row">
						<div class="col-xs-2">
							<div class="form-group">
								<label>Start</label>
								<input type="number" ng-model="item.start" class="form-control" required>
							</div>
						</div>
						<div class="col-xs-2">
							<div class="form-group">
								<label>End</label>
								<input type="number" ng-model="item.end" class="form-control" required>
							</div>
						</div>
						<div class="col-xs-2">
							<div class="form-group">
								<label>Round To</label>
								<input type="number" ng-model="item.round_to" class="form-control" required>
							</div>
						</div>
						<div class="col-xs-2">
							<div class="form-group">
								<label>&nbsp;</label>
								<button type="button" class="btn btn-danger form-control" ng-click="deleteOTSetting(item)"><i class="fa fa-trash"></i></button>
							</div>
						</div>
					</div>
					<div class="m-t-20" ng-if="bid">
						<button type="button" class="btn btn-info" ng-click="addOTSetting()"><span class="fa fa-plus"></span> Add OT Round Setting</button>
						<button class="btn btn-primary" type="submit" ng-if="round_settings.length">Update</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	var config = {
		headers: {
			'Content-Type': 'application/json;charset=utf-8;'
		}
	};
	var app = angular.module('myApp', []);
	app.controller('otCtrl', function($scope,$http) {
		$scope.ot = {};
		$scope.isMonthlyOT = false;
		$scope.round_settings = [];
		$scope.getMinutes = function(){
			$http.post('<?php echo base_url(); ?>' + 'ot_settings/getMinutes', '' , config).then(function (response) {
				$scope.ot = response.data;
			});
		}

		$scope.getOtSettings = function() {
			$("body").LoadingOverlay("show", {size:50});
			$http.post('<?php echo base_url(); ?>' + 'ot_settings/getSettings', '', config).then(function(response) {
				$scope.ot.ot_type = response.data.ot_type;
				$scope.ot.ot_round = response.data.ot_round;
				$scope.ot.round_first_hour_only = response.data.round_first_hour_only;
				$scope.ot.round_by_exact_hour = response.data.round_by_exact_hour;
				$scope.ot.worked_hours_ot_rd = response.data.worked_hours_ot_rd;
				$scope.ot.deduct_hour_ot_rd = response.data.deduct_hour_ot_rd;
				// $scope.ot.ot_daily_hours = response.data.ot_daily_hours;
				$scope.ot.bid = response.data.bid;
				$scope.bid = response.data.bid;

				$scope.round_settings = response.data.ot_round_settings;
				
				$scope.outlets = response.data.outlets;
				if($scope.ot.ot_type === 'eight hours') 
				{
					$scope.ot.ot_round = false;
					$("#round-ot").prop("disabled", true);
				}
				$("body").LoadingOverlay("hide");
			});
			
		}

		$scope.outletChange = function() {
			$("body").LoadingOverlay("show", {size:50});
			$http.post('<?php echo base_url() ?>' + 'ot_settings/getOutletSettings', {outletId: $scope.ot.bid}, config).then(function(response) {
				console.log(response.data);
				$scope.ot.ot_type = response.data.ot_type;
				$scope.ot.ot_round = response.data.ot_round;
				$scope.ot.ot_weekly_hours = response.data.ot_weekly_hours;
				// $scope.ot.ot_daily_hours = response.data.ot_daily_hours;
				$scope.ot.first_day_of_week = response.data.first_day_of_week;
				$scope.ot.round_first_hour_only = response.data.round_first_hour_only;
				$scope.ot.round_by_exact_hour = response.data.round_by_exact_hour;
				$scope.ot.worked_hours_ot_rd = response.data.worked_hours_ot_rd;
				$scope.ot.deduct_hour_ot_rd = response.data.deduct_hour_ot_rd;
				$scope.ot.bid = response.data.bid;
				$scope.bid = response.data.bid;
				$scope.isMonthlyOT = response.data.is_monthly_ot;
				
				$scope.round_settings = response.data.ot_round_settings;

				if($scope.ot.ot_type === 'eight_hours') 
				{
					$scope.ot.ot_round = false;
					$scope.ot.round_first_hour_only = false;
					$scope.ot.round_by_exact_hour = false;
					$("#round-ot").prop("disabled", true);
				} else if($scope.ot.ot_type === 'default') {
					$("#round-ot").prop("disabled", false);
				}
				$("body").LoadingOverlay("hide");
			});
			
		}
		
		$scope.onSubmit = function() {
			if($scope.ot.bid == null || $scope.ot.bid == '')
			{
				showNotification("Error", "Please select an outlet", 'error');
				return;
			}
			$("body").LoadingOverlay("show", {size:50});
			$http.post('<?php echo base_url() ?>' + 'ot_settings/updateSettings', $scope.ot, config).then(function(response) {
				if(response.data.success) {
					showNotification("Success","Settings updated successfully","success");
					$scope.ot.first_day_of_week = response.data.first_day_of_week;
					$scope.ot.ot_weekly_hours = response.data.ot_weekly_hours === null ? 0 : response.data.ot_weekly_hours;
					// $scope.ot.ot_daily_hours = response.data.ot_daily_hours === null ? 0 : response.data.ot_daily_hours;
				}
				else 
					showNotification("Error", response.data.message, 'error');
				$("body").LoadingOverlay("hide");
			});
			
		}

		$scope.updateMinutes = function(){
			$http.post('<?php echo base_url(); ?>' + 'ot_settings/updateMinutes', $scope.ot , config).then(function (response) {
				showNotification("Success","Settings updated successfully","success");
			});
		}

		$scope.otChange = function () {
			if($scope.ot.ot_type === 'eight_hours') {
				$scope.ot.ot_round = false;
				$scope.ot.round_first_hour_only = false;
				$scope.ot.round_by_exact_hour = false;
				$("#round-ot").prop("disabled", true);
			} else {
				$("#round-ot").prop("disabled", false);
			}
		}

		$scope.onOTRoundSubmit = function () {
			if($scope.ot.bid == null || $scope.ot.bid == '')
			{
				showNotification("Error", "Please select an outlet", 'error');
				return;
			}
			if(isOverlapping($scope.round_settings)) 
			{
				showNotification("Error", "OT Round settings are invalid", 'error');
				return;
			}
			$("body").LoadingOverlay("show", {size:50});
			$http.post('<?php echo base_url() ?>' + 'ot_settings/updateOTRoundSettings', { round_settings: $scope.round_settings, bid: $scope.bid }, config).then(function(response) {
				if(response.data.success) 
					showNotification("Success","Settings updated successfully","success");
				else 
					showNotification("Error", response.data.message, 'error');
			}).finally(function() { $("body").LoadingOverlay("hide"); });

		}

		$scope.addOTSetting = function () {
			$scope.round_settings.push({ branch_id: $scope.bid, start: "", end: "", round_to: "" });
		}

		$scope.otRoundChanged = function () {
			if(!$scope.ot.ot_round){
				$scope.ot.round_first_hour_only = false;
				$scope.ot.round_by_exact_hour = false;
			}
		}

		function isOverlapping (range) {
			const length = range.length;
			
			for(let i = 0; i < length; i++) {
				if(range[i].start < 0 || range[i].end > 59) {
					return true;
				}
				for(let j = i + 1; j < length; j++) {
					if(range[j - 1].start <= range[j].end && range[j - 1].end >= range[j].start) {
						return true;
					}
				}
			}
			return false;
		}

		$scope.deleteOTSetting = function (item) {
			const index = $scope.round_settings.indexOf(item);
			$scope.round_settings.splice(index, 1);
		}
	});
</script>