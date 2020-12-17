import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {BadgeMenu} from '#/plugin/open-badge/tools/badges/components/menu'

const ConnectedBadgeMenu = withRouter(connect(
  state => ({
    isAdmin: securitySelectors.isAdmin(state)
  })
)(BadgeMenu))

export {
  ConnectedBadgeMenu as BadgeMenu
}
