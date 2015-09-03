/**
 * Class constructor
 * @returns {StepShowCtrl}
 * @constructor
 */
var StepShowCtrl = function StepShowCtrl(step, inheritedResources, PathService, $sce, UserProgressionService) {
    StepBaseCtrl.apply(this, arguments);

    this.userProgressionService = UserProgressionService;

    if (angular.isDefined(this.step) && angular.isDefined(this.step.description) && typeof this.step.description == 'string') {
        // Trust content to allow Cross Sites URL
        this.step.description = $sce.trustAsHtml(this.step.description);
    }

    // Update User progression if needed (e.g. if the User has never seen the Step, mark it as seen)
    this.progression = this.userProgressionService.getForStep(this.step);
    if (!angular.isObject(this.progression)) {
        // Create progression for User
        this.progression = this.userProgressionService.create(step);
    }

    return this;
};

// Extends the base controller
StepShowCtrl.prototype = Object.create(StepBaseCtrl.prototype);
StepShowCtrl.prototype.constructor = StepShowCtrl;

/**
 * Service that manages the User Progression in the Path
 * @type {{}}
 */
StepShowCtrl.prototype.userProgressionService = {};

/**
 * Progression of the User for the current Step (NOT the progression for the whole Path)
 * @type {null}
 */
StepShowCtrl.prototype.progression = {};

StepShowCtrl.prototype.updateProgression = function (newStatus) {
    this.userProgressionService.update(this.step, newStatus);
};