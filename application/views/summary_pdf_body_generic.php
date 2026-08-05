<style>
	table {
		border-collapse: collapse;
		margin-top: 0 !important;
		padding-top: 0 !important;
		margin-bottom: 5px !important;
		border: 1px solid black !important;
		border-top: none !important;
		border-right: none !important;
		border-left: none !important;
	}

	tr {
		border: 1px solid black !important;
	}

	.inner-table {
		width: 100%;
		border-collapse: collapse;
		border: none !important;
		margin-top: 0 !important;
		margin-bottom: 0 !important;
	}

	.inner-table td {
		border: none;
		padding: 0px 0px;
	}

	.inner-table td:empty {
		border: none !important;
	}

	/* Remove bottom border from last row of inner table */
	/* .inner-table tr:last-child td {
		border-bottom: none !important;
	}

	.inner-table tr:first-child td {
		border-bottom: none !important;
	} */

	.date-row {
		page-break-inside: avoid !important;
		page-break-after: auto !important;

	}

	.innbd {
		border: .5px solid rgb(28, 28, 28);;
		padding: 2px 4px;
		margin-top: 0px !important;
		margin-bottom: 0px !important;
	}
</style>

<table style="border: none;">
	<thead>
		<tr style="border:none !important;">
			<?php
			$col_count = 8;
			$col_count += 7;
			if ($custom_in_outs)          $col_count += 2;
			if (!$custom_in_outs)         $col_count += 1;
			if (!$tsf_custom_summary)     $col_count += 2;
			$col_count += 1;
			$col_count += 1;
			if (!$custom_in_outs && !$tsf_custom_summary) $col_count += 2;
			if ($tsf_custom_summary)      $col_count += 3;
			$col_count += 4;
			?>
			<td colspan="<?= $col_count ?>" style="
                   border: none !important;
                   text-align:left;
                   font-size: 14px;
                    line-height: 1.6;
               ">
				<strong style="font-size:17px; color:#212529;">
					<?= $employee->first_name ?> (<?= $employee->special_id ?>) - Full Summary (<?= $from_f ?> to <?= $to_f ?>)
				</strong>
				<br>
				<b>Position:</b> <?= $employee->position ?>,
				<b>Department:</b> <?= $employee->department ?> |
				Generated at <b><?= date("d/m/Y H:i:s") ?></b> by <b><?= get_user()["first_name"] ?></b>
			</td>
		</tr>
		<tr>
			<th colspan="2" style="width: 50px;">Date</th>
			<th colspan="2" style="width: 35px;">Shift</th>
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
			<th colspan="2" style="width: 80px;">Remark</th>
			<?php if ($tsf_custom_summary): ?>
				<th style="width: 40px;">Meal<br>Allow</th>
			<?php endif; ?>
			<th colspan="2" style="width: 80px;">Staff Remark</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($dates as $date_key => $d): ?>
			<!-- ONE ROW PER DATE - No rowspan, use nested table for shifts -->
			<tr class="date-row">
				<td colspan="2" class="text-center date" style="<?php echo in_array($d->date, $public_holidays) ? 'color: red;' : ''; ?>">
					<b><?php echo $d->clockings[0]->day_f; ?></b>
				</td>

				<!-- Shift column moved outside the inner table -->
				<td colspan="2" class="text-center shift"  style="padding: 0;">
					<?php
					$total_shifts = 0;
					foreach ($d->clockings as $key => $clock):
						if ($key % 2 == 0) $total_shifts++;
					endforeach;

					$current_shift = 0;
					foreach ($d->clockings as $key => $clock): ?>
						<?php if ($key % 2 == 0):
							$current_shift++;
						?>
							<div style="padding: 2px 4px; <?php if ($clock->code && $total_shifts!=1): ?> border: .5px solid lightslategray; <?php endif; ?>">
								<?php echo $clock->code; ?>
							</div>
							<?php if ($current_shift < $total_shifts): ?>
								<!-- <hr style="margin: 1px 0; border: none; border-top: 1px solid lightslategray; height: 1px;"> -->
							<?php endif; ?>
						<?php endif; ?>
					<?php endforeach; ?>
				</td>

				<!-- Nested table for in/out times only (no shift) -->
				<td colspan="<?php echo $custom_in_outs ? 4 : 2; ?>" style="padding: 0;">
					<table class="inner-table">
						<?php foreach ($d->clockings as $key => $clock): ?>
							<?php if ($key % 2 == 0): ?>
								<tr>
									<?php if ($custom_in_outs): ?>
										<?php for ($i = 0; $i < 4; $i++): ?>
											<?php $clock_check = admin_add_edit_check_clock_in(isset($d->in_outs_id[$i]) ? $d->in_outs_id[$i] : null); ?>
											<td class="text-center" style="width: 35px;">
												<div <?php if ($d->in_outs[$i]): ?> class="innbd" <?php endif; ?>>
													<?php if ($clock_check == 1): ?>
														<span class="text-danger"><?= $d->in_outs[$i] ?? '' ?></span>
													<?php else: ?>
														<?= $d->in_outs[$i] ?? '' ?>
													<?php endif; ?>
												</div>
											</td>
										<?php endfor; ?>
									<?php else: ?>
										<?php $clock_in_check = admin_add_edit_check_clock_in(isset($clock->clock_in_id) ? $clock->clock_in_id : null); ?>
										<td class="text-center" style="width: 35px;">
											<div <?php if ($clock->clock_in): ?> class="innbd" <?php endif; ?>>
												<?php if ($clock_in_check == 1): ?>
													<span class="text-danger"><?php echo $clock->clock_in; ?></span>
												<?php else: ?>
													<?php echo $clock->clock_in; ?>
												<?php endif; ?>
											</div>
										</td>

										<?php $clock_out_check = admin_add_edit_check_clock_out(isset($clock->clock_out_id) ? $clock->clock_out_id : null); ?>
										<td class="text-center" style="width: 35px;">
											<div <?php if ($clock->clock_out): ?> class="innbd" <?php endif; ?>>
												<?php if ($clock_out_check == 1): ?>
													<span class="text-danger"><?php echo $clock->clock_out; ?></span>
												<?php else: ?>
													<?php echo $clock->clock_out; ?>
												<?php endif; ?>
											</div>
										</td>
									<?php endif; ?>
								</tr>
							<?php endif; ?>
						<?php endforeach; ?>
					</table>
				</td>

				<td class="text-center"><?php echo $d->total_hours; ?></td>
				<td class="text-center"><?php echo $d->work_hours; ?></td>
				<td class="text-center"><?php echo $d->employee_shift_hours; ?></td>
				<td class="text-center"><?php if ($d->break_hours != "00:00") echo $d->break_hours; ?></td>
				<td class="text-center <?php if (!$d->is_late) echo 'strike'; ?>"><?php if ($d->late_hours != "00:00") echo $d->late_hours; ?></td>
				<td class="text-center <?php if (!$d->is_late_break) echo 'strike'; ?>"><?php if ($d->break_late_hours != "00:00") echo $d->break_late_hours; ?></td>
				<td class="text-center <?php if (!$d->is_early_out) echo 'strike'; ?>"><?php if ($d->early_out != "00:00") echo $d->early_out; ?></td>

				<td class="text-center">
					<?php if (!in_array($d->date, $public_holidays) && !in_array($d->day_name, $rest_days) && !in_array($d->day_name, $off_days) && $d->is_shift == 'true' && !$d->is_replaced_ph && !$d->is_rest_day && !$d->is_employee_off_day): ?>
						<?php if ($d->is_shift == 'false'): ?>
							<span class="text-danger countOT"><?php echo $d->overtime_m ?></span>
						<?php elseif ($d->is_shift == 'true'): ?>
							<?php if ($d->is_ot): ?>
								<span class="<?php echo (!empty($d->overtime_m) || $d->is_extra_ot == true ? "text-danger" : "") ?> countOT"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
							<?php else: ?>
								<?php if (!empty($d->overtime)): ?>
									<span class="strike <?php echo ($d->is_extra_ot == true ? "text-danger" : "") ?>"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
								<?php endif ?>
								<span class="text-danger countOT"><?php echo $d->overtime_m ?></span>
							<?php endif ?>
						<?php endif ?>
					<?php endif ?>
				</td>

				<?php if (!$custom_in_outs): ?>
					<td class="text-center"></td>
				<?php endif; ?>

				<?php if (!$tsf_custom_summary): ?>
					<td class="text-center">
						<?php if ($d->x2 && (in_array($d->date, $public_holidays) || $d->is_replaced_ph)): ?>
							<?php if ($d->is_ot): ?>
								<span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo $d->overtime_ph_x2 ?></span>
							<?php else: ?>
								<?php if (!empty($d->overtime)): ?>
									<span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
								<?php endif ?>
								<span class="text-danger"><?php echo $d->overtime_m ?></span>
							<?php endif ?>
						<?php endif ?>
					</td>
				<?php endif; ?>

				<td class="text-center">
					<?php if ($d->x3 && (in_array($d->date, $public_holidays) || $d->is_replaced_ph)): ?>
						<?php if ($d->is_ot): ?>
							<span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo $d->overtime_ph_x3 ?></span>
						<?php else: ?>
							<?php if (!empty($d->overtime)): ?>
								<span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
							<?php endif ?>
							<span class="text-danger"><?php echo $d->overtime_m ?></span>
						<?php endif ?>
					<?php endif ?>
				</td>

				<td class="text-center">
					<?php if ($d->is_rest_day): ?>
						<?php if ($d->is_ot): ?>
							<span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
						<?php else: ?>
							<?php if (!empty($d->overtime)): ?>
								<span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
							<?php endif ?>
							<span class="text-danger"><?php echo $d->overtime_m ?></span>
						<?php endif ?>
					<?php endif; ?>
				</td>

				<?php if (!$tsf_custom_summary): ?>
					<td class="text-center">
						<?php if (!in_array($d->date, $public_holidays) && (in_array($d->day_name, $off_days) || $d->is_employee_off_day)): ?>
							<?php if ($d->is_ot): ?>
								<span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
							<?php else: ?>
								<?php if (!empty($d->overtime)): ?>
									<span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
								<?php endif ?>
								<span class="text-danger"><?php echo $d->overtime_m ?></span>
							<?php endif ?>
						<?php endif; ?>
					</td>
				<?php endif; ?>

				<td class="text-center"><?php echo $d->days; ?></td>

				<?php if (!$custom_in_outs && !$tsf_custom_summary): ?>
					<td class="text-center"><?php echo $d->trip_a; ?></td>
					<td class="text-center"><?php echo $d->trip_b; ?></td>
				<?php endif; ?>

				<?php if ($tsf_custom_summary): ?>
					<td class="text-center location"><?= map_address($d->location); ?></td>
					<td class="text-center"><?= $d->distance ? round($d->distance) . 'm' : '' ?></td>
				<?php endif; ?>

				<td colspan="2" class="text-center remark">
					<?php
					if (!empty($d->clockings[0]->remark)) {
						echo $d->clockings[0]->remark;
					} elseif (in_array($d->date, $public_holidays)) {
						echo $public_holidays_names[array_search($d->date, $public_holidays)];
					}
					?>
				</td>

				<?php if ($tsf_custom_summary): ?>
					<td class="text-center"><?php echo $d->meal_days ?></td>
				<?php endif; ?>

				<td colspan="2" class="text-center remark"><?php echo $d->clockings[0]->staff_remark ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>

	<tfoot>
		<tr>
			<td colspan="<?= $custom_in_outs ? 7 : 5; ?>"></td>
			<td class="text-center"><b>Total</b></td>
			<td class="text-center"><?php echo $total; ?></td>
			<td class="text-center"><?php echo $work; ?></td>
			<td class="text-center"><?php echo $shift_hours_total; ?></td>
			<td class="text-center"><?php echo $break; ?></td>
			<td class="text-center"><?php echo $late; ?></td>
			<td class="text-center"><?php echo $break_late; ?></td>
			<td class="text-center"><?php echo $total_early; ?></td>
			<td class="text-center <?php if ($lateness_time != $lateness_time_deducted) echo 'strike'; ?>" colspan="<?= $custom_in_outs ? 1 : 2; ?>"><?php echo $month_overtime; ?></td>
			<?php if (!$tsf_custom_summary): ?>
				<td class="text-center"><?php echo $month_overtime_ph_x2; ?></td>
			<?php endif; ?>
			<td class="text-center"><?php echo $month_overtime_ph_x3; ?></td>
			<td class="text-center"><?php echo $month_overtime_rd; ?></td>
			<?php if (!$tsf_custom_summary): ?>
				<td class="text-center"><?php echo $month_overtime_off; ?></td>
			<?php endif; ?>
			<td class="text-center"><?php echo $total_days; ?></td>
			<?php if (!$custom_in_outs && !$tsf_custom_summary): ?>
				<td class="text-center"><?php echo $total_trip_a; ?></td>
				<td class="text-center"><?php echo $total_trip_b; ?></td>
				<td colspan="4"></td>
			<?php else: ?>
				<?php if ($tsf_custom_summary): ?>
					<td colspan="5"></td>
					<td class="text-center"><?php echo $total_meal_days ?></td>
				<?php else: ?>
					<td colspan="4"></td>
				<?php endif; ?>
			<?php endif; ?>
		</tr>
		<tr>
			<td colspan="<?= $custom_in_outs ? 9 : 7; ?>"></td>
			<td colspan="3" class="text-center"><b>Lateness Time</b></td>
			<td colspan="3" class="text-center <?php if ($lateness_time != $lateness_time_deducted) echo 'strike'; ?>"><?php echo $lateness_time; ?></td>
			<td colspan="<?= $custom_in_outs ? 10 : ($tsf_custom_summary ? 12 : 13); ?>"></td>
		</tr>
		<?php if ($lateness_time != $lateness_time_deducted): ?>
			<tr>
				<td colspan="<?= $custom_in_outs ? 9 : 7; ?>"></td>
				<td colspan="3" class="text-center"><b>After Deduction</b></td>
				<td colspan="3" class="text-center"><?php echo $lateness_time_deducted; ?></td>
				<td colspan="2" class="text-center"><?php echo $month_overtime_deducted; ?></td>
				<td colspan="<?= $custom_in_outs ? 8 : ($tsf_custom_summary ? 10 : 11); ?>"></td>
			</tr>
		<?php endif; ?>
	</tfoot>
</table>

<?php if (!empty($is_merged)): ?>

	<pagebreak />
<?php endif; ?>