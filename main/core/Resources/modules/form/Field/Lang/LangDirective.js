import LangController from './LangController'

export default class LangDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('./lang.html')
    this.replace = true
    this.controller = LangController
    this.controllerAs = 'lc'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }
}
