/**
 * Class constructor
 * @returns {PathNavigationBaseCtrl}
 * @constructor
 */
var PathNavigationBaseCtrl = function PathNavigationBaseCtrl($routeParams, $scope, PathService) {
    this.pathService = PathService;

    this.current = $routeParams;

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
PathNavigationBaseCtrl.prototype.current = {};

/**
 * Current displayed step
 * @type {object}
 */
PathNavigationBaseCtrl.prototype.step = {};

/**
 * Parents of the current step
 * @type {object}
 */
PathNavigationBaseCtrl.prototype.parents = {};

/**
 * Reload the Step from route params
 */
PathNavigationBaseCtrl.prototype.reloadStep = function reloadStep() {
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
