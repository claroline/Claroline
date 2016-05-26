/**
 * List of steps of an Exercise
 * @param {Object}          $scope
 * @param {Object}          exercise
 * @param {Array}           steps
 * @param {Object}          dragulaService
 * @param {ExerciseService} ExerciseService
 * @param {StepService}     StepService
 * @constructor
 */
var StepListCtrl = function StepListCtrl($scope, exercise, steps, dragulaService, ExerciseService, StepService) {
    this.steps           = steps;
    this.exercise        = exercise;
    this.ExerciseService = ExerciseService;
    this.StepService     = StepService;


    $scope.$on('order-steps.drop', function (e, el) {
        console.log(this.steps);
        console.log(e);
        console.log(el);
    }.bind(this));
};

// Set p up dependency injection
StepListCtrl.$inject = [ '$scope', 'exercise', 'steps', 'dragulaService', 'ExerciseService', 'StepService' ];

/**
 * Current Exercise
 * @type {Object}
 */
StepListCtrl.prototype.exerciseId = {};

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