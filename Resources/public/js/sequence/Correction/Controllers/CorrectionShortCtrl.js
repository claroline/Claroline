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
                
                for (var i=0; i<this.paper.questions.length; i++) {
                    if (question.id.toString() === this.paper.questions[i].id) {
                        this.answer = this.paper.questions[i].answer[0];
                    }
                }
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