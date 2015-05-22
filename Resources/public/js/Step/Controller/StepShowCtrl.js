/**
 * Class constructor
 * @returns {StepShowCtrl}
 * @constructor
 */
var StepShowCtrl = function StepShowCtrl(step, inheritedResources, PathService) {
    StepBaseCtrl.apply(this, arguments);

    return this;
};

// Extends the base controller
StepShowCtrl.prototype = StepBaseCtrl.prototype;
StepShowCtrl.prototype.constructor = StepShowCtrl;