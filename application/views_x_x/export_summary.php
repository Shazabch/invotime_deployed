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
                <form action="<?php echo site_url() ?>exports/summary_pdf" method="get">
                  <div class="col-md-2 col-md-offset-1">
                    <div class="form-group">
                      <label for="sel1">Outlet</label>
                      <select  class="form-control" id="branch" name="branch">
                        <!-- <option value="">All</option> -->
                        <?php foreach ($branches as $branch): ?>
                          <option <?php echo ($branch->id == $selected_branch_id) ? 'selected' : '' ?> value="<?php echo $branch->id ?>"><?php echo $branch->name ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                  </div>
                  <div class="col-md-2">
                    <div class="form-group">
                      <label class="control-label">From<span class="text-danger">*</span></label>
                      <input class="form-control datetimepicker" type="text" id="from" required="" name="from" autocomplete="off">
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="form-group">
                      <label class="control-label">To<span class="text-danger">*</span></label>
                      <input class="form-control datetimepicker" type="text" id="to" required="" name="to" autocomplete="off">
                    </div>
                  </div>

                  <div class="col-md-2">
                    <div class="form-group">
                      <label for="sel1">File Type</label>
                      <select  class="form-control" id="file_type" name="file_type">
                        <!-- <option value="">All</option> -->
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                        
                      </select>
                    </div>
                  </div>

                  <div class="col-md-2">
                    <div class="form-group">
                      <label for="sel1">Summary Type</label>
                      <select  class="form-control" id="type" name="type">
                        <!-- <option value="">All</option> -->
                        <option value="short">Short</option>
                        <option value="full">Full</option>
                        
                      </select>
                    </div>
                  </div>





                  <div class="col-md-4 col-md-offset-4">
                    <label for="sel1">&nbsp;</label>
                    <button class="btn btn-primary btn-block">Export</button>

                  </div>
                </form>










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

<script>
  $(document).ready(function(){
    $('#from').val('<?php echo $from_f; ?>');
    $('#to').val('<?php echo $to_f; ?>');

  });
</script>