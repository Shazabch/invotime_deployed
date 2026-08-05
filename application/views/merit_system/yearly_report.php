<div class="page-wrapper">
	<style type="text/css">
		.btn.disabled,
		.btn[disabled],
		fieldset[disabled] .btn {
			opacity: 0.3
		}

		.strike {
			text-decoration: line-through;
			text-decoration-color: red;
		}

		.holiday {
			color: red;
		}
	</style>
	<div class="content container-fluid">
		<div class="row">
			<div class="col-xs-4">
				<h4 class="page-title">Merit Sheet Yearly Report</h4>
			</div>
		</div>
		<div class="row card-box">
			<?php echo $filters; ?>
			<div class="col-md-12">
				<a class="btn btn-primary m-b-10" target="_blank" href="<?php echo $merit_system_export_url ?>">Export as PDF</a>
			</div>

			<div class="col-md-12">
				<div class="mycontainer">
					<div class="table-responsive freeze-table">
						<table style="font-size: 13px" class="table table-striped">
							<thead>
								<tr>
									<th style="font-size: 13px;">Name</th>
									<th style="font-size: 13px;">Points</th>
									<th style="font-size: 13px;">Grade</th>
									<th style="width: 60px;">January</th>
									<th style="width: 60px;">February</th>
									<th style="width: 60px;">March</th>
									<th style="width: 60px;">April</th>
									<th style="width: 60px;">May</th>
									<th style="width: 60px;">June</th>
									<th style="width: 60px;">July</th>
									<th style="width: 60px;">August</th>
									<th style="width: 60px;">September</th>
									<th style="width: 60px;">October</th>
									<th style="width: 60px;">November</th>
									<th style="width: 60px;">December</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($employees as $emp) : ?>
									<tr>
										<td><b>
												<?php if (is_page_permitted('employee_report')) : ?>
													<a href="<?php echo base_url() ?>overview/employee_report/<?php echo $emp->id; ?>">
												<?php endif ?>
												<?php echo $emp->first_name; ?>
												<?php if (is_page_permitted('employee_report')) : ?>
													</a>
												<?php endif ?>
											</b><br /> <?php echo $emp->special_id; ?>
										</td>
										<td><?php echo $emp->average_merit_points ?></td>
										<td><?php echo $emp->grade ?></td>
										<?php for ($i = 0; $i < 12; $i++) : ?>
											<td class="text-center"><?php echo isset($emp->merit_points[$i]) ? $emp->merit_points[$i]->points : "" ?></td>
										<?php endfor ?>
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

						<?php if (isset($page) && $page > 1) : ?>
							<li class="page-item"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $page - 1 ?>">Previous</a></li>
						<?php endif; ?>


						<?php if (isset($total_pages)) : for ($x = 1; $x <= $total_pages; $x++) :

								if ($page == $x) {
									$active = "active";
								} else {
									$active = "";
								}

						?>
								<li class="page-item <?php echo $active ?>"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $x ?>"><?php echo $x ?></a></li>

						<?php endfor;
						endif; ?>

						<?php if (isset($page) && isset($total_pages) && $page < $total_pages) : ?>
							<li class="page-item"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $page + 1 ?>">Next</a></li>
						<?php endif; ?>

					</ul>
				</nav>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$(".freeze-table").freezeTable({
			'columnNum': 1,
			'shadow': true,
			'fixedNavbar': '.header',
			'scrollBar': true
		});
	});
</script>