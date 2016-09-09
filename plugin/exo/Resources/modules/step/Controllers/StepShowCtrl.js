/**
 * Step Show Controller
 * @param {UserPaperService} UserPaperService
 * @param {FeedbackService} FeedbackService
 * @param {QuestionService} QuestionService
 * @param {StepService} StepService
 * @constructor
 */
function StepShowCtrl(UserPaperService, FeedbackService, QuestionService, StepService) {
  this.UserPaperService = UserPaperService
  this.FeedbackService = FeedbackService
  this.QuestionService = QuestionService
  this.StepService = StepService

  // Get the order of items from the Paper of the User (in case they are shuffled)
  this.items = this.UserPaperService.orderStepQuestions(this.step)

  // Get feedback info
  this.feedback = this.FeedbackService.get()

  this.FeedbackService
          .on('show', this.onFeedbackShow.bind(this))

  if (this.items[0] && this.getQuestionPaper(this.items[0]).nbTries && this.getQuestionPaper(this.items[0]).nbTries >= this.step.meta.maxAttempts && this.feedback.enabled) {
    this.solutionShown = true
  }

  if (this.items[0] &&  this.feedback.enabled && this.getQuestionPaper(this.items[0]).nbTries) {
    this.onFeedbackShow()

    if (this.allAnswersFound === 0) {
      this.feedback.visible = true
      this.solutionShown = true
    }
  }

  this.showScore = this.UserPaperService.isScoreAvailable(this.UserPaperService.getPaper())

  this.getStepTotalScore()
}

/**
 * Current step
 * @type {Object}
 */
StepShowCtrl.prototype.step = null

/**
 * Current feedback
 * @type {Object}
 */
StepShowCtrl.prototype.feedback = null

/**
 * Items of the Step (correctly ordered)
 * @type {Array}
 */
StepShowCtrl.prototype.items = []

/**
 * Current step number
 * @type {Object}
 */
StepShowCtrl.prototype.stepIndex = 0

/**
 * Current step score
 * @type {Number}
 */
StepShowCtrl.prototype.stepScore = 0

/**
 * Current step total score
 * @type {Number}
 */
StepShowCtrl.prototype.stepScoreTotal = 0

/**
 *
 * @type {boolean}
 */
StepShowCtrl.prototype.solutionShown = false

/**
 *
 * @type {Integer}
 */
StepShowCtrl.prototype.allAnswersFound = -1

/**
 *
 * @type {boolean}
 */
StepShowCtrl.prototype.showScore = true

/**
 * Get the Paper related to the Question
 * @param   {Object} question
 * @returns {Object}
 */
StepShowCtrl.prototype.getQuestionPaper = function getQuestionPaper(question) {
  return this.UserPaperService.getQuestionPaper(question)
}

StepShowCtrl.prototype.getStepTotalScore = function getStepTotalScore() {
  this.stepScoreTotal = 0
  for (var i = 0; i < this.items.length; i++) {
    var question = this.items[i]
    this.stepScoreTotal += this.QuestionService.getTypeService(question.type).getTotalScore(question)
  }
}

/**
 * On Feedback Show
 */
StepShowCtrl.prototype.onFeedbackShow = function onFeedbackShow() {
  this.allAnswersFound = this.FeedbackService.SOLUTION_FOUND
  this.stepScore = 0
  for (var i = 0; i < this.items.length; i++) {
    var question = this.items[i]
    var userPaper = this.getQuestionPaper(question)
    var answer = userPaper.answer
    this.stepScore += this.QuestionService.getTypeService(question.type).getAnswerScore(question, answer)
    this.feedback.state[question.id] = this.QuestionService.getTypeService(question.type).answersAllFound(question, answer)
    if (this.feedback.state[question.id] !== 0) {
      this.allAnswersFound = this.FeedbackService.MULTIPLE_ANSWERS_MISSING
    }
  }
}

StepShowCtrl.prototype.showMinimalCorrection = function showMinimalCorrection() {
  return this.StepService.getExerciseMeta().minimalCorrection
}

/**
 *
 * @returns {string} Get the suite feedback sentence
 */
StepShowCtrl.prototype.getSuiteFeedback = function getSuiteFeedback() {
  var sentence = ''
  if (this.allAnswersFound === this.FeedbackService.SOLUTION_FOUND) {
    // Toutes les réponses ont été trouvées
    if (this.items.length === 1) {
      // L'étape comporte une seule question
      if (this.currentTry === 1) {
        // On en est à l'essai 1
        sentence = 'perfectly_correct'
      } else {
        // L'étape a été jouée plusieurs fois
        sentence = 'answers_correct'
      }
    } else {
      // L'étape comporte plusieurs questions
      if (this.currentTry === 1) {
        sentence = 'all_answers_found'
      } else {
        sentence = 'answers_now_correct'
      }
    }
  } else if (this.allAnswersFound === this.FeedbackService.MULTIPLE_ANSWERS_MISSING) {
    // toutes les réponses n'ont pas été trouvées
    if (this.currentTry < this.step.meta.maxAttempts) {
      sentence = 'some_answers_miss_try_again'
    } else {
      if (this.step.maxAttempts !== 0) {
        sentence = 'max_attempts_reached_see_solution'
      }
    }
  }

  return sentence
}

export default StepShowCtrl
