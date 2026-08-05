<style>
	.selectable:hover {
		background-color: lightgrey;
	}

	table td.ui-selecting {
		background-color: #7f8c8d;
	}

	table td.ui-selecting.ui-selected {
		background-color: #7f8c8d;
	}

	table td.ui-selected {
		background-color: #009ce7 !important;
	}

	.text-white {
		color: #fff !important;
	}

	.text-black {
		color: #000 !important;
	}
</style>
<div class="page-wrapper" ng-app="myApp" ng-controller="patternsCtrl" ng-init="init();">

	<div class="content container-fluid" ng-cloak>
		<div class="row">
			<div class="col-xs-4">
				<h4 class="page-title"><?= $pageTitle ?></h4>
			</div>
		</div>
		<div class="row card-box">
			<form ng-submit="savePattern()">
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label>Outlet</label>
							<select class="form-control apply-select2" ng-model="branch" ng-change="getShifts();">
								<option value="">All Outlets</option>
								<option ng-repeat="b in branches" value="{{b.id}}">{{b.name}}</option>

							</select>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-group">
							<label>Pattern Name</label>
							<input class="form-control" type="text" name="pattern-name" placeholder="Name" required="" ng-model="name">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<div style="display:none" id="selectable-controls">
							<button type="button" id="bulk-action" class="btn btn btn-info" data-toggle="modal" data-target="#bulk-assignment-modal" ng-click="setBulkModalData()">Manage Shift(s)</button>
							<button type="button" ng-click="clearSelection()" class="btn btn btn-default">Clear Selection</button>
						</div>
					</div>
					<div class="col-md-6" ng-repeat="pattern in patterns">
						<div class="table-responsive">
							<table style="font-size: 13px" class="table table-striped">
								<thead>
									<tr>
										<th style="font-size: 13px">Week</th>
										<th style="font-size: 13px" ng-repeat="day in days">{{ day.name }}</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><b>{{ pattern.week }}</b></td>
										<td data-week="{{ pattern.week }}" data-day="{{ p.day }}" class="selectable" ng-repeat="p in pattern.pattern">
											<button id="btn-shift_assignment-{{ pattern.week }}-{{ p.day }}"
												type="button"
												class="btn btn-xs"
												ng-class="{
													'text-white': p.shift_id && p.color && p.color.toLowerCase() !== 'white',
													'text-black': !p.shift_id || !p.color || p.color.toLowerCase() === 'white',
													'btn-default': !p.shift_id
												}"
												ng-style="{ 'background-color': p.color ? p.color : 'white' }"
												aria-label="Assign Shift"
												data-toggle="modal"
												data-target="#assignment-modal"
												ng-click="setModalData(pattern.week, p)">
												<span ng-if="!p.shift_id" class="fa fa-plus" aria-hidden="true"></span>
												<span ng-if="p.shift_id">{{ p.code }}</span>
											</button>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div class="col-md-12">
						<div class="btn-group" role="group">
							<button type="button" class="btn btn-danger" ng-click="removeWeek()" ng-disabled="patterns.length <= 1">
								<i class="fa fa-minus"></i>
							</button>

							<button type="button" class="btn btn-default" disabled>
								{{ patterns.length }} Week{{ patterns.length > 1 ? 's' : '' }}
							</button>

							<button type="button" class="btn btn-primary" ng-click="addWeek()" ng-disabled="patterns.length >= 4">
								<i class="fa fa-plus"></i>
							</button>
						</div>
					</div>
					<div class="col-md-12" style="margin-top: 20px">
						<button type="submit" class="btn btn-primary" ng-disabled="!name || !validPattern || savingPattern">
							<span ng-if="!id">Save Pattern</span>
							<span ng-if="id">Update Pattern</span>
						</button>
						<a type="button" class="btn btn-danger" href="<?php echo site_url('shift_patterns'); ?>">Cancel</a>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div id="assignment-modal" class="modal fade" role="dialog">
		<div class="modal-dialog modal-sm">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Shift Assignment</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="sel1">Select shift from dropdown</label>
						<select class="form-control" id="dropdown-shift" ng-model="selectedShift">
							<option value="">Select shift</option>
							<option ng-repeat="s in shifts" data-color="{{s.color}}" data-code="{{s.code}}" value="{{s.id}}">{{s.name}} ({{s.code}})</option>
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button ng-show="selectedPattern.shift_id" id="btn-shift-delete" type="button" class="btn btn-danger" ng-click="deleteShift()">Delete</button>
					<button id="btn-shift-save" type="button" class="btn btn-primary" ng-disabled="!selectedShift" ng-click="assignShift()">Save</button>
				</div>
			</div>

		</div>
	</div>
	<div id="bulk-assignment-modal" class="modal fade" role="dialog">
		<div class="modal-dialog modal-sm">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Bulk Shift Assignment</h4>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="sel1">Select shift from dropdown</label>
						<select class="form-control" id="dropdown-shift" ng-model="selectedShift">
							<option value="">Select shift</option>
							<option ng-repeat="s in shifts" data-color="{{s.color}}" data-code="{{s.code}}" value="{{s.id}}">{{s.name}} ({{s.code}})</option>
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button ng-show="bulkDelete" id="btn-shift-delete" type="button" class="btn btn-danger" ng-click="deleteBulkShift()">Delete</button>
					<button id="btn-shift-save" type="button" class="btn btn-primary" ng-disabled="!selectedShift" ng-click="assignBulkShift()">Save</button>
				</div>
			</div>

		</div>
	</div>
