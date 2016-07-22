import AbstractCorrectionCtrl from './AbstractCorrectionCtrl'

/**
 * Correction for Cloze question
 * @param {QuestionService}      QuestionService
 * @param {ClozeQuestionService} ClozeQuestionService
 * @constructor
 */
function ClozeCorrectionCtrl(QuestionService, ClozeQuestionService) {
  AbstractCorrectionCtrl.apply(this, arguments)

  this.ClozeQuestionService = ClozeQuestionService
}

// Extends AbstractQuestionCtrl
ClozeCorrectionCtrl.prototype = Object.create(AbstractCorrectionCtrl.prototype)

/**
 * Get the answer for a Hole
 * @param   {Object} hole
 * @returns {Object}
 */
ClozeCorrectionCtrl.prototype.getHoleAnswer = function getHoleAnswer(hole) {
  return this.ClozeQuestionService.getHoleAnswer(this.answer, hole)
}

ClozeCorrectionCtrl.prototype.getHoleFeedback = function getHoleFeedback(hole) {
  var answer = this.getHoleAnswer(hole)

  return this.ClozeQuestionService.getHoleFeedback(this.question, hole, answer)
}

ClozeCorrectionCtrl.prototype.getHoleStats = function getHoleStats(holeId) {
  return this.ClozeQuestionService.getHoleStats(this.question, holeId)
}

ClozeCorrectionCtrl.prototype.getKeywordStats = function getKeywordStats(keyword, holeStats) {
  var stats = null

  if (holeStats && holeStats.keywords) {
    for (var keywordId in holeStats.keywords) {
      if (holeStats.keywords.hasOwnProperty(keywordId)) {
        if (holeStats.keywords[keywordId].id == keyword.id) {
          stats = holeStats.keywords[keywordId]
        }
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

export default ClozeCorrectionCtrl
