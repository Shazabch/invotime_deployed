<?php include(APPPATH . "views/payroll/header.php"); ?>
<?php include(APPPATH . "views/payroll/sidebar.php"); ?>
<style>
	.table-div{
		min-height: 300px;
		max-width: 150px;
	}
</style>
<div class="page-wrapper" ng-app="myApp" ng-controller="reportCtrl" ng-init="getData('<?php echo $process_id;?>')">
	
	<div class="content container-fluid">
		<div class="row">
			<div class="col-xs-4">
				<h4 class="page-title">Payroll Report</h4>
			</div>
		</div>
		<div class="row card-box" ng-cloak>
			<form ng-submit="getPayroll(process, branch, department)">
				<div class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label>Outlet</label>
							<select class="form-control" ng-model="branch" ng-change="getPayrollProcessByBranch()">
								<option value="">All</option>
								<option ng-repeat="b in branches" value="{{b.id}}">{{b.name}}</option>

							</select>
						</div>

					</div>

					<div class="col-md-3">
						<div class="form-group">
							<label>Payroll Process</label>
							<select class="form-control process-select2" ng-model="process" required="">
								<option value="">Select Payroll Process</option>
								<option ng-repeat="p in payroll_processes" value="{{p.id}}">{{p.period}} - {{p.payroll_type}} | {{p.description}}</option>

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

					

					<div class="col-md-3">
						<label>&nbsp;</label>
						<button class="btn btn-primary btn-block">Filter</button>

					</div>

				</div>

			</form>





		</div>

		<div class="row card-box" ng-if="report.length != 0" ng-cloak>
			<h2>{{report.payroll_name}}</h2>
			<nav>
				<ul class="nav nav-tabs nav-fill" role="tablist">
					<li class="nav-item active"><a class="nav-link" data-toggle="tab" href="#summary" role="tab" aria-controls="summary" aria-selected="true">Payroll Summary</a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#no_time_pay_off" role="tab" aria-controls="no_time_pay_off" aria-selected="true">No Time Pay Off</a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#extra_earnings" role="tab" aria-controls="extra_earnings" aria-selected="true">Extra Earnings</a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#deductions_tab" role="tab" aria-controls="deductions_tab" aria-selected="true">Deductions</a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#adjustments_tab" role="tab" aria-controls="adjustments_tab" aria-selected="true">Adjustments</a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#net_report" role="tab" aria-controls="net_report" aria-selected="true">Net Payroll Report</a></li>
					<li class="nav-item"><a class="nav-link" data-toggle="tab" href="#bank_report" role="tab" aria-controls="bank_report" aria-selected="true">Bank Report</a></li>
					<!-- <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#variance_report" role="tab" aria-controls="variance_report" aria-selected="true">Variance Report</a></li> -->
				</ul>
			</nav>
			
			<div class="tab-content">
				<!-- Payroll Summary Tab -->
				<div class="tab-pane active" id="summary">
					<div class="table-responsive">
						<table class="table">
							<thead>
								<th style="width: 230px;">Employee</th>
								<th>Net Basic</th>
								<th>Extra Earnings</th>
								<th>Gross Salary</th>
								<th>Total Deductions</th>
								<th>Net Pay</th>
								<th>Total Adjustments</th>
								<th>Net Payable</th>
								<th>Action</th>
							</thead>
							<tbody>
								<tr ng-if="report.employees.length == 0">
									<td colspan="10" class="text-center">
										<p class="text-danger">No record found for this month</p>
									</td>
								</tr>
								
								<tr ng-repeat="r in report.employees">
									<td><a style="color:#009ce7" href="<?php echo base_url();?>payroll/calculator/{{r.employee_id}}/{{r.process_id}}" target="_blank"><b>{{r.name}}</b></a></td>
										<td ng-show="r.id != null">{{r.net_basic | number:2}}</td>
										<td ng-show="r.id != null">{{r.total_allowances | number:2}}</td>
										<td ng-show="r.id != null">{{r.gross_salary | number:2}}</td>
										<td ng-show="r.id != null">{{r.total_deductions | number:2}}</td>
										<td ng-show="r.id != null">{{r.net_pay | number:2}}</td>
										<td ng-show="r.id != null">{{r.total_adjustments | number:2}}</td>
										<td ng-show="r.id != null">{{r.salary_paid | number:2}}</td>
									<td ng-show="r.id == null" colspan="7" class="text-center text-danger">Payroll not calculated yet</td>
									<td class="text-center">
										<button class="btn btn-xs btn-success" ng-if="r.approved == 'Y'"
										disabled="">Approved</button>
										<span ng-if="r.approved == 'N'">
											<button class="btn btn-xs btn-success" ng-if="r.confirm == 'N'" ng-click="confirm(r.employee_id, r.process_id)">Commit</button>
											<button class="btn btn-xs btn-danger" ng-if="r.confirm == 'Y'" ng-click="unconfirm(r.employee_id, r.process_id)">Uncommit</button>
										</span>
										<a href="<?php echo base_url();?>payroll/calculator/{{r.employee_id}}/{{r.process_id}}" target="_blank" class="btn btn-xs btn-info" ng-if="r.confirm == 'N' || r.confirm == null"><i class="fa fa-edit"></i></a>
										
										<button class="btn btn-xs btn-info" ng-if="r.approved == 'Y'" ng-click="sendSlip(r.employee_id, r.process_id)" id="slip{{r.employee_id}}">Send Slip</button>
										<a href="<?php echo base_url();?>payroll/slip/{{r.employee_id}}/{{r.process_id}}" target="_blank" class="btn btn-xs btn-info" ng-if="r.approved == 'Y'"><i class="fa fa-print"></i></a>
										<a href="<?php echo base_url();?>payroll/new_slip/{{r.employee_id}}/{{r.process_id}}" target="_blank" class="btn btn-xs btn-primary" ng-if="r.approved == 'Y'"><i class="fa fa-print"></i></a>



									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="row" ng-if="report.employees.length > 0">
						<div class="col-md-12">
							<div class="pull-right">
								<button class="btn btn-default" ng-click="confirmAll(selected_process, selected_department)">Commit all payrolls</button>
								<button class="btn btn-default" ng-click="sendAll(selected_process, selected_department)">Send everyone pay slip</button>
							</div>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-md-12">
							<h3>Totals</h3>
							<hr>
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of Net Basic</p>
							<h2>RM {{v_report.net_basic | number : 2}}</h2>
							<!-- <p style="margin-top: -10px;"><small>Last month: RM {{v_report_last.epf + v_report_last.epf_c | number : 2}}</small></p> -->
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of Extra Earnings</p>
							<h2>RM {{v_report.total_allowances | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of Gross Salary</p>
							<h2>RM {{v_report.gross_salary | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of Deductions</p>
							<h2>RM {{v_report.total_deductions | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of Net Pay</p>
							<h2>RM {{v_report.net_pay | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of Adjustments</p>
							<h2>RM {{v_report.total_adjustments | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of Net Payable</p>
							<h2>RM {{v_report.salary_paid | number : 2}}</h2>
							
						</div>
						
						
					</div>
				</div>

				<!-- No Time Pay Off Tab -->
				<div class="tab-pane" id="no_time_pay_off">
					<div class="table-responsive">
						<table class="table">
							<thead>
								<th style="width: 230px;">Employee</th>
								<th>Absent Days</th>
								<th>Absent Amount</th>
								<th>Unpaid Leaves</th>
								<th>Unpaid Leaves Amount</th>
								<th>Lateness Count</th>
								<th>Lateness Time</th>
								<th>Lateness Amount</th>
							</thead>
							<tbody>
								<tr ng-if="report.employees.length == 0">
									<td colspan="10" class="text-center">
										<p class="text-danger">No record found for this month</p>
									</td>
								</tr>
								
								<tr ng-repeat="r in report.employees">
									<td><a style="color:#009ce7" href="<?php echo base_url();?>payroll/calculator/{{r.employee_id}}/{{r.process_id}}" target="_blank"><b>{{r.name}}</b></a></td>
										<td ng-show="r.id != null">{{r.absent_days | number:2}}</td>
										<td ng-show="r.id != null">{{r.absent_amount | number:2}}</td>
										<td ng-show="r.id != null">{{r.unpaid_leaves | number:2}}</td>
										<td ng-show="r.id != null">{{r.unpaid_leaves_amount | number:2}}</td>
										<td ng-show="r.id != null">{{r.lateness_count | number:2}}</td>
										<td ng-show="r.id != null">{{r.lateness_time}}</td>
										<td ng-show="r.id != null">{{r.lateness_amount | number:2}}</td>
									<td ng-show="r.id == null" colspan="7" class="text-center text-danger">Payroll not calculated yet</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="row">
						<div class="col-md-12">
							<h3>Totals</h3>
							<hr>
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of Absent Days</p>
							<h2>{{v_report.absent_days | number : 2}}</h2>
							<!-- <p style="margin-top: -10px;"><small>Last month: RM {{v_report_last.epf + v_report_last.epf_c | number : 2}}</small></p> -->
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of Absent Amount</p>
							<h2>RM {{v_report.absent_amount | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of Unpaid Leaves</p>
							<h2>{{v_report.unpaid_leaves| number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of Unpaid Leave Amount</p>
							<h2>RM {{v_report.unpaid_leaves_amount | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of Lateness Count</p>
							<h2>{{v_report.lateness_count | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of Lateness Time</p>
							<h2>{{v_report.lateness_time}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of Lateness Amount</p>
							<h2>RM {{v_report.lateness_amount | number : 2}}</h2>
							
						</div>
						
						
					</div>
				</div>

				<!-- Extra Earnings Tab -->
				<div class="tab-pane" id="extra_earnings">
					<div class="table-responsive">
						<table class="table">
							<thead>
								<th style="width: 230px;">Employee</th>
								<th>OT Hrs</th>
								<th>Overtime Amount</th>
								<th>OT RD Hrs</th>
								<th>OT RD Amount</th>
								<th>OT PH Hrs</th>
								<th>OT PH Amount</th>
							</thead>
							<tbody>
								<tr ng-if="report.employees.length == 0">
									<td colspan="10" class="text-center">
										<p class="text-danger">No record found for this month</p>
									</td>
								</tr>
								
								<tr ng-repeat="r in report.employees">
									<td><a style="color:#009ce7" href="<?php echo base_url();?>payroll/calculator/{{r.employee_id}}/{{r.process_id}}" target="_blank"><b>{{r.name}}</b></a></td>
										<td ng-show="r.id != null">{{r.ot_hours | number:2}}</td>
										<td ng-show="r.id != null">{{r.ot_amount | number:2}}</td>
										<td ng-show="r.id != null">{{r.ot_rd_hours | number:2}}</td>
										<td ng-show="r.id != null">{{r.ot_rd_amount | number:2}}</td>
										<td ng-show="r.id != null">{{r.ot_ph_hours | number:2}}</td>
										<td ng-show="r.id != null">{{r.ot_ph_amount | number:2}}</td>
									<td ng-show="r.id == null" colspan="6" class="text-center text-danger">Payroll not calculated yet</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="row">
						<div class="col-md-12">
							<h3>Totals</h3>
							<hr>
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of OT Hrs</p>
							<h2>{{v_report.ot_hours | number : 2}}</h2>
							<!-- <p style="margin-top: -10px;"><small>Last month: RM {{v_report_last.epf + v_report_last.epf_c | number : 2}}</small></p> -->
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of OT Amount</p>
							<h2>RM {{v_report.ot_amount | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of OT RD Hrs</p>
							<h2>{{v_report.ot_rd_hours | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of OT RD Amount</p>
							<h2>RM {{v_report.ot_rd_amount | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of OT PH Hrs</p>
							<h2>{{v_report.ot_ph_hours | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of OT PH Amount</p>
							<h2>RM {{v_report.ot_ph_amount | number : 2}}</h2>
							
						</div>
						
						
					</div>
				</div>

				
				<!-- Deductions Tab -->
				<div class="tab-pane" id="deductions_tab">
					<div class="table-responsive">
						<table class="table">
							<thead>
								<th style="width: 230px;">Employee</th>
								<th>EPF 'yee</th>
								<th>EPF 'yer</th>
								<th>SOCSO 'yee</th>
								<th>SOCSO 'yer</th>
								<th>EIS 'yee</th>
								<th>EIS 'yer</th>
								<th>PCB</th>
							</thead>
							<tbody>
								<tr ng-if="report.employees.length == 0">
									<td colspan="10" class="text-center">
										<p class="text-danger">No record found for this month</p>
									</td>
								</tr>
								
								<tr ng-repeat="r in report.employees">
									<td><a style="color:#009ce7" href="<?php echo base_url();?>payroll/calculator/{{r.employee_id}}/{{r.process_id}}" target="_blank"><b>{{r.name}}</b></a></td>
										<td ng-show="r.id != null">{{r.epf | number:2}}</td>
										<td ng-show="r.id != null">{{r.epf_c | number:2}}</td>
										<td ng-show="r.id != null">{{r.socso | number:2}}</td>
										<td ng-show="r.id != null">{{r.socso_c | number:2}}</td>
										<td ng-show="r.id != null">{{r.eis | number:2}}</td>
										<td ng-show="r.id != null">{{r.eis_c | number:2}}</td>
										<td ng-show="r.id != null">{{r.tax | number:2}}</td>
									<td ng-show="r.id == null" colspan="7" class="text-center text-danger">Payroll not calculated yet</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="row">
						<div class="col-md-12">
							<h3>Totals</h3>
							<hr>
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of EPF 'yee</p>
							<h2>RM {{v_report.epf| number : 2}}</h2>
							<!-- <p style="margin-top: -10px;"><small>Last month: RM {{v_report_last.epf + v_report_last.epf_c | number : 2}}</small></p> -->
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of EPF 'yer</p>
							<h2>RM {{v_report.epf_c | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of SOCSO 'yee</p>
							<h2>RM {{v_report.socso | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of SOCSO 'yer</p>
							<h2>RM {{v_report.socso_c | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of EIS 'yee</p>
							<h2>RM {{v_report.eis | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of EIS 'yer</p>
							<h2>RM {{v_report.eis_c | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of PCB</p>
							<h2>RM {{v_report.tax | number : 2}}</h2>
							
						</div>
						
					</div>
				</div>

				<!-- Adjustments Tab -->
				<div class="tab-pane" id="adjustments_tab">
					<div class="table-responsive">
						<table class="table">
							<thead>
								<th style="width: 230px;">Employee</th>
								<th>Loan</th>
								<th>Advance</th>
								<th>In Leau of Notice</th>
							</thead>
							<tbody>
								<tr ng-if="report.employees.length == 0">
									<td colspan="10" class="text-center">
										<p class="text-danger">No record found for this month</p>
									</td>
								</tr>
								
								<tr ng-repeat="r in report.employees">
									<td><a style="color:#009ce7" href="<?php echo base_url();?>payroll/calculator/{{r.employee_id}}/{{r.process_id}}" target="_blank"><b>{{r.name}}</b></a></td>
										<td ng-show="r.id != null">{{r.loan | number:2}}</td>
										<td ng-show="r.id != null">{{r.advance | number:2}}</td>
										<td ng-show="r.id != null">{{r.notice | number:2}}</td>
									<td ng-show="r.id == null" colspan="3" class="text-center text-danger">Payroll not calculated yet</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="row">
						<div class="col-md-12">
							<h3>Totals</h3>
							<hr>
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of Loan</p>
							<h2>RM {{v_report.loan | number : 2}}</h2>
							<!-- <p style="margin-top: -10px;"><small>Last month: RM {{v_report_last.epf + v_report_last.epf_c | number : 2}}</small></p> -->
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of Advance</p>
							<h2>RM {{v_report.advance | number : 2}}</h2>
							
						</div>
						<div class="col-md-3" style="margin-bottom: 10px;">
							<p class="text-muted">Total of In Leau of Notice</p>
							<h2>RM {{v_report.notice | number : 2}}</h2>
							
						</div>
						
						
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
								<td>{{nr.employee_bank}}</td>
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
			<br>
			<div class="row" ng-if="report.approved">
				<div class="col-md-12">
					<p class="text-info">Download Bank File</p>
					<div class="col-md-3">
						<div class="form-group">
							<select class="form-control" ng-model="selected_bank" ng-init="selected_bank = ''">
								<option value="">Select a bank</option>
								<option value="public_bank">Public Bank Berhad</option>
								<option value="cimb_bank">CIMB Bank Berhad</option>

							</select>
						</div>

					</div>
					<div class="col-md-3">
						<!-- <a ng-if="selected_bank != ''" href="<?php echo base_url(); ?>payroll/download_file/{{selected_process}}/{{selected_department == '' ? 0 : selected_department}}/{{selected_bank}}" target="_blank"><button class="btn btn-primary btn-block">Proceed</button></a> -->
						<button class="btn btn-primary btn-block" ng-if="selected_bank != ''" ng-click="download_file(selected_process, selected_department == '' ? 0 : selected_department, selected_bank)">Proceed</button>
						<button class="btn btn-primary btn-block" ng-if="selected_bank == ''" ng-click="showSelectBank()">Proceed</button>
					</div>
				</div>
			</div>

			<a href="#" id="download_btn" style="display: none;" download=""></a>

			
		</div>
		

	</div>
</div>
<?php include(APPPATH . "views/payroll/footer.php"); ?>
<script type="text/javascript">
	var base_url = '<?php echo base_url(); ?>';
	$(document).ready(function(){
		$('.process-select2').select2();
    });
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ang-ui.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/payroll.js?v=4.5"></script>