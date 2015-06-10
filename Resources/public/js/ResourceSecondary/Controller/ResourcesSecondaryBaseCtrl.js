/**
 * Resources secondary base controller
 * @returns {ResourcesSecondaryBaseCtrl}
 * @constructor
 */
var ResourcesSecondaryBaseCtrl = function ResourcesSecondaryBaseCtrl() {
    this.resourceIcons  = AngularApp.resourceIcons;

    return this;
};

/**
 * Icons of the Resources
 * @type {object}
 */
ResourcesSecondaryBaseCtrl.prototype.resourceIcons = {};

/**
 * Resources owned by the Step
 * @type {Array}
 */
ResourcesSecondaryBaseCtrl.prototype.resources = [];

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