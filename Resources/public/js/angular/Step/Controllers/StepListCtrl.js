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

};

/**
 * Add a new item to the Step
 * @param {Object} step
 */
StepListCtrl.prototype.addItem = function addItem(step) {

};

StepListCtrl.prototype.removeItem = function removeItem(item) {
    // {{ 'ujm_question_delete' | path:{ id: item.id } }}
};

// Register controller into Angular JS
angular
    .module('Step')
    .controller('StepListCtrl', StepListCtrl);