import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {actions as listActions} from '#/main/core/data/list/actions'

import {API_REQUEST} from '#/main/core/api/actions'

export const QUESTIONS_SHARE = 'QUESTIONS_SHARE'

export const actions = {}

actions.share = makeActionCreator(QUESTIONS_SHARE, 'questions', 'users', 'adminRights')

actions.shareQuestions = (questions, users, adminRights) => ({
  [API_REQUEST]: {
    url: ['questions_share'],
    request: {
      method: 'POST',
      body: JSON.stringify({
        questions: questions.map(question => question.id),
        users: users.map(user => user.id),
        adminRights
      })
    },
    success: (data, dispatch) => dispatch(actions.share(questions, users, adminRights))
  }
})

actions.duplicateQuestions = (questions, isModel = 0) => ({
  [API_REQUEST]: {
    url: ['questions_duplicate', {isModel: isModel}],
    request: {
      method: 'POST'
    },
    success: (data, dispatch) => dispatch(listActions.fetchData('questions'))
  }
})

actions.removeQuestions = questions => ({
  [API_REQUEST]: {
    url: ['questions_delete'],
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
