/**
 * Exercise Player Controller
 * Plays and registers answers to an Exercise
 *
 * @param {Object}           $location
 * @param {Object}           step
 * @param {Object}           paper
 * @param {CommonService}    CommonService
 * @param {ExerciseService}  ExerciseService
 * @param {FeedbackService}  FeedbackService
 * @param {UserPaperService} UserPaperService
 * @param {TimerService}     TimerService
 * @constructor
 */
var ExercisePlayerCtrl = function ExercisePlayerCtrl(
    $location,
    step,
    paper,
    CommonService,
    ExerciseService,
    FeedbackService,
    UserPaperService,
    TimerService
) {
    // Store services
    this.$location        = $location;
    this.CommonService    = CommonService;
    this.ExerciseService  = ExerciseService;
    this.FeedbackService  = FeedbackService;
    this.UserPaperService = UserPaperService;
    this.TimerService     = TimerService;

    // Initialize some data
    this.exercise = this.ExerciseService.getExercise(); // Current exercise
    this.paper    = paper;    // Paper of the current User

    this.step     = step;
    this.index    = this.ExerciseService.getIndex(step);
    this.previous = this.ExerciseService.getPrevious(step);
    this.next     = this.ExerciseService.getNext(step);
    
    this.allAnswersFound = -1;

    // Reset feedback (hide feedback and reset registered callbacks of the Step)
    this.FeedbackService.reset();

    // Configure Feedback
    if ('3' === this.exercise.meta.type) {
        // Enable feedback
        this.FeedbackService.enable();
    } else {
        // Disable feedback
        this.FeedbackService.disable();
    }

    // Get feedback info
    this.feedback = this.FeedbackService.get();

    // Initialize Timer if needed
    if (0 !== this.exercise.meta.duration) {
        this.timer = this.TimerService.new(this.exercise.id, this.exercise.meta.duration * 60, this.end.bind(this), true);
    }
};

// Set up dependency injection
ExercisePlayerCtrl.$inject = [
    '$location',
    'step',
    'paper',
    'CommonService',
    'ExerciseService',
    'FeedbackService',
    'UserPaperService',
    'TimerService'
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
 * Are the solutions shown in the Exercise ?
 * @type {boolean}
 */
ExercisePlayerCtrl.prototype.solutionShown = false;

/**
 *
 * @type {number}
 */
ExercisePlayerCtrl.prototype.currentStepTry = 1;

/**
 * Timer of the Exercise
 * @type {Object|null}
 */
ExercisePlayerCtrl.prototype.timer = null;

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
 * @param button
 */
ExercisePlayerCtrl.prototype.isButtonEnabled = function isButtonEnabled(button) {
    var buttonEnabled;
    if (button === 'retry') {
        buttonEnabled = this.feedback.enabled && this.feedback.visible && this.currentStepTry !== this.step.meta.maxAttempts && this.allAnswersFound !== 0;
    } else if (button === 'next') {
        buttonEnabled = !this.next || (this.feedback.enabled && !this.feedback.visible) || (this.feedback.enabled && this.feedback.visible && !this.solutionShown && !(this.allAnswersFound === 0));
    } else if (button === 'navigation') {
        buttonEnabled = (this.feedback.enabled && !this.feedback.visible) || (this.feedback.enabled && this.feedback.visible && !this.solutionShown && !(this.allAnswersFound === 0));
    } else if (button === 'end') {
        buttonEnabled = (this.feedback.enabled && !this.feedback.visible) || (this.feedback.enabled && this.feedback.visible && !this.solutionShown && !(this.allAnswersFound === 0));
    }
    
    return buttonEnabled;
};

/**
 * Retry the current Step
 */
ExercisePlayerCtrl.prototype.retry = function retry() {
    this.submitted = false;
    this.currentStepTry++;

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
                this.submitted     = false;
                this.solutionShown = false;

                this.$location.path('/play/' + step.id);
            }.bind(this));
    } else {
        // Directly navigate to the Step
        this.submitted     = false;
        this.solutionShown = false;

        this.$location.path('/play/' + step.id);
    }
};

/**
 * End the Exercise
 * Saves the current step and go to the Exercise home or papers if correction is available
 */
ExercisePlayerCtrl.prototype.end = function end() {
    if (this.timer) {
        // Stop Timer if the Exercise as a fixed duration
        this.TimerService.destroy(this.timer.id);
    }

    this.submit()
        .then(function onSuccess() {
            // Answers submitted, we can now end the Exercise
            this.UserPaperService
                .end()
                .then(function onSuccess() {
                    if (this.UserPaperService.isCorrectionAvailable(this.paper)) {
                        // go to paper correction view
                        this.$location.path('/papers/' + this.paper.id);
                    }
                    else {
                        // go to exercise papers list (to let the User show his registered paper)
                        this.$location.path('/papers');
                    }
                }.bind(this));
            this.feedback.state = null;
        }.bind(this));
};

/**
 * Interrupt the Exercise
 * Saves the current step and go to the Exercise home
 */
ExercisePlayerCtrl.prototype.interrupt = function interrupt() {
    if (this.timer) {
        // Stop Timer if the Exercise as a fixed duration
        this.TimerService.destroy(this.timer.id);
    }

    this.submit()
        .then(function onSuccess() {
            // Return to exercise home
            this.$location.path('/');
        }.bind(this));
};

// Register controller into Angular JS
angular
    .module('Exercise')
    .controller('ExercisePlayerCtrl', ExercisePlayerCtrl);
