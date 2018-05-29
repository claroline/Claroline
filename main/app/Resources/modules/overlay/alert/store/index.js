/**
 * Alert store.
 * Manages the user alerts thrown by the application.
 */

import {registry} from '#/main/app/store/registry'

import {ALERT_ADD, ALERT_REMOVE, actions} from '#/main/app/overlay/alert/store/actions'
import {reducer} from '#/main/app/overlay/alert/store/reducer'
import {selectors} from '#/main/app/overlay/alert/store/selectors'

// append the reducer to the store
registry.add(selectors.STORE_NAME, reducer)

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
