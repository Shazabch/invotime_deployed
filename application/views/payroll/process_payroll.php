<?php include(APPPATH . "views/payroll/header.php"); ?>
<?php include(APPPATH . "views/payroll/sidebar.php"); ?>
<style>
	.multiselect{
		height: 50px;
	}
	.control-label{
		font-weight: normal;
	}
	button.multiselect {
		background-color: initial;
		border: 1px solid #ced4da;
	}
	.multiselect-container{
		width: 100%;
		max-height: 300px;
		overflow-y: auto;
		margin-bottom: 20px;
	}
	.multiselect-all{
		width: 100%;
		display: block;
		text-align: left;
		background-color: white;
		border: 0;
	}
	.multiselect-group{
		width: 100%;
		display: block;
		text-align: left;
		background-color: white;
		border: 0;
	}
	.multiselect-option{
		width: 95%;
		display: block;
		margin-left: 5%;
		text-align: left;
		background-color: white;
		border: 0;
	}
	.multiselect-container .multiselect-option, .multiselect-container .multiselect-group, .multiselect-container .multiselect-all{
		padding: 0.25rem 0.25rem 0.25rem 0rem;
	}
	.multiselect-container .multiselect-option.multiselect-group-option-indented{
		padding-left: 0;
	}
	.form-check-label{
		padding-left: 5px;
		font-weight: normal;
	}
	.multiselect-all .form-check-label{
		font-weight: bold;
		color: blue;
	}
	.multiselect-group .form-check-label{
		color: indigo;
		font-weight: bold;
	}
