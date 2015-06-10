/**
 * Class constructor
 * @returns {PathNavigationCtrl}
 * @constructor
 */
var PathNavigationCtrl = function PathNavigationCtrl($routeParams, $scope, PathService) {
    this.pathService = PathService;

    this.current = $routeParams;

    this.summaryState = this.pathService.getSummaryState();

    // listen to path changes to update history
    $scope.$watch(
        // Property watched (the current step)
        function () {
            return this.current;
        }.bind(this),

        // The callback to execute
        function (newValue, oldValue) {
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

            // Get the next step
            this.next = this.pathService.getNext(this.step);
        }.bind(this)
    , true);

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
 * Next step
 * @type {object}
 */
PathNavigationCtrl.prototype.next = null;

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
 * Allow toggle Summary from the current step
 */
PathNavigationCtrl.prototype.toggleSummary = function () {
    this.pathService.toggleSummaryState();
};