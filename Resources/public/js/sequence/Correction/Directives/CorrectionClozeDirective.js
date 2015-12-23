
(function () {
    'use strict';

    angular.module('Correction').directive('correctionCloze', [
        function () {
            return {
                restrict: 'E',
                replace: false,
                controller: 'CorrectionClozeCtrl',
                controllerAs: 'correctionClozeCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Correction/Partials/correction.cloze.html',
                scope: {
                    question: '=',
                    paper: '='
                },
                link: function (scope, element, attr, correctionClozeCtrl) {
                    correctionClozeCtrl.init(scope.question, scope.paper);
                }
            };
        }
    ]);
})();


