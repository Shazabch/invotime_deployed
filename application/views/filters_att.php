<form action="<?php echo site_url() . $filters_form_action ?>" method="get">
  <div class="col-md-2">
      <div class="form-group">
        <label for="sel1">Outlet</label>
        <select  class="form-control" id="branch" name="branch">
          <option value="">All</option>
          <?php foreach ($branches as $branch): ?>
              <option <?php echo ($branch->id == $selected_branch_id) ? 'selected' : '' ?> value="<?php echo $branch->id ?>"><?php echo $branch->name ?></option>
           <?php endforeach; ?>
        </select>
      </div>
      
  </div>
<!-- 
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
      
  </div> -->

  <div class="col-md-4">
      <div class="form-group">
        <label for="sel1">Employee</label>
        <select class="form-control apply-select2" id="emp" name="emp">
          <option value="">All</option>
          <?php foreach ($employees_dropdown as $emp): ?>
              <option <?php echo ($emp->id == $selected_emp_id) ? 'selected' : '' ?> value="<?php echo $emp->id ?>"><?php echo $emp->special_id . " - " . $emp->first_name ?></option>
           <?php endforeach; ?>

        </select>
      </div>
      
  </div>

  <div class="col-md-2">
      <div class="form-group">
        <label class="control-label">Date</label>
        <input class="form-control datetimepicker" type="text" name="date" value="<?php echo $date; ?>" autocomplete="off">
      </div>                                               
  </div>

  <div class="col-md-2">
      <div class="form-group">
        <label for="sel1">Status</label>
        <select class="form-control" id="sel1" name="status">
          <option <?php echo ('late' == $selected_month) ? 'selected' : '' ?> value="late">Late In</option>
          <option <?php echo ('late_break' == $selected_month) ? 'selected' : '' ?> value="late_break">Late Break</option>
          <option <?php echo ('early_out' == $selected_month) ? 'selected' : '' ?> value="early_out">Early Out</option>
        </select>
      </div>                                               
  </div>

  <div class="col-md-2">
      <label for="sel1">&nbsp;</label>
      <button class="btn btn-primary btn-block">Filter</button>
      
  </div>
   <!-- <div class="col-md-3">
      <label for="sel1">&nbsp;</label>
      <button class="btn btn-default btn-block">Shifts Sheet</button>
      
  </div> -->
</form>

 <script type="text/javascript">


  $(document).ready(function(){

    $('.apply-select2').select2();

  });

</script>