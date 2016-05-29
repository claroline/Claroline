export default class FieldDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('./field.html')
    this.replace = true,
    this.controller = () => {},
    this.controllerAs = 'fic'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }
}
