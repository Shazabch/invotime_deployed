<div class="page-wrapper" ng-app="myApp" ng-controller="summaryCtrl">
	<style type="text/css">
		.strike{
			text-decoration: line-through;
		}
		.btn.disabled, .btn[disabled], fieldset[disabled] .btn{
			opacity: 0.3
		}
	</style>
	<div id="settingsModal" class="modal fade" role="dialog">
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content" id="settingsBox">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title" ng-show="settings.name">{{settings.name}} ({{settings.special_id}})</h4>
				</div>
				<div class="modal-body" id="inputbox" ng-show="settings.name">
					<div class="row">
						<div class="col-md-12">
							<h5>Shift Assignment - {{settings.date_s}}</h5>
							<form class="form-inline">
								<div class="form-group">
									<select class="form-control" ng-model="selected_shift">
										<option value="">Select a shift</option>
										<option ng-repeat="s in settings.shifts" value="{{s.id}}">{{s.combined_name}}</option>
									</select>
									<button class="btn btn-success" ng-show="selected_shift != prev_shift && selected_shift != ''" ng-click="update_shift()">Update</button>
									<button class="btn btn-danger" ng-show="selected_shift != '' && prev_shift != ''" ng-click="delete_shift()">Delete</button>
								</div>
							</form>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-md-12">
							<button class="btn btn-primary" ng-click="refresh_shift()"><i class="fa fa-refresh"></i> Refresh Clockings Shift</button>	
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-md-6">
							<span class="pull-left">
								Late In
								<h5 ng-class="{strike: !settings.is_late}">{{settings.late_hours}}</h5>
							</span>
							<div class="btn-group btn-group-xs pull-right">
								<button type="button" class="btn btn-success btn_check" ng-click="change_status('late_hours', true)" ng-disabled="settings.is_late">
									<span class="fa fa-check"></span>
								</button>
								<button type="button" class="btn btn-danger btn_close" ng-click="change_status('late_hours', false)" ng-disabled="!settings.is_late">
									<span class="fa fa-close"></span>
								</button>
							</div>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="col-md-6">
							<span class="pull-left">
								Late (Break)
								<h5 ng-class="{strike: !settings.is_late_break}">{{settings.break_late_hours}}</h5>
							</span>
							<div class="btn-group btn-group-xs pull-right">
								<button type="button" class="btn btn-success btn_check" ng-click="change_status('break_late_hours', true)" ng-disabled="settings.is_late_break">
									<span class="fa fa-check"></span>
								</button>
								<button type="button" class="btn btn-danger btn_close" ng-click="change_status('break_late_hours', false)" ng-disabled="!settings.is_late_break">
									<span class="fa fa-close"></span>
								</button>
							</div>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="col-md-6">
							<span class="pull-left">
								Early Out
								<h5 ng-class="{strike: !settings.is_early_out}">{{settings.early_out}}</h5>
							</span>
							<div class="btn-group btn-group-xs pull-right">
								<button type="button" class="btn btn-success btn_check" ng-click="change_status('early_out', true)" ng-disabled="settings.is_early_out">
									<span class="fa fa-check"></span>
								</button>
								<button type="button" class="btn btn-danger btn_close" ng-click="change_status('early_out', false)" ng-disabled="!settings.is_early_out">
									<span class="fa fa-close"></span>
								</button>
							</div>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="col-md-6">
							<span class="pull-left">
								Overtime
								<h5 ng-class="{strike: !settings.is_ot}">{{settings.overtime}}</h5>
							</span>
							<div class="btn-group btn-group-xs pull-right">
								<button type="button" class="btn btn-success btn_check" ng-click="change_status('overtime', true)" ng-disabled="settings.is_ot">
									<span class="fa fa-check"></span>
								</button>
								<button type="button" class="btn btn-danger btn_close" ng-click="change_status('overtime', false)" ng-disabled="!settings.is_ot">
									<span class="fa fa-close"></span>
								</button>
							</div>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="col-md-12">
							<h5>Replacement Leave Date</h5>
							<form class="form-inline">
								<div class="form-group">
									<input class="form-control datetimepicker" type="text" required="" name="from" autocomplete="off" spellcheck="false" data-ms-editor="true" id="replacement-date">
									<button class="btn btn-success" ng-click="update_replacement_date()">Update</button>
									<button class="btn btn-danger" ng-click="delete_replacement_leave()">Remove</button>
								</div>
							</form>
						</div>
					</div>
					<hr>
					<div class="row">
						<div class="col-md-6">
							<span class="pull-left">
								Replacement for PH
							</span>
							<div class="pull-right">
								<input type="checkbox" ng-model="settings.is_replaced_ph" ng-change="update_replacement_ph()">
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>

		</div>
	</div>
	<div id="editClockingXCRUD" class="modal fade" role="dialog">
		<div class="modal-dialog">

			<!-- Modal content-->
			<div class="modal-content" id="modalBox">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Edit Clockings</h4>
				</div>
				<div class="modal-body" id="inputboxClockings">
					<div class="row">
						<div class="col-md-12">
							<div ng-show="showClockings">
								<button class="btn btn-primary" ng-click="addClocking()"><i class="fa fa-plus-circle"></i> Add</button>
							</div>
							<div ng-show="!showClockings">
								<button class="btn btn-primary" ng-click="saveClocking()"><i class="fa fa-save"></i> <span ng-show="!clockingId">Save</span><span ng-show="clockingId">Update</span></button>
								<button class="btn btn-warning" ng-click="cancelClocking()"><i class="fa fa-close"></i> Cancel</button>
							</div>
							<br />
							<div ng-show="!showClockings">
								<form class="form-horizontal">
									<div class="form-group">
										<label class="control-label col-sm-2">Type</label>
										<div class="col-sm-10">
											<select class="form-control" ng-model="clockingType">
												<option value="in">In</option>
												<option value="out">Out</option>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="control-label col-sm-2">Time</label>
										<div class="col-sm-10">
											<input class="form-control datetimepicker7" type="text" ng-model="clockingTime" id="clockingTimeField">
										</div>
									</div>
								</form>
							</div>
						</div>
						<div class="col-md-12 table-responsive" ng-show="showClockings && !loading">
							<table class="table table-bordered table-striped">
								<thead>
									<tr class="success">
										<th># </th>
										<th>Type</th>
										<th>Time</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
									<tr ng-repeat="clock in clockings">
										<td>{{ $index + 1 }}</td>
										<td class="text-capitalize">{{ clock.type }}</td>
										<td>{{ clock.time }}</td>
										<td>
											<button class="btn btn-sm btn-warning" ng-click="editClocking(clock.id, clock.type, clock.time)"><i class="fa fa-edit"></i></button>
											<button class="btn btn-sm btn-danger" ng-click="deleteClocking(clock.id)"><i class="fa fa-trash"></i></button>
										</td>
									</tr>
									<tr ng-if="clockings.length == 0">
										<td colspan="4" class="text-center">No clockings found</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div> 
					<div class="row">
						<div class="col-md-12">
							<div id="xcrudBox"></div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>

		</div>
	</div>
	<div class="content container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card-box">
					<div class="row">
						<div class="col-md-12">
							<h3>Employee Summary</h3>
						</div>
						<div class="col-md-4">
							<div style="width:300px" class="form-group">
								<label>Department</label>
								<select class="form-control apply-select3" id="dep" name="dep">
									<?php foreach ($departments as $dep): ?>
										<option <?php echo ($dep->id == $selected_department) ? 'selected' : '' ?> value="<?php echo $dep->id ?>"><?php echo $dep->name; ?></option>
									<?php endforeach; ?>

								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div style="width:300px" class="form-group">
								<label>Employee</label>
								<select class="form-control apply-select2" id="emp" name="emp">
									<?php foreach ($employees_dropdown as $emp): ?>
										<option <?php echo ($emp->id == $employee->emp_id) ? 'selected' : '' ?> value="<?php echo $emp->id ?>"><?php echo $emp->special_id . " - " . $emp->first_name ?></option>
									<?php endforeach; ?>

								</select>
							</div>
						</div>
						<div class="col-md-12">

							<button class="btn btn-primary" onclick="window.print()">Print</button>

							<a class="btn btn-primary" target="_blank" href="<?php echo base_url() ?>summary/pdf/<?php echo $employee->emp_id ?>/<?php echo $from_p ?>/<?php echo $to_p ?>">Export as PDF</a>
							<a class="btn btn-primary" target="_blank" href="<?php echo base_url() ?>exports/excel/<?php echo $employee->emp_id ?>/<?php echo $from_p ?>/<?php echo $to_p ?>">Export as Excel</a>
							<?php if (is_page_permitted('manual_clocking_new')) : ?>
							<a class="btn btn-primary" href="<?php echo base_url() ?>overview/manual_clocking_new?month=<?php echo date("m") ?>&emp=<?php echo $employee->emp_id ?>">Clocking Data</a>
							<?php endif ?>
							<?php if (is_page_permitted('shifts_assignment')) : ?>
							<a class="btn btn-primary" href="<?php echo base_url() ?>overview/shifts_assignment?emp=<?php echo $employee->emp_id ?>&month=<?php echo date('m', strtotime($to_p)) ?>&year=<?php echo date('Y', strtotime($to_p)) ?>">Shift Assignment</a>
							<?php endif ?>
							<?php if ($is_alternate_clockings) : ?>
							<a class="btn btn-primary" href="<?php echo base_url() ?>summary/fix_clockings/<?php echo $employee->emp_id ?>/?from=<?php echo urlencode($from_f) ?>&to=<?php echo urlencode($to_f) ?>">Fix Clockings</a>
							<a class="btn btn-primary" href="<?php echo base_url() ?>summary/reset_clockings/<?php echo $employee->emp_id ?>/?from=<?php echo urlencode($from_f) ?>&to=<?php echo urlencode($to_f) ?>">Reset Clockings</a>
							<?php endif; ?>

							
							<p class="show-on-print" style="margin:0px;display:none;font-weight: bold"><?php echo $from_f ?> to <?php echo $to_f ?> - Printed by <?php echo get_user()["first_name"] ?></p>
						</div>
						
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="card-box">
					<div class="row">
						<div class="col-md-12">

							<ul class="personal-info">
								<li>
									<span class="title">Name:</span>
									<span class="text"><b><?php echo $employee->first_name; ?></b></span>
								</li>
								<li>
									<span class="title">Employee ID:</span>
									<span class="text"><?php echo $employee->special_id; ?></span>
								</li>
								<li>
									<span class="title">Department:</span>
									<span class="text"><?php echo $employee->department; ?></span>
								</li>
								<li>
									<span class="title">Position:</span>
									<span class="text"><?php echo $employee->position; ?></span>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6 hide-on-print">
				<div class="card-box">
					<div class="row">
						<form method="get" action="<?php echo base_url();?>summary/view/<?php echo $emp_id;?>/<?php echo $selected_department; ?>">
							<div class="col-md-6">
								<div class="form-group">
									<label class="control-label">From<span class="text-danger">*</span></label>
									<input class="form-control datetimepicker" type="text" id="from" required="" name="from" autocomplete="off">
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label class="control-label">To<span class="text-danger">*</span></label>
									<input class="form-control datetimepicker" type="text" id="to" required="" name="to" autocomplete="off">
								</div>
							</div>
							<div class="col-md-3">
								<button class="btn btn-primary" type="submit">Filter</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>

		<style>
			.holiday{
				color: red;
			}

			.dark-row{
				background-color: #f9f9f9;
			}

			body {
				-webkit-print-color-adjust: exact !important;
			}

			@media print {

				.dark-row{
					background-color: grey !important;
				}

				.hide-on-print{
					display:none;
				}

				.show-on-print{
					display:inline !important;
				}


				.header, .sidebar, .btn{
					display:none;
				}
				.page-wrapper{
					margin-left: 0px;
					padding-top: 0px;
				}


				body {
					margin: 0;
					padding: 0 !important;
					min-width: 900px;
				}
				.container {
					width: auto;
					min-width: 900px;
				}

				-webkit-print-color-adjust: exact !important;

			}



		</style>
		

		<div class="card-box">
			<div class="row">
				<div class="col-md-12">
					<div class="table-responsive freeze-table">
						<table class="table table-bordered table-stripedx">
							<thead>
								<tr>
									<th style="min-width: 70px;">Date</th>
									<th>Shift</th>
									<th>Clock in</th>
									<th>Clock out</th>
									<?php if ($custom_in_outs): ?>
										<th>Clock In</th>
										<th>Clock Out</th>
									<?php endif; ?>
									<!-- <th>Hours</th> -->
									<th>Total Hours</th>
									<th>Work Hours</th>
									<th>Break Hours</th>
									<th>Late In</th>
									<th>Late (Break)</th>
									<th>Early Out</th>
									<!-- <th>Short Hours</th> -->
									<th>OT</th>
									<th>OT(M)</th>
									<th>OT<br>(PHx2)</th>
									<th>OT<br>(PHx3)</th>
									<th>OT<br>(RD)</th>
									<th>OT<br>(OFF)</th>
									<th>Days</th>
									<?php if (!$custom_in_outs): ?>
										<th style="min-width: 70px;">Trip(A)</th>
										<th style="min-width: 70px;">Trip(B)</th>
										<th>Late Reason</th>
									<?php endif; ?>
									<th>Remark</th>
									
								</tr>
							</thead>
							<tbody>
								<?php foreach($dates as $d){ ?>
									<?php foreach($d->clockings as $key => $clock){ ?>
										<tr>
											<?php if($key == 0){ ?>
												<td rowspan="<?php echo count($d->clockings);?>" class="text-center <?php if(in_array($d->date,$public_holidays) || $d->is_replaced_ph){echo 'holiday';} ?>" style="vertical-align: middle"><b <?php if (in_array($d->date, $public_holidays)){echo "data-toggle='tooltip' data-html='true' data-placement='top' data-original-title='".$public_holidays_names[array_search($d->date,$public_holidays)]."'";} ?>><?php echo $clock->day_f; ?></b><br>
                                        
											<?php if($is_emp_summary_editable === TRUE) : ?>
													<button class="btn btn-xs btn-info" data-toggle="modal" data-target="#editClockingXCRUD" id="editClockingBtn" ng-click="getClockings(<?php echo $employee->emp_id;?>, '<?php echo $d->date;?>', <?php echo $d->overnight; ?>, '<?php echo $d->cut_off_time; ?>')" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>" data-overnight="<?php echo $d->overnight; ?>" data-shift="<?php echo $d->is_shift; ?>"><i class="fa fa-edit"></i></button>
													<button class="btn btn-xs btn-primary" data-toggle="modal" data-target="#settingsModal" ng-click="getSettings(<?php echo $employee->emp_id;?>, '<?php echo $d->date;?>')"><i class="fa fa-gear"></i></button>
											<?php endif; ?>
										
												</td>
											<?php } ?>
											<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>" style="vertical-align: middle"><?php if($key%2 != 1){ echo $clock->name;}else{
												echo "Break";
											} ?>  </td>
											<?php if ($custom_in_outs) : ?>
												<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>" style="vertical-align: middle">
													<?php $clock_in_check = admin_add_edit_check_clock_in(isset($d->in_outs_id[0]) ? $d->in_outs_id[0] : null); ?>
													<?php if ($clock_in_check == 1) { ?>
														<b class="text-danger"><?= $d->in_outs[0] ?? '' ?></b>
													<?php }else{ ?>
														<?= $d->in_outs[0] ?? '' ?>
													<?php } ?>
												</td>
												<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>" style="vertical-align: middle">
													<?php $clock_out_check = admin_add_edit_check_clock_out(isset($d->in_outs_id[1]) ? $d->in_outs_id[1] : null); ?>
													<?php if ($clock_out_check == 1) { ?>
														<b class="text-danger"><?= $d->in_outs[1] ?? '' ?></b>
													<?php }else{ ?>
														<?= $d->in_outs[1] ?? '' ?>
													<?php } ?>
												</td><td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>" style="vertical-align: middle">
													<?php $clock_in_check = admin_add_edit_check_clock_in(isset($d->in_outs_id[2]) ? $d->in_outs_id[2] : null); ?>
													<?php if ($clock_in_check == 1) { ?>
														<b class="text-danger"><?= $d->in_outs[2] ?? '' ?></b>
													<?php }else{ ?>
														<?= $d->in_outs[2] ?? '' ?>
													<?php } ?>
												</td>
												<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>" style="vertical-align: middle">
													<?php $clock_out_check = admin_add_edit_check_clock_out(isset($d->in_outs_id[3]) ? $d->in_outs_id[3] : null); ?>
													<?php if ($clock_out_check == 1) { ?>
														<b class="text-danger"><?= $d->in_outs[3] ?? '' ?></b>
													<?php }else{ ?>
														<?= $d->in_outs[3] ?? '' ?>
													<?php } ?>
												</td>
											<?php else : ?>
												<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>" style="vertical-align: middle">
													<?php $clock_in_check = admin_add_edit_check_clock_in(isset($clock->clock_in_id) ? $clock->clock_in_id : null); ?>
													<?php if ($clock_in_check == 1) { ?>
														<b class="text-danger"><?= $clock->clock_in; ?></b>
													<?php }else{ ?>
														<?= $clock->clock_in; ?>
													<?php } ?>
												</td>
												<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>" style="vertical-align: middle">
													<?php $clock_out_check = admin_add_edit_check_clock_out(isset($clock->clock_out_id) ? $clock->clock_out_id : null); ?>
													<?php if ($clock_out_check == 1) { ?>
														<b class="text-danger"><?= $clock->clock_out; ?></b>
													<?php }else{ ?>
														<?= $clock->clock_out; ?>
													<?php } ?>
												</td>
											<?php endif; ?>
												<!-- hours start -->
												<!-- <td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>" style="vertical-align: middle"><?php if($clock->clock_out == "") {echo ""; }else{ echo $clock->total_time; }?></td> -->
												<!-- hours end -->
												<?php if($key == 0){ ?>
													<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php echo $d->total_hours; ?></td>
													<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php echo $d->work_hours; ?></td>
													<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php if($d->break_hours != "00:00"){echo $d->break_hours;} ?></td>
													
													<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle">
														<span class="<?php if(!$d->is_late){echo 'strike';}else{echo 'countLate';}?>" style="display: none"><?php echo $d->late_hours; ?></span>
														<?php if($d->clockings[0]->clock_in != ""){ ?>
														
															<?php if($is_emp_summary_editable === TRUE) : ?>
															<button 
																style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;"
																class="btn btn-default btn-xs editLateButton <?php if(!$d->is_late){echo 'strike';} ?>"
																data-toggle="modal" data-target="#editLateHours"
																data-date="<?php echo $d->date;?>"
																data-empid="<?php echo $employee->emp_id;?>"
																data-latehours="<?php echo $d->late_hours;?>"
															>
																<?php if (!empty($d->late_hours) && $d->late_hours != '00:00') : ?>
																	<?php echo $d->late_hours; ?>
																<?php else : ?>
																	<i class="fa fa-plus"></i>
																<?php endif ?>
															</button>
															<?php else : ?>
															<?php if(!empty($d->late_hours) && $d->late_hours != "00:00"){ echo $d->late_hours; }  ?>
															<?php endif; ?>
														
														<?php } ?>
															<span class="show-on-print <?php if(!$d->is_late){echo 'strike';} ?>" style="display: none;"><?php echo $d->late_hours; ?></span>
													</td>



													<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle">
													<span class="<?php if(!$d->is_late_break){echo 'strike';}else{echo 'countLateBreak';}?>" style="display: none"><?php echo $d->break_late_hours; ?></span>
													<?php if($d->clockings[0]->clock_in != ""){ ?>
													
														<?php if($is_emp_summary_editable === TRUE) : ?>
															<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editLateBreakButton <?php if(!$d->is_late_break){echo 'strike';} ?>" data-toggle="modal" data-target="#editLateBreakHours" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>" data-latehours="<?php echo $d->break_late_hours;?>"><?php if(!empty($d->break_late_hours) && $d->break_late_hours != "00:00"){ echo $d->break_late_hours; }else{ ?><i class="fa fa-plus"></i> <?php } ?></button>
														<?php else : ?>
														<?php if(!empty($d->break_late_hours) && $d->break_late_hours != "00:00") { echo $d->break_late_hours; }  ?>
														<?php endif ?>
													
													<?php } ?>
														<span class="show-on-print <?php if(!$d->is_late_break){echo 'strike';} ?>" style="display: none;"><?php echo $d->break_late_hours; ?></span>
													</td>
													<!-- early out start -->
													<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle">
														<span class="<?php if(!$d->is_early_out){echo 'strike';}else{echo 'countEarlyOut';}?>" style="display: none"><?php echo $d->early_out; ?></span>
													<?php if($d->clockings[0]->clock_in != ""){ ?>
													
														<?php if($is_emp_summary_editable === TRUE) : ?>
															<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editEarlyOutButton <?php if(!$d->is_early_out){echo 'strike';}?>" data-toggle="modal" data-target="#editEarlyOutHours" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>" data-earlyhours="<?php echo $d->early_out;?>"><?php if(!empty($d->early_out) && $d->early_out != "00:00"){ echo $d->early_out; }else{ ?><i class="fa fa-plus"></i> <?php } ?></button>
														<?php else : ?>
														<?php if(!empty($d->early_out) && $d->early_out != "00:00") { echo $d->early_out; }  ?>
														<?php endif ?>
													
													<?php } ?>
														<span class="show-on-print <?php if(!$d->is_early_out){echo 'strike';}?>" style="display: none;"><?php echo $d->early_out; ?></span>
													</td>
													<!-- early out end -->

													<!-- short hours start -->
													<!-- <td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle">
														<span class="countShortHours" style="display: none"><?php echo $d->short_hours; ?></span>
													<?php if($d->clockings[0]->clock_in != ""){ ?>
														<?php if($is_emp_summary_editable === TRUE) : ?>
															<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editShortHoursButton" data-toggle="modal" data-target="#editShortHours" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>" data-shorthours="<?php echo $d->short_hours;?>"><?php if(!empty($d->short_hours) && $d->short_hours != "00:00"){ echo $d->short_hours; }else{ ?><i class="fa fa-plus"></i> <?php } ?></button>
														<?php else : ?>
														<?php if(!empty($d->short_hours) && $d->short_hours != "00:00") { echo $d->short_hours; }  ?>
														<?php endif ?>
													
													<?php } ?>
														<span class="show-on-print" style="display: none;"><?php echo $d->short_hours; ?></span>
													</td> -->
													<!-- short hours end -->

													<!-- simple OT -->
													<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle">
													<?php if(!in_array($d->date,$public_holidays) && !in_array($d->day_name, $rest_days) && !in_array($d->day_name, $off_days) && $d->is_shift == 'true' && !$d->is_replaced_ph && !$d->is_rest_day) : ?>
														<?php if($d->is_shift == 'false') : ?>
															<span class="text-danger countOT"><?php echo $d->overtime_m ?></span>
														<?php elseif($d->is_shift == 'true') : ?>
															<?php if($d->is_ot) : ?>
																<span class="<?php echo (!empty($d->overtime_m) || $d->is_extra_ot == true ? "text-danger" : "") ?> countOT"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
															<?php else : ?>
																<?php if(!empty($d->overtime)) : ?>
																	<span class="strike <?php echo ($d->is_extra_ot == true) ? "text-danger" : "" ?>"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
																<?php endif ?>
																<span class="text-danger countOT"><?php echo $d->overtime_m ?></span>
															<?php endif ?>
														<?php endif ?>
													<?php endif ?>
													<!-- <?php if(!in_array($d->date,$public_holidays) && !in_array($d->day_name, $rest_days) && !in_array($d->day_name, $off_days) && $d->is_shift == 'true'){ ?><span class="otspan <?php if($d->is_ot){ echo 'countOT';}?>"><?php echo ($d->is_ot) ? add_time_minus($d->overtime, $d->overtime_m) : $d->overtime ; ?></span><?php } ?> -->
													</td>

													<!-- OT(M) -->
													<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle">
														<span class="countOT" style="display: none"><?php echo $d->overtime_m; ?></span>
														
															<?php if($is_emp_summary_editable === TRUE) : ?>
																<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editButton" data-toggle="modal" data-target="#editOvertimeModal" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>" data-overtime="<?php echo $d->overtime_m;?>" data-type="<?php echo $d->overtime_type; ?>"><i class="fa fa-plus"></i></button>
															<?php endif ?>
														
													</td>
												<!-- OT(PH) x2 -->
												<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle">
												<?php if($d->x2 && (in_array($d->date,$public_holidays) || $d->is_replaced_ph)) : ?>
													
													<?php if($d->is_ot) : ?>
														<span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo $d->overtime_ph_x2 ?></span>
													<?php else : ?>
														<?php if(!empty($d->overtime)) : ?>
															<span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
														<?php endif ?>
														<span class="text-danger"><?php echo $d->overtime_m ?></span>
													<?php endif ?>
														
												<?php endif ?>
												<!-- <?php if(in_array($d->date,$public_holidays)){ ?><span><?php echo ($d->is_ot || $d->is_shift == 'false') ? add_time_minus($d->overtime, $d->overtime_m) : $d->overtime ; ?></span><?php } ?> -->
												</td>
												<!-- OT(PH) x3 -->
												<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle">
												<?php if($d->x3 && (in_array($d->date,$public_holidays) || $d->is_replaced_ph)) : ?>
													
													<?php if($d->is_ot) : ?>
														<span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo $d->overtime_ph_x3 ?></span>
													<?php else : ?>
														<?php if(!empty($d->overtime)) : ?>
															<span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
														<?php endif ?>
														<span class="text-danger"><?php echo $d->overtime_m ?></span>
													<?php endif ?>
														
												<?php endif ?>
												<!-- <?php if(in_array($d->date,$public_holidays)){ ?><span><?php echo ($d->is_ot || $d->is_shift == 'false') ? add_time_minus($d->overtime, $d->overtime_m) : $d->overtime ; ?></span><?php } ?> -->
												</td>
												<!-- OT(RD) -->
												<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle">
												<?php if ($d->is_rest_day) : ?>
													
														<?php if($d->is_ot) : ?>
															<span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
														<?php else : ?>
															<?php if(!empty($d->overtime)) : ?>
																<span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
															<?php endif ?>
															<span class="text-danger"><?php echo $d->overtime_m ?></span>
														<?php endif ?>
													<?php endif; ?>
												<!-- <?php if(!in_array($d->date,$public_holidays) && (in_array($d->day_name, $rest_days) || $d->is_shift == 'false')){ ?><span><?php echo ($d->is_ot || $d->is_shift == 'false') ? add_time_minus($d->overtime, $d->overtime_m) : $d->overtime ; ?></span><?php } ?> -->
												</td>

												<!-- OT(OFF) -->
												<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle">
												<?php if (!in_array($d->date,$public_holidays) && (in_array($d->day_name, $off_days))) : ?>
													
														<?php if($d->is_ot) : ?>
															<span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
														<?php else : ?>
															<?php if(!empty($d->overtime)) : ?>
																<span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
															<?php endif ?>
															<span class="text-danger"><?php echo $d->overtime_m ?></span>
														<?php endif ?>
													<?php endif; ?>
												</td>

												<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php echo $d->days; ?></td>
												<?php if(!$custom_in_outs): ?>
												<!-- trip A starts -->
												<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle">
															
																<?php if($is_emp_summary_editable === TRUE) : ?>
																	<span><?= $d->trip_a ; ?></span><br>
																	<button class="btn btn-xs trip_btn" data-no_of_trips="<?php echo $d->trip_a ?>" data-id="<?php echo $emp_id ?>" data-date="<?php echo $d->date ?>" data-type="a-up" style="min-width: 0px; min-height: 0px; font-size: 10px;"><i class="fa fa-arrow-up"></i></button>
																	<button class="btn btn-xs trip_btn"data-no_of_trips="<?php echo $d->trip_a ?>" data-id="<?php echo $emp_id ?>" data-date="<?php echo $d->date ?>" data-type="a-down" style="min-width: 0px; min-height: 0px; font-size: 10px;"><i class="fa fa-arrow-down"></i></button>
																<?php else : ?>
																	<?php if($d->trip_a != 0 || !empty($d->trip_a)) : ?>
																		<?php echo $d->trip_a; ?>
																	<?php endif ?>
																<?php endif ?>
															
															<span class="countTrip_a" style="display: none"><?php if($d->trip_a != "") echo $d->trip_a; ?></span>
															<span class="show-on-print" style="display: none;"><?php if($d->trip_a != 0) echo $d->trip_a; ?></span>
															</td>
															<!-- trip a ends -->
															<!-- trip b starts -->
															<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle">
																
																	<?php if($is_emp_summary_editable === TRUE) : ?>
																		<span><?= $d->trip_b ; ?></span><br>
																<button class="btn btn-xs trip_btn" data-no_of_trips="<?php echo $d->trip_b ?>" data-id="<?php echo $emp_id ?>" data-date="<?php echo $d->date ?>" data-type="b-up" style="min-width: 0px; min-height: 0px; font-size: 10px;"><i class="fa fa-arrow-up"></i></button>
																<button class="btn btn-xs trip_btn"data-no_of_trips="<?php echo $d->trip_b ?>" data-id="<?php echo $emp_id ?>" data-date="<?php echo $d->date ?>" data-type="b-down" style="min-width: 0px; min-height: 0px; font-size: 10px;"><i class="fa fa-arrow-down"></i></button>
																	<?php else: ?>
																		<?php if($d->trip_b != 0 || !empty($d->trip_b)) : ?>
																			<?php echo $d->trip_b; ?>
																		<?php endif; ?>
																	<?php endif ?>
																
																<span class="countTrip_b" style="display: none"><?php if($d->trip_b != "") echo $d->trip_b; ?></span>
																<span class="show-on-print" style="display: none;"><?php if($d->trip_b != 0) echo $d->trip_b; ?></span>
															</td>
															<!-- trip b ends -->
												<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php if( isset($clock->clock_in_o) && beautify_time($clock->clock_in_o) > beautify_time($clock->grace_time_o)): ?>

												
													<?php if($is_emp_summary_editable === TRUE) : ?>
														<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" id="btn-reason-<?php echo $clock->id ?>" data-reason="<?php echo $clock->reason ?>" data-id="<?php echo $clock->id ?>" type="button" class="btn btn-default btn-xs" aria-label="Add reason" data-toggle="modal" data-target="#reason-modal">
													<?php endif; ?>
												
											<!-- <span class="fa fa-comment" aria-hidden="true"></span> -->
												<?php if(empty($clock->reason)): ?>
													
														<?php if($is_emp_summary_editable === TRUE) : ?>
															<span class="fa fa-plus" aria-hidden="true"></span>
														<?php endif; ?>
													
												<?php else: ?>
													<?php echo $clock->reason ?>
												<?php endif; ?>

												
													<?php if($is_emp_summary_editable === TRUE) : ?>
																</button>
													<?php endif; ?>
												

														<?php endif; ?>
														<span class="show-on-print" style="display: none;"><?php echo $clock->reason; ?></span>
													</td>
													<?php endif; ?>
														<td rowspan="<?php echo count($d->clockings); ?>" class="text-center" style="vertical-align: middle">
															<?php if ($is_emp_summary_editable === TRUE): ?>
																<button 
																	style="font-size:11px; max-width:100px; overflow:hidden; white-space: normal;" 
																	id="btn-remark-<?php echo $emp_id; ?>-<?php echo $d->date; ?>" 
																	data-remark="<?php echo $clock->remark; ?>" 
																	data-id="<?php echo $emp_id; ?>" 
																	data-date="<?php echo $d->date; ?>" 
																	type="button" 
																	class="btn <?php echo in_array($d->date, $public_holidays) ? 'btn-danger' : 'btn-default'; ?> btn-xs" 
																	aria-label="Add remark" 
																	data-toggle="modal" 
																	data-target="#remark-modal"
																>
															<?php endif; ?>

															<?php if (!empty($clock->remark)): ?>
																<!-- Show the remark if it exists -->
																<?php echo $clock->remark; ?>
															<?php elseif (in_array($d->date, $public_holidays)): ?>
																<!-- Show the holiday name if it's a public holiday -->
																<?php echo $public_holidays_names[array_search($d->date, $public_holidays)]; ?>
															<?php else: ?>
																<!-- Show add remark icon if editable -->
																<?php if ($is_emp_summary_editable === TRUE): ?>
																	<span class="fa fa-plus" aria-hidden="true"></span>
																<?php endif; ?>
															<?php endif; ?>

															<?php if ($is_emp_summary_editable === TRUE): ?>
																</button>
															<?php endif; ?>

															<!-- Print-only remark -->
															<span class="show-on-print" style="display: none;"><?php echo $clock->remark; ?></span>
														</td>
															
														<?php } ?>
													</tr>

												<?php }} ?>
												<?php if($total != "00:00" || $work != "00:00" || $break != "00:00"){ ?>
													<tr>
														<td colspan="<?= $custom_in_outs ? 5 : 3; ?>"></td>
														<td class="text-center"><b>Total</b></td>
														<td class="text-center"><?php echo $total;?></td>
														<td class="text-center"><?php echo $work;?></td>
														<td class="text-center"><?php echo $break;?></td>
														<td class="text-center month-late"><?php echo $late;?></td>
														<td class="text-center month-late-break"><?php echo $break_late;?></td>
														<td class="text-center month-early-out"><?php echo $total_early; ?></td>
														<!-- <td class="text-center month-short-hours"><?php echo $total_short; ?></td> -->
														<td class="text-center month-overtime <?php if($lateness_time != $lateness_time_deducted){ echo 'strike'; } ?>" colspan="<?= $custom_in_outs ? 1 : 2; ?>"><?php echo $month_overtime;?></td>
														<td class="text-center"><?php echo $month_overtime_ph_x2;?></td>
														<td class="text-center"><?php echo $month_overtime_ph_x3;?></td>
														<td class="text-center"><?php echo $month_overtime_rd;?></td>
														<td class="text-center"><?php echo $month_overtime_off;?></td>
														<td class="text-center"><?php echo $total_days;?></td>
														<?php if(!$custom_in_outs): ?>
															<td class="text-center total_trip_a"><?php echo $total_trip_a; ?></td>
															<td class="text-center total_trip_b"><?php echo $total_trip_b; ?></td>
														<?php endif; ?>
															<td colspan="<?= $custom_in_outs ? 1 : 2; ?>"></td>
													</tr>
													<tr>
														<td colspan="<?= $custom_in_outs ? 6 : 4; ?>"></td>
														<td colspan="3" class="text-center"><b>Lateness Time</b></td>
														<td colspan="3" class="text-center <?php if($lateness_time != $lateness_time_deducted){ echo 'strike'; } ?>"><?php echo $lateness_time; ?></td>
														<td colspan="4" class="text-center">
															<b>Deduct from OT</b>
														</td>
														<td colspan="1" class="text-center">
															<div class="btn-group btn-group-xs" style="min-width: 45px">
																<button type="button" class="btn btn-success status_btn btn_check" <?php if($employee->deduct_from_ot){ echo "disabled";} ?> data-emp-id = "<?php echo $employee->emp_id;?>" data-deduct = "1">
																	<span class="fa fa-check"></span>
																</button>
																<button type="button" class="btn btn-danger status_btn btn_close" <?php if(!$employee->deduct_from_ot){ echo "disabled";} ?> data-emp-id = "<?php echo $employee->emp_id;?>" data-deduct = "0">
																	<span class="fa fa-close"></span>
																</button>
															</div>
														</td>
														<td colspan="6"></td>
													</tr>
													<?php if($lateness_time != $lateness_time_deducted){ ?>
													<tr>
														<td colspan="<?= $custom_in_outs ? 6 : 4; ?>"></td>
														<td colspan="3" class="text-center"><b>After Deduction</b></td>
														<td colspan="3" class="text-center"><?php echo $lateness_time_deducted; ?></td>
														<td colspan="2" class="text-center">
															<?php echo $month_overtime_deducted; ?>
														</td>
														<td colspan="<?= $custom_in_outs ? 5 : 9; ?>"></td>
													</tr>
												<?php } ?>
													
												<?php } ?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				

				<div id="editOvertimeModal" class="modal fade" role="dialog">
					<div class="modal-dialog modal-sm">

						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Edit Overtime</h4>
							</div>
							<div class="modal-body" id="inputbox">
								<div class="row">
									<form id="editForm">
										<div class="col-md-12">
											<div class="form-group">
												<label class="control-label">Overtime<span class="text-danger">*</span></label>
												<input class="form-control datetimepicker2" type="text" id="overtime" required="" name="overtime" autocomplete="off">
											</div>
										</div>
										<input type="hidden" name="empid" id="empid">
										<input type="hidden" name="date" id="date">
										<div class="col-md-12">
											<div class="checkbox">
												<label><input type="checkbox" id="minus-checkbox" name="minus_ot" value="minus"><b>Minus OT</b></label>
											</div>
										</div>

										
										<div class="col-md-12">
											<div class="form-group">
												<button class="btn btn-primary" type="submit">Update</button>
												<button class="btn btn-danger" type="button" style="display: none;" id="removeBtn">Remove</button>
											</div>
										</div>
									</form>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							</div>
						</div>

					</div>
				</div>

				<div id="editLateHours" class="modal fade" role="dialog">
					<div class="modal-dialog modal-sm">

						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Edit Late Hours</h4>
							</div>
							<div class="modal-body" id="inputboxforlate">
								<div class="row">
									<form id="editFormForLate">
										<div class="col-md-12">
											<div class="form-group">
												<label class="control-label">Late Hours<span class="text-danger">*</span></label>
												<input class="form-control datetimepicker3" type="text" id="latehours" required="" name="latehours" autocomplete="off">
											</div>
										</div>

										<input type="hidden" name="empid" id="empidlate">
										<input type="hidden" name="date" id="datelate">
										<div class="col-md-12">
											<div class="form-group">
												<button class="btn btn-primary" type="submit">Update</button>
												<button class="btn btn-danger" style="display: none;" type="button" id="btn-late-delete">Delete</button>
											</div>
										</div>
									</form>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							</div>
						</div>

					</div>
				</div>

				<div id="editLateBreakHours" class="modal fade" role="dialog">
					<div class="modal-dialog modal-sm">

						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Edit Late Hours (Break)</h4>
							</div>
							<div class="modal-body" id="inputboxforlatebreak">
								<div class="row">
									<form id="editFormForLateBreak">
										<div class="col-md-12">
											<div class="form-group">
												<label class="control-label">Late Hours<span class="text-danger">*</span></label>
												<input class="form-control datetimepicker4" type="text" id="latehoursbreak" required="" name="latehours" autocomplete="off">
											</div>
										</div>

										<input type="hidden" name="empid" id="empidlatebreak">
										<input type="hidden" name="date" id="datelatebreak">
										<div class="col-md-12">
											<div class="form-group">
												<button class="btn btn-primary" type="submit">Update</button>
											</div>
										</div>
									</form>
									<form id="deleteFormForLateBreak">
										<input type="hidden" name="empid" id="empidlatebreak_del">
										<input type="hidden" name="date" id="datelatebreak_del">
										<div class="col-md-12">
											<div class="form-group">
												<button class="btn btn-danger _del" type="submit">Delete</button>
											</div>
										</div>
									</form>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							</div>
						</div>

					</div>
				</div>


				<div id="editEarlyOutHours" class="modal fade" role="dialog">
					<div class="modal-dialog modal-sm">

						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Edit Early Out Hours</h4>
							</div>
							<div class="modal-body" id="inputboxforearlyout">
								<div class="row">
									<form id="editFormForEarlyOut">
										<div class="col-md-12">
											<div class="form-group">
												<label class="control-label">Early Out Hours<span class="text-danger">*</span></label>
												<input class="form-control datetimepicker5" type="text" id="earlyouthours" required="" name="early_out" autocomplete="off">
											</div>
										</div>

										<input type="hidden" name="empid" id="empidearlyout">
										<input type="hidden" name="date" id="dateearlyout">
										<div class="col-md-12">
											<div class="form-group">
												<button class="btn btn-primary" type="submit">Update</button>
											</div>
										</div>
									</form>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							</div>
						</div>

					</div>
				</div>

				<div id="editShortHours" class="modal fade" role="dialog">
					<div class="modal-dialog modal-sm">

						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Edit Short Hours</h4>
							</div>
							<div class="modal-body" id="inputboxforshorthours">
								<div class="row">
									<form id="editFormForShortHours">
										<div class="col-md-12">
											<div class="form-group">
												<label class="control-label">Short Hours<span class="text-danger">*</span></label>
												<input class="form-control datetimepicker6" type="text" id="shorthours" required="" name="short_hours" autocomplete="off">
											</div>
										</div>

										<input type="hidden" name="empid" id="empidshorthours">
										<input type="hidden" name="date" id="dateshorthours">
										<div class="col-md-12">
											<div class="form-group">
												<button class="btn btn-primary" type="submit">Update</button>
											</div>
										</div>
									</form>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							</div>
						</div>

					</div>
				</div>

				<div id="editClockingModal" class="modal fade" role="dialog">
					<div class="modal-dialog modal-sm">

						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Edit Clocking</h4>
							</div>
							<div class="modal-body" id="inputbox2">
								<div class="row">
									<form id="editClockingForm">
										<div class="col-md-12">
											<div class="form-group">
												<label class="control-label">Clocking Time<span class="text-danger">*</span></label>
												<input class="form-control datetimepicker3" type="text" id="clocking_time" required="" name="clocking_time" autocomplete="off">
											</div>
										</div>

										<input type="hidden" name="clocking_id" id="clocking_id">
										<div class="col-md-12">
											<div class="form-group">
												<button class="btn btn-primary" type="submit">Update</button>
											</div>
										</div>
									</form>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							</div>
						</div>

					</div>
				</div>

				<!-- Modal -->
				<div id="reason-modal" class="modal fade" role="dialog">
					<div class="modal-dialog modal-sm">

						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Late Reason</h4>
							</div>
							<div class="modal-body">
								<div class="form-group">
									<label for="sel1">Select reason from dropdown</label>
									<select class="form-control" id="dropdown-reason">
										<option value="">Select reason</option>
										<option value="Traffic">Traffic</option>
										<option value="Sick">Sick</option>
										<option value="Other">Other</option>
									</select>
								</div>
								<input type="hidden" class="form-control" id="input-id">

								<div id="input-reason-container" style="display: none" class="form-group">
									<label for="usr">Enter reason</label>
									<input type="text" class="form-control" id="input-reason">
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								<button style="display: none" id="btn-reason-delete" type="button" class="btn btn-danger">Delete</button>
								<button  id="btn-reason-save" type="button" class="disabled btn btn-primary">Save</button>
							</div>
						</div>

					</div>
				</div>

				<!-- Modal -->
				<div id="remark-modal" class="modal fade" role="dialog">
					<div class="modal-dialog modal-sm">

						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Remark</h4>
							</div>
							<div class="modal-body">

								<input type="hidden" class="form-control" id="remark-id">
								<input type="hidden" class="form-control" id="remark-date">

								<div id="input-remark-container" class="form-group">
									<label for="usr">Enter remark</label>
									<textarea class="form-control" id="input-remark"></textarea>
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								<button style="display: none" id="btn-remark-delete" type="button" class="btn btn-danger">Delete</button>
								<button  id="btn-remark-save" type="button" class="disabled btn btn-primary">Save</button>
							</div>
						</div>

					</div>
				</div>

				<script type="text/javascript">


					$(document).ready(function(){

						$('.apply-select2').select2();
						$('.apply-select3').select2();


						$(".apply-select2").change(function(){
							var selectedValue = $(this).children("option:selected").val();
        //alert("You have selected the country - " + selectedValue);
        // Get the current page
        var curr_page = window.location.href;
		  //alert(curr_page);
		  var selectedDepartment = $(".apply-select3").children("option:selected").val();
		  var res = curr_page.split("?");

		  var params = "";

		  if(res.length == 2){
		  	params = "?"+res[1];
		  }

		  //alert(res.length);
		//     next_page = "";

		// // If current page has a query string, append action to the end of the query string, else
		// // create our query string
		// if(curr_page.indexOf("?") > -1) {
		//     next_page = curr_page+"&action=someaction";
		// } else {
		//     next_page = curr_page+"?action=someaction";
		// }

		// Redirect to next page
		window.location = js_base_url + 'summary/view/' + selectedValue+'/' + selectedDepartment + '/' +params;
	});

						$(".apply-select3").change(function(){
							var selectedValue = $(this).children("option:selected").val();
        //alert("You have selected the country - " + selectedValue);
        // Get the current page
        var curr_page = window.location.href;
		  //alert(curr_page);

		  var res = curr_page.split("?");

		  var params = "";

		  if(res.length == 2){
		  	params = "?"+res[1];
		  }

		  //alert(res.length);
		//     next_page = "";

		// // If current page has a query string, append action to the end of the query string, else
		// // create our query string
		// if(curr_page.indexOf("?") > -1) {
		//     next_page = curr_page+"&action=someaction";
		// } else {
		//     next_page = curr_page+"?action=someaction";
		// }

		// Redirect to next page
		window.location = js_base_url + 'summary/view/0/' + selectedValue+params;
		// console.log(js_base_url + 'summary/view/0/' + selectedValue+params);
	});


					});

				</script>

				<script>


					var groups = [];

					function groupIndex(element){
						for(var i = 0; i < groups.length; i++){
							var group = groups[i].parent;
							if(group == element){
								return i;
							}
						}
						return null;
					}

					$(document).ready(function(){
						element = null;
						element2 = null;
			// $(document).on('mouseenter', '.freeze-table table:first .manualTD', function(){
			// 	$(this).children('.editButton').show();
			// });
			// $(document).on('mouseleave', '.freeze-table table:first .manualTD', function(){
			// 	$(this).children('.editButton').hide();
			// });

			$(document).on('click', '.freeze-table table:first .trip_btn', function(){
				trip_data = $(this).data();
				currentElement = $(this);
				if(trip_data.no_of_trips == 0 && (trip_data.type == "a-down" || trip_data.type == "b-down")){
					return;
				}else{
					$("body").LoadingOverlay("show");
					var id = trip_data.id;
					var date = trip_data.date;
					var type = trip_data.type;
					var trips = trip_data.no_of_trips;
					$.ajax({
						type: "GET",  
						url: "<?php echo base_url() ?>summary/save_trips",
						data: {'id':id, 'trips':trips, 'date':date, 'type':type},
						success: function (result) {

                           if(result){
                           	result = JSON.parse(result);
                           	trips = result.trips;
                           	type = result.type;
                           	var total_trips = 0;
                           	var class_text = '.countTrip_'+type;
                           	currentElement.attr("data-no_of_trips",trips);
                           	currentElement.data('no_of_trips', trips);
                           	currentElement.siblings('button').attr("data-no_of_trips",trips);
                           	currentElement.siblings('button').data('no_of_trips', trips);
                           	currentElement.siblings('span').html(trips);
                           	$(".freeze-table table:first "+class_text).each(function() {
                           		currentTrip = $(this).text();
                           		if(currentTrip != ''){
                           			total_trips += parseInt(currentTrip);
                           		}
                           		$('.total_trip_'+type).text(total_trips);
                           		
                           	});

                           	$("body").LoadingOverlay("hide");
                           	$.notify(
								"Success: trip count changed successfully!", 
								{ position:"top center",
								className: 'success',
								style: 'bootstrap',
								gap: 20,
								autoHide: true
							}
							);
                           	
                           	

                           }
                       }
                   });
				}

			});
			$(document).on('click', '.freeze-table table:first .editButton', function(){
				$('#removeBtn').hide();
				element = $(this);
				editData = $(this).data();
				oldTime = editData.overtime.replace('-','');
				oldTime = oldTime.split(':');
				hours = 0;
				minutes = 0;
				if(oldTime.length != 1){
					hours = oldTime[0];
					minutes = oldTime[1];
					$('#removeBtn').show();
				}

				if(editData.type == "-"){
					$('#minus-checkbox').prop('checked', true);
				}else{
					$('#minus-checkbox').prop('checked', false);
				}
				
				$('.datetimepicker2').data("DateTimePicker").date(new Date(1979, 0, 1, hours, minutes, 0, 0));
				$('#empid').val(editData.empid);
				$('#date').val(editData.date);
			});

			$(document).on('click', '.freeze-table table:first .editLateButton', function(){
				element2 = $(this);
				editData = $(this).data();
				oldTime = editData.latehours.split(':');
				hours = 0;
				minutes = 0;
				if(oldTime.length != 1){
					hours = oldTime[0];
					minutes = oldTime[1];
				}
				
				$('.datetimepicker3').data("DateTimePicker").date(new Date(1979, 0, 1, hours, minutes, 0, 0));
				$('#empidlate').val(editData.empid);
				$('#datelate').val(editData.date);

				$.ajax({
					type: "POST",  
					url: "<?php echo base_url() ?>summary/get_manual_late",
					data: JSON.stringify({'employee_id':editData.empid, 'date':editData.date}),
					success: function (result) {
						$('#btn-late-delete').show();
						$('#btn-late-delete').data('id', result.data.id);
					},
					error: function (result) {
						$('#btn-late-delete').hide();
					}
				});
			});

      $('#editLateHours').on('hidden.bs.modal', function () {
        $('#btn-late-delete').hide();
      })

			$('#btn-late-delete').on('click', function(){
				$.ajax({
					type: "POST",  
					url: "<?php echo base_url() ?>summary/delete_manual_late",
					data: JSON.stringify({'id':$(this).data('id')}),
					success: function (result) {
						$('#btn-late-delete').hide();
						$('#editLateHours').modal('hide');
						$.notify(
							result.message, 
							{ position:"top center",
							className: 'success',
							style: 'bootstrap',
							gap: 20,
							autoHide: true
						}
						);
					},
					error: function (result) {
						console.error(result);
					}
				});
			});

			$(document).on('click', '.freeze-table table:first .editLateBreakButton', function(){
				$('._del').show();
				element2 = $(this);
				editData = $(this).data();
				oldTime = editData.latehours.split(':');
				hours = 0;
				minutes = 0;
				if(oldTime.length != 1){
					hours = oldTime[0];
					minutes = oldTime[1];
				}
				if(hours == 0){
					$('._del').hide();
				}
				$('.datetimepicker4').data("DateTimePicker").date(new Date(1979, 0, 1, hours, minutes, 0, 0));
				$('#empidlatebreak').val(editData.empid);
				$('#datelatebreak').val(editData.date);
				$('#empidlatebreak_del').val(editData.empid);
				$('#datelatebreak_del').val(editData.date);
			});

			$(document).on('click', '.freeze-table table:first .editEarlyOutButton', function(){
				element2 = $(this);
				editData = $(this).data();
				oldTime = editData.earlyhours.split(':');
				hours = 0;
				minutes = 0;
				if(oldTime.length != 1){
					hours = oldTime[0];
					minutes = oldTime[1];
				}
				
				$('.datetimepicker5').data("DateTimePicker").date(new Date(1979, 0, 1, hours, minutes, 0, 0));
				$('#empidearlyout').val(editData.empid);
				$('#dateearlyout').val(editData.date);
			});

			$(document).on('click', '.freeze-table table:first .editShortHoursButton', function(){
				element2 = $(this);
				editData = $(this).data();
				oldTime = editData.shorthours.split(':');
				hours = 0;
				minutes = 0;
				if(oldTime.length != 1){
					hours = oldTime[0];
					minutes = oldTime[1];
				}
				
				$('.datetimepicker6').data("DateTimePicker").date(new Date(1979, 0, 1, hours, minutes, 0, 0));
				$('#empidshorthours').val(editData.empid);
				$('#dateshorthours').val(editData.date);
			});

			$(document).on('click', '.freeze-table table:first .btn-clocking', function(){
				element2 = $(this);
				editData = $(this).data();
				oldTime = editData.clocking.split(':');
				hours = 0;
				minutes = 0;
				if(oldTime.length != 1){
					hours = oldTime[0];
					minutes = oldTime[1];
				}
				
				$('.datetimepicker3').data("DateTimePicker").date(new Date(1979, 0, 1, hours, minutes, 0, 0));
				$('#clocking_id').val(editData.id);
			});

			$("#editForm").submit(function(e){
				$("#inputbox").LoadingOverlay("show");
				e.preventDefault();
				formdata = $(this).serializeArray();
				total_ot = "00:00";
				$.ajax({
					type: "POST",  
					url: "<?php echo base_url() ?>summary/updateOT",
					data: {'data':formdata},
					success: function (result) {
						$("#inputbox").LoadingOverlay("hide");
						$('#editOvertimeModal').modal('hide');

						element.siblings('span').html(formdata[0]['value']);
						element.attr('data-overtime', formdata[0]['value']);
						element.data('overtime', formdata[0]['value']);
						element.html(formdata[0]['value']);
						if(typeof(formdata[3]) != 'undefined' && formdata[0]['value'] != "00:00"){
							element.attr('data-type', '-');
							element.data('type', '-');
							element.html('-' + formdata[0]['value']);
							element.siblings('span').html('-' + formdata[0]['value']);
						}else{
							element.attr('data-type', '+');
							element.data('type', '+');
						}
						$(".freeze-table table:first .countOT").each(function() {
							original_time = $(this).text();
							currentTime = $(this).text().split(':');
							if(currentTime.length != 1){
								total_ot = add_time_minus(total_ot, original_time);
							}
							
						});
						$('.month-overtime').html(total_ot);
						if(result){
							$.notify(
								"Success: overtime changed successfully! Reload page to see changes.", 
								{ position:"top center",
								className: 'success',
								style: 'bootstrap',
								gap: 20,
								autoHide: true
							}
							);
						}
						
					}
				});
			});

			function add_time(time1, time2)
			{
				if (time2 == "00:00") {
					return time1;
				}
				
				time1 = time1.split(':');
				time2 = time2.split(':');
				hours = parseFloat(time1[0]) + parseFloat(time2[0]);
				minutes = parseFloat(time1[1]) + parseFloat(time2[1]);
				if (minutes >= 60) {
					minutes -= 60;
					hours = hours + 1;
				}
				
				if (hours == "0" && minutes == "0") {
					return "00:00";
				}
				if(hours < 10) hours = "0" + hours;
				if(minutes < 10) minutes = "0" + minutes;
				return hours + ":" + minutes;
			}

			

			function add_time_minus(time1, time2)
			{
				if (time2 == "00:00") {
					return time1;
				}

				if(is_minus(time1) && is_minus(time2)){
					time1 = time1.replace("-", "");
					time2 = time2.replace("-", "");
					total = "-" + add_time(time1, time2);
				}else if(!is_minus(time1) && !is_minus(time2)){
					total = add_time(time1, time2);
				}else if(!is_minus(time1) && is_minus(time2)){
					time2 = time2.replace("-", "");
					t1 = parseFloat(time1.replace(":", ""));
					t2 = parseFloat(time2.replace(":", ""));

					if(t1 < t2){
						total = "-" + sub_time(time2, time1);
					}else{
						total = sub_time(time1, time2);
					}

				}else{
					time1 = time1.replace("-", "");
					t1 = parseFloat(time1.replace(":", ""));
					t2 = parseFloat(time2.replace(":", ""));

					if(t2 < t1){
						total = "-" + sub_time(time1, time2);
					}else{
						total = sub_time(time2, time1);
					}
				}

				if(total == "-00:00") total = "00:00";

				return total;

			}

			function is_minus(string){
				if (string.includes("-")) {
					return true;
				}
				return false;
			}

			function sub_time(time1, time2)
			{
				if (time2 == "00:00") {
					return time1;
				}

				time1 = time1.split(':');
				time2 = time2.split(':');
				hours = parseFloat(time1[0]) - parseFloat(time2[0]);
				minutes = parseFloat(time1[1]) - parseFloat(time2[1]);
				if (minutes <= 0) {
					minutes += 60;
					hours = hours - 1;
				}
				if (minutes >= 60) {
					minutes -= 60;
					hours = hours + 1;
				}
				if(hours < 10) hours = "0" + hours;
				if(minutes < 10) minutes = "0" + minutes;
				
				return hours + ":" + minutes;
			}

			

			$("#editFormForLate").submit(function(e){
				$("#inputboxforlate").LoadingOverlay("show");
				e.preventDefault();
				formdata = $(this).serializeArray();
				total_hours = 0;
				total_minutes = 0;
				$.ajax({
					type: "POST",  
					url: "<?php echo base_url() ?>summary/updateLateHours",
					data: {'data':formdata},
					success: function (result) {
						$("#inputboxforlate").LoadingOverlay("hide");
						$('#editLateHours').modal('hide');

						element2.siblings('span').html(formdata[0]['value']);
						element2.attr('data-latehours', formdata[0]['value']);
						element2.data('latehours', formdata[0]['value']);
						if(formdata[0]['value'] == "00:00"){
							element2.html('<i class="fa fa-plus"></i>');
						}else{
							element2.html(formdata[0]['value']);
						}
						
						$(".freeze-table table:first .countLate").each(function() {
							currentTime = $(this).text().split(':');
							if(currentTime.length != 1){
								total_hours += parseInt(currentTime[0]);
								total_minutes += parseInt(currentTime[1]);
							}
							if(total_minutes >= 60){
								total_minutes -= 60;
								total_hours += 1;
							}
						});
						if(total_hours < 10 ) total_hours = '0' + total_hours;
						if(total_minutes < 10 ) total_minutes = '0' + total_minutes;
						$('.month-late').html(total_hours + ':' + total_minutes);
						if(result){
							$.notify(
								"Success: late hours changed successfully!", 
								{ position:"top center",
								className: 'success',
								style: 'bootstrap',
								gap: 20,
								autoHide: true
							}
							);
						}
						
					}
				});
			});

			$("#editFormForLateBreak").submit(function(e){
				$("#inputboxforlatebreak").LoadingOverlay("show");
				e.preventDefault();
				formdata = $(this).serializeArray();
				total_hours = 0;
				total_minutes = 0;
				$.ajax({
					type: "POST",  
					url: "<?php echo base_url() ?>summary/updateLateHoursBreak",
					data: {'data':formdata},
					success: function (result) {
						$("#inputboxforlatebreak").LoadingOverlay("hide");
						$('#editLateBreakHours').modal('hide');

						element2.siblings('span').html(formdata[0]['value']);
						element2.attr('data-latehours', formdata[0]['value']);
						element2.data('latehours', formdata[0]['value']);
						if(formdata[0]['value'] == "00:00"){
							element2.html('<i class="fa fa-plus"></i>');
						}else{
							element2.html(formdata[0]['value']);
						}
						
						$(".freeze-table table:first .countLateBreak").each(function() {
							currentTime = $(this).text().split(':');
							if(currentTime.length != 1){
								total_hours += parseInt(currentTime[0]);
								total_minutes += parseInt(currentTime[1]);
							}
							if(total_minutes >= 60){
								total_minutes -= 60;
								total_hours += 1;
							}
						});
						if(total_hours < 10 ) total_hours = '0' + total_hours;
						if(total_minutes < 10 ) total_minutes = '0' + total_minutes;
						$('.month-late-break').html(total_hours + ':' + total_minutes);
						if(result){
							$.notify(
								"Success: late hours (break) changed successfully!", 
								{ position:"top center",
								className: 'success',
								style: 'bootstrap',
								gap: 20,
								autoHide: true
							}
							);
						}
						
					}
				});
			});

			$("#deleteFormForLateBreak").submit(function(e){
				$("#inputboxforlatebreak").LoadingOverlay("show");
				e.preventDefault();
				formdata = $(this).serializeArray();
				total_hours = 0;
				total_minutes = 0;
				$.ajax({
					type: "POST",  
					url: "<?php echo base_url() ?>summary/deleteLateHoursBreak",
					data: {'data':formdata},
					success: function (result) {
						$("#inputboxforlatebreak").LoadingOverlay("hide");
						$('#editLateBreakHours').modal('hide');

						element2.siblings('span').html(formdata[0]['value']);
						element2.attr('data-latehours', formdata[0]['value']);
						element2.data('latehours', formdata[0]['value']);
						element2.html('<i class="fa fa-plus"></i>');
						if(result){
							$.notify(
								"Success: late hours (break) deleted successfully!", 
								{ position:"top center",
								className: 'error',
								style: 'bootstrap',
								gap: 20,
								autoHide: true
							}
							);
						}
					}
				});
			});

			$("#editFormForEarlyOut").submit(function(e){
				$("#inputboxforearlyout").LoadingOverlay("show");
				e.preventDefault();
				formdata = $(this).serializeArray();
				total_hours = 0;
				total_minutes = 0;
				$.ajax({
					type: "POST",  
					url: "<?php echo base_url() ?>summary/updateEarlyOutHours",
					data: {'data':formdata},
					success: function (result) {
						$("#inputboxforearlyout").LoadingOverlay("hide");
						$('#editEarlyOutHours').modal('hide');

						element2.siblings('span').html(formdata[0]['value']);
						element2.attr('data-earlyhours', formdata[0]['value']);
						element2.data('earlyhours', formdata[0]['value']);
						if(formdata[0]['value'] == "00:00"){
							element2.html('<i class="fa fa-plus"></i>');
						}else{
							element2.html(formdata[0]['value']);
						}
						
						$(".freeze-table table:first .countEarlyOut").each(function() {
							currentTime = $(this).text().split(':');
							if(currentTime.length != 1){
								total_hours += parseInt(currentTime[0]);
								total_minutes += parseInt(currentTime[1]);
							}
							if(total_minutes >= 60){
								total_minutes -= 60;
								total_hours += 1;
							}
						});
						if(total_hours < 10 ) total_hours = '0' + total_hours;
						if(total_minutes < 10 ) total_minutes = '0' + total_minutes;
						$('.month-early-out').html(total_hours + ':' + total_minutes);
						if(result){
							$.notify(
								"Success: early out hours changed successfully!", 
								{ position:"top center",
								className: 'success',
								style: 'bootstrap',
								gap: 20,
								autoHide: true
							}
							);
						}
						
					}
				});
			});

			$("#editFormForShortHours").submit(function(e){
				$("#inputboxforshorthours").LoadingOverlay("show");
				e.preventDefault();
				formdata = $(this).serializeArray();
				total_hours = 0;
				total_minutes = 0;
				$.ajax({
					type: "POST",  
					url: "<?php echo base_url() ?>summary/updateShortHours",
					data: {'data':formdata},
					success: function (result) {
						$("#inputboxforshorthours").LoadingOverlay("hide");
						$('#editShortHours').modal('hide');

						element2.siblings('span').html(formdata[0]['value']);
						element2.attr('data-shorthours', formdata[0]['value']);
						element2.data('shorthours', formdata[0]['value']);
						if(formdata[0]['value'] == "00:00"){
							element2.html('<i class="fa fa-plus"></i>');
						}else{
							element2.html(formdata[0]['value']);
						}
						
						$(".freeze-table table:first .countShortHours").each(function() {
							currentTime = $(this).text().split(':');
							if(currentTime.length != 1){
								total_hours += parseInt(currentTime[0]);
								total_minutes += parseInt(currentTime[1]);
							}
							if(total_minutes >= 60){
								total_minutes -= 60;
								total_hours += 1;
							}
						});
						if(total_hours < 10 ) total_hours = '0' + total_hours;
						if(total_minutes < 10 ) total_minutes = '0' + total_minutes;
						$('.month-short-hours').html(total_hours + ':' + total_minutes);
						if(result){
							$.notify(
								"Success: short hours changed successfully!", 
								{ position:"top center",
								className: 'success',
								style: 'bootstrap',
								gap: 20,
								autoHide: true
							}
							);
						}
						
					}
				});
			});

			$("#editClockingForm").submit(function(e){
				$("#inputbox2").LoadingOverlay("show");
				e.preventDefault();
				formdata = $(this).serializeArray();
				total_hours = 0;
				total_minutes = 0;
				$.ajax({
					type: "POST",  
					url: "<?php echo base_url() ?>summary/updateClocking",
					data: {'data':formdata},
					success: function (result) {
						$("#inputbox2").LoadingOverlay("hide");
						$('#editClockingModal').modal('hide');
						console.log(formdata[0]['value']);
						element2.attr('data-clocking', formdata[0]['value']);
						element2.data('clocking', formdata[0]['value']);
						element2.html(formdata[0]['value']);
						if(result){
							$.notify(
								"Success: clocking changed successfully!", 
								{ position:"top center",
								className: 'success',
								style: 'bootstrap',
								gap: 20,
								autoHide: true
							}
							);
							setTimeout(function(){ 
								location.reload(); 

							}, 1000);
						}
					}
				});
			});

			$("#removeBtn").click(function(e){
				$("#inputbox").LoadingOverlay("show");
				e.preventDefault();
				formdata = $('#editForm').serializeArray();
				total_ot = "00:00";
				$.ajax({
					type: "POST",  
					url: "<?php echo base_url() ?>summary/deleteOT",
					data: {'data':formdata},
					success: function (result) {
						$("#inputbox").LoadingOverlay("hide");
						$('#editOvertimeModal').modal('hide');

						element.siblings('span').html('');
						element.closest('td').prev().children('.otspan').addClass('countOT');
						element.attr('data-overtime', '');
						element.data('overtime', '');
						element.attr('data-type', '+');
						element.data('type', '+');
						element.html('<span class="fa fa-plus" aria-hidden="true"></span>');
						$(".freeze-table table:first .countOT").each(function() {
							original_time = $(this).text();
							currentTime = $(this).text().split(':');
							if(currentTime.length != 1){
								total_ot = add_time_minus(total_ot, original_time);
							}
						});
						$('.month-overtime').html(total_ot);
						if(result){
							$.notify(
								"Success: overtime deleted successfully!", 
								{ position:"top center",
								className: 'success',
								style: 'bootstrap',
								gap: 20,
								autoHide: true
							}
							);
						}
					}
				});
			});


			$(".freeze-table").freezeTable({
                  'columnNum' : 1,
                  'shadow': true,
                  'fixedNavbar':'.header',
                  'scrollBar': true

                });


			$('#from').val('<?php echo $from_f; ?>');
			$('#to').val('<?php echo $to_f; ?>');

			var tds = document.querySelectorAll("td, th");
			

			for(var i = 0; i < tds.length; i++){
				if(tds[i].getAttribute('rowspan') != null){
					var rspan = tds[i];
					groups.push({
						parent: rspan.parentNode,
						height: rspan.getAttribute('rowspan')
					});
				}
			}

				//console.log(groups);

				var count = 0;
				var rows = document.querySelectorAll('tr');
				var dark = true;

				for(var i = 0; i < rows.length; i++){
					var row = rows[i];
					var index = groupIndex(row);
					if(index != null && dark){
						var group = groups[index];
						var height = parseInt(group.height);
						for(var j = i; j < i + height; j++){
							rows[j].classList.add('dark-row');
						}
						i += height - 1;
						dark = !dark;
						continue;
					}
					if(dark){
				  	//rows[i].classList.add('dark-row');
				  }
				  dark = !dark;
				}

				

			})
		</script>

		<script type="text/javascript">


			$(document).ready(function(){



				var reasons_array = ["Traffic", "Sick",""];

				$('#reason-modal').on('show.bs.modal', function (event) {
					var id = $(event.relatedTarget).attr('data-id');
					var reason = $(event.relatedTarget).attr('data-reason');
					$(this).find("#input-id").val(id);

					var arraycontainsturtles = (reasons_array.indexOf(reason) > -1);

					if(reason.length > 0){
						$("#btn-reason-delete").show();
					}
					else{
						$("#btn-reason-delete").hide();
					}

                  //alert(reason);

                  if(arraycontainsturtles){
                  	$("#dropdown-reason option[value='"+reason+"']").prop('selected', true);
                  	$("#dropdown-reason").trigger("change");

                  }
                  else{
                  	$("#dropdown-reason option[value='Other']").prop('selected', true);
                  	$("#input-reason").val(reason);
                  	$("#input-reason-container").show();
                  	$("#input-reason").trigger("change");
                  }


              });

				/* $('#editClockingXCRUD').on('show.bs.modal', function (event) {
					var el = document.getElementById('ui-datepicker-div');
					if(el != null){
						el.remove();
					}
					var emp_id = $(event.relatedTarget).attr('data-empid');
					var date = $(event.relatedTarget).attr('data-date');
					var overnight = $(event.relatedTarget).attr('data-overnight');
					var shift = $(event.relatedTarget).attr('data-shift');
					$('#xcrudBox').html('');
					$("#modalBox").LoadingOverlay("show");
					$.ajax({
						type: "GET",  
						url: "<?php echo base_url() ?>summary/getXCRUD",
						data: {'emp_id':emp_id, 'date':date, 'overnight':overnight, 'shift':shift},
						success: function (result) {
                           //do somthing here
                           $("#modalBox").LoadingOverlay("hide");

                           if(result){
                           	$('#xcrudBox').html(result);

                           }
                       }
                   });
					
				}); */

				$('#remark-modal').on('show.bs.modal', function (event) {
					var id = $(event.relatedTarget).attr('data-id');
					var remark = $(event.relatedTarget).attr('data-remark');
					var date = $(event.relatedTarget).attr('data-date');
					$(this).find("#remark-id").val(id);
					$(this).find("#remark-date").val(date);
					$("#input-remark").val(remark);



					if(remark.length > 0){
						$("#btn-remark-delete").show();
						$("#btn-remark-save").removeClass("disabled");
					}
					else{
						$("#btn-remark-delete").hide();
					}



				});

				$('#dropdown-reason').on('change', function (e) {
					var optionSelected = $("option:selected", this);
					var valueSelected = this.value;

					$("#input-reason").val(valueSelected);


					if(valueSelected == "Other"){
						$("#input-reason-container").show();
						$("#input-reason").val("");
					}
					else{
						$("#input-reason-container").hide();
					}
					$("#input-reason").trigger("change");

				});

				$("#input-reason").on("change paste keyup", function() {

					if($(this).val().length > 0){
						$("#btn-reason-save").removeClass("disabled");
					}else{
						$("#btn-reason-save").addClass("disabled");
					}

				});

				$("#input-remark").on("change paste keyup", function() {

					if($(this).val().length > 0){
						$("#btn-remark-save").removeClass("disabled");
					}else{
						$("#btn-remark-save").addClass("disabled");
					}

				});

				$("#btn-reason-save").on("click", function(e) {

					if($(this).hasClass("disabled")){
						return;

					}

					$("#btn-reason-save").LoadingOverlay("show");

					var id = $("#input-id").val();
					var reason = $("#input-reason").val();

					$.ajax({
						type: "GET",  
						url: "<?php echo base_url() ?>overview/save_reason",
						data: {'id':id, 'reason':reason},
						success: function (result) {
                           //do somthing here
                           $("#btn-reason-save").LoadingOverlay("hide");

                           if(result){

                           	$('#reason-modal').modal("hide");

                           	$('#btn-reason-'+id).text(reason);
                           	$('#btn-reason-'+id).attr("data-reason",reason);

                           }
                       }
                   });

				});

				$("#btn-remark-save").on("click", function(e) {

					if($(this).hasClass("disabled")){
						return;

					}

					$("#btn-remark-save").LoadingOverlay("show");

					var id = $("#remark-id").val();
					var remark = $("#input-remark").val();
					var date = $("#remark-date").val();

					$.ajax({
						type: "GET",  
						url: "<?php echo base_url() ?>overview/save_remark",
						data: {'id':id, 'remark':remark, 'date':date},
						success: function (result) {
                           //do somthing here
                           $("#btn-remark-save").LoadingOverlay("hide");

                           if(result){

                           	$('#remark-modal').modal("hide");

                           	$('#btn-remark-'+id+'-'+date).text(remark);
                           	$('#btn-remark-'+id+'-'+date).attr("data-remark",remark);

                           }
                       }
                   });

				});

				

				$("#btn-reason-delete").on("click", function() {

					$("#btn-reason-delete").LoadingOverlay("show");

					var id = $("#input-id").val();

					$.ajax({
						type: "GET",  
						url: "<?php echo base_url() ?>overview/save_reason",
						data: {'id':id, 'reason':''},
						success: function (result) {
                           //do somthing here
                           $("#btn-reason-delete").LoadingOverlay("hide");

                           if(result){

                           	$('#reason-modal').modal("hide");

                           	$('#btn-reason-'+id).html('<span class="fa fa-plus" aria-hidden="true"></span>');
                           	$('#btn-reason-'+id).attr("data-reason",'');

                           }
                       }
                   });

				});

				$("#btn-remark-delete").on("click", function() {

					$("#btn-remark-delete").LoadingOverlay("show");

					var id = $("#remark-id").val();
					var date = $("#remark-date").val();

					$.ajax({
						type: "GET",  
						url: "<?php echo base_url() ?>overview/save_remark",
						data: {'id':id, 'remark':'', 'date':date},
						success: function (result) {
                           //do somthing here
                           $("#btn-remark-delete").LoadingOverlay("hide");

                           if(result){

                           	$('#remark-modal').modal("hide");

                           	$('#btn-remark-'+id+'-'+date).html('<span class="fa fa-plus" aria-hidden="true"></span>');
                           	$('#btn-remark-'+id+'-'+date).attr("data-remark",'');
                           	console.log("yes");
                           }
                       }
                   });

				});

				$('[data-toggle="tooltip"]').tooltip(); 

				$(".btn-view-modal").click(function(){

					var value1 = $(this).attr("data-emp_id");
					var value2 = $(this).attr("data-date");

                //contentType: "application/json; charset=utf-8",
                $("#myModal .modal-body").html("");
                $.ajax({
                	type: "GET",  
                	url: "<?php echo base_url() ?>overview/clocking_details_modal",
                	data: {'emp_id':value1, 'date':value2},
                	success: function (result) {
                           //do somthing here
                           $("#myModal .modal-body").html(result);
                       }
                   });
            });
			});

