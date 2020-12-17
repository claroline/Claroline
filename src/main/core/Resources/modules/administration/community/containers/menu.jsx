import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {CommunityMenu as CommunityMenuComponent} from '#/main/core/administration/community/components/menu'

const CommunityMenu = connect(
  (state) => ({
    isAdmin: securitySelectors.isAdmin(state)
  })
)(CommunityMenuComponent)

export {
  CommunityMenu
}
