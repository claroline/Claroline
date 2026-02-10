import omit from 'lodash/omit'

import {makeInstanceActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const API_FETCH_PENDING = 'API_FETCH_PENDING'
export const API_FETCH_FULFILLED = 'API_FETCH_FULFILLED'
export const API_FETCH_RELOAD = 'API_FETCH_RELOAD'
export const API_FETCH_FAILED = 'API_FETCH_FAILED'
export const API_FETCH_INVALIDATE = 'API_FETCH_INVALIDATE'

export const actions = {}

/**
 * Loads API response into the store
 */
actions.load = makeInstanceActionCreator(API_FETCH_FULFILLED, 'response')

/**
 * Loads new data in the store.
 */
actions.reload = makeInstanceActionCreator(API_FETCH_RELOAD, 'data')

actions.invalidate = makeInstanceActionCreator(API_FETCH_INVALIDATE)

/**
 * Starts an API call.
 */
actions.request = makeInstanceActionCreator(API_FETCH_PENDING)

actions.fail = makeInstanceActionCreator(API_FETCH_FAILED, 'error', 'errorCode', 'data')

/**
 * Calls API.
 */
actions.fetch = (name, url, silent = false) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url,
    silent: silent,
    request: {
      method: 'GET'
    },
    before: () => dispatch(actions.request(name)),
    success: (response) => dispatch(actions.load(name, response)),
    error: (response, errorStatus) => {
      if (typeof response === 'object' && response.error) {
        const data = response.data ? response.data : omit(response, 'error')
        return dispatch(actions.fail(name, response.error, errorStatus, data))
      }

      return dispatch(actions.fail(name, response, errorStatus, null))
    }
  }
})
