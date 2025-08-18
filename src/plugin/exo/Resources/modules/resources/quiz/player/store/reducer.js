import merge from 'lodash/merge'
import moment from 'moment/moment'

import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeId} from '#/main/app/utils/id'
import {getApiFormat} from '#/main/app/intl'

import {
  ANSWER_UPDATE, ANSWERS_SUBMIT,
  ATTEMPT_FINISH,
  ATTEMPT_START, HINT_USE,
  STEP_FEEDBACK,
  STEP_OPEN,
  TEST_MODE_SET
} from '#/plugin/exo/resources/quiz/player/store/actions'
import {isQuestionType} from '#/plugin/exo/items/item-types'
import {UserAnswer} from '#/plugin/exo/resources/quiz/prop-types'

const reducer = combineReducers({
  // the base evaluation attempt
  attempt: makeReducer(null, {
    [ATTEMPT_FINISH]: (state, action) => action.attempt
  }),
  testMode: makeReducer(false, {
    [TEST_MODE_SET]: (state, action) => action.testMode
  }),
  currentStep: makeReducer({}, {
    [STEP_OPEN]: (state, action) => ({
      id: action.step.id,
      feedbackEnabled: false
    }),
    [STEP_FEEDBACK]: (state) => Object.assign({}, state, {
      feedbackEnabled: true
    })
  }),
  paper: makeReducer({}, {
    [ATTEMPT_START]: (state, action) => action.paper,
    [ATTEMPT_FINISH]: (state, action) => merge({}, state, {
      finished: true,
      endDate: (action.paper.endDate ? action.paper.endDate : moment().format(getApiFormat())),
      score: action.paper.score
    })
  }),
  answers: makeReducer({}, {
    [ATTEMPT_START]: (state, action) => action.answers,
    [STEP_OPEN]: (state, action) => {
      const newAnswers = action.step.items.reduce((acc, item) => {
        if (!state[item.id] && isQuestionType(item.type)) {
          acc[item.id] = merge({}, UserAnswer.defaultProps, {
            id: makeId(),
            questionId: item.id,
            _touched: true
          })
        }

        return acc
      }, {})

      return merge({}, state, newAnswers)
    },
    [ANSWER_UPDATE]: (state, action) => {
      const newAnswer = merge({}, state)
      newAnswer[action.questionId].data = action.answerData
      newAnswer[action.questionId]._touched = true

      return newAnswer
    },
    [ANSWERS_SUBMIT]: (state, action) => {
      const updatedAnswers = {}
      for (let questionId in action.answers) {
        let answer = action.answers[questionId]

        updatedAnswers[questionId] = merge({}, answer, {
          _touched: false,
          tries: answer.tries + 1
        })
      }

      return merge({}, state, updatedAnswers)
    },
    [HINT_USE]: (state, action) => {
      let answer
      if (!state[action.questionId]) {
        answer = merge({}, UserAnswer.defaultProps, {
          id: makeId(),
          questionId: action.questionId
        })
      } else {
        answer = merge({}, state[action.questionId])
      }

      answer.usedHints.push(action.hint)

      return merge({}, state, {
        [action.questionId]: answer
      })
    }
  })
})

export {
  reducer
}
