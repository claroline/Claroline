/**
 * Cloze Question Service
 * @constructor
 */
var ClozeQuestionService = function ClozeQuestionService() {
    AbstractQuestionService.apply(this, arguments);
};

// Extends AbstractQuestionCtrl
ClozeQuestionService.prototype = Object.create(AbstractQuestionService.prototype);

// Set up dependency injection (get DI from parent too)
ClozeQuestionService.$inject = AbstractQuestionService.$inject;

/**
 * Initialize the answer object for the Question
 */
ClozeQuestionService.prototype.initAnswer = function initAnswer() {
    return [];
};

/**
 * Get the correct answer from the solutions of a Question
 * @param   {Object} question
 * @returns {Array}
 */
ClozeQuestionService.prototype.getCorrectAnswer = function getCorrectAnswer(question) {
    var answer = [];

    if (question.solutions) {
        for (var i = 0; i < question.holes.length; i++) {
            var hole = question.holes[i];

            // Get the correct answer
            var correct = this.getHoleCorrectAnswer(question, hole);
            if (correct) {
                answer.push({
                    holeId    : hole.id,
                    answerText: hole.selector ? correct.id : correct.text
                });
            }
        }
    }

    return answer;
};

/**
 * Get the correct answer for a Hole
 * @param   {Object} question
 * @param   {Object} hole
 * @returns {Object}
 */
ClozeQuestionService.prototype.getHoleCorrectAnswer = function getHoleCorrectAnswer(question, hole) {
    var correct = null;

    var solution = this.getHoleSolution(question, hole);
    if (solution) {
        // Get the correct answer
        for (var j = 0; j < solution.answers.length; j++) {
            if (null === correct || solution.answers[j].score > correct.score) {
                correct = solution.answers[j];
            }
        }
    }

    return correct;
};

/**
 * Get the answer of a specific Hole from the answer of the Question
 * @param {Array}  answer
 * @param {Object} hole
 * @returns {Object}
 */
ClozeQuestionService.prototype.getHoleAnswer = function getHoleAnswer(answer, hole) {
    var holeAnswer = null;
    for (var i = 0; i < answer.length; i++) {
        if (hole.id === answer[i].holeId) {
            holeAnswer = answer[i];
            break; // Stop searching
        }
    }

    return holeAnswer;
};

/**
 * Get the complete solution for a Hole
 * @param   {Object} question
 * @param   {Object} hole
 * @returns {{
 *      id      : String
 *      answers : Array
 * }}
 */
ClozeQuestionService.prototype.getHoleSolution = function getHoleSolution(question, hole) {
    var solution = null;
    if (question.solutions) {
        for (var i = 0; i < question.solutions.length; i++) {
            if (question.solutions[i].holeId == hole.id) {
                solution = question.solutions[i];
                break; // Stop searching
            }
        }
    }

    return solution;
};

/**
 * Get the feedback for the Hole
 * @param   {Object} question
 * @param   {Object} hole
 * @returns {string}
 */
ClozeQuestionService.prototype.getHoleFeedback = function getHoleFeedback(question, hole) {
    var feedback = '';

    var correct = this.getHoleCorrectAnswer(question, hole);
    if (correct && correct.feedback) {
        feedback = correct.feedback;
    }

    return feedback;
};

// Register service into AngularJS
angular
    .module('Question')
    .service('ClozeQuestionService', ClozeQuestionService);
