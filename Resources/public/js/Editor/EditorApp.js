'use strict';

// Declare app level module which depends on filters, and services
var EditorApp = angular.module('EditorApp', ['ui.bootstrap', 'pageslide-directive', 'ui.sortable']);

// Declare routes
EditorApp.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/404', {templateUrl: 'Editor/Partial/404.html'});
    
    $routeProvider.when('/', {
        templateUrl: EditorApp.webDir + 'js/Editor/Partial/global.html', 
        controller: GlobalCtrl,
        activeTab: 'Global',
        resolve: {
            path: ['PathFactory', function(PathFactory) { return PathFactory.loadPath(EditorApp.pathId); }]
        }
    });
    
    $routeProvider.when('/global', {
        templateUrl: EditorApp.webDir + 'js/Editor/Partial/global.html', 
        controller: GlobalCtrl,
        activeTab: 'Global',
        resolve: {
            path: ['PathFactory', function(PathFactory) { return PathFactory.loadPath(EditorApp.pathId); }]
        }
    });
    
    $routeProvider.when('/skills', {
        templateUrl: EditorApp.webDir + 'js/Editor/Partial/skills.html', 
        controller: TreeCtrl,
        activeTab: 'Skills',
        resolve: {
            path: ['PathFactory', function(PathFactory) { return PathFactory.loadPath(EditorApp.pathId); }]
        }
    });
    
    $routeProvider.when('/scenario', {
        templateUrl: EditorApp.webDir + 'js/Editor/Partial/scenario.html', 
        controller: TreeCtrl,
        activeTab: 'Scenario',
        resolve: {
            path: ['PathFactory', function(PathFactory) { return PathFactory.loadPath(EditorApp.pathId); }]
        }
    });
    
    $routeProvider.when('/planner', {
        templateUrl: EditorApp.webDir + 'js/Editor/Partial/planner.html', 
        controller: TreeCtrl,
        activeTab: 'Planner',
        resolve: {
            path: ['PathFactory', function(PathFactory) { return PathFactory.loadPath(EditorApp.pathId); }]
        }
    });
    
    $routeProvider.when('/validation', {
        templateUrl: EditorApp.webDir + 'js/Editor/Partial/validation.html', 
        controller: TreeCtrl,
        activeTab: 'Validation',
        resolve: {
            path: ['PathFactory', function(PathFactory) { return PathFactory.loadPath(EditorApp.pathId); }]
        }
    });
    
    $routeProvider.otherwise({redirectTo: '/404'});
}]);

// History
EditorApp.factory('HistoryFactory', HistoryFactory);

// Clipboard
EditorApp.factory('ClipboardFactory', ClipboardFactory);

// Alerts
//EditorApp.factory('AlertFactory', AlertFactoryProto);
//EditorApp.controller('AlertCtrl', AlertCtrlProto);

// Main
EditorApp.controller('MainCtrl', MainCtrl);

// Help
EditorApp.controller('HelpModalCtrl', HelpModalCtrl);

// Path
EditorApp.factory('PathFactory', PathFactory);
EditorApp.controller('GlobalCtrl', GlobalCtrl);
EditorApp.controller('TreeCtrl', TreeCtrl);

// Steps
EditorApp.factory('StepFactory', StepFactory);
EditorApp.controller('StepModalCtrl', StepModalCtrl);

// Resources
EditorApp.factory('ResourceFactory', ResourceFactory);
EditorApp.controller('ResourcePickerModalCtrl', ResourcePickerModalCtrl);
EditorApp.controller('ResourceModalCtrl', ResourceModalCtrl);

// Templates
EditorApp.factory('TemplateFactory', TemplateFactory);
EditorApp.controller('TemplateCtrl', TemplateCtrl);
EditorApp.controller('TemplateModalCtrl', TemplateModalCtrl);