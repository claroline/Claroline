import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {DashboardTool as DashboardToolComponent} from '#/plugin/analytics/tools/dashboard/components/tool'
import {selectors} from '#/plugin/analytics/tools/dashboard/store'

const DashboardTool = connect(
  (state) => ({
    workspaceId: toolSelectors.contextId(state),
    count: selectors.count(state)
  })
)(DashboardToolComponent)

export {
  DashboardTool
}
