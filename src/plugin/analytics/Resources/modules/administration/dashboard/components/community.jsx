import React from 'react'
import {PropTypes as T} from 'prop-types'

import {schemeCategory20c} from 'd3-scale'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'

import {TopUsersChart} from '#/plugin/analytics/charts/top-users/containers/chart'
import {UsersChart} from '#/plugin/analytics/charts/users/containers/chart'

const DashboardCommunity = (props) =>
  <ToolPage
    subtitle={trans('community')}
  >
    <div className="row">
      <div className="analytics-card">
        <span className="fa fa-user" style={{backgroundColor: schemeCategory20c[1]}} />

        <h1 className="h3">
          <small>{trans('users')}</small>
          {props.count.users}
        </h1>
      </div>

      <div className="analytics-card">
        <span className="fa fa-users" style={{backgroundColor: schemeCategory20c[5]}} />

        <h1 className="h3">
          <small>{trans('groups')}</small>
          {props.count.groups}
        </h1>
      </div>

      <div className="analytics-card">
        <span className="fa fa-id-badge" style={{backgroundColor: schemeCategory20c[9]}} />

        <h1 className="h3">
          <small>{trans('roles')}</small>
          {props.count.roles}
        </h1>
      </div>

      <div className="analytics-card">
        <span className="fa fa-building" style={{backgroundColor: schemeCategory20c[13]}} />

        <h1 className="h3">
          <small>{trans('organizations')}</small>
          {props.count.organizations}
        </h1>
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
  </ToolPage>

DashboardCommunity.propTypes = {
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
  DashboardCommunity
}
