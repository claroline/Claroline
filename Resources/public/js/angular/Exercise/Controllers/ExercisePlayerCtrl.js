/**
 * Exercise Player Controller
 * @param $window
 * @param $scope
 * @param ExerciseService
 * @param CommonService
 * @param DataSharing
 * @constructor
 */

var exoPlayer;
var myTimer;

var ExercisePlayerCtrl = function ExercisePlayerCtrl(exercise, paper, $window, $scope, ExerciseService, CommonService, DataSharing, $timeout, $localStorage) {
    // Store services
    this.DataSharing     = DataSharing;
    this.CommonService   = CommonService;
    this.ExerciseService = ExerciseService;
    this.$scope          = $scope;
    this.$window        = $window;
    this.$timeout        = $timeout;
    this.$localStorage   = $localStorage;

    // Initialize some data
    this.exercise = exercise;
    this.paper    = this.DataSharing.setPaper(paper);

    // Set the current Step
    this.setCurrentStep(this.currentStepIndex);

    exoPlayer = this;

    exoPlayer.$localStorage.$default({
        counter: 0,
        hours: 0,
        minutes: 0,
        secondes: 0
    });

    exoPlayer.duration = exoPlayer.exercise.meta.duration * 60;

    var onTimeout = function() {

        exoPlayer.$localStorage.counter =  exoPlayer.$localStorage.counter + 1;
        myTimer = exoPlayer.$timeout(onTimeout, 1000);

        exoPlayer.$localStorage.hours = Math.floor((exoPlayer.duration - exoPlayer.$localStorage.counter) / 3600);
        exoPlayer.$localStorage.minutes = Math.floor(((exoPlayer.duration - exoPlayer.$localStorage.counter) - (exoPlayer.$localStorage.hours * 3600))  / 60);
        exoPlayer.$localStorage.secondes = Math.floor((exoPlayer.duration - exoPlayer.$localStorage.counter) - ((exoPlayer.$localStorage.hours * 3600) + (exoPlayer.$localStorage.minutes * 60)));

        if (exoPlayer.$localStorage.counter == exoPlayer.duration) {
            exoPlayer.validateStep('end');
        }
    };

    myTimer = exoPlayer.$timeout(onTimeout, 1000);
};

// Set up dependency injection
ExercisePlayerCtrl.$inject = [ 'exercise', 'paper', '$window', '$scope', 'ExerciseService', 'CommonService', 'DataSharing', '$timeout', '$localStorage' ];

/**
 * Current played Exercise
 * @type {Object}
 */
ExercisePlayerCtrl.prototype.exercise = {};

/**
 * Current User paper
 * @type {Object}
 */
ExercisePlayerCtrl.prototype.paper = {};

/**
 * Is the Exercise finished ?
 * @type {boolean}
 */
ExercisePlayerCtrl.prototype.isFinished = false;

/**
 * Is the current Step the first one ?
 * @type {boolean}
 */
ExercisePlayerCtrl.prototype.isFirstStep = true;

/**
 * Is the current Step the last one ?
 * @type {boolean}
 */
ExercisePlayerCtrl.prototype.isLastStep = false;

/**
 * Current step index
 * @type {number}
 */
ExercisePlayerCtrl.prototype.currentStepIndex = 0;

/**
 * Current step
 * @type {null}
 */
ExercisePlayerCtrl.prototype.currentStep = null;

/**
 * Display feedback ?
 * @type {boolean}
 */
ExercisePlayerCtrl.prototype.feedbackIsShown = false;

/**
 * Check index data validity and set current step
 * @param {Number} index
 */
ExercisePlayerCtrl.prototype.setCurrentStep = function setCurrentStep(index) {
    this.isFirstStep = index === 0;
    this.isLastStep  = index === this.exercise.steps.length - 1;

    console.log(this.exercise.steps);

    // check new index is in computable range
    if (index < this.exercise.steps.length && index >= 0) {
        this.currentStep = this.exercise.steps[index];
    } else {
        var url = Routing.generate('ujm_sequence_error', { message: 'index out of bounds', code: '400' });
        this.$window.location = url;
    }
};

/**
 * Get the step number for display
 * @returns {Number}
 */
ExercisePlayerCtrl.prototype.getCurrentStepNumber = function getCurrentStepNumber() {
    return this.currentStepIndex + 1;
};

/**
 * When using the drop down to jump to a specific step
 * @param {Object} step
 */
