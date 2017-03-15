import template from './../Partial/show.html'

export default class SummaryShowDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.controller = 'SummaryShowCtrl'
    this.controllerAs = 'summaryShowCtrl'
    this.template = template
    this.scope = {}
    this.bindToController = true
  }
}
