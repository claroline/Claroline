import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {hasPermission} from '#/main/app/security'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {WorkspacesMenu as WorkspacesMenuComponent} from '#/main/core/tools/workspaces/components/menu'
import {selectors} from '#/main/core/tools/workspaces/store'

const WorkspacesMenu = withRouter(
  connect(
    (state) => ({
      authenticated: securitySelectors.isAuthenticated(state),
      canCreate: selectors.creatable(state),
      canArchive: hasPermission('archive', toolSelectors.toolData(state))
    })
  )(WorkspacesMenuComponent)
)

export {
  WorkspacesMenu
}
