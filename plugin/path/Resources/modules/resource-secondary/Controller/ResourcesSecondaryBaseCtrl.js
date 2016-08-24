/**
 * Resources secondary base controller
 */

import ResourcesCtrl from './../../resource/Controller/ResourcesCtrl'

export default class ResourcesSecondaryBaseCtrl extends ResourcesCtrl {
  constructor() {
    super()

    /**
     * Icons of the Resources
     * @type {object}
     */
    this.resourceIcons = AngularApp.resourceIcons
  }
}
