import React from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {DOWNLOAD_BUTTON} from '#/main/app/buttons'
import {matchPath, Routes} from '#/main/app/router'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

import {ToolPage} from '#/main/core/tool/containers/page'
import {Overview} from '#/main/core/administration/dashboard/components/overview'
import {Audience} from '#/main/core/administration/dashboard/components/audience'
import {Resources} from '#/main/core/administration/dashboard/components/resources'
import {Widgets} from '#/main/core/administration/dashboard/components/widgets'
import {TopActions} from '#/main/core/administration/dashboard/components/top-actions'
import {Connections} from '#/main/core/administration/dashboard/components/connections'
import {Logs} from '#/main/core/administration/dashboard/components/logs'
import {UserLogs} from '#/main/core/administration/dashboard/components/logs-user'
import {LogDetails} from '#/main/core/layout/logs'

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
        displayed: matchPath(props.location.pathname, {path: '/connections', exact: true})
      }, {
        name: 'download',
        type: DOWNLOAD_BUTTON,
        file: {
          url: url(['apiv2_admin_tool_logs_list_csv']) + props.logsQuery
        },
        label: trans('download_csv_list', {}, 'log'),
        icon: 'fa fa-download',
        displayed: matchPath(props.location.pathname, {path: '/log', exact: true})
      }, {
        name: 'download',
        type: DOWNLOAD_BUTTON,
        file: {
          url: url(['apiv2_admin_tool_logs_list_users_csv']) + props.usersQuery
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
              title: trans('analytics_home'),
              path: '/',
              exact: true
            }, {
              icon: 'fa fa-line-chart',
              title: trans('user_visit'),
              path: '/audience'
            }, {
              icon: 'fa fa-folder',
              title: trans('analytics_resources'),
              path: '/resources'
            }, {
              icon: 'fa fa-list-alt',
              title: trans('widgets'),
              path: '/widgets'
            }, {
              icon: 'fa fa-sort-amount-desc',
              title: trans('analytics_top'),
              path: '/top'
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
            }
          ]}
        />
      </div>

      <div className="dashboard-content col-md-9">
        <Routes
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
      </div>
    </div>
  </ToolPage>

DashboardTool.propTypes = {
  location: T.object.isRequired,
  connectionsQuery: T.string,
  logsQuery: T.string,
  usersQuery: T.string,
  openLog: T.func.isRequired
}

export {
  DashboardTool
}
