/**
 * API module.
 */

import {apiFetch, makeCancelable} from '#/main/app/api/fetch'
import {url} from '#/main/app/api/router'
import {constants} from '#/main/app/api/constants'

// easy access to the new action type
const API_REQUEST = constants.API_REQUEST

export {
  API_REQUEST,

  // fetch sub module
  apiFetch,
  makeCancelable,
  // router sub module
  url
}
