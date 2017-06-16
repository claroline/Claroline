/**
 * Resource Service
 */

import angular from 'angular/index'

export default class ResourceService {
  constructor($q, $http, url, IdentifierService) {
    this.$q = $q
    this.$http = $http
    this.UrlGenerator = url
    this.IdentifierService = IdentifierService

    this.resourceIcons = null
    this.resourceIconsPromise = null
  }

  getResourceIcons() {
    if (!this.resourceIconsPromise) { // Avoid duplicate call if the first one is not finished
      const deferred = this.$q.defer()

      if (null !== this.resourceIcons) {
        deferred.resolve(this.resourceIcons)
      } else {
        this.$http
          .get(this.UrlGenerator('claro_resource_icon_list'))
          .success((response) => {
            this.resourceIcons = response
            deferred.resolve(response)
            delete this.resourceIconsPromise
          })
          .error((response) => {
            deferred.reject(response)
            delete this.resourceIconsPromise
          })

        this.resourceIconsPromise = deferred.promise
      }

      return deferred.promise
    }

    return this.resourceIconsPromise
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
