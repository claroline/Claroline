import template from './../Partial/navigation-item.html'

export default class PathNavigationDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.controller = function PathNavigationItemCtrl() {}
    this.controllerAs = 'pathNavigationItemCtrl'
    this.template = template
    this.scope = {
      parent:  '=?',
      element: '=',
      current: '='
    }
    this.bindToController = true
  }
}
