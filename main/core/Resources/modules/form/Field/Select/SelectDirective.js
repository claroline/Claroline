import SelectController from './SelectController'

export default class SelectDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('./select.html')
    this.replace = true,
    this.controller = SelectController
    this.controllerAs = 'sc'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }
}
