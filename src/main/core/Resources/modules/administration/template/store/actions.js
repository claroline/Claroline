import {makeActionCreator} from '#/main/app/store/actions'

import {API_REQUEST, url} from '#/main/app/api'
import {actions as formActions} from '#/main/app/content/form/store'

export const TEMPLATE_TYPE_LOAD = 'TEMPLATE_TYPE_LOAD'

const actions = {}

actions.loadTemplateType = makeActionCreator(TEMPLATE_TYPE_LOAD, 'templateType')

actions.open = (id = null) => (dispatch) => {
  if (id) {
    return dispatch({
      [API_REQUEST]: {
        url: ['apiv2_template_type_open', {id}],
        silent: true,
        success: (response) => dispatch(actions.loadTemplateType(response))
      }
    })
  }

  return dispatch(actions.loadTemplateType(null))
}

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

actions.deleteTemplate = (templateTypeId, templateId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: url(['apiv2_template_delete_bulk'], {ids: [templateId]}),
    request: {method: 'DELETE'},
    success: () => dispatch(actions.open(templateTypeId))
  }
})

export {
  actions
}