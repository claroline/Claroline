var usersManager = angular.module('usersManager', ['usersSearch', 'data-table']);

//let's do some initialization first.
usersManager.config(function ($httpProvider) {
	$httpProvider.interceptors.push(function ($q) {
		return {
			'request': function(config) {
				$('.please-wait').show();

				return config;
			},
			'requestError': function(rejection) {
				$('.please-wait').hide();

				return $q.reject(rejection);
			},	
			'responseError': function(rejection) {
				$('.please-wait').hide();

				return $q.reject(rejection);
			},
			'response': function(response) {
				$('.please-wait').hide();

				return response;
			}
		};
	});
});

usersManager.controller('usersCtrl', function(
	$scope,
	$log,
	$http,
	$cacheFactory,
	usersSearcher,
	API
) {
	$scope.users = []; 
	//$scope.users = [];
	$scope.dataTableOptions = {
		scrollbarV: false,
 		columnMode: 'force',
        headerHeight: 0,
        footerHeight: 50,
        selectable: true,
 		columns: [
 			{name: "username", prop: "username"}
 		]
	};
	
	usersSearcher.find('', 1, 10).then(function(d) {
		$scope.users = d.data;
		console.log($scope.users);
	});

	//I'm an angular newb so I use $scope inheritance #IDontKnowWhatImDoing
	//searchUsers is defined in a usersSearcher template.
	$scope.searchUsers = function(search) {
		usersSearcher.find(search, 1, 10).then(function(d) {
			$scope.users = d.data;
			console.log($scope.users);
			//then we should do some stuff and bla bla bla...
		});
	};
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