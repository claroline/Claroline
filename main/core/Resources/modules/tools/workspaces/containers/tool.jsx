import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {WorkspacesTool as WorkspacesToolComponent} from '#/main/core/tools/workspaces/components/tool'
import {selectors} from '#/main/core/tools/workspaces/store'

const WorkspacesTool = connect(
  (state) => ({
    path: toolSelectors.path(state),
    authenticated: securitySelectors.isAuthenticated(state),
    creatable: selectors.creatable(state)
  })
)(WorkspacesToolComponent)

export {
  WorkspacesTool
}
