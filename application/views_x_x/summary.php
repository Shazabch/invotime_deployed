<div class="page-wrapper">
	<style type="text/css">
		.strike{
			text-decoration: line-through;
		}
	</style>
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
								<select class="form-control apply-select2" id="emp" name="emp">
									<?php foreach ($employees_dropdown as $emp): ?>
										<option <?php echo ($emp->id == $employee->emp_id) ? 'selected' : '' ?> value="<?php echo $emp->id ?>"><?php echo $emp->special_id . " - " . $emp->first_name ?></option>
									<?php endforeach; ?>

								</select>
							</div>
						</div>
						<div class="col-md-8">

							<button class="btn btn-primary" onclick="window.print()">Print</button>

							<a class="btn btn-primary" target="_blank" href="<?php echo base_url() ?>summary/pdf/<?php echo $employee->emp_id ?>/<?php echo $from_p ?>/<?php echo $to_p ?>">Export as PDF</a>
							<a class="btn btn-primary" target="_blank" href="<?php echo base_url() ?>summary/excel/<?php echo $employee->emp_id ?>/<?php echo $from_p ?>/<?php echo $to_p ?>">Export as Excel</a>
							<a class="btn btn-primary" href="<?php echo base_url() ?>overview/manual_clocking_new?month=<?php echo date("m") ?>&emp=<?php echo $employee->emp_id ?>">Clocking Data</a>

							
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
						<form method="get" action="<?php echo base_url();?>summary/view/<?php echo $emp_id;?>">
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
									<th>Date</th>
									<th>Shift</th>
									<th>Clock in</th>
									<th>Clock out</th>
									<th>Hours</th>
									<th>Total Hours</th>
									<th>Work Hours</th>
									<th>Break Hours</th>
									<th>Late Hours</th>
									<th>OT</th>
									<th>OT(M)</th>
									<th>Days</th>
									<th>Reason</th>
									<th>Remark</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($dates as $d){ ?>
									<?php foreach($d->clockings as $key => $clock){ ?>
										<tr>
											<?php if($key == 0){ ?>
												<td rowspan="<?php echo count($d->clockings);?>" class="text-center <?php if(in_array($d->date,$public_holidays)){echo 'holiday';} ?>" style="vertical-align: middle"><b <?php if (in_array($d->date, $public_holidays)){echo "data-toggle='tooltip' data-html='true' data-placement='top' data-original-title='".$public_holidays_names[array_search($d->date,$public_holidays)]."'";} ?>><?php echo $clock->day_f; ?></b><br>
													<button class="btn btn-xs btn-info" data-toggle="modal" data-target="#editClockingXCRUD" id="editClockingBtn" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>"><i class="fa fa-edit"></i></button>
												</td>
											<?php } ?>
											<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>" style="vertical-align: middle"><?php if($key%2 != 1){ echo $clock->name;}else{
												echo "Break";
											} ?>  </td>
											<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>" style="vertical-align: middle">
											<!-- <?php if(!empty($clock->clock_in) && !$clock->is_break){ ?>
												<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" data-clocking="<?php echo $clock->clock_in ?>" data-id="<?php echo $clock->clock_in_id ?>" type="button" class="btn btn-default btn-xs btn-clocking" data-toggle="modal" data-target="#editClockingModal">
													<?php echo $clock->clock_in; ?>

												</button>
												<span class="show-on-print" style="display: none;"><?php echo $clock->clock_in; ?></span>
												<?php } else{ echo $clock->clock_in; } ?> -->
													<?php echo $clock->clock_in; ?>
												</td>
												<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>" style="vertical-align: middle">
												<!-- <?php if(!empty($clock->clock_out) && !$clock->is_break){ ?>
												<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" data-clocking="<?php echo $clock->clock_out ?>" data-id="<?php echo $clock->clock_out_id ?>" type="button" class="btn btn-default btn-xs btn-clocking" data-toggle="modal" data-target="#editClockingModal">
													<?php echo $clock->clock_out; ?>

												</button>
												<span class="show-on-print" style="display: none;"><?php echo $clock->clock_out; ?></span>
												<?php } else{ echo $clock->clock_out; } ?> -->
													<?php echo $clock->clock_out; ?>
												</td>
												<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>" style="vertical-align: middle"><?php if($clock->clock_out == "") {echo ""; }else{ echo $clock->total_time; }?></td>
												<?php if($key == 0){ ?>
													<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php echo $d->total_hours; ?></td>
													<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php echo $d->work_hours; ?></td>
													<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php if($d->break_hours != "00:00"){echo $d->break_hours;} ?></td>
													<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php if($d->late_hours != "00:00"){echo $d->late_hours;} ?></td>

													<td rowspan="<?php echo count($d->clockings);?>" class="text-center <?php if(!$d->is_ot || $d->is_manual_exist){echo 'strike';} ?>" style="vertical-align: middle"><span class="otspan <?php if($d->is_ot && !$d->is_manual_exist){ echo 'countOT';}?>"><?php echo $d->overtime; ?></span></td>
													<td rowspan="<?php echo count($d->clockings);?>" class="text-center <?php if(!$d->is_ot){echo 'strike';} ?>" style="vertical-align: middle"><span class="<?php if($d->is_ot && $d->is_manual_exist){ echo 'countOT';}?>" style="display: none"><?php echo $d->overtime_m; ?></span><?php if(!in_array($d->date,$public_holidays)){ ?><button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editButton" data-toggle="modal" data-target="#editOvertimeModal" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>" data-overtime="<?php echo $d->overtime_m;?>"><?php if(empty(!$d->overtime_m)){ echo $d->overtime_m; }else{ ?><i class="fa fa-plus"></i> <?php } ?></button><?php } ?>
													<span class="show-on-print" style="display: none;"><?php echo $d->overtime_m; ?></span>
												</td>
													<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php echo $d->days; ?></td>
													<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php if( isset($clock->clock_in_o) && beautify_time($clock->clock_in_o) > beautify_time($clock->grace_time_o)): ?>

													<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" id="btn-reason-<?php echo $clock->id ?>" data-reason="<?php echo $clock->reason ?>" data-id="<?php echo $clock->id ?>" type="button" class="btn btn-default btn-xs" aria-label="Add reason" data-toggle="modal" data-target="#reason-modal">
														<!-- <span class="fa fa-comment" aria-hidden="true"></span> -->
														<?php if(empty($clock->reason)): ?>

															<span class="fa fa-plus" aria-hidden="true"></span>

															<?php else: ?>

																<?php echo $clock->reason ?>

															<?php endif; ?>

														</button>

														<?php endif; ?>
														<span class="show-on-print" style="display: none;"><?php echo $clock->reason; ?></span>
													</td>
														<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle">
															<?php if(isset($clock->id)): ?>
																<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" id="btn-remark-<?php echo $clock->id ?>" data-remark="<?php echo $clock->remark ?>" data-id="<?php echo $clock->id ?>" type="button" class="btn btn-default btn-xs" aria-label="Add remark" data-toggle="modal" data-target="#remark-modal">
																	<!-- <span class="fa fa-comment" aria-hidden="true"></span> -->
																	<?php if(empty($clock->remark)): ?>

																		<span class="fa fa-plus" aria-hidden="true"></span>

																		<?php else: ?>

																			<?php echo $clock->remark ?>

																		<?php endif; ?>

																	</button>
																<?php else: ?>
																	<?php echo $clock->remark ?>
																<?php endif; ?>
																<span class="show-on-print" style="display: none;"><?php echo $clock->remark; ?></span>
															</td>
															<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"></td>
														<?php } ?>
													</tr>

												<?php }} ?>
												<?php if($total != "00:00" || $work != "00:00" || $break != "00:00"){ ?>
													<tr>
														<td colspan="4"></td>
														<td class="text-center"><b>Total</b></td>
														<td class="text-center"><?php echo $total;?></td>
														<td class="text-center"><?php echo $work;?></td>
														<td class="text-center"><?php echo $break;?></td>
														<td class="text-center"><?php echo $late;?></td>
														<td class="text-center month-overtime" colspan="2"><?php echo $month_overtime;?></td>
														<td class="text-center"><?php echo $total_days;?></td>
														<td colspan="3"></td>
													</tr>
												<?php } ?>
											</tbody>
										</table>
									</div>
								</div>
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
							<div class="modal-body" id="inputbox">
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


						$(".apply-select2").change(function(){
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
		window.location = js_base_url + 'summary/view/' + selectedValue+params;
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
			// $(document).on('mouseenter', '.freeze-table table:first .manualTD', function(){
			// 	$(this).children('.editButton').show();
			// });
			// $(document).on('mouseleave', '.freeze-table table:first .manualTD', function(){
			// 	$(this).children('.editButton').hide();
			// });
			$(document).on('click', '.freeze-table table:first .editButton', function(){
				$('#removeBtn').hide();
				element = $(this);
				editData = $(this).data();
				oldTime = editData.overtime.split(':');
				hours = 0;
				minutes = 0;
				if(oldTime.length != 1){
					hours = oldTime[0];
					minutes = oldTime[1];
					$('#removeBtn').show();
				}
				
				$('.datetimepicker2').data("DateTimePicker").date(new Date(1979, 0, 1, hours, minutes, 0, 0));
				$('#empid').val(editData.empid);
				$('#date').val(editData.date);
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
				total_hours = 0;
				total_minutes = 0;
				$.ajax({
					type: "POST",  
					url: "<?php echo base_url() ?>summary/updateOT",
					data: {'data':formdata},
					success: function (result) {
						$("#inputbox").LoadingOverlay("hide");
						$('#editOvertimeModal').modal('hide');

						element.siblings('span').html(formdata[0]['value']);
						element.siblings('span').addClass('countOT');
						element.closest('td').prev().addClass('strike');
						element.closest('td').removeClass('strike');
						element.closest('td').prev().children('.otspan').removeClass('countOT');
						element.attr('data-overtime', formdata[0]['value']);
						element.data('overtime', formdata[0]['value']);
						element.html(formdata[0]['value']);
						$(".freeze-table table:first .countOT").each(function() {
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
						$('.month-overtime').html(total_hours + ':' + total_minutes);
						if(result){
							$.notify(
								"Success: overtime changed successfully!", 
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
				total_hours = 0;
				total_minutes = 0;
				$.ajax({
					type: "POST",  
					url: "<?php echo base_url() ?>summary/deleteOT",
					data: {'data':formdata},
					success: function (result) {
						$("#inputbox").LoadingOverlay("hide");
						$('#editOvertimeModal').modal('hide');

						element.siblings('span').html('');
						element.closest('td').prev().removeClass('strike');
						element.closest('td').prev().children('.otspan').addClass('countOT');
						element.attr('data-overtime', '');
						element.data('overtime', '');
						$(".freeze-table table:first .countOT").each(function() {
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
						$('.month-overtime').html(total_hours + ':' + total_minutes);
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
				'fixedNavbar':'.header'

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

				$('#editClockingXCRUD').on('show.bs.modal', function (event) {
					var emp_id = $(event.relatedTarget).attr('data-empid');
					var date = $(event.relatedTarget).attr('data-date');
					$('#xcrudBox').html('');
					$("#modalBox").LoadingOverlay("show");
					$.ajax({
						type: "GET",  
						url: "<?php echo base_url() ?>summary/getXCRUD",
						data: {'emp_id':emp_id, 'date':date},
						success: function (result) {
                           //do somthing here
                           $("#modalBox").LoadingOverlay("hide");

                           if(result){
                           	$('#xcrudBox').html(result);

                           }
                       }
                   });
					
				});

				$('#remark-modal').on('show.bs.modal', function (event) {
					var id = $(event.relatedTarget).attr('data-id');
					var remark = $(event.relatedTarget).attr('data-remark');
					$(this).find("#remark-id").val(id);
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

					$.ajax({
						type: "GET",  
						url: "<?php echo base_url() ?>overview/save_remark",
						data: {'id':id, 'remark':remark},
						success: function (result) {
                           //do somthing here
                           $("#btn-remark-save").LoadingOverlay("hide");

                           if(result){

                           	$('#remark-modal').modal("hide");

                           	$('#btn-remark-'+id).text(remark);
                           	$('#btn-remark-'+id).attr("data-remark",remark);

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

					$.ajax({
						type: "GET",  
						url: "<?php echo base_url() ?>overview/save_remark",
						data: {'id':id, 'remark':''},
						success: function (result) {
                           //do somthing here
                           $("#btn-remark-delete").LoadingOverlay("hide");

                           if(result){

                           	$('#remark-modal').modal("hide");

                           	$('#btn-remark-'+id).html('<span class="fa fa-plus" aria-hidden="true"></span>');
                           	$('#btn-remark-'+id).attr("data-remark",'');
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
		</script>

		