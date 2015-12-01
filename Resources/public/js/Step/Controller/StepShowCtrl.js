/**
 * Class constructor
 * @returns {StepShowCtrl}
 * @constructor
 */
var StepShowCtrl = function StepShowCtrl(step, inheritedResources, PathService, authorization, $sce, UserProgressionService, $filter) {
    StepBaseCtrl.apply(this, arguments);

    // Store some services
    this.userProgressionService = UserProgressionService;
    this.filterDate             = $filter('date');

    this.authorization = authorization;
    if (authorization && authorization.granted) {
        // User has access to the current step
        if (angular.isDefined(this.step) && angular.isDefined(this.step.description) && typeof this.step.description == 'string') {
            // Trust content to allow Cross Sites URL
            this.step.description = $sce.trustAsHtml(this.step.description);
        }

        // Update User progression if needed (e.g. if the User has never seen the Step, mark it as seen)
        this.progression = this.userProgressionService.getForStep(this.step);
        if (!angular.isObject(this.progression)) {
            //root step is authorized anyways
            var authorized = (this.pathService.getRoot().id == step.id) ? 1 : 0;
            // Create progression for User
            this.progression = this.userProgressionService.create(step, null, authorized);
        }
    }

    return this;
};

// Extends the base controller
StepShowCtrl.prototype = Object.create(StepBaseCtrl.prototype);
StepShowCtrl.prototype.constructor = StepShowCtrl;

// Dependency Injection
StepShowCtrl.$inject = [ 'step', 'inheritedResources', 'PathService', 'authorization', '$sce', 'UserProgressionService', '$filter' ];

/**
 * Service that manages the User Progression in the Path
 * @type {{}}
 */
StepShowCtrl.prototype.userProgressionService = {};

StepShowCtrl.prototype.filterDate = {};

/**
 * Progression of the User for the current Step (NOT the progression for the whole Path)
 * @type {null}
 */
StepShowCtrl.prototype.progression = {};

StepShowCtrl.prototype.isAccessible = function () {
    var now = this.filterDate(new Date(), 'yyyy-MM-dd HH:mm:ss')

    var from = null;
    if (this.step.accessibleFrom != null && this.step.accessibleFrom.length !== 0) {
        from = this.step.accessibleFrom;
    }

    var until = null;
    if (this.step.accessibleUntil != null && this.step.accessibleUntil.length !== 0) {
        until = this.step.accessibleUntil;
    }

    var accessible = false;
    if ( (null === from || now >= from) && (null === until || now <= until) ) {
        accessible = true;
    }

    return accessible;
};

StepShowCtrl.prototype.updateProgression = function (newStatus) {
    this.userProgressionService.update(this.step, newStatus);
};

// Register controller into Angular
angular
    .module('StepModule')
    .controller('StepShowCtrl', StepShowCtrl);
