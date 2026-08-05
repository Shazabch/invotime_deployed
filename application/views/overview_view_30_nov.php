<style>
  #announcements-div::-webkit-scrollbar {
    width: 12px;
  }

  #announcements-div::-webkit-scrollbar-track {
      -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3); 
      border-radius: 10px;
  }

  #announcements-div::-webkit-scrollbar-thumb {
      border-radius: 10px;
      -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.5); 
  }
</style>
<div class="page-wrapper">
  <div class="content container-fluid">
    <div class="rowx">
      <div class="page-header-title">
        <h4 style="float:left" class="page-title">Dashboad Overview <?php //echo "(" . date('F Y') . ")"; 
                                                                    ?>

          <?php

          if (isset($branch)) {

            //echo " (Branch: " . $branch->name . ")";

          } else {
            //echo " (All Branches)";
          }
          ?>
        </h4>
        <form style="float:left" action="" method="get">
          <select style="margin-left:5px;height:26px;font-size: 11px;" class="form-control" name="branch_id" onchange="this.form.submit()">



            <option>All Outlets</option>

            <?php

            if (!isset($branch)) {
              $branch_id = 0;
            } else {
              $branch_id = $branch->id;
            }


            foreach ($branches as $row) { ?>

              <option <?php echo ($row->id == $branch_id) ? 'selected' : '' ?> value="<?php echo $row->id ?>"><?php echo $row->name ?></option>

            <?php } ?>


          </select>
        </form>
        <!-- <div class="row"></div> -->

        <h4 style="float:right" class="page-title"><b>Active users</b>: <?php //echo "(" . date('F Y') . ")"; 
                                                                  ?>

          <?php
          echo  $employees_of_company. '/' . $company_max_active_staff; echo '<br>';
          echo '<b>Outlet(s) </b>:'.$company_outlets.'/'.$company_max_outlets;
          ?>
        </h4>
      </div>
    </div>
    <div class="rowx">
      <div class="containerx">
        <div class="row">


          <div class="col-sm-12 col-lg-12 col-md-12">
            <div class="panel">
              <div class="panel-body p-t-10">

                <div class="col-md-2">
                  <div style="padding-top:20px" class="dash-widget-info">
                    <h3><?php echo $boxes[0]["box_count"] ?></h3>
                    <span><?php echo $boxes[0]["box_title"] ?></span>
                  </div>
                </div>

                <div class="col-md-10">
                  <?php echo get_user()["weather_widget"] ?>
                </div>


              </div>
            </div>

          </div>

        </div>
        <div class="row">
          <div class="col-md-12">
            <h3><b>Release Notes</b></h3>
          </div>
          <div class="col-md-12">
            <div class="card-box" style="max-height: 300px; overflow-y: auto;" id="announcements-div">
              <h3 class="m-b-30"><i class="fa fa-rocket"></i> What's New</h3>
              <?php if(count($announcements) > 0): ?>
                <?php $numbering = 1; ?>
                <?php foreach($announcements as $announcement): ?>
                  <h4 class="header-title m-t-0 m-b-20"><?= $numbering++ . ". " . $announcement->title ?></h4>
                  <span class="text-muted font-13 m-b-10"><?= $announcement->announcement ?></span>
                  <hr>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="alert alert-info">
                  No announcement made yet.
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 col-sm-6 col-lg-3">
            <div class="dash-widget clearfix card-box">
              <!-- <span class="dash-widget-icon"><i class="fa fa-cubes" aria-hidden="true"></i></span> -->
              <div class="dash-widget-info">
                <h3><?php echo $new_employees; ?></h3>
                <span>New Employees</span>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-sm-6 col-lg-2">
            <div class="dash-widget clearfix card-box">
              <!-- <span class="dash-widget-icon"><i class="fa fa-usd" aria-hidden="true"></i></span> -->
              <div class="dash-widget-info">
                <h3><?php echo $resignation_employees; ?></h3>
                <span>Resignation</span>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-sm-6 col-lg-2">
            <div class="dash-widget clearfix card-box">
              <!-- <span class="dash-widget-icon"><i class="fa fa-diamond"></i></span> -->
              <div class="dash-widget-info">
                <h3><?php echo $terminated_employees; ?></h3>
                <span>Terminated</span>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-sm-6 col-lg-2">
            <div class="dash-widget clearfix card-box">
              <!-- <span class="dash-widget-icon"><i class="fa fa-user" aria-hidden="true"></i></span> -->
              <div class="dash-widget-info">
                <h3><?php echo $turnover; ?>%</h3>
                <span>Turnover</span>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-sm-6 col-lg-3">
            <div class="dash-widget clearfix card-box">
              <!-- <span class="dash-widget-icon"><i class="fa fa-cubes" aria-hidden="true"></i></span> -->
              <div class="dash-widget-info">
                <a href="overview/manual_clocking_new?month=<?php echo date('m'); ?>&year=<?php echo date('Y'); ?>&scan_distance=invalid">
                  <h3><?php echo $invalid_clocking_distance; ?></h3>
                </a>
                <span>Invalid Clocking Distance</span>
              </div>
            </div>
          </div>

        </div>
        <div class="row">
          <div class="col-md-6">
            <div class="panel">
              <div class="panel-body p-t-10">
                <div id="graph5"></div>

                <table style="display:none" class="table" id="datatable5">
                  <thead>
                    <tr>
                      <th></th>
                      <th>Count</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($gender_breakdown as $g) : ?>
                      <tr>
                        <td><?php echo $g->sex; ?></td>
                        <td><?php echo $g->gender_count; ?></td>
                      </tr>
                    <?php endforeach; ?>


                  </tbody>
                </table>

                <script type="text/javascript">
                  Highcharts.chart('graph5', {
                    data: {
                      table: 'datatable5'
                    },
                    colors: ['#0D53CA', '#E3457A'],
                    chart: {
                      type: 'pie'
                    },
                    title: {
                      text: 'Gender Breakdown'
                    },
                    yAxis: {
                      allowDecimals: false,
                      title: {
                        text: 'Gender'
                      }
                    },
                    tooltip: {
                      pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y} ({point.percentage:.1f}%)',
                      shared: true
                    },
                    plotOptions: {
                      pie: {
                        dataLabels: {
                          enabled: true,
                          format: '<b>{point.name}</b><br>{point.y} ({point.percentage:.1f}%)',
                        }
                      }
                    }
                  });
                </script>




              </div>

            </div>

          </div>



          <?php if (isset($outlets_breakdown)) : ?>
            <div class="col-md-6">
              <div class="panel">
                <div class="panel-body p-t-10">
                  <div id="graph7"></div>

                  <table style="display:none" class="table" id="datatable7">
                    <thead>
                      <tr>
                        <th></th>
                        <th>Employees</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($outlets_breakdown as $d) : ?>
                        <tr>
                          <td><?php echo $d->name; ?></td>
                          <td><?php echo $d->count; ?></td>
                        </tr>
                      <?php endforeach; ?>


                    </tbody>
                  </table>

                  <script type="text/javascript">
                    Highcharts.chart('graph7', {
                      data: {
                        table: 'datatable7'
                      },
                      chart: {
                        type: 'pie'
                      },
                      title: {
                        text: 'Outlets Breakdown'
                      },
                      yAxis: {
                        allowDecimals: false,
                        title: {
                          text: 'Employees'
                        }
                      },
                      tooltip: {
                        pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y} ({point.percentage:.1f}%)',
                        shared: true
                      },
                      plotOptions: {
                        pie: {
                          dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b><br>{point.y} ({point.percentage:.1f}%)',
                          }
                        }
                      }
                    });
                  </script>
                </div>
              </div>
            </div>

          <?php endif; ?>

          <div class="col-md-6">
            <div class="panel">
              <div class="panel-body p-t-10">
                <div id="graph6"></div>

                <table style="display:none" class="table" id="datatable6">
                  <thead>
                    <tr>
                      <th></th>
                      <th>Employees</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($departments_breakdown as $d) : ?>
                      <tr>
                        <td><?php echo $d->name; ?></td>
                        <td><?php echo $d->count; ?></td>
                      </tr>
                    <?php endforeach; ?>


                  </tbody>
                </table>

                <script type="text/javascript">
                  Highcharts.chart('graph6', {
                    data: {
                      table: 'datatable6'
                    },
                    chart: {
                      type: 'pie'
                    },
                    title: {
                      text: 'Departments Breakdown'
                    },
                    yAxis: {
                      allowDecimals: false,
                      title: {
                        text: 'Employees'
                      }
                    },
                    tooltip: {
                      pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y} ({point.percentage:.1f}%)',
                      shared: true
                    },
                    plotOptions: {
                      pie: {
                        dataLabels: {
                          enabled: true,
                          format: '<b>{point.name}</b><br>{point.y} ({point.percentage:.1f}%)',
                        }
                      }
                    }
                  });
                </script>
              </div>
            </div>
          </div>


          <div class="col-md-6">
            <div class="panel">
              <div class="panel-body p-t-10">
                <div id="graphAttendance"></div>

                <table style="display:none" class="table" id="datatableAttendance">
                  <thead>
                    <tr>
                      <th></th>
                      <th>Count</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>Late</td>
                      <td><?php echo $late_today_count->cnt; ?></td>
                    </tr>
                    <tr>
                      <td>Early</td>
                      <td><?php echo $early_today_count->cnt; ?></td>
                    </tr>
                    <tr>
                      <td>On Time</td>
                      <td><?php echo $ontime_today_count->cnt; ?></td>
                    </tr>
                    <tr>
                      <td>On Leave</td>
                      <td><?php echo $onleave_today_count->cnt; ?></td>
                    </tr>
                    <tr>
                      <td>Absent</td>
                      <td><?php echo $absent_today_count->cnt; ?></td>
                    </tr>
                  </tbody>
                </table>

                <script type="text/javascript">
                  Highcharts.chart('graphAttendance', {
                    data: {
                      table: 'datatableAttendance'
                    },
                    chart: {
                      type: 'pie'
                    },
                    title: {
                      text: 'Attendance Breakdown (Today)'
                    },
                    yAxis: {
                      allowDecimals: false,
                      title: {
                        text: 'Gender'
                      }
                    },
                    tooltip: {
                      pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y} ({point.percentage:.1f}%)',
                      shared: true
                    },
                    plotOptions: {
                      pie: {
                        dataLabels: {
                          enabled: true,
                          format: '<b>{point.name}</b><br>{point.y} ({point.percentage:.1f}%)',
                        }
                      }
                    }
                  });
                </script>




              </div>

            </div>

          </div>

        </div>

        <div class="row col-md-12">
          
        </div>






        <!-- <div class="row">
                            <div class="col-md-12">
                              <div class="panel">
                                <div class="panel-body p-t-10">
                                  <div class="row">
                                    <div class="col-md-12">
                                      <h4>Late (Today)</h4>
                                      <div style="max-height:600px; overflow:auto;">
                                      <table  class="table datatablex">
                                        <thead>
                                          <tr>
                                            <th scope="col"><strong>Name</strong></th>
                                            <th scope="col"><strong>Outlet</strong></th>
                                            <th scope="col"><strong>Department</strong></th>
                                            <th scope="col"><strong>Shift</strong></th>
                                            <th scope="col"><strong>Clocked In</strong></th>
                                          </tr>
                                        </thead>
                                        <tbody>

                                          <?php foreach ($late_today as $item) : ?>
                                          <tr>
                                            <td><?php echo $item->first_name ?>
                                            <br/>
                                                        
                                                        <div style="min-width:100px !important">
                                                     
