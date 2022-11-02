import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {CommunityMenu as CommunityMenuComponent} from '#/main/community/tools/community/components/menu'

const CommunityMenu = connect(
  (state) => ({
    contextType: toolSelectors.contextType(state),
    currentUser: securitySelectors.currentUser(state),
    workspace: toolSelectors.contextData(state),
    canAdministrate: hasPermission('administrate', toolSelectors.toolData(state))
  })
)(CommunityMenuComponent)

export {
  CommunityMenu
}
