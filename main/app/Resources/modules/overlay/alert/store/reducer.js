import cloneDeep from 'lodash/cloneDeep'

import {makeReducer} from '#/main/app/store/reducer'

import {constants} from '#/main/app/overlay/alert/constants'
import {ALERT_ADD, ALERT_REMOVE} from '#/main/app/overlay/alert/store/actions'

const reducer = makeReducer([], {
  [ALERT_ADD]: (state, action) => {
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
  },
  [ALERT_REMOVE]: (state, action) => {
    const newState = cloneDeep(state)

    const alertIndex = newState.findIndex(alert => action.id === alert.id)
    if (-1 !== alertIndex) {
      newState.splice(alertIndex, 1)
    }

    return newState
  }
})

export {
  reducer
}
