import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {CommunityMenu as CommunityMenuComponent} from '#/main/community/tools/community/components/menu'

const CommunityMenu = connect(
  (state) => ({
    path: toolSelectors.path(state),
    contextType: toolSelectors.contextType(state),
    workspace: toolSelectors.contextData(state),
    canEdit: hasPermission('edit', toolSelectors.toolData(state)),
    canShowActivity: hasPermission('show_activity', toolSelectors.toolData(state))
  })
)(CommunityMenuComponent)

export {
  CommunityMenu
}
