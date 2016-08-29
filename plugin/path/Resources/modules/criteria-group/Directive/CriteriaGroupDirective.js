
import template from './../Partial/criteria-group.html'

export default class CriteriaGroupDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.template = template
    this.controller = 'CriteriaGroupCtrl'
    this.controllerAs = 'criteriaGroupCtrl'
    this.bindToController = true
    this.scope = {
      step: '=',
      criteriaGroup: '='
    }
  }
}
