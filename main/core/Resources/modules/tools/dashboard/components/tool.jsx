import React from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {matchPath, Routes} from '#/main/app/router'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

import {ToolPage} from '#/main/core/tool/containers/page'
import {Analytics} from '#/main/core/tools/dashboard/components/analytics'
import {Connections} from '#/main/core/tools/dashboard/components/connections'
import {Logs} from '#/main/core/tools/dashboard/components/logs'
import {UserLogs} from '#/main/core/tools/dashboard/components/logs-user'
import {LogDetails} from '#/main/core/layout/logs'
import {Progression} from '#/main/core/tools/dashboard/components/progression'

const DashboardTool = (props) =>
  <ToolPage
    actions={[
      {
        name: 'download',
        type: DOWNLOAD_BUTTON,
        file: {
          url: url(['apiv2_log_connect_workspace_list_csv', {'workspace': props.workspaceId}]) + props.connectionsQuery
        },
        label: trans('download_csv_list', {}, 'log'),
        icon: 'fa fa-download',
        displayed: matchPath(props.location.pathname, {path: '/connections', exact: true})
      }, {
        name: 'download',
        type: DOWNLOAD_BUTTON,
        file: {
          url: url(['apiv2_workspace_tool_logs_list_csv', {'workspaceId': props.workspaceId}]) + props.logsQuery
        },
        label: trans('download_csv_list', {}, 'log'),
        icon: 'fa fa-download',
        displayed: matchPath(props.location.pathname, {path: '/log', exact: true})
      }, {
        name: 'download',
        type: DOWNLOAD_BUTTON,
        file: {
          url: url(['apiv2_workspace_tool_logs_list_users_csv', {'workspaceId': props.workspaceId}]) + props.usersQuery
        },
        label: trans('download_csv_list', {}, 'log'),
        icon: 'fa fa-download',
        displayed: matchPath(props.location.pathname, {path: '/logs/users', exact: true})
      }
    ]}
  >
    <div className="row">
      <div className="col-md-3">
        <Vertical
          style={{
            marginTop: '20px'
          }}
          tabs={[
            {
              icon: 'fa fa-fw fa-pie-chart',
              title: trans('analytics'),
              path: '/',
              exact: true
            }, {
              icon: 'fa fa-fw fa-clock',
              title: trans('connection_time'),
              path: '/connections'
            }, {
              icon: 'fa fa-fw fa-users',
              title: trans('users_tracking'),
              path: '/log'
            }, {
              icon: 'fa fa-fw fa-user',
              title: trans('user_tracking', {}, 'log'),
              path: '/logs/users',
              exact: true
            }, {
              icon: 'fa fa-fw fa-tasks',
              title: trans('progression'),
              path: '/progression',
              exact: true
            }
          ]}
        />
      </div>

      <div className="dashboard-content col-md-9">
        <Routes
          routes={[
            {
              path: '/',
              component: Analytics,
              exact: true
            }, {
              path: '/connections',
              component: Connections,
              exact: true
            }, {
              path: '/log',
              component: Logs,
              exact: true
            }, {
              path: '/log/:id',
              component: LogDetails,
              onEnter: (params) => props.openLog(params.id, props.workspaceId)
            }, {
              path: '/logs/users',
              component: UserLogs,
              exact: true
            }, {
              path: '/progression',
              component: Progression
            }
          ]}
        />
      </div>
    </div>
  </ToolPage>

DashboardTool.propTypes = {
  location: T.object.isRequired,
  workspaceId: T.number.isRequired,
  connectionsQuery: T.string,
  logsQuery: T.string,
  usersQuery: T.string,
  openLog: T.func.isRequired
}

export {
  DashboardTool
}
