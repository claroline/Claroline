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
 * Progression of the current User (key => stepId, value => json representation of UserProgression Entity)
 * @type {object}
 */
PathShowCtrl.prototype.userProgression = {};

/**
 * Open Path editor
 */
PathShowCtrl.prototype.edit = function () {
    var url = Routing.generate('innova_path_editor_wizard', {
        id: this.id
    });

    window.open(url);
};
