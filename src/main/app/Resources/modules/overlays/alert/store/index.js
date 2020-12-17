/**
 * Alert store.
 * Manages the user alerts thrown by the application.
 */

import {ALERT_ADD, ALERT_REMOVE, actions} from '#/main/app/overlays/alert/store/actions'
import {reducer} from '#/main/app/overlays/alert/store/reducer'
import {selectors} from '#/main/app/overlays/alert/store/selectors'

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
