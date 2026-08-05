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
						<table class="table table-striped">
							<thead>
								<tr>
									<th>Name</th>
									<th>Points</th>
									<th>Grade</th>
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
								<?php foreach ($employees as $employee) : ?>
									<tr>
										<td><b><?php echo $employee->first_name ?></b></td>
										<td><?php echo $employee->average_merit_points ?></td>
										<td><?php echo $employee->grade ?></td>
										<?php for ($i = 0; $i < 12; $i++) : ?>
											<td class="text-center"><?php echo isset($employee->merit_points[$i]) ? $employee->merit_points[$i]->points : "" ?></td>
										<?php endfor ?>
									</tr>
								<?php endforeach ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>