import React from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {matchPath, Routes} from '#/main/app/router'

import {ToolPage} from '#/main/core/tool/containers/page'
import {Overview} from '#/plugin/analytics/tools/dashboard/components/overview'
import {Connections} from '#/plugin/analytics/tools/dashboard/components/connections'
import {Logs} from '#/plugin/analytics/tools/dashboard/components/logs'
import {UserLogs} from '#/plugin/analytics/tools/dashboard/components/logs-user'
import {LogDetails} from '#/main/core/layout/logs'
import {Progression} from '#/plugin/analytics/tools/dashboard/components/progression'
import {Paths} from '#/plugin/analytics/tools/dashboard/path/containers/paths'
import {Evaluations} from '#/plugin/analytics/tools/dashboard/components/evaluations'
import {Requirements} from '#/plugin/analytics/tools/dashboard/containers/requirements'
import {RequirementsDetails} from '#/plugin/analytics/tools/dashboard/containers/requirements-details'

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
        displayed: !!matchPath(props.location.pathname, {path: `${props.path}/connections`, exact: true})
      }, {
        name: 'download',
        type: DOWNLOAD_BUTTON,
        file: {
          url: url(['apiv2_workspace_tool_logs_list_csv', {'workspaceId': props.workspaceId}]) + props.logsQuery
        },
        label: trans('download_csv_list', {}, 'log'),
        icon: 'fa fa-download',
        displayed: !!matchPath(props.location.pathname, {path: `${props.path}/log`, exact: true})
      }, {
        name: 'download',
        type: DOWNLOAD_BUTTON,
        file: {
          url: url(['apiv2_workspace_tool_logs_list_users_csv', {'workspaceId': props.workspaceId}]) + props.usersQuery
        },
        label: trans('download_csv_list', {}, 'log'),
        icon: 'fa fa-download',
        displayed: !!matchPath(props.location.pathname, {path: `${props.path}/logs/users`, exact: true})
      }
    ]}
    subtitle={
      <Routes
        path={props.path}
        routes={[
          {
            path: '/',
            render: () => trans('overview', {}, 'analytics'),
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
          }, {
            path: '/evaluations',
            render: () => trans('evaluations', {}, 'analytics')
          }, {
            path: '/requirements',
            render: () => trans('evaluation_requirements', {}, 'analytics')
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
          component: Overview,
          exact: true
        }, {
          path: '/connections',
          component: Connections
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
          component: UserLogs
        }, {
          path: '/progression',
          component: Progression
        }, {
          path: '/paths',
          component: Paths
        }, {
          path: '/evaluations',
          component: Evaluations
        }, {
          path: '/requirements',
          component: Requirements,
          exact: true
        }, {
          path: '/requirements/:id',
          component: RequirementsDetails,
          onEnter: (params) => props.openRequirements(params.id),
          onLeave: () => props.resetRequirements()
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
  openLog: T.func.isRequired,
  openRequirements: T.func.isRequired,
  resetRequirements: T.func.isRequired
}

export {
  DashboardTool
}
