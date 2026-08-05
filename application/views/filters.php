<form action="<?php echo site_url() . $filters_form_action ?>" method="get">
  <div class="col-md-2">
    <div class="form-group">
      <label for="sel1">Outlet</label>
      <select class="form-control apply-select2" id="branch" name="branch">
        <option value="">All</option>
        <?php foreach ($branches as $branch) : ?>
          <option <?php echo ($branch->id == $selected_branch_id) ? 'selected' : '' ?> value="<?php echo $branch->id ?>"><?php echo $branch->name ?></option>
        <?php endforeach; ?>
      </select>
    </div>

  </div>

  <div class="col-md-2">
    <div class="form-group">
      <label for="sel1">Department</label>
      <select class="form-control apply-select2" id="dep" name="dep">
        <option value="">All</option>
        <?php foreach ($departments as $dep) : ?>
          <option <?php echo ($dep->id == $selected_dep_id) ? 'selected' : '' ?> value="<?php echo $dep->id ?>"><?php echo $dep->name ?></option>
        <?php endforeach; ?>

      </select>
    </div>

  </div>
  
  <div class="col-md-2">
    <div class="form-group">
      <label for="sel1">Section</label>
      <select class="form-control apply-select2" id="sec" name="sec">
        <option value="">All</option>
        <?php foreach ($sections as $sec) : ?>
          <option <?php echo ($sec->id == $selected_sec_id) ? 'selected' : '' ?> value="<?php echo $sec->id ?>"><?php echo $sec->name ?></option>
        <?php endforeach; ?>

      </select>
    </div>

  </div>
  
  <div class="col-md-2">
    <div class="form-group">
      <label for="sel1">Position</label>
      <select class="form-control apply-select2" id="pos" name="pos">
        <option value="">All</option>
        <?php foreach ($positions as $pos) : ?>
          <option <?php echo ($pos->id == $selected_pos_id) ? 'selected' : '' ?> value="<?php echo $pos->id ?>"><?php echo $pos->name ?></option>
        <?php endforeach; ?>

      </select>
    </div>

  </div>

  <div class="col-md-2">
    <div class="form-group">
      <label for="sel1">Employee</label>
      <select class="form-control apply-select2" id="emp" name="emp">
        <option value="">All</option>
        <?php foreach ($employees_dropdown as $emp) : ?>
          <option <?php echo ($emp->id == $selected_emp_id) ? 'selected' : '' ?> value="<?php echo $emp->id ?>"><?php echo $emp->special_id . " - " . $emp->first_name ?></option>
        <?php endforeach; ?>

      </select>
    </div>

  </div>
  <div class="col-md-2">
    <div class="form-group">
      <label for="emp-group">Employee Group</label>
      <select class="form-control apply-select2" id="emp-group" name="emp_group">
        <option value="">All</option>
        <?php foreach ($employee_groups as $group) : ?>
          <option <?php echo ($group->id == $selected_group_id) ? 'selected' : '' ?> value="<?php echo $group->id ?>"><?php echo $group->name ?></option>
        <?php endforeach; ?>

      </select>
    </div>

  </div>

  <div class="col-md-4">
    <div class="form-group">
      <label for="daterange_filter">Date Range</label>
      <input value="<?php echo $daterange_filter ?>"
        class="form-control"
        type="text"
        name="daterange_filter"
        id="daterange_filter">
    </div>
  </div>

  <div class="col-md-2 m-b-10">
    <label for="filter-submit-button">&nbsp;</label>
    <button class="btn btn-primary btn-block">Filter</button>
  </div>
  <!-- <div class="col-md-3">
      <label for="sel1">&nbsp;</label>
      <button class="btn btn-default btn-block">Shifts Sheet</button>
      
  </div> -->
</form>

<script type="text/javascript">
  $(document).ready(function() {
    $('.apply-select2').select2();
    let daysInLastMonth = moment().subtract(1, 'month').daysInMonth();
    let totalNumberOfDays = daysInLastMonth - 1;
    let daterangeFilterOptions = {
      autoApply: true,
      showDropdowns: true,
      maxSpan: {
        'days': 30
      },
      ranges: {
        'Today': [moment(), moment()],
        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
        '7 Days': [moment(), moment().add(6, 'days')],
        'Previous Month': [moment().subtract(daysInLastMonth, 'days'), moment().subtract(1, 'days')],
        'This Month': [moment().startOf('month'), moment().endOf('month')],
        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
      },
      showCustomRangeLabel: true,
      opens: 'center',
      alwaysShowCalendars: true,
      locale: {
        format: 'DD/MM/YYYY',
      }
    };
    $('#daterange_filter').daterangepicker(daterangeFilterOptions);
  });
</script>