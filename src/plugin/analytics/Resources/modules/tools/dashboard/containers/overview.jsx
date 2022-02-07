import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {DashboardOverview as DashboardOverviewComponent} from '#/plugin/analytics/tools/dashboard/components/overview'
import {selectors} from '#/plugin/analytics/tools/dashboard/store'

const DashboardOverview = connect(
  (state) => ({
    workspace: toolSelectors.contextData(state),
    count: selectors.count(state)
  })
)(DashboardOverviewComponent)

export {
  DashboardOverview
}
