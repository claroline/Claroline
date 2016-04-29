/**
 * Exercise Player Controller
 * Plays and registers answers to an Exercise
 *
 * @param {Object}           $location
 * @param {Object}           exercise
 * @param {Object}           step
 * @param {Object}           paper
 * @param {CommonService}    CommonService
 * @param {ExerciseService}  ExerciseService
 * @param {FeedbackService}  FeedbackService
 * @param {UserPaperService} UserPaperService
 * @constructor
 */
var ExercisePlayerCtrl = function ExercisePlayerCtrl($location, exercise, step, paper, CommonService, ExerciseService, FeedbackService, UserPaperService) {
    // Store services
    this.$location        = $location;
    this.CommonService    = CommonService;
    this.ExerciseService  = ExerciseService;
    this.FeedbackService  = FeedbackService;
    this.UserPaperService = UserPaperService;

    // Initialize some data
    this.exercise = exercise; // Current exercise
    this.paper    = paper;    // Paper of the current User

    this.step     = step;
    this.index    = this.ExerciseService.getIndex(step);
    this.previous = this.ExerciseService.getPrevious(step);
    this.next     = this.ExerciseService.getNext(step);
    
    this.solutionShown = false;

    // Reset feedback (hide feedback and reset registered callbacks of the Step)
    this.FeedbackService.reset();

    // Configure Feedback
    if ('3' === this.exercise.meta.type) {
        // Enable feedback
        this.FeedbackService.enable();
    }

    // Get feedback info
    this.feedback = this.FeedbackService.get();
    
    // for now, we set a new variable, maxStepTries, to 5 by default
    for (var i=0; i < this.exercise.steps.length; i++) {
        this.exercise.steps[i].currentTry = 1;
        this.exercise.steps[i].maxStepTries = 5;
    }
};

// Set up dependency injection
ExercisePlayerCtrl.$inject = [
    '$location',
    'exercise',
    'step',
    'paper',
    'CommonService',
    'ExerciseService',
    'FeedbackService',
    'UserPaperService'
];

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
 * Feedback information
 * @type {Object}
 */
ExercisePlayerCtrl.prototype.feedback = null;

/**
 * Current step index
 * @type {number}
 */
ExercisePlayerCtrl.prototype.index = 0;

/**
 * Current played step
 * @type {Object}
 */
ExercisePlayerCtrl.prototype.step = null;

/**
 * Previous step
 * @type {Object}
 */
ExercisePlayerCtrl.prototype.previous = null;

/**
 * Next step
 * @type {Object}
 */
ExercisePlayerCtrl.prototype.next = null;

/**
 * Is the current Step answers submitted ?
 * @type {Boolean}
 */
ExercisePlayerCtrl.prototype.submitted = false;

/**
 * Submit answers for the current Step
 */
ExercisePlayerCtrl.prototype.submit = function submit() {
    return this.UserPaperService
                .submitStep(this.step)
                .then(function onSuccess(response) {
                    if (response) {
                        // Answers have been submitted
                        this.submitted = true;

                        if (this.FeedbackService.isEnabled()) {
                            // Show feedback
                            this.FeedbackService.show();
                        }
                    }
                }.bind(this));
};

/**
 * Retry the current Step
 */
ExercisePlayerCtrl.prototype.retry = function retry() {
    this.submitted = false;
    this.step.currentTry++;

    if (this.FeedbackService.isEnabled()) {
        // Hide feedback
        this.FeedbackService.hide();
    }
};

/**
 * Show the solution
 */
ExercisePlayerCtrl.prototype.showSolution = function showSolution() {
    this.solutionShown = true;
};

/**
 * Navigate to a step
 * @param step
 */
ExercisePlayerCtrl.prototype.goTo = function goTo(step) {
    // Manually disable tooltip
    $('.tooltip').hide();

    if (!this.submitted) {
        // Answers for the current step have not been submitted => submit it before navigating
        this.submit()
            .then(function onSuccess() {
                this.submitted = false;
                this.$location.path('/play/' + step.id);
            }.bind(this));
    } else {
        // Directly navigate to the Step
        this.submitted = false;
        this.$location.path('/play/' + step.id);
    }
};

/**
 * End the Exercise
 * Saves the current step and go to the Exercise home or papers if correction is available
 */
ExercisePlayerCtrl.prototype.end = function end() {
    this.submit()
        .then(function onSuccess() {
            // Answers submitted, we can now end the Exercise
            this.UserPaperService
                .end()
                .then(function onSuccess() {
                    if (this.checkCorrectionAvailability()) {
                        // go to paper correction view
                        this.$location.path('/papers/' + this.paper.id);
                    }
                    else {
                        // go to exercise home page
                        this.$location.path('/');
                    }
                }.bind(this));
        }.bind(this));
};

/**
 * Interrupt the Exercise
 * Saves the current step and go to the Exercise home
 */
ExercisePlayerCtrl.prototype.interrupt = function interrupt() {
    this.submit()
        .then(function onSuccess() {
            // Return to exercise home
            this.$location.path('/');
        }.bind(this));
};

/**
 * Check if correction is available for an exercise
 * @returns {Boolean}
 * @todo To mode into CorrectionService
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

// Register controller into Angular JS
angular
    .module('Exercise')
    .controller('ExercisePlayerCtrl', ExercisePlayerCtrl);