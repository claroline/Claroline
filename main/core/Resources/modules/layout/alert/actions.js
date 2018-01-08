import invariant from 'invariant'

import {makeActionCreator} from '#/main/core/scaffolding/actions'

import {constants as actionConstants} from '#/main/core/layout/action/constants'
import {constants} from '#/main/core/layout/alert/constants'

export const ALERT_ADD = 'ALERT_ADD'
export const ALERT_REMOVE = 'ALERT_REMOVE'

export const actions = {}

actions.addAlert = (id, status, action = constants.ACTION_GENERIC, title = null, message = null) => {
  // validates params
  invariant(id, 'id is required')
  invariant(status, 'status is required')
  invariant(-1 !== Object.keys(constants.ALERT_STATUS).indexOf(status), 'status must be one of the defined ALERT_STATUS.')
  invariant(-1 !== Object.keys(actionConstants.ACTIONS).indexOf(action), 'action must be one of the defined ACTIONS')
  invariant(Object.keys(constants.ALERT_ACTIONS[action][status]), 'action does not implement the alert status')

  return {
    type: ALERT_ADD,
    id,
    status,
    action,
    message,
    title
  }
}

actions.removeAlert = makeActionCreator(ALERT_REMOVE, 'id')
