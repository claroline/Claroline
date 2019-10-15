import cloneDeep from 'lodash/cloneDeep'

import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {CORRECTION_INIT, QUESTION_CURRENT, SCORE_UPDATE, FEEDBACK_UPDATE, REMOVE_ANSWERS} from '#/plugin/exo/resources/quiz/correction/store/actions'

const reducer = combineReducers({
  questions: makeReducer(null, {
    [CORRECTION_INIT]: (state, action) => action.correction.questions
  }),
  answers: makeReducer([], {
    [CORRECTION_INIT]: (state, action) => action.correction.answers,

    [SCORE_UPDATE]: (state, action) => {
      const newState = cloneDeep(state)
      const idx = newState.findIndex(answer => answer.id === action.answerId)

      if (-1 < idx) {
        newState[idx]['score'] = action.score
      }

      return newState
    },

    [FEEDBACK_UPDATE]: (state, action) => {
      const newState = cloneDeep(state)
      const idx = newState.findIndex(answer => answer.id === action.answerId)

      if (-1 < idx) {
        newState[idx]['feeedback'] = action.feeedback
      }

      return newState
    },

    [REMOVE_ANSWERS]: (state, action) => state.filter(a => {
      return a.questionId !== action.questionId || a.score === undefined || a.score === null || isNaN(a.score) || a.score.trim() === ''
    })
  }),
  currentQuestionId: makeReducer(null, {
    [QUESTION_CURRENT]: (state, action) => action.id
  })
})

export {
  reducer
}
