
import template from './../Partials/hint.html'

/**
 * Display an hint.
 */
export default class HintDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.template = template
    this.controller = function() {}
    this.controllerAs = 'hintCtrl'
    this.bindToController = true
    this.scope = {
      hint: '=',
      used: '=',
      enabled: '='
    }
  }
}
