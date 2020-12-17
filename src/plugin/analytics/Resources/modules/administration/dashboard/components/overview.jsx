import React from 'react'
import {PropTypes as T} from 'prop-types'

import {schemeCategory20c} from 'd3-scale'

import {trans, displayDuration} from '#/main/app/intl'
import {ToolPage} from '#/main/core/tool/containers/page'

import {ActivityChart} from '#/plugin/analytics/charts/activity/containers/chart'
import {LatestActionsChart} from '#/plugin/analytics/charts/latest-actions/containers/chart'
import {ResourcesChart} from '#/plugin/analytics/charts/resources/containers/chart'
import {TopResourcesChart} from '#/plugin/analytics/charts/top-resources/containers/chart'
import {TopUsersChart} from '#/plugin/analytics/charts/top-users/containers/chart'
import {UsersChart} from '#/plugin/analytics/charts/users/containers/chart'

const DashboardOverview = (props) =>
  <ToolPage
    subtitle={trans('overview', {}, 'analytics')}
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
        <span className="fa fa-user" style={{backgroundColor: schemeCategory20c[9]}} />

        <h1 className="h3">
          <small>{trans('users')}</small>
          {props.count.users}
        </h1>
      </div>

      <div className="analytics-card">
        <span className="fa fa-clock" style={{backgroundColor: schemeCategory20c[13]}} />

        <h1 className="h3">
          <small>{trans('Connexions')}</small>
          {props.count.connections.count} {props.count.connections.avgTime ? '('+trans('connection_avg_time', {time: displayDuration(props.count.connections.avgTime)}, 'analytics')+')' : ''}
        </h1>
      </div>
    </div>

    <div className="row">
      <div className="col-md-8">
        <ActivityChart url={['apiv2_admin_tool_analytics_activity']} />

        <div className="row">
          <div className="col-md-4">
            <ResourcesChart url={['apiv2_admin_tool_analytics_resources']} />
          </div>

          <div className="col-md-8">
            <TopResourcesChart url={['apiv2_admin_tool_analytics_top_resources']} />
          </div>
        </div>

        <div className="row">
          <div className="col-md-4">
            <UsersChart url={['apiv2_admin_tool_analytics_users']} />
          </div>

          <div className="col-md-8">
            <TopUsersChart url={['apiv2_admin_tool_analytics_top_users']} />
          </div>
        </div>
      </div>

      <div className="col-md-4">
        <LatestActionsChart url={['apiv2_admin_tool_logs_list']} />
      </div>
    </div>
  </ToolPage>

DashboardOverview.propTypes = {
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
  DashboardOverview
}
