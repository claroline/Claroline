import {API_REQUEST, url} from '#/main/app/api'

export const actions = {}

actions.sendMessage = (recipients, object, content) => ({
  [API_REQUEST]: {
    type: 'send',
    url: url(['apiv2_message_send'], {ids: recipients.map(recipient => recipient.id)}),
    request: {
      method: 'POST',
      body: JSON.stringify({
        object,
        content
      })
    }
  }
})
