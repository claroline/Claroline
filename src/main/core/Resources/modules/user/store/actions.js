import {API_REQUEST} from '#/main/app/api'

import {route} from '#/main/core/user/routing'

export const actions = {}

actions.updatePassword = (user, plainPassword) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_update', {id: user.id, event: 'updatePassword'}],
    request: {
      method: 'PUT',
      body: JSON.stringify(Object.assign({}, user, {plainPassword}))
    }
  }
})

actions.updatePublicUrl = (user, publicUrl, redirect = false, callback = () => false) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_update', {id: user.id}],
    request: {
      method: 'PUT',
      body: JSON.stringify(Object.assign({}, user, {meta: {publicUrl: publicUrl, publicUrlTuned: true}}))
    },
    success: (response) => {
      callback(response)

      if (redirect) {
        window.location = '#' + route(response)
      }
    }
  }
})
