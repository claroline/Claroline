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
        }
    ]);
})();