<a title="Clocking Data" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/manual_clocking_new?month=<?php echo date('m') ?>&emp=<?php echo $item->employee_id ?>"><i style="font-size:15px" class="la la-hourglass-start"></i></a>
 
<a title="Employee Report" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/employee_report/<?php echo $item->employee_id ?>?<?php echo "month=" . date('m') ?>"><i style="font-size:15px" class="far fa-clock"></i></a>

<a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>summary/view/<?php echo $item->employee_id ?>"><i style="font-size:15px" class="far fa-address-card"></i></a>

</div>
                                            
                                            </td>
                                            <td><?php echo $item->branch_name ?></td>
                                            <td><?php echo $item->department_name ?></td>
                                            <td><?php echo $item->shift_name ?> <br /> <?php echo $item->start_time ?></td>
                                            <td><?php echo $item->clock_in ?></td>
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
                          </div> -->





      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div id="winner-modal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><?php

                                if (isset($event)) {

                                  echo "Pull a winner from  <strong>" . $event->event_name_english . "<strong>";
                                } else {
                                  echo "Select an event to pull a winner";
                                }
                                ?></h4>
      </div>
      <div class="modal-body">

        <div style="display:none" id="winner-loading" class="text-center">
          <img style="width:100px;" src="<?php base_url() ?>uploads/25ef280441ad6d3a5ccf89960b4e95eb.gif" />
        </div>
        <div id="winner-container" style="display:none" class="text-center">

          <h1 style="color:#00B045">Winner Found</h1>
          <h5 id="winner_qr">QR</h5>
          <h1 id="winner_name">Name</h1>
          <h3 id="winner_company">Company</h3>
          <h3 id="winner_phone">visitor_phone</h3>
          <h3 id="winner_email">visitor_email</h3>
        </div>

        <?php if (isset($event)) { ?>
          <button style="margin-top:30px" id="btn-winner" type="button" class="btn btn-primary btn-lg btn-block">Pull Now</button>



        <?php } ?>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<script>
  $(document).ready(function() {
    $(".datatablex").DataTable({
      "lengthMenu": [5, 10, 20, 40, 60, 80, 100],
      "pageLength": 5
    });

    $('.datatable2').DataTable({
      "order": [
        [3, "desc"]
      ],
      "lengthMenu": [5, 10, 20, 40, 60, 80, 100],
      "pageLength": 5
    });

  });
