(function () {
    'use strict';

    angular.module('Sequence').controller('SequencePlayCtrl', [
        '$window',
        'SequenceService',
        'CommonService',
        function ($window, SequenceService, CommonService) {

            this.sequence = {};
            this.currentStep = {};
            this.steps = {};
            this.nbAttempts = 1;
            this.isLastStep = false;
            this.isFirstStep = true;
            this.paper = {};
            this.isFinished = false;

            /**
             * Init the sequence
             * @param {object} sequence
             * @param {object} sequence steps
             * @param {object} sequence paper
             * @param {number} number of attempts already done
             */
            this.init = function (sequence, steps, paper, nbAttempts) {
                // need to set the sequence and paper in CommonService so that other directives can retrieve the info
                this.sequence = CommonService.setSequence(sequence);
                this.paper = CommonService.setPaper(paper);
                this.steps = steps;
                this.nbAttempts = nbAttempts;
                this.setCurrentStep(0);
                // this.setCurrentPaperData(0);
            };

            /**
             * Check if the question has meta like created / licence / description...
             */
            this.questionHasOtherMeta = function () {
                return CommonService.objectHasOtherMeta(this.sequence);
            };

            /**
             * Check data validity and set current step
             * also set the current paper step for questions directive (previously used hints and given answers)
             * @param {number} index
             */
            this.setCurrentStep = function (index) {
                this.isFirstStep = index === 0;
                this.isLastStep = index === this.steps.length - 1;
                // check new index is in computable range
                if (index < this.steps.length && index >= 0) {
                    this.currentStep = this.steps[index];
                } else {
                    console.log('set current step error');
                }
            };

            /**
             * set the data for current step
             * @param {type} index
             * @returns {undefined}
             */
            this.setCurrentPaperData = function (index) {
                CommonService.setCurrentPaperStep(index);
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
                return index + 1;
            };

            /**
             * When using the drop down to jump to a specific step
             */
            this.goTo = function (step) {
                if (this.steps.indexOf(step) !== this.steps.indexOf(this.currentStep)) {
                    this.validateStep('goto', this.steps.indexOf(step));
                }
            };

            /**
             * save the current step in paper js object
             * go to another step or end sequence
             * @param {String} action
             * @param {Number} index (nullable) the step index
             */
            this.validateStep = function (action, index) {
                // manualy disable tooltips...
                $('.tooltip').each(function () {
                    $(this).hide();
                });
                // data set by question directive
                var studentData = CommonService.getStudentData();
                // get current step index
                var currentStepIndex = this.steps.indexOf(this.currentStep);
                var newIndex = 0;
                if (action && (action === 'forward' || action === 'backward')) {
                    newIndex = action === 'forward' ? currentStepIndex + 1 : currentStepIndex - 1;
                    this.saveAnswerAndGotTo(newIndex, studentData);
                } else if (action && action === 'goto' && index !== undefined) {
                    newIndex = index;
                    this.saveAnswerAndGotTo(newIndex, studentData);
                } else if (action && action === 'end') {
                    console.log('you reached the end of the exercise you will be redirected to paper correction page');
                    // save the paper and redirect to paper details (correction)
                    var promise = SequenceService.endSequence(this.sequence.id, studentData.paper);
                    promise.then(function (result) {
                        this.isFinished = true;
                    }.bind(this), function (error) {
                        console.log('error');
                    }.bind(this));
                } else {
                    console.log('validate step error');
                }
            };

            /**
             * Saves the anwser in DB and change step
             * @param {type} nextStepIndex
             */
            this.saveAnswerAndGotTo = function (nextStepIndex, studentData) {
                // save answer only or whole paper ??
                var promise = SequenceService.recordAnswer(this.sequence.id, studentData);
                promise.then(function (result) {
                    if (result.status === 'success') {
                        // result.data.id = recorded answer id ??? but do we need this ? any answer id can be retrieved by ujm_response.paper_id + ujm_response.question_id
                        // change current step
                        this.setCurrentStep(nextStepIndex);
                        // update paper question = this.currentStep.items[0]
                        CommonService.getCurrentQuestionPaperData(this.currentStep.items[0]);
                    }

                }.bind(this), function (error) {
                    console.log('error');
                }.bind(this));
            };
        }
    ]);
})();