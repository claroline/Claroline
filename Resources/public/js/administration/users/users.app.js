var usersManager = angular.module('usersManager', ['usersSearch']);

usersManager.controller('usersCtrl', function(
	$scope,
	$log,
	$http,
	$cacheFactory,
	usersSearcher,
	API
) {
	$scope.results = {}; 
	$scope.results.users = usersSearcher.getResults(function(searcher, param) {
		return searcher.managedUsers + param + Math.random();
	}, 'test');
});

usersManager.factory('API', function($http) {
	var api = {};

	return api;
});

usersManager.directive('userlist', [
	function userlist() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/users/views/userlist.html',
			replace: true
		}
	}
]);