import {connect} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'

import {CommunityMenu as CommunityMenuComponent} from '#/main/community/administration/community/components/menu'

const CommunityMenu = connect(
  (state) => ({
    isAdmin: securitySelectors.isAdmin(state)
  })
)(CommunityMenuComponent)

export {
  CommunityMenu
}
