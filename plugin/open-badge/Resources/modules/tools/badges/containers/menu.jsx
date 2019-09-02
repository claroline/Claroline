import {connect} from 'react-redux'
import {withRouter} from '#/main/app/router'

import {BadgeMenu} from '#/plugin/open-badge/tools/badges/components/menu'

const ConnectedBadgeMenu = withRouter(connect(
  state => ({
    currentContext: state.tool.currentContext
  }),
  null
)(BadgeMenu))

export {
  ConnectedBadgeMenu as BadgeMenu
}
