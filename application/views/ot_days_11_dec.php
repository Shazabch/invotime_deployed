<div class="page-wrapper">
	<style type="text/css">
		.btn.disabled, .btn[disabled], fieldset[disabled] .btn{
			opacity: 0.3
		}
		.strike{
			text-decoration: line-through;
		}
		.holiday{
			color: red;
		}
	</style>
	<?php 
        $weekly_url_id = $this->uri->segment(3);
        $from_url_month = $_GET['month'];
		$from_url_year = $_GET['year'];
		// echo $from_url_month.' / '.$from_url_year;exit;
        
        if (!empty($from_url_month) && !empty($from_url_year)) {
            // $newDate = date("m-d-Y", strtotime($from_url_date));
            // $date = strtotime($newDate);
            $mon = $from_url_month;
            $year = $from_url_year;
            $day = '01'; 
            $dt = $day .'-'.$mon.'-'.$year;
			// echo $dt;exit;
            // echo 'First day : '. date("01-m-Y", strtotime($dt)).' - Last day : '. date("t-m-Y", strtotime($dt)); 
            $from_date = strtotime(date("01-m-Y", strtotime($dt)));
            $to_date = strtotime(date("t-m-Y", strtotime($dt)));

            $mon_from = date('m', $from_date);
            $year_from = date('Y', $from_date);
            $day_from = date('d', $from_date); 
            $from_date1 = $day_from .'%2F'.$mon_from.'%2F'.$year_from;

            $mon_to = date('m', $to_date);
            $year_to = date('Y', $to_date);
            $day_to = date('d', $to_date); 
            $to_date1 = $day_to .'%2F'.$mon_to.'%2F'.$year_to;
            // echo $from_date1.' to '.$to_date1;
        }
    ?>
	<div class="content container-fluid">
		<div class="row">
			<div class="col-xs-4">
				<h4 class="page-title">OT Sheet</h4>
			</div>
		</div>
		<div class="row card-box">
			<!-- <form action="<?php echo site_url() ?>ot_days" method="get">
				<div class="col-md-2">
					<div class="form-group">
						<label>Outlet</label>
						<select  class="form-control" id="branch" name="branch">
							<option value="">All</option>
							<?php foreach ($branches as $branch): ?>
								<option <?php echo ($branch->id == $selected_branch_id) ? 'selected' : '' ?> value="<?php echo $branch->id ?>"><?php echo $branch->name ?></option>
							<?php endforeach; ?>
						</select>
					</div>

				</div>

				<div class="col-md-2">
					<div class="form-group">
						<label>Department</label>
						<select class="form-control" id="dep" name="dep">
							<option value="">All</option>
							<?php foreach ($departments as $dep): ?>
								<option <?php echo ($dep->id == $selected_dep_id) ? 'selected' : '' ?> value="<?php echo $dep->id ?>"><?php echo $dep->name ?></option>
							<?php endforeach; ?>

						</select>
					</div>

				</div>

				<div class="col-md-2">
					<div class="form-group">
						<label>Month</label>
						<select class="form-control" id="sel1" name="month">
							<option <?php echo ('01' == $selected_month) ? 'selected' : '' ?> value="01">January</option>
							<option <?php echo ('02' == $selected_month) ? 'selected' : '' ?> value="02">February</option>
							<option <?php echo ('03' == $selected_month) ? 'selected' : '' ?> value="03">March</option>
							<option <?php echo ('04' == $selected_month) ? 'selected' : '' ?> value="04">April</option>
							<option <?php echo ('05' == $selected_month) ? 'selected' : '' ?> value="05">May</option>
							<option <?php echo ('06' == $selected_month) ? 'selected' : '' ?> value="06">June</option>
							<option <?php echo ('07' == $selected_month) ? 'selected' : '' ?> value="07">July</option>
							<option <?php echo ('08' == $selected_month) ? 'selected' : '' ?> value="08">August</option>
							<option <?php echo ('09' == $selected_month) ? 'selected' : '' ?> value="09">September</option>
							<option <?php echo ('10' == $selected_month) ? 'selected' : '' ?> value="10">October</option>
							<option <?php echo ('11' == $selected_month) ? 'selected' : '' ?> value="11">November</option>
							<option <?php echo ('12' == $selected_month) ? 'selected' : '' ?> value="12">December</option>
						</select>
					</div>                                               
				</div>

				<div class="col-md-2">
			      <div class="form-group">
			        <label for="sel1">Year</label>
			        <select class="form-control" id="sel1" name="year">
			          <option <?php echo ('2019' == $selected_year) ? 'selected' : '' ?> value="2019">2019</option>
			          <option <?php echo ('2020' == $selected_year) ? 'selected' : '' ?> value="2020">2020</option>
			        </select>
			      </div>                                               
			  </div>

				<div class="col-md-3">
					<label>&nbsp;</label>
					<button class="btn btn-primary btn-block">Filter</button>

				</div>
			</form> -->
			<?php echo $filters; ?>

			<div class="col-md-12">
				<div class="mycontainer">
					<div class="table-responsive freeze-table">
						<table style="font-size: 13px" class="table table-striped">
							<thead>
								<tr>
									
									<th style="font-size: 13px;">Name</th>
									<?php foreach ($days as $d): ?>
										<th style="font-size: 11px;" class="text-center <?php if($d['holiday']){ echo "holiday";} ?>">
											<span <?php if($d['holiday']){ echo "data-toggle='tooltip' data-html='true' data-placement='top' data-original-title='".$d['holiday_name']."'";} ?>>
											<b><?php echo $d['date']; ?></b><br/>
											<?php echo $d['day']; ?>
										</span>
										</th>
									<?php endforeach;?>

								</tr>

							</thead>
							<tbody>
								<?php foreach ($employees as $emp): ?>
									<tr>
										<td><b>
											<a href="<?php echo base_url() ?>overview/employee_report/<?php echo $emp['id']; ?>">
												<?php echo $emp['first_name']; ?>
											</a>

										</b><br/> <?php echo $emp['special_id']; ?>

										<br/>
                                                        
                                                        <div style="min-width:150px !important">
                                                     
