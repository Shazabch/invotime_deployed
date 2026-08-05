<style>
    .text-white {
		color: #fff !important;
	}

	.text-black {
		color: #000 !important;
	}

    .no-click {
        pointer-events: none;
        cursor: default;
    }
</style>
<div class="page-wrapper" ng-app="myApp" ng-controller="patternsCtrl" ng-init="getPatterns();">

    <div class="content container-fluid" ng-cloak>
        <div class="row">
            <div class="col-xs-4">
                <h4 class="page-title">Group Shifts</h4>
            </div>
        </div>
        <div class="row card-box">
            <div class="col-md-12">
                <a type="button" class="btn btn-primary" style="margin-bottom: 10px" href="<?php echo base_url(); ?>shift_patterns/create"><i class="fa fa-plus-circle"></i> Create Group Shift</a>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th><b>Name</b></th>
                            <th><b>Outlet</b></th>
                            <th><b>Pattern</b></th>
                            <th><b>Created At</b></th>
                            <th><b>Created By</b></th>
                            <th class="text-right"><b>Actions</b></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="pattern in patterns">
                            <td>{{ pattern.name }}</td>
                            <td>{{ pattern.branch_name }}</td>
                            <td>
                                <div ng-repeat="week in pattern.pattern" style="margin-bottom: 5px;">
                                    <button ng-repeat="day in week.pattern"
                                        tabindex="-1"
                                        class="btn btn-xs no-click"
                                        ng-class="{
                                            'text-white': day.shift_id && day.color && day.color.toLowerCase() !== 'white',
                                            'text-black': !day.shift_id || !day.color || day.color.toLowerCase() === 'white',
                                            'btn-default': !day.shift_id
                                        }"
                                        ng-style="{ 'background-color': day.color ? day.color : 'white' }"
                                        style="margin-right: 5px;">
                                        {{ day.code ? day.code : '-' }}
                                    </button>
                                </div>
                            </td>
                            <td>{{ pattern.created_at }}</td>
                            <td>{{ pattern.created_by_name }}</td>
                            <td class="text-right">
                                <a href="<?php echo base_url(); ?>shift_patterns/edit/{{ pattern.id }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                                <a ng-click="deletePattern(pattern)" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                        <tr ng-if="patterns.length == 0">
                            <td colspan="6" class="text-center">No group shifts found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var base_url = '<?php echo base_url(); ?>';

    var config = {
        headers: {
            'Content-Type': 'application/json;charset=utf-8;'
        }
    };
    var app = angular.module('myApp', []);

    app.controller('patternsCtrl', function($scope, $http) {
        $scope.patterns = [];

        $scope.getPatterns = function() {
            $('body').LoadingOverlay("show", {
                maxSize: 50
            });
            $http.post(base_url + 'shift_patterns/getPatterns', {}, config).then(function(response) {
                $scope.patterns = response.data.patterns;

                $('body').LoadingOverlay("hide");
            }, function(error) {
                console.log(error.data);
            });
        }

        $scope.deletePattern = function(pattern) {
            if (confirm('Are you sure you want to delete this pattern?')) {
                $('body').LoadingOverlay("show", {
                    maxSize: 50
                })
                $http.post(base_url + 'shift_patterns/deletePattern', {
                    id: pattern.id
                }, config).then(function(response) {
                    $scope.getPatterns();

                    $('body').LoadingOverlay("hide");
                }, function(error) {
                    console.log(error.data);
                });
            }
        }
    });
</script>