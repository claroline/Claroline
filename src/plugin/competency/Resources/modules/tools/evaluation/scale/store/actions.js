import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'

export const actions = {}

actions.open = (formName, defaultProps, id = null) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_competency_scale_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, defaultProps, true))
  }
}

actions.reset = (formName) => (dispatch) => {
  dispatch(formActions.resetForm(formName, {}, true))
}
