(function () {
    'use strict';

    angular.module('Sequence').controller('SequencePlayCtrl', [
        '$window',
        'SequenceService',
        'CommonService',
        function ($window, SequenceService, CommonService) {

            this.sequence = {};
            this.currentStep = {};
            this.user = {};

            this.isLastStep = false;
            this.isFirstStep = true;
            this.paper = {};

            /**
             * Init the sequence
             * @param {object} sequence
             * @param {object} sequence paper
             * @param {number} user id
             */
            this.init = function (sequence, paper, user) {
                // shuffle each question choices order if needed
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
                this.user = CommonService.setUser(user);
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
             * also set the current paper step for questions directive
             * @param {number} index
             */
            this.setCurrentStep = function (index) {
                this.isFirstStep = index === 0;
                this.isLastStep = index === this.sequence.steps.length - 1;
                // check new index is in computable range
                if (index < this.sequence.steps.length && index >= 0) {
                    this.currentStep = this.sequence.steps[index];
                    // CommonService.getCurrentQuestionPaperData(this.currentStep.items[0]);
                } else {
                    // console.log('set current step error');
                    var url = Routing.generate('ujm_sequence_error');
                    $window.location = url;
                }
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
             * @param {Object} step
             */
            this.jumpToStep = function (step) {
                if (this.sequence.steps.indexOf(step) !== this.sequence.steps.indexOf(this.currentStep)) {
                    this.validateStep('goto', this.sequence.steps.indexOf(step));
                }
            };

            /**
             * save the current step in paper js object
             * in some case end the sequence
             * go to another step or end sequence
             * @param {String} action
             * @param {Number} index (nullable) the step index when using direct access
             */
            this.validateStep = function (action, index) {
                // manualy disable tooltips...
                $('.tooltip').each(function () {
                    $(this).hide();
                });
                // get current step index
                var currentStepIndex = this.sequence.steps.indexOf(this.currentStep);
                // get next step index
                var newIndex = this.getNextStepIndex(currentStepIndex, action, index);
                // data set by question directive
                var studentData = CommonService.getStudentData();
                // If anwsers exist we need to save them
                if (studentData.answers && studentData.answers.length > 0) {
                    // save anwsers
                    var submitPromise = SequenceService.submitAnswer(this.paper.id, studentData);
                    submitPromise.then(function (result) {
                        // then navigate to desired step / end / terminate sequence
                        this.handleStepNavigation(action, newIndex, studentData.paper);
                    }.bind(this));
                } else {     
                    // navigate to desired step / end / terminate sequence
                    this.handleStepNavigation(action, newIndex, studentData.paper);
                }
            };

            /**
             * 
             * @param {number} current current index
             * @param {string} action
             * @param {number} index the index to reach (when the drop box is used)
             * @returns {number}
             */
            this.getNextStepIndex = function (current, action, index) {
                var newIndex = 0;
                if (action && (action === 'forward' || action === 'backward')) {
                    newIndex = action === 'forward' ? current + 1 : current - 1;
                } else if (action && action === 'goto' && index !== undefined) {
                    newIndex = index;
                }
                return newIndex;
            };

            /**
             * Navigate to desired step or end sequence and redirect to appropriate view 
             * @param {string} action
             * @param {number} index
             * @param {object} paper
             */
            this.handleStepNavigation = function (action, index, paper) {
                if (action && (action === 'forward' || action === 'backward' || action === 'goto')) {
                    this.setCurrentStep(index);
                    CommonService.setCurrentQuestionPaperData(this.currentStep.items[0]);
                } else if (action && action === 'end') {
                    var endPromise = SequenceService.endSequence(paper)
                    endPromise.then(function (result) {
                        if (this.checkCorrectionAvailability()) {
                            // go to paper correction view
                            var url = CommonService.generateUrl('paper-list', this.sequence.id) + '#/' + this.sequence.id + '/' + paper.id;
                            $window.location = url;
                        }
                        else {
                            var url = CommonService.generateUrl('exercise-home', this.sequence.id);
                            $window.location = url;
                        }
                    }.bind(this));
                } else if (action && action === 'interrupt') {
                    // got to exercise home page
                    var url = CommonService.generateUrl('exercise-home', this.sequence.id);
                    $window.location = url;
                } else {
                    var url = Routing.generate('ujm_sequence_error');
                    $window.location = url;
                }
            };
            
            
            /**
             * Check if correction is available for a sequence
             * @returns {Boolean}
             */
            this.checkCorrectionAvailability = function () {
                var correctionMode = CommonService.getCorrectionMode(this.sequence.meta.correctionMode);
                switch (correctionMode) {
                    case "test-end":
                        return true;
                        break;
                    case "last-try":
                        // check if current try is the last one ?
                        return this.paper.numberNumber === sequence.meta.maxAttempts;
                        break;
                    case "after-date":
                        var now = new Date();                        
                        var searched = new RegExp('-', 'g');
                        var correctionDate = new Date(Date.parse(this.sequence.meta.correctionDate.replace(searched, '/')));
                        return now >= correctionDate;
                        break;
                    case "never":
                        return false;
                        break;
                    default:
                        return false;
                }

            };
        }
    ]);
})();