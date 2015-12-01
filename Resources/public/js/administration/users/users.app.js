var usersManager = angular.module('usersManager', ['usersSearch', 'data-table']);

usersManager.controller('usersCtrl', function(
	$scope,
	$log,
	$http,
	$cacheFactory,
	usersSearcher
) {
	$scope.users = []; 
	$scope.search = '';
	$scope.savedSearch = [];
	//$scope.offset = 0;
	//$scope.size   = 10;
	$scope.users = [];
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
	
	usersSearcher.find([], 1, 10).then(function(d) {
		$scope.users = d.data.users;
		$scope.dataTableOptions.paging.count = d.data.total;
	});
	
	$scope.clarolineSearch = function(searches) {
		$scope.savedSearch = searches;
		usersSearcher.find(searches, $scope.dataTableOptions.paging.offset + 1, $scope.dataTableOptions.paging.size).then(function(d) {
			$scope.users = d.data.users;
			$scope.dataTableOptions.paging.count = d.data.total;
		});
	};

	$scope.paging = function(offset, size) {
		usersSearcher.find($scope.savedSearch, offset + 1, size).then(function(d) {
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

usersManager.directive('userlist', [
	function userlist() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/users/views/userlist.html',
			replace: true
		}
	}
]);

usersManager.directive('usersearch', [
	function usersearch() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/users/views/usersearch.html',
			replace: true
		}
	}
]);