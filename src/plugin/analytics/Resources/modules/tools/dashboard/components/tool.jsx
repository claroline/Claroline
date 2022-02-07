import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {Routes} from '#/main/app/router'
import {Await} from '#/main/app/components/await'
import {ContentLoader} from '#/main/app/content/components/loader'

import {getWorkspaceAnalytics} from '#/plugin/analytics/utils'
import {DashboardOverview} from '#/plugin/analytics/tools/dashboard/containers/overview'

const DashboardTool = (props) =>
  <Await
    for={getWorkspaceAnalytics(props.workspace).then(apps => apps.filter(app => !isEmpty(get(app, 'components.tab'))))}
    placeholder={
      <ContentLoader
        className="row"
        size="lg"
        description={trans('loading', {}, 'tools')}
      />
    }
    then={(apps) => (
      <Routes
        path={props.path}
        routes={[
          {
            path: '/',
            exact: true,
            component: DashboardOverview
          }
        ].concat(apps.map(app => ({
          path: '/'+app.name,
          component: app.components.tab
        })))}
      />
    )}
  />

DashboardTool.propTypes = {
  path: T.string.isRequired,
  workspace: T.shape({
    id: T.string
  }).isRequired
}

export {
  DashboardTool
}
