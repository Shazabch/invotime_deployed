<div class="page-wrapper" ng-app="myApp" ng-controller="otCtrl">
	<div class="content container-fluid" ng-cloak>
		<div class="row">
			<div class="col-xs-4">
				<h4 class="page-title">OT Settings</h4>
			</div>
		</div>
		<div class="row card-box" ng-init="getMinutes()">
			<div class="col-md-4">
				<h5>Skip Time:</h5>
				<div class="form-group">
					<select class="form-control" ng-model="ot.skip_time">
						<option value="no">Do Not Skip</option>
						<option value="15">15 Minutes</option>
						<option value="30">Half Hour</option>
						<option value="60">1 Hour</option>
					</select>
				</div>
				<button class="btn btn-primary" ng-click="updateMinutes()">Update</button>
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
		$scope.getMinutes = function(){
			$http.post('<?php echo base_url(); ?>' + 'ot_settings/getMinutes', '' , config).then(function (response) {
				$scope.ot = response.data;
			});
		}

		$scope.updateMinutes = function(){
			$http.post('<?php echo base_url(); ?>' + 'ot_settings/updateMinutes', $scope.ot , config).then(function (response) {
				showNotification("Success","Settings updated successfully","success");
			});
		}
	});
</script>