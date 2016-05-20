import CheckboxController from './CheckboxController'

export default class CheckboxDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('./checkbox.html')
    this.replace = true
    this.controller = CheckboxController
    this.controllerAs = 'cc'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }
}
