<div class="page-wrapper">
	<div class="content container-fluid">


		<div class="page-content-wrapperx " ng-app="myApp" ng-controller="passwordCtrl">
			<div class="containerx">
				<div class="row">
					<div class="col-sm-12">

						<div class="panel panel-primary">
							<div class="panel-body">
								<h4 class="page-title">Change Password</h4>
								<div>

									<!-- <h3>Change Password</h3> -->
									<div class="col-md-6">
										<form name="change_password" ng-submit="change_pwd()">
											<div class="form-group">
												<label for="cp">Current Password</label>
												<input type="password" class="form-control" id="cp" ng-model="pwd.current_password" required="">
											</div>
											<div class="form-group">
												<label for="pwd">New Password</label>
												<input type="password" class="form-control" id="pwd" ng-model="pwd.new_password" required="" minlength="8">
											</div>
											<div class="form-group">
												<label for="n_pwd">Confirm Password</label>
												<input type="password" class="form-control" id="n_pwd" ng-model="pwd.confirm_password" required="">
											</div>
											<button type="submit" class="btn btn-primary">Change Password</button>
										</form>
									</div>




								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
		<script>
			var config = {
				headers: {
					'Content-Type': 'application/json;charset=utf-8;'
				}
			};
			var base_url = js_base_url;
			var app = angular.module('myApp', []);
			app.controller('passwordCtrl', function($scope,$http) {
				$scope.pwd = {current_password : '', new_password : '', confirm_password : ''};

				$scope.change_pwd = function(){
					$http.post(base_url + 'my_profile/change_password', $scope.pwd, config).then(function (response) {
						if(response.data.success){
							showNotification("Success",response.data.msg,"success");
							$scope.pwd = {current_password : '', new_password : '', confirm_password : ''};
							$('body').LoadingOverlay("hide");
						}else{
							showNotification("Error",response.data.msg,"error");
							$('body').LoadingOverlay("hide");
						}
					}, function (error) {
						console.log(error.data);
					});
					
				}
			});
		</script>