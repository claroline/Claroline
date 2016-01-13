/**
 * Paper details directive controller
 * 
 */
(function () {
    'use strict';

    angular.module('Correction').controller('CorrectionCtrl', [
        'CommonService',
        function (CommonService) {

            this.paper = {};
            this.exercise = {};
            this.questions = {};
            this.user = {};

            this.context = '';

            this.isCollapsed = false;
            this.globalNote = 0.0;
            this.displayRetryExerciseLink = false;

            this.init = function (paper, questions, exercise, user) {
                this.exercise = exercise;
                this.paper = paper;
                this.user = user;

                this.questions = questions;
                this.globalNote = 0;//CommonService.getPaperScore(this.paper, this.questions);
                this.showHideRetryLink();
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
             * compute the score for each question
             * NOT IMPLEMENTED
             * @param {type} question
             * @returns {Number}
             */
            this.getQuestionScore = function (question) {
                var score = 0.0;
                //score = PapersService.getQuestionScore(question, this.paper);
                return score;
            };

            /**
             * Checks if current user can replay the exercise
             * Basicaly if user is admin he will always have access to the button
             * @returns {Boolean}
             */
            this.showHideRetryLink = function () {

                if (this.user.admin || this.exercise.meta.maxAttempts === 0) {
                    this.displayRetryExerciseLink = true;
                } else {
                    var promise = CommonService.countFinishedPaper(this.exercise.id);
                    promise.then(function (result) {
                        this.displayRetryExerciseLink = result < this.exercise.meta.maxAttempts;
                    }.bind(this));
                }
            };

            this.generateUrl = function (witch, _id) {
                return CommonService.generateUrl(witch, _id);
            };
        }
    ]);
})();