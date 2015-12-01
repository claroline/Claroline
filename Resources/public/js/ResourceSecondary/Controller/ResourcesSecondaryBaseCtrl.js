/**
 * Resources secondary base controller
 * @returns {ResourcesSecondaryBaseCtrl}
 * @constructor
 */
var ResourcesSecondaryBaseCtrl = function ResourcesSecondaryBaseCtrl() {
    // Call parent constructor
    ResourcesCtrl.apply(this, arguments);

    this.resourceIcons  = AngularApp.resourceIcons;

    return this;
};

// Extends the base controller
ResourcesSecondaryBaseCtrl.prototype = Object.create(ResourcesCtrl.prototype);
ResourcesSecondaryBaseCtrl.prototype.constructor = ResourcesCtrl;

/**
 * Icons of the Resources
 * @type {object}
 */
ResourcesSecondaryBaseCtrl.prototype.resourceIcons = {};

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
