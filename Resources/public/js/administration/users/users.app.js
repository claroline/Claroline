var usersManager = angular.module('usersManager', ['genericSearch', 'data-table']);
var translator = window.Translator;

usersManager.config(function(clarolineSearchProvider) {
	clarolineSearchProvider.setSearchRoute('api_get_search_users');
	clarolineSearchProvider.setFieldRoute('api_get_user_searchable_fields');
});

usersManager.controller('UsersCtrl', ['$http', 'clarolineSearch', function($http, clarolineSearch) {
	var translate = function(key) {
		return translator.trans(key, {}, 'platform');
	}

	this.search = '';
	this.savedSearch = [];
	this.users = [];

	var columns = [
		{name: translate('username'), prop: "username", isCheckboxColumn: true, headerCheckbox: true},
		{name: translate('first_name'), prop: "firstName"},
		{name: translate('last_name'), prop:"lastName"},
		{name: translate('email'), prop: "mail"},
		{
			name: translate('actions'),
			cellRenderer: function(scope) {
				var route = Routing.generate('claro_desktop_open', {'_switch': scope.$row.username });

				var showAsLink = "<a class='btn btn-default' href='" + route + "' data-toggle='tooltip' data-placement='bottom' data-original-title='show_as' role='button'>" +
				"<i class='fa fa-eye'></i>" +
				"</a>";

				var route = Routing.generate('claro_user_profile_edit', {'user': scope.$row.id});

				var editLink = "<a class='btn btn-default' href='" + route + "' data-toggle='tooltip' data-placement='bottom' title='' data-original-title='edit' role='button'>" +
					"<i class='fa fa-pencil'></i>" +
					"</a>"

				var route = Routing.generate('claro_admin_user_workspaces', {'user': scope.$row.id});

				var wsLink = "<a class='btn btn-default' href='" + route + "' data-toggle='tooltip' data-placement='bottom' title='' data-original-title='workspace' role='button'>" +
					"<i class='fa fa-book'></i>" +
					"</a>";

				var actions = showAsLink + editLink + wsLink;

				return actions;
			}
		}
	];

	this.dataTableOptions = {
		scrollbarV: false,
 		columnMode: 'force',
        headerHeight: 50,
        footerHeight: 50,
        selectable: true,
        multiSelect: true,
        checkboxSelection: true,
 		columns: columns,
 		paging: {
 			externalPaging: true,
 			size: 10
 		}
	};

	this.onSearch = function(searches) {
		this.savedSearch = searches;
		clarolineSearch.find(searches, this.dataTableOptions.paging.offset, this.dataTableOptions.paging.size).then(function(d) {
			this.users = d.data.users;
			this.dataTableOptions.paging.count = d.data.total;
		}.bind(this));
	}.bind(this);

	this.paging = function(offset, size) {
		clarolineSearch.find(this.savedSearch, offset, size).then(function(d) {
			var users = d.data.users;

			//I know it's terrible... but I have no other choice with this table.
			for (var i = 0; i < offset * size; i++) {
				users.unshift({});
			}

			this.users = users;
			this.dataTableOptions.paging.count = d.data.total;
		}.bind(this));
	}.bind(this);
}]);

usersManager.directive('userlist', [
	function userlist() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/users/views/userlist.html',
			replace: true,
			controller: 'UsersCtrl',
			controllerAs: 'uc'
		}
	}
]);

usersManager.directive('usersearch', [
	function usersearch() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/users/views/usersearch.html',
			replace: true,
			controller: 'UsersCtrl',
			controllerAs: 'uc'
		}
	}
]);