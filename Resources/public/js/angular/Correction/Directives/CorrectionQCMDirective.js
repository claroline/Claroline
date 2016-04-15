angular.module('Correction').directive('correctionQcm', [
    function () {
        return {
            restrict: 'E',
            replace: false,
            controller: 'CorrectionQCMCtrl',
            controllerAs: 'correctionQCMCtrl',
            templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Correction/Partials/correction.qcm.html',
            scope: {
                question: '=',
                paper: '='
            },
            link: function (scope, element, attr, correctionQCMCtrl) {
                correctionQCMCtrl.init(scope.question, scope.paper);
            }
        };
    }
]);


