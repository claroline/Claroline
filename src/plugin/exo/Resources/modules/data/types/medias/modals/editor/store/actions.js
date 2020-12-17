import {API_REQUEST} from '#/main/app/api'

const actions = {}

actions.saveFile = (file) => {
  return (dispatch) => {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('fileName', file.name)
    formData.append('sourceType', 'exo_item_object')

    return Promise.resolve(dispatch({
      [API_REQUEST]: {
        url: ['upload_public_file'],
        request: {
          method: 'POST',
          body: formData,
          headers: new Headers({
            //no Content type for automatic detection of boundaries.
            'X-Requested-With': 'XMLHttpRequest'
          })
        },
        success: (url) => url
      }
    }))
  }
}
export {
  actions
}