<div class="page-wrapper">
   <div class="content container-fluid">
      <div class="row">
         <div class="col-xs-4">
            <h4 class="page-title">Logs</h4>
         </div>
      </div>
      <div class="card-box">
         <div class="row">
            <form action="<?php echo base_url() ?>logs">
               <div class="col-md-3">
                  <div class="form-group">
                     <input value="<?php echo $filter_date ?>" class="form-control" type="text" name="filter_date" id="filter_date">
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="form-group">
                     <select class="form-control" name="branch_id">
                        <option value="">Select outlet</option>
                        <?php foreach ($branches as $branch) : ?>
                           <option <?php if ($branch_id == $branch->id) echo "selected" ?> value="<?php echo $branch->id ?>"><?php echo $branch->name ?></option>
                        <?php endforeach; ?>
                     </select>
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="form-group">
                     <button class="btn btn-primary" type="submit">Filter</button>
                  </div>
               </div>
            </form>
         </div>
         <div class="row">
            <div class="col-md-12">
               <ul class="list-group">
                  <?php if (sizeof($logs) == 0) : ?>
                     <li class="list-group-item">No logs are available</li>
                  <?php endif; ?>
                  <?php foreach ($logs as $log) : ?>
                     <li class="list-group-item">
                        <?php echo $log->description ?>
                     </li>
                  <?php endforeach; ?>
               </ul>
            </div>
         </div>
      </div>
   </div>
</div>

<script type="text/javascript">
   $(function() {
      $("#filter_date").daterangepicker({
         locale:  {
            format: 'DD/MM/YYYY',
         }
      });
   });

   $('#filter_date').on('cancel.daterangepicker', function(ev, picker) {
      //do something, like clearing an input
      $(this).val('');
   });
</script>