<div class="page-wrapper">
            <div class="content container-fluid">
               
                 <div class="page-content-wrapperx ">
                    <div class="containerx">
                        <div class="row">
                            <div class="col-sm-12">
                              
                                <div class="panel panel-primary">
                                    <div class="panel-body">
                                      <h4 class="page-title"><?php echo $pageTitle ?></h4>

                                        <div>
                                            <form action="<?php echo site_url() ?>overview/manual_clocking_new" method="get">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                  <label for="sel1">Outlet</label>
                                                  <select  class="form-control" id="branch" name="branch">
                                                    <option value="">All</option>
                                                    <?php foreach ($branches as $branch): ?>
                                                        <option <?php echo ($branch->id == $selected_branch_id) ? 'selected' : '' ?> value="<?php echo $branch->id ?>"><?php echo $branch->name ?></option>
                                                     <?php endforeach; ?>
                                                  </select>
                                                </div>
                                                
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group">
                                                  <label for="sel1">Employee</label>
                                                  <select class="form-control apply-select2" id="emp" name="emp">
                                                    <option value="">All</option>
                                                    <?php foreach ($employees_dropdown as $emp): ?>
                                                        <option <?php echo ($emp->id == $selected_emp_id) ? 'selected' : '' ?> value="<?php echo $emp->id ?>"><?php echo $emp->special_id . " - " . $emp->first_name ?></option>
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

                                            <div class="col-md-3">
                                                <label for="sel1">&nbsp;</label>
                                                <button class="btn btn-primary btn-block">Filter</button>
                                                
                                            </div>
                                             <!-- <div class="col-md-3">
                                                <label for="sel1">&nbsp;</label>
                                                <button class="btn btn-default btn-block">Shifts Sheet</button>
                                                
                                            </div> -->
                                        </form>

                                            <?php
                                               $dateComponents = getdate();
                                              // $month = $dateComponents['mon'];                  
                                               $year = $dateComponents['year'];
                                             // echo shift_calendar($month,$year,$dateArray);


                                            ?>

                                            <style type="text/css">
                                                .color-check{
                                                    color:green;
                                                }

                                                .color-times{
                                                    color:red;
                                                    
                                                }

                                                .color-status-early{
                                                    background-color:#5cb45b;
                                                    
                                                }

                                                .color-status-late{
                                                    background-color:#f7b543;
                                                    
                                                }

                                                .color-status-ontime{
                                                    background-color:#777;
                                                    
                                                }

                                                .color-status-grey{
                                                    background-color:grey;
                                                    
                                                }

                                                table th:hover, table td:nth-child(1):hover {
                                                  /*background-color: lightgrey;*/
                                                }

                                                .selectable:hover {
                                                  background-color: lightgrey;
                                                }

                                                table td.ui-selecting {
                                                  background-color: #7f8c8d;
                                                }
                                                table td.ui-selecting.ui-selected {
                                                  background-color: #7f8c8d;
                                                }
                                                table td.ui-selected {
                                                  background-color: #009ce7 !important;
                                                }

                                            </style>
                                            <div class="col-md-12">
                                               <div class="clearfix"></div>
                                            <div class="table-responsive freeze-table">
                                            <table style="font-size: 13px" class="table table-striped">
                                              <thead>
                                                <tr>
                                                  <th>Name</th>
                                                  <th>Outlet</th>
                                                  <th>Shift</th>
                                                  <th>Device</th>
                                                  <th>Mode</th>
                                                  <th>Type</th>
                                                  <th>Datetime</th>
                                                  <th>Action</th>
                                                  
                                                </tr>
                                              </thead>
                                              <tbody>

                                                <style type="text/css">
                                                  .color-in{
                                                    color:green;
                                                  }

                                                  .color-out{
                                                    color:red;
                                                  }

                                                </style>

                                                <?php foreach($clockings as $c): ?>
                                                  <tr>
                                                      <td><b>
                                                        <a href="<?php echo base_url() ?>overview/manual_clocking_new?branch=<?php echo $selected_branch_id ?>&emp=<?php echo $c["employee_id"]?>&month=<?php echo $_REQUEST['month'] ?>">
                                                        <?php echo $c["first_name"] ?>
                                                          </a>

                                                        </b><br/> <?php echo $c["special_id"] ?>

                                                      </td>
                                                      <td>
                                                        <?php echo $c["branch_name"] ?>
                                                      </td>
                                                      <td>
                                                        <?php echo $c["shift_name"] ?>
                                                      </td>
                                                      <td>
                                                        <?php echo $c["mac_address"] ?>
                                                      </td>
                                                      <td>
                                                        <?php echo $c["mode"] ?>
                                                      </td>
                                                      <td>
                              <b><span id="label-shift_assignment-<?php echo $c["id"] ?>" class="color-<?php echo str_replace(' ', '', $c["type"]) ?>"><?php echo $c["type"] ?></span></b>
                                                      </td>
                                                      <td>
                                                        <b>
                                                          <span id="label-clocking_datetime-<?php echo $c["id"] ?>">
                                                           <?php echo $c["datetime"] ?>
                                                         </span>
                                                            
                                                          </b>
                                                      </td>
                                                      <td>

                                                        <button id="btn-shift_assignment-<?php echo $c["id"] ?>" data-clocking-id="<?php echo $c["id"] ?>" data-clocking-type="<?php echo $c["type"] ?>" data-clocking-datetime="<?php echo $c["datetime"] ?>" class="btn btn-primary" aria-label="Edit Clocking" data-toggle="modal" data-target="#assignment-modal">
                                                                  <span class="fa fa-edit" aria-hidden="true"></span>
                                                        </button>

                                                        
                                                        
                                                      </td>
                                                  </tr>


                                              <?php endforeach; ?>


                                              </tbody>
                                              
                                            </table>
                                          </div>
                                          </div>

                                            
                                            
                                        


                                           
                                        </div>

                                        <div class="col-md-12">

                                       
                                          

                                        
                                  


                                            <nav style="float:right" aria-label="Page navigation example">


                                            <ul class="pagination ">

                                              <?php if($page > 1): ?>
                                                <li class="page-item"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $page - 1 ?>">Previous</a></li>
                                              <?php endif; ?>

                                              
                                              <!-- <li class="page-item">
                                              <select>
                                                <option value="volvo">Volvo</option>
                                                <option value="saab">Saab</option>
                                                <option value="mercedes">Mercedes</option>
                                                <option value="audi">Audi</option>
                                              </select>
                                              </li> -->

                                              <!-- <select> -->
                                              <?php 
                                              
                                              $dots_added = false;

                                              if($page > 3 && $total_pages > 10){
                                                echo '<li class="page-item "><a class="page-link">. . .</a></li>';
                                              }

                                              for ($x = 1; $x <= $total_pages; $x++):

                                                if($page == $x){
                                                  $active = "active";
                                                  $selected_page = $x;
                                                }
                                                else{
                                                    $active = "";
                                                }

                                                ?>
                                              
                                              <?php 

                                              //echo $page;

                                               if($total_pages > 10){

                                                  // if(!$dots_added){

                                                  // }
                                                  // $dots_added = true;

                                                  //continue;

                                                  

                                                  if(($x > ($page - 3 )) &&  ($x < ($page + 3))){
                                                    
                                                  ?>
                                                  <li class="page-item <?php echo $active ?>"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $x ?>"><?php echo $x ?></a></li>

                                                  
                                              <?php

                                               } }
                                              ?>

                                              <?php
                                              
                                              if($total_pages <= 10){
                                              ?>

                                              <li class="page-item <?php echo $active ?>"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $x ?>"><?php echo $x ?></a></li>
                                             
                                             <?php 
                                             }
                                             ?>

                                              <?php endfor; 
                                              
                                              if(($page < ($total_pages - 2)) && $total_pages > 10){
                                                echo '<li class="page-item "><a class="page-link">. . .</a></li>';
                                              }
                                              
                                              ?>
                                              

                                              <?php if($page < $total_pages): ?>
                                                <li class="page-item"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $page + 1 ?>">Next</a></li>
                                              <?php endif; ?>

                                            </ul>

                                          </nav>
                                        </div>
                                        
                                        </div>


                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div id="assignment-modal" class="modal fade" role="dialog">
                  <div class="modal-dialog modal-sm">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Edit Clocking</h4>
                      </div>
                      <div class="modal-body">
                        <div class="form-group">
                          <label for="sel1">Type</label>
                          <select class="form-control" id="dropdown-reason">
                              <option value="in">in</option>
                              <option value="out">out</option>                             
                          </select>
                        </div>
                        <div class="form-group">
                          <label for="sel1">Datetime</label>
                          <input type="text" class="form-control" id="input-datetime">
                        </div>
                        <input type="hidden" class="form-control" id="input-clocking-id">
                        <input type="hidden" class="form-control" id="input-clocking-type">
                        <input type="hidden" class="form-control" id="input-clocking-datetime">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button  id="btn-reason-delete" type="button" class="btn btn-danger">Delete</button>
                        <button  id="btn-reason-save" type="button" class="btn btn-primary">Save</button>
                      </div>
                    </div>

                  </div>
                </div>

                

                </div>

                </div>
            </div>
            <script type="text/javascript">


              $(document).ready(function(){

                $('.apply-select2').select2();



                //$('.freeze-table').freezeTable();

                $(".freeze-table").freezeTable({
                  'columnNum' : 1,
                  'shadow': true,
                  'fixedNavbar':'.header'

                });


                const table = document.querySelector("table");


                $('#assignment-modal').on('show.bs.modal', function (event) {



                   var clocking_id = $(event.relatedTarget).attr('data-clocking-id');
                   var clocking_type = $(event.relatedTarget).attr('data-clocking-type');
                   var clocking_datetime = $(event.relatedTarget).attr('data-clocking-datetime');

                   console.log(clocking_id + " aaa");
                   console.log(clocking_type + " aaa");
                   console.log(clocking_datetime + " aaa");
                   
                   $(this).find("#input-clocking-id").val(clocking_id);
                   $(this).find("#input-clocking-type").val(clocking_type);
                   $(this).find("#input-clocking-datetime").val(clocking_datetime);

                 
                  $("#dropdown-reason option[value='"+clocking_type+"']").prop('selected', true);
                  $("#dropdown-reason").trigger("change");

                  $(this).find("#input-datetime").val(clocking_datetime);
                  $("#input-datetime").trigger("change");


                });

                $('#dropdown-reason').on('change', function (e) {
                  var valueSelected = this.value;

                  console.log(valueSelected);
                  $(this).closest(".modal-body").find("#input-clocking-type").val(valueSelected);


                });

                $('#input-datetime').on('change', function (e) {
                  var valueSelected = this.value;
                  console.log(valueSelected);
                  $(this).closest(".modal-body").find("#input-clocking-datetime").val(valueSelected);

                });


                $("#btn-reason-save").on("click", function(e) {

                    // if($(this).hasClass("disabled")){
                    //     return;

                    // }

                    $("#btn-reason-save").LoadingOverlay("show");

                    var clocking_id = $("#input-clocking-id").val();
                    var clocking_type = $("#input-clocking-type").val();
                    var clocking_datetime = $("#input-clocking-datetime").val();

                    console.log(clocking_datetime);


                    $.ajax({
                      type: "POST",  
                      url: "<?php echo base_url() ?>overview/save_clocking",
                      data: {'clocking_id':clocking_id,'clocking_type':clocking_type,'clocking_datetime':clocking_datetime},

                      success: function (result) {

                           //do somthing here
                           $("#btn-reason-save").LoadingOverlay("hide");

                           if(result){

                            var json_response = $.parseJSON(result);

                            console.log(json_response);

                              $('#assignment-modal').modal("hide");

                              
                              
                              $('#btn-shift_assignment-'+clocking_id).attr("data-clocking-type",clocking_type);
                              $('#btn-shift_assignment-'+clocking_id).attr("data-clocking-datetime",clocking_datetime);
                              $('#label-shift_assignment-'+clocking_id).text(clocking_type);
                              $('#label-clocking_datetime-'+clocking_id).text(clocking_datetime);


                           }
                      }
                    });

                });


                $("#btn-reason-delete").on("click", function() {

                   $("#btn-reason-delete").LoadingOverlay("show");

                   var clocking_id = $("#input-clocking-id").val();

                   $.ajax({
                      type: "POST",  
                      url: "<?php echo base_url() ?>overview/delete_clocking",
                      data: {'clocking_id':clocking_id},

                      success: function (result) {
                           //do somthing here
                           $("#btn-reason-delete").LoadingOverlay("hide");

                           if(result){

                              $('#assignment-modal').modal("hide");
           

                              var json_response = $.parseJSON(result);

                              $('#btn-shift_assignment-'+clocking_id).closest("tr").slideUp();

                           }
                      }
                    });

                });


              });

            
            </script>

        </div>