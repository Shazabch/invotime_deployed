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
		}
		th {
			text-align: center;
		}
	</style>
</head>
<body>

	<div>
		<h3><?php echo $employee->first_name; ?> (<?php echo $employee->special_id; ?>) - Summary</h3>
		<p style="margin:0px;font-weight: bold"><?php echo $from_f ?> to <?php echo $to_f ?></p>
		<p>Position: <?php echo $employee->position; ?>, Department: <?php echo $employee->department; ?></p>
	</div>

	<table>
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
							<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><b><?php echo $clock->day_f; ?></b></td>
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

							<td rowspan="<?php echo count($d->clockings);?>" class="text-center" style="vertical-align: middle"><?php if(!$d->is_ot){echo '';} else{ echo $d->overtime; } ?></td>
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

	</body>
	</html>