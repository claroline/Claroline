angular.module('Correction').directive('correctionShort', [
    function () {
        return {
            restrict: 'E',
            replace: false,
            controller: 'CorrectionShortCtrl',
            controllerAs: 'correctionShortCtrl',
            templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Correction/Partials/correction.short.html',
            scope: {
                question: '=',
                paper: '='
            },
            link: function (scope, element, attr, correctionShortCtrl) {
                correctionShortCtrl.init(scope.question, scope.paper);
            }
        };
    }
]);


