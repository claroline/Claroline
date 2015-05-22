/**
 * Class constructor
 * @returns {ResourcesBaseCtrl}
 * @constructor
 */
var ResourcesBaseCtrl = function ResourcesBaseCtrl() {
    this.resourceIcons  = AngularApp.resourceIcons;

    return this;
};

/**
 * Icons of the Resources
 * @type {object}
 */
ResourcesBaseCtrl.prototype.resourceIcons = {};

/**
 * List of resources
 * @type {Array}
 */
ResourcesBaseCtrl.prototype.resources = [];