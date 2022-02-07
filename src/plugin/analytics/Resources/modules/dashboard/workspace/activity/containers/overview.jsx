import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {ActivityOverview as ActivityOverviewComponent} from '#/plugin/analytics/dashboard/workspace/activity/components/overview'

const ActivityOverview = connect(
  (state) => ({
    workspaceId: toolSelectors.contextId(state)
  })
)(ActivityOverviewComponent)

export {
  ActivityOverview
}
