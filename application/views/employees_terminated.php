<div ng-app="myApp" ng-controller="empCtrl" ng-init="get_datalist_options()">
	<div class="page-wrapper">
		<div class="content container-fluid">
			<div class="row">
				<div class="col-xs-4">
					<h4 class="page-title">Terminated Employees</h4>
				</div>
				<div class="col-xs-8 text-right m-b-30">
					<a target="_blank" href="<?php echo base_url("employees/terminated_export?type=excel") ?>" class="btn btn-primary rounded">Excel</a>
					<a target="_blank" href="<?php echo base_url("employees/terminated_export") ?>" class="btn btn-primary rounded">PDF</a>
				</div>
			</div>
			<!-- <div class="row filter-row">
				<div class="col-sm-3 col-xs-6">  
					<div class="form-group form-focus">
						<label class="control-label">Employee ID</label>
						<input type="text" class="form-control floating" />
					</div>
				</div>
				<div class="col-sm-3 col-xs-6">  
					<div class="form-group form-focus">
						<label class="control-label">Employee Name</label>
						<input type="text" class="form-control floating" />
					</div>
				</div>
				<div class="col-sm-3 col-xs-6"> 
					<div class="form-group form-focus select-focus">
						<label class="control-label">Position</label>
						<select class="select floating"> 
							<option value="">Select Position</option>
							<option value="">Web Developer</option>
							<option value="1">Web Designer</option>
							<option value="1">Android Developer</option>
							<option value="1">Ios Developer</option>
						</select>
					</div>
				</div>
				<div class="col-sm-3 col-xs-6">  
					<a href="#" class="btn btn-success btn-block"> Search </a>  
				</div>     
			</div> -->
			<div class="row" ng-show="mainTable">
				<div class="col-md-12">
					<div class="table-responsive">
						<table id="datatable_emp" class="table table-striped custom-table datatable">
						<!-- <col width="50">
  						<col width="50"> -->
							<thead>
								<tr>
									<th>Name</th>
									<th>Employee ID</th>
									<th>Department</th>
									<th>Termination Type</th>
									<th>Termination Date</th>
									<th>Reason</th>
									<th>Notice Date</th>
									<!-- <th class="text-right">Action</th> -->
								</tr>
							</thead>
							<tbody>
								<?php foreach($employees as $emp) { ?>
									<tr>
										<td>
											<!-- <a href="<?php echo base_url(); ?>profile/index/<?php echo $emp->id; ?>" class="avatar"><?php echo strtoupper($emp->first_name[0]); ?></a> -->
											<h2><a style="color:#009ce7" href="<?php echo base_url(); ?>profile/index/<?php echo $emp->id; ?>"><b><?php echo $emp->first_name; ?></b><span><?php echo $emp->job_name; ?>-<?php echo $emp->id; ?></span>
<br/>
                                                        
<div style="min-width:150px !important">
	
	<!-- <a title="Clocking Data" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/manual_clocking_new?emp=<?php echo $emp->id ?>"><i style="font-size:15px" class="fa fa-hourglass-half"></i></a>
	
	<a title="Employee Report" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/employee_report/<?php echo $emp->id ?>"><i style="font-size:15px" class="fa fa-clock"></i></a>

	<a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>summary/view/<?php echo $emp->id ?>"><i style="font-size:15px" class="fa fa-address-card"></i></a> -->
	<a href="javascript:void(0)" class="btn btn-default btn-xs" data-toggle="modal" data-target="#edit_employee" ng-click="setEditData('<?php echo $emp->id; ?>')"><i style="font-size:15px" class="fa fa-pencil-square"></i></a>
	<?php if (is_page_permitted('view')) : ?>
	<a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>summary/view/<?php echo $emp->id ?>"><i style="font-size:15px" class="fa fa-address-card"></i></a>
	<?php endif ?>
	<!-- <a href="javascript:void(0)" class="btn btn-default btn-xs" data-toggle="modal" data-target="#delete_employee" ng-click="setDeleteID('<?php echo $emp->id; ?>')"><i style="font-size:15px" class="fa fa-trash"></i></a> -->


</div>
											</a></h2>
										</td>
										<td><?php echo $emp->special_id; ?></td>
										<td><?php echo $emp->department_name; ?></td>
										<td><?php echo $emp->termination_type; ?></td>
										<td data-sort="<?php echo $emp->termination_date_sort; ?>"><?php echo $emp->termination_date; ?></td>
										<td><?php echo $emp->termination_reason_text; ?></td>
										<td data-sort="<?php echo $emp->termination_notice_date_sort; ?>"><?php echo $emp->termination_notice_date; ?></td>
										<!-- <td class="text-right">
											<div class="dropdown">
												<a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
												<ul class="dropdown-menu pull-right">
													<li><a href="javascript:void(0)" data-toggle="modal" data-target="#edit_employee" ng-click="setEditData('<?php echo $emp->id; ?>')"><i class="fa fa-pencil m-r-5"></i> Edit</a></li>

													<?php if(get_user()["permissions_level"] == "Company" || $emp->permissions_level == "Personal"): ?>
													<li><a href="javascript:void(0)" data-toggle="modal" data-target="#delete_employee" ng-click="setDeleteID('<?php echo $emp->id; ?>')"><i class="fa fa-trash-o m-r-5"></i> Delete</a></li>
													<?php endif; ?>


												</ul>
											</div>
										</td> -->
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div id="filteredArea"></div>
		</div>
	</div>
	<div id="add_employee" class="modal custom-modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<div class="modal-content modal-lg">
				<!-- <div class="modal-header">
					<h4 class="modal-title">Add Employee</h4>
				</div> -->
				<div class="modal-body">
					<form class="m-b-30" name="emp_form" id="emp_form" ng-submit="onSubmit(emp_form.$valid)">
						<br /><h2>Basic Information</h2><br />
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Name <span class="text-danger">*</span></label>
									<input class="form-control" type="text" ng-model="addModel.first_name" required="">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Gender <span class="text-danger">*</span></label>
									<select class="select sex" ng-model="addModel.sex">
										<option value="Male">Male</option>
										<option value="Female">Female</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Employee ID <span class="text-danger">*</span></label>
									<input class="form-control" type="text" ng-model="addModel.special_id" required="">
								</div>
							</div>
							
							<div class="col-sm-6">  
								<div class="form-group">
									<label class="control-label">Outlet <span class="text-danger">*</span></label>
									<select class="select emptyselect" ng-model="addModel.branch_id" required="">
										<option value=''>Select Outlet</option>
										<?php foreach($branches as $br){ ?>
											<option value="<?php echo $br->id; ?>"><?php echo $br->name; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="col-sm-6">  
								<div class="form-group">
									<label class="control-label">Payroll Outlet</label>
									<select class="select emptyselect" ng-model="addModel.payroll_branch_id">
										<option value=''>Same as Outlet</option>
										<?php foreach($branches as $br){ ?>
											<option value="<?php echo $br->id; ?>"><?php echo $br->name; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Department <span class="text-danger">*</span></label>
									<select class="select emptyselect" ng-model="addModel.department_id" required="">
										<option value=''>Select Department</option>
										<?php foreach($departments as $dep){ ?>
											<option value="<?php echo $dep->id; ?>"><?php echo $dep->name; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group" id="add_designation">
									<label class="control-label">Position <span class="text-danger">*</span></label>
									<select class="select" ng-model="addModel.position_id" required="" >
										<option value=''>Select Position</option>
										<!-- <option ng-repeat="pos in positions" value="{{pos.id}}">{{pos.title}}</option> -->
										<?php foreach($positions as $pos){ ?>
											<option value="<?php echo $pos->id; ?>"><?php echo $pos->title; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="col-sm-6">  
								<div class="form-group">
									<label class="control-label">Role <span class="text-danger">*</span></label>
									<select class="select emptyselect" ng-model="addModel.role_id" required="">
										<option value=''>Select Role</option>
										<?php foreach($roles as $rol){ ?>
											<option value="<?php echo $rol->id; ?>"><?php echo $rol->job_name; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Marital Status</label>
									<select class="select marital"  ng-model="addModel.marital_status">
										<option value="single">Single</option>
										<option value="married">Married</option>
										<option value="married">Widowed</option>
										<option value="married">Separated</option>
										<option value="married">Divorced</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Date of Birth</label>
									<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="addModel.dob" id="dob"></div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Place of Birth</label>
									<input class="form-control" type="text" ng-model="addModel.pob">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Race</label>
									<input class="form-control" list="distinct-races" ng-model="addModel.race">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Religion</label>
									<input class="form-control" type="text" ng-model="addModel.religion">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Nationality</label>
									<input class="form-control" list="distinct-nationalities" ng-model="addModel.nationality">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">NIRC/Passport</label>
									<input class="form-control" type="text" ng-model="addModel.ic_passport">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">IC No.</label>
									<input class="form-control" type="text" ng-model="addModel.ic_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Old IC No.</label>
									<input class="form-control" type="text" ng-model="addModel.old_ic_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Email</label>
									<input class="form-control" type="email" ng-model="addModel.email">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Password</label>
									<input class="form-control" type="password" ng-model="addModel.password" autocomplete="new-password">
								</div>
							</div>
							
						</div>
						<br /><h2>Departmental Information</h2><br />
						<div class="row">
							
							
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">QR Barcode</label>
									<input class="form-control" type="text" ng-model="addModel.qr_barcode">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Job Grade</label>
									<input class="form-control" type="text"  ng-model="addModel.grade">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Employment Type</label>
									<select class="select emp_type"  ng-model="addModel.employment_type">
										<option value="full_time">Full Time</option>
										<option value="part_time">Part Time</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Employee Type</label>
									<select class="select emp_type"  ng-model="addModel.employee_type">
										<option value="m">Malaysian</option>
										<option value="n">Non Malaysian</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Hired On</label>
									<div class="cal-icon"><input class="form-control datetimepicker" type="text"  ng-model="addModel.hired_on" id="doj"></div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Basic Wage</label>
									<input class="form-control" type="text" ng-model="addModel.basic_wage">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">EPF</label>
									<input class="form-control" type="text" ng-model="addModel.epf_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">SOCSO</label>
									<input class="form-control" type="text" ng-model="addModel.socso">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">EIS</label>
									<input class="form-control" type="text" ng-model="addModel.eis">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<div class="checkbox">
										<label><input type="checkbox" value="" ng-model="addModel.is_ot"><b>Is OT</b></label>
									</div>
								</div>
							</div>
						</div>
						<br /><h2>Contact Information</h2><br />
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Temporary Address</label>
									<input class="form-control" type="text"  ng-model="addModel.temp_address">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Temporary City</label>
									<input class="form-control" type="text"  ng-model="addModel.temp_address_city">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Temporary Postcode</label>
									<input class="form-control" type="text"  ng-model="addModel.temp_address_postcode">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Temporary State</label>
									<input class="form-control" type="text"  ng-model="addModel.temp_address_state">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent Address</label>
									<input class="form-control" type="text"  ng-model="addModel.perm_address">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent City</label>
									<input class="form-control" type="text"  ng-model="addModel.perm_address_city">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent Postcode</label>
									<input class="form-control" type="text"  ng-model="addModel.perm_address_postcode">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent State</label>
									<input class="form-control" type="text"  ng-model="addModel.perm_address_state">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Telephone</label>
									<input class="form-control" type="text"  ng-model="addModel.telephone">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Mobile</label>
									<input class="form-control" type="text"  ng-model="addModel.mobile">
								</div>
							</div>
						</div>
						<br /><h2>Other Information</h2><br />
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Income Tax Number</label>
									<input class="form-control" type="text" ng-model="addModel.income_tax_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Income Tax Branch</label>
									<input class="form-control" type="text" ng-model="addModel.income_tax_branch">
								</div>
							</div>

							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Bank Name</label>
									<input class="form-control" type="text" ng-model="addModel.bank_name">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Bank Account Number</label>
									<input class="form-control" type="text" ng-model="addModel.bank_account_no">
								</div>
							</div>

							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">License Class</label>
									<input class="form-control" type="text" ng-model="addModel.license_class">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">License Number</label>
									<input class="form-control" type="text" ng-model="addModel.license_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">License Expiry</label>
									<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="addModel.license_expiry" id="expiry"></div>
								</div>
							</div>

						</div>
						<br /><h2>Leaves</h2><br />
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Compassionate Leaves</label>
									<input class="form-control" type="number" ng-model="addModel.compassionate_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Paternity Leaves</label>
									<input class="form-control" type="number" ng-model="addModel.paternity_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Marriage Leaves</label>
									<input class="form-control" type="number" ng-model="addModel.marriage_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Hospitalisation Leaves</label>
									<input class="form-control" type="number" ng-model="addModel.hospitalisation_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Study Leaves</label>
									<input class="form-control" type="number" ng-model="addModel.study_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Replacement Leaves</label>
									<input class="form-control" type="number" ng-model="addModel.replacement_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Unpaid Leaves</label>
									<input class="form-control" type="number" ng-model="addModel.unpaid_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Emergency Leaves</label>
									<input class="form-control" type="number" ng-model="addModel.emergency_leaves">
								</div>
							</div>
						</div>
						<div class="m-t-20 text-center">
							<button class="btn btn-primary" type="submit">Create Employee</button>
						</div>
						<datalist id="distinct-races">
							<option ng-repeat="race in distinct_races" value="{{race}}">
						</datalist>
						<datalist id="distinct-nationalities">
							<option ng-repeat="nationality in distinct_nationalities" value="{{nationality}}">
						</datalist>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div id="edit_employee" class="modal custom-modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<div class="modal-content modal-lg">
				<div class="modal-header">
					<h4 class="modal-title">Edit Employee</h4>
				</div>
				<div class="modal-body">
					<form class="m-b-30" name="emp_edit_form" id="emp_edit_form" ng-submit="onSubmit2(emp_edit_form.$valid)">
						<br /><h2>General Information</h2><br />
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Name <span class="text-danger">*</span></label>
									<input class="form-control" type="text" ng-model="editModel.first_name" required="">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Gender <span class="text-danger">*</span></label>
									<select class="select" ng-model="editModel.sex">
										<option value="Male">Male</option>
										<option value="Female">Female</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Employee ID <span class="text-danger">*</span></label>
									<input class="form-control" type="text" ng-model="editModel.special_id" required="">
								</div>
							</div>
							<div class="col-sm-6">  
								<div class="form-group">
									<label class="control-label">Outlet <span class="text-danger">*</span></label>
									<select class="select" ng-model="editModel.branch_id" required="">
										<option value=''>Select Outlet</option>
										<option ng-repeat="branch in branches" value="{{branch.id}}">{{branch.name}}</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">  
								<div class="form-group">
									<label class="control-label">Payroll Outlet</label>
									<select class="select" ng-model="editModel.payroll_branch_id">
										<option value=''>Same as Outlet</option>
										<option ng-repeat="branch in branches" value="{{branch.id}}">{{branch.name}}</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Department <span class="text-danger">*</span></label>
									<select class="select" ng-model="editModel.department_id" required="">
										<option value=''>Select Department</option>
										<option ng-repeat="dep in departments" value="{{dep.id}}">{{dep.name}}</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group" id="edit_designation">
									<label class="control-label">Position <span class="text-danger">*</span></label>
									<select class="select" ng-model="editModel.position_id" required="">
										<option value=''>Select Position</option>
										<option ng-repeat="pos in editPositions" value="{{pos.id}}">{{pos.title}}</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">  
								<div class="form-group">
									<label class="control-label">Role <span class="text-danger">*</span></label>
									<select class="select" ng-model="editModel.role_id" required="">
										<option value=''>Select Role</option>
										<option ng-repeat="role in roles" value="{{role.id}}">{{role.job_name}}</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Marital Status</label>
									<select class="select"  ng-model="editModel.marital_status">
										<option value="single">Single</option>
										<option value="married">Married</option>
										<option value="married">Widowed</option>
										<option value="married">Separated</option>
										<option value="married">Divorced</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Date of Birth</label>
									<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editModel.dob" id="dob_edit"></div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Place of Birth</label>
									<input class="form-control" type="text" ng-model="editModel.pob">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Race</label>
									<input class="form-control" list="distinct-races" ng-model="editModel.race">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Religion</label>
									<input class="form-control" type="text" ng-model="editModel.religion">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Nationality</label>
									<input class="form-control" list="distinct-nationalities" ng-model="editModel.nationality">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">NIRC/Passport</label>
									<input class="form-control" type="text" ng-model="editModel.ic_passport">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">IC No.</label>
									<input class="form-control" type="text" ng-model="editModel.ic_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Old IC No.</label>
									<input class="form-control" type="text" ng-model="editModel.old_ic_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Email</label>
									<input class="form-control" type="email" ng-model="editModel.email" >
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Password</label>
									<input class="form-control" type="password" ng-model="editModel.new_password" autocomplete="new-password">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Employee Status</label>
									<select class="select"  ng-model="editModel.employee_status">
										<option value="active">Active</option>
										<option value="terminated">Terminated</option>
										<option value="resigned">Resigned</option>
									</select>
								</div>
							</div>
							<div id="terminated-fields" ng-show="editModel.employee_status == 'terminated'">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Termination Type</label>
										<input class="form-control" type="text" ng-model="editModel.termination_type">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Termination Date</label>
										<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editModel.termination_date" id="termination_date"></div>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Termination Reason</label>
										<select class="select emptyselect"  ng-model="editModel.termination_reason">
										<option value=''>Select Reason</option>
											<option value="{{r.id}}" ng-repeat="r in reasons">{{r.reason}}</option>
									</select>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Notice Date</label>
										<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editModel.termination_notice_date" id="termination_notice_date"></div>
									</div>
								</div>
							</div>
							<div id="resigned-fields" ng-show="editModel.employee_status == 'resigned'">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Resignation Type</label>
										<input class="form-control" type="text" ng-model="editModel.resignation_type">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Resignation Date</label>
										<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editModel.resignation_date" id="resignation_date"></div>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Resignation Reason</label>
										<input class="form-control" type="text" ng-model="editModel.resignation_reason">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Notice Date</label>
										<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editModel.resignation_notice_date" id="resignation_notice_date"></div>
									</div>
								</div>
							</div>
						</div>
						<br /><h2>Departmental Information</h2><br />
						<div class="row">
							
							
							
							
							
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">QR Barcode</label>
									<input class="form-control" type="text" ng-model="editModel.qr_barcode">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Job Grade</label>
									<input class="form-control" type="text"  ng-model="editModel.grade">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Employment Type</label>
									<select class="select"  ng-model="editModel.employment_type">
										<option value="full_time">Full Time</option>
										<option value="part_time">Part Time</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Employee Type</label>
									<select class="select emp_type"  ng-model="editModel.employee_type">
										<option value="m">Malaysian</option>
										<option value="n">Non Malaysian</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Hired On</label>
									<div class="cal-icon"><input class="form-control datetimepicker" type="text"  ng-model="editModel.hired_on" id="doj_edit"></div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Basic Wage</label>
									<input class="form-control" type="text" ng-model="editModel.basic_wage">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">EPF</label>
									<input class="form-control" type="text" ng-model="editModel.epf_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">SOCSO</label>
									<input class="form-control" type="text" ng-model="editModel.socso">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">EIS</label>
									<input class="form-control" type="text" ng-model="editModel.eis">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<div class="checkbox">
										<label><input type="checkbox" value="" ng-model="editModel.is_ot"><b>Is OT</b></label>
									</div>
								</div>
							</div>
						</div>
						<br /><h2>Contact Information</h2><br />
						<div class="row">
							
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Temporary Address</label>
									<input class="form-control" type="text"  ng-model="editModel.temp_address">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Temporary City</label>
									<input class="form-control" type="text"  ng-model="editModel.temp_address_city">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Temporary Postcode</label>
									<input class="form-control" type="text"  ng-model="editModel.temp_address_postcode">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Temporary State</label>
									<input class="form-control" type="text"  ng-model="editModel.temp_address_state">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent Address</label>
									<input class="form-control" type="text"  ng-model="editModel.perm_address">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent City</label>
									<input class="form-control" type="text"  ng-model="editModel.perm_address_city">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent Postcode</label>
									<input class="form-control" type="text"  ng-model="editModel.perm_address_postcode">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent State</label>
									<input class="form-control" type="text"  ng-model="editModel.perm_address_state">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Telephone</label>
									<input class="form-control" type="text"  ng-model="editModel.telephone">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Mobile</label>
									<input class="form-control" type="text"  ng-model="editModel.mobile">
								</div>
							</div>
						</div>
						<br /><h2>Other Information</h2><br />
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Income Tax Number</label>
									<input class="form-control" type="text" ng-model="editModel.income_tax_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Income Tax Branch</label>
									<input class="form-control" type="text" ng-model="editModel.income_tax_branch">
								</div>
							</div>

							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Bank Name</label>
									<input class="form-control" type="text" ng-model="editModel.bank_name">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Bank Account Number</label>
									<input class="form-control" type="text" ng-model="editModel.bank_account_no">
								</div>
							</div>

							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">License Class</label>
									<input class="form-control" type="text" ng-model="editModel.license_class">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">License Number</label>
									<input class="form-control" type="text" ng-model="editModel.license_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">License Expiry</label>									
									<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editModel.license_expiry" id="expiry_edit"></div>
								</div>
							</div>

						</div>
						<br /><h2>Leaves</h2><br />
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Compassionate Leaves</label>
									<input class="form-control" type="number" ng-model="editModel.compassionate_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Paternity Leaves</label>
									<input class="form-control" type="number" ng-model="editModel.paternity_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Marriage Leaves</label>
									<input class="form-control" type="number" ng-model="editModel.marriage_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Hospitalisation Leaves</label>
									<input class="form-control" type="number" ng-model="editModel.hospitalisation_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Study Leaves</label>
									<input class="form-control" type="number" ng-model="editModel.study_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Replacement Leaves</label>
									<input class="form-control" type="number" ng-model="editModel.replacement_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Unpaid Leaves</label>
									<input class="form-control" type="number" ng-model="editModel.unpaid_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Emergency Leaves</label>
									<input class="form-control" type="number" ng-model="editModel.emergency_leaves">
								</div>
							</div>
						</div>
						<div class="m-t-20 text-center">
							<button class="btn btn-primary" type="submit">Save Changes</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div id="delete_employee" class="modal custom-modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog">
			<div class="modal-content modal-md">
				<div class="modal-header">
					<h4 class="modal-title">Delete Employee</h4>
				</div>
				<form>
					<div class="modal-body card-box">
						<p>Are you sure you want to delete this?</p>
						<div class="m-t-20"> <a href="#" class="btn btn-default" data-dismiss="modal">Close</a>
							<button type="submit" class="btn btn-danger" ng-click="delete_employee()">Delete</button>
						</div>
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
	app.controller('empCtrl', function($scope,$http,$compile) {
		$scope.mainTable = true;
		$scope.filtered = '';
		$scope.editModel = {};
		$scope.distinct_races = [];
		$scope.distinct_nationalities = [];
		$scope.addModel = {first_name : '',
		sex : 'Male',
		dob : '',
		pob : '',
		race : '',
		religion : '',
		nationality : '',
		email : '',
		ic_no : '',
		old_ic_no : '',
		password : '',
		branch_id : '',
		payroll_branch_id : '',
		department_id : '',
		role_id : '',
		position_id : '',
		special_id : '',
		grade : '',
		employment_type : 'full_time',
		hired_on : '',
		ic_passport : '',
		perm_address : '',
		perm_address_city : '',
		perm_address_state : '',
		perm_address_postcode : '',
		temp_address : '',
		temp_address_city : '',
		temp_address_state : '',
		temp_address_postcode : '',
		telephone : '',
		mobile : '',
		marital_status : 'single',
		basic_wage : '',
		epf_no : '',
		socso : '',
		eis : '',
		income_tax_no : '',
		income_tax_branch : '',
		qr_barcode : '',
		bank_name : '',
		bank_account_no : '',
		license_class : '',
		license_no : '',
		license_expiry : '',
		is_ot : false,
		employee_type : 'm',
		compassionate_leaves : 0,
		paternity_leaves : 0,
		marriage_leaves : 0,
		hospitalisation_leaves : 0,
		study_leaves : 0,
		replacement_leaves : 0,
		unpaid_leaves : 0,
		emergency_leaves : 0
	}
	$scope.getPositions = function(){
		$("#add_designation").LoadingOverlay("show",{maxSize:50});
		$http.post('<?php echo base_url(); ?>' + 'employees/getPositions', {department_id : $scope.addModel.department_id}, config).then(function (response) {
			$scope.positions = response.data.positions;
			$scope.addModel.position_id = ''
			$("#add_designation").LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}
	$scope.getDeductions = function(){
		$("body").LoadingOverlay("show",{maxSize:50});
		$http.post('<?php echo base_url(); ?>' + 'employees/getDeductions', {employee_type : $scope.addModel.employee_type, basic_wage : $scope.addModel.basic_wage}, config).then(function (response) {
			$scope.addModel.epf_no = response.data.epf;
			$scope.addModel.eis = response.data.eis;
			$scope.addModel.socso = response.data.socso;
			$("body").LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}
	$scope.getDeductionsEdit = function(){
		$("body").LoadingOverlay("show",{maxSize:50});
		$http.post('<?php echo base_url(); ?>' + 'employees/getDeductions', {employee_type : $scope.editModel.employee_type, basic_wage : $scope.editModel.basic_wage}, config).then(function (response) {
			$scope.editModel.epf_no = response.data.epf;
			$scope.editModel.eis = response.data.eis;
			$scope.editModel.socso = response.data.socso;
			$("body").LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}
	$scope.getEditPositions = function(){
		$("#edit_designation").LoadingOverlay("show",{maxSize:50});
		$http.post('<?php echo base_url(); ?>' + 'employees/getPositions', {department_id : $scope.editModel.department_id}, config).then(function (response) {
			$scope.editPositions = response.data.positions;
			$scope.editModel.position_id = ''
			$("#edit_designation").LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}
	$scope.getEmployees = function(){


		setTimeout(function(){ 
			location.reload(); 

		}, 1000);
		

		// $('body').LoadingOverlay("show",{maxSize:50});
		// $http.post('<?php echo base_url(); ?>' + 'employees/getEmployees', {department_id : $scope.addModel.department_id}, config).then(function (response) {
		// 	var generated = $('#filteredArea').html(response.data);
		// 	$compile(generated.contents())($scope);
		// 	$('body').LoadingOverlay("hide");
		// }, function (error) {
		// 	console.log(error.data);
		// });
		
	}
	$scope.setDeleteID = function(id){
		$scope.delete_id = id;
	}
	$scope.onSubmit2 = function(valid){
		if(!valid){
			var req = false;
			var error = $scope.emp_edit_form.$error;
			angular.forEach(error.required, function(field){
				if(field.$invalid){
					req = true;
				}
			});
			if(req){
				showNotification("Error","Please fill all the required fields!","error");
			}else{
				showNotification("Error","Email format is not correct!","error");
			}
		}else{
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.editModel.dob = $('#dob_edit').val();
			$scope.editModel.hired_on = $('#doj_edit').val();
			$scope.editModel.license_expiry = $('#expiry_edit').val();
			$scope.editModel.termination_date = $('#termination_date').val();
			$scope.editModel.termination_notice_date = $('#termination_notice_date').val();
			$scope.editModel.resignation_date = $('#resignation_date').val();
			$scope.editModel.resignation_notice_date = $('#resignation_notice_date').val();
			$http.post('<?php echo base_url(); ?>' + 'employees/update', $scope.editModel, config).then(function (response) {
				if(response.data.success){

					$scope.getEmployees();
					$scope.mainTable = false;
					$scope.editModel = {};
					$('#edit_employee').modal('toggle');

					showNotification("Success",'Employee updated successfully!',"success");
					$('body').LoadingOverlay("hide");
				}else{
					showNotification("Error",response.data.msg,"error");
					$('body').LoadingOverlay("hide");
				}

			}, function (error) {
				console.log(error.data);
			});
		}
	}
	$scope.setEditData = function(id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post('<?php echo base_url(); ?>' + 'employees/getSingleEmployee', {id : id}, config).then(function (response) {
			$scope.editModel = response.data.employee;
			$scope.editModel.compassionate_leaves = parseInt($scope.editModel.compassionate_leaves);
			$scope.editModel.paternity_leaves = parseInt($scope.editModel.paternity_leaves);
			$scope.editModel.marriage_leaves = parseInt($scope.editModel.marriage_leaves);
			$scope.editModel.hospitalisation_leaves = parseInt($scope.editModel.hospitalisation_leaves);
			$scope.editModel.study_leaves = parseInt($scope.editModel.study_leaves);
			$scope.editModel.replacement_leaves = parseInt($scope.editModel.replacement_leaves);
			$scope.editModel.unpaid_leaves = parseInt($scope.editModel.unpaid_leaves);
			$scope.editModel.emergency_leaves = parseInt($scope.editModel.emergency_leaves);
			$scope.branches = response.data.branches;
			$scope.departments = response.data.departments;
			$scope.roles = response.data.roles;
			$scope.editPositions = response.data.positions;
			$scope.reasons = response.data.reasons;
			$scope.editModel.current_emp_status = response.data.employee.employee_status;
			$scope.editModel.current_branch_id = response.data.employee.branch_id;
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}
	$scope.delete_employee = function(){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post('<?php echo base_url(); ?>' + 'employees/delete_employee', {id : $scope.delete_id}, config).then(function (response) {
			$scope.mainTable = false;
			$scope.getEmployees();
			$('#delete_employee').modal('toggle');
			showNotification("Success",'Employee deleted successfully!',"success");
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}
	
	$scope.get_datalist_options = function()
	{
		$http.post("<?php echo base_url(); ?>" + "employees/get_datalist_options", {}, config)
		.then(function(response) {
			$scope.distinct_races = response.data.distinct_races;
			$scope.distinct_nationalities = response.data.distinct_nationalities;
		}).catch(function(error) {
			console.log(error);
		})
	}

	$scope.onSubmit = function(valid){
		if(!valid){
			var req = false;
			var error = $scope.emp_form.$error;
			angular.forEach(error.required, function(field){
				if(field.$invalid){
					req = true;
				}
			});
			if(req){
				alert("Please fill all the required fields!");
			}else{
				alert("Email format is not correct!");
			}
		}
		if(valid) {
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.addModel.dob = $('#dob').val();
			$scope.addModel.hired_on = $('#doj').val();
			$scope.addModel.license_expiry = $('#expiry').val();
			$http.post('<?php echo base_url(); ?>' + 'employees/save', $scope.addModel, config).then(function (response) {
				if(response.data.success){
					$scope.getEmployees();
					$scope.mainTable = false;
					$scope.addModel = {first_name : '',
					sex : 'Male',
					dob : '',
					pob : '',
					race : '',
					religion : '',
					nationality : '',
					email : '',
					ic_no : '',
					old_ic_no : '',
					password : '',
					branch_id : '',
					payroll_branch_id : '',
					department_id : '',
					role_id : '',
					position_id : '',
					special_id : '',
					grade : '',
					employment_type : 'full_time',
					hired_on : '',
					ic_passport : '',
					perm_address : '',
					perm_address_city : '',
					perm_address_state : '',
					perm_address_postcode : '',
					temp_address : '',
					temp_address_city : '',
					temp_address_state : '',
					temp_address_postcode : '',
					telephone : '',
					mobile : '',
					marital_status : 'single',
					basic_wage : '',
					epf_no : '',
					socso : '',
					eis : '',
					income_tax_no : '',
					income_tax_branch : '',
					qr_barcode : '',
					bank_name : '',
					bank_account_no : '',
					license_class : '',
					license_no : '',
					license_expiry : '',
					is_ot : false,
					employee_type : 'm',
					compassionate_leaves : 0,
					paternity_leaves : 0,
					marriage_leaves : 0,
					hospitalisation_leaves : 0,
					study_leaves : 0,
					replacement_leaves : 0,
					unpaid_leaves : 0,
					emergency_leaves : 0

				}
				$scope.positions = [];
				$('.emptyselect').val('').trigger('change.select2');
				$('.sex').val('Male').trigger('change.select2');
				$('.marital').val('single').trigger('change.select2');
				$('.emp_type').val('full_time').trigger('change.select2');
				$('#add_employee').modal('toggle');
				showNotification("Success",response.data.msg,"success");
				$('body').LoadingOverlay("hide");
			}else{
				showNotification("Error",response.data.msg,"error");
				$('body').LoadingOverlay("hide");
			}

		}, function (error) {
			console.log(error.data);
		});
		}
	}
});
</script>