ExercisePlayerCtrl.prototype.jumpToStep = function jumpToStep(step) {
    if (this.exercise.steps.indexOf(step) !== this.exercise.steps.indexOf(this.currentStep)) {
        this.validateStep('goto', this.exercise.steps.indexOf(step));
    }
};

ExercisePlayerCtrl.prototype.getTotalScore = function () {
    var totalScore = 0;
    for (var i=0; i<this.exercise.steps[this.currentStepIndex].items.length; i++) {
        totalScore += this.exercise.steps[this.currentStepIndex].items[i].scoreTotal;
    }

    return totalScore;
};

ExercisePlayerCtrl.prototype.getCurrentScore = function () {
    var studentData = this.DataSharing.getStudentData();
    if (studentData.question.typeOpen === "long") {
        return "-";
    }
    else {
        for (var i=0; i<studentData.paper.questions.length; i++) {
            if (studentData.paper.questions[i].id === studentData.question.id.toString()) {
                return studentData.paper.questions[i].score;
            }
        }
    }
};

/**
 * save the current step in paper js object
 * in some case end the exercise
 * go to another step or end exercise
 * @param {String} action
 * @param {Number} index (nullable) the step index when using direct access
 */
ExercisePlayerCtrl.prototype.validateStep = function (action, index) {
    // manualy disable tooltips...
    $('.tooltip').each(function () {
        $(this).hide();
    });

    // get next step index
    this.currentStepIndex = this.getNextStepIndex(this.currentStepIndex, action, index);

    // data set by question directive
    var studentData = this.DataSharing.getStudentData();
    // TODO : améliorer
    if (3 != this.exercise.meta.type) {
        var submitPromise = this.ExerciseService.submitAnswer(this.paper.id, studentData);
    }

    // navigate to desired step / end / terminate exercise
    this.handleStepNavigation(action, studentData.paper);
};

/**
 *
 * @param {number} current current index
 * @param {string} action
 * @param {number} index the index to reach (when the drop box is used)
 * @returns {number}
 */
ExercisePlayerCtrl.prototype.getNextStepIndex = function (current, action, index) {
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
 * @param {object} paper
 */
ExercisePlayerCtrl.prototype.handleStepNavigation = function (action, paper) {
    this.feedbackIsShown = false;

    if (action && (action === 'forward' || action === 'backward' || action === 'goto')) {
        this.setCurrentStep(this.currentStepIndex);
    } else if (action && action === 'end') {

        exoPlayer.$localStorage.$reset({
            counter: 0
        });
        exoPlayer.$timeout.cancel(myTimer);

        var endPromise = this.ExerciseService.end(paper);
        endPromise.then(function (result) {
            if (this.checkCorrectionAvailability()) {
                // go to paper correction view
                var url = Routing.generate('ujm_exercise_open', {id: this.exercise.id}) + '#/papers/' + paper.id;
                this.$window.location = url;
            }
            else {
                // go to exercise home page
                var url = Routing.generate('ujm_exercise_open', {id: this.exercise.id});
                this.$window.location = url;
            }
        }.bind(this));
    } else if (action && action === 'interrupt') {
        // go to exercise home page
        var url = Routing.generate('ujm_exercise_open', {id: this.exercise.id});
        this.$window.location = url;
    } else {
        var url = Routing.generate('ujm_sequence_error', {message: 'action not allowed', code: '400'});
        this.$window.location = url;
    }
};


/**
 * Check if correction is available for an exercise
 * @returns {Boolean}
 */
ExercisePlayerCtrl.prototype.checkCorrectionAvailability = function () {
    var correctionMode = this.CommonService.getCorrectionMode(this.exercise.meta.correctionMode);
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

ExercisePlayerCtrl.prototype.showFeedback = function () {
    var studentData = this.DataSharing.getStudentData();
    var submitPromise = this.ExerciseService.submitAnswer(this.paper.id, studentData);
    submitPromise.then(function (result) {
        this.feedbackIsShown = true;
    }.bind(this));
    this.$scope.$broadcast('show-feedback');
};

ExercisePlayerCtrl.prototype.hideFeedback = function () {
    this.feedbackIsShown = false;
    this.$scope.$broadcast('hide-feedback');
};

/**
 * Checks if feedback fields can be visible at some times
 * @returns {Boolean}
 */
ExercisePlayerCtrl.prototype.checkIfFeedbackIsAvailable = function () {
    return this.exercise.meta.type === "3";
};

// Register controller into Angular JS
angular
    .module('Exercise')
    .controller('ExercisePlayerCtrl', ExercisePlayerCtrl);