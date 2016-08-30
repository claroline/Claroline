/**
 * Base controller for resource management
 */
export default class ResourcesCtrl {
  constructor(url, ResourceService) {
    this.UrlGenerator = url
    this.ResourceService = ResourceService

    /**
     * Icons of the platform Resources
     * @type {object}
     */
    this.resourceIcons = []
    this.ResourceService.getResourceIcons().then(data => this.resourceIcons = data)
  }

  /**
   * Display resource in new window tab
   * @param resource
   */
  showResource(resource) {
    // Retrieve resource type
    let route = null
    if (resource.url) {
      route = resource.url
    } else {
      route = this.UrlGenerator('claro_resource_open', {
        node: resource.resourceId,
        resourceType: resource.type
      })
    }

    window.open(route, '_blank')
  }
}
