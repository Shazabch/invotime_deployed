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
                                            <form action="<?php echo site_url() ?>overview/shifts_calendar" method="get">
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                  <label for="sel1">Outlet</label>
                                                  <select class="form-control" id="branch" name="branch">
                                                    <option value="">All</option>
                                                    <?php foreach ($branches as $branch): ?>
                                                        <option <?php echo ($branch->id == $selected_branch_id) ? 'selected' : '' ?> value="<?php echo $branch->id ?>"><?php echo $branch->name ?></option>
                                                     <?php endforeach; ?>
                                                  </select>
                                                </div>

                                            </div>
                                           <div class="col-md-2">
                                                <div class="form-group">
                                                  <label for="sel1">Department</label>
                                                  <select class="form-control" id="dep" name="dep">
                                                    <option value="">All</option>
                                                    <?php foreach ($departments as $dep): ?>
                                                        <option <?php echo ($dep->id == $selected_dep_id) ? 'selected' : '' ?> value="<?php echo $dep->id ?>"><?php echo $dep->name ?></option>
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
                                                <option <?php echo ('2026' == $selected_year) ? 'selected' : '' ?> value="2026">2026</option>
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
                                               $year = $selected_year;
                                             // echo shift_calendar($month,$year,$dateArray);


                                            ?>

                                            <style type="text/css">
                                                .bg-grey{
                                                    background-color: #f9f9f9;
                                                }
                                            </style>

                                        <table class="table table-grey">
                                        <thead>
                                          <tr class="bg-grey">
                                            <th>Week</th>
                                            <th><?php echo date('l', strtotime("$year-$selected_month-01")) ?></th>
                                            <th><?php echo date('l', strtotime("$year-$selected_month-02")) ?></th>
                                            <th><?php echo date('l', strtotime("$year-$selected_month-03")) ?></th>
                                            <th><?php echo date('l', strtotime("$year-$selected_month-04")) ?></th>
                                            <th><?php echo date('l', strtotime("$year-$selected_month-05")) ?></th>
                                            <th><?php echo date('l', strtotime("$year-$selected_month-06")) ?></th>
                                            <th><?php echo date('l', strtotime("$year-$selected_month-07")) ?></th>

                                          </tr>
                                        </thead>
                                        <tbody>
                                          <tr class="bg-grey">
                                            <td><b>W1</b></td>

                                            <?php for ($x = 1; $x <= 7; $x++): ?>



                                                <td>

                                                    <?php $data = $shifts_calendar_data[$year."-".$selected_month."-".sprintf("%02d",$x)] ?>

                                                    <?php echo render_shift_calendar_week($data,$x); ?>
                                                </td>



                                            <?php endfor; ?>
                                          </tr>
                                          <tr class="bg-grey">
                                            <td><b>W2</b></td>
                                            <?php for ($x = 8; $x <= 14; $x++): ?>

                                                 <td>
                                                    <?php $data = $shifts_calendar_data[$year."-".$selected_month."-".sprintf("%02d",$x)] ?>

                                                    <?php echo render_shift_calendar_week($data,$x); ?>
                                                </td>

                                            <?php endfor; ?>
                                          </tr>
                                          <tr class="bg-grey">
                                            <td><b>W3</b></td>
                                            <?php for ($x = 15; $x <= 21; $x++): ?>

                                                 <td>
                                                    <?php $data = $shifts_calendar_data[$year."-".$selected_month."-".sprintf("%02d",$x)] ?>

                                                    <?php echo render_shift_calendar_week($data,$x); ?>
                                                </td>

                                            <?php endfor; ?>
                                          </tr>
                                          <tr class="bg-grey">
                                            <td><b>W4</b></td>
                                            <?php for ($x = 22; $x <= 28; $x++): ?>

                                                 <td>
                                                    <?php $data = $shifts_calendar_data[$year."-".$selected_month."-".sprintf("%02d",$x)] ?>

                                                    <?php echo render_shift_calendar_week($data,$x); ?>
                                                </td>

                                            <?php endfor; ?>
                                          </tr>
                                          <tr class="bg-grey">
                                            <td><b>W5</b></td>
                                            <?php for ($x = 29; $x <= cal_days_in_month(CAL_GREGORIAN, $selected_month, $year); $x++): ?>

                                                 <td>
                                                    <?php $data = $shifts_calendar_data[$year."-".$selected_month."-".sprintf("%02d",$x)] ?>

                                                    <?php echo render_shift_calendar_week($data,$x); ?>
                                                </td>

                                            <?php endfor; ?>
                                          </tr>
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
