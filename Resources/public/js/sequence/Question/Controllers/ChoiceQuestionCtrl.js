(function () {
    'use strict';

    angular.module('Question').controller('ChoiceQuestionCtrl', [
        'CommonService',
        function (CommonService) {
            this.question = {};
            
            // keep answers
            this.answers = {};


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
                return CommonService.objectHasOtherMeta(this.question);
                //return this.question.meta.licence || this.question.meta.created || this.question.meta.modified || (this.question.meta.description && this.question.meta.description !== '');
            };

            /**
             * 
             * @param {type} object a javascript object with type property
             * @returns {undefined}
             */
            this.getChoiceSimpleType = function (object) {                
                return CommonService.getObjectSimpleType(object);
            };
            
            this.handleUniqueAnswer = function (id){
                
                console.log('choosen choice with id : ' + id);  
            };
            
            this.initAnswers = function(){
                for(var i = 0; i < this.question.choices.length; i++){
                    this.answers[this.question.choices[i].id] = false;
                }
            };
            

            
            this.test = function (){
                console.log('called');
                console.log(this.answers);
            };
            
            this.check = function (){
                console.log('called');
                console.log(this.answers);
            }
        }
    ]);
})();