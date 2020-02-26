import {connect} from 'react-redux'

import {DashboardTool as DashboardToolComponent} from '#/plugin/analytics/administration/dashboard/components/tool'
import {selectors} from '#/plugin/analytics/administration/dashboard/store'

const DashboardTool = connect(
  (state) => ({
    count: selectors.count(state)
  })
)(DashboardToolComponent)

export {
  DashboardTool
}
