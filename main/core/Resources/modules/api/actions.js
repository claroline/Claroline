import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {url} from '#/main/core/api/router'

export const API_REQUEST = 'API_REQUEST'

export const REQUEST_SEND     = 'REQUEST_SEND'
export const RESPONSE_RECEIVE = 'RESPONSE_RECEIVE'

export const actions = {}

actions.sendRequest = makeActionCreator(REQUEST_SEND, 'apiRequest')
actions.receiveResponse = makeActionCreator(RESPONSE_RECEIVE, 'apiRequest', 'status', 'statusText')

actions.uploadFile = (file, uploadUrl = ['apiv2_file_upload'], onSuccess = () => {}) => {
  const formData = new FormData()
  formData.append('file', file)
  formData.append('fileName', file.name)
  formData.append('sourceType', 'uploadedfile')

  return ({
    [API_REQUEST]: {
      url: uploadUrl,
      type: 'upload',
      request: {
        method: 'POST',
        body: formData
      },
      success: (response) => onSuccess(response[0])
    }
  })
}

actions.deleteFile = (fileId, onSuccess = () => {}) => ({
  [API_REQUEST]: {
    url: url(['apiv2_uploadedfile_delete_bulk'], {ids: [fileId]}),
    request: {
      method: 'DELETE'
    },
    success: () => onSuccess({})
  }
})
