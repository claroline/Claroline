/**
 * Resources primary base controller
 * @returns {ResourcesPrimaryBaseCtrl}
 * @constructor
 */
var ResourcesPrimaryBaseCtrl = function ResourcesPrimaryBaseCtrl() {
    // Call parent constructor
    ResourcesCtrl.apply(this, arguments);

    this.resourceIcons  = AngularApp.resourceIcons;

    return this;
};

// Extends the base controller
ResourcesPrimaryBaseCtrl.prototype = Object.create(ResourcesCtrl.prototype);
ResourcesPrimaryBaseCtrl.prototype.constructor = ResourcesCtrl;

/**
 * Icons of the Resources
 * @type {object}
 */
ResourcesPrimaryBaseCtrl.prototype.resourceIcons = {};
