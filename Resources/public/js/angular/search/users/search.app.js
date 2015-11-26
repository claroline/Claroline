var usersSearch = angular.module('usersSearch', []);

usersSearch.controller('usersSearchCtrl', function(
	$scope,
	$log,
	$http,
	$cacheFactory,
	usersSearcher
) {
	$scope.fields 		= ['name', 'id', 'email', 'administrative_code', 'all', '   create'];
	$scope.$log   		= $log;
	$scope.stringSearch = '';
});

usersSearch.factory('usersSearcher', function($http) {
	var searcher = {};

	searcher.find = function(search, page, limit) {
		if (search) {
			return [];
		} else {
			//can't use fos js routing with nelmio api bundle T_T
			return $http.get(Routing.generate('api_get_partial_list_users', {'page': page, 'limit': limit}));
		}
	}

	return searcher;
});

usersSearch.directive('searchuser', [
	function userlist() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/angular/search/users/views/search.html',
			replace: false
		}
	}
]);