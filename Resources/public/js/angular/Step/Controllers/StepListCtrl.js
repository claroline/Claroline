/**
 * List of steps of an Exercise
 * @param {String}          exerciseId
 * @param {Array}           steps
 * @param {ExerciseService} ExerciseService
 * @param {StepService}     StepService
 * @constructor
 */
var StepListCtrl = function StepListCtrl(exerciseId, steps, ExerciseService, StepService) {
    this.steps           = steps;
    this.exerciseId      = exerciseId;
    this.ExerciseService = ExerciseService;
    this.StepService     = StepService;
};

// Set p up dependency injection
StepListCtrl.$inject = [ 'exerciseId', 'steps', 'ExerciseService', 'StepService' ];

/**
 * ID of the current Exercise
 * @type {string}
 */
StepListCtrl.prototype.exerciseId = null;

/**
 * List of Steps of the Exercise
 * @type {Array}
 */
StepListCtrl.prototype.steps = [];

/**
 * Add a new Step
 */
StepListCtrl.prototype.addStep = function addStep() {
    this.ExerciseService.addStep();
};

StepListCtrl.prototype.removeStep = function removeStep(step) {
    this.ExerciseService.removeStep(step);
};


StepListCtrl.prototype.removeItem = function removeItem(step, item) {
    this.ExerciseService.removeItem(step, item);
};

// Register controller into Angular JS
angular
    .module('Step')
    .controller('StepListCtrl', StepListCtrl);