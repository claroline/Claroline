import {makeReducer} from '#/main/core/utilities/redux'
import {makeListReducer} from '#/main/core/layout/list/reducer'

import {update} from '#/plugin/exo/utils/utils'

import {
  QUESTIONS_SHARE
} from './actions'

const questionReducer = makeReducer([], {
  [QUESTIONS_SHARE]: (state, action) => {
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
})

const reducer = makeListReducer({
  data: questionReducer
})

export {
  reducer
}
