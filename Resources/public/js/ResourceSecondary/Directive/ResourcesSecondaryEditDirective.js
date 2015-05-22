/**
 * Manages Secondary Resources
 */
(function () {
    'use strict';

    angular.module('ResourceSecondaryModule').directive('resourcesSecondaryEdit', [
        function ResourcesPrimaryEditDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: ResourcesSecondaryEditCtrl,
                controllerAs: 'resourcesSecondaryEditCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/ResourceSecondary/Partial/edit.html',
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