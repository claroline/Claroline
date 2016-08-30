import template from '../Partial/edit.html'

export default class ConditionEditDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.controller = 'ConditionEditCtrl'
    this.controllerAs = 'conditionEditCtrl'
    this.template = template
    this.scope = {
      step: '=',
      next: '='
    }
    this.bindToController = true
  }
}
