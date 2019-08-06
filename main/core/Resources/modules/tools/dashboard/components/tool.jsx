import React from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {matchPath, Routes} from '#/main/app/router'

import {ToolPage} from '#/main/core/tool/containers/page'
import {Analytics} from '#/main/core/tools/dashboard/components/analytics'
import {Connections} from '#/main/core/tools/dashboard/components/connections'
import {Logs} from '#/main/core/tools/dashboard/components/logs'
import {UserLogs} from '#/main/core/tools/dashboard/components/logs-user'
import {LogDetails} from '#/main/core/layout/logs'
import {Progression} from '#/main/core/tools/dashboard/components/progression'
import {Paths} from '#/main/core/tools/dashboard/path/containers/paths'

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
        displayed: matchPath(props.location.pathname, {path: `${props.path}/connections`, exact: true})
      }, {
        name: 'download',
        type: DOWNLOAD_BUTTON,
        file: {
          url: url(['apiv2_workspace_tool_logs_list_csv', {'workspaceId': props.workspaceId}]) + props.logsQuery
        },
        label: trans('download_csv_list', {}, 'log'),
        icon: 'fa fa-download',
        displayed: matchPath(props.location.pathname, {path: `${props.path}/log`, exact: true})
      }, {
        name: 'download',
        type: DOWNLOAD_BUTTON,
        file: {
          url: url(['apiv2_workspace_tool_logs_list_users_csv', {'workspaceId': props.workspaceId}]) + props.usersQuery
        },
        label: trans('download_csv_list', {}, 'log'),
        icon: 'fa fa-download',
        displayed: matchPath(props.location.pathname, {path: `${props.path}/logs/users`, exact: true})
      }
    ]}
    subtitle={
      <Routes
        path={props.path}
        routes={[
          {
            path: '/',
            render: () => trans('analytics'),
            exact: true
          }, {
            path: '/connections',
            render: () => trans('connection_time')
          }, {
            path: '/log',
            render: () => trans('users_actions')
          }, {
            path: '/logs/users',
            render: () => trans('user_actions')
          }, {
            path: '/progression',
            render: () => trans('progression')
          }, {
            path: '/paths',
            render: () => trans('paths_tracking')
          }
        ]}
      />
    }
  >
    <Routes
      path={props.path}
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
        }, {
          path: '/paths',
          component: Paths
        }
      ]}
    />
  </ToolPage>

DashboardTool.propTypes = {
  path: T.string.isRequired,
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
