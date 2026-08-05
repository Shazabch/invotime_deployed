

var config = {
	headers: {
		'Content-Type': 'application/json;charset=utf-8;'
	}
}; 
function roundToTwo(num) {
    return +(Math.round(num + "e+2")  + "e-2");
}
function multiply(a, b) {
    var commonMultiplier = 1000000;

    a *= commonMultiplier;
    b *= commonMultiplier;

    return (a * b) / (commonMultiplier * commonMultiplier);
};
var app = angular.module('myApp', ['ui.bootstrap']);
app.directive('contenteditable', function() {
	return {
		require: 'ngModel',
		link: function(scope, elm, attrs, ctrl) {
                // view -> model
                elm.bind('blur keyup change', function() {
                	scope.$apply(function() {
                		ctrl.$setViewValue(elm.html());
                	});
                });

                // model -> view
                ctrl.$render = function() {
                	elm.html(ctrl.$viewValue);
                };

                // load init value from DOM
                //ctrl.$setViewValue(elm.html());
            }
        };
    });
app.controller('payrollCtrl', function($scope,$http) {
	$scope.payroll_processes = [];
	$scope.departments = [];
	$scope.employees = [];
	$scope.process = '';
	$scope.department = '';
	$scope.employee = '';
	$scope.payroll = [];
	$scope.epf_template = "epf_template.html";

	$scope.customize_epf = function(per){
		if($scope.payroll.deductions[0].epf_percentage == -1){
			$scope.payroll.deductions[0].epf_percentage = 0;
			per = 0;
		}
		$scope.payroll.deductions[0].amount = ($scope.payroll.epf_total * per) / 100;
		$scope.calculate(false, false);
	}

	$scope.getData = function(employee_id,process_id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/getdata', '', config).then(function (response) {
			$scope.payroll_processes = response.data.payroll_processes;
			$scope.departments = response.data.departments;
			
			$scope.epf_m_table = response.data.epf_m_table;
			$scope.epf_n_table = response.data.epf_n_table;
			$scope.epf_c_table = response.data.epf_c_table;
			$scope.epf_d_table = response.data.epf_d_table;
			$scope.epf_e_table = response.data.epf_e_table;
			$scope.socso_table = response.data.socso_table;
			$scope.eis_table = response.data.eis_table;
			$scope.epf_nine_table = response.data.epf_nine_table;

			if(employee_id != '' && process_id != ''){

				$scope.employee = employee_id;
				$scope.process = process_id;
				$scope.filterEmployees(employee_id);
				$scope.getPayroll($scope.process, $scope.department, $scope.employee);
				
			}
			
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});

	}

	

	$scope.filterEmployees = function(employee_id = ''){
		$('body').LoadingOverlay("show",{maxSize:50});
		if($scope.process == undefined || $scope.process == ''){
			$scope.employees = [];
			$scope.employee = '';
			$('body').LoadingOverlay("hide");
		}else{
			$http.post(base_url + 'payroll/filterEmployees', {process:$scope.process,department:$scope.department}, config).then(function (response) {
				$scope.employees = response.data.employees;
				if(response.data.employees.length > 0){
					$scope.employee = employee_id != '' ? employee_id : response.data.employees[0].id;	
				}else{
					$scope.employee = '';
				}
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}


		
	}

	$scope.getPayroll = function(process, dep, emp, msg = '', type = 'success'){
		var heading = "Success";
		if(type == 'error'){
			heading = "Error";
		}
		if(emp == ''){
			showNotification("Error",'No employee selected',"error");
		}else{
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.selected_process = process;
			$scope.selected_department = dep;
			var result = $scope.employees.find(obj => {
				return obj.id === emp;
			})

			if(result != undefined){
				$scope.employee = emp;
				$('.apply-select2').val(emp).trigger('change.select2');
			}
			$http.post(base_url + 'payroll/getEmployee', {process:process,department_id:dep,employee_id:emp,reset_flag:false}, config).then(function (response) {
				$scope.payroll = response.data;
				$scope.show_more_allowances = false;
				if($scope.payroll.db == "true"){
					$scope.final = true;
				}else{
					$scope.final = false;
				}

				$scope.payroll.extra_earnings = parseFloat($scope.payroll.extra_earnings);

				
				$scope.payroll.basic = parseFloat($scope.payroll.basic);
				$scope.payroll.eligible_amount = parseFloat($scope.payroll.eligible_amount);
				$scope.payroll.daily = parseFloat($scope.payroll.daily);
				$scope.payroll.unit = parseFloat($scope.payroll.unit);
				$scope.payroll.basic_amount = parseFloat($scope.payroll.basic_amount);
				$scope.payroll.total_allowance = 0;
				angular.forEach($scope.payroll.allowances, function(value){
					value.amount = parseFloat(value.amount);
					$scope.payroll.total_allowance += value.amount;
				});
				$scope.payroll.total_deductions = 0;
				$scope.payroll.no_time_pay_off = 0;
				angular.forEach($scope.payroll.deductions, function(value){
					deduct = 0;
					value.amount = parseFloat(value.amount);
					if(value.percentage == "false" && value.is_apply == "true"){
						deduct = value.amount;
						if(value.name == "PCB" && value.db == "true" && $scope.payroll.deductions[3].is_apply == "false"){
							deduct = 0;
						}
					}else if(value.is_apply == "true"){
						if(value.name == "PCB" && value.db == "true"){
							deduct = $scope.payroll.tax;
						}else{
							basic_allowance = $scope.payroll.basic_amount + $scope.payroll.total_allowance;	
							deduct = basic_allowance * value.amount / 100;
						}

						
						
					}
					deduct = deduct == null ? 0 : deduct;
					if(value.fixed == 'yes'){
						$scope.payroll.no_time_pay_off += parseFloat(deduct);
					}else{
						$scope.payroll.total_deductions += parseFloat(deduct);
					}
					
				});
				$scope.payroll.total_adjustments = 0;
				angular.forEach($scope.payroll.adjustments, function(value){
					adjustment = 0;
					value.amount = value.amount;

					if(value.percentage == "false" && value.is_apply == "true"){
						adjustment = value.amount;
					}else if(value.is_apply == "true"){
						basic_allowance = $scope.payroll.basic_amount + $scope.payroll.total_allowance;	
						adjustment = basic_allowance * value.amount / 100;
					}
					adjustment = adjustment == null ? 0 : adjustment;
					$scope.payroll.total_adjustments += parseFloat(adjustment);
				});
				$scope.payroll.total_allowance = parseFloat($scope.payroll.total_allowance);
				$scope.payroll.net_pay = $scope.payroll.basic_amount + $scope.payroll.total_allowance - $scope.payroll.total_deductions - $scope.payroll.total_adjustments;
				$scope.calculate($scope.payroll.db);
				$('body').LoadingOverlay("hide");
				if(msg != ''){
					showNotification(heading,msg,type);
				}
			}, function (error) {
				console.log(error.data);
			});
		}
	}

	$scope.calculate = function(db = false, change_overtimes = true, change_eligible = false){
		if($scope.payroll.salary_type == 'daily'){
			$scope.payroll.basic2 = $scope.payroll.daily == null ? 0 : $scope.payroll.daily;
		}else{
			$scope.payroll.basic2 = $scope.payroll.basic;
		}

		if($scope.payroll.basic == null){
			$scope.payroll.basic2 = 0;
		}
		if(change_eligible && !db){
			if($scope.payroll.salary_type == 'daily'){
				// $scope.payroll.basic_amount = $scope.payroll.daily * $scope.payroll.unit;
				$scope.payroll.eligible_amount = roundToTwo($scope.payroll.daily * $scope.payroll.unit);
			}else{
				// $scope.payroll.basic_amount = $scope.payroll.basic * $scope.payroll.unit;
				$scope.payroll.eligible_amount = roundToTwo($scope.payroll.basic * $scope.payroll.unit);
			}
		}
		

		$scope.payroll.total_eligible_amount = $scope.payroll.basic;
		$scope.payroll.basic_amount = $scope.payroll.eligible_amount;

		$scope.payroll.basic_amount_for_month = $scope.payroll.basic;
		

		$scope.payroll.epf_total = $scope.payroll.eligible_amount;
		$scope.payroll.eis_total = $scope.payroll.eligible_amount;
		$scope.payroll.socso_total = $scope.payroll.eligible_amount;
		$scope.payroll.tax_total = 0;
		if($scope.payroll.deductions[3].is_apply == "true"){
			$scope.payroll.tax_total = $scope.payroll.eligible_amount;
		}

		$scope.payroll.total_allowance = 0;
		$scope.payroll.extra_earnings = 0;

		angular.forEach($scope.payroll.earnings, function(value){
			value.total = value.num * value.rate;
			$scope.payroll.extra_earnings += value.total;
			if(value.epf == "true"){
				$scope.payroll.epf_total +=value.total;
			}
			if(value.eis == "true"){
				$scope.payroll.eis_total +=value.total;
			}
			if(value.socso == "true"){
				$scope.payroll.socso_total +=value.total;
			}
			if(value.tax == "true" && $scope.payroll.deductions[3].is_apply == "true"){
				$scope.payroll.tax_total +=value.total;
			}
		});

		angular.forEach($scope.payroll.deductions, function(value){
			if(value.epf == "true"){
				$scope.payroll.epf_total -=value.amount;
			}
			if(value.eis == "true"){
				$scope.payroll.eis_total -=value.amount;
			}
			if(value.socso == "true"){
				$scope.payroll.socso_total -=value.amount;
			}
		});

		$scope.payroll.basic_amount += $scope.payroll.extra_earnings;

		angular.forEach($scope.payroll.allowances, function(value){
			if(value.eligible_salary == "true"){
				$scope.payroll.total_eligible_amount += value.amount;
			}
		});

		$scope.payroll.total_eligible_amount = roundToTwo($scope.payroll.total_eligible_amount);

		
		if(change_overtimes){
			// overtime rates
			var rate_hour = roundToTwo($scope.payroll.total_eligible_amount / 26);
			rate_hour = roundToTwo(rate_hour / 8);
			$scope.payroll.rate_hour = rate_hour;
			$scope.payroll.rate_day_worked = roundToTwo($scope.payroll.total_eligible_amount / 26);
			// no time pay off rates
			$scope.payroll.rate_day = roundToTwo($scope.payroll.basic_amount_for_month / $scope.payroll.month_days);
			var rate_hour_late = roundToTwo($scope.payroll.basic_amount_for_month / $scope.payroll.month_days);
			rate_hour_late = roundToTwo(rate_hour_late / 8);
			$scope.payroll.rate_hour_late = rate_hour_late;
		}

		angular.forEach($scope.payroll.allowances, function(value){
			if(change_overtimes){
				if(value.type2 == "per_hour" && !db){
					value.amount = roundToTwo(multiply(value.value, roundToTwo(multiply($scope.payroll.rate_hour , value.multiplier))));
				}else if(value.type2 == "per_day_worked" && !db){
					value.amount = roundToTwo(multiply(value.value , roundToTwo(multiply($scope.payroll.rate_day_worked, value.multiplier))));
				}
			}
			
			$scope.payroll.total_allowance += value.amount;
			if(value.epf == "true"){
				$scope.payroll.epf_total +=value.amount;
			}
			if(value.eis == "true"){
				$scope.payroll.eis_total +=value.amount;
			}
			if(value.socso == "true"){
				$scope.payroll.socso_total +=value.amount;
			}
			if(value.tax == "true" && $scope.payroll.deductions[3].is_apply == "true"){
				$scope.payroll.tax_total +=value.amount;
			}
		});

		


			// console.log("epf_total : " + $scope.payroll.epf_total);
			// console.log("eis_total : " + $scope.payroll.eis_total);
			// console.log("socso_total : " + $scope.payroll.socso_total);
			// console.log("tax_total : " + $scope.payroll.tax_total);
			new_epf = 0;
			new_epf_c = 0;
			new_eis = 0;
			new_eis_c = 0;
			new_socso = 0;
			new_socso_c = 0;
			if($scope.payroll.epf_type == 'nine'){
				temp = $scope.getEPFNine($scope.payroll.epf_total);
				if(temp.length != 0){
					new_epf = temp[0].employee;
					new_epf_c = temp[0].employer;
				}
			}else {
				temp = $scope.getEPF($scope.payroll.epf_total, $scope.payroll.epf_category);
				if(temp.length != 0){
					new_epf = temp[0].employee;
					new_epf_c = temp[0].employer;
				}
			}

			temp = $scope.getEIS($scope.payroll.eis_total);
			if(temp.length != 0){
				new_eis = temp[0].employee;
				new_eis_c = temp[0].employer;
			}

			temp = $scope.getSOCSO($scope.payroll.socso_total);
			if(temp.length != 0){
				if($scope.payroll.socso_secondary){
					new_socso = 0;
					new_socso_c = temp[0].second_category;
				}else{
					new_socso = temp[0].employee;
					new_socso_c = temp[0].employer;
				}
			}
			if(db != "true"){
				$scope.payroll.deductions[0].amount = 0;
				$scope.payroll.deductions[0].epf_percentage = 0;
			}
			
			$scope.payroll.deductions[1].amount = 0;
			$scope.payroll.deductions[2].amount = 0;
			

			$scope.payroll.epf_c = 0;
			$scope.payroll.socso_c = 0;
			$scope.payroll.eis_c = 0;

			if($scope.payroll.deductions[0].is_apply == "true"){
				if(db != "true"){
					$scope.payroll.deductions[0].amount = parseFloat(new_epf);
					if($scope.payroll.epf_total == 0){
						$scope.payroll.deductions[0].epf_percentage = 0;
					}else{
						$scope.payroll.deductions[0].epf_percentage = Math.round((parseFloat(new_epf) * 100) / $scope.payroll.epf_total);
					}
				}
				
				$scope.payroll.epf_c = parseFloat(new_epf_c);

				
			}

			if($scope.payroll.deductions[1].is_apply == "true"){
				$scope.payroll.deductions[1].amount = parseFloat(new_socso);
				$scope.payroll.socso_c = parseFloat(new_socso_c);
			}

			if($scope.payroll.deductions[2].is_apply == "true"){
				$scope.payroll.deductions[2].amount = parseFloat(new_eis);
				$scope.payroll.eis_c = parseFloat(new_eis_c);
			}

			$scope.payroll.total_deductions = 0;
			$scope.payroll.no_time_pay_off = 0;
			angular.forEach($scope.payroll.deductions, function(value){
				if(change_overtimes){
					if(value.type2 == "rate_day" && !db){
						value.amount = roundToTwo(value.value * $scope.payroll.rate_day);
					}else if(value.type2 == "rate_hour_late" && !db){
						value.amount = roundToTwo(value.value * $scope.payroll.rate_hour_late);
					}
				}
				
				deduct = 0;
				if(value.percentage == "false" && value.is_apply == "true"){
					deduct = value.amount;
					if(value.name == "PCB" && value.db == "true" && $scope.payroll.deductions[3].is_apply == "false"){
						deduct = 0;
					}
				}else if(value.is_apply == "true"){
					if(value.name == "PCB" && value.db == "true"){
						basic_allowance = $scope.payroll.tax_total;
						if($scope.payroll.deductions[3].is_apply == "false"){
							basic_allowance = 0;
						}
					}else{
						basic_allowance = $scope.payroll.basic_amount + $scope.payroll.total_allowance;	
					}

					deduct = basic_allowance * value.amount / 100;
				}
				deduct = deduct == null ? 0 : deduct;
				if(value.fixed == 'yes'){
					$scope.payroll.no_time_pay_off += parseFloat(deduct);
				}else{
					$scope.payroll.total_deductions += parseFloat(deduct);
				}
			});

			$scope.payroll.total_adjustments = 0;
			angular.forEach($scope.payroll.adjustments, function(value){
				adjustment = 0;
				value.amount = value.amount;
				
				if(value.percentage == "false" && value.is_apply == "true"){
					adjustment = value.amount;
				}else if(value.is_apply == "true"){
					basic_allowance = $scope.payroll.basic_amount + $scope.payroll.total_allowance;	
					adjustment = basic_allowance * value.amount / 100;
				}
				adjustment = adjustment == null ? 0 : adjustment;
				$scope.payroll.total_adjustments += parseFloat(adjustment);
			});

			
			
			$scope.payroll.net_pay = $scope.payroll.basic_amount + $scope.payroll.total_allowance - $scope.payroll.total_deductions - $scope.payroll.total_adjustments;


		}

		$scope.reset = function(process,dep,emp){
			if(emp == ''){
			showNotification("Error",'No employee selected',"error");
		}else{
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.selected_process = process;
			$scope.selected_department = dep;
			$http.post(base_url + 'payroll/getEmployee', {process:process,department_id:dep,employee_id:emp,reset_flag:true}, config).then(function (response) {
				$scope.payroll = response.data;

				$scope.payroll.extra_earnings = parseFloat($scope.payroll.extra_earnings);
				
				$scope.payroll.basic = parseFloat($scope.payroll.basic);
				$scope.payroll.eligible_amount = parseFloat($scope.payroll.eligible_amount);
				$scope.payroll.basic_amount = parseFloat($scope.payroll.basic_amount);
				$scope.payroll.total_allowance = 0;
				angular.forEach($scope.payroll.allowances, function(value){
					value.amount = parseFloat(value.amount);
					$scope.payroll.total_allowance += value.amount;
				});
				$scope.payroll.total_deductions = 0;
				$scope.payroll.no_time_pay_off = 0;
				angular.forEach($scope.payroll.deductions, function(value){
					deduct = 0;
					value.amount = parseFloat(value.amount);
					if(value.percentage == "false" && value.is_apply == "true"){
						deduct = value.amount;
						if(value.name == "PCB" && value.db == "true" && $scope.payroll.deductions[3].is_apply == "false"){
							deduct = 0;
						}
					}else if(value.is_apply == "true"){
						if(value.name == "PCB" && value.db == "true"){
							deduct = $scope.payroll.tax;
						}else{
							basic_allowance = $scope.payroll.basic_amount + $scope.payroll.total_allowance;	
							deduct = basic_allowance * value.amount / 100;
						}

						
						
					}
					deduct = deduct == null ? 0 : deduct;
					if(value.fixed == 'yes'){
						$scope.payroll.no_time_pay_off += parseFloat(deduct);
					}else{
						$scope.payroll.total_deductions += parseFloat(deduct);
					}
				});
				$scope.payroll.total_adjustments = 0;
				angular.forEach($scope.payroll.adjustments, function(value){
					adjustment = 0;
					value.amount = value.amount;

					if(value.percentage == "false" && value.is_apply == "true"){
						adjustment = value.amount;
					}else if(value.is_apply == "true"){
						basic_allowance = $scope.payroll.basic_amount + $scope.payroll.total_allowance;	
						adjustment = basic_allowance * value.amount / 100;
					}
					adjustment = adjustment == null ? 0 : adjustment;
					$scope.payroll.total_adjustments += parseFloat(adjustment);
				});
				$scope.payroll.total_allowance = parseFloat($scope.payroll.total_allowance);
				$scope.payroll.net_pay = $scope.payroll.basic_amount + $scope.payroll.total_allowance - $scope.payroll.total_deductions - $scope.payroll.total_adjustments;
				$scope.calculate();
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}
		}

		$scope.type_change = function(){
			if($scope.payroll.salary_type == "monthly"){
				$scope.payroll.unit = 1;
				$scope.payroll.type2 = "month(s)";
				$scope.payroll.type3 = "month";
			}else if($scope.payroll.salary_type == "daily"){
				$scope.payroll.type2 = "day(s)";
				$scope.payroll.type3 = "day";
				$scope.payroll.daily = roundToTwo($scope.payroll.basic / 26);
			}else if($scope.payroll.salary_type == "hourly"){
				$scope.payroll.type2 = "hour(s)";
				$scope.payroll.type3 = "hour";
			}else if($scope.payroll.salary_type == "fortnight"){
				$scope.payroll.type2 = "fortnight(s)";
				$scope.payroll.type3 = "fortnight";
			}
			$scope.calculate(false, true, true);
		}

		$scope.getEPF = function(b, type){
			// epf_m, epf_n, epf_c, epf_d, epf_e
			var epf_table = $scope[type + "_table"];
			var result = epf_table.filter(obj => {
				return obj.start <= b && obj.end >= b;
			})
			return result;
		}

		$scope.getEPFNine = function(b){
			var result = $scope.epf_nine_table.filter(obj => {
				return obj.start <= b && obj.end >= b;
			})
			return result;
		}

		$scope.getEIS = function(b){
			var result = $scope.eis_table.filter(obj => {
				return obj.start < b && obj.end >= b;
			})
			return result;
		}

		$scope.getSOCSO = function(b){
			var result = $scope.socso_table.filter(obj => {
				return obj.start < b && obj.end >= b;
			})
			return result;
		}

		$scope.addAllowance = function(){
			var temp = {allowance_name: "Other", amount: "", db: "false", "epf": "false", "socso": "false", "eis": "false", "tax": "false","eligible_salary": "false", "template": "test_template.html", "new": "true"};
			$scope.payroll.allowances.push(temp);
		}

		$scope.removeAllowance = function(a){
			$scope.payroll.allowances.splice(a,1);
			$scope.calculate(false, false);
		}

		$scope.addNewEarning = function(){
			var temp = {num: "", unit: "", rate: "", tax: "true", epf: "true", socso: "true", eis:"true", total: 0};
			$scope.payroll.earnings.push(temp);
		}

		$scope.removeEarning = function(e){
			$scope.payroll.earnings.splice(e,1);
			$scope.calculate();
		}

		$scope.addDeduction = function(){
			var temp = {name: "Other", amount: 0, db: "false", percentage: "false", type: "not_sure", is_apply: "true", show_apply: "false"};
			$scope.payroll.deductions.push(temp);
		}

		$scope.removeDeduction = function(a){
			$scope.payroll.deductions.splice(a,1);
		}

		$scope.togglePercentage = function(i){
			if($scope.payroll.deductions[i].percentage == 'false'){
				$scope.payroll.deductions[i].percentage = 'true';
			}else{
				$scope.payroll.deductions[i].percentage = 'false';
			}
			$scope.calculate(false, false);
		}

		$scope.toggleCarry = function(i){
			if($scope.payroll.carry == 'false'){
				$scope.payroll.carry = 'true';
			}else{
				$scope.payroll.carry = 'false';
			}
		}

		$scope.toggleApply = function(i){
			if($scope.payroll.deductions[i].is_apply == 'false'){
				$scope.payroll.deductions[i].is_apply = 'true';
			}else{
				$scope.payroll.deductions[i].is_apply = 'false';
			}
			$scope.calculate(false, false);
		}

		$scope.toggleTax = function(i){
			if($scope.payroll.allowances[i].tax == 'false'){
				$scope.payroll.allowances[i].tax = 'true';
			}else{
				$scope.payroll.allowances[i].tax = 'false';
			}
			$scope.calculate(false, false);
		} 

		$scope.toggleEPF = function(i){
			if($scope.payroll.allowances[i].epf == 'false'){
				$scope.payroll.allowances[i].epf = 'true';
			}else{
				$scope.payroll.allowances[i].epf = 'false';
			}
			$scope.calculate(false, false);
		} 

		$scope.toggleSOCSO = function(i){
			if($scope.payroll.allowances[i].socso == 'false'){
				$scope.payroll.allowances[i].socso = 'true';
			}else{
				$scope.payroll.allowances[i].socso = 'false';
			}
			$scope.calculate(false, false);
		} 

		$scope.toggleEIS = function(i){
			if($scope.payroll.allowances[i].eis == 'false'){
				$scope.payroll.allowances[i].eis = 'true';
			}else{
				$scope.payroll.allowances[i].eis = 'false';
			}
			$scope.calculate(false, false);
		}

		$scope.toggleEPFDeduction = function(d){
			d.epf = (d.epf == "false") ? "true" : "false";
			$scope.calculate(false, false);
		} 

		$scope.toggleSOCSODeduction = function(d){
			d.socso = (d.socso == "false") ? "true" : "false";
			$scope.calculate(false, false);
		} 

		$scope.toggleEISDeduction = function(d){
			d.eis = (d.eis == "false") ? "true" : "false";
			$scope.calculate(false, false);
		} 


		$scope.toggleEligibleSalary = function(i){
			if($scope.payroll.allowances[i].eligible_salary == 'false'){
				$scope.payroll.allowances[i].eligible_salary = 'true';
			}else{
				$scope.payroll.allowances[i].eligible_salary = 'false';
			}
			$scope.calculate(false, true);
		} 


		$scope.toggleTaxEarning = function(e){
			if($scope.payroll.earnings[e].tax == 'false'){
				$scope.payroll.earnings[e].tax = 'true';
			}else{
				$scope.payroll.earnings[e].tax = 'false';
			}
			$scope.calculate(false, false);
		} 

		$scope.toggleEPFEarning = function(e){
			if($scope.payroll.earnings[e].epf == 'false'){
				$scope.payroll.earnings[e].epf = 'true';
			}else{
				$scope.payroll.earnings[e].epf = 'false';
			}
			$scope.calculate(false, false);
		} 

		$scope.toggleSOCSOEarning = function(e){
			if($scope.payroll.earnings[e].socso == 'false'){
				$scope.payroll.earnings[e].socso = 'true';
			}else{
				$scope.payroll.earnings[e].socso = 'false';
			}
			$scope.calculate(false, false);
		} 

		$scope.toggleEISEarning = function(e){
			if($scope.payroll.earnings[e].eis == 'false'){
				$scope.payroll.earnings[e].eis = 'true';
			}else{
				$scope.payroll.earnings[e].eis = 'false';
			}
			$scope.calculate(false, false);
		} 

		$scope.toggleShowMoreAllowances = function(){
			$scope.show_more_allowances = !$scope.show_more_allowances;
		}

		$scope.save_data = function(next = false){

			$('body').LoadingOverlay("show",{maxSize:50});
			$http.post(base_url + 'payroll/check_committed', {id: $scope.payroll.id}, config).then(function (response) {
				var is_committed = response.data.is_committed;
				if(is_committed == "Y"){
					$scope.payroll.confirm = "Y";
					$scope.payroll.edit_mode = "false";
					$scope.final = true;
					$scope.getPayroll($scope.selected_process,$scope.selected_department,$scope.payroll.employee_id,"Payroll is already committed!","error");
				}else{
					$('body').LoadingOverlay("show",{maxSize:50});
					$http.post(base_url + 'payroll/save_data', $scope.payroll, config).then(function (response) {
						if(response.data.success){							
							if(next != false){
								$scope.getPayroll($scope.selected_process,$scope.selected_department,next,response.data.msg);
							}
						}else{
							showNotification("Error",response.data.msg,"error");
						}
						if(next == false){
							$scope.getPayroll($scope.selected_process,$scope.selected_department,$scope.payroll.employee_id,response.data.msg);
						}
						

						$('body').LoadingOverlay("hide");
					}, function (error) {
						console.log(error.data);
					});
				}

				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});

			
			
		}

		$scope.edit_data = function(){

			$('body').LoadingOverlay("show",{maxSize:50});
			$http.post(base_url + 'payroll/check_committed', {id: $scope.payroll.id}, config).then(function (response) {
				var is_committed = response.data.is_committed;
				if(is_committed == "Y"){
					$scope.payroll.confirm = "Y";
					showNotification("Error","Payroll is already committed!","error");
				}else{
					$scope.payroll.edit_mode = "true";
					$scope.final = false;
				}

				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});

			
		}
	});

app.controller('reportCtrl', function($scope,$http) {
	$scope.branches = {};
	$scope.departments = {};
	$scope.months = {};
	$scope.years = {};
	$scope.branch = '';
	$scope.department = '';
	$scope.month = '';
	$scope.year = '';
	$scope.report = [];

	$scope.getPayrollProcessByBranch = function(){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/getPayrollProcessByBranch', {branch_id: $scope.branch}, config).then(function (response) {
			$scope.payroll_processes = response.data.payroll_processes;
			$scope.process = '';

			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.getData = function(process_id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/getdataReport', '', config).then(function (response) {
			$scope.payroll_processes = response.data.payroll_processes;
			$scope.departments = response.data.departments;
			$scope.branches = response.data.branches;

			if(process_id != ''){
				$scope.process = process_id;
				$scope.getPayroll(process_id, '', '');
			}

			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});

	}

	$scope.sendSlip = function(employee_id, process_id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/sendSlip', {employee_id : employee_id, process_id : process_id}, config).then(function (response) {
			$("#slip"+employee_id).text('Sent');
			$("#slip"+employee_id).prop('disabled', true);
			showNotification("Success","Pay slip sent to "+response.data.name,"success");
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.confirmAll = function(process,department){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/confirmAll', {process:process,department:department}, config).then(function (response) {
			$scope.getPayroll($scope.selected_process, $scope.selected_branch, $scope.selected_department);
			$('body').LoadingOverlay("hide");
		},function (error) {
			console.log(error.data);
		});
	}

	$scope.sendAll = function(process,department){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/sendAll', {process:process,department:department}, config).then(function (response) {
			$('body').LoadingOverlay("hide");
			showNotification("Success","Pay slip sent to everyone","success");
			$scope.getPayroll($scope.selected_process, $scope.selected_branch, $scope.selected_department);
		},function (error) {
			console.log(error.data);
		});
	}

	$scope.getPayroll = function(process,branch,department){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/getEmployeeReport', {process:process,branch:branch,department:department}, config).then(function (response) {
			$scope.report = response.data;
			$scope.net_report = response.data.net_report;
			$scope.net_total = response.data.net_total;
			// $scope.variance_report = response.data.variance_report;
			$scope.v_report = response.data.v_report;
			$scope.v_report.epf = parseFloat($scope.v_report.epf);
			$scope.v_report.epf_c = parseFloat($scope.v_report.epf_c);
			$scope.v_report.eis = parseFloat($scope.v_report.eis);
			$scope.v_report.eis_c = parseFloat($scope.v_report.eis_c);
			$scope.v_report.socso = parseFloat($scope.v_report.socso);
			$scope.v_report.socso_c = parseFloat($scope.v_report.socso_c);
			$scope.v_report.tax = parseFloat($scope.v_report.tax);
			$scope.v_report.cp38 = parseFloat($scope.v_report.cp38);
			$scope.v_report.net_pay = parseFloat($scope.v_report.net_pay);
			// $scope.v_report_last = response.data.v_report_last;
			// $scope.v_report_last.epf = parseFloat($scope.v_report_last.epf);
			// $scope.v_report_last.epf_c = parseFloat($scope.v_report_last.epf_c);
			// $scope.v_report_last.eis = parseFloat($scope.v_report_last.eis);
			// $scope.v_report_last.eis_c = parseFloat($scope.v_report_last.eis_c);
			// $scope.v_report_last.socso = parseFloat($scope.v_report_last.socso);
			// $scope.v_report_last.socso_c = parseFloat($scope.v_report_last.socso_c);
			// $scope.v_report_last.tax = parseFloat($scope.v_report_last.tax);
			// $scope.v_report_last.cp38 = parseFloat($scope.v_report_last.cp38);
			// $scope.v_report_last.net_pay = parseFloat($scope.v_report_last.net_pay);

			$scope.selected_process = process;
			$scope.selected_branch = branch;
			$scope.selected_department = department;

			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.confirm = function(employee_id, process_id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/confirm', {employee_id:employee_id, process_id:process_id}, config).then(function (response) {
			$scope.getPayroll($scope.selected_process,$scope.selected_branch,$scope.selected_department);
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.unconfirm = function(employee_id, process_id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/unconfirm', {employee_id:employee_id, process_id:process_id}, config).then(function (response) {
			$scope.getPayroll($scope.selected_process,$scope.selected_branch,$scope.selected_department);
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.showSelectBank = function(){
		showNotification("Error",'No bank selected',"error");
	}

	$scope.download_file = function(process_id, department_id, bank_name){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/download_file', {process_id: process_id, department_id: department_id, bank_name: bank_name}, config).then(function (response) {
			if(response.data.success){
				showNotification("Success","File is downloading...","success");
				var url = base_url + 'payroll/download_bank_file/' + response.data.file_name;    
     			// window.open(url, '_blank');
     			// window.open(url, '_parent', 'download');
     			$("#download_btn").attr("href", url);
     			$("#download_btn")[0].click();
			}else{
				showNotification("Error","Mandatory field(s) must not be empty","error");
			}
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}
});


app.controller('approvalCtrl', function($scope,$http) {
	$scope.branches = {};
	$scope.departments = {};
	$scope.months = {};
	$scope.years = {};
	$scope.branch = '';
	$scope.department = '';
	$scope.month = '';
	$scope.year = '';
	$scope.report = [];

	$scope.getPayrollProcessByBranch = function(){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/getPayrollProcessByBranch', {branch_id: $scope.branch}, config).then(function (response) {
			$scope.payroll_processes = response.data.payroll_processes;
			$scope.process = '';

			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}
	
	$scope.getData = function(process_id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/getdataReport', '', config).then(function (response) {
			$scope.payroll_processes = response.data.payroll_processes;
			$scope.departments = response.data.departments;
			$scope.branches = response.data.branches;

			if(process_id != ''){
				$scope.process = process_id;
				$scope.getPayroll(process_id, '', '');
			}

			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});

	}

	$scope.sendSlip = function(employee_id, process_id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/sendSlip', {employee_id : employee_id, process_id : process_id}, config).then(function (response) {
			$("#slip"+employee_id).text('Sent');
			$("#slip"+employee_id).prop('disabled', true);
			showNotification("Success","Pay slip sent to "+response.data.name,"success");
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.approveAll = function(process,department){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/approveAll', {process:process,department:department}, config).then(function (response) {
			$scope.getPayroll($scope.selected_process, $scope.selected_branch, $scope.selected_department);
			$('body').LoadingOverlay("hide");
		},function (error) {
			console.log(error.data);
		});
	}

	$scope.sendAll = function(process,department){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/sendAll', {process:process,department:department}, config).then(function (response) {
			$('body').LoadingOverlay("hide");
			showNotification("Success","Pay slip sent to everyone","success");
			$scope.getPayroll($scope.selected_process, $scope.selected_branch, $scope.selected_department);
		},function (error) {
			console.log(error.data);
		});
	}

	$scope.getPayroll = function(process,branch,department){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/getEmployeeReport', {process:process,branch:branch,department:department}, config).then(function (response) {
			$scope.report = response.data;
			$scope.net_report = response.data.net_report;
			$scope.net_total = response.data.net_total;
			// $scope.variance_report = response.data.variance_report;
			$scope.v_report = response.data.v_report;
			$scope.v_report.epf = parseFloat($scope.v_report.epf);
			$scope.v_report.epf_c = parseFloat($scope.v_report.epf_c);
			$scope.v_report.eis = parseFloat($scope.v_report.eis);
			$scope.v_report.eis_c = parseFloat($scope.v_report.eis_c);
			$scope.v_report.socso = parseFloat($scope.v_report.socso);
			$scope.v_report.socso_c = parseFloat($scope.v_report.socso_c);
			$scope.v_report.tax = parseFloat($scope.v_report.tax);
			$scope.v_report.cp38 = parseFloat($scope.v_report.cp38);
			$scope.v_report.net_pay = parseFloat($scope.v_report.net_pay);

			$scope.selected_process = process;
			$scope.selected_branch = branch;
			$scope.selected_department = department;

			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.approve = function(employee_id, process_id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/approve', {employee_id:employee_id, process_id:process_id}, config).then(function (response) {
			$scope.getPayroll($scope.selected_process,$scope.selected_branch,$scope.selected_department);
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.disapprove = function(employee_id, process_id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/disapprove', {employee_id:employee_id, process_id:process_id}, config).then(function (response) {
			$scope.getPayroll($scope.selected_process,$scope.selected_branch,$scope.selected_department);
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}

	$scope.showSelectBank = function(){
		showNotification("Error",'No bank selected',"error");
	}

	$scope.download_file = function(process_id, department_id, bank_name){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'payroll/download_file', {process_id: process_id, department_id: department_id, bank_name: bank_name}, config).then(function (response) {
			if(response.data.success){
				showNotification("Success","File is downloading...","success");
				var url = base_url + 'payroll/download_bank_file/' + response.data.file_name;    
     			// window.open(url, '_blank');
     			// window.open(url, '_parent', 'download');
     			$("#download_btn").attr("href", url);
     			$("#download_btn")[0].click();
			}else{
				showNotification("Error","Mandatory field(s) must not be empty","error");
			}
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}
});