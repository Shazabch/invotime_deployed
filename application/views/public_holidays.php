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
                    <h5>Select a Year</h5>
                    <div class="form-group">
                        <select id="year-select" class="form-control" required>
                            <?php foreach ($years as $year) : ?>
                                <option <?php echo ($year === $current_year) ? "selected" : "" ?> value="<?php echo $year ?>"><?php echo $year ?></option>
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
        function loadXcrud() {
            const year = $("#year-select").val();
            $('#setting-xcrud').fadeOut();
            $("#xcrud-loading").LoadingOverlay("show");
            $.ajax({
                type: "GET",
                url: "<?php echo base_url() ?>overview/get_public_holidays_xcrud",
                data: {
                    year
                },
                success: function(result) {
                    $("#xcrud-loading").LoadingOverlay("hide");
                    if (result) {
                        $('#setting-xcrud').fadeIn();
                        $('#setting-xcrud').html(result);
                    }
                }
            });

        }
        // call it on page load
        loadXcrud();
        // call when year changed
        $('#year-select').on('change', loadXcrud);

        $("body").on("click", "#copy-holidays", function(event) {
            let btn = $(this);
            btn.attr('disabled', true);

            let year = $(this).data('year');
            if(confirm("Do you really want to copy holidays from previous year?")){
                $("body").LoadingOverlay("show");
                $.ajax({
                url: '<?php echo base_url() ?>dashboard/copy_holidays/' + year, 
                        method: "GET",
                        success: function(response) {
                        response = JSON.parse(response);
                        if(response.reset == 1){
                            showNotification('Success', 'Holidays are updated');
                            loadXcrud();
                        }
                        else{
                            showNotification('Success', 'Holidays are already updated');
                        }
                        btn.attr('disabled', false);
                        $("body").LoadingOverlay("hide");
                        },
                        error: function(error)
                        {
                        console.log(error);
                        }
                });
            }
        });
    });
</script>