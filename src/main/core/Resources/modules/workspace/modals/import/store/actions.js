import {API_REQUEST} from '#/main/app/api'

export const actions = {}

actions.save = (data) => {
  const formData = new FormData()
  if (data.name) {
    formData.append('name', data.name || null)
  }
  if (data.code) {
    formData.append('code', data.code || null)
  }

  formData.append('archive', data.archive) // this is an uploaded file

  return ({
    [API_REQUEST]: {
      url: ['apiv2_workspace_import'],
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
