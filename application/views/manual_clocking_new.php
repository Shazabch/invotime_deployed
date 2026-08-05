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
                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="sel1">Outlet</label>
                        <select class="form-control apply-select2" id="branch" name="branch">
                          <option value="">All</option>
                          <?php foreach ($branches as $branch): ?>
                            <option <?php echo ($branch->id == $selected_branch_id) ? 'selected' : '' ?> value="<?php echo $branch->id ?>"><?php echo $branch->name ?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>

                    </div>

                    <div class="col-md-2">
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
                        <label for="sel1">Position</label>
                        <select class="form-control apply-select2" id="pos" name="pos">
                          <option value="">All</option>
                          <?php foreach ($positions as $pos): ?>
                            <option <?php echo ($pos->id == $selected_pos_id) ? 'selected' : '' ?> value="<?php echo $pos->id ?>"><?php echo $pos->name ?></option>
                          <?php endforeach; ?>

                        </select>
                      </div>

                    </div>

                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="sel1">Devices</label>
                        <select class="form-control apply-select2" id="dev" name="dev">
                          <option value="">All</option>
                          <?php foreach ($devices as $dev): ?>
                            <option <?php echo ($dev->device_id == $selected_dev_id) ? 'selected' : '' ?> value="<?php echo $dev->device_id ?>"><?php echo $dev->mac_address ?></option>
                          <?php endforeach; ?>

                        </select>
                      </div>

                    </div>

                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="daterange_filter">Date Range</label>
                        <input type="text" class="form-control" id="daterange_filter" name="daterange_filter" autocomplete="off" value="<?php echo isset($selected_daterange) ? htmlspecialchars($selected_daterange) : '' ?>">
                      </div>
                    </div>

                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="sel1">Distance</label>
                        <select class="form-control apply-select2" id="sel1" name="scan_distance">
                          <option <?php echo ('all' == $selected_distance) ? 'selected' : '' ?> value="all">All</option>
                          <option <?php echo ('invalid' == $selected_distance) ? 'selected' : '' ?> value="invalid">Invalid</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="form-group">
                        <label for="sel1">Mode</label>
                        <select class="form-control apply-select2" id="sel1" name="mode">
                          <!-- - Face
                          - FP
                          - SCK
                          - PALM
                          - BLU
                          - QR -->
                          <option <?php echo ('' == $selected_mode) ? 'selected' : '' ?> value="">All</option>
                          <option <?php echo ('FACE' == $selected_mode) ? 'selected' : '' ?> value="FACE">FACE</option>
                          <option <?php echo ('FP' == $selected_mode) ? 'selected' : '' ?> value="FP">FP</option>
                          <option <?php echo ('SCK' == $selected_mode) ? 'selected' : '' ?> value="SCK">SCK</option>
                          <option <?php echo ('PALM' == $selected_mode) ? 'selected' : '' ?> value="PALM">PALM</option>
                          <option <?php echo ('BLU' == $selected_mode) ? 'selected' : '' ?> value="BLU">BLU</option>
                          <option <?php echo ('QR' == $selected_mode) ? 'selected' : '' ?> value="QR">QR</option>
                        </select>

                      </div>
                    </div>

                    <div class="col-md-2">
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
                  $year = $selected_year;
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
                            <th>Location</th>
                            <th>Mode</th>
                            <th>Clocking Distance</th>
                            <th>Clocking Location</th>
                            <th>Clocking Remark</th>
                            <!-- <th>Temperature</th> -->
                            <th>Type</th>
                            <th>Datetime</th>
                            <th>Action</th>

                          </tr>
                        </thead>
                        <tbody>

                          <style type="text/css">
                            .color-in {
                              color: green;
                            }

                            .color-out {
                              color: red;
                            }
                          </style>

                          <?php foreach ($clockings as $c): ?>
                            <tr>
                              <td><b>
                                  <?php if (is_page_permitted('manual_clocking_new')) : ?>
                                    <a href="<?php echo base_url() ?>overview/manual_clocking_new?branch=<?php echo $selected_branch_id ?>&emp=<?php echo $c["employee_id"] ?>&daterange_filter=<?php echo urlencode($selected_daterange) ?>">
                                    <?php endif ?>
                                    <?php echo $c["first_name"] ?>
                                    <?php if (is_page_permitted('manual_clocking_new')) : ?>
                                    </a>
                                  <?php endif ?>

                                </b><br /> <?php echo $c["special_id"] ?>

                                <br />

                                <div style="min-width:150px !important">
                                  <?php if (is_page_permitted('manual_clocking_new')) : ?>
                                    <a title="Clocking Data" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/manual_clocking_new?daterange_filter=<?php echo urlencode($selected_daterange) ?>&emp=<?php echo $c["employee_id"] ?>"><i style="font-size:15px" class="fa fa-hourglass-half"></i></a>
                                  <?php endif ?>
                                  <?php if (is_page_permitted('employee_report')) : ?>
                                    <a title="Employee Report" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/employee_report/<?php echo $c["employee_id"] ?>?<?php echo "month=" . $selected_month ?>&year=<?php echo $selected_year ?>"><i style="font-size:15px" class="fa fa-clock"></i></a>
                                  <?php endif ?>
                                  <?php if (is_page_permitted('view')) : ?>
                                    <a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>summary/view/<?php echo $c["employee_id"] ?>?<?php echo "from=01%2F" . $selected_month . "%2F" . $selected_year ?>&<?php echo "to=" . last_day_of_month($selected_month) . "%2F" . $selected_month . "%2F" . $selected_year ?>"><i style="font-size:15px" class="fa fa-address-card"></i></a>
                                  <?php endif ?>

                                </div>


                              </td>
                              <td>
                                <?php echo $c["branch_name_clocking"] == "" ? $c["branch_name"] : $c["branch_name_clocking"] ?>
                              </td>
                              <td>
                                <?php echo $c["shift_name"] ?>
                              </td>
                              <td>
                                <?php echo $c["mac_address"] ?>
                              </td>
                              <td>
                                <?php echo $c["location"] ?>
                              </td>
                              <td>
                                <?php echo $c["mode"] ?>
                              </td>
                              <td>

                                <?php if (!empty($c["scan_distance"])): ?>



                                  <?php if (round($c["scan_distance"]) > 30): ?>
                                    <span style="font-weight:bold"><?php echo round($c["scan_distance"]) ?>m</span>
                                  <?php else: ?>
                                    <?php echo round($c["scan_distance"]) ?>m
                                  <?php endif; ?>
                                <?php endif ?>


                              </td>
                              <td>
                                <?= map_address($c['address']) ?: 'N/A' ?>
                                <?php
                                // Logic: Show button if address is empty, N/A, or contains "failed"/"timeout"
                                $addr = strtolower(trim($c['address'] ?? ''));
                                $is_error = (empty($addr) || $addr == 'N/A' || $addr == '' || stripos($addr, 'failed') !== false || stripos($addr, 'timeout') !== false);
                                $has_coords = (!empty($c['latlon']) && strpos($c['latlon'], ',') !== false && $c['latlon'] !== '0,0');

                                if ($is_error && $has_coords): ?>
                                  <button type="button"
                                    class="btn-refresh-addr"
                                    title="Fix Address"
                                    style="background: none; border: none; padding: 0; cursor: pointer; outline: none; box-shadow: none;"
                                    data-id="<?php echo $c['id']; ?>"
                                    data-latlon="<?php echo $c['latlon']; ?>">
                                    <i class="fa fa-refresh text-primary"></i>
                                  </button>
                                <?php endif; ?>

                              </td>
                              <td>
                                <?php echo $c['clocking_remark'] ?>
                              </td>
                              <!-- <td>
                                                        <?php if (is_null($c['temprature'])) : ?>
                                                          N/A
                                                        <?php else: ?>
                                                          <?php if ($c['temprature'] >= "37.3") : ?>
                                                            <span class="text-danger"><?php echo $c['temprature'] ?></span>
                                                          <?php else: ?>
                                                            <?php echo $c['temprature'] ?>
                                                          <?php endif ?>
                                                        <?php endif ?>
                                                      </td> -->
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
                              <td style="min-width: 100px;">
                                <?php if ($is_emp_summary_editable): ?>
                                  <button id="btn-shift_assignment-<?php echo $c["id"] ?>" data-clocking-id="<?php echo $c["id"] ?>" data-clocking-type="<?php echo $c["type"] ?>" data-clocking-datetime="<?php echo $c["datetime"] ?>" class="btn btn-primary btn-sm" style="margin-bottom: 2px;" aria-label="Edit Clocking" data-toggle="modal" data-target="#assignment-modal">
                                    <span class="fa fa-edit" aria-hidden="true"></span>
                                  </button>
                                <?php endif ?>

                                <?php
                                if (
                                  !is_null($c['latlon']) &&
                                  trim($c['latlon']) !== '' &&
                                  strtolower(trim($c['latlon'])) !== 'n/a'
                                ) :
                                ?>

                                  <button id="btn-display-map-<?php echo $c["id"] ?>" data-clocking-id="<?php echo $c["id"] ?>" data-clocking-type="<?php echo $c["type"] ?>" data-clocking-datetime="<?php echo $c["datetime"] ?>" class="btn btn-primary btn-sm" data-scan-distance="<?php echo $c["scan_distance"] ?>" data-latlon="<?php echo $c["latlon"] ?>" aria-label="Clocking Map" data-toggle="modal" data-target="#map-modal">
                                    <span class="fa fa-map" aria-hidden="true"></span>
                                  </button>
                                <?php endif ?>
                                <?php if ($c['selfie_url']) : ?>
                                  <button id="btn-display-selfie-<?php echo $c["id"] ?>"
                                    data-clocking-id="<?php echo $c["id"] ?>"
                                    data-selfie-url="<?php echo $c['selfie_url'] ?>"
                                    class="btn btn-primary btn-sm"
                                    aria-label="Clocking Selfie"
                                    data-toggle="modal"
                                    data-target="#selfie-model">
                                    <span class="fa fa-eye" aria-hidden="true"></span>
                                  </button>
                                <?php endif ?>
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

                      <?php if ($page > 1): ?>
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

                      if ($page > 3 && $total_pages > 10) {
                        echo '<li class="page-item "><a class="page-link">. . .</a></li>';
                      }

                      for ($x = 1; $x <= $total_pages; $x++):

                        if ($page == $x) {
                          $active = "active";
                          $selected_page = $x;
                        } else {
                          $active = "";
                        }

                      ?>

                        <?php

                        //echo $page;

                        if ($total_pages > 10) {

                          // if(!$dots_added){

                          // }
                          // $dots_added = true;

                          //continue;



                          if (($x > ($page - 3)) &&  ($x < ($page + 3))) {

                        ?>
                            <li class="page-item <?php echo $active ?>"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $x ?>"><?php echo $x ?></a></li>


                        <?php

                          }
                        }
                        ?>

                        <?php

                        if ($total_pages <= 10) {
                        ?>

                          <li class="page-item <?php echo $active ?>"><a class="page-link" href="<?php echo $pagination_url ?>&page=<?php echo $x ?>"><?php echo $x ?></a></li>

                        <?php
                        }
                        ?>

                      <?php endfor;

                      if (($page < ($total_pages - 2)) && $total_pages > 10) {
                        echo '<li class="page-item "><a class="page-link">. . .</a></li>';
                      }

                      ?>


                      <?php if ($page < $total_pages): ?>
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


    <div id="map-modal" class="modal fade" role="dialog">
      <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">
              &times;
            </button>
            <h4 class="modal-title">Clocking Map</h4>
          </div>
          <div class="modal-body map-container"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">
              Close
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Selfie Modal -->
    <div class="modal fade" id="selfie-model" tabindex="-1" role="dialog" aria-labelledby="selfieModelLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="selfieModelLabel">Clocking Selfie</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body text-center">
            <img id="clocking-selfie-img" src="" alt="Selfie" class="img-fluid rounded shadow" style="height: 500px;">
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
            <button id="btn-reason-delete" type="button" class="btn btn-danger">Delete</button>
            <button id="btn-reason-save" type="button" class="btn btn-primary">Save</button>
          </div>
        </div>

      </div>
    </div>



  </div>

