(function () {
    'use strict';

    angular.module('Question').controller('ChoiceQuestionCtrl', [
        'CommonService',
        function (CommonService) {
            this.question = {};
            // keep answer
            this.answer = {};
            this.penalty = 0;


            this.setQuestion = function (question) {
                this.question = question;
            };

            this.getQuestion = function () {
                return this.question;
            };

            /**
             * Check if the question has meta like created / licence, description...
             * @returns {boolean}
             */
            this.questionHasOtherMeta = function () {
                return CommonService.objectHasOtherMeta(this.question);
            };

            /**
             * 
             * @param {object} object a javascript object with type property
             * @returns {string}
             */
            this.getChoiceSimpleType = function (object) {
                return CommonService.getObjectSimpleType(object);
            };

            /**
             * Used for multiple choice question
             * The way we get checked boxes in the model is a bit odd so we need to initiate values to false...
             */
            this.initAnswers = function () {
                for (var i = 0; i < this.question.choices.length; i++) {
                    this.answer[this.question.choices[i].id] = false;
                }
                // init the answer objects
                this.updateQuestionChoices();
            };

            /**
             * in case of random order in choices need to create a random ordering
             * @returns {undefined}
             */
            this.initChoicesOrder = function () {
                var meta = CommonService.getSequenceMeta();
                if (meta.random) {
                    this.shuffleChoices(this.question.choices);
                }
            };

            /**
             * private method
             * shuffle array elements
             * @param {type} array
             * @returns {@var;temporaryValue}
             */
            this.shuffleChoices = function (array) {
                var currentIndex = array.length, temporaryValue, randomIndex;
                // While there remain elements to shuffle...
                while (0 !== currentIndex) {
                    // Pick a remaining element...
                    randomIndex = Math.floor(Math.random() * currentIndex);
                    currentIndex -= 1;

                    // And swap it with the current element.
                    temporaryValue = array[currentIndex];
                    array[currentIndex] = array[randomIndex];
                    array[randomIndex] = temporaryValue;
                }
                return array;
            };

            this.showHint = function (hint) {
                angular.element('#' + hint.id).next().show();
                angular.element('#' + hint.id).hide();
                // update penalty
                this.penalty += hint.penalty;
            };

            /**
             * Called on each checkbox / radiobutton click
             * We need to share those informations with parent controllers
             * For that purpose we use a shared service
             */
            this.updateQuestionChoices = function () {
                CommonService.setCurrentQuestionAndAnswer(this.answer, this.question, this.penalty);
            };
        }
    ]);
})();