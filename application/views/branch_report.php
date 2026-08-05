<div class="page-wrapper">
            <div class="content container-fluid">
               
              
                <div class="page-content-wrapperx ">
                    <div class="containerx">
                        <div class="row">
                            <div class="col-sm-12">
                              
                                <div class="panel panel-primary">
                                    <div class="panel-body">
                                      <h4 class="page-title"><?php echo $pageTitle ?></h4>
                                        <!-- <h4 class="m-t-0">Your Title</h4> -->
                                        <div>
                                            <form action="<?php echo site_url() ?>overview/branch_report" method="get">
                                            
                                            <!-- <div class="col-md-3">
                                                <div class="form-group">
                                                  <label for="sel1">Department</label>
                                                  <select class="form-control" id="dep" name="dep">
                                                    <option value="">All</option>
                                                    <?php foreach ($departments as $dep): ?>
                                                        <option <?php echo ($dep->id == $selected_dep_id) ? 'selected' : '' ?> value="<?php echo $dep->id ?>"><?php echo $dep->name ?></option>
                                                     <?php endforeach; ?>

                                                  </select>
                                                </div>
                                                
                                            </div> -->
                                            <div class="col-md-3">
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
                                        </form>
                                          

                                            
                                            <div class="col-md-12">
                                                <table class="table table-striped">
                                                    <thead>
                                                      <tr>
                                                        <th>Outlet</th>
                                                        <th>Total Employees</th>
                                                        <th>Shifts</th>
                                                        <th>Absent</th>
                                                        <th>On Leave</th>
                                                      </tr>
                                                    </thead>
                                                    <tbody>

                                                <?php foreach ($branches as $branch): ?>
                                                      <tr>
                                                        <td>

                                                            <!-- <a class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/attendance_report?month=<?php echo date('m')?>&branch=<?php echo $branch->id ?>">Overview</a> -->

                                                            <strong><?php echo $branch->name ?></strong>

                                                           

                                                                <!-- <strong><?php echo $branch->name ?></strong> -->
                                                                <br/>

                                                                <a class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/attendance_sheet?month=<?php echo $_REQUEST['month']?>&branch=<?php echo $branch->id ?>">Attendance Sheet</a>

                                                                <a class="btn btn-default btn-xs" href="<?php echo base_url() ?>ot_days?month=<?php echo $_REQUEST['month']?>&branch=<?php echo $branch->id ?>">OT Sheet</a>
                                                        </td>
                                                        <td><?php echo $branch->total_employees ?></td>
                                                        <td><?php echo $branch->shifts ?></td>
                                                        <td><?php echo $branch->absent ?></td>
                                                        <td><?php echo $branch->on_leave ?></td>
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
            </script>

        </div>
