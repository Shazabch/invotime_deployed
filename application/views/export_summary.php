<style>
  :root {
    --brand-start: #00c5fb;
    --brand-end: #0253cc;
    --brand-mid: #0688e8;
  }

  body {
    background:
      radial-gradient(circle at top left, rgba(0, 197, 251, 0.16), transparent 28%),
      radial-gradient(circle at top right, rgba(2, 83, 204, 0.12), transparent 24%),
      linear-gradient(180deg, #f4fbff 0%, #eaf3ff 100%);
  }

  .page-wrapper[ng-app="myApp"] {
    min-height: 100vh;
  }

  .content.container-fluid {
    padding-top: 26px;
    padding-bottom: 40px;
  }

  .panel.panel-primary.export-card {
    border: 0;
    border-radius: 24px;
    /* overflow: hidden; */
    background: #fff;
    box-shadow: 0 20px 40px rgba(25, 42, 70, 0.10);
  }

  .panel.panel-primary.export-card .panel-body {
    padding: 15px;
  }

  .export-hero {
    position: relative;
    overflow: hidden;
    display: flex;
    border-radius: 22px;
    justify-content: space-between;
    padding: 10px;
    color: #fff;
    background: -webkit-linear-gradient(left, var(--brand-start) 0%, var(--brand-end) 100%);
    background: linear-gradient(to right, var(--brand-start) 0%, var(--brand-end) 100%);
    box-shadow: 0 22px 45px rgba(24, 47, 78, 0.18);
    margin-bottom: 18px;
  }

  .export-hero:before,
  .export-hero:after {
    content: '';
    position: absolute;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.09);
    pointer-events: none;
  }

  .export-hero:before {
    width: 220px;
    height: 220px;
    right: -80px;
    top: -100px;
  }

  .export-hero:after {
    width: 150px;
    height: 150px;
    right: 110px;
    bottom: -85px;
  }

  .export-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.14);
    font-size: 12px;
    letter-spacing: .04em;
    text-transform: uppercase;
    margin-bottom: 14px;
  }

  .export-title {
    margin: 0;
    padding-left: 5px ;
    font-size: 24px;
    line-height: 1.15;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: #fff;
  }

  .export-subtitle {
    margin: 10px 0 0;
    font-size: 15px;
    line-height: 1.55;
    /* max-width: 860px; */
    color: rgba(255, 255, 255, 0.9);
  }

  .export-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 18px;
  }

  .export-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.12);
    font-size: 12px;
    color: #fff;
    backdrop-filter: blur(8px);
  }

  .export-summary-note {
    margin: 0 0 20px;
    color: #63758d;
    font-size: 13px;
    line-height: 1.6;
  }

  .panel-body > div > form > div > div {
    margin-bottom: 18px;
  }

  .panel-body label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .03em;
    text-transform: uppercase;
    color: #5a6b81;
    margin-bottom: 8px;
  }

  .panel-body .form-control {
    height: 44px;
    border-radius: 12px;
    border: 1px solid #d9e2ee;
    box-shadow: none;
    background-color: #fff;
  }

  .panel-body .form-control:focus {
    border-color: var(--brand-mid);
    box-shadow: 0 0 0 3px rgba(0, 197, 251, 0.14);
  }

  .panel-body .btn.btn-primary.btn-block {
    height: 46px;
    border-radius: 12px;
    font-weight: 700;
    letter-spacing: .02em;
    border: 0;
    background: -webkit-linear-gradient(left, var(--brand-start) 0%, var(--brand-end) 100%);
    background: linear-gradient(to right, var(--brand-start) 0%, var(--brand-end) 100%);
    box-shadow: 0 10px 22px rgba(2, 83, 204, 0.22);
  }

  .select2-container--default .select2-selection--multiple,
  .select2-container--default .select2-selection--single {
    min-height: 44px;
    border: 1px solid #d9e2ee;
    border-radius: 12px;
    padding-top: 6px;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__rendered {
    padding: 0 10px 2px;
  }

  .select2-container--default .select2-selection--multiple .select2-selection__choice {
    border: 0;
    border-radius: 999px;
    padding: 5px 10px;
    margin-top: 4px;
    background: #dff4ff;
    color: #0253cc;
  }

  #exclude_employee + .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background: #ffe4e8;
    color: #b4232c;
    border: 1px solid #f6b7be;
  }

  .select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 30px;
    padding-left: 12px;
    color: #31465b;
  }

  .select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 42px;
  }

  @media (max-width: 991px) {
    .content.container-fluid {
      padding-top: 18px;
      padding-bottom: 28px;
    }

    .export-hero {
      padding: 22px 20px;
    }

    .export-title {
      font-size: 26px;
    }

    .panel.panel-primary.export-card .panel-body {
      padding: 22px 18px 24px;
    }
  }
