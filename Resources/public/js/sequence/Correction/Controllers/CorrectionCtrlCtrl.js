/**
 * Paper details directive controller
 * 
 */
(function () {
    'use strict';

    angular.module('Correction').controller('CorrectionCtrl', [
        'CommonService',
        'CorrectionService',
        'PapersService',
        function (CommonService, CorrectionService, PapersService) {
            
            this.paper = {};
            this.sequence = {};
            this.questions = {};
            this.user = {};

            this.isCollapsed = false;
            this.globalNote = 0.0;

            this.init = function (paper, questions, sequence, user) {
                this.paper = paper;
                this.sequence = sequence;
                this.questions = questions;
                this.user = user;
                this.globalNote = PapersService.getPaperScore(this.paper, this.questions);
            };

            this.toggleDetails = function (id) {
                $('#question-body-' + id).toggle();

                if (angular.element('#question-toggle-' + id).hasClass('fa-chevron-down')) {
                    angular.element('#question-toggle-' + id).removeClass('fa-chevron-down').addClass('fa-chevron-right');
                }
                else if (angular.element('#question-toggle-' + id).hasClass('fa-chevron-right')) {
                    angular.element('#question-toggle-' + id).removeClass('fa-chevron-right').addClass('fa-chevron-down');
                }
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
             * Checks if for a given question student used at least one Hint
             * @param {type} question
             * @returns {Boolean}
             */
            this.oneOrMoreHintsAreUsed = function (question) {
                for (var i = 0; i < this.paper.questions.length; i++) {
                    if (this.paper.questions[i].id === question.id.toString() && this.paper.questions[i].hints.length > 0) {
                        return true;
                    }
                }
                return false;
            };

            /**
             * when rendering each question hint check if student used it
             * @param {type} question
             * @param {type} hint
             * @returns {undefined}
             */
            this.hintIsUsed = function (question, hint) {
                for (var i = 0; i < this.paper.questions.length; i++) {
                    if (this.paper.questions[i].id === question.id.toString()) {
                        for (var j = 0; j < this.paper.questions[i].hints.length; j++) {
                            if (this.paper.questions[i].hints[j] === hint.id) {
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
            
            /**
             * Calculate the score for each question
             * @param {type} question
             * @returns {Number}
             */
            this.getQuestionScore = function (question){
                var score = 0.0;
                score = PapersService.getQuestionScore(question, this.paper);
                return score;
            };

            this.generateUrl = function (witch, _id) {
                return CommonService.generateUrl(witch, _id);
            };
        }
    ]);
})();