/**
 * Step Show Controller
 * @param {UserPaperService} UserPaperService
 * @constructor
 */
var StepShowCtrl = function StepShowCtrl(UserPaperService) {
    // Get the order of items from the Paper of the User (in case they are shuffled)
    this.items = UserPaperService.orderQuestions(this.step);
};

// Set up dependency injection
StepShowCtrl.$inject = [ 'UserPaperService' ];

/**
 * Current step
 * @type {Object}
 */
StepShowCtrl.prototype.step = null;

/**
 * Items of the Step (correctly ordered)
 * @type {Array}
 */
StepShowCtrl.prototype.items = [];

/**
 * Current step number
 * @type {Object}
 */
StepShowCtrl.prototype.stepIndex = 0;

// Inject controller into AngularJS
angular
    .module('Step')
    .controller('StepShowCtrl', StepShowCtrl);