<a title="Clocking Data" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/manual_clocking_new?month=<?php echo $selected_month ?>&year=<?php echo $selected_year ?>&emp=<?php echo $emp["id"] ?>"><i style="font-size:15px" class="fa fa-hourglass-half"></i></a>
 
<a title="Employee Report" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/employee_report/<?php echo $emp["id"] ?>?<?php echo "month=" . $selected_month ?>&year=<?php echo $selected_year ?>"><i style="font-size:15px" class="fa fa-clock"></i></a>

<a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>summary/view/<?php echo $emp["id"].'/?from='.$from_date1.'&to='.$to_date1; ?>"><i style="font-size:15px" class="fa fa-address-card"></i></a>

<button title="Approve All OT" data-toggle="tooltip" class="btn btn-default btn-xs all_button" data-emp-id = "<?php echo $emp['id'];?>" data-month = "<?php echo $selected_month;?>" data-year = "<?php echo $selected_year;?>"><i style="font-size:15px" class="fa fa-check"></i></button>

</div>



									</td>
									<?php foreach ($emp['ot_data'] as $ed): ?>
										<td class="text-center">
											<b class="<?php if(!$ed['is_ot']){ echo "strike";} ?>"><?php echo $ed['overtime']; ?></b><br/>
											<span style="color: red; font-weight: bold;"><?php echo $ed['overtime_m']; ?></span><br>
											
											<div class="btn-group btn-group-xs" style="min-width: 45px">
												<button type="button" class="btn btn-success status_btn btn_check" <?php if($ed['is_ot']){ echo "disabled";} ?> data-emp-id = "<?php echo $ed['id'];?>" data-ot_date = "<?php echo $ed['day'];?>" data-is-ot = "1">
													<span class="fa fa-check"></span>
												</button>
												<button type="button" class="btn btn-danger status_btn btn_close" <?php if(!$ed['is_ot']){ echo "disabled";} ?> data-emp-id = "<?php echo $ed['id'];?>" data-ot_date = "<?php echo $ed['day'];?>" data-is-ot = "0">
													<span class="fa fa-close"></span>
												</button>
											</div>
										</td>
									<?php endforeach; ?>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div class="col-md-12">
            <nav style="float:right" aria-label="Page navigation example">
            <ul class="pagination ">

              <?php if(isset($page) && $page > 1): ?>
                <li class="page-item"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $page - 1 ?>">Previous</a></li>
              <?php endif; ?>


              <?php if(isset($total_pages)): for ($x = 1; $x <= $total_pages; $x++):

                if($page == $x){
                  $active = "active";
                }
                else{
                    $active = "";
                }

                ?>
              <li class="page-item <?php echo $active ?>"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $x ?>"><?php echo $x ?></a></li>

              <?php endfor; endif; ?>

              <?php if(isset($page) && isset($total_pages) && $page < $total_pages): ?>
                <li class="page-item"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $page + 1 ?>">Next</a></li>
              <?php endif; ?>

            </ul>
          </nav>
        </div>

	</div>
</div>
</div>

<script type="text/javascript">

	$(".status_btn").on("click", function(e) {
		var btn = $(this);
		var emp_id = $(this).attr('data-emp-id');
		var day = $(this).attr('data-ot_date');
		var is_ot = $(this).attr('data-is-ot');

		$.ajax({
			type: "POST",  
			url: "<?php echo base_url() ?>ot_days/change_status",
			data: {'id' : emp_id, 'day' : day, 'is_ot' : is_ot},
			success: function (result) {
				btn.prop("disabled", true);
				btn.siblings().prop("disabled", false);
				btn.parent().siblings('b').toggleClass('strike');
			}

		});
	});

	$(".all_button").on("click", function(e) {
		var btn = $(this);
		var emp_id = $(this).attr('data-emp-id');
		var month = $(this).attr('data-month');
		var year = $(this).attr('data-year');

		$.ajax({
			type: "POST",  
			url: "<?php echo base_url() ?>ot_days/approve_all_ot",
			data: {'id' : emp_id, 'month' : month, 'year' : year},
			success: function (result) {
				btn.closest('td').siblings().find('.btn_close').prop("disabled", false);
				btn.closest('td').siblings().find('.btn_check').prop("disabled", true);
			}

		});
		
	});



	


	$(document).ready(function(){

		$(".freeze-table").freezeTable({
			'columnNum' : 1,
			'shadow': true,
			'fixedNavbar':'.header',
			'scrollBar':true

		});


	});
</script>