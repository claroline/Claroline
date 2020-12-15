import React from 'react'
import {PropTypes as T} from 'prop-types'

import {schemeCategory20c} from 'd3-scale'

import {trans, fileSize} from '#/main/app/intl'
import {ToolPage} from '#/main/core/tool/containers/page'

import {ResourcesChart} from '#/plugin/analytics/charts/resources/containers/chart'
import {TopResourcesChart} from '#/plugin/analytics/charts/top-resources/containers/chart'

const DashboardContent = (props) =>
  <ToolPage
    subtitle={trans('content')}
  >
    <div className="row">
      <div className="analytics-card">
        <span className="fa fa-book" style={{backgroundColor: schemeCategory20c[1]}} />

        <h1 className="h3">
          <small>{trans('workspaces')}</small>
          {props.count.workspaces}
        </h1>
      </div>

      <div className="analytics-card">
        <span className="fa fa-folder" style={{backgroundColor: schemeCategory20c[5]}} />

        <h1 className="h3">
          <small>{trans('resources')}</small>
          {props.count.resources}
        </h1>
      </div>

      <div className="analytics-card">
        <span className="fa fa-server" style={{backgroundColor: schemeCategory20c[9]}} />

        <h1 className="h3">
          <small>{trans('storage_used')}</small>
          {fileSize(props.count.storage)+trans('bytes_short')}
        </h1>
      </div>
    </div>

    <div className="row">
      <div className="col-md-4">
        <ResourcesChart url={['apiv2_admin_tool_analytics_resources']} />
      </div>

      <div className="col-md-8">
        <TopResourcesChart url={['apiv2_admin_tool_analytics_top_resources']} />
      </div>
    </div>
  </ToolPage>

DashboardContent.propTypes = {
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
  DashboardContent
}
