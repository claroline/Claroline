'use strict';

// Declare app level module which depends on filters, and services
var EditorApp = angular.module('EditorApp', ['ui.bootstrap', 'pageslide-directive']);

// Declare routes
EditorApp.config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/404', {templateUrl: 'Editor/Partial/404.html'});
    
    $routeProvider.when('/', {
        templateUrl: EditorApp.webDir + 'js/Editor/Partial/global.html', 
        controller: 'TreeCtrl',
        activeTab: 'Global'
    });
    
    $routeProvider.when('/global', {
        templateUrl: EditorApp.webDir + 'js/Editor/Partial/global.html', 
        controller: 'TreeCtrl',
        activeTab: 'Global'
    });
    
    $routeProvider.when('/skills', {
        templateUrl: EditorApp.webDir + 'js/Editor/Partial/skills.html', 
        controller: 'TreeCtrl',
        activeTab: 'Skills'
    });
    
    $routeProvider.when('/scenario', {
        templateUrl: EditorApp.webDir + 'js/Editor/Partial/scenario.html', 
        controller: 'TreeCtrl',
        activeTab: 'Scenario'
    });
    
    $routeProvider.when('/planner', {
        templateUrl: EditorApp.webDir + 'js/Editor/Partial/planner.html', 
        controller: 'TreeCtrl',
        activeTab: 'Planner'
    });
    
    $routeProvider.when('/validation', {
        templateUrl: EditorApp.webDir + 'js/Editor/Partial/validation.html', 
        controller: 'TreeCtrl',
        activeTab: 'Validation'
    });
    
    $routeProvider.otherwise({redirectTo: '/404'});
}]);

// Page Slide
//EditorApp.directive('pageslide', PageslideProto);

// Sortable
//EditorApp.directive('uiSortable', UISortableProto);

// History
EditorApp.factory('HistoryFactory', HistoryFactoryProto);

// Clipboard
EditorApp.factory('ClipboardFactory', ClipboardFactoryProto);

// Alerts
//EditorApp.factory('AlertFactory', AlertFactoryProto);
//EditorApp.controller('AlertCtrl', AlertCtrlProto);

// Main
EditorApp.controller('MainCtrl', MainCtrlProto);

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