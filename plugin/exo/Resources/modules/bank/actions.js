import {makeActionCreator} from '#/main/core/utilities/redux'
import {generateUrl} from '#/main/core/fos-js-router'
import {actions as listActions} from '#/main/core/layout/list/actions'


import {REQUEST_SEND} from '#/main/core/api/actions'

export const QUESTIONS_SHARE = 'QUESTIONS_SHARE'

export const actions = {}

actions.share = makeActionCreator(QUESTIONS_SHARE, 'questions', 'users', 'adminRights')

actions.shareQuestions = (questions, users, adminRights) => ({
  [REQUEST_SEND]: {
    route: ['questions_share'],
    request: {
      method: 'POST',
      body: JSON.stringify({
        questions: questions.map(question => question.id),
        users: users.map(user => user.id),
        adminRights
      })
    },
    success: () => actions.share(questions, users, adminRights)
  }
})

actions.duplicateQuestions = (questions, isModel = 0) => ({
  [REQUEST_SEND]: {
    url: generateUrl('questions_duplicate', {isModel: isModel}),
    request: {
      method: 'POST'
    },
    success: (data, dispatch) => dispatch(listActions.fetchData('questions'))
  }
})

actions.removeQuestions = questions => ({
  [REQUEST_SEND]: {
    route: ['questions_delete'],
    request: {
      method: 'DELETE',
      body: JSON.stringify(questions.map(question => question.id))
    },
    success: (data, dispatch) => {
      //do something better
      dispatch(listActions.changePage(0))
      dispatch(listActions.fetchData('questions'))
    }
  }
})
