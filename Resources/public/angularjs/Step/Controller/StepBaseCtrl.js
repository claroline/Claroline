/**
 * Class constructor
 * @returns {StepBaseCtrl}
 * @constructor
 */
var StepBaseCtrl = function StepBaseCtrl(step, inheritedResources) {
    this.step = step;
    this.inheritedResources = inheritedResources;

    return this;
};

/**
 * Current step
 * @type {object}
 */
StepBaseCtrl.prototype.step = {};

/**
 * Inherited resources from parents of the Step
 * @type {array}
 */
StepBaseCtrl.prototype.inheritedResources = [];
