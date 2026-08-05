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
                                            <?php echo $filters; ?>

                                            <?php
                                               $dateComponents = getdate();
                                              // $month = $dateComponents['mon'];                  
                                               $year = $selected_year; //$dateComponents['year'];
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
                                                .holiday{
                                                  color: red;
                                                }

                                            </style>
                                            <div class="col-md-12">
                                							<a class="btn btn-primary m-b-10" target="_blank" href="<?php echo $summary_export_url ?>">Export as PDF</a>
                                              <div style="display:none" id="selectable-controls">
                                                <button id="bulk-action" class="btn btn btn-info" data-toggle="modal" data-target="#bulk-assignment-modal">Manage Shift(s)</button>
                                                <button style="display:none" id="week-selection" class="btn btn btn-primary">Repeat Selection to Rest of the Month</button>
                                                <button style="display:none" id="carry-selection" class="btn btn btn-default">Copy Selection to Next Month</button>
                                                <button id="clear-selection" class="btn btn btn-default">Clear Selection</button>

                                              </div>
                                               <div class="clearfix"></div>
                                            <div class="table-responsive freeze-table">
                                            <table style="font-size: 13px" class="table table-striped">
                                              <thead>
                                                <tr>
                                                  <th style="font-size: 13px">Name</th>
                                                  <?php for ($x = 1; $x <= cal_days_in_month(CAL_GREGORIAN, $selected_month, $year); $x++): ?>

                                                    <th style="font-size: 11px" id="date-<?= $x ?>" <?php if (in_array(sprintf("%04d-%02d-%02d", $year, $selected_month, $x), $public_holidays)){echo "class='holiday'";} ?>>
                                                      <span <?php if (in_array(sprintf("%04d-%02d-%02d", $year, $selected_month, $x), $public_holidays)){echo "data-toggle='tooltip' data-html='true' data-placement='top' data-original-title='".$public_holidays_names[array_search(sprintf("%04d-%02d-%02d", $year, $selected_month, $x),$public_holidays)]."'";} ?>>
                                                      <b><?php echo $x ?></b><br/>
                                                      <?php echo date('D', strtotime("$year-$selected_month-$x")) ?>
                                                      </span>

                                                    </th>

                                                  <?php endfor; ?>
                                                  
                                                </tr>
                                              </thead>
                                              <tbody>

                                                <?php foreach($employees as $emp): ?>
                                                  <tr>
                                                      <td><b>
                                                        <a href="<?php echo base_url() ?>overview/employee_report/<?php echo $emp["id"] ?>?<?php echo "month=" . $_REQUEST['month'] ?>">
                                                        <?php echo $emp["first_name"] ?>
                                                          </a>

                                                        </b><br/> <?php echo $emp["special_id"] ?>

                                                          <br/>
                                                        
                                                        <div style="min-width:150px !important">
                                                     
<a title="Clocking Data" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/manual_clocking_new?month=<?php echo $_REQUEST['month'] ?>&year=<?php echo $_REQUEST["year"] ?>&emp=<?php echo $emp["id"] ?>"><i style="font-size:15px" class="fa fa-hourglass-half"></i></a>
 
<a title="Employee Report" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/employee_report/<?php echo $emp["id"] ?>?<?php echo "month=" . $_REQUEST['month'] ?>&year=<?php echo $_REQUEST["year"] ?>"><i style="font-size:15px" class="fa fa-clock"></i></a>

<a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>summary/view/<?php echo $emp["id"] ?>?<?php echo "from=01%2F" . $_REQUEST['month'] . "%2F".$_REQUEST['year'] ?>&<?php echo "to=". last_day_of_month($_REQUEST['month']) ."%2F" . $_REQUEST['month'] . "%2F".$_REQUEST['year'] ?>"><i style="font-size:15px" class="fa fa-address-card"></i></a>

