import invariant from 'invariant'
import isFunction from 'lodash/isFunction'
import isString from 'lodash/isString'
import {
  ERROR_AUTH_WINDOW_BLOCKED,
  ERROR_AUTH_WINDOW_CLOSED,
  authenticate
} from '#/main/core/authentication'
import {t} from '#/main/core/translation'
import {generateUrl} from '#/main/core/fos-js-router'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_MESSAGE} from '#/main/core/layout/modal'
import {REQUEST_SEND, actions} from './actions'

const defaultRequest = {
  method: 'GET',
  credentials: 'include'
}

function handleResponse(dispatch, response) {
  dispatch(actions.decrementRequests())
  dispatch(actions.receiveResponse(response))

  if (!response.ok) {
    return Promise.reject(response)
  }

  return response
}

function handleResponseSuccess(data, success) {
  if (success) {
    invariant(isFunction(success), '`success` should be a function')
  }

  return dispatch => {
    if (success) {
      return success(data, dispatch)
    }
  }
}

function handleResponseError(error, failure, request, next) {
  if (failure) {
    invariant(isFunction(failure), '`failure` should be a function')
  } else {
    failure = () => {}
  }

  if (typeof error.status === 'undefined') {
    // if error isn't related to http response, rethrow it
    throw error
  }

  return dispatch => {
    if (error.status === 401) { // authentication needed
      authenticate().then(
        () => doFetch(request, next), // re-execute original request,
        authError => {
          failure(authError, dispatch)
          switch (authError.message) {
            case ERROR_AUTH_WINDOW_BLOCKED:
              return showErrorModal(dispatch, t('request_error_auth_blocked'))
            case ERROR_AUTH_WINDOW_CLOSED:
              return showHttpErrorModal(dispatch, 401)
            default:
              throw authError
          }
        }
      )
    } else {
      failure(error, dispatch)
      showHttpErrorModal(dispatch, error)
    }
  }
}

function showErrorModal(dispatch, message) {
  dispatch(modalActions.showModal(MODAL_MESSAGE, {
    title: t('request_error'),
    bsStyle: 'danger',
    message
  }))
}

function showHttpErrorModal(dispatch, status) {
  showErrorModal(dispatch, [401, 403, 422].indexOf(status) > -1 ?
    t(`request_error_desc_${status}`) :
    t('request_error_desc_default')
  )
}

/**
 * Extracts data from response object.
 *
 * @param {Response} response
 *
 * @returns {mixed}
 */
function getResponseData(response) {
  let data = null

  const contentType = response.headers.get('content-type')
  if (contentType && contentType.indexOf('application/json') !== -1) {
    // Decode JSON
    data = response.json()
  } else {
    // Return raw data (maybe someday we will need to also manage files)
    data = response.text()
  }

  return data // this is a promise
}

function getUrl(url, route) {
  invariant(url || route, 'a `url` or a `route` property is required')

  if (url) {
    invariant(isString(url), '`url` should be a string')

    return url
  }

  invariant(Array.isArray(route), '`route` should be an array')

  return generateUrl(route[0], route[1] ? route[1] : {})
}

function handleBefore(before) {
  return dispatch => {
    if (before) {
      invariant(isFunction(before), '`before` should be a function')
      before(dispatch)
    }

    dispatch(actions.incrementRequests())
  }
}

function getRequest(request = {}) {
  invariant(request instanceof Object, '`request` should be an object')

  // Add default values to request
  return Object.assign({}, defaultRequest, request)
}

function doFetch(requestParameters, next) {
  const {url, route, request, before, success, failure} = requestParameters
  const finalUrl = getUrl(url, route)
  const finalRequest = getRequest(request)

  next(handleBefore(before))

  return fetch(finalUrl, finalRequest)
    .then(response => handleResponse(next, response))
    .then(response => getResponseData(response))
    .then(
      data => next(handleResponseSuccess(data, success)),
      error => next(handleResponseError(error, failure, requestParameters, next))
    )
}

const apiMiddleware = () => next => action => {
  const requestParameters = action[REQUEST_SEND]

  if (typeof requestParameters === 'undefined') {
    return next(action)
  }

  return doFetch(requestParameters, next)
}

export {apiMiddleware}
