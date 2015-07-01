/**
 * Class constructor
 * @returns {PathNavigationCtrl}
 * @constructor
 */
var PathNavigationCtrl = function PathNavigationCtrl($routeParams, $scope, PathService) {
    this.pathService = PathService;

    this.current = $routeParams;

    this.summaryState = this.pathService.getSummaryState();

    // Watch the route changes
    $scope.$watch(function watchCurrentRoute() {
        return this.current;
    }.bind(this), this.reloadStep.bind(this), true);

    // Watch the step property
    $scope.$watch(function watchStep() {
        return this.step;
    }.bind(this), this.reloadStep.bind(this), true);

    return this;
};

/**
 * Current matched route
 * @type {object}
 */
PathNavigationCtrl.prototype.current = {};

/**
 * Current displayed step
 * @type {object}
 */
PathNavigationCtrl.prototype.step = {};

/**
 * Parents of the current step
 * @type {object}
 */
PathNavigationCtrl.prototype.parents = {};

/**
 * Current state of the summary
 * @type {object}
 */
PathNavigationCtrl.prototype.summaryState = {};

/**
 * Reload the Step from route params
 */
PathNavigationCtrl.prototype.reloadStep = function reloadStep() {
    this.step = null;

    // Get step
    if (angular.isDefined(this.current) && angular.isDefined(this.current.stepId)) {
        // Retrieve current step
        this.step = this.pathService.getStep(this.current.stepId);
    } else {
        // Get the root
        this.step = this.pathService.getRoot();
    }

    // Get parents of the step
    if (angular.isDefined(this.step) && angular.isObject(this.step)) {
        this.parents = this.pathService.getParents(this.step);
    }
};

/**
 * Allow toggle Summary from the current step
 */
PathNavigationCtrl.prototype.toggleSummary = function toggleSummary() {
    this.pathService.toggleSummaryState();
};