/**
 * Resources primary show controller
 */

import angular from 'angular/index'

import ResourcesPrimaryBaseCtrl from './ResourcesPrimaryBaseCtrl'

export default class ResourcesPrimaryShowCtrl extends ResourcesPrimaryBaseCtrl {
  constructor(url, ResourceService) {
    super(url, ResourceService)

    // Get resource URL to populate IFrame
    if (angular.isObject(this.resources) && angular.isObject(this.resources[0])) {
      const resource = this.resources[0]

      let resourceUrl = null
      if (resource.url) {
        resourceUrl = resource.url
      } else {
        resourceUrl = this.UrlGenerator('claro_resource_open', {
          node         : this.resources[0].id,
          resourceType : this.resources[0].type
        })
      }

      this.resourceUrl = {
        url: resourceUrl
      }
    }
  }
}
