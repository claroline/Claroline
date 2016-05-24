export default class FormDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('./form.html')
    this.replace = true,
    this.controller = () => {},
    this.controllerAs = 'fc'
    this.bindToController = {
      form: '=',
      ngModel: '='
    }
  }
}
