<div class="page-wrapper">
            <div class="content container-fluid">
               
              
                <div class="page-content-wrapperx ">
                    <div class="containerx">
                        <div class="row">
                            <div class="col-sm-12">
                              
                                <div class="panel panel-primary">
                                    <div class="panel-body">
				                              <a style="float: right; width: 165px; margin-right: 14px;" class="btn btn-primary m-b-10" target="_blank" href="<?php echo $attendance_sheet_export_url ?>">Export as PDF</a>
                                      <h4 class="page-title"><?php echo $pageTitle ?></h4>

                                        <div>
                                            <?php echo $filters; ?>

                                            <?php
                                               $dateComponents = getdate();
                                              // $month = $dateComponents['mon'];                  
                                               $year = $selected_year;
                                             // echo shift_calendar($month,$year,$dateArray);


                                            ?>

                                            <style type="text/css">
                                                .color-calendar-check{
                                                    color:green;
                                                    font-size:20px;
                                                }

                                                .color-calendar-times{
                                                    color:red;
                                                    font-size:20px;                                                  
                                                }

                                                .color-calendar-plus{
                                                    color:blue;
                                                    font-size:20px;                                                   
                                                }

                                                .color-calendar-minus{
                                                    color:orange;
                                                    font-size:20px;    
                                                }

                                                .half-day-paid{
                                                    color:blue;
                                                    font-size:20px;                                                   
                                                }

                                                .half-day-unpaid{
                                                    color:orange;
                                                    font-size:20px;    
                                                }

                                                .color-calendar-o{
                                                    font-size:20px;
                                                }

                                                .color-clock-o{
                                                    color:#17a2b8;
                                                    font-size:20px;
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

                                                .color-status-no_shift{
                                                  background-color: red;
                                                }

                                                .color-status-absent,.color-status-leave{
                                                    background-color:black;
                                                    
                                                }

                                                .holiday{
                                                  color: red;
                                                }


                                            </style>

                                            <div class="clearfix"></div>
                                           

                                            <div class="col-md-12">
                                              <span class="my-tool-tip color-calendar-check far fa-calendar-check"></span> Present&nbsp;&nbsp;&nbsp;
                                              <span class="my-tool-tip color-calendar-plus far fa-calendar-plus"></span> Paid Leave&nbsp;&nbsp;&nbsp;
                                              <span class="my-tool-tip color-calendar-minus far fa-calendar-minus"></span> Unpaid Leave&nbsp;&nbsp;&nbsp;
                                              <span class="my-tool-tip half-day-paid fa fa-calendar-day"></span> Half Day Paid&nbsp;&nbsp;&nbsp;
                                              <span class="my-tool-tip half-day-unpaid fa fa-calendar-day"></span> Half Day Unpaid&nbsp;&nbsp;&nbsp;
                                              <span class="my-tool-tip color-clock-o far fa-clock-o"></span> Half Day&nbsp;&nbsp;&nbsp;
                                              <span class="my-tool-tip color-calendar-times far fa-calendar-times"></span> Absent&nbsp;&nbsp;&nbsp;
                                              <span class="my-tool-tip color-calendar-o far fa-calendar-o"></span> No Shift

                                            </div>

                                             <div class="clearfix"></div>

                                            <div class="table-responsive freeze-table">
                                            <table style="font-size: 13px" class="table table-striped">
                                              <thead>
                                                <tr>
                                                  <th style="font-size: 13px">Name</th>
                                                  <?php for ($x = 1; $x <= cal_days_in_month(CAL_GREGORIAN, $selected_month, $year); $x++): ?>

                                                    <th style="font-size: 11px" <?php if (in_array(sprintf("%04d-%02d-%02d", $year, $selected_month, $x), $public_holidays)){echo "class='holiday'";} ?>>
                                                      <span <?php if (in_array(sprintf("%04d-%02d-%02d", $year, $selected_month, $x), $public_holidays)){echo "data-toggle='tooltip' data-html='true' data-placement='top' data-original-title='".$public_holidays_names[array_search(sprintf("%04d-%02d-%02d", $year, $selected_month, $x),$public_holidays)]."'";} ?>>
                                                      <b><?php echo $x ?></b>
                                                      <br/>
                                                      <?php echo date('D', strtotime("$year-$selected_month-$x")) ?>
                                                    </span>
                                                    </th>

                                                  <?php endfor; ?>
                                                  
                                                </tr>
                                              </thead>
                                              <tbody>

                                                <?php foreach($employees as $emp): ?>
                                                  <tr>
                                                      <td><strong>
                                                        
                                                        <a href="<?php echo base_url() ?>overview/employee_report/<?php echo $emp["id"] ?>?<?php echo "month=" . $_REQUEST['month'] ?>"><?php echo $emp["first_name"] ?></a>

                                                       


                                                      </strong>
                                                        <br/> <?php echo $emp["special_id"] ?>

                                                        <br/>
                                                        
                                                        <div style="min-width:150px !important">
                                                     
<a title="Clocking Data" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/manual_clocking_new?month=<?php echo $_REQUEST['month'] ?>&year=<?php echo $_REQUEST["year"] ?>&emp=<?php echo $emp["id"] ?>"><i style="font-size:15px" class="fa fa-hourglass-half"></i></a>
 
<a title="Employee Report" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/employee_report/<?php echo $emp["id"] ?>?<?php echo "month=" . $_REQUEST['month'] ?>&year=<?php echo $_REQUEST["year"] ?>"><i style="font-size:15px" class="fa fa-clock"></i></a>

<a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>summary/view/<?php echo $emp["id"] ?>?<?php echo "from=01%2F" . $_REQUEST['month'] . "%2F".$_REQUEST['year'] ?>&<?php echo "to=". last_day_of_month($_REQUEST['month']) ."%2F" . $_REQUEST['month'] . "%2F".$_REQUEST['year'] ?>"><i style="font-size:15px" class="fa fa-address-card"></i></a>

<a title="Shift Assignment" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/shifts_assignment?emp=<?php echo $emp["id"] ?>&month=<?php echo $_REQUEST['month'] ?>&year=<?php echo $_REQUEST['year'] ?>"><i style="font-size:15px" class="fa fa-stopwatch"></i></a>

</div>


                                                     

                                                     

                                                         

                                                      </td>

                                                     
                                                      </td>
                                                      <?php for ($x = 1; $x <= cal_days_in_month(CAL_GREGORIAN, $selected_month, $year); $x++): ?>

                                                        <?php $dd = $year."-".$selected_month."-".sprintf("%02d",$x); ?>

                                                        <td>
                                                            <?php if($emp[$dd]["presence"] != "-"): ?>
                                                            <span  class="my-tool-tip color-<?php echo $emp[$dd]["presence"] ?> <?php echo $emp[$dd]["icon_class"] ?> fa-<?php echo $emp[$dd]["presence"] ?>"></span>
                                                            <br/>


                                                            <?php //var_dump($emp[$dd]) ?>


                                                            <?php if($emp[$dd]["status"] == 'absent'): ?>

                                                             <!-- <p  title="Absent" data-toggle="tooltip"  data-html="true" data-placement="top" class="color-status-red" style="width: 6px;height: 6px;margin-left: 2px;border-radius: 3px;" class=""></p> -->

                                                          <?php endif; ?>

                                                            <p   title="<?php echo $emp[$dd]["tooltip"] ?>" data-toggle="tooltip"  data-html="true" data-placement="top" class="color-status-<?php echo $emp[$dd]["status"] ?>" style="width: 6px;height: 6px;margin-left: 6px;border-radius: 3px;" class=""></p>

                                                            <?php else: ?>
                                                                -
                                                            <?php endif; ?>

                                                        </td>

                                                      <?php endfor; ?>
                                                  </tr>


                                              <?php endforeach; ?>


                                              </tbody>
                                              
                                            </table>
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
                    </div>
                </div>
            </div>
            <script type="text/javascript">

              $(document).ready(function(){


                //$('.freeze-table').freezeTable();

                $(".freeze-table").freezeTable({
                  'columnNum' : 1,
                  'shadow': true,
                  'fixedNavbar':'.header',
                  'scrollBar': true

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
