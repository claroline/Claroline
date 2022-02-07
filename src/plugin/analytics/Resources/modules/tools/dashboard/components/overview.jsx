import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import isUndefined from 'lodash/isEmpty'
import get from 'lodash/get'

import {schemeCategory20c} from 'd3-scale'

import {trans, displayDuration} from '#/main/app/intl'
import {Await} from '#/main/app/components/await'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ContentCounter} from '#/main/app/content/components/counter'
import {ContentLoader} from '#/main/app/content/components/loader'

import {getWorkspaceAnalytics} from '#/plugin/analytics/utils'
import {DashboardResume} from '#/plugin/analytics/tools/dashboard/components/resume'

const DashboardOverview = (props) =>
  <ToolPage
    subtitle={trans('overview', {}, 'analytics')}
  >
    <div className="row">
      <ContentCounter
        icon="fa fa-folder"
        label={trans('resources')}
        color={schemeCategory20c[1]}
        value={props.count.resources}
      />

      <ContentCounter
        icon="fa fa-user"
        label={trans('users')}
        color={schemeCategory20c[5]}
        value={props.count.users}
      />

      <ContentCounter
        icon="fa fa-clock"
        label={trans('connections')}
        color={schemeCategory20c[9]}
        value={props.count.connections.count + (props.count.connections.avgTime ? ' ('+trans('connection_avg_time', {time: displayDuration(props.count.connections.avgTime)}, 'analytics')+')' : '')}
      />
    </div>

    <DashboardResume
      workspace={props.workspace}
    />

    <Await
      for={getWorkspaceAnalytics(props.workspace).then(apps => apps.filter(app => !isUndefined(get(app, 'components.overview'))))}
      placeholder={
        <ContentLoader
          className="row"
          size="lg"
          description={trans('loading', {}, 'analytics')}
        />
      }
      then={(apps) => apps.map((app) => createElement(get(app, 'components.overview')))}
    />
  </ToolPage>

DashboardOverview.propTypes = {
  workspace: T.shape({
    id: T.string.isRequired
  }).isRequired,
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
  DashboardOverview
}
