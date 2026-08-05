<div>
	<h4><?php echo $employee->first_name; ?> (<?php echo $employee->special_id; ?>) - Full Summary (<?php echo $from_f ?> to <?php echo $to_f ?>)</h4>
	<p><b>Position</b>: <?php echo $employee->position; ?>, <b>Department</b>: <?php echo $employee->department; ?> | Generated at <b><?php echo date("d/m/Y H:i:s"); ?></b> by <b><?php echo get_user()["first_name"]; ?></b></p>

</div>

<table style="page-break-inside: avoid;">
	<thead>
		<tr>
			<th>Date</th>
			<th style="width: 50px;">Shift</th>
			<th>In</th>
			<th>Out</th>
			<?php if ($custom_in_outs): ?>
				<th>In</th>
				<th>Out</th>
			<?php endif; ?>
			<!-- <th>Hours</th> -->
			<th>Total Hours</th>
			<th>Work Hours</th>
			<th>Break Hours</th>
			<th>Late Hours</th>
			<th>Late (Break)</th>
			<th>Early Out</th>
			<!-- <th>Short Hours</th> -->
			<th>OT</th>
			<?php if (!$custom_in_outs): ?>
				<th>OT(M)</th>
			<?php endif; ?>
			<th>OT<br>(PHx2)</th>
			<th>OT<br>(PHx3)</th>
			<th>OT<br>(RD)</th>
			<th>OT<br>(OFF)</th>
			<th>Days</th>
			<?php if (!$custom_in_outs): ?>
				<th>Trip(A)</th>
				<th>Trip(B)</th>
				<th>Late Reason</th>
			<?php endif; ?>
			<th>Remark</th>
		</tr>
	</thead>
	<tbody>
		<?php $count = 1; $page = 1; $records = 34; foreach($dates as $date_key => $d){ if ($custom_in_outs) {$count++;}?>
			<?php foreach($d->clockings as $key => $clock){ $row_span = ceil(count($d->clockings) / 2); if($key%2 == 0){ if (!$custom_in_outs) {$count++;} ?>
				<tr>
					<?php if($key == 0){ ?>
						<td rowspan="<?php echo $row_span;?>" class="text-center date" style="vertical-align: middle; <?php echo in_array($d->date, $public_holidays) ? 'color: red;' : ''; ?>"><b><?php echo $clock->day_f; ?></b></td>
					<?php } ?>
					<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>"><?php if($key%2 != 1){ echo $clock->code;}else{
						echo "Break";
					} ?>  </td>

					<?php if ($custom_in_outs) : ?>
						<?php $clock_in_check = admin_add_edit_check_clock_in(isset($d->in_outs_id[0]) ? $d->in_outs_id[0] : null); ?>
						<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>">
							<?php if ($clock_in_check == 1) { ?>
								<b class="text-danger"><?= $d->in_outs[0] ?? '' ?></b>
							<?php } else { ?>
								<?= $d->in_outs[0] ?? '' ?>
							<?php } ?>
						</td>
						<?php $clock_in_check = admin_add_edit_check_clock_in(isset($d->in_outs_id[1]) ? $d->in_outs_id[1] : null); ?>
						<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>">
							<?php if ($clock_in_check == 1) { ?>
								<b class="text-danger"><?= $d->in_outs[1] ?? '' ?></b>
							<?php } else { ?>
								<?= $d->in_outs[1] ?? '' ?>
							<?php } ?>
						</td>
						<?php $clock_in_check = admin_add_edit_check_clock_in(isset($d->in_outs_id[2]) ? $d->in_outs_id[2] : null); ?>
						<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>">
							<?php if ($clock_in_check == 1) { ?>
								<b class="text-danger"><?= $d->in_outs[2] ?? '' ?></b>
							<?php } else { ?>
								<?= $d->in_outs[2] ?? '' ?>
							<?php } ?>
						</td>
						<?php $clock_in_check = admin_add_edit_check_clock_in(isset($d->in_outs_id[3]) ? $d->in_outs_id[3] : null); ?>
						<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>">
							<?php if ($clock_in_check == 1) { ?>
								<b class="text-danger"><?= $d->in_outs[3] ?? '' ?></b>
							<?php } else { ?>
								<?= $d->in_outs[3] ?? '' ?>
							<?php } ?>
						</td>
					<?php else: ?>
						<!-- modified by Ali -->
						<?php $clock_in_check = admin_add_edit_check_clock_in(isset($clock->clock_in_id) ? $clock->clock_in_id : null); ?>
						<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>">
							<?php if ($clock_in_check == 1) { ?>
								<b class="text-danger"><?php echo $clock->clock_in; ?></b>
							<?php } else { ?>
								<?php echo $clock->clock_in; ?>
							<?php } ?>
						</td>

						<?php $clock_out_check = admin_add_edit_check_clock_out(isset($clock->clock_out_id) ? $clock->clock_out_id : null); ?>
						<td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>">
							<?php if ($clock_out_check == 1) { ?>
								<b class="text-danger"><?php echo $clock->clock_out; ?></b>
							<?php } else { ?>
								<?php echo $clock->clock_out; ?>
							<?php } ?>
						</td>
						<!-- Modified by Ali -->
					<?php endif; ?>

					<!-- <td class="text-center <?php if($key%2 == 1){echo 'text-danger';}?>"><?php if($clock->clock_out == "") {echo ""; }else{ echo $clock->total_time; }?></td> -->
					<?php if($key == 0){ ?>
						<td rowspan="<?php echo $row_span;?>" class="text-center" style="vertical-align: middle"><?php echo $d->total_hours; ?></td>
						<td rowspan="<?php echo $row_span;?>" class="text-center" style="vertical-align: middle"><?php echo $d->work_hours; ?></td>
						<td rowspan="<?php echo $row_span;?>" class="text-center" style="vertical-align: middle"><?php if($d->break_hours != "00:00"){echo $d->break_hours;} ?></td>
						<td rowspan="<?php echo $row_span;?>" class="text-center <?php if(!$d->is_late){echo 'strike';} ?>" style="vertical-align: middle"><?php if($d->late_hours != "00:00"){echo $d->late_hours;} ?></td>
						<td rowspan="<?php echo $row_span;?>" class="text-center <?php if(!$d->is_late_break){echo 'strike';} ?>" style="vertical-align: middle"><?php if($d->break_late_hours != "00:00"){echo $d->break_late_hours;} ?></td>
						<td rowspan="<?php echo $row_span;?>" class="text-center <?php if(!$d->is_early_out){echo 'strike';} ?>" style="vertical-align: middle"><?php if($d->early_out != "00:00"){echo $d->early_out;} ?></td>
						<!-- <td rowspan="<?php echo $row_span;?>" class="text-center" style="vertical-align: middle"><?php if($d->short_hours != "00:00"){echo $d->short_hours;} ?></td> -->

						<!-- simple OT -->
						<!-- <td rowspan="<?php echo $row_span;?>" class="text-center <?php if(!$d->is_ot){echo 'strike';} ?>" style="vertical-align: middle;"><?php if(!in_array($d->date,$public_holidays) && !in_array($d->day_name, $rest_days) && !in_array($d->day_name, $off_days) && $d->is_shift == 'true' && !$d->is_replaced_ph) echo ($d->is_ot) ? add_time_minus($d->overtime, $d->overtime_m) : $d->overtime ;  ?></td> -->
						<td rowspan="<?php echo $row_span ?>" class="text-center" style="vertical-align: middle">
							<?php if(!in_array($d->date,$public_holidays) && !in_array($d->day_name, $rest_days) && !in_array($d->day_name, $off_days) && $d->is_shift == 'true' && !$d->is_replaced_ph && !$d->is_rest_day) : ?>
								<?php if($d->is_shift == 'false') : ?>
									<span class="text-danger countOT"><?php echo $d->overtime_m ?></span>
								<?php elseif($d->is_shift == 'true') : ?>
									<?php if($d->is_ot) : ?>
										<span class="<?php echo (!empty($d->overtime_m) || $d->is_extra_ot == true ? "text-danger" : "") ?> countOT"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
									<?php else : ?>
										<?php if(!empty($d->overtime)) : ?>
											<span class="strike <?php echo ($d->is_extra_ot == true ? "text-danger" : "" ) ?>"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
										<?php endif ?>
										<span class="text-danger countOT"><?php echo $d->overtime_m ?></span>
									<?php endif ?>
								<?php endif ?>
							<?php endif ?>
						</td>
						<?php if(!$custom_in_outs): ?>
							<td rowspan="<?php echo $row_span;?>" class="text-center" style="vertical-align: middle"></td>
						<?php endif; ?>
						<!-- OT(PH) x2 -->
						<!-- <td rowspan="<?php echo $row_span;?>" class="text-center <?php if(!$d->is_ot){echo 'strike';} ?>" style="vertical-align: middle"><?php if(in_array($d->date,$public_holidays)) echo ($d->is_ot) ? add_time_minus($d->overtime, $d->overtime_m) : $d->overtime ;  ?></td> -->
						<td rowspan="<?php echo $row_span ?>" class="text-center" style="vertical-align: middle">
							<?php if($d->x2 && (in_array($d->date,$public_holidays) || $d->is_replaced_ph)) : ?>
								
									<?php if($d->is_ot) : ?>
										<span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo $d->overtime_ph_x2 ?></span>
									<?php else : ?>
										<?php if(!empty($d->overtime)) : ?>
											<span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
										<?php endif ?>
										<span class="text-danger"><?php echo $d->overtime_m ?></span>
									<?php endif ?>
									
							<?php endif ?>
						</td>
						<!-- OT(PH) x3 -->
						<!-- <td rowspan="<?php echo $row_span;?>" class="text-center <?php if(!$d->is_ot){echo 'strike';} ?>" style="vertical-align: middle"><?php if(in_array($d->date,$public_holidays)) echo ($d->is_ot) ? add_time_minus($d->overtime, $d->overtime_m) : $d->overtime ;  ?></td> -->
						<td rowspan="<?php echo $row_span ?>" class="text-center" style="vertical-align: middle">
							<?php if($d->x3 && (in_array($d->date,$public_holidays) || $d->is_replaced_ph)) : ?>
								
									<?php if($d->is_ot) : ?>
										<span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo $d->overtime_ph_x3 ?></span>
									<?php else : ?>
										<?php if(!empty($d->overtime)) : ?>
											<span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
										<?php endif ?>
										<span class="text-danger"><?php echo $d->overtime_m ?></span>
									<?php endif ?>
									
							<?php endif ?>
						</td>
						<!-- OT(RD) -->
						<!-- <td rowspan="<?php echo $row_span;?>" class="text-center <?php if(!$d->is_ot){echo 'strike';} ?>" style="vertical-align: middle"><?php if(!in_array($d->date,$public_holidays) && (in_array($d->day_name, $rest_days) || $d->is_shift == 'false')) echo ($d->is_ot || $d->is_shift == 'false') ? add_time($d->overtime, $d->overtime_m) : $d->overtime ;  ?></td> -->
						<td rowspan="<?php echo $row_span ?>" class="text-center" style="vertical-align: middle">
							<?php if ($d->is_rest_day) : ?>
									<?php if($d->is_ot) : ?>
										<span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
									<?php else : ?>
										<?php if(!empty($d->overtime)) : ?>
											<span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
										<?php endif ?>
										<span class="text-danger"><?php echo $d->overtime_m ?></span>
									<?php endif ?>
								<?php endif; ?>
						</td>
						<!-- OT(OFF) -->
						<td rowspan="<?php echo $row_span ?>" class="text-center" style="vertical-align: middle">
							<?php if (!in_array($d->date,$public_holidays) && (in_array($d->day_name, $off_days))) : ?>
									<?php if($d->is_ot) : ?>
										<span class="<?php echo (!empty($d->overtime_m) ? "text-danger" : "") ?>"><?php echo add_time_minus($d->overtime, $d->overtime_m) ?></span>
									<?php else : ?>
										<?php if(!empty($d->overtime)) : ?>
											<span class="strike"><?php echo $d->overtime ?></span><?php echo (!empty($d->overtime_m)) ? ", " : "" ?>
										<?php endif ?>
										<span class="text-danger"><?php echo $d->overtime_m ?></span>
									<?php endif ?>
								<?php endif; ?>
						</td>
						<td rowspan="<?php echo $row_span;?>" class="text-center" style="vertical-align: middle"><?php echo $d->days; ?></td>
						<?php if(!$custom_in_outs): ?>
							<td rowspan="<?php echo $row_span;?>" class="text-center" style="vertical-align: middle"><?php echo $d->trip_a; ?></td>
							<td rowspan="<?php echo $row_span;?>" class="text-center" style="vertical-align: middle"><?php echo $d->trip_b; ?></td>
							<td rowspan="<?php echo $row_span;?>" class="text-center" style="vertical-align: middle"><?php echo $clock->reason; ?></td>
						<?php endif; ?>
						<td rowspan="<?php echo $row_span;?>" class="text-center remark" style="vertical-align: middle"><?php if(in_array($d->date,$public_holidays)){echo $public_holidays_names[array_search($d->date,$public_holidays)] ;}else{ echo $clock->remark; }?></td>
					<?php
						}
						if (!$custom_in_outs){
							if(($key == 0 && strlen($clock->remark) > 15) || strlen($clock->code) > 5){
							$count++;
							if(strlen($clock->remark) > 33){
								$count++;
							}
							}
						}
					?>
				</tr>

			<?php }} 
				
				
				if($count > $records && $date_key != count($dates) - 1){
				$page++;
				if($page > 1){
					$records = 40;
				}
				$count = 0; ?>
			</tbody>
		</table>
		<table style="page-break-inside: avoid; page-break-before: always;">
			<thead>
				<tr>
					<th>Date</th>
					<th style="width: 50px;">Shift</th>
					<th>In</th>
					<th>Out</th>
					<?php if ($custom_in_outs): ?>
						<th>In</th>
						<th>Out</th>
					<?php endif; ?>
					<!-- <th>Hours</th> -->
					<th>Total Hours</th>
					<th>Work Hours</th>
					<th>Break Hours</th>
					<th>Late Hours</th>
					<th>Late (Break)</th>
					<th>Early Out</th>
					<!-- <th>Short Hours</th> -->
					<th>OT</th>
					<?php if (!$custom_in_outs): ?>
						<th>OT(M)</th>
					<?php endif; ?>
					<th>OT<br>(PHx2)</th>
					<th>OT<br>(PHx3)</th>
					<th>OT<br>(RD)</th>
					<th>OT<br>(OFF)</th>
					<th>Days</th>
					<?php if (!$custom_in_outs): ?>
						<th>Trip(A)</th>
						<th>Trip(B)</th>
						<th>Late Reason</th>
					<?php endif; ?>
					<th>Remark</th>
				</tr>
			</thead>
			<tbody>
			<?php } ?>

		<?php } ?>
			
				<tr>
					<td colspan="<?= $custom_in_outs ? 5 : 3; ?>"></td>
					<td class="text-center"><b>Total</b></td>
					<td class="text-center"><?php echo $total;?></td>
					<td class="text-center"><?php echo $work;?></td>
					<td class="text-center"><?php echo $break;?></td>
					<td class="text-center"><?php echo $late;?></td>
					<td class="text-center"><?php echo $break_late;?></td>
					<td class="text-center"><?php echo $total_early;?></td>
					<!-- <td class="text-center"><?php echo $total_short;?></td> -->
					<td class="text-center <?php if($lateness_time != $lateness_time_deducted){ echo 'strike'; } ?>" colspan="<?= $custom_in_outs ? 1 : 2; ?>"><?php echo $month_overtime;?></td>
					<td class="text-center"><?php echo $month_overtime_ph_x2;?></td>
					<td class="text-center"><?php echo $month_overtime_ph_x3;?></td>
					<td class="text-center"><?php echo $month_overtime_rd;?></td>
					<td class="text-center"><?php echo $month_overtime_off;?></td>
					<td class="text-center"><?php echo $total_days; ?></td>
					<?php if(!$custom_in_outs): ?>
						<td class="text-center"><?php echo $total_trip_a;?></td>
						<td class="text-center"><?php echo $total_trip_b;?></td>
						<td colspan="2"></td>
					<?php else: ?>
						<td colspan="1"></td>
					<?php endif; ?>
					
				</tr>
				<tr>
					<td colspan="<?= $custom_in_outs ? 6 : 4; ?>"></td>
					<td colspan="3" class="text-center"><b>Lateness Time</b></td>
					<td colspan="3" class="text-center <?php if($lateness_time != $lateness_time_deducted){ echo 'strike'; } ?>"><?php echo $lateness_time; ?></td>
					<td colspan="<?= $custom_in_outs ? 7 : 11; ?>"></td>
				</tr>
					<?php if($lateness_time != $lateness_time_deducted){ ?>
					<tr>
					<td colspan="<?= $custom_in_outs ? 6 : 4; ?>"></td>
					<td colspan="3" class="text-center"><b>After Deduction</b></td>
					<td colspan="3" class="text-center"><?php echo $lateness_time_deducted; ?></td>
					<td colspan="2" class="text-center"><?php echo $month_overtime_deducted; ?></td>
					<td colspan="<?= $custom_in_outs ? 5 : 9; ?>"></td>
				</tr>
					<?php } ?>
			
		</tbody>
	</table>
	<?php if (isset($is_merged) && $is_merged) : ?>
		<div class="page-break"></div>
	<?php endif; ?>