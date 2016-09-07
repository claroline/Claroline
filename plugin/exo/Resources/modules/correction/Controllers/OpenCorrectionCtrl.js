import AbstractCorrectionCtrl from './AbstractCorrectionCtrl'

/**
 * Correction for Open Questions
 * @param {QuestionService}     QuestionService
 * @param {OpenQuestionService} OpenQuestionService
 * @constructor
 */
function OpenCorrectionCtrl(QuestionService, OpenQuestionService) {
  AbstractCorrectionCtrl.apply(this, arguments)

  this.OpenQuestionService = OpenQuestionService
}

// Extends AbstractQuestionCtrl
OpenCorrectionCtrl.prototype = Object.create(AbstractCorrectionCtrl.prototype)

OpenCorrectionCtrl.prototype.getKeywordStats = function getKeywordStats(keyword) {
  var stats = null

  if (this.question.stats) {
    for (var i = 0; i < this.question.stats.solutions; i++) {
      if (this.question.stats.solutions[i].id == keyword.id) {
        stats = this.question.stats.solutions[i]
      }
    }

    if (!stats) {
            // No User have chosen this answer
      stats = {
        id: keyword.id,
        count: 0
      }
    }
  }

  return stats
}

export default OpenCorrectionCtrl
