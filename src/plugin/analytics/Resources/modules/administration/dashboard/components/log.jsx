import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ToolPage} from '#/main/core/tool/containers/page'

import {LatestActionsChart} from '#/plugin/analytics/charts/latest-actions/containers/chart'

const DashboardLog = (props) =>
  <ToolPage subtitle='Log'>
    <div className="row">
      <div className="col-md-4">
        <LatestActionsChart url={['apiv2_log_security']} />
      </div>
    </div>
  </ToolPage>

DashboardLog.propTypes = {
  count: T.shape({
    workspaces: T.number,
    resources: T.number,
    storage: T.number,
    connections: T.shape({
      count: T.number,
      avgTime: T.number
    }),
    users: T.number,
    roles: T.number,
    groups: T.number,
    organizations: T.number
  }).isRequired
}

export {
    DashboardLog
}
