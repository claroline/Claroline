/**
 * Class constructor
 * @returns {StepBaseCtrl}
 * @constructor
 */
var StepBaseCtrl = function StepBaseCtrl(step, inheritedResources, PathService) {
    this.pathService        = PathService;

    this.step               = step;
    this.inheritedResources = inheritedResources;

    // Get Next and Previous steps
    this.previous           = this.pathService.getPrevious(step);
    this.next               = this.pathService.getNext(step);

    return this;
};

/**
 * Current step
 * @type {object}
 */
StepBaseCtrl.prototype.step = null;

/**
 * Previous step
 * @type {object}
 */
StepBaseCtrl.prototype.previous = null;

/**
 * Next step
 * @type {object}
 */
StepBaseCtrl.prototype.next = null;

/**
 * Inherited resources from parents of the Step
 * @type {array}
 */
StepBaseCtrl.prototype.inheritedResources = [];

/**
 * Wrapped for the goTo method (used to jump to next or previous step)
 * @param step
 */
StepBaseCtrl.prototype.goTo = function goTo(step) {
    this.pathService.goTo(step);
};

/**
 * Allow toggle Summary from the current step
 */
StepBaseCtrl.prototype.toggleSummary = function () {
    this.pathService.toggleSummaryState();
};
