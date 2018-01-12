import cloneDeep from 'lodash/cloneDeep'

import {makeReducer} from '#/main/core/scaffolding/reducer'

import {
  REQUEST_SEND,
  RESPONSE_RECEIVE
} from '#/main/core/api/actions'
import {constants as actionConstants} from '#/main/core/layout/action/constants'

import {
  ALERT_ADD,
  ALERT_REMOVE
} from './actions'
import {constants} from '#/main/core/layout/alert/constants'

const addAlert = (state, action) => {
  const newState = cloneDeep(state)

  const defaultAlert = constants.ALERT_ACTIONS[action.action][action.status]

  newState.push({
    id: action.id,
    status: action.status,
    action: action.action,
    message: action.message || defaultAlert.message,
    title: action.title || defaultAlert.title
  })

  return newState
}

const removeAlert = (state, action) => {
  const newState = cloneDeep(state)

  const alertIndex = newState.findIndex(alert => action.id === alert.id)
  if (-1 !== alertIndex) {
    newState.splice(alertIndex, 1)
  }

  return newState
}

const reducer = makeReducer([], {
  // API alerts
  [REQUEST_SEND]: (state, action) => {
    if (!action.apiRequest.silent) {
      const currentAction = action.apiRequest.type || actionConstants.HTTP_ACTIONS[action.apiRequest.request.method]
      const customMessages = action.apiRequest.messages[constants.ALERT_STATUS_PENDING]

      return addAlert(state, {
        id: action.apiRequest.id + constants.ALERT_STATUS_PENDING,
        status: constants.ALERT_STATUS_PENDING,
        action: currentAction,
        title: customMessages && customMessages.title ? customMessages.title : null,
        message: customMessages && customMessages.message ? customMessages.message : null
      })
    }

    return state
  },

  [RESPONSE_RECEIVE]: (state, action) => {
    if (!action.apiRequest.silent) {
      // remove pending alert
      const newState = removeAlert(state, {
        id: action.apiRequest.id + constants.ALERT_STATUS_PENDING
      })

      // add new status alert
      const currentAction = action.apiRequest.type || actionConstants.HTTP_ACTIONS[action.apiRequest.request.method]
      const currentStatus = constants.HTTP_ALERT_STATUS[action.status]

      if (currentStatus && constants.ALERT_ACTIONS[currentAction][currentStatus]) {
        // the current action define a message for the status
        const customMessages = action.apiRequest.messages[currentStatus]

        return addAlert(newState, {
          id: action.apiRequest.id + currentStatus,
          status: currentStatus,
          action: currentAction,
          title: customMessages && customMessages.title ? customMessages.title : null,
          message: customMessages && customMessages.message ? customMessages.message : null
        })
      }

      return newState
    }

    return state
  },

  // Client alerts
  [ALERT_ADD]: addAlert,
  [ALERT_REMOVE]: removeAlert
})

export {
  reducer
}
