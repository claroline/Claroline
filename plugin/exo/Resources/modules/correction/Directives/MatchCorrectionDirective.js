function MatchCorrectionDirective() {
    return {
        restrict: 'E',
        replace: true,
        controller: 'MatchCorrectionCtrl',
        controllerAs: 'matchCorrectionCtrl',
        bindToController: true,
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Correction/Partials/match.html',
        scope: {
            question: '='
        },
        link: function (scope, element, attr, ctrl) {
            ctrl.init(scope.question, scope.paper);
        }
    };
};
