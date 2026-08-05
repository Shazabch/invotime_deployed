<style>
  .dot {
    height: 19px;
    width: 19px;
    border-radius: 50%;
    display: inline-block;
  }
  .holiday {
    color: red;
  }
</style>
<?php
$weekly_url_id = $this->uri->segment(3);
$from_url_date = isset($_GET['from']) ? $_GET['from'] : '';
$from_url_month = $formatted_date['start_date']->format('m');
$from_url_year = $formatted_date['start_date']->format('Y');

if (!empty($from_url_month) && !empty($from_url_year)) {

  $mon = $from_url_month;
  $year = $from_url_year;
  $day = "01";
  $dt = $day . '-' . $mon . '-' . $year;
  // echo 'First day : '. date("01-m-Y", strtotime($dt)).' - Last day : '. date("t-m-Y", strtotime($dt)); 
  $from_date = strtotime(date("01-m-Y", strtotime($dt)));
  $to_date = strtotime(date("t-m-Y", strtotime($dt)));

  $mon_from = date('m', $from_date);
  $year_from = date('Y', $from_date);
  $day_from = date('d', $from_date);
  $from_date1 = $day_from . '%2F' . $mon_from . '%2F' . $year_from;

  $mon_to = date('m', $to_date);
  $year_to = date('Y', $to_date);
  $day_to = date('d', $to_date);
  $to_date1 = $day_to . '%2F' . $mon_to . '%2F' . $year_to;
  // echo $from_date1.' to '.$to_date1;exit;
}
?>
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
                </div>
              </div>



            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">

            <div class="panel panel-primary">
              <div class="panel-body">
                <div class="row">
                  <div class="col-12">
                    <a class="btn btn-primary pull-right" style="margin: 0 10px 10px 0;" href="<?php echo base_url() ?>overview/time_logs_pdf?<?php echo http_build_query($_GET) ?>&page=<?php echo $page ?>">Print PDF</a>
                    <a class="btn btn-primary pull-right" style="margin: 0 10px 10px 0;" href="<?php echo base_url() ?>overview/time_logs_excel?<?php echo http_build_query($_GET) ?>&page=<?php echo $page ?>">Print Excel</a>
                  </div>
                </div>

                <span style="  background-color: #0000ff;" class="dot"></span> - <strong> No Shift with clocking remarks </strong>&nbsp;&nbsp;&nbsp;
                <span style="  background-color: #008000;" class="dot"></span> - <strong> Shift with clocking remarks </strong>&nbsp;&nbsp;&nbsp;
                <span style="  background-color: red;" class="dot"></span> - <strong> No Shift </strong>&nbsp;&nbsp;&nbsp;
                <br><br>

                <?php if ($final_data) : ?>
                  <div class="table-responsive freeze-table">
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <td colspan="<?php echo $numberOfDays ?>" class="text-center"><b>Time Logs</b></td>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($final_data as $f) : ?>
                          <tr style="background-color: lightgray;">
                            <td colspan="<?= floor($numberOfDays / 3) ?>" style="border-right: 0px solid;"><b>Employee ID: <?= $f->employee->special_id; ?></b>
                            <?php if (is_page_permitted('view')) : ?>
                              <a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>summary/view/<?php echo $f->employee->id . '/?from=' . $from_date1 . '&to=' . $to_date1; ?>"><i style="font-size:15px" class="fa fa-address-card"></i></a>
                            <?php endif ?>
                            <?php if (is_page_permitted('shifts_assignment')) : ?>
                              <a title="Shift Assignment" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/shifts_assignment?emp=<?php echo $f->employee->id ?>&daterange_filter=<?= $start_date_f . ' - ' . $end_date_f ?>"><i style="font-size:15px" class="fa fa-stopwatch"></i></a></td>
                            <?php endif ?>
                            <td colspan="<?= floor($numberOfDays / 3) ?>" style="border-left: 0px solid;border-right: 0px solid;"><b>Name: <?= $f->employee->name; ?></b></td>
                            <td colspan="<?= $numberOfDays - floor($numberOfDays / 3) * 2 ?>" style="border-left: 0px solid;"><b>Department: <?= $f->employee->department; ?></b></td>
                          </tr>
                          <?= $f->days; ?>
                          <tr style="font-family: 'Droid Serif';">
                            <?php foreach ($f->dates as $d) : ?>
                              <td style="min-width: 37px; padding: 0px;" class="text-center"><?= $d; ?>&nbsp;</td>
                            <?php endforeach; ?>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
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
</div>
<script>
  $(document).ready(function() {
    $(".freeze-table").freezeTable({
      'freezeColumn': false,
      'shadow': true,
      'fixedNavbar': '.header',
      'scrollBar': true

    });
  })
</script>