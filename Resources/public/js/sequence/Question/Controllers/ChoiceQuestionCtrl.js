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
            
            this.getSolution = function(choiceId){
                for(var i = 0; i < this.question.solutions.length; i++){
                    if(this.question.solutions[i].id === choiceId){
                        return this.question.solutions[i];
                    }
                }
            };
        }
    ]);
})();