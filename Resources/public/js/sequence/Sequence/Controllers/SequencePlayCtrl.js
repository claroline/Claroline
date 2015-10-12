(function () {
    'use strict';

    angular.module('Sequence').controller('SequencePlayCtrl', [
        '$window',
        'SequenceService',
        'CommonService',
        function ($window, SequenceService, CommonService) {

            this.sequence = {};
            this.currentStep = {};
            //this.steps = {};
            this.nbAttempts = 1;
            this.isLastStep = false;
            this.isFirstStep = true;
            this.paper = {};
            this.isFinished = false;

            /**
             * Init the sequence
             * @param {object} sequence
             * @param {object} sequence paper
             * @param {number} number of attempts already done
             */
            this.init = function (sequence, paper, nbAttempts) {

                // shuffle each question choices order if needed
                // TODO handle number of questions to keep
                for (var i = 0; i < sequence.steps.length; i++) {
                    // shuffle step question order
                    if (sequence.meta.random && sequence.steps[i].items.length > 1) {
                        sequence.steps[i].items = CommonService.shuffleArray(sequence.steps[i].items);
                    }
                    // shuffle each step choices order if needed
                    for (var j = 0; j < sequence.steps[i].items.length; j++) {
                        if (sequence.steps[i].items[j].random && sequence.steps[i].items[j].type === 'application/x.choice+json') {
                            sequence.steps[i].items[j].choices = CommonService.shuffleArray(sequence.steps[i].items[j].choices);
                        }
                    }
                }

                // need to set the sequence and paper in CommonService so that other directives can retrieve the data
                this.sequence = CommonService.setSequence(sequence);
                this.paper = CommonService.setPaper(paper);
                //this.steps = sequence.steps;
                this.nbAttempts = nbAttempts;
                // set current step
                this.setCurrentStep(0);

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
                this.isLastStep = index === this.sequence.steps.length - 1;
                // check new index is in computable range
                if (index < this.sequence.steps.length && index >= 0) {
                    this.currentStep = this.sequence.steps[index];
                } else {
                    // console.log('set current step error');
                    var url = Routing.generate('ujm_sequence_error');
                    $window.location = url;
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
                var index = this.sequence.steps.indexOf(this.currentStep);
                return index + 1;
            };

            /**
             * When using the drop down to jump to a specific step
             */
            this.goTo = function (step) {
                if (this.sequence.steps.indexOf(step) !== this.sequence.steps.indexOf(this.currentStep)) {
                    this.validateStep('goto', this.sequence.steps.indexOf(step));
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
                var currentStepIndex = this.sequence.steps.indexOf(this.currentStep);
                var newIndex = 0;
                if (action && (action === 'forward' || action === 'backward')) {
                    newIndex = action === 'forward' ? currentStepIndex + 1 : currentStepIndex - 1;
                    this.saveAnswerAndGotTo(newIndex, studentData);
                } else if (action && action === 'goto' && index !== undefined) {
                    newIndex = index;
                    this.saveAnswerAndGotTo(newIndex, studentData);
                } else if (action && action === 'end') {
                    // save the entire paper and redirect to paper details (correction)
                    var promise = SequenceService.endSequence(this.sequence.id, studentData.paper);
                    promise.then(function (result) {
                        if (this.checkCorrectionAvailability()) {
                            // go to paper correction view
                            var url = CommonService.generateUrl('paper-list', this.sequence.id) + '#/' + this.sequence.id + '/' + studentData.paper.id;
                            $window.location = url;
                        }
                        else {
                            // got to exercise home page
                            var url = CommonService.generateUrl('exercise-home', this.sequence.id);
                            $window.location = url;
                        }
                        //this.isFinished = true;
                    }.bind(this));
                } else {
                    var url = Routing.generate('ujm_sequence_error');
                    $window.location = url;
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
                    // result.data.id = recorded answer id ??? but do we need this ? any answer id can be retrieved by ujm_response.paper_id + ujm_response.question_id
                    // change current step
                    this.setCurrentStep(nextStepIndex);
                    // update paper question = this.currentStep.items[0]
                    CommonService.getCurrentQuestionPaperData(this.currentStep.items[0]);
                }.bind(this));
            };


            /**
             * Check if correction is available for a sequence
             * @returns {Boolean}
             */
            this.checkCorrectionAvailability = function () {
                var correctionMode = CommonService.getCorrectionMode(this.sequence.correctionMode);
                switch (correctionMode) {
                    case "test-end":
                        return true;                       
                        break;
                    case "last-try":
                        // check if current try is the last one ? -> currentAttemptNumber === sequence.maxAttempts - 1 ?
                        return this.nbAttempts === sequence.maxAttempts - 1;
                        break;
                    case "after-date":
                        var current = new Date();
                        // compare with ??? sequence.endDate ?
                        return true;
                        break;
                    case "never":
                        return false;
                        break;
                    default:
                        return false;
                }

            }
        }
    ]);
})();