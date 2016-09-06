/**
 * Paper Show Controller
 * Displays the details of a Paper
 * @param {Object} paperPromise
 * @param {PaperService} PaperService
 * @constructor
 */
function PaperShowCtrl(paperPromise, PaperService) {
  this.PaperService = PaperService

  this.paper        = paperPromise.paper
  this.questions    = this.PaperService.orderQuestions(this.paper, paperPromise.questions)
}

PaperShowCtrl.prototype.paper = {}

/**
 * Ordered Questions of the Paper
 * @type {Array}
 */
PaperShowCtrl.prototype.questions = []

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
