<div class="inv-emp-wizard-shell emp-modern-page" ng-app="myApp" ng-controller="empCtrl" ng-init="get_datalist_options()">
	<style>
		/* =========================================================
		   INVOTIME EMPLOYEE WIZARD
		   Fully prefixed: .inv-emp-wizard-shell
		========================================================= */
		.inv-emp-wizard-shell .avatar img:hover {
			cursor: pointer;
		}

		/* Force original large modal size - override old theme rules */
		.inv-emp-wizard-shell .modal.custom-modal .modal-dialog,
		.inv-emp-wizard-shell #add_employee .modal-dialog,
		.inv-emp-wizard-shell #edit_employee .modal-dialog {
			width: 1280px !important;
			max-width: calc(100vw - 56px) !important;
			margin: 24px auto !important;
		}

		.inv-emp-wizard-shell .modal.custom-modal .modal-content {
			width: 100% !important;
			border: 0 !important;
			border-radius: 16px !important;
			/* overflow:visible !important; */
			box-shadow: 0 24px 80px rgba(15, 23, 42, .28) !important;
			background: #fff !important;
		}

		.inv-emp-wizard-shell .modal.custom-modal .modal-body {
			padding: 0 !important;
			background: #f5f7fb !important;
			max-height: calc(100vh - 150px) !important;
			overflow-y: auto !important;
		}

		.inv-emp-wizard-shell .modal.custom-modal>.close {
			position: absolute !important;
			right: 22px !important;
			top: 18px !important;
			z-index: 20 !important;
			width: 44px !important;
			height: 44px !important;
			border-radius: 50% !important;
			background: rgba(255, 255, 255, .18) !important;
			color: #fff !important;
			opacity: 1 !important;
			font-size: 28px !important;
			line-height: 40px !important;
			text-shadow: none !important;
		}

		.inv-emp-wizard-shell .emp-modern-form-header {
			display: flex;
			align-items: center;
			gap: 14px;
			padding: 24px 78px 24px 30px;
			background:linear-gradient(to right, #00c5fb 0%, #0253cc 100%);
			color: #fff;
			border-radius: 16px 16px 0 0;
		}

		.inv-emp-wizard-shell .emp-modern-header-icon {
			width: 52px;
			height: 52px;
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 14px;
			background: rgba(255, 255, 255, .17);
			font-size: 21px;
		}

		.inv-emp-wizard-shell .emp-modern-form-header h3 {
			margin: 0;
			font-size: 22px;
			font-weight: 700;
			letter-spacing: .2px;
		}

		.inv-emp-wizard-shell .emp-modern-form-header p {
			margin: 4px 0 0;
			opacity: .9;
			font-size: 13px;
		}

		/* Wizard navigation */
		.inv-emp-wizard-shell .inv-emp-wizard-nav {
			display: flex;
			align-items: flex-start;
			gap: 0;
			padding: 20px 28px 16px;
			background: #fff;
			border-bottom: 1px solid #e8edf3;
			overflow-x: auto;
		}

		.inv-emp-wizard-shell .inv-emp-wizard-step {
			position: relative;
			flex: 1 0 150px;
			min-width: 150px;
			padding: 0 10px;
			text-align: center;
			border: 0;
			background: transparent;
			color: #94a3b8;
			cursor: pointer;
			outline: 0;
		}

		.inv-emp-wizard-shell .inv-emp-wizard-step:after {
			content: "";
			position: absolute;
			height: 2px;
			left: calc(50% + 25px);
			right: calc(-50% + 25px);
			top: 19px;
			background: #e2e8f0;
		}

		.inv-emp-wizard-shell .inv-emp-wizard-step:last-child:after {
			display: none;
		}

		.inv-emp-wizard-shell .inv-emp-wizard-step .inv-emp-wizard-num {
			position: relative;
			z-index: 2;
			width: 40px;
			height: 40px;
			margin: 0 auto 7px;
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 50%;
			background: #edf2f7;
			color: #64748b;
			font-size: 13px;
			font-weight: 800;
			transition: .2s ease;
		}

		.inv-emp-wizard-shell .inv-emp-wizard-step .inv-emp-wizard-label {
			display: block;
			font-size: 11px;
			font-weight: 700;
			white-space: nowrap;
		}

		.inv-emp-wizard-shell .inv-emp-wizard-step.is-active {
			color: #0f766e;
		}

		.inv-emp-wizard-shell .inv-emp-wizard-step.is-active .inv-emp-wizard-num {
			background: #0f766e;
			color: #fff;
			box-shadow: 0 6px 16px rgba(15, 118, 110, .25);
		}

		.inv-emp-wizard-shell .inv-emp-wizard-step.is-done {
			color: #0891b2;
		}

		.inv-emp-wizard-shell .inv-emp-wizard-step.is-done .inv-emp-wizard-num {
			background: #0891b2;
			color: #fff;
		}

		/* Step content */
		.inv-emp-wizard-shell .inv-emp-wizard-content {
			padding: 26px 30px 8px;
		}

		.inv-emp-wizard-shell .inv-emp-wizard-pane {
			display: none;
		}

		.inv-emp-wizard-shell .inv-emp-wizard-pane.is-active {
			display: block;
			animation: invEmpFade .22s ease;
		}

		@keyframes invEmpFade {
			from {
				opacity: 0;
				transform: translateY(5px);
			}

			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		.inv-emp-wizard-shell .inv-emp-wizard-pane>h2 {
			margin: 0 0 22px !important;
			padding: 0 0 12px !important;
			border-bottom: 1px solid #e7edf4;
			font-size: 20px !important;
			font-weight: 700 !important;
			color: #1e293b !important;
		}

		.inv-emp-wizard-shell .inv-emp-wizard-pane>br {
			display: none;
		}

		.inv-emp-wizard-shell .inv-emp-wizard-pane .row {
			background: #fff;
			border: 1px solid #e8edf3;
			border-radius: 14px;
			padding: 18px 12px 0;
			margin: 0 0 18px;
			box-shadow: 0 4px 14px rgba(15, 23, 42, .025);
		}

		.inv-emp-wizard-shell .form-group {
			margin-bottom: 18px;
		}

		.inv-emp-wizard-shell .form-group .control-label {
			font-size: 12px;
			font-weight: 700;
			color: #475569;
			margin-bottom: 7px;
			letter-spacing: .15px;
		}

		.inv-emp-wizard-shell .form-control {
			min-height: 42px;
			border-radius: 9px;
			border: 1px solid #d8e0e9;
			box-shadow: none;
			transition: border-color .18s, box-shadow .18s;
		}

		.inv-emp-wizard-shell .form-control:focus {
			border-color: #0891b2;
			box-shadow: 0 0 0 3px rgba(8, 145, 178, .10);
		}

		.inv-emp-wizard-shell .select2-container {
			width: 100% !important;
		}

		.inv-emp-wizard-shell .select2-container--default .select2-selection--single,
		.inv-emp-wizard-shell .select2-container--default .select2-selection--multiple {
			min-height: 42px !important;
			border: 1px solid #d8e0e9 !important;
			border-radius: 9px !important;
		}

		/* Wizard footer */
		.inv-emp-wizard-shell .inv-emp-wizard-actions {
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
			padding: 18px 30px 24px;
			background: #fff;
			border-top: 1px solid #e8edf3;
		}

		.inv-emp-wizard-shell .inv-emp-wizard-actions .btn {
			min-width: 128px;
			border-radius: 9px;
			font-weight: 700;
			padding: 10px 18px;
		}

		.inv-emp-wizard-shell .inv-emp-wizard-actions .inv-emp-next {
			background: linear-gradient(135deg, #0f766e, #0891b2);
			border: 0;
			color: #fff;
			box-shadow: 0 8px 18px rgba(8, 145, 178, .20);
		}

		.inv-emp-wizard-shell .emp-modern-submit {
			min-width: 180px !important;
			border-radius: 9px !important;
			font-weight: 700 !important;
			padding: 10px 20px !important;
		}

		/* Race / Religion / Nationality */
		.inv-emp-wizard-shell .emp-id-field {
			margin-bottom: 18px;
		}

		.inv-emp-wizard-shell .emp-id-field .control-label {
			display: flex;
			align-items: center;
			gap: 7px;
		}

		.inv-emp-wizard-shell .emp-id-field .control-label i {
			color: #0891b2;
		}

		.inv-emp-wizard-shell .emp-id-select-wrap {
			position: relative;
		}

		.inv-emp-wizard-shell .emp-id-select {
			width: 100%;
			height: 42px;
			padding: 0 42px 0 13px;
			border: 1px solid #d8e0e9;
			border-radius: 9px;
			background: #fff;
			color: #334155;
			font-size: 14px;
			outline: 0;
			appearance: none;
			-webkit-appearance: none;
		}

		.inv-emp-wizard-shell .emp-id-select:focus {
			border-color: #0891b2;
			box-shadow: 0 0 0 3px rgba(8, 145, 178, .10);
		}

		.inv-emp-wizard-shell .emp-id-select-wrap:after {
			content: "\f107";
			font-family: FontAwesome;
			position: absolute;
			right: 15px;
			top: 50%;
			transform: translateY(-50%);
			color: #64748b;
			pointer-events: none;
		}

		.inv-emp-wizard-shell .emp-id-custom-box {
			margin-top: 9px;
			padding: 12px;
			border: 1px solid #cceaf0;
			border-radius: 10px;
			background: #f4fbfc;
		}

		.inv-emp-wizard-shell .emp-id-custom-title {
			display: flex;
			align-items: center;
			gap: 7px;
			margin-bottom: 8px;
			font-size: 12px;
			font-weight: 700;
			color: #0f766e;
		}

		.inv-emp-wizard-shell .emp-id-custom-box .form-control {
			background: #fff;
		}

		.inv-emp-wizard-shell .emp-id-hint {
			margin-top: 6px;
			font-size: 11px;
			color: #64748b;
		}

		@media (max-width:991px) {

			.inv-emp-wizard-shell .modal.custom-modal .modal-dialog,
			.inv-emp-wizard-shell #add_employee .modal-dialog,
			.inv-emp-wizard-shell #edit_employee .modal-dialog {
				width: calc(100vw - 24px) !important;
				max-width: none !important;
				margin: 12px auto !important;
			}

			.inv-emp-wizard-shell .modal.custom-modal .modal-body {
				max-height: calc(100vh - 120px) !important;
			}

			.inv-emp-wizard-shell .inv-emp-wizard-nav {
				padding: 15px 12px;
			}

			.inv-emp-wizard-shell .inv-emp-wizard-content {
				padding: 20px 16px 4px;
			}

			.inv-emp-wizard-shell .inv-emp-wizard-pane .row {
				padding: 14px 10px 0;
			}
		}

		@media (max-width:767px) {
			.inv-emp-wizard-shell .emp-modern-form-header {
				padding: 18px 60px 18px 18px;
			}

			.inv-emp-wizard-shell .inv-emp-wizard-step {
				flex: 0 0 105px;
				min-width: 105px;
				padding: 0 4px;
			}

			.inv-emp-wizard-shell .inv-emp-wizard-step .inv-emp-wizard-label {
				font-size: 10px;
			}

			.inv-emp-wizard-shell .inv-emp-wizard-step:after {
				right: calc(-50% + 20px);
			}

			.inv-emp-wizard-shell .inv-emp-wizard-actions {
				padding: 14px 16px 18px;
			}
		}
	</style>
	<style>
		/* =========================================================
   INV EMPLOYEE TABLE V2 - modern table view
   Prefixed under .inv-emp-wizard-shell — page-scoped only
========================================================= */
		.inv-emp-wizard-shell .inv-emp-table2 {
			border-radius: 16px;
			overflow: hidden;
			box-shadow: 0 6px 24px rgba(15, 23, 42, .06);
			border: 1px solid #e8edf3;
			background: #fff;
			padding: 10px;
		}

		/* Toolbar row above the table */
		.inv-emp-wizard-shell .page-title {
			font-weight: 800;
			color: #123b61;
			letter-spacing: .2px;
		}

		.inv-emp-wizard-shell .m-b-30 .btn.rounded {
			border: 0 !important;
			border-radius: 10px !important;
			font-weight: 700;
			padding: 9px 18px;
			transition: transform .18s ease, box-shadow .18s ease, opacity .18s ease;
			box-shadow: 0 6px 14px rgba(8, 126, 164, .18);
		}

		.inv-emp-wizard-shell .m-b-30 .btn.rounded:hover {
			transform: translateY(-2px);
			box-shadow: 0 10px 22px rgba(8, 126, 164, .26);
			opacity: .95;
		}

		.inv-emp-wizard-shell .m-b-30 .btn-primary.rounded {
			background: linear-gradient(to right, #00c5fb 0%, #0253cc 100%);
		}

		/* Table header (column titles) */
		.inv-emp-wizard-shell .inv-emp-table2 table.datatable thead th {
			background: linear-gradient(180deg, #f4f9fc 0%, #eef4f9 100%);
			color: #123b61;
			font-size: 11.5px;
			font-weight: 800;
			text-transform: uppercase;
			letter-spacing: .5px;
			border-bottom: 2px solid #dce7f0 !important;
			padding: 12px;
			position: sticky;
			top: 0;
			z-index: 2;
		}

		/* DataTables' auto-generated per-column search row (2nd row in <thead>) */
		.inv-emp-wizard-shell .inv-emp-table2 table.datatable thead tr:nth-child(2) th {
			padding: 10px 12px 16px;
		}

		.inv-emp-wizard-shell .inv-emp-table2 table.datatable thead tr:nth-child(2) th input {
			padding: 8px 10px;
			height: auto;
			border-radius: 8px;
			border: 1px solid #d8e0e9;
		}

		/* Rows */
		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody tr {
			transition: background-color .15s ease, transform .15s ease, box-shadow .15s ease;
			animation: invEmpRowIn .3s ease both;
		}

		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody tr:nth-child(2n) {
			background-color: #f8fbfd;
		}

		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody tr:hover {
			background-color: #eaf7fb !important;
			box-shadow: inset 3px 0 0 #0891b2;
		}

		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody td {
			padding: 18px 14px;
			vertical-align: middle;
			border-top: 1px solid #eef2f7 !important;
			font-size: 13px;
			color: #334155;
		}

		/* Employee name / link */
		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody td h2 {
			margin: 0;
			font-size: 14px;
			line-height: 1.4;
		}

		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody td h2 a {
			transition: color .15s ease;
		}

		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody td h2 a:hover {
			color: #12b7d4 !important;
		}

		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody td h2 span {
			display: block;
			font-size: 11px;
			font-weight: 600;
			color: #94a3b8;
			margin-top: 2px;
		}

		/* Space the action-icon row away from the name text above it */
		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody td h2>a>div {
			margin-top: 10px !important;
		}

		/* Row action icon buttons (clocking / report / summary / edit) */
		.inv-emp-wizard-shell .inv-emp-table2 .btn-default.btn-xs {
			border-radius: 7px;
			border: 1px solid #e2e8f0;
			margin: 3px 4px 3px 0;
			padding: 5px 8px;
			transition: transform .15s ease, background .15s ease, border-color .15s ease;
		}

		.inv-emp-wizard-shell .inv-emp-table2 .btn-default.btn-xs:hover {
			background: #0891b2;
			border-color: #0891b2;
			transform: translateY(-1px) scale(1.06);
		}

		.inv-emp-wizard-shell .inv-emp-table2 .btn-default.btn-xs:hover i {
			color: #fff !important;
		}

		/* Section / Group / Outlet emphasis (column-position based, no markup needed) */
		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody td:nth-child(5),
		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody td:nth-child(7),
		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody td:nth-child(8) {
			font-weight: 600;
		}

		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody td:nth-child(8) {
			color: #0f766e;
		}

		/* Staggered row entrance animation */
		@keyframes invEmpRowIn {
			from {
				opacity: 0;
				transform: translateY(6px);
			}

			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody tr:nth-child(1) {
			animation-delay: .02s;
		}

		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody tr:nth-child(2) {
			animation-delay: .05s;
		}

		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody tr:nth-child(3) {
			animation-delay: .08s;
		}

		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody tr:nth-child(4) {
			animation-delay: .11s;
		}

		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody tr:nth-child(5) {
			animation-delay: .14s;
		}

		.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody tr:nth-child(n+6) {
			animation-delay: .16s;
		}

		/* Respect reduced motion */
		@media (prefers-reduced-motion: reduce) {
			.inv-emp-wizard-shell .inv-emp-table2 table.datatable tbody tr {
				animation: none !important;
			}
		}

		/* DataTables' own search/length/pagination controls, restyled to match */
		.inv-emp-wizard-shell .inv-emp-table2 .dataTables_filter input,
		.inv-emp-wizard-shell .inv-emp-table2 .dataTables_length select {
			border-radius: 8px;
			border: 1px solid #d8e0e9;
			padding: 6px 10px;
		}

		.inv-emp-wizard-shell .inv-emp-table2 .dataTables_paginate .paginate_button.current {
			background: linear-gradient(120deg, #087ea4, #12b7d4) !important;
			border: 0 !important;
			color: #fff !important;
			border-radius: 7px !important;
		}
	</style>

	<div class="page-wrapper">
		<div class="content container-fluid">
			<div class="row">
				<div class="col-xs-4">
					<h4 class="page-title">Active Employees</h4>
				</div>
				<div class="col-xs-8 text-right m-b-30">
					<a target="_blank" href="<?php echo base_url("employees/export?type=excel") ?>" class="btn btn-primary rounded">Excel</a>
					<a target="_blank" href="<?php echo base_url("employees/export") ?>" class="btn btn-primary rounded">PDF</a>
					<a href="#" class="btn btn-primary rounded" data-toggle="modal" ng-click="setAddData()" data-target="#add_employee"><i class="fa fa-plus"></i> Add Employee</a>
				</div>
			</div>
			<div class="row" ng-show="mainTable">
				<div class="col-md-12">
					<div class="table-responsive inv-emp-table2">
						<table id="datatable_emp" class="table table-striped custom-table datatable">
							<!-- <col width="50">
  						<col width="50"> -->
							<thead>
								<tr>
									<th>Name</th>
									<!-- <th>Photo</th> -->
									<th>Employee ID</th>
									<th>Position</th>
									<th>Department</th>
									<th>Section</th>
									<th>Joining Date</th>
									<th>Group</th>
									<th>Outlet</th>
									<!-- <th class="text-right">Action</th> -->
								</tr>
							</thead>
							<tbody>
								<?php foreach ($employees as $emp) { ?>
									<tr>
										<td>
											<!-- <a href="<?php echo base_url(); ?>profile/index/<?php echo $emp->id; ?>" class="avatar"><?php echo strtoupper($emp->first_name[0]); ?></a> -->
											<h2><a style="color:#009ce7" href="<?php echo base_url(); ?>profile/index/<?php echo $emp->id; ?>"><b><?php echo $emp->first_name; ?></b><span><?php echo $emp->job_name; ?>-<?php echo $emp->id; ?></span>
													<br />

													<div style="min-width:150px !important">
														<?php if (is_page_permitted('manual_clocking_new')) : ?>
															<a title="Clocking Data" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/manual_clocking_new?emp=<?php echo $emp->id ?>"><i style="font-size:15px" class="fa fa-hourglass-half"></i></a>
														<?php endif ?>
														<?php if (is_page_permitted('employee_report')) : ?>
															<a title="Employee Report" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/employee_report/<?php echo $emp->id ?>"><i style="font-size:15px" class="fa fa-clock"></i></a>
														<?php endif ?>
														<?php if (is_page_permitted('view')) : ?>
															<a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>summary/view/<?php echo $emp->id ?>"><i style="font-size:15px" class="fa fa-address-card"></i></a>
														<?php endif ?>
														<a href="javascript:void(0)" class="btn btn-default btn-xs" data-toggle="modal" data-target="#edit_employee" ng-click="setEditData('<?php echo $emp->id; ?>')"><i style="font-size:15px" class="fa fa-pencil-square"></i></a>
														<!-- <a href="javascript:void(0)" class="btn btn-default btn-xs" data-toggle="modal" data-target="#delete_employee" ng-click="setDeleteID('<?php echo $emp->id; ?>')"><i style="font-size:15px" class="fa fa-trash"></i></a> -->


													</div>
												</a></h2>
										</td>

										<!-- In your table cell -->
										<!-- <td>
											<?php if (!empty($emp->face_image_url)): ?>
												<div class="avatar">
													<img src="<?php echo htmlspecialchars($emp->face_image_url); ?>"
														alt="<?php echo htmlspecialchars($emp->first_name); ?>"
														style="width: 50px; height: 50px;"
														onerror="this.src='<?php echo base_url(); ?>assets/img/user.jpg'"
														data-toggle="tooltip"
														title="Click to view larger">

												</div>
											<?php endif; ?>
										</td> -->
										<td><?php echo $emp->special_id; ?></td>
										<td><?php echo $emp->title; ?></td>
										<td><?php echo $emp->department_name; ?></td>
										<td><?php echo $emp->section_title; ?></td>
										<td data-sort="<?php echo $emp->joining_date_sort; ?>"><?php echo $emp->joining_date; ?></td>
										<td><?php echo $emp->group_names; ?></td>
										<td><?php echo $emp->branch_name; ?></td>
										<!-- <td class="text-right">
											<div class="dropdown">
												<a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
												<ul class="dropdown-menu pull-right">
													<li><a href="javascript:void(0)" data-toggle="modal" data-target="#edit_employee" ng-click="setEditData('<?php echo $emp->id; ?>')"><i class="fa fa-pencil m-r-5"></i> Edit</a></li>

													<?php if (get_user()["permissions_level"] == "Company" || $emp->permissions_level == "Personal") : ?>
													<li><a href="javascript:void(0)" data-toggle="modal" data-target="#delete_employee" ng-click="setDeleteID('<?php echo $emp->id; ?>')"><i class="fa fa-trash-o m-r-5"></i> Delete</a></li>
													<?php endif; ?>


												</ul>
											</div>
										</td> -->
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div id="filteredArea"></div>
		</div>
	</div>
	<div id="add_employee" class="modal custom-modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<div class="modal-content modal-lg">
				<div class="emp-modern-form-header">
					<div class="emp-modern-header-icon"><i class="fa fa-user-plus"></i></div>
					<div>
						<h3>Add Employee</h3>
						<p>Create a complete employee profile</p>
					</div>
				</div>
				<div class="modal-body">
					<form class="m-b-30" name="emp_form" id="emp_form" ng-submit="onSubmit(emp_form.$valid)">
						<br />
						<h2>Basic Information</h2><br />
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Name <span class="text-danger">*</span></label>
									<input class="form-control" type="text" ng-model="addModel.first_name" id="addFirstName" required="">
									<span id="firstNameError_add" class="text-danger" style="font-size:12px; display:none;"></span>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Gender <span class="text-danger">*</span></label>
									<select class="select" ng-model="addModel.sex">
										<option value="Male">Male</option>
										<option value="Female">Female</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Employee ID <span class="text-danger">*</span></label>
									<input class="form-control" type="text" ng-model="addModel.special_id" required="">
								</div>
							</div>

							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Outlet <span class="text-danger">*</span></label>
									<select class="select" ng-model="addModel.branch_id" required="">
										<option value=''>Select Outlet</option>
										<?php foreach ($branches as $br) { ?>
											<option value="<?php echo $br->id; ?>"><?php echo $br->name; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Payroll Outlet</label>
									<select class="select" ng-model="addModel.payroll_branch_id">
										<option value=''>Same as Outlet</option>
										<?php foreach ($branches as $br) { ?>
											<option value="<?php echo $br->id; ?>"><?php echo $br->name; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Department <span class="text-danger">*</span></label>
									<select class="select" ng-model="addModel.department_id" required="">
										<option value=''>Select Department</option>
										<?php foreach ($departments as $dep) { ?>
											<option value="<?php echo $dep->id; ?>"><?php echo $dep->name; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group" id="add_designation">
									<label class="control-label">Position <span class="text-danger">*</span></label>
									<select class="select" ng-model="addModel.position_id" required="">
										<option value=''>Select Position</option>
										<!-- <option ng-repeat="pos in positions" value="{{pos.id}}">{{pos.title}}</option> -->
										<?php foreach ($positions as $pos) { ?>
											<option value="<?php echo $pos->id; ?>"><?php echo $pos->title; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Role <span class="text-danger">*</span></label>
									<select class="select" ng-model="addModel.role_id" required="">
										<option value=''>Select Role</option>
										<?php foreach ($roles as $rol) { ?>
											<option value="<?php echo $rol->id; ?>"><?php echo $rol->job_name; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group" id="add_section">
									<label class="control-label">Section</label>
									<select class="select">
										<option value=''>Select Section</option>
										<?php foreach ($sections as $sec) { ?>
											<option value="<?php echo $sec->id; ?>"><?php echo $sec->title; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Level</label>
									<select class="select" ng-model="addModel.level">
										<option value="junior_staff">Junior Staff</option>
										<option value="senior_staff">Senior Staff</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Groups</label>
									<select class="select" ng-model="addModel.groups" multiple="multiple">
										<?php foreach ($employee_groups as $employee_group) { ?>
											<option value="<?php echo $employee_group->id; ?>"><?php echo $employee_group->name; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>

							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Marital Status</label>
									<select class="select" ng-model="addModel.marital_status">
										<option value="single">Single</option>
										<option value="married">Married</option>
										<option value="widowed">Widowed</option>
										<option value="separated">Separated</option>
										<option value="divorced">Divorced</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Date of Birth</label>
									<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="addModel.dob" id="dob"></div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Place of Birth</label>
									<input class="form-control" type="text" ng-model="addModel.pob">
								</div>
							</div>
							<!-- Modern Identity Fields -->
							<div class="col-sm-6">
								<div class="form-group emp-id-field">
									<label class="control-label"><i class="fa fa-users"></i> Race</label>
									<div class="emp-id-select-wrap">
										<select class="emp-id-select" ng-model="identitySelect.add.race" ng-change="onIdentitySelectChange('add','race')">
											<option value="">Select Race</option>
											<option value="Malay">Malay</option>
											<option value="Chinese">Chinese</option>
											<option value="Indian">Indian</option>
											<option value="__other__">Other / Custom Value</option>
										</select>
									</div>
									<div class="emp-id-custom-box" ng-if="identityCustom.add.race">
										<div class="emp-id-custom-title"><i class="fa fa-pencil"></i> Enter Actual Race</div>
										<input class="form-control" type="text" ng-model="identityCustomValue.add.race" ng-change="updateCustomIdentityValue('add','race')" placeholder="Example: Kadazan">
										<div class="emp-id-hint">The actual value is saved directly.</div>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group emp-id-field">
									<label class="control-label"><i class="fa fa-heart"></i> Religion</label>
									<div class="emp-id-select-wrap">
										<select class="emp-id-select" ng-model="identitySelect.add.religion" ng-change="onIdentitySelectChange('add','religion')">
											<option value="">Select Religion</option>
											<option value="muslim">Muslim</option>
											<option value="buddhist">Buddhist</option>
											<option value="christian">Christian</option>
											<option value="hindu">Hindu</option>
											<option value="__other__">Other / Custom Value</option>
										</select>
									</div>
									<div class="emp-id-custom-box" ng-if="identityCustom.add.religion">
										<div class="emp-id-custom-title"><i class="fa fa-pencil"></i> Enter Actual Religion</div>
										<input class="form-control" type="text" ng-model="identityCustomValue.add.religion" ng-change="updateCustomIdentityValue('add','religion')" placeholder="Example: Sikh">
										<div class="emp-id-hint">The actual value is saved directly.</div>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group emp-id-field">
									<label class="control-label"><i class="fa fa-flag"></i> Nationality</label>
									<div class="emp-id-select-wrap">
										<select class="emp-id-select" ng-model="identitySelect.add.nationality" ng-change="onIdentitySelectChange('add','nationality')">
											<option value="">Select Nationality</option>
											<option value="Malaysian">Malaysian</option>
											<option value="__other__">Other / Custom Value</option>
										</select>
									</div>
									<div class="emp-id-custom-box" ng-if="identityCustom.add.nationality">
										<div class="emp-id-custom-title"><i class="fa fa-pencil"></i> Enter Actual Nationality</div>
										<input class="form-control" type="text" ng-model="identityCustomValue.add.nationality" ng-change="updateCustomIdentityValue('add','nationality')" placeholder="Example: Indonesian">
										<div class="emp-id-hint">The actual value is saved directly.</div>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">NIRC/Passport</label>
									<input class="form-control" type="text" ng-model="addModel.ic_passport">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">IC No.</label>
									<input class="form-control" type="text" ng-model="addModel.ic_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Old IC No.</label>
									<input class="form-control" type="text" ng-model="addModel.old_ic_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Email</label>
									<input class="form-control" type="email" ng-model="addModel.email">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Password</label>
									<input class="form-control" type="password" ng-model="addModel.password" autocomplete="new-password">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Device Role</label>
									<select class="select" ng-model="addModel.device_role">
										<option value="">Select Device Role</option>
										<option value="Administrator">Manager</option>
										<option value="User">User</option>
										<option value="Register">Register</option>
										<option value="Querier">Querier</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Face Device Password</label>
									<input class="form-control" type="text" ng-model="addModel.device_password">
								</div>
							</div>
						</div>
						<br />
						<h2>Departmental Information</h2><br />
						<div class="row">


							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">RFID Card Number</label>
									<input class="form-control" type="text" ng-model="addModel.qr_barcode">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Job Grade</label>
									<input class="form-control" type="text" ng-model="addModel.grade">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Employment Type</label>
									<select class="select" ng-model="addModel.employment_type">
										<option value="full_time">Full Time</option>
										<option value="part_time">Part Time</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Employee Type</label>
									<select class="select" ng-model="addModel.employee_type">
										<option value="m">Malaysian</option>
										<option value="n">Non Malaysian</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent Resident</label>
									<select class="select" ng-model="addModel.permanent_resident">
										<option value="yes">Yes</option>
										<option value="no">No</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Elected To Contribute On</label>
									<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="addModel.etc_on" id="etc_on"></div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Elected To Contribute Under</label>
									<select class="select" ng-model="addModel.etc_under">
										<option value="na">N/A</option>
										<option value="para_3">Paragraph 3</option>
										<option value="para_6">Paragraph 6</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Hired On</label>
									<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="addModel.hired_on" id="doj"></div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Basic Wage</label>
									<input class="form-control" type="text" ng-model="addModel.basic_wage">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">EPF</label>
									<input class="form-control" type="text" ng-model="addModel.epf_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">SOCSO</label>
									<input class="form-control" type="text" ng-model="addModel.socso">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">EIS</label>
									<input class="form-control" type="text" ng-model="addModel.eis">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Overtime</label>
									<select class="select" ng-model="addModel.is_ot">
										<option value="yes">Yes</option>
										<option value="no">No</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Early Overtime</label>
									<select class="select" ng-model="addModel.is_early_ot">
										<option value="yes">Yes</option>
										<option value="no">No</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<div class="checkbox">
										<label><input type="checkbox" value="" ng-model="addModel.is_daily_waged"><b>Is Daily Waged</b></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<div class="checkbox">
										<label><input type="checkbox" value="" ng-model="addModel.is_shift_hours"><b>Is Shift Hours</b></label>
									</div>
								</div>
							</div>
						</div>
						<br />
						<h2>Contact Information</h2><br />
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Temporary Address</label>
									<input class="form-control" type="text" ng-model="addModel.temp_address">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Temporary City</label>
									<input class="form-control" type="text" ng-model="addModel.temp_address_city">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Temporary Postcode</label>
									<input class="form-control" type="text" ng-model="addModel.temp_address_postcode">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Temporary State</label>
									<input class="form-control" type="text" ng-model="addModel.temp_address_state">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent Address</label>
									<input class="form-control" type="text" ng-model="addModel.perm_address">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent City</label>
									<input class="form-control" type="text" ng-model="addModel.perm_address_city">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent Postcode</label>
									<input class="form-control" type="text" ng-model="addModel.perm_address_postcode">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent State</label>
									<input class="form-control" type="text" ng-model="addModel.perm_address_state">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Telephone</label>
									<input class="form-control" type="text" ng-model="addModel.telephone">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Mobile</label>
									<input class="form-control" type="text" ng-model="addModel.mobile">
								</div>
							</div>
						</div>
						<br />
						<h2>Other Information</h2><br />
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Income Tax Number</label>
									<input class="form-control" type="text" ng-model="addModel.income_tax_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Income Tax Branch</label>
									<input class="form-control" type="text" ng-model="addModel.income_tax_branch">
								</div>
							</div>

							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Bank Name</label>
									<select class="select" ng-model="addModel.employee_bank_id">
										<option value=''>Select Bank Name</option>
										<?php foreach ($employee_banks as $eb) { ?>
											<option value="<?php echo $eb->id; ?>"><?php echo $eb->name; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Bank Account Number</label>
									<input class="form-control" type="text" ng-model="addModel.bank_account_no">
								</div>
							</div>

							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">License Class</label>
									<input class="form-control" type="text" ng-model="addModel.license_class">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">License Number</label>
									<input class="form-control" type="text" ng-model="addModel.license_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">License Expiry</label>
									<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="addModel.license_expiry" id="expiry"></div>
								</div>
							</div>

						</div>
						<br />
						<h2>Leaves</h2><br />
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Compassionate Leaves</label>
									<input class="form-control" type="number" ng-model="addModel.compassionate_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Paternity Leaves</label>
									<input class="form-control" type="number" ng-model="addModel.paternity_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Marriage Leaves</label>
									<input class="form-control" type="number" ng-model="addModel.marriage_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Hospitalisation Leaves</label>
									<input class="form-control" type="number" ng-model="addModel.hospitalisation_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Study Leaves</label>
									<input class="form-control" type="number" ng-model="addModel.study_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Replacement Leaves</label>
									<input class="form-control" type="number" ng-model="addModel.replacement_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Unpaid Leaves</label>
									<input class="form-control" type="number" ng-model="addModel.unpaid_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Emergency Leaves</label>
									<input class="form-control" type="number" ng-model="addModel.emergency_leaves">
								</div>
							</div>
						</div>
						<?php if ($company_id == 229) : ?>
							<br />
							<h2>Allowance Rates</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Daily Allowance</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.aa_rate">
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($company_id == 196) : ?>
							<br />
							<h2>Allowance Rates</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">MI/MO Rate</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.mi_mo_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Lateness Deduction < 99 Minutes</label>
												<input class="form-control" type="number" step="any" ng-model="addModel.lateness_deduction_99">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Lateness Deduction > 100 Minutes</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.lateness_deduction_100">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Rest Day Entitlement</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.rest_day_entitlement">
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($company_id == 66) : ?>
							<br />
							<h2>Allowance Rates</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">TA Rate</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.ta_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">MA Rate</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.ma_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">CA Rate</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.ca_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">SPA Rate</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.spa_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">ACA Rate</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.aca_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">FL Inc Rate</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.fl_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">C/wash Rate</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.cw_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">M/ope Rate</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.mo_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Shift1 Rate</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.shift1_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Shift2 Rate</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.shift2_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Shift3 Rate</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.shift3_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Attendance Allowance</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.aa_rate">
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($company_id == 215) : ?>
							<br />
							<h2>Allowance Rates</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Attendance Allowance</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.aa_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Night Shift Allowance</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.nsa_rate">
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($company_id == 146) : ?>
							<br />
							<h2>Meal Allowance Entitlement</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Minimum Worked Hours</label>
										<input class="form-control datetimepicker3" type="text" id="min_worked_hours_meal" name="min_worked_hours_meal" ng-model="addModel.min_worked_hours_meal" autocomplete="off">
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($company_id == 206) : ?>
							<br />
							<h2>Allowance Rates</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Food Allowance</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.food_rate">
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($company_id == 152) : ?>
							<br />
							<h2>Allowance Rates</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Special Allowance</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.aa_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Attendance Allowance</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.ta_rate">
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($company_id == 102) : ?>
							<br />
							<h2>Miscellaneous</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">OT Group</label>
										<select class="select" ng-model="addModel.ot_group">
											<option value=''>Select OT Group</option>
											<option value='day'>Day</option>
											<option value='hours'>Hours</option>
										</select>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Special Incentive</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.special_incentive">
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if (in_array($company_id, companies_allowed_for_att_all())) : ?>
							<br>
							<h2>Attendance Allowance</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Allowance</label>
										<select class="select" ng-model="addModel.is_att_all">
											<option value="yes">Yes</option>
											<option value="no">No</option>
										</select>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Code</label>
										<input class="form-control" type="text" ng-model="addModel.att_all_code">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Description</label>
										<input class="form-control" type="text" ng-model="addModel.att_all_desc">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Amount</label>
										<input class="form-control" type="number" ng-model="addModel.att_all_amount">
									</div>
								</div>
							</div>
						<?php endif ?>
						<?php if (in_array($company_id, companies_allowed_for_meal_allowance())) : ?>
							<br />
							<h2>Allowance Rates</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Meal Allowance</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.meal_rate">
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Day Shift Allowance</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.dsa_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Night Shift Allowance</label>
										<input class="form-control" type="number" step="any" ng-model="addModel.nsa_rate">
									</div>
								</div>
							</div>
						<?php endif ?>
						<div class="m-t-20 text-center">
							<button class="btn btn-primary emp-modern-submit" type="submit"><i class="fa fa-check"></i> Create Employee</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div id="edit_employee" class="modal custom-modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<div class="modal-content modal-lg">
				<div class="emp-modern-form-header">
					<div class="emp-modern-header-icon"><i class="fa fa-user"></i></div>
					<div>
						<h3>Edit Employee</h3>
						<p>Update employee information</p>
					</div>
				</div>
				<div class="modal-body">
					<form class="m-b-30" name="emp_edit_form" id="emp_edit_form" ng-submit="onSubmit2(emp_edit_form.$valid)">
						<br />
						<h2>General Information</h2><br />
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Name <span class="text-danger">*</span></label>
									<input class="form-control" type="text" ng-model="editModel.first_name" id="editFirstName" required="">
									<span id="firstNameError_edit" class="text-danger" style="font-size:12px; display:none;"></span>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Gender <span class="text-danger">*</span></label>
									<select class="select select-gender" ng-model="editModel.sex">
										<option value="Male">Male</option>
										<option value="Female">Female</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Employee ID <span class="text-danger">*</span></label>
									<input class="form-control" type="text" ng-model="editModel.special_id" required="">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Outlet <span class="text-danger">*</span></label>
									<select class="select select-outlet" ng-model="editModel.branch_id" required="">
										<option value=''>Select Outlet</option>
										<option ng-repeat="branch in branches" value="{{branch.id}}">{{branch.name}}</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Payroll Outlet</label>
									<select class="select select-payroll-outlet" ng-model="editModel.payroll_branch_id">
										<option value=''>Same as Outlet</option>
										<option ng-repeat="branch in branches" value="{{branch.id}}">{{branch.name}}</option>
									</select>
								</div>
							</div>
							<div id="transfer-fields" ng-hide="editModel.current_branch_id == editModel.branch_id">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Transfer Reason</label>
										<input class="form-control" type="text" ng-model="editModel.transfer_reason" id="transfer_reason">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Transfer Date</label>
										<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editModel.transfer_date" id="transfer_date" id="transfer_date"></div>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Department <span class="text-danger">*</span></label>
									<select class="select select-department" ng-model="editModel.department_id" required="">
										<option value=''>Select Department</option>
										<option ng-repeat="dep in departments" value="{{dep.id}}">{{dep.name}}</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group" id="edit_designation">
									<label class="control-label">Position <span class="text-danger">*</span></label>
									<select class="select select-position" ng-model="editModel.position_id" required="">
										<option value=''>Select Position</option>
										<option ng-repeat="pos in editPositions" value="{{pos.id}}">{{pos.title}}</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Role <span class="text-danger">*</span></label>
									<select class="select select-role" ng-model="editModel.role_id" required="">
										<option value=''>Select Role</option>
										<option ng-repeat="role in roles" value="{{role.id}}">{{role.job_name}}</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Section</label>
									<select class="select select-section" ng-model="editModel.section_id">
										<option value=''>Select section</option>
										<option ng-repeat="section in sections" value="{{section.id}}">{{section.title}}</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Level</label>
									<select class="select select-level" ng-model="editModel.level">
										<option value="junior_staff">Junior Staff</option>
										<option value="senior_staff">Senior Staff</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Groups</label>
									<select class="select multiple-selector" id="groups" multiple="multiple">
										<?php foreach ($employee_groups as $employee_group) { ?>
											<option value="<?php echo $employee_group->id; ?>"><?php echo $employee_group->name; ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Marital Status</label>
									<select class="select select-marital" ng-model="editModel.marital_status">
										<option value="single">Single</option>
										<option value="married">Married</option>
										<option value="widowed">Widowed</option>
										<option value="separated">Separated</option>
										<option value="divorced">Divorced</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Date of Birth</label>
									<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editModel.dob" id="dob_edit"></div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Place of Birth</label>
									<input class="form-control" type="text" ng-model="editModel.pob">
								</div>
							</div>
							<!-- Modern Identity Fields -->
							<div class="col-sm-6">
								<div class="form-group emp-id-field">
									<label class="control-label"><i class="fa fa-users"></i> Race</label>
									<div class="emp-id-select-wrap">
										<select class="emp-id-select" ng-model="identitySelect.edit.race" ng-change="onIdentitySelectChange('edit','race')">
											<option value="">Select Race</option>
											<option value="Malay">Malay</option>
											<option value="Chinese">Chinese</option>
											<option value="Indian">Indian</option>
											<option value="__other__">Other / Custom Value</option>
										</select>
									</div>
									<div class="emp-id-custom-box" ng-if="identityCustom.edit.race">
										<div class="emp-id-custom-title"><i class="fa fa-pencil"></i> Enter Actual Race</div>
										<input class="form-control" type="text" ng-model="identityCustomValue.edit.race" ng-change="updateCustomIdentityValue('edit','race')" placeholder="Enter actual race">
										<div class="emp-id-hint">The actual value is saved directly.</div>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group emp-id-field">
									<label class="control-label"><i class="fa fa-heart"></i> Religion</label>
									<div class="emp-id-select-wrap">
										<select class="emp-id-select" ng-model="identitySelect.edit.religion" ng-change="onIdentitySelectChange('edit','religion')">
											<option value="">Select Religion</option>
											<option value="muslim">Muslim</option>
											<option value="buddhist">Buddhist</option>
											<option value="christian">Christian</option>
											<option value="hindu">Hindu</option>
											<option value="__other__">Other / Custom Value</option>
										</select>
									</div>
									<div class="emp-id-custom-box" ng-if="identityCustom.edit.religion">
										<div class="emp-id-custom-title"><i class="fa fa-pencil"></i> Enter Actual Religion</div>
										<input class="form-control" type="text" ng-model="identityCustomValue.edit.religion" ng-change="updateCustomIdentityValue('edit','religion')" placeholder="Enter actual religion">
										<div class="emp-id-hint">The actual value is saved directly.</div>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group emp-id-field">
									<label class="control-label"><i class="fa fa-flag"></i> Nationality</label>
									<div class="emp-id-select-wrap">
										<select class="emp-id-select" ng-model="identitySelect.edit.nationality" ng-change="onIdentitySelectChange('edit','nationality')">
											<option value="">Select Nationality</option>
											<option value="Malaysian">Malaysian</option>
											<option value="__other__">Other / Custom Value</option>
										</select>
									</div>
									<div class="emp-id-custom-box" ng-if="identityCustom.edit.nationality">
										<div class="emp-id-custom-title"><i class="fa fa-pencil"></i> Enter Actual Nationality</div>
										<input class="form-control" type="text" ng-model="identityCustomValue.edit.nationality" ng-change="updateCustomIdentityValue('edit','nationality')" placeholder="Enter actual nationality">
										<div class="emp-id-hint">The actual value is saved directly.</div>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">NIRC/Passport</label>
									<input class="form-control" type="text" ng-model="editModel.ic_passport">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">IC No.</label>
									<input class="form-control" type="text" ng-model="editModel.ic_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Old IC No.</label>
									<input class="form-control" type="text" ng-model="editModel.old_ic_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Email</label>
									<input class="form-control" type="email" ng-model="editModel.email">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Password</label>
									<input class="form-control" type="password" ng-model="editModel.new_password" autocomplete="new-password">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Device Role</label>
									<select class="select select-device-role" ng-model="editModel.device_role">
										<option value="">Select Device Role</option>
										<option value=""></option>
										<option ng-repeat="(key, device_role) in device_roles" value="{{key}}">{{device_role}}</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Face Device Password</label>
									<input class="form-control" type="text" ng-model="editModel.device_password">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Employee Status</label>
									<select class="select select-status" ng-model="editModel.employee_status">
										<option value="active">Active</option>
										<option value="terminated">Terminated</option>
										<option value="resigned">Resigned</option>
									</select>
								</div>
							</div>
							<div id="terminated-fields" ng-show="editModel.employee_status == 'terminated'">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Termination Type</label>
										<input class="form-control" type="text" ng-model="editModel.termination_type">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Termination Date</label>
										<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editModel.termination_date" id="termination_date"></div>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Termination Reason</label>
										<select class="select select-reason" ng-model="editModel.termination_reason">
											<option value=''>Select Reason</option>
											<option value="{{r.id}}" ng-repeat="r in reasons">{{r.reason}}</option>
										</select>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Notice Date</label>
										<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editModel.termination_notice_date" id="termination_notice_date"></div>
									</div>
								</div>
							</div>
							<div id="resigned-fields" ng-show="editModel.employee_status == 'resigned'">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Resignation Type</label>
										<input class="form-control" type="text" ng-model="editModel.resignation_type">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Resignation Date</label>
										<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editModel.resignation_date" id="resignation_date"></div>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Resignation Reason</label>
										<input class="form-control" type="text" ng-model="editModel.resignation_reason">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Notice Date</label>
										<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editModel.resignation_notice_date" id="resignation_notice_date"></div>
									</div>
								</div>
							</div>
						</div>
						<br />
						<h2>Departmental Information</h2><br />
						<div class="row">





							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">RFID Card Number</label>
									<input class="form-control" type="text" ng-model="editModel.qr_barcode">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Job Grade</label>
									<input class="form-control" type="text" ng-model="editModel.grade">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Employment Type</label>
									<select class="select select-employment" ng-model="editModel.employment_type">
										<option value="full_time">Full Time</option>
										<option value="part_time">Part Time</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Employee Type</label>
									<select class="select select-type" ng-model="editModel.employee_type">
										<option value="m">Malaysian</option>
										<option value="n">Non Malaysian</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent Resident</label>
									<select class="select select-resident" ng-model="editModel.permanent_resident">
										<option value="yes">Yes</option>
										<option value="no">No</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Elected To Contribute On</label>
									<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editModel.etc_on" id="etc_on_edit"></div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Elected To Contribute Under</label>
									<select class="select select-etc" ng-model="editModel.etc_under">
										<option value="na">N/A</option>
										<option value="para_3">Paragraph 3</option>
										<option value="para_6">Paragraph 6</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Hired On</label>
									<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editModel.hired_on" id="doj_edit"></div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Basic Wage</label>
									<input class="form-control" type="text" ng-model="editModel.basic_wage">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">EPF</label>
									<input class="form-control" type="text" ng-model="editModel.epf_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">SOCSO</label>
									<input class="form-control" type="text" ng-model="editModel.socso">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">EIS</label>
									<input class="form-control" type="text" ng-model="editModel.eis">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Overtime</label>
									<select class="select select-overtime" ng-model="editModel.is_ot">
										<option value="yes">Yes</option>
										<option value="no">No</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Early Overtime</label>
									<select class="select select-early" ng-model="editModel.is_early_ot">
										<option value="yes">Yes</option>
										<option value="no">No</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<div class="checkbox">
										<label><input type="checkbox" value="" ng-model="editModel.is_daily_waged"><b>Is Daily Waged</b></label>
									</div>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<div class="checkbox">
										<label><input type="checkbox" value="" ng-model="editModel.is_shift_hours"><b>Is Shift Hours</b></label>
									</div>
								</div>
							</div>
						</div>
						<br />
						<h2>Contact Information</h2><br />
						<div class="row">

							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Temporary Address</label>
									<input class="form-control" type="text" ng-model="editModel.temp_address">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Temporary City</label>
									<input class="form-control" type="text" ng-model="editModel.temp_address_city">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Temporary Postcode</label>
									<input class="form-control" type="text" ng-model="editModel.temp_address_postcode">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Temporary State</label>
									<input class="form-control" type="text" ng-model="editModel.temp_address_state">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent Address</label>
									<input class="form-control" type="text" ng-model="editModel.perm_address">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent City</label>
									<input class="form-control" type="text" ng-model="editModel.perm_address_city">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent Postcode</label>
									<input class="form-control" type="text" ng-model="editModel.perm_address_postcode">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Permanent State</label>
									<input class="form-control" type="text" ng-model="editModel.perm_address_state">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Telephone</label>
									<input class="form-control" type="text" ng-model="editModel.telephone">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Mobile</label>
									<input class="form-control" type="text" ng-model="editModel.mobile">
								</div>
							</div>
						</div>
						<br />
						<h2>Other Information</h2><br />
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Income Tax Number</label>
									<input class="form-control" type="text" ng-model="editModel.income_tax_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Income Tax Branch</label>
									<input class="form-control" type="text" ng-model="editModel.income_tax_branch">
								</div>
							</div>

							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Bank Name</label>
									<select class="select select-bank" ng-model="editModel.employee_bank_id">
										<option value=''>Select Bank Name</option>
										<option ng-repeat="b in employee_banks" value="{{b.id}}">{{b.name}}</option>
									</select>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Bank Account Number</label>
									<input class="form-control" type="text" ng-model="editModel.bank_account_no">
								</div>
							</div>

							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">License Class</label>
									<input class="form-control" type="text" ng-model="editModel.license_class">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">License Number</label>
									<input class="form-control" type="text" ng-model="editModel.license_no">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">License Expiry</label>
									<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editModel.license_expiry" id="expiry_edit"></div>
								</div>
							</div>

						</div>
						<br />
						<h2>Leaves</h2><br />
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Compassionate Leaves</label>
									<input class="form-control" type="number" ng-model="editModel.compassionate_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Paternity Leaves</label>
									<input class="form-control" type="number" ng-model="editModel.paternity_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Marriage Leaves</label>
									<input class="form-control" type="number" ng-model="editModel.marriage_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Hospitalisation Leaves</label>
									<input class="form-control" type="number" ng-model="editModel.hospitalisation_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Study Leaves</label>
									<input class="form-control" type="number" ng-model="editModel.study_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Replacement Leaves</label>
									<input class="form-control" type="number" ng-model="editModel.replacement_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Unpaid Leaves</label>
									<input class="form-control" type="number" ng-model="editModel.unpaid_leaves">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="control-label">Emergency Leaves</label>
									<input class="form-control" type="number" ng-model="editModel.emergency_leaves">
								</div>
							</div>
						</div>
						<?php if ($company_id == 229) : ?>
							<br />
							<h2>Allowance Rates</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Daily Allowance</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.aa_rate">
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($company_id == 196) : ?>
							<br />
							<h2>Allowance Rates</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">MI/MO Rate</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.mi_mo_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Lateness Deduction < 99 Minutes</label>
												<input class="form-control" type="number" step="any" ng-model="editModel.lateness_deduction_99">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Lateness Deduction > 100 Minutes</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.lateness_deduction_100">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Rest Day Entitlement</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.rest_day_entitlement">
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($company_id == 66) : ?>
							<br />
							<h2>Allowance Rates</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">TA Rate</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.ta_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">MA Rate</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.ma_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">CA Rate</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.ca_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">SPA Rate</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.spa_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">ACA Rate</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.aca_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">FL Inc Rate</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.fl_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">C/wash Rate</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.cw_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">M/ope Rate</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.mo_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Shift1 Rate</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.shift1_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Shift2 Rate</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.shift2_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Shift3 Rate</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.shift3_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Attendance Allowance</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.aa_rate">
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($company_id == 215) : ?>
							<br />
							<h2>Allowance Rates</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Attendance Allowance</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.aa_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Night Shift Allowance</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.nsa_rate">
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($company_id == 146) : ?>
							<br />
							<h2>Meal Allowance Entitlement</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Minimum Worked Hours</label>
										<input class="form-control datetimepicker4" type="text" id="min_worked_hours_meal_edit" name="min_worked_hours_meal_edit" ng-model="editModel.min_worked_hours_meal" autocomplete="off">
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($company_id == 206) : ?>
							<br />
							<h2>Allowance Rates</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Food Allowance</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.food_rate">
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($company_id == 152) : ?>
							<br />
							<h2>Allowance Rates</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Special Allowance</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.aa_rate">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Attendance Allowance</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.ta_rate">
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($company_id == 102) : ?>
							<br />
							<h2>Miscellaneous</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">OT Group</label>
										<select class="select select-ot" ng-model="editModel.ot_group">
											<option value=''>Select OT Group</option>
											<option ng-repeat="ot_group in ot_groups" value="{{ot_group.key}}">{{ot_group.value}}</option>
										</select>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Special Incentive</label>
										<input class="form-control" type="number" step="any" ng-model="editModel.special_incentive">
									</div>
								</div>
							</div>
						<?php endif; ?>
						<?php if (in_array($company_id, companies_allowed_for_att_all())) : ?>
							<br>
							<h2>Attendance Allowance</h2><br />
							<div class="row">
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Allowance</label>
										<select class="select select-att-all" ng-model="editModel.is_att_all">
											<option value="yes">Yes</option>
											<option value="no">No</option>
										</select>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Code</label>
										<input class="form-control" type="text" ng-model="editModel.att_all_code">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Description</label>
										<input class="form-control" type="text" ng-model="editModel.att_all_desc">
									</div>
								</div>
								<div class="col-sm-6">
									<div class="form-group">
										<label class="control-label">Amount</label>
										<input class="form-control" type="number" ng-model="editModel.att_all_amount">
									</div>
								</div>
							</div>
						<?php endif ?>
						<?php if (in_array($company_id, companies_allowed_for_meal_allowance()) || in_array($company_id, companies_allowed_for_shift_allowance())) : ?>
							<br />
							<h2>Allowance Rates</h2><br />
							<?php if (in_array($company_id, companies_allowed_for_meal_allowance())) : ?>
								<div class="row">
									<div class="col-sm-6">
										<div class="form-group">
											<label class="control-label">Meal Allowance</label>
											<input class="form-control" type="number" step="any" ng-model="editModel.meal_rate">
										</div>
									</div>
								</div>
							<?php endif; ?>
							<?php if (in_array($company_id, companies_allowed_for_shift_allowance())) : ?>
								<div class="row">
									<div class="col-sm-6">
										<div class="form-group">
											<label class="control-label">Day Shift Allowance</label>
											<input class="form-control" type="number" step="any" ng-model="editModel.dsa_rate">
										</div>
									</div>
									<div class="col-sm-6">
										<div class="form-group">
											<label class="control-label">Night Shift Allowance</label>
											<input class="form-control" type="number" step="any" ng-model="editModel.nsa_rate">
										</div>
									</div>
								</div>
							<?php endif; ?>
						<?php endif; ?>
						<div class="m-t-20 text-center">
							<button class="btn btn-primary" type="submit">Save Changes</button>
							<button class="btn btn-primary" type="button" data-dismiss="modal" data-toggle="modal" data-target="#access_all_outlet" ng-disabled="sync_action === 'SetUserDataAll'">Access All Outlet</button>
							<button class="btn btn-danger" type="button" data-dismiss="modal" data-toggle="modal" data-target="#reset_device" ng-disabled="!user_device_id">Reset Device ID</button>
						</div>
						<datalist id="distinct-races">
							<option ng-repeat="race in distinct_races" value="{{race}}">
						</datalist>
						<datalist id="distinct-nationalities">
							<option ng-repeat="nationality in distinct_nationalities" value="{{nationality}}">
						</datalist>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div id="delete_employee" class="modal custom-modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog">
			<div class="modal-content modal-md">
				<div class="modal-header">
					<h4 class="modal-title">Delete Employee</h4>
				</div>
				<form>
					<div class="modal-body card-box">
						<p>Are you sure you want to delete this?</p>
						<div class="m-t-20"> <a href="#" class="btn btn-default" data-dismiss="modal">Close</a>
							<button type="submit" class="btn btn-danger" ng-click="delete_employee()">Delete</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div id="reset_device" class="modal custom-modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog">
			<div class="modal-content modal-md">
				<div class="modal-header">
					<h4 class="modal-title">Reset Device ID</h4>
				</div>
				<form>
					<div class="modal-body card-box">
						<p>Are you sure you want to reset Device ID for {{current_special_id}}?</p>
						<div class="m-t-20"> <a href="#" class="btn btn-default" data-dismiss="modal" data-toggle="modal" data-target="#edit_employee">Close</a>
							<button type="submit" class="btn btn-danger" ng-click="reset_device()" data-dismiss="modal" data-toggle="modal" data-target="#edit_employee">Reset</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div id="access_all_outlet" class="modal custom-modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog">
			<div class="modal-content modal-md">
				<div class="modal-header">
					<h4 class="modal-title">Access All Outlet</h4>
				</div>
				<form>
					<div class="modal-body card-box">
						<p>Are you sure you want to set sync action to Access All Outlet for {{current_special_id}}?</p>
						<div class="m-t-20"> <a href="#" class="btn btn-default" data-dismiss="modal" data-toggle="modal" data-target="#edit_employee">Close</a>
							<button type="submit" class="btn btn-primary" ng-click="access_all_outlet()" data-dismiss="modal" data-toggle="modal" data-target="#edit_employee">Access All Outlet</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<!-- Add this at the end of your HTML -->
<div id="imageModal" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Employee Photo</h4>
			</div>
			<div class="modal-body text-center">
				<img id="modalImage" src="" style="max-width: 100%; max-height: 400px;">
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function() {
		// Image click handler
		$(document).on('click', '.avatar img', function() {
			var imgSrc = $(this).attr('src');
			$('#modalImage').attr('src', imgSrc);
			$('#imageModal').modal('show');
		});

		// Initialize tooltips
		$('[data-toggle="tooltip"]').tooltip();
	});
</script>

<script type="text/javascript">
	var config = {
		headers: {
			'Content-Type': 'application/json;charset=utf-8;'
		}
	};
	var app = angular.module('myApp', []);
	app.controller('empCtrl', function($scope, $http, $compile) {
		$scope.mainTable = true;
		$scope.filtered = '';
		$scope.editModel = {};

		/* Modern Race / Religion / Nationality UI state - frontend only */
		$scope.identitySelect = {
			add: {
				race: '',
				religion: '',
				nationality: ''
			},
			edit: {
				race: '',
				religion: '',
				nationality: ''
			}
		};
		$scope.identityCustom = {
			add: {
				race: false,
				religion: false,
				nationality: false
			},
			edit: {
				race: false,
				religion: false,
				nationality: false
			}
		};
		$scope.identityCustomValue = {
			add: {
				race: '',
				religion: '',
				nationality: ''
			},
			edit: {
				race: '',
				religion: '',
				nationality: ''
			}
		};
		$scope.identityDefaults = {
			race: ['Malay', 'Chinese', 'Indian'],
			religion: ['muslim', 'buddhist', 'christian', 'hindu'],
			nationality: ['Malaysian']
		};

		$scope.onIdentitySelectChange = function(mode, field) {
			var selected = $scope.identitySelect[mode][field];
			var model = mode === 'add' ? $scope.addModel : $scope.editModel;
			if (selected === '__other__') {
				$scope.identityCustom[mode][field] = true;
				model[field] = $scope.identityCustomValue[mode][field] || '';
			} else {
				$scope.identityCustom[mode][field] = false;
				$scope.identityCustomValue[mode][field] = '';
				model[field] = selected;
			}
		};

		$scope.updateCustomIdentityValue = function(mode, field) {
			var model = mode === 'add' ? $scope.addModel : $scope.editModel;
			model[field] = $scope.identityCustomValue[mode][field];
		};

		$scope.prepareIdentityEditFields = function() {
			['race', 'religion', 'nationality'].forEach(function(field) {
				var actual = $scope.editModel[field] || '';
				if (!actual) {
					$scope.identitySelect.edit[field] = '';
					$scope.identityCustom.edit[field] = false;
					$scope.identityCustomValue.edit[field] = '';
				} else if ($scope.identityDefaults[field].indexOf(actual) !== -1) {
					$scope.identitySelect.edit[field] = actual;
					$scope.identityCustom.edit[field] = false;
					$scope.identityCustomValue.edit[field] = '';
				} else {
					$scope.identitySelect.edit[field] = '__other__';
					$scope.identityCustom.edit[field] = true;
					$scope.identityCustomValue.edit[field] = actual;
				}
			});
		};
		$scope.current_special_id = '';
		$scope.user_device_id = false;
		$scope.sync_action = 'SetUserDataAll';
		$scope.distinct_races = [];
		$scope.distinct_nationalities = [];
		$scope.addModel = {
			first_name: '',
			sex: 'Male',
			dob: '',
			pob: '',
			race: '',
			religion: '',
			nationality: '',
			email: '',
			ic_no: '',
			old_ic_no: '',
			password: '',
			device_role: '',
			branch_id: '',
			payroll_branch_id: '',
			department_id: '',
			role_id: '',
			section_id: '',
			groups: '',
			position_id: '',
			special_id: '',
			grade: '',
			employment_type: 'full_time',
			hired_on: '',
			ic_passport: '',
			perm_address: '',
			perm_address_city: '',
			perm_address_state: '',
			perm_address_postcode: '',
			temp_address: '',
			temp_address_city: '',
			temp_address_state: '',
			temp_address_postcode: '',
			telephone: '',
			mobile: '',
			marital_status: 'single',
			basic_wage: '',
			epf_no: '',
			socso: '',
			eis: '',
			income_tax_no: '',
			income_tax_branch: '',
			qr_barcode: '',
			bank_account_no: '',
			license_class: '',
			license_no: '',
			license_expiry: '',
			is_ot: "yes",
			is_early_ot: "no",
			is_daily_waged: false,
			is_shift_hours: false,
			employee_type: 'm',
			permanent_resident: 'yes',
			etc_on: '',
			etc_under: 'na',
			compassionate_leaves: 0,
			paternity_leaves: 0,
			marriage_leaves: 0,
			hospitalisation_leaves: 0,
			study_leaves: 0,
			replacement_leaves: 0,
			unpaid_leaves: 0,
			emergency_leaves: 0,
			employee_bank_id: '',
			level: 'junior_staff',
			ta_rate: <?php echo $company_id == 152 ? 0 : 1; ?>,
			ma_rate: 1,
			ca_rate: 1,
			spa_rate: 1,
			aca_rate: 1,
			fl_rate: 1,
			cw_rate: 1,
			mo_rate: 1,
			aa_rate: <?php echo $company_id == 152 ? 0 : 1; ?>,
			nsa_rate: 1,
			dsa_rate: 1,
			shift1_rate: 1,
			shift2_rate: 1,
			shift3_rate: 1,
			ot_group: '',
			special_incentive: 0,
			att_all_code: '',
			att_all_desc: '',
			att_all_amount: 100,
			is_att_all: 'no',
			device_password: '',
			mi_mo_rate: 0,
			lateness_deduction_99: 0,
			lateness_deduction_100: 0,
			rest_day_entitlement: 0,
			food_rate: 5,
			meal_rate: 0,
		}
		$scope.getPositions = function() {
			$("#add_designation").LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post('<?php echo base_url(); ?>' + 'employees/getPositions', {
				department_id: $scope.addModel.department_id
			}, config).then(function(response) {
				$scope.positions = response.data.positions;
				$scope.addModel.position_id = ''
				$("#add_designation").LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
			});
		}
		$scope.getDeductions = function() {
			$("body").LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post('<?php echo base_url(); ?>' + 'employees/getDeductions', {
				employee_type: $scope.addModel.employee_type,
				basic_wage: $scope.addModel.basic_wage
			}, config).then(function(response) {
				$scope.addModel.epf_no = response.data.epf;
				$scope.addModel.eis = response.data.eis;
				$scope.addModel.socso = response.data.socso;
				$("body").LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
			});
		}
		$scope.getDeductionsEdit = function() {
			$("body").LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post('<?php echo base_url(); ?>' + 'employees/getDeductions', {
				employee_type: $scope.editModel.employee_type,
				basic_wage: $scope.editModel.basic_wage
			}, config).then(function(response) {
				$scope.editModel.epf_no = response.data.epf;
				$scope.editModel.eis = response.data.eis;
				$scope.editModel.socso = response.data.socso;
				$("body").LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
			});
		}
		$scope.getEditPositions = function() {
			$("#edit_designation").LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post('<?php echo base_url(); ?>' + 'employees/getPositions', {
				department_id: $scope.editModel.department_id
			}, config).then(function(response) {
				$scope.editPositions = response.data.positions;
				$scope.editModel.position_id = ''
				$("#edit_designation").LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
			});
		}
		$scope.getEmployees = function() {


			setTimeout(function() {
				location.reload();

			}, 1000);


			// $('body').LoadingOverlay("show",{maxSize:50});
			// $http.post('<?php echo base_url(); ?>' + 'employees/getEmployees', {department_id : $scope.addModel.department_id}, config).then(function (response) {
			// 	var generated = $('#filteredArea').html(response.data);
			// 	$compile(generated.contents())($scope);
			// 	$('body').LoadingOverlay("hide");
			// }, function (error) {
			// 	console.log(error.data);
			// });

		}
		$scope.setDeleteID = function(id) {
			$scope.delete_id = id;
		}
		$scope.onSubmit2 = function(valid) {
			// Clear previous errors
			$('#firstNameError_edit').hide().text('');

			if (!valid) {
				var req = false;
				var error = $scope.emp_edit_form.$error;
				angular.forEach(error.required, function(field) {
					if (field.$invalid) {
						req = true;
					}
				});
				if (req) {
					showNotification("Error", "Please fill all the required fields!", "error");
				} else {
					showNotification("Error", "Email format is not correct!", "error");
				}
			} else {
				$('body').LoadingOverlay("show", {
					maxSize: 50
				});
				$scope.editModel.dob = $('#dob_edit').val();
				$scope.editModel.hired_on = $('#doj_edit').val();
				$scope.editModel.transfer_date = $("#transfer_date").val();
				$scope.editModel.license_expiry = $('#expiry_edit').val();
				$scope.editModel.termination_date = $('#termination_date').val();
				$scope.editModel.termination_notice_date = $('#termination_notice_date').val();
				$scope.editModel.resignation_date = $('#resignation_date').val();
				$scope.editModel.resignation_notice_date = $('#resignation_notice_date').val();
				$scope.editModel.groups = $('#groups').val();
				$scope.editModel.etc_on = $('#etc_on_edit').val();

				<?php if ($company_id == 146) { ?>
					$scope.editModel.min_worked_hours_meal = $('#min_worked_hours_meal_edit').val();
				<?php } ?>

				$http.post('<?php echo base_url(); ?>' + 'employees/update', $scope.editModel, config).then(function(response) {
					if (response.data.success) {

						$scope.getEmployees();
						$scope.mainTable = false;
						$scope.editModel = {};
						$('#edit_employee').modal('toggle');

						showNotification("Success", 'Employee updated successfully!', "success");
						$('body').LoadingOverlay("hide");
					} else if (response.data.success === false) {
						// Check if error is for special characters in first_name
						if (response.data.msg && response.data.msg.includes('First name may only contain')) {
							$('#firstNameError_edit').text(response.data.msg).show();
						} else if (response.data.duplicate) {
							showNotification("Error", response.data.msg, "error");
						} else {
							showNotification("Error", response.data.msg, "error");
						}
						$('body').LoadingOverlay("hide");
					}

				}, function(error) {
					console.log(error.data);
				});
			}
		}

		$scope.setAddData = function() {
			<?php if ($company_id == 146) { ?>
				let hours = 13;
				let minutes = 0;
				$('.datetimepicker3').data("DateTimePicker").date(new Date(1979, 0, 1, hours, minutes, 0, 0));
			<?php } ?>
		}

		$scope.setEditData = function(id) {
			$('body').LoadingOverlay("show", {
				maxSize: 50
			});
			// selectMap to trigger change event when opening edit form
			var selectMap = {
				"select-gender": "sex",
				"select-outlet": "branch_id",
				"select-payroll-outlet": "payroll_branch_id",
				"select-department": "department_id",
				"select-position": "position_id",
				"select-role": "role_id",
				"select-section": "section_id",
				"select-level": "level",
				"select-marital": "marital_status",
				"select-device-role": "device_role",
				"select-status": "employee_status",
				"select-reason": "termination_reason",
				"select-employment": "employment_type",
				"select-type": "employee_type",
				"select-resident": "permanent_resident",
				"select-etc": "etc_under",
				"select-overtime": "is_ot",
				"select-early": "is_early_ot",
				"select-bank": "employee_bank_id",
				"select-ot": "ot_group",
				"select-att-all": "is_att_all"
			};
			$http.post('<?php echo base_url(); ?>' + 'employees/getSingleEmployee', {
				id: id
			}, config).then(function(response) {
				// console.log(response.data.employee.groups);
				$scope.editModel = response.data.employee;
				$scope.editModel.current_branch_id = response.data.employee.branch_id;
				if ((response.data.employee.groups) == null) {
					// Do nothing ...
				} else {
					var myStr = response.data.employee.groups;
					var arr = myStr.split(', '); // split string on comma space
					// To encode an object (This produces a string)
					var json_str = JSON.stringify(arr);
					// To decode (This produces an object)
					var obj = JSON.parse(json_str);
					$('.multiple-selector').val(obj).change();
				}
				$scope.editModel.current_emp_status = response.data.employee.employee_status;
				$scope.editModel.transfer_reason = '';
				$scope.editModel.transfer_date = '';
				$scope.editModel.compassionate_leaves = parseInt($scope.editModel.compassionate_leaves);
				$scope.editModel.paternity_leaves = parseInt($scope.editModel.paternity_leaves);
				$scope.editModel.marriage_leaves = parseInt($scope.editModel.marriage_leaves);
				$scope.editModel.hospitalisation_leaves = parseInt($scope.editModel.hospitalisation_leaves);
				$scope.editModel.study_leaves = parseInt($scope.editModel.study_leaves);
				$scope.editModel.replacement_leaves = parseInt($scope.editModel.replacement_leaves);
				$scope.editModel.unpaid_leaves = parseInt($scope.editModel.unpaid_leaves);
				$scope.editModel.emergency_leaves = parseInt($scope.editModel.emergency_leaves);
				$scope.editModel.ta_rate = parseFloat($scope.editModel.ta_rate);
				$scope.editModel.ma_rate = parseFloat($scope.editModel.ma_rate);
				$scope.editModel.ca_rate = parseFloat($scope.editModel.ca_rate);
				$scope.editModel.spa_rate = parseFloat($scope.editModel.spa_rate);
				$scope.editModel.aca_rate = parseFloat($scope.editModel.aca_rate);
				$scope.editModel.fl_rate = parseFloat($scope.editModel.fl_rate);
				$scope.editModel.cw_rate = parseFloat($scope.editModel.cw_rate);
				$scope.editModel.mo_rate = parseFloat($scope.editModel.mo_rate);
				$scope.editModel.aa_rate = parseFloat($scope.editModel.aa_rate);
				$scope.editModel.nsa_rate = parseFloat($scope.editModel.nsa_rate);
				$scope.editModel.dsa_rate = parseFloat($scope.editModel.dsa_rate);
				$scope.editModel.shift1_rate = parseFloat($scope.editModel.shift1_rate);
				$scope.editModel.shift2_rate = parseFloat($scope.editModel.shift2_rate);
				$scope.editModel.shift3_rate = parseFloat($scope.editModel.shift3_rate);
				$scope.editModel.mi_mo_rate = parseFloat($scope.editModel.mi_mo_rate);
				$scope.editModel.lateness_deduction_99 = parseFloat($scope.editModel.lateness_deduction_99);
				$scope.editModel.lateness_deduction_100 = parseFloat($scope.editModel.lateness_deduction_100);
				$scope.editModel.rest_day_entitlement = parseFloat($scope.editModel.rest_day_entitlement);
				$scope.editModel.food_rate = parseFloat($scope.editModel.food_rate);
				$scope.editModel.meal_rate = parseFloat($scope.editModel.food_rate);
				$scope.editModel.special_incentive = parseFloat($scope.editModel.special_incentive);
				$scope.editModel.att_all_amount = parseFloat($scope.editModel.att_all_amount);
				$scope.current_special_id = $scope.editModel.special_id;
				$scope.user_device_id = response.data.user_device_id;
				$scope.sync_action = response.data.employee.sync_action;
				$scope.branches = response.data.branches;
				$scope.departments = response.data.departments;
				$scope.roles = response.data.roles;
				$scope.sections = response.data.sections;
				$scope.editPositions = response.data.positions;
				$scope.reasons = response.data.reasons;
				$scope.employee_banks = response.data.employee_banks;
				$scope.device_roles = response.data.device_roles;
				$scope.races = response.data.races;
				$scope.nationalities = response.data.nationalities;
				$scope.ot_groups = response.data.ot_groups;
				$scope.prepareIdentityEditFields();

				<?php if ($company_id == 146) { ?>
					let min_worked_hours_meal = $scope.editModel.min_worked_hours_meal;
					var min_worked_hours_meal_arr = min_worked_hours_meal.split(':');
					let hours = min_worked_hours_meal_arr[0];
					let minutes = min_worked_hours_meal_arr[1];
					$('.datetimepicker4').data("DateTimePicker").date(new Date(1979, 0, 1, hours, minutes, 0, 0));
					$scope.editModel.min_worked_hours_meal = hours + ':' + minutes;
				<?php } ?>

				setTimeout(function() {
					// Loop through and set values dynamically
					$.each(selectMap, function(cls, field) {
						$("." + cls).val($scope.editModel[field]).trigger("change");
					});
				}, 100);

				$('body').LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
			});
		}
		$scope.delete_employee = function() {
			$('body').LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post('<?php echo base_url(); ?>' + 'employees/delete_employee', {
				id: $scope.delete_id
			}, config).then(function(response) {
				$scope.mainTable = false;
				$scope.getEmployees();
				$('#delete_employee').modal('toggle');
				showNotification("Success", 'Employee deleted successfully!', "success");
				$('body').LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
			});
		}
		$scope.reset_device = function() {
			$('body').LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post('<?php echo base_url(); ?>' + 'employees/reset_device', {
				id: $scope.editModel.id
			}, config).then(function(response) {
				$scope.user_device_id = false;
				showNotification("Success", 'Device ID successfully reset for ' + $scope.current_special_id, "success");
				$('body').LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
			});
		}
		$scope.access_all_outlet = function() {
			$('body').LoadingOverlay("show", {
				maxSize: 50
			});
			$http.post('<?php echo base_url(); ?>' + 'employees/access_all_outlet', {
				id: $scope.editModel.id
			}, config).then(function(response) {
				$scope.sync_action = 'SetUserDataAll';
				showNotification("Success", 'All outlets are now accessible by ' + $scope.current_special_id, "success");
				$('body').LoadingOverlay("hide");
			}, function(error) {
				console.log(error.data);
			});
		}

		$scope.get_datalist_options = function() {
			$http.post("<?php echo base_url(); ?>" + "employees/get_datalist_options", {}, config)
				.then(function(response) {
					$scope.distinct_races = response.data.distinct_races;
					$scope.distinct_nationalities = response.data.distinct_nationalities;
				}).catch(function(error) {
					console.log(error);
				})
		}

		$scope.onSubmit = function(valid) {
			// Clear previous errors
			$('#firstNameError_add').hide().text('');

			if (!valid) {
				var req = false;
				var error = $scope.emp_form.$error;
				angular.forEach(error.required, function(field) {
					if (field.$invalid) {
						req = true;
					}
				});
				if (req) {
					alert("Please fill all the required fields!");
				} else {
					alert("Email format is not correct!");
				}
			}
			if (valid) {
				$('body').LoadingOverlay("show", {
					maxSize: 50
				});
				<?php if ($company_id == 146) { ?>
					$scope.addModel.min_worked_hours_meal = $('#min_worked_hours_meal').val();
				<?php } ?>
				$scope.addModel.dob = $('#dob').val();
				$scope.addModel.hired_on = $('#doj').val();
				$scope.addModel.license_expiry = $('#expiry').val();
				$scope.addModel.etc_on = $('#etc_on').val();
				$http.post('<?php echo base_url(); ?>' + 'employees/save', $scope.addModel, config).then(function(response) {
					if (response.data.success) {
						$scope.getEmployees();
						$scope.mainTable = false;
						$scope.addModel = {
							first_name: '',
							sex: 'Male',
							dob: '',
							pob: '',
							race: '',
							religion: '',
							nationality: '',
							email: '',
							ic_no: '',
							old_ic_no: '',
							password: '',
							device_role: '',
							branch_id: '',
							payroll_branch_id: '',
							department_id: '',
							role_id: '',
							section_id: '',
							position_id: '',
							special_id: '',
							grade: '',
							employment_type: 'full_time',
							hired_on: '',
							ic_passport: '',
							perm_address: '',
							perm_address_city: '',
							perm_address_state: '',
							perm_address_postcode: '',
							temp_address: '',
							temp_address_city: '',
							temp_address_state: '',
							temp_address_postcode: '',
							telephone: '',
							mobile: '',
							marital_status: 'single',
							basic_wage: '',
							epf_no: '',
							socso: '',
							eis: '',
							income_tax_no: '',
							income_tax_branch: '',
							qr_barcode: '',
							bank_account_no: '',
							license_class: '',
							license_no: '',
							license_expiry: '',
							is_ot: "yes",
							is_early_ot: "no",
							is_daily_waged: false,
							is_shift_hours: false,
							employee_type: 'm',
							permanent_resident: 'yes',
							etc_on: '',
							etc_under: 'na',
							compassionate_leaves: 0,
							paternity_leaves: 0,
							marriage_leaves: 0,
							hospitalisation_leaves: 0,
							study_leaves: 0,
							replacement_leaves: 0,
							unpaid_leaves: 0,
							emergency_leaves: 0,
							employee_bank_id: '',
							ta_rate: <?php echo $company_id == 152 ? 0 : 1; ?>,
							ma_rate: 1,
							ca_rate: 1,
							spa_rate: 1,
							aca_rate: 1,
							fl_rate: 1,
							cw_rate: 1,
							mo_rate: 1,
							aa_rate: <?php echo $company_id == 152 ? 0 : 1; ?>,
							nsa_rate: 1,
							dsa_rate: 1,
							shift1_rate: 1,
							shift2_rate: 1,
							shift3_rate: 1,
							ot_group: '',
							special_incentive: 0,
							att_all_code: '',
							att_all_desc: '',
							att_all_amount: 100,
							is_att_all: 'no',
							device_password: '',
							mi_mo_rate: 0,
							lateness_deduction_99: 0,
							lateness_deduction_100: 0,
							rest_day_entitlement: 0,
							food_rate: 5,
							meal_rate: 0,
						}
						$scope.positions = [];
						$scope.identitySelect.add = {
							race: '',
							religion: '',
							nationality: ''
						};
						$scope.identityCustom.add = {
							race: false,
							religion: false,
							nationality: false
						};
						$scope.identityCustomValue.add = {
							race: '',
							religion: '',
							nationality: ''
						};
						$('#add_employee').modal('toggle');
						showNotification("Success", response.data.msg, "success");
						$('body').LoadingOverlay("hide");
					} else if (response.data.success === false) {
						// Check if error is for special characters in first_name
						if (response.data.msg && response.data.msg.includes('First name may only contain')) {
							$('#firstNameError_add').text(response.data.msg).show();
						} else {
							showNotification("Error", response.data.msg, "error");
						}
						$('body').LoadingOverlay("hide");
					}

				}, function(error) {
					console.log(error.data);
				});
			}
		}
	});
</script>

<style>
	/* ================================================================
   INV EMPLOYEE WIZARD V2 - HARD OVERRIDES / NEW STRUCTURE
   All rules are isolated under .inv-emp-wizard-shell
================================================================ */
	.inv-emp-wizard-shell .modal.custom-modal .modal-dialog,
	.inv-emp-wizard-shell #add_employee .modal-dialog,
	.inv-emp-wizard-shell #edit_employee .modal-dialog {
		width: calc(100vw - 90px) !important;
		max-width: 1360px !important;
		min-width: 980px !important;
		margin: 18px auto !important;
	}

	.inv-emp-wizard-shell .modal.custom-modal .modal-content {
		border-radius: 20px !important;
	}

	.inv-emp-wizard-shell .modal.custom-modal .modal-body {
		padding: 0 !important;
		max-height: calc(100vh - 135px) !important;
		overflow-y: auto !important;
		background: #f3f6fa !important;
	}

	.inv-emp-wizard-shell .emp-modern-form-header {
		padding: 26px 84px 24px 30px !important;
		background: linear-gradient(to right, #00c5fb 0%, #0253cc 100%);
	}

	.inv-emp-wizard-shell .inv2-progress-wrap {
		padding: 18px 30px 12px;
		background: #fff;
		border-bottom: 1px solid #e5ebf2;
	}

	.inv-emp-wizard-shell .inv2-progress-top {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 12px;
		font-size: 12px;
		font-weight: 700;
		color: #64748b;
	}

	.inv-emp-wizard-shell .inv2-progress-bar {
		height: 6px;
		border-radius: 99px;
		background: #e7edf4;
		overflow: hidden;
	}

	.inv-emp-wizard-shell .inv2-progress-fill {
		width: 16.66%;
		height: 100%;
		border-radius: 99px;
		background: linear-gradient(90deg, #087ea4, #12b7d4);
		transition: width .28s ease;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-nav {
		padding: 18px 24px !important;
		gap: 8px !important;
		background: #fff !important;
		overflow-x: auto;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-step {
		flex: 1 0 165px !important;
		min-width: 165px !important;
		padding: 10px 8px !important;
		border-radius: 14px !important;
		text-align: left !important;
		display: flex !important;
		align-items: center;
		gap: 10px;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-step:after {
		display: none !important;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-step:hover {
		background: #f1f8fb !important;
		color: #087ea4 !important;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-step .inv-emp-wizard-num {
		margin: 0 !important;
		flex: 0 0 38px;
		width: 38px !important;
		height: 38px !important;
		background: #eef2f7 !important;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-step .inv-emp-wizard-label {
		font-size: 12px !important;
		white-space: normal !important;
		line-height: 1.25;
	}

	.inv-emp-wizard-shell .inv2-step-icon {
		width: 20px;
		text-align: center;
		font-size: 14px;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-step.is-active {
		background: #eaf8fc !important;
		color: #087ea4 !important;
		box-shadow: inset 0 0 0 1px #bce7f1;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-step.is-active .inv-emp-wizard-num {
		background: #087ea4 !important;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-step.is-done {
		color: #17845b !important;
		background: #f0fbf6 !important;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-step.is-done .inv-emp-wizard-num {
		background: #1aa06d !important;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-content {
		padding: 26px 30px 12px !important;
		min-height: 470px;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-pane {
		display: none;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-pane.is-active {
		display: block !important;
	}

	.inv-emp-wizard-shell .inv2-step-heading {
		display: flex;
		align-items: center;
		gap: 14px;
		margin: 0 0 20px;
	}

	.inv-emp-wizard-shell .inv2-step-heading-icon {
		width: 46px;
		height: 46px;
		border-radius: 13px;
		display: flex;
		align-items: center;
		justify-content: center;
		background: linear-gradient(135deg, #087ea4, #16b9d3);
		color: #fff;
		font-size: 18px;
		box-shadow: 0 9px 20px rgba(8, 126, 164, .18);
	}

	.inv-emp-wizard-shell .inv2-step-heading h2 {
		margin: 0 !important;
		padding: 0 !important;
		border: 0 !important;
		font-size: 22px !important;
	}

	.inv-emp-wizard-shell .inv2-step-heading p {
		margin: 3px 0 0;
		color: #718096;
		font-size: 12px;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-pane>h2,
	.inv-emp-wizard-shell .inv-emp-wizard-pane>br {
		display: none !important;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-pane .row {
		background: #fff !important;
		border: 1px solid #e3eaf2 !important;
		border-radius: 14px !important;
		padding: 18px 12px 2px !important;
		margin: 0 0 16px !important;
		box-shadow: 0 5px 18px rgba(20, 42, 70, .035) !important;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-actions {
		position: sticky;
		bottom: 0;
		z-index: 5;
		padding: 16px 30px 20px !important;
		box-shadow: 0 -8px 25px rgba(15, 23, 42, .06);
	}

	.inv-emp-wizard-shell .inv2-save-later {
		margin-right: 8px;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-actions .btn {
		min-width: 130px;
	}

	.inv-emp-wizard-shell .inv-emp-wizard-actions .inv-emp-next {
		min-width: 150px !important;
	}

	.inv-emp-wizard-shell .inv2-step-counter {
		font-size: 12px;
		color: #64748b;
		font-weight: 700;
	}

	@media (max-width: 1100px) {

		.inv-emp-wizard-shell .modal.custom-modal .modal-dialog,
		.inv-emp-wizard-shell #add_employee .modal-dialog,
		.inv-emp-wizard-shell #edit_employee .modal-dialog {
			width: calc(100vw - 24px) !important;
			min-width: 0 !important;
		}
	}
</style>
<script>
	/* Employee Wizard V2. Pure frontend: existing Angular models/endpoints are untouched. */
	(function($) {
		var CONFIG = {
			profile: {
				title: 'Profile',
				icon: 'fa-user',
				desc: 'Personal identity and employee basics'
			},
			employment: {
				title: 'Employment',
				icon: 'fa-briefcase',
				desc: 'Outlet, role and organisation details'
			},
			contact: {
				title: 'Contact',
				icon: 'fa-phone',
				desc: 'Address and contact information'
			},
			additional: {
				title: 'Details',
				icon: 'fa-id-card',
				desc: 'Documents, bank and additional settings'
			},
			leave: {
				title: 'Leave',
				icon: 'fa-calendar',
				desc: 'Leave entitlement and balances'
			},
			payroll: {
				title: 'Payroll & Allowances',
				icon: 'fa-money',
				desc: 'Rates, allowances and payroll configuration'
			}
		};
		var headings = {
			'Basic Information': 'profile',
			'General Information': 'profile',
			'Departmental Information': 'employment',
			'Contact Information': 'contact',
			'Other Information': 'additional',
			'Leaves': 'leave',
			'Allowance Rates': 'payroll',
			'Meal Allowance Entitlement': 'payroll',
			'Miscellaneous': 'payroll',
			'Attendance Allowance': 'payroll'
		};

		function ensureLarge($modal) {
			var d = $modal.find('.modal-dialog').get(0);
			if (!d) return;
			d.style.setProperty('width', 'calc(100vw - 90px)', 'important');
			d.style.setProperty('max-width', '1360px', 'important');
			d.style.setProperty('min-width', '980px', 'important');
			if (window.innerWidth < 1100) {
				d.style.setProperty('width', 'calc(100vw - 24px)', 'important');
				d.style.setProperty('min-width', '0', 'important');
			}
		}

		function build(modalId) {
			var $m = $(modalId),
				$f = $m.find('form').first();
			ensureLarge($m);
			if (!$f.length || $f.data('inv2ready')) return;
			var panes = [],
				current = null,
				seen = {};
			$f.contents().toArray().forEach(function(node) {
				var $n = $(node),
					key = null;
				if (node.nodeType === 1 && node.tagName && node.tagName.toLowerCase() === 'h2') key = headings[$.trim($n.text())] || null;
				if (key) {
					if (!seen[key]) {
						current = $('<section class="inv-emp-wizard-pane" data-step="' + key + '"></section>');
						panes.push({
							key: key,
							$pane: current
						});
						seen[key] = true;
					}
					// all repeated payroll subsections remain in the same pane
				}
				if (!current) {
					current = $('<section class="inv-emp-wizard-pane" data-step="profile"></section>');
					panes.push({
						key: 'profile',
						$pane: current
					});
					seen.profile = true;
				}
				current.append(node);
			});
			if (!panes.length) return;
			$f.empty();
			var $prog = $('<div class="inv2-progress-wrap"><div class="inv2-progress-top"><span class="inv2-progress-label">Step 1 of ' + panes.length + '</span><span class="inv2-step-counter">Getting started</span></div><div class="inv2-progress-bar"><div class="inv2-progress-fill"></div></div></div>');
			var $nav = $('<div class="inv-emp-wizard-nav"></div>'),
				$content = $('<div class="inv-emp-wizard-content"></div>');
			panes.forEach(function(p, i) {
				var c = CONFIG[p.key] || {
					title: p.key,
					icon: 'fa-circle',
					desc: ''
				};
				$nav.append('<button type="button" class="inv-emp-wizard-step" data-index="' + i + '"><span class="inv-emp-wizard-num">' + (i + 1) + '</span><span class="inv2-step-icon"><i class="fa ' + c.icon + '"></i></span><span class="inv-emp-wizard-label">' + c.title + '</span></button>');
				p.$pane.prepend('<div class="inv2-step-heading"><div class="inv2-step-heading-icon"><i class="fa ' + c.icon + '"></i></div><div><h2>' + c.title + '</h2><p>' + c.desc + '</p></div></div>');
				$content.append(p.$pane);
			});
			$f.append($prog, $nav, $content);
			var $submit = $f.find('.emp-modern-submit').last();
			var $holder = $submit.closest('.m-t-20.text-center');
			var $actions = $('<div class="inv-emp-wizard-actions"><button type="button" class="btn btn-default inv-emp-prev"><i class="fa fa-angle-left"></i> Previous</button><div><button type="button" class="btn btn-default inv2-save-later"><i class="fa fa-list"></i> All Steps</button><button type="button" class="btn inv-emp-next">Continue <i class="fa fa-arrow-right"></i></button></div></div>');
			if ($submit.length) {
				$submit.detach();
				$actions.children('div').append($submit);
			}
			if ($holder.length) $holder.remove();
			$f.append($actions);
			var idx = 0;

			function validPane(i) {
				var $p = panes[i].$pane,
					bad = [];
				$p.find('input[required],select[required],textarea[required]').each(function() {
					if (!$(this).val()) bad.push(this);
				});
				if (bad.length) {
					$(bad[0]).focus();
					return false;
				}
				return true;
			}

			function show(i) {
				i = Math.max(0, Math.min(i, panes.length - 1));
				idx = i;
				$content.find('.inv-emp-wizard-pane').removeClass('is-active').eq(i).addClass('is-active');
				$nav.find('.inv-emp-wizard-step').each(function(n) {
					$(this).toggleClass('is-active', n === i).toggleClass('is-done', n < i);
				});
				$actions.find('.inv-emp-prev').toggle(i > 0);
				$actions.find('.inv-emp-next').toggle(i < panes.length - 1);
				$submit.toggle(i === panes.length - 1);
				$prog.find('.inv2-progress-label').text('Step ' + (i + 1) + ' of ' + panes.length);
				$prog.find('.inv2-step-counter').text((CONFIG[panes[i].key] || {}).title || '');
				$prog.find('.inv2-progress-fill').css('width', (((i + 1) / panes.length) * 100) + '%');
				var b = $m.find('.modal-body').get(0);
				if (b) b.scrollTop = 0;
				$m.trigger('inv2:stepchange', [i, panes.length]);
			}
			$nav.on('click', '.inv-emp-wizard-step', function() {
				show(+$(this).data('index'));
			});
			$actions.on('click', '.inv-emp-prev', function() {
				show(idx - 1);
			});
			$actions.on('click', '.inv-emp-next', function() {
				if (validPane(idx)) show(idx + 1);
				else alert('Please complete the required fields in this step before continuing.');
			});
			$actions.on('click', '.inv2-save-later', function() {
				$nav.get(0).scrollIntoView({
					behavior: 'smooth',
					block: 'nearest'
				});
			});
			$f.data('inv2ready', true);
			show(0);
		}

		function initAll() {
			build('#add_employee');
			build('#edit_employee');
		}
		$(function() {
			setTimeout(initAll, 100);
			setTimeout(initAll, 700);
		});
		$(document).on('shown.bs.modal', '#add_employee,#edit_employee', function() {
			ensureLarge($(this));
			build('#' + this.id);
		});
		$(window).on('resize', function() {
			$('#add_employee,#edit_employee').each(function() {
				ensureLarge($(this));
			});
		});
		// purely cosmetic, no state/logic
		$('.m-b-30 .btn.rounded').on('click', function() {
			var $b = $(this);
			$b.css('transform', 'scale(0.96)');
			setTimeout(function() {
				$b.css('transform', '');
			}, 120);
		});
	})(jQuery);
</script>