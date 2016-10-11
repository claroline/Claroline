/**
 * Paper Show Controller
 * Displays the details of a Paper
 * @param {Object} attempt
 * @param {PaperService} PaperService
 * @constructor
 */
function PaperShowCtrl(attempt, PaperService, UserPaperService) {
  this.PaperService = PaperService
  this.UserPaperService = UserPaperService

  this.paper     = attempt.paper
  this.questions = attempt.questions
  this.steps     = this.PaperService.getPaperSteps()

  this.UserPaperService.setPaper(this.paper)
  this.UserPaperService.setQuestions(this.questions)
  this.showScore = this.UserPaperService.isScoreAvailable(this.paper)
}

PaperShowCtrl.prototype.paper = {}

/**
 * Ordered Questions of the Paper
 * @type {Array}
 */
PaperShowCtrl.prototype.questions = []

/**
 *
 * @type {boolean}
 */
PaperShowCtrl.prototype.showScore = true

PaperShowCtrl.prototype.orderStepQuestions = function (step) {
  return this.UserPaperService.orderStepQuestions(step)
}

/**
 * Check whether a Paper needs a manual correction (if the score of one question is -1)
 */
PaperShowCtrl.prototype.needManualCorrection = function needManualCorrection() {
  return this.PaperService.needManualCorrection(this.paper)
}

PaperShowCtrl.prototype.getQuestionPaper = function getQuestionPaper(question) {
  return this.PaperService.getQuestionPaper(this.paper, question)
}

PaperShowCtrl.prototype.showMinimalCorrection = function() {
  return this.PaperService.getExerciseMeta().minimalCorrection
}

/**
 * Get the score of a Paper
 * @returns {Number}
 */
PaperShowCtrl.prototype.getScore = function getScore() {
  return this.PaperService.getPaperScore(this.paper, this.questions)
}

export default PaperShowCtrl
