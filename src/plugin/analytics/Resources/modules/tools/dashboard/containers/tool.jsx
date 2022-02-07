import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {DashboardTool as DashboardToolComponent} from '#/plugin/analytics/tools/dashboard/components/tool'

const DashboardTool = connect(
  (state) => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state)
  })
)(DashboardToolComponent)

export {
  DashboardTool
}
