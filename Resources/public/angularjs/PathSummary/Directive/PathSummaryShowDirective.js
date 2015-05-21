(function () {
    'use strict';

    angular.module('PathSummaryModule').directive('pathSummaryShow', [
        function PathSummaryShowDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: PathSummaryShowCtrl,
                controllerAs: 'pathSummaryShowCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/angularjs/PathSummary/Partial/show.html',
                scope: {
                    opened    : '='
                },
                bindToController: true
            };
        }
    ]);
})();