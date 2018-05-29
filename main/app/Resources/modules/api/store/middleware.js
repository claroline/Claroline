import {constants} from '#/main/app/api/constants'
import {apiFetch} from '#/main/app/api/fetch'

/**
 * Declares a new middleware that will automatically catch actions
 * which returns an API_REQUEST.
 *
 * @see `ApiRequest` from '#/main/app/api/prop-types" to know the expected request format.
 */
const apiMiddleware = () => next => action => {
  const requestParameters = action[constants.API_REQUEST]

  if (typeof requestParameters === 'undefined') {
    // this is not an api request action, lets pass it without additional processing
    return next(action)
  }

  // processes the api request
  return apiFetch(requestParameters, next)
}

export {
  apiMiddleware
}
