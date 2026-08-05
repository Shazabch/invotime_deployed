<?php include(APPPATH . "views/payroll/header.php"); ?>
<?php include(APPPATH . "views/payroll/sidebar.php"); ?>
<style>
	.table-div{
		min-height: 300px;
		max-width: 150px;
	}
</style>
<div class="page-wrapper" ng-app="myApp" ng-controller="customizedreportCtrl" ng-init="getData('<?php echo $process_id;?>')">
	
	<div class="content container-fluid">
		<div class="row">
			<div class="col-xs-4">
				<h4 class="page-title"><?php echo $pageTitle; ?></h4>
			</div>
		</div>
		<div class="row card-box" ng-cloak>
			<form ng-submit="loadConfiguration()">
				<div class="row">
					<div class="col-md-3">
						<div class="form-group">
							<label>Payroll Summary Type</label>
							<select class="form-control process-select2" ng-model="summary_type" required="" name="summary_type" required>
								<option value="">Select Payroll Summary Type</option>
								<option ng-repeat="s in summary_types" value="{{s.id}}">{{s.name}}</option>

							</select>
						</div>
					</div>

					<div class="col-md-3">
						<label>&nbsp;</label>
						<button class="btn btn-primary btn-block">Configure</button>

					</div>

				</div>

			</form>
			<div class="col-md-12" ng-repeat="summary_column in summary_columns">
				<div class="col-md-3">
					<div class="form-group">
						<label>{{summary_column.column_name}}</label>
						<select class="form-control" ng-model="summary_column.value_name">
							<option value="">Select Value</option>
							<option value="{{v}}" ng-repeat="v in values">{{v}}</option>
						</select>
					</div>
				</div>
			</div>

			<div class="col-md-3" ng-if="summary_columns.length > 0">
				<label>&nbsp;</label>
				<button class="btn btn-primary btn-block" ng-click="saveSummaryColumns()">Save</button>

			</div>





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