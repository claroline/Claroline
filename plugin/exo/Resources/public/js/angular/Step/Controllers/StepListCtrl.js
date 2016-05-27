/**
 * List of steps of an Exercise
 * @param {Object}          $scope
 * @param {Array}           steps
 * @param {Object}          dragulaService
 * @param {ExerciseService} ExerciseService
 * @param {StepService}     StepService
 * @constructor
 */
var StepListCtrl = function StepListCtrl($scope, steps, dragulaService, ExerciseService, StepService) {
    this.steps           = steps;
    this.ExerciseService = ExerciseService;
    this.StepService     = StepService;

    this.exerciseId      = ExerciseService.getExercise().id;

    dragulaService.options($scope, 'order-steps', {
        moves: function (el, container, handle) {
            return handle.className === 'handle';
        }
    });

    $scope.$on('order-steps.drop-model', function dropStep() {
        this.ExerciseService.reorderSteps();
    }.bind(this));

    $scope.$on('order-questions.drop-model', function dropQuestion(el, target, source) {
        // Can not find another to retrieve the model step
        var stepId = source.attr('data-step-id');
        var step = this.ExerciseService.getStep(stepId);
        if (step) {
            this.StepService.reorderItems(step);
        }
    }.bind(this));
};

// Set p up dependency injection
StepListCtrl.$inject = [ '$scope', 'steps', 'dragulaService', 'ExerciseService', 'StepService' ];

/**
 * Id of the current Exercise (for PHP actions links)
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