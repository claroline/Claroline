import {API_REQUEST} from '#/main/app/api'

export const actions = {}

actions.createFiles = (parent, files, onSuccess) => {
  const formData = new FormData()
  files.forEach((file, index) => formData.append(`files[${index}]`, file))

  return ({
    [API_REQUEST]: {
      url: ['apiv2_resource_files_create', {parent: parent.id}],
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
