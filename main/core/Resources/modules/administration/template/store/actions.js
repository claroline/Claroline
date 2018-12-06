import {API_REQUEST} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'
import {actions as listActions} from '#/main/app/content/list/store'

const actions = {}

actions.openForm = (formName, defaultData = {}, id = null) => (dispatch) => {
  if (id) {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_template_get', {id}],
        success: (response, dispatch) => {
          dispatch(formActions.resetForm(formName, response, false))
        }
      }
    })
  } else {
    dispatch(actions.resetForm(formName, defaultData))
  }
}

actions.resetForm = (formName, defaultData = {}) => (dispatch) => {
  dispatch(formActions.resetForm(formName, defaultData, true))
}

actions.defineDefaultTemplate = (templateId) => ({
  [API_REQUEST]: {
    url: ['apiv2_template_default_define', {id: templateId}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(listActions.invalidateData('templates'))
    }
  }
})

export {
  actions
}