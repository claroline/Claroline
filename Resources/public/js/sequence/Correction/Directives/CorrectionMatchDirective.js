
(function () {
    'use strict';

    angular.module('Correction').directive('correctionMatch', [
        function () {
            return {
                restrict: 'E',
                replace: false,
                controller: 'CorrectionMatchCtrl',
                controllerAs: 'correctionMatchCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Correction/Partials/correction.match.html',
                scope: {
                    question: '=',
                    paper: '='
                },
                link: function (scope, element, attr, correctionMatchCtrl) {
                    correctionMatchCtrl.init(scope.question, scope.paper);
                }
            };
        }
    ]);
})();


