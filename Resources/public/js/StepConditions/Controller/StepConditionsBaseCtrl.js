/**
 * Class constructor
 * @returns {StepConditionsBaseCtrl}
 * @constructor
 */
    //StepService
var StepConditionsBaseCtrl = function StepConditionsBaseCtrl($route, $routeParams, PathService, StepConditionsService) {
    this.webDir = AngularApp.webDir;

    this.pathService = PathService;
    this.stepConditionsService = StepConditionsService;

    this.current = $routeParams;
    var path = this.pathService.getPath();
    if (angular.isObject(path)) {
        // Set the structure of the path
        this.structure = path.steps;
    }

    //force id if not set
    if (typeof this.current.stepId == 'undefined'){this.current.stepId = this.structure[0].id}
    //Get the current step
    this.step = this.pathService.getStep(this.current.stepId);

//    this.evaluation[step.id] = this.stepConditionsService.getEvaluationFromController(step.activityId);

    //get the current condition
    this.conditionstructure = [];
    if (angular.isObject(this.step) && angular.isObject(this.step.condition)){
        this.conditionstructure = [this.step.condition];
    }

    //TODO : Make it work, to use in conditions
//    this.pathService.getAllEvaluationsForPath(this.structure);

    return this;
};

/**
 * Current step
 * @type {object}
 */
StepConditionsBaseCtrl.prototype.step = null;

/**
 * Path to the symfony web directory (where are stored our partials)
 * @type {null}
 */
StepConditionsBaseCtrl.prototype.webDir = null;

/**
 * Structure of the current path
 * @type {object}
 */
StepConditionsBaseCtrl.prototype.structure = {};

/**
 * Structure of the current condition
 * @type {object}
 */
StepConditionsBaseCtrl.prototype.conditionstructure = [];

/**
 * Current displayed Step
 * @type {object}
 */
StepConditionsBaseCtrl.prototype.current = {};