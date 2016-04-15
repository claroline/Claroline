/**
 * Paper details directive controller
 * 
 */
angular.module('Correction').controller('CorrectionQCMCtrl', [
    'CommonService',
    function (CommonService) {
        this.question = {};
        this.paper = {};

        this.init = function (question, paper) {
            this.question = question;
            this.paper = paper;
        };

        /**
         * Check if the choice is an expected one
         * in question.solutions, all choices are present even bad ones
         * so how are we supposed to know wich one is a good one ? based on score > 0 ?
         * @param {type} question
         * @param {type} choice
         * @returns {Boolean}
         */
        this.isChoiceValid = function (question, choice) {
            for (var i = 0; i < question.solutions.length; i++) {
                if (question.solutions[i].id === choice.id && question.solutions[i].score > 0) {
                    return true;
                }
            }
            return false;
        };

        /**
         * Check if choice is valid or not
         * @TODO should we mark as good answer an unexpected anwser not checked by student ?
         * @param {Object} current question
         * @param {Object} current choice
         * @returns {Number}
         *  0 = nothing, (unexpected answer not checked by user)
         *  1 = valid, (expected answer checked by user)
         *  2 = false (unexpected answer checked by user OR valid answer not checked by user)
         */
        this.isStudentChoice = function (question, choice) {
            var isValid = 0;
            // loop over all paper questions
            for (var i = 0; i < this.paper.questions.length; i++) {
                // get the paper question corresponding to current question in table
                if (this.paper.questions[i].id === question.id.toString()) {
                    var solutions = question.solutions;
                    var currentQuestion = this.paper.questions[i];
                    // student anwsers for this question exist
                    if (currentQuestion.answer) {
                        for (var j = 0; j < solutions.length; j++) {
                            // search for valid solutions (score > 0)
                            if (solutions[j].id === choice.id && solutions[j].score > 0) {
                                var found = false;
                                // search for expected answer checked by student
                                for (var k = 0; k < currentQuestion.answer.length; k++) {
                                    if (currentQuestion.answer[k] === choice.id) {
                                        isValid = 1;
                                        found = true;
                                    }
                                }
                                // expected answer not checked by student
                                if (!found) {
                                    isValid = 0;
                                }
                            } else if (solutions[j].id === choice.id && solutions[j].score <= 0) {
                                // search for unexpected answer checked by student
                                for (var k = 0; k < currentQuestion.answer.length; k++) {
                                    if (currentQuestion.answer[k] === choice.id) {
                                        isValid = 2;
                                    }
                                }
                            }
                        }
                    } else {
                        for (var j = 0; j < this.solutions.length; j++) {
                            if (this.solutions[j].id === choice.id && this.solutions[j].score > 0) {
                                // expected answer not checked by student
                                isValid = 0;
                            }
                        }
                    }
                }
            }
            return isValid;
        };

        /**
         * While rendering each question choices and answers get choice feedback if exists
         * @param {type} question
         * @param {type} choice
         * @returns {String} choice feedback
         */
        this.getCurrentChoiceFeedBack = function (question, choice) {
            for (var i = 0; i < question.solutions.length; i++) {
                if (question.solutions[i].id === choice.id) {
                    return question.solutions[i].feedback !== '' && question.solutions[i].feedback !== undefined ? question.solutions[i].feedback : '-';
                }
            }
        };
    }
]);
