/**
 * Resources secondary controller
 * @returns {ResourcesSecondaryShowCtrl}
 * @constructor
 */
var ResourcesSecondaryShowCtrl = function ResourcesSecondaryShowCtrl() {
    // Call parent constructor
    ResourcesSecondaryBaseCtrl.apply(this, arguments);

    return this;
};

// Extends the base controller
ResourcesSecondaryShowCtrl.prototype = Object.create(ResourcesSecondaryBaseCtrl.prototype);
ResourcesSecondaryShowCtrl.prototype.constructor = ResourcesSecondaryShowCtrl;