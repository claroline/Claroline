import {
  QUIZ_SUMMATIVE,
  SHUFFLE_NEVER,
  SHOW_CORRECTION_AT_VALIDATION,
  SHOW_SCORE_AT_CORRECTION,
  SCORE_SUM
} from './enums'

const quiz = {
  description: '',
  parameters: {
    type: QUIZ_SUMMATIVE,
    showMetadata: true,
    randomOrder: SHUFFLE_NEVER,
    randomPick: SHUFFLE_NEVER,
    pick: 0,
    duration: 0,
    maxAttempts: 0,
    interruptible: false,
    showCorrectionAt: SHOW_CORRECTION_AT_VALIDATION,
    correctionDate: '',
    anonymizeAttempts: false,
    showScoreAt: SHOW_SCORE_AT_CORRECTION,
    showStatistics: false,
    showFullCorrection: true
  }
}

const step = {
  title: '',
  description: '',
  parameters: {
    maxAttempts: 0
  }
}

const item = {
  title: '',
  description: '',
  hints: [],
  feedback: '',
  score: {
    type: SCORE_SUM,
    success: 1,
    failure: 0
  }
}

const hint = {
  value: '',
  penalty: 0
}

const answer = {
  tries: 0,
  usedHints: [],
  data: undefined
}

export default {
  quiz,
  step,
  item,
  hint,
  answer
}
