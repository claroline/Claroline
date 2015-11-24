var usersManager = angular.module('usersManager', []);

/**
 * Path base controller
 *
 * @returns {PathBaseCtrl}
 * @constructor
 */
usersManager.controller('usersCtrl', function(
	$scope,
	$log,
	$http,
	$cacheFactory,
	API
) {

	$scope.search = function() {
		$scope.lan g 
	}
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
			replace: false
		}
	}
]);