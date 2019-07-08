import {API_REQUEST} from '#/main/app/api'

// actions
import {constants as toolConst} from '#/main/core/tool/constants'
import {actions as toolActions} from '#/main/core/tool/store/actions'

// action creators
export const actions = {}

// TODO : manage workspaces which change the current ui locale

/**
 * Fetch the required data to open the current Workspace.
 *
 * @param {number} workspaceId
 */
actions.open = (workspaceId) => ({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_workspace_open', {id: workspaceId}],
    success: (response, dispatch) => dispatch(actions.load(response.tools, response.userProgression)),
    error: (response, status, dispatch) => {
      switch (status) {
        case 403: dispatch(actions.setRestrictionsError(response)); break
        case 401: dispatch(actions.setRestrictionsError(response)); break
        default: dispatch(actions.setServerErrors(response))
      }
    }
  }
})

actions.openTool = (toolName, workspace) => ({
  [API_REQUEST]: {
    url: ['claro_workspace_open_tool', {workspaceId: workspace.id, toolName: toolName}],
    success: (response, dispatch) => {
      // TODO : find a way to not duplicate workspace data
      dispatch(toolActions.loadTool(toolName, {type: toolConst.TOOL_WORKSPACE, data: workspace}, response))
      dispatch(toolActions.setToolLoaded())
    }
  }
})
