/**
 * Class constructor
 * @returns {PathBreadcrumbsShowCtrl}
 * @constructor
 */
var PathBreadcrumbsShowCtrl = function PathBreadcrumbsShowCtrl($routeParams, $scope, PathService) {
    this.pathService = PathService;

    this.current = $routeParams;

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

            // Calculate roadback to the previous step
        }.bind(this)
    , true);

    return this;
};

/**
 * Current matched route
 * @type {object}
 */
PathBreadcrumbsShowCtrl.prototype.current = {};

/**
 * Current displayed step
 * @type {object}
 */
PathBreadcrumbsShowCtrl.prototype.step = {};

/**
 * Parents of the current step
 * @type {object}
 */
PathBreadcrumbsShowCtrl.prototype.parents = {};