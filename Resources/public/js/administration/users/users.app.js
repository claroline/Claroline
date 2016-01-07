var usersManager = angular.module('usersManager', ['genericSearch', 'data-table']);
var translator = window.Translator;

usersManager.config(function(clarolineSearchProvider) {
	clarolineSearchProvider.setSearchRoute('api_get_search_users');
	clarolineSearchProvider.setFieldRoute('api_get_user_searchable_fields');
});

usersManager.controller('UsersCtrl', ['$http', 'clarolineSearch', function($http, clarolineSearch) {
	var vm = this;
	var translate = function(key) {
		return translator.trans(key, {}, 'platform');
	}

	this.userActions = [];
	$http.get(Routing.generate('api_get_user_admin_actions')).then(function(d) {
		vm.userActions = d.data;
	}.bind(this));

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
				var content = "<a class='btn btn-default' href='" +  Routing.generate('claro_desktop_open', {'_switch': scope.$row.username}) + "' data-toggle='tooltip' data-placement='bottom' title='' data-original-title='show_as' role='button'>" +
					"<i class='fa fa-eye'></i>" +
					"</a>";

				for (var i = 0; i < vm.userActions.length; i++) {
					var route = Routing.generate('admin_user_action', {'user': scope.$row.id, 'action': vm.userActions[i]['tool_name']});
					content += "<a class='btn btn-default' href='" + route + "'><i class='fa " + vm.userActions[i].class + "'></i></a>";
				}

				console.log(content);

				return '<div>' + content + '</div>';
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