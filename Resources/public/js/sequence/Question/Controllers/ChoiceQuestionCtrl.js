(function () {
    'use strict';

    angular.module('Question').controller('ChoiceQuestionCtrl', [
        function () {
            this.question = {};

            this.isCollapsed = false;


            this.setQuestion = function (question) {
                this.question = question;
            };

            this.getQuestion = function () {
                return this.question;
            };

            /**
             * Check if the choice is in solution collection 
             * if its true the choice is a valid choice so we have to check the radio/checkbox
             * @param {string} choiceId
             * @returns {question.solution}
             */
            this.getSolution = function (choiceId) {
                for (var i = 0; i < this.question.solutions.length; i++) {
                    if (this.question.solutions[i].id === choiceId) {
                        return this.question.solutions[i];
                    }
                }
            };

            /**
             * Check if the question has meta like created / licence, description...
             * @returns {boolean}
             */
            this.questionHasOtherMeta = function () {
                return this.question.meta.licence || this.question.meta.created || this.question.meta.modified || this.question.meta.description;
            };
        }
    ]);
})();