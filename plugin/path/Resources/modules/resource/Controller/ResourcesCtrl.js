/**
 * Base controller for resource management
 */
export default class ResourcesCtrl {
  /**
   * Display resource in new window tab
   * @param resource
   */
  showResource(resource) {
    // Retrieve resource type
    const resourceRoute = Routing.generate('claro_resource_open', {
      node: resource.resourceId,
      resourceType: resource.type
    })

    window.open(resourceRoute, '_blank')
  }
}
