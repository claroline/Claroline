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
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Step/Partials/show.html',
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
