import template from './../Partial/navigation.html'

export default class PathNavigationDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.controller = 'PathNavigationCtrl'
    this.controllerAs = 'pathNavigationCtrl'
    this.template = template
    this.scope = {}
    this.bindToController = true
  }
}
