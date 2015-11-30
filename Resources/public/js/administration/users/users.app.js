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
	$scope.search = '';
	//$scope.users = [];
	$scope.dataTableOptions = {
		scrollbarV: false,
 		columnMode: 'force',
        headerHeight: 50,
        footerHeight: 50,
        selectable: true,
        multiSelect: true,
        checkboxSelection: true,
 		columns: [
 			{name: "username", prop: "username", isCheckboxColumn: true, headerCheckbox: true},
 			{name: "first_name", prop: "firstName"},
 			{name: "last_name", prop:"lastName"},
 			{name: "email", prop: "mail"}
 		],
 		paging: {
 			externalPaging: true,
 			size: 10
 		}
	};
	
	usersSearcher.find($scope.search, 1, 10).then(function(d) {
		$scope.users = d.data.users;
		$scope.dataTableOptions.paging.count = d.data.total;
	});

	//I'm an angular newb so I use $scope inheritance #IDontKnowWhatImDoing
	//searchUsers is defined in a usersSearcher template.
	$scope.searchUsers = function(searches) {
		usersSearcher.find(searches, 1, 10).then(function(d) {
			$scope.users = d.data.users;
			$scope.dataTableOptions.paging.count = d.data.total;
		});
	};

	$scope.paging = function(offset, size) {
		console.log(offset, size);
		usersSearcher.find($scope.search, offset + 1, size).then(function(d) {
			var users = d.data.users;

			//I know it's terrible... but I have no other choice with this table.
			for (var i = 0; i < offset * size; i++) {
				users.unshift({});
			}
			
			$scope.users = users;
			$scope.dataTableOptions.paging.count = d.data.total;
		});
	}
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