/**
 * Question Show Controller
 * Displays a Question
 * @param {FeedbackService}  FeedbackService
 * @param {UserPaperService} UserPaperService
 */
var QuestionShowCtrl = function QuestionShowCtrl(FeedbackService, UserPaperService) {
    this.FeedbackService  = FeedbackService;
    this.UserPaperService = UserPaperService;

    // Get paper for the current Question
    this.questionPaper = this.UserPaperService.getQuestionPaper(this.question);

    // Get feedback info to display the general feedback of the Question
    this.feedback = this.FeedbackService.get();
};

// Set up dependency injection
QuestionShowCtrl.$inject = [ 'FeedbackService', 'UserPaperService' ];

/**
 * Current question
 * @type {Object}
 */
QuestionShowCtrl.prototype.question = {};

/**
 * Paper data for the current question
 * @type {Object}
 */
QuestionShowCtrl.prototype.questionPaper = {};

/**
 * Feedback information
 * @type {Object}
 */
QuestionShowCtrl.prototype.feedback = {};

/**
 * Check if a Hint has already been used (in paper)
 * @param   {Object} hint
 * @returns {Boolean}
 */
QuestionShowCtrl.prototype.isHintUsed = function isHintUsed(hint) {
    if (this.questionPaper.hints) {
        for (var i = 0; i < this.questionPaper.hints.length; i++) {
            if (this.questionPaper.hints[i].id == hint.id) {
                return true;
            }
        }
    }

    return false;
};

/**
 * Get hint data and update student data in common service
 * @param {Object} hint
 */
QuestionShowCtrl.prototype.showHint = function showHint(hint) {
    if (!this.isHintUsed(hint)) {
        // Load Hint data
        this.UserPaperService.useHint(this.question, hint);
    }
};

/**
 * Get Hint value (only available for loaded Hint)
 * @param {Object} hint
 */
QuestionShowCtrl.prototype.getHintValue = function getHintValue(hint) {
    var value = '';
    if (this.questionPaper.hints && this.questionPaper.hints.length > 0) {
        for (var i = 0; i < this.questionPaper.hints.length; i++) {
            if (this.questionPaper.hints[i].id == hint.id) {
                value = this.questionPaper.hints[i].value;
                break;
            }
        }
    }

    return value;
};

// Register controller into AngularJS
angular
    .module('Question')
    .controller('QuestionShowCtrl', QuestionShowCtrl);