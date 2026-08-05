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
			<h2>{{report.month_name}} {{report.year}}</h2>
			<nav>
				<ul class="nav nav-tabs nav-fill" role="tablist">
					<li class="nav-item active"><a class="nav-link" data-toggle="tab" href="#summary" role="tab" aria-controls="summary" aria-selected="true">Payroll Summary</a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#net_report" role="tab" aria-controls="net_report" aria-selected="true">Net Payroll Report</a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#bank_report" role="tab" aria-controls="bank_report" aria-selected="true">Bank Report</a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#variance_report" role="tab" aria-controls="variance_report" aria-selected="true">Variance Report</a></li>
				</ul>
			</nav>
			
			<div class="tab-content">
				<div class="tab-pane active" id="summary">
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

				<div class="tab-pane" id="net_report">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Special ID</th>
								<th>Employee</th>
								<th>Net Pay</th>
								<th>Bank-in</th>
								<th>Cash</th>
								<th>Cheque</th>
							</tr>
						</thead>
						<tbody>
							<tr ng-repeat="nr in net_report">
								<td>{{nr.special_id}}</td>
								<td>{{nr.first_name}}</td>
								<td>{{nr.net_pay | number:2}}</td>
								<td>{{nr.net_pay | number:2}}</td>
								<td>0.00</td>
								<td>0.00</td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="2" class="text-center">Payroll Totals</td>
								<td>{{net_total | number:2}}</td>
								<td>{{net_total | number:2}}</td>
								<td>0.00</td>
								<td>0.00</td>
							</tr>
						</tfoot>
						
					</table>
				</div>

				<div class="tab-pane" id="bank_report">
					<table class="table table-striped">
						<thead>
							<tr>
								<th>Special ID</th>
								<th>Employee</th>
								<th>Bank of Payee</th>
								<th>Account No.</th>
								<th>Amount(RM)</th>
							</tr>
						</thead>
						<tbody>
							<tr ng-repeat="nr in net_report">
								<td>{{nr.special_id}}</td>
								<td>{{nr.first_name}}</td>
								<td>{{nr.bank_name}}</td>
								<td>{{nr.bank_account_no}}</td>
								<td>{{nr.net_pay | number:2}}</td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="5" class="text-center">Total: RM {{net_total | number:2}}</td>
							</tr>
						</tfoot>
						
					</table>
				</div>

				<div class="tab-pane" id="variance_report">
					<table class="table table-hover">
						<thead>
							<tr>
								<th>Type</th>
								<th>Current Month (RM)</th>
								<th>Last Month (RM)</th>
								<th>Difference (RM)</th>
							</tr>
						</thead>
						<tbody>
							<tr ng-repeat-start="vr in variance_report" class="info">
								<td colspan="4"><b>{{vr.first_name}} ({{vr.special_id}})</b></td>
							</tr>
							<tr>
								<td>Basic Salary</td>
								<td>{{vr.current_month.basic_amount | number : 2}}</td>
								<td>{{vr.last_month.basic_amount | number : 2}}</td>
								<td>{{vr.current_month.basic_amount - vr.last_month.basic_amount | number : 2}}</td>
							</tr>
							<tr>
								<td>Additional Pay</td>
								<td>{{vr.current_month.total_allowance | number : 2}}</td>
								<td>{{vr.last_month.total_allowance | number : 2}}</td>
								<td>{{vr.current_month.total_allowance - vr.last_month.total_allowance | number : 2}}</td>
							</tr>
							<tr>
								<td>Additional Deductions</td>
								<td>{{vr.current_month.total_deductions | number : 2}}</td>
								<td>{{vr.last_month.total_deductions | number : 2}}</td>
								<td>{{vr.current_month.total_deductions - vr.last_month.total_deductions | number : 2}}</td>
							</tr>
							<tr>
								<td>Gross Pay</td>
								<td>{{vr.current_month.gross | number : 2}}</td>
								<td>{{vr.last_month.gross | number : 2}}</td>
								<td>{{vr.current_month.gross - vr.last_month.gross | number : 2}}</td>
							</tr>
							<tr>
								<td>Employee EPF</td>
								<td>{{vr.current_month.epf | number : 2}}</td>
								<td>{{vr.last_month.epf | number : 2}}</td>
								<td>{{vr.current_month.epf - vr.last_month.epf | number : 2}}</td>
							</tr>
							<tr>
								<td>Employer EPF</td>
								<td>{{vr.current_month.epf_c | number : 2}}</td>
								<td>{{vr.last_month.epf_c | number : 2}}</td>
								<td>{{vr.current_month.epf_c - vr.last_month.epf_c | number : 2}}</td>
							</tr>
							<tr>
								<td>Employee SOCSO</td>
								<td>{{vr.current_month.socso | number : 2}}</td>
								<td>{{vr.last_month.socso | number : 2}}</td>
								<td>{{vr.current_month.socso - vr.last_month.socso | number : 2}}</td>
							</tr>
							<tr>
								<td>Employer SOCSO</td>
								<td>{{vr.current_month.socso_c | number : 2}}</td>
								<td>{{vr.last_month.socso_c | number : 2}}</td>
								<td>{{vr.current_month.socso_c - vr.last_month.socso_c | number : 2}}</td>
							</tr>
							<tr>
								<td>Tax</td>
								<td>{{vr.current_month.tax | number : 2}}</td>
								<td>{{vr.last_month.tax | number : 2}}</td>
								<td>{{vr.current_month.tax - vr.last_month.tax | number : 2}}</td>
							</tr>
							<tr>
								<td>Net Pay</td>
								<td>{{vr.current_month.net_pay | number : 2}}</td>
								<td>{{vr.last_month.net_pay | number : 2}}</td>
								<td>{{vr.current_month.net_pay - vr.last_month.net_pay | number : 2}}</td>
							</tr>
							<tr>
								<td>Employee EIS</td>
								<td>{{vr.current_month.eis | number : 2}}</td>
								<td>{{vr.last_month.eis | number : 2}}</td>
								<td>{{vr.current_month.eis - vr.last_month.eis | number : 2}}</td>
							</tr>
							<tr ng-repeat-end>
								<td>Employer EIS</td>
								<td>{{vr.current_month.eis_c | number : 2}}</td>
								<td>{{vr.last_month.eis_c | number : 2}}</td>
								<td>{{vr.current_month.eis_c - vr.last_month.eis_c | number : 2}}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			
		</div>
		

	</div>
</div>
<script type="text/javascript">
	var base_url = '<?php echo base_url(); ?>';
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ang-ui.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/payroll.js?v=1.5"></script>