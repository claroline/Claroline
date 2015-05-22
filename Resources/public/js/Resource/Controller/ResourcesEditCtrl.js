/**
 * Resources edit base controller
 * @param $scope
 * @param ConfirmService
 * @param ResourceService
 * @returns {ResourcesEditCtrl}
 * @constructor
 */
var ResourcesEditCtrl = function ResourcesEditCtrl($scope, ConfirmService, ResourceService) {
    // Call parent constructor
    ResourcesBaseCtrl.apply(this, arguments);

    this.scope           = $scope;
    this.confirmService  = ConfirmService;
    this.resourceService = ResourceService;

    return this;
};

// Extends the base controller
ResourcesEditCtrl.prototype = ResourcesBaseCtrl.prototype;
ResourcesEditCtrl.prototype.constructor = ResourcesEditCtrl;

/**
 * Configuration for the Claroline Resource Picker
 * @type {object}
 */
ResourcesEditCtrl.prototype.resourcePicker = {};