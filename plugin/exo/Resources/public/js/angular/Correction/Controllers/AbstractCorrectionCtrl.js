/**
 * Abstract Correction Controller
 * @param {QuestionService} QuestionService
 * @constructor
 */
var AbstractCorrectionCtrl = function AbstractCorrectionCtrl(QuestionService) {
    this.QuestionService = QuestionService;

    // Create the correct answer from the Question solutions
    this.answer = this.QuestionService.getTypeService(this.question.type).getCorrectAnswer(this.question);
};

// Set up dependency injection
AbstractCorrectionCtrl.$inject = [ 'QuestionService' ];

/**
 * Current question
 * @type {Object}
 */
AbstractCorrectionCtrl.prototype.question = null;

/**
 * Correct answer for the Question
 * @type {mixed}
 */
AbstractCorrectionCtrl.prototype.answer = null;

// Register controller into AngularJS
angular
    .module('AbstractCorrectionCtrl', AbstractCorrectionCtrl);