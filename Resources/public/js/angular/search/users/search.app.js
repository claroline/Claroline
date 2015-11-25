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
	$scope.users 		= {};

	$scope.editSearch = function(search) {
		//console.log(search);
	}

	$scope.search = function(search) {
		var saveSearch       = search;
		usersSearcher.search(saveSearch);
		$scope.stringSearch  = '';
	}
});

usersSearch.factory('usersSearcher', function($http) {
	var searcher = {};

	searcher.getResults = function(usersCallback, params) {
		return usersCallback(searcher, params);
	}

	searcher.search = function(managedUsers) {
		console.log('search');
		searcher.managedUsers = managedUsers;
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