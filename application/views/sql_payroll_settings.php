<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row">
            <div class="col-xs-4">
                <h4 class="page-title"><?php echo $pageTitle ?></h4>
            </div>
        </div>
        <div class="card-box">
            <div class="row">
                <div class="col-md-4">
                    <h5>Outlets</h5>
                    <div class="form-group">
                        <select id="outlet-select" class="form-control" required>
                            <option value="">Select an outlet</option>
                            <?php foreach ($branches as $branch) : ?>
                                <option value="<?php echo $branch->id ?>"><?php echo $branch->name ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 relative">
                    <div id="xcrud-loading"></div>
                    <div id="setting-xcrud"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function() {
        $('#outlet-select').on('change', function(event) {
            const branch_id = event.target.value;
            $('#setting-xcrud').fadeOut();
            $("#xcrud-loading").LoadingOverlay("show");
            $.ajax({
                type: "GET",
                url: "<?php echo base_url() ?>exports/get_sql_xcrud",
                data: {
                    branch_id
                },
                success: function(result) {
                    $("#xcrud-loading").LoadingOverlay("hide");
                    if (result) {
                        $('#setting-xcrud').fadeIn();
                        $('#setting-xcrud').html(result);
                    }
                }
            });

        });
    });
</script>