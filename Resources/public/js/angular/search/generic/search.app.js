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

genericSearch.controller('GenericSearchCtrl', ['$log', '$http', 'clarolineSearch', 'searchOptionsService', function(
	$log,
	$http,
	clarolineSearch,
	searchOptionsService
) {
	this.fields   = [];
	this.$log     = $log;
	this.searches = [];
	this.selected = [];
	this.options  = [];

	//init field list
	$http.get(Routing.generate(clarolineSearch.getFieldRoute())).then(function(d) {
		this.fields = d.data;
		this.options = searchOptionsService.generateOptions(this.fields);
	}.bind(this));

	this.refreshOptions = function($select) {
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
		this.selected = $select.selected;
	}.bind(this);

	this.onRemove = function($item, $model, $select) {
		this.selected = $select.selected;
	}.bind(this);

	this.search = function(searches) {
		this.onSearch(searches);
	}.bind(this);
}]);

genericSearch.service('searchOptionsService', function() {
	this.getOptionValue = function(field, search) {
		if (!field) return;
		search = !search ? '': search.trim();

		return translator.trans('filter_by', {}, 'platform') + ' ' + translator.trans(field, {}, 'platform').toLowerCase() + ': ' + search + '';
	}

	this.generateOptions = function(fields) {
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
	var baseRoute = searchRoute = fieldRoute = '';

	this.enablePager = true;
	this.baseParam = {};
	this.searchParam = {};
	var that = this;

	var mergeObject = function(obj1, obj2) {

		for (var attrname in obj2) { 
			obj1[attrname] = obj2[attrname]; 
		}

		return obj1;
	}

	this.setBaseRoute = function(route, baseParam) {
		baseRoute = route;
		this.baseParam = baseParam || {};
	}.bind(this);
	
	this.setSearchRoute = function(route, searchParam) {
		searchRoute = route;
		this.searchParam = searchParam || {};
	}.bind(this);

	this.setFieldRoute = function(route) {
		fieldRoute = route;
	};

	this.disablePager = function() {
		this.enablePager = false;
	}.bind(this);

	//I should remove that
	var that = this;

	this.$get = function($http) {
		return {
			getBaseRoute: function() {
				return baseRoute;
			},
			getSearchRoute: function() {
				return searchRoute;
			},
			getFieldRoute: function() {
				return fieldRoute;
			},
			find: function(searches, page, limit) {
				var params = that.enablePager ? {'page': page, 'limit': limit}: {};

				if (searches.length > 0) {
					var qs = '?';

					for (var i = 0; i < searches.length; i++) {
						qs += searches[i].field +'[]=' + searches[i].value + '&';
					} 

					params = mergeObject(params, that.searchParam);
					var route = Routing.generate(searchRoute, params) + qs;

					return $http.get(route);
				} else {
					params = mergeObject(params, that.baseParam);

					return $http.get(Routing.generate(baseRoute, params));
				}
			}
		}
	};
});

genericSearch.directive('clarolinesearch', [
	function clarolinesearch() {
		var bindings = {
			onSearch: '&'
		};

		return {
			scope: {},
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/angular/search/generic/views/search.html',
			replace: false,
			controller: 'GenericSearchCtrl',
			bindToController: bindings,
			controllerAs: 'cs',
			link: function(scope, elem, attrs) {
				//scope.onSearch()(scope.searches);
			}
		}
	}
]);