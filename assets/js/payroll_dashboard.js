var config = {
	headers: {
		'Content-Type': 'application/json;charset=utf-8;'
	}
}; 


var app = angular.module('payroll_dashboard', []);

app.controller('processPayrollCtrl', function($scope,$http) {
	$scope.group = 'simple';
	$scope.editGroup = 'simple';
	$scope.payrolls = [];

	$scope.current_dates = [];
	for(i=1; i<=31; i++){$scope.current_dates.push(i);}

	$scope.years = [];

	$scope.employees = [];
	$scope.grouped_employees = {};

	$scope.process = {
		type: '',
		include_fix: 'N',
		description: '',
		month: '',
		year: '',
		leave_cut_off: '31',
		bonus_months: '',
		employees_group: [],
		employees: []
	}

	$scope.editPayroll = function(payroll){
		$scope.editProcess = angular.copy(payroll);
		$scope.editProcess.bonus_months = parseFloat($scope.editProcess.bonus_months);
		$scope.getEmployees(true);
	}

	$scope.deletePayroll = function(payroll){
		$scope.payroll_delete_model = {id : payroll.id, company_id : payroll.company_id, branch_id : payroll.branch_id}
	}

	$scope.deletePayrollConfirmed = function(){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'invocore_payroll/deletePayroll', $scope.payroll_delete_model, config).then(function (response) {
			$scope.payrolls = response.data.process_payrolls;
			showNotification("Success",'Payroll Process deleted successfully!',"success");
			$('#deletePayroll').modal('toggle');
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.getData = function(refresh = false){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'invocore_payroll/get_data_process_payroll', '', config).then(function (response) {
			$scope.years = response.data.years;
			$scope.process.year = response.data.current_year;
			$scope.process.leave_cut_off = response.data.leave_cut_off;
			$scope.company_id = response.data.company_id;
			$scope.branch_id = response.data.branch_id;
			$scope.payrolls = response.data.process_payrolls;
			$scope.branches = response.data.branches;
			$scope.admin_type = response.data.admin_type;
			$('body').LoadingOverlay("hide");
			if(refresh){
				showNotification("Success",'Payroll Process data refreshed successfully!',"success");
			}
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.getDays = function(){
		var lastDay = new Date($scope.process.year, parseInt($scope.process.month), 0).getDate();
		var temp_dates = [];
		for(i=1; i<=lastDay; i++){temp_dates.push(i);}
		$scope.current_dates = temp_dates;
		$scope.process.leave_cut_off = '20';
	}

	$scope.getDaysEdit = function(){
		var lastDay = new Date($scope.editProcess.year, parseInt($scope.editProcess.month), 0).getDate();
		var temp_dates = [];
		for(i=1; i<=lastDay; i++){temp_dates.push(i);}
		$scope.current_dates = temp_dates;
		$scope.editProcess.leave_cut_off = '20';
	}

	$scope.getEmployees = function(edit = false, first_time = true){
		$scope.process.employees_group = [];
		$scope.process.employees = [];
		var month, year;
		if(($scope.process.month != undefined && $scope.process.year != undefined && $scope.process.month != "" && $scope.process.year != "" && !edit) || ($scope.editProcess != undefined && $scope.editProcess.month != undefined && $scope.editProcess.month != "" && $scope.editProcess.year != undefined && $scope.editProcess.year != "" && edit)){
			$('body').LoadingOverlay("show",{maxSize:50});
			if(!edit){
				month = $scope.process.month;
				year = $scope.process.year;
			}else{
				month = $scope.editProcess.month;
				year = $scope.editProcess.year;
			}
			$http.post(base_url + 'invocore_payroll/getEmployeesForPayrollProcess', {month: month,year: year}, config).then(function (response) {
				
				$scope.employees = response.data.employees;
				$scope.grouped_employees = response.data.grouped_employees;

				// add simple employees
				var options = "";
				angular.forEach(response.data.employees, function(value){
					options += '<option value="' + value.id + '">' + value.name + '</option>';
				});
				$('.grouped_employees').children().remove();
				$(".grouped_employees").append(options);
				

				

				$(".multi").multiselect('rebuild');

				if(edit){
					if(!first_time){
						$scope.editProcess.employees_group = [];
						$scope.editProcess.employees = [];
					}
					
					$scope.changeGroupEdit();
				}


				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}else{
			$('.simple_employees').children().remove();
			$('.department_employees').children().remove();
			$('.role_employees').children().remove();
			$('.department_role_employees').children().remove();
			$(".multi").multiselect('rebuild');
		}
	}

	$scope.changeGroup = function(){
		// if($scope.process.employees_group.includes("department") && $scope.process.employees_group.includes("role")){
		// 	$scope.group = "department_role";
		// }else if($scope.process.employees_group.includes("department")){
		// 	$scope.group = "department";
		// }else if($scope.process.employees_group.includes("role")){
		// 	$scope.group = "role";
		// }else{
		// 	$scope.group = "simple";
		// }

		$scope.group = $scope.process.employees_group.join('_');
		$scope.group = ($scope.group == "") ? 'simple' : $scope.group;

		if($scope.group == 'simple'){
			var options = "";
			angular.forEach($scope.employees, function(value){
				options += '<option value="' + value.id + '">' + value.name + '</option>';
			});
			$('.grouped_employees').children().remove();
			$(".grouped_employees").append(options);
		}else{
			var options = "";
			angular.forEach($scope.grouped_employees[$scope.group], function(inner_employees, dep){
				options += '<optgroup label="' + dep + '">';

				angular.forEach(inner_employees, function(value){
					options += '<option value="' + value.id + '">' + value.name + '</option>';
				});

				options += '</optgroup>';
			});
			$('.grouped_employees').children().remove();
			$(".grouped_employees").append(options);
		}
		
		$(".multi").multiselect('rebuild');

		$('.multi').multiselect('select', $scope.process.employees);
	}

	$scope.changeGroupEdit = function(){
		// if($scope.editProcess.employees_group.includes("department") && $scope.editProcess.employees_group.includes("role")){
		// 	$scope.editGroup = "department_role";
		// }else if($scope.editProcess.employees_group.includes("department")){
		// 	$scope.editGroup = "department";
		// }else if($scope.editProcess.employees_group.includes("role")){
		// 	$scope.editGroup = "role";
		// }else{
		// 	$scope.editGroup = "simple";
		// }

		$scope.editGroup = $scope.editProcess.employees_group.join('_');
		$scope.editGroup = ($scope.editGroup == "") ? 'simple' : $scope.editGroup;

		if($scope.editGroup == 'simple'){
			var options = "";
			angular.forEach($scope.employees, function(value){
				options += '<option value="' + value.id + '">' + value.name + '</option>';
			});
			$('.grouped_employees').children().remove();
			$(".grouped_employees").append(options);
		}else{
			var options = "";
			angular.forEach($scope.grouped_employees[$scope.editGroup], function(inner_employees, dep){
				options += '<optgroup label="' + dep + '">';

				angular.forEach(inner_employees, function(value){
					options += '<option value="' + value.id + '">' + value.name + '</option>';
				});

				options += '</optgroup>';
			});
			$('.grouped_employees').children().remove();
			$(".grouped_employees").append(options);
		}
		
		$(".multi").multiselect('rebuild');
		
		$('.multi_group').multiselect('deselectAll');
		$('.multi_group').multiselect('select', $scope.editProcess.employees_group);
		$('.multi_employees').multiselect('deselectAll');
		$('.multi_employees').multiselect('select', $scope.editProcess.employees);
	}

	$scope.refreshMulti = function(){
		$scope.process.employees_group = [];
		$scope.process.employees = [];
		$scope.process.type = '';
		$scope.process.description = '';
		$scope.process.month = '';
		$scope.process.year = '';
		$scope.process.include_fix = 'N';
		$scope.process.bonus_months = '';
		$scope.employees = [];
		$scope.grouped_employees = {};
		$scope.process.payroll_branch_id = '';
		$scope.group = 'simple';
		$('.grouped_employees').children().remove();
		$('.multi').multiselect('rebuild');
		$('.multi').multiselect('deselectAll');

	}

	$scope.toggleIncludeFix = function(){
		$scope.process.include_fix = $scope.process.include_fix == 'N' ? 'Y' : 'N';
	}

	$scope.toggleIncludeFixEdit = function(){
		$scope.editProcess.include_fix = $scope.editProcess.include_fix == 'N' ? 'Y' : 'N';
	}

	$scope.saveNewProcess = function(valid){
		$scope.process.company_id = $scope.company_id;
		if($scope.admin_type == 'company'){
			$scope.process.branch_id = $scope.process.payroll_branch_id;
		}else{
			$scope.process.branch_id = $scope.branch_id;
		}
		if(!valid){
			showNotification("Error",'Please fill in all required fields!',"error");
		}else if($scope.process.employees.length == 0){
			showNotification("Error",'No employees selected!',"error");
		}else{
			$('body').LoadingOverlay("show",{maxSize:50});
			$http.post(base_url + 'invocore_payroll/saveNewProcess', $scope.process, config).then(function (response) {
				$scope.payrolls = response.data.process_payrolls;
				showNotification("Success",'New Payroll Process added successfully!',"success");
				$('#addPayroll').modal('toggle');
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}
	}

	$scope.updateProcess = function(valid){
		if($scope.admin_type == 'company'){
			$scope.editProcess.branch_id = $scope.editProcess.payroll_branch_id;
		}else{
			$scope.editProcess.branch_id = $scope.branch_id;
		}
		if(!valid){
			showNotification("Error",'Please fill in all required fields!',"error");
		}else if($scope.editProcess.employees.length == 0){
			showNotification("Error",'No employees selected!',"error");
		}else{
			$('body').LoadingOverlay("show",{maxSize:50});
			$http.post(base_url + 'invocore_payroll/updateProcess', $scope.editProcess, config).then(function (response) {
				$scope.payrolls = response.data.process_payrolls;
				showNotification("Success",'Payroll Process updated successfully!',"success");
				$('#editPayroll').modal('toggle');
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}
	}

});

app.controller('firstTimeSetupCtrl', function($scope,$http) {

	$scope.countries = [];
	$scope.states = [];

	$scope.deletedAllowanceIds = [];
	$scope.deletedDeductionIds = [];

	$scope.steps_done = 0;
	$scope.current_step = 1;

	$scope.show_wizard = false;

	$scope.profile = {
		name: '',
		country_id : '',
		epf_no : '',
		epf_percentage : '',
		company_registration_number: '',
		state_id : '',
		socso_no : '',
		phone : '',
		address : '',
		employer_file_no : '',
		autopay_code : '',
		tax_number : '',
		hrdf_percentage : ''
	}

	$scope.addBankModel = {
		bank_id : '',
		account_no : '',
		state_id : '',
		is_main : 'N'
	}

	$scope.addAllowanceModel = {
		code : '',
		description : '',
		start_period : '',
		end_period : '',
		pay_epf : 'Y',
		pay_socso_eis : 'Y',
		pay_tax : 'Y',
		pay_hrdf : 'Y',
		tax_rule_id : ''
	}

	$scope.addDeductionModel = {
		code : '',
		description : '',
		start_period : '',
		end_period : '',
		pay_epf : 'Y',
		pay_socso_eis : 'Y',
		pay_tax : 'Y',
		pay_hrdf : 'Y'
	}

	$scope.toggleSelectCalendar = function(index){
		if($scope.calendar.rest_days[index].is_apply == "N"){
			$scope.calendar.rest_days[index].is_apply = "Y";
		}else{
			$scope.calendar.rest_days[index].is_apply = "N";
		}
	}

	$scope.toggleEpfAllowanceAdd = function(){
		if($scope.addAllowanceModel.pay_epf == 'N'){
			$scope.addAllowanceModel.pay_epf = 'Y';
		}else{
			$scope.addAllowanceModel.pay_epf = 'N';
		}
	}

	$scope.toggleSocsoAllowanceAdd = function(){
		if($scope.addAllowanceModel.pay_socso_eis == 'N'){
			$scope.addAllowanceModel.pay_socso_eis = 'Y';
		}else{
			$scope.addAllowanceModel.pay_socso_eis = 'N';
		}
	}

	$scope.toggleTaxAllowanceAdd = function(){
		if($scope.addAllowanceModel.pay_tax == 'N'){
			$scope.addAllowanceModel.pay_tax = 'Y';
		}else{
			$scope.addAllowanceModel.pay_tax = 'N';
		}
	}

	$scope.toggleHrdfAllowanceAdd = function(){
		if($scope.addAllowanceModel.pay_hrdf == 'N'){
			$scope.addAllowanceModel.pay_hrdf = 'Y';
		}else{
			$scope.addAllowanceModel.pay_hrdf = 'N';
		}
	}

	$scope.toggleEpfAllowanceEdit = function(){
		if($scope.editAllowanceModel.pay_epf == 'N'){
			$scope.editAllowanceModel.pay_epf = 'Y';
		}else{
			$scope.editAllowanceModel.pay_epf = 'N';
		}
	}

	$scope.toggleSocsoAllowanceEdit = function(){
		if($scope.editAllowanceModel.pay_socso_eis == 'N'){
			$scope.editAllowanceModel.pay_socso_eis = 'Y';
		}else{
			$scope.editAllowanceModel.pay_socso_eis = 'N';
		}
	}

	$scope.toggleTaxAllowanceEdit = function(){
		if($scope.editAllowanceModel.pay_tax == 'N'){
			$scope.editAllowanceModel.pay_tax = 'Y';
		}else{
			$scope.editAllowanceModel.pay_tax = 'N';
		}
	}

	$scope.toggleHrdfAllowanceEdit = function(){
		if($scope.editAllowanceModel.pay_hrdf == 'N'){
			$scope.editAllowanceModel.pay_hrdf = 'Y';
		}else{
			$scope.editAllowanceModel.pay_hrdf = 'N';
		}
	}

	$scope.toggleEpfDeductionAdd = function(){
		if($scope.addDeductionModel.pay_epf == 'N'){
			$scope.addDeductionModel.pay_epf = 'Y';
		}else{
			$scope.addDeductionModel.pay_epf = 'N';
		}
	}

	$scope.toggleSocsoDeductionAdd = function(){
		if($scope.addDeductionModel.pay_socso_eis == 'N'){
			$scope.addDeductionModel.pay_socso_eis = 'Y';
		}else{
			$scope.addDeductionModel.pay_socso_eis = 'N';
		}
	}

	$scope.toggleTaxDeductionAdd = function(){
		if($scope.addDeductionModel.pay_tax == 'N'){
			$scope.addDeductionModel.pay_tax = 'Y';
		}else{
			$scope.addDeductionModel.pay_tax = 'N';
		}
	}

	$scope.toggleHrdfDeductionAdd = function(){
		if($scope.addDeductionModel.pay_hrdf == 'N'){
			$scope.addDeductionModel.pay_hrdf = 'Y';
		}else{
			$scope.addDeductionModel.pay_hrdf = 'N';
		}
	}

	$scope.toggleEpfDeductionEdit = function(){
		if($scope.editDeductionModel.pay_epf == 'N'){
			$scope.editDeductionModel.pay_epf = 'Y';
		}else{
			$scope.editDeductionModel.pay_epf = 'N';
		}
	}

	$scope.toggleSocsoDeductionEdit = function(){
		if($scope.editDeductionModel.pay_socso_eis == 'N'){
			$scope.editDeductionModel.pay_socso_eis = 'Y';
		}else{
			$scope.editDeductionModel.pay_socso_eis = 'N';
		}
	}

	$scope.toggleTaxDeductionEdit = function(){
		if($scope.editDeductionModel.pay_tax == 'N'){
			$scope.editDeductionModel.pay_tax = 'Y';
		}else{
			$scope.editDeductionModel.pay_tax = 'N';
		}
	}

	$scope.toggleHrdfDeductionEdit = function(){
		if($scope.editDeductionModel.pay_hrdf == 'N'){
			$scope.editDeductionModel.pay_hrdf = 'Y';
		}else{
			$scope.editDeductionModel.pay_hrdf = 'N';
		}
	}

	$scope.banksDone = function(skip = false){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'invocore_payroll/banksDone', {company_id : $scope.company_id}, config).then(function (response) {
			if(skip){
				showNotification("Success",'Bank Accounts step skipped successfully!',"success");
			}else{
				showNotification("Success",'Bank Accounts step done successfully!',"success");
			}
			$scope.steps_done = 2;
			$scope.current_step = 3;
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.importDone = function(){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'invocore_payroll/importDone', {company_id : $scope.company_id}, config).then(function (response) {
			showNotification("Success",'Import Employees step done successfully!',"success");
			$scope.steps_done = 5;
			$scope.current_step = 6;
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.allowancesDone = function(skip = false){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'invocore_payroll/allowancesDone', {company_id : $scope.company_id}, config).then(function (response) {
			if(skip){
				showNotification("Success",'Allowances step skipped successfully!',"success");
			}else{
				showNotification("Success",'Allowances step done successfully!',"success");
			}
			$scope.steps_done = 3;
			$scope.current_step = 4;
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.deductionsDone = function(skip = false){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'invocore_payroll/deductionsDone', {company_id : $scope.company_id}, config).then(function (response) {
			if(skip){
				showNotification("Success",'Deductions step skipped successfully!',"success");
			}else{
				showNotification("Success",'Deductions step done successfully!',"success");
			}
			$scope.steps_done = 4;
			$scope.current_step = 5;
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.editBank = function(bank){
		$scope.editBankModel = angular.copy(bank);
		$('.edit-bank_id-select2').val(bank.bank_id).trigger('change.select2');
		$('.edit-state_id-select2').val(bank.state_id).trigger('change.select2');
	}

	$scope.editAllowance = function(index){
		angular.forEach($scope.allowances, function(value){
			value.can_edit = "no";
		});
		$scope.allowances[index].can_edit = "yes";
	}

	$scope.editDeduction = function(index){
		angular.forEach($scope.deductions, function(value){
			value.can_edit = "no";
		});
		$scope.deductions[index].can_edit = "yes";
	}

	$scope.deleteBank = function(bank){
		$scope.bank_delete_model = {id : bank.id, company_id : bank.company_id}
	}

	$scope.deleteAllowance = function(index){
		if($scope.allowances[index].id != null){
			$scope.deletedAllowanceIds.push($scope.allowances[index].id);
		}
		$scope.allowances.splice(index, 1);
	}

	$scope.deleteDeduction = function(index){
		if($scope.deductions[index].id != null){
			$scope.deletedDeductionIds.push($scope.deductions[index].id);
		}
		$scope.deductions.splice(index, 1);
	}

	$scope.deleteBankConfirmed = function(){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'invocore_payroll/deleteBank', $scope.bank_delete_model, config).then(function (response) {
			$scope.company_banks = response.data.company_banks;
			showNotification("Success",'Bank deleted successfully!',"success");
			$('#deleteBank').modal('toggle');
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.deleteAllowanceConfirmed = function(){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'invocore_payroll/deleteAllowance', $scope.allowance_delete_model, config).then(function (response) {
			$scope.allowances = response.data.allowances;
			showNotification("Success",'Allowance deleted successfully!',"success");
			$('#deleteAllowance').modal('toggle');
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.deleteDeductionConfirmed = function(){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'invocore_payroll/deleteDeduction', $scope.deduction_delete_model, config).then(function (response) {
			$scope.deductions = response.data.deductions;
			showNotification("Success",'Deduction deleted successfully!',"success");
			$('#deleteDeduction').modal('toggle');
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.updateBank = function(valid){
		if(!valid){
			showNotification("Error",'Please fill in all required fields!',"error");
		}else{
			$('body').LoadingOverlay("show",{maxSize:50});
			$http.post(base_url + 'invocore_payroll/updateBank', $scope.editBankModel, config).then(function (response) {
				$scope.company_banks = response.data.company_banks;
				showNotification("Success",'Bank details updated successfully!',"success");
				$('#editBank').modal('toggle');
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}
	}

	$scope.createBank = function(valid){
		if(!valid){
			showNotification("Error",'Please fill in all required fields!',"error");
		}else{
			$scope.addBankModel.company_id = $scope.company_id;
			$('body').LoadingOverlay("show",{maxSize:50});
			$http.post(base_url + 'invocore_payroll/createBank', $scope.addBankModel, config).then(function (response) {
				$scope.company_banks = response.data.company_banks;
				$scope.addBankModel = {
					bank_id : '',
					account_no : '',
					state_id : '',
					is_main : 'N'
				}
				$('.add-bank-select2').val('').trigger('change.select2');
				showNotification("Success",'New bank added successfully!',"success");
				$('#addBank').modal('toggle');
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}
	}

	$scope.createAllowance = function(valid){
		if(!valid){
			showNotification("Error",'Please fill in all required fields!',"error");
		}else{
			$('body').LoadingOverlay("show",{maxSize:50});
			date1 = $('#datetimepicker1').data("DateTimePicker").date();
			date2 = $('#datetimepicker2').data("DateTimePicker").date();
			if(date1 == null){
				$scope.addAllowanceModel.start_period = ''
			}else{
				$scope.addAllowanceModel.start_period = date1.format('YYYY-MM-DD');
			}
			if(date2 == null){
				$scope.addAllowanceModel.end_period = ''
			}else{
				$scope.addAllowanceModel.end_period = date2.format('YYYY-MM-DD');
			}
			$scope.addAllowanceModel.company_id = $scope.company_id;
			$http.post(base_url + 'invocore_payroll/createAllowance', $scope.addAllowanceModel, config).then(function (response) {
				$scope.allowances = response.data.allowances;
				$scope.addAllowanceModel = {
					code : '',
					description : '',
					start_period : '',
					end_period : '',
					pay_epf : 'Y',
					pay_socso_eis : 'Y',
					pay_tax : 'Y',
					pay_hrdf : 'Y',
					tax_rule_id : ''
				}
				$('#datetimepicker1').data("DateTimePicker").clear()
				$('#datetimepicker2').data("DateTimePicker").clear()
				$('.tax-rule-add').val('').trigger('change.select2');
				showNotification("Success",'New allowance added successfully!',"success");
				$('#addAllowance').modal('toggle');
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}
	}

	$scope.createDeduction = function(valid){
		if(!valid){
			showNotification("Error",'Please fill in all required fields!',"error");
		}else{
			$('body').LoadingOverlay("show",{maxSize:50});
			date1 = $('#datetimepicker5').data("DateTimePicker").date();
			date2 = $('#datetimepicker6').data("DateTimePicker").date();
			if(date1 == null){
				$scope.addDeductionModel.start_period = ''
			}else{
				$scope.addDeductionModel.start_period = date1.format('YYYY-MM-DD');
			}
			if(date2 == null){
				$scope.addDeductionModel.end_period = ''
			}else{
				$scope.addDeductionModel.end_period = date2.format('YYYY-MM-DD');
			}
			$scope.addDeductionModel.company_id = $scope.company_id;
			$http.post(base_url + 'invocore_payroll/createDeduction', $scope.addDeductionModel, config).then(function (response) {
				$scope.deductions = response.data.deductions;
				$scope.addDeductionModel = {
					code : '',
					description : '',
					start_period : '',
					end_period : '',
					pay_epf : 'Y',
					pay_socso_eis : 'Y',
					pay_tax : 'Y',
					pay_hrdf : 'Y'
				}
				$('#datetimepicker5').data("DateTimePicker").clear()
				$('#datetimepicker6').data("DateTimePicker").clear()
				$('.tax-rule-add').val('').trigger('change.select2');
				showNotification("Success",'New deduction added successfully!',"success");
				$('#addDeduction').modal('toggle');
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}
	}

	$scope.updateAllowance = function(valid){
		if(!valid){
			showNotification("Error",'Please fill in all required fields!',"error");
		}else{
			$('body').LoadingOverlay("show",{maxSize:50});
			date1 = $('#datetimepicker3').data("DateTimePicker").date();
			date2 = $('#datetimepicker4').data("DateTimePicker").date();
			if(date1 == null){
				$scope.editAllowanceModel.start_period = ''
			}else{
				$scope.editAllowanceModel.start_period = date1.format('YYYY-MM-DD');
			}
			if(date2 == null){
				$scope.editAllowanceModel.end_period = ''
			}else{
				$scope.editAllowanceModel.end_period = date2.format('YYYY-MM-DD');
			}
			$http.post(base_url + 'invocore_payroll/updateAllowance', $scope.editAllowanceModel, config).then(function (response) {
				$scope.allowances = response.data.allowances;
				showNotification("Success",'Allowance details updated successfully!',"success");
				$('#editAllowance').modal('toggle');
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}
	}

	$scope.updateDeduction = function(valid){
		if(!valid){
			showNotification("Error",'Please fill in all required fields!',"error");
		}else{
			$('body').LoadingOverlay("show",{maxSize:50});
			date1 = $('#datetimepicker7').data("DateTimePicker").date();
			date2 = $('#datetimepicker8').data("DateTimePicker").date();
			if(date1 == null){
				$scope.editDeductionModel.start_period = ''
			}else{
				$scope.editDeductionModel.start_period = date1.format('YYYY-MM-DD');
			}
			if(date2 == null){
				$scope.editDeductionModel.end_period = ''
			}else{
				$scope.editDeductionModel.end_period = date2.format('YYYY-MM-DD');
			}
			$http.post(base_url + 'invocore_payroll/updateDeduction', $scope.editDeductionModel, config).then(function (response) {
				$scope.deductions = response.data.deductions;
				showNotification("Success",'Deduction details updated successfully!',"success");
				$('#editDeduction').modal('toggle');
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}
	}

	$scope.toggleMainAdd = function(){
		if($scope.addBankModel.is_main == 'N'){
			$scope.addBankModel.is_main = 'Y';
		}else if($scope.addBankModel.is_main == 'Y'){
			$scope.addBankModel.is_main = 'N';
		}
	}

	$scope.toggleMainEdit = function(){
		if($scope.editBankModel.is_main == 'N'){
			$scope.editBankModel.is_main = 'Y';
		}else if($scope.editBankModel.is_main == 'Y'){
			$scope.editBankModel.is_main = 'N';
		}
	}

	$scope.saveProfile = function(valid){
		if(!valid){
			showNotification("Error",'Please fill in all required fields!',"error");
		}else{
			$scope.profile.steps_done = $scope.steps_done;
			$scope.profile.current_step = $scope.current_step;
			$scope.profile.company_id = $scope.company_id;
			$('body').LoadingOverlay("show",{maxSize:50});
			$http.post(base_url + 'invocore_payroll/save_profile', $scope.profile, config).then(function (response) {
				$scope.steps_done = response.data.steps_done;
				$scope.current_step = 2;
				showNotification("Success",'Company Profile updated successfully!',"success");
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}
	}

	$scope.toggleCheck = function(scope_value, index, type){
		if(type == 'tax'){
			if(scope_value[index].pay_tax == 'N'){
				scope_value[index].pay_tax = 'Y';
			}else{
				scope_value[index].pay_tax = 'N';
			}
		}else if(type == 'epf'){
			if(scope_value[index].pay_epf == 'N'){
				scope_value[index].pay_epf = 'Y';
			}else{
				scope_value[index].pay_epf = 'N';
			}
		}else if(type == 'socso'){
			if(scope_value[index].pay_socso == 'N'){
				scope_value[index].pay_socso = 'Y';
			}else{
				scope_value[index].pay_socso = 'N';
			}
		}else if(type == 'eis'){
			if(scope_value[index].pay_eis == 'N'){
				scope_value[index].pay_eis = 'Y';
			}else{
				scope_value[index].pay_eis = 'N';
			}
		}else if(type == 'eligible_salary'){
			if(scope_value[index].eligible_salary == 'N'){
				scope_value[index].eligible_salary = 'Y';
			}else{
				scope_value[index].eligible_salary = 'N';
			}
		}
	}

	$scope.getData = function(){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'invocore_payroll/get_data', '', config).then(function (response) {
			$scope.show_wizard = true;
			$scope.countries = response.data.countries;
			$scope.all_states = response.data.states;
			$scope.profile = response.data.profile;
			$scope.profile.hrdf_percentage = parseFloat($scope.profile.hrdf_percentage);
			$scope.profile.epf_percentage = parseFloat($scope.profile.epf_percentage);
			$scope.steps_done = response.data.steps_done;
			$scope.current_step = response.data.current_step;
			$scope.company_id = response.data.company_id;
			$scope.malaysia_banks = response.data.malaysia_banks;
			$scope.malaysia_states = response.data.malaysia_states;
			$scope.company_banks = response.data.company_banks;
			$scope.tax_rules = response.data.tax_rules;
			$scope.allowances = response.data.allowances;
			$scope.deductions = response.data.deductions;
			$scope.calendar = response.data.calendar;
			$scope.getStates(true);
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.addNewAllowance = function(){
		angular.forEach($scope.allowances, function(value){
			value.can_edit = "no";
		});
		new_allowance = {
			id : null,
			allowance_name : '',
			pay_tax : 'N',
			pay_epf : 'N',
			eligible_salary : 'N',
			pay_eis : 'N',
			pay_socso : 'N',
			can_edit : "yes"
		}
		$scope.allowances.unshift(new_allowance);
	}

	$scope.addNewDeduction = function(){
		angular.forEach($scope.deductions, function(value){
			value.can_edit = "no";
		});
		new_deduction = {
			id : null,
			deduction_name : '',
			pay_tax : 'N',
			pay_epf : 'N',
			pay_hrdf : 'N',
			pay_eis : 'N',
			pay_socso : 'N',
			can_edit : "yes"
		}
		$scope.deductions.unshift(new_deduction);
	}


	$scope.saveAllowances = function(go_to_next = true){
		valid = true;
		angular.forEach($scope.allowances, function(value){
			value.can_edit = "no";
		});
		for(i = 0; i < $scope.allowances.length; i++){
			value = $scope.allowances[i];
			if(value.allowance_name == ""){
				valid = false;
				value.can_edit = "yes";
				break;
			}
		};

		if(valid){
			$('body').LoadingOverlay("show",{maxSize:50});
			$http.post(base_url + 'invocore_payroll/saveAllowances', {allowances: $scope.allowances,
			steps_done: $scope.steps_done, company_id: $scope.company_id,
			deleted_ids: $scope.deletedAllowanceIds}, config).then(function (response) {
				$scope.allowances = response.data.allowances;
				$scope.steps_done = response.data.steps_done;
				if(go_to_next){
					$scope.current_step = 4;
				}
				$scope.deletedAllowanceIds = [];
				showNotification("Success",'Allowances updated successfully',"success");
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}else{
			showNotification("Error",'Allowance Name can not be empty!',"error");
		}
	}

	$scope.saveDeductions = function(go_to_next = true){
		valid = true;
		angular.forEach($scope.deductions, function(value){
			value.can_edit = "no";
		});
		for(i = 0; i < $scope.deductions.length; i++){
			value = $scope.deductions[i];
			if(value.deduction_name == ""){
				valid = false;
				value.can_edit = "yes";
				break;
			}
		};

		if(valid){
			$('body').LoadingOverlay("show",{maxSize:50});
			$http.post(base_url + 'invocore_payroll/saveDeductions', {deductions: $scope.deductions,
			steps_done: $scope.steps_done, company_id: $scope.company_id,
			deleted_ids: $scope.deletedDeductionIds}, config).then(function (response) {
				$scope.deductions = response.data.deductions;
				$scope.steps_done = response.data.steps_done;
				if(go_to_next){
					$scope.current_step = 5;
				}
				$scope.deletedDeductionIds = [];
				showNotification("Success",'Deductions updated successfully',"success");
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}else{
			showNotification("Error",'Deduction Name can not be empty!',"error");
		}
	}

	$scope.step_changed = function(step){
		$scope.current_step = step;
	}

	$scope.back_step = function(){
		$scope.current_step--;
	}

	$scope.inc_step = function(){
		$scope.current_step++;
	}

	$scope.getStates = function(db = false){
		$scope.states = [];
		if(!db){
			$scope.profile.state_id = '';
		}
		if($scope.profile.country_id != undefined && $scope.profile.country_id != ''){
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.states = $scope.all_states.filter(obj => {
				return obj.country_id == $scope.profile.country_id;
			});
			$('body').LoadingOverlay("hide");
		}
	}

	$scope.validatePercentage = function(){
		if($scope.profile.hrdf_percentage < 0){
			$scope.profile.hrdf_percentage = 0;
		}else if($scope.profile.hrdf_percentage > 100){
			$scope.profile.hrdf_percentage = 100;
		}
	}

	$scope.saveCalendarSetting = function(){
		$('body').LoadingOverlay("show",{maxSize:50});
		$scope.calendar.steps_done = $scope.steps_done;
		$scope.calendar.current_step = $scope.current_step;
		$scope.calendar.company_id = $scope.company_id
		$http.post(base_url + 'invocore_payroll/save_calendar_setting', $scope.calendar, config).then(function (response) {
			$scope.steps_done = response.data.steps_done;
			$scope.current_step = 6;
			showNotification("Success",'Calendar Setting updated successfully!',"success");
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

});

$(function () {
	$('.date').datetimepicker({
		viewMode: 'years',
		format: 'MMMM YYYY'
	});
});


function validatedate(dateText) {
	if (dateText) {
		try {
			var errorMessage = "";   

			var splitComponents = dateText.split('/').join('-').split('-');

			if (splitComponents.length = 3) {
				var day = parseInt(splitComponents[0]);
				var month = parseInt(splitComponents[1]);
				var year = parseInt(splitComponents[2]);

				if (isNaN(day) || isNaN(month) || isNaN(year)) {
					errorMessage = "The day, month and year need to be numbers";
					return false;
				}

				if (day <= 0 || month <= 0 || year <= 1900) {
					errorMessage = "The day, month and year need to be positive values greater than 0";
				}

				if (month > 12) {
					errorMessage = "The month cannot be greater than 12.";
				}

				if (errorMessage == "") {
                                  // assuming no leap year by default
                                  var daysPerMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
                                  if (year % 4 == 0) {
                                      // current year is a leap year
                                      daysPerMonth[1] = 29;
                                  }

                                  if (day > daysPerMonth[month - 1]) {
                                  	errorMessage = "Number of days are more than those allowed for the month";
                                  }
                              }
                          } else {
                          	errorMessage = "Please enter the date in dd-mm-yyyy format";
                          }

                          if (errorMessage) {
                          	return false;
                          }
                      } catch (e) {
                      	return false;
                      }
                  }

                  return true;
              }

              function validateCSV(data,fieldsToValidate){

              	var validation_errors = [];
              	var fields = fieldsToValidate.split(",");

              	$.each(data, function(i, emp) {
              		$.each(fields, function(j, f){
                              // Check if dob is in correct format
                              // if dob exists check format/
                              // dob is not required field
                              if(f == 'dob' || f == 'hired_on' || f == 'license_expiry') {
                              	if(f != ''){
                              		const is_valid = validatedate(emp[f]);
                              		if (!is_valid) {
                              			validation_errors.push({row:i+1, error:f + ' date is not valid'});
                              		} 
                              	}
                              }
                              else if(!emp[f]){
                              	validation_errors.push({row:i+1,error:f + ' column is not valid'});
                              }                               
                          });

              	});

              	console.log(validation_errors);

              	return validation_errors;
              }

                    function tableGenerator(selector, data) { // data is an array
                      var keys = Object.keys(Object.assign({}, ...data));// Get the keys to make the header
                      // Add header
                      //var table = '<table>';
                      //selector.append(table);

                      var table = jQuery('<table/>', {class: 'table'});

                      //console.log(table);

                      selector.append(table);



                      var head = '<thead><tr>';
                      keys.forEach(function(key) {
                      	head += '<th><b>'+key+'</b></th>';
                      });
                      table.append(head+'</tr></thead>');
                      // Add body
                      var body = '<tbody>';

                      console.log(data);
                      data.forEach(function(obj) { // For each row
                      	var row = '<tr>';
                        keys.forEach(function(key) { // For each column
                        	row += '<td>';
                          if (obj.hasOwnProperty(key)) { // If the obj doesnt has a certain key, add a blank space.
                          	row += obj[key];
                          }
                          row += '</td>';
                      });
                        body += row+'</tr>';
                    })

                      table.append(body+'</tbody>');
                  }

                  $(document).ready(function(){

                  	$(".btn-invalid" ).click(function() {

                  		$(this).next().slideToggle();

                  	});

                  	$("input[type=file]").change(function(evt) {

                  		var obj = $(this);

                  		obj.siblings(".btn-import").hide();
                  		obj.siblings(".btn-invalid").hide();
                  		obj.siblings(".msg").html("");
                  		obj.siblings(".collapse").html("");

                  		if(evt.target.files.length > 0){
                  			var file = evt.target.files[0];
                  			var data_file = $(this).attr("data-file");

                  			Papa.parse(file, {
                  				header: true,
                  				dynamicTyping: false,
                  				skipEmptyLines: true,
                  				complete: function(results) {
                  					console.log(results);


					        		//obj.siblings(".msg").html("Invalid records found");

					        		var import_base_url = js_base_url;
					        		var url = '';
					        		var _fields_to_validate = '';




					        		if(data_file == "basic-info"){
					        			url = import_base_url + 'invocore_payroll/import_basic_info'
					        			_fields_to_validate = 'employee_id,full_name,department,position,role,outlet,sex,dob,hired_on,license_expiry'

					        		}
					        		if(data_file == "allowances"){
					        			url = import_base_url + 'import/import_allowances'
					        			_fields_to_validate = 'employee_id,allowance_name,amount'

					        		}

					        		if(data_file == "incentives"){
					        			url = import_base_url +'import/import_incentives'
					        			_fields_to_validate = 'employee_id,incentive_name,amount'

					        		}

					        		if(data_file == "emergency_contacts"){
					        			url = import_base_url + 'import/import_emergency_contacts'
					        			_fields_to_validate = 'employee_id,first_name,relation'
					        		}

					        		if(data_file == "family_members"){
					        			url = import_base_url +'import/import_family_members'
					        			_fields_to_validate = 'employee_id,first_name,relation'
					        		}

					        		if(data_file == "qualifications"){
					        			url = import_base_url +'import/import_qualifications'
					        			_fields_to_validate = 'employee_id,institution,country,course_field,period_from,period_to,highest_qualification_attained'
					        		}

					        		if(data_file == "languages"){
					        			url = import_base_url +'import/import_languages'
					        			_fields_to_validate = 'employee_id,language,writing_skills,speaking_skill'
					        		}

					        		if(data_file == "skills"){
					        			url = import_base_url +'import/import_skills'
					        			_fields_to_validate = 'employee_id,skill,level'
					        		}

					        		if(data_file == "employment_history"){
					        			url = import_base_url +'import/import_employment_history'
					        			_fields_to_validate = 'employee_id,company_name,period_from,period_to'
					        		}

					        		if(data_file == "manual_clockings"){
					        			url = import_base_url +'import/import_clockings'
					        			_fields_to_validate = 'employee_id,device_mac_address,clock_in,clock_out'
					        		}

					        		if(data_file == "manual_clockings_new"){

					        			url = import_base_url +'import/import_clockings_new'
					        			_fields_to_validate = 'device_serial,no,employee_id,mode,type,datetime'
					        		}

                      // Filter those rows whose employee_id and first_name are empty
                      var filteredData = results.data.filter(elem => { 
                      	if(elem.employee_id == '' && elem.full_name == '')
                      		return false;
                      	return true;
                      });

                      var validation_errors = validateCSV(filteredData,_fields_to_validate);

                      if(validation_errors.length == 0){
                      	obj.siblings(".msg").html("");
                      	obj.siblings(".btn-import").show();
                      	obj.siblings(".btn-invalid").hide();
                      	obj.siblings(".collapse").html("");
                      	obj.siblings(".collapse").hide();

                      }else{
                      	obj.val('');
                      	obj.siblings(".msg").html("Invalid data found in CSV");
                      	obj.siblings(".btn-import").hide();
                      	obj.siblings(".btn-invalid").show();
                      	tableGenerator(obj.siblings(".collapse"),validation_errors)
                      }


                      obj.siblings(".btn-import" ).off("click");

                      obj.siblings(".btn-import" ).click(function() {
                      	obj.parent().LoadingOverlay("show");

                      	console.log("test");
                      	console.log(results.data);


									  	$.ajax({ //Process the form using $.ajax()
								            type      : 'POST', //Method type
								            url       : url, //Your form processing file URL
								            data      : {'json':filteredData}, //Forms name
								            dataType  : 'json',
								            success   : function(data) {
								            	obj.parent().LoadingOverlay("hide");
								            	console.log(data);

								            	obj.siblings(".msg").html(data.msg);

								            	if(data.insert_failed == 0){
								            		obj.val('');
                                                                //alert("Data imported successfully");
                                                                obj.siblings(".btn-import").hide();
                                                                obj.siblings(".btn-invalid").hide();
                                                                
                                                                //obj.siblings(".collapse").html("");
                                                            }
                                                            else{
                                                            	obj.val('');
                                                            	obj.siblings(".btn-import").hide();
                                                            	obj.siblings(".btn-invalid").show();
                                                                //obj.siblings(".collapse").html('<pre>'+data.rows_error+'</pre>');

                                                                tableGenerator(obj.siblings(".collapse"),JSON.parse(data.rows_error));

                                                                //obj.siblings(".collapse").jsonViewer(data.rows_error);
                                                                //$('#json-renderer').jsonViewer(data.rows_error);

                                                            }
                                                        }
                                                    });


									  });


                  }
              });
}
else{

}

});




});