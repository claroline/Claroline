/**
 * Manages Primary Resources
 */
(function () {
    'use strict';

    angular.module('ResourceSecondaryModule').directive('resourcesSecondaryShow', [
        function ResourcesSecondaryEditDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: ResourcesSecondaryShowCtrl,
                controllerAs: 'resourcesSecondaryShowCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/ResourceSecondary/Partial/show.html',
                scope: {
                    resources : '=', // Resources of the Step
                    inherited : '=', // Inherited resources of the step
                    excluded  : '='  // Inherited resources which are not available in the Step
                },
                bindToController: true
            };
        }
    ]);
})();