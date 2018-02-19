import {API_REQUEST} from '#/main/core/api/actions'

import {constants} from '#/main/core/user/registration/constants'

export const actions = {}

actions.createUser = (user, onCreated = () => {}) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_create_and_login'],
    messages: constants.ALERT_REGISTRATION,
    request: {
      method: 'POST',
      body: JSON.stringify(user)
    },
    success: () => onCreated(),
    error: () => alert('error')
  }
})
