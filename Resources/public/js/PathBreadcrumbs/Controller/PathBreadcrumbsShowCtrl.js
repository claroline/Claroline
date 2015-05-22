/**
 * Class constructor
 * @returns {PathBreadcrumbsShowCtrl}
 * @constructor
 */
var PathBreadcrumbsShowCtrl = function PathBreadcrumbsShowCtrl($routeParams) {
    this.current = $routeParams;

    return this;
};

/**
 * Current displayed Step
 * @type {object}
 */
PathBreadcrumbsShowCtrl.prototype.current = {};
