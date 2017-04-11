import {LoadingIndicator} from '#/main/core/loader/loading-indicator'

import {makeReducer} from '#/main/core/utilities/redux'

import {
  REQUESTS_INCREMENT,
  REQUESTS_DECREMENT
} from './actions'

function incrementRequests(state) {
  if (0 === state) {
    LoadingIndicator.show()
  }

  return state + 1
}

function decrementRequests(state) {
  const count = state - 1
  if (0 === count) {
    LoadingIndicator.hide()
  }

  return count
}

export const reducers = {
  currentRequests: makeReducer(0, {
    [REQUESTS_INCREMENT]: incrementRequests,
    [REQUESTS_DECREMENT]: decrementRequests
  })
}
