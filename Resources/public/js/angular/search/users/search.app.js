var usersSearch = angular.module('usersSearch', ['ui.select', 'ngSanitize', 'genericSearch']);

usersSearch.config(function(clarolineSearchProvider) {
	clarolineSearchProvider.setBaseRoute('api_search_partial_list_users');
	clarolineSearchProvider.setSearchRoute('api_get_partial_list_users');
	clarolineSearchProvider.setFields(['username', 'last_name', 'first_name', 'email', 'administrative_code']);
});

usersSearch.factory('usersSearcher', function($http, genericSearcher) {
	var searcher = {};

	searcher.find = function(searches, page, limit) {
		return genericSearcher.find(searches, page, limit)
	}

	searcher.getCurrent = function() {
		return genericSearcher.getCurrent();
	}

	return searcher;
});

usersSearch.directive('searchuser', [
	function usersearch() {
		return {
			restrict: 'E',
			templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/angular/search/users/views/search.html',
			replace: false
		}
	}
]);