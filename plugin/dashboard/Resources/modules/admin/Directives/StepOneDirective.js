import stepOne from './../Partials/stepOne.html'

export default function StepOneDirective() {
  return {
    restrict: 'E',
    replace: true,
    controller: 'StepOneCtrl',
    controllerAs: 'stepOneCtrl',
    template: stepOne,
    scope: {
      workspaces: '=',
      dashboard: '=',
      user: '='
    },
    bindToController: true
  }
}
