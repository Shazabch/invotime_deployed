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
                <form action="<?php echo site_url() ?>BMI_report/process_file" method="post" enctype="multipart/form-data" id="bmi_form">
                  
                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">From<span class="text-danger">*</span></label>
                      <input class="form-control datetimepicker" type="text" id="from" required="" name="from" autocomplete="off">
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                      <label class="control-label">To<span class="text-danger">*</span></label>
                      <input class="form-control datetimepicker" type="text" id="to" required="" name="to" autocomplete="off">
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="bmi_file" class="form-label">BMI Report File<span class="text-danger">*</span></label>
                      <input class="form-control" type="file" name="bmi_file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" id="bmi_file" required="">
                    </div>
                  </div>

                  <div class="col-md-4 col-md-offset-4">
                    <label for="sel1">&nbsp;</label>
                    <button class="btn btn-primary btn-block">Insert Data</button>

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
    setCookie('downloadStarted', 0, 100);
  });

  var setCookie = function(name, value, expiracy) {
    var exdate = new Date();
    exdate.setTime(exdate.getTime() + expiracy * 1000);
    var c_value = escape(value) + ((expiracy == null) ? "" : "; expires=" + exdate.toUTCString());
    document.cookie = name + "=" + c_value + '; path=/';
  };

  var getCookie = function(name) {
    var i, x, y, ARRcookies = document.cookie.split(";");
    for (i = 0; i < ARRcookies.length; i++) {
      x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
      y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
      x = x.replace(/^\s+|\s+$/g, "");
      if (x == name) {
        return y ? decodeURI(unescape(y.replace(/\+/g, ' '))) : y;
      }
    }
  };

  $('#bmi_form').submit(function() {
    $("body").LoadingOverlay("show");
    setCookie('downloadStarted', 0, 100);
    setTimeout(checkDownloadCookie, 1000);
  });

  var downloadTimeout;
  var checkDownloadCookie = function() {
    if (getCookie("downloadStarted") == 1) {
      setCookie("downloadStarted", "false", 100);
      $("body").LoadingOverlay("hide");
    } else {
      downloadTimeout = setTimeout(checkDownloadCookie, 1000);
    }
  };
</script>


