/**
 * Manages Path form
 */

import template from './../Partial/edit.html'

export default class PathEditDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.controller = 'PathEditCtrl'
    this.controllerAs = 'pathEditCtrl'
    this.template = template
    this.scope = {
      path      : '=', // Data of the path
      modified  : '@', // Is Path have pending modifications ?
      published : '@'  // Is path published ?
    }
    this.bindToController = true
  }
}
