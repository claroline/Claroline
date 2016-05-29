/**
 * Choice Question Service
 * @param {FeedbackService} FeedbackService
 * @constructor
 */
var ChoiceQuestionService = function ChoiceQuestionService(FeedbackService) {
    AbstractQuestionService.apply(this, arguments);
    
    this.FeedbackService = FeedbackService;
};

// Extends AbstractQuestionCtrl
ChoiceQuestionService.prototype = Object.create(AbstractQuestionService.prototype);

// Set up dependency injection (get DI from parent too)
ChoiceQuestionService.$inject = AbstractQuestionService.$inject.concat(['FeedbackService']);

/**
 * Initialize the answer object for the Question
 */
ChoiceQuestionService.prototype.initAnswer = function initAnswer() {
    return [];
};

/**
 * Check if all, all but one, or not all answers are found
 */
ChoiceQuestionService.prototype.answersAllFound = function answersAllFound(question, answer) {
    var numAnswersFound = 0;
    var numSolutions = 0;
    for (var i=0; i<question.solutions.length; i++) {
        if (question.solutions[i].score > 0) {
            numSolutions++;
        }
        for (var j=0; j<answer.length; j++) {
            if (question.solutions[i].id === answer[j] && question.solutions[i].score > 0) {
                numAnswersFound++;
            }
        }
    }
    var feedbackState = -1;
    if (numAnswersFound === numSolutions) {
        // all answers have been found
        feedbackState = this.FeedbackService.SOLUTION_FOUND;
    } else if (numAnswersFound === numSolutions -1 && question.multiple) {
        // one answer remains to be found
        feedbackState = this.FeedbackService.ONE_ANSWER_MISSING;
    } else {
        // more answers remain to be found
        feedbackState = this.FeedbackService.MULTIPLE_ANSWERS_MISSING;
    }
    
    return feedbackState;
};

/**
 * Get the correct answer from the solutions of a Question
 * @param   {Object} question
 * @returns {Array}
 */
ChoiceQuestionService.prototype.getCorrectAnswer = function getCorrectAnswer(question) {
    var answer = [];

    var betterFound = null;
    if (question.solutions) {
        for (var i = 0; i < question.solutions.length; i++) {
            var choice = question.solutions[i];

            if (question.multiple) {
                // Multiple choices
                if (0 < choice.score) {
                    answer.push(choice.id);
                }
            } else {
                // Unique choice
                if (null === betterFound || choice.score > betterFound.score) {
                    // Correct choice not already found OR current choice has more point than the previous found
                    betterFound = choice;
                }
            }
        }
    }

    if (!question.multiple) {
        answer.push(betterFound.id);
    }

    return answer;
};

/**
 * Check whether a choice is part of the answer
 * @param   {Array}  answer
 * @param   {Object} choice
 * @returns {boolean}
 */
ChoiceQuestionService.prototype.isChoiceSelected = function isChoiceSelected(answer, choice) {
    return answer && -1 !== answer.indexOf(choice.id);
};

/**
 * Check if choice is valid or not
 * @param   {Object} question
 * @param   {Object} choice
 * @returns {boolean}
 */
ChoiceQuestionService.prototype.isChoiceValid = function isChoiceValid(question, choice) {
    var isValid = false;

    var choiceSolution = this.getChoiceSolution(question, choice);
    if (choiceSolution.score > 0) {
        // The current choice is part of the right response => User choice is Valid
        isValid = true;
    }

    return isValid;
};

/**
 * Get the solution for a choice
 * @param   {Object} question
 * @param   {Object} choice
 * @returns {Object}
 */
ChoiceQuestionService.prototype.getChoiceSolution = function getChoiceSolution(question, choice) {
    var solution = null;

    if (question.solutions) {
        // Solutions have been loaded
        for (var i = 0; i < question.solutions.length; i++) {
            if (choice.id === question.solutions[i].id) {
                solution = question.solutions[i];
                break; // Stop searching
            }
        }
    }

    return solution;
};

/**
 * Get the Feedback of a Choice
 * @param   {Object} question
 * @param   {Object} choice
 * @returns {String}
 */
ChoiceQuestionService.prototype.getChoiceFeedback = function getChoiceFeedback(question, choice) {
    var feedback = '';

    var solution = this.getChoiceSolution(question, choice);
    if (solution) {
        feedback = solution.feedback;
    }

    return feedback;
};

/**
 * 
 */
ChoiceQuestionService.prototype.getFeedbackState = function getFeedbackState() {
    return this.feedbackState;
};

// Register service into AngularJS
angular
    .module('Question')
    .service('ChoiceQuestionService', ChoiceQuestionService);
