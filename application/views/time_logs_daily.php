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
                    <a class="btn btn-primary pull-right" style="margin: 0 10px 10px 0;" href="<?php echo base_url() ?>overview/time_logs_daily_pdf?<?php echo http_build_query($_GET) ?>&page=<?php echo $page ?>">Print PDF</a>
                  </div>
                </div>
                <?php if ($final_data) : ?>
                  <div class="table-responsive freeze-table">
                    <table class="table table-bordered">
                      <thead>
                        <tr>
                          <td colspan="4" class="text-center"><b>Daily Time Logs</b></td>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($final_data as $f) : ?>
                          <tr>
                            <td style="border-right: 0px solid;"><b><?= $f->employee->special_id; ?></b>
                            <?php if (is_page_permitted('view')) : ?>
                              <a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>summary/view/<?php echo $f->employee->id ?>?<?php echo "from=01%2F" . $month . "%2F" . $year ?>&<?php echo "to=" . last_day_of_month($month) . "%2F" . $month . "%2F" . $year ?>"><i style="font-size:15px" class="fa fa-address-card"></i></a>
                            <?php endif ?>
                            <?php if (is_page_permitted('shifts_assignment')) : ?>
                              <a title="Shift Assignment" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/shifts_assignment?emp=<?php echo $f->employee->id ?>&month=<?php echo $month ?>&year=<?php echo $year ?>"><i style="font-size:15px" class="fa fa-stopwatch"></i></a></td>
                            <?php endif ?>
                            <td style="border-left: 0px solid;border-right: 0px solid;"><b><?= $f->employee->name; ?></b></td>
                            <td style="border-left: 0px solid;border-right: 0px solid;"><b>Department: <?= $f->employee->department; ?></b></td>
                            <td style="border-left: 0px solid;"><b><?= $f->shift_code ?></b></td>

                          </tr>
                          <tr>
                            <td colspan="4">
                              <?php if(count($f->clockings) === 0) : ?>
                                &nbsp;
                              <?php endif ?>
                              <?php foreach ($f->clockings as $clocking) : ?>
                                <?php echo $clocking ?>
                              <?php endforeach ?>
                            </td>
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
    $("#date").val("<?php echo $date_f ?>");
  })
</script>