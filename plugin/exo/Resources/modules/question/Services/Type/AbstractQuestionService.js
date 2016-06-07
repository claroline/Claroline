var AbstractQuestionService = function AbstractQuestionService() {

};

// Set up dependency injection
AbstractQuestionService.$inject = [];

/**
 * Initialize the answer object for the Question
 */
AbstractQuestionService.prototype.initAnswer = function initAnswer() {
    console.error('Each instance of AbstractQuestionType must implement the `initAnswer`.');
};

/**
 * Get the correct answer from the solutions of a Question
 * @param   {Object} question
 * @returns {Object|Array}
 */
AbstractQuestionService.prototype.getCorrectAnswer = function getCorrectAnswer(question) {
    console.error('Each instance of AbstractQuestionType must implement the `getCorrectAnswer`.');
};

// Register service into AngularJS
angular
    .module('Question')
    .service('AbstractQuestionService', AbstractQuestionService);