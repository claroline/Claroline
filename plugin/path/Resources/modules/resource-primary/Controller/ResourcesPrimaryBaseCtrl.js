/**
 * Resources primary base controller
 */

import ResourcesCtrl from './../../resource/Controller/ResourcesCtrl'

export default class ResourcesPrimaryBaseCtrl extends ResourcesCtrl {
  constructor() {
    super()

    /**
     * Icons of the platform Resources
     * @type {object}
     */
    this.resourceIcons  = AngularApp.resourceIcons;
  }
}
