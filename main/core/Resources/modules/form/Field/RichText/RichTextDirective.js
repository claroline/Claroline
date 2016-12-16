import FieldController from '../FieldController'
import template from './rich_text.html'

export default class RichTextDirective {
  constructor($parse, $compile) {
    this.$parse = $parse
    this.$compile = $compile
    this.scope = {}
    this.priority = 1001
    this.restrict = 'E'
    this.template = template
    this.replace = true
    this.controller = FieldController
    this.controllerAs = 'rtc'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }
}
