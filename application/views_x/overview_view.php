<div class="page-wrapper">
            <div class="content container-fluid">
                <div class="rowx">
                    <div class="page-header-title">
                        <h4 style="float:left" class="page-title">Dashboad Overview <?php echo "(" . date('F Y') . ")"; ?>

                          <?php

                            if(isset($branch)){

                                //echo " (Branch: " . $branch->name . ")";

                            }
                            else{
                                //echo " (All Branches)";
                            }
                           ?>
                        </h4>  <form style="float:left" action="" method="get">
                              <select style="margin-left:5px;height:26px;font-size: 11px;" class="form-control" name="branch_id" onchange="this.form.submit()">


                                
                                <option>All Outlets</option>

                                <?php 

                                if(!isset($branch)){
                                  $branch_id = 0;
                                }
                                else
                                {
                                  $branch_id = $branch->id;
                                }


                                 foreach ($branches as $row) { ?>
                                  
                                  <option <?php echo ($row->id == $branch_id) ? 'selected' : '' ?> value="<?php echo $row->id ?>"><?php echo $row->name ?></option>
                                
                                <?php } ?>


                              </select>
                            </form>
                            <div class="row"></div>

                        
                    </div>
                </div>
                <div class="rowx">
                    <div class="containerx">
                      <div class="row">

                        <?php foreach($boxes as $box): ?>

                          <div class="col-sm-12 col-lg-<?php echo $box["width"] ?>  col-md-<?php echo $box["width"] ?>">

                            <div class="dash-widget clearfix card-box">
                              <!-- style="height:30px;width:30px;font-size:15px;line-height: 30px" -->
                              <span  class="dash-widget-icon"><i class="fa fa-cubes" aria-hidden="true"></i></span>
                              <div class="dash-widget-info">
                                <h3><?php echo $box["box_count"] ?></h3>
                                <span><?php echo $box["box_title"] ?></span>
                              </div>
                            </div>
                          </div>

                        <?php endforeach; ?>

                      </div>

                      <div class="row">
                        <div class="col-md-12">
                          <div class="panel">
                            <div class="panel-body p-t-10">
                              <?php echo get_user()["weather_widget"] ?>


                              <!-- <a class="weatherwidget-io" href="https://forecast7.com/en/3d16101d59/kota-damansara/" data-label_1="KOTA DAMANSARA" data-label_2="WEATHER" data-theme="light" >KOTA DAMANSARA WEATHER</a>
