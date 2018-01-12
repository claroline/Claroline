import resourceIconSetListTemplate from './resource-icon-set-list.partial.html'
import resourceIconSetListController from './resource-icon-set-list.controller'

export default class ResourceIconSetListDirective {
  constructor() {
    this.restrict = 'E'
    this.scope = {}
    this.template = resourceIconSetListTemplate
    this.controller  = resourceIconSetListController
    this.controllerAs = 'vm'
    this.bindToController = true
  }
}