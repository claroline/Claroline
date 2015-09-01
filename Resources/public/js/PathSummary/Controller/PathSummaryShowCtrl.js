/**
 * Class constructor
 * @returns {PathSummaryShowCtrl}
 * @constructor
 */
var PathSummaryShowCtrl = function PathSummaryShowCtrl($routeParams, PathService, UserProgressionService) {
    PathSummaryBaseCtrl.apply(this, arguments);

    // Get Progression of the current User
    this.userProgressionService = UserProgressionService;
    this.userProgression = this.userProgressionService.get();

    // Check if summary is displayed by default or not
    var path = this.pathService.getPath();
    if (angular.isObject(path)) {
        if (!path.summaryDisplayed) {
            this.pathService.setSummaryState(false);
        } else {
            this.pathService.setSummaryState(true);
        }
    }

    return this;
};

// Extends the base controller
PathSummaryShowCtrl.prototype = Object.create(PathSummaryBaseCtrl.prototype);
PathSummaryShowCtrl.prototype.constructor = PathSummaryShowCtrl;

/**
 * Progression of the current User into the Path
 * @type {object}
 */
PathSummaryShowCtrl.prototype.userProgression = {};

PathSummaryShowCtrl.prototype.updateProgression = function (step, newStatus) {
    if (!angular.isObject(this.userProgression[step.id])) {
        this.userProgressionService.create(step, newStatus);
    } else {
        this.userProgressionService.update(step, newStatus);
    }
};

PathSummaryShowCtrl.prototype.goTo = function goTo(step) {
    console.log('coucou je suis PathSummaryShow');
    this.pathService.goTo(step);
};