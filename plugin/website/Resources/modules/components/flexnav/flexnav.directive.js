import uiFlexnavController from './flexnav.controller'
import mainMenuTemplate from './flexnav.partial.html'

export default class uiFlexnavDirective {
  constructor() {
    this.restrict = 'E'
    this.scope = {
      menu: '=',
      options: '=',
      styleOptions: '='
    }
    this.template = mainMenuTemplate
    this.controller = uiFlexnavController
    this.controllerAs = 'vm'
    this.bindToController = true
    this.replace = true
  }
}
