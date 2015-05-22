/**
 * Resources primary controller
 * @returns {ResourcesPrimaryShowCtrl}
 * @constructor
 */
var ResourcesPrimaryShowCtrl = function ResourcesPrimaryShowCtrl() {
    // Call parent constructor
    ResourcesPrimaryBaseCtrl.apply(this, arguments);

    // Get resource URL to populate IFrame

    return this;
};

// Extends the base controller
ResourcesPrimaryShowCtrl.prototype = ResourcesPrimaryBaseCtrl.prototype;
ResourcesPrimaryShowCtrl.prototype.constructor = ResourcesPrimaryShowCtrl;