<?php include(APPPATH . "views/payroll/header.php"); ?>
<?php include(APPPATH . "views/payroll/sidebar.php"); ?>
<style type="text/css">
	select,
	select option {
		color: black;
	}

	select:invalid,
	select option[value=""] {
		color: lightgray;
	}

	.collapse{
		max-height:250px;
		overflow:scroll;
		display:none;
		color:#f62d51;
	}

	.collapse table{
		color:#f62d51;
	}

	.collapse hr{
		margin-top: 5px;
		margin-bottom: 5px;
	}
</style>
<div class="page-wrapper" ng-app="payroll_dashboard" ng-controller="firstTimeSetupCtrl" ng-init="getData()">
	<div class="content container-fluid" ng-cloak>
		<div class="row bg-white" style="min-height: 530px;" ng-show="show_wizard">
			<div class="col-md-12">
				<br>
				<ul class="nav nav-pills nav-stacked col-md-2">
					<li ng-class="{'active': current_step == 1}"><a data-toggle="pill" href="#profile" ng-click="step_changed(1)">Company Profile <i class="fa fa-check-circle" ng-if="steps_done > 0"></i></a></li>

					<li ng-if="steps_done < 1"><a>Bank Accounts</a></li>
					<li ng-if="steps_done > 0" ng-class="{'active': current_step == 2}"><a data-toggle="pill" href="#account" ng-click="step_changed(2)">Bank Accounts <i class="fa fa-check-circle" ng-if="steps_done > 1"></i></a></li>
					<li ng-if="steps_done < 2"><a>Allowances</a></li>
					<li ng-if="steps_done > 1" ng-class="{'active': current_step == 3}"><a data-toggle="pill" href="#allowance" ng-click="step_changed(3)">Allowances <i class="fa fa-check-circle" ng-if="steps_done > 2"></i></a></li>
					<li ng-if="steps_done < 3"><a>Deductions</a></li>
					<li ng-if="steps_done > 2" ng-class="{'active': current_step == 4}"><a data-toggle="pill" href="#deduction" ng-click="step_changed(4)">Deductions <i class="fa fa-check-circle" ng-if="steps_done > 3"></i></a></li>
					<!-- <li ng-if="steps_done < 4"><a>Calendar Setting</a></li>
						<li ng-if="steps_done > 3" ng-class="{'active': current_step == 5}"><a data-toggle="pill" href="#calendar" ng-click="step_changed(5)">Calendar Setting <i class="fa fa-check-circle" ng-if="steps_done > 4"></i></a></li> -->
						<li ng-if="steps_done < 4"><a>Import Employee</a></li>
						<li ng-if="steps_done > 3" ng-class="{'active': current_step == 5}"><a data-toggle="pill" href="#import" ng-click="step_changed(5)">Import Employee <i class="fa fa-check-circle" ng-if="steps_done > 4"></i></a></li>
						<li ng-if="steps_done < 5"><a>Complete</a></li>
						<li ng-if="steps_done > 4" ng-class="{'active': current_step == 6}"><a data-toggle="pill" href="#complete" ng-click="step_changed(6)">Complete <i class="fa fa-check-circle" ng-if="steps_done >= 5"></i></a></li>







					</ul>

					<div class="tab-content col-md-10" style="height: 450px; overflow-y: auto;">
						<div id="profile" ng-class="{'tab-pane fade in active': current_step == 1, 'tab-pane fade': current_step != 1}">
							<h3>Fill out your company profile information.</h3>
							<br>
							<div class="row">
								<form name="profile_form">
									<div class="col-md-4">
										<div class="form-group">
											<label>Company Name <span class="text-danger">*</span></label>
											<input type="text" class="form-control" required="" ng-model="profile.name">
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label>Country <span class="text-danger">*</span></label>
											<select class="form-control apply-select2" required="" ng-model="profile.country_id" ng-change="getStates()" style="width: 100%;">
												<option value="">Select country</option>
												<option value="{{c.id}}" ng-repeat="c in countries">{{c.name}}</option>
											</select>
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group">
											<label>EPF No. <span class="text-danger">*</span></label>
											<input type="text" class="form-control" ng-model="profile.epf_no" required="">
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group">
											<label>EPF % <span class="text-danger">*</span></label>
											<input type="number" class="form-control" ng-model="profile.epf_percentage" required="">
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label>Company Registration No. <span class="text-danger">*</span></label>
											<input type="text" class="form-control" required="" ng-model="profile.company_registration_number">
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label>State <span class="text-danger">*</span></label>
											<select class="form-control apply-select2" required="" ng-model="profile.state_id" style="width: 100%;">
												<option value="">Select state</option>
												<option value="{{s.id}}" ng-repeat="s in states">{{s.name}}</option>
											</select>
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label>SOCSO No. <span class="text-danger">*</span></label>
											<input type="text" class="form-control" ng-model="profile.socso_no" required="">
										</div>
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label>Phone No. <span class="text-danger">*</span></label>
											<input type="text" class="form-control" required="" ng-model="profile.phone">
										</div>

										<div class="form-group">
											<label>Autopay Org Code (CIMB)</label>
											<input type="text" class="form-control" ng-model="profile.autopay_code">
										</div>


									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label>Address <span class="text-danger">*</span></label>
											<textarea class="form-control" rows="5" style="resize: none;" required="" ng-model="profile.address"></textarea>
										</div>
									</div>


									<div class="col-md-4">
										<div class="form-group">
											<label>Employer File No.</label>
											<input type="text" class="form-control" ng-model="profile.employer_file_no">
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group">
											<label>Tax No. <span class="text-danger">*</span></label>
											<input type="text" class="form-control" ng-model="profile.tax_number" required="">
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group">
											<label>HRDF %</label>
											<input type="number" class="form-control" ng-model="profile.hrdf_percentage" ng-change="validatePercentage()">
										</div>
									</div>
								</form>
							</div>
						</div>
						<div id="account" ng-class="{'tab-pane fade in active': current_step == 2, 'tab-pane fade': current_step != 2}">
							<h3>Fill in your company bank accounts.</h3>
							<br>
							<div class="row">
								<div class="col-md-12">
									<button class="btn btn-primary pull-right" data-toggle="modal" data-target="#addBank">Add New Bank</button>
								</div>
							</div>
							<br>
							<table class="table table-striped">
								<thead class="bg-primary">
									<tr>
										<th><b>Bank</b></th>
										<th><b>Account Number</b></th>
										<th><b>State</b></th>
										<th><b>Is Main Account</b></th>
										<th><b>Actions</b></th>
									</tr>
								</thead>
								<tbody>
									<tr ng-if="company_banks.length == 0">
										<td class="text-center" colspan="5">No data</td>
									</tr>
									<tr ng-repeat=" c in company_banks">
										<td style="width: 220px;">{{c.bank}}</td>
										<td>{{c.account_no}}</td>
										<td>{{c.state}}</td>
										<td><span ng-if="c.is_main == 'Y'"><i class="fa fa-check"></i></span></td>
										<td>
											<button class="btn btn-info btn-sm" ng-click="editBank(c)" data-toggle="modal" data-target="#editBank"><i class="fa fa-edit"></i></button>
											<button class="btn btn-danger btn-sm" ng-click="deleteBank(c)" data-toggle="modal" data-target="#deleteBank"><i class="fa fa-trash"></i></button>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div id="allowance" ng-class="{'tab-pane fade in active': current_step == 3, 'tab-pane fade': current_step != 3}">
							<h3>Maintain your company allowances.</h3>
							<br>
							<div class="row">
								<div class="col-md-12">
									<button class="btn btn-primary pull-right" ng-click="addNewAllowance()">Add New</button>
								</div>
							</div>
							<br>
							<table class="table table-striped">
								<thead class="bg-primary">
									<tr>
										<th style="width: 200px;"><b>Allowance Name</b></th>
										<th><b>Pay Tax</b></th>
										<th><b>Pay EPF</b></th>
										<th><b>Pay SOCSO</b></th>
										<th><b>Pay EIS</b></th>
										<th><b>Add to Total Wages</b></th>
										<th><b></b></th>
									</tr>
								</thead>
								<tbody>
									<tr ng-if="allowances.length == 0">
										<td class="text-center" colspan="7">No data</td>
									</tr>
									<tr ng-repeat="a in allowances track by $index">
										<td>
											<span ng-if="a.can_edit == 'no' || a.is_default == 'Y'">{{a.allowance_name}}</span>
											<div class="form-group" ng-if="a.can_edit == 'yes' && a.is_default != 'Y'">
												<input type="text" class="form-control" ng-model="a.allowance_name">
											</div>
										</td>
										<td class="text-center">
											<div class="checkbox m-t-0">
												<label><input type="checkbox"  ng-click="toggleCheck(allowances, $index, 'tax')" ng-checked="a.pay_tax == 'Y'" ng-disabled="a.can_edit == 'no'"></label>
											</div>
										</td>
										<td class="text-center">
											<div class="checkbox m-t-0">
												<label><input type="checkbox"  ng-click="toggleCheck(allowances, $index, 'epf')" ng-checked="a.pay_epf == 'Y'" ng-disabled="a.can_edit == 'no'"></label>
											</div>
										</td>
										<td class="text-center">
											<div class="checkbox m-t-0">
												<label><input type="checkbox"  ng-click="toggleCheck(allowances, $index, 'socso')" ng-checked="a.pay_socso == 'Y'" ng-disabled="a.can_edit == 'no'"></label>
											</div>
										</td>
										<td class="text-center">
											<div class="checkbox m-t-0">
												<label><input type="checkbox"  ng-click="toggleCheck(allowances, $index, 'eis')" ng-checked="a.pay_eis == 'Y'" ng-disabled="a.can_edit == 'no'"></label>
											</div>
										</td>
										<td class="text-center">
											<div class="checkbox m-t-0">
												<label><input type="checkbox"  ng-click="toggleCheck(allowances, $index, 'eligible_salary')" ng-checked="a.eligible_salary == 'Y'" ng-disabled="a.can_edit == 'no'"></label>
											</div>
										</td>
										<td>
											<button class="btn btn-info btn-xs" ng-click="editAllowance($index)" ng-show="a.can_edit == 'no'"><i class="fa fa-edit"></i></button>
											<button ng-disabled="a.is_default == 'Y'" class="btn btn-danger btn-xs" ng-click="deleteAllowance($index)"><i class="fa fa-trash"></i></button>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div id="deduction" ng-class="{'tab-pane fade in active': current_step == 4, 'tab-pane fade': current_step != 4}">
							<h3>Maintain your company deductions.</h3>
							<br>
							<div class="row">
								<div class="col-md-12">
									<button class="btn btn-primary pull-right" ng-click="addNewDeduction()">Add New</button>
								</div>
							</div>
							<br>
							<table class="table table-striped">
								<thead class="bg-primary">
									<tr>
										<th style="width: 200px;"><b>Deduction Name</b></th>
										<th><b>Pay Tax</b></th>
										<th><b>Pay EPF</b></th>
										<th><b>Pay SOCSO</b></th>
										<th><b>Pay EIS</b></th>
										<th><b>Pay HRDF</b></th>
										<th><b></b></th>
									</tr>
								</thead>
								<tbody>
									<tr ng-if="deductions.length == 0">
										<td class="text-center" colspan="7">No data</td>
									</tr>
									<tr ng-repeat="d in deductions track by $index">
										<td>
											<span ng-if="d.can_edit == 'no'">{{d.deduction_name}}</span>
											<div class="form-group" ng-if="d.can_edit == 'yes'">
												<input type="text" class="form-control" ng-model="d.deduction_name">
											</div>
										</td>
										<td class="text-center">
											<div class="checkbox m-t-0">
												<label><input type="checkbox"  ng-click="toggleCheck(deductions, $index, 'tax')" ng-checked="d.pay_tax == 'Y'" ng-disabled="d.can_edit == 'no'"></label>
											</div>
										</td>
										<td class="text-center">
											<div class="checkbox m-t-0">
												<label><input type="checkbox"  ng-click="toggleCheck(deductions, $index, 'epf')" ng-checked="d.pay_epf == 'Y'" ng-disabled="d.can_edit == 'no'"></label>
											</div>
										</td>
										<td class="text-center">
											<div class="checkbox m-t-0">
												<label><input type="checkbox"  ng-click="toggleCheck(deductions, $index, 'socso')" ng-checked="d.pay_socso == 'Y'" ng-disabled="d.can_edit == 'no'"></label>
											</div>
										</td>
										<td class="text-center">
											<div class="checkbox m-t-0">
												<label><input type="checkbox"  ng-click="toggleCheck(deductions, $index, 'eis')" ng-checked="d.pay_eis == 'Y'" ng-disabled="d.can_edit == 'no'"></label>
											</div>
										</td>
										<td class="text-center">
											<div class="checkbox m-t-0">
												<label><input type="checkbox"  ng-click="toggleCheck(deductions, $index, 'hrdf')" ng-checked="d.pay_hrdf == 'Y'" ng-disabled="d.can_edit == 'no'"></label>
											</div>
										</td>
										<td>
											<button class="btn btn-info btn-xs" ng-click="editDeduction($index)" ng-show="d.can_edit == 'no'"><i class="fa fa-edit"></i></button>
											<button class="btn btn-danger btn-xs" ng-click="deleteDeduction($index)"><i class="fa fa-trash"></i></button>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					<!-- <div id="calendar" ng-class="{'tab-pane fade in active': current_step == 5, 'tab-pane fade': current_step != 5}">
						<h3>Fill in codes and select state of calendar.</h3>
						<br>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label>Code</label>
									<input type="text" placeholder="Code" class="form-control" ng-model="calendar.calendar_code">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>State</label>
									<select class="form-control apply-select2" ng-model="calendar.calendar_state_id" style="width: 100%;">
										<option value="">Select state</option>
										<option value="{{s.id}}" ng-repeat="s in malaysia_states">{{s.name}}</option>
									</select>
								</div>
							</div>
						</div>
						<p><b>Rest Days</b></p><br>
						<div class="row">
							<div class="col-md-2"><b>Select</b></div>
							<div class="col-md-3"><b>Month</b></div>
							<div class="col-md-3"><b>Week Day</b></div>
							<div class="col-md-3"><b>Day Type</b></div>
						</div>
						<br>
						<div class="row" ng-repeat="day in calendar.rest_days track by $index">
							<div class="col-md-2">
								<div class="checkbox">
									<label><input type="checkbox"  ng-click="toggleSelectCalendar($index)" ng-checked="calendar.rest_days[$index].is_apply == 'Y'"></label>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<select class="form-control apply-select2-dynamic" ng-model="calendar.rest_days[$index].month" style="width: 100%;" required="">
										<option value="">Select</option>
										<option value="Every Month">Every Month</option>
										<option value="Odd Month">Odd Month</option>
										<option value="Even Month">Even Month</option>
									</select>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<select class="form-control apply-select2-dynamic" ng-model="calendar.rest_days[$index].week_day" style="width: 100%;" required="">
										<option value="">Select</option>
										<option value="Saturday">Saturday</option>
										<option value="Sunday">Sunday</option>
										<option value="Monday">Monday</option>
										<option value="Tuesday">Tuesday</option>
										<option value="Wednesday">Wednesday</option>
										<option value="Thursday">Thursday</option>
										<option value="Friday">Friday</option>
									</select>
								</div>
							</div>
							<div class="col-md-3">
								<div class="form-group">
									<select class="form-control apply-select2-dynamic" ng-model="calendar.rest_days[$index].day_type" style="width: 100%;" required="">
										<option value="">Select</option>
										<option value="Full Day">Full Day</option>
										<option value="AM">AM</option>
										<option value="PM">PM</option>
									</select>
								</div>
							</div>
						</div>
					</div> -->
					<div id="import" ng-class="{'tab-pane fade in active': current_step == 5, 'tab-pane fade': current_step != 5}">
						<h3>Import Employee</h3>
						<div class="panel panel-primary">
							<div class="panel-body">
								<!-- <h4 class="page-title"><?php echo $pageTitle ?></h4> -->
								<h4 class="page-title">Employees Basic Info</h4>
								<!-- <h4 class="m-t-0">Your Title</h4> -->
								<div id="div-basic-info">
									<input data-file="basic-info" type="file" name="file1"/>

									<p style="font-weight:bold;margin-top:10px" class="msg"></p>

									<button style="display:none;margin-top:5px" class="btn-import btn btn-primary btn-sm">Import</button>

									<button style="display:none;margin-top:5px"  class="btn-invalid btn btn-danger btn-xs">View Details <i style="font-weight:bold" class="fa fa-angle-down"></i></button>
									<div class="collapse">




									</div>


								</div>
							</div>
						</div>
						<div>
							Download the sample Excel template file from <a target="_blank" href="<?php echo base_url() ?>assets/import_template.xlsx">here</a>, <b>convert all the Excel sheets into separate CSV files</b> before uploading in the relevant section below.<br/>
							Download list of Bank Names from <a target="_blank" href="<?php echo base_url() ?>assets/banks.xlsx">here</a>, any mismatch in Bank Name will result in failure to import employees.
							<br/><br/>
							<p style="color:blue">
								<b>Considerations:</b> 
								<br/>*Use dd-mm-yyyy format for date fields, example 31-12-2016 
								<br/>*Red columns in the template indicate that the field is required
								<br/>*File must be in a CSV format. Excel to CSV conversion can be done using any spreadsheet software e.g MS Excel, Google Sheets etc
							</p>
						</div>
					</div>
					<div id="complete" ng-class="{'tab-pane fade in active': current_step == 6, 'tab-pane fade': current_step != 6}">
						<h3>First Time Setup Complete</h3>
						<p>Congratulations! You have successfully completed your setup.</p>
						<br>
						<br>
						<h3>Now you can run the payroll process.</h3>
					</div>
				</div>


			</div>
			<div class="col-md-12">
				<br>
				<div class="pull-right">
					<button class="btn btn-warning" ng-if="current_step > 1" ng-click="back_step()" style="width: 150px;">Back</button>
					<button class="btn btn-primary" ng-if="current_step == 1" ng-click="saveProfile(profile_form.$valid)" style="width: 150px;">Save & Next</button>

					<button class="btn btn-primary" ng-if="current_step == 2 && company_banks.length == 0 && steps_done == 1" style="width: 150px;" ng-click="banksDone(true)">Skip</button>
					<button class="btn btn-primary" ng-if="current_step == 2 && company_banks.length != 0 && steps_done == 1" style="width: 150px;" ng-click="banksDone()">Next</button>
					<button class="btn btn-primary" ng-if="current_step == 2 && steps_done >= 2" style="width: 150px;" ng-click="inc_step()">Next</button>

					<button class="btn btn-primary" ng-if="current_step == 3" style="width: 150px;" ng-click="saveAllowances(false)">Save</button>
					<button class="btn btn-primary" ng-if="current_step == 3" style="width: 150px;" ng-click="saveAllowances()">Save & Next</button>
					
					<button class="btn btn-primary" ng-if="current_step == 4" style="width: 150px;" ng-click="saveDeductions(false)">Save</button>
					<button class="btn btn-primary" ng-if="current_step == 4" style="width: 150px;" ng-click="saveDeductions()">Save & Next</button>


					<!-- <button class="btn btn-primary" ng-if="current_step == 5" style="width: 150px;" ng-click="saveCalendarSetting()">Save & Next</button> -->

					<button class="btn btn-primary" ng-if="current_step == 5 && steps_done < 5" style="width: 150px;" ng-click="importDone()">Next</button>
					<button class="btn btn-primary" ng-if="current_step == 5 && steps_done >= 5" style="width: 150px;" ng-click="inc_step()">Next</button>

					<a href="<?php echo base_url(); ?>invocore_payroll/process_payroll"><button class="btn btn-primary" ng-if="current_step == 6" style="width: 150px;">Yes, Run Payroll</button></a>
					
				</div>
			</div>
		</div>

	</div>

	<!-- Add Bank Modal -->
	<div id="addBank" class="modal fade" role="dialog">
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header bg-primary">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Add New Bank</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<form name="addBankForm">
							<div class="col-md-6">
								<div class="form-group">
									<label>Bank <span class="text-danger">*</span></label>
									<select class="form-control apply-select2 add-bank-select2" required="" ng-model="addBankModel.bank_id" style="width: 100%;">
										<option value="">Select a bank</option>
										<option value="{{b.id}}" ng-repeat="b in malaysia_banks">{{b.name}}</option>
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>Account Number <span class="text-danger">*</span></label>
									<input type="text" placeholder="Account Number" class="form-control" ng-model="addBankModel.account_no" required="">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>State <span class="text-danger">*</span></label>
									<select class="form-control apply-select2 add-bank-select2" required="" ng-model="addBankModel.state_id" style="width: 100%;">
										<option value="">Select a state</option>
										<option value="{{s.id}}" ng-repeat="s in malaysia_states">{{s.name}}</option>
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 30px;">
									<label><input type="checkbox" ng-click="toggleMainAdd()" ng-checked="addBankModel.is_main == 'Y'">Is Main Account</label>
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" style="width: 80px;" ng-click="createBank(addBankForm.$valid)">Save</button>
				</div>
			</div>

		</div>
	</div>

	<!-- Edit Bank Modal -->
	<div id="editBank" class="modal fade" role="dialog">
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header bg-primary">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Edit Bank Details</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<form name="editBankForm">
							<div class="col-md-6">
								<div class="form-group">
									<label>Bank <span class="text-danger">*</span></label>
									<select class="form-control apply-select2 edit-bank_id-select2" required="" ng-model="editBankModel.bank_id" style="width: 100%;">
										<option value="">Select a bank</option>
										<option value="{{b.id}}" ng-repeat="b in malaysia_banks">{{b.name}}</option>
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>Account Number <span class="text-danger">*</span></label>
									<input type="text" placeholder="Account Number" class="form-control" ng-model="editBankModel.account_no" required="">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>State <span class="text-danger">*</span></label>
									<select class="form-control apply-select2 edit-state_id-select2" required="" ng-model="editBankModel.state_id" style="width: 100%;">
										<option value="">Select a state</option>
										<option value="{{s.id}}" ng-repeat="s in malaysia_states">{{s.name}}</option>
									</select>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 30px;">
									<label><input type="checkbox" ng-click="toggleMainEdit()" ng-checked="editBankModel.is_main == 'Y'">Is Main Account</label>
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" style="width: 80px;" ng-click="updateBank(editBankForm.$valid)">Update</button>
				</div>
			</div>

		</div>
	</div>

	<!-- Delete Bank Modal -->
	<div id="deleteBank" class="modal fade" role="dialog">
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header bg-danger">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Delete Bank Confirmation</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<p>Do you really want to delete this bank?</p>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
					<button type="button" class="btn btn-danger" style="width: 80px;" ng-click="deleteBankConfirmed()">Delete</button>
				</div>
			</div>

		</div>
	</div>

	<!-- Add Allowance Modal -->
	<div id="addAllowance" class="modal fade" role="dialog">
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header bg-primary">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Add New Allowance</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<form name="addAllowanceForm">
							<div class="col-md-12">
								<div class="form-group">
									<label>Code <span class="text-danger">*</span></label>
									<input type="text" placeholder="Code" class="form-control" ng-model="addAllowanceModel.code" required="">
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<label>Description</label>
									<input type="text" placeholder="Description" class="form-control" ng-model="addAllowanceModel.description">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>Start Period</label>
									<div class='input-group date' id='datetimepicker1'>
										<input type='text' class="form-control" placeholder="Start Period" ng-model="addAllowanceModel.start_period" />
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar">
											</span>
										</span>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>End Period</label>
									<div class='input-group date' id='datetimepicker2'>
										<input type='text' class="form-control" placeholder="End Period" ng-model="addAllowanceModel.end_period" />
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar">
											</span>
										</span>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 0px;">
									<label><input type="checkbox" ng-click="toggleEpfAllowanceAdd()" ng-checked="addAllowanceModel.pay_epf == 'Y'">Pay EPF</label>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 0px;">
									<label><input type="checkbox" ng-click="toggleSocsoAllowanceAdd()" ng-checked="addAllowanceModel.pay_socso_eis == 'Y'">Pay SOCSO & EIS</label>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 0px;">
									<label><input type="checkbox" ng-click="toggleTaxAllowanceAdd()" ng-checked="addAllowanceModel.pay_tax == 'Y'">Pay Tax</label>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 0px;">
									<label><input type="checkbox" ng-click="toggleHrdfAllowanceAdd()" ng-checked="addAllowanceModel.pay_hrdf == 'Y'">Pay HRDF</label>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<label>Tax Exempted Rule</label>
									<select class="form-control apply-select2 tax-rule-add" ng-model="addAllowanceModel.tax_rule_id" style="width: 100%;">
										<option value="">Select a rule</option>
										<option value="{{t.id}}" ng-repeat="t in tax_rules">{{t.description}} (Limit: {{t.limit_amount | number : 2}})</option>
									</select>
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" style="width: 80px;" ng-click="createAllowance(addAllowanceForm.$valid)">Save</button>
				</div>
			</div>

		</div>
	</div>

	<!-- Edit Allowance Modal -->
	<div id="editAllowance" class="modal fade" role="dialog">
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header bg-primary">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Edit Allowance Details</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<form name="editAllowanceForm">
							<div class="col-md-12">
								<div class="form-group">
									<label>Code <span class="text-danger">*</span></label>
									<input type="text" placeholder="Code" class="form-control" ng-model="editAllowanceModel.code" required="">
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<label>Description</label>
									<input type="text" placeholder="Description" class="form-control" ng-model="editAllowanceModel.description">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>Start Period</label>
									<div class='input-group date' id='datetimepicker3'>
										<input type='text' class="form-control" placeholder="Start Period" ng-model="editAllowanceModel.start_period" />
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar">
											</span>
										</span>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>End Period</label>
									<div class='input-group date' id='datetimepicker4'>
										<input type='text' class="form-control" placeholder="End Period" ng-model="editAllowanceModel.end_period" />
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar">
											</span>
										</span>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 0px;">
									<label><input type="checkbox" ng-click="toggleEpfAllowanceEdit()" ng-checked="editAllowanceModel.pay_epf == 'Y'">Pay EPF</label>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 0px;">
									<label><input type="checkbox" ng-click="toggleSocsoAllowanceEdit()" ng-checked="editAllowanceModel.pay_socso_eis == 'Y'">Pay SOCSO & EIS</label>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 0px;">
									<label><input type="checkbox" ng-click="toggleTaxAllowanceEdit()" ng-checked="editAllowanceModel.pay_tax == 'Y'">Pay Tax</label>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 0px;">
									<label><input type="checkbox" ng-click="toggleHrdfAllowanceEdit()" ng-checked="editAllowanceModel.pay_hrdf == 'Y'">Pay HRDF</label>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<label>Tax Exempted Rule</label>
									<select class="form-control apply-select2 tax-rule-edit" ng-model="editAllowanceModel.tax_rule_id" style="width: 100%;">
										<option value="">Select a rule</option>
										<option value="{{t.id}}" ng-repeat="t in tax_rules">{{t.description}} (Limit: {{t.limit_amount | number : 2}})</option>
									</select>
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" style="width: 80px;" ng-click="updateAllowance(editAllowanceForm.$valid)">Update</button>
				</div>
			</div>

		</div>
	</div>

	<!-- Delete Allowance Modal -->
	<div id="deleteAllowance" class="modal fade" role="dialog">
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header bg-danger">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Delete Allowance Confirmation</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<p>Do you really want to delete this allowance?</p>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
					<button type="button" class="btn btn-danger" style="width: 80px;" ng-click="deleteAllowanceConfirmed()">Delete</button>
				</div>
			</div>

		</div>
	</div>

	<!-- Add Deduction Modal -->
	<div id="addDeduction" class="modal fade" role="dialog">
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header bg-primary">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Add New Deduction</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<form name="addDeductionForm">
							<div class="col-md-12">
								<div class="form-group">
									<label>Code <span class="text-danger">*</span></label>
									<input type="text" placeholder="Code" class="form-control" ng-model="addDeductionModel.code" required="">
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<label>Description</label>
									<input type="text" placeholder="Description" class="form-control" ng-model="addDeductionModel.description">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>Start Period</label>
									<div class='input-group date' id='datetimepicker5'>
										<input type='text' class="form-control" placeholder="Start Period" ng-model="addDeductionModel.start_period" />
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar">
											</span>
										</span>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>End Period</label>
									<div class='input-group date' id='datetimepicker6'>
										<input type='text' class="form-control" placeholder="End Period" ng-model="addDeductionModel.end_period" />
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar">
											</span>
										</span>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 0px;">
									<label><input type="checkbox" ng-click="toggleEpfDeductionAdd()" ng-checked="addDeductionModel.pay_epf == 'Y'">Pay EPF</label>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 0px;">
									<label><input type="checkbox" ng-click="toggleSocsoDeductionAdd()" ng-checked="addDeductionModel.pay_socso_eis == 'Y'">Pay SOCSO & EIS</label>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 0px;">
									<label><input type="checkbox" ng-click="toggleTaxDeductionAdd()" ng-checked="addDeductionModel.pay_tax == 'Y'">Pay Tax</label>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 0px;">
									<label><input type="checkbox" ng-click="toggleHrdfDeductionAdd()" ng-checked="addDeductionModel.pay_hrdf == 'Y'">Pay HRDF</label>
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" style="width: 80px;" ng-click="createDeduction(addDeductionForm.$valid)">Save</button>
				</div>
			</div>

		</div>
	</div>

	<!-- Edit Deduction Modal -->
	<div id="editDeduction" class="modal fade" role="dialog">
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header bg-primary">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Edit Deduction Details</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<form name="editDeductionForm">
							<div class="col-md-12">
								<div class="form-group">
									<label>Code <span class="text-danger">*</span></label>
									<input type="text" placeholder="Code" class="form-control" ng-model="editDeductionModel.code" required="">
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<label>Description</label>
									<input type="text" placeholder="Description" class="form-control" ng-model="editDeductionModel.description">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>Start Period</label>
									<div class='input-group date' id='datetimepicker7'>
										<input type='text' class="form-control" placeholder="Start Period" ng-model="editDeductionModel.start_period" />
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar">
											</span>
										</span>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label>End Period</label>
									<div class='input-group date' id='datetimepicker8'>
										<input type='text' class="form-control" placeholder="End Period" ng-model="editDeductionModel.end_period" />
										<span class="input-group-addon">
											<span class="glyphicon glyphicon-calendar">
											</span>
										</span>
									</div>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 0px;">
									<label><input type="checkbox" ng-click="toggleEpfDeductionEdit()" ng-checked="editDeductionModel.pay_epf == 'Y'">Pay EPF</label>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 0px;">
									<label><input type="checkbox" ng-click="toggleSocsoDeductionEdit()" ng-checked="editDeductionModel.pay_socso_eis == 'Y'">Pay SOCSO & EIS</label>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 0px;">
									<label><input type="checkbox" ng-click="toggleTaxDeductionEdit()" ng-checked="editDeductionModel.pay_tax == 'Y'">Pay Tax</label>
								</div>
							</div>
							<div class="col-md-6">
								<div class="checkbox" style="margin-top: 0px;">
									<label><input type="checkbox" ng-click="toggleHrdfDeductionEdit()" ng-checked="editDeductionModel.pay_hrdf == 'Y'">Pay HRDF</label>
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary" style="width: 80px;" ng-click="updateDeduction(editDeductionForm.$valid)">Update</button>
				</div>
			</div>

		</div>
	</div>

	<!-- Delete Deduction Modal -->
	<div id="deleteDeduction" class="modal fade" role="dialog">
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header bg-danger">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Delete Deduction Confirmation</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<p>Do you really want to delete this deduction?</p>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
					<button type="button" class="btn btn-danger" style="width: 80px;" ng-click="deleteDeductionConfirmed()">Delete</button>
				</div>
			</div>

		</div>
	</div>

</div>
<?php include(APPPATH . "views/payroll/footer.php"); ?>
<script type="text/javascript">
	var base_url = '<?php echo base_url(); ?>';
	$(document).ready(function(){
		$('.apply-select2').select2();
	});
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/payroll_dashboard.js?v=1.8"></script>