$(".status_btn").on("click", function(e) {
	var btn = $(this);
	var emp_id = $(this).attr('data-emp-id');
	var deduct = $(this).attr('data-deduct');

	$.ajax({
		type: "POST",  
		url: "<?php echo base_url() ?>summary/change_deduction_setting",
		data: {'id' : emp_id, 'deduct' : deduct},
		success: function (result) {
			btn.prop("disabled", true);
			btn.siblings().prop("disabled", false);
		}

	});
});
		</script>

		
<script>
	var base_url = '<?php echo base_url(); ?>';

    var config = {
      headers: {
        'Content-Type': 'application/json;charset=utf-8;'
      }
    }; 
    var app = angular.module('myApp', []);

    app.controller('summaryCtrl', function($scope,$http) {

    	$scope.settings = {};
		$scope.clockings = [];
		$scope.replacement_leave = "";
		$scope.prev_replacement_leave = "";
		$scope.showClockings = true;
		$scope.clockingType = "in";
		$scope.clockingTime = "";
		$scope.clockingId = "";
		$scope.clockingDate = "";
		$scope.employee_id = <?php echo $employee->emp_id; ?>;
		$scope.overnight = false;
		$scope.cut_off_time = "";
		$scope.loading = false;

		$scope.getClockings = function(id, date, overnight, cut_off_time){
			$scope.loading = true;
			$scope.showClockings = true;
			$scope.clockingDate = date;
			$scope.overnight = overnight;
			$scope.cut_off_time = cut_off_time;
			$scope.clockings = [];
			$('#inputboxClockings').LoadingOverlay("show",{maxSize:50});
			$http.post(base_url + 'summary/getClockings', {id: id, date: date, overnight: overnight, cut_off_time: cut_off_time}, config).then(function (response) {
				$scope.clockings = response.data.clockings;
				$scope.loading = false;
				$('#inputboxClockings').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}

		$scope.addClocking = function(){
			$scope.showClockings = false;
			$scope.clockingType = "in";
			$scope.clockingTime = "";
			$scope.clockingId = "";
			// empty the time field datetimepicker7
			$('.datetimepicker7').data("DateTimePicker").date(null);
		}

		$scope.editClocking = function(id, type, time){
			$scope.showClockings = false;
			$scope.clockingType = type;
			$scope.clockingTime = time;
			$scope.clockingId = id;
			// set the time field datetimepicker7
			$('.datetimepicker7').data("DateTimePicker").date(new Date(1979, 0, 1, time.split(":")[0], time.split(":")[1], 0, 0));
		}

		$scope.saveClocking = function(){
			let time = $("#clockingTimeField").val();
			if(time == ""){
				showNotification("Error", "Please enter a valid time", "error");
				return;
			}
			$('#inputboxClockings').LoadingOverlay("show",{maxSize:50});
			$http.post(base_url + 'summary/saveClocking', {employee_id: $scope.employee_id, date: $scope.clockingDate, time: time, type: $scope.clockingType, id: $scope.clockingId, overnight: $scope.overnight}, config).then(function (response) {
				if(response.data.success){
					$scope.showClockings = true;
					$('#inputboxClockings').LoadingOverlay("hide");
					$scope.getClockings($scope.employee_id, $scope.clockingDate, $scope.overnight, $scope.cut_off_time);
					showNotification("Success", response.data.msg, "success");
				}else{
					showNotification("Error", response.data.msg, "error");
					$('#inputboxClockings').LoadingOverlay("hide");
				}
			}, function (error) {
				console.log(error.data);
			});
		}

		$scope.deleteClocking = function(id){
			if(confirm("Are you sure you want to delete this clocking?")){
				$('#inputboxClockings').LoadingOverlay("show",{maxSize:50});
				$http.post(base_url + 'summary/deleteClocking', {id: id}, config).then(function (response) {
					if(response.data.success){
						$('#inputboxClockings').LoadingOverlay("hide");
						$scope.getClockings($scope.employee_id, $scope.clockingDate, $scope.overnight, $scope.cut_off_time);
						showNotification("Success", response.data.msg, "success");
					}else{
						showNotification("Error", response.data.msg, "error");
						$('#inputboxClockings').LoadingOverlay("hide");
					}
				}, function (error) {
					console.log(error.data);
				});
			}
		}

		$scope.cancelClocking = function(){
			$scope.showClockings = true;
		}

    	$scope.getSettings = function(id, date){
    		$scope.settings = {};
    		$('#settingsBox').LoadingOverlay("show",{maxSize:50});
    		$http.post(base_url + 'summary/getSettings', {id: id, date: date}, config).then(function (response) {
				$scope.settings = response.data;
    			$scope.selected_shift = response.data.shift_id;
    			$scope.prev_shift = response.data.shift_id;
    			$('#settingsBox').LoadingOverlay("hide");
				$("#replacement-date").val(response.data.replacement_leave);
				$scope.replacement_leave = response.data.replacement_leave;
				$scope.prev_replacement_leave = response.data.replacement_leave;
    		}, function (error) {
    			console.log(error.data);
    		});
    	}

    	$scope.update_shift = function(){
    		$('#settingsBox').LoadingOverlay("show",{maxSize:50});
    		$http.post(base_url + 'summary/update_shift', {shift: $scope.selected_shift, employee_id: $scope.settings.employee_id, date: $scope.settings.date}, config).then(function (response) {
    			$scope.prev_shift = $scope.selected_shift;
    			$('#settingsBox').LoadingOverlay("hide");
    			$.notify(
    				response.data.msg, 
    				{ position:"top center",
    				className: 'success',
    				style: 'bootstrap',
    				gap: 20,
    				autoHide: true
    			}
    			);
    		}, function (error) {
    			console.log(error.data);
    		});
    	}

		$scope.delete_shift = function(){
    		$('#settingsBox').LoadingOverlay("show",{maxSize:50});
    		$http.post(base_url + 'summary/delete_shift', {shift: $scope.prev_shift, employee_id: $scope.settings.employee_id, date: $scope.settings.date}, config).then(function (response) {
    			$scope.prev_shift = '';
				$scope.selected_shift = '';
    			$('#settingsBox').LoadingOverlay("hide");
    			$.notify(
    				response.data.msg, 
    				{ position:"top center",
    				className: 'success',
    				style: 'bootstrap',
    				gap: 20,
    				autoHide: true
    			}
    			);
    		}, function (error) {
    			console.log(error.data);
    		});
    	}

		$scope.refresh_shift = function(){
			$('#settingsBox').LoadingOverlay("show",{maxSize:50});
			$http.post(base_url + 'summary/refresh_shift', {employee_id: $scope.settings.employee_id, date: $scope.settings.date, shift: $scope.prev_shift}, config).then(function (response) {
				$('#settingsBox').LoadingOverlay("hide");
				$.notify(
					response.data.msg, 
					{ position:"top center",
					className: 'success',
					style: 'bootstrap',
					gap: 20,
					autoHide: true
				}
				);
			}, function (error) {
				console.log(error.data);
			});
		}

		$scope.update_replacement_date = function(){
    		$('#settingsBox').LoadingOverlay("show",{maxSize:50});
			const replacementDate = $("#replacement-date").val();
    		$http.post(base_url + 'summary/update_replacement_leave', {replacement_date: replacementDate, employee_id: $scope.settings.employee_id, date: $scope.settings.date}, config).then(function (response) {
    			$('#settingsBox').LoadingOverlay("hide");
    			$.notify(
    				response.data.msg, 
    				{ position:"top center",
    				className: response.data.success === true ?'success': 'error' ,
    				style: 'bootstrap',
    				gap: 20,
    				autoHide: true
    			}
    			);
    		}, function (error) {
    			console.log(error.data);
    		});
    	}

		$scope.delete_replacement_leave = function () {
    		$('#settingsBox').LoadingOverlay("show",{ maxSize:50 });
			$http.post(base_url + "summary/remove_replacement_leave", { employee_id: $scope.settings.employee_id, date: $scope.settings.date }, config)
				.then(function(response) {
					$('#settingsBox').LoadingOverlay("hide");
					$.notify(
						response.data.msg, 
						{
							position:"top center",
							className: response.data.success === true ?'success': 'error',
							style: 'bootstrap',
							gap: 20,
							autoHide: true
						}
					);
				}, function(error) {
					console.log(error.data);
				});
		}

    	$scope.change_status = function(type, status){
    		$('#settingsBox').LoadingOverlay("show",{maxSize:50});
    		$http.post(base_url + 'summary/change_status', {employee_id: $scope.settings.employee_id, date: $scope.settings.date, type: type, status: status}, config).then(function (response) {
    			if(type == "late_hours"){
    				$scope.settings.is_late = status;
    			}else if(type == "break_late_hours"){
    				$scope.settings.is_late_break = status;
    			}else if(type == "early_out"){
    				$scope.settings.is_early_out = status;
    			}else if(type == "overtime"){
    				$scope.settings.is_ot = status;
    			}
    			$('#settingsBox').LoadingOverlay("hide");
    			$.notify(
    				response.data.msg, 
    				{ position:"top center",
    				className: 'success',
    				style: 'bootstrap',
    				gap: 20,
    				autoHide: true
    			}
    			);
    		}, function (error) {
    			console.log(error.data);
    		});
    	}

		$scope.update_replacement_ph = function () {
    		$('#settingsBox').LoadingOverlay("show",{maxSize:50});
    		$http.post(base_url + 'summary/update_replacement_ph_status', {employee_id: $scope.settings.employee_id, date: $scope.settings.date, is_replaced_ph: $scope.settings.is_replaced_ph}, config).then(function (response) {
    			$('#settingsBox').LoadingOverlay("hide");
    			$.notify(
    				response.data.msg, 
    				{ position:"top center",
    				className: 'success',
    				style: 'bootstrap',
    				gap: 20,
    				autoHide: true
    			}
    			);
    		}, function (error) {
    			console.log(error.data);
    		});
		}
    });
</script>