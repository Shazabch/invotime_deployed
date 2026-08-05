<style>
	.table-div{
		min-height: 300px;
		max-width: 150px;
	}
</style>
<div class="page-wrapper" ng-app="myApp" ng-controller="payrollCtrl" ng-init="getData('<?php echo $employee_id;?>','<?php echo $year;?>','<?php echo $month;?>')">
	
	<div class="content container-fluid">
		<div class="row">
			<div class="col-xs-4">
				<h4 class="page-title">Payroll Calculator</h4>
			</div>
		</div>
		<div class="row card-box" ng-cloak>
			<form ng-submit="getPayroll(employee,month,year)">
				<div class="row">
					<div class="col-md-2">
						<div class="form-group">
							<label>Outlet</label>
							<select  class="form-control" ng-model="branch" ng-change="filterEmployees()">
								<option value="">All</option>
								<option ng-repeat="b in branches" value="{{b.id}}">{{b.name}}</option>

							</select>
						</div>

					</div>

					<div class="col-md-2">
						<div class="form-group">
							<label>Department</label>
							<select class="form-control" ng-model="department" ng-change="filterEmployees()">
								<option value="">All</option>
								<option ng-repeat="d in departments" value="{{d.id}}">{{d.name}}</option>


							</select>
						</div>

					</div>

					<div class="col-md-3">
						<div class="form-group">
							<label>Employee</label>
							<select class="form-control" ng-model="employee">
								<option ng-repeat="e in employees" value="{{e.id}}">{{e.name}}</option>
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

					<div class="col-md-1">
						<label>&nbsp;</label>
						<button class="btn btn-primary btn-block">Filter</button>

					</div>

				</div>

			</form>





		</div>
		<div class="row card-box" ng-if="payroll.length != 0" ng-cloak>
			<div class="col-md-9">
				<span style="font-size: 20px"><b>{{payroll.employee_name}}</b> ({{payroll.month_name}} - {{payroll.year}})</span>
			</div>
			<div class="col-md-3 text-right">
				<span ng-if="payroll.db == 'false'"><button class="btn btn-primary" ng-click="save_data()">Save</button></span>
				<span ng-if="payroll.db == 'true' && payroll.edit_mode == 'false' && payroll.confirm == 'false'">
					<button class="btn btn-primary" ng-click="edit_data()">Edit</button>
				</span>
				<span ng-if="payroll.db == 'true' && payroll.edit_mode == 'true' && payroll.confirm == 'false' ">
					<!-- <button class="btn btn-default" ng-click="reset_data()">Reset</button> -->
					<button class="btn btn-success" ng-click="save_data()">Update</button>	
				</span>
			</div>
			
			
			<fieldset ng-disabled="final" class="col-md-12  table-responsive">
				<table class="table">
					<thead>
						<th>Salary type</th>
						<th>Basic earnings</th>
						<th>Extra earnings</th>
						<th>Deductions</th>
						<th>Pay amount</th>
					</thead>
					<tbody>
						<tr>
							<td><div class="table-div">
								<div class="form-group">
									<select class="form-control" ng-model="payroll.salary_type" ng-change="type_change()">
										<option value="monthly">Monthly</option>
										<option value="fortnight">Fortnight</option>
										<option value="daily">Daily</option>
										<option value="hourly">Hourly</option>
									</select>
								</div>
								<div class="form-group">
									<label>Full Amount (RM)</label>
									<div class="input-group">
										<span class="input-group-addon">RM</span>
										<input type="number" class="form-control" ng-model="payroll.basic" ng-change="calculate()">
									</div>
								</div>
							</div></td>
							<td><div class="table-div">
								<b>RM {{payroll.basic2 | number:2}}</b> / {{payroll.type3}}
								<br>
								<br>
								<label>Worked:</label>
								<div class="input-group">
									<input type="text" class="form-control" ng-model="payroll.unit" ng-disabled="payroll.salary_type == 'monthly'" ng-change="calculate()">
									<span class="input-group-addon">{{payroll.type2}}</span>
								</div><br><br>
								Amount:<br>
								<p style="font-size: 20px">RM {{payroll.basic_amount | number:2}}</p>
							</div></td>
							<td><div class="table-div">
								<div class="form-group" ng-repeat="a in payroll.allowances">
									<label contenteditable ng-model="a.allowance_name" ng-if="a.db == 'false'" style="font-size: 11px;">{{a.allowance_name}}</label>
									<label ng-if="a.db == 'true'" style="font-size: 11px;">{{a.allowance_name}}</label>
									<span class="pull-right">

										<button uib-popover-template="a.template" type="button" class="btn btn-xs btn-default" popover-placement="bottom">
											<i class="fa fa-gear"></i>
										</button>
										<button class="btn btn-xs btn-danger" ng-if="a.db=='false'" ng-click="removeAllowance($index)"><i class="fa fa-close"></i></button>

										<script type="text/ng-template" id="test_template.html">
											<p>This item is subjected to:</p>
											<div class="checkbox">
												<label><input type="checkbox" ng-checked="a.tax == 'true'" ng-click="toggleTax($index)"><b>Tax</b></label>
											</div>
											<div class="checkbox">
												<label><input type="checkbox" ng-checked="a.epf == 'true'" ng-click="toggleEPF($index)"><b>EPF</b></label>
											</div>
											<div class="checkbox">
												<label><input type="checkbox" ng-checked="a.socso == 'true'" ng-click="toggleSOCSO($index)"><b>SOCSO</b></label>
											</div>
											<div class="checkbox">
												<label><input type="checkbox" ng-checked="a.eis == 'true'" ng-click="toggleEIS($index)"><b>EIS</b></label>
											</div>
										</script>
									</span>
									<div class="input-group">
										<span class="input-group-addon">RM</span>
										<input type="number" class="form-control" ng-model="a.amount" ng-change="calculate()">
									</div>
								</div>
								<button class="btn btn-sm btn-success" ng-click="addAllowance()"><i class="fa fa-plus"></i></button>
							</div></td>
							<td><div class="table-div">
								<div class="form-group" ng-repeat="d in payroll.deductions">
									<label contenteditable ng-model="d.name" ng-if="d.db == 'false'" style="font-size: 11px;">{{d.name}}</label>
									<label ng-if="d.db == 'true'" style="font-size: 11px;">{{d.name}}</label>

									<span class="checkbox-inline pull-right"><span ng-if="d.epf_percentage >= 0" style="margin-right: 25px;">-{{d.epf_percentage}}%</span><input type="checkbox" ng-checked="d.is_apply == 'true'" ng-if="d.show_apply == 'true'" ng-click="toggleApply($index)">
										<button class="btn btn-xs btn-danger" ng-if="d.db=='false'" ng-click="removeDeduction($index)"><i class="fa fa-close"></i></button></span>

										<span ng-if="d.type == 'not_sure'">
											<br>
											<label class="checkbox-inline"><input type="checkbox" ng-checked="d.percentage == 'true'" ng-click="togglePercentage($index)">%</label>
										</span>
										<div class="input-group">
										<span class="input-group-addon"><span ng-if="d.percentage=='true'">%</span><span ng-if="d.percentage=='false'">RM</span></span>
										<input type="number" class="form-control" ng-model="d.amount" ng-change="calculate2()" ng-disabled="d.is_apply == 'false'">
									</div>
									</div>
									<button class="btn btn-sm btn-success" ng-click="addDeduction()"><i class="fa fa-plus"></i></button>
								</div></td>
								<td><div class="table-div">
									<span>Gross pay</span><br>
									<h2 style="font-size: 20px">RM {{payroll.basic_amount | number:2}}</h2>
									<br>
									<br>
									<span>Additional earnings</span><br>
									<h2 style="font-size: 20px">RM {{payroll.total_allowance | number:2}}</h2>
									<br>
									<br>
									<span>Deductions</span><br>
									<h2 style="font-size: 20px">RM {{payroll.total_deductions | number:2}}</h2>

									<hr>
									<span>Net pay</span><br>
									<h2 style="font-size: 20px" class="text-info">RM {{payroll.net_pay | number:2}}</h2>
									<hr>
									<p>Company Contribution</p>
									<br>
									<span>EPF</span><br>
									<h2 style="font-size: 20px">RM {{payroll.epf_c | number:2}}</h2>
									<br>
									<br>
									<span>SOCSO</span><br>
									<h2 style="font-size: 20px">RM {{payroll.socso_c | number:2}}</h2>
									<br>
									<br>
									<span>EIS</span><br>
									<h2 style="font-size: 20px">RM {{payroll.eis_c | number:2}}</h2>
								</div></td>
							</tr>
						</tbody>
					</table>
				</fieldset>
			</div>

		</div>
	</div>
	<script type="text/javascript">
		var base_url = '<?php echo base_url(); ?>';
	</script>
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ang-ui.js"></script>
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/payroll.js?v=1.5"></script>