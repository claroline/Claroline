import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ResourcesChart} from '#/plugin/analytics/charts/resources/containers/chart'
import {TopResourcesChart} from '#/plugin/analytics/charts/top-resources/containers/chart'

const ContentOverview = (props) =>
  <div className="row">
    <div className="col-md-4">
      <ResourcesChart url={['apiv2_workspace_analytics_resources', {workspace: props.workspaceId}]} />
    </div>

    <div className="col-md-8">
      <TopResourcesChart url={['apiv2_workspace_analytics_top_resources', {workspace: props.workspaceId}]} />
    </div>
  </div>

ContentOverview.propTypes = {
  workspaceId: T.string.isRequired
}

export {
  ContentOverview
}
