/**
 * Manages Primary Resources
 */
(function () {
    'use strict';

    angular.module('ResourcePrimaryModule').directive('resourcesPrimaryShow', [
        function ResourcesPrimaryEditDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: ResourcesPrimaryShowCtrl,
                controllerAs: 'resourcesPrimaryShowCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/ResourcePrimary/Partial/show.html',
                scope: {
                    resources : '=' // Resources of the Step
                },
                bindToController: true
            };
        }
    ]);
})();