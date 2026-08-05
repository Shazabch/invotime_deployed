<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title></title>
	<link rel="stylesheet" href="">
	<style>
		body{
			font-family: 'Montserrat', sans-serif;;
		}
		table {
			border-collapse: collapse;
		}

		table, th, td {
			border: 1px solid black;
		}

		td {
			text-align: center;
			font-size: 11px;
		}
		th {
			text-align: center;
			font-size: 13px;
		}
		.strike{
			text-decoration: line-through;
		}
	</style>
</head>
<body>

	<div>
		<h3><?php echo $branch_name; ?> - Short Summary (<?php echo $from_f ?> to <?php echo $to_f ?>)</h3>

		<p>Generated at <b><?php echo date("d/m/Y H:i:s"); ?></b> by <b><?php echo get_user()["first_name"]; ?></b></p>

	</div>

	<table>
		<thead>
			<tr>
				<th>Name</th>
				<th>Employee ID</th>
				<th>Working Days</th>
				<th>Worked Days</th>
				<th>Absent Days</th>
				<th>Leave Days</th>
				<th>Unpaid Leave Days</th>
				<th>Worked Rest Days</th>
				<th>Worked Off Days</th>
				<th>Worked Holidays</th>
				<th>OT</th>
				<th>OT (PHx2)</th>
				<th>OT (PHx3)</th>
				<th>OT (RD)</th>
				<th>OT (OFF)</th>
				<th>Lateness Count</th>
				<th>Lateness Time</th>
				<th>Trips A</th>
				<th>Trips B</th>
			</tr>
		</thead>
		<tbody>

			<?php foreach($all_data as $row){ ?>
				<tr>
					<td><?php echo $row["employee"]->first_name; ?></td>
					<td><?php echo $row["employee"]->special_id; ?></td>
					<td><?php echo $row["working_days"]; ?></td>
					<td><?php echo $row["worked_days"]; ?></td>
					<td><?php echo $row["absent_days"];?></td>
					<td><?php echo $row["paid_leaves"];?></td>
					<td><?php echo $row["unpaid_leaves"];?></td>
					<td><?php echo $row["worked_rest_days"];?></td>
					<td><?php echo $row["worked_off_days"];?></td>
					<td><?php echo $row["worked_holidays"];?></td>
					<td><?php echo $row["month_overtime_deducted"];?></td>
					<td><?php echo $row["month_overtime_ph_x2"];?></td>
					<td><?php echo $row["month_overtime_ph_x3"];?></td>
					<td><?php echo $row["month_overtime_rd"];?></td>
					<td><?php echo $row["month_overtime_off"];?></td>
					<td><?php echo $row["late_count"];?></td>
					<td><?php echo $row["lateness_time_deducted"];?></td>
					<td><?php echo $row["total_trip_a"];?></td>
					<td><?php echo $row["total_trip_b"];?></td>
				</tr>
			<?php } ?>


			</tbody>
		</table>

	</body>
	</html>
