<style>
@page {
	size: A4;
	margin: 15mm;
}

* {
	box-sizing: border-box;
}

table {
	border-collapse: collapse;
	width: 100%;
	table-layout: fixed;
}

td, th {
	border: 1px solid #000;
	padding: 4px 2px;
	font-size: 8px;
	word-wrap: break-word;
	overflow-wrap: break-word;
	word-break: break-word;
	vertical-align: middle;
}

th {
	/* background-color: #f0f0f0; */
	font-weight: bold;
	text-align: center;
}

/* Prevent rows from breaking across pages */
tbody tr {
	page-break-inside: avoid !important;
	page-break-after: auto;
}

/* Allow page breaks between rows */
tbody tr:not(:last-child) {
	page-break-after: auto;
}

/* Keep table header on each page */
thead {
	display: table-header-group;
}

/* Keep table footer on each page */
tfoot {
	display: table-footer-group;
}

/* Text wrapping for long content */
.remark, .location {
	max-width: 100%;
	word-wrap: break-word;
	overflow-wrap: break-word;
	white-space: normal;
	font-size: 7px;
	line-height: 1.2;
}

.text-center {
	text-align: center;
}

.text-danger {
	color: red;
}

.strike {
	text-decoration: line-through;
}

.header {
	margin-bottom: 10px;
}

.header h4 {
	margin: 0 0 5px 0;
	font-size: 12px;
}

.header p {
	margin: 0;
	font-size: 9px;
}
</style>

<div class="header">
	<h4><?php echo $employee->first_name; ?> (<?php echo $employee->special_id; ?>) - Full Summary (<?php echo $from_f ?> to <?php echo $to_f ?>)</h4>
	<p><b>Position</b>: <?php echo $employee->position; ?>, <b>Department</b>: <?php echo $employee->department; ?> | Generated at <b><?php echo date("d/m/Y H:i:s"); ?></b> by <b><?php echo get_user()["first_name"]; ?></b></p>
</div>

