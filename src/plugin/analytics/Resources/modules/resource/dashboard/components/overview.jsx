import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentTitle} from '#/main/app/content/components/title'

import {ActivityChart} from '#/plugin/analytics/charts/activity/containers/chart'
import {LatestActionsChart} from '#/plugin/analytics/charts/latest-actions/containers/chart'
import {TopUsersChart} from '#/plugin/analytics/charts/top-users/containers/chart'
import {UsersChart} from '#/plugin/analytics/charts/users/containers/chart'

const DashboardOverview = (props) =>
  <Fragment>
    <ContentTitle
      title={trans('overview', {}, 'analytics')}
    />

    <div className="row">
      <div className="col-md-8">
        <ActivityChart url={['apiv2_resource_analytics_activity', {resource: props.resourceId}]} />

        {false &&
          <div className="row">
            <div className="col-md-4">
              <UsersChart url={['apiv2_resource_analytics_users', {resource: props.resourceId}]} />
            </div>

            <div className="col-md-8">
              <TopUsersChart url={['apiv2_resource_analytics_top_users', {resource: props.resourceId}]} />
            </div>
          </div>
        }
      </div>

      <div className="col-md-4">
        <LatestActionsChart url={['apiv2_resource_logs_list', {resourceId: props.resourceId}]} />
      </div>
    </div>
  </Fragment>

DashboardOverview.propTypes = {
  resourceId: T.string.isRequired
}

export {
  DashboardOverview
}