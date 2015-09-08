(function () {
    'use strict';

    angular.module('Step').controller('StepShowCtrl', [
        'CommonService',
        function (CommonService) {           

            // all steps for the sequence
            this.steps = {};
            // for now just one step
            this.currentStep = {};
            // TODO differenciate content step and question step
            
            

            this.getNextStep = function () {
                var index = this.steps.indexOf(this.currentStep);
                
                var newIndex = index + 1;
                if (this.steps[newIndex]) {
                    this.setCurrentStep(this.steps[newIndex]);
                } else {
                    this.setCurrentStep(this.steps[0]);
                }
            };

            // during a sequence can we go backward ??
            /*this.getPreviousStep = function () {
                var newIndex = this.currentStepIndex - 1;
                if (this.steps[newIndex]) {
                    this.setCurrentStep(this.steps[newIndex]);
                } else {
                    this.setCurrentStep(this.steps.length - 1);
                }
            };*/

            this.setSteps = function (steps) {
                this.steps = CommonService.setSteps(steps);
            };

            this.getSteps = function () {
                return CommonService.getSteps();
            };

            // on step square click or called by getNext/getPrevious Step
            this.setCurrentStep = function (step) {
                var index = this.steps.indexOf(step);
                this.currentStep = CommonService.setCurrentStep(step);
                // questions are applicable only for "normal" steps
                // so we need to get step questions only if we currently are on a "question type step" opposed to "content step"
                if (index !== 0 || index !== this.steps.length - 1) {
                    // this.getStepQuestions(step);
                }
            };
            
            this.stepHasQuestions = function (){
            
                if(this.steps.length === 0){
                    return false;
                }
                else if(this.currentStep.items.length === 0){
                    return false;
                }
                else{
                    return true;
                }
            };
        }
    ]);
})();