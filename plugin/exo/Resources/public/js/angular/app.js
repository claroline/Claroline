/**
 * Exercise Root Application
 */
angular
    // Declare the new Application
    .module('ExerciseApp', [
        'ngRoute',
        'angular-loading-bar',
        'mgcrea.ngStrap.datepicker',
        'Exercise',
        'Step',
        'Paper'
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
                modelDateFormat: 'yyyy-MM-dd\THH:mm:ss',
                autoclose: true
            });

            // Define routes
            $routeProvider
                // Overview
                .when('/', {
                    templateUrl : AngularApp.webDir + 'bundles/ujmexo/js/angular/Exercise/Partials/overview.html',
                    controller  : 'ExerciseOverviewCtrl',
                    controllerAs: 'exerciseOverviewCtrl',
                    tab: 'overview'
                })

                // Edit Exercise parameters
                .when('/edit', {
                    templateUrl : AngularApp.webDir + 'bundles/ujmexo/js/angular/Exercise/Partials/metadata.html',
                    controller  : 'ExerciseMetadataCtrl',
                    controllerAs: 'exerciseMetadataCtrl',
                    tab: 'metadata'
                })

                // Display the list of Questions
                .when('/steps', {
                    templateUrl : AngularApp.webDir + 'bundles/ujmexo/js/angular/Step/Partials/list.html',
                    controller  : 'StepListCtrl',
                    controllerAs: 'stepListCtrl',
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
                                    promise = PaperService.getCurrent($route.current.params.id);
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
