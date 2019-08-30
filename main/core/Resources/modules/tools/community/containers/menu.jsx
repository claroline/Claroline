import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {CommunityMenu as CommunityMenuComponent} from '#/main/core/tools/community/components/menu'

const CommunityMenu = connect(
  (state) => ({
    contextType: toolSelectors.contextType(state),
    currentUser: securitySelectors.currentUser(state),
    workspace: toolSelectors.contextData(state)
  })
)(CommunityMenuComponent)

export {
  CommunityMenu
}
