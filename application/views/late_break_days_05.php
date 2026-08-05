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
        $from_url_month = $formatted_date['start_date']->format('m');
		$from_url_year = $formatted_date['start_date']->format('Y');
        
        if (!empty($from_url_month) && !empty($from_url_year)) {
            $mon = $from_url_month;
            $year = $from_url_year;
            $day = '01'; 
            $dt = $day .'-'.$mon.'-'.$year;
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
				<h4 class="page-title">Late (Break) Sheet</h4>
			</div>
		</div>
		<div class="row card-box">
			
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
                                                     
<a title="Clocking Data" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/manual_clocking_new?month=<?php echo $formatted_date['start_date']->format('m') ?>&year=<?php echo $formatted_date['start_date']->format('Y') ?>&emp=<?php echo $emp["id"] ?>"><i style="font-size:15px" class="fa fa-hourglass-half"></i></a>
 
<a title="Employee Report" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/employee_report/<?php echo $emp["id"] ?>?<?php echo "month=" . $formatted_date['start_date']->format('m') ?>&year=<?php echo $formatted_date['start_date']->format('Y') ?>"><i style="font-size:15px" class="fa fa-clock"></i></a>

<a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>summary/view/<?php echo $emp["id"].'/?from='.$start_date_f.'&to='.$end_date_f; ?>"><i style="font-size:15px" class="fa fa-address-card"></i></a>

<button title="Approve All Late (Break)" data-toggle="tooltip" class="btn btn-default btn-xs all_button" data-emp-id = "<?php echo $emp['id'];?>" data-start_date = "<?php echo $start_date ?>" data-end_date = "<?php echo $end_date ?>"><i style="font-size:15px" class="fa fa-check"></i></button>

</div>



									</td>
									<?php foreach ($emp['late_break_data'] as $ed): ?>
										<td class="text-center">
											<b class="<?php if(!$ed['is_late_break']){ echo "strike";} ?>"><?php echo $ed['late_break_time']; ?></b><br/>
											<div class="btn-group btn-group-xs" style="min-width: 45px">
												<button type="button" class="btn btn-success status_btn btn_check" <?php if($ed['is_late_break']){ echo "disabled";} ?> data-emp-id = "<?php echo $ed['id'];?>" data-late_break_date = "<?php echo $ed['day'];?>" data-is-late_break = "1">
													<span class="fa fa-check"></span>
												</button>
												<button type="button" class="btn btn-danger status_btn btn_close" <?php if(!$ed['is_late_break']){ echo "disabled";} ?> data-emp-id = "<?php echo $ed['id'];?>" data-late_break_date = "<?php echo $ed['day'];?>" data-is-late_break = "0">
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
		var day = $(this).attr('data-late_break_date');
		var is_late_break = $(this).attr('data-is-late_break');

		$.ajax({
			type: "POST",  
			url: "<?php echo base_url() ?>late_break_days/change_status",
			data: {'id' : emp_id, 'day' : day, 'is_late_break' : is_late_break},
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
		var start = $(this).attr('data-start_date');
		var end = $(this).attr('data-end_date');

		$.ajax({
			type: "POST",  
			url: "<?php echo base_url() ?>late_break_days/approve_all_late_break",
			data: {'id' : emp_id, 'start' : start, 'end' : end},
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