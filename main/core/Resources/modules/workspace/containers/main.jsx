import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {constants as toolConst} from '#/main/core/tool/constants'
import {actions as toolActions} from '#/main/core/tool/store'

import {route} from '#/main/core/workspace/routing'
import {WorkspaceMain as WorkspaceMainComponent} from '#/main/core/workspace/components/main'
import {actions, reducer, selectors} from '#/main/core/workspace/store'

const WorkspaceMain = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        loaded: selectors.loaded(state),
        managed: selectors.managed(state),
        workspace: selectors.workspace(state),
        accessErrors: selectors.accessErrors(state),
        defaultOpening: selectors.defaultOpening(state),
        tools: selectors.tools(state)
      }),
      (dispatch) => ({
        openTool(toolName, workspace) {
          dispatch(toolActions.open(toolName, {
            type: toolConst.TOOL_WORKSPACE,
            url: ['claro_workspace_open_tool', {id: workspace.id, toolName: toolName}],
            data: workspace // TODO : find a way to not duplicate workspace data
          }, route(workspace)))
        },
        dismissRestrictions() {
          dispatch(actions.dismissRestrictions())
        }
      })
    )(WorkspaceMainComponent)
  )
)

export {
  WorkspaceMain
}
