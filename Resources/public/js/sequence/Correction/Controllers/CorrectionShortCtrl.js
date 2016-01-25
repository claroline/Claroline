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
            this.score = 0;

            this.init = function (question, paper) {
                this.question = question;
                this.paper = paper;
                console.log(this.question);
                console.log(this.paper);
                
                for (var i=0; i<this.paper.questions.length; i++) {
                    if (question.id.toString() === this.paper.questions[i].id) {
                        this.answer = this.paper.questions[i].answer;
                        this.score = this.paper.questions[i].score;
                    }
                }
            };
            
            this.getAnswer = function () {
                return this.answer;
            };
            
            this.setAnswer = function (answer) {
                this.answer = answer;
            };
            
            this.showNotationInput = function () {
                $("#note_question").hide();
                $("#score_p").show();
            };
            
            this.saveNote = function () {
                var note = $("#score_given").val();
                
                CorrectionService.saveScore(this.question.id, this.paper.id, note);
                
                this.score = note;
            };

        }
    ]);
})();