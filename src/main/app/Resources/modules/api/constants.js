import {constants as alertConstants} from '#/main/app/overlays/alert/constants'
import {constants as actionConstants} from '#/main/app/action/constants'

const API_REQUEST = 'API_REQUEST'

// declares the available action for api
const API_ACTIONS = Object.assign({}, actionConstants.ACTIONS)

// map actions on HTTP methods
// (this is used to display the correct alert message on api calls)
const HTTP_ACTIONS = {
  OPTIONS: actionConstants.ACTION_LOAD,
  HEAD:    actionConstants.ACTION_LOAD,
  GET:     actionConstants.ACTION_LOAD,
  POST:    actionConstants.ACTION_CREATE,
  PUT:     actionConstants.ACTION_UPDATE,
  PATCH:   actionConstants.ACTION_UPDATE,
  DELETE:  actionConstants.ACTION_DELETE
}

// remap action on HTTP status code
const HTTP_ALERT_STATUS = {
  // success
  200: alertConstants.ALERT_STATUS_SUCCESS,
  201: alertConstants.ALERT_STATUS_SUCCESS,
  202: alertConstants.ALERT_STATUS_INFO,
  204: alertConstants.ALERT_STATUS_SUCCESS,
  // warning
  401: alertConstants.ALERT_STATUS_UNAUTHORIZED,
  403: alertConstants.ALERT_STATUS_FORBIDDEN,
  422: alertConstants.ALERT_STATUS_WARNING,
  // error
  500: alertConstants.ALERT_STATUS_ERROR,
  503: alertConstants.ALERT_STATUS_UNAVAILABLE
}

export const constants = {
  API_REQUEST,
  API_ACTIONS,
  HTTP_ACTIONS,
  HTTP_ALERT_STATUS
}