</div>
<script type="text/javascript">
	var base_url = '<?php echo base_url(); ?>';

	var config = {
		headers: {
			'Content-Type': 'application/json;charset=utf-8;'
		}
	};
	var app = angular.module('myApp', []);

	app.controller('patternsCtrl', function($scope, $http) {
		$scope.branches = [];
		$scope.branch = '';
		$scope.name = '';
		$scope.shifts = [];
		$scope.selectedShift = '';
		$scope.selectedWeek = '';
		$scope.selectedDay = '';
		$scope.days = [];
		$scope.patterns = [];
		$scope.patternTemplate = {};
		$scope.selectedPattern = {};
		$scope.selectables = [];
		$scope.bulkDelete = false;
		$scope.validPattern = false;
		$scope.savingPattern = false;
		$scope.id = '<?php echo $id; ?>';

		$scope.init = function() {
			$scope.getBranchesAndOutlets();
			$scope.getPattern();
		}

		$scope.getShifts = function(reset = true) {
			$('body').LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post(base_url + 'shift_patterns/getShifts', {
				branch_id: $scope.branch
			}, config).then(function(response) {
				$scope.shifts = response.data.shifts;

				if (reset) {
					// remove all shifts from patterns
					$scope.patterns.forEach(function(week) {
						week.pattern.forEach(function(day) {
							// if shift id assigned and not in current shifts
							if (day.shift_id && !$scope.shifts.find(s => s.id == day.shift_id)) {
								day.shift_id = '';
								day.code = '';
								day.color = '';
							}
						})
					});
				}

				$('body').LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
			});
		}

		$scope.getBranchesAndOutlets = function() {
			$('body').LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post(base_url + 'default_shifts/getBranchesAndOutlets', '', config).then(function(response) {
				$scope.branches = response.data.branches;

				$('body').LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
			});
		}

		$scope.setModalData = function(week, pattern) {
			$scope.selectedWeek = week;
			$scope.selectedDay = pattern.day;
			$scope.selectedShift = pattern.shift_id;
			$scope.selectedPattern = pattern;
		}

		$scope.setBulkModalData = function() {
			$scope.selectedShift = '';
			// get all ui-selected
			let selectedItems = $(".ui-selected");

			let bulkDelete = false;
			for (let i = 0; i < selectedItems.length; i++) {
				let week = $(selectedItems[i]).attr('data-week');
				let day = $(selectedItems[i]).attr('data-day');

				// check in patterns if shift is already assigned
				let pattern = $scope.getPatternByWeekAndDay(week, day);

				if (pattern) {
					if (pattern.shift_id) {
						bulkDelete = true;
						break;
					}
				}
			}

			$scope.bulkDelete = bulkDelete;
		}

		$scope.getPatternByWeekAndDay = function(week, day) {
			let weekPattern = $scope.patterns.find(p => p.week == week);

			if (weekPattern) {
				let pattern = weekPattern.pattern.find(p => p.day == day);

				return pattern;
			}

			return null;
		}

		$scope.assignShift = function() {
			let shift = $scope.shifts.find(s => s.id == $scope.selectedShift);

			$scope.selectedPattern.shift_id = $scope.selectedShift;
			$scope.selectedPattern.code = shift.code;
			$scope.selectedPattern.color = shift.color;
			$('#assignment-modal').modal('hide');
		}

		$scope.assignBulkShift = function() {
			let shift = $scope.shifts.find(s => s.id == $scope.selectedShift);

			let selectedItems = $(".ui-selected");

			for (let i = 0; i < selectedItems.length; i++) {
				let week = $(selectedItems[i]).attr('data-week');
				let day = $(selectedItems[i]).attr('data-day');

				// get pattern
				let pattern = $scope.getPatternByWeekAndDay(week, day);

				if (pattern) {
					pattern.shift_id = $scope.selectedShift;
					pattern.code = shift.code;
					pattern.color = shift.color;
				}
			}

			$scope.clearSelection();

			$('#bulk-assignment-modal').modal('hide');
		}

		$scope.deleteShift = function() {
			$scope.selectedPattern.shift_id = '';
			$scope.selectedPattern.code = '';
			$scope.selectedPattern.color = '';
			$('#assignment-modal').modal('hide');
		}

		$scope.deleteBulkShift = function() {
			let selectedItems = $(".ui-selected");

			for (let i = 0; i < selectedItems.length; i++) {
				let week = $(selectedItems[i]).attr('data-week');
				let day = $(selectedItems[i]).attr('data-day');

				// check in patterns if shift is already assigned
				let pattern = $scope.getPatternByWeekAndDay(week, day);

				if (pattern) {
					if (pattern.shift_id) {
						pattern.shift_id = '';
						pattern.code = '';
						pattern.color = '';
					}
				}
			}

			$scope.clearSelection();
			$('#bulk-assignment-modal').modal('hide');
		}

		$scope.getPattern = function() {
			$('body').LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post(base_url + 'shift_patterns/getPattern', {
				id: '<?php echo $id; ?>'
			}, config).then(function(response) {
				$scope.days = response.data.days;
				$scope.patterns = response.data.patterns;
				$scope.patternTemplate = response.data.patternTemplate;
				$scope.name = response.data.name;
				$scope.branch = response.data.branch_id != "0" ? response.data.branch_id : '';

				$scope.getShifts(false);

				$scope.initializeSelectable();

				$('body').LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
			});
		}

		$scope.addWeek = function() {
			if ($scope.patterns.length >= 4) {
				return;
			}
			let week = $scope.patterns.length + 1;
			let pattern = {
				'week': week,
				'pattern': angular.copy($scope.patternTemplate)
			};

			$scope.patterns.push(pattern);

			$scope.initializeSelectable();
		}

		$scope.removeWeek = function() {
			if ($scope.patterns.length <= 1) {
				return;
			}
			$scope.patterns.pop();
			$scope.selectables.pop();
			$scope.update();
		}

		$scope.initializeSelectable = function() {
			setTimeout(function() {
				const tables = document.querySelectorAll("table");

				if (tables.length === 0) return;

				tables.forEach(function(table) {
					if (table.getAttribute("data-selectable-initialized") === "true") return;

					const selectable = new Selectable({
						filter: table.querySelectorAll(".selectable"),
						toggle: true,
						autoScroll: {
							threshold: 30,
							increment: 30,
						},
						ignore: "button",
						selectAll: false
					});

					selectable.table();

					selectable.on("end", $scope.update);

					$scope.selectables.push(selectable);

					table.setAttribute("data-selectable-initialized", "true");
				});
			}, 100); // 500ms delay
		};

		$scope.update = function() {
			setTimeout(function() {
				var selectedItems = $(".ui-selected").length;

				if (selectedItems > 0) {
					$("#selectable-controls").slideDown("fast");
					$("#bulk-action").text("Manage " + selectedItems + " shift(s)");
				} else {
					$("#selectable-controls").slideUp("fast");
				}
			}, 100);
		};

		$scope.clearSelection = function() {
			for (var i = 0; i < $scope.selectables.length; i++) {
				$scope.selectables[i].clear();
			}
			$scope.update();
		};

		$scope.savePattern = function() {
			let valid = $scope.isValidPattern($scope.patterns);

			if (!valid) {
				showNotification("Error", "All weeks must have a shift assigned", "error");
			} else {
				$scope.savingPattern = true;

				$('body').LoadingOverlay("show", {
					maxSize: 50
				});

				$http.post(base_url + 'shift_patterns/savePattern', {
					pattern: $scope.patterns,
					branch_id: $scope.branch,
					name: $scope.name,
					id: '<?php echo $id; ?>'
				}, config).then(function(response) {
					$('body').LoadingOverlay("hide");

					showNotification("Success", response.data.message, "success");

					// redirect to index page after 500 ms
					setTimeout(function() {
						window.location.href = base_url + 'shift_patterns';
					}, 500);
				}, function(error) {
					console.log(error.data);
				});
			}
		}

		$scope.$watch('patterns', function(newValue, oldValue) {
			$scope.validPattern = $scope.isValidPattern(newValue);
		}, true);

		$scope.isValidPattern = function(patterns) {
			if (patterns.length == 0) {
				return false;
			} else {
				let valid = true;

				for (let i = 0; i < patterns.length; i++) {
					let pattern = patterns[i].pattern;
					let hasShift = false;

					for (let j = 0; j < pattern.length; j++) {
						let shift = pattern[j];

						if (shift.shift_id) {
							hasShift = true;
							break;
						}
					}

					if (!hasShift) {
						valid = false;
						break;
					}
				}

				return valid;
			}
		}
	});
</script>