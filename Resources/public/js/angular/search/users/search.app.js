var usersSearch = angular.module('usersSearch', []);

usersSearch.controller('usersSearchCtrl', function(
	$scope,
	$log,
	$http,
	$cacheFactory,
	API
) {

});

usersSearch.factory('API', function($http) {
	var api = {};

	return api;
});

angular.module('usersSearch').directive('usersSearch', [
	function userlist() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/angular/search/users/views/search.html',
			replace: false
		}
	}
]);