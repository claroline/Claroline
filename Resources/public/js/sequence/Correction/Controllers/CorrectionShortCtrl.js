/**
 * Paper details directive controller
 * 
 */
(function () {
    'use strict';

    angular.module('Correction').controller('CorrectionShortCtrl', [
        'CommonService',
        'CorrectionService',
        function (CommonService, CorrectionService) {

            this.question = {};
            this.paper = {};
            this.answer = "";

            this.init = function (question, paper) {
                this.question = question;
                this.paper = paper;
                
                console.log(this.question);
                console.log(this.paper);
                
                //this.answer;
                
                for (var i=0; i<this.paper.questions.length; i++) {
                    if (question.id.toString() === this.paper.questions[i].id) {
                        this.answer = this.paper.questions[i].answer[0];
                    }
                }
                
                console.log(this.answer);
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