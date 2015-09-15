
(function () {
    'use strict';

    angular.module('Correction').directive('correctionShow', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                controller: 'CorrectionCtrl',
                controllerAs: 'correctionCtrl',
                templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Correction/Partials/correction.show.html',
                scope: {
                    sequence: '=',
                    answers: '='
                },
                link: function (scope, element, attr, correctionCtrl) {
                    console.log('correctionShow directive link method called');
                    correctionCtrl.init(scope.sequence, scope.answers);
                }
            };
        }
    ]);
})();


