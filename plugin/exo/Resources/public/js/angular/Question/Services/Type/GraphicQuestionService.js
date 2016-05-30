/**
 * Graphic Question Service
 * @param {FeedbackService} FeedbackService
 * @param {ImageAreaService} ImageAreaService
 * @constructor
 */
var GraphicQuestionService = function GraphicQuestionService(FeedbackService, ImageAreaService) {
    AbstractQuestionService.apply(this, arguments);
    
    this.FeedbackService = FeedbackService;
    this.ImageAreaService = ImageAreaService;
};

// Extends AbstractQuestionCtrl
GraphicQuestionService.prototype = Object.create(AbstractQuestionService.prototype);

// Set up dependency injection (get DI from parent too)
GraphicQuestionService.$inject = AbstractQuestionService.$inject.concat(['FeedbackService', 'ImageAreaService']);

/**
 * Initialize the answer object for the Question
 */
GraphicQuestionService.prototype.initAnswer = function initAnswer() {
    return [];
};

/**
 * Get the correct answer from the solutions of a Question
 * @param   {Object} question
 * @returns {Array}
 */
GraphicQuestionService.prototype.getCorrectAnswer = function getCorrectAnswer(question) {
    var answer = [];

    return answer;
};

/**
 *
 */
GraphicQuestionService.prototype.answersAllFound = function answersAllFound(question, answers) {
    var notFound = 0;
    for (var i = 0; i < question.solutions.length; i++) {
        for (var j = 0; j < answers.length; j++) {
            var found = this.ImageAreaService.isInArea(question.solutions[i], answers[j]);
            if (found) {
                break;
            }
        }

        if (!found) {
            // zone has no answer
            notFound++;
        }
    }
    
    var feedbackState = -1;
    if (0 === notFound) {
        feedbackState = this.FeedbackService.SOLUTION_FOUND;
    } else if (1 === notFound) {
        feedbackState = this.FeedbackService.ONE_ANSWER_MISSING;
    } else {
        feedbackState = this.FeedbackService.MULTIPLE_ANSWERS_MISSING;
    }
    
    return feedbackState;
};

// Register service into AngularJS
angular
    .module('Question')
    .service('GraphicQuestionService', GraphicQuestionService);
