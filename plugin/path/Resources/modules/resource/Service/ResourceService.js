/**
 * Resource Service
 */

import angular from 'angular/index'

export default class ResourceService {
  constructor(IdentifierService) {
    this.IdentifierService = IdentifierService;
  }

  /**
   * Create a new Resource object.
   *
   * @param   {string} [type]
   * @param   {string} [mimeType]
   * @param   {number} [id]
   * @param   {string} [name]
   *
   * @returns {Object}
   */
  newResource(type, mimeType, id, name) {
    return {
      id                  : this.IdentifierService.generateUUID(),
      resourceId          : id ? id : null,
      name                : name ? name : null,
      type                : type ? type : null,
      mimeType            : mimeType ? mimeType : null,
      propagateToChildren : true
    }
  }

  /**
   * Check if a Resource is part of a collection.
   *
   * @param   {array}  resources
   * @param   {object} resource
   *
   * @returns {boolean}
   */
  exists(resources, resource) {
    let resourceExists = false

    if (angular.isObject(resources)) {
      for (let i = 0; i < resources.length; i++) {
        var currentResource = resources[i]
        if (currentResource.resourceId === resource.resourceId) {
          resourceExists = true

          break
        }
      }
    }

    return resourceExists
  }
}
