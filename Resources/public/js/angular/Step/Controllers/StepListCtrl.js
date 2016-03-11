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

// Register controller into Angular JS
angular
    .module('Step')
    .controller('StepListCtrl', StepListCtrl);