<table>
	<thead>
		<tr>
			<th style="width: 50px;">Date</th>
			<th style="width: 35px;">Shift</th>
			<th style="width: 35px;">In</th>
			<th style="width: 35px;">Out</th>
			<?php if ($custom_in_outs): ?>
				<th style="width: 35px;">In</th>
				<th style="width: 35px;">Out</th>
			<?php endif; ?>
			<th style="width: 35px;">Total<br>Hours</th>
			<th style="width: 35px;">Work<br>Hours</th>
			<th style="width: 35px;">Shift<br>Hours</th>
			<th style="width: 35px;">Break<br>Hours</th>
			<th style="width: 35px;">Late<br>Hours</th>
			<th style="width: 35px;">Late<br>(Break)</th>
			<th style="width: 35px;">Early<br>Out</th>
			<th style="width: 30px;">OT</th>
			<?php if (!$custom_in_outs): ?>
				<th style="width: 30px;">OT(M)</th>
			<?php endif; ?>
			<?php if (!$tsf_custom_summary): ?>
				<th style="width: 30px;">OT<br>(PHx2)</th>
			<?php endif; ?>
			<th style="width: 30px;">OT<br>(PHx3)</th>
			<th style="width: 30px;">OT<br>(RD)</th>
			<?php if (!$tsf_custom_summary): ?>
				<th style="width: 30px;">OT<br>(OFF)</th>
			<?php endif; ?>
			<th style="width: 30px;">Days</th>
			<?php if (!$custom_in_outs && !$tsf_custom_summary): ?>
				<th style="width: 30px;">Trip<br>(A)</th>
				<th style="width: 30px;">Trip<br>(B)</th>
			<?php endif; ?>
			<?php if ($tsf_custom_summary): ?>
				<th style="width: 80px;">Location</th>
				<th style="width: 40px;">Distance</th>
			<?php endif; ?>
			<th style="width: 80px;">Remark</th>
			<?php if ($tsf_custom_summary): ?>
				<th style="width: 40px;">Meal<br>Allow</th>
			<?php endif; ?>
			<th style="width: 80px;">Staff Remark</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($dates as $date_key => $d): ?>
			<?php foreach($d->clockings as $key => $clock): ?>
				<?php
					$row_span = ceil(count($d->clockings) / 2);
					if($key % 2 == 0):
				?>
				<tr>
					<?php if($key == 0): ?>
						<td rowspan="<?php echo $row_span; ?>" class="text-center date" style="<?php echo in_array($d->date, $public_holidays) ? 'color: red;' : ''; ?>">
							<b><?php echo $clock->day_f; ?></b>
						</td>
					<?php endif; ?>

					<td class="text-center shift <?php if($key%2 == 1) echo 'text-danger'; ?>">
						<?php echo ($key%2 != 1) ? $clock->code : "Break"; ?>
					</td>

					<?php if ($custom_in_outs): ?>
						<?php for($i = 0; $i < 4; $i++): ?>
							<?php $clock_check = admin_add_edit_check_clock_in(isset($d->in_outs_id[$i]) ? $d->in_outs_id[$i] : null); ?>
							<td class="text-center <?php if($key%2 == 1) echo 'text-danger'; ?>">
								<?php if ($clock_check == 1): ?>
									<b class="text-danger"><?= $d->in_outs[$i] ?? '' ?></b>
								<?php else: ?>
									<?= $d->in_outs[$i] ?? '' ?>
								<?php endif; ?>
							</td>
						<?php endfor; ?>
					<?php else: ?>
						<?php $clock_in_check = admin_add_edit_check_clock_in(isset($clock->clock_in_id) ? $clock->clock_in_id : null); ?>
						<td class="text-center <?php if($key%2 == 1) echo 'text-danger'; ?>">
							<?php if ($clock_in_check == 1): ?>
								<b class="text-danger"><?php echo $clock->clock_in; ?></b>
							<?php else: ?>
								<?php echo $clock->clock_in; ?>
							<?php endif; ?>
						</td>

						<?php $clock_out_check = admin_add_edit_check_clock_out(isset($clock->clock_out_id) ? $clock->clock_out_id : null); ?>
						<td class="text-center <?php if($key%2 == 1) echo 'text-danger'; ?>">
							<?php if ($clock_out_check == 1): ?>
								<b class="text-danger"><?php echo $clock->clock_out; ?></b>
							<?php else: ?>
								<?php echo $clock->clock_out; ?>
							<?php endif; ?>
						</td>
					<?php endif; ?>

					<?php if($key == 0): ?>
						<td rowspan="<?php echo $row_span; ?>" class="text-center"><?php echo $d->total_hours; ?></td>
						<td rowspan="<?php echo $row_span; ?>" class="text-center"><?php echo $d->work_hours; ?></td>
						<td rowspan="<?php echo $row_span; ?>" class="text-center"><?php echo $d->employee_shift_hours; ?></td>
						<td rowspan="<?php echo $row_span; ?>" class="text-center"><?php if($d->break_hours != "00:00") echo $d->break_hours; ?></td>
						<td rowspan="<?php echo $row_span; ?>" class="text-center <?php if(!$d->is_late) echo 'strike'; ?>"><?php if($d->late_hours != "00:00") echo $d->late_hours; ?></td>
						<td rowspan="<?php echo $row_span; ?>" class="text-center <?php if(!$d->is_late_break) echo 'strike'; ?>"><?php if($d->break_late_hours != "00:00") echo $d->break_late_hours; ?></td>
						<td rowspan="<?php echo $row_span; ?>" class="text-center <?php if(!$d->is_early_out) echo 'strike'; ?>"><?php if($d->early_out != "00:00") echo $d->early_out; ?></td>

						<td rowspan="<?php echo $row_span ?>" class="text-center">
							<?php if(!in_array($d->date,$public_holidays) && !in_array($d->day_name, $rest_days) && !in_array($d->day_name, $off_days) && $d->is_shift == 'true' && !$d->is_replaced_ph && !$d->is_rest_day): ?>
								<?php if($d->is_shift == 'false'): ?>
									<span class="text-danger countOT"><?php echo $d->overtime_m ?></span>
								<?php elseif($d->is_shift == 'true'): ?>
									<?php if($d->is_ot): ?>
										<span class="<?php echo (!empty($d->overtime_m) || $d->is_extra_ot == true ? "text-danger" : "") ?> countOT"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
									<?php else: ?>
										<?php if(!empty($d->overtime)): ?>
											<span class="strike <?php echo ($d->is_extra_ot == true ? "text-danger" : "" ) ?>"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
										<?php endif ?>
										<span class="text-danger countOT"><?php echo $d->overtime_m ?></span>
									<?php endif ?>
								<?php endif ?>
							<?php endif ?>
						</td>

						<?php if(!$custom_in_outs): ?>
							<td rowspan="<?php echo $row_span; ?>" class="text-center"></td>
						<?php endif; ?>

						<?php if (!$tsf_custom_summary): ?>
						<td rowspan="<?php echo $row_span ?>" class="text-center">
							<?php if($d->x2 && (in_array($d->date,$public_holidays) || $d->is_replaced_ph)): ?>
								<?php if($d->is_ot): ?>
									<span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo $d->overtime_ph_x2 ?></span>
								<?php else: ?>
									<?php if(!empty($d->overtime)): ?>
										<span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
									<?php endif ?>
									<span class="text-danger"><?php echo $d->overtime_m ?></span>
								<?php endif ?>
							<?php endif ?>
						</td>
						<?php endif; ?>

						<td rowspan="<?php echo $row_span ?>" class="text-center">
							<?php if($d->x3 && (in_array($d->date,$public_holidays) || $d->is_replaced_ph)): ?>
								<?php if($d->is_ot): ?>
									<span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo $d->overtime_ph_x3 ?></span>
								<?php else: ?>
									<?php if(!empty($d->overtime)): ?>
										<span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
									<?php endif ?>
									<span class="text-danger"><?php echo $d->overtime_m ?></span>
								<?php endif ?>
							<?php endif ?>
						</td>

						<td rowspan="<?php echo $row_span ?>" class="text-center">
							<?php if ($d->is_rest_day): ?>
								<?php if($d->is_ot): ?>
									<span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
								<?php else: ?>
									<?php if(!empty($d->overtime)): ?>
										<span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
									<?php endif ?>
									<span class="text-danger"><?php echo $d->overtime_m ?></span>
								<?php endif ?>
							<?php endif; ?>
						</td>

						<?php if (!$tsf_custom_summary): ?>
						<td rowspan="<?php echo $row_span ?>" class="text-center">
							<?php if (!in_array($d->date,$public_holidays) && (in_array($d->day_name, $off_days))): ?>
								<?php if($d->is_ot): ?>
									<span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
								<?php else: ?>
									<?php if(!empty($d->overtime)): ?>
										<span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
									<?php endif ?>
									<span class="text-danger"><?php echo $d->overtime_m ?></span>
								<?php endif ?>
							<?php endif; ?>
						</td>
						<?php endif; ?>

						<td rowspan="<?php echo $row_span; ?>" class="text-center"><?php echo $d->days; ?></td>

						<?php if(!$custom_in_outs && !$tsf_custom_summary): ?>
							<td rowspan="<?php echo $row_span; ?>" class="text-center"><?php echo $d->trip_a; ?></td>
							<td rowspan="<?php echo $row_span; ?>" class="text-center"><?php echo $d->trip_b; ?></td>
						<?php endif; ?>

						<?php if ($tsf_custom_summary): ?>
							<td rowspan="<?php echo $row_span; ?>" class="text-center location"><?= map_address($d->location); ?></td>
							<td rowspan="<?php echo $row_span; ?>" class="text-center"><?= $d->distance ? round($d->distance) . 'm' : '' ?></td>
						<?php endif; ?>

						<td rowspan="<?php echo $row_span; ?>" class="text-center remark">
							<?php
								if (!empty($clock->remark)) {
									echo $clock->remark;
								} elseif (in_array($d->date, $public_holidays)) {
									echo $public_holidays_names[array_search($d->date, $public_holidays)];
								}
							?>
						</td>

						<?php if ($tsf_custom_summary): ?>
							<td rowspan="<?php echo $row_span; ?>" class="text-center"><?php echo $d->meal_days ?></td>
						<?php endif; ?>

						<td rowspan="<?php echo $row_span; ?>" class="text-center remark"><?php echo $clock->staff_remark ?></td>
					<?php endif; ?>
				</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endforeach; ?>
	</tbody>

	<tfoot>
		<tr>
			<td colspan="<?= $custom_in_outs ? 5 : 3; ?>"></td>
			<td class="text-center"><b>Total</b></td>
			<td class="text-center"><?php echo $total; ?></td>
			<td class="text-center"><?php echo $work; ?></td>
			<td class="text-center"><?php echo $shift_hours_total; ?></td>
			<td class="text-center"><?php echo $break; ?></td>
			<td class="text-center"><?php echo $late; ?></td>
			<td class="text-center"><?php echo $break_late; ?></td>
			<td class="text-center"><?php echo $total_early; ?></td>
			<td class="text-center <?php if($lateness_time != $lateness_time_deducted) echo 'strike'; ?>" colspan="<?= $custom_in_outs ? 1 : 2; ?>"><?php echo $month_overtime; ?></td>
			<?php if (!$tsf_custom_summary): ?>
				<td class="text-center"><?php echo $month_overtime_ph_x2; ?></td>
			<?php endif; ?>
			<td class="text-center"><?php echo $month_overtime_ph_x3; ?></td>
			<td class="text-center"><?php echo $month_overtime_rd; ?></td>
			<?php if (!$tsf_custom_summary): ?>
				<td class="text-center"><?php echo $month_overtime_off; ?></td>
			<?php endif; ?>
			<td class="text-center"><?php echo $total_days; ?></td>
			<?php if(!$custom_in_outs && !$tsf_custom_summary): ?>
				<td class="text-center"><?php echo $total_trip_a; ?></td>
				<td class="text-center"><?php echo $total_trip_b; ?></td>
				<td colspan="2"></td>
			<?php else: ?>
				<?php if($tsf_custom_summary): ?>
					<td colspan="3"></td>
					<td class="text-center"><?php echo $total_meal_days ?></td>
				<?php else: ?>
					<td colspan="2"></td>
				<?php endif; ?>
			<?php endif; ?>
		</tr>
		<tr>
			<td colspan="<?= $custom_in_outs ? 7 : 5; ?>"></td>
			<td colspan="3" class="text-center"><b>Lateness Time</b></td>
			<td colspan="3" class="text-center <?php if($lateness_time != $lateness_time_deducted) echo 'strike'; ?>"><?php echo $lateness_time; ?></td>
			<td colspan="<?= $custom_in_outs ? 8 : ($tsf_custom_summary ? 10 : 11); ?>"></td>
		</tr>
		<?php if($lateness_time != $lateness_time_deducted): ?>
		<tr>
			<td colspan="<?= $custom_in_outs ? 7 : 5; ?>"></td>
			<td colspan="3" class="text-center"><b>After Deduction</b></td>
			<td colspan="3" class="text-center"><?php echo $lateness_time_deducted; ?></td>
			<td colspan="2" class="text-center"><?php echo $month_overtime_deducted; ?></td>
			<td colspan="<?= $custom_in_outs ? 6 : ($tsf_custom_summary ? 8 : 9); ?>"></td>
		</tr>
		<?php endif; ?>
	</tfoot>
</table>

<?php if (isset($is_merged) && $is_merged): ?>
	<div style="page-break-after: always;"></div>
<?php endif; ?>