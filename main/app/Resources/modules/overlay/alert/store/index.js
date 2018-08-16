/**
 * Alert store.
 * Manages the user alerts thrown by the application.
 */

import {ALERT_ADD, ALERT_REMOVE, actions} from '#/main/app/overlay/alert/store/actions'
import {reducer} from '#/main/app/overlay/alert/store/reducer'
import {selectors} from '#/main/app/overlay/alert/store/selectors'

// export store module
export {
  // public actions
  ALERT_ADD,
  ALERT_REMOVE,
  // action creators
  actions,
  // reducers
  reducer,
  // selectors
  selectors
}
