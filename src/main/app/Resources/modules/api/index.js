/**
 * API module.
 */

import {makeCancelable} from '#/main/app/api/fetch/makeCancelable'
import {url} from '#/main/app/api/router'
import {constants} from '#/main/app/api/constants'

// easy access to the new action type
const API_REQUEST = constants.API_REQUEST

export {
  API_REQUEST,

  makeCancelable,
  url
}