</style>
<div class="page-wrapper" ng-app="payroll_dashboard" ng-controller="processPayrollCtrl" ng-init="getData()">
	<div class="content container-fluid" ng-cloak>
		<div class="row bg-white">
			<div class="col-md-12">
				<br>
				<button class="btn btn-primary pull-right" style="margin-left: 5px;" data-toggle="modal" data-target="#addPayroll"  ng-click="refreshMulti()">New Payroll Process</button>
				<button class="btn btn-warning pull-right" ng-click="getData(true)"><i class="fa fa-refresh"></i></button>
			</div>

			<div class="col-md-12">
				<br>
				<br>
				<table class="table table-striped">
					<thead class="bg-primary">
						<tr>
							<th class="text-center"><b>Outlet</b></th>
							<th class="text-center"><b>Period</b></th>
							<th class="text-center"><b>Date</b></th>
							<th class="text-center"><b>Leave Cutoff Date</b></th>
							<th class="text-center"><b>Payroll Type</b></th>
							<th class="text-center"><b>Employee Count</b></th>
							<th class="text-center"><b>Description</b></th>
							<th class="text-center"><b>Is Committed</b></th>
							<th class="text-center"><b>Actions</b></th>
						</tr>
					</thead>
					<tbody>
						<tr ng-if="payrolls.length == 0">
							<td class="text-center" colspan="8">No data</td>
						</tr>

						<tr ng-repeat="p in payrolls" class="text-center">
							<td>{{p.branch_name}}</td>
							<td>{{p.period_formatted}}</td>
							<td>{{p.date}}</td>
							<td>{{p.leave_cut_off}}</td>
							<td>{{p.payroll_type}}</td>
							<td>{{p.employee_count}}</td>
							<td>{{p.description}}</td>
							<td>
								<i class="fa fa-check text-success" ng-if="p.is_committed"></i>
								<i class="fa fa-close text-danger" ng-if="!p.is_committed"></i>
							</td>
							<td>
								<a href="<?php echo base_url(); ?>payroll/report/{{p.id}}" target="_blank"><button class="btn btn-primary btn-xs"><i class="fa fa-eye"></i></button></a>
								<button class="btn btn-info btn-xs" ng-click="editPayroll(p)" data-toggle="modal" data-target="#editPayroll"><i class="fa fa-edit"></i></button>
								<button class="btn btn-danger btn-xs" ng-click="deletePayroll(p)" data-toggle="modal" data-target="#deletePayroll"><i class="fa fa-trash"></i></button>
							</td>
						</tr>
						
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div id="addPayroll" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header bg-primary">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">New Payroll Process</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<form class="form-horizontal" name="process_payroll_form">
							<div class="col-md-6">
								<div class="form-group">
									<label class="control-label col-md-4">Payroll Type <span class="text-danger">*</span></label>
									<div class="col-md-8">
										<select class="form-control" required="" ng-model="process.type">
											<option value="">Select</option>
											<option value="first_half">First Half</option>
											<option value="second_half">Month End / Second Half</option>
											<option value="bonus">Bonus</option>
											<option value="commission">Commission</option>
											<option value="claim">Claim</option>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-6" ng-show="process.type == 'bonus'">
								<div class="checkbox">
									<label><input type="checkbox" ng-click="toggleIncludeFix()" ng-checked="process.include_fix == 'Y'"><b>Include fix allowance & deduction</b></label>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<label class="control-label col-md-2">Description</label>
									<div class="col-md-10">
										<input type="text" class="form-control" ng-model="process.description">
									</div>
								</div>
							</div>
							<div class="col-md-6" ng-if="admin_type == 'company'">
								<div class="form-group">
									<label class="control-label col-md-4">Outlet <span class="text-danger">*</span></label>
									<div class="col-md-8">
										<select class="form-control" ng-model="process.payroll_branch_id" required="">
											<option value="">Select</option>
											<option value="{{b.id}}" ng-repeat="b in branches">{{b.name}}</option>
										</select>
									</div>
								</div>
							</div>
							<br>
							<div class="col-md-12">
								<br>
								<p style="font-weight: 200; font-size: 20px;">Payroll Period</p>
								<hr style="margin-top: 0px;">
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="control-label col-md-4">Month <span class="text-danger">*</span></label>
									<div class="col-md-8">
										<select class="form-control" required="" ng-model="process.month" ng-change="getDays();getEmployees();">
											<option value="">Select a month</option>
											<option value="01">January</option>
											<option value="02">February</option>
											<option value="03">March</option>
											<option value="04">April</option>
											<option value="05">May</option>
											<option value="06">June</option>
											<option value="07">July</option>
											<option value="08">August</option>
											<option value="09">September</option>
											<option value="10">October</option>
											<option value="11">November</option>
											<option value="12">December</option>
										</select>
									</div>
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-group">
									<label class="control-label col-md-4">Year <span class="text-danger">*</span></label>
									<div class="col-md-8">
										<select class="form-control" required="" ng-model="process.year" ng-change="getDays();getEmployees();">
											<option value="">Select a year</option>
											<option value="{{y}}" ng-repeat="y in years">{{y}}</option>
										</select>
									</div>
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-group">
									<label class="control-label col-md-4">Leave Cut Off Date</label>
									<div class="col-md-8">
										<select class="form-control" required="" ng-model="process.leave_cut_off" ng-disabled="true">
											<option value="{{c}}" ng-repeat="c in current_dates">{{c}}</option>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-6" ng-show="process.type == 'bonus'">
								<div class="form-group">
									<label class="control-label col-md-4">No. of Bonus Month <span class="text-danger">*</span></label>
									<div class="col-md-8">
										<input type="number" class="form-control" ng-model="process.bonus_months" ng-required="process.type == 'bonus'">
									</div>
								</div>
							</div>
							<br>
							<div class="col-md-12">
								<br>
								<div class="form-group">
									<label class="control-label col-md-2">Group By</label>
									<div class="col-md-10">
										<select class="form-control multi" multiple="" ng-model="process.employees_group" ng-change="changeGroup()">
											<option value="outlet">Outlet</option>
											<option value="department">Department</option>
											<option value="level">Level</option>
											<option value="role">Role</option>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<label class="control-label col-md-2">Employees</label>
									<div class="col-md-10">

											<!-- <div ng-show="group == 'simple'"> -->
												<select class="form-control multi grouped_employees" multiple="" ng-model="process.employees">
													
												</select>
											<!-- </div>
											<div ng-show="group == 'department'">
												<select class="form-control multi department_employees" multiple="" ng-model="process.employees">
													
												</select>
											</div>
											<div ng-show="group == 'role'">
												<select class="form-control multi role_employees" multiple="" ng-model="process.employees">
													
												</select>
											</div>
											<div ng-show="group == 'department_role'">
												<select class="form-control multi department_role_employees" multiple="" ng-model="process.employees">
													
												</select>
												
											</div> -->
										
									</div>
								</div>
							</div>
							
						</form>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" style="width: 80px;" ng-click="saveNewProcess(process_payroll_form.$valid)">Process</button>
				</div>
			</div>

		</div>
	</div>


	<div id="editPayroll" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header bg-primary">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Edit Payroll Process</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<form class="form-horizontal" name="edit_process_payroll_form">
							<div class="col-md-6">
								<div class="form-group">
									<label class="control-label col-md-4">Payroll Type <span class="text-danger">*</span></label>
									<div class="col-md-8">
										<select class="form-control" required="" ng-model="editProcess.type">
											<option value="">Select</option>
											<option value="first_half">First Half</option>
											<option value="second_half">Month End / Second Half</option>
											<option value="bonus">Bonus</option>
											<option value="commission">Commission</option>
											<option value="claim">Claim</option>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-6" ng-show="editProcess.type == 'bonus'">
								<div class="checkbox">
									<label><input type="checkbox" ng-click="toggleIncludeFixEdit()" ng-checked="editProcess.include_fix == 'Y'"><b>Include fix allowance & deduction</b></label>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<label class="control-label col-md-2">Description</label>
									<div class="col-md-10">
										<input type="text" class="form-control" ng-model="editProcess.description">
									</div>
								</div>
							</div>
							<div class="col-md-6" ng-if="admin_type == 'company'">
								<div class="form-group">
									<label class="control-label col-md-4">Outlet <span class="text-danger">*</span></label>
									<div class="col-md-8">
										<select class="form-control" ng-model="editProcess.payroll_branch_id" required="">
											<option value="">Select</option>
											<option value="{{b.id}}" ng-repeat="b in branches">{{b.name}}</option>
										</select>
									</div>
								</div>
							</div>
							<br>
							<div class="col-md-12">
								<br>
								<p style="font-weight: 200; font-size: 20px;">Payroll Period</p>
								<hr style="margin-top: 0px;">
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="control-label col-md-4">Month <span class="text-danger">*</span></label>
									<div class="col-md-8">
										<select class="form-control" required="" ng-model="editProcess.month" ng-change="getDaysEdit();getEmployees(true, false);">
											<option value="">Select a month</option>
											<option value="01">January</option>
											<option value="02">February</option>
											<option value="03">March</option>
											<option value="04">April</option>
											<option value="05">May</option>
											<option value="06">June</option>
											<option value="07">July</option>
											<option value="08">August</option>
											<option value="09">September</option>
											<option value="10">October</option>
											<option value="11">November</option>
											<option value="12">December</option>
										</select>
									</div>
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-group">
									<label class="control-label col-md-4">Year <span class="text-danger">*</span></label>
									<div class="col-md-8">
										<select class="form-control" required="" ng-model="editProcess.year" ng-change="getDaysEdit();getEmployees(true, false);">
											<option value="">Select a year</option>
											<option value="{{y}}" ng-repeat="y in years">{{y}}</option>
										</select>
									</div>
								</div>
							</div>

							<div class="col-md-6">
								<div class="form-group">
									<label class="control-label col-md-4">Leave Cut Off Date</label>
									<div class="col-md-8">
										<select class="form-control" required="" ng-model="editProcess.leave_cut_off" ng-disabled="true">
											<option value="{{c}}" ng-repeat="c in current_dates">{{c}}</option>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-6" ng-show="editProcess.type == 'bonus'">
								<div class="form-group">
									<label class="control-label col-md-4">No. of Bonus Month <span class="text-danger">*</span></label>
									<div class="col-md-8">
										<input type="number" class="form-control" ng-model="editProcess.bonus_months" ng-required="editProcess.type == 'bonus'">
									</div>
								</div>
							</div>
							<br>
							<div class="col-md-12">
								<br>
								<div class="form-group">
									<label class="control-label col-md-2">Group By</label>
									<div class="col-md-10">
										<select class="form-control multi multi_group" multiple="" ng-model="editProcess.employees_group" ng-change="changeGroupEdit()">
											<option value="outlet">Outlet</option>
											<option value="department">Department</option>
											<option value="level">Level</option>
											<option value="role">Role</option>
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<label class="control-label col-md-2">Employees</label>
									<div class="col-md-10">

											<!-- <div ng-show="editGroup == 'simple'"> -->
												<select class="form-control multi multi_employees grouped_employees" multiple="" ng-model="editProcess.employees">
													
												</select>
											<!-- </div>
											<div ng-show="editGroup == 'department'">
												<select class="form-control multi multi_employees department_employees" multiple="" ng-model="editProcess.employees">
													
												</select>
											</div>
											<div ng-show="editGroup == 'role'">
												<select class="form-control multi multi_employees role_employees" multiple="" ng-model="editProcess.employees">
													
												</select>
											</div>
											<div ng-show="editGroup == 'department_role'">
												<select class="form-control multi multi_employees department_role_employees" multiple="" ng-model="editProcess.employees">
													
												</select>
												
											</div> -->
										
									</div>
								</div>
							</div>
							
						</form>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" style="width: 80px;" ng-click="updateProcess(edit_process_payroll_form.$valid)">Update</button>
				</div>
			</div>

		</div>
	</div>


	<div id="deletePayroll" class="modal fade" role="dialog">
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header bg-danger">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Delete Payroll Process Confirmation</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<p>Do you really want to delete this Payroll Process?</p>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
					<button type="button" class="btn btn-danger" style="width: 80px;" ng-click="deletePayrollConfirmed()">Delete</button>
				</div>
			</div>

		</div>
	</div>


</div>

<?php include(APPPATH . "views/payroll/footer.php"); ?>

<script type="text/javascript">
    $('.multi').multiselect({
    	includeSelectAllOption: true,
    	buttonWidth: '100%',
    	enableClickableOptGroups: true
    });
    var base_url = '<?php echo base_url(); ?>';
</script>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/payroll_dashboard.js?v=1.8"></script>