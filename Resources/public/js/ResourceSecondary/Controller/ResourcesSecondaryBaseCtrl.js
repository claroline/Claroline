/**
 * Resources secondary base controller
 * @returns {ResourcesSecondaryBaseCtrl}
 * @constructor
 */
var ResourcesSecondaryBaseCtrl = function ResourcesSecondaryBaseCtrl() {
    // Call parent constructor
    ResourcesBaseCtrl.apply(this, arguments);

    return this;
};

// Extends the base controller
ResourcesSecondaryBaseCtrl.prototype = ResourcesBaseCtrl.prototype;
ResourcesSecondaryBaseCtrl.prototype.constructor = ResourcesSecondaryBaseCtrl;

/**
 * Resources inherited from the parents
 * @type {Array}
 */
ResourcesSecondaryBaseCtrl.prototype.inherited = [];

/**
 * Resources inherited which should not be available in the Step
 * @type {Array}
 */
ResourcesSecondaryBaseCtrl.prototype.excluded = [];

/**
 * Display resource in new window tab
 * @param resource
 */
ResourcesSecondaryBaseCtrl.prototype.showResource = function showResource(resource) {
    // Retrieve resource type
    var resourceRoute = Routing.generate('claro_resource_open', {
        node: resource.resourceId,
        resourceType: resource.type
    });

    window.open(resourceRoute, '_blank');
};