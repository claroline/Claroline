import {connect} from 'react-redux'
import {withRouter} from '#/main/app/router'

import {Tool} from '#/plugin/open-badge/tools/badges/components/tool'

const ConnectedTool = withRouter(connect(
  state => ({
    currentContext: state.tool.currentContext
  }),
  null
)(Tool))

export {
  ConnectedTool as OpenBadgeTool
}
