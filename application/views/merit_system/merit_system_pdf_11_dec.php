<div class="page-wrapper">
<style>
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
				<h4 class="page-title"><?php echo $branch_name ?> - Merit Sheet (<?php echo $month_f ?>/<?php echo $year_f ?>)</h4>
			</div>
		</div>
    <p>Generated at <b><?php echo date("d/m/Y H:i:s"); ?></b> by <b><?php echo $current_user['first_name'] ?></b></p>
		<div class="row card-box">
			<?php echo $filters; ?>
			<div class="col-md-12">
				<div class="mycontainer">
					<div class="table-responsive freeze-table">
						<table style="font-size: 13px" class="table table-striped">
							<thead>
								<tr>
									<th style="font-size: 13px;">Name</th>
									<th>Totals</th>
									<?php foreach ($days as $d) : ?>
										<th style="font-size: 11px;" class="text-center <?php if ($d['holiday']) : ?>holiday<?php endif ?>">
											<span <?php if ($d['holiday']) : ?> data-toggle="tooltip" data-html="true" data-placement="top" data-original-title="<?php echo $d['holiday_name'] ?>" <?php endif ?>>
												<b><?php echo $d['date']; ?></b><br />
												<?php echo $d['day']; ?>
											</span>
										</th>
									<?php endforeach; ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($employees as $emp) : ?>
									<tr>
										<td><b>
													<?php echo $emp['first_name']; ?>

											</b><br /> <?php echo $emp['special_id']; ?>

											<br />

										</td>
										<td><b><?php echo $emp["total_points"] ?></b></td>
										<?php foreach ($emp['offenses'] as $offense) : ?>
											<td class="text-center">
												<b class="<?php if (!$offense['is_offense']) : ?>strike<?php endif ?>" data-toggle="tooltip" data-original-title="<?php echo $offense['offenses_today'] ?>" data-html="true">
													<?php echo $offense["sign"] . $offense['points']; ?>
												</b><br />
											</td>
										<?php endforeach; ?>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<div class="col-md-12">
				<nav style="float:right" aria-label="Page navigation">
					<ul class="pagination">
						<?php if (isset($page) && $page > 1) : ?>
							<li class="page-item"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $page - 1 ?>">Previous</a></li>
						<?php endif; ?>
						<?php if (isset($total_pages)) : ?>
							<?php for ($x = 1; $x <= $total_pages; $x++) : ?>
								<?php if ($page == $x) : ?>
									<?php $active = "active" ?>
								<?php else : ?>
									<?php $active = "" ?>
								<?php endif ?>
								<li class="page-item <?php echo $active ?>"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $x ?>"><?php echo $x ?></a></li>
							<?php endfor ?>
						<?php endif ?>
						<?php if (isset($page) && isset($total_pages) && $page < $total_pages) : ?>
							<li class="page-item"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $page + 1 ?>">Next</a></li>
						<?php endif; ?>
					</ul>
				</nav>
			</div>
		</div>
	</div>