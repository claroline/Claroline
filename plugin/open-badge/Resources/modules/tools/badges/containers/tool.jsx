import {connect} from 'react-redux'
import {withRouter} from '#/main/app/router'

import {OpenBadgeAdminTool as OpenBadgeAdminToolComponent} from '#/plugin/open-badge/tools/badges/components/tool'

const ConnectedOpenBadgeAdminTool = withRouter(connect(
  state => ({
    currentContext: state.currentContext
  }),
  null
)(OpenBadgeAdminToolComponent))

export {
  ConnectedOpenBadgeAdminTool as OpenBadgeAdminTool
}
