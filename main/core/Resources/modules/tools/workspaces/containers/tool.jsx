import {connect} from 'react-redux'

import {WorkspacesTool as WorkspacesToolComponent} from '#/main/core/tools/workspaces/components/tool'
import {selectors, actions} from '#/main/core/tools/workspaces/store'

const WorkspacesTool = connect(
  (state) => ({
    creatable: selectors.creatable(state)
  }),
  (dispatch) => ({
    open(workspaceId) {
      dispatch(actions.open(workspaceId))
    }
  })
)(WorkspacesToolComponent)

export {
  WorkspacesTool
}
