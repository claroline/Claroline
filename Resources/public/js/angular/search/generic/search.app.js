var genericSearch = angular.module('genericSearch', ['ui.select']);
var translator = window.Translator;

//let's do some initialization first.
genericSearch.config(function ($httpProvider) {
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

genericSearch.controller(
	'GenericSearchCtrl',
	['$log', '$http', 'clarolineSearch', 'searchOptionsService',
	function($log, $http, clarolineSearch, searchOptionsService)
{
	this.$log     = $log;
	this.selected = [];
	this.options  = [];

	this.refreshOptions = function($select) {
		console.log(this.fields);
		//I should not be doing this here. Probably in a directive would be better.
		this.options = searchOptionsService.generateOptions(this.fields);

		for (var i = 0; i < this.options.length; i++) {
			this.options[i].name = searchOptionsService.getOptionValue(this.options[i].field, $select.search);
			this.options[i].value = $select.search;
		}
	}.bind(this);

	this.onSelect = function($item, $model, $select) {
		//angular and its plugins does not make any sense to me.
		$select.selected.pop();
		var cloned = angular.copy($item);
		$select.selected.push(cloned);
		this.options.push(searchOptionsService.getOptionValue($item.field));
		this.selected = angular.copy($select.selected);
	}.bind(this);

	this.onRemove = function($item, $model, $select) {
		this.selected = $select.selected;
	}.bind(this);

	this.search = function() {
		//what the actual fuck ?
		//#brainmalfunction
		this.onSearch()(this.selected);
	}.bind(this);
}]);

genericSearch.service('searchOptionsService', function() {
	this.getOptionValue = function(field, search) {
		if (!field) return;
		search = !search ? '': search.trim();

		return translator.trans('filter_by', {}, 'platform') + ' ' + translator.trans(field, {}, 'platform').toLowerCase() + ': ' + search + '';
	}

	this.generateOptions = function(fields) {
		if (fields == undefined) return [];
		var options = [];

		for (var i = 0; i < fields.length; i++) {
			options.push(
				{
					id: i,
					name: this.getOptionValue(fields[i]),
					field: fields[i],
					value: ''
				}
			);
		}

		return options;
	}
});

genericSearch.provider("clarolineSearch", function() {
	this.enablePager = true;
	this.searchParam = {};

	var mergeObject = function(obj1, obj2) {

		for (var attrname in obj2) { 
			obj1[attrname] = obj2[attrname]; 
		}

		return obj1;
	}

	this.disablePager = function() {
		this.enablePager = false;
	}.bind(this);

	var vm = this;

	this.$get = function($http) {
		return {
			find: function(route, searches, page, limit) {
				var params = vm.enablePager ? {'page': page, 'limit': limit}: {};
				var qs = '?';

				for (var i = 0; i < searches.length; i++) {
					qs += searches[i].field +'[]=' + searches[i].value + '&';
				}

				params = mergeObject(params, vm.searchParam);
				var route = Routing.generate(route, params) + qs;

				return $http.get(route);
			}
		}
	};
});

genericSearch.directive('clarolinesearch', [
	function clarolinesearch() {
		var bindings = {
			onSearch: '&',
			fields: '='
		};

		return {
			scope: {},
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/angular/search/generic/views/search.html',
			replace: false,
			controller: 'GenericSearchCtrl',
			bindToController: bindings,
			controllerAs: 'cs'
		}
	}
]);