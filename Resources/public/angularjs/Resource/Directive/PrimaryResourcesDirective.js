/**
 * Manages Primary Resources
 */
(function () {
    'use strict';

    angular.module('ResourceModule').directive('primaryResources', [
        function PrimaryResourcesDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: 'PrimaryResourcesCtrl',
                controllerAs: 'primaryResourcesCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/angularjs/Resource/Partial/primary-resources.html',
                scope: {
                    resources : '=' // Resources of the Step
                },
                link: function (scope, element, attrs, primaryResourcesCtrl) {
                    scope.$watch('resources', function (newValue) {
                        primaryResourcesCtrl.resources = newValue;
                    }, true);
                }
            };
        }
    ]);
})();