</div>


                                                      </td>
                                                      <?php for ($x = 1; $x <= cal_days_in_month(CAL_GREGORIAN, $selected_month, $year); $x++): ?>

                                                        <?php $dd = $year."-".$selected_month."-".sprintf("%02d",$x); ?>

                                                        <td data-date-short-x="<?php echo $year."-".$selected_month."-" ?>" data-date-x="<?php echo $x ?>" data-emp-id-x="<?php echo $emp["id"] ?>" class="selectable">
                                                            <?php if($emp[$dd]["assigned"] != "-"): ?>

                                                            <button style="background: <?php echo $emp[$dd]["color"] ?>;color:white" id="btn-shift_assignment-<?php echo $emp["id"] ?>-<?php echo $dd ?>" data-emp-id="<?php echo $emp["id"] ?>" data-date="<?php echo $dd ?>" data-shift-id="<?php echo $emp[$dd]["shift_id"] ?>" data-color="<?php echo $emp[$dd]["color"] ?>" data-code="<?php echo $emp[$dd]["code"] ?>" data-remark="<?php echo $emp[$dd]["remark"] ?>" type="button" class="btn btn-xs" aria-label="Assign Shift" data-toggle="modal" data-target="#assignment-modal">
                                                              <?php echo $emp[$dd]["code"] ?>
                                                            </button>


                                                            <?php else: ?>
                                                                <button id="btn-shift_assignment-<?php echo $emp["id"] ?>-<?php echo $dd ?>" data-emp-id="<?php echo $emp["id"] ?>" data-date="<?php echo $dd ?>" data-shift-id="<?php echo $emp[$dd]["shift_id"] ?>" data-color="<?php echo $emp[$dd]["color"] ?>" data-code="<?php echo $emp[$dd]["code"] ?>" data-remark="<?php echo $emp[$dd]["remark"] ?>" type="button" class="btn btn-default btn-xs" aria-label="Assign Shift" data-toggle="modal" data-target="#assignment-modal">
                                                                  <span class="fa fa-plus" aria-hidden="true"></span>
                                                                </button>
                                                            <?php endif; ?>

                                                        </td>

                                                      <?php endfor; ?>
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


                                              <?php for ($x = 1; $x <= $total_pages; $x++):

                                                if($page == $x){
                                                  $active = "active";
                                                }
                                                else{
                                                    $active = "";
                                                }

                                                ?>
                                              <li class="page-item <?php echo $active ?>"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $x ?>"><?php echo $x ?></a></li>

                                              <?php endfor; ?>

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
                        <h4 class="modal-title">Shift Assignment</h4>
                      </div>
                      <div class="modal-body">
                        <div class="form-group">
                          <label for="sel1">Select shift from dropdown</label>
                          <select class="form-control" id="dropdown-reason">
                            <option value="">Select shift</option>
                             <?php foreach ($shifts as $shift): ?>
                              <option data-color="<?php echo $shift->color ?>" data-code="<?php echo $shift->code ?>" <?php echo ($shift->id == 1) ? 'selected' : '' ?> value="<?php echo $shift->id ?>"><?php echo (($permissions_level !== 'Outlet' && $shift->branch_name) ? "$shift->branch_name - " : "") . "$shift->name ($shift->code)" ?></option>
                             <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="form-group" style="display: none" id="remarkDiv">
                          <label>Remark</label>
                          <input type="text" id="remark" class="form-control">
                        </div>
                        <input type="hidden" class="form-control" id="input-emp-id">
                        <input type="hidden" class="form-control" id="input-date">
                        <input type="hidden" class="form-control" id="input-shift-id">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button style="display: none" id="btn-reason-delete" type="button" class="btn btn-danger">Delete</button>
                        <button  id="btn-reason-save" type="button" class="disabled btn btn-primary">Save</button>
                      </div>
                    </div>

                  </div>
                </div>

                 <div id="bulk-assignment-modal" class="modal fade" role="dialog">
                  <div class="modal-dialog modal-sm">

                    <!-- Modal content-->
                    <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Bulk Shift Assignment</h4>
                      </div>
                      <div class="modal-body">
                        <div class="form-group">
                          <label for="sel1">Select shift from dropdown</label>
                          <select class="form-control" id="bulk-dropdown-reason">
                            <option value="">Select shift</option>
                             <?php foreach ($shifts as $shift): ?>
                              <option data-color="<?php echo $shift->color ?>" data-code="<?php echo $shift->code ?>" <?php echo ($shift->id == 1) ? 'selected' : '' ?> value="<?php echo $shift->id ?>"><?php echo $shift->name . " (" . $shift->code . ")" ?></option>
                             <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="form-group" style="display: none" id="bulkRemarkDiv">
                          <label>Remark</label>
                          <input type="text" id="bulkRemark" class="form-control">
                        </div>
                        <!-- <input type="text" class="form-control" id="input-emp-ids"> -->
                        <!-- <input type="text" class="form-control" id="input-dates"> -->
                        <input type="hidden" class="form-control" id="bulk-input-data">
                        <input type="hidden" class="form-control" id="bulk-input-shift-id">
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button style="display:none" id="bulk-btn-reason-delete" type="button" class="btn btn-danger">Delete</button>
                        <button  id="bulk-btn-reason-save" type="button" class="disabled btn btn-primary">Save</button>
                      </div>
                    </div>

                  </div>
                </div>

                </div>

                </div>
            </div>
            <script type="text/javascript">

              var shift_already_assigned = false;

              var selectable;

              function isSequential(data) {
                var j=Math.min(...data);
                 var   l=Math.max(...data);
                 console.log(j);
                 console.log(l);
                 var k=j;
                 while(k<=l)
                
                 {
                  n = data.includes(k);

                  if(n == true)
                  {
                  
                          k++;
                  }
                  else 
                  {
                    return false;
                  }
                     
                  }
                return true;
                
              }

              function daysInMonth(year, month)
                {
                    return new Date(year, month + 1, 0).getDate();
                }

                function addMonths(date, months)
                {
                    var target_month = date.getMonth() + months;
                    var year = date.getFullYear() + parseInt(target_month / 12);
                    var month = target_month % 12;
                    var day = date.getDate();
                    var last_day = daysInMonth(year, month);
                    if (day > last_day)
                    {
                        day = last_day;
                    }
                    var new_date = new Date(year, month, day);
                    return   new_date.getFullYear() + "-" +   ("0" + (new_date.getMonth() + 1)).slice(-2)   + "-" + ("0" + new_date.getDate()).slice(-2);
                }

              
              function update() {
                var selectedItems = selectable.getSelectedNodes();
                 // console.log(selectedItems);

                  //console.log(selectedItems[0]);

                  if(selectedItems.length > 0){
                    $("#selectable-controls").slideDown("fast");
                    $("#bulk-action").text("Manage " + selectedItems.length + " shift(s)");
                    //$('#bulk-assignment-modal').modal("hide");


                    var week_selected = false;
                    var items = {};
                    var items_dates = {};
                    var items_array = new Array();
                    var items_dates_array = new Array();

                    //for each selection
                    $('.ui-selected').each(function (index, value) {
                      items[$(this).attr('data-emp-id-x')] = true;
                      items_dates[$(this).attr('data-date-x')] = true;
                    });

                    for(var i in items)
                    {
                        items_array.push(i);
                    }
                    for(var i in items_dates)
                    {
                        items_dates_array.push(parseInt(i));
                    }

                    //console.log(items);
                    //console.log(items_dates);
                    var first_selected_item = $('.ui-selected').first(); //$(selectedItems[0]);

                    //console.log(first_selected_item.children("button").attr('data-date'));
                    //console.log(addMonths(new Date(first_selected_item.children("button").attr('data-date')),1));

                    var day_this_month = new Date(first_selected_item.children("button").attr('data-date'));
                    var day_next_month = new Date(addMonths(new Date(first_selected_item.attr('data-date-short-x') + "01"),1));

                    console.log(day_this_month);
                    console.log(day_next_month);
                    console.log("day_this_month.getDay()" + day_this_month.getDay());
                    console.log("day_next_month.getDay()" + day_next_month.getDay());

                    // && items_array.length == 1

                    console.log(items_dates_array);

                     if(selectedItems.length > 1 && isSequential(items_dates_array)){
                        $("#week-selection").show();


                        if(day_this_month.getDay() === day_next_month.getDay()){
                          $("#carry-selection").show();
                        }else{
                          $("#carry-selection").hide();
                        }
                        
                      }
                      else{
                        $("#week-selection").hide();
                        $("#carry-selection").hide();
                      }

                    //console.log(items_dates_array);
                    // console.log(items_array.length);
                    // console.log(selectedItems.length);

                  }
                  else{
                    $("#selectable-controls").slideUp("fast");
                  }


                  //console.log($(selectedItems));
              }

              $(document).ready(function(){

                $('.apply-select2').select2();


                //$('.freeze-table').freezeTable();

                $(".freeze-table").freezeTable({
                  'columnNum' : 1,
                  'shadow': true,
                  'fixedNavbar':'.header',
                  'scrollBar': true

                });


                const table = document.querySelector("table");

                selectable = new Selectable({
                  filter: table.querySelectorAll(".selectable"),
                  toggle: true,
                  autoScroll: {
                      threshold: 30,
                      increment: 30,
                  },
                  ignore: "button"

                });

                // enable table plugin
                selectable.table();


                $("#week-selection").on("click", function(e) {


                  var bulk_data_array = [];
                  $('.ui-selected').each(function (index, value) {

                    var d = parseInt($(this).attr("data-date-x"));
                    var emp_id_x = parseInt($(this).attr("data-emp-id-x"));
                    var d_temp = d;

                    console.log(d_temp);

                    
                    var this_selected_count = $('.ui-selected[data-emp-id-x="'+emp_id_x+'"]').length;
                    //console.log("xxx " +this_selected_count);


                    while(d_temp < 31){

                      if((d_temp+this_selected_count) > 31 ){
                        console.log("continue");
                        break;
                      }

                      d_temp = (d_temp+this_selected_count);

                      console.log(d + " - " + $(this).attr("data-date-short-x")+("0" + d_temp).slice(-2) + " - " + $(this).children().attr("data-emp-id") + " - " + $(this).children().attr("data-shift-id"));

                      //console.log(d_temp);

                      //$("#btn-shift_assignment-"+$(this).attr("data-emp-id-x")+"-"+$(this).attr("data-date-short-x")+("0" + d_temp).slice(-2)).html(d);



                      //bulk_data_array.push($(this).attr("data-emp-id")+'|'+$(this).attr("data-date")+'|'+valueSelected);
                      
                      if($(this).children().attr("data-shift-id").length > 0 && $("#date-"+d_temp).hasClass("holiday") == false){
                        bulk_data_array.push($(this).attr("data-emp-id-x")+'|'+$(this).attr("data-date-short-x")+("0" + d_temp).slice(-2)+'|'+$(this).children().attr("data-shift-id"));
                      }
                      else{
                        //console.log();
                      }


                     // console.log("#btn-shift_assignment-"+$(this).attr("data-emp-id-x")+"-"+$(this).attr("data-date-short-x")+("0" + d_temp).slice(-2));

                      console.log('-------------------------------');
                      //console.log(("0" + d_temp).slice(-2));

                    }

                    //console.log(bulk_data_array.join());

                     

                    // console.log(d + " - " + (d+7));

                    // for(var i=0;i<5;i++){
                    //   console.log(i);
                    // }
                    

                  });

                  //console.log(bulk_data_array.join());

                  $.ajax({
                        type: "POST",  
                        url: "<?php echo base_url() ?>overview/save_assignment",
                        data: {'data':bulk_data_array.join()},
                        success: function (result) {
                             //do somthing here
                             $("#bulk-btn-reason-save").LoadingOverlay("hide");

                             if(result){

                                $('#bulk-assignment-modal').modal("hide");


                                var json_response = $.parseJSON(result);


                                $.notify(
                                  "Success: shifts have been repeated", 
                                  { position:"top center",
                                    className: 'success',
                                    style: 'bootstrap',
                                    gap: 20,
                                    autoHide: true
                                   }
                                );


                                $.each(json_response,function (index, value) {
                              
                                   $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).attr("data-shift-id",value.shift_id);
                                   $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).attr("data-remark",value.remark);
                                   $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).removeClass("btn-default");
                                   $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).css("background",value.color);
                                   $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).css("color","white");
                                  //$('#btn-shift_assignment-'+emp_id+'-'+date).css("background",color);
                                  //$('#btn-shift_assignment-'+emp_id+'-'+date).addClass("btn-primary");

                                   var shift_name = $("#dropdown-reason option[value='"+value.shift_id+"']").text();
                                   $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).text(value.code);


                                });

                                selectable.clear();
                                update();
                                

                             }
                        }
                      });

                });

                

                $("#carry-selection").on("click", function(e) {

                  var bulk_data_array = [];

                  var first_date = $('.ui-selected').attr("data-date-x");
                  $('.ui-selected').each(function (index, value) {

                    var d = parseInt($(this).attr("data-date-x"));
                    var emp_id_x = parseInt($(this).attr("data-emp-id-x"));
                    var d_temp = d;

                    //console.log(d_temp);

                    
                    var this_selected_count = $('.ui-selected[data-emp-id-x="'+emp_id_x+'"]').length;
                    //console.log("xxx " +this_selected_count);


                    //while(d_temp < 31){

                      // if((d_temp+this_selected_count) > 31 ){
                      //   console.log("continue");
                      //   //break;
                      // }

                     // d_temp = (d_temp+this_selected_count);


                      

                      var full_date_temp = $(this).attr("data-date-short-x")+("0" + (d - (parseInt(first_date) - 1))).slice(-2);

                      //console.log(d + " - " + $(this).attr("data-date-short-x")+("0" + d_temp).slice(-2) + " - " + $(this).children().attr("data-emp-id") + " - " + $(this).children().attr("data-shift-id"));
                      //console.log(d + " - " + addMonths(new Date(full_date_temp), 1) + " - " + $(this).children().attr("data-emp-id") + " - " + $(this).children().attr("data-shift-id"));

                      //console.log(index);
                      //return;

                      //console.log(addMonths(new Date(full_date_temp), 1));


                      //$("#btn-shift_assignment-"+$(this).attr("data-emp-id-x")+"-"+$(this).attr("data-date-short-x")+("0" + d_temp).slice(-2)).html(d);



                      //bulk_data_array.push($(this).attr("data-emp-id")+'|'+$(this).attr("data-date")+'|'+valueSelected);
                      
                      if($(this).children().attr("data-shift-id").length > 0){
                        bulk_data_array.push($(this).attr("data-emp-id-x")+'|'+addMonths(new Date(full_date_temp), 1)+'|'+$(this).children().attr("data-shift-id"));
                      }
                      else{
                        //console.log();
                      }

                      


                     // console.log("#btn-shift_assignment-"+$(this).attr("data-emp-id-x")+"-"+$(this).attr("data-date-short-x")+("0" + d_temp).slice(-2));

                      //console.log('-------------------------------');
                      //console.log(("0" + d_temp).slice(-2));

                    //}

                    

                    //console.log(bulk_data_array.join());

                     

                    // console.log(d + " - " + (d+7));

                    // for(var i=0;i<5;i++){
                    //   console.log(i);
                    // }
                    

                  });


                  // console.log(bulk_data_array);
                  // return;

                  //console.log(bulk_data_array.join());

                  $.ajax({
                        type: "POST",  
                        url: "<?php echo base_url() ?>overview/save_assignment",
                        data: {'data':bulk_data_array.join()},
                        success: function (result) {
                             //do somthing here
                             $("#bulk-btn-reason-save").LoadingOverlay("hide");

                             if(result){

                                $('#bulk-assignment-modal').modal("hide");


                                var json_response = $.parseJSON(result);

                                $.notify(
                                  "Success: shifts have been carried to the next month", 
                                  { position:"top center",
                                    className: 'success',
                                    style: 'bootstrap',
                                    gap: 20,
                                    autoHide: true
                                   }
                                );


                                // $.each(json_response,function (index, value) {
                              
                                //    $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).attr("data-shift-id",value.shift_id);
                                //    $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).removeClass("btn-default");
                                //    $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).css("background",value.color);
                                //    $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).css("color","white");
                                //   //$('#btn-shift_assignment-'+emp_id+'-'+date).css("background",color);
                                //   //$('#btn-shift_assignment-'+emp_id+'-'+date).addClass("btn-primary");

                                //    var shift_name = $("#dropdown-reason option[value='"+value.shift_id+"']").text();
                                //    $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).text(value.code);


                                // });

                                selectable.clear();
                                update();
                                

                             }
                        }
                      });

                });







                                
                //selectable.on("init", update);

                // Listen for update event
               // selectable.on("update", update);

                // Listen for end event
                selectable.on("end", update);

                $("#clear-selection").on("click", function(e) {

                  selectable.clear();
                  update();
                });
                



                $('#bulk-assignment-modal').on('show.bs.modal', function (event) {

                  $("#bulk-dropdown-reason option[value='']").prop('selected', true);
                  $("#bulk-dropdown-reason").trigger("change");

                  $('#bulkRemark').val('');

                  var has_shift_id = false;
                  $('.ui-selected button').each(function (index, value) {

                    if($(this).attr("data-shift-id")){
                      has_shift_id = true;

                     // console.log("has attr");

                    }

                    if(has_shift_id){
                      $("#bulk-btn-reason-delete").show();
                      $("#bulkRemarkDiv").show();
                      shift_already_assigned = true;
                    }
                    else{
                      $("#bulk-btn-reason-delete").hide();
                      $("#bulkRemarkDiv").hide();
                      shift_already_assigned = false;

                    }

                    //console.log('div' + index + ':' + $(this).attr('id'));


                  });


                  $('#bulk-dropdown-reason').on('change', function (e) {
                    var valueSelected = this.value;

                    var bulkRemark = $('#bulkRemark').val();

                    var bulk_data_array = [];

                    $('.ui-selected button').each(function (index, value) {

                        if(shift_already_assigned){
                          bulk_data_array.push($(this).attr("data-emp-id")+'|'+$(this).attr("data-date")+'|'+valueSelected+'|'+bulkRemark);
                        }else{
                          bulk_data_array.push($(this).attr("data-emp-id")+'|'+$(this).attr("data-date")+'|'+valueSelected);
                        }

                    });
                    console.log(bulk_data_array);




                    //console.log(valueSelected);
                    $(this).closest(".modal-body").find("#bulk-input-data").val(bulk_data_array.join());
                    $(this).closest(".modal-body").find("#bulk-input-shift-id").val(valueSelected);

                    if(valueSelected.length > 0){
                       $("#bulk-btn-reason-save").removeClass("disabled");


                    }
                    else{
                       $("#bulk-btn-reason-save").addClass("disabled");
                      //$("#input-reason").val(reason);
                      //$("#input-reason-container").show();
                      $(this).closest(".modal-body").find("#bulk-input-data").val("");
                    }

                  });

                  $('#bulkRemark').on('change', function (e) {
                    var valueSelected = $('#bulk-dropdown-reason').val();

                    var bulkRemark = $('#bulkRemark').val();

                    var bulk_data_array = [];

                    $('.ui-selected button').each(function (index, value) {

                        bulk_data_array.push($(this).attr("data-emp-id")+'|'+$(this).attr("data-date")+'|'+valueSelected+'|'+bulkRemark);

                    });
                    console.log(bulk_data_array);




                    //console.log(valueSelected);
                    $(this).closest(".modal-body").find("#bulk-input-data").val(bulk_data_array.join());
                    $(this).closest(".modal-body").find("#bulk-input-shift-id").val(valueSelected);

                    if(valueSelected.length > 0){
                       $("#bulk-btn-reason-save").removeClass("disabled");


                    }
                    else{
                       $("#bulk-btn-reason-save").addClass("disabled");
                      //$("#input-reason").val(reason);
                      //$("#input-reason-container").show();
                      $(this).closest(".modal-body").find("#bulk-input-data").val("");
                    }

                  });

                    $("#bulk-btn-reason-save").on("click", function(e) {

                      if($(this).hasClass("disabled")){
                          return;

                      }

                      $("#bulk-btn-reason-save").LoadingOverlay("show");

                      var bulk_input_data = $("#bulk-input-data").val();
                      var bulk_input_shift_id = $("#bulk-input-shift-id").val();

                      

                      $.ajax({
                        type: "POST",  
                        url: "<?php echo base_url() ?>overview/save_assignment",
                        data: {'data':bulk_input_data},
                        success: function (result) {
                             //do somthing here
                             $("#bulk-btn-reason-save").LoadingOverlay("hide");

                             if(result){

                                $('#bulk-assignment-modal').modal("hide");


                                var json_response = $.parseJSON(result);

                                $.notify(
                                  "Success: shift(s) have been saved", 
                                  { position:"top center",
                                    className: 'success',
                                    style: 'bootstrap',
                                    gap: 20,
                                    autoHide: true
                                   }
                                );


                                $.each(json_response,function (index, value) {
                              
                                   $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).attr("data-shift-id",value.shift_id);
                                   $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).attr("data-remark",value.remark);
                                   $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).removeClass("btn-default");
                                   $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).css("background",value.color);
                                   $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).css("color","white");
                                  //$('#btn-shift_assignment-'+emp_id+'-'+date).css("background",color);
                                  //$('#btn-shift_assignment-'+emp_id+'-'+date).addClass("btn-primary");

                                   var shift_name = $("#dropdown-reason option[value='"+value.shift_id+"']").text();
                                   $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).text(value.code);


                                });

                                selectable.clear();
                                update();
                                

                             }
                        }
                      });

                  });


                  $("#bulk-btn-reason-delete").on("click", function() {

                   $("#bulk-btn-reason-delete").LoadingOverlay("show");


                   var bulk_data_array = [];

                    $('.ui-selected button').each(function (index, value) {

                      //naveed

                      if($(this).attr("data-shift-id")){
                        bulk_data_array.push($(this).attr("data-emp-id")+'|'+$(this).attr("data-date")+'|'+$(this).attr("data-shift-id"));
                      }

                    });


                   $.ajax({
                      type: "POST",  
                      url: "<?php echo base_url() ?>overview/delete_assignment",
                      data: {'data':bulk_data_array.join()},
                      success: function (result) {
                           //do somthing here
                           $("#bulk-btn-reason-delete").LoadingOverlay("hide");

                           if(result){

                              $('#bulk-assignment-modal').modal("hide");

                              $.notify(
                                  "Success: shift(s) have been deleted", 
                                  { position:"top center",
                                    className: 'success',
                                    style: 'bootstrap',
                                    gap: 20,
                                    autoHide: true
                                   }
                                );

                                // $('.ui-selected button').each(function (index, value) {

                                //   var emp_id = $(this).attr("data-emp-id");
                                //   var date = $(this).attr("data-date");
                                //   var shift_id = $("#bulk-input-shift-id").val();


                                //   //$('#btn-shift_assignment-'+emp_id+'-'+date).attr("data-emp-id",emp_id);
                                //   //$('#btn-shift_assignment-'+emp_id+'-'+date).attr("data-date",date);

                                //   //var shift_name = $("#dropdown-reason option[value='"+shift_id+"']").text();
                                //   $('#btn-shift_assignment-'+emp_id+'-'+date).html('<span class="fa fa-plus" aria-hidden="true"></span>');


                                //   $('#btn-shift_assignment-'+emp_id+'-'+date).attr("data-shift-id","");
                                //   $('#btn-shift_assignment-'+emp_id+'-'+date).addClass("btn-default");
                                //   $('#btn-shift_assignment-'+emp_id+'-'+date).css("background","white");
                                //   $('#btn-shift_assignment-'+emp_id+'-'+date).css("color","black");

                                

                                // });

                                var json_response = $.parseJSON(result);
                                $.each(json_response,function (index, value) {
                                  $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).html('<span class="fa fa-plus" aria-hidden="true"></span>');

                                  $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).attr("data-shift-id","");
                                  $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).addClass("btn-default");
                                  $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).css("background","white");
                                  $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).css("color","black");
                                });


                                selectable.clear();
                                update();


                           }
                      }
                    });

                });


                });


                $('#assignment-modal').on('show.bs.modal', function (event) {

                  

                  //alert("as");
                   var emp_id = $(event.relatedTarget).attr('data-emp-id');
                   var date = $(event.relatedTarget).attr('data-date');
                   var shift_id = $(event.relatedTarget).attr('data-shift-id');
                   var remark = $(event.relatedTarget).attr('data-remark');

                   // console.log(emp_id);
                   // console.log(date);
                   // console.log(shift_id);
                  // var reason = $(event.relatedTarget).attr('data-reason');
                   $(this).find("#input-emp-id").val(emp_id);
                   $(this).find("#input-date").val(date);
                   $(this).find("#input-shift-id").val(shift_id);
                   $(this).find("#remark").val(remark);

                  // var arraycontainsturtles = (reasons_array.indexOf(reason) > -1);

                  // if(reason.length > 0){
                  //   $("#btn-reason-delete").show();
                  // }
                  // else{
                  //   $("#btn-reason-delete").hide();
                  // }

                  // //alert(reason);

                  if(shift_id.length > 0){
                    $("#dropdown-reason option[value='"+shift_id+"']").prop('selected', true);
                     $("#dropdown-reason").trigger("change");
                     $("#btn-reason-delete").show();
                     $("#remarkDiv").show();
                     shift_already_assigned = true;

                  }
                  else{
                    $("#dropdown-reason option[value='']").prop('selected', true);
                    //$("#input-reason").val(reason);
                    //$("#input-reason-container").show();
                    $("#dropdown-reason").trigger("change");
                    $("#btn-reason-delete").hide();
                    $("#remarkDiv").hide();
                    shift_already_assigned = false;
                  }


                });

                $('#dropdown-reason').on('change', function (e) {
                  var valueSelected = this.value;

                  console.log(valueSelected);
                  $(this).closest(".modal-body").find("#input-shift-id").val(valueSelected);

                  if(valueSelected.length > 0){
                     $("#btn-reason-save").removeClass("disabled");


                  }
                  else{
                     $("#btn-reason-save").addClass("disabled");
                    //$("#input-reason").val(reason);
                    //$("#input-reason-container").show();
                  }

                });


                $("#btn-reason-save").on("click", function(e) {

                    if($(this).hasClass("disabled")){
                        return;

                    }

                    $("#btn-reason-save").LoadingOverlay("show");

                    var emp_id = $("#input-emp-id").val();
                    var date = $("#input-date").val();
                    var shift_id = $("#input-shift-id").val();
                    var remark = $("#remark").val();
                    data = {'data':emp_id+'|'+date+'|'+shift_id};
                    if(shift_already_assigned){
                      data = {'data':emp_id+'|'+date+'|'+shift_id+'|'+remark};
                    }

                    $.ajax({
                      type: "POST",  
                      url: "<?php echo base_url() ?>overview/save_assignment",
                      data: data,

                      success: function (result) {

                           //do somthing here
                           $("#btn-reason-save").LoadingOverlay("hide");

                           if(result){

                            var json_response = $.parseJSON(result);

                            $.notify(
                                  "Success: shift(s) have been saved", 
                                  { position:"top center",
                                    className: 'success',
                                    style: 'bootstrap',
                                    gap: 20,
                                    autoHide: true
                                   }
                                );

                            //console.log(json_response);

                              $('#assignment-modal').modal("hide");

                              //$('#btn-shift_assignment-'+emp_id+'-'+date).attr("data-emp-id",emp_id);
                              //$('#btn-shift_assignment-'+emp_id+'-'+date).attr("data-date",date);

                              $.each(json_response,function (index, value) {
                              
                                 $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).attr("data-shift-id",value.shift_id);
                                 $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).attr("data-remark",value.remark);
                                 $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).removeClass("btn-default");
                                 $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).css("background",value.color);
                                 $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).css("color","white");
                                //$('#btn-shift_assignment-'+emp_id+'-'+date).css("background",color);
                                //$('#btn-shift_assignment-'+emp_id+'-'+date).addClass("btn-primary");

                                 var shift_name = $("#dropdown-reason option[value='"+value.shift_id+"']").text();
                                 $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).text(value.code);


                              });

                             



                           }
                      }
                    });

                });


                $("#btn-reason-delete").on("click", function() {

                   $("#btn-reason-delete").LoadingOverlay("show");

                   var emp_id = $("#input-emp-id").val();
                   var date = $("#input-date").val();
                   var shift_id = $("#input-shift-id").val();

                   $.ajax({
                      type: "POST",  
                      url: "<?php echo base_url() ?>overview/delete_assignment",
                      data: {'data':emp_id+'|'+date+'|'+shift_id},

                      success: function (result) {
                           //do somthing here
                           $("#btn-reason-delete").LoadingOverlay("hide");

                           if(result){

                              $('#assignment-modal').modal("hide");

                              $.notify(
                                  "Success: shift(s) have been deleted", 
                                  { position:"top center",
                                    className: 'success',
                                    style: 'bootstrap',
                                    gap: 20,
                                    autoHide: true
                                   }
                                );

                              //$('#btn-shift_assignment-'+emp_id+'-'+date).attr("data-emp-id",emp_id);
                              //$('#btn-shift_assignment-'+emp_id+'-'+date).attr("data-date",date);

                              //var shift_name = $("#dropdown-reason option[value='"+shift_id+"']").text();

                              var json_response = $.parseJSON(result);
                                $.each(json_response,function (index, value) {
                                   $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).html('<span class="fa fa-plus" aria-hidden="true"></span>');


                                $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).attr("data-shift-id","");
                                $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).addClass("btn-default");
                                $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).css("background","white");
                                $('#btn-shift_assignment-'+value.employee_id+'-'+value.date).css("color","black");
                                });

                             

                           }
                      }
                    });

                });


              });

            jQuery(document).on("xcrudafterrequest",function(event,container){
                if(Xcrud.current_task == 'save')
                {
                    // console.log(Xcrud);
                    // console.log(event);
                    // console.log(container);
                }
            });
            </script>

        </div>