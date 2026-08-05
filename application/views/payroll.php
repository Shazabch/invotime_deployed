<?php include(APPPATH . "views/payroll/header.php"); ?>
<?php include(APPPATH . "views/payroll/sidebar.php"); ?>
<style>
	.table-div{
		min-height: 850px;
		max-width: 150px;
	}
</style>
<div class="page-wrapper" ng-app="myApp" ng-controller="payrollCtrl" ng-init="getData('<?php echo $employee_id;?>','<?php echo $process_id;?>')">
	
	<div class="content container-fluid">
		<div class="row">
			<div class="col-xs-4">
				<h4 class="page-title">Payroll Calculator</h4>
			</div>
		</div>
		<div class="row card-box" ng-cloak>
			<form ng-submit="getPayroll(process,department,employee)">
				<div class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label>Payroll Process</label>
							<select  class="form-control process-select2" ng-model="process" ng-change="filterEmployees()" required="">
								<option value="">Select payroll process</option>
								<option ng-repeat="p in payroll_processes" value="{{p.id}}">{{p.period}} - {{p.payroll_type}} | {{p.description}}</option>

							</select>
						</div>

					</div>

					<div class="col-md-3">
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
							<select class="form-control apply-select2" ng-model="employee">
								<option value="">Select an employee</option>
								<option ng-repeat="e in employees" value="{{e.id}}">{{e.name}}</option>
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
		<div class="row card-box" ng-if="payroll.length != 0" ng-cloak>
			<div class="col-md-8">
				<span style="font-size: 20px"><b>{{payroll.employee_name}}</b> ({{payroll.period}})</span>
			</div>
			<div class="col-md-4 text-right">
				<button class="btn btn-default" ng-click="getPayroll(selected_process,selected_department,payroll.previous)" ng-disabled="payroll.previous == false"><i class="fa fa-arrow-left"></i></button>
				<button class="btn btn-default" ng-click="getPayroll(selected_process,selected_department,payroll.next)" ng-disabled="payroll.next == false"><i class="fa fa-arrow-right"></i></button>
				<span ng-if="payroll.db == 'false'">
					<button class="btn btn-success" ng-click="save_data()">Save</button>
					<button class="btn btn-warning" ng-click="save_data(payroll.next)" ng-disabled="payroll.next == false">Save & Next</button>
				</span>
				<span ng-if="(payroll.db == 'true' || payroll.resetting == 'true') && payroll.edit_mode == 'false' && payroll.confirm == 'false'">
					<button class="btn btn-primary" ng-click="edit_data()">Edit</button>
				</span>
				<span ng-if="(payroll.db == 'true' || payroll.resetting == 'true') && payroll.edit_mode == 'true' && payroll.confirm == 'false' ">
					<!-- <button class="btn btn-default" ng-click="reset_data()">Reset</button> -->
					<button class="btn btn-success" ng-click="save_data()">Update</button>	
					<button class="btn btn-warning" ng-click="save_data(payroll.next)" ng-disabled="payroll.next == false">Update & Next</button>
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
									<label>Basic Wages (RM)</label>
									<div class="input-group">
										<span class="input-group-addon">RM</span>
										<input type="number" class="form-control" ng-model="payroll.basic" ng-change="calculate()" ng-disabled="true">
									</div>
								</div>
								<div class="form-group" ng-if="payroll.salary_type == 'daily'">
									<label>Daily Wages (RM)</label>
									<div class="input-group">
										<span class="input-group-addon">RM</span>
										<input type="number" class="form-control" ng-model="payroll.daily" ng-change="calculate()" ng-disabled="true">
									</div>
								</div>
								<div class="form-group">
									<label>Eligible Wages (RM)</label>
									<div class="input-group">
										<span class="input-group-addon">RM</span>
										<input type="number" class="form-control" ng-model="payroll.eligible_amount" ng-change="calculate()">
									</div>
								</div>
								<div class="form-group">
									<label>Total Wages (RM)</label>
									<div class="input-group">
										<span class="input-group-addon">RM</span>
										<input type="number" class="form-control" ng-model="payroll.total_eligible_amount" ng-disabled="true">
									</div>
								</div>
								<a href="javascript:void(0)" ng-click="reset(selected_process,selected_department,payroll.employee_id)" ng-if="payroll.db == 'true' && payroll.edit_mode == 'true' && payroll.confirm == 'false' ">Reset this person's payroll calculation for this month</a>
								
							</div></td>
							<td><div class="table-div">
								<b>RM {{payroll.basic2 | number:2}}</b> / {{payroll.type3}}
								<br>
								<br>
								<label>Worked:</label>
								<div class="input-group">
									<input type="number" class="form-control" ng-model="payroll.unit" ng-disabled="payroll.salary_type == 'monthly'" ng-change="calculate(false, true, true)">
									<span class="input-group-addon">{{payroll.type2}}</span>
								</div><br><br>
								Amount:<br>
								<p style="font-size: 20px">RM {{payroll.basic_amount | number:2}}</p>

								<p>Add-on salary:</p>
								<div class="row" ng-repeat="e in payroll.earnings" style="border: 1px solid gray; margin-bottom: 20px;box-shadow: 0 2px 2px 2px rgba(0, 0, 0, .14), 0 3px 1px 3px rgba(0, 0, 0, .2), 0 1px 5px 5px rgba(0, 0, 0, .12)">
									<div class="col-md-12">
									<span style="font-size: 18px;">RM {{e.num * e.rate | number:2}}</span>
									<button class="btn btn-xs btn-danger pull-right" ng-click="removeEarning($index)" style="margin-top: 2px;"><i class="fa fa-close"></i></button>
								</div>
								<br><br>
									<div class="form-group col-md-6" style="padding-left: 2px;padding-right: 2px">
										<input class="form-control" type="number" ng-model="e.num" ng-change="calculate(false, false)">
									</div>
									<div class="form-group col-md-6" style="padding-left: 2px;padding-right: 2px">
										<input class="form-control" type="text" ng-model="e.unit" placeholder="unit">
									</div>
									<p class="text-center">@</p>
									<div class="input-group" style="padding-right: 2px; padding-left: 2px;">
										<span class="input-group-addon">RM</span>
										<input type="number" class="form-control" ng-model="e.rate" ng-change="calculate(false, false)">
									</div>
									<p class="text-center">pay/unit</p>
									<div class="col-md-6" style="padding-right: 2px; padding-left: 2px;">
										<div class="checkbox">
											<label><input type="checkbox" ng-checked="e.tax == 'true'" ng-click="toggleTaxEarning($index)"><b>PCB</b></label>
										</div>
									</div>
									<div class="col-md-6" style="padding-right: 2px; padding-left: 2px;">
										<div class="checkbox">
											<label><input type="checkbox" ng-checked="e.epf == 'true'" ng-click="toggleEPFEarning($index)"><b>EPF</b></label>
										</div>
									</div>
									<div class="col-md-6" style="padding-right: 2px; padding-left: 2px;">
										<div class="checkbox">
											<label><input type="checkbox" ng-checked="e.socso == 'true'" ng-click="toggleSOCSOEarning($index)"><b>SOCSO</b></label>
										</div>
									</div>
									<div class="col-md-6" style="padding-right: 2px; padding-left: 2px;">
										<div class="checkbox">
											<label><input type="checkbox" ng-checked="e.eis == 'true'" ng-click="toggleEISEarning($index)"><b>EIS</b></label>
										</div>
									</div>
								</div>
								<a href="javascript:void(0)" ng-click="addNewEarning()" ng-if="payroll.db == 'false' || (payroll.edit_mode == 'true' && payroll.confirm == 'false') ">Add basic earning</a>
								<hr>
								<span><b>No time pay off</b></span><br><br>
								<div class="form-group" ng-repeat="d in payroll.deductions" ng-if="d.db == 'false'">
									<label contenteditable ng-model="d.name" ng-if="d.db == 'false' && d.fixed != 'yes'" style="font-size: 11px;">{{d.name}}</label>
									<label ng-if="d.db == 'true' || d.fixed == 'yes'" style="font-size: 11px;">{{d.name}}</label>

									<span class="pull-right" ng-if="d.show_settings == 'true'">

										<span uib-popover-template="d.template" class="btn btn-xs btn-default" popover-placement="bottom" popover-trigger="'outsideClick'">
											<i class="fa fa-gear"></i>
										</span>

										<script type="text/ng-template" id="deduction_template.html">
											<p>This item is subjected to:</p>
											<div class="checkbox">
												<label><input type="checkbox" ng-checked="d.epf == 'true'" ng-click="toggleEPFDeduction(d)"><b>EPF</b></label>
											</div>
											<div class="checkbox">
												<label><input type="checkbox" ng-checked="d.socso == 'true'" ng-click="toggleSOCSODeduction(d)"><b>SOCSO</b></label>
											</div>
											<div class="checkbox">
												<label><input type="checkbox" ng-checked="d.eis == 'true'" ng-click="toggleEISDeduction(d)"><b>EIS</b></label>
											</div>
										</script>
									</span>

									<span class="checkbox-inline pull-right"><span ng-if="d.epf_percentage >= 0" style="margin-right: 25px;">-{{d.epf_percentage}}%</span><input type="checkbox" ng-checked="d.is_apply == 'true'" ng-if="d.show_apply == 'true'" ng-click="toggleApply($index)">
										<button class="btn btn-xs btn-danger" ng-if="d.db=='false' && d.remove != 'false'" ng-click="removeDeduction($index)"><i class="fa fa-close"></i></button></span>

										<span ng-if="d.type == 'not_sure'">
											<br>
											<label class="checkbox-inline"><input type="checkbox" ng-checked="d.percentage == 'true'" ng-click="togglePercentage($index)">%</label>
										</span>
										<div class="input-group">
										<span class="input-group-addon"><span ng-if="d.percentage=='true'">%</span><span ng-if="d.percentage=='false'">RM</span></span>
										<input type="number" class="form-control" ng-model="d.amount" ng-change="calculate(false, false)" ng-disabled="d.is_apply == 'false'">
									</div>
									<small>{{d.description}}<span ng-if="d.type2 == 'rate_hour_late'"> x RM{{payroll.rate_hour_late | number : 2}}</span><span ng-if="d.type2 == 'rate_day'"> x RM{{payroll.rate_day | number : 2}}</span></small>
									</div>
								<button class="btn btn-sm btn-success" ng-click="addDeduction()"><i class="fa fa-plus"></i></button>
							</div></td>
							<td><div class="table-div">
								<div class="form-group" ng-if="a.type2 != undefined || show_more_allowances || a.new == 'true'" ng-repeat="a in payroll.allowances">
									<label contenteditable ng-model="a.allowance_name" ng-if="a.db == 'false'" style="font-size: 11px;">{{a.allowance_name}}</label>
									<label ng-if="a.db == 'true'" style="font-size: 11px;">{{a.allowance_name}}</label>
									<span class="pull-right">

										<span uib-popover-template="a.template" class="btn btn-xs btn-default" popover-placement="bottom" popover-trigger="'outsideClick'">
											<i class="fa fa-gear"></i>
										</span>
										<button class="btn btn-xs btn-danger" ng-if="a.db=='false'" ng-click="removeAllowance($index)"><i class="fa fa-close"></i></button>

										<script type="text/ng-template" id="test_template.html">
											<p>This item is subjected to:</p>
											<div class="checkbox">
												<label><input type="checkbox" ng-checked="a.tax == 'true'" ng-click="toggleTax($index)"><b>PCB</b></label>
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
											<div class="checkbox">
												<label><input type="checkbox" ng-checked="a.eligible_salary == 'true'" ng-click="toggleEligibleSalary($index)"><b>Add to Total Wages</b></label>
											</div>
										</script>
									</span>
									<div class="input-group">
										<span class="input-group-addon">RM</span>
										<input type="number" class="form-control" ng-model="a.amount" ng-change="calculate(false, false)" ng-if="a.type2 != undefined">
										<input type="number" class="form-control" ng-model="a.amount" ng-change="calculate(false, true)" ng-if="a.type2 == undefined">
									</div>
									<small>{{a.description}}
										<span ng-if="a.description && a.type2 == 'per_hour'"> x RM{{payroll.rate_hour | number : 2}} x {{a.multiplier}}</span>
										<span ng-if="a.description && a.type2 == 'per_day_worked'"> x RM{{payroll.rate_day_worked | number : 2}} x {{a.multiplier}}</span>
									</small>
								</div>
								<button class="btn btn-link btn-sm" ng-click="toggleShowMoreAllowances()"><span ng-if="!show_more_allowances"><u>Show more</u></span><span ng-if="show_more_allowances"><u>Show less</u></span></button>
								<br><br>
								<button class="btn btn-sm btn-success" ng-click="addAllowance()"><i class="fa fa-plus"></i></button>
							</div></td>
							<script type="text/ng-template" id="epf_template.html">
								<input class="form-control" type="number" ng-model="d.epf_percentage" ng-change="customize_epf(d.epf_percentage)" />
							</script>
							<td><div class="table-div">
								<div class="form-group" ng-repeat="d in payroll.deductions" ng-if="d.db == 'true'">
									<label contenteditable ng-model="d.name" ng-if="d.db == 'false'" style="font-size: 11px;">{{d.name}}</label>
									<label ng-if="d.db == 'true'" style="font-size: 11px;">{{d.name}}</label>
									<span class="checkbox-inline pull-right"><span ng-if="d.epf_percentage >= 0" style="margin-right: 25px;" uib-popover-template="epf_template" popover-placement="left" popover-trigger="'outsideClick'"><span ng-if="d.epf_percentage > 0">-</span>{{d.epf_percentage}}%</span><input type="checkbox" ng-checked="d.is_apply == 'true'" ng-if="d.show_apply == 'true'" ng-click="toggleApply($index)">
										<button class="btn btn-xs btn-danger" ng-if="d.db=='false'" ng-click="removeDeduction($index)"><i class="fa fa-close"></i></button></span>



										<span ng-if="d.type == 'not_sure'">
											<br>
											<label class="checkbox-inline"><input type="checkbox" ng-checked="d.percentage == 'true'" ng-click="togglePercentage($index)">%</label>
										</span>
										<div class="input-group">
										<span class="input-group-addon"><span ng-if="d.percentage=='true'">%</span><span ng-if="d.percentage=='false'">RM</span></span>
										<input type="number" class="form-control" ng-model="d.amount" ng-change="calculate(false, false)" ng-disabled="d.is_apply == 'false'">
									</div>
									<span ng-if="d.epf_percentage >= 0">
										<label class="radio-inline">
											<input type="radio" name="epf_type" ng-model="payroll.epf_type" value="eleven" ng-change="calculate(false, false)">~11%
										</label>
										<label class="radio-inline">
											<input type="radio" name="epf_type" ng-model="payroll.epf_type" value="nine" ng-change="calculate(false, false)">9%
										</label>
									</span>
									</div>

									<hr>
								<span><b>Adjustments</b></span><br><br>
								<div class="form-group" ng-repeat="ad in payroll.adjustments">
									<label contenteditable ng-model="ad.name" ng-if="ad.db == 'false'" style="font-size: 11px;">{{ad.name}}</label>
									<label ng-if="ad.db == 'true'" style="font-size: 11px;">{{ad.name}}</label>

									<input type="checkbox" ng-checked="ad.is_apply == 'true'" ng-if="ad.show_apply == 'true'" ng-click="toggleApply($index)">
										<button class="btn btn-xs btn-danger" ng-if="ad.db=='false' && ad.remove != 'false'" ng-click="removeDeduction($index)"><i class="fa fa-close"></i></button></span>

										<span ng-if="ad.type == 'not_sure'">
											<br>
											<label class="checkbox-inline"><input type="checkbox" ng-checked="ad.percentage == 'true'" ng-click="togglePercentage($index)">%</label>
										</span>
										<div class="input-group">
										<span class="input-group-addon"><span ng-if="ad.percentage=='true'">%</span><span ng-if="ad.percentage=='false'">RM</span></span>
										<input type="number" class="form-control" ng-model="ad.amount" ng-change="calculate(false, false)" ng-disabled="ad.is_apply == 'false'">
									</div>
									</div>
									
								</div></td>
								<td><div class="table-div">
									<span>Total no time pay off</span><br>
									<span>RM {{payroll.no_time_pay_off | number:2}}</span>
									<br><br>
									<h2 style="font-size: 20px">Net Basic</h2>
									<h2 style="font-size: 20px">RM {{payroll.basic_amount - payroll.no_time_pay_off | number:2}}</h2>
									<br><br>
									<span>Total extra earnings</span><br>
									<span>RM {{payroll.total_allowance | number:2}}</span>
									<br><br>
									<h2 style="font-size: 20px">Gross Salary</h2>
									<h2 style="font-size: 20px">RM {{payroll.basic_amount - payroll.no_time_pay_off + payroll.total_allowance | number:2}}</h2>
									<br><br>
									<span>Total deductions</span><br>
									<span>RM {{payroll.total_deductions | number:2}}</span>
									<br><br>
									<h2 style="font-size: 20px">Net Pay</h2>
									<h2 style="font-size: 20px">RM {{payroll.basic_amount - payroll.no_time_pay_off + payroll.total_allowance - payroll.total_deductions | number:2}}</h2>
									<br><br>
									<span>Total adjustments</span><br>
									<span>RM {{payroll.total_adjustments | number:2}}</span>
									<br><br>
									<h2 style="font-size: 20px" class="text-info">Net Payable</h2>
									<h2 style="font-size: 20px" class="text-info">RM {{payroll.basic_amount - payroll.no_time_pay_off + payroll.total_allowance - payroll.total_deductions - payroll.total_adjustments | number:2}}</h2>
									<!-- <span>Gross pay</span><br>
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
									<h2 style="font-size: 20px" class="text-info">RM {{payroll.net_pay | number:2}}</h2> -->
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

	<?php include(APPPATH . "views/payroll/footer.php"); ?>
	<script type="text/javascript">
		var base_url = '<?php echo base_url(); ?>';
		$(document).ready(function(){
			$('.apply-select2').select2();
			$('.process-select2').select2();
        });
	</script>
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ang-ui.js"></script>
	<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/payroll.js?v=4.5"></script>

