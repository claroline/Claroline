/**
 * Manages Secondary Resources
 */

import template from './../Partial/show.html'

export default class ResourcesSecondaryShowDirective {
  constructor() {
    this.restrict = 'E'
    this.replace = true
    this.controller = 'ResourcesSecondaryShowCtrl'
    this.controllerAs = 'resourcesSecondaryShowCtrl'
    this.template = template
    this.scope = {
      resources : '=', // Resources of the Step
      inherited : '='  // Inherited resources of the step
    }
    this.bindToController = true
  }
}
