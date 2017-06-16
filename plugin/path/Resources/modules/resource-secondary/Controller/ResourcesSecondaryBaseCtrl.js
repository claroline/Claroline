/**
 * Resources secondary base controller
 */

import ResourcesCtrl from './../../resource/Controller/ResourcesCtrl'

export default class ResourcesSecondaryBaseCtrl extends ResourcesCtrl {
  constructor(url, ResourceService) {
    super(url, ResourceService)
  }
}
