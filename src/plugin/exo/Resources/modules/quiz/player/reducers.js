import moment from 'moment'
import merge from 'lodash/merge'

import {makeReducer} from '#/main/app/store/reducer'
import {getApiFormat} from '#/main/app/intl/date'
import {makeId} from '#/main/core/scaffolding/id'

import {isQuestionType} from '#/plugin/exo/items/item-types'
import {UserAnswer} from '#/plugin/exo/resources/quiz/prop-types'

import {
  TEST_MODE_SET,
  ATTEMPT_START,
  ATTEMPT_FINISH,
  STEP_OPEN,
  ANSWER_UPDATE,
  ANSWERS_SUBMIT,
  STEP_FEEDBACK,
  HINT_USE
} from '#/plugin/exo/quiz/player/actions'

export const reducers = {
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
        if (action.answers.hasOwnProperty(questionId)) {
          let answer = action.answers[questionId]

          updatedAnswers[questionId] = merge({}, answer, {
            _touched: false,
            tries: answer.tries + 1
          })
        }
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
}
