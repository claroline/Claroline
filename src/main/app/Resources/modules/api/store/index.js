/**
 * API store.
 * Manages api integration in redux env.
 */

import {REQUEST_SEND, RESPONSE_RECEIVE, actions} from '#/main/app/api/store/actions'
import {reducer} from '#/main/app/api/store/reducer'
import {selectors} from '#/main/app/api/store/selectors'

// export store module
export {
  // public actions
  REQUEST_SEND,
  RESPONSE_RECEIVE,

  actions,
  reducer,
  selectors
}
