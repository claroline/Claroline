import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ActivityChart} from '#/plugin/analytics/charts/activity/containers/chart'
import {LatestActionsChart} from '#/plugin/analytics/charts/latest-actions/containers/chart'

const ActivityOverview = (props) =>
  <div className="row">
    <div className="col-md-8">
      <ActivityChart url={['apiv2_resource_analytics_activity', {resource: props.resourceId}]} />
    </div>

    <div className="col-md-4">
      <LatestActionsChart url={['apiv2_resource_logs_list', {resourceId: props.resourceId}]} />
    </div>
  </div>

ActivityOverview.propTypes = {
  resourceId: T.string.isRequired
}

export {
  ActivityOverview
}