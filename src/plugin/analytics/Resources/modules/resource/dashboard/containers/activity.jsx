import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {DashboardActivity as DashboardActivityComponent} from '#/plugin/analytics/resource/dashboard/components/activity'

const DashboardActivity = connect(
  state => ({
    resourceId: resourceSelectors.id(state)
  })
)(DashboardActivityComponent)

export {
  DashboardActivity
}