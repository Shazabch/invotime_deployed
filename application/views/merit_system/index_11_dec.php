<div class="page-wrapper">
	<style type="text/css">
		.btn.disabled,
		.btn[disabled],
		fieldset[disabled] .btn {
			opacity: 0.3
		}

		.strike {
			text-decoration: line-through;
			text-decoration-color: red;
		}

		.holiday {
			color: red;
		}
	</style>
	<div class="content container-fluid">
		<div class="row">
			<div class="col-xs-4">
				<h4 class="page-title">Merit Sheet</h4>
			</div>
		</div>
		<div class="row card-box">
			<?php echo $filters; ?>
			<div class="col-md-12">
				<a class="btn btn-primary m-b-10" target="_blank" href="<?php echo $merit_system_export_url ?>">Export as PDF</a>
			</div>

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
												<a href="<?php echo base_url() ?>overview/employee_report/<?php echo $emp['id']; ?>">
													<?php echo $emp['first_name']; ?>
												</a>

											</b><br /> <?php echo $emp['special_id']; ?>

											<br />

											<div style="min-width:150px !important">
												<a title="Clocking Data" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/manual_clocking_new?month=<?php echo $_REQUEST['month'] ?>&year=<?php echo $_REQUEST["year"] ?>&emp=<?php echo $emp["id"] ?>"><i style="font-size:15px" class="fa fa-hourglass-half"></i></a>
												<a title="Employee Report" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/employee_report/<?php echo $emp["id"] ?>?<?php echo "month=" . $_REQUEST['month'] ?>&year=<?php echo $_REQUEST["year"] ?>"><i style="font-size:15px" class="fa fa-clock"></i></a>
												<a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>summary/view/<?php echo $emp["id"] ?>?from=<?= $first_day_f ?>&to=<?= $last_day_f ?>"><i style="font-size:15px" class="fa fa-address-card"></i></a>
												<button title="Approve All Offenses" data-toggle="tooltip" class="btn btn-default btn-xs approve_all_button" data-emp-id="<?php echo $emp['id']; ?>" data-month="<?php echo $selected_month; ?>" data-year="<?php echo $selected_year; ?>"><i style="font-size:15px" class="fa fa-check"></i></button>
											</div>
										</td>
										<td><b><?php echo $emp["total_points"] ?></b></td>
										<?php foreach ($emp['offenses'] as $offense) : ?>
											<td class="text-center">
												<b class="<?php if (!$offense['is_offense']) : ?>strike<?php endif ?>" data-toggle="tooltip" data-original-title="<?php echo $offense['offenses_today'] ?>" data-html="true">
													<?php echo $offense["sign"] . $offense['points']; ?>
												</b><br />
												<div class="btn-group btn-group-xs" style="min-width: 45px">
													<button type="button" class="btn btn-success status_btn btn_check" <?php if ($offense['is_offense']) : ?>disabled<?php endif ?> data-emp-id="<?php echo $offense['id']; ?>" data-offense_date="<?php echo $offense['day']; ?>" data-is_offense="1">
														<span class="fa fa-check"></span>
													</button>
													<button type="button" class="btn btn-danger status_btn btn_close" <?php if (!$offense['is_offense']) : ?> disabled <?php endif ?> data-emp-id="<?php echo $offense['id']; ?>" data-offense_date="<?php echo $offense['day']; ?>" data-is_offense="0">
														<span class="fa fa-close"></span>
													</button>
												</div>
												<button type="button" class="btn btn-info btn-xs update_offense_btn" data-toggle="modal" data-target="#updateOffenseModal" data-empid="<?php echo $offense['id'] ?>" data-date="<?php echo $offense["day"] ?>" data-points="<?php echo $offense["points"] ?>" data-sign="<?php echo $offense["sign"] ?>" data-offense="<?php echo $offense["offense"] ?>">
													<span class="fa fa-edit"></span>
												</button>
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

	<div id="updateOffenseModal" class="modal fade" role="dialog">
		<div class="modal-dialog modal-sm">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Edit Offense</h4>
				</div>
				<div class="modal-body" id="inputbox">
					<div class="row">
						<form id="updateOffenseForm">
							<div class="col-md-12">
								<div class="form-group">
									<label class="control-label">Offense<span class="text-danger">*</span></label>
									<input class="form-control" type="text" id="offense" required="" name="offense" autocomplete="off">
								</div>
								<div class="form-group">
									<label class="control-label">Points</label>
									<input class="form-control" type="number" id="points" required="" name="points" autocomplete="off">
								</div>
							</div>
							<input type="hidden" name="empid" id="empid">
							<input type="hidden" name="date" id="date">
							<div class="col-md-12">
								<div class="checkbox">
									<label><input type="checkbox" id="minus-checkbox" name="minus_offense" value="-"><b>Minus Offense</b></label>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<button class="btn btn-primary" type="submit">Update</button>
									<button class="btn btn-danger" type="button" style="display: none;" id="removeBtn">Remove</button>
								</div>
							</div>
						</form>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(".status_btn").on("click", function(e) {
		const btn = $(this);
		const emp_id = $(this).attr("data-emp-id");
		const date = $(this).attr("data-offense_date");
		const is_offense = $(this).attr("data-is_offense");

		$.ajax({
			url: "<?php echo base_url() ?>merit_system/change_status",
			type: "POST",
			data: JSON.stringify({
				"employee_id": emp_id,
				"date": date,
				"is_offense": is_offense
			}),
			contentType: "application/json",
			success: function(result) {
				btn.prop("disabled", true);
				btn.siblings().prop("disabled", false);
				btn.parent().siblings("b").toggleClass('strike');
			}
		});
	});



	$(".approve_all_button").on("click", function(e) {
		const btn = $(this);
		const emp_id = $(this).attr('data-emp-id');
		const month = $(this).attr('data-month');
		const year = $(this).attr('data-year');

		$.ajax({
			type: "POST",
			url: "<?php echo base_url() ?>merit_system/approve_all_offenses",
			data: {
				'id': emp_id,
				'month': month,
				'year': year
			},
			success: function(result) {
				btn.closest('td').siblings().find('.btn_close').prop("disabled", false);
				btn.closest('td').siblings().find('.btn_check').prop("disabled", true);
				$.notify(`Success: ${result.msg}`, {
					position: "top center",
					className: 'success',
					style: 'bootstrap',
					gap: 20,
					autoHide: true
				});
			}
		});
	});

	$(document).on("click", ".freeze-table table:first .update_offense_btn", function() {
		$("#removeBtn").hide();
		const button = $(this);
		const updateData = $(this).data();

		if (updateData.offense !== "") {
			$("#removeBtn").show();
		}

		if (updateData.sign === "-") {
			$("#minus-checkbox").prop("checked", true);
		} else {
			$("#minus-checkbox").prop("checked", false);
		}

		$("#empid").val(updateData.empid);
		$("#date").val(updateData.date);
		$("#offense").val(updateData.offense);
		$("#points").val(updateData.points);
	});

	$("#updateOffenseForm").submit(function(e) {
		$("#inputbox").LoadingOverlay("show");
		e.preventDefault();
		const formData = $(this).serializeArray();
		$.ajax({
			type: "POST",
			url: "<?php echo base_url() ?>merit_system/update_offense",
			data: JSON.stringify(formData),
			contentType: "application/json",
			success: function(result) {
				$("#inputbox").LoadingOverlay("hide");
				$('#updateOffenseModal').modal('hide');
				if (result) {
					$.notify(
						"Success: overtime changed successfully! Reload page to see changes.", {
							position: "top center",
							className: 'success',
							style: 'bootstrap',
							gap: 20,
							autoHide: true
						}
					);
				}
			}
		});
	});

	$("#removeBtn").click(function(e) {
		$("#inputbox").LoadingOverlay("show");
		e.preventDefault();
		const formData = $("#updateOffenseForm").serializeArray();
		$.ajax({
			type: "POST",
			url: "<?php echo base_url() ?>merit_system/remove_offense",
			data: JSON.stringify(formData),
			contentType: "application/json",
			success: function(result) {
				$("#inputbox").LoadingOverlay("hide");
				$('#updateOffenseModal').modal('hide');
				if (result) {
					$.notify(
						"Success: overtime removed successfully! Reload page to see changes.", {
							position: "top center",
							className: 'success',
							style: 'bootstrap',
							gap: 20,
							autoHide: true
						}
					);
				}
			}
		});
	});

	$(document).ready(function() {
		$(".freeze-table").freezeTable({
			'columnNum': 1,
			'shadow': true,
			'fixedNavbar': '.header',
			'scrollBar': true
		});
	});
</script>