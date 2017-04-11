import {makeActionCreator} from '#/main/core/utilities/redux'

import {REQUEST_SEND} from './../../api/actions'

export const QUESTIONS_SHARE = 'QUESTIONS_SHARE'
export const QUESTIONS_SET = 'QUESTIONS_SET'
export const QUESTIONS_REMOVE = 'QUESTIONS_REMOVE'

export const actions = {}

actions.setQuestions = makeActionCreator(QUESTIONS_SET, 'questions')
actions.removeQuestions = makeActionCreator(QUESTIONS_REMOVE, 'questions')
actions.share = makeActionCreator(QUESTIONS_SHARE, 'questions', 'users', 'adminRights')

actions.shareQuestions = (questions, users, adminRights) => ({
  [REQUEST_SEND]: {
    route: ['questions_share'],
    request: {
      method: 'POST',
      body: JSON.stringify({
        adminRights,
        questions,
        users: users.map(user => user.id)
      })
    },
    success: () => actions.share(questions, users, adminRights)
  }
})

actions.deleteQuestions = questions => ({
  [REQUEST_SEND]: {
    route: ['questions_delete'],
    request: {
      method: 'DELETE',
      body: JSON.stringify(questions)
    },
    success: () => actions.removeQuestions(questions)
  }
})
