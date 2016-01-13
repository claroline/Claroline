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
            /*
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
            });*/

            this.init = function (question, paper) {
                this.question = question;
                this.paper = paper;
                
                this.setAnswer(this.question.text);
                
                var id_question;
                var id_answer;
                var currElem;
                var currElemGParent;
                
                console.log("blablablahahahaha");
                
                console.log(this.paper.questions);
                console.log(this.question);
                
                for (var i=0; i<this.paper.questions.length; i++) {
                    if (this.question.id.toString() === this.paper.questions[i].id) {
                        var answers = this.paper.questions[i].answer;
                        var elements = document.getElementsByClassName('blank');
                        
                        /**
                         * The following loop fills the fields in the "Your answers" column
                         * with the user's answers
                         */
                        for (var j=0; j<elements.length; j++) {
                            currElem = elements[j];
                            currElemGParent = elements[j].parentNode.parentNode;
                            if (currElemGParent.getAttribute('id') === "answer_" + this.question.id) {
                                id_answer = elements[j].getAttribute("id");
                                id_question = this.question.id;
                                Object.keys(answers).map(function(key){
                                    if (key === id_answer) {
                                        $('#answer_' + id_question).find('#'+id_answer).val(answers[key]);
                                    }
                                });
                            }
                            
                            if (currElemGParent.getAttribute('id') === "solution_" + this.question.id) {
                                id_answer = elements[j].getAttribute('id');
                                /*
                                 * A implémenter après discussion ujm
                                 */
                            }
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