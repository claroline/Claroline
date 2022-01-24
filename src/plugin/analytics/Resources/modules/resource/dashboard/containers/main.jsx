import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {DashboardMain as DashboardMainComponent} from '#/plugin/analytics/resource/dashboard/components/main'
import {reducer, selectors} from '#/plugin/analytics/resource/dashboard/store'

const DashboardMain = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: resourceSelectors.path(state),
      resourceNode: resourceSelectors.resourceNode(state)
    })
  )(DashboardMainComponent)
)

export {
  DashboardMain
}