</div>
</div>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@3/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@3/daterangepicker.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Attach event listener to all buttons starting with "btn-display-selfie-"
    document.querySelectorAll("[id^='btn-display-selfie-']").forEach(function(button) {
      button.addEventListener("click", function() {
        let clockingId = this.dataset.clockingId;

        // TODO: replace with actual API call OR signed URL fetch
        // Example: pass signed URL directly in data attribute from PHP
        let selfieUrl = this.dataset.selfieUrl;

        // Set image in modal
        document.getElementById("clocking-selfie-img").src = selfieUrl;
      });
    });
  });
</script>
<script type="text/javascript">
  $(document).ready(function() {
    $(document).on('click', '.btn-refresh-addr', function(e) {
      e.preventDefault();

      var btn = $(this);
      var icon = btn.find('i');
      var clockingId = btn.data('id');
      var latlon = btn.data('latlon');

      // UI Loading state
      btn.prop('disabled', true);
      icon.addClass('fa-spin');

      $.ajax({
        type: "POST",
        url: "<?php echo base_url() ?>overview/refresh_address_ajax",
        data: {
          id: clockingId,
          latlon: latlon
        },
        dataType: 'json',
        success: function(res) {
          // Check if the server returned a logical success
          if (res.success) {
            // Refresh the entire page to show the updated data from the database
            window.location.reload();
          } else {
            // Handle application-level errors (e.g., API limit reached or DB write failure)
            alert("Update Failed: " + (res.message || "Unknown error occurred."));
            resetButton();
          }
        },
        error: function(xhr, status, error) {
          // Robust error handling for different scenarios
          var errorMessage = "Network error. Please try again.";

          if (xhr.status === 0) {
            errorMessage = "Not connected. Verify your network connection.";
          } else if (xhr.status == 404) {
            errorMessage = "The requested page was not found. [404]";
          } else if (xhr.status == 500) {
            errorMessage = "Internal Server Error [500]. Please contact IT support.";
          } else if (status === 'timeout') {
            errorMessage = "The request timed out. The server might be busy.";
          }

          alert(errorMessage);
          console.error("AJAX Error:", status, error, xhr.responseText);
          resetButton();
        }
      });

      // Helper to restore button state
      function resetButton() {
        btn.prop('disabled', false);
        icon.removeClass('fa-spin');
      }
    });
    $('.apply-select2').select2();

    $('#daterange_filter').daterangepicker({
      autoUpdateInput: true,
      alwaysShowCalendars: true,
      dateLimit: {
        months: 1
      },
      locale: {
        format: 'DD/MM/YYYY',
        separator: ' - '
      },
      startDate: moment("<?php echo isset($selected_daterange) ? explode(' - ', $selected_daterange)[0] : date('d/m/Y') ?>", 'DD/MM/YYYY'),
      endDate: moment("<?php echo isset($selected_daterange) ? (explode(' - ', $selected_daterange)[1] ?? explode(' - ', $selected_daterange)[0]) : date('d/m/Y') ?>", 'DD/MM/YYYY'),
      ranges: {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
      }
    });

    // Safety net: if a range still ends up spanning two different calendar months
    // (e.g. picked via typing, or 31-Jul to 1-Aug via dateLimit), clamp end date
    // back to the last day of the start date's month.
    $('#daterange_filter').on('apply.daterangepicker', function(ev, picker) {
      if (picker.startDate.month() !== picker.endDate.month() || picker.startDate.year() !== picker.endDate.year()) {
        picker.endDate = moment(picker.startDate).endOf('month');
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
      }
    });



    //$('.freeze-table').freezeTable();

    $(".freeze-table").freezeTable({
      'columnNum': 1,
      'shadow': true,
      'fixedNavbar': '.header'

    });


    const table = document.querySelector("table");

    $("#map-modal").on("show.bs.modal", function(event) {
      const clocking_id = $(event.relatedTarget).attr("data-clocking-id");
      const clocking_type = $(event.relatedTarget).attr('data-clocking-type');
      const clocking_datetime = $(event.relatedTarget).attr('data-clocking-datetime');
      const scan_distance = $(event.relatedTarget).attr("data-scan-distance");
      const latlon = $(event.relatedTarget).attr("data-latlon");
      $(".map-container").append(
        `<iframe width="100%" height="400px" src="https://maps.google.com/maps?q=${latlon}&t=&z=12&ie=UTF8&iwloc=&output=embed" style="border-radius: 1%;" id="gmap_canvas" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"></iframe>`
      );
    });

    $("#map-modal").on('hide.bs.modal', function(event) {
      $("#gmap_canvas").fadeOut(300, function() {
        $(this).remove()
      });
    });


    $('#assignment-modal').on('show.bs.modal', function(event) {



      var clocking_id = $(event.relatedTarget).attr('data-clocking-id');
      var clocking_type = $(event.relatedTarget).attr('data-clocking-type');
      var clocking_datetime = $(event.relatedTarget).attr('data-clocking-datetime');

      console.log(clocking_id + " aaa");
      console.log(clocking_type + " aaa");
      console.log(clocking_datetime + " aaa");

      $(this).find("#input-clocking-id").val(clocking_id);
      $(this).find("#input-clocking-type").val(clocking_type);
      $(this).find("#input-clocking-datetime").val(clocking_datetime);


      $("#dropdown-reason option[value='" + clocking_type + "']").prop('selected', true);
      $("#dropdown-reason").trigger("change");

      $(this).find("#input-datetime").val(clocking_datetime);
      $("#input-datetime").trigger("change");


    });

    $('#dropdown-reason').on('change', function(e) {
      var valueSelected = this.value;

      console.log(valueSelected);
      $(this).closest(".modal-body").find("#input-clocking-type").val(valueSelected);


    });

    $('#input-datetime').on('change', function(e) {
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
        data: {
          'clocking_id': clocking_id,
          'clocking_type': clocking_type,
          'clocking_datetime': clocking_datetime
        },

        success: function(result) {

          //do somthing here
          $("#btn-reason-save").LoadingOverlay("hide");

          if (result) {

            var json_response = $.parseJSON(result);

            console.log(json_response);

            $('#assignment-modal').modal("hide");



            $('#btn-shift_assignment-' + clocking_id).attr("data-clocking-type", clocking_type);
            $('#btn-shift_assignment-' + clocking_id).attr("data-clocking-datetime", clocking_datetime);
            $('#label-shift_assignment-' + clocking_id).text(clocking_type);
            $('#label-clocking_datetime-' + clocking_id).text(clocking_datetime);


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
        data: {
          'clocking_id': clocking_id
        },

        success: function(result) {
          //do somthing here
          $("#btn-reason-delete").LoadingOverlay("hide");

          if (result) {

            $('#assignment-modal').modal("hide");


            var json_response = $.parseJSON(result);

            $('#btn-shift_assignment-' + clocking_id).closest("tr").slideUp();

          }
        }
      });

    });


  });
</script>

</div>