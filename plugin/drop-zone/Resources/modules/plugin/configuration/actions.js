import {makeActionCreator} from '#/main/core/scaffolding/actions'
import {generateUrl} from '#/main/core/api/router'
import {API_REQUEST} from '#/main/core/api/actions'
import {actions as modalActions} from '#/main/core/layout/modal/actions'

import {isValid} from '#/plugin/drop-zone/plugin/configuration/validator'

export const TOOL_FORM_LOAD = 'TOOL_FORM_LOAD'
export const TOOL_FORM_RESET = 'TOOL_FORM_RESET'
export const TOOL_FORM_UPDATE = 'TOOL_FORM_UPDATE'
export const TOOL_FORM_VALIDATE = 'TOOL_FORM_VALIDATE'
export const TOOL_UPDATE = 'TOOL_UPDATE'

export const actions = {}

actions.loadToolForm = makeActionCreator(TOOL_FORM_LOAD, 'tool')
actions.resetToolForm = makeActionCreator(TOOL_FORM_RESET)
actions.updateToolForm = makeActionCreator(TOOL_FORM_UPDATE, 'property', 'value')
actions.validateToolForm = makeActionCreator(TOOL_FORM_VALIDATE)
actions.updateTool = makeActionCreator(TOOL_UPDATE, 'tool')

actions.submitTool = (tool) => {
  return (dispatch) => {
    dispatch(actions.validateToolForm())

    if (isValid(tool)) {
      dispatch(actions.saveTool(tool))
    }
  }
}

actions.saveTool = (tool) => ({
  [API_REQUEST]: {
    url: generateUrl('apiv2_dropzonetool_update', {id: tool.id}),
    request: {
      method: 'PUT',
      body: JSON.stringify(tool)
    },
    success: (data, dispatch) => {
      dispatch(actions.updateTool(data))
      dispatch(modalActions.fadeModal())
      dispatch(actions.resetToolForm())
    }
  }
})