</script>




<!-- <script>
  
  $(document).ready(function(){


    $('#winner-modal').on('hidden.bs.modal', function (e) {
      // do something...
      //alert("jhjh");

      $("#winner-container").hide();
      $("#winner-loading").hide();

      $("#winner_qr").html("");
      $("#winner_name").html("");
      $("#winner_company").html("");
      $("#winner_phone").html("");
      $("#winner_email").html("");


    });
    

    $("#btn-winner").click(function(){

    $("#winner-container").hide();

    $("#winner-loading").show();

      setTimeout(function(){

         $.ajax({url: "api/pull_winner?event_id="+'<?php echo $event->id; ?>', success: function(result){
          //console.log(result);

          if(result.winner != null){        
            $("#winner-container").show();
            $("#winner-loading").hide();

            $("#winner_qr").html(result.winner.qr_code);
            $("#winner_name").html(result.winner.visitor_name);
            $("#winner_company").html(result.winner.visitor_company);
            $("#winner_phone").html(result.winner.visitor_phone);
            $("#winner_email").html(result.winner.visitor_email);
          }
          else{
            $("#winner-loading").hide();
            alert("No winner found, try again");
          }
        }});

       }, 2000);
     


    });

  });

</script> -->
<!-- 

            <footer class="footer"> &copy; <?php echo date("Y"); ?> <?php echo antelope_config()["antelope_brand_name"] ?> - All Rights Reserved. </footer> -->
</div>