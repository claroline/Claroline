var usersManager = angular.module('usersManager', ['usersSearch']);

angular.module('usersSearch').constant('usersCallback', function(users) {
	alert(users);
});

usersManager.controller('usersCtrl', function(
	$scope,
	$log,
	$http,
	$cacheFactory,
	$users,
	usersSearcher,
	API
) {

	$scope.results = usersSearcher.getResults();
	console.log($scope.results);
});

usersManager.factory('API', function($http) {
	var api = {};


	return api;
});

angular.module('usersManager').directive('userlist', [
	function userlist() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/users/views/userlist.html',
			replace: true
		}
	}
]);