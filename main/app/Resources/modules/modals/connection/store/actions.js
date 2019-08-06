import {API_REQUEST} from '#/main/app/api'

export const actions = {}

actions.discard = (messageId) => ({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_connection_message_discard', {id: messageId}],
    request: {
      method: 'PUT'
    }
  }
})