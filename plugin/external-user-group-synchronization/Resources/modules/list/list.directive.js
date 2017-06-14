import externalSourceListTemplate from './list.partial.html'
import externalSourceListController from './list.controller'

export default class ExternalSourceListDirective {
  constructor() {
    this.restrict = 'E'
    this.scope = {}
    this.template = externalSourceListTemplate
    this.controller  = externalSourceListController
    this.controllerAs = 'vm'
    this.bindToController = true
  }
}