</style>

<div class="page-wrapper" ng-app="myApp" ng-controller="exportCtrl" ng-init="getData()">
  <div class="content container-fluid" ng-cloak>

    <div class="page-content-wrapperx ">
      <div class="containerx">
        <div class="row">
          <div class="col-sm-12">

            <div class="export-hero">
              <!-- <div class="export-kicker">Reports Center</div> -->
              <div>
                <h1 class="export-title"><?php echo $pageTitle ?></h1>
              <p class="export-subtitle">Generate payroll, summary, clocking, and custom company reports from one place.</p>
              </div>
              <div class="export-badges">
                <div class="export-badge">Multi-filter export</div>
                <div class="export-badge">PDF and Excel output</div>
                <div class="export-badge">Company-specific reports</div>
              </div>
            </div>

            <div class="panel panel-primary export-card">
              <div class="panel-body">
                <!-- <h4 class="page-title"><?php echo $pageTitle ?></h4>
                <p class="export-summary-note">Use the filters below to generate a report without changing the current export flow or report values.</p> -->

                <div>
                  <form target="_blank" action="<?php echo site_url() ?>exports/summary_pdf" method="post">
                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="sel1">Outlets</label>
                        <select class="form-control outlet_select" id="branch" name="branch[]" style="width: 100%" multiple="" ng-model="branch" ng-change="getEmployees()">
                          <option ng-repeat="o in outlets" value="{{o.id}}">{{o.name}}</option>

                        </select>
                      </div>

                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="sel1">Departments</label>
                        <select class="form-control department_select" id="department" name="department[]" style="width: 100%" multiple="" ng-model="department" ng-change="getEmployees()">
                          <option ng-repeat="d in departments" value="{{d.id}}">{{d.name}}</option>

                        </select>
                      </div>

                    </div>

                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="sel1">Positions</label>
                        <select class="form-control position_select" id="position" name="position[]" style="width: 100%" multiple="" ng-model="position" ng-change="getEmployees()">
                          <option ng-repeat="p in positions" value="{{p.id}}">{{p.name}}</option>

                        </select>
                      </div>

                    </div>

                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="sel1">Sections</label>
                        <select class="form-control section_select" id="section" name="section[]" style="width: 100%" multiple="" ng-model="section" ng-change="getEmployees()">
                          <option ng-repeat="s in sections" value="{{s.id}}">{{s.name}}</option>

                        </select>
                      </div>

                    </div>

                    <div class="col-md-12">
                      <div class="form-group">
                        <label for="sel1">Select Employees</label>
                        <select class="form-control employee_select" id="employee" name="employee[]" style="width: 100%" multiple="">
                          <option ng-repeat="g in groups" value="{{g.id}}-group">group - {{g.name}}</option>
                          <option ng-repeat="e in filtered_employees" value="{{e.id}}">{{e.special_id}} - {{e.first_name}}</option>

                        </select>
                      </div>

                    </div>
                    <div class="col-md-12">
                      <div class="form-group">
                        <label for="sel1">Exclude Employees</label>
                        <select class="form-control exclude_employee_select" id="exclude_employee" name="exclude_employee[]" style="width: 100%" multiple="">
                          <option ng-repeat="g in groups" value="{{g.id}} - group">group - {{g.name}}</option>
                          <option ng-repeat="e in filtered_employees" value="{{e.id}}">{{e.special_id}} - {{e.first_name}}</option>

                        </select>
                      </div>

                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="control-label">From<span class="text-danger">*</span></label>
                        <input class="form-control datetimepicker" type="text" id="from" required="" name="from" autocomplete="off">
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label class="control-label">To<span class="text-danger">*</span></label>
                        <input class="form-control datetimepicker" type="text" id="to" required="" name="to" autocomplete="off">
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="sel1">Summary Type</label>
                        <select class="form-control" id="type" name="type" ng-model="type">
                          <!-- <option value="">All</option> -->
                          <option value="short">Short</option>
                           <?php if (in_array($company_id, companies_allowed_for_mcb01_clocking())): ?>
                            <option value="mcb01_clocking">Custom Clocking</option>
                          <?php endif; ?>
                          <option value="lateness_report">Lateness </option>
                          <option value="full">Full</option>
                          <!-- <option value="full_merged">Full Merged(For Printing Purpose)</option> -->
                          <option value="accounts">AutoCount Payroll</option>
                          <option value="sql">SQL Payroll</option>
                          <option value="weekly_ot">Weekly OT</option>
                          <option value="weekly_ot_reports">SQL Weekly OT</option>
                          <?php if (in_array($company_id, companies_allowed_for_ot_summary())): ?>
                            <option value="over_time_summary">Custom OT Summary</option>
                          <?php endif; ?>

                          <?php if ($company_id == 66): ?>
                            <option value="bmi_summary">BMI Full Summary</option>
                            <option value="bmi_summary_short">BMI Short Summary</option>
                          <?php endif; ?>
                          <?php if ($company_id == 102): ?>
                            <option value="cjc01_payroll">CJC01 Payroll</option>
                          <?php endif; ?>
                          <option value="daily_time_card">Daily Time Card</option>
                          <?php if ($company_id == 146): ?>
                            <option value="tsf01_csv_report">TSF01 CSV Report</option>
                          <?php endif; ?>
                          <?php if ($company_id == 175): ?>
                            <option value="mm01_report">MM01 Report</option>
                          <?php endif; ?>
                          <?php if ($company_id == 95): ?>
                            <option value="work_hours_summary">Work Hours Summary</option>
                          <?php endif; ?>
                          <?php if ($company_id == 223 || $company_id == 259): ?>
                            <option value="gni01_payroll_process">GNI01 Payroll Process</option>
                          <?php endif; ?>
                          <?php if (in_array($company_id, companies_allowed_for_alya01_custom_report())): ?>
                            <option value="alya01_custom_report">Custom Clocking Report</option>
                          <?php endif; ?>
                        </select>
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="sel1">File Type</label>
                        <select class="form-control" id="file_type" name="file_type">
                          <!-- <option value="">All</option> -->
                          <option value="pdf">PDF</option>
                          <option value="excel">Excel 97-2003 Workbook (.xls)</option>
                          <option value="xlsx">Excel Workbook (.xlsx)</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-md-3" ng-show="type == 'gni01_payroll_process'">
                      <div class="form-group">
                        <label class="control-label">OT From<span class="text-danger">*</span></label>
                        <input class="form-control datetimepicker" type="text" id="ot_from" ng-required="type == 'gni01_payroll_process'" name="ot_from" autocomplete="off">
                      </div>
                    </div>
                    <div class="col-md-3" ng-show="type == 'gni01_payroll_process'">
                      <div class="form-group">
                        <label class="control-label">OT To<span class="text-danger">*</span></label>
                        <input class="form-control datetimepicker" type="text" id="ot_to" ng-required="type == 'gni01_payroll_process'" name="ot_to" autocomplete="off">
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
  $(document).ready(function() {
    $('#from').val('<?php echo $from_f; ?>');
    $('#to').val('<?php echo $to_f; ?>');
    $('#ot_from').val('<?php echo $ot_from_f; ?>');
    $('#ot_to').val('<?php echo $ot_to_f; ?>');

    // on change of from date
    $('#from').on('dp.change', function() {
      if ($('#type').val() != 'gni01_payroll_process') {
        return;
      }
      var from = $(this).val();

      // get 21st of last month
      var date = new Date(from.split('/')[2], from.split('/')[1] - 1, from.split('/')[0]);
      var lastMonth = date.getMonth() == 0 ? 11 : date.getMonth() - 1;
      // add 0 if less than 10
      lastMonth = (lastMonth + 1) < 10 ? '0' + (lastMonth + 1) : (lastMonth + 1);
      var year = date.getMonth() == 0 ? date.getFullYear() - 1 : date.getFullYear();
      // make dd/mm/Y format
      lastMonthDateFormatted = '21/' + lastMonth + '/' + year;
      $('#ot_from').val(lastMonthDateFormatted);

      // get 20th of this month
      var thisMonth = date.getMonth();
      // add 0 if less than 10
      thisMonth = (thisMonth + 1) < 10 ? '0' + (thisMonth + 1) : (thisMonth + 1);
      var thisMonthDate = new Date(date.getFullYear(), thisMonth, 20);
      // make d/m/Y format
      var thisMonthDateFormatted = thisMonthDate.getDate() + '/' + thisMonth + '/' + thisMonthDate.getFullYear();
      $('#ot_to').val(thisMonthDateFormatted);
    });
  });
