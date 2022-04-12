import {API_REQUEST} from '#/main/app/api'

export const actions = {}

actions.save = (data) => {
  const formData = new FormData()
  formData.append('name', data.name)
  formData.append('code', data.code)
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
