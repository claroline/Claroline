import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Await} from '#/main/app/components/await'
import {Routes} from '#/main/app/router'
import {ContentLoader} from '#/main/app/content/components/loader'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

import {getResourceAnalytics} from '#/plugin/analytics/utils'
import {DashboardOverview} from '#/plugin/analytics/resource/dashboard/containers/overview'

const DashboardMain = (props) =>
  <Await
    for={getResourceAnalytics(props.resourceNode).then(apps => apps.filter(app => !isEmpty(get(app, 'components.tab'))))}
    placeholder={
      <ContentLoader
        className="row"
        size="lg"
        description={trans('loading', {}, 'analytics')}
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
                }
              ].concat(apps.map(app => ({
                name: app.name,
                icon: app.meta.icon,
                title: app.meta.label,
                path: '/'+app.name
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
                }
              ].concat(apps.map(app => ({
                path: '/'+app.name,
                component: app.components.tab
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
