(function () {
    'use strict';

    angular.module('PathModule').directive('pathSummary', [
        function PathSummaryDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: 'PathSummaryCtrl',
                controllerAs: 'pathSummaryCtrl',
                templateUrl: EditorApp.webDir + 'bundles/innovapath/angularjs/Path/Partial/path-summary.html',
                scope: {
                    opened    : '=',
                    structure : '='
                },
                link: function (scope, element, attrs, pathSummaryCtrl) {
                    pathSummaryCtrl.structure = scope.structure;
                    pathSummaryCtrl.opened    = scope.opened;

                    // Watch for changes
                    scope.$watch('structure', function (newValue) {
                        pathSummaryCtrl.structure = newValue;
                    }, true);

                    scope.$watch('opened', function (newValue) {
                        pathSummaryCtrl.opened = newValue;
                    }, true);
                }
            };
        }
    ]);
})();