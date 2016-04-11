/**
 * List of steps of an Exercise
 * @param {Array}           steps
 * @param {ExerciseService} ExerciseService
 * @constructor
 */
var StepListCtrl = function StepListCtrl(exerciseId, steps, ExerciseService) {
    this.steps           = steps;
    this.exerciseId      = exerciseId;
    this.ExerciseService = ExerciseService;
};

// Set p up dependency injection
StepListCtrl.$inject = [ 'exerciseId', 'steps', 'ExerciseService' ];

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