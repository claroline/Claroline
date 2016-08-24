/**
 * Manages edition of Secondary Resources
 */

import template from './../Partial/edit.html'

export default class ResourcesPrimaryEditDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.controller = 'ResourcesSecondaryEditCtrl'
    this.controllerAs = 'resourcesSecondaryEditCtrl'
    this.template = template
    this.scope = {
      resources : '=', // Resources of the Step
      inherited : '=', // Inherited resources of the step
      excluded  : '='  // Inherited resources which are not available in the Step
    }
    this.bindToController = true
  }
}
