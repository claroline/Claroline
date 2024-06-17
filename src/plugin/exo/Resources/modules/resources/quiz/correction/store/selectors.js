import {createSelector} from 'reselect'

import {selectors as quizSelectors} from '#/plugin/exo/resources/quiz/store/selectors'

const STORE_NAME = 'correction'

const quizId = quizSelectors.id

const correction = createSelector(
  [quizSelectors.store],
  (resourceState) => resourceState[STORE_NAME]
)

const questionsFetched = createSelector(
  [correction],
  (correction) => !!correction.questions
)

const questions = createSelector(
  correction,
  (correction) => {
    let data = []

    if (correction.questions) {
      correction.questions.forEach(q => {
        data.push({
          question: q,
          answers: correction.answers.filter(a => a.questionId === q.id)
        })
      })
    }

    return data
  }
)
const answers = createSelector(
  [correction],
  (correction) => {

    return correction.answers ? correction.answers.filter(a => a.questionId === correction.currentQuestionId): []
  }
)

const currentQuestion = createSelector(
  [correction],
  (correction) => {
    return correction.questions ? correction.questions.find(question => question.id === correction.currentQuestionId) : {}
  }
)

const hasCorrection = createSelector(
  [correction, currentQuestion],
  (correction, currentQuestion) => {
    let result = false

    if (correction.answers) {
      correction.answers.forEach(a => {
        if (a.questionId === correction.currentQuestionId &&
          a.score !== undefined &&
          a.score !== null &&
          !isNaN(a.score) &&
          a.score.trim() !== '' &&
          a.score <= currentQuestion.score.max) {

          result = true
        }
      })
    }
    return result
  }
)

export const selectors = {
  STORE_NAME,

  quizId,
  questions,
  answers,
  currentQuestion,
  hasCorrection,
  questionsFetched
}
