import {API_REQUEST} from '#/main/app/api'

export const actions = {}

actions.createFiles = (parent, files, onSuccess) => {
  const formData = new FormData()
  files.forEach((file, index) => formData.append(index, file))

  return ({
    [API_REQUEST]: {
      url: ['claro_resource_action', {action: 'add_files', id: parent.id}],
      type: 'upload',
      request: {
        method: 'POST',
        body: formData,
        headers: new Headers({
          //no Content type for automatic detection of boundaries.
          'X-Requested-With': 'XMLHttpRequest'
        })
      },
      success: (response) => onSuccess(response)
    }
  })
}
