/**
 * Cloze Question Controller
 * @param {FeedbackService} FeedbackService
 * @constructor
 */
var ClozeQuestionCtrl = function ClozeQuestionCtrl(FeedbackService) {
    AbstractQuestionCtrl.apply(this, arguments);

    // Initialize answer if needed
    if (null === this.questionPaper.answer || typeof this.questionPaper.answer === 'undefined') {
        this.questionPaper.answer = [];
    }
};

// Extends AbstractQuestionCtrl
ClozeQuestionCtrl.prototype = Object.create(AbstractQuestionCtrl.prototype);

// Set up dependency injection (get DI from parent too)
ClozeQuestionCtrl.$inject = AbstractQuestionCtrl.$inject;

/**
 * Stores Holes to be able to toggle there state
 * This object is populated while compiling the directive to add data-binding on cloze
 * @type {Object}
 */
ClozeQuestionCtrl.prototype.holes = {};

/**
 * Check whether a Hole is valid or not
 * @param   {Object} hole
 * @returns {Boolean}
 */
ClozeQuestionCtrl.prototype.isHoleValid = function isHoleValid(hole) {
    var solution = this.getHoleSolution(hole);
    var answer   = this.getHoleAnswer(hole);

    if (solution && answer) {
        var expected = this.getHoleExpectedAnswer(hole, solution);
        if (expected) {
            // The right response has been found, we can check the User answer
            if (hole.selector) {
                // <select>
                return answer.answerText === expected.id;
            } else {
                // <input>
                return !!((expected.caseSensitive && expected.response === answer.answerText)
                || (!expected.caseSensitive && expected.response.toLowerCase() === answer.answerText.toLowerCase()));
            }
        }
    }
};

/**
 * Get the User answer for a Hole
 * @param   {Object} hole
 * @returns {Object}
 */
ClozeQuestionCtrl.prototype.getHoleAnswer = function getHoleAnswer(hole) {
    var answer = null;

    for (var i = 0; i < this.questionPaper.answer.length; i++) {
        if (hole.id === this.questionPaper.answer[i].holeId) {
            answer = this.questionPaper.answer[i];
            break; // Stop searching
        }
    }

    if (null === answer) {
        // Generate an empty response
        answer = {
            holeId     : hole.id,
            answerText : ''
        };

        // Add to the list of answers
        this.questionPaper.answer.push(answer);
    }

    return answer;
};

/**
 * Get the complete solution for a Hole
 * @param   {Object} hole
 * @returns {{
 *      id            : String
 *      wordResponses : Array
 * }}
 */
ClozeQuestionCtrl.prototype.getHoleSolution = function getHoleSolution(hole) {
    var solution = null;
    if (this.question.solutions) {
        for (var i = 0; i < this.question.solutions.length; i++) {
            if (this.question.solutions[i].id == hole.id) {
                solution = this.question.solutions[i];
                break; // Stop searching
            }
        }
    }

    return solution;
};

/**
 * Get the Feedback of a Hole
 * @param   {Object} hole
 * @returns {string}
 */
ClozeQuestionCtrl.prototype.getHoleFeedback = function getHoleFeedback(hole) {
    var feedback = '';

    var solution = this.getHoleSolution(hole);
    if (solution) {
        var expected = this.getHoleExpectedAnswer(hole, solution);
        if (expected && expected.feedback) {
            feedback = expected.feedback;
        }
    }

    return feedback;
};

/**
 * Get the expected answer from the Hole solution
 * @param   {Object} hole
 * @param   {Object} solution
 * @returns {{
 *      id            : String,
 *      word          : String,
 *      caseSensitive : Boolean,
 *      score         : Number,
 *      feedback      : String,
 *      rightResponse : Boolean
 * }}
 */
ClozeQuestionCtrl.prototype.getHoleExpectedAnswer = function getHoleExpectedAnswer(hole, solution) {
    var expectedWord = null;
    if (hole.selector) {
        // The hole is a <select>
        // Find the expected word in the solution list
        for (var i = 0; i < solution.wordResponses.length; i++) {
            if (solution.wordResponses[i].rightResponse) {
                expectedWord = solution.wordResponses[i];
                break; // stop searching
            }
        }
    } else {
        // The hole is a <input>
        expectedWord = solution.wordResponses && solution.wordResponses.length > 0 ? solution.wordResponses[0] : null;
    }

    return expectedWord;
};

/**
 * Validate Holes when feedback are shown to know which answers are valid
 */
ClozeQuestionCtrl.prototype.onFeedbackShow = function onFeedbackShow() {
    // Validate holes
    if (this.question.solutions) {
        for (var holeId in this.holes) {
            if (this.holes.hasOwnProperty(holeId)) {
                this.holes[holeId].valid = this.isHoleValid(this.holes[holeId]);
            }
        }
    }
};

// Register controller into AngularJS
angular
    .module('Question')
    .controller('ClozeQuestionCtrl', ClozeQuestionCtrl);