/**
 * Path base controller
 *
 * @returns {PathBaseCtrl}
 * @constructor
 */
var PathBaseCtrl = function PathBaseCtrl($route, PathService) {
    this.pathService = PathService;

    // Store path to make it available by all UI components
    this.pathService.setId(this.id);
    this.pathService.setPath(this.path);

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