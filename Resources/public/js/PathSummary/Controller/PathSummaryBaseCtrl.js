/**
 * Class constructor
 * @returns {PathSummaryBaseCtrl}
 * @constructor
 */
var PathSummaryBaseCtrl = function PathSummaryBaseCtrl($routeParams, PathService) {
    this.webDir = AngularApp.webDir;

    this.pathService = PathService;
    this.current = $routeParams;

    this.state = this.pathService.getSummaryState();

    var path = this.pathService.getPath();
    if (angular.isObject(path)) {
        // Set the structure of the path
        this.structure = path.steps;
    }
    return this;
};

/**
 * Path to the symfony web directory (where are stored our partials)
 * @type {null}
 */
PathSummaryBaseCtrl.prototype.webDir = null;

/**
 * Sate of the summary
 * @type {boolean}
 */
PathSummaryBaseCtrl.prototype.state = false;

/**
 * Title to display on the summary
 * @type {null}
 */
PathSummaryBaseCtrl.prototype.title = null;

/**
 * Structure of the current path
 * @type {object}
 */
PathSummaryBaseCtrl.prototype.structure = {};

/**
 * Current displayed Step
 * @type {object}
 */
PathSummaryBaseCtrl.prototype.current = {};

/**
 * Close Summary
 */
PathSummaryBaseCtrl.prototype.close = function close() {
    this.pathService.toggleSummaryState();
};

/**
 * Go to a specific Step
 * @param step
 */

PathSummaryBaseCtrl.prototype.goTo = function goTo(step) {
    this.pathService.goTo(step);
};