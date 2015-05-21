/**
 * Editor routes
 * Use Angular routes to manage navigation between steps
 */
(function () {
    'use strict';

    angular.module('PathEditorApp')
        .config([
            '$routeProvider',
            '$locationProvider',
            function PathEditorConfig($routeProvider) {
                $routeProvider
                    .when('/:stepId?', {
                        templateUrl: EditorApp.webDir + 'bundles/innovapath/angularjs/Step/Partial/step-form.html',
                        controller: 'StepFormCtrl',
                        controllerAs: 'stepFormCtrl',
                        resolve: {
                            step: [
                                '$route',
                                'PathService',
                                function ($route, PathService) {
                                    var step = null;
                                    // Retrieve the step from route ID
                                    if ($route.current.params && $route.current.params.stepId) {
                                        step = PathService.getStep($route.current.params.stepId);
                                    }

                                    return step;
                                }
                            ],
                            inheritedResources: [
                                '$route',
                                'PathService',
                                function ($route, PathService) {
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
                        }
                    })
                    .otherwise({
                        redirectTo: '/:stepId?'
                    });
            }
        ])
        .run([
            '$rootScope',
            '$timeout',
            '$location',
            'PathService',
            function PathEditorRun($rootScope, $timeout, $location, PathService) {
                /*$rootScope.$on('$routeChangeStart', function (event, next, current) {
                    console.log('route change start');
                    var getRoot = false;

                    // Check if we need to redirect to the Root step
                    if (!angular.isDefined(next.params) || !angular.isDefined(next.params.stepId)) {
                        // No ID provided => get the Root step
                        getRoot = true;
                    } else {
                        // Check if step exists
                        var step = PathService.getStep(next.params.stepId);
                        if (!step) {
                            getRoot = true;
                        }
                    }

                    if (getRoot) {
                        var path = PathService.getPath();
                        if (path && path.steps instanceof Array && path.steps.length > 0) {
                            $location.path('/' + path.steps[0].id);
                        }
                    }
                });*/
            }
        ]);
})();