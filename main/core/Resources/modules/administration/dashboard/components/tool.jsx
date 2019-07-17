import React from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {matchPath, Routes} from '#/main/app/router'

import {ToolPage} from '#/main/core/tool/containers/page'
import {Overview} from '#/main/core/administration/dashboard/components/overview'
import {Audience} from '#/main/core/administration/dashboard/components/audience'
import {Resources} from '#/main/core/administration/dashboard/components/resources'
import {Widgets} from '#/main/core/administration/dashboard/components/widgets'
import {TopActions} from '#/main/core/administration/dashboard/components/top-actions'
import {Connections} from '#/main/core/administration/dashboard/components/connections'
import {Logs} from '#/main/core/administration/dashboard/components/logs'
import {UserLogs} from '#/main/core/administration/dashboard/components/logs-user'
import {LogDetails} from '#/main/core/administration/dashboard/components/log-details'

const DashboardTool = (props) =>
  <ToolPage
    actions={[
      {
        name: 'download',
        type: DOWNLOAD_BUTTON,
        file: {
          url: url(['apiv2_log_connect_platform_list_csv']) + props.connectionsQuery
        },
        label: trans('download_csv_list', {}, 'log'),
        icon: 'fa fa-download',
        displayed: matchPath(props.location.pathname, {path: `${props.path}/connections`, exact: true})
      }, {
        name: 'download',
        type: DOWNLOAD_BUTTON,
        file: {
          url: url(['apiv2_admin_tool_logs_list_csv']) + props.logsQuery
        },
        label: trans('download_csv_list', {}, 'log'),
        icon: 'fa fa-download',
        displayed: matchPath(props.location.pathname, {path: `${props.path}/log`, exact: true})
      }, {
        name: 'download',
        type: DOWNLOAD_BUTTON,
        file: {
          url: url(['apiv2_admin_tool_logs_list_users_csv']) + props.usersQuery
        },
        label: trans('download_csv_list', {}, 'log'),
        icon: 'fa fa-download',
        displayed: matchPath(props.location.pathname, {path: `${props.path}/logs/users`, exact: true})
      }
    ]}
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/',
          component: Overview,
          exact: true
        }, {
          path: '/audience',
          component: Audience,
          exact: true
        }, {
          path: '/resources',
          component: Resources,
          exact: true
        }, {
          path: '/widgets',
          component: Widgets,
          exact: true
        }, {
          path: '/top',
          component: TopActions,
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
          onEnter: (params) => props.openLog(params.id)
        }, {
          path: '/logs/users',
          component: UserLogs,
          exact: true
        }
      ]}
    />
  </ToolPage>

DashboardTool.propTypes = {
  path: T.string.isRequired,
  location: T.object.isRequired,
  connectionsQuery: T.string,
  logsQuery: T.string,
  usersQuery: T.string,
  openLog: T.func.isRequired
}

export {
  DashboardTool
}
