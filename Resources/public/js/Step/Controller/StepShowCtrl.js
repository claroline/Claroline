/**
 * Class constructor
 * @returns {StepShowCtrl}
 * @constructor
 */
var StepShowCtrl = function StepShowCtrl(step, inheritedResources, PathService, $sce) {
    StepBaseCtrl.apply(this, arguments);

    if (angular.isDefined(this.step) && angular.isDefined(this.step.description) && typeof this.step.description == 'string') {
        // Trust content to allow Cross Sites URL
        this.step.description = $sce.trustAsHtml(this.step.description);
    }

    return this;
};

// Extends the base controller
StepShowCtrl.prototype = StepBaseCtrl.prototype;
StepShowCtrl.prototype.constructor = StepShowCtrl;