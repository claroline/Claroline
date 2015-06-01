(function () {
    'use strict';

    angular.module('PathSummaryModule').directive('pathSummaryEdit', [
        function PathSummaryEditDirective() {
            return {
                restrict: 'E',
                replace: true,
                controller: PathSummaryEditCtrl,
                controllerAs: 'pathSummaryEditCtrl',
                templateUrl: AngularApp.webDir + 'bundles/innovapath/js/PathSummary/Partial/edit.html',
                scope: {
                    title: '='
                },
                bindToController: true
            };
        }
    ]);
})();