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
      if (!angular.isDefined(resource.url) || 0 === resource.url.length) {
        resource.url = this.UrlGenerator('claro_resource_open', {
          node         : this.resources[0].id,
          resourceType : this.resources[0].type
        }) + '?iframe=1' 
      }
    }
  }
}
