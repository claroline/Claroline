/**
 * Class constructor
 * @returns {StepConditionsShowCtrl}
 * @constructor
 */
var StepConditionsShowCtrl = function StepConditionsShowCtrl($routeParams, PathService, UserProgressionService) {
    StepConditionsBaseCtrl.apply(this, arguments);

    // Get Progression of the current User
    this.userProgressionService = UserProgressionService;
    this.userProgression = this.userProgressionService.get();

    return this;
};

// Extends the base controller
StepConditionsShowCtrl.prototype = Object.create(StepConditionsBaseCtrl.prototype);
StepConditionsShowCtrl.prototype.constructor = StepConditionsShowCtrl;

