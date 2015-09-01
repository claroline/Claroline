/**
 * Class constructor
 * @returns {PathShowCtrl}
 * @constructor
 */
var PathShowCtrl = function PathShowCtrl($window, $route, $routeParams, PathService, UserProgressionService) {
    // Call parent constructor
    PathBaseCtrl.apply(this, arguments);

    this.userProgressionService = UserProgressionService;

    // Store UserProgression
    if (angular.isObject(this.userProgression)) {
        this.userProgressionService.set(this.userProgression);
    }

    return this;
};

// Extends the base controller
PathShowCtrl.prototype = Object.create(PathBaseCtrl.prototype);
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

    if (angular.isObject(this.currentStep) && angular.isDefined(this.currentStep.stepId)) {
        url += '#/' + this.currentStep.stepId;
    }

    this.window.location.href = url;
};
