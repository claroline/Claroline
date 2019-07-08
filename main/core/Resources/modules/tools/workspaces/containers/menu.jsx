import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {WorkspacesMenu as WorkspacesMenuComponent} from '#/main/core/tools/workspaces/components/menu'
import {selectors} from '#/main/core/tools/workspaces/store'

const WorkspacesMenu = withRouter(
  connect(
    (state) => ({
      creatable: selectors.creatable(state)
    })
  )(WorkspacesMenuComponent)
)

export {
  WorkspacesMenu
}
