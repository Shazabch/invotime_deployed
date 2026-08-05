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
				<th>Emp ID</th>
				<th>Department</th>
				<th>Position</th>
				<th>Total Hours</th>
				<th>Work Hours</th>
				<th>Break Hours</th>
				<th>OT Hours</th>
				<th>Late Hours</th>
				<th>Days</th>
			</tr>
		</thead>
		<tbody>

			<?php foreach($all_data as $row){ ?>
				<tr>
					<td><?php echo $row["employee"]->first_name; ?></td>
					<td><?php echo $row["employee"]->special_id; ?></td>
					<td><?php echo $row["employee"]->department; ?></td>
					<td><?php echo $row["employee"]->position; ?></td>
					<td><?php echo $row["total"];?></td>
					<td><?php echo $row["work"];?></td>
					<td><?php echo $row["break"];?></td>
					<td><?php echo $row["month_overtime"];?></td>
					<td><?php echo $row["late"];?></td>
					<td><?php echo $row["total_days"];?></td>
				</tr>
			<?php } ?>

			
			</tbody>
		</table>

	</body>
	</html>