/**
 * Resources primary controller
 * @returns {ResourcesPrimaryShowCtrl}
 * @constructor
 */
var ResourcesPrimaryShowCtrl = function ResourcesPrimaryShowCtrl() {
    // Call parent constructor
    ResourcesBaseCtrl.apply(this, arguments);

    // Get resource URL to populate IFrame

    return this;
};

// Extends the base controller
ResourcesPrimaryShowCtrl.prototype = ResourcesBaseCtrl.prototype;
ResourcesPrimaryShowCtrl.prototype.constructor = ResourcesPrimaryShowCtrl;