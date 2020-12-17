import React from 'react'
import {PropTypes as T} from 'prop-types'

import {schemeCategory20c} from 'd3-scale'

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
          url: ['apiv2_log_connect_platform_list_csv']
        },
        group: trans('transfer')
      }, {
        name: 'download',
        type: DOWNLOAD_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export-actions', {}, 'actions'),
        file: {
          url: ['apiv2_admin_tool_logs_list_csv']
        },
        group: trans('transfer')
      }, {
        name: 'download-users',
        type: DOWNLOAD_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export-user-actions', {}, 'actions'),
        file: {
          url: ['apiv2_admin_tool_logs_list_users_csv']
        },
        group: trans('transfer')
      }
    ]}
  >
    <div className="row">
      <div className="analytics-card">
        <span className="fa fa-power-off" style={{backgroundColor: schemeCategory20c[1]}} />

        <h1 className="h3">
          <small>{trans('Connexions')}</small>
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
          url={['apiv2_admin_tool_analytics_actions']}
          listUrl={['apiv2_admin_tool_logs_list']}
        />
      </div>

      <div className="col-md-6">
        <ConnectionTimeChart
          url={['apiv2_admin_tool_analytics_time']}
          listUrl={['apiv2_log_connect_platform_list']}
        />
      </div>
    </div>
  </ToolPage>

DashboardActivity.propTypes = {
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
