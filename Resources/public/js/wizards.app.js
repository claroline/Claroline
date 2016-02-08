/**
 * Declares Editor and Player wizards
 */
(function initWizards() {
    'use strict';

    // Dependencies
    var dependencies = [
        'ngSanitize',
        'ngRoute',
        'ui.bootstrap',
        'pageslide-directive',
        'ui.tinymce',
        'ui.translation',
        'ui.tree',

        'AlertModule',
        'ClipboardModule',
        'ConfirmModule',
        'HistoryModule',
        'PathSummaryModule',
        'PathNavigationModule',
        'PathModule',
        'StepModule',
        /*'TemplateModule',*/
        'UserProgressionModule',
        'StepConditionsModule'
    ];

    // Resolve functions (it's the same between Editor and Player as we navigate in the same way in the 2 apps)
    var resolveFunctions = {
        /**
         * Get the current Step from route params
         */
        step: [
            '$q',
            '$route',
            'PathService',
            function getCurrentStep($q, $route, PathService) {
                var defer = $q.defer();

                var step = null;

                // Retrieve the step from route ID
                if ($route.current.params && $route.current.params.stepId) {
                    step = PathService.getStep($route.current.params.stepId);
                }

                if (angular.isDefined(step) && angular.isObject(step)) {
                    defer.resolve(step);
                } else {
                    defer.reject('step_not_found');
                }

                return defer.promise;
            }
        ],

        /**
         * Get inherited resources for the current Step
         */
        inheritedResources: [
            '$route',
            'PathService',
            function getCurrentInheritedResources($route, PathService) {
                var inherited = [];

                var step = PathService.getStep($route.current.params.stepId);
                if (angular.isDefined(step) && angular.isObject(step)) {
                    var path = PathService.getPath();

                    // Grab inherited resources
                    inherited = PathService.getStepInheritedResources(path.steps, step);
                }

                return inherited;
            }
        ]
    };

    // Get the Root step and its resources
    var resolveRootFunctions = {
        /**
         * Get the Root step of the Path
         */
        step: [
            'PathService',
            function getRootStep(PathService) {
                return PathService.getRoot();
            }
        ],

        /**
         * Get inherited resources for the Root step
         */
        inheritedResources: [
            'PathService',
            function getRootInheritedResources(PathService) {
                var inherited = [];

                var path = PathService.getPath();
                if (angular.isObject(path) && angular.isObject(path.steps) && angular.isObject(path.steps[0])) {
                    // Grab inherited resources
                    inherited = PathService.getStepInheritedResources(path.steps, path.steps[0]);
                }

                return inherited;
            }
        ],
        /**
         * get return values of promises made in PathService to be available elsewhere
         */
        /**
         * list of all user group in Claro
         */
        allgroups: [
            'PathService',
            function getAllgroups(PathService) {
                return PathService.usergrouppromise;
            }
        ],
        /**
         * list of all evaluation statuses in Claro
         */
        conditionEvaluationStatuses: [
            'PathService',
            function getEvaluationStatuses(PathService) {
                return PathService.evaluationstatusespromise;
            }
        ]
    };

    var appRun = [
        '$rootScope',
        '$location',
        function appRun($rootScope, $location) {
            $rootScope.$on("$routeChangeError", function handleRouteChangeError(evt, current, previous, rejection) {
                // If step not found, redirect user to rhe Root step
                if ('step_not_found' == rejection) {
                    $location.path('/');
                }
            });
        }
    ];

    angular
        // Path Editor application
        .module('PathEditorApp', dependencies)

        // Declare routes
        .config([
            '$routeProvider',
            function PathEditorConfig($routeProvider) {
                $routeProvider
                    .when('/', {
                        templateUrl: AngularApp.webDir + 'bundles/innovapath/js/Step/Partial/edit.html',
                        controller: 'StepEditCtrl',
                        controllerAs: 'stepEditCtrl',
                        resolve: resolveRootFunctions
                    })
                    .when('/:stepId?', {
                        templateUrl: AngularApp.webDir + 'bundles/innovapath/js/Step/Partial/edit.html',
                        controller: 'StepEditCtrl',
                        controllerAs: 'stepEditCtrl',
                        resolve: resolveFunctions
                    })
                    .otherwise({
                        redirectTo: '/:stepId?'
                    });
            }
        ])

        // Bind run function
        .run(appRun);

    angular
        // Path Player application
        .module('PathPlayerApp', dependencies)

        // Declare routes
        .config([
            '$routeProvider',
            function PathPlayerConfig($routeProvider) {
                // Declare route to navigate between steps
                $routeProvider
                    .when('/', {
                        templateUrl: AngularApp.webDir + 'bundles/innovapath/js/Step/Partial/show.html',
                        controller: 'StepShowCtrl',
                        controllerAs: 'stepShowCtrl',
                        resolve: angular.merge({
                            // Always allow access to the Root step
                            authorization: [
                                function authorizationRootResolve() {
                                    return { granted: true };
                                }
                            ]
                        }, resolveRootFunctions)
                    })
                    .when('/:stepId?', {
                        templateUrl: AngularApp.webDir + 'bundles/innovapath/js/Step/Partial/show.html',
                        controller: 'StepShowCtrl',
                        controllerAs: 'stepShowCtrl',
                        resolve: angular.merge({
                            // Add authorization checker
                            authorization: [
                                '$route',
                                'PathService',
                                'AuthorizationCheckerService',
                                function authorizationResolve($route, PathService, AuthorizationCheckerService) {
                                    var authorization = false;

                                    var step = PathService.getStep($route.current.params.stepId);
                                    if (angular.isDefined(step) && angular.isObject(step)) {
                                        authorization = AuthorizationCheckerService.isAuthorized(step);
                                    }

                                    return authorization;
                                }
                            ]
                        }, resolveFunctions)
                    })
                    .otherwise({
                        redirectTo: '/:stepId?'
                    });
            }
        ])

        // Bind run function
        .run(appRun);
})();