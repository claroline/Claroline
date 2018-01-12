import merge from 'lodash/merge'
import {checkPropTypes} from 'prop-types'

import {getUrl} from '#/main/core/api/router'
import {authenticate} from '#/main/core/api/authentication'
import {makeId} from '#/main/core/scaffolding/id'

import {actions} from '#/main/core/api/actions'
import {ApiRequest as ApiRequestTypes} from '#/main/core/api/prop-types'

/**
 * A callback executed before the request is sent.
 *
 * @param {function} dispatch
 * @param {object}   originalRequest
 * @param {function} before
 *
 * @return {mixed}
 */
function handleBefore(dispatch, originalRequest, before) {
  dispatch(actions.sendRequest(originalRequest))

  return before(dispatch)
}

/**
 * A callback executed when a response is received.
 *
 * @param {function} dispatch
 * @param {object}   response
 * @param {object}   originalRequest
 *
 * @return {object}
 */
function handleResponse(dispatch, response, originalRequest) {
  dispatch(actions.receiveResponse(originalRequest, response.status, response.statusText))

  if (!response.ok) {
    return Promise.reject(response)
  }

  return getResponseData(response)
}

/**
 * A callback executed when a success response is received.
 *
 * @param {function} dispatch
 * @param {mixed}    responseData
 * @param {function} success
 *
 * @return {mixed}
 */
function handleResponseSuccess(dispatch, responseData, success) {
  success(responseData, dispatch)

  return responseData
}

/**
 * A callback executed when an error response is received.
 *
 * @param {function} dispatch
 * @param {object}   responseError
 * @param {object}   originalRequest
 * @param {function} error
 *
 * @return {mixed}
 */
function handleResponseError(dispatch, responseError, originalRequest, error) {
  if (!responseError.isCanceled) {
    if (typeof responseError.status === 'undefined') {
      // if error isn't related to http response, rethrow it
      throw responseError
    }

    if (401 === responseError.status) { // authentication needed
      return authenticate()
        .then(
          () => apiFetch(originalRequest, dispatch), // re-execute original request,
          authError => {
            error(authError, dispatch)

            return authError
          }
        )
    } else {
      return getResponseData(responseError) // get error data if any
        .then(errorData => {
          error(errorData, dispatch)

          return errorData
        })
    }
  }
}

/**
 * Extracts data from response object.
 *
 * @param {object} response
 *
 * @returns {Promise}
 */
function getResponseData(response) {
  if (204 !== response.status) {
    const contentType = response.headers && response.headers.get('content-type')
    if (contentType && contentType.indexOf('application/json') !== -1) {
      // Decode JSON
      return response.json()
    } else {
      // Return raw data (maybe someday we will need to also manage files)
      return response.text()
    }
  }

  return Promise.resolve(null)
}

/**
 * Sends a Request to a backend API.
 * NB. The maim difference with regular `fetch` is the request is managed by Redux.
 *
 * @param {object}   apiRequest - the request to send (@see `ApiRequest` from '#/main/core/api/prop-types" for the expected format).
 * @param {function} dispatch   - the redux action dispatcher
 */
function apiFetch(apiRequest, dispatch) {
  // add default parameters
  const requestParameters = merge({}, ApiRequestTypes.defaultProps, apiRequest)

  // generate id for the request
  if (!requestParameters.id) {
    requestParameters.id = makeId()
  }

  // validate parameters
  checkPropTypes(ApiRequestTypes.propTypes, requestParameters, 'prop', 'API_REQUEST')

  handleBefore(dispatch, requestParameters, requestParameters.before)

  return fetch(getUrl(requestParameters.url), requestParameters.request)
    .then(
      response => handleResponse(dispatch, response, requestParameters)
    )
    .then(
      responseData  => handleResponseSuccess(dispatch, responseData, requestParameters.success),
      responseError => handleResponseError(dispatch, responseError, requestParameters, requestParameters.error)
    )
}

export {
  apiFetch
}