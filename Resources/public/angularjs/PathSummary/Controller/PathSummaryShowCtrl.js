/**
 * Class constructor
 * @returns {PathSummaryShowCtrl}
 * @constructor
 */
var PathSummaryShowCtrl = function PathSummaryShowCtrl($routeParams, PathService) {
    PathSummaryBaseCtrl.apply(this, arguments);

    return this;
};

// Extends the base controller
PathSummaryShowCtrl.prototype = PathSummaryBaseCtrl.prototype;
PathSummaryShowCtrl.prototype.constructor = PathSummaryShowCtrl;