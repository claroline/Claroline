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

        // Exercise modules
        'Common',
        'Exercise',
        'Question',
        'Paper',
        'Correction'
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
                    }
                })

                // Edit Exercise parameters
                .when('/edit', {
                    templateUrl : AngularApp.webDir + 'bundles/ujmexo/js/angular/Exercise/Partials/parameters.html',
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
                    templateUrl : AngularApp.webDir + 'bundles/ujmexo/js/angular/Question/list.html',
                    controller  : 'StepEditCtrl',
                    controllerAs: 'stepEditCtrl'
                })

                // Display Papers list
                .when('/papers/:eid', {
                    templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Paper/Partials/papers.list.html',
                    controller: 'PaperListCtrl',
                    controllerAs: 'paperListCtrl',
                    resolve: {
                        /**
                         * Get the paper details
                         */
                        papersPromise: [
                            '$route',
                            'PaperService',
                            function getPapers($route, PaperService) {

                                var promise = null;
                                if ($route.current.params && $route.current.params.eid) {
                                    promise = PaperService.getAll($route.current.params.eid);
                                }
                                return promise;
                            }
                        ],
                        paperExercise: [
                            '$route',
                            'PaperService',
                            function getSequence($route, PaperService) {

                                var promise = null;
                                if ($route.current.params && $route.current.params.eid) {
                                    promise = PaperService.getSequence($route.current.params.eid);

                                }
                                return promise;
                            }
                        ],
                        user: [
                            '$route',
                            'PaperService',
                            function getConnectedUserInfos($route, PaperService) {

                                var promise = null;
                                if ($route.current.params && $route.current.params.eid) {
                                    promise = PaperService.getConnectedUser($route.current.params.eid);
                                }
                                return promise;
                            }
                        ]
                    }
                })

                // Display a Paper
                .when('/papers/:eid/:pid', {
                    templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Paper/Partials/paper.show.html',
                    controller: 'PaperDetailsCtrl',
                    controllerAs: 'paperDetailsCtrl',
                    resolve: {
                        /**
                         * Get the paper details
                         */
                        paperPromise: [
                            '$route',
                            'CorrectionService',
                            function getPaper($route, CorrectionService) {

                                var promise = null;
                                if ($route.current.params && $route.current.params.eid && $route.current.params.pid) {
                                    promise = CorrectionService.getOne($route.current.params.eid, $route.current.params.pid);
                                }
                                return promise;
                            }
                        ],
                        paperExercise: [
                            '$route',
                            'PaperService',
                            function getSequence($route, PaperService) {

                                var promise = null;
                                if ($route.current.params && $route.current.params.eid) {
                                    promise = PaperService.getSequence($route.current.params.eid);
                                }
                                return promise;
                            }
                        ],
                        user: [
                            '$route',
                            'PaperService',
                            function getConnectedUserInfos($route, PaperService) {

                                var promise = null;
                                if ($route.current.params && $route.current.params.eid) {
                                    promise = PaperService.getConnectedUser($route.current.params.eid);
                                }
                                return promise;
                            }
                        ]
                    }
                })

                // Respond to Exercise
                .when('/play', {
                    templateUrl : AngularApp.webDir + 'bundles/ujmexo/js/angular/Exercise/player.html',
                    controller  : 'ExercisePlayerCtrl',
                    controllerAs: 'exercisePlayerCtrl'
                })

                // Otherwize redirect User on Overview
                .otherwise({
                    redirectTo: '/'
                });
        }
    ]);