</script>

<script type="text/javascript">
  $(document).ready(function() {


    $(".outlet_select").select2({
        placeholder: "All Outlets"
      }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
      .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
      .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
      .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));

    $(".department_select").select2({
        placeholder: "All Departments"
      }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
      .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
      .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
      .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));

    $(".position_select").select2({
        placeholder: "All Positions"
      }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
      .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
      .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
      .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));

    $(".section_select").select2({
        placeholder: "All Sections"
      }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
      .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
      .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
      .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));

    $(".employee_select").select2({
        placeholder: "All Employees",
        closeOnSelect: false,
      }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
      .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
      .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
      .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));

    $(".exclude_employee_select").select2({
        closeOnSelect: false,
        placeholder: "Exclude Employees"
      }).on('select2:selecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
      .on('select2:select', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')))
      .on('select2:unselecting', e => $(e.currentTarget).data('scrolltop', $('.select2-results__options').scrollTop()))
      .on('select2:unselect', e => $('.select2-results__options').scrollTop($(e.currentTarget).data('scrolltop')));

  });
  var base_url = '<?php echo base_url(); ?>';

  var config = {
    headers: {
      'Content-Type': 'application/json;charset=utf-8;'
    }
  };
  var app = angular.module('myApp', []);

  app.controller('exportCtrl', function($scope, $http) {

    $scope.outlets = [];
    $scope.departments = [];
    $scope.positions = [];
    $scope.employees = [];
    $scope.filtered_employees = [];
    $scope.branch = [];
    $scope.department = [];
    $scope.position = [];
    $scope.section = [];
    $scope.type = 'short';

    $scope.getData = function() {
      $('body').LoadingOverlay("show", {
        maxSize: 50
      });
      $http.post(base_url + 'exports/getData', '', config).then(function(response) {
        // console.log(response);
        $scope.outlets = response.data.outlets;
        $scope.departments = response.data.departments;
        $scope.positions = response.data.positions;
        $scope.sections = response.data.sections;
        $scope.employees = response.data.employees;
        $scope.filtered_employees = response.data.employees;
        $scope.groups = response.data.groups;

        $('body').LoadingOverlay("hide");
      }, function(error) {
        console.log(error.data);
      });
    }

    $scope.getEmployees = function() {
      $('body').LoadingOverlay("show", {
        maxSize: 50
      });
      var total = $scope.filtered_employees = angular.copy($scope.employees);
      var newArray;
      if ($scope.branch.length != 0) {
        newArray = total = total.filter(function(el) {
          return $scope.branch.includes(el.branch_id)
        });
        $scope.filtered_employees = newArray;
      }
      if ($scope.department.length != 0) {
        newArray = total = total.filter(function(el) {
          return $scope.department.includes(el.department_id)
        });
        $scope.filtered_employees = newArray;
      }
      if ($scope.position.length != 0) {
        newArray = total.filter(function(el) {
          return $scope.position.includes(el.position_id)
        });
        $scope.filtered_employees = newArray;
      }
      if ($scope.section.length != 0) {
        newArray = total.filter(function(el) {
          return $scope.section.includes(el.section_id)
        });
        $scope.filtered_employees = newArray;
      }
      $('body').LoadingOverlay("hide");
    }


  });
