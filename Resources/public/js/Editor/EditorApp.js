'use strict';

// Declare app level module which depends on filters, and services
var EditorApp = angular.module('EditorApp', ['ui.bootstrap', 'pageslide-directive', 'notifications']);

// Declare routes
EditorApp.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/404', {templateUrl: 'partials/404.html'});
    
    $routeProvider.when('/path/global', {templateUrl: 'partials/editing-steps/global.html', controller: 'TreeCtrl'});
    $routeProvider.when('/path/global/:id', {templateUrl: 'partials/editing-steps/global.html', controller: 'TreeCtrl'});
    $routeProvider.when('/path/skills/:id', {templateUrl: 'partials/editing-steps/skills.html', controller: 'TreeCtrl'});
    $routeProvider.when('/path/scenario/:id', {templateUrl: 'partials/editing-steps/scenario.html', controller: 'TreeCtrl'});
    $routeProvider.when('/path/validation/:id', {templateUrl: 'partials/editing-steps/validation.html', controller: 'TreeCtrl'});
    $routeProvider.when('/path/planner/:id', {templateUrl: 'partials/editing-steps/planner.html', controller: 'TreeCtrl'});
    
    $routeProvider.otherwise({redirectTo: '/404'});
}]);

// History
EditorApp.factory('HistoryFactory', historyFactoryProto);

// Clipboard
EditorApp.factory('ClipboardFactory', ClipboardFactoryProto);

// Alerts
//EditorApp.factory('AlertFactory', AlertFactoryProto);
//EditorApp.controller('AlertCtrl', AlertCtrlProto);

// Help
Editor.controller('HelpModalCtrl', HelpModalCtrlProto);

// Path
EditorApp.factory('PathFactory', PathFactoryProto);
EditorApp.controller('TreeCtrl', TreeCtrlProto);

// Steps
EditorApp.factory('StepFactory', StepFactoryProto);
EditorApp.controller('StepModalCtrl', StepModalCtrlProto);

// Resources
EditorApp.factory('ResourceFactory', ResourceFactoryProto);
EditorApp.controller('ResourceModalCtrl', ResourceModalCtrlProto);

// Templates
EditorApp.factory('TemplateFactory', TemplateFactoryProto);
EditorApp.controller('TemplateCtrl', TemplateCtrlProto);
EditorApp.controller('TemplateModalCtrl', TemplateModalCtrlProto);