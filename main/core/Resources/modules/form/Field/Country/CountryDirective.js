import CountryController from './CountryController'

export default class CountryDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('./country.html')
    this.replace = true,
    this.controller = CountryController
    this.controllerAs = 'coc'
    this.bindToController = {
      field: '=',
      ngModel: '='
    }
  }
}
