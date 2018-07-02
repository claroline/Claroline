import {makeActionCreator} from '#/main/app/store/actions'
import {url} from '#/main/app/api/router'

import {actions as alertActions} from '#/main/app/overlay/alert/store'

import {constants as alertConstants} from '#/main/app/overlay/alert/constants'
import {constants} from '#/main/app/api/constants'

// actions
export const REQUEST_SEND     = 'REQUEST_SEND'
export const RESPONSE_RECEIVE = 'RESPONSE_RECEIVE'

// action creators
export const actions = {}

// request actions
actions.processRequest = makeActionCreator(REQUEST_SEND, 'apiRequest')
actions.sendRequest = (apiRequest) => (dispatch) => {
  if (!apiRequest.silent) {
    // display a user alert
    const currentAction = apiRequest.type || constants.HTTP_ACTIONS[apiRequest.request.method]
    const customMessages = apiRequest.messages[alertConstants.ALERT_STATUS_PENDING]

    dispatch(alertActions.addAlert(
      // id
      apiRequest.id + alertConstants.ALERT_STATUS_PENDING,
      // status
      alertConstants.ALERT_STATUS_PENDING,
      // action
      currentAction,
      // title
      customMessages && customMessages.title,
      // message
      customMessages && customMessages.message
    ))
  }

  return dispatch(actions.processRequest(apiRequest))
}

// response actions
actions.processResponse = makeActionCreator(RESPONSE_RECEIVE, 'apiRequest', 'status', 'statusText')
actions.receiveResponse = (apiRequest, status, statusText) => dispatch => {
  if (!apiRequest.silent) {
    // removes pending alert
    dispatch(alertActions.removeAlert(
      apiRequest.id + alertConstants.ALERT_STATUS_PENDING
    ))

    // add new status alert
    const currentAction = apiRequest.type || constants.HTTP_ACTIONS[apiRequest.request.method]
    const currentStatus = constants.HTTP_ALERT_STATUS[status]

    if (currentStatus && alertConstants.ALERT_ACTIONS[currentAction][currentStatus]) {
      // the current action define a message for the status
      const customMessages = apiRequest.messages[currentStatus]

      dispatch(alertActions.addAlert(
        // id
        apiRequest.id + currentStatus,
        // status
        currentStatus,
        // action
        currentAction,
        // title
        customMessages && customMessages.title,
        // message
        customMessages && customMessages.message
      ))
    }
  }

  return dispatch(actions.processResponse(apiRequest, status, statusText))
}

// file actions
actions.uploadFile = (file, uploadUrl = ['apiv2_file_upload'], onSuccess = () => {}) => {
  const formData = new FormData()
  formData.append('file', file)
  formData.append('fileName', file.name)
  formData.append('sourceType', 'uploadedfile')

  return ({
    [constants.API_REQUEST]: {
      url: uploadUrl,
      type: 'upload',
      request: {
        method: 'POST',
        body: formData,
        headers: new Headers({
          //no Content type for automatic detection of boundaries.
          'X-Requested-With': 'XMLHttpRequest'
        })
      },
      success: (response) => Array.isArray(response) ? onSuccess(response[0]) : onSuccess(response)
    }
  })
}

actions.deleteFile = (fileId, onSuccess = () => {}) => ({
  [constants.API_REQUEST]: {
    url: url(['apiv2_publicfile_delete_bulk'], {ids: [fileId]}),
    request: {
      method: 'DELETE'
    },
    success: () => onSuccess({})
  }
})