<script>
!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='https://weatherwidget.io/js/widget.min.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','weatherwidget-io-js');
</script> -->
                              
                            </div>
                            
                          </div>
                          
                        </div>
                        <div class="col-md-6">
                          <div class="panel">
                            <div class="panel-body p-t-10">
                              <!-- <h4>Calendar</h4> -->

                              <div id="calendar">
                                
                              </div>

                              <script type="text/javascript">
                                $( document ).ready(function() {
                                  // Handler for .ready() called.
                                  $('#calendar').fullCalendar({
                                    defaultView: 'month'
                                  });
                                });
                                
                              </script>
                              
                            </div>
                            
                          </div>
                          
                        </div>

                        <div class="col-md-6">
                          <div class="panel">
                            <div class="panel-body p-t-10">

                              

                              <div style="padding:0px" class="task-wrapper">
                                <button class="add-task-btn btn btn-primary btn-sm">
                                Add Task
                              </button>
                          <div class="task-list-container">
                            <div class="task-list-body">
                              <ul id="task-list">
                                <li class="task">
                                  <div class="task-container">
                                    <span class="task-action-btn task-check">
                                      <span class="action-circle large complete-btn" title="Mark Complete">
                                        <i class="material-icons">check</i>
                                      </span>
                                    </span>
                                    <span class="task-label" contenteditable="true">Task 1</span>
                                    <span class="task-action-btn task-btn-right">
                                      <span class="action-circle large" title="Assign">
                                        <i class="material-icons">person_add</i>
                                      </span>
                                      <span class="action-circle large delete-btn" title="Delete Task">
                                        <i class="material-icons">delete</i>
                                      </span>
                                    </span>
                                  </div>
                                </li>
                                <li class="task">
                                  <div class="task-container">
                                    <span class="task-action-btn task-check">
                                      <span class="action-circle large complete-btn" title="Mark Complete">
                                        <i class="material-icons">check</i>
                                      </span>
                                    </span>
                                    <span class="task-label" contenteditable="true">Send UI design to the designer</span>
                                    <span class="task-action-btn task-btn-right">
                                      <span class="action-circle large" title="Assign">
                                        <i class="material-icons">person_add</i>
                                      </span>
                                      <span class="action-circle large delete-btn" title="Delete Task">
                                        <i class="material-icons">delete</i>
                                      </span>
                                    </span>
                                  </div>
                                </li>
                                <li class="completed task">
                                  <div class="task-container">
                                    <span class="task-action-btn task-check">
                                      <span class="action-circle large complete-btn" title="Mark Complete">
                                        <i class="material-icons">check</i>
                                      </span>
                                    </span>
                                    <span class="task-label">Task 3</span>
                                    <span class="task-action-btn task-btn-right">
                                      <span class="action-circle large" title="Assign">
                                        <i class="material-icons">person_add</i>
                                      </span>
                                      <span class="action-circle large delete-btn" title="Delete Task">
                                        <i class="material-icons">delete</i>
                                      </span>
                                    </span>
                                  </div>
                                </li>
                                <li class="task">
                                  <div class="task-container">
                                    <span class="task-action-btn task-check">
                                      <span class="action-circle large complete-btn" title="Mark Complete">
                                        <i class="material-icons">check</i>
                                      </span>
                                    </span>
                                    <span class="task-label" contenteditable="true">Task 4</span>
                                    <span class="task-action-btn task-btn-right">
                                      <span class="action-circle large" title="Assign">
                                        <i class="material-icons">person_add</i>
                                      </span>
                                      <span class="action-circle large delete-btn" title="Delete Task">
                                        <i class="material-icons">delete</i>
                                      </span>
                                    </span>
                                  </div>
                                </li>
                                <li class="task">
                                  <div class="task-container">
                                    <span class="task-action-btn task-check">
                                      <span class="action-circle large complete-btn" title="Mark Complete">
                                        <i class="material-icons">check</i>
                                      </span>
                                    </span>
                                    <span class="task-label" contenteditable="true">Private chat module</span>
                                    <span class="task-action-btn task-btn-right">
                                      <span class="action-circle large" title="Assign">
                                        <i class="material-icons">person_add</i>
                                      </span>
                                      <span class="action-circle large delete-btn" title="Delete Task">
                                        <i class="material-icons">delete</i>
                                      </span>
                                    </span>
                                  </div>
                                </li>
                                <li class="task">
                                  <div class="task-container">
                                    <span class="task-action-btn task-check">
                                      <span class="action-circle large complete-btn" title="Mark Complete">
                                        <i class="material-icons">check</i>
                                      </span>
                                    </span>
                                    <span class="task-label" contenteditable="true">Test</span>
                                    <span class="task-action-btn task-btn-right">
                                      <span class="action-circle large" title="Assign">
                                        <i class="material-icons">person_add</i>
                                      </span>
                                      <span class="action-circle large delete-btn" title="Delete Task">
                                        <i class="material-icons">delete</i>
                                      </span>
                                    </span>
                                  </div>
                                </li>
                                <li class="task">
                                  <div class="task-container">
                                    <span class="task-action-btn task-check">
                                      <span class="action-circle large complete-btn" title="Mark Complete">
                                        <i class="material-icons">check</i>
                                      </span>
                                    </span>
                                    <span class="task-label" contenteditable="true">Add more tasks</span>
                                    <span class="task-action-btn task-btn-right">
                                      <span class="action-circle large" title="Assign">
                                        <i class="material-icons">person_add</i>
                                      </span>
                                      <span class="action-circle large delete-btn" title="Delete Task">
                                        <i class="material-icons">delete</i>
                                      </span>
                                    </span>
                                  </div>
                                </li>
                              </ul>
                            </div>
                            <div class="task-list-footer">
                              <div class="new-task-wrapper">
                                <textarea  id="new-task" placeholder="Enter new task here. . ."></textarea>
                                <span class="error-message hidden">You need to enter a task first</span>
                                <span class="add-new-task-btn btn btn-default btn-sm" id="add-task">Add Task</span>
                                <span class="cancel-btn btn btn-sm btn-default" id="close-task-panel">Close</span>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="notification-popup hide">
                          <p>
                            <span class="task"></span>
                            <span class="notification-text"></span>
                          </p>
                        </div>
                              
                            </div>
                            
                          </div>
                          
                        </div>
                        
                      </div>

                      <div class="row">

                        <div class="col-md-6">
                            <div class="panel">
                              <div class="panel-body p-t-10">
                                <div id="graph1"></div>

                                <table style="display:none" class="table" id="datatable">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Ontime</th>
                                            <th>Early</th>
                                            <th>Late</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                      <?php foreach ($chart1_data as $row): ?>
                                        <tr>
                                            <th><?php echo $row->department_name; ?></th>
                                            <td><?php echo $row->ontime; ?></td>
                                            <td><?php echo $row->early; ?></td>
                                            <td><?php echo $row->late; ?></td>
                                        </tr>
                                        <?php endforeach; ?>


                                    </tbody>
                                </table>

                                <script type="text/javascript">
                                  Highcharts.chart('graph1', {
                                    data: {
                                        table: 'datatable'
                                    },
                                    colors: ['#777','#5cb45b','#FF0000'],
                                    chart: {
                                        type: 'column'
                                    },
                                    title: {
                                        text: 'Clocking Status by Department'
                                    },
                                    yAxis: {
                                        allowDecimals: false,
                                        title: {
                                            text: 'Percentage'
                                        }
                                    },
                                    tooltip: {
                                          pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>',
                                          shared: true
                                      },
                                    plotOptions: {
                                        column: {
                                            stacking: 'percent'
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
                                <div id="graph2"></div>

                                <table style="display:none" class="table" id="datatable2">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Hours</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                      <?php foreach ($chart2_data as $row): ?>
                                        <tr>
                                            <th><?php echo $row->department_name; ?></th>
                                            <td><?php echo $row->hours; ?></td>
                                        </tr>
                                        <?php endforeach; ?>


                                    </tbody>
                                </table>

                                <script type="text/javascript">
                                  Highcharts.chart('graph2', {
                                    data: {
                                        table: 'datatable2'
                                    },
                                    chart: {
                                        type: 'bar'
                                    },
                                    title: {
                                        text: 'Hours by Department'
                                    },
                                    yAxis: {
                                        allowDecimals: false,
                                        title: {
                                            text: 'Hours'
                                        }
                                    },
                                    tooltip: {
                                          pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
                                          shared: false
                                      }
                                });
                                </script>

                              </div>
                              
                            </div>                    

                          </div>

                          <div class="col-md-6">
                            <div class="panel">
                              <div class="panel-body p-t-10">
                                <div id="graph3"></div>

                                <table style="display:none" class="table" id="datatable3">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Male</th>
                                            <th>Female</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                      <?php foreach ($chart3_data as $row): ?>
                                        <tr>
                                            <th><?php echo $row->department_name; ?></th>
                                            <td><?php echo $row->male; ?></td>
                                            <td><?php echo $row->female; ?></td>
                                        </tr>
                                        <?php endforeach; ?>


                                    </tbody>
                                </table>

                                <script type="text/javascript">
                                  Highcharts.chart('graph3', {
                                    data: {
                                        table: 'datatable3'
                                    },
                                    colors: ['#0D53CA','#E3457A'],
                                    chart: {
                                        type: 'column'
                                    },
                                    title: {
                                        text: 'Gender Attendance by Department'
                                    },
                                    yAxis: {
                                        allowDecimals: false,
                                        title: {
                                            text: 'Attendance'
                                        }
                                    },
                                    tooltip: {
                                          pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>',
                                          shared: true
                                      }
                                });
                                </script>


                              </div>
                              
                            </div>                    

                          </div>

                          <div class="col-md-6">
                            <div class="panel">
                              <div class="panel-body p-t-10">
                                <div id="graph4"></div>

                                <table style="display:none" class="table" id="datatable4">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Attendance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                      <?php foreach ($chart4_data as $row): ?>
                                        <tr>
                                            <td><?php echo $row->sex; ?></td>
                                            <td><?php echo $row->count; ?></td>
                                        </tr>
                                        <?php endforeach; ?>


                                    </tbody>
                                </table>

                                <script type="text/javascript">
                                  Highcharts.chart('graph4', {
                                    data: {
                                        table: 'datatable4'
                                    },
                                    colors: ['#0D53CA','#E3457A'],
                                    chart: {
                                        type: 'pie'
                                    },
                                    title: {
                                        text: 'Gender Attendance Ratio'
                                    },
                                    yAxis: {
                                        allowDecimals: false,
                                        title: {
                                            text: 'Attendance'
                                        }
                                    },
                                    tooltip: {
                                          pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}<br/>',
                                          shared: true
                                      }
                                });
                                </script>


                              </div>
                              
                            </div>                    

                          </div>





                        <?php foreach($charts as $index => $chart): ?>

                          


                          <div class="col-sm-12 col-lg-<?php echo $chart["width"] ?>  col-md-<?php echo $chart["width"] ?>">
                             <div class="panel text-center">


                                <div class="panel-body p-t-10">

                                    <div id="container_<?php echo $index ?>" style="width:100%; height: 300px;"></div>

                                </div>
                             </div>
                          </div>



<script>
$(function () {
    var myChart = Highcharts.chart('container_<?php echo $index ?>', {
        chart: {
            type: '<?php echo $chart["type"] ?>'
        },
        title: {
            text: '<?php echo $chart["title"] ?>'
        },
        xAxis: {
            categories: <?php echo json_encode($chart["categories"]) ?>
        },
        yAxis: {
            title: {
                text: '<?php echo $chart["y_axis_title"] ?>'
            }
        },
        series: <?php echo json_encode($chart["series"]) ?>

    });
});
</script>



                        <?php endforeach; ?>

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

                            if(isset($event)){

                                echo "Pull a winner from  <strong>". $event->event_name_english ."<strong>";

                            }
                            else{
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

<?php if(isset($event)){ ?>
      <button style="margin-top:30px" id="btn-winner" type="button" class="btn btn-primary btn-lg btn-block">Pull Now</button>



      <?php }?>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

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
