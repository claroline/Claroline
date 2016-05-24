import CheckboxesController from './CheckboxesController'

export default class CheckboxesDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('./checkboxes.html')
    this.replace = true,
    this.controller = CheckboxesController
    this.controllerAs = 'chc'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }
}
