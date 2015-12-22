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
                
                var answers_fields = document.getElementsByName('users_answer');
                var solutions_fields = document.getElementsByName('teachers_solution');
                
                for (var i=0; i<answers_fields.length; i++) {
                    var answers_inputs = answers_fields[i].getElementsByTagName('input');
                    var solutions_inputs = solutions_fields[i].getElementsByTagName('input');
                    
                    for (var j=0; j<answers_inputs.length; j++) {
                        if (answers_inputs[j].value === solutions_inputs[j].value) {
                            answers_inputs[j].style.color = "#2289b5";
                        }
                        else {
                            answers_inputs[j].style.color = "#FC0204";
                        }
                        solutions_inputs[j].style.color = "black";
                    }
                }
            });

            this.init = function (question, paper) {
                this.question = question;
                this.paper = paper;
                
                this.setAnswer(this.question.text);
                
                for (var i=0; i<this.paper.questions.length; i++) {
                    if (question.id.toString() === this.paper.questions[i].id) {
                        var answers = $.parseJSON(this.paper.questions[i].answer);
                        
                        // loop to update the value in the string, since we don't have access to the HTML object
                        var l=0;
                        var k=0;
                        while (k<this.answer.length) {
                            if (this.answer.substr(k,4) === "id=\"") {
                                k = k+4;
                                l=k;
                                while (this.answer.substr(k,1) !== "\"") {
                                    k++;
                                }
                                
                                for (var j=0; j<answers.length; j++) {
                                    if (answers[j].id === this.answer.substr(l,k-l)) {
                                        while (this.answer.substr(k,7) !== "value=\"") {
                                            k++;
                                        }
                                        
                                        this.answer = this.answer.substr(0,k+7) + answers[j].answer + this.answer.substr(k+7,this.answer.length);
                                    }
                                }
                            }
                            k++;
                        }
                
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