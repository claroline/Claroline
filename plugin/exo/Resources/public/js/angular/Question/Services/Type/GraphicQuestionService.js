/**
 * Graphic Question Service
 * @param {FeedbackService} FeedbackService
 * @constructor
 */
var GraphicQuestionService = function GraphicQuestionService(FeedbackService) {
    AbstractQuestionService.apply(this, arguments);
    
    this.FeedbackService = FeedbackService;
};

// Extends AbstractQuestionCtrl
GraphicQuestionService.prototype = Object.create(AbstractQuestionService.prototype);

// Set up dependency injection (get DI from parent too)
GraphicQuestionService.$inject = AbstractQuestionService.$inject.concat(['FeedbackService']);

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

GraphicQuestionService.prototype.getCrosshair = function getCrosshair() {

};

/**
 *
 */
GraphicQuestionService.prototype.answersAllFound = function answersAllFound(question, answers) {
    var startX = 0;
    var startY = 0;
    var start;
    var centerX = 0;
    var centerY = 0;
    
    var notFoundZones = [];
    
    for (var i=0; i<question.solutions.length; i++) {
        notFoundZones.push(question.solutions[i]);
    }

    for (var i = 0; i < question.solutions.length; i++) {
        for (var j=0; j<answers.length; j++) {
            var answer = answers[j].split("-");
            var answerX = answer[0];
            var answerY = answer[1];
            
            start = question.solutions[i].value.split(",");
            startX = parseFloat(start[0]);
            startY = parseFloat(start[1]);
            centerX = startX + question.solutions[i].size/2;
            centerY = startY + question.solutions[i].size/2;
            var endX = startX + question.solutions[i].size;
            var endY = startY + question.solutions[i].size;

            var distance = Math.sqrt((centerX-answerX)*(centerX-answerX) + (centerY-answerY)*(centerY-answerY));
            distance = Math.round(distance);

            if (((question.solutions[i].size >= distance*2 && question.solutions[i].shape === "circle")
                || (question.solutions[i].shape === "square" && answerX > startX && answerX < endX && answerY > startY && answerY < endY))
                && notFoundZones.indexOf(question.solutions[i]) !== -1) {
                notFoundZones.splice(notFoundZones.indexOf(question.solutions[i]), 1);
            }
        }
    }
    
    var feedbackState = -1;
    if (notFoundZones.length === 0) {
        feedbackState = this.FeedbackService.SOLUTION_FOUND;
    } else if (notFoundZones.length === 1) {
        feedbackState = this.FeedbackService.ONE_ANSWER_MISSING;
    } else {
        feedbackState = this.FeedbackService.MULTIPLE_ANSWERS_MISSING;
    }
    
    return feedbackState;
};

/**
 * Get the URL of the image
 * @param question
 * @returns {string}
 */
GraphicQuestionService.prototype.getImageUrl = function getImageUrl(question) {
    var url = null;
    if (question.document && question.document.url) {
        url = AngularApp.webDir + question.document.url;
    }

    return url;
};

// Register service into AngularJS
angular
    .module('Question')
    .service('GraphicQuestionService', GraphicQuestionService);
