import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {WorkspacesMenu as WorkspacesMenuComponent} from '#/main/core/tools/workspaces/components/menu'
import {selectors} from '#/main/core/tools/workspaces/store'

const WorkspacesMenu = withRouter(
  connect(
    (state) => ({
      authenticated: securitySelectors.isAuthenticated(state),
      creatable: selectors.creatable(state)
    })
  )(WorkspacesMenuComponent)
)

export {
  WorkspacesMenu
}
