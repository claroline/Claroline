import FieldController from '../FieldController'

export default class FileDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('./file.html')
    this.replace = true,
    this.controller = FieldController
    this.controllerAs = 'fc'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }
}
