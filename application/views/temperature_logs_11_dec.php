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
                  <a class="btn btn-primary pull-right" style="margin: 0 10px 10px 0;" href="<?php echo base_url() ?>overview/temperature_logs_pdf?<?php echo http_build_query($_GET) ?>&page=<?php echo $page ?>">Print PDF</a>
</div>
</div>
              <?php if($final_data): ?>
                <div class="table-responsive">
                <table class="table table-bordered">
                  <tbody>
                    <?php foreach($final_data as $f): ?>
                      <tr>
                        <td colspan="10" style="border-right: 0px solid;"><b>Employee ID: <?= $f->employee->special_id; ?></b> <a title="Employee Summary" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>summary/view/<?php echo $f->employee->id ?>?<?php echo "from=01%2F" . $_GET['month'] . "%2F".$_GET['year'] ?>&<?php echo "to=". last_day_of_month($_GET['month']) ."%2F" . $_GET['month'] . "%2F".$_GET['year'] ?>"><i style="font-size:15px" class="fa fa-address-card"></i></a> <a title="Shift Assignment" data-toggle="tooltip" class="btn btn-default btn-xs" href="<?php echo base_url() ?>overview/shifts_assignment?emp=<?php echo $f->employee->id ?>&month=<?php echo $_GET['month'] ?>&year=<?php echo $_GET['year'] ?>"><i style="font-size:15px" class="fa fa-stopwatch"></i></a></td>
                        <td colspan="10" style="border-left: 0px solid;border-right: 0px solid;"><b>Name: <?= $f->employee->name; ?></b></td>
                        <td colspan="<?= $max_date-20; ?>" style="border-left: 0px solid;"><b>Department: <?= $f->employee->department; ?></b></td>
                      </tr>
                     <?= $f->days; ?>
                     <tr style="font-family: 'Droid Serif';">
                       <?php foreach($f->dates as $d): ?>
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
