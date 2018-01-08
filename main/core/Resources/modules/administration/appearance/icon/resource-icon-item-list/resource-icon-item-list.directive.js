import resourceIconItemListTemplate from './resource-icon-item-list.partial.html'
import resourceIconItemListController from './resource-icon-item-list.controller'

export default class ResourceIconItemListDirective {
  constructor() {
    this.restrict = 'E'
    this.scope = {}
    this.template = resourceIconItemListTemplate
    this.controller  = resourceIconItemListController
    this.controllerAs = 'vm'
    this.bindToController = true
  }
}