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
      position        : '@',
      step            : '=',
      items           : '=',
      currentTry      : '=',
      solutionShown   : '=',
      allAnswersFound : '=?'
    }
  }
}

export default StepShowDirective
