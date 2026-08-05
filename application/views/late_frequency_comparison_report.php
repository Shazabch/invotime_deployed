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
                                              <div style="display:none" id="selectable-controls">
                                                <button id="bulk-action" class="btn btn btn-info" data-toggle="modal" data-target="#bulk-assignment-modal">Manage Shift(s)</button>
                                                <button style="display:none" id="week-selection" class="btn btn btn-primary">Repeat Selection to Rest of the Month</button>
                                                <button style="display:none" id="carry-selection" class="btn btn btn-default">Copy Selection to Next Month</button>
                                                <button id="clear-selection" class="btn btn btn-default">Clear Selection</button>

                                              </div>
                                               <div class="clearfix"></div>
                                            <div class="table-responsive freeze-table">
                                            
                                            <table id="table-chart-1" class="table datatable2">
                                        <thead>
                                          <tr>
                                            <th scope="col"><strong>Month</strong></th>
                                            <th scope="col"><strong>Late Times</strong></th>
                                            
                                          </tr>
                                        </thead>
                                        <tbody>

                                          <?php foreach($late_data as $key => $item): ?>
                                          <tr>
                                            <td><a href="late_frequency_report?month=<?php echo $key ?>&year=<?php echo $year ?>&branch=<?php echo $_REQUEST["branch"] ?>&dep=<?php echo $this->input->get('dep') ?>"><b><?php echo date("F", mktime(0, 0, 0, $key, 10)); ?></b></a></td>
                                         
                                            <td><?php echo $item ?></td>
                                            
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

                            <div class="row">
                              <div class="col-md-12">

                                <div class="panel panel-primary">
                                  <div class="panel-body">
                                      <div id="graph1x"></div>


                                      <script type="text/javascript">
                                        Highcharts.chart('graph1x', {
                                          data: {
                                              table: 'table-chart-1'
                                          },
                                          chart: {
                                              type: 'line'
                                          },
                                          title: {
                                              text: 'Late Frequency Comparison Graph'
                                          },
                                          yAxis: {
                                              allowDecimals: false,
                                              title: {
                                                  text: 'Late Times'
                                              }
                                          },
                                          tooltip: {
                                                pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
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
                              <option data-color="<?php echo $shift->color ?>" data-code="<?php echo $shift->code ?>" <?php echo ($shift->id == 1) ? 'selected' : '' ?> value="<?php echo $shift->id ?>"><?php echo $shift->name . " (" . $shift->code . ")" ?></option>
                             <?php endforeach; ?>
                          </select>
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
        </div>

        <script>
  
  $(document).ready(function(){
    $(".datatablex").DataTable({
        "lengthMenu": [5,10,20,40, 60, 80, 100],
        "pageLength": 20
    });

    $('.datatable2').DataTable( {
        "order": [[ 3, "desc" ]],
        "lengthMenu": [5,10,20,40, 60, 80, 100],
        "pageLength": 20
    } );

  });


</script>