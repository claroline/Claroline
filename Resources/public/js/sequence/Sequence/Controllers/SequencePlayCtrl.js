(function () {
    'use strict';

    angular.module('Sequence').controller('SequencePlayCtrl', [
        'SequenceService',
        'CommonService',
        function (SequenceService, CommonService) {

            this.sequence = {};
            this.currentStep = {};
            this.steps = {};
            this.nbAttempts = 1;
            this.studentResults = Array();
            this.isFinished = false;
            this.isLastStep = false;
            
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
             * @returns number of steps in the collection
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
             * @returns the current step index (+1 for human readability)
             */
            this.getCurrentStepIndex = function () {
                var index = this.steps.indexOf(this.currentStep);
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
             * If next step get it (also save student progression)
             * Else terminate sequence (also save student paper)
             */
            this.validateStep = function () {
                var data = CommonService.getStudentData();
                console.log('student data are below');
                console.log(data);
                // save step results TODO save it in db !!!!
                // also save the current progression in db ?
                this.studentResults.push(data);
                // go to next step
                var currentStepIndex = this.steps.indexOf(this.currentStep);
                var length = this.steps.length;
                var newIndex = currentStepIndex + 1;
                if(newIndex < length){
                    
                    
                    this.setCurrentStep(newIndex);
                    this.isLastStep = newIndex === this.steps.length - 1 ? true:false;
                }
                else{
                    console.log('you reached the end of the exercise you will be redirected to summary page');
                    this.isFinished = true;
                    // TODO save the results in db
                    // save the hints used (table ujm_link_hint_paper) -> really need this ?
                    // save the paper (table ujm_paper) (and the question order for the paper...)
                    // save answers (table ujm_response)
                    // show correction summary page
                    // should correction summary page be on another route ? or not ?
                }
                
            };
        }
    ]);
})();