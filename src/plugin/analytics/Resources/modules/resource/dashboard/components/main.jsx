import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Await} from '#/main/app/components/await'
import {Routes} from '#/main/app/router'
import {Vertical} from '#/main/app/content/tabs/components/vertical'
import {ContentLoader} from '#/main/app/content/components/loader'
import {LogDetails} from '#/main/core/layout/logs'

import {getAnalytics} from '#/plugin/analytics/resource/utils'
import {Logs} from '#/plugin/analytics/resource/dashboard/containers/logs'
import {UserLogs} from '#/plugin/analytics/resource/dashboard/containers/logs-user'

const DashboardMain = (props) =>
  <Await
    for={getAnalytics(props.resourceNode)}
    placeholder={
      <ContentLoader
        size="lg"
        description={trans('loading')}
      />
    }
    then={(apps) => (
      <div className="row">
        <div className="col-md-3">
          <Vertical
            style={{
              marginTop: '20px'
            }}
            basePath={props.path}
            tabs={
              apps.map(app => ({
                icon: app.icon,
                title: app.label,
                path: `/dashboard/${app.path}`,
                exact: true
              })).concat([
                {
                  icon: 'fa fa-fw fa-users',
                  title: trans('users_actions'),
                  path: '/dashboard/log'
                }, {
                  icon: 'fa fa-fw fa-user',
                  title: trans('user_actions'),
                  path: '/dashboard/logs/users',
                  exact: true
                }
              ])
            }
          />
        </div>

        <div className="dashboard-content col-md-9">
          <Routes
            path={props.path}
            routes={
              apps.map(app => ({
                path: `/dashboard/${app.path}`,
                component: app.component,
                exact: true
              })).concat([
                {
                  path: '/dashboard/log',
                  component: Logs,
                  exact: true
                }, {
                  path: '/dashboard/log/:id',
                  component: LogDetails,
                  onEnter: (params) => props.openLog(params.id)
                }, {
                  path: '/dashboard/logs/users',
                  component: UserLogs,
                  exact: true
                }
              ])
            }
          />
        </div>
      </div>
    )}
  />

DashboardMain.propTypes = {
  path: T.string.isRequired,
  resourceNode: T.object.isRequired,
  openLog: T.func.isRequired
}

export {
  DashboardMain
}
