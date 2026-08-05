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
		<h3><?php echo $employee->first_name; ?> (<?php echo $employee->special_id; ?>) - Full Summary (<?php echo $from_f ?> to <?php echo $to_f ?>)</h3>
		<p>Position: <?php echo $employee->position; ?>, Department: <?php echo $employee->department; ?></p>
		<p>Generated at <b><?php echo date("d/m/Y H:i:s"); ?></b> by <b><?php echo get_user()["first_name"]; ?></b></p>

	</div>

	<table>
		<thead>
			<tr>
				<th>Date</th>
				<th>Shift</th>
				<th>In</th>
				<th>Out</th>
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
			</tr>
		</thead>
		<tbody>
			<?php foreach($dates as $d){ ?>
				<?php foreach($d->clockings as $key => $clock){ ?>
					<tr>
						<?php if($key == 0){ ?>
							<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><b><?php echo $clock->day_f; ?></b></td>
						<?php } ?>
						<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>"><?php if($key%2 != 1){ echo $clock->code;}else{
							echo "Break";
						} ?>  </td>
						<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>"><?php echo $clock->clock_in; ?></td>
						<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>"><?php echo $clock->clock_out; ?></td>
						<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>"><?php if($clock->clock_out == "") {echo ""; }else{ echo $clock->total_time; }?></td>
						<?php if($key == 0){ ?>
							<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php echo $d->total_hours; ?></td>
							<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php echo $d->work_hours; ?></td>
							<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php if($d->break_hours != "00:00"){echo $d->break_hours;} ?></td>
							<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php if($d->late_hours != "00:00"){echo $d->late_hours;} ?></td>

							<td rowspan="<?php echo count($d->clockings);?>" class="text-center <?php if(!$d->is_ot || $d->is_manual_exist){echo 'strike';} ?>" style="vertical-align: middle;"><?php echo $d->overtime;  ?></td>
							<td rowspan="<?php echo count($d->clockings);?>" class="text-center <?php if(!$d->is_ot){echo 'strike';} ?>" style="vertical-align: middle"><?php echo $d->overtime_m;  ?></td>
							<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php echo $d->days; ?></td>
							<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php echo $clock->reason; ?></td>
							<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php echo $clock->remark; ?></td>
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
						<td class="text-center" colspan="2"><?php echo $month_overtime;?></td>
						<td class="text-center"><?php echo $total_days; ?></td>
						<td colspan="2"></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>

	</body>
	</html>