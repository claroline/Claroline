import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {WorkspaceMain as WorkspaceMainComponent} from '#/main/app/layout/sections/workspace/components/main'
import {actions, selectors} from '#/main/app/layout/sections/workspace/store'

const WorkspaceMain = withRouter(
  connect(
    (state) => ({
      defaultOpening: selectors.defaultOpening(state),
      workspace: selectors.workspace(state),
      tools: selectors.tools(state)
    }),
    (dispatch) => ({
      openTool(toolName, workspace) {
        dispatch(actions.openTool(toolName, workspace))
      }
    })
  )(WorkspaceMainComponent)
)

export {
  WorkspaceMain
}
