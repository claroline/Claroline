import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {ActivityOverview as ActivityOverviewComponent} from '#/plugin/analytics/dashboard/resource/activity/components/overview'

const ActivityOverview = connect(
  state => ({
    resourceId: resourceSelectors.id(state)
  })
)(ActivityOverviewComponent)

export {
  ActivityOverview
}