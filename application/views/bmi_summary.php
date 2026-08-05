<div class="page-wrapper">
	<style type="text/css">
		.strike{
			text-decoration: line-through;
		}
		.btn.disabled, .btn[disabled], fieldset[disabled] .btn{
			opacity: 0.3
		}
	</style>
	<div class="content container-fluid">
		<div class="row">
			<div class="col-md-12">
				<div class="card-box">
					<div class="row">
						<div class="col-md-12">
							<h3>BMI Summary</h3>
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
						<form method="get" action="<?php echo base_url();?>bmi_summary/bmi_view/<?php echo $emp_id;?>/<?php echo $selected_department; ?>">
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
									<th class="text-center" style="min-width: 70px;">Date</th>
									<th class="text-center">Acting</th>
									<th class="text-center">Shift</th>
									<th class="text-center">In</th>
									<th class="text-center">Out</th>
									<th class="text-center">WD</th>
									<th class="text-center">OT</th>
									<th class="text-center">Sun</th>
									<th class="text-center">PH < 8</th>
									<th class="text-center">PH > 8</th>
									<th class="text-center">TA<br>(R - <?php echo number_format($employee->ta_rate, 2) ?>)</th>
									<th class="text-center">MA<br>(R - <?php echo number_format($employee->ma_rate, 2) ?>)</th>
									<th class="text-center">CA<br>(R - <?php echo number_format($employee->ca_rate, 2) ?>)</th>
									<th class="text-center">SPA<br>(R - <?php echo number_format($employee->spa_rate, 2) ?>)</th>
									<th class="text-center">ACA<br>(R - <?php echo number_format($employee->aca_rate, 2) ?>)</th>
									<th class="text-center">FL Inc<br>(R - <?php echo number_format($employee->spa_rate, 2) ?>)</th>
									<th class="text-center">C/wash<br>(R - <?php echo number_format($employee->spa_rate, 2) ?>)</th>
									<th class="text-center">M/ope<br>(R - <?php echo number_format($employee->spa_rate, 2) ?>)</th>
									<th class="text-center">Shift1<br>(R - <?php echo number_format($employee->shift1_rate, 2) ?>)</th>
									<th class="text-center">Shift2<br>(R - <?php echo number_format($employee->shift2_rate, 2) ?>)</th>
									<th class="text-center">Shift3<br>(R - <?php echo number_format($employee->shift3_rate, 2) ?>)</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($dates as $d){ ?>
									<?php foreach($d->clockings as $key => $clock){ ?>
										<tr>
											<?php if($key == 0){ ?>
												<td class="text-center <?php if(in_array($d->date,$public_holidays) || $d->is_replaced_ph){echo 'holiday';} ?>"><b <?php if (in_array($d->date, $public_holidays)){echo "data-toggle='tooltip' data-html='true' data-placement='top' data-original-title='".$public_holidays_names[array_search($d->date,$public_holidays)]."'";} ?>><?php echo $clock->day_f; ?></b>
										
												</td>
												<td class="text-center"><?php echo $d->acting_code; ?></td>
												<td class="text-center"><?php echo $d->shift_name; ?></td>
												<td class="text-center"><?php echo $d->first_in; ?></td>
												<td class="text-center"><?php echo $d->last_out; ?></td>
												<td class="text-center"><?php echo $d->days; ?></td>
												<td class="text-center"><?php echo $d->bmi_ot; ?></td>
												<td class="text-center"><?php echo $d->bmi_ot_sunday; ?></td>
												<td class="text-center"><?php echo $d->bmi_ph_1; ?></td>
												<td class="text-center"><?php echo $d->bmi_ph_2; ?></td>
												<td class="text-center">
													<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editButton" data-toggle="modal" data-target="#editModal" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>" data-value="<?php echo $d->bmi_ta_final;?>" data-value_original="<?php echo $d->bmi_ta;?>" data-value_manual="<?php echo $d->bmi_ta_manual;?>" data-type="ta"><?php if(!empty($d->bmi_ta_final) && $d->bmi_ta_final != "0.00"){ echo $d->bmi_ta_final; }else{ ?><i class="fa fa-plus"></i> <?php } ?></button>
												</td>
												<td class="text-center">
													<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editButton" data-toggle="modal" data-target="#editModal" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>" data-value="<?php echo $d->bmi_ma_final;?>" data-value_original="<?php echo $d->bmi_ma;?>" data-value_manual="<?php echo $d->bmi_ma_manual;?>" data-type="ma"><?php if(!empty($d->bmi_ma_final) && $d->bmi_ma_final != "0.00"){ echo $d->bmi_ma_final; }else{ ?><i class="fa fa-plus"></i> <?php } ?></button>
												</td>
												<td class="text-center">
													<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editButton" data-toggle="modal" data-target="#editModal" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>" data-value="<?php echo $d->bmi_ca_final;?>" data-value_original="<?php echo $d->bmi_ca;?>" data-value_manual="<?php echo $d->bmi_ca_manual;?>" data-type="ca"><?php if(!empty($d->bmi_ca_final) && $d->bmi_ca_final != "0.00"){ echo $d->bmi_ca_final; }else{ ?><i class="fa fa-plus"></i> <?php } ?></button>
												</td>
												<td class="text-center">
													<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editButton" data-toggle="modal" data-target="#editModal" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>" data-value="<?php echo $d->bmi_spa_final;?>" data-value_original="<?php echo $d->bmi_spa;?>" data-value_manual="<?php echo $d->bmi_spa_manual;?>" data-type="spa"><?php if(!empty($d->bmi_spa_final) && $d->bmi_spa_final != "0.00"){ echo $d->bmi_spa_final; }else{ ?><i class="fa fa-plus"></i> <?php } ?></button>
												</td>
												<td class="text-center">
													<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editButton" data-toggle="modal" data-target="#editModal" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>" data-value="<?php echo $d->bmi_aca_final;?>" data-value_original="<?php echo $d->bmi_aca;?>" data-value_manual="<?php echo $d->bmi_aca_manual;?>" data-type="aca"><?php if(!empty($d->bmi_aca_final) && $d->bmi_aca_final != "0.00"){ echo $d->bmi_aca_final; }else{ ?><i class="fa fa-plus"></i> <?php } ?></button>
												</td>
												<td class="text-center">
													<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editButton" data-toggle="modal" data-target="#editModal" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>" data-value="<?php echo $d->bmi_fl_final;?>" data-value_original="<?php echo $d->bmi_fl;?>" data-value_manual="<?php echo $d->bmi_fl_manual;?>" data-type="fl"><?php if(!empty($d->bmi_fl_final) && $d->bmi_fl_final != "0.00"){ echo $d->bmi_fl_final; }else{ ?><i class="fa fa-plus"></i> <?php } ?></button>
												</td>
												<td class="text-center">
													<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editButton" data-toggle="modal" data-target="#editModal" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>" data-value="<?php echo $d->bmi_cw_final;?>" data-value_original="<?php echo $d->bmi_cw;?>" data-value_manual="<?php echo $d->bmi_cw_manual;?>" data-type="cw"><?php if(!empty($d->bmi_cw_final) && $d->bmi_cw_final != "0.00"){ echo $d->bmi_cw_final; }else{ ?><i class="fa fa-plus"></i> <?php } ?></button>
												</td>
												<td class="text-center">
													<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editButton" data-toggle="modal" data-target="#editModal" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>" data-value="<?php echo $d->bmi_mo_final;?>" data-value_original="<?php echo $d->bmi_mo;?>" data-value_manual="<?php echo $d->bmi_mo_manual;?>" data-type="mo"><?php if(!empty($d->bmi_mo_final) && $d->bmi_mo_final != "0.00"){ echo $d->bmi_mo_final; }else{ ?><i class="fa fa-plus"></i> <?php } ?></button>
												</td>
												<td class="text-center">
													<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editButton" data-toggle="modal" data-target="#editModal" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>" data-value="<?php echo $d->bmi_shift1_final;?>" data-value_original="<?php echo $d->bmi_shift1;?>" data-value_manual="<?php echo $d->bmi_shift1_manual;?>" data-type="shift1"><?php if(!empty($d->bmi_shift1_final) && $d->bmi_shift1_final != "0.00"){ echo $d->bmi_shift1_final; }else{ ?><i class="fa fa-plus"></i> <?php } ?></button>
												</td>
												<td class="text-center">
													<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editButton" data-toggle="modal" data-target="#editModal" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>" data-value="<?php echo $d->bmi_shift2_final;?>" data-value_original="<?php echo $d->bmi_shift2;?>" data-value_manual="<?php echo $d->bmi_shift2_manual;?>" data-type="shift2"><?php if(!empty($d->bmi_shift2_final) && $d->bmi_shift2_final != "0.00"){ echo $d->bmi_shift2_final; }else{ ?><i class="fa fa-plus"></i> <?php } ?></button>
												</td>
												<td class="text-center">
													<button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" class="btn btn-default btn-xs editButton" data-toggle="modal" data-target="#editModal" data-date="<?php echo $d->date;?>" data-empid="<?php echo $employee->emp_id;?>" data-value="<?php echo $d->bmi_shift3_final;?>" data-value_original="<?php echo $d->bmi_shift3;?>" data-value_manual="<?php echo $d->bmi_shift3_manual;?>" data-type="shift3"><?php if(!empty($d->bmi_shift3_final) && $d->bmi_shift3_final != "0.00"){ echo $d->bmi_shift3_final; }else{ ?><i class="fa fa-plus"></i> <?php } ?></button>
												</td>
											</tr>

												<?php }}} ?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div id="editModal" class="modal fade" role="dialog">
					<div class="modal-dialog modal-sm">

						<!-- Modal content-->
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Edit Allowance</h4>
							</div>
							<div class="modal-body" id="inputbox">
								<div class="row">
									<form id="editForm">
										<div class="col-md-12">
											<div class="form-group">
												<label class="control-label">Allowance <span class="text-danger">*</span></label>
												<input class="form-control" type="number" id="allowance_value" required="" name="allowance_value" autocomplete="off">
											</div>
										</div>

										<input type="hidden" name="empid" id="empid">
										<input type="hidden" name="date" id="date">
										<input type="hidden" name="type" id="type">
										<input type="hidden" name="remove" id="remove">
										<div class="col-md-12">
											<div class="form-group">
												<button class="btn btn-primary" type="submit">Update</button>
												<button style="display: none;" class="btn btn-danger" id="removeButton" type="button">Reset</button>
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

		window.location = js_base_url + 'bmi_summary/bmi_view/' + selectedValue+'/' + selectedDepartment + '/' +params;
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

		window.location = js_base_url + 'bmi_summary/bmi_view/0/' + selectedValue+params;
	});


					});

				</script>

