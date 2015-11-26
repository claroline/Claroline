var usersSearch = angular.module('usersSearch', []);

usersSearch.controller('usersSearchCtrl', function(
	$scope,
	$log,
	$http,
	$cacheFactory,
	usersSearcher
) {
	//should be dynamic because it'll include facets and so on...
	$scope.fields 		= ['username', 'last_name', 'first_name', 'email', 'administrative_code', 'all', '   create'];
	$scope.$log   		= $log;
	$scope.stringSearch = '';
	usersSearcher.setFields($scope.fields);
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

	searcher.find = function(search, page, limit) {
		if (search.trim() !== '') {
			var qs = '?';
			data = parseSearchString(search);
			for (var prop in data) {
				if (data.hasOwnProperty(prop)) {
					for (var i = 0; i < data[prop].length; i++) {
						qs += prop + '[]=' + data[prop][i] + '&';
					}
				}
			}
			console.log(qs);
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