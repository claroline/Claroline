import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {selectors} from '#/main/app/security/password/send/store/selectors'
import {actions as alertActions} from '#/main/app/overlays/alert/store'
import {constants as alertConstants} from '#/main/app/overlays/alert/constants'
import {trans} from '#/main/app/intl/translation'

// action creators
export const actions = {}

/**
 * Fetch the required data to open the current user desktop.
 */
actions.reset = (email, callback = () => {}) => ({
  [API_REQUEST]: {
    url: ['claro_security_send_token'],
    request: {
      method: 'POST',
      body: JSON.stringify({
        email: email
      })
    },
    success: (response, dispatch) => {
      dispatch(alertActions.addAlert(
        // id
        'ALERT_SEND_PASSWORD_SUCCESS',
        // status
        alertConstants.ALERT_STATUS_SUCCESS,
        // action
        alertActions.ALERT_ADD,
        // title
        null,
        // message
        trans(response)
      ))
      callback()
    },
    error: (response, status, dispatch) => {
      if (response.error) {
        dispatch(formActions.setErrors(selectors.FORM_NAME, trans(response.error)))
      } else {
        dispatch(alertActions.addAlert(
          // id
          'ALERT_SEND_PASSWORD_RESET_ERROR',
          // status
          alertConstants.ALERT_STATUS_ERROR,
          // action
          alertActions.ALERT_ADD,
          // title
          null,
          // message
          trans(response)
        ))
      }
    }
  }
})
