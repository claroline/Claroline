'use strict';

// Declare app level module which depends on filters, and services
var playerApp = angular.module('playerApp', ['ui.bootstrap', 'pageslide-directive']);

// Declare routes
playerApp.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/404', {templateUrl: 'partials/404.html'});
    
    $routeProvider.when('/', {
        templateUrl: playerApp.templateRoute + 'global.html', 
        controller: MainCtrl
    });
    
    $routeProvider.otherwise({redirectTo: '/404'});
}]);


playerApp.controller('MainCtrl', MainCtrl);

// Path
playerApp.controller('ResourceSidebarCtrl', ResourceSidebarCtrl);