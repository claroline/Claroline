import React from 'react'

import {ActivityChart} from '#/plugin/analytics/charts/activity/containers/chart'
import {LatestActionsChart} from '#/plugin/analytics/charts/latest-actions/containers/chart'

const ActivityOverview = () =>
  <div className="row">
    <div className="col-md-8">
      <ActivityChart url={['apiv2_admin_tool_analytics_activity']} />
    </div>

    <div className="col-md-4">
      <LatestActionsChart url={['apiv2_admin_tool_logs_list']} />
    </div>
  </div>

export {
  ActivityOverview
}
