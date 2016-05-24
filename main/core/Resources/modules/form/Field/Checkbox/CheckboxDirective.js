import FieldController from '../FieldController'

export default class CheckboxDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('./checkbox.html')
    this.replace = true
    this.controller = FieldController
    this.controllerAs = 'cc'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }
}
