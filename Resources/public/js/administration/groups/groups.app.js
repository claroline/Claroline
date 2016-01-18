var groupsManager = angular.module('groupsManager', ['genericSearch',  'data-table']);
var translator = window.Translator;

groupsManager.controller('GroupsCtrl', ['$http', 'clarolineSearch', function($http, clarolineSearch) {
	var translate = function(key) {
		return translator.trans(key, {}, 'platform');
	}

	this.search = '';
	this.savedSearch = [];
	this.groups = [];

	var columns = [
		{name: translate('name'), prop: "name", isCheckboxColumn: true, headerCheckbox: true},
		{
			name: translate('actions'),
			cellRenderer: function(scope) {

				var actions = 'delete';

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
		clarolineSearch.find('api_get_search_groups', searches, this.dataTableOptions.paging.offset, this.dataTableOptions.paging.size).then(function(d) {
			this.groups = d.data.groups;
			this.dataTableOptions.paging.count = d.data.total;
		}.bind(this));
	}.bind(this);

	this.paging = function(offset, size) {
		clarolineSearch.find('api_get_search_groups', this.savedSearch, offset, size).then(function(d) {
			var groups = d.data.groups;

			//I know it's terrible... but I have no other choice with this table.
			for (var i = 0; i < offset * size; i++) {
				groups.unshift({});
			}

			this.groups = groups;
			this.dataTableOptions.paging.count = d.data.total;
		}.bind(this));
	}.bind(this);
}]);

groupsManager.directive('grouplist', [
	function userlist() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/groups/views/grouplist.html',
			replace: true,
			controllerAs: 'gc',
			controller: 'GroupsCtrl'
		}
	}
]);

groupsManager.directive('groupsearch', [
	function usersearch() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/groups/views/groupsearch.html',
			replace: true,
			controllerAs: 'gc',
			controller: 'GroupsCtrl'
		}
	}
]);