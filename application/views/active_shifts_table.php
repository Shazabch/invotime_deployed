<div class="page-wrapper" style="height: 1px;">
    <div class="content container-fluid" style="min-height: 100%;">


        <div class="page-content-wrapperx ">
            <div class="containerx">
                <div class="row">
                    <div class="col-sm-12">

                        <div class="panel panel-primary">
                            <div class="panel-body">
                                <h4 class="page-title"><?php echo $pageTitle ?></h4>
                                <?php if ($permissions_level != 'Outlet'): ?>
                                    <div class="col-md-3">
                                        <select id="outlet-select" name="branch_id" class="form-control apply-select2">
                                            <option value="">Select Branch</option>
                                            <?php foreach ($branches as $branch): ?>
                                                <option value="<?= $branch->id ?>"><?= $branch->name ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div><br><br><br>
                                <?php endif; ?>
                                <?php if (in_array($company_id, companies_allowed_for_shift_allowance())): ?>
                                    <div class="col-md-3">
                                        <select id="shift-code-select" name="shift_code" class="form-control apply-select2">
                                            <option value="">Select Shift Code</option>
                                            <option value="">None</option>
                                            <option value="DSA">DSA</option>
                                            <option value="NSA">NSA</option>
                                        </select>
                                    </div><br><br><br>
                                <?php endif; ?>
                                <div id="table-content">

                                    <?php echo $table_content ?>


                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('.apply-select2').select2();
        $('#outlet-select').change(function() {
            var branchId = $(this).val(); // Get the selected branch ID

            $.ajax({
                url: '<?php echo base_url() ?>/active_shifts/filter_xcrud', // Update with your controller/method
                type: 'POST', // Change to POST method
                data: {
                    branch_id: branchId
                }, // Send the branch ID as data
                success: function(response) {
                    $('#table-content').html(response); // Update the table content
                },
                error: function() {
                    alert('Error loading data.');
                }
            });
        });
        $('#shift-code-select').change(function() {
            var shiftCode = $(this).val();
            $.ajax({
                url: '<?php echo base_url() ?>/active_shifts/filter_xcrud',
                type: 'POST',
                data: {
                    shiftCode: shiftCode
                },
                success: function(response) {
                    $('#table-content').html(response);
                },
                error: function() {
                    alert('Error loading data.');
                }
            });
        });
    });
</script>

<script type="text/javascript">
    jQuery(document).on("xcrudafterrequest", function(event, container) {
        if (Xcrud.current_task == 'save') {
            // console.log(Xcrud);
            // console.log(event);
            // console.log(container);
        }
    });
</script>

<script type="text/javascript">
    jQuery(document).on("xcrudbeforerequest", function(event, container) {
        if (container) {
            jQuery(container).find("select").select2("destroy");
        } else {
            jQuery(".xcrud").find("select").select2("destroy");
        }
    });
    jQuery(document).on("ready xcrudafterrequest", function(event, container) {
        if (container) {
            jQuery(container).find("select").select2({
                    closeOnSelect: false,
                    allowHtml: true,
                    allowClear: true,
                    tags: true
                }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
                .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
                .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
                .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));;
        } else {
            jQuery(".xcrud").find("select").select2({
                    closeOnSelect: false,
                    allowHtml: true,
                    allowClear: true,
                    tags: true
                }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
                .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
                .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
                .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));;
        }
    });
    jQuery(document).on("xcrudbeforedepend", function(event, container, data) {
        jQuery(container).find('select[name="' + data.name + '"]').select2("destroy");
    });
    jQuery(document).on("xcrudafterdepend", function(event, container, data) {
        jQuery(container).find('select[name="' + data.name + '"]').select2({
                closeOnSelect: false,
                allowHtml: true,
                allowClear: true,
                tags: true
            }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
            .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
            .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
            .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));;
    });
</script>