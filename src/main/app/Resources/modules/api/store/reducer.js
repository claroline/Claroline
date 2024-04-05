import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {
  REQUEST_SEND,
  RESPONSE_RECEIVE
} from '#/main/app/api/store/actions'

const reducer = combineReducers({
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
    [REQUEST_SEND]: (state, action) => {
      if (!action.apiRequest.silent) {
        return state + 1
      }
      return state
    },

    /**
     * Decrements the number of pending requests when a new response is received.
     *
     * @param {number} state
     *
     * @return {number}
     */
    [RESPONSE_RECEIVE]: (state, action) => {
      if (!action.apiRequest.silent) {
        return state - 1
      }
      return state
    }
  })
})

export {
  reducer
}
