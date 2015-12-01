var genericSearch = angular.module('genericSearch', ['ui.select']);

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

genericSearch.controller('genericSearchCtrl', function(
	$scope,
	$log,
	$http,
	$cacheFactory,
	clarolineSearch,
	genericSearcher
) {
	$scope.fields 		= clarolineSearch.getFields();
	$scope.$log   		= $log;
	$scope.searches     = [];
	$scope.selected     = [];

	$scope.refreshOption = function($select) {		
		for (var i = 0; i < $scope.options.length; i++) {
			$scope.options[i].name = getOptionValue($scope.options[i].field, $select.search);
			$scope.options[i].value = $select.search;
		}
	}

	$scope.onSelect = function($item, $model, $select) {
		//angular and its plugins does not make any sense to me.
		$select.selected.pop();
		var cloned = angular.copy($item);
		$select.selected.push(cloned);
		$scope.options.push(getOptionValue($item.field));
		$scope.selected = $select.selected;
	}

	$scope.onRemove = function($item, $model, $select) {
		//angular and its plugins does not make any sense to me.
		//$select.selected.pop();
		//var cloned = angular.copy($item);
		//$select.selected.push(cloned);
		//$scope.options.push(getOptionValue($item.field));
		$scope.selected = $select.selected;
	}

	var getOptionValue = function(field, search) {
		search = !search ? '': search.trim();
		
		return field + ':(' + search + ')'; 
	}

	var generateOptions = function() {
		var options = [];

		for (var i = 0; i < $scope.fields.length; i++) {
			options.push(
				{
					id: i,
					name: getOptionValue($scope.fields[i]),
					field: $scope.fields[i],
					value: ''
				}
			);
		}

		return options;
	}

	genericSearcher.setFields($scope.fields);

	$scope.options = generateOptions();
});


genericSearch.factory('genericSearcher', function($http, clarolineSearch) {
	var searcher = {};

	searcher.setFields = function(fields) {
		searcher.fields = fields;
	}

	searcher.find = function(searches, page, limit) {
		var baseRoute = clarolineSearch.getBaseRoute();
		var searchRoute = clarolineSearch.getSearchRoute();

		if (searches.length > 0) {
			var qs = '?';

			for (var i = 0; i < searches.length; i++) {
				qs += searches[i].field +'[]=' + searches[i].value + '&';
			} 

			var route = Routing.generate(baseRoute, {'page': page, 'limit': limit});
			route += qs;

			return $http.get(route);
		} else {
			return $http.get(Routing.generate(searchRoute, {'page': page, 'limit': limit}));
		}
	}

	return searcher;
});

genericSearch.directive('clarolinesearch', [
	function clarolinesearch() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/angular/search/generic/views/search.html',
			replace: false
		}
	}
]);

genericSearch.provider("clarolineSearch", function() {
	var baseRoute = searchRoute = searchFields = '';

	this.setBaseRoute = function(route) {
		baseRoute = route;
	};
	
	this.setSearchRoute = function(route) {
		searchRoute = route;
	};

	this.setFields = function(fields) {
		searchFields = fields;
	};

	this.$get = function() {
		return {
			getBaseRoute: function() {
				return baseRoute;
			},
			getSearchRoute: function() {
				return searchRoute;
			},
			getFields: function() {
				return searchFields;
			}
		}
	}
});