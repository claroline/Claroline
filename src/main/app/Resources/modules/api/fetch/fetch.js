import merge from 'lodash/merge'
import {checkPropTypes} from 'prop-types'

import {url} from '#/main/app/api/router'
import {makeId} from '#/main/core/scaffolding/id'

import {actions} from '#/main/app/api/store'
import {MODAL_LOGIN} from '#/main/app/modals/login'
import {actions as modalActions} from '#/main/app/overlays/modal/store/actions'
import {ApiRequest as ApiRequestTypes} from '#/main/app/api/prop-types'

/**
 * A callback executed before the request is sent.
 *
 * @param {function} dispatch
 * @param {object}   originalRequest
 * @param {function} before
 *
 * @return {*}
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
 * A callback executed when a response is received.
 *
 * @param {object}   response
 *
 * @return {object}
 */
function handleDownload(response) {
  const disposition = response.headers && response.headers.get('Content-Disposition')

  const n = disposition.lastIndexOf('=')
  const name = disposition.substring(n + 1)

  // The actual download
  return response.blob().then(blob => {
    const link = document.createElement('a')
    link.href = window.URL.createObjectURL(blob)
    link.download = name

    document.body.appendChild(link)

    link.click()
    link.remove()
  })
}

/**
 * A callback executed when a success response is received.
 *
 * @param {function} dispatch
 * @param {*}        responseData
 * @param {function} success
 *
 * @return {*}
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
 * @return {*}
 */
function handleResponseError(dispatch, responseError, originalRequest, error) {
  if (!responseError.isCanceled) {
    if (typeof responseError.status === 'undefined') {
      // if error isn't related to http response, rethrow it
      throw responseError
    }

    if (401 === responseError.status && originalRequest.forceReauthenticate) {
      // authentication needed
      return new Promise(function (resolve, reject) {
        dispatch(modalActions.showModal(MODAL_LOGIN, {
          onLogin: () => resolve(apiFetch(originalRequest, dispatch)), // re-execute original request
          onAbort: () => {
            // user still not logged, forward the original error
            return getResponseData(responseError) // get error data if any
              .then(errorData => {
                error(errorData, responseError.status, dispatch)

                return reject(errorData)
              })
          }
        }))
      })
    }

    return getResponseData(responseError) // get error data if any
      .then(errorData => {
        error(errorData, responseError.status, dispatch)

        return Promise.reject(errorData)
      })
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
    if (contentType) {
      if (contentType.indexOf('application/json') !== -1) {
        // Decode JSON
        return response.json()
      }

      if (contentType.indexOf('text') !== -1) {
        return response.text()
      }

      return handleDownload(response)
    }
  }

  return Promise.resolve(null)
}

/**
 * Sends a Request to a backend API.
 * NB. The maim difference with regular `fetch` is the request is managed by Redux.
 *
 * @param {object}   apiRequest - the request to send (@see `ApiRequest` from '#/main/app/api/prop-types" for the expected format).
 * @param {function} dispatch   - the redux action dispatcher
 *
 * @todo integrates makeCancelable by default
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

  return fetch(url(requestParameters.url), requestParameters.request)
    .then(
      response =>  handleResponse(dispatch, response, requestParameters)
    )
    .then(
      responseData  => handleResponseSuccess(dispatch, responseData, requestParameters.success),
      responseError => handleResponseError(dispatch, responseError, requestParameters, requestParameters.error)
    )
}

export {
  apiFetch
}
