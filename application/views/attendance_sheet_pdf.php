<style>
  body {
    font-family: 'Montserrat', sans-serif;
    ;
  }

  table {
    border-collapse: collapse;
    width: 100%;
  }

  table,
  th,
  td {
    border: 1px solid black;
  }

  td {
    text-align: center;
    font-size: 10px;
    padding: 10px;
  }

  th {
    text-align: center;
    font-size: 11px;
  }

  .strike {
    text-decoration: line-through;
  }

  .date {
    font-size: 8px;
    white-space: nowrap;
  }

  .remark {
    font-size: 8px;
  }

  .text-danger {
    color: #d9534f;
  }
</style>
<div class="page-wrapper">
  <div class="content container-fluid">
    <div class="row">
      <div class="col-xs-4">
        <h4 class="page-title">Attendance Sheet</h4>
      </div>
    </div>
    <p>Generated at <b><?php echo date("d/m/Y H:i:s"); ?></b> by <b><?php echo $current_user['first_name'] ?></b></p>

    <div class="page-content-wrapperx ">
      <div class="containerx">
        <div class="row">
          <div class="col-sm-12">

            <div class="panel panel-primary">
              <div class="panel-body">

                <?php
                $dateComponents = getdate();
                // $month = $dateComponents['mon'];                  
                $year = $selected_year;
                // echo shift_calendar($month,$year,$dateArray);


                ?>

    <style type="text/css">
      .color-calendar-check::before{
          color:green;
          font-size:15px;
          content: 'P';
      }

      .color-calendar-times::before{
          color:red;
          font-size:15px;          
          content: 'AB';                                        
      }

      .color-calendar-plus::before{
          color:blue;
          font-size:15px;
          content: 'PL';                                                   
      }

      .color-calendar-minus::before{
          color:orange;
          font-size:15px;    
          content: 'UL';
      }

      .half-day-paid::before{
          color:blue;
          font-size:12px;   
          content: '1/2 PL';                                                
      }

      .half-day-unpaid::before{
          color:orange;
          font-size:12px;  
          content: '1/2 UL';                                                
      }

      .color-calendar-o::before{
          font-size:15px;
          padding: 4px;
          content: 'N/A';
      }

      .color-clock-o::before{
          color:#17a2b8;
          font-size:15px;
          content: 'HD';
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

                <div class="clearfix"></div>

                <div class="table-responsive freeze-table">
                  <table style="font-size: 13px" class="table table-striped">
                    <thead>
                      <tr>
                        <th style="font-size: 13px">Name</th>
                        <?php foreach ($period_of_dates as $period) : ?>

                          <th style="font-size: 11px" <?php if (in_array($period->format('Y-m-d'), $public_holidays)) {
                                                        echo "class='holiday'";
                                                      } ?>>
                            <span <?php if (in_array($period->format('Y-m-d'), $public_holidays)) {
                                    echo "data-toggle='tooltip' data-html='true' data-placement='top' data-original-title='" . $public_holidays_names[array_search($period->format('Y-m-d'), $public_holidays)] . "'";
                                  } ?>>
                              <b><?php echo $period->format('j') ?></b>
                              <br />
                              <?php echo $period->format('D') ?>
                            </span>
                          </th>

                        <?php endforeach ?>

                      </tr>
                    </thead>
                    <tbody>

                      <?php foreach ($employees as $emp) : ?>
                        <tr>
                          <td><strong>

                              <?php echo $emp["first_name"] ?>




                            </strong>
                            <br /> <?php echo $emp["special_id"] ?>

                            <br />

                            <div style="min-width:150px !important">

                              <a title="Clocking Data" data-toggle="tooltip" class="btn btn-default btn-xs" href="#"><i style="font-size:15px" class="fa fa-hourglass-half"></i></a>

                              <a title="Employee Report" data-toggle="tooltip" class="btn btn-default btn-xs" href="#"><i style="font-size:15px" class="fa fa-clock"></i></a>

                              <a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="#"><i style="font-size:15px" class="fa fa-address-card"></i></a>

                              <a title="Shift Assignment" data-toggle="tooltip" class="btn btn-default btn-xs" href="#"><i style="font-size:15px" class="fa fa-stopwatch"></i></a>

                            </div>








                          </td>


                          </td>
                          <?php foreach ($period_of_dates as $period) : ?>
                            <?php $dd = $period->format('Y-m-d') ?>

                            <td>
                              <?php if ($emp[$dd]["presence"] != "-") : ?>
                                <span class="my-tool-tip color-<?php echo $emp[$dd]["presence"] ?> <?php echo $emp[$dd]["icon_class"] ?> fa-<?php echo $emp[$dd]["presence"] ?>"></span>
                                <br />


                                <?php //var_dump($emp[$dd]) 
                                ?>


                                <?php if ($emp[$dd]["status"] == 'absent') : ?>

                                  <!-- <p  title="Absent" data-toggle="tooltip"  data-html="true" data-placement="top" class="color-status-red" style="width: 6px;height: 6px;margin-left: 2px;border-radius: 3px;" class=""></p> -->

                                <?php endif; ?>

                                <!-- <p title="<?php echo $emp[$dd]["tooltip"] ?>" data-toggle="tooltip" data-html="true" data-placement="top" class="color-status-<?php echo $emp[$dd]["status"] ?>" style="width: 6px;height: 6px;margin-left: 6px;border-radius: 3px;" class=""></p> -->

                              <?php else : ?>
                                -
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

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</div>
</div>