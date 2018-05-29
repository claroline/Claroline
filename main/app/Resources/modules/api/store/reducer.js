import {makeReducer} from '#/main/app/store/reducer'

import {
  REQUEST_SEND,
  RESPONSE_RECEIVE
} from '#/main/app/api/store/actions'

const reducer = {
  /**
   * Reduces the current number of pending requests.
   */
  currentRequests: makeReducer(0, {
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
}

export {
  reducer
}
