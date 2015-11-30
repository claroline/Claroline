var usersSearch = angular.module('usersSearch', ['ui.select', 'ngSanitize']);

usersSearch.controller('usersSearchCtrl', function(
	$scope,
	$log,
	$http,
	$cacheFactory,
	usersSearcher
) {
	//should be dynamic because it'll include facets and so on...
	$scope.fields 		= ['username', 'last_name', 'first_name', 'email', 'administrative_code'];
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

	usersSearcher.setFields($scope.fields);

	$scope.options = generateOptions();
});


usersSearch.factory('usersSearcher', function($http) {
	var searcher = {};

	var parseSearchString = function(search) {
		var data = {};

		for (var i = 0; i < searcher.fields.length; i++) {
			regex = searcher.fields[i] + ':\\((.*?)\\)';
			regxp = new RegExp(regex, 'g');
			var grep = search.match(regxp);

			if (grep) {
				data[searcher.fields[i]] = [];
				for (var j = 0; j < grep.length; j++) {
					var val = grep[j].match(/\((.*)\)/);
					data[searcher.fields[i]].push(val[1]);
				}
			}
		}
		
		return data;
	} 

	searcher.setFields = function(fields) {
		searcher.fields = fields;
	}

	searcher.find = function(searches, page, limit) {
		if (searches.length > 0) {
			var qs = '?';

			for (var i = 0; i < searches.length; i++) {
				qs += searches[i].field +'[]=' + searches[i].value + '&';
			} 

			var route = Routing.generate('api_search_partial_list_users', {'page': page, 'limit': limit});
			route += qs;

			return $http.get(route);
		} else {
			//can't use fos js routing with nelmio api bundle T_T
			return $http.get(Routing.generate('api_get_partial_list_users', {'page': page, 'limit': limit}));
		}
	}

	return searcher;
});

usersSearch.directive('searchuser', [
	function userlist() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/angular/search/users/views/search.html',
			replace: false
		}
	}
]);