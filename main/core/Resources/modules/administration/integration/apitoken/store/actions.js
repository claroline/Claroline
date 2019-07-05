import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'

export const actions = {}

actions.open = (formName, id = null, defaultValue) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_apitoken_create', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else {
    dispatch(formActions.resetForm(formName, defaultValue, true))
  }
}
