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
                    .color-check {
                      color: green;
                    }

                    .color-times {
                      color: red;

                    }

                    .color-status-early {
                      background-color: #5cb45b;

                    }

                    .color-status-late {
                      background-color: #f7b543;

                    }

                    .color-status-ontime {
                      background-color: #777;

                    }

                    .color-status-grey {
                      background-color: grey;

                    }

                    table th:hover,
                    table td:nth-child(1):hover {
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

                    .holiday {
                      color: red;
                    }
                  </style>
                  <div class="col-md-12">
                    <!-- <a class="btn btn-primary m-b-10" target="_blank" href="<?php echo $summary_export_url ?>">Export as PDF</a> -->
                    <div style="display:none" id="selectable-controls">
                      <button id="bulk-action" class="btn btn btn-info" data-toggle="modal" data-target="#bulk-assignment-modal">Manage Allowance(s)</button>
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
                            <?php foreach ($period_of_dates as $periodDate) : ?>

                              <th style="font-size: 11px" id="date-<?= $periodDate->format('j') ?>" <?php if (in_array($periodDate->format('Y-m-d'), $public_holidays)) {
                                                                                echo "class='holiday'";
                                                                              } ?>>
                                <span <?php if (in_array($periodDate->format('Y-m-d'), $public_holidays)) {
                                        echo "data-toggle='tooltip' data-html='true' data-placement='top' data-original-title='" . $public_holidays_names[array_search($periodDate->format('Y-m-d'), $public_holidays)] . "'";
                                      } ?>>
                                  <b><?php echo $periodDate->format('j') ?></b><br />
                                  <?php echo $periodDate->format('D') ?>
                                </span>

                              </th>

                            <?php endforeach; ?>

                          </tr>
                        </thead>
                        <tbody>

                          <?php foreach ($employees as $emp) : ?>
                            <tr>
                              <td><b>
                                  <?php if (is_page_permitted('employee_report')) : ?>
                                    <a href="<?php echo base_url() ?>overview/employee_report/<?php echo $emp["id"] ?>?<?php echo "month=" . $formatted_date['start_date']->format('m') ?>">
                                  <?php endif ?>
                                    <?php echo $emp["first_name"] ?>
                                  <?php if (is_page_permitted('employee_report')) : ?>
                                    </a>
                                  <?php endif ?>

                                </b><br /> <?php echo $emp["special_id"] ?>

                                <br />

                                <div style="min-width:150px !important">
                                  <?php if (is_page_permitted('manual_clocking_new')) : ?>
                                    <a title="Clocking Data" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/manual_clocking_new?month=<?php echo $formatted_date['start_date']->format('m') ?>&year=<?php echo $formatted_date['start_date']->format('Y') ?>&emp=<?php echo $emp["id"] ?>"><i style="font-size:15px" class="fa fa-hourglass-half"></i></a>
                                  <?php endif ?>
                                  <?php if (is_page_permitted('employee_report')) : ?>
                                    <a title="Employee Report" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/employee_report/<?php echo $emp["id"] ?>?<?php echo "month=" . $formatted_date['start_date']->format('m') ?>&year=<?php echo $formatted_date['start_date']->format('Y') ?>"><i style="font-size:15px" class="fa fa-clock"></i></a>
                                  <?php endif ?>
                                  <?php if (is_page_permitted('view')) : ?>
                                    <a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>summary/view/<?php echo $emp["id"] ?>?<?php echo "from=" . $start_date_f ?>&<?php echo "to=" . $end_date_f ?>"><i style="font-size:15px" class="fa fa-address-card"></i></a>
                                  <?php endif ?>
                                </div>


                              </td>
                              <?php foreach ($period_of_dates as $periodDate) : ?>

                                <?php $dd = $periodDate->format('Y-m-d') ?>

                                <td data-date-short-x="<?php echo $periodDate->format('Y-m-') ?>" data-date-x="<?php echo $periodDate->format('j') ?>" data-emp-id-x="<?php echo $emp["id"] ?>" class="selectable">
                                  <?php if ($emp[$dd]["assigned"] != "-") : ?>

                                    <button id="btn-shift_assignment-<?php echo $emp["id"] ?>-<?php echo $dd ?>" data-emp-id="<?php echo $emp["id"] ?>" data-date="<?php echo $dd ?>" data-allowance1_id="<?php echo $emp[$dd]["allowance1_id"] ?>" data-allowance2_id="<?php echo $emp[$dd]["allowance2_id"] ?>" type="button" class="btn btn-xs btn-primary" aria-label="Assign Shift" data-toggle="modal" data-target="#assignment-modal">
                                      <?php echo $emp[$dd]["code"] ?>
                                    </button>


                                  <?php else : ?>
                                    <button id="btn-shift_assignment-<?php echo $emp["id"] ?>-<?php echo $dd ?>" data-emp-id="<?php echo $emp["id"] ?>" data-date="<?php echo $dd ?>" data-allowance1_id="<?php echo $emp[$dd]["allowance1_id"] ?>" data-allowance2_id="<?php echo $emp[$dd]["allowance2_id"] ?>" type="button" class="btn btn-default btn-xs" aria-label="Assign Shift" data-toggle="modal" data-target="#assignment-modal">
                                      <span class="fa fa-plus" aria-hidden="true"></span>
                                    </button>
                                  <?php endif; ?>

                                </td>

                              <?php endforeach ?>
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

                      <?php if ($page > 1) : ?>
                        <li class="page-item"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $page - 1 ?>">Previous</a></li>
                      <?php endif; ?>


                      <?php for ($x = 1; $x <= $total_pages; $x++) :

                        if ($page == $x) {
                          $active = "active";
                        } else {
                          $active = "";
                        }

                      ?>
                        <li class="page-item <?php echo $active ?>"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $x ?>"><?php echo $x ?></a></li>

                      <?php endfor; ?>

                      <?php if ($page < $total_pages) : ?>
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
            <h4 class="modal-title">Allowance Assignment</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="dropdown-allowance1">Select allowance from dropdown</label>
              <select class="form-control" id="dropdown-allowance1">
                <option value="">Select allowance</option>
                <?php foreach ($allowances as $allowance) : ?>
                  <option data-code="<?php echo $allowance->code ?>" value="<?php echo $allowance->id ?>"><?php echo "$allowance->name ($allowance->code)" ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button id="add-second-allowance" class="btn btn-default">Add Second Allowance</button>
            <div class="form-group" style="display: none" id="second-allowance">
              <label for="dropdown-allowance2">Select allowance from dropdown</label>
              <select class="form-control" id="dropdown-allowance2">
                <option value="">Select allowance</option>
                <?php foreach ($allowances as $allowance) : ?>
                  <option data-code="<?php echo $allowance->code ?>" value="<?php echo $allowance->id ?>"><?php echo "$allowance->name ($allowance->code)" ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <input type="hidden" class="form-control" id="input-emp-id">
            <input type="hidden" class="form-control" id="input-date">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button style="display: none" id="btn-reason-delete" type="button" class="btn btn-danger">Delete</button>
            <button id="btn-reason-save" type="button" class="disabled btn btn-primary">Save</button>
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
            <h4 class="modal-title">Bulk Allowance Assignment</h4>
          </div>
          <div class="modal-body">
          <div class="form-group">
              <label for="bulk-dropdown-allowance1">Select allowance from dropdown</label>
              <select class="form-control" id="bulk-dropdown-allowance1">
                <option value="">Select allowance</option>
                <?php foreach ($allowances as $allowance) : ?>
                  <option data-code="<?php echo $allowance->code ?>" value="<?php echo $allowance->id ?>"><?php echo "$allowance->name ($allowance->code)" ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <button id="bulk-add-second-allowance" class="btn btn-default">Add Second Allowance</button>
            <div class="form-group" style="display: none" id="bulk-second-allowance">
              <label for="bulk-dropdown-allowance2">Select allowance from dropdown</label>
              <select class="form-control" id="bulk-dropdown-allowance2">
                <option value="">Select allowance</option>
                <?php foreach ($allowances as $allowance) : ?>
                  <option data-code="<?php echo $allowance->code ?>" value="<?php echo $allowance->id ?>"><?php echo "$allowance->name ($allowance->code)" ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <input type="hidden" class="form-control" id="bulk-input-data">
            <input type="hidden" class="form-control" id="bulk-input-allowance1_id">
            <input type="hidden" class="form-control" id="bulk-input-allowance2_id">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button style="display:none" id="bulk-btn-reason-delete" type="button" class="btn btn-danger">Delete</button>
            <button id="bulk-btn-reason-save" type="button" class="disabled btn btn-primary">Save</button>
          </div>
        </div>

      </div>
    </div>

  </div>

</div>
</div>
<script type="text/javascript">

  var selectable;

  function isSequential(data) {
    var j = Math.min(...data);
    var l = Math.max(...data);
    console.log(j);
    console.log(l);
    var k = j;
    while (k <= l)

    {
      n = data.includes(k);

      if (n == true) {

        k++;
      } else {
        return false;
      }

    }
    return true;

  }

  function daysInMonth(year, month) {
    return new Date(year, month + 1, 0).getDate();
  }

  function addMonths(date, months) {
    var target_month = date.getMonth() + months;
    var year = date.getFullYear() + parseInt(target_month / 12);
    var month = target_month % 12;
    var day = date.getDate();
    var last_day = daysInMonth(year, month);
    if (day > last_day) {
      day = last_day;
    }
    var new_date = new Date(year, month, day);
    return new_date.getFullYear() + "-" + ("0" + (new_date.getMonth() + 1)).slice(-2) + "-" + ("0" + new_date.getDate()).slice(-2);
  }


  function update() {
    var selectedItems = selectable.getSelectedNodes();
    // console.log(selectedItems);

    //console.log(selectedItems[0]);

    if (selectedItems.length > 0) {
      $("#selectable-controls").slideDown("fast");
      $("#bulk-action").text("Manage " + selectedItems.length + " allowance(s)");


      var week_selected = false;
      var items = {};
      var items_dates = {};
      var items_array = new Array();
      var items_dates_array = new Array();

      //for each selection
      $('.ui-selected').each(function(index, value) {
        items[$(this).attr('data-emp-id-x')] = true;
        items_dates[$(this).attr('data-date-x')] = true;
      });

      for (var i in items) {
        items_array.push(i);
      }
      for (var i in items_dates) {
        items_dates_array.push(parseInt(i));
      }

      //console.log(items);
      //console.log(items_dates);
      var first_selected_item = $('.ui-selected').first(); //$(selectedItems[0]);

      //console.log(first_selected_item.children("button").attr('data-date'));
      //console.log(addMonths(new Date(first_selected_item.children("button").attr('data-date')),1));

      var day_this_month = new Date(first_selected_item.children("button").attr('data-date'));
      var day_next_month = new Date(addMonths(new Date(first_selected_item.attr('data-date-short-x') + "01"), 1));

      console.log(day_this_month);
      console.log(day_next_month);
      console.log("day_this_month.getDay()" + day_this_month.getDay());
      console.log("day_next_month.getDay()" + day_next_month.getDay());

      // && items_array.length == 1

      console.log(items_dates_array);

      if (selectedItems.length > 1 && isSequential(items_dates_array)) {
        $("#week-selection").show();


        if (day_this_month.getDay() === day_next_month.getDay()) {
          $("#carry-selection").show();
        } else {
          $("#carry-selection").hide();
        }

      } else {
        $("#week-selection").hide();
        $("#carry-selection").hide();
      }

      //console.log(items_dates_array);
      // console.log(items_array.length);
      // console.log(selectedItems.length);

    } else {
      $("#selectable-controls").slideUp("fast");
    }


    //console.log($(selectedItems));
  }

  $(document).ready(function() {

    $('.apply-select2').select2();


    //$('.freeze-table').freezeTable();

    $(".freeze-table").freezeTable({
      'columnNum': 1,
      'shadow': true,
      'fixedNavbar': '.header',
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
      $('.ui-selected').each(function(index, value) {

        var d = parseInt($(this).attr("data-date-x"));
        var emp_id_x = parseInt($(this).attr("data-emp-id-x"));
        var d_temp = d;


        var this_selected_count = $('.ui-selected[data-emp-id-x="' + emp_id_x + '"]').length;
        //console.log("xxx " +this_selected_count);


        while (d_temp < 31) {

          if ((d_temp + this_selected_count) > 31) {
            console.log("continue");
            break;
          }

          d_temp = (d_temp + this_selected_count);

          if ($(this).children().attr("data-allowance1_id").length > 0 && $("#date-" + d_temp).hasClass("holiday") == false) {
            bulk_data_array.push($(this).attr("data-emp-id-x") + '|' + $(this).attr("data-date-short-x") + ("0" + d_temp).slice(-2) + '|' + $(this).children().attr("data-allowance1_id") + '|' + $(this).children().attr("data-allowance2_id"));
          } else {
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
        url: "<?php echo base_url() ?>allowances/save_assignment",
        data: {
          'data': bulk_data_array.join()
        },
        success: function(result) {
          //do somthing here
          $("#bulk-btn-reason-save").LoadingOverlay("hide");

          if (result) {

            $('#bulk-assignment-modal').modal("hide");


            var json_response = $.parseJSON(result);


            $.notify(
              "Success: allowances have been repeated", {
                position: "top center",
                className: 'success',
                style: 'bootstrap',
                gap: 20,
                autoHide: true
              }
            );


            $.each(json_response, function(index, value) {
                $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).attr("data-allowance1_id", value.allowance1_id);
                $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).attr("data-allowance2_id", value.allowance2_id);
                $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).removeClass("btn-default");
                $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).addClass("btn-primary");

                $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).text(value.code);
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
      $('.ui-selected').each(function(index, value) {

        var d = parseInt($(this).attr("data-date-x"));
        var emp_id_x = parseInt($(this).attr("data-emp-id-x"));
        var d_temp = d;

        //console.log(d_temp);


        var this_selected_count = $('.ui-selected[data-emp-id-x="' + emp_id_x + '"]').length;
        //console.log("xxx " +this_selected_count);


        //while(d_temp < 31){

        // if((d_temp+this_selected_count) > 31 ){
        //   console.log("continue");
        //   //break;
        // }

        // d_temp = (d_temp+this_selected_count);




        var full_date_temp = $(this).attr("data-date-short-x") + ("0" + (d - (parseInt(first_date) - 1))).slice(-2);

        //console.log(d + " - " + $(this).attr("data-date-short-x")+("0" + d_temp).slice(-2) + " - " + $(this).children().attr("data-emp-id") + " - " + $(this).children().attr("data-shift-id"));
        //console.log(d + " - " + addMonths(new Date(full_date_temp), 1) + " - " + $(this).children().attr("data-emp-id") + " - " + $(this).children().attr("data-shift-id"));

        //console.log(index);
        //return;

        //console.log(addMonths(new Date(full_date_temp), 1));


        //$("#btn-shift_assignment-"+$(this).attr("data-emp-id-x")+"-"+$(this).attr("data-date-short-x")+("0" + d_temp).slice(-2)).html(d);



        //bulk_data_array.push($(this).attr("data-emp-id")+'|'+$(this).attr("data-date")+'|'+valueSelected);

        if ($(this).children().attr("data-allowance1_id").length > 0) {
          bulk_data_array.push($(this).attr("data-emp-id-x") + '|' + addMonths(new Date(full_date_temp), 1) + '|' + $(this).children().attr("data-allowance1_id") + '|' + $(this).children().attr("data-allowance2_id"));
        } else {
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
        url: "<?php echo base_url() ?>allowances/save_assignment",
        data: {
          'data': bulk_data_array.join()
        },
        success: function(result) {
          //do somthing here
          $("#bulk-btn-reason-save").LoadingOverlay("hide");

          if (result) {

            $('#bulk-assignment-modal').modal("hide");


            var json_response = $.parseJSON(result);

            $.notify(
              "Success: allowances have been carried to the next month", {
                position: "top center",
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




    $('#bulk-assignment-modal').on('show.bs.modal', function(event) {

      $("#bulk-dropdown-allowance1 option[value='']").prop('selected', true);
      $("#bulk-dropdown-allowance1").trigger("change");

      $("#bulk-dropdown-allowance2 option[value='']").prop('selected', true);
      $("#bulk-dropdown-allowance2").trigger("change");

      var has_allowance_id = false;
      $('.ui-selected button').each(function(index, value) {

        if ($(this).attr("data-allowance1_id")) {
          has_allowance_id = true;

          // console.log("has attr");

        }

        if (has_allowance_id) {
          $("#bulk-btn-reason-delete").show();
        } else {
          $("#bulk-btn-reason-delete").hide();
        }

        //console.log('div' + index + ':' + $(this).attr('id'));


      });

      $('#bulk-dropdown-allowance1').on('change', function(e) {
        updateBulkSaveButton();
      });

      $('#bulk-dropdown-allowance2').on('change', function(e) {
        updateBulkSaveButton();
      });

      function updateBulkSaveButton(){
        var bulk_allowance1_id = $('#bulk-dropdown-allowance1').val();
        var bulk_allowance2_id = $('#bulk-dropdown-allowance2').val();

        var bulk_data_array = [];

        $('.ui-selected button').each(function(index, value) {

          bulk_data_array.push($(this).attr("data-emp-id") + '|' + $(this).attr("data-date") + '|' + bulk_allowance1_id + '|' + bulk_allowance2_id);

        });

        $('#bulk-input-data').val(bulk_data_array.join());
        $('#bulk-input-allowance1_id').val(bulk_allowance1_id);
        $('#bulk-input-allowance2_id').val(bulk_allowance2_id);

        if (bulk_allowance1_id.length > 0) {
          $("#bulk-btn-reason-save").removeClass("disabled");
        } else {
          $("#bulk-btn-reason-save").addClass("disabled");
          $('#bulk-input-data').val("");
        }
      }

      $("#bulk-btn-reason-save").on("click", function(e) {

        if ($(this).hasClass("disabled")) {
          return;
        }

        $("#bulk-btn-reason-save").LoadingOverlay("show");

        var bulk_input_data = $("#bulk-input-data").val();
        var bulk_input_allowance1_id = $("#bulk-input-allowance1_id").val();
        var bulk_input_allowance2_id = $("#bulk-input-allowance2_id").val();



        $.ajax({
          type: "POST",
          url: "<?php echo base_url() ?>allowances/save_assignment",
          data: {
            'data': bulk_input_data
          },
          success: function(result) {
            //do somthing here
            $("#bulk-btn-reason-save").LoadingOverlay("hide");

            if (result) {

              $('#bulk-assignment-modal').modal("hide");


              var json_response = $.parseJSON(result);

              $.notify(
                "Success: allowance(s) have been saved", {
                  position: "top center",
                  className: 'success',
                  style: 'bootstrap',
                  gap: 20,
                  autoHide: true
                }
              );


              $.each(json_response, function(index, value) {

                $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).attr("data-allowance1_id", value.allowance1_id);
                $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).attr("data-allowance2_id", value.allowance2_id);
                $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).removeClass("btn-default");
                $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).addClass("btn-primary");

                $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).text(value.code);
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

        $('.ui-selected button').each(function(index, value) {

          //naveed

          if ($(this).attr("data-allowance1_id")) {
            bulk_data_array.push($(this).attr("data-emp-id") + '|' + $(this).attr("data-date"));
          }

        });


        $.ajax({
          type: "POST",
          url: "<?php echo base_url() ?>allowances/delete_assignment",
          data: {
            'data': bulk_data_array.join()
          },
          success: function(result) {
            //do somthing here
            $("#bulk-btn-reason-delete").LoadingOverlay("hide");

            if (result) {

              $('#bulk-assignment-modal').modal("hide");

              $.notify(
                "Success: allowance(s) have been deleted", {
                  position: "top center",
                  className: 'success',
                  style: 'bootstrap',
                  gap: 20,
                  autoHide: true
                }
              );

              var json_response = $.parseJSON(result);
              $.each(json_response, function(index, value) {
                  $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).attr("data-allowance1_id", "");
                  $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).attr("data-allowance2_id", "");
                  $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).removeClass("btn-primary");
                  $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).addClass("btn-default");
                  $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).html('<span class="fa fa-plus" aria-hidden="true"></span>');
              });


              selectable.clear();
              update();


            }
          }
        });

      });


    });

    $('#add-second-allowance').on('click', function(e) {
      $("#add-second-allowance").hide();
      $("#second-allowance").show();
    });

    $('#bulk-add-second-allowance').on('click', function(e) {
      $("#bulk-add-second-allowance").hide();
      $("#bulk-second-allowance").show();
    });


    $('#assignment-modal').on('show.bs.modal', function(event) {

        var emp_id = $(event.relatedTarget).attr('data-emp-id');
        var date = $(event.relatedTarget).attr('data-date');
        var allowance1_id = $(event.relatedTarget).attr('data-allowance1_id');
        var allowance2_id = $(event.relatedTarget).attr('data-allowance2_id');

        $(this).find("#input-emp-id").val(emp_id);
        $(this).find("#input-date").val(date);
        $(this).find("#dropdown-allowance1").val(allowance1_id);
        $(this).find("#dropdown-allowance2").val(allowance2_id);

      if (allowance1_id.length > 0) {
        $("#dropdown-allowance1 option[value='" + allowance1_id + "']").prop('selected', true);
        $("#dropdown-allowance1").trigger("change");
        $("#btn-reason-delete").show();
      } else {
        $("#btn-reason-delete").hide();
      }

      if (allowance2_id.length > 0) {
        $("#add-second-allowance").hide();
        $("#second-allowance").show();
        $("#dropdown-allowance2 option[value='" + allowance2_id + "']").prop('selected', true);
        $("#dropdown-allowance2").trigger("change");
      } else {
        $("#add-second-allowance").show();
        $("#second-allowance").hide();
      }

    });

    $('#dropdown-allowance1').on('change', function(e) {
        updateSaveButton();
    });

    $('#dropdown-allowance2').on('change', function(e) {
        updateSaveButton();
    });

    function updateSaveButton() {
        var valueSelected1 = $("#dropdown-allowance1").val();
        var valueSelected2 = $("#dropdown-allowance2").val();

        if (valueSelected1.length > 0 || valueSelected2.length > 0) {
        $("#btn-reason-save").removeClass("disabled");
        } else {
        $("#btn-reason-save").addClass("disabled");
        }

        $("#btn-reason-save").closest(".modal-body").find("#dropdown-allowance1").val(valueSelected1);
        $("#btn-reason-save").closest(".modal-body").find("#dropdown-allowance2").val(valueSelected2);

    }


    $("#btn-reason-save").on("click", function(e) {

      if ($(this).hasClass("disabled")) {
        return;

      }

      $("#btn-reason-save").LoadingOverlay("show");

      var emp_id = $("#input-emp-id").val();
      var date = $("#input-date").val();
      var allowance1_id = $("#dropdown-allowance1").val();
    var allowance2_id = $("#dropdown-allowance2").val();
    if (allowance1_id == ''){
      alert('Please select an allowance');
      $("#btn-reason-save").LoadingOverlay("hide");
      return;
    } else if (allowance1_id == allowance2_id){
      alert('Please select different allowances');
      $("#btn-reason-save").LoadingOverlay("hide");
      return;
    }
      data = {
        'data': emp_id + '|' + date + '|' + allowance1_id + '|' + allowance2_id
      };

      $.ajax({
        type: "POST",
        url: "<?php echo base_url() ?>allowances/save_assignment",
        data: data,

        success: function(result) {

          //do somthing here
          $("#btn-reason-save").LoadingOverlay("hide");

          if (result) {

            var json_response = $.parseJSON(result);

            $.notify(
              "Success: allowance(s) have been saved", {
                position: "top center",
                className: 'success',
                style: 'bootstrap',
                gap: 20,
                autoHide: true
              }
            );

            // console.log(json_response);

            $('#assignment-modal').modal("hide");

            $.each(json_response, function(index, value) {

              $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).attr("data-allowance1_id", value.allowance1_id);
              $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).attr("data-allowance2_id", value.allowance2_id);
              $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).removeClass("btn-default");
              $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).addClass("btn-primary");

              $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).text(value.code);


            });





          }
        }
      });

    });


    $("#btn-reason-delete").on("click", function() {

      $("#btn-reason-delete").LoadingOverlay("show");

      var emp_id = $("#input-emp-id").val();
      var date = $("#input-date").val();

      $.ajax({
        type: "POST",
        url: "<?php echo base_url() ?>allowances/delete_assignment",
        data: {
          'data': emp_id + '|' + date
        },

        success: function(result) {
          //do somthing here
          $("#btn-reason-delete").LoadingOverlay("hide");

          if (result) {

            $('#assignment-modal').modal("hide");

            $.notify(
              "Success: allowance(s) have been deleted", {
                position: "top center",
                className: 'success',
                style: 'bootstrap',
                gap: 20,
                autoHide: true
              }
            );
            
            var json_response = $.parseJSON(result);
            $.each(json_response, function(index, value) {
              $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).html('<span class="fa fa-plus" aria-hidden="true"></span>');
              $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).attr("data-allowance1_id", "");
              $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).attr("data-allowance2_id", "");
              $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).removeClass("btn-primary");
              $('#btn-shift_assignment-' + value.employee_id + '-' + value.date).addClass("btn-default");
            });



          }
        }
      });

    });


  });

  jQuery(document).on("xcrudafterrequest", function(event, container) {
    if (Xcrud.current_task == 'save') {
      // console.log(Xcrud);
      // console.log(event);
      // console.log(container);
    }
  });
</script>

</div>