import {PropTypes as T} from 'prop-types'

import {constants} from '#/main/app/api/constants'

/**
 * Definition of the API request configuration object.
 *
 * @type {object}
 */
const ApiRequest = {
  propTypes: {
    /**
     * The unique identifier of the request.
     * If none is provided, we will generate an UUID.
     */
    id: T.string,

    /**
     * The action type to retrieve the correct set of default alerts.
     * If not provided, it will be retrieved from the HTTP method used.
     */
    type: T.oneOf(
      Object.keys(constants.API_ACTIONS)
    ),

    /**
     * The target of the request.
     * Either a plain URL or a route array.
     */
    url: T.oneOfType([T.string, T.array]).isRequired,

    /**
     * The Request object to send.
     * @see `fetch()` documentation for more info.
     */
    request: T.shape({
      method: T.oneOf(
        Object.keys(constants.HTTP_ACTIONS)
      ),
      body: T.oneOfType([T.string, T.object]),
      credentials: T.string
    }),

    /**
     * Disables all automatic messages for the request.
     * This permits a manual management of feedback if the default one does not handle your use case.
     */
    silent: T.bool,

    /**
     * Displays the login modal if API returns authentication errors (aka 401 http status).
     * This is mostly used to avoid login errors to reopen another login modal.
     */
    forceReauthenticate: T.bool,

    /**
     * An object permitting to override the messages displayed to the user
     * during the lifecycle of the Request.
     * Each key in the object is the status name to override.
     */
    messages: T.objectOf(T.shape({
      title: T.string,
      message: T.string
    })),

    /**
     * A callback to execute before the Request is sent.
     * It receives the redux `dispatch` method as first argument.
     */
    before: T.func,

    /**
     * A callback to execute if a success Response is returned.
     * It receives the response data and the redux `dispatch` method as first argument.
     */
    success: T.func,

    /**
     * A callback to execute if an error Response is returned.
     * It receives the response error and the redux `dispatch` method as first argument.
     */
    error: T.func
  },
  defaultProps: {
    silent: false,
    forceReauthenticate: true,
    request: {
      method: 'GET',
      credentials: 'include',
      headers: new Headers({
        'Content-Type': 'application/json; charset=utf-8',
        // next header is required for symfony to recognize our requests as XMLHttpRequest
        // there is no spec about possible values, but this is the one expected by symfony
        // @see Symfony\Component\HttpFoundation\Request::isXmlHttpRequest
        'X-Requested-With': 'XMLHttpRequest'
      })
    },
    messages: {},
    before: () => true,
    success: (responseData) => responseData,
    error: (responseError) => responseError
  }
}

export {
  ApiRequest
}
