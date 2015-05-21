/**
 * Editor routes
 * Use Angular routes to manage navigation between steps
 */
(function () {
    'use strict';

    angular.module('PathEditorApp')
        .config([
            '$routeProvider',
            function PathEditorConfig($routeProvider) {
                $routeProvider
                    .when('/:stepId?', {
                        templateUrl: AngularApp.webDir + 'bundles/innovapath/angularjs/Step/Partial/step-form.html',
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
        ]);
})();