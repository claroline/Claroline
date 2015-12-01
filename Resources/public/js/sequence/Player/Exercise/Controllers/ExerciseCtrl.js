(function () {
    'use strict';

    angular.module('ExercisePlayerApp').controller('ExerciseCtrl', [
        '$window',
        '$route',
        'ExerciseService',
        'CommonService',
        'PlayerDataSharing',
        'data',
        'user',
        function ($window, $route, ExerciseService, CommonService, PlayerDataSharing, data, user) {

            console.log('data');
            console.log(data);
            
            this.exercise = data.exercise;
            this.paper = PlayerDataSharing.setPaper(data.paper);
            this.user = PlayerDataSharing.setUser(user);
            
            this.isFinished = false;
            this.isLastStep = false;
            this.isFirstStep = true;
            
            this.currentStepIndex = $route.current.params.sid ? $route.current.params.sid : 0;
           
            
            
            console.log('ok ?');
            console.log($route.current.params ? 'params OK' : 'params KO');
            console.log($route.current.params.eid ?  'eid :: ' + $route.current.params.eid : 'no eid');
            console.log($route.current.params.sid ?  'step index :: ' + $route.current.params.sid : 'no sid');
            
            
            
            this.init = function () {

                // shuffle each question choices order if needed
                for (var i = 0; i < this.exercise.steps.length; i++) {                   
                    // shuffle each step choices order if needed
                    for (var j = 0; j < this.exercise.steps[i].items.length; j++) {
                        // current item = a question
                        if (this.exercise.steps[i].items[j].random && this.exercise.steps[i].items[j].type === 'application/x.choice+json') {
                            this.exercise.steps[i].items[j].choices = ExerciseService.shuffleArray(this.exercise.steps[i].items[j].choices);
                        }
                    }
                }
                // set the exercise for sharing after shuffle
                this.exercise = PlayerDataSharing.setExercise(this.exercise);
            };
            
            this.init();
            
             /**
             * Check index data validity and set current step
             * @param {Number} index
             */
            this.setCurrentStep = function (index) {
                this.isFirstStep = index === 0;
                this.isLastStep = index === this.exercise.steps.length - 1;
                // check new index is in computable range
                if (index < this.exercise.steps.length && index >= 0) {
                    this.currentStep = this.exercise.steps[index];
                } else {
                    var url = Routing.generate('ujm_sequence_error', {message:'index out of bounds', code:'400'});
                    $window.location = url;
                }
            };
            
            // dunow if useful
            this.setCurrentStep(this.currentStepIndex);
            

            
            this.getCurrentStepNumber = function(){
                var index = this.currentStepIndex;
                index ++;
                return index;
            };         

            /**
             * When using the drop down to jump to a specific step
             * @param {Object} step
             */
            this.jumpToStep = function (step) {
                if (this.exercise.steps.indexOf(step) !== this.exercise.steps.indexOf(this.currentStep)) {
                    this.validateStep('goto', this.exercise.steps.indexOf(step));
                }
            };

            /**
             * save the current step in paper js object
             * in some case end the exercise
             * go to another step or end exercise
             * @param {String} action
             * @param {Number} index (nullable) the step index when using direct access
             */
            this.validateStep = function (action, index) {
                // manualy disable tooltips...
                $('.tooltip').each(function () {
                    $(this).hide();
                });
                // get current step index
                
                // get next step index
                this.currentStepIndex = this.getNextStepIndex(this.currentStepIndex, action, index);
                console.log('next :: ' + this.currentStepIndex);
                
                
                // data set by question directive
                // var studentData = CommonService.getStudentData();
                // If anwsers exist we need to save them
                /*if (studentData.answers && studentData.answers.length > 0) {
                    // save anwsers
                    var submitPromise = SequenceService.submitAnswer(this.paper.id, studentData);
                    submitPromise.then(function (result) {
                        // then navigate to desired step / end / terminate exercise
                        this.handleStepNavigation(action, newIndex, studentData.paper);
                    }.bind(this));
                } else {     
                    // navigate to desired step / end / terminate exercise
                    this.handleStepNavigation(action, newIndex, studentData.paper);
                }*/
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
             * Navigate to desired step or end exercise and redirect to appropriate view 
             * @param {string} action
             * @param {number} index
             * @param {object} paper
             */
            this.handleStepNavigation = function (action, index, paper) {
                if (action && (action === 'forward' || action === 'backward' || action === 'goto')) {
                    this.setCurrentStep(index);
                    // CommonService.setCurrentQuestionPaperData(this.currentStep.items[0]);
                } else if (action && action === 'end') {
                    var endPromise = ExerciseService.endSequence(paper)
                    endPromise.then(function (result) {
                        if (this.checkCorrectionAvailability()) {
                            // display correction directive
                            this.isFinished = true;
                            
                            // go to paper correction view
                            //var url = CommonService.generateUrl('paper-list', this.sequence.id) + '#/' + this.sequence.id + '/' + paper.id;
                            //$window.location = url;
                        }
                        else {
                           var url = CommonService.generateUrl('exercise-home', this.exercise.id);
                           $window.location = url;
                        }
                    }.bind(this));
                } else if (action && action === 'interrupt') {
                    // got to exercise home page
                    var url = CommonService.generateUrl('exercise-home', this.exercise.id);
                    $window.location = url;
                } else {
                    var url = Routing.generate('ujm_sequence_error', {message:'action not allowed', code:'400'});
                    $window.location = url;
                }
            };
            
            
            /**
             * Check if correction is available for an exercise
             * @returns {Boolean}
             */
            this.checkCorrectionAvailability = function () {
                var correctionMode = CommonService.getCorrectionMode(this.exercise.meta.correctionMode);
                switch (correctionMode) {
                    case "test-end":
                        return true;
                        break;
                    case "last-try":
                        // check if current try is the last one ?
                        return this.paper.number === this.exercise.meta.maxAttempts;
                        break;
                    case "after-date":
                        var now = new Date();                        
                        var searched = new RegExp('-', 'g');
                        var correctionDate = new Date(Date.parse(this.exercise.meta.correctionDate.replace(searched, '/')));
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