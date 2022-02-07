import React from 'react'

import {ResourcesChart} from '#/plugin/analytics/charts/resources/containers/chart'
import {TopResourcesChart} from '#/plugin/analytics/charts/top-resources/containers/chart'

const ContentOverview = () =>
  <div className="row">
    <div className="col-md-4">
      <ResourcesChart url={['apiv2_admin_tool_analytics_resources']} />
    </div>

    <div className="col-md-8">
      <TopResourcesChart url={['apiv2_admin_tool_analytics_top_resources']} />
    </div>
  </div>

export {
  ContentOverview
}
