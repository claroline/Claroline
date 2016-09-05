
import template from './../Partials/hints.html'

/**
 * Displays hints for a question.
 */
export default class HintsDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.template = template
    this.controller = 'HintsCtrl'
    this.controllerAs = 'hintsCtrl'
    this.bindToController = true
    this.scope = {
      hints: '=',
      usedHints: '=',
      enabled: '='
    }
  }
}
