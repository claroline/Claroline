
import {API_REQUEST} from '#/main/app/api'
import {actions as toolActions} from '#/main/core/tool/store'

export const actions = {}

actions.refresh = (toolName, updatedData, contextType) => (dispatch) => {
  dispatch(toolActions.load(toolName, updatedData, contextType))
  dispatch(toolActions.loadType(toolName, updatedData, contextType))
}

actions.fetchRights = (toolName, contextType, contextId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    url: ['apiv2_tool_get_rights', {name: toolName, context: contextType, contextId: contextId}],
    //success: (response) => dispatch(actions.loadRights(response))
  }
})
