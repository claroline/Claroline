var usersSearch = angular.module('usersSearch', []);

usersSearch.controller('usersSearchCtrl', function(
	$scope,
	$log,
	$http,
	$cacheFactory,
	usersSearcher,
	usersCallback
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
		$scope.stringSearch  = '';
		usersCallback(saveSearch);
	}
});

usersSearch.config(['$provide', function($provide) {
	$provide.factory('usersSearcher', function() {
		var api = {};

		api.getResults = function() {
			return api.managedUsers;
		}

		return api;
	});
}]);


usersSearch.factory('API', function($http) {

});

angular.module('usersSearch').directive('searchuser', [
	function userlist() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/angular/search/users/views/search.html',
			replace: false
		}
	}
]);