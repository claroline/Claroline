import {generateUrl} from '#/main/core/fos-js-router'
import {REQUEST_SEND} from '#/main/core/api/actions'
import {trans} from '#/main/core/translation'
import {actions as clacoFormActions} from '../../actions'
import {actions as editorActions} from '../actions'

export const actions = {}

actions.saveTemplate = (template, useTemplate) => (dispatch, getState) => {
  const resourceId = getState().resource.id
  const formData = new FormData()
  formData.append('template', template)
  formData.append('useTemplate', useTemplate ? 1 : 0)

  dispatch({
    [REQUEST_SEND]: {
      url: generateUrl('claro_claco_form_template_edit', {clacoForm: resourceId}),
      request: {
        method: 'POST',
        body: formData
      },
      success: (data, dispatch) => {
        dispatch(editorActions.updateResourceProperty('template', data.template))
        dispatch(editorActions.updateResourceParamsProperty('use_template', data.useTemplate))
        dispatch(clacoFormActions.updateMessage(trans('template_success_message', {}, 'clacoform'), 'success'))
      }
    }
  })
}
