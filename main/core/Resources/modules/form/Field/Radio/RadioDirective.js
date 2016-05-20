import RadioController from './RadioController'

export default class RadioDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('./radio.html')
    this.replace = true,
    this.controller = RadioController
    this.controllerAs = 'rc'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }
}
