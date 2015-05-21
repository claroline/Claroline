/**
 * Manages Secondary Resources
 */
(function () {
    'use strict';

    angular.module('ResourceModule').directive('secondaryResources', [
        function SecondaryResourcesDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: 'SecondaryResourcesCtrl',
                controllerAs: 'secondaryResourcesCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/angularjs/Resource/Partial/secondary-resources.html',
                scope: {
                    resources : '=', // Resources of the Step
                    inherited : '=', // Inherited resources of the step
                    excluded  : '='  // Inherited resources which are not available in the Step
                },
                /*bindToController: true,*/
                link: function (scope, element, attrs, secondaryResourcesCtrl) {
                    scope.$watch('resources', function (newValue) {
                        secondaryResourcesCtrl.resources = newValue;
                    });

                    scope.$watch('inherited', function (newValue) {
                        secondaryResourcesCtrl.inherited = newValue;
                    });

                    scope.$watch('excluded', function (newValue) {
                        secondaryResourcesCtrl.excluded  = newValue;
                    });
                }
            };
        }
    ]);
})();