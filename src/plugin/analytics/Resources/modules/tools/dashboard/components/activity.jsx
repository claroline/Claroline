import React from 'react'
import {PropTypes as T} from 'prop-types'

import {schemeCategory20c} from 'd3-scale'

import {url} from '#/main/app/api'
import {trans, displayDuration} from '#/main/app/intl'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {ActionsChart} from '#/plugin/analytics/charts/actions/containers/chart'
import {ConnectionTimeChart} from '#/plugin/analytics/charts/connection-time/containers/chart'

const DashboardActivity = (props) =>
  <ToolPage
    subtitle={trans('activity')}
    toolbar="more"
    actions={[
      {
        name: 'download-connection-times',
        type: DOWNLOAD_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export-connections', {}, 'actions'),
        file: {
          url: url(['apiv2_log_connect_workspace_list_csv', {workspace: props.workspaceId}])
        },
        group: trans('transfer')
      }, {
        name: 'download',
        type: DOWNLOAD_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export-actions', {}, 'actions'),
        file: {
          url: url(['apiv2_workspace_tool_logs_list_csv', {workspaceId: props.workspaceId}])
        },
        group: trans('transfer')
      }, {
        name: 'download-users',
        type: DOWNLOAD_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export-user-actions', {}, 'actions'),
        file: {
          url: url(['apiv2_workspace_tool_logs_list_users_csv', {workspaceId: props.workspaceId}])
        },
        group: trans('transfer')
      }
    ]}
  >
    <div className="row">
      <div className="analytics-card">
        <span className="fa fa-power-off" style={{backgroundColor: schemeCategory20c[1]}} />

        <h1 className="h3">
          <small>{trans('connections')}</small>
          {props.count.connections.count}
        </h1>
      </div>

      <div className="analytics-card">
        <span className="fa fa-clock" style={{backgroundColor: schemeCategory20c[5]}} />

        <h1 className="h3">
          <small>{trans('connection_time')}</small>
          {props.count.connections.avgTime ?
            trans('connection_avg_time', {time: displayDuration(props.count.connections.avgTime)}, 'analytics') :
            '-'
          }
        </h1>
      </div>
    </div>

    <div className="row">
      <div className="col-md-6">
        <ActionsChart
          url={['apiv2_workspace_analytics_actions', {workspace: props.workspaceId}]}
          listUrl={['apiv2_workspace_tool_logs_list', {workspaceId: props.workspaceId}]}
        />
      </div>

      <div className="col-md-6">
        <ConnectionTimeChart
          url={['apiv2_workspace_analytics_time', {workspace: props.workspaceId}]}
          listUrl={['apiv2_log_connect_workspace_list', {workspace: props.workspaceId}]}
        />
      </div>
    </div>
  </ToolPage>

DashboardActivity.propTypes = {
  workspaceId: T.string.isRequired,
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
  DashboardActivity
}
