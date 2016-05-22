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
        'angular-table',
        'ui.bootstrap',
        'ui.translation',
        'ui.tinymce',
        'ngBootbox',
        'mgcrea.ngStrap.datepicker',

        // Exercise modules
        'Common',
        'Step',
        'Exercise',
        'Question',
        'Paper',
        'Correction',
        'ngStorage'
    ])

    // Configure application
    .config([
        '$routeProvider',
        'cfpLoadingBarProvider',
        '$datepickerProvider',
        function ExerciseAppConfig($routeProvider, cfpLoadingBarProvider, $datepickerProvider) {
            // Configure loader
            cfpLoadingBarProvider.latencyThreshold = 200;
            cfpLoadingBarProvider.includeBar       = false;
            cfpLoadingBarProvider.spinnerTemplate  = '<div class="loading">Loading&#8230;</div>';

            // Configure DatePicker
            angular.extend($datepickerProvider.defaults, {
                dateFormat: 'dd/MM/yyyy',
                dateType: 'string',
                startWeek: 1,
                iconLeft: 'fa fa-fw fa-chevron-left',
                iconRight: 'fa fa-fw fa-chevron-right',
                modelDateFormat: 'yyyy-MM-dd HH:mm:ss',
                autoclose: true
            });

            // Define routes
            $routeProvider
                // Overview
                .when('/', {
                    templateUrl : AngularApp.webDir + 'bundles/ujmexo/js/angular/Exercise/Partials/overview.html',
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
                    },

                    // Active tab
                    tab: 'overview'
                })

                // Edit Exercise parameters
                .when('/edit', {
                    templateUrl : AngularApp.webDir + 'bundles/ujmexo/js/angular/Exercise/Partials/metadata.html',
                    controller  : 'ExerciseMetadataCtrl',
                    controllerAs: 'exerciseMetadataCtrl',
                    resolve: {
                        // Get the Exercise to Edit
                        exercise: [
                            'ExerciseService',
                            function exerciseResolve(ExerciseService) {
                                return ExerciseService.getExercise();
                            }
                        ]
                    },

                    // Active tab
                    tab: 'metadata'
                })

                // Display the list of Questions
                .when('/steps', {
                    templateUrl : AngularApp.webDir + 'bundles/ujmexo/js/angular/Step/Partials/list.html',
                    controller  : 'StepListCtrl',
                    controllerAs: 'stepListCtrl',
                    resolve: {
                        exerciseId: [
                            'ExerciseService',
                            function exerciseIdResolve(ExerciseService) {
                                var exercise = ExerciseService.getExercise();

                                return exercise ? exercise.id : null;
                            }
                        ],

                        // Get the list of Steps from the Exercise
                        steps: [
                            'ExerciseService',
                            function stepsResolve(ExerciseService) {
                                return ExerciseService.getSteps();
                            }
                        ]
                    },

                    tab: 'steps'
                })

                // Display Papers list
                .when('/papers', {
                    templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Paper/Partials/list.html',
                    controller: 'PaperListCtrl',
                    controllerAs: 'paperListCtrl',
                    resolve: {
                        papers: [
                            'PaperService',
                            function papersResolve(PaperService) {
                                return PaperService.getAll();
                            }
                        ],
                        exercise: [
                            'ExerciseService',
                            function exerciseResolve(ExerciseService) {
                                return ExerciseService.getExercise();
                            }
                        ]
                    },

                    // Active tab
                    tab: 'papers'
                })

                // Display a Paper
                .when('/papers/:id', {
                    templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Paper/Partials/show.html',
                    controller: 'PaperShowCtrl',
                    controllerAs: 'paperShowCtrl',
                    resolve: {
                        paperPromise: [
                            '$route',
                            'PaperService',
                            function paperResolve($route, PaperService) {
                                var promise = null;
                                if ($route.current.params && $route.current.params.id) {
                                    promise = PaperService.get($route.current.params.id);
                                }

                                return promise;
                            }
                        ]
                    },

                    // Active tab
                    tab: 'papers'
                })

                // Respond to Exercise
                .when('/play/:stepId?', {
                    templateUrl : AngularApp.webDir + 'bundles/ujmexo/js/angular/Exercise/Partials/player.html',
                    controller  : 'ExercisePlayerCtrl',
                    controllerAs: 'exercisePlayerCtrl',
                    resolve: {
                        exercise: [
                            'ExerciseService',
                            function exerciseResolve(ExerciseService) {
                                return ExerciseService.getExercise();
                            }
                        ],
                        paper: [
                            'ExerciseService',
                            'UserPaperService',
                            function paperResolve(ExerciseService, UserPaperService) {
                                // Start a new Attempt and retrieve the Paper if maxAttempt is not reach
                                return UserPaperService.start(ExerciseService.getExercise());
                            }
                        ],
                        step: [
                            '$route',
                            'ExerciseService',
                            function stepResolve($route, ExerciseService) {
                                var step = null;

                                // Retrieve the step from route ID
                                if ($route.current.params && $route.current.params.stepId) {
                                    step = ExerciseService.getStep($route.current.params.stepId);
                                } else {
                                    // No route param => open the first Step
                                    var steps = ExerciseService.getSteps();
                                    if (steps && steps[0]) {
                                        step = steps[0];
                                    }
                                }

                                return step;
                            }
                        ]
                    },

                    // Active tab
                    tab: 'play'
                })

                // Otherwise redirect User on Overview
                .otherwise({
                    redirectTo: '/'
                });
        }
    ]);