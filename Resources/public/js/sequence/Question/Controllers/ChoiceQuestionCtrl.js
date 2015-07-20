(function () {
    'use strict';

    angular.module('Question').controller('ChoiceQuestionCtrl', [
        function () {
            this.question = {};
            /**
             * test
             */
            this.test = function () {
                console.log('test');
            };

            this.setQuestion = function (question) {
                this.question = question;
            };

            this.getQuestion = function () {
                return this.question;
            };
        }
    ]);
})();