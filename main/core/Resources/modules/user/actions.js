import {API_REQUEST} from '#/main/app/api'

import {url} from '#/main/app/api'

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

actions.updatePublicUrl = (user, publicUrl, redirect = false) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_update', {id: user.id}],
    request: {
      method: 'PUT',
      body: JSON.stringify(Object.assign({}, user, {meta: {publicUrl: publicUrl, publicUrlTuned: true}}))
    },
    success: () => {
      if (redirect) {
        window.location = url(['claro_user_profile', {publicUrl: publicUrl}]) // TODO : find better
      }
    }
  }
})
