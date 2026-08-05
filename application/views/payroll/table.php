<?php include(APPPATH . "views/payroll/header.php"); ?>
<?php include(APPPATH . "views/payroll/sidebar.php"); ?>

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

                                          <?php echo $table_content ?>




                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <!-- </div>
                </div> -->
            </div>
            <script type="text/javascript">
            jQuery(document).on("xcrudafterrequest",function(event,container){
                if(Xcrud.current_task == 'save')
                {
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
        jQuery(container).find("select").select2();
    } else {
        jQuery(".xcrud").find("select").select2();
    }
});
jQuery(document).on("xcrudbeforedepend", function(event, container, data) {
    jQuery(container).find('select[name="' + data.name + '"]').select2("destroy");
});
jQuery(document).on("xcrudafterdepend", function(event, container, data) {
    jQuery(container).find('select[name="' + data.name + '"]').select2();
});

</script>

        </div>
    </div>
</div>

<?php include(APPPATH . "views/payroll/footer.php"); ?>