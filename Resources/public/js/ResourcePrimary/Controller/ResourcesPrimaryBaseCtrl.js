/**
 * Resources primary base controller
 * @returns {ResourcesSecondaryBaseCtrl}
 * @constructor
 */
var ResourcesPrimaryBaseCtrl = function ResourcesPrimaryBaseCtrl() {
    this.resourceIcons  = AngularApp.resourceIcons;

    return this;
};

/**
 * Icons of the Resources
 * @type {object}
 */
ResourcesPrimaryBaseCtrl.prototype.resourceIcons = {};

/**
 * Resources owned by the Step
 * @type {Array}
 */
ResourcesPrimaryBaseCtrl.prototype.resources = [];