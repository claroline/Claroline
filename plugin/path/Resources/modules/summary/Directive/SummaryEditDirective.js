import template from './../Partial/edit.html'

export default class SummaryEditDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.controller = 'SummaryEditCtrl'
    this.controllerAs = 'summaryEditCtrl'
    this.template = template
    this.scope = {}
    this.bindToController = true
  }
}
