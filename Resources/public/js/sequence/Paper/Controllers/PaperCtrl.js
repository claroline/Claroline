(function () {
    'use strict';

    angular.module('Paper').controller('PaperCtrl', [
        'CommonService',
        function (CommonService) {
            this.currentQuestion = {};
            this.currentAnswer = {};
            this.paper = {};
            this.isCollapsed = false;
            this.note = 0;
            this.context = "";
            this.exoId;

            this.init = function (paper, context, exoId) {
                this.paper = paper;
                this.context = context;
                this.exoId = exoId;
                this.note = CommonService.getPaperScore(this.paper);
            };

            this.setCurrentQuestion = function (question) {
                this.currentQuestion = question;
            };

            this.setCurrentAnswer = function (answer) {
                this.currentAnswer = answer;
            };

            this.toggleDetails = function (id) {
                $('#question-body-' + id).toggle();
                
                if(angular.element('#question-toggle-' + id).hasClass('fa-chevron-down')){
                    //id="question-toggle-{{question.id}}"
                    angular.element('#question-toggle-' + id).removeClass('fa-chevron-down').addClass('fa-chevron-right');
                }
                else if(angular.element('#question-toggle-' + id).hasClass('fa-chevron-right')){
                    angular.element('#question-toggle-' + id).removeClass('fa-chevron-right').addClass('fa-chevron-down');
                }
            };

            this.getChoiceSimpleType = function (choice) {
                return CommonService.getObjectSimpleType(choice);
            };
            
            /**
             * Check if the choice is an expected one
             * @param {type} question
             * @param {type} choice
             * @returns {Boolean}
             */
            this.isChoiceValid = function (question, choice){
                for(var i = 0 ; i < question.solution.length; i++){
                    if(question.solution[i] === choice.id){
                        return true;
                    }
                }
                return false;
            };
            
            /**
             * Check if the student choosed this proposal
             * @param {type} question 
             * @param {type} choice
             * @returns {Boolean}
             */
            this.isStudentChoice = function (question, choice){
                for(var i = 0 ; i < question.answer.length; i++){
                    if(question.answer[i] === choice.id){
                        return true;
                    }
                }
                return false;
            };

            this.generateUrl = function (witch, _id) {
                return CommonService.generateUrl(witch, _id);
            };
        }
    ]);
})();