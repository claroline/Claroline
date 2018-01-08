import {makeReducer} from '#/main/core/scaffolding/reducer'

import {
  REQUEST_SEND,
  RESPONSE_RECEIVE
} from './actions'

/**
 * Reduces the current number of pending requests.
 */
const reducer = makeReducer(0, {
  /**
   * Increments the number of pending requests when a new request is sent.
   *
   * @param {number} state
   *
   * @return {number}
   */
  [REQUEST_SEND]: (state) => state + 1,

  /**
   * Decrements the number of pending requests when a new response is received.
   *
   * @param {number} state
   *
   * @return {number}
   */
  [RESPONSE_RECEIVE]: (state) => state - 1
})

export {
  reducer
}
