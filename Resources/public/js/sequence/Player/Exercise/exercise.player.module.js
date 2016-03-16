/**
 * ExercisePlayerApp
 */

(function () {
    'use strict';

    var dependencies = [
        'ngSanitize',
        'ngRoute',
        'angular-loading-bar',
        'ui.bootstrap',
        'ui.translation',
        'ngBootbox',
        'Common',
        'PlayerSharedServices',
        'Question',
        'ngStorage'
    ];
    // exercise player module
    var ExercisePlayerApp = angular.module('ExercisePlayerApp', dependencies);
    
    ExercisePlayerApp.config([
        'cfpLoadingBarProvider',
        function ExercisePlayerAppConfig(cfpLoadingBarProvider) {
            
            // please wait spinner config
            cfpLoadingBarProvider.latencyThreshold = 200;
            cfpLoadingBarProvider.includeBar = false;
            cfpLoadingBarProvider.spinnerTemplate = '<div class="loading">Loading&#8230;</div>';
        }
    ]);
    
     ExercisePlayerApp.filter(
            'unsafe',
            function ($sce) {
                return $sce.trustAsHtml;
            });
})();

