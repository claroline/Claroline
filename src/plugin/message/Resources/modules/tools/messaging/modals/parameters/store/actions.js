import {API_REQUEST} from '#/main/app/api'

export const actions = {}

actions.setMailNotification = (user, mailNotified) => ({
  [API_REQUEST]: {
    url: ['apiv2_user_update', {id: user.id}],
    request: {
      body: JSON.stringify(Object.assign({}, user, {meta: {mailNotified: mailNotified}})),
      method: 'PUT'
    }
  }
})
