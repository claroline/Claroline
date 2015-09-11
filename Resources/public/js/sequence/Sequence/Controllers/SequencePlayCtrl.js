(function () {
    'use strict';

    angular.module('Sequence').controller('SequencePlayCtrl', [
        '$scope',
        'SequenceService',
        'CommonService',
        function ($scope, SequenceService, CommonService) {

            this.sequence = {};
            this.currentStep = {};
            this.steps = {};
            this.nbAttempts = 1;
            this.studentResults = Array();
            
            this.setSequence = function (sequence) {
                this.sequence = sequence;
                CommonService.setSequence(sequence);
            };

            this.getSequence = function () {
                return CommonService.getSequence();
            };

            /**
             * Check if the question has meta like created / licence / description...
             */
            this.questionHasOtherMeta = function () {
                return CommonService.objectHasOtherMeta(this.sequence);
            };
            
            this.setSteps = function(steps){
                this.steps = steps;
            };
            
            this.getSteps = function () {
                return this.steps;
            };

            /**
             * 
             * @returns {undefined}
             */
            this.getNbStep = function () {
                return this.steps.length;
            };

            this.setCurrentStep = function (index) {
                this.currentStep = this.steps[index];
            };
            
            this.getCurrentStep = function () {
                return this.currentStep;
            };
            
            /**
             * use for display 
             * @returns {Number|SequencePlayCtrl_L7@pro;sequence@pro;steps@call;indexOf}
             */
            this.getCurrentStepIndex = function () {
                var index = this.steps.indexOf(this.currentStep);
                console.log('current index ' + index);
                return  index + 1;
            };

            this.setNbAttempts = function (nb) {
                this.nbAttempts = nb;
            };

            this.getNbAttempts = function () {
                return this.nbAttempts;
            };
            
            /**
             * Validate the current step after confirm
             * If next step get next step
             * @returns {undefined}
             */
            this.validateStep = function () {
                var data = CommonService.getStudentData();
                console.log('student data are below');
                console.log(data);
                var stepResult = {
                    step : this.currentStep,
                    answers: this.data
                };
                // save step results
                this.studentResults.push(stepResult);
                // go to next step
                var currentStepIndex = this.steps.indexOf(this.currentStep);
                var length = this.steps.length;
                var newIndex = currentStepIndex + 1;
                if(newIndex < length){
                    this.setCurrentStep(newIndex); 
                }
                else{
                    console.log('you reached the end of the exercise you will be redirected to correction page');
                    this.endSequence();
                }
                
            };
            
            this.endSequence = function(){
                console.log('TODO');
            };
        }
    ]);
})();