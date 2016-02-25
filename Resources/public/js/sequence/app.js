/**
 * Exercise Root Application
 */
angular
    // Declare the new Application
    .module('ExerciseApp', [
        // Libraries
        'ngSanitize',
        'ngRoute',
        'angular-loading-bar',
        'ui.bootstrap',
        'ui.translation',
        'ngBootbox',

        // Exercise modules
        'Exercise'
    ])

    // Configure application
    .config([
        'cfpLoadingBarProvider',
        function ExerciseAppConfig(cfpLoadingBarProvider) {
            // please wait spinner config
            cfpLoadingBarProvider.latencyThreshold = 200;
            cfpLoadingBarProvider.includeBar       = false;
            cfpLoadingBarProvider.spinnerTemplate  = '<div class="loading">Loading&#8230;</div>';
        }
    ]);