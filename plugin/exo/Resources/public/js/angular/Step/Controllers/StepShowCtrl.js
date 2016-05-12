/**
 * Step Show Controller
 * @param {UserPaperService} UserPaperService
 * @constructor
 */
var StepShowCtrl = function StepShowCtrl(UserPaperService) {
    this.UserPaperService = UserPaperService;

    // Get the order of items from the Paper of the User (in case they are shuffled)
    this.items = this.UserPaperService.orderQuestions(this.step);
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

/**
 *
 * @type {boolean}
 */
StepShowCtrl.prototype.solutionShown = false;

/**
 * Get the Paper related to the Question
 * @param   {Object} question
 * @returns {Object}
 */
StepShowCtrl.prototype.getQuestionPaper = function getQuestionPaper(question) {
    return this.UserPaperService.getQuestionPaper(question);
};

// Inject controller into AngularJS
angular
    .module('Step')
    .controller('StepShowCtrl', StepShowCtrl);