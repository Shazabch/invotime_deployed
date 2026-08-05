<div class="page-wrapper">
    <div class="content container-fluid"> 
        <div class="page-content-wrapperx ">
            <div class="containerx">
                <div class="row">
                	<div class="col-sm-12">            
                        <div class="panel panel-primary">
                            <div class="panel-body">
                              <!-- <h4 class="page-title"><?php echo $pageTitle ?></h4> -->
                              <h4 class="page-title">Import Employees Data</h4>

                              <?php 

                              $permissions_level = get_user()["permissions_level"];

                                if($permissions_level == "Company"){

                                  echo '<p style="color:blue">You can import employees to any branch under <b>'.get_user()["company_name"].'</b></p>';

                                }

                                if($permissions_level == "Outlet"){

                                  echo '<p style="color:blue">You can import employees to branch <b>'.get_user()["branch_name"].'</b> only</p>';

                                }
                                
                              
                                            
                            ?>

                                <!-- <h4 class="m-t-0">Your Title</h4> -->
                                <div>
                                	You can download the sample excel template file <a target="_blank" href="<?php echo base_url() ?>assets/import_template.xlsx">here</a>, convert all the excel sheets into separate CSV files and upload in the relevant section below. <br/> <b>Note:</b> You must import "Employees Basic Info" CSV before importing other data. <br/>
                                  <p style="color:blue"><b>Considerations:</b> <br/>*Use dd-mm-yyyy format for date fields, example 31-12-2016 <br/>*Red columns in the template indicate that the field is required <br/>*Basic Info CSV > employment_type should be full_time or part_time</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <style type="text/css">
                        .collapse{
                            max-height:250px;
                            overflow:scroll;
                            display:none;
                            color:#f62d51;
                        }

                        .collapse table{
                            color:#f62d51;
                        }

                        .collapse hr{
                          margin-top: 5px;
                          margin-bottom: 5px;
                        }
                    </style>
                    <div class="col-md-4">            
                        <div class="panel panel-primary">
                            <div class="panel-body">
                              <!-- <h4 class="page-title"><?php echo $pageTitle ?></h4> -->
                              <h4 class="page-title">Employees Basic Info</h4>
                                <!-- <h4 class="m-t-0">Your Title</h4> -->
                                <div id="div-basic-info">
                                	<input data-file="basic-info" type="file" name="file1"/>

                                	<p style="font-weight:bold;margin-top:10px" class="msg"></p>

                                	<button style="display:none;margin-top:5px" class="btn-import btn btn-primary btn-sm">Import</button>

                                	<button style="display:none;margin-top:5px"  class="btn-invalid btn btn-danger btn-xs">View Details <i style="font-weight:bold" class="fa fa-angle-down"></i></button>
                									<div class="collapse">

                                                    

                									
                									</div>


                                </div>
                            </div>
                        </div>

                        <div class="panel panel-primary">
                            <div class="panel-body">
                              <!-- <h4 class="page-title"><?php echo $pageTitle ?></h4> -->
                              <h4 class="page-title">Clockings</h4>
                                <!-- <h4 class="m-t-0">Your Title</h4> -->
                                <div>
                                  <input data-file="manual_clockings_new" type="file" name="file1"/>

                                  <p style="font-weight:bold;margin-top:10px" class="msg"></p>

                                  <button style="display:none;margin-top:5px" class="btn-import btn btn-primary btn-sm">Import</button>

                                  <button style="display:none;margin-top:5px"  class="btn-invalid btn btn-danger btn-xs">View Details <i style="font-weight:bold" class="fa fa-angle-down"></i></button>
                                  <div class="collapse">

                                  </div>


                                </div>
                            </div>
                        </div>

                        <div class="panel panel-primary">
                            <div class="panel-body">
                              <!-- <h4 class="page-title"><?php echo $pageTitle ?></h4> -->
                              <h4 class="page-title">Allowances</h4>
                                <!-- <h4 class="m-t-0">Your Title</h4> -->
                                <div>

                                	<input data-file="allowances" type="file" name="file2"/>

                                	<p style="font-weight:bold;margin-top:10px" class="msg"></p>

                                	<button style="display:none;margin-top:5px" class="btn-import btn btn-primary btn-sm">Import</button>

                                	<button style="display:none;margin-top:5px"  class="btn-invalid btn btn-danger btn-xs">View Details <i style="font-weight:bold" class="fa fa-angle-down"></i></button>
                                    <div class="collapse"></div>

                                </div>
                            </div>
                        </div>

                        <div class="panel panel-primary">
                            <div class="panel-body">
                              <!-- <h4 class="page-title"><?php echo $pageTitle ?></h4> -->
                              <h4 class="page-title">Incentives</h4>
                                <!-- <h4 class="m-t-0">Your Title</h4> -->
                                <div>
                                  <input data-file="incentives" type="file" name="file2"/>

                                  <p style="font-weight:bold;margin-top:10px" class="msg"></p>

                                  <button style="display:none;margin-top:5px" class="btn-import btn btn-primary btn-sm">Import</button>

                                  <button style="display:none;margin-top:5px"  class="btn-invalid btn btn-danger btn-xs">View Details <i style="font-weight:bold" class="fa fa-angle-down"></i></button>
                                    <div class="collapse"></div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">            
                        <div class="panel panel-primary">
                            <div class="panel-body">
                              <!-- <h4 class="page-title"><?php echo $pageTitle ?></h4> -->
                              <h4 class="page-title">Emergency Contacts</h4>
                                <!-- <h4 class="m-t-0">Your Title</h4> -->
                                <div>
                                  <input data-file="emergency_contacts" type="file" name="file2"/>

                                  <p style="font-weight:bold;margin-top:10px" class="msg"></p>

                                  <button style="display:none;margin-top:5px" class="btn-import btn btn-primary btn-sm">Import</button>

                                  <button style="display:none;margin-top:5px"  class="btn-invalid btn btn-danger btn-xs">View Details <i style="font-weight:bold" class="fa fa-angle-down"></i></button>
                                    <div class="collapse"></div>

                                </div>
                            </div>
                        </div>

                        <!-- <div class="panel panel-primary">
                            <div class="panel-body">
                              <h4 class="page-title">OLD Clockings</h4>
                                <div>
                                  <input data-file="manual_clockings" type="file" name="file1"/>

                                  <p style="font-weight:bold;margin-top:10px" class="msg"></p>

                                  <button style="display:none;margin-top:5px" class="btn-import btn btn-primary btn-sm">Import</button>

                                  <button style="display:none;margin-top:5px"  class="btn-invalid btn btn-danger btn-xs">View Details <i style="font-weight:bold" class="fa fa-angle-down"></i></button>
                                  <div class="collapse">

                                  </div>
                                </div>
                            </div>
                        </div> -->

                        <div class="panel panel-primary">
                            <div class="panel-body">
                              <!-- <h4 class="page-title"><?php echo $pageTitle ?></h4> -->
                              <h4 class="page-title">Family Members</h4>
                                <!-- <h4 class="m-t-0">Your Title</h4> -->
                                <div>
                                  <input data-file="family_members" type="file" name="file2"/>

                                  <p style="font-weight:bold;margin-top:10px" class="msg"></p>

                                  <button style="display:none;margin-top:5px" class="btn-import btn btn-primary btn-sm">Import</button>

                                  <button style="display:none;margin-top:5px"  class="btn-invalid btn btn-danger btn-xs">View Details <i style="font-weight:bold" class="fa fa-angle-down"></i></button>
                                    <div class="collapse"></div>

                                </div>
                            </div>
                        </div>

                        <div class="panel panel-primary">
                            <div class="panel-body">
                              <!-- <h4 class="page-title"><?php echo $pageTitle ?></h4> -->
                              <h4 class="page-title">Qualification</h4>
                                <!-- <h4 class="m-t-0">Your Title</h4> -->
                                <div>
                                   <input data-file="qualifications" type="file" name="file2"/>

                                  <p style="font-weight:bold;margin-top:10px" class="msg"></p>

                                  <button style="display:none;margin-top:5px" class="btn-import btn btn-primary btn-sm">Import</button>

                                  <button style="display:none;margin-top:5px"  class="btn-invalid btn btn-danger btn-xs">View Details <i style="font-weight:bold" class="fa fa-angle-down"></i></button>
                                    <div class="collapse"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">            
                        <div class="panel panel-primary">
                            <div class="panel-body">
                              <!-- <h4 class="page-title"><?php echo $pageTitle ?></h4> -->
                              <h4 class="page-title">Languages</h4>
                                <!-- <h4 class="m-t-0">Your Title</h4> -->
                                <div>
                                   <input data-file="languages" type="file" name="file2"/>

                                  <p style="font-weight:bold;margin-top:10px" class="msg"></p>

                                  <button style="display:none;margin-top:5px" class="btn-import btn btn-primary btn-sm">Import</button>

                                  <button style="display:none;margin-top:5px"  class="btn-invalid btn btn-danger btn-xs">View Details <i style="font-weight:bold" class="fa fa-angle-down"></i></button>
                                    <div class="collapse"></div>

                                </div>
                            </div>
                        </div>

                        <div class="panel panel-primary">
                            <div class="panel-body">
                              <!-- <h4 class="page-title"><?php echo $pageTitle ?></h4> -->
                              <h4 class="page-title">Skills</h4>
                                <!-- <h4 class="m-t-0">Your Title</h4> -->
                                <div>
                                   <input data-file="skills" type="file" name="file2"/>

                                  <p style="font-weight:bold;margin-top:10px" class="msg"></p>

                                  <button style="display:none;margin-top:5px" class="btn-import btn btn-primary btn-sm">Import</button>

                                  <button style="display:none;margin-top:5px"  class="btn-invalid btn btn-danger btn-xs">View Details <i style="font-weight:bold" class="fa fa-angle-down"></i></button>
                                    <div class="collapse"></div>

                                </div>
                            </div>
                        </div>

                        <div class="panel panel-primary">
                            <div class="panel-body">
                              <!-- <h4 class="page-title"><?php echo $pageTitle ?></h4> -->
                              <h4 class="page-title">Employement History</h4>
                                <!-- <h4 class="m-t-0">Your Title</h4> -->
                                <div>
                                  <input data-file="employment_history" type="file" name="file2"/>

                                  <p style="font-weight:bold;margin-top:10px" class="msg"></p>

                                  <button style="display:none;margin-top:5px" class="btn-import btn btn-primary btn-sm">Import</button>

                                  <button style="display:none;margin-top:5px"  class="btn-invalid btn btn-danger btn-xs">View Details <i style="font-weight:bold" class="fa fa-angle-down"></i></button>
                                    <div class="collapse"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    

                </div>

                <script type="text/javascript">
                	

                    function validateCSV(data,fieldsToValidate){

                        var validation_errors = [];
                        var fields = fieldsToValidate.split(",");

                        $.each(data, function(i, emp) {
                            $.each(fields, function(j, f){
                                if(!emp[f]){
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
							      complete: function(results) {
							        console.log(results);

							        
					        		//obj.siblings(".msg").html("Invalid records found");

                      var import_base_url = js_base_url;
					        		var url = '';
                      var _fields_to_validate = '';

                      
						        

							        if(data_file == "basic-info"){
                         url = import_base_url + 'import/import_basic_info'
							        	_fields_to_validate = 'employee_id,first_name,department,position,role,outlet,sex'

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

                      var validation_errors = validateCSV(results.data,_fields_to_validate);

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
								            data      : {'json':results.data}, //Forms name
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
								// obj.siblings(".btn-import").hide();
        //                         obj.siblings(".btn-invalid").hide();
        //                         obj.siblings(".msg").html("");
        //                         obj.siblings(".collapse").html("");
								// console.log("no file selected");
							}
						
						});

					 //  	$('input[type=file]').parse({
						// 	config: {
						// 		complete: function(results) {
						// 			console.log("Finished:", results.data);
						// 		}
						// 		// base config to use for each file
						// 	},
						// 	before: function(file, inputElem)
						// 	{
						// 		console.log("before");
						// 		// executed before parsing each file begins;
						// 		// what you return here controls the flow
						// 	},
						// 	error: function(err, file, inputElem, reason)
						// 	{
						// 		console.log("error");
						// 		// executed if an error occurs while loading the file,
						// 		// or if before callback aborted for some reason
						// 	},
						// 	complete: function()
						// 	{
						// 		console.log("complete");
						// 		// executed after all files are complete
						// 	}
						// });


					});

                </script>
            </div>
           </div>
    </div>
</div>