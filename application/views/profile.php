<style type="text/css">
	.lable-detail{
		color: #515365;
	    font-weight: 500;
	    width: 50%;
	}
	.value-detail{
		color: #888da8;
	    width: 50%;
	}
</style>

<div class="page-wrapper" ng-app="myApp" ng-controller="profileCtrl" ng-init="getData('<?php echo $emp->id; ?>')" ng-cloak>
	<div class="content container-fluid">
		<!-- <div class="row">
			<div class="col-sm-8">
				<h4 class="page-title">My Profile</h4>
			</div>

		</div> -->
		<div class="card-box">
			<div class="row">
				<div class="col-md-12">
					<div class="profile-view">
						<div class="profile-img-wrap">
							<div class="profile-img">
								<a href="#"><img class="avatar" src="blue/assets/img/user.jpg" alt=""></a>
							</div>
						</div>
						<div class="profile-basic">
							<div class="row">
								<div class="col-md-6">
									<div class="profile-info-left">
										<h3 class="user-name m-t-0 m-b-0"><?php echo $emp->first_name;?></h3>
										<small class="text-muted"><?php echo $emp->title;?></small>
										<div class="staff-id">Employee ID : <?php echo $emp->special_id;?></div>
									</div>
								</div>
								<div class="col-md-6">
									<ul class="personal-info">
										<li>
											<span class="title">Role:</span>
											<span class="text"><?php echo $emp->job_name; ?></span>
										</li>
										<li>
											<span class="title">Department:</span>
											<span class="text"><?php echo $emp->department; ?></span>
										</li>
										<li>
											<span class="title">Outlet:</span>
											<span class="text"><?php echo $emp->branch; ?></span>
										</li>
										<li>
											<span class="title">Phone:</span>
											<span class="text"><?php echo $emp->mobile; ?></span>
										</li>
										<li>
											<span class="title">Email:</span>
											<span class="text"><?php echo $emp->email; ?></span>
										</li>
									</ul>
								</div>
								<div class="col-md-12 text-center" ng-show="!showDetails">
									<button class="btn btn-link" style="color:blue" ng-click="showDetails = !showDetails">Show More</button>
								</div>
							</div>
							<div class="row" ng-show="showDetails">
								<hr>
								<div class="col-md-6">
									
									<table style="width: 100%">
										<tr>
											<td class="lable-detail">Gender:</td>
											<td class="value-detail"><?php echo $emp->sex;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Marital Status:</td>
											<td class="value-detail"><?php echo $emp->marital_status;?></td>
										</tr>
										<tr>
											<td class="lable-detail">DOB:</td>
											<td class="value-detail"><?php echo $emp->dob;?></td>
										</tr>
										<tr>
											<td class="lable-detail">POB:</td>
											<td class="value-detail"><?php echo $emp->pob;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Race:</td>
											<td class="value-detail"><?php echo $emp->race;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Religion:</td>
											<td class="value-detail"><?php echo $emp->religion;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Nationality:</td>
											<td class="value-detail"><?php echo $emp->nationality;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Email:</td>
											<td class="value-detail"><?php echo $emp->email;?></td>
										</tr>
										<tr>
											<td class="lable-detail">NIRC/Passport:</td>
											<td class="value-detail"><?php echo $emp->ic_passport;?></td>
										</tr>
										<tr>
											<td class="lable-detail">QR Barcode:</td>
											<td class="value-detail"><?php echo $emp->qr_barcode;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Job Grade:</td>
											<td class="value-detail"><?php echo $emp->grade;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Employment Type:</td>
											<td class="value-detail"><?php echo $emp->employment_type;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Employee Type:</td>
											<td class="value-detail"><?php echo $emp->employee_type;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Hired On:</td>
											<td class="value-detail"><?php echo $emp->hired_on;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Basic Wage:</td>
											<td class="value-detail"><?php echo $emp->basic_wage;?></td>
										</tr>
										<tr>
											<td class="lable-detail">EPF:</td>
											<td class="value-detail"><?php echo $emp->epf_no;?></td>
										</tr>
										<tr>
											<td class="lable-detail">SOCSO:</td>
											<td class="value-detail"><?php echo $emp->socso;?></td>
										</tr>
										<tr>
											<td class="lable-detail">EIS:</td>
											<td class="value-detail"><?php echo $emp->eis;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Overtime:</td>
											<td class="value-detail"><?php echo $emp->is_ot;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Early Overtime:</td>
											<td class="value-detail"><?php echo $emp->is_early_ot;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Is Daily Waged:</td>
											<td class="value-detail"><?php echo $emp->is_daily_waged;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Temporary Address:</td>
											<td class="value-detail"><?php echo $emp->temp_address;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Temporary City:</td>
											<td class="value-detail"><?php echo $emp->temp_address_city;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Temporary Postcode:</td>
											<td class="value-detail"><?php echo $emp->temp_address_postcode;?></td>
										</tr>
										
									</table>
								</div>
								<div class="col-md-6">
									<table style="width: 100%">
										
										<tr>
											<td class="lable-detail">Temporary State:</td>
											<td class="value-detail"><?php echo $emp->temp_address_state;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Permanent Address:</td>
											<td class="value-detail"><?php echo $emp->perm_address;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Permanent City:</td>
											<td class="value-detail"><?php echo $emp->perm_address_city;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Permanent Postcode:</td>
											<td class="value-detail"><?php echo $emp->perm_address_postcode;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Permanent State:</td>
											<td class="value-detail"><?php echo $emp->temp_address_state;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Telephone:</td>
											<td class="value-detail"><?php echo $emp->telephone;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Mobile:</td>
											<td class="value-detail"><?php echo $emp->mobile;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Income Tax Number:</td>
											<td class="value-detail"><?php echo $emp->income_tax_no;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Income Tax Branch:</td>
											<td class="value-detail"><?php echo $emp->income_tax_branch;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Bank Name:</td>
											<td class="value-detail"><?php echo $emp->employee_bank;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Bank Account Number:</td>
											<td class="value-detail"><?php echo $emp->bank_account_no;?></td>
										</tr>
										<tr>
											<td class="lable-detail">License Class:</td>
											<td class="value-detail"><?php echo $emp->license_class;?></td>
										</tr>
										<tr>
											<td class="lable-detail">License Number:</td>
											<td class="value-detail"><?php echo $emp->license_no;?></td>
										</tr>
										<tr>
											<td class="lable-detail">License Expiry:</td>
											<td class="value-detail"><?php echo $emp->license_expiry;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Compassionate Leaves:</td>
											<td class="value-detail"><?php echo $emp->compassionate_leaves;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Paternity Leaves:</td>
											<td class="value-detail"><?php echo $emp->paternity_leaves;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Marriage Leaves:</td>
											<td class="value-detail"><?php echo $emp->marriage_leaves;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Hospitalisation Leaves:</td>
											<td class="value-detail"><?php echo $emp->hospitalisation_leaves;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Study Leaves:</td>
											<td class="value-detail"><?php echo $emp->study_leaves;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Replacement Leaves:</td>
											<td class="value-detail"><?php echo $emp->replacement_leaves;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Unpaid Leaves:</td>
											<td class="value-detail"><?php echo $emp->unpaid_leaves;?></td>
										</tr>
										<tr>
											<td class="lable-detail">Emergency Leaves:</td>
											<td class="value-detail"><?php echo $emp->emergency_leaves;?></td>
										</tr>
									</table>
								</div>
								<div class="col-md-12 text-center" ng-show="showDetails">
									<button class="btn btn-link" style="color:blue" ng-click="showDetails = !showDetails">Show Less</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-4">
				<div class="card-box m-b-0">
					<h3 class="card-title">Skills</h3>

					


					<div class="list-group" ng-show="allowances.skills != 0">								 
						<div class="media list-group-item main_div" ng-repeat="s in skills">
							
							<div class="media-body">
								<h4 class="media-heading">{{s.skill}}<span class="pull-right main_btn" style="display: none;"><button class="btn btn-info" data-toggle="modal" data-target="#edit_skill" ng-click="editSkillData(s.id)"><i class="fa fa-pencil"></i></button> <button class="btn btn-danger" data-toggle="modal" data-target="#delete_modal" ng-click="setDelete('skill',s.id)"><i class="fa fa-trash"></i></button></span><button class="btn btn-info pull-right" style="visibility:hidden">i</button></h4>
								<table width="100%">
									<tr>
										<td width="25%"><strong>Level</strong></td>
										<td>{{s.level}}</td>
									</tr>
									<tr>
										<td width="25%"><strong>Notes</strong></td>
										<td>{{s.notes}}</td>
									</tr>
									
								</table>
							</div>
						</div>


					</div>
					<div class="text-center">
						<input type="button" class="btn btn-primary" value="Add Skill" ng-show="!show_add_skill" ng-click="show_skill_form()">
					</div>
					<form name="add_skil" id="add_skil" ng-submit="add_skill(add_skil.$valid)" ng-show="show_add_skill">										
						<div class="form-group">
							<label class="control-label">Skill Name <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addSkill.skill" required="">
						</div>	
						<div class="form-group">
							<label class="control-label">Level <span class="text-danger">*</span></label>
							<select class="select add_skill" ng-model="addSkill.level" required="">
								<option value="">Select Level</option>
								<option value="Beginner">Beginner</option>
								<option value="Intermediate">Intermediate</option>
								<option value="Advanced">Advanced</option>
							</select>
						</div>							
						<div class="form-group">
							<label class="control-label">Notes <span class="text-danger"></span></label>
							<textarea class="form-control" ng-model="addSkill.notes"></textarea>
						</div>
						<button type="submit" class="btn btn-primary">Save</button>
						<input type="button" class="btn btn-default" value="Cancel" ng-click="hide_add_skill()">
					</form>
				</div>
			</div>
			<div class="col-md-8">
				<div class="card-box">
					<h3 class="card-title">Qualifications</h3>
					<div class="experience-box" ng-show="educations.length!=0">
						<ul class="experience-list">
							<li ng-repeat="e in educations" class="main_div">
								<div class="experience-user">
									<div class="before-circle"></div>
								</div>
								<div class="experience-content">
									<div class="timeline-content">
										<span class="pull-right main_btn" style="display: none;"><button class="btn btn-info" data-toggle="modal" data-target="#edit_education" ng-click="editEducationData(e.id)"><i class="fa fa-pencil"></i></button> <button class="btn btn-danger" data-toggle="modal" data-target="#delete_modal" ng-click="setDelete('education',e.id)"><i class="fa fa-trash"></i></button></span>
										<a href="javascript:void(0)" class="name">{{e.institution}} ({{e.country}})</a>
										<div>{{e.highest_qualification_attained}} {{e.course_field}}</div>
										<span class="time">{{e.start}} - {{e.end}}</span>
										
									</div>
								</div>

							</li>
							
						</ul>
					</div>
					<br>
					<div class="text-center">
						<input type="button" class="btn btn-primary" value="Add Qualification" ng-show="!show_add_education" ng-click="show_education_form()">
					</div>
					<form name="add_edu" id="add_edu" ng-submit="add_education(add_edu.$valid)" ng-show="show_add_education">										
						<div class="form-group">
							<label class="control-label">Institution Name <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addEdu.institution" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Institution Country <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addEdu.country" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Course/Field <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addEdu.course_field" required="">
						</div>

						<div class="form-group col-md-12">
							<div class="col-xs-6">
								<label class="control-label">Period From <span class="text-danger">*</span></label>
								<div class="cal-icon"><input class="form-control datetimepicker" type="text"  ng-model="addEdu.period_from" required="" id="add_edu_from"></div>
							</div>
							<div class="col-xs-6">
								<label class="control-label">Period To <span class="text-danger">*</span></label>
								<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="addEdu.period_to" required="" id="add_edu_to"></div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label">Highest Qualification Attained <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addEdu.highest_qualification_attained" required="">
						</div>
						<button type="submit" class="btn btn-primary">Save</button>
						<input type="button" class="btn btn-default" value="Cancel" ng-click="hide_add_education()">
					</form>
				</div>
				<div class="card-box m-b-0">
					<h3 class="card-title">Experience</h3>
					<div class="experience-box" ng-show="experience.length != 0">
						<ul class="experience-list">
							<li ng-repeat="ex in experience" class="main_div">
								<div class="experience-user">
									<div class="before-circle"></div>
								</div>
								<div class="experience-content">
									<div class="timeline-content">
										<span class="pull-right main_btn" style="display: none;"><button class="btn btn-info" data-toggle="modal" data-target="#edit_experience" ng-click="editExperienceData(ex.id)"><i class="fa fa-pencil"></i></button> <button class="btn btn-danger" data-toggle="modal" data-target="#delete_modal" ng-click="setDelete('experience',ex.id)"><i class="fa fa-trash"></i></button></span>
										<a href="javascript:void(0)" class="name">{{ex.position}} at {{ex.company_name}}</a>
										<br><small><strong>Industry :</strong>{{ex.industry}}</small>
										<br><small><strong>Basic Salry :</strong>{{ex.basic_salary}}</small>
										<br><small><strong>Bonus :</strong>{{ex.bonus}}</small>
										<br><small><strong>Allowance :</strong>{{ex.allowance}}</small>

										<span class="time">{{ex.start}} - {{ex.end}}</span>
										

									</div>
								</div>
							</li>
						</ul>
					</div>
					<br>
					<div class="text-center">
						<input type="button" class="btn btn-primary" value="Add Experience" ng-show="!show_add_experience" ng-click="show_experience_form()">
					</div>
					<form name="add_experience_form" id="add_experience_form" ng-submit="add_experience(add_experience_form.$valid)" ng-show="show_add_experience">										
						<div class="form-group">
							<label class="control-label">Company Name <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addExp.company_name" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Company Industry <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addExp.industry" required="">
						</div>

						<div class="form-group col-md-12">
							<div class="col-xs-6">
								<label class="control-label">Period From <span class="text-danger">*</span></label>
								<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="addExp.period_from" required="" id="add_exp_from"></div>
							</div>
							<div class="col-xs-6">
								<label class="control-label">Period To <span class="text-danger">*</span></label>
								<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="addExp.period_to" required="" id="add_exp_to"></div>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label">Position <span class="text-danger">*</span></label>
							<input class="form-control" type="text"  ng-model="addExp.position" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Basic Salary <span class="text-danger">*</span></label>
							<input class="form-control" type="number" ng-model="addExp.basic_salary" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Bonus <span class="text-danger">*</span></label>
							<input class="form-control" type="number" ng-model="addExp.bonus" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Allowance (If any)</label>
							<input class="form-control" type="number" ng-model="addExp.allowance">
						</div>
						<button type="submit" class="btn btn-primary">Save</button>
						<input type="button" class="btn btn-default" value="Cancel" ng-click="hide_add_experience()">
					</form>

					
				</div>
			</div>
		</div><br><!-- NO SPACE HERE. FIND OUT WHY & REMOVE BR***-->

		<div class="row">

			<div class="col-md-6">
				<div class="card-box m-b-0">
					<h3 class="card-title">Emergency Contacts</h3>

					


					<div class="list-group" ng-show="contacts.length != 0">								 
						<div class="media list-group-item main_div" ng-repeat="c in contacts">
							
							<div class="media-body">
								<h4 class="media-heading">{{c.first_name}} {{c.last_name}}<span class="text-primary"> ({{c.relation}})</span> <span class="pull-right main_btn" style="display: none"><button class="btn btn-info" data-toggle="modal" data-target="#edit_contact" ng-click="editContactData(c.id)"><i class="fa fa-pencil"></i></button> <button class="btn btn-danger" data-toggle="modal" data-target="#delete_modal" ng-click="setDelete('contact',c.id)"><i class="fa fa-trash"></i></button></span><button class="btn btn-info pull-right" style="visibility:hidden">i</button></h4>
								<table width="100%">
									<tr>
										<td width="25%"><strong>Email</strong></td>
										<td>{{c.email}}</td>
									</tr>
									<tr>
										<td width="25%"><strong>Landline</strong></td>
										<td>{{c.telephone}}</td>
									</tr>
									<tr>
										<td width="25%"><strong>Office No.</strong></td>
										<td>{{c.office_no}}</td>
									</tr>
									<tr>
										<td width="25%"><strong>Mobile</strong></td>
										<td>{{c.mobile}}</td>
									</tr>
									<tr>
										<td width="25%"><strong>Address</strong></td>
										<td>{{c.address}}, {{c.address_city}}, {{c.address_state}}, {{c.address_postcode}}</td>
									</tr>
								</table>
							</div>
						</div>



					</div>
					<div class="text-center">
						<input type="button" class="btn btn-primary" value="Add Emergency Contact" ng-show="!show_add_contact" ng-click="show_contact_form()">
					</div>
					<form name="add_em_contacts" id="add_em_contacts" ng-submit="add_contact(add_em_contacts.$valid)" ng-show="show_add_contact">										
						<div class="form-group">
							<label class="control-label">First Name <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addContact.first_name" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Last Name <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addContact.last_name" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Relation <span class="text-danger">*</span></label>
							<select class="select contact_relation" ng-model="addContact.relation"  required="">
								<option value="">Select Relation</option>
								<option value="Father">Father</option>
								<option value="Mother">Mother</option>
								<option value="Sibling">Sibling</option>
								<option value="Spouse">Spouse</option>
								<option value="Child">Child</option>
								<option value="Guardian">Guardian</option>
								<option value="Other">Other</option>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Email <span class="text-danger">*</span></label>
							<input class="form-control" type="email" ng-model="addContact.email" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Landline <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addContact.telephone" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Office No. <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addContact.office_no" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Mobile <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addContact.mobile" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Address <span class="text-danger">*</span></label>
							<textarea class="form-control" ng-model="addContact.address" required=""></textarea>
						</div>
						<div class="form-group">
							<label class="control-label">City <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addContact.address_city" required="">
						</div>
						<div class="form-group">
							<label class="control-label">State <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addContact.address_state" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Postcode <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addContact.address_postcode" required="">
						</div>
						<button type="submit" class="btn btn-primary">Save</button>
						<input type="button" class="btn btn-default" value="Cancel" ng-click="hide_add_contact()">



					</form>



				</div>
			</div>
			<div id="delete_modal" class="modal custom-modal fade" role="dialog" data-backdrop="static" data-keyboard="false">
				<div class="modal-dialog">
					<div class="modal-content modal-md">
						<div class="modal-header">
							<h4 class="modal-title"></h4>
						</div>
						<form>
							<div class="modal-body card-box">
								<p>Are you sure you want to delete this?</p>
								<div class="m-t-20"> <a href="#" class="btn btn-default" data-dismiss="modal">Close</a>
									<button type="submit" class="btn btn-danger" ng-click="deleteItem()">Delete</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>

			<div class="col-md-6">
				<div class="card-box" ng-cloak>
					<h3 class="card-title">Family Members</h3>
					<div class="list-group" ng-show="family.length != 0">								 
						<div class="media list-group-item main_div" ng-repeat="f in family">
							
							<div class="media-body">
								<h4 class="media-heading">{{f.first_name}} {{f.last_name}}<span class="text-primary"> ({{f.relation}})</span> <span class="pull-right main_btn" style="display: none;"><button class="btn btn-info" data-toggle="modal" data-target="#edit_family" ng-click="editFamilyData(f.id)"><i class="fa fa-pencil"></i></button> <button class="btn btn-danger" data-toggle="modal" data-target="#delete_modal" ng-click="setDelete('family',f.id)"><i class="fa fa-trash"></i></button></span><button class="btn btn-info pull-right" style="visibility:hidden">i</button></h4>
								<table width="100%">
									<tr>
										<td width="25%"><strong>Age</strong></td>
										<td>{{f.age}}</td>
									</tr>
									<tr>
										<td width="25%"><strong>Job</strong></td>
										<td>{{f.job}}</td>
									</tr>
									<tr>
										<td width="25%"><strong>Phone</strong></td>
										<td>{{f.mobile}}</td>
									</tr>
								</table>
							</div>
						</div>


					</div>
					<div id="edit_language" class="modal fade" role="dialog">
						<div class="modal-dialog">

							<!-- Modal content-->
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">Edit Language</h4>
								</div>
								<div class="modal-body">
									<form name="edit_language_form" id="edit_language_form" ng-submit="edit_language(edit_language_form.$valid)">								
										<div class="form-group">
											<label class="control-label">Choose Language <span class="text-danger">*</span></label>
											<select class="select edit_language_select" ng-model="editLanguage.language" required="">
												<option value="">Select Language</option>
												<option value="Malay">Malay</option>
												<option value="English">English</option>
												<option value="Chinese (Mandarin)">Chinese (Mandarin)</option>
												<option value="Tamil">Tamil</option>
												<option value="Afrikanns">Afrikanns</option>
												<option value="Albanian">Albanian</option>
												<option value="Arabic">Arabic</option>
												<option value="Armenian">Armenian</option>
												<option value="Basque">Basque</option>
												<option value="Bengali">Bengali</option>
												<option value="Bulgarian">Bulgarian</option>
												<option value="Catalan">Catalan</option>
												<option value="Cambodian">Cambodian</option>
												<option value="Croation">Croation</option>
												<option value="Czech">Czech</option>
												<option value="Danish">Danish</option>
												<option value="Dutch">Dutch</option>
												<option value="Estonian">Estonian</option>
												<option value="Fiji">Fiji</option>
												<option value="Finnish">Finnish</option>
												<option value="French">French</option>
												<option value="Georgian">Georgian</option>
												<option value="German">German</option>
												<option value="Greek">Greek</option>
												<option value="Gujarati">Gujarati</option>
												<option value="Hebrew">Hebrew</option>
												<option value="Hindi">Hindi</option>
												<option value="Hungarian">Hungarian</option>
												<option value="Icelandic">Icelandic</option>
												<option value="Indonesian">Indonesian</option>
												<option value="Irish">Irish</option>
												<option value="Italian">Italian</option>
												<option value="Japanese">Japanese</option>
												<option value="Javanese">Javanese</option>
												<option value="Korean">Korean</option>
												<option value="Latin">Latin</option>
												<option value="Latvian">Latvian</option>
												<option value="Lithuanian">Lithuanian</option>
												<option value="Macedonian">Macedonian</option>
												<option value="Malayalam">Malayalam</option>
												<option value="Maltese">Maltese</option>
												<option value="Maori">Maori</option>
												<option value="Marathi">Marathi</option>
												<option value="Mongolian">Mongolian</option>
												<option value="Nepali">Nepali</option>
												<option value="Norwegian">Norwegian</option>
												<option value="Persian">Persian</option>
												<option value="Polish">Polish</option>
												<option value="Portuguese">Portuguese</option>
												<option value="Punjabi">Punjabi</option>
												<option value="Quechua">Quechua</option>
												<option value="Romanian">Romanian</option>
												<option value="Russian">Russian</option>
												<option value="Samoan">Samoan</option>
												<option value="Serbian">Serbian</option>
												<option value="Slovak">Slovak</option>
												<option value="Slovenian">Slovenian</option>
												<option value="Spanish">Spanish</option>
												<option value="Swahili">Swahili</option>
												<option value="Swedish ">Swedish </option>
												<option value="Tatar">Tatar</option>
												<option value="Telugu">Telugu</option>
												<option value="Thai">Thai</option>
												<option value="Tibetan">Tibetan</option>
												<option value="Tonga">Tonga</option>
												<option value="Turkish">Turkish</option>
												<option value="Ukranian">Ukranian</option>
												<option value="Urdu">Urdu</option>
												<option value="Uzbek">Uzbek</option>
												<option value="Vietnamese">Vietnamese</option>
												<option value="Welsh">Welsh</option>
												<option value="Xhosa">Xhosa</option>
											</select>
										</div>
										<div class="form-group">
											<label class="control-label">Writing Grade <span class="text-danger">*</span></label>
											<select class="select edit_select_writing" ng-model="editLanguage.writing_skill" required="">
												<option value="">Select Grade</option>
												<option value="Good">Good</option>
												<option value="Average">Average</option>
												<option value="Weak">Weak</option>
											</select>
										</div>	
										<div class="form-group">
											<label class="control-label">Speaking Grade <span class="text-danger">*</span></label>
											<select class="select edit_select_speaking" ng-model="editLanguage.speaking_skill" required="">
												<option value="">Select Grade</option>
												<option value="Good">Good</option>
												<option value="Average">Average</option>
												<option value="Weak">Weak</option>
											</select>
										</div>	
										<button type="submit" class="btn btn-primary">Update</button>
										<input type="button" class="btn btn-default" value="Cancel" data-dismiss="modal">						

									</form>
								</div>
								
							</div>

						</div>
					</div>
					<div id="edit_incentive" class="modal fade" role="dialog">
						<div class="modal-dialog">

							<!-- Modal content-->
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">Edit Incentive</h4>
								</div>
								<div class="modal-body">
									<form name="edit_incentive_form" id="edit_incentive_form" ng-submit="edit_incentive(edit_incentive_form.$valid)">								
										<div class="form-group">
											<label class="control-label">Incentive Name <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editIncentive.incentive_name" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Amount <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editIncentive.amount" required="">
										</div>	
										<button type="submit" class="btn btn-primary">Update</button>
										<input type="button" class="btn btn-default" value="Cancel" data-dismiss="modal">						

									</form>
								</div>
								
							</div>

						</div>
					</div>
					
					<div id="edit_education" class="modal fade" role="dialog">
						<div class="modal-dialog">

							<!-- Modal content-->
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">Edit Qualification</h4>
								</div>
								<div class="modal-body">
									<form name="edit_edu" id="edit_edu" ng-submit="edit_education(edit_edu.$valid)" >										
										<div class="form-group">
											<label class="control-label">Institution Name <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editEdu.institution" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Institution Country <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editEdu.country" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Course/Field <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editEdu.course_field" required="">
										</div>

										<div class="form-group col-md-12">
											<div class="col-xs-6">
												<label class="control-label">Period From <span class="text-danger">*</span></label>
												<div class="cal-icon"><input class="form-control datetimepicker" type="text"  ng-model="editEdu.period_from" required="" id="edit_edu_from"></div>
											</div>
											<div class="col-xs-6">
												<label class="control-label">Period To <span class="text-danger">*</span></label>
												<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editEdu.period_to" required="" id="edit_edu_to"></div>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label">Highest Qualification Attained <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editEdu.highest_qualification_attained" required="">
										</div>
										<button type="submit" class="btn btn-primary">Update</button>
										<input type="button" class="btn btn-default" value="Cancel" data-dismiss="modal">
									</form>
								</div>
								
							</div>

						</div>
					</div>
					<div id="edit_skill" class="modal fade" role="dialog">
						<div class="modal-dialog">

							<!-- Modal content-->
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">Edit Skill</h4>
								</div>
								<div class="modal-body">
									<form name="edit_skil" id="edit_skil" ng-submit="edit_skill(edit_skil.$valid)">										
										<div class="form-group">
											<label class="control-label">Skill Name <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editSkill.skill" required="">
										</div>	
										<div class="form-group">
											<label class="control-label">Skill Application <span class="text-danger">*</span></label>
											<select class="select edit_skill" ng-model="editSkill.level" required="">
												<option value="">Select Level</option>
												<option value="Beginner">Beginner</option>
												<option value="Intermediate">Intermediate</option>
												<option value="Advanced">Advanced</option>
											</select>
										</div>							
										<div class="form-group">
											<label class="control-label">Notes <span class="text-danger"></span></label>
											<textarea class="form-control" ng-model="editSkill.notes"></textarea>
										</div>
										<button type="submit" class="btn btn-primary">Update</button>
										<input type="button" class="btn btn-default" value="Cancel" data-dismiss="modal">
									</form>
								</div>
								
							</div>

						</div>
					</div>
					<div id="edit_experience" class="modal fade" role="dialog">
						<div class="modal-dialog">

							<!-- Modal content-->
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">Edit Experience</h4>
								</div>
								<div class="modal-body">
									<form name="edit_experience_form" id="edit_experience_form" ng-submit="edit_experience(edit_experience_form.$valid)">										
										<div class="form-group">
											<label class="control-label">Company Name <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editExp.company_name" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Company Industry <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editExp.industry" required="">
										</div>

										<div class="form-group col-md-12">
											<div class="col-xs-6">
												<label class="control-label">Period From <span class="text-danger">*</span></label>
												<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editExp.period_from" required="" id="edit_exp_from"></div>
											</div>
											<div class="col-xs-6">
												<label class="control-label">Period To <span class="text-danger">*</span></label>
												<div class="cal-icon"><input class="form-control datetimepicker" type="text" ng-model="editExp.period_to" required="" id="edit_exp_to"></div>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label">Position <span class="text-danger">*</span></label>
											<input class="form-control" type="text"  ng-model="editExp.position" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Basic Salary <span class="text-danger">*</span></label>
											<input class="form-control" type="number" ng-model="editExp.basic_salary" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Bonus <span class="text-danger">*</span></label>
											<input class="form-control" type="number" ng-model="editExp.bonus" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Allowance (If any)</label>
											<input class="form-control" type="number" ng-model="editExp.allowance">
										</div>
										<button type="submit" class="btn btn-primary">Update</button>
										<input type="button" class="btn btn-default" value="Cancel" data-dismiss="modal">
									</form>
								</div>
								
							</div>

						</div>
					</div>
					<div id="edit_allowance" class="modal fade" role="dialog">
						<div class="modal-dialog">

							<!-- Modal content-->
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">Edit Allowance</h4>
								</div>
								<div class="modal-body">
									<form name="edit_allowance_form" id="edit_allowance_form" ng-submit="edit_allowance(edit_allowance_form.$valid)">								
										<div class="form-group">
											<label class="control-label">Allowance Name <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editAllowance.allowance_name" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Amount <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editAllowance.amount" required="">
										</div>	
										<button type="submit" class="btn btn-primary">Update</button>
										<input type="button" class="btn btn-default" value="Cancel" data-dismiss="modal">						

									</form>
								</div>
								
							</div>

						</div>
					</div>
					<div id="edit_family" class="modal fade" role="dialog">
						<div class="modal-dialog">

							<!-- Modal content-->
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">Edit Family Member</h4>
								</div>
								<div class="modal-body">
									<form name="edit_family_form" id="edit_family_form" ng-submit="edit_family(edit_family_form.$valid)">										
										<div class="form-group">
											<label class="control-label">First Name <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editFamily.first_name" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Last Name <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editFamily.last_name" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Relation <span class="text-danger">*</span></label>
											<select class="select edit_family_relation" ng-model="editFamily.relation"  required="">
												<option value="">Select Relation</option>
												<option value="Father">Father</option>
												<option value="Mother">Mother</option>
												<option value="Sibling">Sibling</option>
												<option value="Spouse">Spouse</option>
												<option value="Child">Child</option>
												<option value="Guardian">Guardian</option>
												<option value="Other">Other</option>
											</select>
										</div>
										<div class="form-group">
											<label class="control-label">Age <span class="text-danger">*</span></label>
											<input class="form-control" type="number" ng-model="editFamily.age" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Mobile <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editFamily.mobile" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Job <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editFamily.job" required="">
										</div>
										<button type="submit" class="btn btn-primary">Update</button>
										<input type="button" class="btn btn-default" value="Cancel" data-dismiss="modal">


									</form>
								</div>
								
							</div>

						</div>
					</div>
					<div id="edit_contact" class="modal fade" role="dialog">
						<div class="modal-dialog">

							<!-- Modal content-->
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal">&times;</button>
									<h4 class="modal-title">Edit Emergency Contact</h4>
								</div>
								<div class="modal-body">
									<form name="edit_em_contacts" id="edit_em_contacts" ng-submit="edit_contact(edit_em_contacts.$valid)">										
										<div class="form-group">
											<label class="control-label">First Name <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editContact.first_name" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Last Name <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editContact.last_name" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Relation <span class="text-danger">*</span></label>
											<select class="select edit_contact_relation" ng-model="editContact.relation"  required="">
												<option value="">Select Relation</option>
												<option value="Father">Father</option>
												<option value="Mother">Mother</option>
												<option value="Sibling">Sibling</option>
												<option value="Spouse">Spouse</option>
												<option value="Child">Child</option>
												<option value="Guardian">Guardian</option>
												<option value="Other">Other</option>
											</select>
										</div>
										<div class="form-group">
											<label class="control-label">Email <span class="text-danger">*</span></label>
											<input class="form-control" type="email" ng-model="editContact.email" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Landline <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editContact.telephone" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Office No. <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editContact.office_no" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Mobile <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editContact.mobile" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Address <span class="text-danger">*</span></label>
											<textarea class="form-control" ng-model="editContact.address" required=""></textarea>
										</div>
										<div class="form-group">
											<label class="control-label">City <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editContact.address_city" required="">
										</div>
										<div class="form-group">
											<label class="control-label">State <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editContact.address_state" required="">
										</div>
										<div class="form-group">
											<label class="control-label">Postcode <span class="text-danger">*</span></label>
											<input class="form-control" type="text" ng-model="editContact.address_postcode" required="">
										</div>
										<button type="submit" class="btn btn-primary">Update</button>
										<input type="button" class="btn btn-default" value="Cancel" data-dismiss="modal">



									</form>
								</div>
								
							</div>

						</div>
					</div>
					<div class="text-center">
						<input type="button" class="btn btn-primary" value="Add Family Member" ng-show="!show_add_family" ng-click="show_family_form()">
					</div>
					<form name="add_family_form" id="add_family_form" ng-submit="add_family(add_family_form.$valid)" ng-show="show_add_family">										
						<div class="form-group">
							<label class="control-label">First Name <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addFamily.first_name" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Last Name <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addFamily.last_name" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Relation <span class="text-danger">*</span></label>
							<select class="select family_relation" ng-model="addFamily.relation"  required="">
								<option value="">Select Relation</option>
								<option value="Father">Father</option>
								<option value="Mother">Mother</option>
								<option value="Sibling">Sibling</option>
								<option value="Spouse">Spouse</option>
								<option value="Child">Child</option>
								<option value="Guardian">Guardian</option>
								<option value="Other">Other</option>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Age <span class="text-danger">*</span></label>
							<input class="form-control" type="number" ng-model="addFamily.age" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Mobile <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addFamily.mobile" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Job <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addFamily.job" required="">
						</div>
						<button type="submit" class="btn btn-primary">Save</button>
						<input type="button" class="btn btn-default" value="Cancel" ng-click="hide_add_fmily()">


					</form>


					
				</div>
			</div>

		</div>

		

		<div class="row">
			<div class="col-md-6">
				<div class="card-box m-b-0">
					<h3 class="card-title">Languages</h3>
					<div class="list-group" ng-show="languages.length != 0">								 
						<div class="media list-group-item main_div" ng-repeat="l in languages">
							
							<div class="media-body">
								<h4 class="media-heading">{{l.language}}<span class="pull-right main_btn" style="display: none;"><button class="btn btn-info" data-toggle="modal" data-target="#edit_language" ng-click="editLanguageData(l.id)"><i class="fa fa-pencil"></i></button> <button class="btn btn-danger" data-toggle="modal" data-target="#delete_modal" ng-click="setDelete('language',l.id)"><i class="fa fa-trash"></i></button></span><button class="btn btn-info pull-right" style="visibility:hidden">i</button></h4>
								<table width="100%">
									<tr>
										<td width="25%"><strong>Writing</strong></td>
										<td>{{l.writing_skill}}</td>
									</tr>
									<tr>
										<td width="25%"><strong>Speaking</strong></td>
										<td>{{l.speaking_skill}}</td>
									</tr>
									
								</table>
							</div>
						</div>


					</div>
					<div class="text-center">
						<input type="button" class="btn btn-primary" value="Add Language" ng-show="!show_add_language" ng-click="show_language_form()">
					</div>
					<form name="add_language_form" id="add_language_form" ng-submit="add_language(add_language_form.$valid)" ng-show="show_add_language">								
						<div class="form-group">
							<label class="control-label">Choose Language <span class="text-danger">*</span></label>
							<select class="select language_select" ng-model="addLanguage.language" required="">
								<option value="">Select Language</option>
								<option value="Malay">Malay</option>
								<option value="English">English</option>
								<option value="Chinese (Mandarin)">Chinese (Mandarin)</option>
								<option value="Tamil">Tamil</option>
								<option value="Afrikanns">Afrikanns</option>
								<option value="Albanian">Albanian</option>
								<option value="Arabic">Arabic</option>
								<option value="Armenian">Armenian</option>
								<option value="Basque">Basque</option>
								<option value="Bengali">Bengali</option>
								<option value="Bulgarian">Bulgarian</option>
								<option value="Catalan">Catalan</option>
								<option value="Cambodian">Cambodian</option>
								<option value="Croation">Croation</option>
								<option value="Czech">Czech</option>
								<option value="Danish">Danish</option>
								<option value="Dutch">Dutch</option>
								<option value="Estonian">Estonian</option>
								<option value="Fiji">Fiji</option>
								<option value="Finnish">Finnish</option>
								<option value="French">French</option>
								<option value="Georgian">Georgian</option>
								<option value="German">German</option>
								<option value="Greek">Greek</option>
								<option value="Gujarati">Gujarati</option>
								<option value="Hebrew">Hebrew</option>
								<option value="Hindi">Hindi</option>
								<option value="Hungarian">Hungarian</option>
								<option value="Icelandic">Icelandic</option>
								<option value="Indonesian">Indonesian</option>
								<option value="Irish">Irish</option>
								<option value="Italian">Italian</option>
								<option value="Japanese">Japanese</option>
								<option value="Javanese">Javanese</option>
								<option value="Korean">Korean</option>
								<option value="Latin">Latin</option>
								<option value="Latvian">Latvian</option>
								<option value="Lithuanian">Lithuanian</option>
								<option value="Macedonian">Macedonian</option>
								<option value="Malayalam">Malayalam</option>
								<option value="Maltese">Maltese</option>
								<option value="Maori">Maori</option>
								<option value="Marathi">Marathi</option>
								<option value="Mongolian">Mongolian</option>
								<option value="Nepali">Nepali</option>
								<option value="Norwegian">Norwegian</option>
								<option value="Persian">Persian</option>
								<option value="Polish">Polish</option>
								<option value="Portuguese">Portuguese</option>
								<option value="Punjabi">Punjabi</option>
								<option value="Quechua">Quechua</option>
								<option value="Romanian">Romanian</option>
								<option value="Russian">Russian</option>
								<option value="Samoan">Samoan</option>
								<option value="Serbian">Serbian</option>
								<option value="Slovak">Slovak</option>
								<option value="Slovenian">Slovenian</option>
								<option value="Spanish">Spanish</option>
								<option value="Swahili">Swahili</option>
								<option value="Swedish ">Swedish </option>
								<option value="Tatar">Tatar</option>
								<option value="Telugu">Telugu</option>
								<option value="Thai">Thai</option>
								<option value="Tibetan">Tibetan</option>
								<option value="Tonga">Tonga</option>
								<option value="Turkish">Turkish</option>
								<option value="Ukranian">Ukranian</option>
								<option value="Urdu">Urdu</option>
								<option value="Uzbek">Uzbek</option>
								<option value="Vietnamese">Vietnamese</option>
								<option value="Welsh">Welsh</option>
								<option value="Xhosa">Xhosa</option>
							</select>
						</div>
						<div class="form-group">
							<label class="control-label">Writing Grade <span class="text-danger">*</span></label>
							<select class="select select_writing" ng-model="addLanguage.writing_skill" required="">
								<option value="">Select Grade</option>
								<option value="Good">Good</option>
								<option value="Average">Average</option>
								<option value="Weak">Weak</option>
							</select>
						</div>	
						<div class="form-group">
							<label class="control-label">Speaking Grade <span class="text-danger">*</span></label>
							<select class="select select_speaking" ng-model="addLanguage.speaking_skill" required="">
								<option value="">Select Grade</option>
								<option value="Good">Good</option>
								<option value="Average">Average</option>
								<option value="Weak">Weak</option>
							</select>
						</div>	
						<button type="submit" class="btn btn-primary">Save</button>
						<input type="button" class="btn btn-default" value="Cancel" ng-click="hide_add_language()">						

					</form>


				</div>
			</div>
			<div class="col-md-6">
				<div class="card-box m-b-0">
					<h3 class="card-title">Incentives</h3>
					<div class="list-group" ng-show="incentives.length != 0">								 
						<div class="media list-group-item main_div" ng-repeat="i in incentives">
							
							<div class="media-body">
								<h4 class="media-heading">{{i.incentive_name}}<span class="pull-right main_btn" style="display: none;"><button class="btn btn-info" data-toggle="modal" data-target="#edit_incentive" ng-click="editIncentiveData(i.id)"><i class="fa fa-pencil"></i></button> <button class="btn btn-danger" data-toggle="modal" data-target="#delete_modal" ng-click="setDelete('incentive',i.id)"><i class="fa fa-trash"></i></button></span><button class="btn btn-info pull-right" style="visibility:hidden">i</button></h4>
								<table width="100%">
									<tr>
										<td width="25%"><strong>Amount</strong></td>
										<td>{{i.amount}}</td>
									</tr>
									
								</table>
							</div>
						</div>


					</div>
					<div class="text-center">
						<input type="button" class="btn btn-primary" value="Add Incentive" ng-show="!show_add_incentive" ng-click="show_incentive_form()">
					</div>
					<form name="add_incentive_form" id="add_incentive_form" ng-submit="add_incentive(add_incentive_form.$valid)" ng-show="show_add_incentive">								
						<div class="form-group">
							<label class="control-label">Incentive Name <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addIncentive.incentive_name" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Amount <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addIncentive.amount" required="">
						</div>	
						<button type="submit" class="btn btn-primary">Save</button>
						<input type="button" class="btn btn-default" value="Cancel" ng-click="hide_add_incentive()">						

					</form>


				</div>
			</div>
		</div>
		<br />
		<div class="row">
			<div class="col-md-6">
				<div class="card-box m-b-0">
					<h3 class="card-title">Allowances</h3>
					<div class="list-group" ng-show="allowances.length != 0">								 
						<div class="media list-group-item main_div" ng-repeat="a in allowances">
							
							<div class="media-body">
								<h4 class="media-heading">{{a.allowance_name}}<span class="pull-right main_btn" style="display: none;"><button class="btn btn-info" data-toggle="modal" data-target="#edit_allowance" ng-click="editAllowanceData(a.id)"><i class="fa fa-pencil"></i></button> <button class="btn btn-danger" data-toggle="modal" data-target="#delete_modal" ng-click="setDelete('allowance',a.id)"><i class="fa fa-trash"></i></button></span><button class="btn btn-info pull-right" style="visibility:hidden">i</button></h4>
								<table width="100%">
									<tr>
										<td width="25%"><strong>Amount</strong></td>
										<td>{{a.amount}}</td>
									</tr>
									
								</table>
							</div>
						</div>


					</div>
					<div class="text-center">
						<input type="button" class="btn btn-primary" value="Add Allowance" ng-show="!show_add_allowance" ng-click="show_allowance_form()">
					</div>
					<form name="add_allowance_form" id="add_allowance_form" ng-submit="add_allowance(add_allowance_form.$valid)" ng-show="show_add_allowance">								
						<div class="form-group">
							<label class="control-label">Allowance Name <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addAllowance.allowance_name" required="">
						</div>
						<div class="form-group">
							<label class="control-label">Amount <span class="text-danger">*</span></label>
							<input class="form-control" type="text" ng-model="addAllowance.amount" required="">
						</div>	
						<button type="submit" class="btn btn-primary">Save</button>
						<input type="button" class="btn btn-default" value="Cancel" ng-click="hide_add_allowance()">						

					</form>


				</div>
			</div>
			<div class="col-md-6">
				<div class="card-box m-b-0">
					<h3 class="card-title">Outlet Transfers</h3>
					<div class="list-group" ng-show="transfers.length != 0">						
							 <!-- 
						<div class="media list-group-item main_div" ng-repeat="t in transfers">
							
							<div class="media-body">
								{{t.transfer_reason}}
							</div>
						</div> -->
						<table class="table table-striped">
							<thead>
								<tr>
									<th>From</th>
									<th>To</th>
									<th>Date</th>
									<th>Reason</th>
								</tr>
							</thead>
							<tbody>
								<tr ng-repeat="t in transfers">
									<td>{{t.old_name}}</td>
									<td>{{t.new_name}}</td>
									<td>{{t.transfer_date}}</td>
									<td>{{t.transfer_reason}}</td>
								</tr>
							</tbody>
						</table> 
					</div>
				</div>
			</div>
		</div>
	</div>

</div>
<<script src="<?php echo base_url();?>assets/js/profile.js?v=1.1" type="text/javascript"></script>