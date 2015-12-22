var usersManager = angular.module('usersManager', ['genericSearch', 'data-table']);
var translator = window.Translator;

usersManager.config(function(clarolineSearchProvider) {
	clarolineSearchProvider.setBaseRoute('api_search_partial_list_users');
	clarolineSearchProvider.setSearchRoute('api_get_partial_list_users');
	clarolineSearchProvider.setFieldRoute('api_get_user_searchable_fields');
});

usersManager.controller('UsersCtrl', function(
	$scope,
	$log,
	$http,
	$cacheFactory,
	clarolineSearch
) {
	var translate = function(key) {
		return translator.trans(key, {}, 'platform');
	}

	$scope.search = '';
	$scope.savedSearch = [];
	$scope.users = [];

	$scope.columns = [
		{name: translate('username'), prop: "username", isCheckboxColumn: true, headerCheckbox: true},
		{name: translate('first_name'), prop: "firstName"},
		{name: translate('last_name'), prop:"lastName"},
		{name: translate('email'), prop: "mail"}
	];

	$scope.dataTableOptions = {
		scrollbarV: false,
 		columnMode: 'force',
        headerHeight: 50,
        footerHeight: 50,
        selectable: true,
        multiSelect: true,
        checkboxSelection: true,
 		columns: $scope.columns,
 		paging: {
 			externalPaging: true,
 			size: 10
 		}
	};

	clarolineSearch.find([], 1, 10).then(function(d) {
		$scope.users = d.data.users;
		$scope.dataTableOptions.paging.count = d.data.total;
	});
	
	$scope.find = function(searches) {
		console.log(searches);
		$scope.savedSearch = searches;
		clarolineSearch.find(searches, $scope.dataTableOptions.paging.offset + 1, $scope.dataTableOptions.paging.size).then(function(d) {
			$scope.users = d.data.users;
			$scope.dataTableOptions.paging.count = d.data.total;
		});
	};

	$scope.paging = function(offset, size) {
		clarolineSearch.find($scope.savedSearch, offset + 1, size).then(function(d) {
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