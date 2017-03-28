import {
  SHOW_CORRECTION_AT_VALIDATION,
  SHOW_CORRECTION_AT_LAST_ATTEMPT,
  SHOW_CORRECTION_AT_DATE,
  SHOW_SCORE_AT_CORRECTION,
  SHOW_SCORE_AT_VALIDATION
} from './../enums'

export const utils = {
  showCorrection(isAdmin, isFinished, showCorrectionAt, correctionDate) {
    if (isAdmin) {
      return true
    } else if (showCorrectionAt === SHOW_CORRECTION_AT_VALIDATION || showCorrectionAt === SHOW_CORRECTION_AT_LAST_ATTEMPT){
      return isFinished
    } else if (showCorrectionAt === SHOW_CORRECTION_AT_DATE){
      const today = Date.parse(new Date(Date.now()))
      const parsedCorrectionDate = Date.parse(correctionDate)
      return today >= parsedCorrectionDate
    } else {
      return false
    }
  },
  showScore(isAdmin, isFinished, showScoreAt, showCorrectionAt, correctionDate) {
    if (isAdmin) {
      return true
    } else if (showScoreAt === SHOW_SCORE_AT_CORRECTION){
      return utils.showCorrection(isAdmin, isFinished, showCorrectionAt, correctionDate)
    } else if (showScoreAt === SHOW_SCORE_AT_VALIDATION){
      return isFinished
    } else {
      return false
    }
  }
}
