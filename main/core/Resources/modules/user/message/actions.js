import {API_REQUEST} from '#/main/core/api/actions'

export const actions = {}

actions.sendMessage = (recipients, object, content) => ({
  [API_REQUEST]: {
    type: 'send',
    url: [], // todo find the correct one
    request: {
      method: 'POST',
      body: JSON.stringify({
        object,
        content
      })
    }
  }
})