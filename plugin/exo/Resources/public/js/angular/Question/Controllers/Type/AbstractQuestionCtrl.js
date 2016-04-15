/**
 * Base class for all Question types
 * @param {FeedbackService} FeedbackService
 * @constructor
 */
var AbstractQuestionCtrl = function AbstractQuestionCtrl(FeedbackService) {
    this.FeedbackService = FeedbackService;

    // get feedback info
    this.feedback = this.FeedbackService.get();

    // Register specific Feedback functions
    this.FeedbackService
        .on('show', this.onFeedbackShow.bind(this))
        .on('hide', this.onFeedbackHide.bind(this))
};

// Set up dependency injection
AbstractQuestionCtrl.$inject = [ 'FeedbackService' ];

/**
 * Current Question
 * @type {Object}
 */
AbstractQuestionCtrl.prototype.question = {};

/**
 * Paper data for the current question
 * @type {Object}
 */
AbstractQuestionCtrl.prototype.questionPaper = {};

/**
 * Feedback info (available + visible)
 * @type {null}
 */
AbstractQuestionCtrl.prototype.feedback = null;

/**
 * Is the Question currently displayed ?
 * @type {boolean}
 */
AbstractQuestionCtrl.prototype.collapsed = false;

/**
 * Callback executed when Feedback for the Question is shown
 */
AbstractQuestionCtrl.prototype.onFeedbackShow = function onFeedbackShow() {
    console.info('onFeedbackShow: Implement this method in your Type/*QuestionCtrl if you need custom logic.');
};

/**
 * Callback executed when Feedback for the Question is hidden
 */
AbstractQuestionCtrl.prototype.onFeedbackHide = function onFeedbackHide() {
    console.info('onFeedbackHide: Implement this method in your Type/*QuestionCtrl if you need custom logic.');
};

// Register controller into Angular JS
angular
    .module('Question')
    .controller('AbstractQuestionCtrl', AbstractQuestionCtrl);