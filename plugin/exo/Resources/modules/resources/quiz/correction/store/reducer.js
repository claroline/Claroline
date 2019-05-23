import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {CORRECTION_INIT, QUESTION_CURRENT, SCORE_UPDATE, FEEDBACK_UPDATE, REMOVE_ANSWERS} from '#/plugin/exo/resources/quiz/correction/store/actions'

const reducer = combineReducers({
  questions: makeReducer(null, {
    [CORRECTION_INIT]: (state, action) => action.correction.questions
  }),
  answers: makeReducer([], {
    [CORRECTION_INIT]: (state, action) => action.correction.answers,

    [SCORE_UPDATE]: (state, action) => state.answers.map((answer) => {
      if (answer.id === action.answerId) {
        return Object.assign({}, answer, {score: action.score})
      } else {
        return answer
      }
    }),

    [FEEDBACK_UPDATE]: (state, action) => state.answers.map((answer) => {
      if (answer.id === action.answerId) {
        return Object.assign({}, answer, {feedback: action.feedback})
      } else {
        return answer
      }
    }),

    [REMOVE_ANSWERS]: (state, action) => {
      const question = state.questions.find(q => q.id === action.questionId)

      return state.answers.filter(a =>
        a.questionId !== action.questionId || a.score === undefined || a.score === null || isNaN(a.score) || a.score.trim() === '' || a.score > question.score.max
      )
    }
  }),
  currentQuestionId: makeReducer(null, {
    [QUESTION_CURRENT]: (state, action) => action.id
  })
})

export {
  reducer
}
