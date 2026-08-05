<style>
	.table-div{
		min-height: 300px;
		max-width: 150px;
	}
</style>
<div class="page-wrapper" ng-app="myApp" ng-controller="reportCtrl" ng-init="getData()">
	
	<div class="content container-fluid">
		<div class="row">
			<div class="col-xs-4">
				<h4 class="page-title">Payroll Report</h4>
			</div>
		</div>
		<div class="row card-box" ng-cloak>
			<form ng-submit="getPayroll(branch,department,year,month)">
				<div class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label>Outlet</label>
							<select  class="form-control" ng-model="branch">
								<option value="">All</option>
								<option ng-repeat="b in branches" value="{{b.id}}">{{b.name}}</option>

							</select>
						</div>

					</div>

					<div class="col-md-3">
						<div class="form-group">
							<label>Department</label>
							<select class="form-control" ng-model="department">
								<option value="">All</option>
								<option ng-repeat="d in departments" value="{{d.id}}">{{d.name}}</option>


							</select>
						</div>

					</div>

					

					<div class="col-md-2">
						<div class="form-group">
							<label>Month</label>
							<select class="form-control" ng-model="month">
								<option ng-repeat="m in months" value="{{m.id}}">{{m.name}}</option>
							</select>
						</div>                                               
					</div>

					<div class="col-md-2">
						<div class="form-group">
							<label>Year</label>
							<select class="form-control" ng-model="year">
								<option ng-repeat="y in years" value="{{y.id}}">{{y.name}}</option>
							</select>
						</div>                                               
					</div>

					<div class="col-md-2">
						<label>&nbsp;</label>
						<button class="btn btn-primary btn-block">Filter</button>

					</div>

				</div>

			</form>





		</div>

		<div class="row card-box" ng-if="report.length != 0" ng-cloak>
			<h2>{{report.month_name}} {{report.year}} Payroll</h2>
			<div class="table-responsive">
				<table class="table">
					<thead>
						<th>Employee</th>
						<th>Basic</th>
						<th>Additional</th>
						<th>EPF</th>
						<th>SOCSO</th>
						<th>Tax</th>
						<th>EIS</th>
						<th>Other deductions</th>
						<th>Net pay</th>
						<th>Action</th>
					</thead>
					<tbody>
						<tr ng-repeat="r in report.employees">
							<td><a style="color:#009ce7" href="<?php echo base_url(); ?>profile/index/{{r.employee_id}}" target="_blank"><b>{{r.name}}</b></a></td>
							<td>{{r.basic_amount | number:2}}</td>
							<td>{{r.total_allowance | number:2}}</td>
							<td>{{r.epf | number:2}}</td>
							<td>{{r.socso | number:2}}</td>
							<td>{{r.tax | number:2}}</td>
							<td>{{r.eis | number:2}}</td>
							<td>{{r.total_deductions - r.epf - r.socso - r.tax - r.eis | number:2}}</td>
							<td>{{r.net_pay | number:2}}</td>
							<td>
								<button class="btn btn-xs btn-success" ng-if="r.confirm == 'N'" ng-click="confirm(r.id)">Confirm</button>
								<a href="<?php echo base_url();?>payroll/calculator/{{r.employee_id}}/{{report.year}}/{{report.month}}" target="_blank" class="btn btn-xs btn-info" ng-if="r.confirm == 'N'"><i class="fa fa-edit"></i></a>
								<button class="btn btn-xs btn-danger" ng-if="r.confirm == 'Y'" ng-click="unconfirm(r.id)">Unconfirm</button>
								<a href="<?php echo base_url();?>payroll/slip/{{r.employee_id}}/{{report.year}}/{{report.month}}" target="_blank" class="btn btn-xs btn-info" ng-if="r.confirm == 'Y'"><i class="fa fa-print"></i></a>
								

							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		

	</div>
</div>
<script type="text/javascript">
	var base_url = '<?php echo base_url(); ?>';
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ang-ui.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/payroll.js?v=1.1"></script>