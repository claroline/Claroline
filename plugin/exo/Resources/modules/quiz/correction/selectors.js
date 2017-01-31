import {createSelector} from 'reselect'

const quizId = state => state.quiz.id
const correction = state => state.correction
const questionsFetched = state => !!state.correction.questions
const questions = createSelector(
  correction,
  (correction) => {
    let data = []
    correction.questions.forEach(q => {
      data.push({
        question: q,
        answers: correction.answers.filter(a => a.questionId === q.id)
      })
    })

    return data
  }
)
const answers = createSelector(
  correction,
  (correction) => {
    return correction.answers.filter(a => a.questionId === correction.currentQuestionId)
  }
)
const currentQuestion = createSelector(
  correction,
  (correction) => {
    return correction.questions ? correction.questions.find(question => question.id === correction.currentQuestionId) : {}
  }
)
const hasCorrection = createSelector(
  correction,
  currentQuestion,
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
  quizId,
  questions,
  answers,
  currentQuestion,
  hasCorrection,
  questionsFetched
}
