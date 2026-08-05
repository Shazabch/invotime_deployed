<!DOCTYPE html>
<html>
<head>
	<title>Payroll Slip</title>
	<style type="text/css">
		body{
			font-size: 12px;
			font-family: Courier;
			color: #313131;
			line-height: 1;
		}
	</style>
</head>
<body>
	<div style="width:100%;">
		<span style="width: 25%;float: left;text-align: center;"><b><?php echo $employee; ?></b>(<?php echo $special_id; ?>)</span>
		<span style="width: 25%;float: left;text-align: center;">Period: <b><?php echo $month; ?> <?php echo $year; ?></b></span>
		<span style="width: 25%;float: left;text-align: center;">Printed: <b><?php echo $today; ?></b></span>
		<span style="width: 25%;float: left;text-align: center;">Paid: <b>Bank Transfer</b></span>
	</div>
	<br>
	<hr>
	<br>
	<br>
	<div style="width: 100%;height: 180px;">
		<div style="width: 48%;float: left;">
			<b style="color:#4AADC6;">Employee Earnings</b><br><br>
			<table style="width: 100%;border-top: 1px solid #313131;border-bottom: 0.5px solid #313131;">
				<tr>
					<td>Basic</td>
					<td style="text-align: right">RM <?php echo $basic_amount; ?></td>
				</tr>
				<?php foreach ($allowances as $a) { ?>
				<tr>
					<td><?php echo $a->allowance_name; ?></td>
					<td style="text-align: right">RM <?php echo $a->amount; ?></td>
				</tr>
			<?php } ?>
			</table>
			<table style="width: 100%;">
				<tr>
					<td><b>Total Earnings</b></td>
					<td style="text-align: right"><b style="color:#4AADC6;">RM <?php echo $gross_pay; ?></b></td>
				</tr>
			</table>
		</div>
		<div style="width: 48%;float: right;">
			<b style="color:#4AADC6;">Employee Deductions</b><br><br>
			<table style="width: 100%;border-top: 1px solid #313131;border-bottom: 0.5px solid #313131;">
				<tr>
					<td>Employee EPF</td>
					<td style="text-align: right">RM <?php echo $epf; ?></td>
				</tr>
				<tr>
					<td>Employee SOCSO</td>
					<td style="text-align: right">RM <?php echo $socso; ?></td>
				</tr>
				<tr>
					<td>Tax</td>
					<td style="text-align: right">RM <?php echo $tax; ?></td>
				</tr>
				<tr>
					<td>Employee EIS</td>
					<td style="text-align: right">RM <?php echo $eis; ?></td>
				</tr>
				<?php foreach ($deductions as $d) { ?>
				<tr>
					<td><?php echo $d->name; ?></td>
					<td style="text-align: right">RM <?php echo $d->amount; ?></td>
				</tr>
			<?php } ?>
			</table>
			<table style="width: 100%;">
				<tr>
					<td><b>Total Deductions</b></td>
					<td style="text-align: right"><b style="color:#4AADC6;">RM <?php echo $total_deductions; ?></b></td>
				</tr>
			</table>
		</div>
	</div>

	<div style="width: 100%;clear: both;height: 140px;">
		<div style="width: 30%;float: left;">
			<b><?php echo $employee; ?></b><br><br>
			<table style="width: 100%;border-top: 1px solid #313131;">
				<tr>
					<td>Position: <?php echo $position; ?></td>
				</tr>
				<tr>
					<td>Dept: <?php echo $department; ?></td>
				</tr>
				<tr>
					<td>IC/Passport: <?php echo $ic_passport; ?></td>
				</tr>
				<tr>
					<td>EPF No: </td>
				</tr>
				<tr>
					<td>SOCSO No: </td>
				</tr>
				<tr>
					<td>Income Tax No: </td>
				</tr>
			</table>
		</div>
		<div style="width: 5%;float: left;"></div>
		<div style="width: 30%;float: left;">
			<b>Totals this period</b><br><br>
			<table style="width: 100%;border-top: 1px solid #313131;">
				<tr>
					<td>Net Pay</td>
					<td style="text-align: right">RM <?php echo $net_pay; ?></td>
				</tr>
				<tr>
					<td>Gross Pay</td>
					<td style="text-align: right">RM <?php echo $gross_pay; ?></td>
				</tr>
				<tr>
					<td>E'R EPF</td>
					<td style="text-align: right">RM <?php echo $epf_c; ?></td>
				</tr>
				<tr>
					<td>E'R EIS</td>
					<td style="text-align: right">RM <?php echo $eis_c; ?></td>
				</tr>
				<tr>
					<td>E'R SOCSO</td>
					<td style="text-align: right">RM <?php echo $socso_c; ?></td>
				</tr>
			</table>
		</div>
		<div style="width: 5%;float: left;"></div>
		<div style="width: 30%;float: left;">
			<b>Totals YTD</b><br><br>
			<table style="width: 100%;border-top: 1px solid #313131;">
				
			</table>
		</div>
	</div>
	<div style="width: 100%;clear: both">
		<div style="width: 48%;float: left">
			<h4><?php echo $company; ?></h4>
			<?php echo $address; ?><br>
			<?php echo $phone; ?>
		</div>
		<div style="width: 50%;float: right">
			<div style="height: 65px;width: 209px;border: 1px solid #313131;margin-top: 20px;float: left">
				
			</div>
			<div style="height: 60px;width: 140px;border: 1px solid #4AADC6;margin-top: 22px;float: right">
				<p style="text-align: right;color: #4AADC6;font-size: 13px;"><b>NET PAY  </b></p>
				<p style="text-align: right;font-size: 13px;"><b>RM <?php echo $net_pay; ?>  </b></p>
			</div>
		</div>
	</div>
</body>
</html>