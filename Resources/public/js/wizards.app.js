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
        'PathBreadcrumbsModule',
        'PathModule',
        'StepModule',
        'TemplateModule'
    ];

    // Resolve functions (it's the same between Editor and Player as we navigate in the same way in the 2 apps)
    var resolveFunctions = {
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
    };

    // Get the Root step and its resources
    var resolveRootFunctions = {
        step: [
            'PathService',
            function (PathService) {
                return PathService.getRoot();
            }
        ],
        inheritedResources: [
            'PathService',
            function (PathService) {
                var inherited = [];

                var path = PathService.getPath();
                if (angular.isObject(path) && angular.isObject(path.steps) && angular.isObject(path.steps[0])) {
                    // Grab inherited resources
                    inherited = PathService.getStepInheritedResources(path.steps, path.steps[0]);
                }

                return inherited;
            }
        ]
    };

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
                        controller: StepEditCtrl,
                        controllerAs: 'stepEditCtrl',
                        resolve: resolveRootFunctions
                    })
                    .when('/:stepId?', {
                        templateUrl: AngularApp.webDir + 'bundles/innovapath/js/Step/Partial/edit.html',
                        controller: StepEditCtrl,
                        controllerAs: 'stepEditCtrl',
                        resolve: resolveFunctions
                    })
                    .otherwise({
                        redirectTo: '/:stepId?'
                    });
            }
        ]);


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
                        controller: StepShowCtrl,
                        controllerAs: 'stepShowCtrl',
                        resolve: resolveRootFunctions
                    })
                    .when('/:stepId?', {
                        templateUrl: AngularApp.webDir + 'bundles/innovapath/js/Step/Partial/show.html',
                        controller: StepShowCtrl,
                        controllerAs: 'stepShowCtrl',
                        resolve: resolveFunctions
                    })
                    .otherwise({
                        redirectTo: '/:stepId?'
                    });
            }
        ]);
})();