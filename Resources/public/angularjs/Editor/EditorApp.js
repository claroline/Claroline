'use strict';

// Declare app level module which depends on filters, and services
var EditorApp = angular.module('EditorApp', [
    'ngRoute', 
    'ui.bootstrap', 
    'ui.pageslide', 
    'ui.sortable', 
    'ui.tinymce'
]);

// History
EditorApp.factory('HistoryFactory', HistoryFactory);

// Clipboard
EditorApp.factory('ClipboardFactory', ClipboardFactory);

// Alerts
EditorApp.factory('AlertFactory', AlertFactory);
EditorApp.controller('AlertCtrl', AlertCtrl);

// Main
EditorApp.controller('MainCtrl', MainCtrl);

// Path
EditorApp.factory('PathFactory', PathFactory);
EditorApp.controller('GlobalCtrl', GlobalCtrl);
EditorApp.controller('TreeCtrl', TreeCtrl);

// Steps
EditorApp.factory('StepFactory', StepFactory);
EditorApp.controller('StepModalCtrl', StepModalCtrl);
EditorApp.controller('SelectImageModalCtrl', SelectImageModalCtrl);

// Resources
EditorApp.factory('ResourceFactory', ResourceFactory);
EditorApp.controller('ResourcePickerModalCtrl', ResourcePickerModalCtrl);
EditorApp.controller('ResourceModalCtrl', ResourceModalCtrl);

// Templates
EditorApp.factory('TemplateFactory', TemplateFactory);
EditorApp.controller('TemplateCtrl', TemplateCtrl);
EditorApp.controller('TemplateModalCtrl', TemplateModalCtrl);

EditorApp.filter('truncate', TruncateTextFilter);