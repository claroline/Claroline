import {API_REQUEST} from '#/main/core/api/actions'

import {actions as clacoFormActions} from '#/plugin/claco-form/resources/claco-form/actions'
import {actions as editorActions} from '#/plugin/claco-form/resources/claco-form/editor/actions'

export const actions = {}

actions.saveTemplate = (template, useTemplate) => (dispatch, getState) => {
  const clacoFormId = getState().clacoForm.id
  const formData = new FormData()
  formData.append('template', template)
  formData.append('useTemplate', useTemplate ? 1 : 0)

  dispatch({
    [API_REQUEST]: {
      url: ['claro_claco_form_template_edit', {clacoForm: clacoFormId}],
      request: {
        method: 'POST',
        body: formData
      },
      success: (data, dispatch) => {
        dispatch(editorActions.updateResourceProperty('template', data.template))
        dispatch(editorActions.updateResourceParamsProperty('use_template', data.useTemplate))
        dispatch(clacoFormActions.resetMessage())
      }
    }
  })
}
