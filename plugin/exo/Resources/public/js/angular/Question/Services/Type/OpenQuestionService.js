/**
 * Open Question Service
 * @constructor
 */
var OpenQuestionService = function OpenQuestionService() {
    AbstractQuestionService.apply(this, arguments);
};

// Extends AbstractQuestionCtrl
OpenQuestionService.prototype = Object.create(AbstractQuestionService.prototype);

// Set up dependency injection (get DI from parent too)
OpenQuestionService.$inject = AbstractQuestionService.$inject;

/**
 * Initialize the answer object for the Question
 */
OpenQuestionService.prototype.initAnswer = function initAnswer() {
    return '';
};

/**
 * Get the correct answer from the solutions of a Question
 * For type = long we can not generate a correct answer at it requires a manual correction
 * @param   {Object} question
 * @returns {Object}
 */
OpenQuestionService.prototype.getCorrectAnswer = function getCorrectAnswer(question) {
    var answer = null;

    if (question.solutions && 'long' !== question.typeOpen) {
        answer = [];

        // Only get the list of required keywords
        if ('oneWord' === question.typeOpen) {
            // One word answer (get the keyword with the max score)
            var betterFound = null;
            for (var i = 0; i < question.solutions.length; i++) {
                if (null === betterFound || question.solutions[i].score > betterFound.score) {
                    betterFound = question.solutions[i];
                }
            }

            answer.push(betterFound);
        } else if ('short' === question.typeOpen) {
            // Short answer (display all keywords with a positive score as expected answer)
            answer = question.solutions;
        }
    }

    return answer;
};

// Register service into AngularJS
angular
    .module('Question')
    .service('OpenQuestionService', OpenQuestionService);
