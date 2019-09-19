import {API_REQUEST} from '#/main/app/api'

export const actions = {}

actions.changeFile = (node, file) => ({
  [API_REQUEST]: {
    url: ['claro_resource_action_short', {action: 'change_file', id: node.id}],
    request: {
      method: 'PUT',
      body: JSON.stringify({file: file})
    }
  }
})
