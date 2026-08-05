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
				<h4 class="page-title">Full Report</h4>
			</div>
		</div>
		<div class="row card-box" ng-cloak>
			<form action="<?php echo site_url() ?>payroll/getFullReport" method="post">
				<div class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label>Payroll Process</label>
							<select class="form-control process-select2" ng-model="process" required="" name="process">
								<option value="">Select Payroll Process</option>
								<option ng-repeat="p in payroll_processes" value="{{p.id}}">{{p.period}} - {{p.payroll_type}} | {{p.description}}</option>

							</select>
						</div>

					</div>

					<div class="col-md-3">
						<div class="form-group">
							<label>Outlet</label>
							<select class="form-control" ng-model="branch" name="branch">
								<option value="">All</option>
								<option ng-repeat="b in branches" value="{{b.id}}">{{b.name}}</option>

							</select>
						</div>

					</div>

					<div class="col-md-3">
						<div class="form-group">
							<label>Department</label>
							<select class="form-control" ng-model="department" name="department">
								<option value="">All</option>
								<option ng-repeat="d in departments" value="{{d.id}}">{{d.name}}</option>


							</select>
						</div>

					</div>

					

					<div class="col-md-3">
						<label>&nbsp;</label>
						<button class="btn btn-primary btn-block">Download Full Report</button>

					</div>

				</div>

			</form>





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
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/payroll.js?v=4.0"></script>