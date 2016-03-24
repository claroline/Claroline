angular.module('Correction').directive('correctionShow', [
    function () {
        return {
            restrict: 'E',
            replace: true,
            controller: 'CorrectionCtrl',
            controllerAs: 'correctionCtrl',
            templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Correction/Partials/correction.details.html',
            scope: {
                paper: '=',
                questions: '='
            },
            link: function (scope, element, attr, correctionCtrl) {
                correctionCtrl.init(scope.paper, scope.questions);
            }
        };
    }
]);
