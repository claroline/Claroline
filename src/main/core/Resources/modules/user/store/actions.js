import {API_REQUEST} from '#/main/app/api'

export const actions = {}

actions.updatePassword = (user, plainPassword) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_update', {id: user.id}],
    request: {
      method: 'PUT',
      body: JSON.stringify(Object.assign({}, user, {plainPassword}))
    }
  }
})
