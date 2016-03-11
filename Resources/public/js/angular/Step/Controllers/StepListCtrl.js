/**
 * List of steps of an Exercise
 * @param {Array} steps
 * @constructor
 */
var StepListCtrl = function StepListCtrl(steps) {
    this.steps = steps;
};

// Set p up dependency injection
StepListCtrl.$inject = [ 'steps' ];

/**
 * List of Steps of the Exercise
 * @type {Array}
 */
StepListCtrl.prototype.steps = [];

/**
 * Add a new Step
 */
StepListCtrl.prototype.addStep = function addStep() {
    // Initialize a new Step
    this.steps.push({
        id: null,
        items: []
    });
};

/**
 * Add a new item to the Step
 * @param {Object} step
 */
StepListCtrl.prototype.addItem = function addItem(step) {

};

// Register controller into Angular JS
angular
    .module('Step')
    .controller('StepListCtrl', StepListCtrl);