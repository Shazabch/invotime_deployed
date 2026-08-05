<style>
	.table-div {
		min-height: 300px;
		max-width: 150px;
	}
	.select2-container {
		width: 100% !important;
		.select2-selection--multiple {
			min-height: 40px !important;
		}
	}
</style>
<div class="page-wrapper" ng-app="myApp" ng-controller="shiftCtrl" ng-init="getBranchesAndOutlets();getEmployees();getShifts();getGroups();">

	<div class="content container-fluid">
		<div class="row">
			<div class="col-xs-4">
				<h4 class="page-title">Yearly Shifts</h4>
			</div>
		</div>
		<div class="row card-box" ng-cloak>
			<form ng-submit="assignShifts()">
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label>Shift Type</label>
							<select class="form-control apply-select2" ng-model="filterModel.shift_type" required="" ng-change="reset();">
								<option ng-repeat="a in shiftTypes" value="{{a.key}}">{{a.value}}</option>
							</select>
						</div>

					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Outlet</label>
							<select class="form-control apply-select2" ng-model="filterModel.branch" required="" ng-change="getShifts();filterEmployees();getGroups();getPatterns();">
								<option value="">Select an outlet</option>
								<option ng-repeat="b in branches" value="{{b.id}}">{{b.name}}</option>

							</select>
						</div>

					</div>



					<div class="col-md-4" ng-show="filterModel.type != 'delete-all' && filterModel.shift_type == 'single_shift'">
						<div class="form-group">
							<label>Shift</label>
							<select class="form-control apply-select2 shift-select2" ng-model="filterModel.shift_id" ng-required="filterModel.type != 'delete-all' && filterModel.shift_type == 'single_shift'">
								<option value="">Select a shift</option>
								<option ng-repeat="s in shifts" value="{{s.id}}">{{s.name}}</option>

							</select>
						</div>

					</div>

					<div class="col-md-4" ng-show="filterModel.type != 'delete-all' && filterModel.shift_type == 'shift_pattern'">
						<div class="form-group">
							<label>Pattern</label>
							<select class="form-control apply-select2 pattern-select2" ng-model="filterModel.pattern_id" ng-required="filterModel.type != 'delete-all' && filterModel.shift_type == 'shift_pattern'" ng-change="updateWeeks();">
								<option value="">Select a pattern</option>
								<option ng-repeat="p in patterns" value="{{p.id}}">{{p.name}}</option>

							</select>
						</div>

					</div>

					<div class="col-md-4" ng-show="filterModel.type != 'delete-all' && filterModel.shift_type == 'shift_pattern' && weeks.length > 1">
						<div class="form-group">
							<label>Starting Week</label>
							<select class="form-control apply-select2 week-select2" ng-model="filterModel.starting_week">
								<option ng-repeat="w in weeks" value="{{w}}">{{w}}</option>
							</select>
						</div>

					</div>

					<div class="col-md-4">
						<div class="form-group">
							<label>Position</label>
							<select class="form-control positions" ng-model="filterModel.position" ng-change="filterEmployees();" style="width: 100%" multiple="">
								<option ng-repeat="p in positions" value="{{p.id}}">{{p.name}}</option>
							</select>
						</div>

					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Department</label>
							<select class="form-control departments" ng-model="filterModel.department" ng-change="filterEmployees();" style="width: 100%" multiple="">
								<option ng-repeat="d in departments" value="{{d.id}}">{{d.name}}</option>


							</select>
						</div>

					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Section</label>
							<select class="form-control sections" ng-model="filterModel.section" ng-change="filterEmployees();" style="width: 100%" multiple="">
								<option ng-repeat="s in sections" value="{{s.id}}">{{s.name}}</option>


							</select>
						</div>

					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Groups</label>
							<select class="form-control groups" ng-model="filterModel.groups" style="width: 100%" multiple="" ng-change="filterEmployees();">
								<option ng-repeat="g in groups" value="{{g.id}}">{{g.name}}</option>


							</select>
						</div>

					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Employees</label>
							<select class="form-control employees" ng-model="filterModel.employees" style="width: 100%" multiple="">
								<option ng-repeat="e in employees" value="{{e.id}}">{{e.special_id}} - {{e.name}}</option>


							</select>
						</div>

					</div>
					<div class="col-md-4" ng-show="filterModel.type != 'delete-all' && filterModel.shift_type == 'single_shift'">
						<div class="form-group">
							<label>Days</label>
							<select class="form-control shift_days" ng-model="filterModel.shift_days" style="width: 100%" multiple="" ng-required="filterModel.type != 'delete-all' && filterModel.shift_type == 'single_shift'">
								<option ng-repeat="d in days" value="{{d}}">{{d}}</option>
							</select>
						</div>

					</div>
					<div class="col-md-4" ng-show="filterModel.type != 'delete-all'">
						<div class="form-group">
							<label>Exclude Public Holidays</label>
							<select class="form-control public_holidays" ng-model="filterModel.public_holidays" style="width: 100%" multiple="">
								<option ng-repeat="h in holidays" value="{{h.holiday_date}}">{{h.title}}</option>
							</select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>From Date</label>
							<input type="text" class="form-control datetimepicker from_date" ng-model="filterModel.from_date" required>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>To Date</label>
							<input type="text" class="form-control datetimepicker to_date" ng-model="filterModel.to_date" required>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Assignment Type</label>
							<select class="form-control" ng-model="filterModel.type">
								<option value="">Default</option>
								<option value="overwrite">Overwrite</option>
								<option value="delete-overwrite">Delete & Overwrite</option>
								<option value="delete-all">Delete All</option>
							</select>
						</div>

					</div>

					<div class="col-md-12">
						<!-- description of assignment types -->
						<div class="alert alert-info" style="margin-top: 10px;" ng-if="filterModel.type == ''">
							<strong>Default</strong> This option allows you to assign shifts to selected employees on specific days, but only if they don't already have a shift assigned for those days.
						</div>
						<div class="alert alert-info" style="margin-top: 10px;" ng-if="filterModel.type == 'overwrite'">
							<strong>Overwrite</strong> This option enables you to replace any existing shift assignments for selected employees on specific days with a new shift.
						</div>
						<div class="alert alert-info" style="margin-top: 10px;" ng-if="filterModel.type == 'delete-overwrite'">
							<strong>Delete & Overwrite</strong> This option allows you to delete any existing shift assignments for selected employees and then assign new shifts to them on specific days.
						</div>
						<div class="alert alert-info" style="margin-top: 10px;" ng-if="filterModel.type == 'delete-all'">
							<strong>Delete All</strong> This option allows you to delete all shift assignments for selected employees. (Any selected shift or days will be ignored)
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-3">
						<label>&nbsp;</label>
						<button class="btn btn-primary btn-block submit-btn" ng-if="filterModel.type != 'delete-all'">Apply Shift</button>
						<button class="btn btn-primary btn-block submit-btn" ng-if="filterModel.type == 'delete-all'">Delete Shifts</button>

					</div>

					<div class="col-md-9" ng-if="show_progress">
						<div class="progress" style="margin-top: 30px;">
							<div class="progress-bar" role="progressbar" aria-valuenow="{{ progress }}" aria-valuemin="0" aria-valuemax="100" style="width:{{ progress }}%">
								{{ progress }}%
							</div>
						</div>
					</div>
				</div>

			</form>





		</div>


	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {

		$('.apply-select2').select2();

		$(".shift_days").select2({
			placeholder: "Days",
			closeOnSelect: false
		});
		$(".public_holidays").select2({
			placeholder: "Exclude All",
			closeOnSelect: false
		});
		$(".employees").select2({
			placeholder: "All Employees",
			closeOnSelect: false
		});
		$(".departments").select2({
			placeholder: "All Departments",
			closeOnSelect: false
		});
		$(".sections").select2({
			placeholder: "All Sections",
			closeOnSelect: false
		});
		$(".positions").select2({
			placeholder: "All Positions",
			closeOnSelect: false
		});
		$(".groups").select2({
			placeholder: "All Groups",
			closeOnSelect: false
		});
	});
	var base_url = '<?php echo base_url(); ?>';

	var config = {
		headers: {
			'Content-Type': 'application/json;charset=utf-8;'
		}
	};
	var app = angular.module('myApp', []);

	app.controller('shiftCtrl', function($scope, $http) {

		$scope.filterModel = {
			branch: '',
			department: '',
			shift_id: '',
			shift_days: [],
			public_holidays: [],
			employees: [],
			groups: [],
			department: [],
			section: [],
			position: [],
			type: '',
			from_date: '<?= date("d/m/Y") ?>',
			to_date: '<?= date("31/12/Y") ?>',
			shift_type: 'single_shift',
			pattern_id: '',
			starting_week: '1',
		};
		$scope.shifts = [];
		$scope.days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
		$scope.employees = [];
		$scope.groups = [];
		$scope.holidays = [];
		$scope.show_progress = false;
		$scope.progress = 0;
		let progressTimeout = null;

		$scope.shiftTypes = [
			{
				key: 'single_shift',
				value: 'Single Shift'
			},
			{
				key: 'shift_pattern',
				value: 'Group Shift'
			}
		];
		$scope.patterns = [];
		$scope.weeks = [];

		$scope.reset = function() {
			$scope.filterModel.shift_id = '';
			$scope.filterModel.shift_days = [];
			$scope.filterModel.pattern_id = '';
			$scope.weeks = [];
			$scope.filterModel.starting_week = '1';

			setTimeout(function() {
				$('.shift-select2').val('').trigger('change');
				$('.pattern-select2').val('').trigger('change');
				$('.week-select2').val('').trigger('change');
				$('.shift_days').val([]).trigger('change');	
			}, 100);
		}

		$scope.getBranchesAndOutlets = function() {
			$('body').LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post(base_url + 'default_shifts/getBranchesAndOutlets', '', config).then(function(response) {
				$scope.branches = response.data.branches;
				$scope.departments = response.data.departments;
				$scope.sections = response.data.sections;
				$scope.positions = response.data.positions;
				$scope.holidays = response.data.holidays;

				if ($scope.holidays.length > 0) {
					// add "none" option
					$scope.holidays.unshift({
						holiday_date: 'none',
						title: 'None (Do not exclude any)'
					});
				}

				$('body').LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
			});
		}

		$scope.getPatterns = function() {
            $('body').LoadingOverlay("show", {
                maxSize: 50
            });
            $http.post(base_url + 'shift_patterns/getPatterns', {
                branch_id: $scope.filterModel.branch,
				selected_branch_only: true
            }, config).then(function(response) {
                $scope.patterns = response.data.patterns;
				$scope.weeks = [];

                $('body').LoadingOverlay("hide");
            }, function(error) {
                console.log(error.data);
            });
        }

		$scope.updateWeeks = function() {
			$scope.weeks = [];
			$scope.filterModel.starting_week = '1';

			let pattern = $scope.patterns.find(p => p.id == $scope.filterModel.pattern_id);

			if (pattern) {
				for (let i = 1; i <= pattern.weeks; i++) {
					$scope.weeks.push(i);
				}
			}
		}

		$scope.getShifts = function() {
			$('body').LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post(base_url + 'default_shifts/getShifts', {
				branch_id: $scope.filterModel.branch
			}, config).then(function(response) {
				$scope.shifts = response.data.shifts;

				$('body').LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
			});
		}

		$scope.getGroups = function() {
			$('body').LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post(base_url + 'default_shifts/getGroups', {
				branch_id: $scope.filterModel.branch
			}, config).then(function(response) {
				$scope.groups = response.data.groups;
				$scope.filterModel.groups = [];

				$('body').LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
			});
		}

		$scope.filterEmployees = function() {
			$('body').LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post(base_url + 'default_shifts/getEmployees', {
				branch: $scope.filterModel.branch,
				department: $scope.filterModel.department,
				section: $scope.filterModel.section,
				position: $scope.filterModel.position,
				groups: $scope.filterModel.groups
			}, config).then(function(response) {
				$scope.employees = response.data.employees;

				$('body').LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
			});
		}

		$scope.assignShifts = function() {
			$('.submit-btn').LoadingOverlay("show", {
				maxSize: 50
			});
			$scope.show_progress = true;
			$scope.progress = 0;
			$scope.filterModel.session_id = makeid(10);
			$scope.filterModel.from_date = $('.from_date').val();
			$scope.filterModel.to_date = $('.to_date').val();
			if (progressTimeout) {
				clearTimeout(progressTimeout);
			}
			$http.post(base_url + 'default_shifts/assignShifts', $scope.filterModel, config).then(function(response) {

				if (response.data.success) {
					let msg = $scope.filterModel.type == "delete-all" ? "Shifts deleted successfully!" : "Shifts assigned successfully!";
					showNotification("Success", msg, "success");
					$scope.progress = 100;
				}

				$('.submit-btn').LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
			});

			$scope.getProgress();
		}

		$scope.getProgress = function() {
			if ($scope.progress == 100) return;
			$http.post(base_url + 'default_shifts/getProgress', {
				session_id: $scope.filterModel.session_id
			}, config).then(function(response) {
				if (response.data.progress < $scope.progress) {
					$scope.progress = 100;
					return;
				}
				$scope.progress = response.data.progress;
				if ($scope.progress < 100) {
					// wait for 10 sec and call again $scope.getProgress
					progressTimeout = setTimeout($scope.getProgress, 10000);
				}
			}, function(error) {
				console.log(error.data);
			});
		}

		$scope.getEmployees = function() {
			$('body').LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post(base_url + 'default_shifts/getEmployees', {}, config).then(function(response) {
				$scope.employees = response.data.employees;

				$('body').LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
			});
		}

		$scope.$watch('filterModel.public_holidays', function(newValue, oldValue) {
			let disableNone = false;
			let selectedOptions = $scope.filterModel.public_holidays;
			if (oldValue.includes('none') && newValue.includes('none') && newValue.length > 1) {
				// remove none from selectedOptions
				selectedOptions = selectedOptions.filter(item => item !== 'none');
			} else if (!oldValue.includes('none') && newValue.includes('none')) {
				// add none to selectedOptions
				selectedOptions = ['none'];
			}
			$scope.filterModel.public_holidays = selectedOptions;
			setTimeout(function() {
				$('.public_holidays').val(selectedOptions).trigger('change');
			}, 100);
		}, true);
	});

	function makeid(length) {
		let result = '';
		const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		const charactersLength = characters.length;
		let counter = 0;
		while (counter < length) {
			result += characters.charAt(Math.floor(Math.random() * charactersLength));
			counter += 1;
		}
		return result;
	}
</script>
<!-- <script type="text/javascript">
  $(document).ready(function() {
  });
</script> -->