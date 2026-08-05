<!DOCTYPE html>
<html>

<head>
	<title>Admin Panel</title>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/bootstrap.min.css">
	<script src="<?php echo base_url(); ?>assets/js/jquery.min.js" type="text/javascript"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js" type="text/javascript"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.6/dist/loadingoverlay.min.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/toast/src/jquery.toast.css">
	<script src='<?php echo base_url(); ?>assets/toast/src/jquery.toast.js'></script>
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/css/select2.min.css">
	<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>blue/assets/select2-bootstrap/dist/select2-bootstrap.css">
	<script src='<?php echo base_url(); ?>assets/js/select2.min.js'></script>
</head>

<body ng-app="invotime" ng-controller="adminpanel" ng-init="getCompanies()">
	<div class="container">

		<h2>Invotime Admin Panel</h2>

		<ul class="nav nav-tabs">
			<li class="active"><a data-toggle="tab" href="#new_company">New Company</a></li>
			<li><a data-toggle="tab" href="#new_outlet">New Outlet</a></li>
			<li><a data-toggle="tab" href="#subscriptions">Subscriptions</a></li>
			<li><a data-toggle="tab" href="#payroll-admin">Payroll Admin</a></li>
			<li><a data-toggle="tab" href="#reset-passwords">Reset Passwords</a></li>
			<li><a data-toggle="tab" href="#packages">Packages</a></li>
			<li><a data-toggle="tab" href="#announcements">Announcements</a></li>
			<li><a data-toggle="tab" href="#leave-admin">Leave Admin</a></li>
			<li><a data-toggle="tab" href="#resellers">Resellers</a></li>
		</ul>

		<div class="tab-content">
			<div id="new_company" class="tab-pane fade in active">
				<br><br>
				<ul class="nav nav-tabs">
					<li class="active"><a data-toggle="tab" href="#add">Add</a></li>
					<li><a data-toggle="tab" href="#view">View</a></li>
				</ul>

				<div class="tab-content">

					<div id="add" class="tab-pane fade in active">
						<!-- <h3>HOME</h3> -->
						<br><br>
						<form name="company_form" ng-submit="onSubmit(company_form.$valid)">
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label">Company Name*</label>
										<input type="text" class="form-control" placeholder="Enter company name" ng-model="company.name" required="">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label">Company Address</label>
										<input type="text" class="form-control" placeholder="Enter company address" ng-model="company.address">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label">Company Phone</label>
										<input type="text" class="form-control" placeholder="Enter company phone" ng-model="company.phone">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label">Organization ID</label>
										<input type="text" class="form-control" placeholder="Enter company phone" ng-model="company.organization_id">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label">Packages</label>
										<select class="form-control select2" ng-model="company.package">
											<option disabled value="">Select package</option>
											<?php foreach ($packages as $package) { ?>
												<option class="form-control" value="<?= $package->id ?>"><?= $package->name; ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label">Additional Staff</label>
										<input type="number" class="form-control" placeholder="Enter additional staff" ng-model="company.additional_staff">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label">Company Admin*</label>
										<input type="text" class="form-control" placeholder="Enter company admin" ng-model="company.admin" required="">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label">Company Admin Email*</label>
										<input type="email" class="form-control" placeholder="Enter company admin email" ng-model="company.email" required="">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label">Company Admin Password*</label>
										<input type="password" class="form-control" placeholder="Enter company admin password" autocomplete="new-password" ng-model="company.password" required="">
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										<button type="submit" class="btn btn-default">Save</button>
									</div>
								</div>
							</div>
						</form>
					</div>

					<div id="view" class="tab-pane fade">
						<br>
						<?= $companies_xcrud ?>
						<div id="xcrud-loading-company"></div>
						<div id="setting-xcrud-company"></div>
					</div>

				</div>
				<br><br>
				<br>
			</div>
			<div id="new_outlet" class="tab-pane fade">
				<br><br>
				<form name="outlet_form" ng-submit="onSubmit2(outlet_form.$valid)">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Select Company*</label>
								<select class="form-control select2" required="" ng-model="outlet.company">
									<option value="">Select a company</option>
									<option value="{{c.id}}" ng-repeat="c in companies">{{c.name}}</option>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Outlet Name*</label>
								<input type="text" class="form-control" placeholder="Enter outlet name" required="" ng-model="outlet.name">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Outlet Address</label>
								<input type="text" class="form-control" placeholder="Enter outlet address" ng-model="outlet.address">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Outlet Phone</label>
								<input type="text" class="form-control" placeholder="Enter outlet phone" ng-model="outlet.phone">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Outlet Admin*</label>
								<input type="text" class="form-control" placeholder="Enter outlet admin" ng-model="outlet.admin">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Outlet Admin Email*</label>
								<input type="email" class="form-control" placeholder="Enter outlet admin email" ng-model="outlet.email">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Outlet Admin Password*</label>
								<input type="password" class="form-control" placeholder="Enter outlet admin password" autocomplete="new-password" ng-model="outlet.password">
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<button type="submit" class="btn btn-default">Save</button>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div id="subscriptions" class="tab-pane fade">
				<br><br>
				<form ng-submit="getXcrud()">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Select Company*</label>
								<select class="form-control select2" required="" ng-model="company_selected">
									<option value="">Select a company</option>
									<option value="{{c.id}}" ng-repeat="c in companies">{{c.name}}</option>
								</select>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label></label>
								<button type="submit" class="btn btn-default">Filter</button>
							</div>
						</div>
					</div>
				</form>
				<div class="row">
					<div class="col-md-12">
						<div id="xcrudDiv"></div>
					</div>
				</div>
			</div>
			<div id="payroll-admin" class="tab-pane fade">
				<br><br>
				<form name="payroll_form" ng-submit="onSubmitPayrollForm(payroll_form.$valid)">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Select Company*</label>
								<select class="form-control select2" required="" ng-model="payroll.company" ng-change="getOutlets()">
									<option value="">Select a company</option>
									<option value="{{c.id}}" ng-repeat="c in companies">{{c.name}}</option>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Select Outlet*</label>
								<select class="form-control select2" required="" ng-model="payroll.outlet" id="outlet-input">
									<option value="">Select an outlet</option>
									<option value="{{o.id}}" ng-repeat="o in outlets">{{o.name}}</option>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Admin Type*</label>
								<select class="form-control select2" required="" ng-model="payroll.type">
									<option value="">Select admin type</option>
									<option value="company">Company</option>
									<option value="outlet">Outlet</option>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Payroll Admin Name*</label>
								<input type="text" class="form-control" placeholder="Enter payroll admin name" ng-model="payroll.admin" required="">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Payroll Admin Email*</label>
								<input type="email" class="form-control" placeholder="Enter payroll admin email" ng-model="payroll.email" required="">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Payroll Admin Password*</label>
								<input type="password" class="form-control" placeholder="Enter payroll admin password" autocomplete="new-password" ng-model="payroll.password" required="">
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<button type="submit" class="btn btn-default">Save</button>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div id="reset-passwords" class="tab-pane fade">
				<br><br>
				<form name="password_form" ng-submit="onSubmitPasswordForm(password_form.$valid)">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Email*</label>
								<input type="text" class="form-control" placeholder="Email" ng-model="password.email" required="">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Password*</label>
								<input type="password" class="form-control" placeholder="Password" ng-model="password.password" required="">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Confirm Password*</label>
								<input type="password" class="form-control" placeholder="Confirm Password" autocomplete="new-password" ng-model="password.confirm_password" required="">
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<button type="submit" class="btn btn-default">Save</button>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div id="packages" class="tab-pane fade">
				<br><br>
				<ul class="nav nav-tabs">
					<li class="active"><a data-toggle="tab" href="#add-package">Add</a></li>
					<li><a data-toggle="tab" href="#view-package">View</a></li>
				</ul>

				<div class="tab-content">

					<div id="add-package" class="tab-pane fade in active">
						<!-- <h3>HOME</h3> -->
						<br><br>
						<form method="POST" action="<?= base_url() . 'admin_panel/add_package'; ?>">
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label">Name*</label>
										<input type="text" name="name" class="form-control" placeholder="Enter package Name" required="">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label">Max outlets</label>
										<input type="number" name="max_outlets" class="form-control" placeholder="Enter package Max outlets">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label class="control-label">Max active staff</label>
										<input type="number" name="max_active_staff" class="form-control" placeholder="Enter package Max active staff">
									</div>
								</div>
								<div class="col-md-12">
									<div class="form-group">
										<button type="submit" class="btn btn-default">Save</button>
									</div>
								</div>
							</div>
						</form>
					</div>

					<div id="view-package" class="tab-pane fade">
						<br><br>
						<div class="row">
							<div class="col-s-8">
								<table class="table">
									<tr>
										<th>Name</th>
										<th>Max outlets</th>
										<th>Max active staff</th>
										<th>Created_at</th>
										<th>Actions</th>
									</tr>
									<?php
									foreach ($packages as $package) { ?>
										<tr>
											<!-- <td hidden><?= $company->id; ?></td> -->
											<td class="name"><?= $package->name; ?></td>
											<td class="max_outlets"><?= $package->max_outlets; ?></td>
											<td class="max_active_staff"><?= $package->max_active_staff; ?></td>
											<td><?= $package->created_at; ?></td>
											<td><button data-id="<?= $package->id; ?>" class="btn bn-sm btn-warning edit_company">Edit</button> <a href="<?= base_url() . 'admin_panel/delete_package/' . $package->id; ?>"><button class="btn bn-sm btn-danger">Delete</button></a></td>
										</tr>
									<?php
									}
									?>
								</table>
							</div>
						</div>

					</div>

				</div>
				<!-- <div id="xcrud-loading"></div>
				<div id="setting-xcrud"></div> -->
			</div>
			<div id="announcements" class="tab-pane fade">
				<br>
				<?= $announcements_xcrud; ?>
				<div id="announcements-xcrud"></div>
			</div>
			<div id="leave-admin" class="tab-pane fade">
				<br><br>
				<form name="leave_form" ng-submit="onSubmitLeaveForm(leave_form.$valid)">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Select Company*</label>
								<select class="form-control select2" required="" ng-model="leave.company" ng-change="getOutletsForLeave()">
									<option value="">Select a company</option>
									<option value="{{c.id}}" ng-repeat="c in companies">{{c.name}}</option>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Select Outlet*</label>
								<select class="form-control select2" required="" ng-model="leave.outlet" id="outlet-input-leave">
									<option value="">Select an outlet</option>
									<option value="{{o.id}}" ng-repeat="o in outlets">{{o.name}}</option>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Admin Type*</label>
								<select class="form-control select2" required="" ng-model="leave.type">
									<option value="">Select admin type</option>
									<option value="company">Company</option>
									<option value="outlet">Outlet</option>
								</select>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Leave Admin Name*</label>
								<input type="text" class="form-control" placeholder="Enter leave admin name" ng-model="leave.admin" required="">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Leave Admin Email*</label>
								<input type="email" class="form-control" placeholder="Enter leave admin email" ng-model="leave.email" required="">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Leave Admin Password*</label>
								<input type="password" class="form-control" placeholder="Enter leave admin password" autocomplete="new-password" ng-model="leave.password" required="">
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<button type="submit" class="btn btn-default">Save</button>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div id="resellers" class="tab-pane fade">
				<br>
				<?= $resellers_xcrud; ?>
			</div>
		</div>

	</div>
</body>

</html>

<script type="text/javascript">
	function showNotification(heading, message, icon) {
		$.toast({
			heading: heading,
			showHideTransition: 'slide',
			text: message,
			textColor: "#ffffff",
			position: 'bottom-right',
			loaderBg: '#fff',
			icon: icon,
			hideAfter: 3000,
			stack: 10
		});
	}

	var config = {
		headers: {
			'Content-Type': 'application/json;charset=utf-8;'
		}
	};
	var app = angular.module('invotime', []);
	app.controller('adminpanel', function($scope, $http) {
		$scope.company = {
			name: '',
			address: '',
			phone: '',
			organization_id: '',
			admin: '',
			email: '',
			password: '',
			package: '',
			additional_staff: ''
		};

		$scope.outlet = {
			name: '',
			address: '',
			phone: '',
			admin: '',
			email: '',
			password: '',
			company: ''
		}

		$scope.payroll = {
			company: '',
			outlet: '',
			type: '',
			admin: '',
			email: '',
			password: ''
		}

		$scope.leave = {
			company: '',
			outlet: '',
			type: '',
			admin: '',
			email: '',
			password: ''
		}

		$scope.password = {
			email: '',
			password: '',
			confirm_password: ''
		}

		$scope.package = {
			package_name: '',
			max_outlets: '',
			max_active_staff: ''
		}

		$scope.onSubmit = function(valid) {
			if (valid) {
				$('body').LoadingOverlay("show", {
					maxSize: 50
				});
				$http.post('<?php echo base_url(); ?>' + 'admin_panel/new_company', $scope.company, config).then(function(response) {
					if (response.data.success) {

						$scope.company = {
							name: '',
							address: '',
							phone: '',
							organization_id: '',
							admin: '',
							email: '',
							password: '',
							package: '',
							additional_staff: ''
						};
						$scope.getCompanies();
						showNotification("Success", response.data.message, "success");
						$('body').LoadingOverlay("hide");
					} else {
						showNotification("Error", response.data.message, "error");
						$('body').LoadingOverlay("hide");
					}

				}, function(error) {
					console.log(error.data);
				});
			}
		}

		$scope.onSubmit2 = function(valid) {
			if (valid) {
				$('body').LoadingOverlay("show", {
					maxSize: 50
				});
				$http.post('<?php echo base_url(); ?>' + 'admin_panel/new_outlet', $scope.outlet, config).then(function(response) {
					if (response.data.success) {

						$scope.outlet = {
							name: '',
							address: '',
							phone: '',
							admin: '',
							email: '',
							password: '',
							company: ''
						};

						showNotification("Success", response.data.message, "success");
						$('body').LoadingOverlay("hide");
					} else {
						showNotification("Error", response.data.message, "error");
						$('body').LoadingOverlay("hide");
					}

				}, function(error) {
					console.log(error.data);
				});
			}
		}

		$scope.add_package = function(valid) {
			if (valid) {
				$('body').LoadingOverlay("show", {
					maxSize: 50
				});
				$http.post('<?php echo base_url(); ?>' + 'admin_panel/add_package', $scope.package, config).then(function(response) {
					if (response.data.success) {

						$scope.package = {
							package_name: '',
							max_outlets: '',
							max_active_staff: '',
						};

						showNotification("Success", response.data.message, "success");
						$('body').LoadingOverlay("hide");
					} else {
						showNotification("Error", response.data.message, "error");
						$('body').LoadingOverlay("hide");
					}

				}, function(error) {
					console.log(error.data);
				});
			}
		}

		$scope.onSubmitPayrollForm = function(valid) {
			if (valid) {
				$('body').LoadingOverlay("show", {
					maxSize: 50
				});
				$http.post('<?php echo base_url(); ?>' + 'admin_panel/makePayrollAdmin', $scope.payroll, config).then(function(response) {
					if (response.data.success) {

						$scope.payroll = {
							company: '',
							outlet: '',
							admin: '',
							email: '',
							password: ''
						};

						$scope.outlets = [];

						showNotification("Success", response.data.message, "success");
						$('body').LoadingOverlay("hide");
					} else {
						showNotification("Error", response.data.message, "error");
						$('body').LoadingOverlay("hide");
					}

				}, function(error) {
					console.log(error.data);
				});
			}
		}

		$scope.onSubmitLeaveForm = function(valid) {
			if (valid) {
				$('body').LoadingOverlay("show", {
					maxSize: 50
				});
				$http.post('<?php echo base_url(); ?>' + 'admin_panel/makeLeaveAdmin', $scope.leave, config).then(function(response) {
					if (response.data.success) {

						$scope.leave = {
							company: '',
							outlet: '',
							admin: '',
							email: '',
							password: ''
						};

						$scope.outlets = [];

						showNotification("Success", response.data.message, "success");
						$('body').LoadingOverlay("hide");
					} else {
						showNotification("Error", response.data.message, "error");
						$('body').LoadingOverlay("hide");
					}

				}, function(error) {
					console.log(error.data);
				});
			}
		}

		$scope.onSubmitPasswordForm = function(valid) {
			if (valid) {
				if ($scope.password.password == $scope.password.confirm_password) {
					$('body').LoadingOverlay("show", {
						maxSize: 50
					});
					$http.post('<?php echo base_url(); ?>' + 'admin_panel/resetPassword', $scope.password, config).then(function(response) {
						if (response.data.success) {

							$scope.password = {
								email: '',
								password: '',
								confirm_password: ''
							}

							showNotification("Success", response.data.message, "success");
							$('body').LoadingOverlay("hide");
						} else {
							showNotification("Error", response.data.message, "error");
							$('body').LoadingOverlay("hide");
						}

					}, function(error) {
						console.log(error.data);
					});
				} else {
					showNotification("Error", "Password and Confirm Password do not match", "error");
				}
			}
		}

		$scope.getCompanies = function() {
			$('body').LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post('<?php echo base_url(); ?>' + 'admin_panel/getCompanies', '', config).then(function(response) {
				if (response.data.success) {

					$scope.companies = response.data.companies;


					$('body').LoadingOverlay("hide");
				} else {

					$('body').LoadingOverlay("hide");
				}

			}, function(error) {
				console.log(error.data);
			});
		}

		$scope.getOutlets = function() {
			$('#outlet-input').LoadingOverlay("show", {
				maxSize: 50
			});
			console.log($scope.payroll.company);
			if ($scope.payroll.company == undefined) {
				$scope.outlets = [];
				$scope.payroll.outlet = '';
				$('#outlet-input').LoadingOverlay("hide");
			} else {
				$http.post('<?php echo base_url(); ?>' + 'admin_panel/getOutlets', {
					id: $scope.payroll.company
				}, config).then(function(response) {
					if (response.data.success) {

						$scope.outlets = response.data.outlets;
						$scope.payroll.outlet = '';


						$('#outlet-input').LoadingOverlay("hide");
					} else {

						$('#outlet-input').LoadingOverlay("hide");
					}

				}, function(error) {
					console.log(error.data);
				});
			}
		}

		$scope.getOutletsForLeave = function() {
			$('#outlet-input-leave').LoadingOverlay("show", {
				maxSize: 50
			});
			console.log($scope.leave.company);
			if ($scope.leave.company == undefined) {
				$scope.outlets = [];
				$scope.leave.outlet = '';
				$('#outlet-input-leave').LoadingOverlay("hide");
			} else {
				$http.post('<?php echo base_url(); ?>' + 'admin_panel/getOutlets', {
					id: $scope.leave.company
				}, config).then(function(response) {
					if (response.data.success) {

						$scope.outlets = response.data.outlets;
						$scope.leave.outlet = '';


						$('#outlet-input-leave').LoadingOverlay("hide");
					} else {

						$('#outlet-input-leave').LoadingOverlay("hide");
					}

				}, function(error) {
					console.log(error.data);
				});
			}
		}

		$scope.getXcrud = function() {
			var el = document.getElementById('ui-datepicker-div');
			if (el != null) {
				el.remove();
			}

			$('body').LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post('<?php echo base_url(); ?>' + 'admin_panel/getSubscriptions', {
				company_id: $scope.company_selected
			}, config).then(function(response) {

				$('#xcrudDiv').html(response.data);


				$('body').LoadingOverlay("hide");

			}, function(error) {
				console.log(error.data);
			});
		}
	});

	$(function() {
		// function loadXcrud() {
		// 	const year = $("#year-select").val();
		// 	$('#setting-xcrud').fadeOut();
		// 	$("#xcrud-loading").LoadingOverlay("show");
		// 	$.ajax({
		// 		type: "GET",
		// 		url: "<?php echo base_url() ?>admin_panel/get_packages_xcrud",
		// 		data: {
		// 			year
		// 		},
		// 		success: function(result) {
		// 			$("#xcrud-loading").LoadingOverlay("hide");
		// 			if (result) {
		// 				// console.log(result);
		// 				$('#setting-xcrud').fadeIn();
		// 				$('#setting-xcrud').html(result);
		// 			}
		// 		}
		// 	});

		// }
		// // call it on page load
		// loadXcrud();

		// function loadXcrudCompany() {
		// 	const year = $("#year-select").val();
		// 	$('#setting-xcrud-company').fadeOut();
		// 	$("#xcrud-loading-company").LoadingOverlay("show");
		// 	$.ajax({
		// 		type: "GET",
		// 		url: "<?php echo base_url() ?>admin_panel/get_company_xcrud",
		// 		data: {
		// 			year
		// 		},
		// 		success: function(result) {
		// 			$("#xcrud-loading-company").LoadingOverlay("hide");
		// 			if (result) {
		// 				// console.log(result);
		// 				$('#setting-xcrud-company').fadeIn();
		// 				$('#setting-xcrud-company').html(result);
		// 			}
		// 		}
		// 	});

		// }
		// // call it on page load
		// loadXcrudCompany();

		// function loadAnnouncementsXcrud() {
		// 	$.ajax({
		// 		type: "GET",
		// 		url: "<?php echo base_url() ?>admin_panel/get_announcements_xcrud",
		// 		success: function(result) {
		// 			if (result) {
		// 				$('#announcements-xcrud').html(result);
		// 			}
		// 		}
		// 	});

		// }
		// // call it on page load
		// loadAnnouncementsXcrud();
	});

	$(document).ready(function() {
		$('.edit_company').click(function() {
			$('#editModal').modal('show');
			var id = $(this).data('id');
			$.ajax({
				url: "<?= base_url() . 'admin_panel/get_package_settings/' ?>" + id,
				type: 'POST',
				// dataType: JSON,
				success: function(response) {
					json = JSON.parse(response);
					// console.log(json.id);
					$('#edit_pk_id').val(json.id);
					$('#edit_pk_name').val(json.name);
					$('#edit_pk_max_outlets').val(json.max_outlets);
					$('#edit_pk_max_active_staff').val(json.max_active_staff);
				}
			})
		})
		$('#save_edit_package').click(function() {
			var id = $('#edit_pk_id').val();
			var name = $('#edit_pk_name').val();
			var max_outlets = $('#edit_pk_max_outlets').val();
			var max_active_staff = $('#edit_pk_max_active_staff').val();
			$.ajax({
				url: "<?= base_url() . 'admin_panel/update_package_settings/' ?>",
				type: 'POST',
				data: {
					id: id,
					name: name,
					max_outlets: max_outlets,
					max_active_staff: max_active_staff
				},
				// dataType: JSON,
				success: function(response) {
					json = JSON.parse(response);
					console.log($(this).closest('tr'));
					// alert($(this).closest('tr'));
					$('#editModal').modal('hide');
					location.reload();
				}
			})
		})

		$(".select2").select2({
			theme: "bootstrap",
			width: '100%'
		});
	})
</script>
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel"><strong> Edit Package </strong></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form name="company_form" ng-submit="onSubmit(company_form.$valid)">
					<div class="row">
						<div class="col-md-6">
							<input type="hidden" id="edit_pk_id">
							<div class="form-group">
								<label class="control-label">Name*</label>
								<input type="text" class="form-control" placeholder="Enter Name" id="edit_pk_name" name="edit_pk_name" required="">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Max outlets</label>
								<input type="text" class="form-control" placeholder="Enter Max outlets" id="edit_pk_max_outlets" name="edit_pk_max_outlets">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">Max active staff</label>
								<input type="text" class="form-control" placeholder="Enter Max active staff" id="edit_pk_max_active_staff" name="edit_pk_max_active_staff">
							</div>
						</div>
						<div class="col-md-12">
							<div class="form-group">
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				<button type="button" id="save_edit_package" class="btn btn-primary">Save changes</button>
			</div>
		</div>
	</div>
</div>