/**
 * Choice Question Controller
 * @param {FeedbackService} FeedbackService
 * @constructor
 */
var ChoiceQuestionCtrl = function ChoiceQuestionCtrl(FeedbackService) {
    AbstractQuestionCtrl.apply(this, arguments);

    if (this.question.choices) {
        this.choices = this.question.choices;
    }
};

// Extends AbstractQuestionCtrl
ChoiceQuestionCtrl.prototype = Object.create(AbstractQuestionCtrl.prototype);

// Set up dependency injection (get DI from parent too)
ChoiceQuestionCtrl.$inject = AbstractQuestionCtrl.$inject;

/**
 * Stores Choices to be able to toggle there state
 * @type {Object}
 */
ChoiceQuestionCtrl.prototype.choices = [];

/**
 * Toggle the selected state of a Choice
 * @param {Object} choice
 */
ChoiceQuestionCtrl.prototype.toggleChoice = function toggleChoice(choice) {
    if (!this.feedback.visible && 1 !== choice.valid && !this.isUniqueChoiceValid()) {
        if (!this.questionPaper.answer) {
            this.questionPaper.answer = [];
        }

        // Toggle value only if field is not locked
        if (!this.isChoiceSelected(choice)) {
            // Choice is not already selected => SELECT IT
            if (!this.question.multiple) {
                // Only one response authorized for this question, so we need to empty the response array
                this.questionPaper.answer.splice(0, this.questionPaper.answer.length);
            }

            // Add new choice into the response array
            this.questionPaper.answer.push(choice.id);
        } else {
            // Choice is already selected => UNSELECT IT
            var choicePosition = this.questionPaper.answer.indexOf(choice.id);

            this.questionPaper.answer.splice(choicePosition, 1);
        }
    }
};

/**
 * Check if choice is valid or not
 * @TODO expected answers not checked by user should not be shown as bad result except after the last step try
 * @param {Object} choice
 * @returns {Number}
 *  0 = nothing, (unexpected answer not checked by user)
 *  1 = valid, (expected answer checked by user)
 *  2 = false (unexpected answer checked by user OR valid answer not checked by user)
 */
ChoiceQuestionCtrl.prototype.isChoiceValid = function isChoiceValid(choice) {
    var isValid = 0;

    // if there is any answer in student data
    if (this.questionPaper.answer && this.questionPaper.answer.length > 0 && this.question.solutions) {
        if (this.isChoiceSelected(choice)) {
            // The choice has been selected by User => check if it's a right response or not
            var choiceSolution = this.getChoiceSolution(choice);

            if (choiceSolution.rightResponse) {
                // The current choice is part of the right response => User choice is Valid
                isValid = 1;
            } else {
                // The current choice is not part of the right response => User choice is Invalid
                isValid = 2;
            }
        }
    }

    return isValid;
};

/**
 * For unique choice
 */
ChoiceQuestionCtrl.prototype.isUniqueChoiceValid = function isUniqueChoiceValid() {
    var valid = false;
    if (!this.question.multiple) {
        // Loop over all choice to see if the correct one has been selected
        for (var i = 0; i < this.choices.length; i++) {
            if (1 === this.isChoiceValid(this.choices[i])) {
                valid = true;
                break;
            }
        }
    }

    return valid;
};

/**
 * Check if a choice has been selected by User
 * @param choice
 */
ChoiceQuestionCtrl.prototype.isChoiceSelected = function isChoiceSelected(choice) {
    return this.questionPaper.answer && -1 !== this.questionPaper.answer.indexOf(choice.id);
};

/**
 * Get the solution for a choice
 * @param {Object} choice
 */
ChoiceQuestionCtrl.prototype.getChoiceSolution = function getChoiceSolution(choice) {
    var solution = null;

    if (this.question.solutions) {
        // Solutions have been loaded
        for (var i = 0; i < this.question.solutions.length; i++) {
            if (choice.id === this.question.solutions[i].id) {
                solution = this.question.solutions[i];
                break; // Stop searching
            }
        }
    }

    return solution;
};

/**
 * Get the Feedback of a Choice
 * @param   {Object} choice
 * @returns {string}
 */
ChoiceQuestionCtrl.prototype.getChoiceFeedback = function getChoiceFeedback(choice) {
    if (this.isChoiceSelected(choice)) {
        var solution = this.getChoiceSolution(choice);
        if (solution) {
            return solution.feedback;
        }
    }
};

/**
 * Validate Holes when feedback are shown to know which answers are valid
 */
ChoiceQuestionCtrl.prototype.onFeedbackShow = function onFeedbackShow() {
    // Validate holes
    if (this.question.solutions) {
        for (var i = 0; i < this.choices.length; i++) {
            this.choices[i].valid = this.isChoiceValid(this.choices[i]);
        }
    }
};

// Register controller into AngularJS
angular
    .module('Question')
    .controller('ChoiceQuestionCtrl', ChoiceQuestionCtrl);
