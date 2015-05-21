/**
 * Class constructor
 * @returns {PathShowCtrl}
 * @constructor
 */
var PathShowCtrl = function PathShowCtrl($route, PathService) {
    // Call parent constructor
    PathBaseCtrl.apply(this, arguments);

    return this;
};

// Extends the base controller
PathShowCtrl.prototype = PathBaseCtrl.prototype;
PathShowCtrl.prototype.constructor = PathShowCtrl;
