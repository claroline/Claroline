import EmailController from './EmailController'

export default class EmailDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('./email.html')
    this.replace = true,
    this.controller = EmailController
    this.controllerAs = 'ec'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }
}
