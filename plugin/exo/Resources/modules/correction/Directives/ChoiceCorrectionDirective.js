export default function ChoiceCorrectionDirective() {
    return {
        restrict: 'E',
        replace: true,
        controller: 'ChoiceCorrectionCtrl',
        controllerAs: 'choiceCorrectionCtrl',
        bindToController: true,
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Correction/Partials/choice.html',
        scope: {
            question: '='
        }
    };
}
