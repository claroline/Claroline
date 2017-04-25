import CascadeSelectController from './CascadeSelectController'
import cascadeSelectTemplate from './cascade_select.html'

export default class CascadeSelectDirective {
  constructor() {
    this.scope = {}
    this.restrict = 'E'
    this.template = cascadeSelectTemplate
    this.replace = true,
    this.controller = CascadeSelectController
    this.controllerAs = 'sc'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }
}