<script>

	$(document).ready(function(){
		$(".freeze-table").freezeTable({
          'columnNum' : 1,
          'shadow': true,
          'fixedNavbar':'.header',
          'scrollBar': true
        });


		$('#from').val('<?php echo $from_f; ?>');
		$('#to').val('<?php echo $to_f; ?>');
	})

	var element = null;

	$(document).on('click', '.freeze-table table:first .editButton', function(){
		element = $(this);
		editData = $(this).data();

		if(editData.type == "ta"){
			$('.modal-title').html('Edit TA Allowance');
		}

		$('#empid').val(editData.empid);
		$('#date').val(editData.date);
		$('#type').val(editData.type);
		if(editData.value != "0.00"){
			$('#allowance_value').val(editData.value);
		}else{
			$('#allowance_value').val('');
		}
		if(editData.value_manual == ""){
			$('#removeButton').hide();
		}else{
			$('#removeButton').show();
		}
		$('#remove').val("");
	});

	$("#editForm").submit(function(e){
		$("#inputbox").LoadingOverlay("show");
		e.preventDefault();
		formdata = $(this).serializeArray();

		$.ajax({
			type: "POST",  
			url: "<?php echo base_url() ?>bmi_summary/updateAllowance",
			data: {'data':formdata},
			success: function (result) {
				$("#inputbox").LoadingOverlay("hide");
				$('#editModal').modal('hide');

				if(result){
					result = JSON.parse(result);
					if(result.removed){
						if(element.data('value_original') == "" || element.data('value_original') == "0.00"){
							element.html('<i class="fa fa-plus"></i>');
						}else{
							element.html(element.data('value_original'));
						}
						element.data('value', element.data('value_original'));
						element.data('value_manual', '');
					}else{
						if(result.value == "0.00"){
							element.html('<i class="fa fa-plus"></i>');
						}else{
							element.html(result.value);
						}
						element.data('value', result.value);
						element.data('value_manual', result.value);
					}
					
					$.notify(
						"Success: " + result.msg, 
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

	$(document).on('click', '#removeButton', function(){
		$('#remove').val("yes");
		$('#editForm').submit();
		
	});
</script>

		

		
