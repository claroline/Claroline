import {API_REQUEST} from '#/main/app/api'

import {constants} from '#/main/app/security/registration/constants'

export const actions = {}

actions.createUser = (user, onCreated = () => {}) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_create_and_login'],
    messages: constants.ALERT_REGISTRATION,
    request: {
      method: 'POST',
      body: JSON.stringify(user)
    },
    success: onCreated
  }
})
