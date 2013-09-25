'use strict';

// Declare app level module which depends on filters, and services
var EditorApp = angular.module('EditorApp', ['ui.bootstrap', 'pageslide-directive']);

// Declare routes
EditorApp.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/404', {templateUrl: 'partials/404.html'});
    
    $routeProvider.when('/', {
        templateUrl: EditorApp.templateRoute + 'Editor/global.html', 
        controller: 'TreeCtrl'
    });
    
    $routeProvider.when('/global', {
        templateUrl: EditorApp.templateRoute + 'Editor/global.html', 
        controller: 'TreeCtrl'
    });
    
    $routeProvider.when('/global/:id', {
        templateUrl: EditorApp.templateRoute + 'Editor/global.html', 
        controller: 'TreeCtrl'
    });
    
    $routeProvider.when('/skills/:id', {
        templateUrl: EditorApp.templateRoute + 'Editor/skills.html', 
        controller: 'TreeCtrl'
    });
    
    $routeProvider.when('/scenario/:id', {
        templateUrl: EditorApp.templateRoute + 'Editor/scenario.html', 
        controller: 'TreeCtrl'
    });
    
    $routeProvider.when('/validation/:id', {
        templateUrl: EditorApp.templateRoute + 'Editor/validation.html', 
        controller: 'TreeCtrl'
    });
    
    $routeProvider.when('/planner/:id', {
        templateUrl: EditorApp.templateRoute + 'Editor/planner.html', 
        controller: 'TreeCtrl'
    });
    
    $routeProvider.otherwise({redirectTo: '/404'});
}]);

// History
EditorApp.factory('HistoryFactory', HistoryFactoryProto);

// Clipboard
EditorApp.factory('ClipboardFactory', ClipboardFactoryProto);

// Alerts
//EditorApp.factory('AlertFactory', AlertFactoryProto);
//EditorApp.controller('AlertCtrl', AlertCtrlProto);

// Help
EditorApp.controller('HelpModalCtrl', HelpModalCtrlProto);

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