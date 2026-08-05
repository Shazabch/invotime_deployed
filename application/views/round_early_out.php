<div class="page-wrapper" ng-app="myApp" ng-controller="otCtrl">
	<div class="content container-fluid" ng-cloak>
		<div class="row">
			<div class="col-xs-4">
				<h4 class="page-title">Early Out round Settings</h4>
			</div>
		</div>
		<div class="row card-box" ng-init="getOtSettings()">
			<div class="col-md-4">
				<form ng-submit="onSubmit()">
					<h5>Outlets</h5>
					<div class="form-group">
						<select id="ot-outlets" class="form-control" ng-model="ot.bid" ng-change="outletChange()">
							<option value="">Select an outlet</option>
							<option ng-repeat="outlet in outlets" value="{{outlet.id}}">{{outlet.name}}</option>
						</select>
					</div>
				</form>
			</div>
			<div class="col-md-8">
				<form ng-submit="onLDRoundSubmit()">
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
						<button type="button" class="btn btn-info" ng-click="addRLDSetting()"><span class="fa fa-plus"></span> Add Late In Round Setting</button>
						<button class="btn btn-primary" type="submit">Update</button>
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
	app.controller('otCtrl', function($scope, $http) {
		$scope.ot = {};
		$scope.round_settings = [];
		$scope.getMinutes = function() {
			$http.post('<?php echo base_url(); ?>' + 'ot_settings/getMinutes', '', config).then(function(response) {
				$scope.ot = response.data;
			});
		}

		$scope.getOtSettings = function() {
			$("body").LoadingOverlay("show", {
				size: 50
			});
			$http.post('<?php echo base_url(); ?>' + 'overview/getSettingsEarlyOut', '', config).then(function(response) {
				$scope.ot.ot_type = response.data.ot_type;
				$scope.ot.ot_round = response.data.ot_round;
				$scope.ot.round_first_hour_only = response.data.round_first_hour_only;
				$scope.ot.round_by_exact_hour = response.data.round_by_exact_hour;
				$scope.ot.bid = response.data.bid;
				$scope.bid = response.data.bid;

				$scope.round_settings = response.data.late_in_round_settings;

				$scope.outlets = response.data.outlets;
				if ($scope.ot.ot_type === 'eight hours') {
					$scope.ot.ot_round = false;
					$("#round-ot").prop("disabled", true);
				}
				$("body").LoadingOverlay("hide");
			});

		}

		$scope.outletChange = function() {
			$("body").LoadingOverlay("show", {
				size: 50
			});
			$http.post('<?php echo base_url() ?>' + 'overview/getOutletSettingsEarlyOut', {
				outletId: $scope.ot.bid
			}, config).then(function(response) {
				console.log(response.data);
				$scope.ot.ot_type = response.data.ot_type;
				$scope.ot.ot_round = response.data.ot_round;
				$scope.ot.ot_weekly_hours = response.data.ot_weekly_hours;
				$scope.ot.first_day_of_week = response.data.first_day_of_week;
				$scope.ot.round_first_hour_only = response.data.round_first_hour_only;
				$scope.ot.round_by_exact_hour = response.data.round_by_exact_hour;
				$scope.ot.bid = response.data.bid;
				$scope.bid = response.data.bid;

				$scope.round_settings = response.data.late_in_round_settings;

				if ($scope.ot.ot_type === 'eight_hours') {
					$scope.ot.ot_round = false;
					$scope.ot.round_first_hour_only = false;
					$scope.ot.round_by_exact_hour = false;
					$("#round-ot").prop("disabled", true);
				} else if ($scope.ot.ot_type === 'default') {
					$("#round-ot").prop("disabled", false);
				}
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
			if ($scope.ot.ot_type === 'eight_hours') {
				$scope.ot.ot_round = false;
				$scope.ot.round_first_hour_only = false;
				$scope.ot.round_by_exact_hour = false;
				$("#round-ot").prop("disabled", true);
			} else {
				$("#round-ot").prop("disabled", false);
			}
		}

		$scope.onLDRoundSubmit = function() {
			if ($scope.ot.bid == null || $scope.ot.bid == '') {
				showNotification("Error", "Please select an outlet", 'error');
				return;
			}
			if (isOverlapping($scope.round_settings)) {
				showNotification("Error", "Late In Round settings are invalid or overlapping", 'error');
				return;
			}
			$("body").LoadingOverlay("show", {
				size: 50
			});
			$http.post('<?php echo base_url() ?>' + 'overview/updateLDRoundSettingsEarlyOut', {
				round_settings: $scope.round_settings,
				bid: $scope.bid
			}, config).then(function(response) {
				if (response.data.success)
					showNotification("Success", "Settings updated successfully", "success");
				else
					showNotification("Error", response.data.message, 'error');
			}).finally(function() {
				$("body").LoadingOverlay("hide");
			});

		}

		$scope.addRLDSetting = function() {
			$scope.round_settings.push({
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
				if (range[i].start < 0 || range[i].end < 0 || range[i].round_to < 0) {
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
	});
</script>