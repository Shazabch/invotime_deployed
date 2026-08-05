<div class="page-wrapper" ng-app="myApp" ng-controller="otCtrl">
	<div class="content container-fluid" ng-cloak>
		<div class="row">
			<div class="col-xs-4">
				<h4 class="page-title">OT Settings</h4>
			</div>
		</div>
		<div class="card-box" ng-init="getOtSettings()">
			<div class="row">
				<div class="col-md-4">
					<form ng-submit="onSubmit()">
						<h5>Outlets</h5>
						<div class="form-group">
							<select id="ot-outlets" class="form-control" ng-model="ot.bid" ng-change="outletChange()">
								<option value="">Select an outlet</option>
								<option ng-repeat="outlet in outlets" value="{{outlet.id}}">{{outlet.name}}</option>
							</select>
						</div>
						<h5>OT Calculations</h5>
						<div class="form-group">
							<select ng-model="ot.ot_type" id="ot-settings" class="form-control" ng-change="otChange()">
								<option value="default">Default OT</option>
								<option value="eight_hours">Eight Hours</option>
								<option ng-if="isWeeklyOT" value="weekly_hours">Weekly OT</option>
								<option ng-if="isMonthlyOT" value="monthly_ot">Monthly OT</option>
							</select>
						</div>
						<div ng-if="ot.ot_type == 'weekly_hours'">
							<h5>Weekly Working Hours</h5>
							<div class="form-group">
								<input type="number" ng-model="ot.ot_weekly_hours" id="weekly-hours" class="form-control" min="0" step="0.1">
							</div>
						</div>
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
							<label for="different-first-hour-rounding"><input ng-model="ot.different_first_hour_rounding" type="checkbox" id="different-first-hour-rounding" ng-disabled="!ot.ot_round"><b>Different first hour rounding</b></label>
						</div>
						<div class="checkbox">
							<label for="ot-rd"><input ng-model="ot.worked_hours_ot_rd" type="checkbox" id="ot-rd"><b>Worked hours as OT for Rest Day</b></label>
						</div>
						<div class="checkbox">
							<label for="deduct-ot-rd"><input ng-model="ot.deduct_hour_ot_rd" type="checkbox" id="deduct-ot-rd"><b>Deduct 1 hour from OT(RD)</b></label>
						</div>
						<div class="checkbox">
							<label for="ot-ph"><input ng-model="ot.worked_hours_ot_ph" type="checkbox" id="ot-ph"><b>Worked hours as OT for Public Holiday</b></label>
						</div>
						<div class="checkbox">
							<label for="deduct-ot-ph"><input ng-model="ot.deduct_hour_ot_ph" type="checkbox" id="deduct-ot-ph"><b>Deduct 1 hour from OT(PH)</b></label>
						</div>
						<div class="checkbox">
							<label for="ot-off"><input ng-model="ot.worked_hours_ot_off" type="checkbox" id="ot-off"><b>Worked hours as OT for Off Day</b></label>
						</div>
						<div class="checkbox">
							<label for="deduct-ot-off"><input ng-model="ot.deduct_hour_ot_off" type="checkbox" id="deduct-ot-off"><b>Deduct 1 hour from OT(OFF)</b></label>
						</div>
						<div class="checkbox">
							<label for="ignore-breaks-after-endtime"><input ng-model="ot.ignore_breaks_after_endtime" type="checkbox" id="ignore-breaks-after-endtime"><b>Ignore breaks after End Time</b></label>
						</div>
						<div class="checkbox">
							<label for="round-early-ot"><input ng-model="ot.early_ot_round" type="checkbox" id="round-early-ot"><b>Round Off Early OT</b></label>
						</div>
						<div class="checkbox">
							<label for="use-half-hours-for-saturdays"><input ng-model="ot.use_half_hours_for_saturdays" type="checkbox" id="use-half-hours-for-saturdays" ng-disabled="ot.ot_type != 'eight_hours'"><b>Use half hours for Saturdays</b></label>
						</div>
						<div class="m-t-20" ng-if="bid">
							<button class="btn btn-primary" type="submit">Update</button>
						</div>
					</form>
				</div>
				<div class="col-md-8">
					<div class="row" ng-if="bid">
						<div class="col-md-12" ng-if="ot.different_first_hour_rounding">
							<form ng-submit="onFirstHourOTRoundSubmit()">
								<div ng-repeat="item in first_hour_round_settings" class="row">
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
											<button type="button" class="btn btn-danger form-control" ng-click="deleteFirstHourOTSetting(item)"><i class="fa fa-trash"></i></button>
										</div>
									</div>
								</div>
								<div class="m-t-20">
									<button type="button" class="btn btn-info" ng-click="addFirstHourOTSetting()"><span class="fa fa-plus"></span> Add First Hour OT Round Setting</button>
									<button class="btn btn-primary" type="submit">Update</button>
								</div>
							</form>
						</div>
						<div class="col-md-12" ng-if="ot.different_first_hour_rounding">
							<hr>
						</div>
						<div class="col-md-12">
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
								<div class="m-t-20">
									<button type="button" class="btn btn-info" ng-click="addOTSetting()"><span class="fa fa-plus"></span> Add OT Round Setting</button>
									<button class="btn btn-primary" type="submit">Update</button>
								</div>
							</form>
						</div>
						<div class="col-md-12">
							<hr>
						</div>
						<div class="col-md-12">
							<form ng-submit="onEarlyOTRoundSubmit()">
								<div ng-repeat="item in early_ot_round_settings" class="row">
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
											<button type="button" class="btn btn-danger form-control" ng-click="deleteEarlyOTSetting(item)"><i class="fa fa-trash"></i></button>
										</div>
									</div>
								</div>
								<div class="m-t-20">
									<button type="button" class="btn btn-info" ng-click="addEarlyOTSetting()"><span class="fa fa-plus"></span> Add Early OT Round Setting</button>
									<button class="btn btn-primary" type="submit">Update</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<div ng-if="ot.ot_type == 'monthly_ot'" class="m-t-30">
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label for="monthly-ot-year">Year</label>
							<select class="form-control" title="Year" ng-model="monthlyOT.year" ng-options="item for item in monthlyOT.years" ng-change="fetchMonthlyWorkingDays()"></select>
						</div>
					</div>
				</div>
				<div class="row m-t-10" ng-if="monthlyOT.months.length != 0">
					<div class="col-sm-12">
						<div class="table-responsive">
							<table class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>Month</th>
										<th>Days</th>
									</tr>
								</thead>
								<tbody>
									<tr ng-repeat="item in monthlyOT.months">
										<td>
											<p type="text">{{item.month | monthFilter}}</p>
										</td>
										<td><input type="text" ng-model="item.days" class="form-control" /></td>
									</tr>
								</tbody>
							</table>
						</div>
						<button class="btn btn-primary m-t-10" type="button" ng-click="updateWorkingDays()">Update Working Days</button>
					</div>
				</div>
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
	app.filter('monthFilter', function() {
		return function(input) {
			var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
			return months[input - 1];
		};
	});
	app.controller('otCtrl', function($scope, $http) {
		$scope.ot = {};
		$scope.isMonthlyOT = false;
		$scope.isWeeklyOT = false;
		$scope.round_settings = [];
		$scope.first_hour_round_settings = [];
		$scope.monthlyOT = {};
		$scope.monthlyOT.months = [];
		$scope.monthlyOT.year = 2023;
		$scope.monthlyOT.years = [2019, 2020, 2021, 2022, 2023, 2024, 2025, 2026];
		$scope.getMinutes = function() {
			$http.post('<?php echo base_url(); ?>' + 'ot_settings/getMinutes', '', config).then(function(response) {
				$scope.ot = response.data;
			});
		}

		$scope.getOtSettings = function() {
			$("body").LoadingOverlay("show", {
				size: 50
			});
			$http.post('<?php echo base_url(); ?>' + 'ot_settings/getSettings', '', config).then(function(response) {
				$scope.ot.ot_type = response.data.ot_type;
				$scope.ot.ot_round = response.data.ot_round;
				$scope.ot.early_ot_round = response.data.early_ot_round;
				$scope.ot.use_half_hours_for_saturdays = response.data.use_half_hours_for_saturdays;
				$scope.ot.round_first_hour_only = response.data.round_first_hour_only;
				$scope.ot.round_by_exact_hour = response.data.round_by_exact_hour;
				$scope.ot.different_first_hour_rounding = response.data.different_first_hour_rounding;
				$scope.ot.worked_hours_ot_rd = response.data.worked_hours_ot_rd;
				$scope.ot.worked_hours_ot_ph = response.data.worked_hours_ot_ph;
				$scope.ot.worked_hours_ot_off = response.data.worked_hours_ot_off;
				$scope.ot.deduct_hour_ot_rd = response.data.deduct_hour_ot_rd;
				$scope.ot.deduct_hour_ot_ph = response.data.deduct_hour_ot_ph;
				$scope.ot.deduct_hour_ot_off = response.data.deduct_hour_ot_off;
				$scope.ot.ignore_breaks_after_endtime = response.data.ignore_breaks_after_endtime;
				// $scope.ot.ot_daily_hours = response.data.ot_daily_hours;
				$scope.ot.bid = response.data.bid;
				$scope.bid = response.data.bid;

				$scope.round_settings = response.data.ot_round_settings;
				$scope.first_hour_round_settings = response.data.first_hour_ot_round_settings;
				$scope.early_ot_round_settings = response.data.early_ot_round_settings;

				$scope.outlets = response.data.outlets;
				// if ($scope.ot.ot_type === 'eight hours') {
				// 	$scope.ot.ot_round = false;
				// 	$("#round-ot").prop("disabled", true);
				// }
				$("body").LoadingOverlay("hide");
			});

		}

		$scope.outletChange = function() {
			$("body").LoadingOverlay("show", {
				size: 50
			});
			$http.post('<?php echo base_url() ?>' + 'ot_settings/getOutletSettings', {
				outletId: $scope.ot.bid
			}, config).then(function(response) {
				console.log(response.data);
				$scope.ot.ot_type = response.data.ot_type;
				$scope.ot.ot_round = response.data.ot_round;
				$scope.ot.early_ot_round = response.data.early_ot_round;
				$scope.ot.use_half_hours_for_saturdays = response.data.use_half_hours_for_saturdays;
				$scope.ot.ot_weekly_hours = response.data.ot_weekly_hours;
				// $scope.ot.ot_daily_hours = response.data.ot_daily_hours;
				$scope.ot.first_day_of_week = response.data.first_day_of_week;
				$scope.ot.round_first_hour_only = response.data.round_first_hour_only;
				$scope.ot.round_by_exact_hour = response.data.round_by_exact_hour;
				$scope.ot.different_first_hour_rounding = response.data.different_first_hour_rounding;
				$scope.ot.worked_hours_ot_rd = response.data.worked_hours_ot_rd;
				$scope.ot.worked_hours_ot_ph = response.data.worked_hours_ot_ph;
				$scope.ot.worked_hours_ot_off = response.data.worked_hours_ot_off;
				$scope.ot.deduct_hour_ot_rd = response.data.deduct_hour_ot_rd;
				$scope.ot.deduct_hour_ot_ph = response.data.deduct_hour_ot_ph;
				$scope.ot.deduct_hour_ot_off = response.data.deduct_hour_ot_off;
				$scope.ot.ignore_breaks_after_endtime = response.data.ignore_breaks_after_endtime;
				$scope.ot.bid = response.data.bid;
				$scope.bid = response.data.bid;
				$scope.isMonthlyOT = response.data.is_monthly_ot;
				$scope.isWeeklyOT = response.data.is_weekly_ot;
				$scope.monthlyOT.months = response.data.months;

				$scope.round_settings = response.data.ot_round_settings;
				$scope.first_hour_round_settings = response.data.first_hour_ot_round_settings;
				$scope.early_ot_round_settings = response.data.early_ot_round_settings;

				// if ($scope.ot.ot_type === 'eight_hours') {
				// 	$scope.ot.ot_round = false;
				// 	$scope.ot.round_first_hour_only = false;
				// 	$scope.ot.round_by_exact_hour = false;
				// 	$("#round-ot").prop("disabled", true);
				// } else if ($scope.ot.ot_type === 'default') {
				// 	$("#round-ot").prop("disabled", false);
				// }

				$("body").LoadingOverlay("hide");
			});

		}

		$scope.onSubmit = function() {
			if ($scope.ot.bid == null || $scope.ot.bid == '') {
				showNotification("Error", "Please select an outlet", 'error');
				return;
			}
			$("body").LoadingOverlay("show", {
				size: 50
			});
			$http.post('<?php echo base_url() ?>' + 'ot_settings/updateSettings', $scope.ot, config).then(function(response) {
				if (response.data.success) {
					showNotification("Success", "Settings updated successfully", "success");
					$scope.ot.first_day_of_week = response.data.first_day_of_week;
					$scope.ot.ot_weekly_hours = response.data.ot_weekly_hours === null ? 0 : response.data.ot_weekly_hours;
					// $scope.ot.ot_daily_hours = response.data.ot_daily_hours === null ? 0 : response.data.ot_daily_hours;
				} else
					showNotification("Error", response.data.message, 'error');
				$("body").LoadingOverlay("hide");
			});

		}

		$scope.updateMinutes = function() {
			$http.post('<?php echo base_url(); ?>' + 'ot_settings/updateMinutes', $scope.ot, config).then(function(response) {
				showNotification("Success", "Settings updated successfully", "success");
			});
		}

		$scope.otChange = function() {
			// if ($scope.ot.ot_type === 'eight_hours') {
			// 	$scope.ot.ot_round = false;
			// 	$scope.ot.round_first_hour_only = false;
			// 	$scope.ot.round_by_exact_hour = false;
			// 	$("#round-ot").prop("disabled", true);
			// } else {
			// 	$("#round-ot").prop("disabled", false);
			// }
		}

		$scope.onOTRoundSubmit = function() {
			if ($scope.ot.bid == null || $scope.ot.bid == '') {
				showNotification("Error", "Please select an outlet", 'error');
				return;
			}
			if (isOverlapping($scope.round_settings)) {
				showNotification("Error", "OT Round settings are invalid", 'error');
				return;
			}
			$("body").LoadingOverlay("show", {
				size: 50
			});
			$http.post('<?php echo base_url() ?>' + 'ot_settings/updateOTRoundSettings', {
				round_settings: $scope.round_settings,
				bid: $scope.bid
			}, config).then(function(response) {
				if (response.data.success)
					showNotification("Success", "OT Round Settings updated successfully", "success");
				else
					showNotification("Error", response.data.message, 'error');
			}).finally(function() {
				$("body").LoadingOverlay("hide");
			});

		}

		$scope.onFirstHourOTRoundSubmit = function() {
			if ($scope.ot.bid == null || $scope.ot.bid == '') {
				showNotification("Error", "Please select an outlet", 'error');
				return;
			}
			if (isOverlapping($scope.first_hour_round_settings)) {
				showNotification("Error", "First Hour OT Round settings are invalid", 'error');
				return;
			}
			$("body").LoadingOverlay("show", {
				size: 50
			});
			$http.post('<?php echo base_url() ?>' + 'ot_settings/updateFirstHourOTRoundSettings', {
				round_settings: $scope.first_hour_round_settings,
				bid: $scope.bid
			}, config).then(function(response) {
				if (response.data.success)
					showNotification("Success", "First Hour OT Round Settings updated successfully", "success");
				else
					showNotification("Error", response.data.message, 'error');
			}).finally(function() {
				$("body").LoadingOverlay("hide");
			});

		}

		$scope.onEarlyOTRoundSubmit = function() {
			if ($scope.ot.bid == null || $scope.ot.bid == '') {
				showNotification("Error", "Please select an outlet", 'error');
				return;
			}
			if (isOverlapping($scope.early_ot_round_settings)) {
				showNotification("Error", "OT Round settings are invalid", 'error');
				return;
			}
			$("body").LoadingOverlay("show", {
				size: 50
			});
			$http.post('<?php echo base_url() ?>' + 'ot_settings/updateEarlyOTRoundSettings', {
				round_settings: $scope.early_ot_round_settings,
				bid: $scope.bid
			}, config).then(function(response) {
				if (response.data.success)
					showNotification("Success", "Early OT Round Settings updated successfully", "success");
				else
					showNotification("Error", response.data.message, 'error');
			}).finally(function() {
				$("body").LoadingOverlay("hide");
			});

		}

		$scope.addOTSetting = function() {
			$scope.round_settings.push({
				branch_id: $scope.bid,
				start: "",
				end: "",
				round_to: ""
			});
		}

		$scope.addFirstHourOTSetting = function() {
			$scope.first_hour_round_settings.push({
				branch_id: $scope.bid,
				start: "",
				end: "",
				round_to: ""
			});
		}

		$scope.addEarlyOTSetting = function() {
			$scope.early_ot_round_settings.push({
				branch_id: $scope.bid,
				start: "",
				end: "",
				round_to: ""
			});
		}

		$scope.otRoundChanged = function() {
			if (!$scope.ot.ot_round) {
				$scope.ot.round_first_hour_only = false;
				$scope.ot.round_by_exact_hour = false;
			}
		}

		function isOverlapping(range) {
			const length = range.length;

			for (let i = 0; i < length; i++) {
				if (range[i].start < 0 || range[i].end > 59) {
					return true;
				}
				for (let j = i + 1; j < length; j++) {
					if (range[j - 1].start <= range[j].end && range[j - 1].end >= range[j].start) {
						return true;
					}
				}
			}
			return false;
		}

		$scope.deleteOTSetting = function(item) {
			const index = $scope.round_settings.indexOf(item);
			$scope.round_settings.splice(index, 1);
		}

		$scope.deleteFirstHourOTSetting = function(item) {
			const index = $scope.first_hour_round_settings.indexOf(item);
			$scope.first_hour_round_settings.splice(index, 1);
		}

		$scope.deleteEarlyOTSetting = function(item) {
			const index = $scope.early_ot_round_settings.indexOf(item);
			$scope.early_ot_round_settings.splice(index, 1);
		}

		$scope.fetchMonthlyWorkingDays = function() {
			$("body").LoadingOverlay("show", {
				size: 50
			});
			$http.post('<?php echo base_url() ?>' + `ot_settings/getMonthlyWorkingDays`, {
				year: $scope.monthlyOT.year,
				branch_id: $scope.bid
			}, config).then(function(response) {
				if (response.data.success) {
					$scope.monthlyOT.months = response.data.months;
					$("body").LoadingOverlay("hide");
				}
			});
		}

		$scope.updateWorkingDays = function() {
			$("body").LoadingOverlay("show", {
				size: 50
			});
			$http.post('<?php echo base_url() ?>' + `ot_settings/updateMonthlyWorkingDays`, {
				year: $scope.monthlyOT.year,
				branch_id: $scope.bid,
				months: $scope.monthlyOT.months
			}, config).then(function(response) {
				if (response.data.success) {
					showNotification("Success", response.data.message, "success");
					$("body").LoadingOverlay("hide");
				}
			});
		}
	});
</script>