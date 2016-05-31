import FacetController from '../Controller/FacetController'

export default class FacetManagementDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('../Partial/facets.html')
    this.replace = false
    this.controller = FacetController
    this.controllerAs = 'fc'
  }
}
