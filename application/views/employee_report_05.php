<div class="page-wrapper">
            <div class="content container-fluid">
                <div class="page-content-wrapperx ">
                    <div class="containerx">
                        <div class="row">
                            <div class="col-sm-12">
                              
                                <div class="panel panel-primary">
                                    <div class="panel-body">
                                      <h4 class="page-title"><?php echo $pageTitle ?> (Clocking) <a title="Employee Summary" data-toggle="tooltip" href="<?php echo site_url() ?>summary/view/<?php echo $emp->id ?>?<?php echo "from=01%2F" . $_REQUEST['month'] . "%2F".$_REQUEST['year'] ?>&<?php echo "to=". last_day_of_month($_REQUEST['month']) ."%2F" . $_REQUEST['month'] . "%2F".$_REQUEST['year'] ?>" class="btn btn-default"><i style="font-size:20px" class="fa fa-address-card"></i></a></h4>
                                        <!-- <h4 class="m-t-0">Your Title</h4> -->
                                        <div>
                                            
                                            <form action="<?php echo site_url() ?>overview/employee_report/<?php echo $emp->id ?>" method="get">
                                            

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                  <label for="sel1">Employee</label>
                                                  <select class="form-control apply-select2" id="emp" name="emp">
                                                    <?php foreach ($employees_dropdown as $employee): ?>
                                                        <option <?php echo ($employee->id == $selected_emp_id) ? 'selected' : '' ?> value="<?php echo $employee->id ?>"><?php echo $employee->special_id . " - " . $employee->first_name ?></option>
                                                     <?php endforeach; ?>

                                                  </select>
                                                </div>
                                                
                                            </div>


                                            <div class="col-md-2">
                                                <div class="form-group">
                                                  <label for="sel1">Month</label>
                                                  <select class="form-control" id="sel1" name="month">
                                                    <option <?php echo ('01' == $selected_month) ? 'selected' : '' ?> value="01">January</option>
                                                    <option <?php echo ('02' == $selected_month) ? 'selected' : '' ?> value="02">February</option>
                                                    <option <?php echo ('03' == $selected_month) ? 'selected' : '' ?> value="03">March</option>
                                                    <option <?php echo ('04' == $selected_month) ? 'selected' : '' ?> value="04">April</option>
                                                    <option <?php echo ('05' == $selected_month) ? 'selected' : '' ?> value="05">May</option>
                                                    <option <?php echo ('06' == $selected_month) ? 'selected' : '' ?> value="06">June</option>
                                                    <option <?php echo ('07' == $selected_month) ? 'selected' : '' ?> value="07">July</option>
                                                    <option <?php echo ('08' == $selected_month) ? 'selected' : '' ?> value="08">August</option>
                                                    <option <?php echo ('09' == $selected_month) ? 'selected' : '' ?> value="09">September</option>
                                                    <option <?php echo ('10' == $selected_month) ? 'selected' : '' ?> value="10">October</option>
                                                    <option <?php echo ('11' == $selected_month) ? 'selected' : '' ?> value="11">November</option>
                                                    <option <?php echo ('12' == $selected_month) ? 'selected' : '' ?> value="12">December</option>
                                                  </select>
                                                </div>                                               
                                            </div>

                                            <div class="col-md-2">
      <div class="form-group">
        <label for="sel1">Year</label>
        <select class="form-control" id="sel1" name="year">
          <option <?php echo ('2019' == $selected_year) ? 'selected' : '' ?> value="2019">2019</option>
          <option <?php echo ('2020' == $selected_year) ? 'selected' : '' ?> value="2020">2020</option>
          <option <?php echo ('2021' == $selected_year) ? 'selected' : '' ?> value="2021">2021</option>
          <option <?php echo ('2022' == $selected_year) ? 'selected' : '' ?> value="2022">2022</option>
          <option <?php echo ('2023' == $selected_year) ? 'selected' : '' ?> value="2023">2023</option>
          <option <?php echo ('2024' == $selected_year) ? 'selected' : '' ?> value="2024">2024</option>
          <option <?php echo ('2025' == $selected_year) ? 'selected' : '' ?> value="2025">2025</option>
        </select>
      </div>                                               
  </div>

                                            <div class="col-md-3">
                                                <label for="sel1">&nbsp;</label>
                                                <button class="btn btn-primary btn-block">Filter</button>
                                                
                                            </div>
                                        </form>
                                          

                                            
                                        <div class="col-md-12 freeze-table">
                                                <table class="table table-striped">
                                                    <thead>
                                                      <tr>
                                                        <th>Date</th>
                                                        <th>Hours</th>
                                                        <th>Clock In</th>
                                                        <th>Clock Out</th>
                                                        <th>Shift</th>
                                              
                                                        <th></th>
                                                         <th>Late Reason</th>
                                                         <th>Remark</th>
                                                        <th></th>
                                                      </tr>
                                                    </thead>
                                                    <tbody>

                                                <?php foreach ($shift_days as $day): ?>
                                                      <tr>
                                                        <td><strong><?php echo beautify_date($day->date) ?></strong></td>


                                                        <?php if($day->clock_in != NULL): ?>


                                                        <td><?php echo $day->total_time ?></td>
                                                        <td><?php echo beautify_date($day->clock_in) . " " . beautify_time($day->clock_in) ?> <a class='my-tool-tip' data-toggle="tooltip" data-html="true" data-placement="top" title="<?php echo beautify_date($day->clock_in) . " " . beautify_time_am_pm($day->clock_in) ?>"> <!-- The class CANNOT be tooltip... -->
                <i class='glyphicon glyphicon-info-sign'></i>
            </a></td>
                                                        <td><?php echo beautify_date($day->clock_out) . " " . beautify_time($day->clock_out) ?><?php if($day->clock_out != ""){ ?><a class='my-tool-tip' data-toggle="tooltip" data-html="true" data-placement="top" title="<?php echo beautify_date($day->clock_out) . " " . beautify_time_am_pm($day->clock_out) ?>"> <!-- The class CANNOT be tooltip... -->
                <i class='glyphicon glyphicon-info-sign'></i>
            </a><?php } ?><?php if($day->auto_clock_out == 'Yes'){ echo '<span style="font-weight:bold">Auto</span>'; } ?>

          </td>
                                                        <td><?php echo $day->shifts ?>
          <?php if($day->shift_is_leave != "yes"): ?>
            <a class='my-tool-tip' data-toggle="tooltip" data-html="true" data-placement="top" title="Start Time: <?php echo beautify_time($day->shift_start_time) ?> (<?php echo beautify_time_am_pm($day->shift_start_time) ?>) <br /> End Time: <?php echo beautify_time($day->shift_end_time) ?> (<?php echo beautify_time_am_pm($day->shift_end_time) ?>) <br /> Grace Time: <?php echo beautify_time($day->shift_grace_time) ?> (<?php echo beautify_time_am_pm($day->shift_grace_time) ?>)"> <!-- The class CANNOT be tooltip... -->
                <i class='glyphicon glyphicon-info-sign'></i>
            </a>
          <?php endif; ?>

                                                        </td>
                                                        <td>

                                                            <?php
                                                                // var_dump($day->shift_grace_time);
                                                                // var_dump($day->clock_in);
                                                                // die();
                                                            ?>

                                                            <?php if($day->shift_is_leave == "yes"): ?>

                                                            <?php elseif(beautify_time($day->clock_in) < beautify_time($day->shift_start_time)): ?>

                                                                <span class="label label-success">Early</span>

                                                             <?php elseif(beautify_time($day->clock_in) > beautify_time($day->shift_grace_time)): ?>

                                                                <?php 

                                                                 // var_dump($day->clock_in);
                                                                 // var_dump($day->shift_grace_time);
                                                                 // die();



                                                                ?>

                                                                <span class="label label-warning">Late</span>
                                                                


                                                            <?php else: ?>

                                                                <span class="label label-default">On time</span>

                                                            <?php endif; ?>
                                                            

                                                        </td>
                                                        <td>
                                                            <?php if(beautify_time($day->clock_in) > beautify_time($day->shift_grace_time)): ?>
                                                                
                                                            <button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" id="btn-reason-<?php echo $day->id ?>" data-reason="<?php echo $day->reason ?>" data-id="<?php echo $day->id ?>" type="button" class="btn btn-default btn-xs" aria-label="Add reason" data-toggle="modal" data-target="#reason-modal">
                                                                  <!-- <span class="fa fa-comment" aria-hidden="true"></span> -->
                                                                  <?php if(empty($day->reason)): ?>

                                                                    <span class="fa fa-plus" aria-hidden="true"></span>

                                                                  <?php else: ?>

                                                                    <?php echo $day->reason ?>

                                                                  <?php endif; ?>

                                                            </button>

                                                        <?php endif; ?>

                                                        </td>
                                                        <td>
                                                          <button style="font-size:11px;max-width:100px;overflow:hidden;white-space: normal;" id="btn-remark-<?php echo $day->id ?>" data-remark="<?php echo $day->remark ?>" data-id="<?php echo $day->id ?>" type="button" class="btn btn-default btn-xs" aria-label="Add remark" data-toggle="modal" data-target="#remark-modal">
                                                                  <!-- <span class="fa fa-comment" aria-hidden="true"></span> -->
                                                                  <?php if(empty($day->remark)): ?>

                                                                    <span class="fa fa-plus" aria-hidden="true"></span>

                                                                  <?php else: ?>

                                                                    <?php echo $day->remark ?>

                                                                  <?php endif; ?>

                                                            </button>
                                                        </td>
                                                        
                                                        <td>
                                                           <?php if($day->hours != NULL): ?>
                                                            <button data-date="<?php echo $day->date ?>" data-emp_id="<?php echo $emp->id ?>" data-toggle="modal" data-target="#myModal" class="btn btn-sm btn-primary btn-view-modal">Details</button>
                                                           <?php endif; ?>

                                                        </td>
                                                        <?php else: ?>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td><?php echo $day->name ?></td>
                                                        <td>
                                                          <?php

                                                          if($day->shift_is_leave == 'yes'){

                                                            if($day->shift_is_paid == 'yes'){
                                                              echo '<span style="background-color:blue" class="label">Paid Leave</span>';
                                                            }

                                                            if($day->shift_is_paid == 'no'){
                                                               echo '<span style="background-color:orange" class="label">Unpaid Leave</span>';
                                                            }

                                                          }

                                                          // var_dump($day);
                                                          // var_dump($day->date);
                                                          // var_dump(date("Y-m-d"));

                                                          ?>
                                                            <?php if(($day->date < date("Y-m-d")) && $day->shift_is_leave == 'no'): ?>
                                                            <span class="label label-danger">Absent</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>

                                                        </td>
                                                        <td></td>
                                                        <td></td>
                                                        <?php endif; ?>

                                                        


                                                      </tr>

                                                <?php endforeach; ?>


                                                    </tbody>
                                                  </table>
                                            </div>



                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal -->
                <div id="reason-modal" class="modal fade" role="dialog">
                  <div class="modal-dialog modal-sm">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Late Reason</h4>
                      </div>
                      <div class="modal-body">
                        <div class="form-group">
                          <label for="sel1">Select reason from dropdown</label>
                          <select class="form-control" id="dropdown-reason">
                            <option value="">Select reason</option>
                            <option value="Traffic">Traffic</option>
                            <option value="Sick">Sick</option>
                            <option value="Other">Other</option>
                          </select>
                        </div>
                        <input type="hidden" class="form-control" id="input-id">

                        <div id="input-reason-container" style="display: none" class="form-group">
                          <label for="usr">Enter reason</label>
                          <input type="text" class="form-control" id="input-reason">
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button style="display: none" id="btn-reason-delete" type="button" class="btn btn-danger">Delete</button>
                        <button  id="btn-reason-save" type="button" class="disabled btn btn-primary">Save</button>
                      </div>
                    </div>

                  </div>
                </div>

                    <!-- Modal -->
                <div id="remark-modal" class="modal fade" role="dialog">
                  <div class="modal-dialog modal-sm">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Remark</h4>
                      </div>
                      <div class="modal-body">
                        
                        <input type="hidden" class="form-control" id="remark-id">

                        <div id="input-remark-container" class="form-group">
                          <label for="usr">Enter remark</label>
                          <textarea class="form-control" id="input-remark"></textarea>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button style="display: none" id="btn-remark-delete" type="button" class="btn btn-danger">Delete</button>
                        <button  id="btn-remark-save" type="button" class="disabled btn btn-primary">Save</button>
                      </div>
                    </div>

                  </div>
                </div>


                    <!-- Modal -->
                <div id="myModal" class="modal fade" role="dialog">
                  <div class="modal-dialog">

                    <!-- Modal content-->

                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Clocking Details</h4>
                      </div>
                      <div class="modal-body">

                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                      </div>
                    </div>
                    

                  </div>
                </div>


                </div>
            </div>
            <script type="text/javascript">
            jQuery(document).on("xcrudafterrequest",function(event,container){
                if(Xcrud.current_task == 'save')
                {
                    // console.log(Xcrud);
                    // console.log(event);
                    // console.log(container);
                }
            });

            $(document).ready(function(){

              $(".freeze-table").freezeTable({
                  'columnNum' : 1,
                  'shadow': true,
                  'fixedNavbar':'.header'

                });

                var reasons_array = ["Traffic", "Sick",""];

                $('#reason-modal').on('show.bs.modal', function (event) {
                  var id = $(event.relatedTarget).attr('data-id');
                  var reason = $(event.relatedTarget).attr('data-reason');
                  $(this).find("#input-id").val(id);

                  var arraycontainsturtles = (reasons_array.indexOf(reason) > -1);

                  if(reason.length > 0){
                    $("#btn-reason-delete").show();
                  }
                  else{
                    $("#btn-reason-delete").hide();
                  }

                  //alert(reason);

                  if(arraycontainsturtles){
                    $("#dropdown-reason option[value='"+reason+"']").prop('selected', true);
                     $("#dropdown-reason").trigger("change");

                  }
                  else{
                    $("#dropdown-reason option[value='Other']").prop('selected', true);
                    $("#input-reason").val(reason);
                    $("#input-reason-container").show();
                    $("#input-reason").trigger("change");
                  }


                });

                $('#remark-modal').on('show.bs.modal', function (event) {
                  var id = $(event.relatedTarget).attr('data-id');
                  var remark = $(event.relatedTarget).attr('data-remark');
                  $(this).find("#remark-id").val(id);
                  $("#input-remark").val(remark);

                  

                  if(remark.length > 0){
                    $("#btn-remark-delete").show();
                    $("#btn-remark-save").removeClass("disabled");
                  }
                  else{
                    $("#btn-remark-delete").hide();
                  }



                });

                $('#dropdown-reason').on('change', function (e) {
                        var optionSelected = $("option:selected", this);
                        var valueSelected = this.value;

                        $("#input-reason").val(valueSelected);


                        if(valueSelected == "Other"){
                            $("#input-reason-container").show();
                            $("#input-reason").val("");
                        }
                        else{
                            $("#input-reason-container").hide();
                        }
                        $("#input-reason").trigger("change");

                });

                $("#input-reason").on("change paste keyup", function() {

                   if($(this).val().length > 0){
                        $("#btn-reason-save").removeClass("disabled");
                   }else{
                        $("#btn-reason-save").addClass("disabled");
                   }

                });

                $("#input-remark").on("change paste keyup", function() {

                   if($(this).val().length > 0){
                        $("#btn-remark-save").removeClass("disabled");
                   }else{
                        $("#btn-remark-save").addClass("disabled");
                   }

                });

                $("#btn-reason-save").on("click", function(e) {

                    if($(this).hasClass("disabled")){
                        return;

                    }

                    $("#btn-reason-save").LoadingOverlay("show");

                    var id = $("#input-id").val();
                    var reason = $("#input-reason").val();

                   $.ajax({
                      type: "GET",  
                      url: "<?php echo base_url() ?>overview/save_reason",
                      data: {'id':id, 'reason':reason},
                      success: function (result) {
                           //do somthing here
                           $("#btn-reason-save").LoadingOverlay("hide");

                           if(result){

                            $('#reason-modal').modal("hide");

                            $('#btn-reason-'+id).text(reason);
                            $('#btn-reason-'+id).attr("data-reason",reason);

                           }
                      }
                    });

                });

                $("#btn-remark-save").on("click", function(e) {

                    if($(this).hasClass("disabled")){
                        return;

                    }

                    $("#btn-remark-save").LoadingOverlay("show");

                    var id = $("#remark-id").val();
                    var remark = $("#input-remark").val();

                   $.ajax({
                      type: "GET",  
                      url: "<?php echo base_url() ?>overview/save_remark",
                      data: {'id':id, 'remark':remark},
                      success: function (result) {
                           //do somthing here
                           $("#btn-remark-save").LoadingOverlay("hide");

                           if(result){

                            $('#remark-modal').modal("hide");

                            $('#btn-remark-'+id).text(remark);
                            $('#btn-remark-'+id).attr("data-remark",remark);

                           }
                      }
                    });

                });

                $("#btn-reason-delete").on("click", function() {

                    $("#btn-reason-delete").LoadingOverlay("show");

                    var id = $("#input-id").val();

                   $.ajax({
                      type: "GET",  
                      url: "<?php echo base_url() ?>overview/save_reason",
                      data: {'id':id, 'reason':''},
                      success: function (result) {
                           //do somthing here
                           $("#btn-reason-delete").LoadingOverlay("hide");

                           if(result){

                            $('#reason-modal').modal("hide");

                            $('#btn-reason-'+id).html('<span class="fa fa-plus" aria-hidden="true"></span>');
                            $('#btn-reason-'+id).attr("data-reason",'');

                           }
                      }
                    });

                });

                $("#btn-remark-delete").on("click", function() {

                    $("#btn-remark-delete").LoadingOverlay("show");

                    var id = $("#remark-id").val();

                   $.ajax({
                      type: "GET",  
                      url: "<?php echo base_url() ?>overview/save_remark",
                      data: {'id':id, 'remark':''},
                      success: function (result) {
                           //do somthing here
                           $("#btn-remark-delete").LoadingOverlay("hide");

                           if(result){

                            $('#remark-modal').modal("hide");

                            $('#btn-remark-'+id).html('<span class="fa fa-plus" aria-hidden="true"></span>');
                            $('#btn-remark-'+id).attr("data-remark",'');
                            console.log("yes");
                           }
                      }
                    });

                });

                $('[data-toggle="tooltip"]').tooltip(); 

                $(".btn-view-modal").click(function(){

                 var value1 = $(this).attr("data-emp_id");
                 var value2 = $(this).attr("data-date");
                
                //contentType: "application/json; charset=utf-8",
                $("#myModal .modal-body").html("");
                 $.ajax({
                      type: "GET",  
                      url: "<?php echo base_url() ?>overview/clocking_details_modal",
                      data: {'emp_id':value1, 'date':value2},
                      success: function (result) {
                           //do somthing here
                           $("#myModal .modal-body").html(result);
                      }
                 });
                });
            });
            </script>

        </div>
