/**
 * Graphic Question Service
 * @constructor
 */
var GraphicQuestionService = function GraphicQuestionService() {
    AbstractQuestionService.apply(this, arguments);
};

// Extends AbstractQuestionCtrl
GraphicQuestionService.prototype = Object.create(AbstractQuestionService.prototype);

// Set up dependency injection (get DI from parent too)
GraphicQuestionService.$inject = AbstractQuestionService.$inject;

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
