import DateController from './DateController'

export default class DateDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('./date.html')
    this.replace = true,
    this.controller = DateController
    this.controllerAs = 'dc'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }
}
