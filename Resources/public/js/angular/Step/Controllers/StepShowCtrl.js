/**
 * Step Show Controller
 * @constructor
 */
var StepShowCtrl = function StepShowCtrl() {

};

// Set up dependency injection
StepShowCtrl.$inject = [ ];

/**
 * Current step
 * @type {Object}
 */
StepShowCtrl.prototype.step = null;

/**
 * Current step number
 * @type {Object}
 */
StepShowCtrl.prototype.stepIndex = 0;

// Inject controller into AngularJS
angular
    .module('Step')
    .controller('StepShowCtrl', StepShowCtrl);