<div class="page-wrapper" ng-app="myApp" ng-controller="importCtrl">

    <div class="content container-fluid">
        <div class="row card-box">
            <div class="row">
                <!-- Page Title -->
                <div class="col-xs-12">
                    <h4 class="page-title mb-4">Import Leave Application</h4>
                </div>

                <!-- File Upload Section -->
                <div class="col-md-12">
                    <div class="well well-sm">
                        <label for="file" class="control-label"><strong>Select Excel File (.xlsx)</strong></label>
                        <input type="file" name="file" id="file" class="form-control" accept=".xlsx" />
                        <small class="text-muted">Only .xlsx files are allowed</small>
                        <div class="mt-3">
                            <button type="button"
                                class="btn btn-primary"
                                ng-click="import()"
                                ng-disabled="processing">
                                <span ng-if="!processing">📂 Import</span>
                                <span ng-if="processing"><i class="fa fa-spinner fa-spin"></i> Processing...</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Import Summary -->
                <div class="col-md-12" ng-show="imported >= 0">
                    <div class="alert alert-info">
                        <p class="mb-1">
                            <strong class="text-success">✔ Success:</strong> {{imported}}
                        </p>
                        <p class="mb-0">
                            <strong class="text-danger">✖ Errors:</strong> {{errors.length}}
                        </p>
                    </div>
                </div>

                <!-- Error Table -->
                <div class="col-md-12" ng-show="errors.length > 0">
                    <div class="panel panel-danger">
                        <div class="panel-heading">
                            <strong>Import Errors</strong>
                            <span class="badge pull-right">{{errors.length}}</span>
                        </div>
                        <div class="panel-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-condensed custom-table mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>Error Message</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr ng-repeat="error in errors track by $index">
                                            <td>{{$index + 1}}</td>
                                            <td>{{error}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>


    </div>
</div>
<script>
    var base_url = '<?php echo base_url(); ?>';

    var config = {
        transformRequest: angular.identity, // Prevent Angular from serializing FormData
        headers: {
            'Content-Type': undefined
        } // Let browser set correct multipart/form-data
    };
    var app = angular.module('myApp', []);

    app.controller('importCtrl', function($scope, $http) {
        $scope.processing = false;
        $scope.imported = -1;
        $scope.errors = [];

        $scope.import = function() {
            var file = $('#file')[0].files[0];
            if (!file) {
                showNotification("Error", "Please select a file", "error");
                return;
            }

            $scope.processing = true;

            var form_data = new FormData();
            form_data.append('file', file);

            // reset file input
            $('#file').val('');

            $http.post(base_url + 'leave_application/import_file', form_data, config).then(function(response) {
                $scope.processing = false;
                $scope.imported = response.data.count;
                $scope.errors = response.data.errors;
                if (response.data.success) {
                    showNotification("Success", response.data.message, "success");
                } else {
                    showNotification("Error", response.data.message, "error");
                }
            }, function(error) {
                console.log(error.data);
            });
        }
    });
</script>