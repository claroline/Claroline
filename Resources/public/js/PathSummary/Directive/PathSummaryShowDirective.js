(function () {
    'use strict';

    angular.module('PathSummaryModule').directive('pathSummaryShow', [
        function PathSummaryShowDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: PathSummaryShowCtrl,
                controllerAs: 'pathSummaryShowCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/PathSummary/Partial/show.html',
                scope: {
                    title: '='
                },
                bindToController: true
            };
        }
    ]);
})();