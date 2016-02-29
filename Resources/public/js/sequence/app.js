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
        'ui.tinymce',
        'ngBootbox',

        // Exercise modules
        'Exercise'
    ])

    // Configure application
    .config([
        '$routeProvider',
        'cfpLoadingBarProvider',
        function ExerciseAppConfig($routeProvider, cfpLoadingBarProvider) {
            // please wait spinner config
            cfpLoadingBarProvider.latencyThreshold = 200;
            cfpLoadingBarProvider.includeBar       = false;
            cfpLoadingBarProvider.spinnerTemplate  = '<div class="loading">Loading&#8230;</div>';

            // Define routes
            $routeProvider
                // Overview
                .when('/', {
                    templateUrl : AngularApp.webDir + 'bundles/ujmexo/js/sequence/Player/Exercise/Partials/overview.html',
                    controller  : 'ExerciseOverviewCtrl',
                    controllerAs: 'exerciseOverviewCtrl',
                    resolve: {
                        // Get the Exercise to Display
                        exercise: [
                            'ExerciseService',
                            function exerciseResolve(ExerciseService) {
                                return ExerciseService.getExercise();
                            }
                        ],
                        editEnabled: [
                            'ExerciseService',
                            function editEnabledResolve(ExerciseService) {
                                return ExerciseService.isEditEnabled();
                            }
                        ]
                    }
                })

                // Edit Exercise parameters
                .when('/edit', {
                    templateUrl : AngularApp.webDir + 'bundles/ujmexo/js/sequence/Player/Exercise/Partials/parameters.html',
                    controller  : 'ExerciseParametersCtrl',
                    controllerAs: 'exerciseParametersCtrl',
                    resolve: {
                        // Get the Exercise to Edit
                        exercise: [
                            'ExerciseService',
                            function exerciseResolve(ExerciseService) {
                                return ExerciseService.getExercise();
                            }
                        ]
                    }
                })

                // Display the list of Questions
                .when('/questions', {
                    templateUrl : AngularApp.webDir + 'bundles/ujmexo/js/.html',
                    controller  : 'StepEditCtrl',
                    controllerAs: 'stepEditCtrl'
                })

                // Display Results
                .when('/results', {
                    templateUrl : AngularApp.webDir + 'bundles/ujmexo/js/.html',
                    controller  : 'StepEditCtrl',
                    controllerAs: 'stepEditCtrl'
                })

                // Respond to Exercise
                .when('/play', {
                    templateUrl : AngularApp.webDir + 'bundles/ujmexo/js/.html',
                    controller  : 'StepEditCtrl',
                    controllerAs: 'stepEditCtrl'
                })

                // Otherwize redirect User on Overview
                .otherwise({
                    redirectTo: '/'
                });
        }
    ]);