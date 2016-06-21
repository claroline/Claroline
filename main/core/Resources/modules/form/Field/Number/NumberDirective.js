import FieldController from '../FieldController'

export default class NumberDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('./number.html')
    this.replace = true,
    this.controller = FieldController
    this.controllerAs = 'nc'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }
}
