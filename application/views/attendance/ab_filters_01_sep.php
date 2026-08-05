<form action="<?php echo site_url() . $filters_form_action ?>" method="get">
    <div class="col-md-2">
        <div class="form-group">
            <label for="sel1">Outlet</label>
            <select class="form-control" id="branch" name="branch">
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
            <select class="form-control apply-select2" id="dep_filter" name="dep_filter[]" multiple>
                <?php foreach ($departments as $dep) : ?>
                    <option value="<?php echo $dep->id ?>"><?php echo $dep->name ?></option>
                <?php endforeach ?>

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
            <label for="sel1">Month</label>
            <select class="form-control" id="sel1" name="month">
                <option <?php echo ('01' == $selected_month) ? 'selected' : '' ?> value="01">January</option>
                <option <?php echo ('02' == $selected_month) ? 'selected' : '' ?> value="02">February</option>
                <option <?php echo ('03' == $selected_month) ? 'selected' : '' ?> value="03">March</option>
                <option <?php echo ('04' == $selected_month) ? 'selected' : '' ?> value="04">April</option>
                <option <?php echo ('05' == $selected_month) ? 'selected' : '' ?> value="05">May</option>
                <option <?php echo ('06' == $selected_month) ? 'selected' : '' ?> value="06">June</option>
                <option <?php echo ('07' == $selected_month) ? 'selected' : '' ?> value="07">July</option>
                <option <?php echo ('08' == $selected_month) ? 'selected' : '' ?> value="08">August</option>
                <option <?php echo ('09' == $selected_month) ? 'selected' : '' ?> value="09">September</option>
                <option <?php echo ('10' == $selected_month) ? 'selected' : '' ?> value="10">October</option>
                <option <?php echo ('11' == $selected_month) ? 'selected' : '' ?> value="11">November</option>
                <option <?php echo ('12' == $selected_month) ? 'selected' : '' ?> value="12">December</option>
            </select>
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            <label for="sel1">Year</label>
            <select class="form-control" id="sel1" name="year">
                <option <?php echo ('2019' == $selected_year) ? 'selected' : '' ?> value="2019">2019</option>
                <option <?php echo ('2020' == $selected_year) ? 'selected' : '' ?> value="2020">2020</option>
                <option <?php echo ('2021' == $selected_year) ? 'selected' : '' ?> value="2021">2021</option>
                <option <?php echo ('2022' == $selected_year) ? 'selected' : '' ?> value="2022">2022</option>
                <option <?php echo ('2023' == $selected_year) ? 'selected' : '' ?> value="2023">2023</option>
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
    $(document).ready(function() {
        <?php if (isset($dep_filter)) : ?>
            const dep_filter = <?php echo json_encode($dep_filter) ?>;
        <?php endif ?>
        $('.apply-select2').select2({
            placeholder: "All",
            closeOnSelect: false,
        });

        if (typeof dep_filter !== "undefined") {
            $("#dep_filter").val(dep_filter);
            $("#dep_filter").trigger("change");
        }
    });
</script>