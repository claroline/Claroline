import NumberController from './NumberController'

export default class NumberDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('./number.html')
    this.replace = true,
    this.controller = NumberController
    this.controllerAs = 'nc'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }
}
