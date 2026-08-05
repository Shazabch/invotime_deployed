var config = {
	headers: {
		'Content-Type': 'application/json;charset=utf-8;'
	}
};
var base_url = js_base_url;

var app = angular.module('myApp', []);
app.controller('profileCtrl', function($scope,$http,$compile) {
	$scope.show_add_family = false;
	$scope.show_add_contact = false;
	$scope.show_add_language = false;
	$scope.show_add_incentive = false;
	$scope.show_add_allowance = false;
	$scope.show_add_education = false;
	$scope.show_add_skill = false;
	$scope.delete = {};
	$scope.addFamily = {first_name:'',last_name:'',relation:'',age:'',mobile:'',job:''};
	$scope.addContact = {first_name:'',last_name:'',relation:'',email:'',telephone:'',office_no:'',mobile:'',address:'',address_city:'',address_state:'',address_postcode:''};
	$scope.addLanguage = {language:'',writing_skill:'',speaking_skill:''};
	$scope.addIncentive = {incentive_name:'',amount:''};
	$scope.addAllowance = {allowance_name:'',amount:''};
	$scope.addEdu = {institution:'',country:'',course_field:'',period_from:'',period_to:'',highest_qualification_attained:''};
	$scope.addExp = {company_name:'',industry:'',position:'',period_from:'',period_to:'',basic_salary:'',bonus:'',allowance:''};
	$scope.addSkill = {skill:'',level:'',notes:''};
	$scope.family = [];
	$scope.contacts = [];
	$scope.languages = [];
	$scope.incentives = [];
	$scope.allowances = [];
	$scope.transfers = [];
	$scope.educations = [];
	$scope.experience = [];
	$scope.skills = [];
	$scope.showDetails = false;
	$scope.getData = function(id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'profile/get_emp_data', {id : id}, config).then(function (response) {
			$scope.family = response.data.family;
			$scope.contacts = response.data.contacts;
			$scope.languages = response.data.languages;
			$scope.incentives = response.data.incentives;
			$scope.allowances = response.data.allowances;
			$scope.educations = response.data.educations;
			$scope.experience = response.data.experience;
			$scope.skills = response.data.skills;
			$scope.emp_id = id;
			$scope.transfers = response.data.transfers;
			console.log($scope.transfers);
			$('body').LoadingOverlay("hide");
		}, function (error) {
			console.log(error.data);
		});
	}
	$scope.hide_add_fmily = function(){
		$scope.show_add_family = false;
	}
	$scope.show_family_form = function(){
		$scope.show_add_family = true;
		$('.family_relation').val('').trigger('change.select2');
		$scope.addFamily = {first_name:'',last_name:'',relation:'',age:'',mobile:'',job:''};
	}
	$scope.hide_add_contact = function(){
		$scope.show_add_contact = false;
	}
	$scope.show_contact_form = function(){
		$scope.show_add_contact = true;
	}
	$scope.hide_add_language = function(){
		$scope.show_add_language = false;
	}
	$scope.show_language_form = function(){
		$scope.show_add_language = true;
	}
	$scope.hide_add_incentive = function(){
		$scope.show_add_incentive = false;
	}
	$scope.show_incentive_form = function(){
		$scope.show_add_incentive = true;
	}
	$scope.hide_add_allowance = function(){
		$scope.show_add_allowance = false;
	}
	$scope.show_allowance_form = function(){
		$scope.show_add_allowance = true;
	}
	$scope.hide_add_education = function(){
		$scope.show_add_education = false;
	}
	$scope.show_education_form = function(){
		$scope.show_add_education = true;
	}
	$scope.hide_add_experience = function(){
		$scope.show_add_experience = false;
	}
	$scope.show_experience_form = function(){
		$scope.show_add_experience = true;
	}
	$scope.hide_add_skill = function(){
		$scope.show_add_skill = false;
	}
	$scope.show_skill_form = function(){
		$scope.show_add_skill = true;
	}
	$scope.add_family = function(valid){
		if(!valid){
			alert("Please fill all required fields");
		}
		if(valid){
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.addFamily.emp_id = $scope.emp_id;
			$http.post(base_url + 'profile/save_family', $scope.addFamily, config).then(function (response) {
				if(response.data.success){
					showNotification("Success",response.data.msg,"success");
					$scope.family = response.data.family;
					$scope.show_add_family = false;
					$('.family_relation').val('').trigger('change.select2');
					$scope.addFamily = {first_name:'',last_name:'',relation:'',age:'',mobile:'',job:''};
					$('body').LoadingOverlay("hide");
				}else{
					showNotification("Error",response.data.msg,"error");
					$('body').LoadingOverlay("hide");
				}
			}, function (error) {
				console.log(error.data);
			});
		}
		
	}
	$scope.add_skill = function(valid){
		if(!valid){
			alert("Please fill all required fields");
		}
		if(valid){
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.addSkill.emp_id = $scope.emp_id;
			$http.post(base_url + 'profile/save_skill', $scope.addSkill, config).then(function (response) {
				if(response.data.success){
					showNotification("Success",response.data.msg,"success");
					$scope.skills = response.data.skills;
					$scope.show_add_skill = false;
					$('.add_skill').val('').trigger('change.select2');
					$scope.addSkill = {skill:'',level:'',notes:''};
					$('body').LoadingOverlay("hide");
				}else{
					showNotification("Error",response.data.msg,"error");
					$('body').LoadingOverlay("hide");
				}
			}, function (error) {
				console.log(error.data);
			});
		}
		
	}
		$scope.edit_skill = function(valid){
		if(!valid){
			alert("Please fill all required fields");
		}
		if(valid){
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.editSkill.emp_id = $scope.emp_id;
			$http.post(base_url + 'profile/update_skill', $scope.editSkill, config).then(function (response) {
				if(response.data.success){
					showNotification("Success",response.data.msg,"success");
					$scope.skills = response.data.skills;
					$scope.show_edit_skill = false;
					$('.edit_skill').val('').trigger('change.select2');
					$scope.editSkill = {};
					$('#edit_skill').modal("toggle");
					$('body').LoadingOverlay("hide");
				}else{
					showNotification("Error",response.data.msg,"error");
					$('body').LoadingOverlay("hide");
				}
			}, function (error) {
				console.log(error.data);
			});
		}
		
	}
	$scope.edit_family = function(valid){
		if(!valid){
			alert("Please fill all required fields");
		}
		if(valid){
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.editFamily.emp_id = $scope.emp_id;
			$http.post(base_url + 'profile/update_family', $scope.editFamily, config).then(function (response) {
				if(response.data.success){
					showNotification("Success",response.data.msg,"success");
					$scope.family = response.data.family;
					$scope.show_add_family = false;
					$scope.editFamily = {};
					$('#edit_family').modal('toggle');
					$('body').LoadingOverlay("hide");
				}else{
					showNotification("Error",response.data.msg,"error");
					$('body').LoadingOverlay("hide");
				}
			}, function (error) {
				console.log(error.data);
			});
		}
		
	}
	$scope.add_education = function(valid){
		$('body').LoadingOverlay("show",{maxSize:50});
			$scope.addEdu.period_from = $('#add_edu_from').val();
			$scope.addEdu.period_to = $('#add_edu_to').val();
			$scope.addEdu.emp_id = $scope.emp_id;
			$http.post(base_url + 'profile/save_education', $scope.addEdu, config).then(function (response) {
				if(response.data.success){
					showNotification("Success",response.data.msg,"success");
					$scope.educations = response.data.educations;
					$scope.show_add_education = false;
					$scope.addEdu = {institution:'',country:'',course_field:'',period_from:'',period_to:'',highest_qualification_attained:''};
					$('body').LoadingOverlay("hide");
				}else{
					showNotification("Error",response.data.msg,"error");
					$('body').LoadingOverlay("hide");
				}
			}, function (error) {
				console.log(error.data);
			});
		
		
	}
	$scope.add_experience = function(valid){
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.addExp.period_from = $('#add_exp_from').val();
			$scope.addExp.period_to = $('#add_exp_to').val();
			$scope.addExp.emp_id = $scope.emp_id;
			$http.post(base_url + 'profile/save_experience', $scope.addExp, config).then(function (response) {
				if(response.data.success){
					showNotification("Success",response.data.msg,"success");
					$scope.experience = response.data.experience;
					$scope.show_add_experience = false;
					$scope.addExp = {company_name:'',industry:'',position:'',period_from:'',period_to:'',basic_salary:'',bonus:'',allowance:''};
					$('body').LoadingOverlay("hide");
				}else{
					showNotification("Error",response.data.msg,"error");
					$('body').LoadingOverlay("hide");
				}
			}, function (error) {
				console.log(error.data);
			});
		
		
	}
	$scope.edit_experience = function(valid){
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.editExp.period_from = $('#edit_exp_from').val();
			$scope.editExp.period_to = $('#edit_exp_to').val();
			$scope.editExp.emp_id = $scope.emp_id;
			$http.post(base_url + 'profile/update_experience', $scope.editExp, config).then(function (response) {
				if(response.data.success){
					showNotification("Success",response.data.msg,"success");
					$scope.experience = response.data.experience;
					$scope.show_add_experience = false;
					$scope.editExp = {};
					$('#edit_experience').modal("toggle");
					$('body').LoadingOverlay("hide");
				}else{
					showNotification("Error",response.data.msg,"error");
					$('body').LoadingOverlay("hide");
				}
			}, function (error) {
				console.log(error.data);
			});
		
		
	}
	$scope.edit_education = function(valid){
		$('body').LoadingOverlay("show",{maxSize:50});
			$scope.editEdu.period_from = $('#edit_edu_from').val();
			$scope.editEdu.period_to = $('#edit_edu_to').val();
			$scope.editEdu.emp_id = $scope.emp_id;
			$http.post(base_url + 'profile/update_education', $scope.editEdu, config).then(function (response) {
				if(response.data.success){
					showNotification("Success",response.data.msg,"success");
					$scope.educations = response.data.educations;
					$scope.show_add_education = false;
					$scope.editEdu = {};
					$('#edit_education').modal("toggle");
					$('body').LoadingOverlay("hide");
				}else{
					showNotification("Error",response.data.msg,"error");
					$('body').LoadingOverlay("hide");
				}
			}, function (error) {
				console.log(error.data);
			});
		
		
	}
	$scope.add_contact = function (valid){
		if(!valid){
			alert("Please fill all required fields");
		}
		if(valid){
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.addContact.emp_id = $scope.emp_id;
			$http.post(base_url + 'profile/save_contact', $scope.addContact, config).then(function (response) {
				if(response.data.success){
					showNotification("Success",response.data.msg,"success");
					$scope.contacts = response.data.contacts;
					$scope.show_add_contact = false;
					$('.contact_relation').val('').trigger('change.select2');
					$scope.addContact = {first_name:'',last_name:'',relation:'',email:'',telephone:'',office_no:'',mobile:'',address:'',address_city:'',address_state:'',address_postcode:''};
					$('body').LoadingOverlay("hide");
				}else{
					showNotification("Error",response.data.msg,"error");
					$('body').LoadingOverlay("hide");
				}
			}, function (error) {
				console.log(error.data);
			});
		}
	}
	$scope.edit_contact = function (valid){
		if(!valid){
			alert("Please fill all required fields");
		}
		if(valid){
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.editContact.emp_id = $scope.emp_id;
			$http.post(base_url + 'profile/update_contact', $scope.editContact, config).then(function (response) {
				if(response.data.success){
					showNotification("Success",response.data.msg,"success");
					$scope.contacts = response.data.contacts;
					$scope.show_add_contact = false;
					$scope.editContact = {};
					$('#edit_contact').modal('toggle');
					$('body').LoadingOverlay("hide");
				}else{
					showNotification("Error",response.data.msg,"error");
					$('body').LoadingOverlay("hide");
				}
			}, function (error) {
				console.log(error.data);
			});
		}
	}
	$scope.add_language = function (valid){
		if(!valid){
			alert("Please fill all required fields");
		}
		if(valid){
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.addLanguage.emp_id = $scope.emp_id;
			$http.post(base_url + 'profile/save_language', $scope.addLanguage, config).then(function (response) {
				if(response.data.success){
					showNotification("Success",response.data.msg,"success");
					$scope.languages = response.data.languages;
					$scope.show_add_language = false;
					$('.language_select').val('').trigger('change.select2');
					$('.select_writing').val('').trigger('change.select2');
					$('.select_speaking').val('').trigger('change.select2');
					$scope.addLanguage = {language:'',writing_skill:'',speaking_skill:''};
					$('body').LoadingOverlay("hide");
				}else{
					showNotification("Error",response.data.msg,"error");
					$('body').LoadingOverlay("hide");
				}
			}, function (error) {
				console.log(error.data);
			});
		}
	}
	$scope.editLanguageData = function(id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'profile/get_language', {id : id}, config).then(function (response) {
				if(response.data.success){
					$scope.editLanguage = response.data.language;
					$('.edit_language_select').val($scope.editLanguage.language).trigger('change.select2');
					$('.edit_select_writing').val($scope.editLanguage.writing_skill).trigger('change.select2');
					$('.edit_select_speaking').val($scope.editLanguage.speaking_skill).trigger('change.select2');
					$('body').LoadingOverlay("hide");
				}else{
					showNotification("Error",response.data.msg,"error");
					$('body').LoadingOverlay("hide");
				}
			}, function (error) {
				console.log(error.data);
			});
	}
	$scope.editSkillData = function(id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'profile/get_skill', {id : id}, config).then(function (response) {
				if(response.data.success){
					$scope.editSkill = response.data.skill;
					$('.edit_skill').val($scope.editSkill.level).trigger('change.select2');
					$('body').LoadingOverlay("hide");
				}else{
					showNotification("Error",response.data.msg,"error");
					$('body').LoadingOverlay("hide");
				}
			}, function (error) {
				console.log(error.data);
			});
	}
	$scope.editIncentiveData = function(id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'profile/get_incentive', {id : id}, config).then(function (response) {
				if(response.data.success){
					$scope.editIncentive = response.data.incentive;
					$('body').LoadingOverlay("hide");
				}else{
					showNotification("Error",response.data.msg,"error");
					$('body').LoadingOverlay("hide");
				}
			}, function (error) {
				console.log(error.data);
			});
	}
	$scope.editAllowanceData = function(id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'profile/get_allowance', {id : id}, config).then(function (response) {
				if(response.data.success){
					$scope.editAllowance = response.data.allowance;
				}else{
					showNotification("Error",response.data.msg,"error");
				}
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
	}
	$scope.editFamilyData = function(id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'profile/get_family', {id : id}, config).then(function (response) {
				if(response.data.success){
					$scope.editFamily = response.data.family;
					$scope.editFamily.age = parseInt($scope.editFamily.age);
					$('.edit_family_relation').val($scope.editFamily.relation).trigger('change.select2');
				}else{
					showNotification("Error",response.data.msg,"error");
				}
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
	}
	$scope.editContactData = function(id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'profile/get_contact', {id : id}, config).then(function (response) {
				if(response.data.success){
					$scope.editContact = response.data.contact;
					$('.edit_contact_relation').val($scope.editContact.relation).trigger('change.select2');
				}else{
					showNotification("Error",response.data.msg,"error");
				}
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
	}
	$scope.editEducationData = function(id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'profile/get_education', {id : id}, config).then(function (response) {
				if(response.data.success){
					$scope.editEdu = response.data.education;
				}else{
					showNotification("Error",response.data.msg,"error");
				}
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
	}
	$scope.editExperienceData = function(id){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'profile/get_experience', {id : id}, config).then(function (response) {
				if(response.data.success){
					$scope.editExp = response.data.experience;
					$scope.editExp.basic_salary = parseInt($scope.editExp.basic_salary);
					$scope.editExp.bonus = parseInt($scope.editExp.bonus);
					$scope.editExp.allowance = parseInt($scope.editExp.allowance);
				}else{
					showNotification("Error",response.data.msg,"error");
				}
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
	}
	$scope.edit_language = function(valid){
		if(!valid){
			alert("Please fill all required fields");
		}
		if(valid){
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.editLanguage.emp_id = $scope.emp_id;
			$http.post(base_url + 'profile/update_language', $scope.editLanguage, config).then(function (response) {
				if(response.data.success){
					showNotification("Success",response.data.msg,"success");
					$scope.languages = response.data.languages;
					$scope.show_add_language = false;
					$scope.editLanguage = {};
					$('#edit_language').modal('toggle');
				}else{
					showNotification("Error",response.data.msg,"error");
				}
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}
	}
	$scope.add_incentive = function (valid){
		if(!valid){
			alert("Please fill all required fields");
		}
		if(valid){
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.addIncentive.emp_id = $scope.emp_id;
			$http.post(base_url + 'profile/save_incentive', $scope.addIncentive, config).then(function (response) {
				if(response.data.success){
					showNotification("Success",response.data.msg,"success");
					$scope.incentives = response.data.incentives;
					$scope.show_add_incentive = false;
					$scope.addIncentive = {incentive_name:'',amount:''};
				}else{
					showNotification("Error",response.data.msg,"error");
				}
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}
	}
		$scope.edit_incentive = function (valid){
		if(!valid){
			alert("Please fill all required fields");
		}
		if(valid){
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.editIncentive.emp_id = $scope.emp_id;
			$http.post(base_url + 'profile/update_incentive', $scope.editIncentive, config).then(function (response) {
				if(response.data.success){
					showNotification("Success",response.data.msg,"success");
					$scope.incentives = response.data.incentives;
					$scope.show_add_incentive = false;
					$scope.editIncentive = {};
					$('#edit_incentive').modal('toggle');
				}else{
					showNotification("Error",response.data.msg,"error");
				}
				$('body').LoadingOverlay("show",{maxSize:50});
			}, function (error) {
				console.log(error.data);
			});
		}
	}
	$scope.add_allowance = function (valid){
		if(!valid){
			alert("Please fill all required fields");
		}
		if(valid){
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.addAllowance.emp_id = $scope.emp_id;
			$http.post(base_url + 'profile/save_allowance', $scope.addAllowance, config).then(function (response) {
				if(response.data.success){
					showNotification("Success",response.data.msg,"success");
					$scope.allowances = response.data.allowances;
					$scope.show_add_allowance = false;
					$scope.addAllowance = {allowance_name:'',amount:''};
				}else{
					showNotification("Error",response.data.msg,"error");
				}
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}
	}
	$scope.edit_allowance = function (valid){
		if(!valid){
			alert("Please fill all required fields");
		}
		if(valid){
			$('body').LoadingOverlay("show",{maxSize:50});
			$scope.editAllowance.emp_id = $scope.emp_id;
			$http.post(base_url + 'profile/update_allowance', $scope.editAllowance, config).then(function (response) {
				if(response.data.success){
					showNotification("Success",response.data.msg,"success");
					$scope.allowances = response.data.allowances;
					$scope.show_add_allowance = false;
					$scope.addAllowance = {allowance_name:'',amount:''};
					$('#edit_allowance').modal('toggle');
				}else{
					showNotification("Error",response.data.msg,"error");
				}
				$('body').LoadingOverlay("hide");
			}, function (error) {
				console.log(error.data);
			});
		}
	}
	$scope.setDelete = function(type, id){
		$scope.delete.type = type;
		$scope.delete.id = id;
	}

	$scope.deleteItem = function(){
		$('body').LoadingOverlay("show",{maxSize:50});
		$http.post(base_url + 'profile/delete_data', $scope.delete, config).then(function (response) {
			if(response.data.success){
				showNotification("Success",response.data.msg,"success");
				$('#delete_modal').modal('toggle');
				$scope.getData($scope.emp_id);
				$('body').LoadingOverlay("hide");
			}
		}, function (error) {
			console.log(error.data);
		});
	}
});

$(document).on("mouseenter", ".main_div", function() {
    $(this).find('.main_btn').show();
});

$(document).on("mouseleave", ".main_div", function() {
    $(this).find('.main_btn').hide();
});