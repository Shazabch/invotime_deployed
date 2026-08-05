<style type="text/css">
	.left {
		float: left;
	}

	.right {
		float: right;
	}

	.flex {
		display: flex;
	}

	.clearfix {
		overflow: auto;
	}

	.clearfix::after {
		content: "";
		clear: both;
		display: table;
	}

	.w-full {
		width: 100%;
	}

	.w-0-33 {
		width: 33.3333%;
	}

	.text-right {
		text-align: right;
	}

	.text-left {
		text-align: left;
	}

	.mb-0 {
		margin-bottom: 0;
	}

	.bold {
		font-weight: bold;
	}

	.col-container {
		display: table;
		width: 100%;
	}

	.col {
		display: table-cell;
	}

	.responsive-image {
		width: 100%;
		height: auto;
	}

	.text-sm {
		font-size: 0.75rem;
	}

	.text-xs {
		font-size: 0.5rem;
	}

	.text-md {
		font-size: 1rem;
	}

	.text-lg {
		font-size: 1.25rem;
	}

	.text-xl {
		font-size: 1.5rem;
	}

	p {
		margin: 0;
	}

	.mr-4 {
		margin-right: 1rem;
	}

	.ml-4 {
		margin-left: 1rem;
	}

	.mt-4 {
		margin-top: 1rem;
	}

	.mt-2 {
		margin-top: 0.5rem;
	}

	.mt-8 {
		margin-top: 2rem;
	}

	table {
		width: 100%
	}

	.cut-text {
		text-overflow: ellipsis;
		overflow: hidden;
		white-space: nowrap;
	}

	hr.dotted {
		border-top: 1px dotted black;
	}

	.sign-container {
		width: 150px;
	}
</style>
<?php foreach ($records as $main_index => $record) : ?>
	<div class="text-sm" style="height: 498px;">
		<section class="header">
			<p class="text-right mb-0 bold">BORANG PENILAIAN PRESTASI BULANAN STAF</p>
			<hr>
		</section>

		<section id="company-details">
			<table>
				<tbody>
					<tr>
						<td style="width: 198px;">
							<img src="<?= $record["company_logo"] ?>" alt="" class="responsive-image">
						</td>
						<td>
							<div class="text-left ml-4">
								<p class="bold"><?= $record["company_data"]->name ?></p>
								<p><?= $record["company_data"]->address ?></p>
								<p><?= $record["company_data"]->phone ?></p>
							</div>
						</td>
						<td></td>
					</tr>
				</tbody>
			</table>
		</section>

		<section>
			<p>Name&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <?= $record["employee"]->first_name; ?><br>Jawatan&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <?= $record["employee"]->position; ?><br>Cawangan&nbsp; &nbsp; &nbsp;: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <?= $record["employee"]->branch_name; ?><br>Bulan&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; : &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <?= $record["month"] ?><br><br></p>
		</section>

		<section>
			<p style="background-color: lightgray; text-align: center;">Jenis Kesalahan yang dilakukan (Diisi oleh wakil bahagian yang terlibat)</p>
		</section>
		<br>
		<section style="border: 1px solid;">
			<table>
				<tbody>
					<tr>
						<td width="100px">A) Kesalahan:</td>
						<td class="text-left text-sm">
							<table>
								<tbody>
									<?php $offenses_list_count = count($record["offenses_data"]["list_of_offenses"]) ?>
									<?php for ($i = 0; $i < $offenses_list_count; $i += 5) : ?>
										<tr>
											<?php for ($j = $i; $j < $offenses_list_count && $j < ($i + 5); $j++) : ?>
												<td>
													<?= $record["offenses_data"]["list_of_offenses"][$j]["date"] ?> - <?= map_offense($record["offenses_data"]["list_of_offenses"][$j]["offense"]) ?>
												</td>
											<?php endfor ?>
										</tr>
									<?php endfor ?>
								</tbody>
							</table>
							<?php if (count($record["offenses_data"]["list_of_offenses"]) === 0) : ?>
								<hr class="dotted">
							<?php endif ?>
						</td>
					</tr>
				</tbody>
			</table>
			<table>
				<tbody>
					<tr>
						<td>B) Jumlah Merit: <?= (100 - $record["offenses_data"]["total_points"]) ?></td>
						<td>Tarikh: <?= date("d/m/Y") ?></td>
					</tr>
				</tbody>
			</table>
		</section>
		<section class="mt-2">
			<table>
				<tbody>
					<tr>
						<td>Lateness - Late In/Early Out</td>
						<td>HDP - Half Day Paid</td>
						<td>HUP - Half Day Unpaid</td>
					</tr>
					<tr>
						<td>ML - Medical Leave</td>
						<td>No In/Out - Missing In/Out</td>
						<td>LB - Late Break</td>
					</tr>
					<tr>
						<td colspan="3">A/UL Absent/Unpaid Leave</td>
					</tr>
				</tbody>
			</table>
		</section>
		<section class="mt-2">
			<table>
				<thead>
					<tr>
						<td style="width: 200px;">Dinilai oleh:</td>
						<td>Diterima oleh:</td>
						<td></td>
					</tr>
				</thead>
				<tbody>
					<td style="width: 200px; vertical-align: bottom;">
						<?php if ($record['signature'] != "") : ?>
							<div class="mt-2 sign-container">
								<img src="<?php echo $record['signature'] ?>" alt="signature" class="responsive-image" />
							</div>
							<p class="mt-2"><?php echo $record['position'] != '' ? $record['position'] : '----------------------------------------' ?></p>
						<?php else : ?>
							<p class="mt-8"><?php echo $record['position'] != '' ? $record['position'] : '----------------------------------------' ?></p>
						<?php endif ?>
					</td>
					<td style="vertical-align: bottom;">
						<p class="<?php echo $record['signature'] != '' ? 'mt-2' : 'mt-8' ?>">----------------------------------------</p>
					</td>
					<td style="text-align: right; vertical-align:bottom">
						<span><?php echo ($main_index === 0) ? 'Company' : 'Staff' ?> copy</span>
					</td>
				</tbody>
			</table>
		</section>
	</div>
	<?php if ($main_index === 0) : ?>
		<div class="mt-4"></div>
	<?php endif ?>
<?php endforeach ?>