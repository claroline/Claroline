/**
 * Class constructor
 * @returns {PathNavigationEditCtrl}
 * @constructor
 */
var PathNavigationEditCtrl = function PathNavigationEditCtrl($routeParams, $scope, PathService) {
    // Call parent constructor
    PathNavigationBaseCtrl.apply(this, arguments);

    return this;
};

// Extends the base controller
PathNavigationEditCtrl.prototype = Object.create(PathNavigationBaseCtrl.prototype);
PathNavigationEditCtrl.prototype.constructor = PathNavigationEditCtrl;