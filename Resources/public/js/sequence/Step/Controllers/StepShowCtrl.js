(function () {
    'use strict';

    angular.module('Step').controller('StepShowCtrl', [
        '$modal',
        function ($modal) {
            

            // all steps for the sequence
            this.steps = {};
            this.currentStepIndex = 0;           

            

            this.getNextStep = function () {
                var newIndex = this.currentStepIndex + 1;
                if (this.steps[newIndex]) {
                    this.setCurrentStep(this.steps[newIndex]);
                } else {
                    this.setCurrentStep(this.steps[0]);
                }
            };

            this.getPreviousStep = function () {
                var newIndex = this.currentStepIndex - 1;
                if (this.steps[newIndex]) {
                    this.setCurrentStep(this.steps[newIndex]);
                } else {
                    this.setCurrentStep(this.steps.length - 1);
                }
            };

            this.setSteps = function (steps) {
                this.steps = steps;
            };

            this.getSteps = function () {
                return this.steps;
            };

            // on step square click or called by getNext/getPrevious Step
            this.setCurrentStep = function (step) {
                var index = this.steps.indexOf(step);
                this.currentStepIndex = index;
                // questions are applicable only for "normal" steps
                if (index !== 0 || index !== this.steps.length - 1) {
                    this.getStepQuestions(step);
                }
            };
        }
    ]);
})();