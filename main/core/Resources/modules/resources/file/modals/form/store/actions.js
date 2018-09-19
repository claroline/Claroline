import {API_REQUEST} from '#/main/app/api'

export const actions = {}

actions.changeFile = (node, file) => {
  const formData = new FormData()
  formData.append('file', file)

  return ({
    [API_REQUEST]: {
      url: ['claro_resource_action_short', {action: 'change_file', id: node.id}],
      type: 'upload',
      request: {
        method: 'POST',
        body: formData,
        headers: new Headers({
          //no Content type for automatic detection of boundaries.
          'X-Requested-With': 'XMLHttpRequest'
        })
      }
    }
  })
}
