(function () {
    'use strict';

    angular.module('PathModule').directive('pathStructure', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'PathStructureCtrl',
                controllerAs: 'pathStructureCtrl',
                templateUrl: EditorApp.webDir + 'bundles/innovapath/angularjs/Path/Partial/path-structure.html',
                scope: {
                    structure: '='
                },
                link: function (scope, element, attrs, pathStructureCtrl) {
                    pathStructureCtrl.structure = scope.structure;

                    if (pathStructureCtrl.structure && pathStructureCtrl.structure) {
                        // Get the root of the path structure as the current step
                        pathStructureCtrl.currentStep = pathStructureCtrl.structure[0];
                    }

                    // Watch for changes
                    scope.$watch('structure', function (newValue) {
                        pathStructureCtrl.structure = newValue;
                    }, true);
                }
            };
        }
    ]);
})();