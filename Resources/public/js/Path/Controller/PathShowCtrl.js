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

/**
 * Is current User allowed to Edit the Path
 * @type {boolean}
 */
PathShowCtrl.prototype.editEnabled = false;

/**
 * Open Path editor
 */
PathShowCtrl.prototype.edit = function () {
    var url = Routing.generate('innova_path_editor_wizard', {
        id: this.id
    });

    window.open(url);
};