</script>
<script>
  $(document).ready(function() {
    // Create a function to show a nice notification
    function showNotification(message, isError = true) {
      // Create notification div
      var notification = $('<div class="custom-notification">' + message + '</div>');

      // Style the notification
      notification.css({
        'position': 'fixed',
        'top': '20px',
        'right': '20px',
        'background': isError ? '#f8d7da' : '#d4edda',
        'color': isError ? '#721c24' : '#155724',
        'padding': '15px',
        'border-radius': '5px',
        'border': '1px solid ' + (isError ? '#f5c6cb' : '#c3e6cb'),
        'z-index': '9999',
        'box-shadow': '0 4px 6px rgba(0,0,0,0.1)',
        'max-width': '300px'
      });

      // Add to page
      $('body').append(notification);

      // Remove after 5 seconds
      setTimeout(function() {
        notification.fadeOut(500, function() {
          $(this).remove();
        });
      }, 5000);
    }

    // Function to handle lateness report specific settings
    function setupLatenessReport() {
      var $outletSelect = $('.outlet_select');
      var currentSelections = $outletSelect.val();
      var outletOptions = $outletSelect.find('option');

      // Get the file type dropdown
      var $fileTypeSelect = $('#file_type');

      if (outletOptions.length > 0) {
        // Get the first outlet option value and text
        var firstOption = $(outletOptions[0]);
        var firstOutletValue = firstOption.val();
        var firstOutletText = firstOption.text();

        // If no outlet is selected or multiple outlets are selected
        if (!currentSelections || currentSelections.length === 0 || currentSelections.length > 1) {
          // Set outlet select to first option only
          $outletSelect.val([firstOutletValue]).trigger('change');

          // Show notification with outlet name
          showNotification('For Lateness Report, automatically selected first outlet: ' + firstOutletText, false);
        } else {
          // If exactly one outlet is already selected
          var selectedOption = $outletSelect.find('option[value="' + currentSelections[0] + '"]');
          showNotification('Lateness Report: Using selected outlet: ' + selectedOption.text(), false);
        }

        // Force file type to xlsx for lateness report
        $fileTypeSelect.val('xlsx');

        // Disable and hide other options in file type dropdown
        $fileTypeSelect.find('option').each(function() {
          if ($(this).val() !== 'xlsx') {
            $(this).prop('disabled', true).hide();
          } else {
            $(this).prop('disabled', false).show();
          }
        });

        // Show notification about file type restriction
        showNotification('Lateness Report only supports XLSX file format.', false);

        // Update Angular scope
        var scope = angular.element($outletSelect).scope();
        if (scope && scope.$apply) {
          scope.$apply(function() {
            scope.branch = [firstOutletValue];
            if (scope.getEmployees) {
              scope.getEmployees();
            }
          });
        }
      } else {
        showNotification('No outlets available. Please add outlets first.');
        $('#type').val('short');
        return;
      }

      // Also update the select2 to allow only single selection
      $outletSelect.select2({
        maximumSelectionLength: 1,
        placeholder: "One outlet required"
      });
    }

    // Function to reset to normal mode (non-lateness report)
    function resetToNormalMode() {
      // Re-enable all file type options
      $('#file_type').find('option').each(function() {
        $(this).prop('disabled', false).show();
      });

      // Reset file type to default (pdf)
      $('#file_type').val('pdf');

      // Allow multiple outlet selections
      $('.outlet_select').select2({
        maximumSelectionLength: null,
        placeholder: "All Outlets"
      });
    }

    // Listen for type change
    $('#type').on('change', function() {
      var selectedType = $(this).val();

      if (selectedType === 'lateness_report') {
        setupLatenessReport();
      } else {
        resetToNormalMode();
      }
    });

    // Initialize on page load if lateness_report is already selected
    if ($('#type').val() === 'lateness_report') {
      $('#type').trigger('change');
    }
  });
</script>