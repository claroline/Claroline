/**
 * Created by panos on 5/30/16.
 */
import portalSearchTemplate from './portal-search.partial.html'
import portalSearchController from './portal-search.controller'

export default class PortalSearchDirective {
  constructor() {
    this.restrict = 'EA'
    this.scope = {
      options: '='
    }
    this.template = portalSearchTemplate
    this.controller  = portalSearchController
    this.controllerAs = 'vm'
    this.bindToController = true
  }
}