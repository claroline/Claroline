/**
 * Paper details directive controller
 * 
 */
(function () {
    'use strict';

    angular.module('Correction').controller('CorrectionQCMCtrl', [
        'CommonService',
        'CorrectionService',
        function (CommonService, CorrectionService) {


            this.question = {};
            this.paper = {};

            this.init = function (question, paper) {
                this.question = question;
                this.paper = paper;
            };

            this.getChoiceSimpleType = function (choice) {
                return CommonService.getObjectSimpleType(choice);
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
             * Check if the student choosed the current choice
             * @param {type} question
             * @param {type} choice current choice in the table
             * @returns {Boolean}
             */
            this.isStudentChoice = function (question, choice) {
                for (var i = 0; i < this.paper.questions.length; i++) {
                    if (this.paper.questions[i].id === question.id.toString() && this.paper.questions[i].answer) {
                        for (var j = 0; j < this.paper.questions[i].answer.length; j++) {
                            if (this.paper.questions[i].answer[j] === choice.id) {
                                return true;
                            }
                        }
                    }
                }
                return false;
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
})();