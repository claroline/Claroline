import {API_REQUEST} from '#/main/app/api'

import {constants as toolConst} from '#/main/core/tool/constants'
import {actions as toolActions} from '#/main/core/tool/store/actions'

export const actions = {}

actions.open = (workspaceId) => ({
  [API_REQUEST]: {
    silent: true,
    url: ['claro_workspace_open', {
      workspaceId: workspaceId
    }],
    success: (response, dispatch) => {
      //dispatch(actions.load(response))
    }
  }
})

actions.openTool = (toolName, workspace) => ({
  [API_REQUEST]: {
    url: ['claro_workspace_open_tool', {
      workspaceId: workspace.id,
      toolName: toolName
    }],
    success: (response, dispatch) => {
      // TODO : find a way to not duplicate workspace data
      dispatch(toolActions.loadTool(toolName, {type: toolConst.TOOL_WORKSPACE, data: workspace}, response))
      dispatch(toolActions.setToolLoaded())
    }
  }
})
