/**
 * Paper details directive controller
 * 
 */
(function () {
    'use strict';

    angular.module('Correction').controller('CorrectionMatchCtrl', [
        'CommonService',
        'CorrectionService',
        function (CommonService, CorrectionService) {


            this.question = {};
            this.paper = {};

            this.studentAnswers = [];
            this.correctAnswers = [];
            this.studentErrors = []; // array of proposals ids;

            this.init = function (question, paper) {
                this.question = question;
                this.paper = paper;
                /*console.log('this.paper');
                console.log(this.paper);
                console.log('this.question');
                console.log(this.question);*/
                this.setCorrectAnswers();
                this.setStudentAnswers();
            };


            /**
             * Checks the validity of proposals associated with one label
             * @param {type} label
             * @returns {Boolean}
             */
            this.checkAnswerValidity = function (label) {
                var errors = [];
                var studentProposalsForLabel = this.getStudentAnswers(label);
                var correctProposalsForLabel = this.getCorrectAnswers(label);                
                // CASE 1 : no answers but expected ones
                if(studentProposalsForLabel.length === 0 && correctProposalsForLabel.length > 0){
                    return false;
                }                
                // CASE 2 : answers from student, check that answers are in expected ones
                // search for corresponding student proposal in solution
                for (var i = 0; i < studentProposalsForLabel.length; i++) {
                    var found = false;
                    var searched = studentProposalsForLabel[i].id;
                    // answer not in proposals
                    for (var j = 0; j < correctProposalsForLabel.length; j++) {
                        if(correctProposalsForLabel[j].id === searched){
                            found = true;
                            break;
                        }
                    }
                    if(!found){
                        errors.push(searched);
                    }                    
                }
                // keep wrong assocations for later use (apply line-through style to unexpected element in student answers)
                this.studentErrors = errors;
                
                // CASE 3 : missing answers from student
                if(studentProposalsForLabel.length < correctProposalsForLabel.length){
                    return false;
                }               
                
                return errors.length === 0;
            };

            this.setStudentAnswers = function () {
                for (var i = 0; i < this.question.secondSet.length; i++) {
                    var label = this.question.secondSet[i];
                    var studentProposals = this.getStudentProposalsForLabel(label);
                    var item = {
                        label: label,
                        proposals: studentProposals[0]
                    };
                    this.studentAnswers.push(item);
                }
            };

            this.getStudentAnswers = function (label) {
                for (var i = 0; i < this.studentAnswers.length; i++) {
                    if (label.id === this.studentAnswers[i].label.id) {
                        return this.studentAnswers[i].proposals;
                    }
                }
            };

            this.setCorrectAnswers = function () {
                for (var i = 0; i < this.question.secondSet.length; i++) {
                    var label = this.question.secondSet[i];
                    var correctAnswers = this.getSolutionProposalsForLabel(label);
                    var item = {
                        label: label,
                        proposals: correctAnswers
                    };
                    this.correctAnswers.push(item);
                }
            };

            this.getCorrectAnswers = function (label) {
                for (var i = 0; i < this.correctAnswers.length; i++) {
                    if (label.id === this.correctAnswers[i].label.id) {
                        return this.correctAnswers[i].proposals;
                    }
                }
            };


            this.getStudentProposalsForLabel = function (label) {
                var results = [];
                for (var i = 0; i < this.paper.questions.length; i++) {
                    if (this.paper.questions[i].id === this.question.id.toString()) {
                        var answers = this.paper.questions[i].answer;
                        var formatted = this.formatStudentAnswers(answers, label);
                        results.push(formatted);
                    }
                }
                return results;
            };

            this.getSolutionProposalsForLabel = function (label) {
                var results = [];
                for (var i = 0; i < this.question.solutions.length; i++) {
                    if (this.question.solutions[i].secondId === label.id) {
                        var proposal = this.getProposalFromId(this.question.solutions[i].firstId);
                        results.push(proposal);
                    }
                }
                return results;
            };

            this.formatStudentAnswers = function (answers, label) {
                var formatted = [];
                for (var j = 0; j < answers.length; j++) {
                    var set = answers[j].split(',');
                    var proposalId = set[0];
                    var associatedLabelId = set[1];
                    if (associatedLabelId === label.id) {
                        var proposal = this.getProposalFromId(proposalId);
                        formatted.push(proposal);
                    }
                }
                return formatted;
            };

            this.getProposalFromId = function (id) {
                for (var k = 0; k < this.question.firstSet.length; k++) {
                    if (this.question.firstSet[k].id === id) {
                        return this.question.firstSet[k];
                    }
                }
            };

            /**
             * Checks that a proposal is in the studentErrors array
             * if found we apply line-through style on the label to ease error visualisations.
             * @param {type} proposalId
             * @returns {Boolean} true if proposal is valid else false
             */
            this.checkProposalValidity = function (proposalId) {
                for (var i = 0; i < this.studentErrors.length; i++) {
                    if (this.studentErrors[i] === proposalId) {
                        return false;
                    }
                }
                return true;
            };

            /**
             * While rendering each question label and answers get feedback if exists
             * @param {type} question
             * @param {type} choice
             * @returns {String} choice feedback
             */
            this.getCurrentItemFeedBack = function (label) {
                for (var i = 0; i < this.question.solutions.length; i++) {
                    if (this.question.solutions[i].secondId === label.id) {
                        return this.question.solutions[i].feedback !== '' && this.question.solutions[i].feedback !== undefined ? this.question.solutions[i].feedback : '-';
                    }
                }
            };


        }
    ]);
})();