(function () {
    'use strict';

    angular.module('Paper').controller('PaperCtrl', [
        'CommonService',
        function (CommonService) {
            this.currentQuestion = {};
            this.currentAnswer = {};
            this.paper = {};
            this.sequence = {};
            this.isCollapsed = false;
            this.note = 0;

            this.init = function (sequence, paper) {
                this.paper = paper;
                this.sequence = sequence;
                console.log('paper');
                console.log(this.paper);
                console.log('sequence');
                console.log(this.sequence);
                //this.note = this.setNote();
            };

            /**
             * Set the student note /20
             * 1 - Get the total points on the entire exercise
             * 2 - For each student answer get the points and subtract penalties
             * 3 - Calculate the final note ( studentPoints * 20 / totalPoints )
             * @returns number
             */
            this.setNote = function () {
                var nbAnswers = this.answers.length;
                var score = 0;
                var totalPoints = 0;
                var studentPoints = 0;
                for (var i = 0; i < nbAnswers; i++) {
                    // anwser is an array [questionId] => true/false if question is multiple or a string if not
                    var answer = this.answers[i].answer;
                    var penalty = this.answers[i].penalty;
                    var question = this.answers[i].question;
                    //var solutions = question.solutions;
                    totalPoints += this.getSolutionsPoints(question.solutions);
                    studentPoints += this.getStudentQuestionScore(question, answer, penalty);
                }
                score = studentPoints * 20 / totalPoints;
                return score > 0 ? score : 0;
            };

            this.getStudentQuestionScore = function (question, answer, penalty) {
                var score = 0;
                // usefull on choice questions but for the others ???
                var isMultiple = question.multiple;
                if (isMultiple) {
                    for (var i = 0; i < question.solutions.length; i++) {
                        var questionId = parseInt(question.solutions[i].id);
                        if(answer[questionId] === true){
                            score += question.solutions[i].score;
                        }
                        
                    }
                }
                else{
                    var questionId = parseInt(question.solutions[0].id);
                    if(questionId === parseInt(answer)){
                        score += question.solutions[0].score;
                    }
                }
                score -= penalty;
                console.log('crac ' + score.toString());
                return score;

            };

            /**
             * get the points available in a question solutions
             * @param solutions
             * @returns number
             */
            this.getSolutionsPoints = function (solutions) {
                var points = 0;
                for (var i = 0; i < solutions.length; i++) {
                    points += solutions[i].score;
                }
                return points;
            };

            this.setCurrentQuestion = function (question) {
                this.currentQuestion = question;
            };

            this.setCurrentAnswer = function (answer) {
                this.currentAnswer = answer;
            };

            this.goTo = function (index) {

            };

            this.toggleDetails = function (id) {
                console.log('toggle ' + id);
                $('#' + id).toggle();
            };

            this.getChoiceSimpleType = function (choice) {
                return CommonService.getObjectSimpleType(choice);
            };

            this.getStudentAnswer = function (choice, item) {
                if (item.multiple) {
                    var isSelected = item.answer[choice.id];
                    return isSelected;
                }
                else {
                    var id = parseInt(item.answer);
                    return id === choice.id;
                }
            };

            this.generateUrl = function (witch) {
                return CommonService.generateUrl(witch);
            };
        }
    ]);
})();