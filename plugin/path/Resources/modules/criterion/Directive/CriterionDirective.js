
import template from './../Partial/criterion.html'

export default class CriterionDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.template = template
    this.controller = 'CriterionCtrl'
    this.controllerAs = 'criterionCtrl'
    this.bindToController = true
    this.scope = {
      step: '=',
      criterion: '='
    }
  }
}
