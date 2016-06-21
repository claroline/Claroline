import FieldController from '../FieldController'

export default class TextDirective {
  constructor ($parse, $compile) {
    this.$parse = $parse
    this.$compile = $compile
    this.scope = {}
    this.priority = 1001
    this.restrict = 'E'
    this.template = require('./text.html')
    this.replace = true
    this.controller = FieldController
    this.controllerAs = 'tc'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }
}
