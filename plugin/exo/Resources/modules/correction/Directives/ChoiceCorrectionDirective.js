import choice from './../Partials/choice.html'

export default function ChoiceCorrectionDirective() {
    return {
        restrict: 'E',
        replace: true,
        controller: 'ChoiceCorrectionCtrl',
        controllerAs: 'choiceCorrectionCtrl',
        bindToController: true,
        template: choice,
        scope: {
            question: '='
        }
    };
}
