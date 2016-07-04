import show from './../Partials/show.html'

/**
 * Step Show Directive
 * @constructor
 */
function StepShowDirective() {
    return {
        restrict: 'E',
        replace: true,
        controller: 'StepShowCtrl',
        controllerAs: 'stepShowCtrl',
        bindToController: true,
        template: show,
        scope: {
            step            : '=',
            stepIndex       : '@',
            currentTry      : '=',
            solutionShown   : '=',
            allAnswersFound : '='
        }
    };
}

export default StepShowDirective
