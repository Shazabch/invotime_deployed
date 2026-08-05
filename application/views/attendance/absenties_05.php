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
                  $year = $selected_year;
                  ?>
                  <style type="text/css">
                    .color-calendar-check {
                      color: green;
                      font-size: 20px;
                    }

                    .color-calendar-times {
                      color: red;
                      font-size: 20px;
                    }

                    .color-calendar-plus {
                      color: blue;
                      font-size: 20px;
                    }

                    .color-calendar-minus {
                      color: orange;
                      font-size: 20px;
                    }

                    .half-day-paid {
                      color: blue;
                      font-size: 20px;
                    }

                    .half-day-unpaid {
                      color: orange;
                      font-size: 20px;
                    }

                    .color-calendar-o {
                      font-size: 20px;
                    }

                    .color-clock-o {
                      color: #17a2b8;
                      font-size: 20px;
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

                    .color-status-no_shift {
                      background-color: red;
                    }

                    .color-status-absent,
                    .color-status-leave {
                      background-color: black;

                    }

                    .holiday {
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
                          <?php for ($x = 1; $x <= cal_days_in_month(CAL_GREGORIAN, $selected_month, $year); $x++) : ?>
                            <?php $is_public_holiday = in_array(sprintf('%04d-%02d-%02d', $year, $selected_month, $x), $public_holidays) ?>
                            <th style="font-size: 11px" <?php if ($is_public_holiday) : ?> class="holiday" <?php endif ?>>
                              <span <?php if ($is_public_holiday) : ?> data-toggle='tooltip' data-html='true' data-placement='top' data-original-title='<?php echo $public_holidays_names[array_search(sprintf("%04d-%02d-%02d", $year, $selected_month, $x), $public_holidays)] ?>' <?php endif ?>>
                                <b><?php echo $x ?></b>
                                <br />
                                <?php echo date('D', strtotime("$year-$selected_month-$x")) ?>
                              </span>
                            </th>
                          <?php endfor; ?>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($employees as $employee) : ?>
                          <tr>
                            <td>
                              <strong>
                                <a href="<?php echo base_url() ?>overview/employee_report/<?php echo $employee->id ?>?<?php echo "month=" . $_GET['month'] ?>"><?php echo $employee->first_name ?></a>
                              </strong>
                              <br /> <?php echo $employee->special_id ?>
                              <br />
                              <div style="min-width:150px !important">
                                <a title="Clocking Data" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/manual_clocking_new?month=<?php echo $_GET['month'] ?>&year=<?php echo $_GET["year"] ?>&emp=<?php echo $employee->id ?>"><i style="font-size:15px" class="fa fa-hourglass-half"></i></a>
                                <a title="Employee Report" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/employee_report/<?php echo $employee->id ?>?<?php echo "month=" . $_GET['month'] ?>&year=<?php echo $_GET["year"] ?>"><i style="font-size:15px" class="fa fa-clock"></i></a>
                                <a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>summary/view/<?php echo $employee->id ?>?<?php echo "from=01%2F" . $_GET['month'] . "%2F" . $_GET['year'] ?>&<?php echo "to=" . last_day_of_month($_GET['month']) . "%2F" . $_GET['month'] . "%2F" . $_GET['year'] ?>"><i style="font-size:15px" class="fa fa-address-card"></i></a>
                                <a title="Shift Assignment" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/shifts_assignment?emp=<?php echo $employee->id ?>&month=<?php echo $_GET['month'] ?>&year=<?php echo $_GET['year'] ?>"><i style="font-size:15px" class="fa fa-stopwatch"></i></a>
                              </div>
                            </td>
                            <?php for ($x = 1; $x <= cal_days_in_month(CAL_GREGORIAN, $selected_month, $year); $x++) : ?>
                              <td>
                                <?php if ($employee->data[$x - 1]->is_absent === true) : ?>
                                  <span class="my-tool-tip color-calendar-times far fa-calendar-times"></span>
                                  <br />
                                  <p title="Absent<br/> Shift: <?php echo $employee->data[$x - 1]->shift ?>" data-toggle="tooltip" data-html="true" data-placement="top" class="color-status-absent" style="width: 6px;height: 6px;margin-left: 6px;border-radius: 3px;" class=""></p>
                                <?php else : ?>
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
    </div>
  </div>
</div>