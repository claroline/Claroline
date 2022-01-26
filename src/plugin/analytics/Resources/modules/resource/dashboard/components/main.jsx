import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Await} from '#/main/app/components/await'
import {Routes} from '#/main/app/router'
import {Vertical} from '#/main/app/content/tabs/components/vertical'
import {ContentLoader} from '#/main/app/content/components/loader'

import {getAnalytics} from '#/plugin/analytics/resource/utils'
import {DashboardActivity} from '#/plugin/analytics/resource/dashboard/containers/activity'
import {DashboardOverview} from '#/plugin/analytics/resource/dashboard/containers/overview'

const DashboardMain = (props) =>
  <Await
    for={getAnalytics(props.resourceNode)}
    placeholder={
      <ContentLoader
        className="row"
        size="lg"
        description={trans('loading')}
      />
    }
    then={(apps) => (
      <div className="row">
        <div className="col-md-3">
          <Vertical
            style={{marginTop: 20}}
            basePath={props.path+'/dashboard'}
            tabs={
              [
                {
                  icon: 'fa fa-fw fa-pie-chart',
                  title: trans('overview', {}, 'analytics'),
                  path: '/',
                  exact: true
                }, {
                  icon: 'fa fa-fw fa-chart-line',
                  title: trans('activity'),
                  path: '/activity'
                }
              ].concat(apps.map(app => ({
                icon: app.icon,
                title: app.label,
                path: app.path,
                exact: true
              })))
            }
          />
        </div>

        <div className="col-md-9">
          <Routes
            path={props.path+'/dashboard'}
            routes={
              [
                {
                  path: '/',
                  component: DashboardOverview,
                  exact: true
                }, {
                  path: '/activity',
                  component: DashboardActivity
                }
              ].concat(apps.map(app => ({
                path: app.path,
                component: app.component,
                exact: true
              })))
            }
          />
        </div>
      </div>
    )}
  />

DashboardMain.propTypes = {
  path: T.string.isRequired,
  resourceNode: T.object.isRequired
}

export {
  DashboardMain
}
