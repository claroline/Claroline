import {connect} from 'react-redux'

import {DashboardOverview as DashboardOverviewComponent} from '#/plugin/analytics/administration/dashboard/components/overview'
import {selectors} from '#/plugin/analytics/administration/dashboard/store'

const DashboardOverview = connect(
  (state) => ({
    count: selectors.count(state)
  })
)(DashboardOverviewComponent)

export {
  DashboardOverview
}
