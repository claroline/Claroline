/**
 * Step Show Controller
 * @param {UserPaperService} UserPaperService
 * @param {FeedbackService} FeedbackService
 * @param {QuestionService} QuestionService
 * @constructor
 */
function StepShowCtrl(UserPaperService, FeedbackService, QuestionService) {
    this.UserPaperService = UserPaperService;
    this.FeedbackService = FeedbackService;
    this.QuestionService = QuestionService;

    // Get the order of items from the Paper of the User (in case they are shuffled)
    this.items = this.UserPaperService.orderStepQuestions(this.step);

    // Get feedback info
    this.feedback = this.FeedbackService.get();

    this.FeedbackService
        .on('show', this.onFeedbackShow.bind(this));
    
    if (this.getQuestionPaper(this.items[0]).nbTries && this.getQuestionPaper(this.items[0]).nbTries >= this.step.meta.maxAttempts && this.feedback.enabled) {
        this.solutionShown = true;
    }
    if (this.feedback.enabled && this.getQuestionPaper(this.items[0]).nbTries) {
        this.onFeedbackShow();
    }
}

/**
 * Current step
 * @type {Object}
 */
StepShowCtrl.prototype.step = null;

/**
 * Current feedback
 * @type {Object}
 */
StepShowCtrl.prototype.feedback = null;

/**
 * Items of the Step (correctly ordered)
 * @type {Array}
 */
StepShowCtrl.prototype.items = [];

/**
 * Current step number
 * @type {Object}
 */
StepShowCtrl.prototype.stepIndex = 0;

/**
 *
 * @type {boolean}
 */
StepShowCtrl.prototype.solutionShown = false;

/**
 *
 * @type {Integer}
 */
StepShowCtrl.prototype.allAnswersFound = -1;

/**
 * Get the Paper related to the Question
 * @param   {Object} question
 * @returns {Object}
 */
StepShowCtrl.prototype.getQuestionPaper = function getQuestionPaper(question) {
    return this.UserPaperService.getQuestionPaper(question);
};

/**
 * On Feedback Show
 */
StepShowCtrl.prototype.onFeedbackShow = function onFeedbackShow() {
    this.allAnswersFound = this.FeedbackService.SOLUTION_FOUND;
    for (var i = 0; i < this.items.length; i++) {
        var question = this.items[i];
        var answer = this.getQuestionPaper(question).answer;
        this.feedback.state[question.id] = this.QuestionService.getTypeService(question.type).answersAllFound(question, answer);
        if (this.feedback.state[question.id] !== 0) {
            this.allAnswersFound = this.FeedbackService.MULTIPLE_ANSWERS_MISSING;
        }
    }
};

/**
 *
 * @returns {string} Get the suite feedback sentence
 */
StepShowCtrl.prototype.getSuiteFeedback = function getSuiteFeedback() {
    var sentence = "";
    if (this.allAnswersFound === this.FeedbackService.SOLUTION_FOUND) {
        // Toutes les réponses ont été trouvées
        if (this.items.length === 1) {
            // L'étape comporte une seule question
            if (this.currentTry === 1) {
                // On en est à l'essai 1
                sentence = "perfectly_correct";
            } else {
                // L'étape a été jouée plusieurs fois
                sentence = "answers_correct";
            }
        } else {
            // L'étape comporte plusieurs questions
            if (this.currentTry === 1) {
                sentence = "all_answers_found";
            } else {
                sentence = "answers_now_correct";
            }
        }
    } else if (this.allAnswersFound === this.FeedbackService.MULTIPLE_ANSWERS_MISSING) {
        // toutes les réponses n'ont pas été trouvées
        if (this.currentTry < this.step.meta.maxAttempts) {
            sentence = "some_answers_miss_try_again";
        } else {
            sentence = "max_attempts_reached_see_solution";
        }
    }

    return sentence;
};

export default StepShowCtrl
