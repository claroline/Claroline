(function () {
    'use strict';

    angular.module('Sequence').controller('SequencePlayCtrl', [
        'SequenceService',
        'CommonService',
        function (SequenceService, CommonService) {

            this.sequence = {};
            this.currentStep = {};
            this.nbAttempts = 1;
            
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

            this.setSteps = function () {
                this.steps = this.sequence.steps;
            };
            
            this.getSteps = function () {
                return this.sequence.steps;
            };

            /**
             * 
             * @returns {undefined}
             */
            this.getNbStep = function () {
                return this.sequence.steps.length;
            };

            this.setCurrentStep = function (index) {
                this.currentStep = this.sequence.steps[index];
            };
            this.getCurrentStep = function () {
                return this.currentStep;
            };
            

            this.getCurrentStepIndex = function () {
                var index = this.sequence.steps.indexOf(this.currentStep);
                return  index + 1;
            };

            this.setNbAttempts = function (nb) {
                this.nbAttempts = nb;
                //return CommonService.setNbAttempts(nb);
            };

            this.getNbAttempts = function () {
                return this.nbAttempts;
                //return CommonService.getNbAttempts();
            };
            
            /**
             * Validate the current step after confirm
             * If next step get next step
             * @returns {undefined}
             */
            this.validateStep = function () {
                var data = CommonService.getStudentData();
                //console.log('student data are below');
                console.log(data);
            };
        }
    ]);
})();