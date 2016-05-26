import FieldController from '../FieldController'

export default class RadioDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('./radio.html')
    this.replace = true,
    this.controller = FieldController
    this.controllerAs = 'rc'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }
}
