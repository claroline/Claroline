/**
 * Manages Primary Resources
 */

import template from './../Partial/edit.html'

export default class ResourcesPrimaryEditDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.controller = 'ResourcesPrimaryEditCtrl'
    this.controllerAs = 'resourcesPrimaryEditCtrl'
    this.template = template
    this.scope = {
      resources : '=' // Resources of the Step
    }
    this.bindToController = true
  }
}
