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
							<h3><?php echo $employee->first_name; ?> - Summary <button class="btn btn-primary" onclick="window.print()">Print</button>

								<a class="btn btn-primary" target="_blank" href="<?php echo base_url() ?>summary/pdf/<?php echo $employee->emp_id ?>/<?php echo $from_p ?>/<?php echo $to_p ?>">Export as PDF</a>
								<a class="btn btn-primary" href="<?php echo base_url() ?>overview/manual_clocking_new?month=<?php echo date("m") ?>&emp=<?php echo $employee->emp_id ?>">Clocking Data</a>

							</h3>
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
									<th>OT</th>
									<th>Reason</th>
									<th>Status</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($dates as $d){ ?>
									<?php foreach($d->clockings as $key => $clock){ ?>
										<tr>
											<?php if($key == 0){ ?>
												<td rowspan="<?php echo count($d->clockings);?>" class="text-center <?php if(in_array($d->date,$public_holidays)){echo 'holiday';} ?>" style="vertical-align: middle"><b><?php echo $clock->day_f; ?></b></td>
											<?php } ?>
											<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>"><?php if($key%2 != 1){ echo $clock->name;}else{
												echo "Break";
											} ?>  </td>
											<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>"><?php echo $clock->clock_in; ?></td>
											<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>"><?php echo $clock->clock_out; ?></td>
											<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>"><?php if($clock->clock_out == "") {echo ""; }else{ echo $clock->total_time; }?></td>
											<?php if($key == 0){ ?>
												<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php echo $d->total_hours; ?></td>
												<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php echo $d->work_hours; ?></td>
												<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php if($d->break_hours != "00:00"){echo $d->break_hours;} ?></td>
											
											<td rowspan="<?php echo count($d->clockings);?>" class="text-center <?php if(!$d->is_ot){echo 'strike';} ?>" style="vertical-align: middle"><?php echo $d->overtime; ?></td>
											<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php echo $clock->reason; ?></td>
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
											<td class="text-center"><?php echo $month_overtime;?></td>
											<td colspan="2"></td>
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