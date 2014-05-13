'use strict';

var portfolioApp = angular.module('portfolioApp', ['ngResource', 'ngSanitize']);

portfolioApp.config(["$httpProvider", "$locationProvider", function($http) {
    var elementToRemove = ['views', 'editing'];

    $http.defaults.transformRequest.push(function(data) {
        data = angular.fromJson(data);
        angular.forEach(data, function(element, index) {
            if(elementToRemove.inArray(index)) {
                delete data[index];
            }
        });
        return JSON.stringify(data);
    });
}]);

// Bootstrap portfolio application
angular.element(document).ready(function() {
    angular.bootstrap(document, ['portfolioApp']);
});