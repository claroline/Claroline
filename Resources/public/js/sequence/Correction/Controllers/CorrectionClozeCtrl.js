/**
 * Paper details directive controller
 * 
 */
(function () {
    'use strict';

    angular.module('Correction').controller('CorrectionClozeCtrl', [
        'CommonService',
        'CorrectionService',
        '$timeout',
        function (CommonService, CorrectionService, $timeout) {

            this.question = {};
            this.paper = {};
            this.answer = "";
                    
            $timeout(function () {
                var inputs = document.getElementsByClassName('blank');
                for (var i=0; i<inputs.length; i++) {
                    inputs[i].setAttribute("disabled", true);
                }
            });

            this.init = function (question, paper) {
                this.question = question;
                this.paper = paper;
                
                this.setAnswer(this.question.text);
                
                for (var i=0; i<this.paper.questions; i++) {
                    if (this.question.id === this.paper.questions[i].id) {
                        console.log(this.paper.questions[i].answer);
                    }
                }
                
                //------------------ Changer format d'enregistrement BDD? --------------------//
                var answers = $.parseJSON(this.paper.questions[0].answer);;
                console.log(answers);
                
                for (var i=0; i<answers.length; i++) {
                    console.log(answers[i]);
                }
                
                console.log(this.question);
                console.log(this.paper);
            };
            
            this.getAnswer = function () {
                return this.answer;
            };
            
            this.setAnswer = function (answer) {
                this.answer = answer;
            };

        }
    ]);
})();