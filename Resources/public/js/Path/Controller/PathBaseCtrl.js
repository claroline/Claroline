/**
 * Path base controller
 *
 * @returns {PathBaseCtrl}
 * @constructor
 */
var PathBaseCtrl = function PathBaseCtrl($window, $route, $routeParams, PathService) {
    this.window = $window;
    this.pathService = PathService;

    // Store path to make it available by all UI components
    this.pathService.setId(this.id);
    this.pathService.setPath(this.path);

    this.currentStep = $routeParams;

    this.summaryState = this.pathService.getSummaryState();

    //triggering of promise
    this.pathService.userteampromise(this.id);

    // Force reload of the route (as ng-view is deeper in the directive tree, route resolution is deferred and it causes issues)
    $route.reload();

    return this;
};

/**
 * ID of the current path
 * @type {number}
 */
PathBaseCtrl.prototype.id = null;

/**
 * Path to edit
 * @type {object}
 */
PathBaseCtrl.prototype.path = {};

/**
 * Current step ID (used to generate edit and preview routes)
 */
PathBaseCtrl.prototype.currentStep = {};

/**
 * Current state of the summary
 * @type {object}
 */
PathBaseCtrl.prototype.summaryState = {};

/**
 * Allow toggle Summary
 */
PathBaseCtrl.prototype.toggleSummary = function toggleSummary() {
    this.pathService.toggleSummaryState();
};
