import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ActivityChart} from '#/plugin/analytics/charts/activity/containers/chart'
import {LatestActionsChart} from '#/plugin/analytics/charts/latest-actions/containers/chart'

const ActivityOverview = (props) =>
  <div className="row">
    <div className="col-md-8">
      <ActivityChart url={['apiv2_workspace_analytics_activity', {workspace: props.workspaceId}]} />
    </div>

    <div className="col-md-4">
      <LatestActionsChart url={['apiv2_workspace_tool_logs_list', {workspaceId: props.workspaceId}]} />
    </div>
  </div>

ActivityOverview.propTypes = {
  workspaceId: T.string.isRequired
}

export {
  ActivityOverview
}
