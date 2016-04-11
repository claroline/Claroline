angular.module('Correction').directive('correctionGraphic', [
    '$timeout',
    function ($timeout) {
        return {
            restrict: 'E',
            replace: false,
            controller: 'CorrectionGraphicCtrl',
            controllerAs: 'correctionGraphicCtrl',
            templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Correction/Partials/correction.graphic.html',
            scope: {
                question: '=',
                paper: '='
            },
            link: function (scope, element, attr, correctionGraphicCtrl) {
                correctionGraphicCtrl.init(scope.question, scope.paper);

                $timeout(function(){
                    correctionGraphicCtrl.createElements();
                });
            }
        };
    }
]);
