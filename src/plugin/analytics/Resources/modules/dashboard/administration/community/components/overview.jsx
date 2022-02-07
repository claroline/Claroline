import React from 'react'

import {UsersChart} from '#/plugin/analytics/charts/users/containers/chart'
import {TopUsersChart} from '#/plugin/analytics/charts/top-users/containers/chart'

const CommunityOverview = () =>
  <div className="row">
    <div className="col-md-4">
      <UsersChart url={['apiv2_admin_tool_analytics_users']} />
    </div>

    <div className="col-md-8">
      <TopUsersChart url={['apiv2_admin_tool_analytics_top_users']} />
    </div>
  </div>


export {
  CommunityOverview
}
