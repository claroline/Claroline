/**
 * Manages Primary Resources
 */
(function () {
    'use strict';

    angular.module('ResourcePrimaryModule').directive('resourcesPrimaryEdit', [
        function ResourcesPrimaryEditDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: ResourcesPrimaryEditCtrl,
                controllerAs: 'resourcesPrimaryEditCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/ResourcePrimary/Partial/edit.html',
                scope: {
                    resources : '=' // Resources of the Step
                },
                bindToController: true
            };
        }
    ]);
})();