import React from 'react'
import {PropTypes as T} from 'prop-types'

import {UsersChart} from '#/plugin/analytics/charts/users/containers/chart'
import {TopUsersChart} from '#/plugin/analytics/charts/top-users/containers/chart'

const CommunityOverview = (props) =>
  <div className="row">
    <div className="col-md-4">
      <UsersChart url={['apiv2_workspace_analytics_users', {workspace: props.workspaceId}]} />
    </div>

    <div className="col-md-8">
      <TopUsersChart url={['apiv2_workspace_analytics_top_users', {workspace: props.workspaceId}]} />
    </div>
  </div>

CommunityOverview.propTypes = {
  workspaceId: T.string.isRequired
}

export {
  CommunityOverview
}
