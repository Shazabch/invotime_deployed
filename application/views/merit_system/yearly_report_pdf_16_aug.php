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

		body {
			font-family: 'Montserrat', sans-serif;
			;
		}

		table {
			border-collapse: collapse;
			width: 100%;
		}

		table,
		th,
		td {
			border: 1px solid black;
		}

		td {
			text-align: center;
			font-size: 10px;
		}

		th {
			text-align: center;
			font-size: 11px;
		}

		.strike {
			text-decoration: line-through;
		}

		.date {
			font-size: 8px;
			white-space: nowrap;
		}

		.remark {
			font-size: 8px;
		}

		.text-danger {
			color: #d9534f;
		}
	</style>
	<div class="content container-fluid">
		<div class="row">
			<div class="col-xs-4">
				<h4 class="page-title">Merit Sheet Yearly Report</h4>
			</div>
		</div>

		<p>Generated at <b><?php echo date("d/m/Y H:i:s"); ?></b> by <b><?php echo $current_user['first_name'] ?></b></p>

		<div class="row card-box">

			<div class="col-md-12">
				<div class="mycontainer">
					<div class="table-responsive freeze-table">
						<table style="font-size: 13px" class="table table-striped">
							<thead>
								<tr>
									<th style="font-size: 13px;">Employee Id</th>
									<th style="font-size: 13px;">Name</th>
									<th style="font-size: 13px;">Points</th>
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
								<?php foreach ($employees as $employee) { ?>
									<?php $points = get_employee_points($employee->employee_id, $year); ?>
									<tr>
										<td><?= $employee->employee_id; ?></td>
										<td><?= $employee->name; ?></td>
										<?php
										$total_points = 0;
										foreach ($points as $point) {
											$total_points += $point->points;
										}
										?>
										<td><?= sprintf('%0.2f', round($total_points / 12, 2)); ?></td>

										<?php $data = get_employee_points_by_month($employee->employee_id, $year, 1); ?>
										<?php if (!empty($data)) { ?>
											<td><?= $data[0]->points; ?></td>
										<?php } else { ?>
											<td></td>
										<?php } ?>

										<?php $data = get_employee_points_by_month($employee->employee_id, $year, 2); ?>
										<?php if (!empty($data)) { ?>
											<td><?= $data[0]->points; ?></td>
										<?php } else { ?>
											<td></td>
										<?php } ?>

										<?php $data = get_employee_points_by_month($employee->employee_id, $year, 3); ?>
										<?php if (!empty($data)) { ?>
											<td><?= $data[0]->points; ?></td>
										<?php } else { ?>
											<td></td>
										<?php } ?>

										<?php $data = get_employee_points_by_month($employee->employee_id, $year, 4); ?>
										<?php if (!empty($data)) { ?>
											<td><?= $data[0]->points; ?></td>
										<?php } else { ?>
											<td></td>
										<?php } ?>

										<?php $data = get_employee_points_by_month($employee->employee_id, $year, 5); ?>
										<?php if (!empty($data)) { ?>
											<td><?= $data[0]->points; ?></td>
										<?php } else { ?>
											<td></td>
										<?php } ?>

										<?php $data = get_employee_points_by_month($employee->employee_id, $year, 6); ?>
										<?php if (!empty($data)) { ?>
											<td><?= $data[0]->points; ?></td>
										<?php } else { ?>
											<td></td>
										<?php } ?>

										<?php $data = get_employee_points_by_month($employee->employee_id, $year, 7); ?>
										<?php if (!empty($data)) { ?>
											<td><?= $data[0]->points; ?></td>
										<?php } else { ?>
											<td></td>
										<?php } ?>

										<?php $data = get_employee_points_by_month($employee->employee_id, $year, 8); ?>
										<?php if (!empty($data)) { ?>
											<td><?= $data[0]->points; ?></td>
										<?php } else { ?>
											<td></td>
										<?php } ?>

										<?php $data = get_employee_points_by_month($employee->employee_id, $year, 9); ?>
										<?php if (!empty($data)) { ?>
											<td><?= $data[0]->points; ?></td>
										<?php } else { ?>
											<td></td>
										<?php } ?>

										<?php $data = get_employee_points_by_month($employee->employee_id, $year, 10); ?>
										<?php if (!empty($data)) { ?>
											<td><?= $data[0]->points; ?></td>
										<?php } else { ?>
											<td></td>
										<?php } ?>

										<?php $data = get_employee_points_by_month($employee->employee_id, $year, 11); ?>
										<?php if (!empty($data)) { ?>
											<td><?= $data[0]->points; ?></td>
										<?php } else { ?>
											<td></td>
										<?php } ?>

										<?php $data = get_employee_points_by_month($employee->employee_id, $year, 12); ?>
										<?php if (!empty($data)) { ?>
											<td><?= $data[0]->points; ?></td>
										<?php } else { ?>
											<td></td>
										<?php } ?>
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