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
                    
                    var answers_select = answers_fields[i].getElementsByTagName('select');
                    var solutions_select = solutions_fields[i].getElementsByTagName('select');
                    
                    for (var j=0; j<answers_select.length; j++) {
                        if (answers_select[j].options[answers_select[j].selectedIndex].innerHTML === solutions_select[j].options[solutions_select[j].selectedIndex].value) {
                            answers_select[j].style.color = "#2289b5";
                        }
                        else {
                            answers_select[j].style.color = "#FC0204";
                        }
                        solutions_select[j].style.color = "black";
                    }
                    
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
                
                var type_answer = "";
                
                for (var i=0; i<this.paper.questions.length; i++) {
                    if (question.id.toString() === this.paper.questions[i].id) {
                        var answers = $.parseJSON(this.paper.questions[i].answer);
                        
                        // loop to update the value in the string, since we don't have access to the HTML object
                        var l=0;
                        var k=0;
                        var m=0;
                        while (k<this.answer.length) {
                            if (this.answer.substr(k,7) === "<select") {
                                type_answer = "select";
                            }
                            else if (this.answer.substr(k,6) === "<input") {
                                type_answer = "input";
                            }
                            
                            if (this.answer.substr(k,4) === "id=\"" && type_answer === "input") {
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
                            else if (type_answer === "select" && this.answer.substr(k,4) === "id=\"") {
                                k = k+4;
                                l=k;
                                while (this.answer.substr(k,1) !== "\"") {
                                    k++;
                                }
                                
                                //k-l = id answer
                                
                                for (var j=0; j<answers.length; j++) {
                                    if (answers[j].id === this.answer.substr(l,k-l)) {
                                        
                                        var good_value = false;
                                        while (!good_value) {
                                            while (this.answer.substr(k,7) !== "value=\"") {
                                                k++;
                                            }
                                            k=k+7;
                                            m=k;
                                            while (this.answer.substr(k,1) !== "\"") {
                                                k++;
                                            }
                                            if (answers[j].answer === this.answer.substr(m,k-m)) {
                                                this.answer = this.answer.substr(0,k+1) + " selected=\"selected\" " + this.answer.substr(k+1,this.answer.length);
                                                good_value = true;
                                            }
                                        }
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