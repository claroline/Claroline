/**
 * API store.
 * Manages the state for api integration.
 */

import {registry} from '#/main/app/store/registry'

import {REQUEST_SEND, RESPONSE_RECEIVE, actions} from '#/main/app/api/store/actions'
import {reducer}   from '#/main/app/api/store/reducer'
import {selectors} from '#/main/app/api/store/selectors'

// append the reducer to the store
registry.add(selectors.STORE_NAME, reducer)

// export store module
export {
  // public actions
  REQUEST_SEND,
  RESPONSE_RECEIVE,
  // action creators
  actions,
  // reducers
  reducer,
  // selectors
  selectors
}
