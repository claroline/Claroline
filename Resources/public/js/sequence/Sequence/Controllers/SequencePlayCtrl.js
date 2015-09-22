(function () {
    'use strict';

    angular.module('Sequence').controller('SequencePlayCtrl', [        
        '$ngBootbox',
        'SequenceService',
        'CommonService',
        function ($ngBootbox, SequenceService, CommonService) {

            this.sequence = {};
            this.currentStep = {};
            this.steps = {};
            this.nbAttempts = 1;
            this.studentResults = Array();
            this.isLastStep = false;
            this.isFirstStep = true;


            /**
             * Init the sequence
             * @param {object} sequence
             * @param {object} sequence steps
             * @param {number} number of attempts already done
             */
            this.init = function (sequence, steps, attempts) {
                // need to set the sequence in CommonService so that other directives can retrieve the info
                this.sequence = CommonService.setSequence(sequence);
                this.steps = steps;
                this.setCurrentStep(0);
                this.nbAttempts = attempts;
            };

            /**
             * Check if the question has meta like created / licence / description...
             */
            this.questionHasOtherMeta = function () {
                return CommonService.objectHasOtherMeta(this.sequence);
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

            /**
             * When using the drop down to jum to a specific step
             */
            this.goTo = function (step) {
                this.validateStep('goto', this.steps.indexOf(step));
                // no need to confirm !!
                /*$ngBootbox.confirm(Translator.trans('sequence_next_step_confirm', {}, 'ujm_sequence'))
                        .then(function () {
                            console.log('Confirmed!');
                            this.validateStep('goto', this.steps.indexOf(step));
                        }.bind(this), function () {
                           // console.log('Confirm dismissed!');
                        });*/
            };

            /**
             * Validate the current step after confirm
             * If next/prev step get it (also save student progression)
             * Else end the sequence (also save student paper)
             * 
             * @param {String} action
             * @param {Number} index (nullable) the step index
             */
            this.validateStep = function (action, index) {
                // disable tooltips...
                $('.tooltip').each(function(){
                    $(this).hide();
                });
                
                // data are given by question directive
                var data = CommonService.getStudentData();
                console.log('student data are below');
                console.log(data);
                // save step results in DB !!!!
                // also save the current progression in db ?
                // probably not necessary as the correction will be done by another module and data will be retrieved in DB
                this.studentResults.push(data);
                // get current step index
                var currentStepIndex = this.steps.indexOf(this.currentStep);
                var length = this.steps.length;
                var newIndex = index ? index : 0;
                if (action && action === 'forward') {
                    newIndex = currentStepIndex + 1;
                }
                else if (action && action === 'backward') {
                    newIndex = currentStepIndex - 1;
                }
                this.isFirstStep = newIndex === 0;
                this.isLastStep = newIndex === this.steps.length - 1;
                // check new index is in computable range
                if (newIndex < length && newIndex >= 0) {
                    this.setCurrentStep(newIndex);
                }
                else if (this.isLastStep) {
                    console.log('you reached the end of the exercise you will be redirected to summary page');

                    // TODO save the results in db
                    // save the hints used (table ujm_link_hint_paper) -> really need this ?
                    // save the paper (table ujm_paper) (and the question order for the paper...)
                    // save answers (table ujm_response)
                    // show correction summary page
                    // should correction summary page be on another route ? or not ?
                }
                else {
                    // error page
                    console.log('error...');
                }

            };
        }
    ]);
})();