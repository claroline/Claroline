import {API_REQUEST} from '#/main/app/api'

import {actions as listActions} from '#/main/app/content/list/store'

export const actions = {}

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
    success: (data, dispatch) => dispatch(listActions.invalidateData('questions'))
  }
})

actions.duplicateQuestions = (questions) => ({
  [API_REQUEST]: {
    url: ['questions_duplicate'],
    request: {
      method: 'POST'
    },
    success: (data, dispatch) => dispatch(listActions.invalidateData('questions'))
  }
})

actions.removeQuestions = questions => ({
  [API_REQUEST]: {
    url: ['questions_delete'],
    request: {
      method: 'DELETE',
      body: JSON.stringify(questions.map(question => question.id))
    },
    success: (data, dispatch) => dispatch(listActions.invalidateData('questions'))
  }
})
