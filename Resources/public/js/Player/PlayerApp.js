'use strict';

// Declare app level module which depends on filters, and services
var playerApp = angular.module('playerApp', ['ui.bootstrap', 'pageslide-directive']);

// Declare routes
playerApp.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/404', {templateUrl: 'partials/404.html'});
    
    $routeProvider.when('/', {
        templateUrl: playerApp.templateRoute + 'global.html', 
        controller: 'mainCtrl'
    });
    
    $routeProvider.otherwise({redirectTo: '/404'});
}]);

// Path
playerApp.factory('PathFactory', PathFactoryProto);
playerApp.controller('TreeCtrl', TreeCtrlProto);

