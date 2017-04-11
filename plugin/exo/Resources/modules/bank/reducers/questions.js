import {makeReducer} from '#/main/core/utilities/redux'
import {update} from './../../utils/utils'

import {
  QUESTIONS_SET,
  QUESTIONS_REMOVE,
  QUESTIONS_SHARE
} from './../actions/questions'

function setQuestions(state, action) {
  return action.questions
}

function removeQuestions(state, action) {
  let newState = state
  action.questions.map((questionId) => {
    const pos = newState.findIndex(questionId)
    if (-1 !== pos) {
      newState = update(newState, {$splice: [[pos, 1]]})
    }
  })

  return newState
}

function shareQuestions(state, action) {
  let newState = state

  action.questions.map((questionId, questionIndex) => {
    const question = state.find(question => questionId === question.id)
    action.users.map((user) => {
      let newShare = {
        user: user,
        adminRights: action.adminRights
      }

      // Check if the question is already shared with the user
      let alreadyShared = false
      for (let i = 0; i < question.meta.sharedWith.length;i++) {
        let shared = question.meta.sharedWith[i]
        if (shared.user.id === user.id) {
          newState = update(newState, {
            [questionIndex]: {
              meta: {
                sharedWith: {[i]: {$set: newShare}}
              }
            }
          })
          alreadyShared = true
          break
        }

        if (!alreadyShared) {
          newState = update(newState, {
            [questionIndex]: {
              meta: {
                sharedWith: {$push: [newShare]}
              }
            }
          })
        }
      }
    })
  })

  return newState
}

const questionsReducer = makeReducer([], {
  [QUESTIONS_SET]: setQuestions,
  [QUESTIONS_REMOVE]: removeQuestions,
  [QUESTIONS_SHARE]: shareQuestions
})

export default questionsReducer
