/**
 * Resources base controller
 * @returns {ResourcesCtrl}
 * @constructor
 */
var ResourcesCtrl = function ResourcesCtrl() {
    return this;
};

/**
 * List of Resources
 * @type {Array}
 */
ResourcesCtrl.prototype.resources = [];

/**
 * Display resource in new window tab
 * @param resource
 */
ResourcesCtrl.prototype.showResource = function showResource(resource) {
    // Retrieve resource type
    var resourceRoute = Routing.generate('claro_resource_open', {
        node: resource.resourceId,
        resourceType: resource.type
    });

    window.open(resourceRoute, '_blank');
};