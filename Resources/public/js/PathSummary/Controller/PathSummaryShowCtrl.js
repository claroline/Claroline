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

    return this;
};

// Extends the base controller
PathSummaryShowCtrl.prototype = PathSummaryBaseCtrl.prototype;
PathSummaryShowCtrl.prototype.constructor = PathSummaryShowCtrl;

/**
 * Progression of the current User into the Path
 * @type {object}
 */
PathSummaryShowCtrl.prototype.userProgression = {};