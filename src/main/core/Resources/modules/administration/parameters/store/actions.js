import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'

export const actions = {}

actions.openConnectionMessageForm = (formName, defaultProps, id = null) => (dispatch) => {
  if (id) {
    return dispatch({
      [API_REQUEST]: {
        url: ['apiv2_connectionmessage_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  }

  return dispatch(formActions.resetForm(formName, defaultProps, true))
}

actions.resetForm = (formName) => (dispatch) => dispatch(formActions.resetForm(formName, {}, true))
